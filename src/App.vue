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
	<div id="sendent-admin-settings">
		<!-- Terms gate: blocks access until agreed -->
		<TermsAgreement v-if="!termsStore.agreed && !termsStore.loading" />

		<!-- Main app content -->
		<template v-if="termsStore.agreed">
			<h2>Sendent for Outlook / Teams</h2>
			<TabContainer :tabs="mainTabs" default-tab="general">
				<template #general>
					<GeneralTab />
				</template>
				<template #groups>
					<GroupSettingsTab />
				</template>
			</TabContainer>
		</template>

		<!-- Loading state -->
		<div v-if="termsStore.loading" class="sendent-loading">
			<span class="icon-loading" />
		</div>
	</div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useTermsStore } from './stores/terms'
import { useGroupsStore } from './stores/groups'
import { useLicenseStore } from './stores/license'
import { useRetentionStore } from './stores/retention'
import TermsAgreement from './components/TermsAgreement.vue'
import TabContainer from './components/TabContainer.vue'
import GeneralTab from './components/general/GeneralTab.vue'
import GroupSettingsTab from './components/groups/GroupSettingsTab.vue'

const termsStore = useTermsStore()
const groupsStore = useGroupsStore()
const licenseStore = useLicenseStore()
const retentionStore = useRetentionStore()

const mainTabs = [
	{ id: 'general', label: 'General' },
	{ id: 'groups', label: 'Group Settings' },
]

onMounted(async () => {
	// Load initial state from PHP
	groupsStore.loadInitialState()
	licenseStore.loadInitialState()
	retentionStore.loadInitialState()

	// Check terms agreement
	await termsStore.checkAgreement()

	// If agreed, fetch initial data
	if (termsStore.agreed) {
		await licenseStore.refreshStatus('')
	}
})
</script>

<style scoped>
#sendent-admin-settings {
	padding: 20px;
	max-width: 1200px;
}

#sendent-admin-settings h2 {
	font-size: 20px;
	font-weight: 700;
	margin-bottom: 16px;
}

.sendent-loading {
	display: flex;
	justify-content: center;
	padding: 40px;
}
</style>
