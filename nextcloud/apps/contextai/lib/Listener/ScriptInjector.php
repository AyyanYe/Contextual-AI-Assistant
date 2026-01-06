<?php
namespace OCA\Contextai\Listener;

use OCP\EventDispatcher\IEventDispatcher;
use OCP\EventDispatcher\IEventListener;
use OCP\AppFramework\Http\Events\BeforeTemplateRenderedEvent;
use OCP\Util;

class ScriptInjector implements IEventListener {
    public function handle(\OCP\EventDispatcher\Event $event): void {
        if (!$event instanceof BeforeTemplateRenderedEvent) {
            return;
        }

        // Force load the script using the internal Util class
        // This bypasses some app checks
        Util::addScript('contextai', 'sidebar');
    }
}