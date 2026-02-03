<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Meili_Rivera_Indexer
 * 
 * Handles transformation of WordPress Posts/Products into MeiliSearch Documents.
 * Supports dynamic taxonomy indexing.
 */
class Meili_Rivera_Indexer
{
    private $client;
    const BATCH_SIZE = 50;

    public function __construct()
    {
        $this->client = Meili_Rivera_Client::instance()->get_client();
    }

    /**
     * Build product document.
     * 
     * @param int $post_id
     * @return array|null
     */
    public function build_product_document($post_id)
    {
        $product = wc_get_product($post_id);
        if (!$product || $product->get_status() !== 'publish') {
            return null;
        }

        $title = get_the_title($post_id);
        $formatted_title = wordwrap($title, 14, "\n", false);

        // Base Document
        $document = [
            'id' => $post_id,
            'post_title' => $title,
            'permalink' => get_permalink($post_id),
            'image' => get_the_post_thumbnail_url($post_id, 'large') ?: 'https://placehold.co/300x400/white/1559ed?text=' . urlencode($formatted_title),

            // Pricing & sorting fields
            'price' => (float) $product->get_price(),
            'regular_price' => (float) $product->get_regular_price(),
            'sale_price' => $product->get_sale_price() ? (float) $product->get_sale_price() : null,
            'on_sale' => $product->is_on_sale(),
            'post_date_timestamp' => $product->get_date_created() ? $product->get_date_created()->getTimestamp() : 0,
        ];

        // 1. Index WooCommerce Attributes
        foreach ($product->get_attributes() as $attribute) {
            $document[$attribute->get_name()] = $product->get_attribute($attribute->get_name());
        }

        // 2. Index Dynamic Taxonomies (Abstracted Logic)
        // Get taxonomies from options or default to filtering hooked array
        $taxonomies_to_index = get_option(MEILI_RIVERA_OPTION_TAX, []);

        // Allow developers to hook in default taxes if option is empty
        if (empty($taxonomies_to_index)) {
            $taxonomies_to_index = apply_filters('meili_rivera_default_taxonomies', [
                'product_cat',
                'product_tag'
            ]);
        }

        foreach ($taxonomies_to_index as $tax) {
            $terms = wc_get_product_terms($post_id, $tax);
            if (!is_wp_error($terms) && !empty($terms)) {
                // Field for Faceting (Strings)
                $document[$tax] = wp_list_pluck($terms, 'name');

                // Field for Display (Rich Data)
                // We append '_rich' to the key to avoid collision with the faceted array
                $document[$tax . '_rich'] = array_map(function ($term) {
                    return [
                        'id' => $term->term_id,
                        'name' => $term->name,
                        'slug' => $term->slug,
                        'link' => get_term_link($term)
                    ];
                }, $terms);
            }
        }

        // 3. Index ACF Fields (Abstracted Logic)
        $acf_fields = get_option(MEILI_RIVERA_OPTION_ACF, []);
        if (!empty($acf_fields) && function_exists('get_field')) {
            foreach ($acf_fields as $field_name) {
                $field_value = get_field($field_name, $post_id);
                if ($field_value) {
                    // Normalize array values to string for simpler searching, or keep as array if needed.
                    // For now, mirroring old logic: implode if array.
                    $document[$field_name] = is_array($field_value) ? implode(' ', $field_value) : $field_value;
                }
            }
        }

        return $document;
    }

    /**
     * Process batch indexing.
     */
    public function process_indexing_batch($paged = 1)
    {
        if (!$this->client)
            return 0;

        $index_name = defined('MEILI_INDEX_NAME') ? MEILI_INDEX_NAME : 'wordpress_content';

        $args = [
            'post_type' => 'product',
            'posts_per_page' => self::BATCH_SIZE,
            'paged' => $paged,
            'post_status' => 'publish',
        ];

        $query = new WP_Query($args);

        if (!$query->have_posts()) {
            return 0;
        }

        $documents = [];
        foreach ($query->posts as $post) {
            $doc = $this->build_product_document($post->ID);
            if ($doc) {
                $documents[] = $doc;
            }
        }

        if (!empty($documents)) {
            // Update filterable attributes settings on the fly? 
            // Better to do it in the Admin Page save action.
            // Here we just add documents.
            $this->client->index($index_name)->addDocuments($documents, 'id');
            return count($documents);
        }

        return 0;
    }
}
