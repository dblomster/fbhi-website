<?php
/**
 * Salient Child Theme – Functions
 *
 * @package Salient-Child
 */

/* ========================================================================
   Enqueue Styles
   ======================================================================== */

add_action( 'wp_enqueue_scripts', 'enqueue_parent_styles' );
function enqueue_parent_styles() {
	wp_enqueue_style( 'main-styles', get_stylesheet_directory_uri() . '/css/build/style.css' );
	wp_enqueue_style( 'nectar-blog-standard-featured-left', get_stylesheet_directory_uri() . '/css/build/blog/standard-featured-left.css' );

	if ( is_singular( 'network-project' ) ) {
		wp_enqueue_style( 'portfolio', get_site_url() . '/wp-content/plugins/salient-portfolio/css/portfolio.css' );
	}
}

/* ========================================================================
   Custom Post Type: Network Projects
   ======================================================================== */

function network_projects_cpt() {
	$labels = array(
		'name'          => _x( 'Network Projects', '' ),
		'singular_name' => _x( 'Network Project', '' ),
		'menu_name'     => __( 'Network Projects' ),
	);
	$args = array(
		'label'               => __( 'Network Projects' ),
		'description'         => __( '' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
		'taxonomies'          => array( '' ),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_position'       => 10,
		'show_in_admin_bar'   => true,
		'show_in_nav_menus'   => true,
		'can_export'          => true,
		'has_archive'         => false,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'page',
	);
	register_post_type( 'network-project', $args );
}
add_action( 'init', 'network_projects_cpt', 0 );

/* ========================================================================
   Taxonomy: Network Project Category
   ======================================================================== */

function network_project_category() {
	register_taxonomy(
		'network-project-category',
		'network-project',
		array(
			'hierarchical' => true,
			'label'        => 'Categories',
			'query_var'    => true,
		)
	);
}
add_action( 'init', 'network_project_category' );

/* ========================================================================
   WW-Fingers: Admin Column, Quick Edit, Bulk Edit & Front-end
   Post type: network-project
   Meta key:  _ww_fingers
   ======================================================================== */

/**
 * Helper: normalize WW-Fingers values.
 */
function fbhi_is_ww_fingers_enabled_value( $value ) {
	return in_array( strtolower( (string) $value ), array( '1', 'true', 'yes', 'on' ), true );
}

/**
 * Helper: get the original/source post ID for WPML.
 * Falls back to the current post ID if WPML is not active or mapping fails.
 */
function fbhi_get_ww_fingers_source_post_id( $post_id ) {
	$post_id   = (int) $post_id;
	$post_type = get_post_type( $post_id );

	if ( ! $post_id || ! $post_type ) {
		return $post_id;
	}

	// If WPML is not active, use current post.
	if ( ! has_filter( 'wpml_element_language_details' ) || ! has_filter( 'wpml_get_element_translations' ) ) {
		return $post_id;
	}

	$element_type = 'post_' . $post_type;

	$details = apply_filters( 'wpml_element_language_details', null, array(
		'element_id'   => $post_id,
		'element_type' => $element_type,
	) );

	if ( empty( $details ) || empty( $details->trid ) ) {
		return $post_id;
	}

	$translations = apply_filters( 'wpml_get_element_translations', null, $details->trid, $element_type );

	if ( empty( $translations ) || ! is_array( $translations ) ) {
		return $post_id;
	}

	foreach ( $translations as $translation ) {
		if ( isset( $translation->original ) && (int) $translation->original === 1 && ! empty( $translation->element_id ) ) {
			return (int) $translation->element_id;
		}
	}

	return $post_id;
}

/**
 * Register admin column.
 */
add_filter( 'manage_edit-network-project_columns', function ( $columns ) {
	$columns['ww_fingers'] = 'WW-Fingers';
	return $columns;
} );

add_filter( 'manage_edit-network-project_sortable_columns', function ( $columns ) {
	$columns['ww_fingers'] = 'ww_fingers';
	return $columns;
} );

add_action( 'pre_get_posts', function ( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( $query->get( 'orderby' ) === 'ww_fingers' ) {
		$query->set( 'meta_key', '_ww_fingers' );
		$query->set( 'orderby', 'meta_value' );
	}
} );

/**
 * Render admin column content.
 * Always read the value from the original/source post.
 */
add_action( 'manage_network-project_posts_custom_column', function ( $column, $post_id ) {
	if ( $column !== 'ww_fingers' ) {
		return;
	}

	$source_post_id = fbhi_get_ww_fingers_source_post_id( $post_id );
	$value          = get_post_meta( $source_post_id, '_ww_fingers', true );
	$enabled        = fbhi_is_ww_fingers_enabled_value( $value );

	echo $enabled ? 'Yes' : 'No';
	echo '<div class="hidden" data-ww-fingers="' . ( $enabled ? '1' : '0' ) . '"></div>';
}, 10, 2 );

/**
 * Add quick edit field.
 */
add_action( 'quick_edit_custom_box', function ( $column_name, $post_type ) {
	if ( $post_type !== 'network-project' || $column_name !== 'ww_fingers' ) {
		return;
	}
	?>
	<fieldset class="inline-edit-col-right">
		<div class="inline-edit-col">
			<label class="alignleft">
				<input type="checkbox" name="ww_fingers_quick_edit" value="1">
				<span class="checkbox-title">WW-Fingers</span>
			</label>
		</div>
	</fieldset>
	<?php
}, 10, 2 );

/**
 * Add bulk edit field.
 */
add_action( 'bulk_edit_custom_box', function ( $column_name, $post_type ) {
	if ( $post_type !== 'network-project' || $column_name !== 'ww_fingers' ) {
		return;
	}
	?>
	<fieldset class="inline-edit-col-right">
		<div class="inline-edit-col">
			<label class="alignleft">
				<span class="title">WW-Fingers</span>
				<select name="ww_fingers_bulk_edit">
					<option value="">— No Change —</option>
					<option value="1">Enabled</option>
					<option value="0">Disabled</option>
				</select>
			</label>
		</div>
	</fieldset>
	<?php
}, 10, 2 );

/**
 * Save quick edit value.
 * Always write to the original/source post.
 */
add_action( 'save_post_network-project', function ( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( ! isset( $_REQUEST['_inline_edit'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( $_REQUEST['_inline_edit'], 'inlineeditnonce' ) ) {
		return;
	}

	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}

	$target_post_id = fbhi_get_ww_fingers_source_post_id( $post_id );

	if ( ! current_user_can( 'edit_post', $target_post_id ) ) {
		return;
	}

	if ( isset( $_REQUEST['ww_fingers_quick_edit'] ) ) {
		update_post_meta( $target_post_id, '_ww_fingers', '1' );
	} else {
		delete_post_meta( $target_post_id, '_ww_fingers' );
	}
} );

/**
 * Save bulk edit value.
 * Always write to the original/source post.
 */
add_action( 'load-edit.php', function () {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	if ( ! $screen || $screen->post_type !== 'network-project' ) {
		return;
	}

	if ( ! isset( $_REQUEST['post_type'] ) || $_REQUEST['post_type'] !== 'network-project' ) {
		return;
	}

	if ( ! isset( $_REQUEST['ww_fingers_bulk_edit'] ) || $_REQUEST['ww_fingers_bulk_edit'] === '' ) {
		return;
	}

	$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
	$action        = $wp_list_table->current_action();

	if ( $action !== 'edit' ) {
		return;
	}

	if ( empty( $_REQUEST['post'] ) || ! is_array( $_REQUEST['post'] ) ) {
		return;
	}

	$bulk_value = sanitize_text_field( wp_unslash( $_REQUEST['ww_fingers_bulk_edit'] ) );
	$post_ids   = array_map( 'intval', $_REQUEST['post'] );
	$done_ids   = array();

	foreach ( $post_ids as $post_id ) {
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			continue;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			continue;
		}

		$target_post_id = fbhi_get_ww_fingers_source_post_id( $post_id );

		if ( ! $target_post_id || isset( $done_ids[ $target_post_id ] ) ) {
			continue;
		}

		if ( ! current_user_can( 'edit_post', $target_post_id ) ) {
			continue;
		}

		if ( $bulk_value === '1' ) {
			update_post_meta( $target_post_id, '_ww_fingers', '1' );
		} elseif ( $bulk_value === '0' ) {
			delete_post_meta( $target_post_id, '_ww_fingers' );
		}

		$done_ids[ $target_post_id ] = true;
	}
} );

/**
 * Populate quick edit checkbox from current row value.
 */
add_action( 'admin_footer-edit.php', function () {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	if ( ! $screen || $screen->post_type !== 'network-project' ) {
		return;
	}
	?>
	<script>
	(function($) {
		var wpInlineEdit = inlineEditPost.edit;

		inlineEditPost.edit = function(id) {
			wpInlineEdit.apply(this, arguments);

			var postId = 0;
			if (typeof id === 'object') {
				postId = parseInt(this.getId(id), 10);
			} else {
				postId = parseInt(id, 10);
			}

			if (!postId) {
				return;
			}

			var $postRow  = $('#post-' + postId);
			var enabled   = $postRow.find('[data-ww-fingers]').data('ww-fingers');
			var $editRow  = $('#edit-' + postId);
			var $checkbox = $editRow.find('input[name="ww_fingers_quick_edit"]');

			$checkbox.prop('checked', String(enabled) === '1');
		};
	})(jQuery);
	</script>
	<?php
} );

/**
 * Front-end CSS.
 */
add_action( 'wp_head', function () {
	echo '<style>
		.nectar-post-grid-item .inner {
			position: relative !important;
		}
		.ww-fingers-logo {
			position: absolute !important;
			bottom: 8px !important;
			right: 8px !important;
			height: 24px !important;
			width: auto !important;
			z-index: 200 !important;
			pointer-events: none !important;
			display: block !important;
			margin: 0 !important;
		}
	</style>';
} );

/**
 * Front-end output: only posts with _ww_fingers = 1/true.
 */
add_action( 'wp_footer', function () {
	$enabled_posts = get_posts( array(
		'post_type'      => 'network-project',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_query'     => array(
			'relation' => 'OR',
			array(
				'key'   => '_ww_fingers',
				'value' => '1',
			),
			array(
				'key'   => '_ww_fingers',
				'value' => 'true',
			),
		),
	) );

	$enabled_json = wp_json_encode( array_map( 'strval', $enabled_posts ) );

	echo '<script>
	(function() {
		var logo = "https://fbhi.se/wp-content/uploads/2026/03/ww-fingers-logo-small.webp";
		var enabled = ' . $enabled_json . ';

		function addWwFingersLogos() {
			document.querySelectorAll(".nectar-post-grid-item > .inner:not(.ww-fingers-processed)").forEach(function(inner) {
				inner.classList.add("ww-fingers-processed");

				var card = inner.closest(".nectar-post-grid-item");
				var postId = card ? card.getAttribute("data-post-id") : null;

				if (!postId || enabled.indexOf(postId) === -1) {
					return;
				}

				var img = document.createElement("img");
				img.src = logo;
				img.alt = "";
				img.className = "ww-fingers-logo";
				inner.appendChild(img);
			});
		}

		addWwFingersLogos();

		new MutationObserver(function() {
			addWwFingersLogos();
		}).observe(document.body, {
			childList: true,
			subtree: true
		});
	})();
	</script>';
}, 90 );

