/**
 * FBHI Guides — block editor additions.
 *
 * Adds a "Guide page" document-settings panel with the chapter label, the
 * subtitle, the accent colour (FBHI palette swatches, custom colour still
 * allowed) and the print-button text. The canvas mirrors the accent colour and
 * shows the subtitle and the excerpt (intro) under the title, as on the frontend.
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
	var metaSubtitle = config.metaSubtitle || '_fbhi_guide_subtitle';
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
				label: __( 'Subtitle', 'salient-child' ),
				help: __( 'Shown under the title on the page and on the index card, not in the menu.', 'salient-child' ),
				value: meta[ metaSubtitle ] || '',
				onChange: function ( value ) {
					setMeta( metaSubtitle, value );
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
	/* The canvas iframe loads after the editor's data, and is rebuilt e.g. when switching the preview
	   device, so the colour is applied per canvas: nothing until the iframe's wrapper exists. */
	function canvasRoot() {
		var frame = document.querySelector( 'iframe[name="editor-canvas"]' );
		var doc = frame ? frame.contentDocument : document;
		return doc ? doc.querySelector( '.editor-styles-wrapper' ) : null;
	}
	/* Excerpt → paragraphs as wpautop makes them on the frontend: a blank line starts a new paragraph,
	   a single line break stays a line break. Text only (any HTML in the excerpt is shown as its text). */
	function excerptParagraphs( doc, excerpt ) {
		var text = new DOMParser().parseFromString( excerpt, 'text/html' ).body.textContent || '';
		return text.split( /\n\s*\n/ ).map( function ( para ) {
			return para.trim();
		} ).filter( Boolean ).map( function ( para ) {
			var p = doc.createElement( 'p' );
			para.split( '\n' ).forEach( function ( line, i ) {
				if ( i ) {
					p.appendChild( doc.createElement( 'br' ) );
				}
				p.appendChild( doc.createTextNode( line ) );
			} );
			return p;
		} );
	}
	/* Subtitle and intro (the manual excerpt): a read-only block under the title, as in the frontend
	   header. Rebuilt when either text changes, the title is re-rendered or the canvas rebuilt; removed
	   when both are empty. */
	function syncHeaderBand( root, subtitle, excerpt ) {
		var title = root.querySelector( '.wp-block-post-title' );
		var band = root.querySelector( '.fbhi-guide-editor-band' );
		subtitle = String( subtitle || '' ).trim();
		excerpt = String( excerpt || '' ).trim();
		if ( ! title || ( ! subtitle && ! excerpt ) ) {
			if ( band ) {
				band.remove();
			}
			return;
		}
		var sig = subtitle + '\u0000' + excerpt;
		if ( band && band.previousElementSibling === title && band.getAttribute( 'data-sig' ) === sig ) {
			return;
		}
		if ( band ) {
			band.remove();
		}
		var doc = title.ownerDocument;
		band = doc.createElement( 'div' );
		band.className = 'fbhi-guide-editor-band';
		band.setAttribute( 'data-sig', sig );
		if ( subtitle ) {
			var sub = doc.createElement( 'p' );
			sub.className = 'fbhi-guide-editor-subtitle';
			sub.title = __( 'Edit in the Guide page panel.', 'salient-child' );
			sub.textContent = subtitle;
			band.appendChild( sub );
		}
		var paras = excerpt ? excerptParagraphs( doc, excerpt ) : [];
		if ( paras.length ) {
			var intro = doc.createElement( 'div' );
			intro.className = 'fbhi-guide-editor-intro';
			intro.title = __( 'Edit via "Edit excerpt" in the sidebar.', 'salient-child' );
			paras.forEach( function ( p ) {
				intro.appendChild( p );
			} );
			band.appendChild( intro );
		}
		title.insertAdjacentElement( 'afterend', band );
	}
	var lastAccent, lastRoot;
	wp.data.subscribe( function () {
		var editor = wp.data.select( 'core/editor' );
		if ( ! editor || editor.getCurrentPostType() !== ( config.postType || 'guide' ) ) {
			return;
		}
		var meta = editor.getEditedPostAttribute( 'meta' ) || {};
		var accent = meta[ metaAccent ] || '';
		var root = canvasRoot();
		if ( ! root ) {
			return;
		}
		syncHeaderBand( root, meta[ metaSubtitle ] || '', editor.getEditedPostAttribute( 'excerpt' ) || '' );
		if ( accent === lastAccent && root === lastRoot ) {
			return;
		}
		lastAccent = accent;
		lastRoot = root;
		if ( accent ) {
			root.style.setProperty( '--fbhi-guide-accent', accent );
			root.style.setProperty( '--fbhi-guide-tint', tintFor( accent ) );
		} else {
			root.style.removeProperty( '--fbhi-guide-accent' );
			root.style.removeProperty( '--fbhi-guide-tint' );
		}
	} );
} )( window.wp );
