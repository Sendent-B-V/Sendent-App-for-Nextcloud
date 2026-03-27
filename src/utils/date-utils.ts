/**
 * Formats a date string to dd-MM-yyyy format (e.g. 27-03-2026).
 * Returns the original string if parsing fails.
 *
 * @param dateStr - ISO date string or any string parseable by Date constructor
 * @return Formatted date string in dd-MM-yyyy format, or the original string on failure
 */
export function formatDate(dateStr: string): string {
	try {
		const d = new Date(dateStr)
		if (isNaN(d.getTime())) return dateStr
		const dd = String(d.getDate()).padStart(2, '0')
		const mm = String(d.getMonth() + 1).padStart(2, '0')
		const yyyy = d.getFullYear()
		return `${dd}-${mm}-${yyyy}`
	} catch {
		return dateStr
	}
}
