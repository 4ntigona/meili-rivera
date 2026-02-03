# Meili Rivera

**Integração Premium do Meilisearch com WordPress/WooCommerce para Pedro & João Editores.**

Este plugin substitui a busca padrão do WordPress por uma solução ultra-veloz usando Meilisearch, com suporte nativo a blocos (Gutenberg) e Interactivity API.

## Funcionalidades

- **Indexação Inteligente**: Sincroniza produtos, atributos e taxonomias em tempo real.
- **Busca Instantânea**: Resultados em milissegundos.
- **Interatividade**: Filtros e paginação sem recarregamento de página (SPA-like) usando Interactivity API.
- **Configuração Fina**: Selecione quais taxonomias e campos ACF indexar via Admin.

## Instalação

1. Clone este repositório em `wp-content/plugins/meili-rivera`.
2. Execute `composer install` para instalar as dependências.
3. Ative o plugin no Painel WordPress.
4. Defina as constantes no `wp-config.php`:

   ```php
   define('MEILI_HOST', 'http://127.0.0.1:7700');
   define('MEILI_MASTER_KEY', 'sua_master_key');
   define('MEILI_INDEX_NAME', 'pej_livros');
   ```

## Desenvolvimento

### Requisitos

- PHP 8.1+
- WordPress 6.5+ (Interactivity API)
- Meilisearch Server v1.6+

### Comandos Úteis

- `composer install`: Instala SDK PHP.
- `npm install`: Instala dependências JS (frontend).
- `npm start`: Compila blocos em modo watch.
- `npm run build`: Compila blocos para produção.
