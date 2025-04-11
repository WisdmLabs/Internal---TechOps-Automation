<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wisdmalbs.com
 * @since      1.0.0
 *
 * @package    Ga4_Ecommerce_Funnel_Tracking
 * @subpackage Ga4_Ecommerce_Funnel_Tracking/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Ga4_Ecommerce_Funnel_Tracking
 * @subpackage Ga4_Ecommerce_Funnel_Tracking/admin
 * @author     WisdmLabs <software@wisdmlabs.com>
 */
class Ga4_Ecommerce_Funnel_Tracking_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Ga4_Ecommerce_Funnel_Tracking_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Ga4_Ecommerce_Funnel_Tracking_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/ga4-ecommerce-funnel-tracking-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Ga4_Ecommerce_Funnel_Tracking_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Ga4_Ecommerce_Funnel_Tracking_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/ga4-ecommerce-funnel-tracking-admin.js', array('jquery'), $this->version, false);
	}

	public function gtm_script_menu()
	{
		add_menu_page(
			'GTM Scripts',
			'GA4 Ecommerce Funnel Tracking',
			'manage_options',
			'wdm-ga4-menu',
			array($this, 'gtm_scripts_settings'),
			'dashicons-tag'
		);
	}

	public function gtm_scripts_settings()
	{
		echo '<div class="wrap">';
		echo '<h1>' . get_admin_page_title() . '</h1>';
		echo '<form method="post" action="options.php">';
		settings_fields('gtm_scripts_options');
		do_settings_sections('gtm_scripts');
		submit_button();
		echo '</form>';
		echo '</div>';
	}

	public function gtm_scripts_init()
	{
		register_setting('gtm_scripts_options', 'gtm_head_script');
		register_setting('gtm_scripts_options', 'gtm_body_script');
		add_settings_section('gtm_scripts_section', 'GTM Scripts', '', 'gtm_scripts');
		add_settings_field('gtm_head_script', 'GTM Head Script', array($this, 'gtm_head_script_callback'), 'gtm_scripts', 'gtm_scripts_section');
		add_settings_field('gtm_body_script', 'GTM Body Script', array($this, 'gtm_body_script_callback'), 'gtm_scripts', 'gtm_scripts_section');
	}

	public function gtm_head_script_callback()
	{
		$gtm_head_script = get_option('gtm_head_script');
		echo '<textarea name="gtm_head_script" rows="5" cols="50">' . esc_textarea($gtm_head_script) . '</textarea>';
	}

	public function gtm_body_script_callback()
	{
		$gtm_body_script = get_option('gtm_body_script');
		echo '<textarea name="gtm_body_script" rows="5" cols="50">' . esc_textarea($gtm_body_script) . '</textarea>';
	}
}
