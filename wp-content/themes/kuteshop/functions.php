<?php
if ( !isset( $content_width ) ) $content_width = 900;
if ( !class_exists( 'Kuteshop_Functions' ) ) {
	class Kuteshop_Functions
	{
		/**
		 * Instance of the class.
		 *
		 * @since   1.0.0
		 *
		 * @var   object
		 */
		protected static $instance = null;

		/**
		 * Initialize the plugin by setting localization and loading public scripts
		 * and styles.
		 *
		 * @since    1.0.0
		 */
		public function __construct()
		{
			add_action( 'after_setup_theme', array( $this, 'kuteshop_setup' ) );
			add_action( 'widgets_init', array( $this, 'widgets_init' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
			if ( !is_admin() ) {
				add_filter( 'style_loader_tag', array( $this, 'custom_attr_enqueue_style' ), 10, 2 );
				add_filter( 'script_loader_tag', array( $this, 'custom_attr_enqueue_scripts' ), 10, 2 );
				add_filter( 'script_loader_src', array( $this, 'kuteshop_remove_query_string_version' ) );
				add_filter( 'style_loader_src', array( $this, 'kuteshop_remove_query_string_version' ) );
			}
			add_action( 'wp_footer', array( $this, 'kuteshop_deregister_scripts' ) );
			add_filter( 'get_default_comment_status', array( $this, 'open_default_comments_for_page' ), 10, 3 );
			add_filter( 'comment_form_fields', array( $this, 'kuteshop_move_comment_field_to_bottom' ), 10, 3 );
			$this->includes();
		}

		public function kuteshop_setup()
		{
			load_theme_textdomain( 'kuteshop', get_template_directory() . '/languages' );
			add_theme_support( 'automatic-feed-links' );
			add_theme_support( 'title-tag' );
			add_theme_support( 'post-thumbnails' );
			add_theme_support( 'custom-header' );
			add_theme_support( 'custom-background' );
			add_theme_support( 'customize-selective-refresh-widgets' );
			add_editor_style( array( 'assets/css/style.css', self::kuteshop_fonts_url() ) );
			/*This theme uses wp_nav_menu() in two locations.*/
			register_nav_menus( array(
					'primary'         => esc_html__( 'Primary Menu', 'kuteshop' ),
					'top_left_menu'   => esc_html__( 'Top Left Menu', 'kuteshop' ),
					'top_right_menu'  => esc_html__( 'Top Right Menu', 'kuteshop' ),
					'top_center_menu' => esc_html__( 'Top Center Menu', 'kuteshop' ),
					'sticky_menu'     => esc_html__( 'Sticky Menu', 'kuteshop' ),
					'vertical_menu'   => esc_html__( 'Vertical Menu', 'kuteshop' ),
				)
			);
			add_theme_support( 'html5', array(
					'search-form', 'comment-form', 'comment-list', 'gallery', 'caption',
				)
			);
			add_theme_support( 'post-formats',
				array(
					'image',
					'video',
					'quote',
					'link',
					'gallery',
					'audio',
				)
			);
			/*Support woocommerce*/
			add_theme_support( 'woocommerce' );
			add_theme_support( 'wc-product-gallery-lightbox' );
			add_theme_support( 'wc-product-gallery-slider' );
			add_theme_support( 'wc-product-gallery-zoom' );
		}

		public function kuteshop_move_comment_field_to_bottom( $fields )
		{
			$comment_field = $fields['comment'];
			unset( $fields['comment'] );
			$fields['comment'] = $comment_field;

			return $fields;
		}

		/**
		 * Register widget area.
		 *
		 * @since kuteshop 1.0
		 *
		 * @link https://codex.wordpress.org/Function_Reference/register_sidebar
		 */
		function widgets_init()
		{
			$opt_multi_slidebars = apply_filters( 'theme_get_option', 'multi_widget', '' );
			if ( is_array( $opt_multi_slidebars ) && count( $opt_multi_slidebars ) > 0 ) {
				foreach ( $opt_multi_slidebars as $value ) {
					if ( $value && $value != '' ) {
						register_sidebar( array(
								'name'          => $value['add_widget'],
								'id'            => 'custom-sidebar-' . sanitize_key( $value['add_widget'] ),
								'before_widget' => '<div id="%1$s" class="widget block-sidebar %2$s">',
								'after_widget'  => '</div>',
								'before_title'  => '<div class="title-widget widgettitle"><strong>',
								'after_title'   => '</strong></div>',
							)
						);
					}
				}
			}
			register_sidebar( array(
					'name'          => esc_html__( 'Widget Area', 'kuteshop' ),
					'id'            => 'widget-area',
					'description'   => esc_html__( 'Add widgets here to appear in your sidebar.', 'kuteshop' ),
					'before_widget' => '<div id="%1$s" class="widget %2$s">',
					'after_widget'  => '</div>',
					'before_title'  => '<h2 class="widgettitle">',
					'after_title'   => '<span class="arow"></span></h2>',
				)
			);
			register_sidebar( array(
					'name'          => esc_html__( 'Shop Widget Area', 'kuteshop' ),
					'id'            => 'shop-widget-area',
					'description'   => esc_html__( 'Add widgets here to appear in your sidebar.', 'kuteshop' ),
					'before_widget' => '<div id="%1$s" class="widget %2$s">',
					'after_widget'  => '</div>',
					'before_title'  => '<h2 class="widgettitle">',
					'after_title'   => '<span class="arow"></span></h2>',
				)
			);
			register_sidebar( array(
					'name'          => esc_html__( 'Product Widget Area', 'kuteshop' ),
					'id'            => 'product-widget-area',
					'description'   => esc_html__( 'Add widgets here to appear in your sidebar.', 'kuteshop' ),
					'before_widget' => '<div id="%1$s" class="widget %2$s">',
					'after_widget'  => '</div>',
					'before_title'  => '<h2 class="widgettitle">',
					'after_title'   => '<span class="arow"></span></h2>',
				)
			);
		}

		/**
		 * Register custom fonts.
		 */
		function kuteshop_fonts_url()
		{
			$fonts_url = '';
			/**
			 * Translators: If there are characters in your language that are not
			 * supported by Montserrat, translate this to 'off'. Do not translate
			 * into your own language.
			 */
			$montserrat          = esc_html_x( 'on', 'Montserrat font: on or off', 'kuteshop' );
			$kuteshop_typography = apply_filters( 'theme_get_option', 'typography_font_family' );
			if ( 'off' !== $montserrat ) {
				$font_families   = array();
				$font_families[] = 'Montserrat:300,300i,400,400i,500,500i,600,600i,700,700i';
				$font_families[] = 'Open+Sans:300,300i,400,400i,600,600i,700,700i';
				$font_families[] = 'Oswald:300,400,500,600';
				$font_families[] = 'Arimo:400,700';
				$font_families[] = 'Lato:400,700';
				$font_families[] = 'Pacifico';
				$match           = array(
					'Montserrat',
					'Open Sans',
					'Poppins',
					'Oswald',
					'Pacifico',
					'Arimo',
					'Lato',
				);
				if ( !in_array( $kuteshop_typography, $match ) ) {
					$font_families[] = str_replace( ' ', '+', $kuteshop_typography['family'] );
				}
				$query_args = array(
					'family' => urlencode( implode( '|', $font_families ) ),
					'subset' => urlencode( 'latin,latin-ext' ),
				);
				$fonts_url  = add_query_arg( $query_args, 'https://fonts.googleapis.com/css' );
			}

			return esc_url_raw( $fonts_url );
		}

		function custom_attr_enqueue_style( $tag, $handle )
		{
			return str_replace( ' href', ' defer="defer" href', $tag );
		}

		function custom_attr_enqueue_scripts( $tag, $handle )
		{
			if ( $handle != 'jquery-core' )
				return str_replace( ' src', ' defer="defer" src', $tag );

			return $tag;
		}

		function kuteshop_remove_query_string_version( $src )
		{
			remove_query_arg( 'ver', $src );
			remove_query_arg( 'id', $src );
			remove_query_arg( 'type', $src );
			remove_query_arg( 'version', $src );

			return $src;
		}

		function kuteshop_deregister_scripts()
		{
			wp_dequeue_script( 'vc_jquery_skrollr_js' );
		}

		/**
		 * Enqueue scripts and styles.
		 *
		 * @since kuteshop 1.0
		 */
		function scripts()
		{
			global $wp_query;
			$posts                 = $wp_query->posts;
			$kuteshop_gmap_api_key = '';
			wp_dequeue_style( 'woocommerce_prettyPhoto_css' );
			wp_dequeue_style( 'yith-wcwl-font-awesome' );
			wp_dequeue_style( 'yith-quick-view' );
			wp_dequeue_script( 'prettyPhoto' );
			foreach ( $posts as $post ) {
				if ( is_a( $post, 'WP_Post' ) && !has_shortcode( $post->post_content, 'contact-form-7' ) ) {
					wp_dequeue_script( 'contact-form-7' );
				}
				if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'kuteshop_googlemap' ) ) {
					$kuteshop_gmap_api_key = apply_filters( 'theme_get_option', 'gmap_api_key' );
				}
			}
			// Add custom fonts, used in the main stylesheet.
			wp_enqueue_style( 'kuteshop-fonts', self::kuteshop_fonts_url(), array(), null );
			// Theme stylesheet.
			wp_enqueue_style( 'animate-css' );
			wp_enqueue_style( 'bootstrap', get_theme_file_uri( '/assets/css/bootstrap.min.css' ), array(), '3.3.7' );
			wp_enqueue_style( 'flaticon', get_theme_file_uri( '/assets/css/flaticon.css' ), array(), '1.0' );
			wp_enqueue_style( 'font-awesome', get_theme_file_uri( '/assets/css/font-awesome.min.css' ), array(), '4.7.0' );
			wp_enqueue_style( 'pe-icon-7-stroke', get_theme_file_uri( '/assets/css/pe-icon-7-stroke.min.css' ), array(), '1.0' );
			wp_enqueue_style( 'chosen', get_theme_file_uri( '/assets/css/chosen.min.css' ), array(), '1.8.2' );
			wp_enqueue_style( 'slick', get_theme_file_uri( '/assets/css/slick.min.css' ), array(), '1.8.0' );
			if ( is_rtl() ) {
				wp_enqueue_style( 'kuteshop_custom_css', get_theme_file_uri( '/assets/css/style-rtl.css' ), array(), '1.0', 'all' );
			} else {
				wp_enqueue_style( 'kuteshop_custom_css', get_theme_file_uri( '/assets/css/style.css' ), array(), '1.0', 'all' );
			}
			wp_enqueue_style( 'kuteshop-main-style', get_stylesheet_uri() );
			if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
				wp_enqueue_script( 'comment-reply' );
			}
			wp_enqueue_script( 'bootstrap', get_theme_file_uri( '/assets/js/vendor/bootstrap.min.js' ), array(), '3.3.7', true );
			wp_enqueue_script( 'countdown', get_theme_file_uri( '/assets/js/vendor/jquery.countdown.min.js' ), array(), '2.2.0', true );
			wp_enqueue_script( 'chosen', get_theme_file_uri( '/assets/js/vendor/chosen.jquery.min.js' ), array(), '1.8.2', true );
			wp_enqueue_script( 'lazy_load', get_theme_file_uri( '/assets/js/vendor/jquery.lazyload.min.js' ), array(), '1.7.6', true );
			wp_enqueue_script( 'slick', get_theme_file_uri( '/assets/js/vendor/slick.min.js' ), array(), '1.8.0', true );
			wp_enqueue_script( 'kuteshop-script', get_theme_file_uri( '/assets/js/functions.min.js' ), array(), '1.0' );
			wp_localize_script( 'kuteshop-script', 'kuteshop_ajax_frontend', array(
					'ajaxurl'  => admin_url( 'admin-ajax.php' ),
					'security' => wp_create_nonce( 'kuteshop_ajax_frontend' ),
				)
			);
			$kuteshop_enable_popup        = apply_filters( 'theme_get_option', 'kuteshop_enable_popup' );
			$kuteshop_enable_popup_mobile = apply_filters( 'theme_get_option', 'kuteshop_enable_popup_mobile' );
			$kuteshop_popup_delay_time    = apply_filters( 'theme_get_option', 'kuteshop_popup_delay_time', '1' );
			$kuteshop_enable_sticky_menu  = apply_filters( 'theme_get_option', 'kuteshop_enable_sticky_menu' );
			wp_localize_script( 'kuteshop-script', 'kuteshop_global_frontend', array(
					'kuteshop_enable_popup'        => $kuteshop_enable_popup,
					'kuteshop_enable_popup_mobile' => $kuteshop_enable_popup_mobile,
					'kuteshop_popup_delay_time'    => $kuteshop_popup_delay_time,
					'kuteshop_enable_sticky_menu'  => $kuteshop_enable_sticky_menu,
					'kuteshop_gmap_api_key'        => trim( $kuteshop_gmap_api_key ),
					'kuteshop_parallax'            => get_theme_file_uri( '/assets/js/vendor/parallax.min.js' ),
				)
			);
		}

		/**
		 * Filter whether comments are open for a given post type.
		 *
		 * @param string $status Default status for the given post type,
		 *                             either 'open' or 'closed'.
		 * @param string $post_type Post type. Default is `post`.
		 * @param string $comment_type Type of comment. Default is `comment`.
		 * @return string (Maybe) filtered default status for the given post type.
		 */
		function open_default_comments_for_page( $status, $post_type, $comment_type )
		{
			if ( 'page' == $post_type ) {
				return 'open';
			}

			return $status;
		}

		public function includes()
		{
			include_once( get_parent_theme_file_path( '/framework/framework.php' ) );
			define( 'CS_ACTIVE_FRAMEWORK', true );
			define( 'CS_ACTIVE_METABOX', true );
			define( 'CS_ACTIVE_TAXONOMY', true );
			define( 'CS_ACTIVE_SHORTCODE', false );
			define( 'CS_ACTIVE_CUSTOMIZE', false );
		}
	}

	new  Kuteshop_Functions();
}