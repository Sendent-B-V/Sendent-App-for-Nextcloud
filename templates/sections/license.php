<div id="license">
    <h1>
        <?php p($l->t('License information')); ?>
    </h1>
    <div class="license-settings-setting-box" id="licenseMessage">
        <div class="settingkeyvalue">
            <div class="labelFullWidth" id="lblLicenseMessage" style="display:grid;float:left;text-align:left;color:slate-gray;font-style:italic;font-size:smaller">
                <p>Find out how to configure your license <a style="color: var(--color-primary-element);text-decoration:underline"  href="https://sendent.freshdesk.com/support/solutions/articles/80000592300-configuring-your-license">here</a>.</p>
                <?php p($l->t('You only need a license key if you are using one of the paid plans of Sendent. If you don’t have a valid license key anymore, you will automatically be downgraded to Sendent Free.')); ?>
            </div>
            <input type="hidden" name="settingkeyname" value="licenseMessage"/>
            <input type="hidden" name="settingkeykey" value="1000"/>
            <input type="hidden" name="settingkeyid" value="1000"/>
        </div>
    </div>
    <div class="license-settings-setting-box">
        <div class="settingkeyvalue">
            <label>
                <span class="templatesettingkeyname">
                    <?php p($l->t('License email address')); ?>
                </span>
            </label>
            <div class="status-error icon-error error hidden"></div>
            <div class="status-ok icon-checkmark ok hidden"></div>
            <input class="settingkeyvalueinput" type="email" name="licensekeyvalueinput" id="licenseEmail" value=""
                placeholder="Enter email address" autocomplete="on" autocapitalize="none" autocorrect="off"/>
            <label style="display:none">
                <input class="settingkeyvalueinheritedcheckbox"  type="checkbox" />
                <span class="settingkeyvalueinheritedlabel"><?php p($l->t('Use default group settings'));?></span>
            </label>
            <input type="hidden" name="settingkeyname" value="licenseEmail"/>
            <input type="hidden" name="settingkeykey" value="901"/>
            <input type="hidden" name="settingkeyid" value="901"/>
        </div>
    </div>
    <div class="license-settings-setting-box">
        <div class="settingkeyvalue">
            <label>
                <span class="templatesettingkeyname">
                    <?php p($l->t('License key')); ?>
                </span>
            </label>
            <div class="status-error icon-error error hidden"></div>
            <div class="status-ok icon-checkmark ok hidden"></div>
            <input class="settingkeyvalueinput" type="text" name="licensekeyvalueinput" id="licensekey" value=""
                placeholder="Put your license key here" autocomplete="on" autocapitalize="none" autocorrect="off"/>
            <label style="display:none">
                <input class="settingkeyvalueinheritedcheckbox"  type="checkbox" />
                <span class="settingkeyvalueinheritedlabel"><?php p($l->t('Use default group settings'));?></span>
            </label>
            <input type="hidden" name="settingkeyname" value="licensekey"/>
            <input type="hidden" name="settingkeykey" value="900"/>
            <input type="hidden" name="settingkeyid" value="900"/>
        </div>
    </div>
    <div class="license-settings-setting-box">
        <div class="settingkeyvalue">
            <label>
                <span class="templatesettingkeyname"></span>
            </label>
            <div class="status-error icon-error error hidden"></div>
            <div class="status-ok icon-checkmark ok hidden"></div>
            <input type="button" id="btnLicenseActivation" value="Activate license"/>
            <input type="hidden" name="settingkeyname" value="licensebutton"/>
            <input type="hidden" name="settingkeykey" value="900"/>
            <input type="hidden" name="settingkeyid" value="900"/>
        </div>
    </div>
    <div class="license-settings-setting-box">
        <div class="settingkeyvalue">
            <label>
                <span class="templatesettingkeyname"></span>
            </label>
            <div class="status-error icon-error error hidden"></div>
            <div class="status-ok icon-checkmark ok hidden"></div>
            <input type="button" id="btnClearLicense" value="Clear license"/>
            <input type="hidden" name="settingkeyname" value="licenseClearButton"/>
            <input type="hidden" name="settingkeykey" value="900"/>
            <input type="hidden" name="settingkeyid" value="900"/>
        </div>
    </div>
    <div class="license-settings-setting-box subscriptionInformation">
        <div class="settingkeyvalue">
            <label class="licenselabel">
                <span class="templatesettingkeyname licenseitem ">
                    <?php p($l->t('License last checked')); ?>
                </span>
            </label>
            <div class="status-error icon-error error hidden"></div>
            <div class="status-ok icon-checkmark ok hidden"></div>
            <span style="font-style:italic;color:none !important;" class="licensekeyvalueinput licenseitem" name="licensekeyvalueinput" id="licenselastcheck"></span>
            <input type="hidden" name="settingkeyname" value="licenselastcheck"/>
            <input type="hidden" name="settingkeykey" value="904"/>
            <input type="hidden" name="settingkeyid" value="904"/>
        </div>
    </div>
    <div class="license-settings-setting-box subscriptionInformation" style="margin-top:20px">
        <div class="settingkeyvalue">
            <label class="licenselabel">
                <span class="templatesettingkeyname licenseitem">
                    <?php p($l->t('Subscription type')); ?>
                </span>
            </label>
            <div class="status-error icon-error error hidden"></div>
            <div class="status-ok icon-checkmark ok hidden"></div>
            <span class="licensekeyvalueinput licenseitem" name="licensekeyvalueinput" id="licenseSubscriptionType"></span>
            <input type="hidden" name="settingkeyname" value="licenseSubscriptionType"/>
            <input type="hidden" name="settingkeykey" value="902"/>
            <input type="hidden" name="settingkeyid" value="902"/>
        </div>
    </div>
    <div class="license-settings-setting-box subscriptionInformation" id="defaultlicensesupportedproductscontainer">
        <div class="settingkeyvalue">
            <label class="licenselabel">
                <span class="templatesettingkeyname licenseitem">
                    <?php p($l->t('Supported product(s)')); ?>
                </span>
            </label>
            <div class="status-error icon-error error hidden"></div>
            <div class="status-ok icon-checkmark ok hidden"></div>
            <span class="licensekeyvalueinput licenseitem" name="licensekeyvalueinput" id="licenseProducts"></span>
            <input type="hidden" name="settingkeyname" value="licenseProducts"/>
            <input type="hidden" name="settingkeykey" value="902"/>
            <input type="hidden" name="settingkeyid" value="902"/>
        </div>
    </div>
    <div class="license-settings-setting-box subscriptionInformation" id="licenselevelcontainer">
        <div class="settingkeyvalue">
            <label class="licenselabel">
                <span class="templatesettingkeyname licenseitem">
                    <?php p($l->t('Subscription level')); ?>
                </span>
            </label>
            <div class="status-error icon-error error hidden"></div>
            <div class="status-ok icon-checkmark ok hidden"></div>
            <span class="licensekeyvalueinput licenseitem" name="licensekeyvalueinput" id="licenselevel"></span>
            <input type="hidden" name="settingkeyname" value="licenselevel"/>
            <input type="hidden" name="settingkeykey" value="902"/>
            <input type="hidden" name="settingkeyid" value="902"/>
        </div>
    </div>
    <div class="license-settings-setting-box statusItem">
        <div class="settingkeyvalue">
            <label class="licenselabel">
                <span class="templatesettingkeyname licenseitem">
                    <?php p($l->t('Subscription status')); ?>
                </span>
            </label>
            <div class="status-error icon-error error hidden"></div>
            <div class="status-ok icon-checkmark ok hidden"></div>
            <span class="licensekeyvalueinput licenseitem" name="licensekeyvalueinput" id="licensestatus"></span>
            <input type="hidden" name="settingkeyname" value="licensestatus"/>
            <input type="hidden" name="settingkeykey" value="903"/>
            <input type="hidden" name="settingkeyid" value="903"/>
        </div>
    </div>
    <div class="license-settings-setting-box subscriptionInformation">
        <div class="settingkeyvalue">
            <label class="licenselabel">
                <span class="templatesettingkeyname licenseitem">
                    <?php p($l->t('Subscription expiration date')); ?>
                </span>
            </label>
            <div class="status-error icon-error error hidden"></div>
            <div class="status-ok icon-checkmark ok hidden"></div>
            <span class="licensekeyvalueinput licenseitem" name="licensekeyvalueinput" id="licenseexpires"></span>
            <input type="hidden" name="settingkeyname" value="licenseexpires"/>
            <input type="hidden" name="settingkeykey" value="905"/>
            <input type="hidden" name="settingkeyid" value="905"/>
        </div>
    </div>
</div>
