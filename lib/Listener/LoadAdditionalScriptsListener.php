<?php

namespace OCA\Sendent\Listener;

use OCA\Files\Event\LoadAdditionalScriptsEvent;
use OCA\Files_Sharing\Event\BeforeTemplateRenderedEvent;
use OCA\Sendent\AppInfo\Application;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;

class LoadAdditionalScriptsListener implements IEventListener {
	public function handle(Event $event): void {
		if (!($event instanceof LoadAdditionalScriptsEvent)
			&& !($event instanceof BeforeTemplateRenderedEvent)) {
			return;
		}

		\OCP\Util::addScript(Application::APPID, 'filelist');
	}
}
