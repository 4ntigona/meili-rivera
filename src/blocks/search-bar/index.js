import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls, PanelColorSettings, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl, SelectControl, Button } from '@wordpress/components';
import metadata from './block.json';

registerBlockType(metadata.name, {
    edit: ({ attributes, setAttributes }) => {
        const { 
            placeholder, buttonText, isInstant, 
            inputPadding, inputFontSize, inputColor,
            buttonPadding, buttonFontSize, buttonColor, buttonBgColor,
            showIcon, iconUrl, iconPosition 
        } = attributes;
        
        const blockProps = useBlockProps({
            className: 'meili-search-bar-block'
        });

        return (
            <div { ...blockProps }>
                <InspectorControls>
                    <PanelBody title="Configurações Gerais" initialOpen={true}>
                        <ToggleControl
                            label="Busca Instantânea"
                            help="Se ativado, a busca acontece enquanto o usuário digita (ideal para a página de catálogo). Se desativado, a busca só acontece ao clicar no botão ou pressionar Enter (ideal para o cabeçalho)."
                            checked={ isInstant }
                            onChange={ (val) => setAttributes({ isInstant: val }) }
                        />
                    </PanelBody>

                    <PanelBody title="Área de Texto" initialOpen={false}>
                        <TextControl
                            label="Placeholder"
                            value={ placeholder }
                            onChange={ (val) => setAttributes({ placeholder: val }) }
                        />
                        <TextControl
                            label="Tamanho da Fonte (ex: 16px, 1rem)"
                            value={ inputFontSize }
                            onChange={ (val) => setAttributes({ inputFontSize: val }) }
                        />
                        <TextControl
                            label="Espaçamento (Padding, ex: 10px 15px)"
                            value={ inputPadding }
                            onChange={ (val) => setAttributes({ inputPadding: val }) }
                        />
                    </PanelBody>

                    <PanelColorSettings
                        title="Cores da Área de Texto"
                        initialOpen={false}
                        colorSettings={[
                            {
                                value: inputColor,
                                onChange: (colorValue) => setAttributes({ inputColor: colorValue }),
                                label: 'Cor do Texto',
                            },
                        ]}
                    />

                    <PanelBody title="Botão de Busca" initialOpen={false}>
                        <TextControl
                            label="Texto do Botão"
                            value={ buttonText }
                            onChange={ (val) => setAttributes({ buttonText: val }) }
                        />
                        <TextControl
                            label="Tamanho da Fonte (ex: 16px, 1rem)"
                            value={ buttonFontSize }
                            onChange={ (val) => setAttributes({ buttonFontSize: val }) }
                        />
                        <TextControl
                            label="Espaçamento (Padding, ex: 10px 20px)"
                            value={ buttonPadding }
                            onChange={ (val) => setAttributes({ buttonPadding: val }) }
                        />
                        <ToggleControl
                            label="Exibir Ícone"
                            checked={ showIcon }
                            onChange={ (val) => setAttributes({ showIcon: val }) }
                        />
                        {showIcon && (
                            <>
                                <SelectControl
                                    label="Posição do Ícone"
                                    value={iconPosition}
                                    options={[
                                        { label: 'Dentro (Esquerda)', value: 'inside-left' },
                                        { label: 'Dentro (Direita)', value: 'inside-right' },
                                        { label: 'No Botão', value: 'button' }
                                    ]}
                                    onChange={(val) => setAttributes({ iconPosition: val })}
                                />
                                <MediaUploadCheck>
                                    <MediaUpload
                                        onSelect={(media) => setAttributes({ iconUrl: media.url })}
                                        allowedTypes={['image']}
                                        render={({ open }) => (
                                            <Button variant="secondary" onClick={open} style={{ marginBottom: '10px', display: 'block' }}>
                                                {iconUrl ? 'Trocar Ícone' : 'Escolher Ícone'}
                                            </Button>
                                        )}
                                    />
                                </MediaUploadCheck>
                                {iconUrl && (
                                    <Button variant="link" isDestructive onClick={() => setAttributes({ iconUrl: '' })}>
                                        Remover Ícone
                                    </Button>
                                )}
                            </>
                        )}
                    </PanelBody>

                    <PanelColorSettings
                        title="Cores do Botão"
                        initialOpen={false}
                        colorSettings={[
                            {
                                value: buttonColor,
                                onChange: (colorValue) => setAttributes({ buttonColor: colorValue }),
                                label: 'Cor do Texto',
                            },
                            {
                                value: buttonBgColor,
                                onChange: (colorValue) => setAttributes({ buttonBgColor: colorValue }),
                                label: 'Cor de Fundo',
                            },
                        ]}
                    />
                </InspectorControls>
                <div className={`meili-search-bar-wrapper icon-pos-${iconPosition}`} style={{ display: 'flex', gap: '8px', width: '100%' }}>
                    <div className="meili-search-input-wrapper" style={{ position: 'relative', display: 'flex', flexGrow: 1, alignItems: 'center' }}>
                        {showIcon && iconPosition === 'inside-left' && iconUrl && (
                            <img src={iconUrl} alt="Search Icon" className="meili-search-icon inside left" style={{ position: 'absolute', left: '10px', width: '20px', height: '20px' }} />
                        )}
                        <input 
                            type="text" 
                            className="meili-search-input" 
                            placeholder={ placeholder } 
                            disabled 
                            style={{ 
                                padding: inputPadding, 
                                fontSize: inputFontSize, 
                                color: inputColor,
                                paddingLeft: showIcon && iconPosition === 'inside-left' ? '40px' : undefined,
                                paddingRight: showIcon && iconPosition === 'inside-right' ? '40px' : undefined,
                                width: '100%'
                            }}
                        />
                        {showIcon && iconPosition === 'inside-right' && iconUrl && (
                            <img src={iconUrl} alt="Search Icon" className="meili-search-icon inside right" style={{ position: 'absolute', right: '10px', width: '20px', height: '20px' }} />
                        )}
                    </div>
                    <button 
                        className="meili-search-button" 
                        disabled
                        style={{ 
                            display: 'flex',
                            alignItems: 'center',
                            gap: '8px',
                            padding: buttonPadding, 
                            fontSize: buttonFontSize, 
                            color: buttonColor, 
                            backgroundColor: buttonBgColor 
                        }}
                    >
                        {showIcon && iconPosition === 'button' && iconUrl && (
                            <img src={iconUrl} alt="Search Icon" className="meili-search-button-icon" style={{ width: '20px', height: '20px' }} />
                        )}
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
