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
			<button ref="triggerRef"
				class="setting-textarea__open"
				:disabled="disabled"
				@click="openModal">
				Edit template
			</button>
			<button class="setting-textarea__reset"
				:disabled="disabled"
				@click="$emit('reset')">
				Reset to default
			</button>
		</div>
		<Teleport to="body">
			<div v-if="showModal"
				class="sendent-editor-modal"
				role="dialog"
				aria-modal="true"
				:aria-labelledby="titleId">
				<div class="sendent-editor-modal__backdrop" @click="closeModal" />
				<div class="sendent-editor-modal__dialog">
					<header class="sendent-editor-modal__header">
						<h2 :id="titleId" class="sendent-editor-modal__title">
							Edit template
						</h2>
						<button type="button"
							class="sendent-editor-modal__close"
							aria-label="Close"
							@click="closeModal">
							×
						</button>
					</header>
					<div class="sendent-editor-modal__body">
						<div ref="editorRef" />
					</div>
					<footer class="sendent-editor-modal__footer">
						<button type="button"
							class="sendent-editor-modal__btn"
							@click="closeModal">
							Close
						</button>
						<button type="button"
							class="sendent-editor-modal__btn sendent-editor-modal__btn--primary"
							@click="save">
							Save
						</button>
					</footer>
				</div>
			</div>
		</Teleport>
	</div>
</template>

<script setup lang="ts">
import { nextTick, ref, toRef, watch } from 'vue'
import { storeToRefs } from 'pinia'
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
const triggerRef = ref<HTMLButtonElement | null>(null)
const titleId = `sendent-editor-title-${crypto.randomUUID().slice(0, 8)}`

let previouslyFocused: HTMLElement | null = null

const { getContent } = useTinyMce({
	elementRef: editorRef,
	value: toRef(props, 'modelValue'),
	disabled: toRef(props, 'disabled'),
	logoUrl: themingLogoUrl,
})

function openModal() {
	previouslyFocused = document.activeElement instanceof HTMLElement
		? document.activeElement
		: triggerRef.value
	showModal.value = true
}

function closeModal() {
	showModal.value = false
	nextTick(() => {
		previouslyFocused?.focus()
		previouslyFocused = null
	})
}

function save() {
	const content = getContent()
	emit('save', content)
	closeModal()
}

function onEscape(event: KeyboardEvent) {
	// Only close the outer modal on Esc when no inner TinyMCE dialog is open.
	// TinyMCE handles Esc inside its own dialogs and stops propagation, so by
	// the time the event reaches window, no inner dialog is consuming it.
	if (event.key === 'Escape') {
		closeModal()
	}
}

watch(showModal, (isOpen) => {
	if (isOpen) {
		window.addEventListener('keydown', onEscape)
	} else {
		window.removeEventListener('keydown', onEscape)
	}
})
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

/* Custom modal — intentionally non-trapping so TinyMCE's body-level
 * secondary UI (Source code, link, table, color pickers) remains
 * interactive. Replaces NcDialog whose focus trap conflicts with
 * TinyMCE's active-state detection. */
.sendent-editor-modal {
	position: fixed;
	inset: 0;
	z-index: 10010;
	display: flex;
	align-items: flex-start;
	justify-content: center;
	padding: 4vh 24px;
}

.sendent-editor-modal__backdrop {
	position: absolute;
	inset: 0;
	background: rgba(0, 0, 0, 0.55);
}

.sendent-editor-modal__dialog {
	position: relative;
	width: min(1200px, calc(100vw - 48px));
	max-height: calc(100vh - 8vh);
	display: flex;
	flex-direction: column;
	background: var(--color-main-background, #fff);
	color: var(--color-main-text, inherit);
	border: 1px solid var(--color-border, #ccc);
	border-radius: 12px;
	box-shadow: 0 20px 60px rgba(0, 0, 0, 0.45);
	overflow: hidden;
}

.sendent-editor-modal__header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 12px 16px;
	border-bottom: 1px solid var(--color-border, #e0e0e0);
}

.sendent-editor-modal__title {
	margin: 0;
	font-size: 16px;
	font-weight: 600;
}

.sendent-editor-modal__close {
	background: none;
	border: none;
	font-size: 24px;
	line-height: 1;
	cursor: pointer;
	padding: 4px 8px;
	color: var(--color-main-text, inherit);
}

.sendent-editor-modal__body {
	flex: 1 1 auto;
	min-height: 0;
	overflow: auto;
	padding: 16px;
}

.sendent-editor-modal__footer {
	display: flex;
	justify-content: flex-end;
	gap: 8px;
	padding: 12px 16px;
	border-top: 1px solid var(--color-border, #e0e0e0);
}

.sendent-editor-modal__btn {
	padding: 6px 14px;
	font-size: 13px;
	background: none;
	border: 1px solid var(--color-border, #ccc);
	border-radius: var(--border-radius);
	cursor: pointer;
	color: var(--color-main-text, inherit);
}

.sendent-editor-modal__btn--primary {
	background: var(--color-primary-element);
	color: white;
	border-color: var(--color-primary-element);
}
</style>

<style>
/* TinyMCE 7 mounts its secondary UI (.tox-tinymce-aux) at body level.
 * The modal sits at z-index 10010; we boost TinyMCE's aux above it so
 * dialogs (Source code, link picker, table prompt) and dropdowns
 * (color pickers, font menus) always paint over the modal.
 * Matches the pattern used by nc-connector/Server_Backend. */
.tox-tinymce-aux,
.tox-silver-sink {
	z-index: 10030 !important;
}

.tox-tinymce-aux .tox-dialog,
.tox-tinymce-aux .tox-menu {
	z-index: 10031 !important;
}
</style>
