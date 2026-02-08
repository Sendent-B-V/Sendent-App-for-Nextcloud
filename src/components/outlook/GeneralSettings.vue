<template>
	<SettingsSection title="General"
		:definitions="definitions"
		:labels="labels" />
</template>

<script setup lang="ts">
import SettingsSection from '../settings/SettingsSection.vue'
import { getSettingsForSection } from '../../common/settingsRegistry'

const definitions = getSettingsForSection('General')

const labels: Record<string, string> = {
	setlanguage: 'Language',
	insertatcursor: 'Insert snippet location',
	dateaddition: 'Add upload date to folder path',
	debugmode: 'Debug mode',
	disablesettings: 'Access to settings',
	passwordcontrolbehavior: 'Password control behavior',
	sendmode: 'Password communication mode',
	htmlsnippetpassword: 'Password communication snippet',
	statussync: 'Presence Synchronization',
}
</script>
