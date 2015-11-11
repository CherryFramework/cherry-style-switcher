<?php
/**
 * Panel render
 *
 * @package   Cherry Style Switcher
 * @author    Cherry Team
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

// If class Cherry_Style_Switcher_Panel not exists.
if ( ! class_exists( 'Cherry_Style_Switcher_Panel' ) ) {

	/**
	 * Sets up and initializes Style Switcher plugin.
	 *
	 * @since 1.0.0
	 */
	class Cherry_Style_Switcher_Panel {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		public $default_settings = null;

		public static $preset_settings = null;

		/**
		 * Sets up needed actions/filters for the plugin to initialize.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			add_action( 'wp_ajax_cherry_preset_import', array( $this, 'preset_import' ) );
			add_action( 'wp_ajax_nopriv_cherry_preset_import', array( $this, 'preset_import' ) );

			add_action( 'cherry_dynamic_styles', array( $this, 'dynamic_styles' ) );

			$this->default_settings = apply_filters( 'cherry_preset_switcher_default_settings',
				array(
					'layout_group' => array(
						'group_name'	=> __( 'Layouts', 'cherry-style-switcher' ),
						'presets'		=> array(
							'wide' => array(
								'label'			=> __( 'Wide', 'cherry-style-switcher' ),
								'description'	=> __( 'Select layout pattern for website. Wide layout will fit window width.', 'cherry-style-switcher' ),
								'thumbnail'		=> 'wide.svg',
								'preset'		=> 'wide.options',
							),
							'boxed' => array(
								'label'			=> __( 'Boxed', 'cherry-style-switcher' ),
								'description'	=> __( 'Select layout pattern for website. Boxed layout will have fixed width.', 'cherry-style-switcher' ),
								'thumbnail'		=> 'boxed.svg',
								'preset'		=> 'boxed.options',
							),
						)
					),
					'sidebar_group' => array(
						'group_name'	=> __( 'Sidebars', 'cherry-style-switcher' ),
						'presets'		=> array(
							'left_sidebar' => array(
								'label'			=> __('Left sidebar', 'cherry-style-switcher'),
								'description'	=> __( 'Page with left sidebar position', 'cherry-style-switcher' ),
								'thumbnail'		=> 'left-sidebar.svg',
								'preset'		=> 'left-sidebar.options',
							),
							'right_sidebar' => array(
								'label'			=> __( 'Right sidebar', 'cherry-style-switcher' ),
								'description'	=> __( 'Page with right sidebar position', 'cherry-style-switcher' ),
								'thumbnail'		=> 'right-sidebar.svg',
								'preset'		=> 'right-sidebar.options',
							),
							'none_sidebar' => array(
								'label'			=> __( 'No sidebar', 'cherry-style-switcher' ),
								'description'	=> __( 'Page without sidebar', 'cherry-style-switcher' ),
								'thumbnail'		=> 'none-sidebar.svg',
								'preset'		=> 'none-sidebar.options',
							),
						)
					),
				)
			);
		}// __construct end

		/**
		 * Panel render.
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function panel_render() {
			$demo_mode_class = Cherry_Style_Switcher::is_demo_mode() ? ' demo-mode-class' : '';

			$html = '';
			$html .= '<div class="style-switcher-panel' . $demo_mode_class . '">';
			$html .= '<div class="preset-spinner">';
				$html .= '<div class="spinner-folding-cube"><div class="spinner-cube1 spinner-cube"></div><div class="spinner-cube2 spinner-cube"></div><div class="spinner-cube4 spinner-cube"></div><div class="spinner-cube3 spinner-cube"></div></div>';
				$html .= '<span>' . __( 'Applying changes...', 'cherry-style-switcher' ) . '</span>';
				$html .= '<div class="clear"></div>';
			$html .= '</div>';
				$html .= '<div class="panel-inner">';
				$html .= apply_filters( 'cherry_style_panel_before', '' );
				$html .= '<h3 class="theme-name"><span>' . __( 'Theme', 'cherry-style-switcher' ) . '</span>' . wp_get_theme() . '</h3>';

					foreach ( $this->default_settings as $group_key => $group_setting ) {
						$html .= '<div class="group-wrap">';
						$html .= '<h5 class="group-name"><span>' . $group_setting['group_name'] . '</span></h5>';
						$html .= '<ul class="preset-list" data-group="' . $group_key . '">';

							foreach ( $group_setting['presets'] as $preset_key => $preset_setting ) {
								$tooltip = ! empty( $preset_setting['description'] ) ? ' title="' . esc_html( $preset_setting['description'] ) .'"' : '';
								$item_class = ( isset( $preset_setting['soon'] ) && true == $preset_setting['soon'] ) ? ' class="coming-soon"' : '';
								$html .= '<li data-preset="' . $preset_key . '"' . $tooltip . '' . $item_class . '>';
									$thumbnail = self::get_thumbnail( $preset_setting['thumbnail'] );
									$html .= '<div class="inner">';
										$html .= '<div class="thumbnail">';
											$html .= '<img src="' . $thumbnail . '" alt="' . $preset_setting['label'] . '">';
										$html .= '</div>';
										$html .= '<span class="title">' . $preset_setting['label']  . '</span>';
									$html .= '</div>';
								$html .= '</li>';
							}

						$html .= '</ul>';
						$html .= '<div class="clear"></div>';
						$html .= '</div>';
					}
				$html .= '</div>';
				$html .= '<div class="panel-toggle"><i class="fa fa-cogs"></i></div>';
				$html .= apply_filters( 'cherry_style_panel_after', '' );

				//add nonce
				wp_nonce_field( 'cherry_preset_import', 'preset-import-nonce', false );
			$html .= '</div>';
			$html .= '<div class="site-cover"></div>';

			echo $html;
		}

		/**
		 * Add to dynamic css
		 *
		 * @since 1.0.0
		 */
		public function dynamic_styles() {
			$preset_user_css = cherry_get_option( 'preset-user-css' );

			echo $preset_user_css;
		}

		/**
		 * Get current preset thumbnail
		 *
		 * @param  string $thumbnail name
		 * @return bool/string thumbnail uri
		 *
		 * @since 1.0.0
		 */
		public static function get_thumbnail( $thumbnail = '' ) {

			$child_preset_dir = CHILD_THEME_DIR . '/child-presets/thumbnails/';
			$child_preset_uri = CHILD_THEME_URI . '/child-presets/thumbnails/';
			$plugin_preset_dir = CHERRY_STYLE_SWITCHER_DIR . 'default_presets/thumbnails/';
			$plugin_preset_uri = CHERRY_STYLE_SWITCHER_URI . 'default_presets/thumbnails/';

			if ( file_exists( $child_preset_dir . $thumbnail ) && ! empty( $thumbnail ) ) {
				return $child_preset_uri . $thumbnail;
			}

			if ( file_exists( $plugin_preset_dir . $thumbnail ) && ! empty( $thumbnail ) ) {
				return $plugin_preset_uri . $thumbnail;
			} else {
				return $plugin_preset_uri . 'inherit.svg';
			}

			return false;
		}

		/**
		 * Get current preset json
		 *
		 * @param string $json name.
		 * @return bool/string $json uri
		 *
		 * @since 1.0.0
		 */
		public static function get_preset_json( $json = '' ) {

			$child_preset_dir = CHILD_THEME_DIR . '/child-presets/presets/';
			$child_preset_uri = CHILD_THEME_URI . '/child-presets/presets/';
			$plugin_preset_dir = CHERRY_STYLE_SWITCHER_DIR . 'default_presets/presets/';
			$plugin_preset_uri = CHERRY_STYLE_SWITCHER_URI . 'default_presets/presets/';

			if ( file_exists( $child_preset_dir . $json ) && ! empty( $json ) ) {
				return $child_preset_dir . $json;
			}

			if ( file_exists( $plugin_preset_dir . $json ) && ! empty( $json ) ) {
				return $plugin_preset_dir . $json;
			} else {
				return $plugin_preset_dir . 'inherit.json';
			}

			return false;
		}

		/**
		 * Ajax import preset
		 *
		 * @since 4.0.0
		 */
		function preset_import() {

			if ( ! empty( $_POST ) && array_key_exists( 'preset', $_POST ) && array_key_exists( 'group', $_POST ) && array_key_exists( '_wpnonce', $_POST ) ) {
				$preset = $_POST['preset'];
				$group = $_POST['group'];
				$_wpnonce = $_POST['_wpnonce'];

				// generate query arg url
				$query_arg_url = $_SERVER['HTTP_REFERER'];

				$query_arg_url = add_query_arg( array( '_group' => $group, '_preset' => $preset ), $query_arg_url );

				$validate = check_ajax_referer( 'cherry_preset_import', $_wpnonce, false );
				if ( ! $validate ) {
					wp_die( __( 'Invalid request', 'cherry' ), __( 'Error. Invalid request', 'cherry' ) );
				}

				if ( false !== self::get_preset_json( $this->default_settings[ $group ]['presets'][ $preset ]['preset'] ) ) {
					$file_name = self::get_preset_json( $this->default_settings[ $group ]['presets'][ $preset ]['preset'] );

					$file_content = self::get_contents( $file_name );
					$file_content = !is_wp_error( $file_content ) ? $file_content : '{}';

					if ( 'string' !== gettype( $file_content ) ) {
						wp_send_json( array( 'type' => 'error', 'url' => $query_arg_url ) );
					}

					$import_options = json_decode( $file_content, true );

					$import_statics = isset( $import_options['statics'] ) ? $import_options['statics'] : array() ;
					$import_options = isset( $import_options['options'] ) ? $import_options['options'] : $import_options ;

					if ( ! is_array( $import_options ) || empty( $import_options ) ) {
						wp_send_json( array( 'type' => 'error', 'url' => $query_arg_url ) );
					}

					// get current options array
					$settings        = get_option( 'cherry-options' );
					$current_options = get_option( $settings['id'] );

					if ( Cherry_Style_Switcher::is_demo_mode() ) {
						$current_options = isset( $_SESSION['demo_options_storage']['options'] ) ? $_SESSION['demo_options_storage']['options'] : $_SESSION['demo_options_storage'] ;
					}

					$result = array();

					foreach ( $current_options as $section => $data ) {
						foreach ( $data['options-list'] as $opt => $val ) {

							if ( isset( $import_options[ $opt ] ) ) {
								$result[ $section ]['options-list'][ $opt ] = $import_options[ $opt ];
							} else {
								$result[ $section ]['options-list'][ $opt ] = $current_options[ $section ]['options-list'][ $opt ];
							}
						}
					}

					if ( Cherry_Style_Switcher::is_demo_mode() ) {
						$_SESSION['demo_options_storage']['options'] = $result;

						if ( ! empty( $import_statics ) && isset( $import_statics ) ) {
							$_SESSION['demo_options_storage']['statics'] = $import_statics;
						}

						wp_send_json( array( 'type' => 'success', 'url' => $query_arg_url ) );
					}

					update_option( $settings['id'], $result );

					if ( ! empty( $import_statics ) && isset( $import_statics ) ) {
						update_option( $settings['id'] . '_statics', $import_statics );
					}
				}

				wp_send_json( array( 'type' => 'success', 'url' => $query_arg_url ) );
			}
		}

		/**
		 * Read template (static).
		 *
		 * @since  1.0.0
		 * @return bool|WP_Error|string - false on failure, stored text on success.
		 */
		public static function get_contents( $file ) {

			if ( ! function_exists( 'WP_Filesystem' ) ) {
				include_once( ABSPATH . '/wp-admin/includes/file.php' );
			}

			WP_Filesystem();
			global $wp_filesystem;

			// Check for existence
			if ( ! $wp_filesystem->exists( $file ) ) {
				return false;
			}

			// Read the file.
			$content = $wp_filesystem->get_contents( $file );

			if ( ! $content ) {
				return new WP_Error( 'reading_error', 'Error when reading file' ); // Return error object.
			}

			return $content;
		}

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
	}
}

?>