/* ========================================================================
   The Events Calendar: Inject Salient Global Sections before/after list
   ======================================================================== */

/**
 * Render a Salient Global Section by ID (WPML-aware).
 *
 * @param int    $original_section_id Section post ID (in default language).
 * @param string $wrapper_class       CSS class(es) for the wrapper div.
 * @param string $inner_html_open     Optional extra opening markup inside the wrapper.
 * @param string $inner_html_close    Optional extra closing markup inside the wrapper.
 */
function fbhi_render_global_section( $original_section_id, $wrapper_class, $inner_html_open = '', $inner_html_close = '' ) {
	$section_id = apply_filters( 'wpml_object_id', $original_section_id, 'salient_g_sections', true );
	$section    = get_post( $section_id );

	if ( ! $section || 'salient_g_sections' !== $section->post_type ) {
		return;
	}

	echo '<div class="' . esc_attr( $wrapper_class ) . '">' . $inner_html_open;
	echo apply_filters( 'the_content', $section->post_content );
	echo $inner_html_close . '</div>';
}

/* Global Section post IDs (default language). */
$fbhi_events_section_before = 13868;
$fbhi_events_section_after  = 7011;

/* Section before the events list. */
add_action( 'tribe_template_before_include:events/v2/list', function ( $file, $name, $template ) use ( $fbhi_events_section_before ) {
	fbhi_render_global_section( $fbhi_events_section_before, 'events-global-section before-events' );
}, 10, 3 );

/* Section after the events list. */
add_action( 'tribe_template_after_include:events/v2/list', function ( $file, $name, $template ) use ( $fbhi_events_section_after ) {
	fbhi_render_global_section(
		$fbhi_events_section_after,
		'events-global-section after-events',
		'<div class="container normal-container row">',
		'</div>'
	);
}, 10, 3 );
