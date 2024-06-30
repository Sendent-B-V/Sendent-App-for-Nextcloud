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
    product: string,
    istrial: number
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

            this.createLicense(email, key, '');
        });

        $("#btnClearLicense").on('click', (ev) => {
            $("#licenseEmail").val('');
            $("#licensekey").val('');
            this.createLicense('', '', '');
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

            this.instance.refreshLicenseStatus('');
        }

        return this.instance;
    }

    public async createLicense(email: string, key: string, gid: string): Promise<void> {
        this.disableButtons();

        try {
            await this.sendCreationRequest(email, key, gid);
        } catch (err) {
            console.log('Could not create license', err);
        }

        this.enableButtons();

        return this.refreshLicenseStatus(gid);
    }

    public async refreshLicenseStatus(gid: string): Promise<void> {
        this.insertLoadIndicator('#licensestatus, #latestVSTOVersion, #licenselastcheck, #licenseexpires, #licenselevel, #licenseProducts, #licenseSubscriptionType');
        this.disableButtons();

		console.log('Refreshing license status', gid);
        try {
            const { data: status } = await this.requestStatus(gid);
            const { data: appStatus } = await this.requestApplicationStatus();
            const offline_mode_text = "Undetermined because Offline mode is used.";
            let LatestVSTOAddinVersionReleaseDate = new Date(appStatus.LatestVSTOAddinVersion.ReleaseDate);
            let LatestVSTOAddinVersionReleaseDateString = LatestVSTOAddinVersionReleaseDate.toLocaleDateString('nl-NL', { timeZone: 'UTC' });
            let statusdateLastCheckDate = new Date(status.dateLastCheck);
            let statusdateLastCheckDateString = status.level == "Offline_mode" ? offline_mode_text : statusdateLastCheckDate.toLocaleDateString('nl-NL', { timeZone: 'UTC' });
            let statusdateExpirationDate = new Date(status.dateExpiration);
            let statusdateExpirationDateString = status.level == "Offline_mode" ? offline_mode_text : statusdateExpirationDate.toLocaleDateString('nl-NL', { timeZone: 'UTC' });
            let statusSubscriptionType = status.level == "Offline_mode" ? offline_mode_text : status.istrial == 1 ? "Trial" : status.istrial == 0 ? "Paid subscription" : "Subscription type can't be determined";
            let statusSubscriptionLevel = status.level == "Offline_mode" ? offline_mode_text : status.level == '0' || status.level == ''|| status.level == null ? status.product : status.level;


            statusSubscriptionLevel = statusSubscriptionLevel.replace('\r\n', '<br/>');
            status.product = status.product.replace('\r\n', '<br/>');
            
            
            console.log('status.istrial = ' + status.istrial);
            console.log('status.level = ' + status.level);
            console.log('status.product = ' + status.product);
            console.log('statusSubscriptionType = ' + statusSubscriptionType);
            console.log('statusSubscriptionLevel = ' + statusSubscriptionLevel);

            
            if ((statusSubscriptionLevel !== 'Free' && statusSubscriptionLevel !== '-' && statusSubscriptionLevel !== '') && status.status == 'Current license is valid') {
                $("#outlookAddon").removeClass("hidden").addClass("shown");
                $("#btnSupportButton").removeClass("hidden").addClass("shown");
                $("#latestVSTOVersion").text(appStatus.LatestVSTOAddinVersion.Version);
                $("#latestVSTOVersionReleaseDate").text(LatestVSTOAddinVersionReleaseDateString);
                document.getElementById("latestVSTOVersionDownload")?.setAttribute("href", appStatus.LatestVSTOAddinVersion.UrlBinary);
                document.getElementById("latestVSTOVersionReleaseNotes")?.setAttribute("href", appStatus.LatestVSTOAddinVersion.UrlReleaseNotes);
                $("#outlookAddon").removeClass("hidden").addClass("shown");
                $("#outlookAddon_Releasedate").removeClass("hidden").addClass("shown");
                $("#outlookAddon_Latestversion").removeClass("hidden").addClass("shown");
                $("#outlookAddon_download").removeClass("hidden").addClass("shown");
                $("#outlookAddon_releasenotes").removeClass("hidden").addClass("shown");

                let date1 = new Date(appStatus.LatestVSTOAddinVersion.ReleaseDate);
                let date2 = new Date();
                
                // Calculating the time difference
                // of two dates
                let Difference_In_Time =
                    date2.getTime() - date1.getTime();
                
                // Calculating the no. of days between
                // two dates
                let Difference_In_Days =
                    Math.round
                        (Difference_In_Time / (1000 * 3600 * 24));

                if(Difference_In_Days < 24)
                {
                    $("#newbadge").removeClass("hidden").addClass("shown");
                }
                else
                {
                    $("#newbadge").removeClass("shown").addClass("hidden");
                }
            }
            else{
                $("#btnSupportButton").removeClass("hidden").addClass("shown");
                $("#outlookAddon").removeClass("shown").addClass("hidden");
                $("#outlookAddon_Releasedate").removeClass("shown").addClass("hidden");
                $("#outlookAddon_Latestversion").removeClass("shown").addClass("hidden");
                $("#outlookAddon_download").removeClass("shown").addClass("hidden");
                $("#outlookAddon_releasenotes").removeClass("shown").addClass("hidden");
          }
            if(statusSubscriptionLevel == '' || statusSubscriptionLevel == status.product ||  status.product.toLowerCase().includes(statusSubscriptionLevel.toLowerCase()))
            {
                $("#licenselevelcontainer").removeClass("shown").addClass("hidden");
                $("#defaultlicenselevelcontainer").removeClass("shown").addClass("hidden");
            }
            else
            {                
                $("#licenselevelcontainer").removeClass("hidden").addClass("shown");
                $("#defaultlicenselevelcontainer").removeClass("hidden").addClass("shown");
            }
            if(status.product == '' || status.level == "Offline_mode")
            {
                $("#licensesupportedproductscontainer").removeClass("shown").addClass("hidden");
                $("#defaultlicensesupportedproductscontainer").removeClass("shown").addClass("hidden");
            }
            else
            {                
                $("#licensesupportedproductscontainer").removeClass("hidden").addClass("shown");
                $("#defaultlicensesupportedproductscontainer").removeClass("hidden").addClass("shown");
            }

            $("#licensestatus").html(status.status);
            $("#licenselastcheck").text(statusdateLastCheckDateString);
            $("#licenseexpires").text(statusdateExpirationDateString);
            $("#licenselevel").html(statusSubscriptionLevel);
            $("#licenseEmail").val(status.email);
            $("#licensekey").val(status.licensekey);
            $("#licenseProducts").html(status.product);
            $("#licenseSubscriptionType").html(statusSubscriptionType);
            
			if (gid === '') {
				$("#defaultlicensestatus").html(status.status);
	            $("#defaultlicenselastcheck").text(statusdateLastCheckDateString);
				$("#defaultlicenseexpires").text(statusdateExpirationDateString);
				$("#defaultlicenselevel").html(statusSubscriptionLevel);
                $("#defaultlicenseproducts").html(status.product);
                $("#defaultlicensesubscriptiontype").html(statusSubscriptionType);
			}

            
            this.updateStatus(status.statusKind);
            this.updateButtonStatus(status.statusKind);
			// Shows/Hides inheritance checkbox and disables user input if needed
			if (gid !== '') {
				$("#licensekey").next().addClass('settingkeyvalueinherited');
				if (status.ncgroup === '') {
					// Group inherits the default license
					$("#licensekey").next().find("input").prop('checked', true);
					$("#licensekey").prop('disabled', true);
					$("#licenseEmail").prop('disabled', true);
					this.disableButtons();
				} else {
					// Group has a specific license
					$("#licensekey").next().find("input").prop('checked', false);
					$("#licensekey").prop('disabled', false);
					$("#licenseEmail").prop('disabled', false);
			        this.enableButtons();
				}
			} else {
				// We are showing the default license
				$("#licensekey").next().removeClass('settingkeyvalueinherited');
				$("#licensekey").prop('disabled', false);
				$("#licenseEmail").prop('disabled', false);
		        this.enableButtons();
			}

			// Sets up inheritance checkbox's action
            $("#licensekey").next().find("input").off('change')
            $("#licensekey").next().find("input").on('change', {gid}, (ev) => {
				if ($("#licensekey").next().find('input:checked').val()) {
					this.deleteLicense(ev.data.gid);
					this.refreshLicenseStatus(ev.data.gid).then(() => {
						$("#licensekey").prop('disabled', true);
						$("#licenseEmail").prop('disabled', true);
						this.disableButtons()
					});
	            } else {
					$("#licensekey").prop('disabled', false);
					$("#licenseEmail").prop('disabled', false);
					this.createLicense('', '', ev.data.gid);
					this.enableButtons();
                }
            });
            this.hideValuesForOffline(status);

			// Makes sure the buttons' click action act on the correct group
			$("#btnLicenseActivation").off('click')
			$("#btnLicenseActivation").on('click', {gid}, (ev) => {
	            ev.preventDefault();
	            const email = $("#licenseEmail").val()?.toString().replace(/\s+/g, '') || '';
	            const key = $("#licensekey").val()?.toString().replace(/\s+/g, '') || '';
	            this.createLicense(email, key, ev.data.gid);
	        });
		    $("#btnClearLicense").off('click')
		    $("#btnClearLicense").on('click', {gid}, (ev) => {
				$("#licenseEmail").val('');
		        $("#licensekey").val('');
	            this.createLicense('', '', ev.data.gid);
	        });
        } catch (err) {
            console.warn('Error while fetching license status', err);

            $("#licensestatus").text(t("sendent", "Cannot verify your license. Please make sure your licensekey and emailaddress are correct before you try to 'Activate license'."));
            $("#licenselastcheck").text(t("sendent", "Just now"));
            $("#licenseexpires, #licenselevel").text("-");
            $("#licenseProducts, #licenselevel").text("-");
            $("#licenseSubscriptionType, #licenselevel").text("-");

            //this.showErrorStatus();

            $("#btnLicenseActivation").val(t("sendent", "Activate license"));
            $("#btnLicenseActivation").removeClass("hidden").addClass("shown");
            $("#btnSupportButton").removeClass("shown").addClass("hidden");

	        this.enableButtons();
        }

        //Remove exchange connector for now.
        $("tr[id*='exchange']").each(function (i, el) {
            $(this).removeClass("shown").addClass("hidden");
        });
		return;
    }

    private insertLoadIndicator(selector: string) {
        $(selector).html('<div class="spinner"> <div class="bounce1"></div> <div class="bounce2"></div> <div class="bounce3"></div></div>');
    }

    private updateStatus(kind: string) {
        if (['valid'].includes(kind)) {
            //this.showOkStatus();
        } else if (['check', 'nolicense', 'userlimit'].includes(kind)) {
            //this.showWarningStatus();
        } else {
            //this.showErrorStatus();
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
    private hideValuesForOffline(status: LicenseStatus) {
        if (status.level === 'Offline_mode') {
            $(".subscriptionInformation").removeClass("shown").addClass("hidden");
        }
        else{
            $(".subscriptionInformation").removeClass("hidden").addClass("shown");
        }
        if(status.level == '' || status.level == status.product ||  status.product.toLowerCase().includes(status.level.toLowerCase()))
            {
                $("#licenselevelcontainer").removeClass("shown").addClass("hidden");
                $("#defaultlicenselevelcontainer").removeClass("shown").addClass("hidden");
            }
    }
    // private showErrorStatus() {
    //     $("#license .licensekeyvalueinput").addClass("errorStatus").removeClass("okStatus warningStatus");
    // }

    // private showWarningStatus() {
    //     $("#license .licensekeyvalueinput").addClass("warningStatus").removeClass("okStatus errorStatus");
    // }

    // private showOkStatus() {
    //     $("#license .licensekeyvalueinput").addClass("okStatus").removeClass("errorStatus warningStatus");
    // }

    private disableButtons() {
        $("#btnSupportButton, #btnClearLicense, #btnLicenseActivation").prop('disabled', true);
    }

    private enableButtons() {
        $("#btnSupportButton, #btnClearLicense, #btnLicenseActivation").removeAttr("disabled");
    }

	private deleteLicense(gid: string) {
        const url = generateUrl('/apps/sendent/api/1.0/license');

        return axios.delete(url, { data: { group: gid } });
	}

    private sendCreationRequest(email: string, license: string, gid: string) {
        const url = generateUrl('/apps/sendent/api/1.0/license');

        return axios.post(url, { email, license, ncgroup: gid });
    }

    private requestStatus(gid: string) {

        const url = generateUrl('/apps/sendent/api/1.0/licensestatusForNCGroupinternal?ncgroup=' + gid);

        return axios.get<LicenseStatus>(url);
    }
    private requestApplicationStatus() {
        const url = generateUrl('/apps/sendent/api/1.0/status');

        return axios.get<LicenseStatus>(url);
    }

	public getReport() {
        const url = generateUrl('/apps/sendent/api/1.0/licensereport');

		return axios.get(url);
	}
}
