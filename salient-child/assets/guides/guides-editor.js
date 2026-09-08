/**
 * FBHI Guides — block editor additions.
 *
 * Adds a "Guide page" document-settings panel with the chapter label, the
 * accent colour (FBHI palette swatches, custom colour still allowed) and the
 * print-button text.
 * Hand-written (no build step): uses wp.element.createElement, no JSX.
 */
( function ( wp ) {
	'use strict';

	if ( ! wp || ! wp.plugins ) {
		return;
	}

	var config = window.fbhiGuideEditor || {};
	var registerPlugin = wp.plugins.registerPlugin;
	var PluginDocumentSettingPanel =
		( wp.editor && wp.editor.PluginDocumentSettingPanel ) ||
		( wp.editPost && wp.editPost.PluginDocumentSettingPanel );

	if ( ! PluginDocumentSettingPanel ) {
		return;
	}

	var el = wp.element.createElement;
	var __ = wp.i18n.__;
	var useSelect = wp.data.useSelect;
	var useDispatch = wp.data.useDispatch;
	var TextControl = wp.components.TextControl;
	var ColorPalette = wp.components.ColorPalette;
	var BaseControl = wp.components.BaseControl;

	var metaAccent = config.metaAccent || '_fbhi_guide_accent';
	var metaLabel = config.metaLabel || '_fbhi_guide_label';
	var metaPrintLabel = config.metaPrintLabel || '_fbhi_guide_print_label';

	function GuidePanel() {
		var postType = useSelect( function ( select ) {
			return select( 'core/editor' ).getCurrentPostType();
		}, [] );
		var meta = useSelect( function ( select ) {
			return select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {};
		}, [] );
		var parentId = useSelect( function ( select ) {
			return select( 'core/editor' ).getEditedPostAttribute( 'parent' ) || 0;
		}, [] );
		var editPost = useDispatch( 'core/editor' ).editPost;

		if ( postType !== ( config.postType || 'guide' ) ) {
			return null;
		}

		function setMeta( key, value ) {
			var next = {};
			next[ key ] = value;
			editPost( { meta: next } );
		}

		var help = parentId
			? __( 'Inherited by sub-pages of this chapter. Leave empty to inherit from the parent.', 'salient-child' )
			: __( 'Optional colour for the guide start page. Chapters set their own colour.', 'salient-child' );

		return el(
			PluginDocumentSettingPanel,
			{
				name: 'fbhi-guide-settings',
				title: __( 'Guide page', 'salient-child' ),
				className: 'fbhi-guide-settings',
				initialOpen: true,
			},
			el( TextControl, {
				label: __( 'Label', 'salient-child' ),
				help: __( 'Shown above the title and on the index card, e.g. "Avsnitt 1".', 'salient-child' ),
				value: meta[ metaLabel ] || '',
				onChange: function ( value ) {
					setMeta( metaLabel, value );
				},
				__nextHasNoMarginBottom: true,
				__next40pxDefaultSize: true,
			} ),
			el( TextControl, {
				label: __( 'Print button text', 'salient-child' ),
				help: __( 'Leave empty for the default text.', 'salient-child' ),
				placeholder: config.printLabelDefault || '',
				value: meta[ metaPrintLabel ] || '',
				onChange: function ( value ) {
					setMeta( metaPrintLabel, value );
				},
				__nextHasNoMarginBottom: true,
				__next40pxDefaultSize: true,
			} ),
			el(
				BaseControl,
				{
					label: __( 'Accent colour', 'salient-child' ),
					help: help,
					id: 'fbhi-guide-accent',
					__nextHasNoMarginBottom: true,
				},
				el( ColorPalette, {
					colors: config.palette || [],
					value: meta[ metaAccent ] || undefined,
					onChange: function ( value ) {
						setMeta( metaAccent, value ? String( value ).toUpperCase() : '' );
					},
					clearable: true,
					disableCustomColors: false,
				} )
			)
		);
	}

	registerPlugin( 'fbhi-guide-settings', {
		render: GuidePanel,
		icon: 'book-alt',
	} );

	/* Mirror the accent colour into the editor canvas so callouts, checklists
	   and the index preview use the chapter colour while editing. */
	function tintFor( accent ) {
		var entry = ( config.palette || [] ).filter( function ( c ) {
			return c.color && accent && c.color.toUpperCase() === accent.toUpperCase();
		} )[ 0 ];
		if ( entry && entry.tint ) {
			return entry.tint;
		}
		return accent ? 'color-mix(in srgb, ' + accent + ' 30%, white)' : '';
	}
	function canvasRoot() {
		var frame = document.querySelector( 'iframe[name="editor-canvas"]' );
		var doc = frame && frame.contentDocument ? frame.contentDocument : document;
		return doc.querySelector( '.editor-styles-wrapper' ) || doc.body;
	}
	var lastAccent;
	wp.data.subscribe( function () {
		var editor = wp.data.select( 'core/editor' );
		if ( ! editor || editor.getCurrentPostType() !== ( config.postType || 'guide' ) ) {
			return;
		}
		var meta = editor.getEditedPostAttribute( 'meta' ) || {};
		var accent = meta[ metaAccent ] || '';
		if ( accent === lastAccent ) {
			return;
		}
		lastAccent = accent;
		var root = canvasRoot();
		if ( ! root ) {
			return;
		}
		if ( accent ) {
			root.style.setProperty( '--fbhi-guide-accent', accent );
			root.style.setProperty( '--fbhi-guide-tint', tintFor( accent ) );
		} else {
			root.style.removeProperty( '--fbhi-guide-accent' );
			root.style.removeProperty( '--fbhi-guide-tint' );
		}
	} );
} )( window.wp );
