<div class="settingTemplateDetailExclude section" id="msteams_defaultLicenseStatus">

    <h2>
        <?php p($l->t('Default Sendent for MS Teams license information')); ?>
    </h2>

    <div class="license-settings-setting-box">
        <div class="settingkeyvalue">
            <label class="licenselabel">
                <span class="templatesettingkeyname licenseitem">
                    <?php p($l->t('Subscription level')); ?></span>
            </label>
            <span class="statuskeyvalueinput statusitem" id="msteams_defaultlicenselevel"><?php p($_['defaultLicenseLevel']); ?></span>
        </div>
    </div>
    <div class="license-settings-setting-box">
        <div class="settingkeyvalue">
            <label class="licenselabel">
                <span class="templatesettingkeyname licenseitem">
                    <?php p($l->t('License status')); ?></span>
            </label>
            <span class="statuskeyvalueinput statusitem" id="msteams_defaultlicensestatus"><?php p($_['defaultLicenseStatus']); ?></span>
        </div>
    </div>

    <div class="license-settings-setting-box">
        <div class="settingkeyvalue">
            <label class="licenselabel">
                <span class="templatesettingkeyname licenseitem">
                    <?php p($l->t('License expiration date')); ?></span>
            </label>
            <span class="statuskeyvalueinput statusitem" id="msteams_defaultlicenseexpires"><?php p($_['defaultLicenseExpirationDate']); ?></span>
        </div>
    </div>
    <div class="license-settings-setting-box">
        <div class="settingkeyvalue">
            <label class="licenselabel">
                <span class="templatesettingkeyname licenseitem">
                    <?php p($l->t('License last check')); ?></span>
            </label>
            <span class="statuskeyvalueinput statusitem" id="msteams_defaultlicenselastcheck"><?php p($_['defaultLicenseLastCheck']); ?></span>
        </div>
    </div>

    <div class="labelFullWidth" style="color:slate-gray;font-style:italic;font-size:smaller">
        <p><?php p($l->t('To make changes to your license(s), please go to the "Group Settings" tab.')); ?> 
    </div>
	<div>
		<input type="button" id="msteams_btnDownloadLicenseReport" value="Download license report" style="margin-top:20px;">
    </div>
    
</div>
