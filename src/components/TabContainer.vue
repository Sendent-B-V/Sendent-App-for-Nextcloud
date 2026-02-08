<template>
	<div class="sendent-tabs">
		<div class="sendent-tabs__nav">
			<button v-for="tab in tabs"
				:key="tab.id"
				class="sendent-tabs__tab"
				:class="{ 'sendent-tabs__tab--active': activeTab === tab.id }"
				@click="activeTab = tab.id">
				{{ tab.label }}
			</button>
		</div>
		<div class="sendent-tabs__content">
			<slot :name="activeTab" />
		</div>
	</div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

interface Tab {
	id: string
	label: string
}

const props = defineProps<{
	tabs: Tab[]
	defaultTab?: string
}>()

const activeTab = ref(props.defaultTab ?? props.tabs[0]?.id ?? '')
</script>

<style scoped>
.sendent-tabs__nav {
	display: flex;
	gap: 0;
	border-bottom: 1px solid var(--color-border);
	margin-bottom: 20px;
}

.sendent-tabs__tab {
	padding: 10px 20px;
	border: none;
	background: none;
	cursor: pointer;
	font-size: 14px;
	color: var(--color-text-maxcontrast);
	border-bottom: 2px solid transparent;
	transition: color 0.2s, border-color 0.2s;
}

.sendent-tabs__tab:hover {
	color: var(--color-main-text);
}

.sendent-tabs__tab--active {
	color: var(--color-primary);
	border-bottom-color: var(--color-primary);
}

.sendent-tabs__content {
	padding: 0 4px;
}
</style>
