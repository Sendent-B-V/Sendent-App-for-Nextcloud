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
	<SettingsSection :title="t('sendent', 'Advanced Theming')"
		:definitions="definitions"
		:labels="labels" />
</template>

<script setup lang="ts">
import { translate as t } from '@nextcloud/l10n'
import SettingsSection from '../settings/SettingsSection.vue'
import { getSettingsForSection } from '../../common/settingsRegistry'

const definitions = getSettingsForSection('AdvancedTheming')

const labels: Record<string, string> = {
	AdvancedThemingEnabled: t('sendent', 'Enable Advanced Theming'),
	VendorName: t('sendent', 'Vendor Name'),
	ButtonPrimaryColor: t('sendent', 'Button Primary Color'),
	ButtonPrimaryFontColor: t('sendent', 'Button Primary Font Color'),
	ButtonPrimaryHoverColor: t('sendent', 'Button Primary Hover Color'),
	ButtonPrimaryIconColor: t('sendent', 'Button Primary Icon Color'),
	ButtonSecondaryColor: t('sendent', 'Button Secondary Color'),
	ButtonSecondaryFontColor: t('sendent', 'Button Secondary Font Color'),
	ButtonSecondaryHoverColor: t('sendent', 'Button Secondary Hover Color'),
	ButtonSecondaryIconColor: t('sendent', 'Button Secondary Icon Color'),
	DialogFooterBackgroundColor: t('sendent', 'Dialog Footer Background Color'),
	DialogFooterFontColor: t('sendent', 'Dialog Footer Font Color'),
	DialogFooterHoverColor: t('sendent', 'Dialog Footer Hover Color'),
	DialogFooterIconColor: t('sendent', 'Dialog Footer Icon Color'),
	DialogFooterIconBackgroundColor: t('sendent', 'Dialog Footer Icon Background Color'),
	DialogHeaderColor: t('sendent', 'Dialog Header Color'),
	DialogHeaderFontColor: t('sendent', 'Dialog Header Font Color'),
	PopupBackgroundColor: t('sendent', 'Popup Background Color'),
	GeneralFontColor: t('sendent', 'General Font Color'),
	GeneralIconColor: t('sendent', 'General Icon Color'),
	TaskpaneActivityTrackerColor: t('sendent', 'Taskpane Activity Tracker Color'),
	TaskpaneActivityTrackerFontColor: t('sendent', 'Taskpane Activity Tracker Font Color'),
	TaskpaneSecureMailColor: t('sendent', 'Taskpane Secure Mail Color'),
	TaskpaneSecureMailFontColor: t('sendent', 'Taskpane Secure Mail Font Color'),
	TaskpaneSecureMailControlColor: t('sendent', 'Taskpane Secure Mail Control Color'),
}
</script>
