<?php
/** @var $l \OCP\IL10N */
/** @var $_ array */

script('sendent', '3rdparty/jscolor/jscolor');
script('sendent', 'settings');
script('sendent', 'termsAgreement');
style('sendent', ['style']);
?>

<div class="tabmenu">
	<a class="tablink active" id="tab_sendent_general"><?php p($l->t('General')) ?></a>
	<a class="tablink" id="tab_sendent_settings"><?php p($l->t('Group Settings')) ?></a>
</div>

<div class="TermsAgreement">
	<?php print_unescaped($this->inc('sections/termsAgreement')); ?>
</div>

<div class="Settingspage" id="sendent_general">
	<?php print_unescaped($this->inc('sections/outlookAddon')); ?>

	<?php print_unescaped($this->inc('sections/defaultLicenseStatus')); ?>

	<?php print_unescaped($this->inc('sections/dependencies')); ?>
	
	<?php print_unescaped($this->inc('sections/retentionAssistant')); ?>

</div>

<div class="Settingspage" id="sendent_settings" style="display:none">

	<form class="form" method="post" id="settingsform">

	    <?php print_unescaped($this->inc('sections/groupsManagement')); ?>

		<div class="section">
			<h2>
        		<?php p($l->t('Settings for ')); ?>
					<span id="currentGroup">Default</span>
        		<?php p($l->t(' group ')); ?>
    		</h2>
			<div class="subtab-menu" style="border-bottom: 1px solid #ccc;">
				<a class="tablink active" id="tab_sendent_settings_license"><?php p($l->t('License')) ?></a>
				<a class="tablink" id="tab_sendent_settings_outlook"><?php p($l->t('Sendent for Outlook')) ?></a>
				<a class="tablink" id="tab_sendent_settings_teams"><?php p($l->t('Sendent for MS Teams')) ?></a>
			</div>
			<div class="SettingsGroup" id="sendent_settings_license">
				<?php print_unescaped($this->inc('sections/license')); ?>
			</div>
			<div class="SettingsGroup" id="sendent_settings_outlook" style="display:none">
	    		<?php print_unescaped($this->inc('sections/general')); ?>
		    	<?php print_unescaped($this->inc('sections/talk')); ?>
		    	<?php print_unescaped($this->inc('sections/archiving')); ?>
			    <?php print_unescaped($this->inc('sections/domainExceptions')); ?>
    			<?php print_unescaped($this->inc('sections/attachments')); ?>
			    <?php print_unescaped($this->inc('sections/fileHandling')); ?>
    			<?php print_unescaped($this->inc('sections/secureMail')); ?>
	    		<?php print_unescaped($this->inc('sections/guestAccounts')); ?>
    			<?php print_unescaped($this->inc('sections/advancedTheming')); ?>
			</div>
			<div class="SettingsGroup" id="sendent_settings_teams" style="display:none">
				<?php print_unescaped($this->inc('sections/teams')); ?>
			</div>
		</div>

	</form>

</div>
