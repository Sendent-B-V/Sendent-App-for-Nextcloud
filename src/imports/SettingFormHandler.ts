/* eslint-disable @nextcloud/no-deprecations */
import MultiInputList from "./MultiInputList";
import SettingGroupValueAjaxCalls from "./SettingGroupValueAjaxCalls";
import SettingKeyAjaxCalls from "./SettingKeyAjaxCalls";
import TemplateEditor from "./TemplateEditor";
import tinymce from 'tinymce';


export default class SettingFormHandler {

    private static instance: SettingFormHandler;

    public static get(): SettingFormHandler {
        if (!this.instance) {
            this.instance = new SettingFormHandler();
        }

        return this.instance;
    }

    private calls: SettingKeyAjaxCalls;
    private valuecalls: SettingGroupValueAjaxCalls;
    private logoUrl: string;

    private constructor() {
        this.calls = new SettingKeyAjaxCalls();
        this.valuecalls = new SettingGroupValueAjaxCalls();
        this.logoUrl = $('#header .logo').css('background-image').replace(/url\(("|')(.+)("|')\)/gi, '$2').trim();
    }

    public async loopThroughSettings(gid=''): Promise<void> {
		console.log('Displaying settings for group "' + gid + '"');
        const allSettings = await this.valuecalls.list(gid);

        $(".settingkeyvalue").each((index, element) => {
            const inputElement = $(element).find<HTMLTextAreaElement>('.settingkeyvalueinput');
            const name = $(element).find("[name='settingkeyname']").val()?.toString();
            const key = $(element).find("[name='settingkeykey']").val()?.toString();
            const templateId = $(element).find("[name='settingkeytemplateid']").val()?.toString();
            const groupId = $(element).find("[name='settinggroupid']").val()?.toString();
            const value = inputElement.val()?.toString();
            const valueType = inputElement.prop('type');

            if (!key || !name || !templateId || !valueType || typeof value !== 'string' || !groupId) {
                console.warn('key, name, templateid, valuetype, not string or no groupid constrained triggerd. cannot continue with settings renderling.');
                return;
            }

            const setting = allSettings.filter(candidate => candidate.settingkeyid.toString() === key);

            if (setting.length < 1) {
                this.saveSetting($(element).parents('.personal-settings-setting-box'), gid);
            }

            this.updateUI($(element));

			// Removes existing event handler in case of refresh
			try {
				inputElement.off('change');
			} catch (err) {}

			// Adds an onChange event handler
            inputElement.on('change', () => {
                this.saveSetting($(element).parents('.personal-settings-setting-box'), gid);

				// Unchecks inherited checkbox if needed
				if (gid !== '') {
					let label = inputElement.next();
					if (!label.is('label')) {
						// In case of free-text settings, the tincymce editor comes in the way between the input and the label
						label = label.next();
					}
					label.find('input').prop('checked', false);
				}

                this.updateUI($(element));
            });

            //when settingkey is present: populate UI
            try {
				// Sets setting's value
                inputElement.val(setting[0].value);
            } catch (err) {
                console.warn(key);
                console.warn(name);
                console.warn(setting[0]);
                console.warn(err.message);

                //when no settingkey is present
                this.initSettingKey(element, key, name, valueType, templateId, value, groupId);
            }

			// When we are not showing the default group'settings, we show the inheritedCheckbox. Else, we hide it
			let label = inputElement.next();
			if (!label.is('label')) {
				// In case of free-text settings, the tincymce editor comes in the way between the input and the label
				label = label.next();
			}
			if (gid !== '') {
				// Shows inherited checkbox
				label.addClass('settingkeyvalueinherited');
                
				// Checks the checkbox when setting is inherited
				const inheritedCheckbox = label.find('input');
				if (setting[0].ncgroup === '') {
					inheritedCheckbox.prop('checked', true);
				} else {
					inheritedCheckbox.prop('checked', false);
				}

				// Adds onChange action
				try {
					inheritedCheckbox.off('change');
				} catch (err) {}
				inheritedCheckbox.on('change', () => {
					if (label.find('input:checked').val()) {
						console.log('refreshing setting');
						this.valuecalls.delete(key, gid).then((defaultSetting) => {
							// Refresh group setting with default setting after inheritance has been removed
							if (inputElement.prop('tagName') === 'TEXTAREA') {
								this.refreshTinymceEditor(inputElement, defaultSetting.value);
				            } else {
								inputElement.val(defaultSetting.value);
							}
        					this.setShowHideAllSettings();
						})
					} else {
						this.saveSetting($(element).parents('.personal-settings-setting-box'), gid);
					}
				});
			} else {
				// Hides inherited checkbox
				const label = inputElement.next();
				label.removeClass('settingkeyvalueinherited');
			}

			// Adds color picker to theming color settings
			try {
				if (inputElement.hasClass('theming-color')) {
					this.refreshColorPicker(element);
				}
			} catch (err) {}

            if (inputElement.hasClass('multiValueInput')) {
                const multiInputContainer = $(element).find('.multiInputContainer');
                const currentValue = setting.length > 0 ? setting[0].value : '';

                new MultiInputList(multiInputContainer, currentValue, inputElement, gid)
            }

			// Free text settings
            if (inputElement.prop('tagName') === 'TEXTAREA') {
				this.refreshTinymceEditor(inputElement, setting[0].value);
            }
        });

        this.setShowHideAllSettings();
    }

	private refreshTinymceEditor(element: JQuery<HTMLTextAreaElement>, value: string): void {
		const id = element.get(0)!.id;

		// Removes existing tinymce if any
		try {
			tinymce.get(id).remove();
			element.get(0)!.value = value;
		} catch (error) {}

		// Attaches a tinymce editor to the setting
		new TemplateEditor(element.get(0) as HTMLTextAreaElement, this.logoUrl);
	}

    private refreshColorPicker(element: HTMLElement): void {
        new (<any>window).jscolor($(element).find(".settingkeyvalueinput.theming-color")[0], { hash: true });
    }

    private async initSettingKey(element: HTMLElement, key: string, name: string, valueType: string, templateId: string, value: string, groupId: string) {
        const data = await this.calls.create(key, name, valueType, templateId);

        $(element).find("[name='settingkeyid']").val(data.key);

        try {
            await this.valuecalls.showBySettingKeyId(key);
        } catch (err) {
            //when no settinggroupvalue is present
            const data2 = await this.valuecalls.create(data.key, value, groupId);

            //when no settinggroupvalue is present: create settinggroupvalue
            //TODO: We have to check if settingkeyvaluetype is indicating DOM element is supposed to contain HTML without rendering it.
            $(element).find(".settingkeyvalueinput").val(data2.value)
        }
    }

    public async saveSetting(settingbox: JQuery<HTMLElement>, gid: string): Promise<boolean> {
        const settingkeyvalueblock = $(settingbox).find(".settingkeyvalue")[0]; //@TODO

        //@TODO move to method
        const id = $(settingkeyvalueblock).find("[name='settingkeyid']").val()?.toString();
        const name = $(settingkeyvalueblock).find("[name='settingkeyname']").val()?.toString();
        const key = $(settingkeyvalueblock).find("[name='settingkeykey']").val()?.toString();
        const groupId = $(settingkeyvalueblock).find("[name='settinggroupid']").val()?.toString();
        const value = $(settingkeyvalueblock).find(".settingkeyvalueinput").val()?.toString();

        if (!id || !groupId || typeof value !== 'string') {
            return false;
        }

        try {
            const data3 = await this.valuecalls.update(id, id, value, groupId, gid);

            $(settingkeyvalueblock).find(".settingkeyvalueinput").val(data3.value);


        } catch (err) {
            //when no settinggroupvalue is present
            const data3 = await this.valuecalls.create(id, value, groupId, gid);

            $(settingkeyvalueblock).find(".settingkeyvalueinput").val(data3.value);
        }

        const statusElement = $(settingkeyvalueblock).find(".status-ok")[0];
        $(statusElement).removeClass("hidden").addClass("shown").delay(1000).queue(function (next) {
            $(this).addClass("hidden");
            $(this).removeClass("shown")
            next();
        });

        return true;
    }

    private setShowHideAllSettings(): void {
        const personalSettingBoxes = $(".personal-settings-setting-box");

        personalSettingBoxes.each((_, settingbox) => {
            const values = $(settingbox).find(".settingkeyvalueinput");
            const settingkeyid = $(settingbox).find("[name='settingkeyname']").val()?.toString();

            if (!settingkeyid) {
                return;
            }

            values.each(() => {
                this.showHideAttachmentSize(values, settingkeyid);
                this.showHideAdvancedTheming(values, settingkeyid);
            });
        });
    }

    private showHideAttachmentSize(settingkeyvalues: JQuery<HTMLElement>, settingkeyid: string): void {
        const settingkeyvalue = settingkeyvalues.val();

        if (settingkeyid == "attachmentmode") {
            if (settingkeyvalue == "MaximumAttachmentSize") {
                $(".personal-settings-setting-box.attachmentSize").removeClass("hidden").addClass("shown");
            }
            else {
                $(".personal-settings-setting-box.attachmentSize").addClass("hidden").removeClass("shown");
            }
        }
        else if (settingkeyid == "sendmode") {
            if (settingkeyvalue == "Separate") {
                $(".personal-settings-setting-box.htmlSnippetPassword").removeClass("hidden").addClass("shown");
            }
            else {
                $(".personal-settings-setting-box.htmlSnippetPassword").addClass("hidden").removeClass("shown");
            }
        }
    }

    private showHideAdvancedTheming(settingkeyvalues: JQuery<HTMLElement>, settingkeyid: string): void {
        const settingkeyvalue = settingkeyvalues.val();

        if (settingkeyid == "AdvancedThemingEnabled") {
            if (settingkeyvalue == "true") {
                $(".advancedTheming").removeClass("hidden").addClass("shown");
            }
            else {
                $(".advancedTheming").addClass("hidden").removeClass("shown");
            }
        }
    }

    private updateUI(settingbox: JQuery<HTMLElement>): void {
        const keyValue = $(settingbox).find(".settingkeyvalueinput").first().val()?.toString();
        const keyId = $(settingbox).find("[name='settingkeyname']").val()?.toString();

        if (!keyId || typeof keyValue !== 'string') {
            return;
        }

        if (keyId === "attachmentmode") {
            if (keyValue === "MaximumAttachmentSize") {
                $(".personal-settings-setting-box.attachmentSize").removeClass("hidden").addClass("shown");
            } else {
                $(".personal-settings-setting-box.attachmentSize").addClass("hidden").removeClass("shown");
            }
        }
        else if (keyId === "sendmode") {
            if (keyValue == "Separate") {
                $(".personal-settings-setting-box.htmlSnippetPassword").removeClass("hidden").addClass("shown");
            } else {
                $(".personal-settings-setting-box.htmlSnippetPassword").addClass("hidden").removeClass("shown");
            }
        } else if (keyId === "AdvancedThemingEnabled") {
            if (keyValue === "true") {
                $(".advancedTheming").removeClass("hidden").addClass("shown");
            }
            else {
                $(".advancedTheming").addClass("hidden").removeClass("shown");
            }
        }
    }
}
