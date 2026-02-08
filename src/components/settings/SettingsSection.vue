<template>
	<div class="settings-section">
		<h3 class="settings-section__title">
			{{ title }}
		</h3>
		<p class="settings-section__autosave">
			Changes are saved automatically
		</p>
		<SettingField v-for="def in definitions"
			:key="def.key"
			:definition="def"
			:label="labels[def.name] ?? def.name" />
	</div>
</template>

<script setup lang="ts">
import type { SettingDefinition } from '../../types/settings'
import SettingField from './SettingField.vue'

defineProps<{
	title: string
	definitions: SettingDefinition[]
	labels: Record<string, string>
}>()
</script>

<style scoped>
.settings-section {
	margin-bottom: 30px;
}

.settings-section__title {
	font-size: 16px;
	font-weight: 600;
	margin-bottom: 4px;
	padding-bottom: 8px;
	border-bottom: 1px solid var(--color-border);
}

.settings-section__autosave {
	font-size: 12px;
	color: var(--color-text-maxcontrast);
	margin-bottom: 16px;
}
</style>
