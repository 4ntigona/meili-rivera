<?php
// render.php
global $meili_rivera_query_interceptor;

// Get attributes
$taxonomy = isset($attributes['attribute']) ? $attributes['attribute'] : 'product_cat';
$label = isset($attributes['label']) ? $attributes['label'] : 'Filtros';
$show_count = isset($attributes['showCount']) ? (bool) $attributes['showCount'] : true;

// Active filters from URL
$active_filters = [];
if (isset($_GET[$taxonomy])) {
    $active_filters = explode(',', sanitize_text_field($_GET[$taxonomy]));
}

$facet_data = [];

// Use Meilisearch facets if interceptor has result (active Meili query)
if ($meili_rivera_query_interceptor && $meili_rivera_query_interceptor->get_meili_results()) {
    $results = $meili_rivera_query_interceptor->get_meili_results();
    if (isset($results['facets'][$taxonomy])) {
        foreach ($results['facets'][$taxonomy] as $slug => $count) {
            $name = $slug;

            if (taxonomy_exists($taxonomy)) {
                $term = get_term_by('slug', $slug, $taxonomy);
                if ($term && !is_wp_error($term)) {
                    $name = $term->name;
                }
            }

            $facet_data[] = [
                'name' => $name,
                'slug' => $slug,
                'count' => $count,
            ];
        }
    }
}

// Fallback to native terms when no Meili facets are available
if (empty($facet_data) && taxonomy_exists($taxonomy)) {
    $terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => true]);
    if (!is_wp_error($terms)) {
        foreach ($terms as $term) {
            $facet_data[] = [
                'name' => $term->name,
                'slug' => $term->slug,
                'count' => $term->count,
            ];
        }
    }
}
?>
<details <?php echo get_block_wrapper_attributes(['class' => 'meili-filter-accordion']); ?>
    data-wp-interactive="meiliRivera/search">
    <summary class="meili-filter-summary">
        <?php echo esc_html($label); ?>
    </summary>

    <?php if (empty($facet_data)): ?>
        <p class="meili-filter-empty">Nenhuma opção disponível.</p>
    <?php else: ?>
        <ul class="meili-filters-list">
            <?php foreach ($facet_data as $item):
                $context = [
                    'listName' => $taxonomy,
                    'value' => $item['slug'],
                ];
                ?>
                <li data-wp-context="<?php echo esc_attr(wp_json_encode($context)); ?>">
                    <label class="meili-filter-label">
                        <input type="checkbox" data-wp-on--change="actions.setFilter" <?php checked(in_array($item['slug'], $active_filters)); ?>>
                        <span class="meili-filter-name"><?php echo esc_html($item['name']); ?></span>
                        <?php if ($show_count): ?>
                            <span class="meili-filter-count">(<?php echo (int) $item['count']; ?>)</span>
                        <?php endif; ?>
                    </label>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</details>

<style>
    .meili-filter-accordion {
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        margin-block-end: 12px;
    }

    .meili-filter-summary {
        padding: 12px 16px;
        font-weight: 600;
        cursor: pointer;
        list-style: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
        user-select: none;
    }

    .meili-filter-summary::-webkit-details-marker {
        display: none;
    }

    .meili-filter-summary::after {
        content: '▶';
        font-size: 0.65em;
        transition: transform 0.2s ease;
        opacity: 0.5;
    }

    details[open]>.meili-filter-summary::after {
        transform: rotate(90deg);
    }

    .meili-filters-list {
        list-style: none;
        padding: 0 8px 8px;
        margin: 0;
        max-height: 420px;
        overflow-y: auto;
    }

    .meili-filter-label {
        display: flex;
        gap: 8px;
        align-items: center;
        cursor: pointer;
        padding: 5px 8px;
        border-radius: 4px;
        transition: background 0.15s;
    }

    .meili-filter-label:hover {
        background: #f8f8f8;
    }

    .meili-filter-name {
        flex: 1;
    }

    .meili-filter-count {
        opacity: 0.5;
        font-size: 0.85em;
    }

    .meili-filter-empty {
        padding: 8px 16px;
        opacity: 0.6;
        font-style: italic;
    }
</style>