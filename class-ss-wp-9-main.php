<?php
/**
 * Plugin Name: SoftwareSeni WP Training 9
 * Description: Understand how to create custom REST API in WordPress
 * Version: 1.0
 * Author: Bismoko Widyatno
 *
 * @package ss-wp-9
 */

/**
 * Import necessary files for method is_plugin_active
 */
if ( ! function_exists( 'is_plugin_active' ) ) {
	include_once ABSPATH . 'wp-admin/includes/plugin.php';
}


/**
 * --------------------------------------------------------------------------
 * Main class for this plugin. This class will handle most of the
 * plugin logic
 * --------------------------------------------------------------------------
 **/
class SS_WP_9_Main {
	/**
	 * Custom post type name
	 *
	 * @var string
	 */
	private $ss_custom_post_type_name = 'wt9-testimonial';

	/**
	 * Prefix
	 *
	 * @var string
	 */
	private $ss_prefix = 'ss-wt-9';

	/**
	 * Testimonials to show per page
	 *
	 * @var int
	 */
	private $ss_per_page = 1;

	/**
	 * Class constructor
	 */
	public function __construct() {
		/**
		* Execute this when plugin activated and have been loaded
		* 1. register shortcodes
		*/
		add_action( 'plugins_loaded', array( $this, 'ss_wp9_plugins_loaded_handlers' ) );
	}

	/**
	 * Function to display latest posts from WP API
	 *
	 * @param string $ss_shortcode_atts action to do ( select, insert, update, delete ).
	 */
	public function ss_wp9_create_shortcode( $ss_shortcode_atts = array() ) {
		ob_start();

		// -- override default shortcode parameters
		$ss_wp_8_shd_atts = shortcode_atts(
			[
				'action' => 'select',
			],
			$ss_shortcode_atts
		);

		// $this->ss_wp9_get_testimonials();

		return ob_get_clean();
	}

	/**
	 * Function to create custom post type
	 */
	public function ss_wp_9_crt_cst_post_type() {
		register_post_type(
			$this->ss_custom_post_type_name,
			array(
				'labels'        => array(
					'name'               => 'Testimonials',
					'singular_name'      => 'Testimonial',
					'add_new'            => 'Add New',
					'add_new_item'       => 'Add New Testimonial',
					'edit'               => 'Edit',
					'edit_item'          => 'Edit Testimonial',
					'new_item'           => 'New Testimonial',
					'view'               => 'View',
					'view_item'          => 'View Testimonial',
					'search_items'       => 'Search Testimonial',
					'not_found'          => 'No Testimonials Found',
					'not_found_in_trash' => 'No Testimonials Found in Trash',
					'parent'             => 'Parent Testimonial',
				),

				'public'        => true,
				'menu_position' => 15,
				'supports'      => array( 'title', 'editor', 'comments', 'thumbnail', 'custom-fields' ),
				'taxonomies'    => array( '' ),
				'menu_icon'     => 'dashicons-format-quote',
				'has_archive'   => true,
			)
		);
	}

	/**
	 * Function to create meta boxes using Meta Box plugin (http://metabox.io/)
	 */
	public function ss_wp_9_crt_metabox() {
		$ss_meta_boxes[] = array(
			'title'      => 'Other Information',
			'post_types' => $this->ss_custom_post_type_name,

			'fields'     => array(
				array(
					'name' => esc_html__( 'Author', 'ss_wp_9' ),
					'desc' => '',
					'id'   => $this->ss_prefix . 'author',
					'type' => 'text',
				),
				array(
					'name' => esc_html__( 'Content', 'ss_wp_9' ),
					'desc' => '',
					'id'   => $this->ss_prefix . 'content',
					'type' => 'textarea',
				),
				array(
					'name' => esc_html__( 'Date', 'ss_wp_9' ),
					'desc' => '',
					'id'   => $this->ss_prefix . 'date',
					'type' => 'date',
				),
				array(
					'name'    => esc_html__( 'Rate', 'ss_wp_9' ),
					'desc'    => '',
					'id'      => $this->ss_prefix . 'rate',
					'type'    => 'radio',
					'options' => array(
						'1' => '1',
						'2' => '2',
						'3' => '3',
						'4' => '4',
						'5' => '5',
					),
					'inline'  => true,
				),
			),
		);
		return $ss_meta_boxes;
	}

	/**
	 * Function to get testimonial data
	 *
	 * @param WP_REST_Request $request page and per_page.
	 * @return object $ss_testimonials Testimonials data.
	 */
	public function ss_wp9_get_testimonials( WP_REST_Request $request ) {
		$ss_testimonials = '';

		// -- get parameters
		$parameters = $request->get_params();

		if ( null !== $parameters ) {
			// -- spesific testimonials
			if ( ! empty( $parameters['id'] ) ) {
				// -- get spesific testimonials
				$ss_args = array(
					'post_type'   => $this->ss_custom_post_type_name,
					'post_status' => 'publish',
					'p'           => $parameters['id'],
				);
			} else {
				// -- get all testimonials
				$ss_args = array(
					'post_type'      => $this->ss_custom_post_type_name,
					'post_status'    => 'publish',
					'orderby'        => 'title',
					'order'          => 'ASC',
					'paged'          => $parameters['page'],
					'posts_per_page' => $parameters['per_page'],
				);
			}

			$ss_testimonials = new WP_Query( $ss_args );

			if ( ! empty( $ss_testimonials ) ) {
				return $ss_testimonials;
			}
		}

		return $ss_testimonials;
	}

	/**
	 * Function for registering REST route
	 */
	public function ss_wp9_reg_rest_route() {
		// -- all testimonials
		register_rest_route(
			'ss-wp-9/v1',
			'/testimonials',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'ss_wp9_get_testimonials' ),
				'args'     => array(
					'page'     => array(
						'validate_callback' => function( $param, $request, $key ) {
							return is_numeric( $param );
						},
						'default'           => 1,
					),
					'per_page' => array(
						'validate_callback' => function( $param, $request, $key ) {
							return is_numeric( $param );
						},
						'default'           => $this->ss_per_page,
					),
				),
			)
		);

		// -- spesific testimonials
		register_rest_route(
			'ss-wp-9/v1',
			'/testimonials/(?P<id>\d+)',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'ss_wp9_get_testimonials' ),
				'args'     => array(
					'id' => array(
						'validate_callback' => function( $param, $request, $key ) {
							return is_numeric( $param );
						},
						'required'          => true,
					),
				),
			)
		);
	}

	/**
	 * Function for executing some task when plugins loaded
	 */
	public function ss_wp9_plugins_loaded_handlers() {
		// -- register custom post type
		add_action( 'init', array( $this, 'ss_wp_9_crt_cst_post_type' ) );

		// -- show metaboxes ( from metabox.io )
		if ( is_plugin_active( 'meta-box/meta-box.php' ) ) {
			add_filter( 'rwmb_meta_boxes', array( $this, 'ss_wp_9_crt_metabox' ) );
		}

		// -- register shortcode
		add_shortcode( 'wp9_custom_rest_api', array( $this, 'ss_wp9_create_shortcode' ) );

		// -- register REST API custom route
		add_action( 'rest_api_init', array( $this, 'ss_wp9_reg_rest_route' ) );
	}
}

// -- run the main class
$ss_wp_9_main_class = new SS_WP_9_Main();
