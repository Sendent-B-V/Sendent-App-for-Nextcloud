<template>
	<div class="dependencies">
		<h3>Dependencies</h3>
		<div v-if="depsStore.loading" class="dependencies__loading">
			<span class="icon-loading" />
			Checking dependencies...
		</div>
		<div v-else class="dependencies__columns">
			<div class="dependencies__group">
				<h4>Required</h4>
				<div v-for="app in depsStore.requiredApps"
					:key="app.id"
					class="dependencies__item"
					:class="{ 'dependencies__item--ok': app.installed, 'dependencies__item--fail': !app.installed }">
					<span class="dependencies__icon">{{ app.installed ? '&#x2713;' : 'X' }}</span>
					<span>{{ app.name }}</span>
				</div>
			</div>
			<div class="dependencies__group">
				<h4>Recommended</h4>
				<div v-for="app in depsStore.recommendedApps"
					:key="app.id"
					class="dependencies__item"
					:class="{ 'dependencies__item--ok': app.installed, 'dependencies__item--warn': !app.installed }">
					<span class="dependencies__icon">{{ app.installed ? '&#x2713;' : 'X' }}</span>
					<span>{{ app.name }}</span>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useDependenciesStore } from '../../stores/dependencies'

const depsStore = useDependenciesStore()

onMounted(() => {
	depsStore.checkDependencies()
})
</script>

<style scoped>
.dependencies {
	margin-bottom: 24px;
}

.dependencies h3 {
	font-size: 16px;
	font-weight: 600;
	margin-bottom: 12px;
}

.dependencies__loading {
	display: flex;
	align-items: center;
	gap: 8px;
	color: var(--color-text-maxcontrast);
}

.dependencies__columns {
	display: flex;
	gap: 40px;
}

.dependencies__group {
	flex: 1;
	min-width: 200px;
	margin-bottom: 12px;
}

.dependencies__group h4 {
	font-weight: 500;
	margin-bottom: 6px;
}

.dependencies__item {
	display: flex;
	align-items: center;
	gap: 8px;
	padding: 4px 8px;
}

.dependencies__icon {
	font-variant-emoji: text;
}

.dependencies__item--ok .dependencies__icon {
	color: var(--color-success-text);
}

.dependencies__item--fail .dependencies__icon {
	color: var(--color-error-text);
}

.dependencies__item--warn .dependencies__icon {
	color: var(--color-warning);
}
</style>
