<template>
	<SettingsSection title="Teams"
		:definitions="definitions"
		:labels="labels" />
</template>

<script setup lang="ts">
import SettingsSection from '../settings/SettingsSection.vue'
import { getSettingsForSection } from '../../common/settingsRegistry'

const definitions = getSettingsForSection('Teams')

const labels: Record<string, string> = {
	teams_pathuploadfiles: 'Path for uploaded files',
}
</script>
