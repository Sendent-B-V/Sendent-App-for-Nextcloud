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
	<div class="signature-preview">
		<div class="signature-preview__header">
			<h4 class="signature-preview__title">
				Preview (rendered with your profile)
			</h4>
			<button class="signature-preview__refresh"
				:disabled="loading"
				@click="refresh">
				{{ loading ? 'Rendering…' : 'Refresh preview' }}
			</button>
		</div>
		<p v-if="error" class="signature-preview__error" role="alert">
			Preview failed to render. Check nextcloud.log and the browser Network tab.
		</p>
		<!-- Sandboxed: rendered signature HTML must never execute scripts in the admin page -->
		<iframe v-else
			class="signature-preview__frame"
			:class="{ 'signature-preview__frame--loading': loading }"
			sandbox=""
			:srcdoc="renderedHtml"
			title="Signature preview" />
	</div>
</template>

<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import { renderSignaturePreview } from '../../services/signatureApi'

const props = defineProps<{ template: string }>()

const renderedHtml = ref('')
const loading = ref(false)
const error = ref(false)

let requestId = 0

/** Render the current template with the caller's profile via the render endpoint. */
async function refresh() {
	const id = ++requestId
	if (!props.template) {
		renderedHtml.value = ''
		error.value = false
		return
	}
	loading.value = true
	error.value = false
	try {
		const html = await renderSignaturePreview(props.template)
		if (id !== requestId) return // superseded by a newer request
		renderedHtml.value = html ?? ''
	} catch {
		if (id !== requestId) return
		error.value = true
	} finally {
		if (id === requestId) loading.value = false
	}
}

onMounted(refresh)

// Re-render when the saved template changes (editor save, group switch, reset)
watch(() => props.template, refresh)
</script>

<style scoped>
.signature-preview {
	margin-top: 16px;
	max-width: 664px;
}

.signature-preview__header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	margin-bottom: 8px;
}

.signature-preview__title {
	margin: 0;
	font-size: 14px;
	font-weight: 600;
}

.signature-preview__refresh {
	padding: 4px 10px;
	font-size: 13px;
	color: var(--color-primary-element);
	background: none;
	border: 1px solid var(--color-primary-element);
	border-radius: var(--border-radius);
	cursor: pointer;
}

.signature-preview__refresh:hover:not(:disabled) {
	background: var(--color-primary-element);
	color: var(--color-primary-element-text, #fff);
}

.signature-preview__refresh:disabled {
	opacity: 0.5;
	cursor: not-allowed;
}

.signature-preview__error {
	color: var(--color-error, #c9302c);
	font-size: 13px;
}

.signature-preview__frame {
	width: 100%;
	height: 320px;
	min-height: 220px;
	resize: vertical;
	/* Deliberately fixed white — email clients render on white regardless of Nextcloud theme */
	background: #ffffff;
	border: 1px solid var(--color-border, #ccc);
	border-radius: var(--border-radius);
}

.signature-preview__frame--loading {
	opacity: 0.4;
	transition: opacity 0.15s;
}
</style>
