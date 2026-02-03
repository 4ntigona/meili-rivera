import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import metadata from './block.json';

registerBlockType(metadata.name, {
    edit: function () {
        const blockProps = useBlockProps();
        return (
            <div {...blockProps}>
                <p>Meili Rivera Pagination (Preview no Frontend)</p>
            </div>
        );
    },
    save: function () {
        return null;
    },
});
