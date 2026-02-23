<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WP-CLI commands for Meili Rivera.
 */
class Meili_Rivera_CLI
{
    /**
     * Sync all products to Meilisearch.
     *
     * ## EXAMPLES
     *
     *     wp meili-rivera sync
     *
     * @when after_wp_load
     */
    public function sync($args, $assoc_args)
    {
        WP_CLI::log('Starting Meilisearch sync...');

        // Ensure index settings are up to date before syncing
        Meili_Rivera_Client::instance()->ensure_filterable_attributes();

        $indexer = new Meili_Rivera_Indexer();
        
        // Get total products
        $count = wp_count_posts('product');
        $total = $count->publish;
        
        if ($total === 0) {
            WP_CLI::success('No products found to sync.');
            return;
        }

        WP_CLI::log("Found {$total} products to sync.");

        $batch_size = Meili_Rivera_Indexer::BATCH_SIZE;
        $total_pages = ceil($total / $batch_size);
        $total_processed = 0;

        $progress = \WP_CLI\Utils\make_progress_bar('Syncing products', $total);

        for ($page = 1; $page <= $total_pages; $page++) {
            $processed = $indexer->process_indexing_batch($page);
            $total_processed += $processed;
            $progress->tick($processed);
        }

        $progress->finish();

        WP_CLI::success("Successfully synced {$total_processed} products to Meilisearch.");
    }
}

if (class_exists('WP_CLI')) {
    WP_CLI::add_command('meili-rivera', 'Meili_Rivera_CLI');
}
