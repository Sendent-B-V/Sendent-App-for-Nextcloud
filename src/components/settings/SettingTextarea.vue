<!--
  - @copyright Copyright (c) 2026 Sendent B.V.
  -
  - @author Sendent B.V. <info@sendent.com>
  -
  - @license AGPL-3.0-or-later
  -
  - This program is free software: you can redistribute it and/or modify
  - it under the terms of the GNU Affero General Public License as
  - published by the Free Software Foundation, either version 3 of the
  - License, or (at your option) any later version.
  -
  - This program is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  - GNU Affero General Public License for more details.
  -
  - You should have received a copy of the GNU Affero General Public License
  - along with this program. If not, see <http://www.gnu.org/licenses/>.
  -->
<template>
	<div class="setting-textarea">
		<div class="setting-textarea__toggle"
			@click="expanded = !expanded">
			<span class="setting-textarea__arrow">{{ expanded ? '▾' : '▸' }}</span>
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
	font-size: 12px;
	font-variant-emoji: text;
}

.setting-textarea__editor {
	margin-top: 8px;
	max-width: 700px;
}
</style>
