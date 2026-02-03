# AGENTS.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Overview

Meili Rivera is a WordPress/WooCommerce plugin that integrates Meilisearch for ultra-fast product search. It uses the WordPress Interactivity API for reactive, SPA-like frontend behavior without page reloads.

**Critical Rule:** All search queries must pass through Meilisearch first—never fall back to WordPress's native WP_Query for search functionality.

## Development Commands

```bash
# Backend dependencies (PHP SDK)
composer install

# Frontend dependencies
npm install

# Build blocks for production
npm run build

# Watch mode during development
npm start

# Update WordPress packages
npm run packages-update
```

## Local Meilisearch Setup

```bash
docker compose up -d
```

This starts Meilisearch at `http://127.0.0.1:7700` with default key `masterKey123`.

## Configuration

Add to `wp-config.php`:
```php
define('MEILI_HOST', 'http://127.0.0.1:7700');
define('MEILI_MASTER_KEY', 'your_master_key');
define('MEILI_INDEX_NAME', 'pej_livros');
```

## Architecture

### Backend (PHP)

- **`meili-rivera.php`** — Plugin bootstrap, singleton pattern, registers blocks
- **`includes/class-meili-rivera-client.php`** — Singleton Meilisearch client wrapper
- **`includes/class-meili-rivera-indexer.php`** — Transforms WC_Product into Meilisearch documents. Handles taxonomy and ACF field indexing based on admin settings
- **`includes/class-meili-rivera-synchronizer.php`** — Hooks into `save_post_product`, `wp_trash_post` to keep index synced
- **`admin/class-meili-rivera-admin-page.php`** — Settings page with AJAX batch indexing

### Frontend (Interactivity API)

- **`src/store.js`** — Global reactive state: query, facets, results, pagination. All search actions happen here via client-side Meilisearch JS SDK
- **`src/blocks/`** — Three Gutenberg blocks:
  - `products/` — Renders search results grid
  - `filters/` — Dynamic faceted filters based on `facetDistribution`
  - `pagination/` — Page navigation

### Data Flow

1. **Indexing:** Admin triggers re-index → AJAX calls `Meili_Rivera_Indexer::process_indexing_batch()` in batches of 50
2. **Real-time sync:** Product save/delete hooks trigger immediate index updates
3. **Search:** Client-side JS queries Meilisearch directly → store updates → DOM reacts via `data-wp-*` directives

### Document Structure

Each product document includes:
- Base fields: `id`, `post_title`, `permalink`, `image`, `price`, `on_sale`, `post_date_timestamp`
- Taxonomies (configurable): stored as arrays for faceting (`product_cat`) plus `_rich` variants with full term data
- ACF fields (configurable via admin)

## Block Development

Blocks use Interactivity API directives:
- `data-wp-interactive="meiliRivera/search"` — Binds to the store namespace
- `data-wp-each--item="state.results"` — Loops over results
- `data-wp-text`, `data-wp-bind--*` — Reactive bindings
- `data-wp-on--change` — Event handlers

Example from `render.php`:
```php
<template data-wp-each--hit="state.results">
    <h3 data-wp-text="context.hit.post_title"></h3>
</template>
```

## Option Keys

- `meili_rivera_searchable_acf_fields` — ACF fields to index
- `meili_rivera_searchable_taxonomies` — Taxonomies to index as facets

## Requirements

- PHP 8.1+
- WordPress 6.5+ (Interactivity API)
- WooCommerce
- Meilisearch Server v1.6+
