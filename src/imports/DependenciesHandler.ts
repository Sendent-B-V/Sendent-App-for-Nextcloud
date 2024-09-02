/* eslint-disable @nextcloud/no-deprecations */
import axios from '@nextcloud/axios';
import { generateUrl } from '@nextcloud/router';

export default class DependenciesHandler {
    private static instance: DependenciesHandler;

    public static setup() {
		console.log('Initializing dependencies');

        if (!this.instance) {
            this.instance = new DependenciesHandler();
        }

		$('#requiredApps').html('<div class="spinner"> <div class="bounce1"></div> <div class="bounce2"></div> <div class="bounce3"></div></div>')
		$('#recommendedApps').html('<div class="spinner"> <div class="bounce1"></div> <div class="bounce2"></div> <div class="bounce3"></div></div>')

		this.instance.getCapabilities().then(resp => {
			const capabilities = resp.data.ocs.data.capabilities
			const capabilitiesKeys = Object.keys(capabilities)

			const requiredApps = {
				'core': 'core',
				'files': 'files',
				'dav': 'dav',
				'ocm': 'ocm',
				'files_sharing': 'files sharing',
				'password_policy': 'password policy',
				'theming': 'theming'			
			}
			
			$('#requiredApps').html('')
			let div = document.createElement('div')
			$(div).attr('id','requiredAppsList')
			$(div).appendTo('#requiredApps')
			let allGood = true
			Object.keys(requiredApps).forEach(app => {
				const idx = capabilitiesKeys.findIndex((key) => key === app)
				if (idx !== -1) {
					let div = this.instance.createSettingBox(requiredApps[app], true)
					const capability = capabilities[idx]
					$(div).appendTo('#requiredApps #requiredAppsList')
				} else {
					allGood = false
					let div = this.instance.createSettingBox(requiredApps[app], false)
					$(div).appendTo('#requiredApps #requiredAppsList')
				}
			})

			div = document.createElement('div')
			$(div).attr('id','allRequiredAppsInstalled')
			$(div).appendTo('#requiredApps')
			//div = this.instance.createSettingBox('All installed', true)
			//$(div).appendTo('#requiredApps #allRequiredAppsInstalled')

			// // When all dependencies are satisfied mmoves the apps list into the AllInstalled div and make it foldable
			// if (allGood) {
			// 	$('#requiredApps #requiredAppsList').detach().appendTo('#requiredApps #allRequiredAppsInstalled')
			// 	$('#requiredApps #requiredAppsList').attr('style', 'margin-left: 20px')
			// 	$('#requiredApps #allRequiredAppsInstalled span').attr('style', 'cursor: pointer')
			// 	$('#requiredApps #requiredAppsList').addClass('hidden')
			// 	$('#requiredApps #allRequiredAppsInstalled').on('click', () => {
			// 		if ($('#requiredApps #requiredAppsList').hasClass('hidden')) {
			// 			$('#requiredApps #requiredAppsList').removeClass('hidden')
			// 		} else {
			// 			$('#requiredApps #requiredAppsList').addClass('hidden')
			// 		}
			// 	})
			// } else {
			// 	$('#requiredApps #allRequiredAppsInstalled').addClass('hidden')
			// }

			const recommendedApps = {
				'activity': 'activity',
				'spreed': 'Talk'
			}
			$('#recommendedApps').html('')
			div = document.createElement('div')
			$(div).attr('id','recommendedAppsList')
			$(div).appendTo('#recommendedApps')
			allGood = true
			Object.keys(recommendedApps).forEach(app => {
				const idx = capabilitiesKeys.findIndex((key) => key === app)
				if (idx !== -1) {
					let div = this.instance.createSettingBox(recommendedApps[app], true)
					const capability = capabilities[idx]
					$(div).appendTo('#recommendedApps #recommendedAppsList')
				} else {
					allGood = false
					let div = this.instance.createSettingBox(recommendedApps[app], false)
					$(div).appendTo('#recommendedApps #recommendedAppsList')
				}
			})

			div = document.createElement('div')
			$(div).attr('id','allRecommendedAppsInstalled')
			$(div).appendTo('#recommendedApps')
			//div = this.instance.createSettingBox('All installed', true)
			//$(div).appendTo('#recommendedApps #allRecommendedAppsInstalled')

			// // When all dependencies are satisfied mmoves the apps list into the AllInstalled div and make it foldable
			// if (allGood) {
			// 	$('#recommendedApps #recommendedAppsList').detach().appendTo('#recommendedApps #allRecommendedAppsInstalled')
			// 	$('#recommendedApps #recommendedAppsList').attr('style', 'margin-left: 20px')
			// 	$('#recommendedApps #allRecommendedAppsInstalled span').attr('style', 'cursor: pointer')
			// 	$('#recommendedApps #recommendedAppsList').addClass('hidden')
			// 	$('#recommendedApps #allRecommendedAppsInstalled').on('click', () => {
			// 		if ($('#recommendedApps #recommendedAppsList').hasClass('hidden')) {
			// 			$('#recommendedApps #recommendedAppsList').removeClass('hidden')
			// 		} else {
			// 			//$('#recommendedApps #recommendedAppsList').addClass('hidden')
			// 		}
			// 	})
			// } else {
			// 	//$('#recommendedApps #allRecommendedAppsInstalled').addClass('hidden')
			// }

		});

        return;
    }

	private async getCapabilities() {
		let url = generateUrl('/ocs/v1.php/cloud/capabilities').replace('/index.php','')
		return axios.get(url,{
			headers: {
				'OCS-APIRequest': 'true'
			}
		})
	}

	private createSettingBox(app, state) {
		let div1 = document.createElement('div')
		$(div1).addClass('dependency-settings-setting-box')
		let div2 = document.createElement('div')
		$(div2).addClass('settingkeyvalue')
		if (state) {
			$(div2).attr('data-state', 'success')
		} else {
			$(div2).attr('data-state', 'fail')
		}
		let label = document.createElement('label')
		$(label).addClass('licenselabel')
		let span = document.createElement('span')
		$(span).addClass('templatesettingkeyname').addClass('licenseitem').text(app)
		$(span).appendTo(label)
		$(label).appendTo(div2)
		$(div2).appendTo(div1)

		return div1
	}
}
