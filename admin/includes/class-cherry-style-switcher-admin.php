<?php

/**
 * Sets up the admin functionality for the plugin.
 *
 * @package   Cherry_Style_Switcher_Admin
 * @author    Cherry Team
 * @license   GPL-3.0
 * @link      http://www.cherryframework.com/
 * @copyright 2015 Cherry Team
 */
class Cherry_Style_Switcher_Admin
{
	/**
	 * Holds the instances of this class.
	 *
	 * @since 1.0.0
	 * @var   object
	 */
	private static $instance = NULL;

	/**
	 * Sets up needed actions/filters for the admin to initialize.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function __construct()
	{
		add_filter('cherry_defaults_settings', array($this, 'add_cherry_options'), 11);
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'), 20);
	}

	/**
	 * Register and enqueue admin-facing javascript.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_admin_scripts(){
		wp_enqueue_script(
			'cherry-switcher-script-admin',
			trailingslashit( CHERRY_STYLE_SWITCHER_URI ) . 'admin/includes/assets/js/script_admin.js',
			array('jquery', 'interface-builder'),
			CHERRY_STYLE_SWITCHER_VERSION,
			TRUE
		);

		$isShow = FALSE;

		if (function_exists('cherry_get_option'))
		{
			$isShow = cherry_get_option('show') === 'true';
		}

		wp_localize_script( 'cherry-switcher-script-admin', 'cherryOptions', array('isShow' => $isShow) );
	}

	/**
	 * Adds `Style Switcher settings` tab with options.
	 *
	 * @since 1.0.0
	 *
	 * @param array $sections
	 */
	public function add_cherry_options($sections){
		$style_switcher_options = array();
		$isShow = cherry_get_option('show') === 'true';

		//$listSkins  = $this->scanDir('/css/skins');
		//$listNav    = $this->scanDir('/css/nav');

		$style_switcher_options['show'] = array(
			'type' => 'switcher',
			'title' => __('Style Switcher', 'cherry-style-switcher'),
			'hint' => array(
				'type' => 'text',
				'content' => __('Enable/disable displaying of Style Switcher on site.', 'cherry-style-switcher'),
			),
			'value' => 'false',
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
	 * Returns the instance.
	 *
	 * @since  1.0.0
	 * @return object
	 */
	public static function get_instance()
	{

		// If the single instance hasn't been set, set it now.
		if (NULL == self::$instance){
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Returns list filename/.
	 *
	 * @since  1.0.0
	 * @param str $pathDir
	 *
	 * @return array
	 */
	private function scanDir($pathDir)
	{
		$listFiles = array();

		$files = scandir( CHERRY_STYLE_SWITCHER_UPLOADS_DIR . $pathDir );

		foreach ($files as $file)
		{
			if ('.' == $file || '..' == $file)
			{
				continue;
			}

			$info = pathinfo($file);

			if (isset($info['extension']) && !empty($info['extension']))
			{
				$extension = $info['extension'];
			}
			else
			{
				$extension = pathinfo($file, PATHINFO_EXTENSION);
			}

			$listFiles[] = basename($file, '.' . $extension);
		}

		return $listFiles;
	}
}

Cherry_Style_Switcher_Admin::get_instance();