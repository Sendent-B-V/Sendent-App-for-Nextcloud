<template>
	<SettingsSection title="Secure Mail"
		:definitions="definitions"
		:labels="labels" />
</template>

<script setup lang="ts">
import SettingsSection from '../settings/SettingsSection.vue'
import { getSettingsForSection } from '../../common/settingsRegistry'

const definitions = getSettingsForSection('SecureMail')

const labels: Record<string, string> = {
	securemail: 'Activate Secure Mail',
	securemailenforced: 'Enforce Secure Mail',
	securemailuimode: 'Secure Mail UI mode',
	pathsecuremailbox: 'Path for Secure Mail mailbox',
	securemailhtml: 'Secure Mail snippet',
}
</script>
