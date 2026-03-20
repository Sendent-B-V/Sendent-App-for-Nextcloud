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
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'

function parseHash(): Map<string, string> {
	const hash = window.location.hash.replace(/^#/, '')
	const params = new Map<string, string>()
	if (!hash) return params
	for (const part of hash.split('&')) {
		const [key, value] = part.split('=')
		if (key) params.set(decodeURIComponent(key), decodeURIComponent(value ?? ''))
	}
	return params
}

function writeHash(params: Map<string, string>) {
	const parts: string[] = []
	for (const [key, value] of params) {
		if (value) {
			parts.push(`${encodeURIComponent(key)}=${encodeURIComponent(value)}`)
		}
	}
	const newHash = parts.length ? `#${parts.join('&')}` : ''
	if (window.location.hash !== newHash) {
		history.replaceState(null, '', newHash || window.location.pathname + window.location.search)
	}
}

/**
 * Returns a ref synced with a URL hash parameter.
 * When the ref changes, the hash updates. When the hash changes
 * externally (e.g. back button), the ref updates.
 * @param key - hash parameter name
 * @param defaultValue - fallback when parameter is absent
 */
export function useHashParam(key: string, defaultValue: string = '') {
	const params = parseHash()
	const value = ref(params.get(key) ?? defaultValue)

	watch(value, (newVal) => {
		const current = parseHash()
		if (newVal && newVal !== defaultValue) {
			current.set(key, newVal)
		} else {
			current.delete(key)
		}
		writeHash(current)
	})

	function onHashChange() {
		const params = parseHash()
		const hashVal = params.get(key) ?? defaultValue
		if (value.value !== hashVal) {
			value.value = hashVal
		}
	}

	onMounted(() => window.addEventListener('hashchange', onHashChange))
	onBeforeUnmount(() => window.removeEventListener('hashchange', onHashChange))

	return value
}