# Changelog

Todas as mudanças notáveis ​​neste projeto serão documentadas neste arquivo.

O formato é baseado em [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
e este projeto adere ao [PrideVer](https://pridever.org/).

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
