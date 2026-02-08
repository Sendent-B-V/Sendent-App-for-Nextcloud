<template>
	<SettingsSection title="Talk"
		:definitions="definitions"
		:labels="labels" />
</template>

<script setup lang="ts">
import SettingsSection from '../settings/SettingsSection.vue'
import { getSettingsForSection } from '../../common/settingsRegistry'

const definitions = getSettingsForSection('Talk')

const labels: Record<string, string> = {
	talkenabled: 'Activate Talk',
	generatetalkpassword: 'Generate password for meetings',
	talkhtml: 'Talk snippet',
	talkpwsepenabled: 'Activate Talk Password separate',
	talkpwhtml: 'Talk password snippet',
}
</script>
