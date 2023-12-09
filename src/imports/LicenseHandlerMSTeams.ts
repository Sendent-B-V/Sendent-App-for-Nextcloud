/* eslint-disable @nextcloud/no-deprecations */
import axios from '@nextcloud/axios';
import { generateUrl } from '@nextcloud/router';
import { translate as t } from '@nextcloud/l10n'

type LicenseStatus = {
    status: string
    statusKind: string,
    dateExpiration: string,
    email: string,
    level: string,
    licensekey: string,
    dateLastCheck: string,
    LatestVSTOAddinVersion : AppVersionStatus,
	ncgroup: string,
    type: string
}
type AppVersionStatus = {
    ApplicationName : string
    ApplicationId : string
    Version : string
    UrlBinary: string
    UrlManual: string,
    UrlReleaseNotes : string,
    ReleaseDate: string
}
export default class LicenseHandler {
    private static instance: LicenseHandler;

    private constructor() {
        $("#msteams_btnLicenseActivation").on('click', (ev) => {
            ev.preventDefault();

            const email = $("#msteams_licenseEmail").val()?.toString().replace(/\s+/g, '') || '';
            const key = $("#msteams_licensekey").val()?.toString().replace(/\s+/g, '') || '';

            this.createLicense(email, key, '', 'msteams');
        });

        $("#msteams_btnClearLicense").on('click', (ev) => {
            $("#msteams_licenseEmail").val('');
            $("#msteams_licensekey").val('');
            this.createLicense('', '', '');
        });

        $("#msteams_licenseEmail, #msteams_licensekey").on('change', () => {
            $("#msteams_btnLicenseActivation").val(t("sendent", "Activate license"));
            $("#msteams_btnLicenseActivation").removeClass("hidden").addClass("shown");
        });

        $('#msteams_btnSupportButton').on('click', () => {
            (window as any).location = 'mailto:support@sendent.nl';
        });
    }

    public static setup(type: string = 'msteams'): LicenseHandler {
        if (!this.instance) {
            this.instance = new LicenseHandler();

            this.instance.refreshLicenseStatus('', type);
        }

        return this.instance;
    }

    public async createLicense(email: string, key: string, gid: string, type: string = 'msteams'): Promise<void> {
        this.disableButtons();

        try {
            await this.sendCreationRequest(email, key, gid, type);
        } catch (err) {
            console.log('Could not create license', err);
        }

        this.enableButtons();

        return this.refreshLicenseStatus(gid, type);
    }

    public async refreshLicenseStatus(gid: string, type: string = 'msteams'): Promise<void> {
        this.insertLoadIndicator('#msteams_licensestatus, #msteams_latestVSTOVersion, #msteams_licenselastcheck, #msteams_licenseexpires, #msteams_licenselevel');
        this.disableButtons();

		console.log('Refreshing license status', gid);
        try {
            const { data: status } = await this.requestStatus(gid, type);
            const { data: appStatus } = await this.requestApplicationStatus();
            
            let LatestVSTOAddinVersionReleaseDate = new Date(appStatus.LatestVSTOAddinVersion.ReleaseDate);
            let LatestVSTOAddinVersionReleaseDateString = LatestVSTOAddinVersionReleaseDate.toLocaleDateString('nl-NL', { timeZone: 'UTC' });
            let statusdateLastCheckDate = new Date(status.dateLastCheck);
            let statusdateLastCheckDateString = statusdateLastCheckDate.toLocaleDateString('nl-NL', { timeZone: 'UTC' });
            let statusdateExpirationDate = new Date(status.dateExpiration);
            let statusdateExpirationDateString = statusdateExpirationDate.toLocaleDateString('nl-NL', { timeZone: 'UTC' });
            
            
            if (status.level !== 'Free' && status.level !== '-' && status.level !== '') {
                $("#msteams_btnSupportButton").removeClass("hidden").addClass("shown");
                $("#msteams_latestVSTOVersion").text(appStatus.LatestVSTOAddinVersion.Version);
                $("#msteams_latestVSTOVersionReleaseDate").text(LatestVSTOAddinVersionReleaseDateString);
                document.getElementById("msteams_latestVSTOVersionDownload")?.setAttribute("href", appStatus.LatestVSTOAddinVersion.UrlBinary);
                document.getElementById("msteams_latestVSTOVersionReleaseNotes")?.setAttribute("href", appStatus.LatestVSTOAddinVersion.UrlReleaseNotes);
            }

            $("#msteams_licensestatus").html(status.status);
            $("#msteams_licenselastcheck").text(statusdateLastCheckDateString);
            $("#msteams_licenseexpires").text(statusdateExpirationDateString);
            $("#msteams_licenselevel").text(status.level);
            $("#msteams_licenseEmail").val(status.email);
            $("#msteams_licensekey").val(status.licensekey);
            
			if (gid === '') {
				$("#msteams_defaultlicensestatus").html(status.status);
	            $("#msteams_defaultlicenselastcheck").text(statusdateLastCheckDateString);
				$("#msteams_defaultlicenseexpires").text(statusdateExpirationDateString);
				$("#msteams_defaultlicenselevel").text(status.level);
			}

            this.updateStatus(status.statusKind);
            this.updateButtonStatus(status.statusKind);

			// Shows/Hides inheritance checkbox and disables user input if needed
			if (gid !== '') {
				$("#msteams_licensekey").next().addClass('settingkeyvalueinherited');
				if (status.ncgroup === '') {
					// Group inherits the default license
					$("#msteams_licensekey").next().find("input").prop('checked', true);
					$("#msteams_licensekey").prop('disabled', true);
					$("#msteams_licenseEmail").prop('disabled', true);
					this.disableButtons();
				} else {
					// Group has a specific license
					$("#msteams_licensekey").next().find("input").prop('checked', false);
					$("#msteams_licensekey").prop('disabled', false);
					$("#msteams_licenseEmail").prop('disabled', false);
			        this.enableButtons();
				}
			} else {
				// We are showing the default license
				$("#msteams_licensekey").next().removeClass('settingkeyvalueinherited');
				$("#msteams_licensekey").prop('disabled', false);
				$("#msteams_licenseEmail").prop('disabled', false);
		        this.enableButtons();
			}

			// Sets up inheritance checkbox's action
            $("#msteams_licensekey").next().find("input").off('change')
            $("#msteams_licensekey").next().find("input").on('change', {gid}, (ev) => {
				if ($("#msteams_licensekey").next().find('input:checked').val()) {
					this.deleteLicense(ev.data.gid);
					this.refreshLicenseStatus(ev.data.gid).then(() => {
						$("#msteams_licensekey").prop('disabled', true);
						$("#msteams_licenseEmail").prop('disabled', true);
						this.disableButtons()
					});
	            } else {
					$("#msteams_licensekey").prop('disabled', false);
					$("#msteams_licenseEmail").prop('disabled', false);
					this.createLicense('', '', ev.data.gid);
					this.enableButtons();
                }
            });

			// Makes sure the buttons' click action act on the correct group
			$("#msteams_btnLicenseActivation").off('click')
			$("#msteams_btnLicenseActivation").on('click', {gid}, (ev) => {
	            ev.preventDefault();
	            const email = $("#msteams_licenseEmail").val()?.toString().replace(/\s+/g, '') || '';
	            const key = $("#msteams_licensekey").val()?.toString().replace(/\s+/g, '') || '';
	            this.createLicense(email, key, ev.data.gid);
	        });
		    $("#msteams_btnClearLicense").off('click')
		    $("#msteams_btnClearLicense").on('click', {gid}, (ev) => {
				$("#msteams_licenseEmail").val('');
		        $("#msteams_licensekey").val('');
	            this.createLicense('', '', ev.data.gid);
	        });

        } catch (err) {
            console.warn('Error while fetching license status', err);

            $("#msteams_licensestatus").text(t("sendent", "Cannot verify your license. Please make sure your licensekey and emailaddress are correct before you try to 'Activate license'."));
            $("#msteams_licenselastcheck").text(t("sendent", "Just now"));
            $("#msteams_licenseexpires, #msteams_licenselevel").text("-");

            this.showErrorStatus();

            $("#msteams_btnLicenseActivation").val(t("sendent", "Activate license"));
            $("#msteams_btnLicenseActivation").removeClass("hidden").addClass("shown");
            $("#msteams_btnSupportButton").removeClass("shown").addClass("hidden");

	        this.enableButtons();
        }

		return;
    }

