<?php
// render.php
global $meili_rivera_query_interceptor;

// Get attributes
$taxonomy = isset($attributes['attribute']) ? $attributes['attribute'] : 'product_cat';
$label = isset($attributes['label']) ? $attributes['label'] : 'Filtros';

// If interceptor has results (active search), use Meili facets
$active_filters = [];
if (isset($_GET[$taxonomy])) {
    $active_filters = explode(',', sanitize_text_field($_GET[$taxonomy]));
}

$facet_data = [];

if ($meili_rivera_query_interceptor && $meili_rivera_query_interceptor->get_meili_results()) {
    $results = $meili_rivera_query_interceptor->get_meili_results();
    if (isset($results['facets'][$taxonomy])) {
        foreach ($results['facets'][$taxonomy] as $slug => $count) {
            $name = $slug;

            // Try to resolve name from slug if it's a taxonomy
            if (taxonomy_exists($taxonomy)) {
                $term = get_term_by('slug', $slug, $taxonomy);
                if ($term && !is_wp_error($term)) {
                    $name = $term->name;
                }
            }

            $facet_data[] = [
                'name' => $name,
                'slug' => $slug,
                'count' => $count
            ];
        }
    }
}

// Fallback to native terms if no Meili facets
if (empty($facet_data) && taxonomy_exists($taxonomy)) {
    $terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => true]);
    if (!is_wp_error($terms)) {
        foreach ($terms as $term) {
            $facet_data[] = [
                'name' => $term->name,
                'slug' => $term->slug,
                'count' => $term->count
            ];
        }
    }
}
?>
<div <?php echo get_block_wrapper_attributes(); ?> data-wp-interactive="meiliRivera/search">
    <h3><?php echo esc_html($label); ?></h3>

    <?php if (empty($facet_data)): ?>
        <p>Nenhuma opção disponível.</p>
    <?php else: ?>
        <ul class="meili-filters-list" style="list-style: none; padding: 0;">
            <?php foreach ($facet_data as $item):
                $context = [
                    'listName' => $taxonomy,
                    'value' => $item['slug']
                ];
                ?>
                <li data-wp-context="<?php echo esc_attr(wp_json_encode($context)); ?>">
                    <label style="display: flex; gap: 8px; align-items: center; cursor: pointer;">
                        <input type="checkbox" data-wp-on--change="actions.setFilter" <?php checked(in_array($item['slug'], $active_filters)); ?>>
                        <span><?php echo esc_html($item['name']); ?></span>
                        <span class="count" style="opacity: 0.6; font-size: 0.9em;">(<?php echo (int) $item['count']; ?>)</span>
                    </label>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>