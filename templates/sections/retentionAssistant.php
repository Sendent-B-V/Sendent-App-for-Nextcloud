<?php
/** @var $l \OCP\IL10N */
/** @var $_ array */

script('sendent', 'retentionAssistant');
?>
<div id="sendent-retention-assistant" class="section">
    <h2><?php p($l->t('Retention Assistant')); ?> <a style="color:darkgray;font-size:large;margin-left:10px" href="https://help.sendent.com/sendent-app-for-nextcloud-outlook-ms-teams/how-to-configure-the-retention-assistant-and-time">i</a></h2>

    <p class="assistant-text"><?php p($l->t('With the help of our Retention Assistant, you can easily set up each step of the process for cleaning up unused content.')); ?></p>
     
     <a style="margin-top:25px; display:inline-block" href="#assistant" class="assistantButton button hidden"><?php p($l->t('Start assistant')); ?></a>
     

    <p class="sendent-is-loading"><br /><span class="icon-loading" style="padding-left:32px;margin-right:0.5em;"></span> <?php p($l->t('Assistant is loading...')); ?></p>

    <ul style="margin-top:25px" class="sendent-steps hidden">
        <li class="RetentionListItem" data-action="automatedTaggingApp"><?php p($l->t('Check automated tagging app')); ?></li>
        <li class="RetentionListItem" data-action="workflow"><?php p($l->t('Check workflow for upload tagging')); ?></li>
        <li class="RetentionListItem" data-action="retentionApp"><?php p($l->t('Check retention app')); ?></li>
        <li class="RetentionListItem" data-action="retention"><?php p($l->t('Check retention rules')); ?></li>
    </ul>
</div>
