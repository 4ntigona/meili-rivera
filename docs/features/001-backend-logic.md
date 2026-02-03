# Feature: Backend Logic Port

**Branch**: `feature/interactivity-api`
**Status**: Implemented

## Descrição

Port da lógica PHP do antigo plugin `wp-meilisearch-rivera` para o novo `meili-rivera`.

## Mudanças Principais

1. **Renomeação de Classes**:
   - `Wp_Meili_Search_Plugin` -> `Meili_Rivera_Plugin`
   - `Meili_Indexer` -> `Meili_Rivera_Indexer`
   - `Meili_Synchronizer` -> `Meili_Rivera_Synchronizer`

2. **Abstração de Taxonomias**:
   - A lógica hardcoded para `pa_autoria-livro` etc. foi removida.
   - Agora, o `Indexer` consulta a opção `meili_rivera_searchable_taxonomies`.
   - Se a opção estiver vazia, um filtro `meili_rivera_default_taxonomies` fornece padrões.
   - O Admin GUI permite selecionar quais taxonomias enviar para o índice.

3. **Admin Page Modernizada**:
   - Interface limpa para monitorar status da conexão.
   - Barra de progresso para indexação em lote.

## Sentimento (PrideVer)

**DEFAULT**. O código é sólido e limpo, refatorando dívida técnica (hardcoded arrays) em configuração dinâmica.
