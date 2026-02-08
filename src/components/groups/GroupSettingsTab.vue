<template>
	<div class="group-settings-tab">
		<GroupsManagement />
		<div v-if="settingsStore.loading" class="group-settings-tab__loading">
			<span class="icon-loading" />
			Loading settings...
		</div>
		<div v-else class="group-settings-tab__content">
			<TabContainer :tabs="subtabs" default-tab="license">
				<template #license>
					<LicenseSection />
				</template>
				<template #outlook>
					<OutlookSettingsTab />
				</template>
				<template #teams>
					<TeamsSettingsTab />
				</template>
			</TabContainer>
		</div>
	</div>
</template>

<script setup lang="ts">
import { useSettingsStore } from '../../stores/settings'
import GroupsManagement from './GroupsManagement.vue'
import TabContainer from '../TabContainer.vue'
import LicenseSection from '../license/LicenseSection.vue'
import OutlookSettingsTab from '../outlook/OutlookSettingsTab.vue'
import TeamsSettingsTab from '../teams/TeamsSettingsTab.vue'

const settingsStore = useSettingsStore()

const subtabs = [
	{ id: 'license', label: 'License' },
	{ id: 'outlook', label: 'Outlook Settings' },
	{ id: 'teams', label: 'Teams Settings' },
]
</script>

<style scoped>
.group-settings-tab__loading {
	display: flex;
	align-items: center;
	gap: 8px;
	padding: 20px;
	color: var(--color-text-maxcontrast);
}
</style>
