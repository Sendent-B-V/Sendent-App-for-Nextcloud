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
	<SettingsSection :title="t('sendent', 'Domain Exceptions')"
		:description="description"
		:definitions="definitions"
		:labels="labels" />
</template>

<script setup lang="ts">
import { translate as t } from '@nextcloud/l10n'
import SettingsSection from '../settings/SettingsSection.vue'
import { getSettingsForSection } from '../../common/settingsRegistry'

const definitions = getSettingsForSection('DomainExceptions')

const description = t('sendent', 'When a user clicks Send, the add-in checks the email for attachments to share and, if enforced, for Secure Mail. An email skips these checks when every recipient is on one of these lists. Add a domain (example.com) or a full address (name@example.com). Matches are exact, so a domain does not include its subdomains.')

const labels: Record<string, string> = {
	attachmentdomainexceptionsinternal: t('sendent', 'Internal domain exceptions'),
	attachmentdomainexceptions: t('sendent', 'External domain exceptions'),
	attachmentdomainexceptionsexternalpopup: t('sendent', 'External domain exceptions popup'),
}
</script>
