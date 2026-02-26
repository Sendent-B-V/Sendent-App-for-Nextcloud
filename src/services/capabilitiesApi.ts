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

interface OCSCapabilities {
	ocs: {
		data: {
			capabilities: Record<string, unknown>
		}
	}
}

/**
 *
 */
export async function fetchCapabilities(): Promise<Record<string, unknown>> {
	const response = await axios.get<OCSCapabilities>('/ocs/v1.php/cloud/capabilities', {
		headers: { 'OCS-APIRequest': 'true' },
	})
	return response.data.ocs.data.capabilities
}
