<?php
declare(strict_types=1);

use OCA\Contextai\Listener\ScriptInjector;
use OCP\AppFramework\Http\Events\BeforeTemplateRenderedEvent;

$dispatcher = \OC::$server->getEventDispatcher();

// Connect the "Before Render" event to our Injector script
$dispatcher->addListener(
    BeforeTemplateRenderedEvent::class,
    ScriptInjector::class
);