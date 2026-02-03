<?php

if (!defined('ABSPATH')) {
    exit;
}

use MeiliSearch\Client;

/**
 * Class Meili_Rivera_Client
 *
 * Singleton wrapper for the Meilisearch PHP Client.
 */
class Meili_Rivera_Client
{
    private static $instance = null;
    private $client = null;

    private function __construct()
    {
        $host = defined('MEILI_HOST') ? MEILI_HOST : 'http://127.0.0.1:7700';
        $key = defined('MEILI_MASTER_KEY') ? MEILI_MASTER_KEY : '';

        try {
            $this->client = new Client($host, $key);
        } catch (\Exception $e) {
            error_log('Meili Rivera Connection Error: ' . $e->getMessage());
        }
    }

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @return Client|null
     */
    public function get_client()
    {
        return $this->client;
    }

    public function is_connected()
    {
        if (!$this->client) {
            return false;
        }
        try {
            return $this->client->isHealthy();
        } catch (\Exception $e) {
            return false;
        }
    }
}
