<div id="generalsettings">
    <h1>
        <?php p($l->t('General')); ?>
    </h1>
    <div class="personal-settings-setting-box">

    <div class="settingkeyvalue">
        <label>
            <span class="templatesettingkeyname">
                <?php p($l->t('Path for default upload')); ?>
            </span>
        </label>
        <div class="status-error icon-error error hidden"></div>
        <div class="status-ok icon-checkmark ok hidden"></div>
        <input class="settingkeyvalueinput" type="text" name="settingkeyvalueinput" id="teams_pathuploadfiles" value=""
            placeholder="e.g: /MSTeams/Upload-Share/" autocomplete="on" autocapitalize="none" autocorrect="off">
        <label style="display:none">
            <input class="settingkeyvalueinheritedcheckbox" type="checkbox" />
            <span class="settingkeyvalueinheritedlabel">
                <?php p($l->t('Use default group settings')); ?>
            </span>
        </label>
        <input type="hidden" name="settingkeyname" value="teams_pathuploadfiles">
        <input type="hidden" name="settingkeytemplateid" value="2">
        <input type="hidden" name="settinggroupid" value="0">
        <input type="hidden" name="settingkeykey" value="501">
        <input type="hidden" name="settingkeyid" value="501">
    </div>
    </div>
</div>