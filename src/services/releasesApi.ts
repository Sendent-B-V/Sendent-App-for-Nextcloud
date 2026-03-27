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
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import type { ReleaseEntry } from '../types/releases'

const baseUrl = '/apps/sendent/api/1.0'

/**
 * Fetches the latest release entries for all products.
 *
 * @return Map of product slug to release entry
 */
export async function fetchLatestReleases(): Promise<Record<string, ReleaseEntry>> {
	const url = generateUrl(`${baseUrl}/releases`)
	const response = await axios.get(url)
	return response.data
}
