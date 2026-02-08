// TinyMCE 7 — imports MUST be in this exact order
import 'tinymce/tinymce'
import 'tinymce/models/dom'
import 'tinymce/icons/default'
import 'tinymce/themes/silver'

// Skins (bundled via webpack asset rules)
import 'tinymce/skins/ui/oxide/skin.min.css'
import 'tinymce/skins/ui/oxide/content.min.css'
import 'tinymce/skins/content/default/content.min.css'

// Plugins
import 'tinymce/plugins/code'
import 'tinymce/plugins/link'
import 'tinymce/plugins/lists'
import 'tinymce/plugins/autolink'
import 'tinymce/plugins/preview'
import 'tinymce/plugins/table'

import { onMounted, onBeforeUnmount, watch, type Ref } from 'vue'
import { generateUrl } from '@nextcloud/router'
import type { Editor } from 'tinymce'

/** NC theming logo URL used to preview cid:logo.png@logo in the editor */
const CID_LOGO = 'cid:logo.png@logo'
const NC_LOGO_URL = generateUrl('/apps/theming/image/logo')

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

/** Replace cid: logo with NC theming URL for preview */
function toPreview(html: string): string {
	return html.replaceAll(CID_LOGO, NC_LOGO_URL)
}

/** Restore cid: logo reference for saving */
function toStorage(html: string): string {
	return html.replaceAll(NC_LOGO_URL, CID_LOGO)
}

interface TinyMceOptions {
	elementRef: Ref<HTMLElement | null>
	value: Ref<string>
	disabled: Ref<boolean>
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
					ed.setContent(toPreview(options.value.value || ''))
				})

				// Emit changes on blur (not every keystroke)
				ed.on('blur', () => {
					const content = toStorage(ed.getContent())
					if (content !== options.value.value) {
						options.onSave(content)
					}
				})
			},
		})
	})

	// Watch for external value changes (e.g. group switch)
	watch(options.value, (newVal) => {
		const preview = toPreview(newVal || '')
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
