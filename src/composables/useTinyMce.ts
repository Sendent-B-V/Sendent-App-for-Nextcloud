/**
 * @copyright Copyright (c) 2026 Sendent B.V.
 *
 * @author Sendent B.V. <info@sendent.com>
 *
 * @license AGPL-3.0-or-later
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
// TinyMCE 7 — imports MUST be in this exact order
import 'tinymce/tinymce'
import 'tinymce/models/dom'
import 'tinymce/icons/default'
import 'tinymce/themes/silver'

// UI skin (bundled via webpack) — only the UI chrome, not content CSS
// content.min.css files are for TinyMCE's iframe and must NOT be imported
// globally as they contain bare `body` and `table` selectors that leak
import 'tinymce/skins/ui/oxide/skin.min.css'

// Plugins
import 'tinymce/plugins/code'
import 'tinymce/plugins/link'
import 'tinymce/plugins/lists'
import 'tinymce/plugins/autolink'
import 'tinymce/plugins/preview'
import 'tinymce/plugins/table'

import { onMounted, onBeforeUnmount, watch, type Ref } from 'vue'
import type { Editor } from 'tinymce'

/** Placeholder used in email templates for the organisation logo */
const LOGO_PLACEHOLDER = '{LOGO}'

const TEMPLATE_VARIABLES = [
	'{URL}',
	'{PASSWORD}',
	'{EXPIRATIONDATE}',
	'{RECIPIENTS}',
	'{FROM}',
	'{SUBJECT}',
	'{SHAREID}',
	'{FILES}',
	'{CURRENTDATE}',
	'{CURRENTTIME}',
	'{EXTRA}',
	'{LOGO}',
]

/**
 * Replace {LOGO} placeholder with the theming logo URL for WYSIWYG preview
 */
function toPreview(html: string, logoUrl: string): string {
	return html.replaceAll(LOGO_PLACEHOLDER, logoUrl)
}

/**
 * Restore {LOGO} placeholder before persisting
 */
function toStorage(html: string, logoUrl: string): string {
	return html.replaceAll(logoUrl, LOGO_PLACEHOLDER)
}

interface TinyMceOptions {
	elementRef: Ref<HTMLElement | null>
	value: Ref<string>
	disabled: Ref<boolean>
	logoUrl: Ref<string>
	onSave: (content: string) => void
}

/**
 * Composable wrapping TinyMCE 7 for Vue 3 lifecycle management.
 * Handles initialization, content syncing, readonly toggling, and cleanup.
 * @param options
 */
export function useTinyMce(options: TinyMceOptions) {
	let editor: Editor | null = null

	onMounted(() => {
		if (!options.elementRef.value) return

		window.tinymce.init({
			target: options.elementRef.value,
			skin: false, // skins loaded via CSS imports above
			content_css: false,
			height: 400,
			menubar: false,
			branding: false,
			promotion: false,
			plugins: 'code link lists autolink preview table',
			toolbar: [
				'undo redo | fontfamily fontsize | bold italic underline strikethrough | forecolor backcolor',
				'alignleft aligncenter alignright | bullist numlist | link table | templatevars | code preview',
			],
			readonly: options.disabled.value,

			setup(ed: Editor) {
				editor = ed

				// Register template variables dropdown button
				ed.ui.registry.addMenuButton('templatevars', {
					text: 'Insert variable',
					fetch(callback) {
						const items = TEMPLATE_VARIABLES.map(tag => ({
							type: 'menuitem' as const,
							text: tag,
							onAction() {
								ed.insertContent(tag)
							},
						}))
						callback(items)
					},
				})

				// Sync initial content once editor is ready
				ed.on('init', () => {
					ed.setContent(toPreview(options.value.value || '', options.logoUrl.value))
				})

				// Emit changes on blur (not every keystroke)
				ed.on('blur', () => {
					const content = toStorage(ed.getContent(), options.logoUrl.value)
					if (content !== options.value.value) {
						options.onSave(content)
					}
				})
			},
		})
	})

	// Watch for external value changes (e.g. group switch)
	watch(options.value, (newVal) => {
		const preview = toPreview(newVal || '', options.logoUrl.value)
		if (editor && editor.getContent() !== preview) {
			editor.setContent(preview)
		}
	})

	// Watch for disabled state changes
	watch(options.disabled, (newDisabled) => {
		if (editor) {
			editor.mode.set(newDisabled ? 'readonly' : 'design')
		}
	})

	onBeforeUnmount(() => {
		if (editor) {
			editor.destroy()
			editor = null
		}
	})

	return { getEditor: () => editor }
}
