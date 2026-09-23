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
		:labels="labels">
		<p v-if="guestsAppMissing" class="guest-accounts__warning">
			{{ t('sendent', 'Guest accounts require the Nextcloud Guests app.') }}
			<a :href="appsUrl">{{ t('sendent', 'Enable it under Apps') }}</a>
		</p>
	</SettingsSection>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { translate as t } from '@nextcloud/l10n'
import { generateUrl } from '@nextcloud/router'
import SettingsSection from '../settings/SettingsSection.vue'
import { getSettingsForSection } from '../../common/settingsRegistry'
import { useDependenciesStore } from '../../stores/dependencies'
import type { Client } from '../../types/settings'

const props = defineProps<{
	/** Settings tab that renders this section. Settings restricted to other clients are skipped. */
	client: Client
}>()

const definitions = getSettingsForSection('GuestAccounts')
	.filter(def => !def.clients || def.clients.includes(props.client))

const labels: Record<string, string> = {
	guestaccountsenabled: t('sendent', 'Activate Guest Accounts'),
	guestaccountsenforced: t('sendent', 'Enforce Guest Accounts'),
	disableanonymousshare: t('sendent', 'Disable anonymous share'),
	htmlsnippetguestaccounts: t('sendent', 'Guest accounts snippet'),
	htmlsnippetpublicaccounts: t('sendent', 'Public accounts snippet'),
}

const depsStore = useDependenciesStore()
const appsUrl = generateUrl('/settings/apps')

/** False while the capabilities check has not run yet, so nothing is shown until the answer is known. */
const guestsAppMissing = computed(() => depsStore.recommendedApps.find(app => app.id === 'guests')?.installed === false)

onMounted(() => {
	// The Dependencies panel on the General tab normally fills the store; fetch here if this tab was opened first.
	if (depsStore.recommendedApps.length === 0) {
		depsStore.checkDependencies()
	}
})
</script>

<style scoped>
.guest-accounts__warning {
	margin: 0 0 16px;
	padding: 8px 12px;
	border-left: 4px solid var(--color-warning);
	border-radius: var(--border-radius);
	background-color: var(--color-background-hover);
}
</style>
