<template>
	<span v-if="saving" class="save-indicator save-indicator--saving">
		&#x21BB;
	</span>
	<span v-else-if="saved" class="save-indicator save-indicator--saved">
		&#x2713;
	</span>
</template>

<script setup lang="ts">
defineProps<{
	saving: boolean
	saved: boolean
}>()
</script>

<style scoped>
.save-indicator {
	display: inline-block;
	margin-left: 8px;
	font-variant-emoji: text;
}

.save-indicator--saving {
	animation: spin 1s linear infinite;
	color: var(--color-text-maxcontrast);
}

.save-indicator--saved {
	color: var(--color-success-text);
	animation: fadeIn 0.3s ease-in;
}

@keyframes spin {
	from { transform: rotate(0deg); }
	to { transform: rotate(360deg); }
}

@keyframes fadeIn {
	from { opacity: 0; }
	to { opacity: 1; }
}
</style>
