/* eslint-disable @nextcloud/no-deprecations */
import axios from '@nextcloud/axios';
import { generateUrl } from '@nextcloud/router';
import { translate as t } from '@nextcloud/l10n'
import SettingFormHandler from "./SettingFormHandler";
import LicenseHandler from "./LicenseHandler"
import Sortable from "sortablejs";
require("jquery-ui/ui/widgets/sortable");

export default class GroupsManagementHandler {

	private static instance: GroupsManagementHandler;
	private settingFormHandler: SettingFormHandler;
	private licenseHandler: LicenseHandler;

	public static setup(settingFormHandler: SettingFormHandler, licenseHandler: LicenseHandler): GroupsManagementHandler {
		console.log('Initializing sendent groups lists');

		if (!this.instance) {
			this.instance = new GroupsManagementHandler();
		}

		this.instance.settingFormHandler = settingFormHandler;
		this.instance.licenseHandler = licenseHandler;

		// Activates group lists filters
		$("#ncGroupsFilter").on( "keyup", function() {
			const value = $(this).val()!.toString().toLowerCase()
			$("#ncGroups li").each(function() {
				$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
			})
		})
		$("#sendentGroupsFilter").on( "keyup", function() {
			const value = $(this).val()!.toString().toLowerCase()
			$("#sendentGroups li").each(function() {
				$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
			})
		})

		// Makes the Sendent groups lists sortable
		// Sortable.create(document.getElementById('ncGroups'), {
		// 	filter: ".ui-state-disabled",
		// 	group: "GroupsManagement",
		// 	sort: true,
		// 	draggable: ".ui-state-default",
		// 	dataIdAttr: 'data-gid'
		// }).find( "li" )
		// .on( "click", this.instance.showSettingsForGroup);

		// Sortable.create(document.getElementById('sendentGroups'), {
		// 	filter: ".ui-state-disabled",
		// 	onAdd: () => this.instance.updateGroupLists(),
		// 	onRemove: () => this.instance.updateGroupLists(),
		// 	group: "GroupsManagement",
		// 	draggable: ".ui-state-default",
		// 	dataIdAttr: 'data-gid',
		// 	sort: true
		// }).find( "li" )
		// .on( "click", this.instance.showSettingsForGroup);

		var $sortable1 = $("#ncGroups")
		.sortable({
			connectWith: ".connectedSortable",
			items: ".sorting-initialize" // only insert element with class sorting-initialize
		});

		$sortable1.find(".ui-state-default").on("mouseenter",function(){
		  $(this).addClass("sorting-initialize");
		  $sortable1.sortable('refresh');
		});

		$("#sendentGroups").sortable({
			connectWith: ".connectedSortable",
			update: () => this.instance.updateGroupLists()
		}).find( "li" )
		.on( "click", this.instance.showSettingsForGroup)

		$("#defaultGroup").sortable({
			cancel: ".unsortable",
		}).find( "li" )
		.on( "click", this.instance.showSettingsForGroup)

		return this.instance;
	}

	private showSettingsForGroup(event) {

		// Don't do anything if the clicked group is not a Sendent group
		if (event.target.parentNode.id === "ncGroups") {
			return;
		}

		// Unselect all other previously selected groups
		$('#groupsManagement div ul li').each(function() {
			if (this !== event.target) {
				$(this).removeClass('ui-selected');
			} else {
				$(this).addClass('ui-selected');
			}
		});

		// Gets group for which settings are to be shown
		let ncgroupDisplayName = event.target.textContent
		const ncgroupGid = event.target.dataset.gid;

		// Changes currently selected group information
		$('#currentGroup').text(ncgroupDisplayName);

		// Default should be the empty string
		ncgroupDisplayName = ncgroupDisplayName === t('sendent', 'Default') ? '' : ncgroupDisplayName;

		// Updates license
		GroupsManagementHandler.instance.licenseHandler.refreshLicenseStatus(ncgroupGid)

		// Updates settings value
		GroupsManagementHandler.instance.settingFormHandler.loopThroughSettings(ncgroupGid);
	}

	private updateGroupLists() {

		// Disable sortable attribute for NC groups that have been deleted
		$('#ncGroups li').filter(function() {return this.innerHTML.endsWith('*** DELETED GROUP ***')}).addClass('ui-state-disabled')

		// Get the list of sendent groups from the UI
		// TODO: Rewrite the selection with a each()
		const li = $('#sendentGroups li');
		const newSendentGroups = Object.values(li).map(htmlElement => htmlElement.dataset?.gid).filter(text => text !== undefined);

		// Update backend
		console.log('Updating backend');
		const url = generateUrl('/apps/sendent/api/2.0/groups/update');
		return axios.post(url, {newSendentGroups});

	}
}
