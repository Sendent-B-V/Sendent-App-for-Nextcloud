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

const baseUrl = '/apps/sendent/api/1.0'

export interface TermsAgreement {
	Version: string
	Agreed: string
}

/**
 * Checks whether the current user has agreed to a specific version of the terms.
 *
 * @param version - Terms version string to check agreement for
 * @return Agreement status including version and whether it was agreed to
 */
export async function checkAgreement(version: string): Promise<TermsAgreement> {
	const url = generateUrl(`${baseUrl}/termsagreement/isagreed/{version}`, { version })
	const response = await axios.get(url)
	return response.data
}

/**
 * Records that the current user has agreed to a specific version of the terms.
 *
 * @param version - Terms version string to mark as agreed
 * @return The resulting agreement record
 */
export async function markAgreed(version: string): Promise<TermsAgreement> {
	const url = generateUrl(`${baseUrl}/termsagreement/agree/{version}`, { version })
	const response = await axios.post(url)
	return response.data
}
