# Feature: Frontend Interactivity API

**Branch**: `feature/interactivity-api`
**Status**: Implemented

## Descrição

Implementação dos blocos Gutenberg usando `@wordpress/interactivity` para criar uma experiência de busca "Single Page Application" (SPA).

## Componentes

### 1. Store Global (`src/store.js`)

- Namespace: `meiliRivera/search`
- Gerencia estado: `query`, `facets`, `results`, `pagination`.
- Comunica com MeiliSearch via `meilisearch-js`.

### 2. Blocos

- **Products**: Renderiza o grid de resultados. Usa diretivas `data-wp-each` para loop eficiente.
- **Filters**: Renderiza checkboxes de facetas. Usa `data-wp-context` para escopo de lista.
- **Pagination**: Botões de navegação vinculados a `actions.navigate`.

## Decisões Técnicas

- **Client-Side Search**: A busca é feita diretamente do navegador para o MeiliSearch, reduzindo carga no servidor WP.
- **SSR Base**: Os blocos possuem `render.php` para SEO e carga inicial (embora a busca dispare no init por enquanto).

## Sentimento (PrideVer)

**PROUD**. A transição de Vanilla JS CustomEvents para Interactivity API resultou em um código muito mais declarativo e fácil de manter.
