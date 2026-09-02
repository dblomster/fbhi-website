/**
 * fbhi/guide-index — editor side (hand-written, no build).
 * Shows the server-rendered cards in the editor plus a heading setting.
 */
( function ( wp ) {
	'use strict';

	var el = wp.element.createElement;
	var __ = wp.i18n.__;
	var registerBlockType = wp.blocks.registerBlockType;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var PanelBody = wp.components.PanelBody;
	var TextControl = wp.components.TextControl;
	var ServerSideRender = wp.serverSideRender;

	registerBlockType( 'fbhi/guide-index', {
		edit: function ( props ) {
			var blockProps = useBlockProps( { className: 'fbhi-guide-index-block-editor' } );
			return el(
				'div',
				blockProps,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Guide index', 'salient-child' ), initialOpen: true },
						el( TextControl, {
							label: __( 'Heading', 'salient-child' ),
							help: __( 'Leave empty for the default heading.', 'salient-child' ),
							value: props.attributes.heading || '',
							onChange: function ( value ) {
								props.setAttributes( { heading: value } );
							},
							__nextHasNoMarginBottom: true,
							__next40pxDefaultSize: true,
						} )
					)
				),
				el( 'p', { className: 'fbhi-guide-index-block-editor__note' }, __( 'Guide index — generated automatically from the chapter pages.', 'salient-child' ) ),
				el( ServerSideRender, { block: 'fbhi/guide-index', attributes: props.attributes } )
			);
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp );
