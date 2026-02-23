# Changelog

Todas as mudanças notáveis ​​neste projeto serão documentadas neste arquivo.

O formato é baseado em [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
e este projeto adere ao [PrideVer](https://pridever.org/).

## [0.1.0] - 2026-02-23

### Adicionado

- **Aumento do Limite de Resultados**: O limite de resultados totais do Meilisearch (`maxTotalHits`) foi aumentado de 1000 para 100000, permitindo a exibição de todos os produtos na página de catálogo.
- **Busca Textual em Filtros**: Adicionado um campo de busca textual dentro do bloco de filtros (usando a Interactivity API) para filtrar as opções de facetas em tempo real, ignorando acentos e maiúsculas/minúsculas.
- **Aumento do Limite de Facetas**: O limite padrão de facetas do Meilisearch (`maxValuesPerFacet`) foi aumentado de 100 para 3000, permitindo a exibição completa de taxonomias com muitos itens (ex: autoria, organização).

### Corrigido

- **Build do Webpack**: Corrigido o problema onde o `view.js` era deletado durante o processo de build duplo (editor e view) ao definir `clean: false` no `webpack.config.js`.

## [0.0.3] - 2026-02-23

### Adicionado

- **Filter Label Resolution**: O bloco `meili-rivera/filters` agora resolve slugs de taxonomia para nomes legíveis (ex: `educacao-publica` → `Educação Pública`) usando `get_term_by` durante o render PHP.
- **Filter-the-Filters (Facetagem Dinâmica)**: Ao ativar um filtro, os outros filtros passam a mostrar apenas as opções com produtos em comum. Comportamento padrão agora em toda instalação do bloco.

### Corrigido

- **Paginação no WooCommerce Catalog Block**: Removida a sobrescrita de `paged=1` no interceptor de query. O interceptor agora anula apenas o `offset` da SQL, preservando o estado interno de paginação dos blocos nativos do WooCommerce.
- **404 ao Filtrar em Página Paginada**: A ação `setFilter` em `store.js` agora remove o segmento `/page/N/` do path antes de navegar, garantindo que qualquer filtro sempre redirecione para a página 1 dos resultados.

## [0.0.2] - 2026-02-23

### Corrigido

- **Critical**: Corrigido erro de "Página Não Encontrada" (404) em páginas de produtos individuais. O interceptor de query agora ignora consultas singulares (ID ou slug), permitindo que o WordPress resolva permalinks nativamente enquanto mantém a busca em massa via Meilisearch para listagens e buscas.

## [0.0.1] - 2026-02-03

### Adicionado

- Estrutura inicial do plugin `meili-rivera`.
- Integração Backend com Meilisearch PHP SDK.
- Classes Core: `Meili_Rivera_Client`, `Meili_Rivera_Indexer`, `Meili_Rivera_Synchronizer`.
- Página de Admin para indexação em massa e configuração de campos.
- Suporte dinâmico para indexação de Taxonomias e campos ACF.
- **Interactivity API**: Store global `meiliRivera/search`.
- **Blocos**: `meili-rivera/products`, `meili-rivera/filters`, `meili-rivera/pagination`.
- Estrutura de pastas e build scripts (`@wordpress/scripts`).

### Corrigido

- Injeção de configurações do Meilisearch (Host/Key) no Frontend.
- Inicialização do Store da Interactivity API nos blocos.
