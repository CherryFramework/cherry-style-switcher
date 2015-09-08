<?php
/**
 * Plugin Name: Cherry Style Switcher
 * Plugin URI:  http://www.cherryframework.com/
 * Description: Cherry Style Switcher plugin for WordPress.
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
		 * @var bool
		 */
		public $isShow = true;

		/**
		 * @var object
		 */
		public $switcher_panel;

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

			// Panel init
			add_action( 'init', array( $this, 'panel_init' ) );

			// Load public-facing style sheet.
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 100);
			// Load public-facing JavaScript.
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			// Register activation and deactivation hook.
			register_activation_hook( __FILE__, array( $this, 'activation'     ) );
			register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );

			// Display panel
			add_action( 'wp_head', array($this, 'display_panel') );

			add_filter('cherry_defaults_settings', array( $this, 'add_cherry_options' ) );

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
			 * get_stylesheet_directory.
			 *
			 * @since 1.0.0
			 */
			define( 'CHILD_THEME_DIR', get_stylesheet_directory() );

			/**
			 * get_stylesheet_directory_uri
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
						'repository_name'	=> CHERRY_STYLE_SWITCHER_SLUG
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

			$this->switcher_panel = new Cherry_Style_Switcher_Panel();

			$this->isShow = apply_filters( 'cherry_preset_switcher_show_panel', true );
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
			//$this->isShow = cherry_get_option('panel_show') === 'true';
			wp_enqueue_style( 'cherry-style-switcher', CHERRY_STYLE_SWITCHER_URI . 'includes/assets/css/style.css', array(), CHERRY_STYLE_SWITCHER_VERSION );
		}

		/**
		 * Register and enqueue public-facing javascript.
		 *
		 * @since 1.0.0
		 */
		public function enqueue_scripts() {
			if ( cherry_get_option('panel_show')  === 'true' ){
				wp_enqueue_script( 'jquery-ui-tooltip' );
				wp_enqueue_script( 'cherry-api', trailingslashit( CHERRY_STYLE_SWITCHER_URI ) . 'includes/assets/js/cherry-api.js', array( 'jquery' ), CHERRY_STYLE_SWITCHER_VERSION, true);
				wp_enqueue_script( 'jquery-json', trailingslashit( CHERRY_STYLE_SWITCHER_URI ) . 'includes/assets/js/jquery.json.js', array( 'jquery' ), CHERRY_STYLE_SWITCHER_VERSION, true);
				wp_enqueue_script( 'cherry-style-switcher-init', trailingslashit( CHERRY_STYLE_SWITCHER_URI ) . 'includes/assets/js/init.js', array( 'jquery' ), CHERRY_STYLE_SWITCHER_VERSION, true);

				//ajax js object preset_import_ajax
				wp_localize_script( 'cherry-style-switcher-init', 'preset_import_ajax', array( 'url' => admin_url('admin-ajax.php') ) );
			}
		}

		/**
		 * Adds `Style Switcher settings` tab with options.
		 *
		 * @since 1.0.0
		 *
		 * @param array $sections
		 */
		public function add_cherry_options( $sections ){
			$style_switcher_options = array();

			$style_switcher_options['panel_show'] = array(
				'type' => 'switcher',
				'title' => __('Style Switcher', 'cherry-style-switcher'),
				'hint' => array(
					'type' => 'text',
					'content' => __('Enable/disable displaying of Style Switcher on site.', 'cherry-style-switcher'),
				),
				'value' => 'true',
				'class' => 'cherry-switcher-panel',
				'toggle'		=> array(
					'true_toggle'	=> __( 'Enabled', 'cherry' ),
					'false_toggle'	=> __( 'Disabled', 'cherry' ),
					'true_slave'	=> 'style-switcher-true-slave',
					'false_slave'	=> 'style-switcher-false-slave'
				),
			);

			$style_switcher_options['access-frontend-panel'] = array(
				'type'			=> 'select',
				'title'			=> __('Visible To:', 'cherry-style-switcher'),
				'label'			=> '',
				'description'	=> '',
				'multiple'		=> true,
				'value'			=> array('administrator'),
				'class'			=> 'cherry-multi-select',
				'options'		=> $this->_get_roles(),
				'master'		=> 'style-switcher-true-slave',
			);

			$style_switcher_options['preset-user-css'] = array(
				'type'         => 'ace-editor',
				'title'        => __( 'Custom style CSS', 'cherry' ),
				'description'  => __( 'Define style CSS styling.', 'cherry-style-switcher' ),
				'editor_mode'  => 'css',
				'editor_theme' => 'monokai',
				'value'        => '',
				'master'		=> 'style-switcher-true-slave',
			);

			$sections['style-switcher-section'] = array(
				'name' => __('Style Switcher', 'cherry-style-switcher'),
				'icon' => 'dashicons dashicons-art',
				'priority' => 130,
				'options-list' => $style_switcher_options,
			);

			return $sections;
		}

		/**
		 * Get all roles
		 *
		 * @return array
		 */
		private function _get_roles()
		{
			$roles = array();
			global $wp_roles;
			$all_roles = $wp_roles->roles;

			if (isset($all_roles) && !empty($all_roles))
			{
				foreach ($all_roles as $role => $value)
				{
					$roles[$role] = $value['name'];
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
			if ( null == self::$instance )
				self::$instance = new self;

			return self::$instance;
		}

		public function display_panel() {

			if( isset( $_GET['action'] ) && $_GET['action'] === 'yith-woocompare-view-table'){
				return false;
			}

			if ( is_user_logged_in() ){
				$user_info = wp_get_current_user();
				$access_roles = cherry_get_option( 'access-frontend-panel' );
				if ( isset( $user_info->roles ) && !empty( $user_info->roles ) && is_array( $access_roles ) && !empty( $access_roles ) ){
					$role_user = $user_info->roles[0];
					if ( in_array( $role_user, $access_roles ) ){
						if ( cherry_get_option('panel_show') === 'true' ){
							$this->switcher_panel->panel_render();
							//Cherry_Preset_Switcher_Panel::panel_render();
						}
					}
				}
			}
		}
	}

	Cherry_Style_Switcher::get_instance();
}