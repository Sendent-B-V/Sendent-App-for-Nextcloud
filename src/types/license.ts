export interface VSTOAddinVersion {
	ApplicationName: string
	ApplicationId: string
	Version: string
	UrlBinary: string
	UrlManual: string
	UrlReleaseNotes: string
	ReleaseDate: string
}

export interface LicenseStatus {
	status: string
	statusKind: string
	dateExpiration: string
	email: string
	level: string
	licensekey: string
	dateLastCheck: string
	LatestVSTOAddinVersion: VSTOAddinVersion | null
	ncgroup: string
	product: string
	istrial: number
}

export interface DefaultLicenseState {
	status: string
	level: string
	expirationDate: string
	lastCheck: string
}

export interface AppVersionStatus {
	LatestVSTOAddinVersion: VSTOAddinVersion | null
}
