<?php
/**
 * Plugin Name:       Meili Rivera
 * Description:       Integra a busca do WordPress e WooCommerce com Meilisearch usando Interactivity API.
 * Version:           0.0.2
 * Author:            RIVERA
 * Author URI:        https://pedrorivera.me
 * License:           GPL v2 or later
 * Text Domain:       meili-rivera
 * Requires at least: 6.5
 * Requires PHP:      8.1
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Meili_Rivera_Plugin
 *
 * Singleton wrapper to bootstrap the plugin.
 */
final class Meili_Rivera_Plugin
{
    const VERSION = '0.0.2';

    /**
     * @var Meili_Rivera_Plugin|null
     */
    private static $instance = null;

    private function __construct()
    {
        $this->define_constants();
        $this->load_dependencies();
        $this->init_plugin();

        // Settings Link
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'add_settings_link']);
    }

    private function define_constants()
    {
        define('MEILI_RIVERA_PATH', plugin_dir_path(__FILE__));
        define('MEILI_RIVERA_URL', plugin_dir_url(__FILE__));

        // Option keys
        define('MEILI_RIVERA_OPTION_ACF', 'meili_rivera_searchable_acf_fields');
        define('MEILI_RIVERA_OPTION_TAX', 'meili_rivera_searchable_taxonomies');
    }

    private function load_dependencies()
    {
        if (file_exists(MEILI_RIVERA_PATH . 'vendor/autoload.php')) {
            require_once MEILI_RIVERA_PATH . 'vendor/autoload.php';
        } else {
            add_action('admin_notices', function () {
                echo '<div class="notice notice-error"><p><strong>Meili Rivera:</strong> Dependências não encontradas. Execute <code>composer install</code>.</p></div>';
            });
        }

        // Include classes manually if not using PSR-4 autoloading for these specific WP-style classes
        // (Assuming we want to stick to the plan's file structure, but ideally we'd use Composer autoload)
        require_once MEILI_RIVERA_PATH . 'includes/class-meili-rivera-client.php';
        require_once MEILI_RIVERA_PATH . 'includes/class-meili-rivera-indexer.php';
        require_once MEILI_RIVERA_PATH . 'includes/class-meili-rivera-synchronizer.php';
        require_once MEILI_RIVERA_PATH . 'includes/class-meili-rivera-query-interceptor.php';

        if (is_admin()) {
            require_once MEILI_RIVERA_PATH . 'admin/class-meili-rivera-admin-page.php';
        }
    }

    private function init_plugin()
    {
        // Initialize Singletons
        Meili_Rivera_Client::instance();
        new Meili_Rivera_Synchronizer();

        // Register the global interceptor early
        global $meili_rivera_query_interceptor;
        $meili_rivera_query_interceptor = new Meili_Rivera_Query_Interceptor();

        if (is_admin()) {
            new Meili_Rivera_Admin_Page();
        }

        // Standard WP Hooks
        add_action('init', [$this, 'register_blocks']);

        // Pass PHP Constants to JS Store via wp_head to fail-safe dependency issues
        // Keeping this as it's the standard way to pass config to Interactivity API state in some patterns,
        // although wp_interactivity_state() is preferred. Let's use the latter for 6.9 standards.
        add_action('init', [$this, 'initialize_interactivity_state']);
    }

    public function register_blocks()
    {
        // Register Blocks using metadata (Standard WP 6.5+)
        $block_data = register_block_type(MEILI_RIVERA_PATH . 'build/blocks/filters');

        // Pass the saved taxonomies and ACF fields to the editor JS.
        // This enables the SelectControl dropdown to be populated from plugin settings.
        if ($block_data && $block_data->editor_script_handles) {
            foreach ($block_data->editor_script_handles as $handle) {
                wp_localize_script($handle, 'MeiliRiveraEditorData', [
                    'taxonomies' => get_option(MEILI_RIVERA_OPTION_TAX, ['product_cat', 'product_tag']),
                    'acfFields' => get_option(MEILI_RIVERA_OPTION_ACF, []),
                ]);
            }
        }

        // Force interactivity enqueuing as a safeguard
        add_action('wp_enqueue_scripts', function () {
            if (function_exists('wp_enqueue_interactivity')) {
                wp_enqueue_interactivity();
            } else {
                wp_enqueue_script('wp-interactivity');
            }
        });
    }

    public function initialize_interactivity_state()
    {
        if (function_exists('wp_interactivity_state')) {
            $host = defined('MEILI_HOST') ? MEILI_HOST : 'http://127.0.0.1:7700';
            $key = defined('MEILI_PUBLIC_KEY') ? MEILI_PUBLIC_KEY : (defined('MEILI_MASTER_KEY') ? MEILI_MASTER_KEY : '');
            $index = defined('MEILI_INDEX_NAME') ? MEILI_INDEX_NAME : 'wordpress_content';

            wp_interactivity_state('meiliRivera/search', [
                'config' => [
                    'host' => $host,
                    'publicKey' => $key,
                    'indexName' => $index,
                ]
            ]);
        }
    }

    public function add_settings_link($links)
    {
        $settings_link = '<a href="' . admin_url('admin.php?page=meili-rivera-admin') . '">' . __('Configurações', 'meili-rivera') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function log($message)
    {
        $log_file = dirname(__FILE__) . '/debug.log';
        $timestamp = date('Y-m-d H:i:s');
        $msg = "[{$timestamp}] " . (is_array($message) || is_object($message) ? json_encode($message) : $message) . PHP_EOL;
        file_put_contents($log_file, $msg, FILE_APPEND);
    }
}

// Kickoff
Meili_Rivera_Plugin::instance();
