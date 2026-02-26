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
	<SettingsSection title="Advanced Theming"
		:definitions="definitions"
		:labels="labels" />
</template>

<script setup lang="ts">
import SettingsSection from '../settings/SettingsSection.vue'
import { getSettingsForSection } from '../../common/settingsRegistry'

const definitions = getSettingsForSection('AdvancedTheming')

const labels: Record<string, string> = {
	AdvancedThemingEnabled: 'Enable Advanced Theming',
	VendorName: 'Vendor Name',
	ButtonPrimaryColor: 'Button Primary Color',
	ButtonPrimaryFontColor: 'Button Primary Font Color',
	ButtonPrimaryHoverColor: 'Button Primary Hover Color',
	ButtonPrimaryIconColor: 'Button Primary Icon Color',
	ButtonSecondaryColor: 'Button Secondary Color',
	ButtonSecondaryFontColor: 'Button Secondary Font Color',
	ButtonSecondaryHoverColor: 'Button Secondary Hover Color',
	ButtonSecondaryIconColor: 'Button Secondary Icon Color',
	DialogFooterBackgroundColor: 'Dialog Footer Background Color',
	DialogFooterFontColor: 'Dialog Footer Font Color',
	DialogFooterHoverColor: 'Dialog Footer Hover Color',
	DialogFooterIconColor: 'Dialog Footer Icon Color',
	DialogFooterIconBackgroundColor: 'Dialog Footer Icon Background Color',
	DialogHeaderColor: 'Dialog Header Color',
	DialogHeaderFontColor: 'Dialog Header Font Color',
	PopupBackgroundColor: 'Popup Background Color',
	GeneralFontColor: 'General Font Color',
	GeneralIconColor: 'General Icon Color',
	TaskpaneActivityTrackerColor: 'Taskpane Activity Tracker Color',
	TaskpaneActivityTrackerFontColor: 'Taskpane Activity Tracker Font Color',
	TaskpaneSecureMailColor: 'Taskpane Secure Mail Color',
	TaskpaneSecureMailFontColor: 'Taskpane Secure Mail Font Color',
	TaskpaneSecureMailControlColor: 'Taskpane Secure Mail Control Color',
}
</script>
