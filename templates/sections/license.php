<?php
/** @var $l \OCP\IL10N */
/** @var $_ array */
?>
<div class="settingTemplateDetailExclude section" id="license">
    <h1>
        <?php p($l->t('License information')); ?>
    </h1>
    <div class="license-settings-setting-box" id="licenseMessage">
        <div class="settingkeyvalue">

            <div class="labelFullWidth" id="lblLicenseMessage"
                style="display:grid;float:left;text-align:left;color:slate-gray;font-style:italic;font-size:smaller">
                <p>Find out how to configure your license <a style="color:blue;text-decoration:underline"  href="https://sendent.freshdesk.com/support/solutions/articles/80000592300-configuring-your-license">here</a>.</p>
                <?php p($l->t('You only need a license key if you are using one of the paid plans of Sendent. If you don’t have a valid license key anymore, you will automatically be downgraded to Sendent Free.')); ?>
                <br><br>
            </div>
            <input type="hidden" name="settingkeyname" value="licenseMessage">
            <input type="hidden" name="settingkeykey" value="1000">
            <input type="hidden" name="settingkeyid" value="1000">
        </div>
    </div>
    <div class="license-settings-setting-box">
        <div class="settingkeyvalue">
            <label>
                <span class="templatesettingkeyname">
                    <?php p($l->t('License key')); ?></span>
            </label>
            <div class="status-error icon-error error hidden"></div>
            <div class="status-ok icon-checkmark ok hidden"></div>
            <input class="settingkeyvalueinput" type="text" name="licensekeyvalueinput" id="licensekey" value=""
                placeholder="Put your licensekey here" autocomplete="on" autocapitalize="none" autocorrect="off">
            <input type="hidden" name="settingkeyname" value="licensekey">
            <input type="hidden" name="settingkeykey" value="900">
            <input type="hidden" name="settingkeyid" value="900">
        </div>
    </div>
    <div class="license-settings-setting-box">
        <div class="settingkeyvalue">
            <label>
                <span class="templatesettingkeyname">
                    <?php p($l->t('Licensed email address')); ?></span>
            </label>
            <div class="status-error icon-error error hidden"></div>
            <div class="status-ok icon-checkmark ok hidden"></div>
            <input class="settingkeyvalueinput" type="email" name="licensekeyvalueinput" id="licenseEmail" value=""
                placeholder="Enter email address" autocomplete="on" autocapitalize="none" autocorrect="off">
            <input type="hidden" name="settingkeyname" value="licenseEmail">
            <input type="hidden" name="settingkeykey" value="901">
            <input type="hidden" name="settingkeyid" value="901">
        </div>
    </div>
    <div class="license-settings-setting-box">
        <div class="settingkeyvalue">
            <label>
                <span class="templatesettingkeyname"></span>
            </label>
            <div class="status-error icon-error error hidden"></div>
            <div class="status-ok icon-checkmark ok hidden"></div>
            <input type="button" id="btnLicenseActivation" value="Activate license">
            <input type="hidden" name="settingkeyname" value="licensebutton">
            <input type="hidden" name="settingkeykey" value="900">
            <input type="hidden" name="settingkeyid" value="900">
        </div>
    </div>
    <div class="license-settings-setting-box">
        <div class="settingkeyvalue">
            <label>
                <span class="templatesettingkeyname"></span>
            </label>
            <div class="status-error icon-error error hidden"></div>
            <div class="status-ok icon-checkmark ok hidden"></div>
            <input type="button" id="btnClearLicense" value="Clear license">
            <input type="hidden" name="settingkeyname" value="licenseClearButton">
            <input type="hidden" name="settingkeykey" value="900">
            <input type="hidden" name="settingkeyid" value="900">
        </div>
    </div>

    <div class="license-settings-setting-box">
        <div class="settingkeyvalue">
            <label class="licenselabel">
                <span class="templatesettingkeyname licenseitem">
                    <?php p($l->t('Subscription level')); ?></span>
            </label>
            <div class="status-error icon-error error hidden"></div>
            <div class="status-ok icon-checkmark ok hidden"></div>
            <span class="licensekeyvalueinput licenseitem" name="licensekeyvalueinput" id="licenselevel"></span>
            <input type="hidden" name="settingkeyname" value="licenselevel">
            <input type="hidden" name="settingkeykey" value="902">
            <input type="hidden" name="settingkeyid" value="902">
        </div>
    </div>
    <div class="license-settings-setting-box">
        <div class="settingkeyvalue">
            <label class="licenselabel">
                <span class="templatesettingkeyname licenseitem">
                    <?php p($l->t('License status')); ?></span>
            </label>
            <div class="status-error icon-error error hidden"></div>
            <div class="status-ok icon-checkmark ok hidden"></div>
            <span class="licensekeyvalueinput licenseitem" name="licensekeyvalueinput" id="licensestatus"></span>
            <input type="hidden" name="settingkeyname" value="licensestatus">
            <input type="hidden" name="settingkeykey" value="903">
            <input type="hidden" name="settingkeyid" value="903">
        </div>
    </div>

    <div class="license-settings-setting-box">
        <div class="settingkeyvalue">
            <label class="licenselabel">
                <span class="templatesettingkeyname licenseitem">
                    <?php p($l->t('License expiration date')); ?></span>
            </label>
            <div class="status-error icon-error error hidden"></div>
            <div class="status-ok icon-checkmark ok hidden"></div>
            <span class="licensekeyvalueinput licenseitem" name="licensekeyvalueinput" id="licenseexpires"></span>
            <input type="hidden" name="settingkeyname" value="licenseexpires">
            <input type="hidden" name="settingkeykey" value="905">
            <input type="hidden" name="settingkeyid" value="905">
        </div>
    </div>
    <div class="license-settings-setting-box">
        <div class="settingkeyvalue">
            <label class="licenselabel">
                <span class="templatesettingkeyname licenseitem">
                    <?php p($l->t('License last check')); ?></span>
            </label>
            <div class="status-error icon-error error hidden"></div>
            <div class="status-ok icon-checkmark ok hidden"></div>
            <span class="licensekeyvalueinput licenseitem" name="licensekeyvalueinput" id="licenselastcheck"></span>
            <input type="hidden" name="settingkeyname" value="licenselastcheck">
            <input type="hidden" name="settingkeykey" value="904">
            <input type="hidden" name="settingkeyid" value="904">
        </div>
    </div>
    <br><br>
    <h1>
        <?php p($l->t('Outlook Add-in version information')); ?>
