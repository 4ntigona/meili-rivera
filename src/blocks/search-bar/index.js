import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';
import metadata from './block.json';

registerBlockType(metadata.name, {
    edit: ({ attributes, setAttributes }) => {
        const { placeholder, buttonText, isInstant } = attributes;
        const blockProps = useBlockProps({
            className: 'meili-search-bar-block'
        });

        return (
            <div { ...blockProps }>
                <InspectorControls>
                    <PanelBody title="Configurações da Busca">
                        <TextControl
                            label="Placeholder"
                            value={ placeholder }
                            onChange={ (val) => setAttributes({ placeholder: val }) }
                        />
                        <TextControl
                            label="Texto do Botão"
                            value={ buttonText }
                            onChange={ (val) => setAttributes({ buttonText: val }) }
                        />
                        <ToggleControl
                            label="Busca Instantânea"
                            help="Se ativado, a busca acontece enquanto o usuário digita (ideal para a página de catálogo). Se desativado, a busca só acontece ao clicar no botão ou pressionar Enter (ideal para o cabeçalho)."
                            checked={ isInstant }
                            onChange={ (val) => setAttributes({ isInstant: val }) }
                        />
                    </PanelBody>
                </InspectorControls>
                <div className="meili-search-bar-wrapper">
                    <input 
                        type="text" 
                        className="meili-search-input" 
                        placeholder={ placeholder } 
                        disabled 
                    />
                    <button className="meili-search-button" disabled>
                        { buttonText }
                    </button>
                </div>
            </div>
        );
    },
    save: () => {
        return null; // Rendered via PHP
    }
});
