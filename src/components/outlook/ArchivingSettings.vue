<template>
	<SettingsSection title="Archiving"
		:definitions="definitions"
		:labels="labels" />
</template>

<script setup lang="ts">
import SettingsSection from '../settings/SettingsSection.vue'
import { getSettingsForSection } from '../../common/settingsRegistry'

const definitions = getSettingsForSection('Archiving')

const labels: Record<string, string> = {
	archivingenabled: 'Activate Archiving',
	patharchiving: 'Path for Archiving',
	archivemode: 'Archive mode',
	archivecreatefolder: 'Archive create folder',
}
</script>
