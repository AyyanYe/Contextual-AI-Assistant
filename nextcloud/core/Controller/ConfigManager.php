<?php

namespace OCA\ContextualAI\Service;

use OCP\IConfig;

class ConfigManager {
    private $config;
    private $appName = 'contextual_ai';

    public function __construct(IConfig $config) {
        $this->config = $config;
    }

    public function setApiKey(string $key) {
        // Securely stores the key in the Nextcloud database
        $this->config->setAppValue($this->appName, 'api_key', $key);
    }

    public function getApiKey() {
        return $this->config->getAppValue($this->appName, 'api_key');
    }
}