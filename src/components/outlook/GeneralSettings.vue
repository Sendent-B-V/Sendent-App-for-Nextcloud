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
	<SettingsSection :title="t('sendent', 'General')"
		:definitions="definitions"
		:labels="labels" />
</template>

<script setup lang="ts">
import { translate as t } from '@nextcloud/l10n'
import SettingsSection from '../settings/SettingsSection.vue'
import { getSettingsForSection } from '../../common/settingsRegistry'

const definitions = getSettingsForSection('General')

const labels: Record<string, string> = {
	setlanguage: t('sendent', 'Language'),
	insertatcursor: t('sendent', 'Insert snippet location'),
	dateaddition: t('sendent', 'Add upload date to folder path'),
	debugmode: t('sendent', 'Debug mode'),
	disablesettings: t('sendent', 'Access to settings'),
	passwordcontrolbehavior: t('sendent', 'Password control behavior'),
	sendmode: t('sendent', 'Password communication mode'),
	htmlsnippetpassword: t('sendent', 'Password communication snippet'),
	statussync: t('sendent', 'Presence Synchronization'),
	sharedaysenabled: t('sendent', 'Share expiration days'),
	sharedays: t('sendent', 'Number of days'),
}
</script>
