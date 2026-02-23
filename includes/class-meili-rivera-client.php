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

    /**
     * Update the index settings to ensure all searchable taxonomies and ACF fields
     * are configured as filterableAttributes in Meilisearch.
     */
    public function ensure_filterable_attributes()
    {
        if (!$this->is_connected()) {
            return;
        }

        $taxonomies = get_option(MEILI_RIVERA_OPTION_TAX, []);
        if (empty($taxonomies)) {
            $taxonomies = apply_filters('meili_rivera_default_taxonomies', ['product_cat', 'product_tag']);
        }
        $acf_fields = get_option(MEILI_RIVERA_OPTION_ACF, []);

        $filterable = array_merge($taxonomies, $acf_fields);
        // Also add native price fields for potential future range filters
        $filterable[] = 'price';
        $filterable[] = 'on_sale';

        $filterable = array_unique($filterable);

        $index_name = defined('MEILI_INDEX_NAME') ? MEILI_INDEX_NAME : 'wordpress_content';

        try {
            Meili_Rivera_Plugin::log('[Meili Rivera] Updating filterableAttributes: ' . json_encode($filterable));
            $this->client->index($index_name)->updateFilterableAttributes(array_values($filterable));
        } catch (\Exception $e) {
            Meili_Rivera_Plugin::log('Meili Settings Error: ' . $e->getMessage());
        }
    }
}
