<?php
// render.php

$placeholder = isset($attributes['placeholder']) ? $attributes['placeholder'] : 'Buscar produtos...';
$button_text = isset($attributes['buttonText']) ? $attributes['buttonText'] : 'Buscar';
$is_instant = isset($attributes['isInstant']) ? (bool) $attributes['isInstant'] : false;

// Get current search query from URL
$current_search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

$shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/loja/');

$wrapper_attributes = get_block_wrapper_attributes(['class' => 'meili-search-bar-block']);
?>
<div <?php echo $wrapper_attributes; ?>
    data-wp-interactive="meiliRivera/search"
    data-wp-router-region="meili-search-bar"
    data-wp-context='<?php echo esc_attr(wp_json_encode(["isInstant" => $is_instant])); ?>'>
    
    <form class="meili-search-bar-form" 
          data-wp-on--submit="actions.submitSearch"
          action="<?php echo esc_url($shop_url); ?>" 
          method="get">
        
        <input type="search" 
               name="s"
               class="meili-search-input" 
               placeholder="<?php echo esc_attr($placeholder); ?>" 
               value="<?php echo esc_attr($current_search); ?>"
               <?php if ($is_instant) echo 'data-wp-on--input="actions.instantSearch"'; ?>
               autocomplete="off" />
               
        <button type="submit" class="meili-search-button">
            <?php echo esc_html($button_text); ?>
        </button>
    </form>
</div>
