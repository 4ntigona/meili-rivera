import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import metadata from './block.json';

registerBlockType(metadata.name, {
    edit: function ({ attributes, setAttributes }) {
        const blockProps = useBlockProps();
        return (
            <div {...blockProps}>
                <InspectorControls>
                    <PanelBody title="Configurações do Filtro">
                        <TextControl
                            label="Atributo (Taxonomia/Campo)"
                            help="Ex: product_cat, pa_autor"
                            value={attributes.attribute}
                            onChange={(val) => setAttributes({ attribute: val })}
                        />
                        <TextControl
                            label="Rótulo"
                            value={attributes.label}
                            onChange={(val) => setAttributes({ label: val })}
                        />
                    </PanelBody>
                </InspectorControls>
                <p>Filtro: {attributes.label} ({attributes.attribute})</p>
            </div>
        );
    },
    save: function () {
        return null;
    },
});