</h1>
    <div class="license-settings-setting-box">
        <div class="settingkeyvalue">
            <label class="licenselabel">
                <span class="templatesettingkeyname statusitem">
                    <?php p($l->t('Latest Outlook Add-in version')); ?></span>
            </label>
            <div class="status-error icon-error error hidden"></div>
            <div class="status-ok icon-checkmark ok hidden"></div>
            <span class="statuskeyvalueinput statusitem" name="statuskeyvalueinput" id="latestVSTOVersion"></span>
            <input type="hidden" name="settingkeyname" value="latestVSTOVersion">
            <input type="hidden" name="settingkeykey" value="905">
            <input type="hidden" name="settingkeyid" value="905">
        </div>
    </div>
    <div class="license-settings-setting-box">
        <div class="settingkeyvalue">
            <label class="licenselabel">
                <span class="templatesettingkeyname statusitem">
                    <?php p($l->t('Releasedate')); ?></span>
            </label>
            <div class="status-error icon-error error hidden"></div>
            <div class="status-ok icon-checkmark ok hidden"></div>
            <span class="statuskeyvalueinput statusitem" name="statuskeyvalueinput" id="latestVSTOVersionReleaseDate"></span>
            <input type="hidden" name="settingkeyname" value="latestVSTOVersionReleaseDate">
            <input type="hidden" name="settingkeykey" value="905">
            <input type="hidden" name="settingkeyid" value="905">
        </div>
    </div>
    <div class="license-settings-setting-box">
        <div class="settingkeyvalue">
            <label class="licenselabel">
                <span class="templatesettingkeyname statusitem">
                    <?php p($l->t('Downloadlink')); ?></span>
            </label>
            <div class="status-error icon-error error hidden"></div>
            <div class="status-ok icon-checkmark ok hidden"></div>
            <a style="color:blue; text-decoration:underline;" class="statuskeyvalueinput statusitem" name="statuskeyvalueinput" id="latestVSTOVersionDownload"><?php p($l->t('Download')); ?></a> <br>
            <input type="hidden" name="settingkeyname" value="latestVSTOVersionDownload">
            <input type="hidden" name="settingkeykey" value="905">
            <input type="hidden" name="settingkeyid" value="905">
        </div>
    </div>
    <div class="license-settings-setting-box">
        <div class="settingkeyvalue">
            <label class="licenselabel">
                <span class="templatesettingkeyname statusitem">
                    <?php p($l->t('Releasenotes')); ?></span>
            </label>
            <div class="status-error icon-error error hidden"></div>
            <div class="status-ok icon-checkmark ok hidden"></div>
            <a style="color:blue; text-decoration:underline;" class="statuskeyvalueinput statusitem" name="statuskeyvalueinput" id="latestVSTOVersionReleaseNotes"><?php p($l->t('Open')); ?></a> <br>
            <input type="hidden" name="settingkeyname" value="latestVSTOVersionReleaseNotes">
            <input type="hidden" name="settingkeykey" value="905">
            <input type="hidden" name="settingkeyid" value="905">
        </div>
    </div>
    <!-- <div class="license-settings-setting-box">
        <div class="settingkeyvalue">
            <label>
                <span class="templatesettingkeyname"></span>
            </label>
            <div class="status-error icon-error error hidden"></div>
            <div class="status-ok icon-checkmark ok hidden"></div>
            <input type="button" id="btnSupportButton" value="Contact support">
            <input type="hidden" name="settingkeyname" value="supportButton">
            <input type="hidden" name="settingkeykey" value="1100">
            <input type="hidden" name="settingkeyid" value="1100">
        </div>
    </div> -->


    <div class="licensesection test" id="licensesection">

    </div>
</div>
