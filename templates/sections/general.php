<div id="generalsettings">
    <h1>
        <?php p($l->t('General')); ?>
    </h1>
    <div class="personal-settings-setting-box">
        <div class="settingkeyvalue">
            <label>
                <span class="templatesettingkeyname">
                    <?php p($l->t('Language')); ?>
                </span>
            </label>
            <div class="status-error icon-error error hidden"></div>
            <div class="status-ok icon-checkmark ok hidden"></div>
            <select class="settingkeyvalueinput" type="select" name="settingkeyvalueinput" id="setlanguage">
                <option value="en" selected>English</option>
                <option value="nl">Nederlands</option>
                <option value="fr">Français</option>
                <option value="de">Deutsch</option>
                <option value="it">Italiano</option>
                <option value="es">Español</option>
            </select>
			<label style="display:none">
				<input class="settingkeyvalueinheritedcheckbox"  type="checkbox" />
				<span class="settingkeyvalueinheritedlabel"><?php p($l->t('Use default group settings'));?></span>
			</label>
            <input type="hidden" name="settingkeyname" value="setlanguage">
            <input type="hidden" name="settingkeykey" value="20">
            <input type="hidden" name="settingkeytemplateid" value="0">
            <input type="hidden" name="settinggroupid" value="0">
            <input type="hidden" name="settingkeyid" value="20">
        </div>
    </div>

    <div class="personal-settings-setting-box">
        <div class="settingkeyvalue">
            <label>
                <span class="templatesettingkeyname">
                    <?php p($l->t('Insert snippet location')); ?>
                </span>
            </label>
            <div class="status-error icon-error error hidden"></div>
            <div class="status-ok icon-checkmark ok hidden"></div>
            <select class="settingkeyvalueinput" type="select" name="settingkeyvalueinput" id="insertatcursor">
                <option selected value="True"><?php p($l->t('At cursor')); ?></option>
                <option value="False"><?php p($l->t('Top of email body')); ?></option>
            </select>
			<label style="display:none">
				<input class="settingkeyvalueinheritedcheckbox"  type="checkbox" />
				<span class="settingkeyvalueinheritedlabel"><?php p($l->t('Use default group settings'));?></span>
			</label>
            <input type="hidden" name="settingkeyname" value="insertatcursor">
            <input type="hidden" name="settingkeytemplateid" value="0">
            <input type="hidden" name="settinggroupid" value="0">
            <input type="hidden" name="settingkeykey" value="28">
            <input type="hidden" name="settingkeyid" value="28">
        </div>
    </div>
    <div class="personal-settings-setting-box">
        <div class="settingkeyvalue">
            <label>
                <span class="templatesettingkeyname">
                    <?php p($l->t('Add upload date to folder path')); ?>
                </span>
            </label>
            <div class="status-error icon-error error hidden"></div>
            <div class="status-ok icon-checkmark ok hidden"></div>
            <select class="settingkeyvalueinput" type="select" name="settingkeyvalueinput" id="dateaddition">
                <option selected value="True"><?php p($l->t('Add upload date to folder path')); ?></option>
                <option value="False"><?php p($l->t('Do not add upload date to folder path')); ?></option>
            </select>
			<label style="display:none">
				<input class="settingkeyvalueinheritedcheckbox"  type="checkbox" />
				<span class="settingkeyvalueinheritedlabel"><?php p($l->t('Use default group settings'));?></span>
			</label>
            <input type="hidden" name="settingkeyname" value="dateaddition">
            <input type="hidden" name="settingkeytemplateid" value="0">
            <input type="hidden" name="settinggroupid" value="0">
            <input type="hidden" name="settingkeykey" value="6">
            <input type="hidden" name="settingkeyid" value="6">
        </div>
    </div>
    <div class="personal-settings-setting-box">
        <div class="settingkeyvalue">
            <label>
                <span class="templatesettingkeyname">
                    <?php p($l->t('Debug mode')); ?>
                </span>
            </label>
            <div class="status-error icon-error error hidden"></div>
            <div class="status-ok icon-checkmark ok hidden"></div>
            <select class="settingkeyvalueinput" type="select" name="settingkeyvalueinput" id="debugmode">
                <option value="True"><?php p($l->t('Enabled')); ?></option>
                <option value="False" selected><?php p($l->t('Disabled')); ?></option>
            </select>
			<label style="display:none">
				<input class="settingkeyvalueinheritedcheckbox"  type="checkbox" />
				<span class="settingkeyvalueinheritedlabel"><?php p($l->t('Use default group settings'));?></span>
			</label>
            <input type="hidden" name="settingkeyname" value="debugmode">
            <input type="hidden" name="settingkeykey" value="22">
            <input type="hidden" name="settingkeytemplateid" value="0">
            <input type="hidden" name="settinggroupid" value="0">
            <input type="hidden" name="settingkeyid" value="22">
        </div>
    </div>
    <div class="personal-settings-setting-box">
        <div class="settingkeyvalue">
            <label>
                <span class="templatesettingkeyname">
                    <?php p($l->t('Access to settings')); ?>
                </span>
            </label>
            <div class="status-error icon-error error hidden"></div>
            <div class="status-ok icon-checkmark ok hidden"></div>
            <select class="settingkeyvalueinput" type="select" name="settingkeyvalueinput" id="disablesettings">
                <option value="False" selected><?php p($l->t('Enabled')); ?></option>
                <option value="True"><?php p($l->t('Disabled')); ?></option>
            </select>
			<label style="display:none">
				<input class="settingkeyvalueinheritedcheckbox"  type="checkbox" />
				<span class="settingkeyvalueinheritedlabel"><?php p($l->t('Use default group settings'));?></span>
			</label>
            <input type="hidden" name="settingkeyname" value="disablesettings">
            <input type="hidden" name="settingkeykey" value="17">
            <input type="hidden" name="settingkeytemplateid" value="0">
            <input type="hidden" name="settinggroupid" value="0">
            <input type="hidden" name="settingkeyid" value="17">
        </div>
    </div>
    <div class="personal-settings-setting-box">
        <div class="settingkeyvalue">
            <label>
                <span class="templatesettingkeyname">
                    <?php p($l->t('Password control behavior')); ?>
                </span>
            </label>
            <div class="status-error icon-error error hidden"></div>
            <div class="status-ok icon-checkmark ok hidden"></div>
            <select class="settingkeyvalueinput" type="select" name="settingkeyvalueinput" id="passwordcontrolbehavior">
                <option value="Off"><?php p($l->t('Off')); ?></option>
                <option value="BeforeSend" selected><?php p($l->t('Before sending')); ?></option>
                <option value="BeforeSendAndAfter"><?php p($l->t('Before and after sending')); ?></option>
            </select>
			<label style="display:none">
				<input class="settingkeyvalueinheritedcheckbox"  type="checkbox" />
				<span class="settingkeyvalueinheritedlabel"><?php p($l->t('Use default group settings'));?></span>
			</label>
            <input type="hidden" name="settingkeyname" value="passwordcontrolbehavior">
            <input type="hidden" name="settingkeytemplateid" value="0">
            <input type="hidden" name="settinggroupid" value="0">
            <input type="hidden" name="settingkeykey" value="19">
            <input type="hidden" name="settingkeyid" value="19">
        </div>
    </div>
    <div class="personal-settings-setting-box">
        <div class="settingkeyvalue">
            <label>
                <span class="templatesettingkeyname">
                    <?php p($l->t('Password communication mode')); ?>
                </span>
            </label>
            <div class="status-error icon-error error hidden"></div>
            <div class="status-ok icon-checkmark ok hidden"></div>
            <select class="settingkeyvalueinput" type="select" name="settingkeyvalueinput" id="sendmode">
                <option value="CurrentMail"><?php p($l->t('Include in email body')); ?></option>
                <option selected value="Separate"><?php p($l->t('Send in separate email')); ?></option>
                <option disabled value="External"><?php p($l->t('Use external service (like sms-gateway)')); ?></option>
            </select>
			<label style="display:none">
				<input class="settingkeyvalueinheritedcheckbox"  type="checkbox" />
				<span class="settingkeyvalueinheritedlabel"><?php p($l->t('Use default group settings'));?></span>
			</label>
            <input type="hidden" name="settingkeyname" value="sendmode">
            <input type="hidden" name="settingkeytemplateid" value="0">
            <input type="hidden" name="settinggroupid" value="0">
            <input type="hidden" name="settingkeykey" value="23">
            <input type="hidden" name="settingkeyid" value="23">
        </div>
    </div>

    <div class="personal-settings-setting-box htmlSnippetPassword">
        <div class="settingkeyvalue">
            <label>
                <span class="templatesettingkeynametextareakind">
                    <?php p($l->t('Password communication snippet')); ?>
                </span>
            </label>
            <details>
                <summary><?php p($l->t('Toggle editor')); ?></summary>

                <textarea class="settingkeyvalueinput" name="settingkeyvalueinput" type="html"
                id="htmlsnippetpassword"></textarea>
				<label style="display:none">
					<input class="settingkeyvalueinheritedcheckbox"  type="checkbox" />
					<span class="settingkeyvalueinheritedlabel"><?php p($l->t('Use default group settings'));?></span>
				</label>
            </details>
            <div class="status-error icon-error error hidden"></div>
            <div class="status-ok icon-checkmark ok hidden"></div>
            <input type="hidden" name="settingkeyname" value="htmlsnippetpassword">
            <input type="hidden" name="settingkeytemplateid" value="0">
            <input type="hidden" name="settinggroupid" value="0">
            <input type="hidden" name="settingkeykey" value="30">
            <input type="hidden" name="settingkeyid" value="30">
        </div>
    </div>
    <div class="personal-settings-setting-box">
        <div class="settingkeyvalue">
            <label>
                <span class="templatesettingkeyname">
                    <?php p($l->t('Presence Synchronization')); ?>
                </span>
            </label>
            <div class="status-error icon-error error hidden"></div>
            <div class="status-ok icon-checkmark ok hidden"></div>
            <select class="settingkeyvalueinput" type="select" name="settingkeyvalueinput" id="statussync">
                <option value="True"><?php p($l->t('Enabled')); ?></option>
                <option value="False" selected><?php p($l->t('Disabled')); ?></option>
            </select>
			<label style="display:none">
				<input class="settingkeyvalueinheritedcheckbox"  type="checkbox" />
				<span class="settingkeyvalueinheritedlabel"><?php p($l->t('Use default group settings'));?></span>
			</label>
            <input type="hidden" name="settingkeyname" value="statussync">
            <input type="hidden" name="settingkeytemplateid" value="0">
            <input type="hidden" name="settinggroupid" value="0">
            <input type="hidden" name="settingkeyid" value="401">
            <input type="hidden" name="settingkeykey" value="401">
        </div>
    </div>
    
</div>
