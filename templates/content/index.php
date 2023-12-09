<?php
/** @var $l \OCP\IL10N */
/** @var $_ array */

script('sendent', '3rdparty/jscolor/jscolor');
script('sendent', 'settings');
script('sendent', 'termsAgreement');
style('sendent', ['style']);
?>

<div class="tabmenu">
	<a class="tablink active" id="tab_sendent_general">General</a>
	<a class="tablink" id="tab_sendent_settings">Group Settings</a>
	<a class="tablink" id="tab_sendent_ms-teams-settings">MS Teams Settings</a>
</div>

<div class="TermsAgreement">
	<?php print_unescaped($this->inc('sections/termsAgreement')); ?>
</div>

<div class="Settingspage" id="sendent_general">
	<?php print_unescaped($this->inc('sections/outlookAddon')); ?>

	<?php print_unescaped($this->inc('sections/defaultLicenseStatus')); ?>
	
	<?php print_unescaped($this->inc('sections/msteamsDefaultLicenseStatus')); ?>

	<?php print_unescaped($this->inc('sections/dependencies')); ?>
	
	<?php print_unescaped($this->inc('sections/retentionAssistant')); ?>

</div>

<div class="Settingspage" id="sendent_settings" style="display:none">

	<form class="form" method="post" id="settingsform">

	    <?php print_unescaped($this->inc('sections/groupsManagement')); ?>

		<?php print_unescaped($this->inc('sections/license')); ?>

    	<?php print_unescaped($this->inc('sections/general')); ?>

	    <?php print_unescaped($this->inc('sections/talk')); ?>

	    <?php print_unescaped($this->inc('sections/domainExceptions')); ?>

    	<?php print_unescaped($this->inc('sections/attachments')); ?>

	    <?php print_unescaped($this->inc('sections/fileHandling')); ?>

    	<?php print_unescaped($this->inc('sections/secureMail')); ?>

	    <?php print_unescaped($this->inc('sections/guestAccounts')); ?>

    	<?php print_unescaped($this->inc('sections/advancedTheming')); ?>

	</form>

</div>
<div class="Settingspage" id="sendent_ms-teams-settings" style="display:none">

	<form class="form" method="post" id="settingsform">
		<?php print_unescaped($this->inc('sections/msteamsLicense')); ?>
	</form>

</div>