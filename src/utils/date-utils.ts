/**
 * Formats a date string to Dutch (nl-NL) locale format (dd-mm-yyyy).
 * Returns the original string if parsing fails.
 *
 * @param dateStr - ISO date string or any string parseable by Date constructor
 * @return Formatted date string in nl-NL locale, or the original string on failure
 */
export function formatDate(dateStr: string): string {
	try {
		return new Date(dateStr).toLocaleDateString('nl-NL')
	} catch {
		return dateStr
	}
}
