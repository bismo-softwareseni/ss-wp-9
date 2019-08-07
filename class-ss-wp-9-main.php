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

		// -- display insert new testimonial
		$this->ss_wp9_display_form( 'insert' );

		// -- display testimonial ( ajax )
		$this->ss_wp9_display_testimonials( 1, $this->ss_per_page );

		// -- display update testimonial
		$this->ss_wp9_display_form( 'update' );

		return ob_get_clean();
	}

	/**
	 * Function to display form
	 *
	 * @param string $ss_action Action insert or update.
	 */
	public function ss_wp9_display_form( $ss_action ) {
		$ss_custom_ss = 'margin-bottom: 50px;';

		// -- set custom css for update form
		if ( 'update' === $ss_action ) {
			$ss_custom_ss = 'margin-bottom: 50px; display: none;';
		}

		// -- only show the form to the user that has access and have been logged in
		if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
			?>

		<form class="ss-api-form-<?php echo esc_attr( $ss_action ); ?>-post ui form" style="<?php echo esc_attr( $ss_custom_ss ); ?>">
			<h4><?php echo esc_html( $ss_action ); ?> New Testimonial</h4>

			<div class="field">
				<label for="ss-input-tst-title">Author</label>
				<input required type="text" name="ss-input-tst-author" id="ss-input-tst-author" class="ss-input-text" value="" style="width:100%;" />
			</div>

			<div class="field">
				<label for="ss-input-tst-title">Content</label>
				<textarea required name="ss-input-tst-content" id="ss-input-tst-content" class="ss-input-textarea" style="width:100%;"></textarea>
			</div>

			<div class="field">
				<label for="ss-input-tst-date">Date ( Format: yyyy-mm-dd ) </label>
				<input required type="text" name="ss-input-tst-date" id="ss-input-tst-date" class="ss-input-text" value="" style="width:100%;" />
			</div>

			<div class="field">
				<label for="ss-input-tst-rate">Rate</label>
				<select class="ui fluid dropdown" name="ss-input-tst-rate" id="ss-input-tst-rate">
					<option value="1">1</option>
					<option value="2">2</option>
					<option value="3">3</option>
					<option value="4">4</option>
					<option value="5">5</option>
				</select>
			</div>

			<button type="submit" name="ss-tst-submit" class="ss-button ui button" style="margin-top:10px;">Submit</button>
		</form>

			<?php
		}
	}

	/**
	 * Function to display form to update testimonial
	 */
	public function ss_wp9_display_upt_form() {
		// -- only show the form to the user that has access and have been logged in
		if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
			?>

		<form class="ss-api-form-update-post ui form" style="margin-bottom: 50px; display: none;">
			<h4>Insert New Testimonial</h4>

			<div class="field">
				<label for="ss-input-tst-title">Author</label>
				<input required type="text" name="ss-input-tst-author" id="ss-input-tst-author" class="ss-input-text" value="" style="width:100%;" />
			</div>

			<div class="field">
				<label for="ss-input-tst-title">Content</label>
				<textarea required name="ss-input-tst-content" id="ss-input-tst-content" class="ss-input-textarea" style="width:100%;"></textarea>
			</div>

			<div class="field">
				<label for="ss-input-tst-date">Date ( Format: yyyy-mm-dd ) </label>
				<input required type="text" name="ss-input-tst-date" id="ss-input-tst-date" class="ss-input-text" value="" style="width:100%;" />
			</div>

			<div class="field">
				<label for="ss-input-tst-rate">Rate</label>
				<select class="ui fluid dropdown" name="ss-input-tst-rate" id="ss-input-tst-rate">
					<option value="1">1</option>
					<option value="2">2</option>
					<option value="3">3</option>
					<option value="4">4</option>
					<option value="5">5</option>
				</select>
			</div>

			<button type="submit" name="ss-tst-submit" class="ss-button ui button" style="margin-top:10px;">Submit</button>
		</form>

			<?php
		}
	}



	/**
	 * Function to display testimonials
	 *
	 * @param int $ss_page current page default 1.
	 * @param int $ss_tstm_per_page maximum post amount per page.
	 */
	public function ss_wp9_display_testimonials( $ss_page = 1, $ss_tstm_per_page ) {
		$ss_posts_response = wp_remote_get( get_site_url() . '/wp-json/ss-wp-9/v1/testimonials?page=' . $ss_page . '&per_page=' . $ss_tstm_per_page );

		// -- exit if request error
		if ( is_wp_error( $ss_posts_response ) ) {
			return;
		} else {
			?>

		<!-- post results container -->
		<div class="ajax-post-results-container" style="margin-bottom: 30px;">
			<?php
				// -- get the results
				$ss_posts_result = json_decode( wp_remote_retrieve_body( $ss_posts_response ) );

				// -- get max posts and max pages
				$ss_max_page = 0;

			if ( ! empty( $ss_posts_result ) ) {
				$ss_max_page = $ss_posts_result[0]->max_num_pages;

				foreach ( $ss_posts_result as $ss_post ) {
					?>

				<div class="post-<?php echo esc_attr( $ss_post->ID ); ?>">
					<h4>
						<a href="<?php echo esc_url( get_permalink( $ss_post->ID ) ); ?>">
							<?php echo esc_html( $ss_post->post_content ); ?>
						</a>
					</h4>

					<p>Author : <?php echo esc_html( $ss_post->tst_author ); ?></p>
					<p>Date : <?php echo esc_html( $ss_post->tst_date ); ?></p>
					<p>Rate : <?php echo esc_html( $ss_post->tst_rate ); ?></p>

					<!-- if show update and delete -->
						<?php
						if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
							?>

						<div>
							<a href="#" class="api-delete-post" data-post-id="<?php echo esc_attr( $ss_post->ID ); ?>" style="color: #262626; margin-right: 10px;">delete</a>
							<a href="#" class="api-update-post" data-post-id="<?php echo esc_attr( $ss_post->ID ); ?>" style="color: #262626;">update</a>
						</div>

							<?php
						}
						?>
				</div>

					<?php
				}
			}
			?>
		</div>
		<!-- end post results container -->

		<!-- pagination container -->
		<div class="ajax-pagination-container" style="margin-bottom: 30px;">
			<?php
				// -- set next page
				$ss_next_page = 0;

			if ( $ss_max_page > 1 ) {
				$ss_next_page = 2;
			} else {
				$ss_next_page = 1;
			}
			?>

			<div class="page-number">
				<span class="current-page">1</span>
				<span>of</span>
				<span class="max-page"><?php echo esc_html( $ss_max_page ); ?></span>
			</div>

			<div class="ui large buttons" data-max-page="<?php echo esc_attr( $ss_max_page ); ?>" data-current-page="1" data-post-perpage="<?php echo esc_attr( $ss_tstm_per_page ); ?>">
				<button class="ui button left labeled icon button-ajax-pagination prev-page" data-page="1">
					<i class="left arrow icon"></i> Previous Page
				</button>
				<button class="ui button right labeled icon button-ajax-pagination next-page" data-page="<?php echo esc_attr( $ss_next_page ); ?>">
					Next Page <i class="right arrow icon"></i>
				</button>
			</div>
		</div>
		<!-- end pagination container -->

			<?php
		}
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
	 * @param WP_REST_Request $request page, per_page, or id.
	 * @return object $ss_testimonials Testimonials data.
	 */
	public function ss_wp9_get_testimonials( WP_REST_Request $request ) {
		$ss_testimonials = [];

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
					'orderby'        => 'date',
					'order'          => 'DESC',
					'paged'          => $parameters['page'],
					'posts_per_page' => $parameters['per_page'],
				);
			}

			$ss_post_results = new WP_Query( $ss_args );

			// -- add id, post_title, post_content, and post meta into array
			if ( ! empty( $ss_post_results ) ) {
				// -- get maximum pages
				$ss_max_num_pages = $ss_post_results->max_num_pages;

				// -- check if current user can edit posts
				$ss_user_can_edit = false;
				if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
					$ss_user_can_edit = true;
				}

				foreach ( $ss_post_results->posts as $ss_post ) {
					array_push(
						$ss_testimonials,
						[
							'ID'            => $ss_post->ID,
							'post_title'    => $ss_post->post_title,
							'post_content'  => $ss_post->post_content,
							'guid'          => $ss_post->guid,
							'max_num_pages' => $ss_max_num_pages,
							'tst_author'    => get_post_meta( $ss_post->ID, $this->ss_prefix . 'author', true ),
							'tst_content'   => get_post_meta( $ss_post->ID, $this->ss_prefix . 'content', true ),
							'tst_date'      => get_post_meta( $ss_post->ID, $this->ss_prefix . 'date', true ),
							'tst_rate'      => get_post_meta( $ss_post->ID, $this->ss_prefix . 'rate', true ),
							'tst_can_edit'  => $ss_user_can_edit,
						]
					);
				}
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

		// -- insert new testimonial
		register_rest_route(
			'ss-wp-9/v1',
			'/testimonials',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'ss_wp9_insert_testimonial' ),
				'permission_callback' => function() {
					return current_user_can( 'publish_posts' );
				},
				'args'                => array(
					'author'  => array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'required'          => true,
					),
					'content' => array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'required'          => true,
					),
					'date'    => array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'required'          => true,
					),
					'rate'    => array(
						'type'              => 'int',
						'sanitize_callback' => 'absint',
						'required'          => true,
					),
				),
			)
		);

		// -- update testimonial
		register_rest_route(
			'ss-wp-9/v1',
			'/testimonials/(?P<id>\d+)',
			array(
				'methods'             => 'PATCH',
				'callback'            => array( $this, 'ss_wp9_update_testimonial' ),
				'permission_callback' => function() {
					return current_user_can( 'edit_posts' );
				},
				'args'                => array(
					'id'      => array(
						'validate_callback' => function( $param, $request, $key ) {
							return is_numeric( $param );
						},
						'required'          => true,
					),
					'author'  => array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'required'          => true,
					),
					'content' => array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'required'          => true,
					),
					'date'    => array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'required'          => true,
					),
					'rate'    => array(
						'type'              => 'int',
						'sanitize_callback' => 'absint',
						'required'          => true,
					),
				),
			)
		);

		// -- delete testimonial
		register_rest_route(
			'ss-wp-9/v1',
			'/testimonials/(?P<id>\d+)',
			array(
				'methods'             => 'DELETE',
				'callback'            => array( $this, 'ss_wp9_delete_testimonial' ),
				'permission_callback' => function() {
					return current_user_can( 'delete_posts' );
				},
				'args'                => array(
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
	 * Function for inserting new testimonials
	 *
	 * @param WP_REST_Request $request author, content, date, rate.
	 * @return object $response The message whether insert success or not.
	 */
	public function ss_wp9_insert_testimonial( WP_REST_Request $request ) {
		// -- get parameters
		$parameters = $request->get_params();

		if ( null !== $parameters ) {
			$ss_insert_args = array(
				'post_title'   => substr( $parameters['content'], 0, 200 ),
				'post_status'  => 'publish',
				'post_content' => $parameters['content'],
				'post_type'    => $this->ss_custom_post_type_name,
				'meta_input'   => array(
					$this->ss_prefix . 'author'  => $parameters['author'],
					$this->ss_prefix . 'content' => $parameters['content'],
					$this->ss_prefix . 'date'    => $parameters['date'],
					$this->ss_prefix . 'rate'    => $parameters['rate'],
				),
			);

			$response = wp_insert_post( $ss_insert_args, true );

			return $response;
		}
	}

	/**
	 * Function for deleting testimonials
	 *
	 * @param WP_REST_Request $request id.
	 * @return object $response The message whether delete success or not.
	 */
	public function ss_wp9_delete_testimonial( WP_REST_Request $request ) {
		// -- get parameters
		$parameters = $request->get_params();

		if ( null !== $parameters ) {
			$response = wp_delete_post( $parameters['id'] );

			return $response;
		}
	}

	/**
	 * Function for updating testimonials
	 *
	 * @param WP_REST_Request $request id, author, content, date, rate.
	 * @return object $response The message whether update success or not.
	 */
	public function ss_wp9_update_testimonial( WP_REST_Request $request ) {
		// -- get parameters
		$parameters = $request->get_params();

		if ( null !== $parameters ) {
			$ss_update_args = array(
				'ID'           => $parameters['id'],
				'post_title'   => substr( $parameters['content'], 0, 200 ),
				'post_status'  => 'publish',
				'post_content' => $parameters['content'],
				'post_type'    => $this->ss_custom_post_type_name,
				'meta_input'   => array(
					$this->ss_prefix . 'author'  => $parameters['author'],
					$this->ss_prefix . 'content' => $parameters['content'],
					$this->ss_prefix . 'date'    => $parameters['date'],
					$this->ss_prefix . 'rate'    => $parameters['rate'],
				),
			);

			$response = wp_update_post( $ss_update_args, true );

			return $response;
		}
	}

	/**
	 * Function for importing js script, required for submitting, deleting, and updating posts
	 */
	public function ss_wp9_enqueue_js() {
		// -- js file to submit the post ( insert, update, and delete )
		wp_enqueue_script( 'ss-api-post-submit', plugin_dir_url( __FILE__ ) . '/js/ss-api-post-submit.js', array( 'jquery' ), 'v1.0', true );

		// -- localize the script for ajax call ( insert, update, delete )
		wp_localize_script(
			'ss-api-post-submit',
			'ss_api_post_submit_action',
			array(
				'root'            => esc_url_raw( rest_url() ),
				'nonce'           => wp_create_nonce( 'wp_rest' ),
				'success'         => __( 'Data processed successfully', 'ss-wp8' ),
				'failure'         => __( 'Error.', 'ss-wp8' ),
				'current_user_id' => get_current_user_id(),
			)
		);

		// -- js file to handle pagination
		wp_enqueue_script( 'ss-api-post-pagination', plugin_dir_url( __FILE__ ) . '/js/ss-api-post-pagination.js', array( 'jquery' ), 'v1.0', true );

		// -- localize the pagination script for ajax call
		wp_localize_script(
			'ss-api-post-pagination',
			'ss_api_post_pagination',
			array(
				'root'            => esc_url_raw( rest_url() ),
				'nonce'           => wp_create_nonce( 'wp_rest' ),
				'success'         => __( 'Data processed successfully', 'ss-wp8' ),
				'failure'         => __( 'Error.', 'ss-wp8' ),
				'current_user_id' => get_current_user_id(),
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

		// -- enqueue scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'ss_wp9_enqueue_js' ) );
	}
}

// -- run the main class
$ss_wp_9_main_class = new SS_WP_9_Main();
