<?php
if (!defined('ABSPATH')) {
    exit;
}

class Meili_Rivera_Router_Wrapper {
    public function __construct() {
        // Wrap WooCommerce main content
        add_action('woocommerce_before_main_content', [$this, 'start_wrapper'], 5);
        add_action('woocommerce_after_main_content', [$this, 'end_wrapper'], 50);
        
        // Wrap WooCommerce product collection block
        add_filter('render_block_woocommerce/product-collection', [$this, 'wrap_product_collection'], 10, 2);
        add_filter('render_block_woocommerce/all-products', [$this, 'wrap_product_collection'], 10, 2);
        
        // Wrap Core Query block
        add_filter('render_block_core/query', [$this, 'wrap_core_query'], 10, 2);
    }

    public function start_wrapper() {
        if (is_shop() || is_product_taxonomy() || is_search()) {
            echo '<div data-wp-interactive="meiliRivera/search" data-wp-router-region="meili-products-area">';
        }
    }

    public function end_wrapper() {
        if (is_shop() || is_product_taxonomy() || is_search()) {
            echo '</div>';
        }
    }

    public function wrap_product_collection($block_content, $block) {
        return '<div data-wp-interactive="meiliRivera/search" data-wp-router-region="meili-products-area">' . $block_content . '</div>';
    }

    public function wrap_core_query($block_content, $block) {
        // Only wrap if it's the main query or inherits query
        if (isset($block['attrs']['query']['inherit']) && $block['attrs']['query']['inherit']) {
            return '<div data-wp-interactive="meiliRivera/search" data-wp-router-region="meili-products-area">' . $block_content . '</div>';
        }
        return $block_content;
    }
}
