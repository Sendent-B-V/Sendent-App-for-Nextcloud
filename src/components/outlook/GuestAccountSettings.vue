<!--
  - @copyright Copyright (c) 2026 Sendent B.V.
  -
  - @author Sendent B.V. <info@sendent.com>
  -
  - @license AGPL-3.0-or-later
  -
  - This program is free software: you can redistribute it and/or modify
  - it under the terms of the GNU Affero General Public License as
  - published by the Free Software Foundation, either version 3 of the
  - License, or (at your option) any later version.
  -
  - This program is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  - GNU Affero General Public License for more details.
  -
  - You should have received a copy of the GNU Affero General Public License
  - along with this program. If not, see <http://www.gnu.org/licenses/>.
  -->
<template>
	<SettingsSection :title="t('sendent', 'Guest Accounts')"
		:definitions="definitions"
		:labels="labels" />
</template>

<script setup lang="ts">
import { translate as t } from '@nextcloud/l10n'
import SettingsSection from '../settings/SettingsSection.vue'
import { getSettingsForSection } from '../../common/settingsRegistry'

const definitions = getSettingsForSection('GuestAccounts')

const labels: Record<string, string> = {
	guestaccountsenabled: t('sendent', 'Activate Guest Accounts'),
	guestaccountsenforced: t('sendent', 'Enforce Guest Accounts'),
	disableanonymousshare: t('sendent', 'Disable anonymous share'),
	htmlsnippetguestaccounts: t('sendent', 'Guest accounts snippet'),
	htmlsnippetpublicaccounts: t('sendent', 'Public accounts snippet'),
}
</script>
