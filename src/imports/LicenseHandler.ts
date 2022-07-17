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
    LatestVSTOAddinVersion : AppVersionStatus
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
        $("#btnLicenseActivation").on('click', (ev) => {
            ev.preventDefault();

            const email = $("#licenseEmail").val()?.toString().replace(/\s+/g, '') || '';
            const key = $("#licensekey").val()?.toString().replace(/\s+/g, '') || '';

            this.createLicense(email, key);
        });

        $("#btnClearLicense").on('click', () => {
            $("#licenseEmail").val('');
            $("#licensekey").val('');
            
            this.createLicense('', '');
        });

        $("#licenseEmail, #licensekey").on('change', () => {
            $("#btnLicenseActivation").val(t("sendent", "Activate license"));
            $("#btnLicenseActivation").removeClass("hidden").addClass("shown");
        });

        $('#btnSupportButton').on('click', () => {
            (window as any).location = 'mailto:support@sendent.nl';
        });
    }

    public static setup(): LicenseHandler {
        if (!this.instance) {
            this.instance = new LicenseHandler();

            this.instance.refreshLicenseStatus();
        }

        return this.instance;
    }

    public async createLicense(email: string, key: string): Promise<void> {
        this.disableButtons();

        try {
            await this.sendCreationRequest(email, key);
        } catch (err) {
            console.log('Could not create license', err);
        }

        this.enableButtons();

        return this.refreshLicenseStatus();
    }

    public async refreshLicenseStatus(): Promise<void> {
        this.insertLoadIndicator('#licensestatus, #latestVSTOVersion, #licenselastcheck, #licenseexpires, #licenselevel');
        this.disableButtons();

        try {
            const { data: status } = await this.requestStatus();
            const { data: appStatus } = await this.requestApplicationStatus();

            if (status.level !== 'Free' && status.level !== '-' && status.level !== '') {
                $("#btnSupportButton").removeClass("hidden").addClass("shown");
                $("#latestVSTOVersion").text(appStatus.LatestVSTOAddinVersion.Version);
                $("#latestVSTOVersionReleaseDate").text(appStatus.LatestVSTOAddinVersion.ReleaseDate);
                document.getElementById("latestVSTOVersionDownload")?.setAttribute("href", appStatus.LatestVSTOAddinVersion.UrlBinary);
                document.getElementById("latestVSTOVersionReleaseNotes")?.setAttribute("href", appStatus.LatestVSTOAddinVersion.UrlReleaseNotes);
            }

            $("#licensestatus").html(status.status);
            $("#licenselastcheck").text(status.dateLastCheck);
            $("#licenseexpires").text(status.dateExpiration);
            $("#licenselevel").text(status.level);
            $("#licenseEmail").val(status.email);
            $("#licensekey").val(status.licensekey);
            


            this.updateStatus(status.statusKind);
            this.updateButtonStatus(status.statusKind);
        } catch (err) {
            console.warn('Error while fetching license status', err);

            $("#licensestatus").text(t("sendent", "Cannot verify your license. Please make sure your licensekey and emailaddress are correct before you try to 'Activate license'."));
            $("#licenselastcheck").text(t("sendent", "Just now"));
            $("#licenseexpires, #licenselevel").text("-");

            this.showErrorStatus();

            $("#btnLicenseActivation").val(t("sendent", "Activate license"));
            $("#btnLicenseActivation").removeClass("hidden").addClass("shown");
            $("#btnSupportButton").removeClass("shown").addClass("hidden");
        }

        this.enableButtons();
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
            $("#btnLicenseActivation")
                .removeClass("shown")
                .addClass("hidden")
                .val(t("sendent", "Activate license"));
        } else if (['check', 'expired', 'userlimit'].includes(kind)) {
            $("#btnLicenseActivation")
                .removeClass("hidden")
                .addClass("shown")
                .val(t("sendent", "Revalidate license"));
        } else {
            $("#btnLicenseActivation")
                .removeClass("hidden")
                .addClass("shown")
                .val(t("sendent", "Activate license"));
        }
    }

    private showErrorStatus() {
        $("#license .licensekeyvalueinput").addClass("errorStatus").removeClass("okStatus warningStatus");
    }

    private showWarningStatus() {
        $("#license .licensekeyvalueinput").addClass("warningStatus").removeClass("okStatus errorStatus");
    }

    private showOkStatus() {
        $("#license .licensekeyvalueinput").addClass("okStatus").removeClass("errorStatus warningStatus");
    }

    private disableButtons() {
        $("#btnSupportButton, #btnClearLicense, #btnLicenseActivation").prop('disabled', true);
    }

    private enableButtons() {
        $("#btnSupportButton, #btnClearLicense, #btnLicenseActivation").removeAttr("disabled");
    }

    private sendCreationRequest(email: string, license: string) {
        const url = generateUrl('/apps/sendent/api/1.0/license');

        return axios.post(url, { email, license });
    }

    private requestStatus() {
        const url = generateUrl('/apps/sendent/api/1.0/licensestatus');

        return axios.get<LicenseStatus>(url);
    }
    private requestApplicationStatus() {
        const url = generateUrl('/apps/sendent/api/1.0/status');

        return axios.get<LicenseStatus>(url);
    }
}
