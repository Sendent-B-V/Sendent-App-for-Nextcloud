<template>
	<div class="setting-textarea">
		<div class="setting-textarea__toggle"
			@click="expanded = !expanded">
			<span class="setting-textarea__arrow">{{ expanded ? '&#x25BC;' : '&#x25B6;' }}</span>
			<span>{{ expanded ? 'Hide editor' : 'Show editor' }}</span>
		</div>
		<div v-show="expanded" class="setting-textarea__editor">
			<div ref="editorRef" />
		</div>
	</div>
</template>

<script setup lang="ts">
import { ref, toRef } from 'vue'
import { useTinyMce } from '../../composables/useTinyMce'

const props = defineProps<{
	modelValue: string
	disabled: boolean
}>()

const emit = defineEmits<{
	(e: 'save', content: string): void
}>()

const expanded = ref(false)
const editorRef = ref<HTMLElement | null>(null)

useTinyMce({
	elementRef: editorRef,
	value: toRef(props, 'modelValue'),
	disabled: toRef(props, 'disabled'),
	onSave(content: string) {
		emit('save', content)
	},
})
</script>

<style scoped>
.setting-textarea__toggle {
	cursor: pointer;
	display: inline-flex;
	align-items: center;
	gap: 6px;
	padding: 4px 0;
	color: var(--color-primary-element);
	font-size: 14px;
	user-select: none;
}

.setting-textarea__toggle:hover {
	text-decoration: underline;
}

.setting-textarea__arrow {
	font-size: 10px;
}

.setting-textarea__editor {
	margin-top: 8px;
	max-width: 700px;
}
</style>
