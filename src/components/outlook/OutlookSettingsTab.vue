<template>
	<div class="outlook-settings-tab">
		<GeneralSettings />
		<TalkSettings />
		<ArchivingSettings />
		<DomainExceptions />
		<AttachmentSettings />
		<FileHandlingSettings />
		<SecureMailSettings />
		<GuestAccountSettings />
		<AdvancedTheming />
	</div>
</template>

<script setup lang="ts">
import GeneralSettings from './GeneralSettings.vue'
import TalkSettings from './TalkSettings.vue'
import ArchivingSettings from './ArchivingSettings.vue'
import DomainExceptions from './DomainExceptions.vue'
import AttachmentSettings from './AttachmentSettings.vue'
import FileHandlingSettings from './FileHandlingSettings.vue'
import SecureMailSettings from './SecureMailSettings.vue'
import GuestAccountSettings from './GuestAccountSettings.vue'
import AdvancedTheming from './AdvancedTheming.vue'
</script>
