<?php

/**
 * @copyright Copyright (c) 2026 Sendent B.V.
 *
 * @author Sendent B.V. <info@sendent.com>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace OCA\Sendent\Service;

use OCP\Accounts\IAccount;
use OCP\Accounts\IAccountManager;
use OCP\Accounts\PropertyDoesNotExistException;
use OCP\IConfig;
use OCP\IURLGenerator;
use OCP\IUserManager;
use Psr\Log\LoggerInterface;

/**
 * Renders an email-signature template by substituting {TAG} placeholders with
 * the profile fields of a given user.
 *
 * Profile values are read via IAccountManager::getAccount(), which performs no
 * scope filtering server-side — private-scoped fields resolve without the user
 * changing privacy settings. This service is only ever called to render for
 * the REQUESTING user (their own data), so no enumeration/privacy gating is
 * required (compare UserLookupService::isResolvableBy(), which short-circuits
 * for self-lookups for the same reason).
 *
 * FIRSTNAME/MIDDLENAME/LASTNAME are derived from the display name: first
 * token, everything-in-between, last token ("Luc van der Berg" -> Luc /
 * van der / Berg). Multi-word surnames without particles split imperfectly;
 * known v1 limitation.
 *
 * 'pronouns' is addressed by string literal, not by constant: the
 * IAccountManager::PROPERTY_PRONOUNS constant only exists from NC 31 and this
 * app supports NC 30. Missing properties resolve to ''.
 *
 * {LOGO} is the one org-level (not per-user) tag: it resolves to the
 * instance's theming logo (falling back to core's default logo when no
 * custom logo has been uploaded — see logoUrl()), or '' if resolution fails
 * entirely (e.g. theming app disabled).
 *
 * Escaping contract: substituted values are HTML-escaped (and BIOGRAPHY is
 * additionally nl2br'd), so tags may only be placed in HTML text content or
 * quoted-attribute contexts — never inside a <script>/<style> block or an
 * unquoted attribute, which htmlspecialchars() does not protect against.
 * Templates are admin-editable, not end-user input, but this contract must
 * hold regardless.
 */
class SignatureService {
	/** Property name literal: IAccountManager::PROPERTY_PRONOUNS only exists from NC 31; this app supports NC 30. */
	private const PROPERTY_PRONOUNS = 'pronouns';

	private IUserManager $userManager;
	private IAccountManager $accountManager;
	private IConfig $config;
	private IURLGenerator $urlGenerator;
	private LoggerInterface $logger;

	public function __construct(
		IUserManager $userManager,
		IAccountManager $accountManager,
		IConfig $config,
		IURLGenerator $urlGenerator,
		LoggerInterface $logger,
	) {
		$this->userManager = $userManager;
		$this->accountManager = $accountManager;
		$this->config = $config;
		$this->urlGenerator = $urlGenerator;
		$this->logger = $logger;
	}

	/**
	 * Substitute all supported placeholder tags in $template with $userId's
	 * profile data. Unknown user: the template is returned unrendered.
	 */
	public function render(string $template, string $userId): string {
		$user = $this->userManager->get($userId);
		if ($user === null) {
			return $template;
		}

		$account = $this->accountManager->getAccount($user);
		$displayName = $user->getDisplayName() ?? '';
		[$firstName, $middleName, $lastName] = $this->splitDisplayName($displayName);

		$values = [
			'{DISPLAYNAME}' => $this->escape($displayName),
			'{FIRSTNAME}' => $this->escape($firstName),
			'{MIDDLENAME}' => $this->escape($middleName),
			'{LASTNAME}' => $this->escape($lastName),
			'{PRONOUNS}' => $this->escape($this->propertyValue($account, self::PROPERTY_PRONOUNS)),
			'{EMAIL}' => $this->escape($user->getEMailAddress() ?? ''),
			'{PHONE}' => $this->escape($this->propertyValue($account, IAccountManager::PROPERTY_PHONE)),
			'{LOCATION}' => $this->escape($this->propertyValue($account, IAccountManager::PROPERTY_ADDRESS)),
			'{WEBSITE}' => $this->escape($this->propertyValue($account, IAccountManager::PROPERTY_WEBSITE)),
			'{ORGANISATION}' => $this->escape($this->propertyValue($account, IAccountManager::PROPERTY_ORGANISATION)),
			'{ROLE}' => $this->escape($this->propertyValue($account, IAccountManager::PROPERTY_ROLE)),
			'{HEADLINE}' => $this->escape($this->propertyValue($account, IAccountManager::PROPERTY_HEADLINE)),
			'{BIOGRAPHY}' => nl2br($this->escape($this->propertyValue($account, IAccountManager::PROPERTY_BIOGRAPHY))),
			'{LOGO}' => $this->escape($this->logoUrl()),
		];

		// strtr() scans the subject once, left to right, so a profile value that
		// happens to literally contain e.g. '{EMAIL}' is never re-substituted —
		// unlike str_replace(), which rescans its own output.
		return strtr($template, $values);
	}

	private function propertyValue(IAccount $account, string $property): string {
		try {
			return $account->getProperty($property)->getValue();
		} catch (PropertyDoesNotExistException $e) {
			// Expected: e.g. 'pronouns' on NC 30, or any property the profile has
			// never had a value set for.
			return '';
		} catch (\Throwable $e) {
			$this->logger->warning(
				'Signature tag for account property {property} could not be resolved.',
				['property' => $property, 'exception' => $e],
			);
			return '';
		}
	}

	private function escape(string $value): string {
		return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
	}

	/**
	 * Absolute URL of the instance logo: the theming app's uploaded logo when
	 * one exists, else core's default logo. The theming getImage route 404s
	 * without an uploaded logo, so the logoMime check mirrors core's own
	 * ThemingDefaults::getLogo() fallback. useSvg=0 requests the PNG
	 * conversion (SVG does not render in Outlook/Gmail); passed as 0, not
	 * 'false', because AppFramework's bool coercion treats any non-empty
	 * string as true. ?v= cache-buster mirrors core (the route caches 3600s).
	 * {LOGO} is the one org-level tag — all others are per-user profile fields.
	 */
	private function logoUrl(): string {
		try {
			$cacheBuster = $this->config->getAppValue('theming', 'cachebuster', '0');
			if ($this->config->getAppValue('theming', 'logoMime', '') === '') {
				return $this->urlGenerator->getAbsoluteURL(
					$this->urlGenerator->imagePath('core', 'logo/logo.png')
				) . '?v=' . $cacheBuster;
			}
			return $this->urlGenerator->linkToRouteAbsolute(
				'theming.Theming.getImage',
				['key' => 'logo', 'useSvg' => 0, 'v' => $cacheBuster]
			);
		} catch (\Throwable $e) {
			$this->logger->debug('Could not resolve instance logo for {LOGO} signature tag.', ['exception' => $e]);
			return '';
		}
	}

	/**
	 * @return array{0: string, 1: string, 2: string} [first, middle, last]
	 */
	private function splitDisplayName(string $displayName): array {
		$tokens = preg_split('/\s+/', trim($displayName), -1, PREG_SPLIT_NO_EMPTY);
		if ($tokens === false || count($tokens) === 0) {
			return ['', '', ''];
		}
		if (count($tokens) === 1) {
			return [$tokens[0], '', ''];
		}
		$first = array_shift($tokens);
		$last = array_pop($tokens);
		return [$first, implode(' ', $tokens), $last];
	}
}
