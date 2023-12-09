/* eslint-disable @nextcloud/no-deprecations */
import "../css/dependencies.scss";
import DependenciesHandler from "./imports/DependenciesHandler";
import LicenseHandler from "./imports/LicenseHandler";
import MSTeamsLicenseHandler from "./imports/LicenseHandlerMSTeams";
import SettingFormHandler from "./imports/SettingFormHandler";
import GroupsManagementHandler from "./imports/GroupsManagementHandler";

$(() => {
	console.log('Setting script loaded');

	DependenciesHandler.setup();
	const licenseHandler = LicenseHandler.setup();
	const licenseHandlerMSTeams = MSTeamsLicenseHandler.setup();
	const settingFormHandler = SettingFormHandler.get();
	settingFormHandler.loopThroughSettings();
	GroupsManagementHandler.setup(settingFormHandler, licenseHandler);

	$('#btnDownloadLicenseReport').on('click', function (ev) {
		licenseHandler.getReport().then(resp => {
			let licenses = JSON.parse(resp.data);
			let csv = 'Group,Key,Email,Expiration date,Level, Inherited\n';
			licenses.forEach(function(row) {
				const data = row['ncgroup'] + ',' + row['licensekey'] + ',' + row['email'] + ',' + row['datelicenseend'] + ',' + row['level'] + ',' + row['inherited'];
				csv += data;
				csv += "\n";
			});
			let dummy = document.createElement('a');
			dummy.href = 'data:text/csv;charset=utf-8,' + encodeURI(csv);
			dummy.target = '_blank';
			dummy.download = 'SendentLicensesReport.csv';
			dummy.click();
		});
	})
	$('#msteams_btnDownloadLicenseReport').on('click', function (ev) {
		licenseHandlerMSTeams.getReport('msteams').then(resp => {
			let licenses = JSON.parse(resp.data);
			let csv = 'Group,Key,Email,Expiration date,Level, Inherited\n';
			licenses.forEach(function(row) {
				const data = row['ncgroup'] + ',' + row['licensekey'] + ',' + row['email'] + ',' + row['datelicenseend'] + ',' + row['level'] + ',' + row['inherited'];
				csv += data;
				csv += "\n";
			});
			let dummy = document.createElement('a');
			dummy.href = 'data:text/csv;charset=utf-8,' + encodeURI(csv);
			dummy.target = '_blank';
			dummy.download = 'SendentMSTeamsLicensesReport.csv';
			dummy.click();
		});
	})

	$('#settingsform').on('submit', function (ev) {
		ev.preventDefault();
		//I had an issue that the forms were submitted in geometrical progression after the next submit.
		// This solved the problem.
		ev.stopImmediatePropagation();
	});
})
