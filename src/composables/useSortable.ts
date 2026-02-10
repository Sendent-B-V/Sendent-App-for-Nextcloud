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
import { onMounted, onBeforeUnmount, ref, type Ref } from 'vue'
import Sortable from 'sortablejs'

interface SortableOptions {
	onEnd?: (oldIndex: number, newIndex: number) => void
	group?: string | Sortable.GroupOptions
	handle?: string
	animation?: number
}

/**
 * Composable wrapping SortableJS for Vue 3 lifecycle management.
 * @param elementRef
 * @param options
 */
export function useSortable(
	elementRef: Ref<HTMLElement | null>,
	options: SortableOptions = {},
) {
	const instance = ref<Sortable | null>(null)

	onMounted(() => {
		if (!elementRef.value) return
		instance.value = Sortable.create(elementRef.value, {
			animation: options.animation ?? 150,
			group: options.group,
			handle: options.handle,
			onEnd(evt) {
				if (evt.oldIndex !== undefined && evt.newIndex !== undefined) {
					options.onEnd?.(evt.oldIndex, evt.newIndex)
				}
			},
		})
	})

	onBeforeUnmount(() => {
		instance.value?.destroy()
		instance.value = null
	})

	return { instance }
}
