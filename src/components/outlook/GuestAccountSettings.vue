<template>
	<SettingsSection title="Guest Accounts"
		:definitions="definitions"
		:labels="labels" />
</template>

<script setup lang="ts">
import SettingsSection from '../settings/SettingsSection.vue'
import { getSettingsForSection } from '../../common/settingsRegistry'

const definitions = getSettingsForSection('GuestAccounts')

const labels: Record<string, string> = {
	guestaccountsenabled: 'Activate Guest Accounts',
	guestaccountsenforced: 'Enforce Guest Accounts',
	disableanonymousshare: 'Disable anonymous share',
	htmlsnippetguestaccounts: 'Guest accounts snippet',
	htmlsnippetpublicaccounts: 'Public accounts snippet',
}
</script>
