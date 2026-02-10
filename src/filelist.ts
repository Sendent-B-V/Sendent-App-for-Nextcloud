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
import { getNavigation, Node as NCNode } from '@nextcloud/files'
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

/**
 * FooterFile: displays a preview of a SECUREMAIL.html file below the files list
 */
class FooterFile {

	// eslint-disable-next-line no-useless-constructor
	constructor(private file: NCNode) {
	}

	public async appendBelowFiles(): Promise<void> {
		// eslint-disable-next-line no-console
		console.log('sendent: Appending a preview of the securemail file at the bottom of the files list')

		// Remove any existing preview
		document.getElementById(CONTENT_ID)?.remove()

		// Create container
		const containerElement = document.createElement('div')
		containerElement.id = CONTENT_ID

		// Insert after the file list table
		const anchor = OCA.Sharing?.PublicApp
			? document.querySelector('.files-filestable')
			: document.querySelector('.files-list__table')
		anchor?.insertAdjacentElement('afterend', containerElement)

		// Show loading spinner
		const loadingElement = document.createElement('span')
		loadingElement.classList.add('icon-loading')
		loadingElement.style.position = 'unset'
		containerElement.appendChild(loadingElement)

		// Fetch and sanitize content
		const filePath = this.getFilePath()
		const response = await axios.get(filePath)
		const content = sanitizeHtml(response.data, SANITIZE_OPTIONS)

		// Replace loading spinner with iframe
		containerElement.innerHTML = ''
		containerElement.appendChild(this.generateIframeElement(content))
	}

	private getFilePath(): string {
		const path = encodeURI(this.file.path)
		const source = encodeURI(this.file.source)
		const name = encodeURIComponent(this.file.basename)

		if (OCA.Sharing?.PublicApp) {
			const tokenInput = document.getElementById('sharingToken') as HTMLInputElement | null
			const token = tokenInput?.value ?? ''

			return generateUrl('/s/{token}/download?path={path}&files={name}', {
				token,
				path,
				name,
			})
		}

		return source
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

/**
 * Searches for a securemail file, and, when found, display its sanitized content below the file list
 */
async function onFileListUpdated() {
	const wait = (ms: number) => new Promise<void>((resolve) => setTimeout(resolve, ms))

	let fileList: Array<{ type: string; basename: string; name?: string; path: string; source: string }>
	if (OCA.Sharing?.PublicApp) {
		await wait(1500)
		fileList = OCA.Sharing.PublicApp.fileList.files.map((f: { name: string; basename?: string }) => {
			f.basename = f.name
			return f
		})
	} else {
		const currentPath = OCP.Files.Router.query.dir ?? '/'
		const view = getNavigation().active
		const content = await view?.getContents(currentPath)
		fileList = content?.contents ?? []
	}

	for (const file of fileList ?? []) {
		if (file.type === 'file' && file.basename === FOOTER_NAME) {
			const footerFile = new FooterFile(file as unknown as NCNode)
			footerFile.appendBelowFiles()
		}
	}
}

/**
 * Initializes the securemail previewer
 */
function initSecureMailPreviewer() {
	// eslint-disable-next-line no-console
	console.log('sendent: initialising securemail previewer')
	subscribe('files:list:updated', () => onFileListUpdated())
	onFileListUpdated()
}

/**
 * Entry point
 */
if (document.readyState === 'complete') {
	initSecureMailPreviewer()
} else {
	document.addEventListener('DOMContentLoaded', initSecureMailPreviewer)
}
