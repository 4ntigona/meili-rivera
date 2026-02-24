<?php
// render.php

$placeholder = isset($attributes['placeholder']) ? $attributes['placeholder'] : 'Buscar produtos...';
$button_text = isset($attributes['buttonText']) ? $attributes['buttonText'] : 'Buscar';
$is_instant = isset($attributes['isInstant']) ? (bool) $attributes['isInstant'] : false;

// Styling attributes
$input_padding = isset($attributes['inputPadding']) ? $attributes['inputPadding'] : '';
$input_font_size = isset($attributes['inputFontSize']) ? $attributes['inputFontSize'] : '';
$input_color = isset($attributes['inputColor']) ? $attributes['inputColor'] : '';

$button_padding = isset($attributes['buttonPadding']) ? $attributes['buttonPadding'] : '';
$button_font_size = isset($attributes['buttonFontSize']) ? $attributes['buttonFontSize'] : '';
$button_color = isset($attributes['buttonColor']) ? $attributes['buttonColor'] : '';
$button_bg_color = isset($attributes['buttonBgColor']) ? $attributes['buttonBgColor'] : '';

// Icon attributes
$show_icon = isset($attributes['showIcon']) ? (bool) $attributes['showIcon'] : false;
$icon_url = isset($attributes['iconUrl']) ? $attributes['iconUrl'] : '';
$icon_position = isset($attributes['iconPosition']) ? $attributes['iconPosition'] : 'inside-right';

// Build inline styles
$input_style = '';
if ($input_padding) $input_style .= "padding: {$input_padding}; ";
if ($input_font_size) $input_style .= "font-size: {$input_font_size}; ";
if ($input_color) $input_style .= "color: {$input_color}; ";

$button_style = '';
if ($button_padding) $button_style .= "padding: {$button_padding}; ";
if ($button_font_size) $button_style .= "font-size: {$button_font_size}; ";
if ($button_color) $button_style .= "color: {$button_color}; ";
if ($button_bg_color) $button_style .= "background-color: {$button_bg_color}; ";

// Get current search query from URL
$current_search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

$shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/loja/');

$wrapper_attributes = get_block_wrapper_attributes(['class' => 'meili-search-bar-block']);
?>
<div <?php echo $wrapper_attributes; ?>
    data-wp-interactive="meiliRivera/search"
    data-wp-router-region="meili-search-bar"
    data-wp-context='<?php echo esc_attr(wp_json_encode(["isInstant" => $is_instant])); ?>'>
    
    <form class="meili-search-bar-form <?php echo esc_attr("icon-position-{$icon_position}"); ?>" 
          data-wp-on--submit="actions.submitSearch"
          action="<?php echo esc_url($shop_url); ?>" 
          method="get">
        
        <div class="meili-search-input-wrapper">
            <?php if ($show_icon && $icon_url && $icon_position === 'inside-left') : ?>
                <img src="<?php echo esc_url($icon_url); ?>" class="meili-search-icon left" alt="Search" />
            <?php endif; ?>

            <input type="search" 
                   name="s"
                   class="meili-search-input" 
                   placeholder="<?php echo esc_attr($placeholder); ?>" 
                   value="<?php echo esc_attr($current_search); ?>"
                   <?php if ($is_instant) echo 'data-wp-on--input="actions.instantSearch"'; ?>
                   autocomplete="off"
                   style="<?php echo esc_attr($input_style); ?>" />

            <?php if ($show_icon && $icon_url && $icon_position === 'inside-right') : ?>
                <img src="<?php echo esc_url($icon_url); ?>" class="meili-search-icon right" alt="Search" />
            <?php endif; ?>
        </div>
               
        <button type="submit" class="meili-search-button" style="<?php echo esc_attr($button_style); ?>">
            <?php if ($show_icon && $icon_url && $icon_position === 'button') : ?>
                <img src="<?php echo esc_url($icon_url); ?>" class="meili-search-button-icon" alt="Search" />
            <?php endif; ?>
            <?php echo esc_html($button_text); ?>
        </button>
    </form>
</div>
