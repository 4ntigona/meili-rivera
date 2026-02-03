# Arquitetura do Meili Rivera

## Visão Geral

O **Meili Rivera** é um plugin nativo do WordPress projetado para delegar a busca de produtos WooCommerce para uma instância Meilisearch.

### Estrutura de Diretórios

- **`meili-rivera.php`**: Bootstrap. Inicializa singletons e define constantes.
- **`includes/`**: Lógica de Backend.
  - `class-meili-rivera-client.php`: Gerencia conexão HTTP com Meilisearch.
  - `class-meili-rivera-indexer.php`: Transforma `WP_Post/WC_Product` em JSON documentos.
  - `class-meili-rivera-synchronizer.php`: Ouve hooks (`save_post`, `delete_post`) para manter o índice atualizado.
- **`admin/`**: Interface administrativa.
  - `class-meili-rivera-admin-page.php`: Renderiza a página de ferramentas e processa AJAX de indexação em lote.
- **`src/`**: Frontend (Interactivity API).
  - `store.js`: Estado global (busca, filtros, resultados).
  - `blocks/`: Blocos Gutenberg (Products, Filters, Pagination).

## Fluxo de Dados

1. **Indexação**:
   - Admin clica em "Re-indexar".
   - AJAX chama `Meili_Rivera_Indexer::process_indexing_batch()`.
   - Produtos são convertidos para JSON com campos de faceta e display.
   - Enviados via POST para o Meilisearch.

2. **Busca (Frontend)**:
   - `Products Block` inicia e hidrata o store.
   - Usuário digita ou filtra.
   - `store.js` dispara `actions.search()`.
   - Cliente JS Meilisearch faz request direto ao servidor Meilisearch (Client-side search).
   - Resultados atualizam o DOM via Interactivity API (`data-wp-text`, `data-wp-each`).
