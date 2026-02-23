import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
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
                    <PanelBody title="Configurações do Filtro">
                        <SelectControl
                            label="Taxonomia / Campo"
                            help="Selecione a opção configurada no plugin Meili Rivera."
                            value={attributes.attribute}
                            options={options}
                            onChange={(val) => setAttributes({ attribute: val })}
                        />
                        <TextControl
                            label="Rótulo (Título do Filtro)"
                            value={attributes.label}
                            onChange={(val) => setAttributes({ label: val })}
                        />
                        <ToggleControl
                            label="Exibir Contagem de Itens"
                            checked={attributes.showCount}
                            onChange={(val) => setAttributes({ showCount: val })}
                        />
                    </PanelBody>
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
