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
	<div class="group-settings-tab">
		<GroupsManagement />
		<div v-if="settingsStore.loading" class="group-settings-tab__loading">
			<span class="icon-loading" />
			{{ t('sendent', 'Loading settings …') }}
		</div>
		<div v-else class="group-settings-tab__content">
			<TabContainer :tabs="subtabs" default-tab="license" hash-key="subtab">
				<template #license>
					<DocumentationLink :links="[
						{ href: 'https://help.sendent.com/sendent-app-for-nextcloud-outlook-ms-teams/how-to-configure-your-license-information-on-the-sendent-server-app', label: t('sendent', 'How to configure your license') },
					]" />
					<h3 class="group-settings-tab__active-group">{{ editingHeading }}</h3>
					<LicenseSection />
				</template>
				<template #outlook>
					<DocumentationLink :links="[
						{ href: 'https://help.sendent.com/sendent-for-outlook-cross-platform/3019/getting-started-administrator', label: t('sendent', 'Cross-Platform Outlook') },
						{ href: 'https://help.sendent.com/sendent-for-outlook-windows-only/3021/getting-started-administrator', label: t('sendent', 'Windows Only Outlook Classic') },
					]" />
					<h3 class="group-settings-tab__active-group">{{ editingHeading }}</h3>
					<OutlookSettingsTab />
				</template>
				<template #teams>
					<DocumentationLink :links="[
						{ href: 'https://help.sendent.com/sendent-for-ms-teams/3024/getting-started-administrator', label: t('sendent', 'Sendent for Microsoft Teams') },
					]" />
					<h3 class="group-settings-tab__active-group">{{ editingHeading }}</h3>
					<TeamsSettingsTab />
				</template>
			</TabContainer>
		</div>
	</div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { translate as t } from '@nextcloud/l10n'
import { useSettingsStore } from '../../stores/settings'
import { useGroupsStore } from '../../stores/groups'
import GroupsManagement from './GroupsManagement.vue'
import TabContainer from '../TabContainer.vue'
import DocumentationLink from '../DocumentationLink.vue'
import LicenseSection from '../license/LicenseSection.vue'
import OutlookSettingsTab from '../outlook/OutlookSettingsTab.vue'
import TeamsSettingsTab from '../teams/TeamsSettingsTab.vue'

const settingsStore = useSettingsStore()
const groupsStore = useGroupsStore()

/** "Editing: <group>" heading. Rendered as text, so placeholder escaping and sanitizing are disabled. */
const editingHeading = computed(() => {
	const group = groupsStore.selectedGroup?.displayName || t('sendent', 'Default')
	return t('sendent', 'Editing: {group}', { group }, undefined, { escape: false, sanitize: false })
})

const subtabs = [
	{ id: 'license', label: t('sendent', 'License') },
	{ id: 'outlook', label: t('sendent', 'Outlook Settings') },
	{ id: 'teams', label: t('sendent', 'Teams Settings') },
]
</script>

<style scoped>
.group-settings-tab__active-group {
	font-size: 16px;
	font-weight: 600;
	margin: 0 0 16px 0;
}

.group-settings-tab__loading {
	display: flex;
	align-items: center;
	gap: 8px;
	padding: 20px;
	color: var(--color-text-maxcontrast);
}
</style>
