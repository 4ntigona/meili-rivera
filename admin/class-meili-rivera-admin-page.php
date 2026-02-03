<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Meili_Rivera_Admin_Page
 * 
 * Renders the settings page and handles AJAX batch indexing.
 */
class Meili_Rivera_Admin_Page
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'handle_form_actions']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);

        // AJAX Handlers
        add_action('wp_ajax_meili_rivera_get_total', [$this, 'ajax_get_total_products']);
        add_action('wp_ajax_meili_rivera_process_batch', [$this, 'ajax_process_batch']);
    }

    public function add_admin_menu()
    {
        add_management_page(
            'Meili Rivera',
            'Meili Rivera',
            'manage_options',
            'meili-rivera-admin',
            [$this, 'render_page']
        );
    }

    public function handle_form_actions()
    {
        if (!isset($_POST['meili_rivera_save_settings']))
            return;

        if (!check_admin_referer('meili_rivera_settings_action', 'meili_rivera_nonce')) {
            return;
        }

        if (isset($_POST['meili_rivera_acf'])) {
            $sanitized_acf = array_map('sanitize_text_field', $_POST['meili_rivera_acf']);
            update_option(MEILI_RIVERA_OPTION_ACF, $sanitized_acf);
        } else {
            update_option(MEILI_RIVERA_OPTION_ACF, []);
        }

        if (isset($_POST['meili_rivera_tax'])) {
            $sanitized_tax = array_map('sanitize_text_field', $_POST['meili_rivera_tax']);
            update_option(MEILI_RIVERA_OPTION_TAX, $sanitized_tax);
        } else {
            update_option(MEILI_RIVERA_OPTION_TAX, []);
        }

        add_settings_error('meili_rivera_messages', 'meili_rivera_message', 'Configurações salvas com sucesso.', 'success');
    }

    public function enqueue_scripts($hook)
    {
        if ($hook !== 'tools_page_meili-rivera-admin')
            return;

        // Simple inline JS for the batch processing, to avoid an extra file for now.
        // Ideally this would be in admin/js/admin-script.js
        wp_enqueue_script('jquery');
    }

    public function ajax_get_total_products()
    {
        check_ajax_referer('meili_rivera_batch_nonce', 'nonce');

        $count = wp_count_posts('product');
        $total = $count->publish;

        wp_send_json_success(['total' => $total]);
    }

    public function ajax_process_batch()
    {
        check_ajax_referer('meili_rivera_batch_nonce', 'nonce');

        $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;

        $indexer = new Meili_Rivera_Indexer();
        $processed = $indexer->process_indexing_batch($paged);

        wp_send_json_success(['processed' => $processed]);
    }

    public function render_page()
    {
        $acf_fields = get_option(MEILI_RIVERA_OPTION_ACF, []);
        $tax_fields = get_option(MEILI_RIVERA_OPTION_TAX, []);

        // Get all public taxonomies for products
        $product_taxonomies = get_object_taxonomies('product', 'objects');

        ?>
        <div class="wrap">
            <h1>Meili Rivera Configuration</h1>

            <?php settings_errors('meili_rivera_messages'); ?>

            <div class="card" style="max-width: 800px; padding: 20px; margin-top: 20px;">
                <h2>Status da Conexão</h2>
                <?php
                $client = Meili_Rivera_Client::instance();
                if ($client->is_connected()):
                    ?>
                    <p style="color: green;"><strong>✅ Conectado ao Meilisearch</strong></p>
                <?php else: ?>
                    <p style="color: red;"><strong>❌ Erro de conexão. Verifique MEILI_HOST e MEILI_MASTER_KEY no
                            wp-config.php</strong></p>
                <?php endif; ?>

                <hr>

                <h2>Indexação em Massa</h2>
                <p>Total de Produtos: <strong>
                        <?php echo wp_count_posts('product')->publish; ?>
                    </strong></p>

                <button id="meili-start-index" class="button button-primary">Re-indexar Todos os Produtos</button>

                <div id="meili-progress-wrap" style="display:none; margin-top: 15px;">
                    <div style="background: #f0f0f1; border: 1px solid #ccc; height: 20px; width: 100%;">
                        <div id="meili-progress-bar" style="background: #2271b1; height: 100%; width: 0%;"></div>
                    </div>
                    <p id="meili-status-text">Iniciando...</p>
                </div>
            </div>

            <form method="post" action="">
                <?php wp_nonce_field('meili_rivera_settings_action', 'meili_rivera_nonce'); ?>

                <div class="card" style="max-width: 800px; padding: 20px; margin-top: 20px;">
                    <h2>Campos Indexáveis</h2>

                    <h3>Taxonomias (Filtros)</h3>
                    <p>Selecione as taxonomias que devem ser enviadas ao Meilisearch para filtragem.</p>

                    <?php foreach ($product_taxonomies as $tax): ?>
                        <label style="display:block; margin-bottom: 5px;">
                            <input type="checkbox" name="meili_rivera_tax[]" value="<?php echo esc_attr($tax->name); ?>" <?php checked(in_array($tax->name, $tax_fields)); ?>>
                            <?php echo esc_html($tax->label); ?> (<code><?php echo esc_html($tax->name); ?></code>)
                        </label>
                    <?php endforeach; ?>

                    <h3>Campos ACF</h3>
                    <p>Insira as chaves dos campos ACF (ex: <code>autor_livro</code>) que deseja indexar.</p>
                    <!-- Simple text area for now, could be dynamic logic later -->
                    <?php
                    // Retrieve global ACF fields if possible to list them could be hard without groups.
                    // For now, listing previously saved.
                    ?>
                    <?php foreach ($acf_fields as $field): ?>
                        <div style="margin-bottom: 5px;">
                            <input type="text" name="meili_rivera_acf[]" value="<?php echo esc_attr($field); ?>" />
                        </div>
                    <?php endforeach; ?>
                    <div id="acf-wrapper"></div>
                    <button type="button" class="button" onclick="addAcfField()">Adicionar Campo</button>

                    <script>
                        function addAcfField() {
                            const wrapper = document.getElementById('acf-wrapper');
                            const div = document.createElement('div');
                            div.style.marginBottom = '5px';
                            div.innerHTML = '<input type="text" name="meili_rivera_acf[]" placeholder="field_key" />';
                            wrapper.appendChild(div);
                        }
                    </script>
                </div>

                <p class="submit">
                    <button type="submit" name="meili_rivera_save_settings" class="button button-primary">Salvar
                        Configurações</button>
                </p>
            </form>
        </div>

        <script>
            jQuery(document).ready(function ($) {
                $('#meili-start-index').on('click', function (e) {
                    e.preventDefault();
                    const btn = $(this);
                    btn.prop('disabled', true);

                    $('#meili-progress-wrap').show();
                    $('#meili-status-text').text('Calculando total...');

                    $.post(ajaxurl, {
                        action: 'meili_rivera_get_total',
                        nonce: '<?php echo wp_create_nonce('meili_rivera_batch_nonce'); ?>'
                        }, function (res) {
                        if (res.success) {
                            const total = res.data.total;
                            // Batch size is 50 defined in PHP
                            const batchSize = 50;
                            const totalPages = Math.ceil(total / batchSize);
                            let currentPage = 1;

                            function processNext() {
                                if (currentPage > totalPages) {
                                    $('#meili-status-text').text('Concluído!');
                                    btn.prop('disabled', false);
                                    return;
                                }

                                const pct = Math.round(((currentPage - 1) / totalPages) * 100);
                                $('#meili-progress-bar').css('width', pct + '%');
                                $('#meili-status-text').text('Processando lote ' + currentPage + ' de ' + totalPages + '...');

                                $.post(ajaxurl, {
                                    action: 'meili_rivera_process_batch',
                                    nonce: '<?php echo wp_create_nonce('meili_rivera_batch_nonce'); ?>',
                                    paged: currentPage
                                }, function (r) {
                                    currentPage++;
                                    processNext();
                                }).fail(function () {
                                    $('#meili-status-text').text('Erro no lote ' + currentPage);
                                    btn.prop('disabled', false);
                                });
                            }

                            processNext();

                        } else {
                            alert('Erro ao iniciar');
                            btn.prop('disabled', false);
                        }
                    });
                });
            });
        </script>
        <?php
    }
}
