<?php
// render.php
$attribute = isset($attributes['attribute']) ? $attributes['attribute'] : 'product_cat';
$label = isset($attributes['label']) ? $attributes['label'] : 'Filtro';
?>
<div data-wp-interactive="meiliRivera/search" data-wp-context='{ "listName": "<?php echo esc_attr($attribute); ?>" }'
    class="meili-filter-group" style="margin-bottom: 20px;">
    <h4>
        <?php echo esc_html($label); ?>
    </h4>

    <!-- Render list of available facets from distribution or predefined -->
    <!-- Ideally, we iterate over facetDistribution[attribute] -->

    <div data-wp-if="state.facetDistribution[context.listName]">
        <ul class="meili-facet-list">
            <template data-wp-each--facet="state.facetDistribution[context.listName]">
                <li data-wp-context='{ "value": "context.facet" }'>
                    <label>
                        <input type="checkbox" data-wp-on--change="actions.setFilter"
                            data-wp-bind--checked="state.facets[context.listName]?.includes(context.value)" />
                        <span data-wp-text="context.value"></span>
                        <!-- <span data-wp-text="context.facet.count"></span> (Count needs object iteration logic) -->
                    </label>
                </li>
            </template>
        </ul>
    </div>
</div>