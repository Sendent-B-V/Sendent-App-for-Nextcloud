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
import './filelist.scss'
import { generateUrl } from '@nextcloud/router'
import { getNavigation } from '@nextcloud/files'
import { subscribe } from '@nextcloud/event-bus'
import axios from '@nextcloud/axios'
import sanitizeHtml from 'sanitize-html'

const FOOTER_NAME = '.SECUREMAIL.html'
const CONTENT_ID = 'sendent-content'

const SANITIZE_OPTIONS = {
	allowedTags: sanitizeHtml.defaults.allowedTags.concat(['img', 'html', 'head', 'body', 'meta', 'style']),
	allowedAttributes: {
		...sanitizeHtml.defaults.allowedAttributes,
		'*': ['class', 'style', 'colspan', 'width', 'border', 'valign', 'cellspacing', 'cellpadding', 'align', 'height', 'rowspan', 'rowspacing', 'rowpadding'],
		img: ['src', 'width', 'height', 'style', 'id'],
		html: ['xmlns*'],
		meta: ['name', 'content'],
	},
	allowVulnerableTags: true,
	allowedSchemes: ['data'],
	allowedSchemesByTag: {
		a: ['http', 'https', 'mailto', 'tel'],
	},
}

/* eslint-disable @typescript-eslint/no-explicit-any */

/**
 * Displays a preview of a .SECUREMAIL.html file below the files list.
 */
class FooterFile {

	// eslint-disable-next-line no-useless-constructor
	constructor(
		private basename: string,
		private dirPath: string,
		private source: string,
	) {}

	public async appendBelowFiles(version: number): Promise<void> {
		// Skip if a newer render has already been requested
		if (version !== renderVersion) return

		// eslint-disable-next-line no-console
		console.log('sendent: Appending a preview of the securemail file at the bottom of the files list')

		// Remove any existing preview
		document.getElementById(CONTENT_ID)?.remove()

		// Create container
		const container = document.createElement('div')
		container.id = CONTENT_ID

		// Insert after the file list table (try multiple selectors for NC 28-33)
		const anchor = document.querySelector('.files-list__table')
			|| document.querySelector('.files-filestable')
			|| document.querySelector('#filestable')

		if (!anchor) return

		anchor.insertAdjacentElement('afterend', container)

		// Show loading spinner
		const spinner = document.createElement('span')
		spinner.classList.add('icon-loading')
		spinner.style.position = 'unset'
		container.appendChild(spinner)

		try {
			// Fetch and sanitize content
			const response = await axios.get(this.getFilePath())

			// Bail out if a newer render superseded us during the fetch
			if (version !== renderVersion) return

			const content = sanitizeHtml(response.data, SANITIZE_OPTIONS)

			// Replace loading spinner with iframe
			container.innerHTML = ''
			container.appendChild(this.generateIframeElement(content))
		} catch (err) {
			// eslint-disable-next-line no-console
			console.error('sendent: Failed to load securemail content', err)
			container.remove()
		}
	}

	private getFilePath(): string {
		// Prefer WebDAV source URL (works for both regular and NC 28+ public shares)
		if (this.source) {
			return this.source
		}

		// Fallback: build download URL from sharing token (legacy public shares)
		const tokenInput = document.getElementById('sharingToken') as HTMLInputElement | null
		if (tokenInput?.value) {
			return generateUrl('/s/{token}/download?path={path}&files={name}', {
				token: tokenInput.value,
				path: encodeURI(this.dirPath),
				name: encodeURIComponent(this.basename),
			})
		}

		return ''
	}

	private generateIframeElement(content: string): HTMLIFrameElement {
		const iframe = document.createElement('iframe')
		iframe.width = '0'
		iframe.height = '0'
		iframe.addEventListener('load', () => {
			const innerHeight = iframe.contentDocument?.documentElement?.scrollHeight
			const innerWidth = iframe.contentDocument?.documentElement?.scrollWidth
			if (innerHeight) iframe.height = String(innerHeight)
			if (innerWidth) iframe.width = String(innerWidth)
		})
		iframe.srcdoc = content
		return iframe
	}

}

let debounceTimer: ReturnType<typeof setTimeout> | null = null
let renderVersion = 0

/**
 * Debounced wrapper — collapses rapid successive calls into one.
 * @param files
 */
function processFileListDebounced(files: any[]) {
	if (debounceTimer) clearTimeout(debounceTimer)
	debounceTimer = setTimeout(() => processFileList(files), 50)
}

/**
 * Scans a file list for a .SECUREMAIL.html file and renders its preview.
 * Removes any stale preview if no securemail file is found.
 * @param files
 */
function processFileList(files: any[]) {
	const version = ++renderVersion
	for (const file of files ?? []) {
		const basename = file.basename || file.name
		if (file.type === 'file' && basename === FOOTER_NAME) {
			// Extract directory from full path (Node.dirname or manual extraction)
			const dirPath = file.dirname
				?? (file.path ? file.path.substring(0, file.path.lastIndexOf('/')) || '/' : '/')
			new FooterFile(basename, dirPath, file.source ?? '').appendBelowFiles(version)
			return
		}
	}
	// No securemail file in this directory — clean up stale preview
	document.getElementById(CONTENT_ID)?.remove()
}

/**
 * Gets the current directory path, with multiple fallback strategies for NC 28-33.
 */
function getCurrentDir(): string {
	try {
		if (typeof OCP !== 'undefined' && OCP?.Files?.Router?.query?.dir) {
			return OCP.Files.Router.query.dir
		}
	} catch { /* ignore */ }
	return new URL(window.location.href).searchParams.get('dir') || '/'
}

/**
 * Loads the file list on first render (in case we missed the files:list:updated event).
 */
async function loadInitialFiles() {
	try {
		// Public shares: rely solely on files:list:updated event — the Navigation API
		// uses the wrong WebDAV root (logged-in user instead of share token)
		if (document.getElementById('sharingToken')) {
			return
		}

		// Regular files view: use Navigation API
		const view = getNavigation().active
		if (!view) return
		const result = await view.getContents(getCurrentDir())
		processFileList(result?.contents ?? [])
	} catch (err) {
		// eslint-disable-next-line no-console
		console.warn('sendent: Could not load initial file list', err)
	}
}

/**
 * Initializes the securemail previewer.
 */
function initSecureMailPreviewer() {
	// eslint-disable-next-line no-console
	console.log('sendent: initialising securemail previewer')

	// NC 28+: subscribe to file list update events (payload contains { contents: Node[] })
	subscribe('files:list:updated', (event: any) => {
		if (event?.contents) {
			processFileListDebounced(event.contents)
		}
	})

	// Try loading initial file list (fallback if we missed the first event)
	loadInitialFiles()
}

/**
 * Entry point
 */
if (document.readyState === 'complete' || document.readyState === 'interactive') {
	initSecureMailPreviewer()
} else {
	document.addEventListener('DOMContentLoaded', initSecureMailPreviewer)
}
