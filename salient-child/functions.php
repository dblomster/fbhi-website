<?php
  add_action('wp_enqueue_scripts', 'enqueue_parent_styles');
  function enqueue_parent_styles() {
    wp_enqueue_style('main-styles', get_stylesheet_directory_uri().'/css/build/style.css');
    wp_enqueue_style('nectar-blog-standard-featured-left', get_stylesheet_directory_uri().'/css/build/blog/standard-featured-left.css');

    if (is_singular('network-project')) {
      wp_enqueue_style('portfolio', get_site_url().'/wp-content/plugins/salient-portfolio/css/portfolio.css');
    }
  }

	function network_projects_cpt() {
		$labels = array(
			'name'                  => _x( 'Network Projects', '' ),
			'singular_name'         => _x( 'Network Project', '' ),
			'menu_name'             => __( 'Network Projects' ),
		);
		$args = array(
			'label'                 => __( 'Network Projects' ),
			'description'           => __( '' ),
			'labels'                => $labels,
			'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
			'taxonomies'            => array( '' ),
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 10,
			//'menu_icon'             => 'dashicons-calendar-alt',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => false,
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'capability_type'       => 'page',
		);
		register_post_type( 'network-project', $args );

	}
	add_action( 'init', 'network_projects_cpt', 0 );

  function network_project_category() {
    register_taxonomy(
      'network-project-category', // Taxonomy name
      'network-project', // Post type
      array(
        'hierarchical' => true,
        'label' => 'Categories',
        'query_var' => true,

      )
    );
  }
  add_action('init', 'network_project_category');
?>
