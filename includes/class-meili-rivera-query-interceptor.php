<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Meili_Rivera_Query_Interceptor
 * 
 * Intercepts WP_Query and redirects it to Meilisearch.
 */
class Meili_Rivera_Query_Interceptor
{
    private $query_results = [];

    public function __construct()
    {
        add_action('pre_get_posts', [$this, 'intercept_query'], 999);
        add_filter('found_posts', [$this, 'override_found_posts'], 999, 2);
    }

    public function intercept_query($query)
    {
        // Never intercept in admin or for non-frontend queries
        if (is_admin()) {
            return;
        }

        $query_hash = spl_object_hash($query);
        Meili_Rivera_Plugin::log("[Ariadne] --- THREAD START --- Query: {$query_hash}");

        // Identify if this is a product-related query (Core or WooCommerce)
        $post_types = (array) $query->get('post_type');
        $is_product_query = in_array('product', $post_types) || $query->get('product');

        // Determine search term from native 's' param or generic GET
        $search_term = $query->get('s') ?: (isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '');

        $taxonomies = get_option(MEILI_RIVERA_OPTION_TAX, []);
        if (empty($taxonomies)) {
            $taxonomies = apply_filters('meili_rivera_default_taxonomies', ['product_cat', 'product_tag']);
        }
        $acf_fields = get_option(MEILI_RIVERA_OPTION_ACF, []);

        $active_filters = [];

        // Extract filters from URL
        foreach ($taxonomies as $tax) {
            if (isset($_GET[$tax]) && !empty($_GET[$tax])) {
                $active_filters[$tax] = explode(',', sanitize_text_field($_GET[$tax]));
            }
        }
        foreach ($acf_fields as $acf) {
            if (isset($_GET[$acf]) && !empty($_GET[$acf])) {
                $active_filters[$acf] = explode(',', sanitize_text_field($_GET[$acf]));
            }
        }

        $is_woo_shop = function_exists('is_shop') && is_shop();
        $is_woo_tax = function_exists('is_product_taxonomy') && is_product_taxonomy();
        $is_main = $query->is_main_query();

        // Avoid intercepting single post/product queries
        $is_singular = $query->is_singular() || $query->get('p') || $query->get('name') || $query->get('product');

        // Should we intercept?
        // We intercept if:
        // 1. It's a product query AND it's NOT singular (e.g. it's a list/archive or blocks)
        // 2. OR it's the main query and it's a search, shop page, or product taxonomy archive
        $should_intercept = ($is_product_query && !$is_singular) || ($is_main && ($query->is_search() || $is_woo_shop || $is_woo_tax));

        Meili_Rivera_Plugin::log("[Ariadne] Decision: is_prod=" . ($is_product_query ? 'Y' : 'N') . ", is_singular=" . ($is_singular ? 'Y' : 'N') . ", is_main=" . ($is_main ? 'Y' : 'N') . ", is_search=" . ($query->is_search() ? 'Y' : 'N') . " -> Result=" . ($should_intercept ? 'YES' : 'NO'));

        if (!$should_intercept) {
            return;
        }

        // If viewing an archive/taxonomy natively, implicitly filter it
        if ($is_woo_tax || $query->is_category() || $query->is_tag() || $query->is_tax()) {
            $qo = $query->get_queried_object();
            if ($qo && isset($qo->taxonomy) && isset($qo->slug)) {
                $tax = $qo->taxonomy;
                if (!isset($active_filters[$tax])) {
                    $active_filters[$tax] = [];
                }
                if (!in_array($qo->slug, $active_filters[$tax])) {
                    $active_filters[$tax][] = $qo->slug;
                    Meili_Rivera_Plugin::log("[Ariadne] Implicit taxonomy filter: {$tax} = {$qo->slug}");
                }
            }
        }

        // Connect to Meilisearch
        $client = Meili_Rivera_Client::instance()->get_client();
        if (!$client) {
            Meili_Rivera_Plugin::log("[Ariadne] ERROR: Meilisearch client not available.");
            return;
        }

        $index_name = defined('MEILI_INDEX_NAME') ? MEILI_INDEX_NAME : 'wordpress_content';
        try {
            $index = $client->index($index_name);

            // Build filter string for Meilisearch
            $filter_groups = [];
            foreach ($active_filters as $key => $values) {
                $group = [];
                foreach ($values as $val) {
                    $val = addslashes(trim($val));
                    $group[] = "{$key} = \"{$val}\"";
                }
                if (!empty($group)) {
                    $filter_groups[] = '(' . implode(' OR ', $group) . ')';
                }
            }
            $filter_string = implode(' AND ', $filter_groups);

            // Pagination parameters
            $page = max(1, $query->get('paged', 1));

            // FSE Core Query Block pagination support (query-X-page=Y)
            $fse_page_key = null;
            foreach ($_GET as $key => $value) {
                if (strpos($key, 'query-') === 0 && strpos($key, '-page') !== false) {
                    $page = absint($value);
                    $fse_page_key = $key;
                    break;
                }
            }

            $posts_per_page = $query->get('posts_per_page');
            if (!$posts_per_page || $posts_per_page == -1) {
                $posts_per_page = get_option('posts_per_page', 12);
            }

            // Overrides
            if (isset($_GET['limit'])) {
                $posts_per_page = absint($_GET['limit']);
                Meili_Rivera_Plugin::log("[Ariadne] Limit override: {$posts_per_page}");
            }

            $search_params = [
                'limit' => (int) $posts_per_page,
                'offset' => ($page - 1) * (int) $posts_per_page,
                'facets' => ['*']
            ];

            if (!empty($filter_string)) {
                $search_params['filter'] = $filter_string;
            }

            Meili_Rivera_Plugin::log("[Ariadne] Meili Search: term='{$search_term}', page={$page}, offset={$search_params['offset']}, filter='{$filter_string}'");

            $response = $index->search($search_term, $search_params);

            // Save results
            $raw_response = $response->getRaw();
            $total = isset($raw_response['estimatedTotalHits']) ? $raw_response['estimatedTotalHits'] : (isset($raw_response['totalHits']) ? $raw_response['totalHits'] : count($response->getHits()));

            Meili_Rivera_Plugin::log("[Ariadne] Meili Result: hits=" . count($response->getHits()) . ", total={$total}");

            $this->query_results[$query_hash] = [
                'total' => $total,
                'hits' => $response->getHits(),
                'facets' => $response->getFacetDistribution()
            ];

            $post_ids = [];
            foreach ($response->getHits() as $hit) {
                if (isset($hit['id'])) {
                    $post_ids[] = (int) $hit['id'];
                }
            }

            if (empty($post_ids)) {
                Meili_Rivera_Plugin::log("[Ariadne] Forcing empty result (post__in=[0])");
                $query->set('post__in', [0]);
            } else {
                Meili_Rivera_Plugin::log("[Ariadne] Setting post__in: " . implode(',', $post_ids));
                $query->set('post__in', $post_ids);
                $query->set('orderby', 'post__in');

                // FIX: Pagination Subsetting
                // Since we are already providing the IDs for the current page,
                // we must tell WP not to try to offset/limit them again.
                // We force offset=0 to ensure WP takes ALL provided IDs from post__in.
                // We DO NOT touch 'paged' or 'posts_per_page' here, because they are 
                // needed for blocks to render correct pagination links and state.
                $query->set('offset', 0);
                Meili_Rivera_Plugin::log("[Ariadne] WP Override: offset=0. Original paged=" . $query->get('paged') . ", PPP=" . $query->get('posts_per_page'));
            }

            // Clear markers that would trigger slow SQL
            $query->set('s', '');
            $query->set('tax_query', []);

        } catch (\Exception $e) {
            Meili_Rivera_Plugin::log("[Ariadne] EXCEPTION: " . $e->getMessage());
        }
    }

    public function override_found_posts($found_posts, $query)
    {
        $query_hash = spl_object_hash($query);
        if (isset($this->query_results[$query_hash])) {
            Meili_Rivera_Plugin::log("[Ariadne] found_posts override for {$query_hash}: returning " . $this->query_results[$query_hash]['total']);
            return $this->query_results[$query_hash]['total'];
        }
        return $found_posts;
    }

    public function get_meili_results()
    {
        if (empty($this->query_results)) {
            return null;
        }
        return end($this->query_results);
    }
}
