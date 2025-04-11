<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://wisdmalbs.com
 * @since      1.0.0
 *
 * @package    Ga4_Ecommerce_Funnel_Tracking
 * @subpackage Ga4_Ecommerce_Funnel_Tracking/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Ga4_Ecommerce_Funnel_Tracking
 * @subpackage Ga4_Ecommerce_Funnel_Tracking/public
 * @author     WisdmLabs <software@wisdmlabs.com>
 */
class Ga4_Ecommerce_Funnel_Tracking_Public
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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/ga4-ecommerce-funnel-tracking-public.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/ga4-ecommerce-funnel-tracking-public.js', array('jquery'), $this->version, false);
		// wp_enqueue_script('ga4_form_event', plugin_dir_url(__FILE__) . 'js/ga4_form_event.js', array(), $this->version, false);
		// wp_enqueue_script('wp-form-tracking-js', plugin_dir_url(__FILE__) . 'js/wp-form-tracking.js', array('jquery'), $this->version, false);
	}

	/**
	 * Enqueues the GTM (Google Tag Manager) head script.
	 *
	 * This function is responsible for enqueueing the GTM head script file.
	 * The script file is added to the head section of the website.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */

	public function gtm_head_script()
	{
		// wp_enqueue_script('gtm-head-script', plugin_dir_url(__FILE__) . '/js/gtm_head_script.js', array(), $this->version, false);

		$gtm_head_script = get_option('gtm_head_script');
		if (!empty($gtm_head_script)) {
			echo $gtm_head_script;
		}
	}

	/**
	 * Enqueues the GTM (Google Tag Manager) body script.
	 *
	 * This function is responsible for enqueuing the GTM body script code snippet.
	 * The script is added to the body section of the website within a noscript tag.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */

	public function gtm_body_script()
	{
		$gtm_body_script = get_option('gtm_body_script');
		if (!empty($gtm_body_script)) {
			echo $gtm_body_script;
		}
	}

	public function wp_form_tracking($confirmation, $form_data, $fields)
	{
		// Get the user's information from the form submission
		$form_title = $form_data['title'];
		$field_values = array();

		// Iterate through the form fields and extract values based on the field labels
		foreach ($fields as $field) {
			$value = isset($field['value']) ? $field['value'] : '';
			$field_values[$field['name']] = $value;
		}

		// Prepare the data to be sent to Google Analytics
		$data = array(
			'form_title'  => $form_title,
			'form_fields' => $field_values,
		);

?>
		<script>
			console.log("form-tracking-file");
			var form_data = <?php echo json_encode($data); ?>;
			dataLayer.push({
				event: form_data.form_title ?
					form_data.form_title + " form_tracking" : "form_tracking",
				ecommerce: {
					form_title: form_data.form_title,
					form_fields: form_data.form_fields,
				},
			});
		</script>
<?php
	}

	function gform_tracking($entry, $form)
	{
		// Get the user's information from the form submission
		$form_title = $form['title'];
		$field_values = array();

		// Iterate through the form fields and extract values based on the field labels
		foreach ($form["fields"] as $field) {
			$value = rgar($entry, $field->id);
			$field_values[$field->label] = $value;
		}

		// Prepare the data to be sent to Google Analytics
		$data = array(
			'form_title'  => $form_title,
			'form_fields' => $field_values,
		);

		// Enqueue the JavaScript file and pass the form data as an object to the script
		wp_enqueue_script('form_tracking_script', plugin_dir_url(__FILE__) . 'js/gform_tracking.js', array(), '1.0', true);
		wp_localize_script('form_tracking_script', 'form_data_object', $data);
	}


	/**
	 * Lists and enqueues products on the shop page for tracking purposes.
	 *
	 * This function retrieves the products displayed on the shop page,
	 * prepares the necessary data for tracking, and enqueues the script
	 * responsible for handling the tracking.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */

	public function list_shop_page_products()
	{
		if (!is_shop()) {
			return;
		}
		$data = array(
			'currency' => get_woocommerce_currency(),
			'items' => array(),
		);

		if (have_posts()) {
			while (have_posts()) {
				the_post();
				$product = wc_get_product(get_the_ID());
				$product_data = $product->get_data();

				$item_data = array(
					'item_id' => $product->get_id(),
					'item_name' => $product_data['name'],
					'discount' => 0,
					'item_brand' => get_bloginfo('name'),
					'item_category' => 'Uncategorized',
					'price' => floatval($product_data['price']),
					'quantity' => 1,
				);

				if ($product->get_sale_price() !== '') {
					$discount_amount = $product->get_regular_price() - $product->get_sale_price();
					$item_data['discount'] = $discount_amount;
				}

				$category_ids = $product->get_category_ids();
				if (!empty($category_ids)) {
					$category = get_term($category_ids[0], 'product_cat');
					$item_data['item_category'] = $category->name;
				}

				$data['items'][] = $item_data;
			}
		}

		wp_enqueue_script('list_shop_page_products', plugin_dir_url(__FILE__) . 'js/list_shop_page_products.js', array('jquery'), $this->version, false);
		wp_localize_script('list_shop_page_products', 'item_list_data', $data);
	}

	/**
	 * Handles the event when a product is added to the cart.
	 *
	 * This function is triggered when a product is added to the cart.
	 * It retrieves the necessary data for tracking the added item, such as
	 * product information, price, and quantity. It then enqueues the script
	 * responsible for handling the tracking.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $cart_id          The cart ID.
	 * @param int    $product_id       The ID of the product being added.
	 * @param int    $request_quantity The quantity of the product being added.
	 * @return void
	 */

	public function add_to_cart_event($cart_id, $product_id, $request_quantity)
	{
		$product_data = wc_get_product($product_id);
		$category_ids = $product_data->get_category_ids();

		$data = array(
			'currency' => get_woocommerce_currency(),
			'value' => (float) $product_data->get_price(),
			'item' => array(
				'item_id' => (string) $product_id,
				'item_name' => $product_data->get_name(),
				'item_brand' => get_bloginfo('name'),
				'item_category' => !empty($category_ids) ? get_term($category_ids[0], 'product_cat')->name : "Uncategorized",
				'price' => (float) $product_data->get_price(),
				'quantity' => $request_quantity
			)
		);

		wp_enqueue_script('add_to_cart', plugin_dir_url(__FILE__) . 'js/add_to_cart.js', array('jquery'), $this->version, false);
		wp_localize_script('add_to_cart', 'item_data', $data);
	}

	/**
	 * Handles the event when a single product is viewed.
	 *
	 * This function is triggered when a single product page is viewed.
	 * It retrieves the necessary data for tracking the viewed product, such as
	 * product information, price, and category. It then enqueues the script
	 * responsible for handling the tracking.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */

	public function view_single_product_event()
	{
		if (!is_product()) {
			return;
		}
		global $product;
		$product_data = $product->get_data();
		$data = array(
			'currency' => get_woocommerce_currency(),
			'items' => array(),
		);

		$item_data = array(
			'item_id' => $product->get_id(),
			'item_name' => $product_data['name'],
			'discount' => 0,
			'item_brand' => get_bloginfo('name'),
			'item_category' => 'Uncategorized',
			'price' => floatval($product_data['price']),
		);

		if ($product->get_sale_price() !== '') {
			$discount_amount = $product->get_regular_price() - $product->get_sale_price();
			$item_data['discount'] = $discount_amount;
		}

		$category_ids = $product->get_category_ids();
		if (!empty($category_ids)) {
			$category = get_term($category_ids[0], 'product_cat');
			$item_data['item_category'] = $category->name;
		}

		wp_enqueue_script('single_product_view', plugin_dir_url(__FILE__) . 'js/single_product_view.js', array('jquery'), $this->version, false);
		wp_localize_script('single_product_view', 'data', $item_data);
	}

	/**
	 * Handles the event when the cart page is viewed.
	 *
	 * This function is triggered when the cart page is viewed. It retrieves
	 * the necessary data for tracking the products in the cart, such as
	 * product information, price, and quantity. It then enqueues the script
	 * responsible for handling the tracking.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */

	public function view_cart_event()
	{
		// die("Stopped");
		if (!is_cart()) {
			return;
		}

		$woocommerce = WC();
		$data = array(
			'currency' => get_woocommerce_currency(),
			'items' => array(),
			// 'coupon_code' => '',
		);

		foreach ($woocommerce->cart->get_cart() as $cart_item_key => $cart_item) {
			$product = $cart_item['data'];
			$product_data = $product->get_data();

			$item_data = array(
				'item_id' => $product->get_id(),
				'item_name' => $product_data['name'],
				'discount' => 0,
				'item_brand' => get_bloginfo('name'),
				'item_category' => 'Uncategorized',
				'price' => floatval($product_data['price']),
				'quantity' => $cart_item['quantity'],
			);

			if ($product->get_sale_price() !== '') {
				$discount_amount = $product->get_regular_price() - $product->get_sale_price();
				$item_data['discount'] = $discount_amount;
			}

			$category_ids = $product->get_category_ids();
			if (!empty($category_ids)) {
				$category = get_term($category_ids[0], 'product_cat');
				$item_data['item_category'] = $category->name;
			}

			$data['items'][] = $item_data;
		}
		// if ($woocommerce->cart->applied_coupons) {
		// 	$coupon_codes = $woocommerce->cart->get_coupons();
		// 	$data['coupon_code'] = reset($coupon_codes)->get_code();
		// }
		wp_enqueue_script('view_cart', plugin_dir_url(__FILE__) . 'js/view_cart.js', array('jquery'), $this->version, false);
		wp_localize_script('view_cart', 'cart_data', $data);
	}

	/**
	 * Handles the event when the checkout page is viewed.
	 *
	 * This function is triggered when the checkout page is viewed. It retrieves
	 * the necessary data for tracking the products in the cart, such as
	 * product information, price, and quantity. It then enqueues the script
	 * responsible for handling the tracking.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */

	public function checkout_event()
	{
		if (!is_checkout()) {
			return;
		}

		$woocommerce = WC();
		$data = array(
			'currency' => get_woocommerce_currency(),
			'items' => array(),
			'coupon_code' => 'Not Applied',
		);

		foreach ($woocommerce->cart->get_cart() as $cart_item_key => $cart_item) {
			$product = $cart_item['data'];
			$product_data = $product->get_data();

			$item_data = array(
				'item_id' => $product->get_id(),
				'item_name' => $product_data['name'],
				'discount' => 0,
				'item_brand' => get_bloginfo('name'),
				'item_category' => 'Uncategorized',
				'price' => floatval($product_data['price']),
				'quantity' => $cart_item['quantity'],
			);

			if ($product->get_sale_price() !== '') {
				$discount_amount = $product->get_regular_price() - $product->get_sale_price();
				$item_data['discount'] = $discount_amount;
			}

			$category_ids = $product->get_category_ids();
			if (!empty($category_ids)) {
				$category = get_term($category_ids[0], 'product_cat');
				$item_data['item_category'] = $category->name;
			}

			$data['items'][] = $item_data;
		}

		$applied_coupons = $woocommerce->cart->get_applied_coupons();
		if (!empty($applied_coupons)) {
			$data['coupon_codes'] = $applied_coupons;
		}

		if ($woocommerce->cart->applied_coupons) {
			$coupon_codes = $woocommerce->cart->get_coupons();
			$data['coupon_code'] = reset($coupon_codes)->get_code();
		}


		wp_enqueue_script('checkout_event', plugin_dir_url(__FILE__) . 'js/checkout.js', array('jquery'), $this->version, false);
		wp_localize_script('checkout_event', 'cart_data', $data);
	}

	/**
	 * Handles the event when the purchase is completed.
	 *
	 * This function is triggered when an order is placed and the purchase is completed.
	 * It retrieves the necessary order details such as order number, total, billing email,
	 * billing phone, payment method, country, and customer ID. It then enqueues the script
	 * responsible for handling the completion tracking.
	 *
	 * @since 1.0.0
	 *
	 * @param int $order_id The ID of the completed order.
	 * @return void
	 */

	public function purchase_event($order_id)
	{
		// die("Hello");
		$order = wc_get_order($order_id);
		// var_dump($order);

		$order_details = array(
			'order_number' => $order->get_order_number(),
			'order_total' => $order->get_total(),
			'billing_email' => $order->get_billing_email(),
			'billing_phone' => $order->get_billing_phone(),
			'payment_method' => $order->get_payment_method_title(),
			'country' => $order->get_billing_country(),
			'customer_id' => $order->get_customer_id(),
		);

		wp_enqueue_script('purchase_complete', plugin_dir_url(__FILE__) . 'js/purchase_complete.js', array('jquery'), $this->version, false);
		wp_localize_script('purchase_complete', 'order_data', $order_details);
	}
}
