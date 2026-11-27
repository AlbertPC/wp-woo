const { createElement } = window.wp.element;
const { RichText, useBlockProps } = window.wp.blockEditor;

window.wp.blocks.registerBlockType( 'wp-woo/nota', {
	edit( { attributes, setAttributes } ) {
		return createElement( RichText, {
			...useBlockProps(),
			tagName: 'p',
			value: attributes.text,
			placeholder: 'Escribe una nota…',
			onChange: ( text ) => setAttributes( { text } ),
		} );
	},
	save( { attributes } ) {
		return createElement( RichText.Content, {
			...useBlockProps.save(),
			tagName: 'p',
			value: attributes.text,
		} );
	},
} );