    private insertLoadIndicator(selector: string) {
        $(selector).html('<div class="spinner"> <div class="bounce1"></div> <div class="bounce2"></div> <div class="bounce3"></div></div>');
    }

    private updateStatus(kind: string) {
        if (['valid'].includes(kind)) {
            this.showOkStatus();
        } else if (['check', 'nolicense', 'userlimit'].includes(kind)) {
            this.showWarningStatus();
        } else {
            this.showErrorStatus();
        }
    }

    private updateButtonStatus(kind: string) {
        if (kind === 'valid') {
            $("#msteams_btnLicenseActivation")
                .removeClass("shown")
                .addClass("hidden")
                .val(t("sendent", "Activate license"));
        } else if (['check', 'expired', 'userlimit'].includes(kind)) {
            $("#msteams_btnLicenseActivation")
                .removeClass("hidden")
                .addClass("shown")
                .val(t("sendent", "Revalidate license"));
        } else {
            $("#msteams_btnLicenseActivation")
                .removeClass("hidden")
                .addClass("shown")
                .val(t("sendent", "Activate license"));
        }
    }

    private showErrorStatus() {
        $("#msteams_license .licensekeyvalueinput").addClass("errorStatus").removeClass("okStatus warningStatus");
    }

    private showWarningStatus() {
        $("#msteams_license .licensekeyvalueinput").addClass("warningStatus").removeClass("okStatus errorStatus");
    }

    private showOkStatus() {
        $("#msteams_license .licensekeyvalueinput").addClass("okStatus").removeClass("errorStatus warningStatus");
    }

    private disableButtons() {
        $("#msteams_btnSupportButton, #msteams_btnClearLicense, #msteams_btnLicenseActivation").prop('disabled', true);
    }

    private enableButtons() {
        $("#msteams_btnSupportButton, #msteams_btnClearLicense, #msteams_btnLicenseActivation").removeAttr("disabled");
    }

	private deleteLicense(gid: string, type: string = 'msteams') {
        const url = generateUrl('/apps/sendent/api/1.0/license');

        return axios.delete(url, { data: { group: gid, type: type } });
	}

    private sendCreationRequest(email: string, license: string, gid: string, type: string = 'msteams') {
        const url = generateUrl('/apps/sendent/api/1.0/license');

        return axios.post(url, { email, license, ncgroup: gid, type: type });
    }

    private requestStatus(gid: string, type: string = 'msteams') {

        const url = generateUrl('/apps/sendent/api/1.0/licensestatusForNCGroupinternal?ncgroup=' + gid + '&type=' + type);

        return axios.get<LicenseStatus>(url);
    }
    private requestApplicationStatus() {
        const url = generateUrl('/apps/sendent/api/1.0/status');

        return axios.get<LicenseStatus>(url);
    }

	public getReport(type: string = 'msteams') {
        const url = generateUrl('/apps/sendent/api/1.0/licensereport?type=' + type);

		return axios.get(url);
	}
}
