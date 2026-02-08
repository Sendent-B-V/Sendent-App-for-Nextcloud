<template>
	<div class="general-tab">
		<OutlookAddonInfo />
		<DefaultLicenseStatus />
		<DependenciesChecker />
		<RetentionAssistant />
	</div>
</template>

<script setup lang="ts">
import OutlookAddonInfo from './OutlookAddonInfo.vue'
import DefaultLicenseStatus from './DefaultLicenseStatus.vue'
import DependenciesChecker from './DependenciesChecker.vue'
import RetentionAssistant from './RetentionAssistant.vue'
</script>
