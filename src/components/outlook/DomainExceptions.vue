<template>
	<SettingsSection title="Domain Exceptions"
		:definitions="definitions"
		:labels="labels" />
</template>

<script setup lang="ts">
import SettingsSection from '../settings/SettingsSection.vue'
import { getSettingsForSection } from '../../common/settingsRegistry'

const definitions = getSettingsForSection('DomainExceptions')

const labels: Record<string, string> = {
	attachmentdomainexceptionsinternal: 'Internal domain exceptions',
	attachmentdomainexceptions: 'External domain exceptions',
	attachmentdomainexceptionsexternalpopup: 'External domain exceptions popup',
}
</script>
