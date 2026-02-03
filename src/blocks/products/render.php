<?php
// render.php

// Ensure strict typing for attributes if needed
$attributes = isset($attributes) ? $attributes : [];
?>
<div data-wp-interactive="meiliRivera/search" data-wp-context='{ "limit": 12 }' data-wp-init="actions.init"
    class="meili-products-wrapper">
    <!-- Loading State -->
    <div data-wp-bind--hidden="!state.isLoading" class="meili-loading">
        Carregando...
    </div>

    <!-- Error State -->
    <div data-wp-bind--hidden="!state.error" class="meili-error" data-wp-text="state.error"
        style="color:red; display:none;"></div>

    <!-- Results List -->
    <div class="meili-products-grid" data-wp-bind--hidden="state.isLoading"
        style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
        <template data-wp-each--hit="state.results">
            <div class="product-card" style="border:1px solid #eee; padding: 10px;">
                <a data-wp-bind--href="context.hit.permalink">
                    <img data-wp-bind--src="context.hit.image" alt=""
                        style="width:100%; height:auto; aspect-ratio: 3/4; object-fit:cover;">
                </a>
                <h3 data-wp-text="context.hit.post_title">Create a Post</h3>
                <div class="price" data-wp-text="context.hit.price"></div>

                <!-- Example of rich taxonomy display -->
                <div class="tags" data-wp-each--tag="context.hit.product_cat_rich">
                    <span style="background:#eee; padding:2px 5px; font-size:0.8em; margin-right:5px;"
                        data-wp-text="context.tag.name"></span>
                </div>
            </div>
        </template>

        <!-- Empty State -->
        <div data-wp-bind--hidden="state.hasResults">
            Nenhum produto encontrado.
        </div>
    </div>
</div>