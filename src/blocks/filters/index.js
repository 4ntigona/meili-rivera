import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls, PanelColorSettings } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl, ToggleControl } from '@wordpress/components';
import metadata from './block.json';

// Build dropdown options from the plugin's saved settings.
// MeiliRiveraEditorData is provided via wp_localize_script in meili-rivera.php.
function buildOptions() {
    const data = window.MeiliRiveraEditorData || {};
    const taxonomies = data.taxonomies || ['product_cat', 'product_tag'];
    const acfFields = data.acfFields || [];

    const options = [
        ...taxonomies.map((slug) => ({ label: slug, value: slug })),
        ...acfFields.map((field) => ({ label: `${field} (ACF)`, value: field })),
    ];

    return options.length > 0 ? options : [{ label: 'product_cat', value: 'product_cat' }];
}

registerBlockType(metadata.name, {
    edit: function ({ attributes, setAttributes }) {
        const blockProps = useBlockProps();
        const options = buildOptions();

        return (
            <div {...blockProps}>
                <InspectorControls>
                    <PanelBody title="Configurações do Título" initialOpen={true}>
                        <TextControl
                            label="Rótulo (Título do Filtro)"
                            value={attributes.label}
                            onChange={(val) => setAttributes({ label: val })}
                        />
                        <ToggleControl
                            label="Aberto por padrão"
                            checked={attributes.isOpen}
                            onChange={(val) => setAttributes({ isOpen: val })}
                        />
                        <TextControl
                            label="Tamanho da Fonte (ex: 16px, 1.2rem)"
                            value={attributes.titleFontSize}
                            onChange={(val) => setAttributes({ titleFontSize: val })}
                        />
                        <TextControl
                            label="Espaçamento (Padding, ex: 10px 15px)"
                            value={attributes.titlePadding}
                            onChange={(val) => setAttributes({ titlePadding: val })}
                        />
                    </PanelBody>

                    <PanelColorSettings
                        title="Cores do Título"
                        initialOpen={false}
                        colorSettings={[
                            {
                                value: attributes.titleColor,
                                onChange: (colorValue) => setAttributes({ titleColor: colorValue }),
                                label: 'Cor do Texto',
                            },
                        ]}
                    />

                    <PanelBody title="Configurações dos Itens" initialOpen={false}>
                        <SelectControl
                            label="Taxonomia / Campo"
                            help="Selecione a opção configurada no plugin Meili Rivera."
                            value={attributes.attribute}
                            options={options}
                            onChange={(val) => setAttributes({ attribute: val })}
                        />
                        <ToggleControl
                            label="Exibir Contagem de Itens"
                            checked={attributes.showCount}
                            onChange={(val) => setAttributes({ showCount: val })}
                        />
                        <ToggleControl
                            label="Exibir Hierarquia"
                            checked={attributes.showHierarchy}
                            onChange={(val) => setAttributes({ showHierarchy: val })}
                        />
                        <SelectControl
                            label="Ordenamento"
                            value={attributes.sortBy}
                            options={[
                                { label: 'Quantidade de termos', value: 'count' },
                                { label: 'Alfabético', value: 'alphabetical' }
                            ]}
                            onChange={(val) => setAttributes({ sortBy: val })}
                        />
                    </PanelBody>

                    <PanelBody title="Estilo dos Itens" initialOpen={false}>
                        <TextControl
                            label="Tamanho da Fonte (ex: 14px, 1rem)"
                            value={attributes.itemFontSize}
                            onChange={(val) => setAttributes({ itemFontSize: val })}
                        />
                        <TextControl
                            label="Espaçamento (Padding, ex: 5px 0)"
                            value={attributes.itemPadding}
                            onChange={(val) => setAttributes({ itemPadding: val })}
                        />
                    </PanelBody>

                    <PanelColorSettings
                        title="Cores dos Itens"
                        initialOpen={false}
                        colorSettings={[
                            {
                                value: attributes.itemColor,
                                onChange: (colorValue) => setAttributes({ itemColor: colorValue }),
                                label: 'Cor do Texto',
                            },
                            {
                                value: attributes.checkboxColor,
                                onChange: (colorValue) => setAttributes({ checkboxColor: colorValue }),
                                label: 'Cor do Checkbox',
                            },
                        ]}
                    />
                </InspectorControls>
                <p style={{ padding: '8px', background: '#f0f0f0', borderRadius: '4px' }}>
                    <strong>Meili Filtro:</strong> {attributes.label}{' '}
                    <code style={{ fontSize: '11px' }}>({attributes.attribute})</code>
                    {attributes.showCount ? '' : ' — Contagem oculta'}
                </p>
            </div>
        );
    },
    save: function () {
        return null;
    },
});
