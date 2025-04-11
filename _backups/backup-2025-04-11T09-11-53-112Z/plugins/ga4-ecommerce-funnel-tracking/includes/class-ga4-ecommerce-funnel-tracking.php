<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://wisdmalbs.com
 * @since      1.0.0
 *
 * @package    Ga4_Ecommerce_Funnel_Tracking
 * @subpackage Ga4_Ecommerce_Funnel_Tracking/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Ga4_Ecommerce_Funnel_Tracking
 * @subpackage Ga4_Ecommerce_Funnel_Tracking/includes
 * @author     WisdmLabs <software@wisdmlabs.com>
 */
class Ga4_Ecommerce_Funnel_Tracking
{

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Ga4_Ecommerce_Funnel_Tracking_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{
		if (defined('GA4_ECOMMERCE_FUNNEL_TRACKING_VERSION')) {
			$this->version = GA4_ECOMMERCE_FUNNEL_TRACKING_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'ga4-ecommerce-funnel-tracking';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Ga4_Ecommerce_Funnel_Tracking_Loader. Orchestrates the hooks of the plugin.
	 * - Ga4_Ecommerce_Funnel_Tracking_i18n. Defines internationalization functionality.
	 * - Ga4_Ecommerce_Funnel_Tracking_Admin. Defines all hooks for the admin area.
	 * - Ga4_Ecommerce_Funnel_Tracking_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies()
	{

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-ga4-ecommerce-funnel-tracking-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-ga4-ecommerce-funnel-tracking-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-ga4-ecommerce-funnel-tracking-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-ga4-ecommerce-funnel-tracking-public.php';

		$this->loader = new Ga4_Ecommerce_Funnel_Tracking_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Ga4_Ecommerce_Funnel_Tracking_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale()
	{

		$plugin_i18n = new Ga4_Ecommerce_Funnel_Tracking_i18n();

		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks()
	{

		$plugin_admin = new Ga4_Ecommerce_Funnel_Tracking_Admin($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
		$this->loader->add_action('admin_menu', $plugin_admin, 'gtm_script_menu');
		$this->loader->add_action('admin_init', $plugin_admin, 'gtm_scripts_init');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks()
	{

		$plugin_public = new Ga4_Ecommerce_Funnel_Tracking_Public($this->get_plugin_name(), $this->get_version());
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
		if (get_option('gtm_head_script')) {
			$this->loader->add_action('wp_head', $plugin_public, 'gtm_head_script');
		}
		if (get_option('gtm_body_script')) {
			$this->loader->add_action('wp_body_open', $plugin_public, 'gtm_body_script');
		}
		// $this->loader->add_action('init', $plugin_public, 'form_tracking');
		$this->loader->add_action('wpforms_frontend_confirmation_message_after', $plugin_public, 'wp_form_tracking', 10, 3);
		// $this->loader->add_action('woocommerce_add_to_cart', $plugin_public, 'add_to_cart_event', 1, 3);
		$this->loader->add_action('wp', $plugin_public, 'list_shop_page_products');
		$this->loader->add_action('woocommerce_before_add_to_cart_quantity', $plugin_public, 'view_single_product_event');
		// $this->loader->add_action('woocommerce_before_cart', $plugin_public, 'view_cart_event');
		// $this->loader->add_action('woocommerce_before_checkout_form', $plugin_public, 'checkout_event');
		// $this->loader->add_action('woocommerce_thankyou', $plugin_public, 'purchase_event', 10, 1);
		// $this->loader->add_action('woocommerce_remove_cart_item', $plugin_public, 'remove_from_cart_event', 10, 1);
		$this->loader->add_action('gform_after_submission', $plugin_public, 'gform_tracking', 10, 2);
		// $this->loader->add_filter('gform_confirmation', $plugin_public, 'gform_tracking', 10, 4);
		// $this->loader->add_action('gform_pre_handle_confirmation', $plugin_public, 'gform_tracking', 10, 3);
		// $this->loader->add_filter('gform_entry_post_save', $plugin_public, 'gform_tracking', 10, 2);
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
	{
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name()
	{
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Ga4_Ecommerce_Funnel_Tracking_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader()
	{
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version()
	{
		return $this->version;
	}
}
