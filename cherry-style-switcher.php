<?php
/**
 * Plugin Name: Cherry Style Switcher
 * Plugin URI:  http://www.cherryframework.com/
 * Description: Cherry Style Switcher plugin for WordPress.
 * Version:     1.0.5
 * Author:      Cherry Team
 * Author URI:  http://www.cherryframework.com/
 * Text Domain: cherry-style-switcher
 * License:     GPL-3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path: /languages
 *
 * @package  Cherry Style Switcher
 * @category Core
 * @author   Cherry Team
 * @license  GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

// If class 'Cherry_Style_Switcher' not exists.
if ( ! class_exists( 'Cherry_Style_Switcher' ) ) {

	/**
	 * Sets up and initializes Preset Switcher plugin.
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
		 * Panel show status
		 *
		 * @since 1.0.0
		 * @var   bool
		 */
		public $isShow = true;

		/**
		 * Sets up needed actions/filters for the plugin to initialize.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			// Set the constants needed by the plugin.
			add_action( 'plugins_loaded', array( $this, 'constants' ), 1 );

			// Internationalize the text strings used.
			add_action( 'plugins_loaded', array( $this, 'lang' ), 2 );

			// Load the functions files.
			add_action( 'plugins_loaded', array( $this, 'includes' ), 3 );

			// Load the admin files.
			add_action( 'plugins_loaded', array( $this, 'admin' ), 4 );

			// Panel init
			add_action( 'init', array( $this, 'panel_init' ) );

			// Load public-facing style sheet.
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 100 );
			// Load public-facing JavaScript.
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			// Register activation and deactivation hook.
			register_activation_hook( __FILE__, array( $this, 'activation' ) );
			register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );

			// Display panel
			add_action( 'wp_footer', array( $this, 'display_panel' ) );

			add_filter( 'cherry_defaults_settings', array( $this, 'add_cherry_options' ) );

			add_filter( 'cherry_option_value_source_array', array( $this, 'value_source_array' ) );

			add_filter( 'cherry_static_current_statics', array( $this, 'current_statics' ) );

			add_filter( 'cherry_compiler_static_css', array( $this, 'add_style_to_compiler' ) );
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
			define( 'CHERRY_STYLE_SWITCHER_VERSION', '1.0.5' );

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
			 * Get_stylesheet_directory.
			 *
			 * @since 1.0.0
			 */
			define( 'CHILD_THEME_DIR', get_stylesheet_directory() );

			/**
			 * Get_stylesheet_directory_uri
			 *
			 * @since 1.0.0
			 */
			define( 'CHILD_THEME_URI', get_stylesheet_directory_uri() );
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
						'repository_name'	=> CHERRY_STYLE_SWITCHER_SLUG,
				));
			}

			require_once( CHERRY_STYLE_SWITCHER_DIR . 'views/panel.php' );
		}

		/**
		 * Panel class init
		 *
		 * @since 1.0.0
		 */
		function panel_init() {

			if ( self::is_demo_mode() ) {

				$settings = get_option( 'cherry-options' );
				$current_options = get_option( $settings['id'] );
				$current_statics = get_option( $settings['id'] . '_statics' );

				if ( ! session_id() ) {
					session_start();
				}

				if ( ! isset( $_SESSION['demo_options_storage'] ) ) {
					$_SESSION['demo_options_storage']['options'] = $current_options;
					$_SESSION['demo_options_storage']['statics'] = $current_statics;
				}
			}

			$this->switcher_panel = new Cherry_Style_Switcher_Panel();
			$this->isShow = apply_filters( 'cherry_preset_switcher_show_panel', true );
		}

		/**
		 * Value source array
		 *
		 * @since 1.0.0
		 */
		function value_source_array( $options_source_array ) {
			$logged_in = is_user_logged_in();

			if ( isset( $_SESSION['demo_options_storage'] ) && ! $logged_in ) {
				$options_source_array = isset( $_SESSION['demo_options_storage']['options'] ) ? $_SESSION['demo_options_storage']['options'] : $_SESSION['demo_options_storage'] ;
			}
			return $options_source_array;
		}

		/**
		 * Value current statics
		 *
		 * @return array current settings
		 * @since 1.0.0
		 */
		function current_statics( $current_statics ) {
			$logged_in = is_user_logged_in();

			if ( isset( $_SESSION['demo_options_storage'] ) && ! $logged_in ) {
				$current_statics = isset( $_SESSION['demo_options_storage']['statics'] ) ? $_SESSION['demo_options_storage']['statics'] : array() ;
			}

			return $current_statics;
		}

		/**
		 * Is demo state enabled
		 *
		 * @since 1.0.0
		 *
		 * @return boolean demo state
		 */
		public static function is_demo_mode() {

			if ( ! is_user_logged_in() ) {

				if ( 'true' === self::cherry_swither_get_option( 'demo-mode', 'false' ) ) {
					return true;
				}

				return false;
			}

			return false;
		}

		/**
		 * Is panel enabled
		 *
		 * @since 1.0.0
		 *
		 * @return boolean show panel
		 */
		public static function is_panel_show() {

			if ( isset( $_GET['action'] ) && 'yith-woocompare-view-table' === $_GET['action'] ) {
				return false;
			}

			if ( ! is_user_logged_in() ) {

				if ( 'true' === self::cherry_swither_get_option( 'panel-show', 'false' ) && 'true' === self::cherry_swither_get_option( 'demo-mode', 'false' ) ) {
					return true;
				}

				return false;

			} else {
				$user_info = wp_get_current_user();
				$access_roles = self::cherry_swither_get_option( 'access-frontend-panel', false );

				if ( isset( $user_info->roles ) && ! empty( $user_info->roles ) && is_array( $access_roles ) && ! empty( $access_roles ) ) {
					$role_user = $user_info->roles[0];

					if ( in_array( $role_user, $access_roles ) ) {

						if ( 'true' === self::cherry_swither_get_option( 'panel-show', 'false' ) ) {
							return true;
						}
					}
				}
			}

			return false;
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
				// include
			}
		}

		/**
		 * Register and enqueue public-facing style sheet.
		 *
		 * @since 1.0.0
		 */
		public function enqueue_styles() {
			wp_enqueue_style( 'cherry-style-switcher', CHERRY_STYLE_SWITCHER_URI . 'includes/assets/css/style.css', array(), CHERRY_STYLE_SWITCHER_VERSION );
		}

		/**
		 * Pass style handle to CSS compiler.
		 *
		 * @since 1.0.5
		 *
		 * @param array $handles CSS handles to compile.
		 */
		function add_style_to_compiler( $handles ) {
			$handles = array_merge(
				array( 'cherry-style-switcher' => CHERRY_STYLE_SWITCHER_URI . 'includes/assets/css/style.css' ),
				$handles
			);

			return $handles;
		}

		/**
		 * Register and enqueue public-facing javascript.
		 *
		 * @since 1.0.0
		 */
		public function enqueue_scripts() {
			if ( self::cherry_swither_get_option( 'panel-show', 'false' ) === 'true' ) {
				wp_enqueue_script( 'jquery-ui-tooltip' );
				wp_enqueue_script( 'cherry-api', trailingslashit( CHERRY_STYLE_SWITCHER_URI ) . 'includes/assets/js/cherry-api.min.js', array( 'jquery' ), CHERRY_STYLE_SWITCHER_VERSION, true );
				wp_enqueue_script( 'jquery-json', trailingslashit( CHERRY_STYLE_SWITCHER_URI ) . 'includes/assets/js/jquery.json.min.js', array( 'jquery' ), CHERRY_STYLE_SWITCHER_VERSION, true );
				wp_enqueue_script( 'cherry-style-switcher-init', trailingslashit( CHERRY_STYLE_SWITCHER_URI ) . 'includes/assets/js/init.min.js', array( 'jquery' ), CHERRY_STYLE_SWITCHER_VERSION, true );

				// ajax js object preset_import_ajax
				wp_localize_script( 'cherry-style-switcher-init', 'preset_import_ajax', array( 'url' => admin_url( 'admin-ajax.php' ) ) );
			}
		}

		/**
		 * Adds `Style Switcher settings` tab with options.
		 *
		 * @since 1.0.0
		 *
		 * @param array $sections updated options array.
		 * @return array $sections
		 */
		public function add_cherry_options( $sections ) {
			$style_switcher_options = array();

			$style_switcher_options['panel-show'] = array(
				'type' => 'switcher',
				'title' => __( 'Style Switcher', 'cherry-style-switcher' ),
				'hint' => array(
					'type' => 'text',
					'content' => __( 'Enable/disable displaying of Style Switcher on site.', 'cherry-style-switcher' ),
				),
				'value' => 'true',
				'class' => 'cherry-switcher-panel',
				'toggle'		=> array(
					'true_toggle'	=> __( 'Enabled', 'cherry' ),
					'false_toggle'	=> __( 'Disabled', 'cherry' ),
					'true_slave'	=> 'style-switcher-true-slave',
					'false_slave'	=> 'style-switcher-false-slave',
				),
			);

			$style_switcher_options['access-frontend-panel'] = array(
				'type'			=> 'select',
				'title'			=> __( 'Visible To:', 'cherry-style-switcher' ),
				'label'			=> '',
				'description'	=> '',
				'multiple'		=> true,
				'value'			=> array( 'administrator' ),
				'class'			=> 'cherry-multi-select',
				'options'		=> $this->_get_roles(),
				'master'		=> 'style-switcher-true-slave',
			);

			$style_switcher_options['preset-user-css'] = array(
				'type'			=> 'ace-editor',
				'title'			=> __( 'Custom style CSS', 'cherry' ),
				'description'	=> __( 'Define style CSS styling.', 'cherry-style-switcher' ),
				'editor_mode'	=> 'css',
				'editor_theme'	=> 'monokai',
				'value'			=> '',
				'master'		=> 'style-switcher-true-slave',
			);

			$style_switcher_options['demo-mode'] = array(
				'type' => 'switcher',
				'title' => __( 'Demo mode', 'cherry-style-switcher' ),
				'hint' => array(
					'type' => 'text',
					'content' => __( 'Enable/disable demo mode. Demo mode is used for not logged users (guests)', 'cherry-style-switcher' ),
				),
				'value' => 'false',
				'class' => 'cherry-switcher-panel',
				'toggle'		=> array(
					'true_toggle'	=> __( 'Enabled', 'cherry' ),
					'false_toggle'	=> __( 'Disabled', 'cherry' ),
				),
				'master'		=> 'style-switcher-true-slave',
			);

			$sections['style-switcher-section'] = array(
				'name' => __( 'Style Switcher', 'cherry-style-switcher' ),
				'icon' => 'dashicons dashicons-art',
				'priority' => 130,
				'options-list' => $style_switcher_options,
			);

			return $sections;
		}

		/**
		 * Get option by name from theme options
		 *
		 * @since  1.0.0
		 *
		 * @uses   cherry_get_option  use cherry_get_option from Cherry framework if exist
		 *
		 * @param  string $name    option name to get.
		 * @param  mixed  $default  default option value.
		 * @return mixed           option value
		 */
		public static function cherry_swither_get_option( $name, $default = false ) {

			if ( function_exists( 'cherry_get_option' ) ) {
				$result = cherry_get_option( $name , $default );
				return $result;
			}
			return $default;
		}

		/**
		 * Get all roles
		 *
		 * @return array
		 */
		private function _get_roles() {
			$roles = array();
			global $wp_roles;
			$all_roles = $wp_roles->roles;

			if ( isset( $all_roles ) && ! empty( $all_roles ) ) {

				foreach ( $all_roles as $role => $value ) {
					$roles[ $role ] = $value['name'];
				}
			}

			return $roles;

		}

		/**
		 * On plugin activation.
		 *
		 * @since 1.0.0
		 */
		function activation() {}

		/**
		 * On plugin deactivation.
		 *
		 * @since 1.0.0
		 */
		function deactivation() {}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @return object
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		/**
		 * Render the panel
		 *
		 * @since  1.0.0
		 *
		 * @return void
		 */
		public function display_panel() {

			if ( self::is_panel_show() ) {
				$this->switcher_panel->panel_render();
			}

		}
	}

	Cherry_Style_Switcher::get_instance();
}
