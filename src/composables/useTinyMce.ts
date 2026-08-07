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

/**
 * TinyMCE's internal helper rules, copied verbatim from
 * tinymce/skins/ui/oxide/content.css. With `skin: false` + `content_css: false`
 * NO stylesheet reaches the editable iframe, so TinyMCE's own in-body helper
 * elements (fake-caret containers, resize handles, offscreen selection clones,
 * table cell selection overlays) render in normal document flow — shifting the
 * layout mid-click and breaking caret placement around table-based templates.
 * These rules restore the positioning TinyMCE's editing logic assumes.
 */
const CONTENT_HELPER_CSS = [
	'.mce-content-body .mce-visual-caret { background-color: black; background-color: currentColor; position: absolute; }',
	'.mce-content-body .mce-visual-caret-hidden { display: none; }',
	'.mce-content-body *[data-mce-caret] { left: -1000px; margin: 0; padding: 0; position: absolute; right: auto; top: 0; }',
	'.mce-content-body .mce-offscreen-selection { left: -2000000px; max-width: 1000000px; position: absolute; }',
	'.mce-content-body div.mce-resizehandle { background-color: #4099ff; border-color: #4099ff; border-style: solid; border-width: 1px; box-sizing: border-box; height: 10px; position: absolute; width: 10px; z-index: 1298; }',
	'.mce-content-body td[data-mce-selected], .mce-content-body th[data-mce-selected] { position: relative; }',
	".mce-content-body td[data-mce-selected]::after, .mce-content-body th[data-mce-selected]::after { background-color: rgba(180, 215, 255, 0.7); border: 1px solid rgba(180, 215, 255, 0.7); bottom: -1px; content: ''; left: -1px; mix-blend-mode: multiply; position: absolute; right: -1px; top: -1px; }",
	// Snooker table-resize bars: invisible strips laid across every row/column
	// boundary. `user-select: none` is load-bearing — without it the browser
	// places the CARET inside these bogus divs on near-boundary clicks, making
	// the caret "jump" (confirmed via selectionchange logging on Chromium).
	'.ephox-snooker-resizer-bar { background-color: #b4d7ff; opacity: 0; -webkit-user-select: none; user-select: none; }',
	'.ephox-snooker-resizer-cols { cursor: col-resize; }',
	'.ephox-snooker-resizer-rows { cursor: row-resize; }',
	'.ephox-snooker-resizer-bar.ephox-snooker-resizer-bar-dragging { opacity: 1; }',
].join(' ')

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
	/** Placeholder tags for the "Insert variable" menu; defaults to TEMPLATE_VARIABLES. */
	variables?: string[]
	/** Strip elements/styles that break email clients (classic Outlook = Word engine). */
	signatureMode?: boolean
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
		const variables = options.variables ?? TEMPLATE_VARIABLES

		window.tinymce.init({
			target: el,
			license_key: 'gpl',
			skin: false,
			content_css: false,
			content_style: CONTENT_HELPER_CSS
				+ ` img[src="${LOGO_PLACEHOLDER}"] { content: url(${options.logoUrl.value}); }`
				+ (options.signatureMode ? ' body { font-family: Arial, sans-serif; font-size: 13px; }' : ''),
			height: 400,
			menubar: false,
			branding: false,
			promotion: false,
			plugins: 'code link lists autolink preview table',
			toolbar: [
				'undo redo | fontfamily fontsize | bold italic underline strikethrough | forecolor backcolor',
				'alignleft aligncenter alignright | bullist numlist | link table | templatevars | code preview',
			],
			ui_mode: 'split',
			link_context_toolbar: true,
			link_default_target: '_blank',
			readonly: options.disabled.value,

			// Email-signature safety: classic Outlook renders with the Word engine.
			// Remove/unwrap elements it cannot render and strip styles it ignores or mangles.
			...(options.signatureMode
				? {
					invalid_elements: 'script,iframe,form,input,button,select,option,optgroup,textarea,fieldset,object,embed,svg,video,audio',
					invalid_styles: {
						'*': 'float position display max-width max-height overflow overflow-x overflow-y z-index background background-image',
					},
				}
				: {}),

			setup(ed: Editor) {
				editor = ed

				ed.ui.registry.addMenuButton('templatevars', {
					text: 'Insert variable',
					fetch(callback) {
						const items = variables.map(tag => ({
							type: 'menuitem' as const,
							text: tag,
							onAction() {
								if (options.signatureMode && tag === LOGO_PLACEHOLDER) {
									// {LOGO} only works as an image source; inserting it as
									// text would render a bare URL in the signature.
									ed.insertContent(`<img src="${LOGO_PLACEHOLDER}" alt="" style="border:0;">`)
								} else {
									ed.insertContent(tag)
								}
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
