<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Meili_Rivera_Synchronizer
 *
 * Hooks into WordPress events to sync products with Meilisearch.
 */
class Meili_Rivera_Synchronizer
{
    private $indexer;
    private $client;

    public function __construct()
    {
        $this->indexer = new Meili_Rivera_Indexer();
        $this->client = Meili_Rivera_Client::instance()->get_client();

        // Sync on save
        add_action('save_post_product', [$this, 'sync_on_save'], 10, 3);

        // Sync on deletion
        add_action('wp_trash_post', [$this, 'delete_from_index']);
        add_action('before_delete_post', [$this, 'delete_from_index']);
    }

    /**
     * Sync product when saved/updated.
     *
     * @param int $post_id
     * @param WP_Post $post
     * @param bool $update
     */
    public function sync_on_save($post_id, $post, $update)
    {
        // Avoid autosaves and revisions
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;
        if (wp_is_post_revision($post_id))
            return;

        // Check connection
        if (!$this->client)
            return;

        $index_name = defined('MEILI_INDEX_NAME') ? MEILI_INDEX_NAME : 'wordpress_content';
        $document = $this->indexer->build_product_document($post_id);

        if ($document) {
            try {
                // If product is published, index it.
                if ($post->post_status === 'publish') {
                    $this->client->index($index_name)->addDocuments([$document], 'id');
                } else {
                    // If status changes to something else (draft, private), remove it.
                    $this->delete_from_index($post_id);
                }
            } catch (\Exception $e) {
                error_log("Meili Rivera Sync Error (Save $post_id): " . $e->getMessage());
            }
        }
    }

    /**
     * Remove product from index.
     *
     * @param int $post_id
     */
    public function delete_from_index($post_id)
    {
        if (get_post_type($post_id) !== 'product')
            return;
        if (!$this->client)
            return;

        $index_name = defined('MEILI_INDEX_NAME') ? MEILI_INDEX_NAME : 'wordpress_content';

        try {
            $this->client->index($index_name)->deleteDocument($post_id);
        } catch (\Exception $e) {
            error_log("Meili Rivera Sync Error (Delete $post_id): " . $e->getMessage());
        }
    }
}
