<div class="section" id="defaultLicenseStatus">

    <h2>
        <?php p($l->t('Default license information')); ?>
    </h2>

    <div class="license-settings-setting-box subscriptionInformation">
        <div class="settingkeyvalue">
            <label class="licenselabel">
                <span class="templatesettingkeyname licenseitem">
                    <?php p($l->t('Subscription type')); ?></span>
            </label>
            <span class="statuskeyvalueinput statusitem" id="defaultlicensesubscriptiontype"></span>
        </div>
    </div>
    <div class="license-settings-setting-box subscriptionInformation" id="defaultlicensesupportedproductscontainer">
        <div class="settingkeyvalue">
            <label class="licenselabel">
                <span class="templatesettingkeyname licenseitem">
                    <?php p($l->t('Supported product(s)')); ?></span>
            </label>
            <span class="statuskeyvalueinput statusitem" id="defaultlicenseproducts"></span>
        </div>
    </div>
    <div class="license-settings-setting-box subscriptionInformation" id="defaultlicenselevelcontainer">
        <div class="settingkeyvalue">
            <label class="licenselabel">
                <span class="templatesettingkeyname licenseitem">
                    <?php p($l->t('Subscription level')); ?></span>
            </label>
            <span class="statuskeyvalueinput statusitem" id="defaultlicenselevel"><?php p($_['defaultLicenseLevel']); ?></span>
        </div>
    </div>
    <div class="license-settings-setting-box">
        <div class="settingkeyvalue">
            <label class="licenselabel">
                <span class="templatesettingkeyname licenseitem">
                    <?php p($l->t('Subscription status')); ?></span>
            </label>
            <span class="statuskeyvalueinput statusitem" id="defaultlicensestatus"><?php p($_['defaultLicenseStatus']); ?></span>
        </div>
    </div>

    <div class="license-settings-setting-box subscriptionInformation">
        <div class="settingkeyvalue">
            <label class="licenselabel">
                <span class="templatesettingkeyname licenseitem">
                    <?php p($l->t('Subscription expiration date')); ?></span>
            </label>
            <span class="statuskeyvalueinput statusitem" id="defaultlicenseexpires"><?php p($_['defaultLicenseExpirationDate']); ?></span>
        </div>
    </div>
    <div class="license-settings-setting-box subscriptionInformation">
        <div class="settingkeyvalue">
            <label class="licenselabel">
                <span class="templatesettingkeyname licenseitem">
                    <?php p($l->t('License last checked')); ?></span>
            </label>
            <span class="statuskeyvalueinput statusitem" id="defaultlicenselastcheck"><?php p($_['defaultLicenseLastCheck']); ?></span>
        </div>
    </div>

    <div class="labelFullWidth" style="color:slate-gray;font-style:italic;font-size:smaller">
        <p><?php p($l->t('To make changes to your license(s), please go to the "Group Settings" tab.')); ?> 
    </div>
	<div>
		<input type="button" id="btnDownloadLicenseReport" value="Download license report" style="margin-top:20px;">
    </div>
</div>
