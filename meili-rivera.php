<?php
/**
 * Plugin Name:       Meili Rivera
 * Description:       Integra a busca do WordPress e WooCommerce com Meilisearch usando Interactivity API.
 * Version:           0.0.1
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
    const VERSION = '0.0.1';

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

        if (is_admin()) {
            require_once MEILI_RIVERA_PATH . 'admin/class-meili-rivera-admin-page.php';
        }
    }

    private function init_plugin()
    {
        // Initialize Singletons
        Meili_Rivera_Client::instance();
        new Meili_Rivera_Synchronizer();

        if (is_admin()) {
            new Meili_Rivera_Admin_Page();
        }

        add_action('init', [$this, 'register_blocks']);
    }

    public function register_blocks()
    {
        // Register Blocks
        register_block_type(MEILI_RIVERA_PATH . 'build/blocks/products');
        register_block_type(MEILI_RIVERA_PATH . 'build/blocks/filters');
        register_block_type(MEILI_RIVERA_PATH . 'build/blocks/pagination');
    }

    public function add_settings_link($links)
    {
        $settings_link = '<a href="' . admin_url('tools.php?page=meili-rivera-admin') . '">' . __('Configurações', 'meili-rivera') . '</a>';
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
}

// Kickoff
Meili_Rivera_Plugin::instance();
