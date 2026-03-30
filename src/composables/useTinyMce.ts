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

import { onBeforeUnmount, watch, type Ref } from 'vue'
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

interface TinyMceOptions {
	elementRef: Ref<HTMLElement | null>
	value: Ref<string>
	disabled: Ref<boolean>
	logoUrl: Ref<string>
}

/**
 * Composable wrapping TinyMCE 7 for Vue 3 lifecycle management.
 * Init/destroy is driven by elementRef presence (for modal support).
 * Saving is explicit via the returned getContent() function.
 * @param options
 */
export function useTinyMce(options: TinyMceOptions) {
	let editor: Editor | null = null

	function initEditor(el: HTMLElement) {
		window.tinymce.init({
			target: el,
			license_key: 'gpl',
			skin: false,
			content_css: false,
			content_style: `img[src="${LOGO_PLACEHOLDER}"] { content: url(${options.logoUrl.value}); }`,
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

				ed.on('init', () => {
					ed.setContent(options.value.value || '')
				})
			},
		})
	}

	function destroyEditor() {
		if (editor) {
			editor.destroy()
			editor = null
		}
	}

	// Init when modal opens (elementRef becomes non-null), destroy when it closes
	watch(options.elementRef, (el) => {
		if (el) {
			initEditor(el)
		} else {
			destroyEditor()
		}
	}, { flush: 'post' })

	// Watch for external value changes (e.g. group switch)
	watch(options.value, (newVal) => {
		if (editor && editor.getContent() !== (newVal || '')) {
			editor.setContent(newVal || '')
		}
	})

	// Watch for disabled state changes
	watch(options.disabled, (newDisabled) => {
		if (editor) {
			editor.mode.set(newDisabled ? 'readonly' : 'design')
		}
	})

	onBeforeUnmount(() => {
		destroyEditor()
	})

	return {
		getEditor: () => editor,
		getContent: () => editor?.getContent() ?? '',
	}
}
