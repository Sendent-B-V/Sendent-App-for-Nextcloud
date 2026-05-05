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
		<div class="setting-textarea__actions">
			<button class="setting-textarea__open"
				:disabled="disabled"
				@click="showModal = true">
				Edit template
			</button>
			<button class="setting-textarea__reset"
				:disabled="disabled"
				@click="$emit('reset')">
				Reset to default
			</button>
		</div>
		<NcDialog :open="showModal"
			name="Edit template"
			size="large"
			:buttons="dialogButtons"
			:additional-trap-elements="tinyMceAuxElements"
			@update:open="showModal = $event">
			<div ref="editorRef" />
		</NcDialog>
	</div>
</template>

<script setup lang="ts">
import { ref, toRef } from 'vue'
import { storeToRefs } from 'pinia'
import NcDialog from '@nextcloud/vue/components/NcDialog'
import { useTinyMce } from '../../composables/useTinyMce'
import { useDependenciesStore } from '../../stores/dependencies'

const props = defineProps<{
	modelValue: string
	disabled: boolean
}>()

const { themingLogoUrl } = storeToRefs(useDependenciesStore())

const emit = defineEmits<{
	(e: 'save', content: string): void
	(e: 'reset'): void
}>()

const showModal = ref(false)
const editorRef = ref<HTMLElement | null>(null)

const { getContent } = useTinyMce({
	elementRef: editorRef,
	value: toRef(props, 'modelValue'),
	disabled: toRef(props, 'disabled'),
	logoUrl: themingLogoUrl,
})

// TinyMCE renders dialogs (e.g. source code editor) inside .tox-tinymce-aux
// which lives outside the NcDialog DOM tree. We must add it to the focus trap
// so the textarea inside the source code dialog is interactive.
const tinyMceAuxElements = ['.tox-tinymce-aux']

const dialogButtons = [
	{
		label: 'Close',
		callback: () => { showModal.value = false },
	},
	{
		label: 'Save',
		variant: 'primary' as const,
		callback: () => {
			const content = getContent()
			emit('save', content)
			showModal.value = false
		},
	},
]
</script>

<style scoped>
.setting-textarea__actions {
	display: flex;
	align-items: center;
	gap: 16px;
}

.setting-textarea__open {
	padding: 4px 10px;
	font-size: 13px;
	color: var(--color-primary-element);
	background: none;
	border: 1px solid var(--color-primary-element);
	border-radius: var(--border-radius);
	cursor: pointer;
}

.setting-textarea__open:hover:not(:disabled) {
	background: var(--color-primary-element);
	color: white;
}

.setting-textarea__open:disabled,
.setting-textarea__reset:disabled {
	opacity: 0.5;
	cursor: not-allowed;
}

.setting-textarea__reset {
	padding: 4px 10px;
	font-size: 13px;
	color: #c9302c;
	background: none;
	border: 1px solid #c9302c;
	border-radius: var(--border-radius);
	cursor: pointer;
	font-weight: 600;
}

.setting-textarea__reset:hover:not(:disabled) {
	background: #c9302c;
	color: white;
}
</style>

<style>
/* TinyMCE toolbar dropdowns must render above the NcDialog overlay */
.tox-tinymce-aux {
	z-index: 100000 !important;
}
</style>
