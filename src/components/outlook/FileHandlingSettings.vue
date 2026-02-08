<template>
	<SettingsSection title="File Handling"
		:definitions="definitions"
		:labels="labels" />
</template>

<script setup lang="ts">
import SettingsSection from '../settings/SettingsSection.vue'
import { getSettingsForSection } from '../../common/settingsRegistry'

const definitions = getSettingsForSection('FileHandling')

const labels: Record<string, string> = {
	pathuploadfiles: 'Path for uploaded files',
	sharefilehtml: 'File share snippet',
	pathpublicshare: 'Path for public share',
	sharefolderhtml: 'Folder share snippet',
	enforceFiledrop: 'Enforce file drop',
}
</script>
