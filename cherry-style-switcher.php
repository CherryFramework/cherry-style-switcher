<?php
/**
 * Plugin Name: Cherry Style Switcher
 * Plugin URI:  http://www.cherryframework.com/
 * Description: Cherry style switcher plugin for WordPress.
 * Version:     1.0.0
 * Author:      Cherry Team
 * Author URI:  http://www.cherryframework.com/
 * Text Domain: cherry-style-switcher
 * License:     GPL-3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
// If class 'Cherry_Style_Switcher' not exists.
if ( !class_exists( 'Cherry_Style_Switcher' ) ) {

	/**
	 * Sets up and initializes Style Switcher plugin.
	 *
	 * @since 1.0.0
	 */
	class Cherry_Style_Switcher {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * @var bool
		 */
		public $isShow;

		/**
		 * Sets up needed actions/filters for the plugin to initialize.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			// Set the constants needed by the plugin.
			add_action( 'plugins_loaded', array( $this, 'constants' ), 1 );

			// Internationalize the text strings used.
			add_action( 'plugins_loaded', array( $this, 'lang' ),      2 );

			// Load the functions files.
			add_action( 'plugins_loaded', array( $this, 'includes' ),  3 );

			// Load the admin files.
			add_action( 'plugins_loaded', array( $this, 'admin' ),     4 );

			// Load public-facing style sheet.
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 100);
			// Load public-facing JavaScript.
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			// Register activation and deactivation hook.
			register_activation_hook( __FILE__, array( $this, 'activation'     ) );
			register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );

			// Display panel
			add_action( 'wp_head', array($this, 'display_panel') );
		}

		/**
		 * Defines constants for the plugin.
		 *
		 * @since 1.0.0
		 */
		function constants() {
			/**
			 * Set the version number of the plugin.
			 *
			 * @since 1.0.0
			 */
			define( 'CHERRY_STYLE_SWITCHER_VERSION', '1.0.0' );

			/**
			 * Set the slug of the plugin.
			 *
			 * @since 1.0.0
			 */
			define( 'CHERRY_STYLE_SWITCHER_SLUG', basename( dirname( __FILE__ ) ) );

			/**
			 * Set constant path to the plugin directory.
			 *
			 * @since 1.0.0
			 */
			define( 'CHERRY_STYLE_SWITCHER_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );

			/**
			 * Set constant path to the plugin URI.
			 *
			 * @since 1.0.0
			 */
			define( 'CHERRY_STYLE_SWITCHER_URI', trailingslashit( plugin_dir_url( __FILE__ ) ) );

			/**
			 * Set constant path to the plugin uploads PATH.
			 *
			 * @since 1.0.0
			 */
			define('CHERRY_STYLE_SWITCHER_UPLOADS', '/uploads/cherry-style-switcher');

			/**
			 * Set constant path to the plugin uploads URL.
			 *
			 * @since 1.0.0
			 */
			define('CHERRY_STYLE_SWITCHER_UPLOADS_URL', WP_CONTENT_URL . CHERRY_STYLE_SWITCHER_UPLOADS);

			/**
			 * Set constant path to the plugin uploads DIR.
			 *
			 * @since 1.0.0
			 */
			define('CHERRY_STYLE_SWITCHER_UPLOADS_DIR', WP_CONTENT_DIR . CHERRY_STYLE_SWITCHER_UPLOADS);
		}

		/**
		 * Loads files from the '/inc' folder.
		 *
		 * @since 1.0.0
		 */
		function includes() {
			if ( is_admin() ) {
				require_once( CHERRY_STYLE_SWITCHER_DIR . 'admin/includes/class-cherry-update/class-cherry-plugin-update.php' );

				$Cherry_Plugin_Update = new Cherry_Plugin_Update();
				$Cherry_Plugin_Update -> init( array(
						'version'			=> CHERRY_STYLE_SWITCHER_VERSION,
						'slug'				=> CHERRY_STYLE_SWITCHER_SLUG,
						'repository_name'	=> CHERRY_STYLE_SWITCHER_SLUG
				));
			}
		}

		/**
		 * Loads the translation files.
		 *
		 * @since 1.0.0
		 */
		function lang() {
			load_plugin_textdomain( 'cherry-style-switcher', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}

		/**
		 * Loads admin files.
		 *
		 * @since 1.0.0
		 */
		function admin() {
			if ( is_admin() ) {
				require_once( CHERRY_STYLE_SWITCHER_DIR . 'admin/includes/class-cherry-style-switcher-admin.php' );
			}
		}

		/**
		 * Register and enqueue public-facing style sheet.
		 *
		 * @since 1.0.0
		 */
		public function enqueue_styles() {
			$this->isShow = cherry_get_option('show') === 'true';
            $skin         = cherry_get_option('skin');
            $nav          = cherry_get_option('nav');

            if (!$this->isShow)
            {
                if (isset($_COOKIE['cookie_nav'])){
                    setcookie('cookie_nav', '');
                }

                if (isset($_COOKIE['cookie_layout'])){
                    setcookie('cookie_layout', '');
                }

                if (isset($_COOKIE['cookie_skins'])){
                    setcookie('cookie_skins', '');
                }
            }

            wp_enqueue_style( 'scrollbar', CHERRY_STYLE_SWITCHER_UPLOADS_URL . '/css/jquery.mcustomscrollbar.css', array(), CHERRY_STYLE_SWITCHER_VERSION );
            wp_enqueue_style( 'cherry-style-switcher', CHERRY_STYLE_SWITCHER_UPLOADS_URL . '/css/style.css', array(), CHERRY_STYLE_SWITCHER_VERSION );
            wp_enqueue_style( 'style-switcher-nav', CHERRY_STYLE_SWITCHER_UPLOADS_URL . '/css/nav/' . $nav . '.css', array(), CHERRY_STYLE_SWITCHER_VERSION );
            wp_enqueue_style( 'cherry-dynamic', CHERRY_STYLE_SWITCHER_UPLOADS_URL . '/css/skins/' . $skin . '.css', array(), CHERRY_STYLE_SWITCHER_VERSION );
            wp_enqueue_style( 'style-switcher-layout', CHERRY_STYLE_SWITCHER_UPLOADS_URL . '/css/layout/wide.css'   , array(), CHERRY_STYLE_SWITCHER_VERSION );
		}

		/**
		 * Register and enqueue public-facing javascript.
		 *
		 * @since 1.0.0
		 */
		public function enqueue_scripts() {
			wp_enqueue_script( 'modernizr', trailingslashit( CHERRY_STYLE_SWITCHER_URI ) . 'includes/assets/js/libs/modernizr-2.7.1.min.js', array( 'jquery' ), CHERRY_STYLE_SWITCHER_VERSION, true);
			wp_enqueue_script( 'cookie', trailingslashit( CHERRY_STYLE_SWITCHER_URI ) . 'includes/assets/js/libs/jquery.cookie.js', array( 'jquery' ), CHERRY_STYLE_SWITCHER_VERSION, true);
			wp_enqueue_script( 'scrollba', trailingslashit( CHERRY_STYLE_SWITCHER_URI ) . 'includes/assets/js/libs/jquery.mcustomscrollbar.min.js', array( 'jquery' ), CHERRY_STYLE_SWITCHER_VERSION, true);
			wp_enqueue_script( 'mousewheel', trailingslashit( CHERRY_STYLE_SWITCHER_URI ) . 'includes/assets/js/libs/jquery.mousewheel.min.js', array( 'jquery' ), CHERRY_STYLE_SWITCHER_VERSION, true);
			wp_enqueue_script( 'cherry-style-switcher', trailingslashit( CHERRY_STYLE_SWITCHER_URI ) . 'includes/assets/js/script.js', array( 'jquery' ), CHERRY_STYLE_SWITCHER_VERSION, true);
		}

		/**
		 * On plugin activation.
		 *
		 * @since 1.0.0
		 */
		function activation() {
		}

		/**
		 * On plugin deactivation.
		 *
		 * @since 1.0.0
		 */
		function deactivation() {
//			$setting = get_option( 'cherry-options' );
//			pr(get_option( $setting['id'] ));
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @return object
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance )
				self::$instance = new self;

			return self::$instance;
		}

		public function display_panel() {

			if (is_user_logged_in())
			{
				$user_info = wp_get_current_user();
				$access_roles = cherry_get_option( 'access-frontend-panel' );

				if (isset($user_info->roles) && !empty($user_info->roles)
				    && is_array($access_roles) && !empty($access_roles))
				{
					$role_user = $user_info->roles[0];

					if (in_array($role_user, $access_roles))
					{
						if ( $this->isShow )
						{
							require_once( CHERRY_STYLE_SWITCHER_DIR . 'views/panel.php' );
						}
					}
				}
			}
		}
	}

	Cherry_Style_Switcher::get_instance();
}