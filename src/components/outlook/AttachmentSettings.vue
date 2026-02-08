<template>
	<SettingsSection title="Attachments"
		:definitions="definitions"
		:labels="labels" />
</template>

<script setup lang="ts">
import SettingsSection from '../settings/SettingsSection.vue'
import { getSettingsForSection } from '../../common/settingsRegistry'

const definitions = getSettingsForSection('Attachments')

const labels: Record<string, string> = {
	attachmentmode: 'Attachment mode',
	attachmentsize: 'Maximum attachment size (MB)',
	senderexceptions: 'Sender exceptions',
}
</script>
