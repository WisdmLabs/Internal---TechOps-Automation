<?php
/**
 * WordPress booster V 3.1 by tagDiv
 */


do_action('td_wp_booster_before');  //@todo is probably not used by anyone


// theme utility files
require_once('td_global.php');
require_once('td_options.php');

//td_global::$td_options = get_option(TD_THEME_OPTIONS_NAME); //read the theme settings once

require_once('td_util.php');

require_once('td_email.php');


// load the wp_booster_api
require_once('td_api.php');


// !!!! 22.07.2016 - this should run on the td_global_after hook. BUT it should be first on that hook
// I've checked td_global, td_util & td_api and it's safe to load this here for pathing the td_global::$td_options
require_once('wp-admin/panel/panel_core/td_panel_data_source.php');

// Was moved before 'td_global_after' hook, to allow api to get fonts
require_once("td_fonts.php");           // no autoload - fonts support


// hook here to use the theme api
do_action('td_global_after');

require_once('td_first_install.php');

require_once('td_global_blocks.php');   // no autoload -

require_once('td_menu.php');   // theme menu support
td_api_autoload::add('td_nav_menu_edit_walker', td_global::$get_template_directory . '/legacy/common/wp_booster/td_menu_back.php');



require_once('td_social_icons.php');    // no autoload (almost always needed) - The social icons
require_once('td_js_buffer.php');       // no autoload - the theme always outputs JS form this buffer
require_once('td_unique_posts.php');    // no autoload - unique posts (uses hooks + do_action('td_wp_boost_new_module'); )
require_once('td_module.php');          // module builder
require_once('td_block.php');           // block builder
require_once('td_cake.php');
require_once('td_js_generator.php');    // no autoload - the theme always outputs JS
require_once('td_block_widget.php');    // no autoload - used to make widgets from our blocks
require_once('td_background.php');      // background support - is not autoloaded due to issues
require_once('td_background_render.php');
require_once('td_style.php');           // - base class for block' styles
require_once('td_transients_manager.php'); // transients manager class

require_once('td_autoload_classes.php');  //used to autoload classes [modules, blocks]
// Every class after this (that has td_ in the name) is auto loaded only when it's required
td_api_autoload::add('td_log', td_global::$get_template_directory . '/legacy/common/wp_booster/td_log.php');
td_api_autoload::add('td_css_inline', td_global::$get_template_directory . '/legacy/common/wp_booster/td_css_inline.php');
td_api_autoload::add('td_login', td_global::$get_template_directory . '/legacy/common/wp_booster/td_login.php');
td_api_autoload::add('td_category_template', td_global::$get_template_directory . '/legacy/common/wp_booster/td_category_template.php');
td_api_autoload::add('td_category_top_posts_style', td_global::$get_template_directory . '/legacy/common/wp_booster/td_category_top_posts_style.php');
td_api_autoload::add('td_page_generator', td_global::$get_template_directory . '/legacy/common/wp_booster/td_page_generator.php');   //not used on some homepages
td_api_autoload::add('td_block_layout', td_global::$get_template_directory . '/legacy/common/wp_booster/td_block_layout.php');
td_api_autoload::add('td_template_layout', td_global::$get_template_directory . '/legacy/common/wp_booster/td_template_layout.php');
td_api_autoload::add('td_css_compiler', td_global::$get_template_directory . '/legacy/common/wp_booster/td_css_compiler.php');
td_api_autoload::add('td_css_res_compiler', td_global::$get_template_directory . '/legacy/common/wp_booster/td_css_res_compiler.php');
td_api_autoload::add('td_module_single_base', td_global::$get_template_directory . '/legacy/common/wp_booster/td_module_single_base.php');
td_api_autoload::add('td_smart_list', td_global::$get_template_directory . '/legacy/common/wp_booster/td_smart_list.php');
td_api_autoload::add('td_remote_cache', td_global::$get_template_directory . '/legacy/common/wp_booster/td_remote_cache.php');
td_api_autoload::add('td_css_buffer', td_global::$get_template_directory . '/legacy/common/wp_booster/td_css_buffer.php');
td_api_autoload::add('td_data_source', td_global::$get_template_directory . '/legacy/common/wp_booster/td_data_source.php');
td_api_autoload::add('td_help_pointers', td_global::$get_template_directory . '/legacy/common/wp_booster/td_help_pointers.php');
td_api_autoload::add('td_block_template', td_global::$get_template_directory . '/legacy/common/wp_booster/td_block_template.php');
td_api_autoload::add('td_social_sharing', td_global::$get_template_directory . '/legacy/common/wp_booster/td_social_sharing.php');



/* ----------------------------------------------------------------------------
 * PageView support
 */
td_api_autoload::add('td_page_views', td_global::$get_template_directory . '/legacy/common/wp_booster/td_page_views.php');

$excluded_post_types = array( 'acf-field-group', 'acf-field', 'product_variation', 'product','page', 'shop_order', 'shop_order_refund', 'shop_coupon', 'shop_webhook', 'vc_grid_item', 'tdb_templates', 'amp_validated_url', 'tds_email', 'tds_locker' );
$post_type = 'post';
if (!empty($_GET['post_type'])) {
    $post_type = $_GET['post_type'];
}

if ( !in_array($post_type, $excluded_post_types) ) {
    add_filter('manage_' . $post_type . '_posts_columns', array('td_page_views', 'on_manage_posts_columns_views'));
    add_action('manage_' . $post_type . '_posts_custom_column', array('td_page_views', 'on_manage_posts_custom_column'), 5, 2);
}



/* ----------------------------------------------------------------------------
 * JSON LD Breadcrumbs
 */
add_action('wp_head', array('td_page_generator', 'get_breadcrumbs_json_ld'), 45);


/* ----------------------------------------------------------------------------
 * Review support
 */
td_api_autoload::add('td_review', td_global::$get_template_directory . '/legacy/common/wp_booster/td_review.php');
add_filter('save_post', array('td_review', 'on_save_post_update_review'), 11);



/* ----------------------------------------------------------------------------
 * Ajax support
 */
td_api_autoload::add('td_ajax', td_global::$get_template_directory . '/legacy/common/wp_booster/td_ajax.php');
// ajax: block ajax hooks
add_action('wp_ajax_nopriv_td_ajax_block', array('td_ajax', 'on_ajax_block'));
add_action('wp_ajax_td_ajax_block',        array('td_ajax', 'on_ajax_block'));

// ajax: Renders loop pagination, for now used only on categories
add_action('wp_ajax_nopriv_td_ajax_loop', array('td_ajax', 'on_ajax_loop'));
add_action('wp_ajax_td_ajax_loop',        array('td_ajax', 'on_ajax_loop'));

// ajax: site wide search
add_action('wp_ajax_nopriv_td_ajax_search', array('td_ajax', 'on_ajax_search'));
add_action('wp_ajax_td_ajax_search',        array('td_ajax', 'on_ajax_search'));

// ajax: login window login
add_action('wp_ajax_nopriv_td_mod_login', array('td_ajax', 'on_ajax_login'));
add_action('wp_ajax_td_mod_login',        array('td_ajax', 'on_ajax_login'));

// ajax: login window register
add_action('wp_ajax_nopriv_td_mod_register', array('td_ajax', 'on_ajax_register'));
add_action('wp_ajax_td_mod_register',        array('td_ajax', 'on_ajax_register'));

add_action('wp_ajax_nopriv_td_mod_subscription_register', array('td_ajax', 'on_ajax_subscription_register'));
add_action('wp_ajax_td_mod_subscription_register',        array('td_ajax', 'on_ajax_subscription_register'));

add_action('wp_ajax_nopriv_td_resend_subscription_activation_link', array('td_ajax', 'on_ajax_resend_subscription_activation_link'));
add_action('wp_ajax_td_resend_subscription_activation_link',        array('td_ajax', 'on_ajax_resend_subscription_activation_link'));

// ajax: login window remember pass?
add_action('wp_ajax_nopriv_td_mod_remember_pass', array('td_ajax', 'on_ajax_remember_pass'));
add_action('wp_ajax_td_mod_remember_pass',        array('td_ajax', 'on_ajax_remember_pass'));

// ajax: login reset pass
add_action('wp_ajax_nopriv_td_mod_subscription_reset_pass', array('td_ajax', 'on_ajax_subscription_reset_pass'));
add_action('wp_ajax_td_mod_subscription_reset_pass',        array('td_ajax', 'on_ajax_subscription_reset_pass'));

// ajax: update views - via ajax only when enable in panel
add_action('wp_ajax_nopriv_td_ajax_update_views', array('td_ajax', 'on_ajax_update_views'));
add_action('wp_ajax_td_ajax_update_views',        array('td_ajax', 'on_ajax_update_views'));

// ajax: get views - via ajax only when enabled in panel
add_action('wp_ajax_nopriv_td_ajax_get_views', array('td_ajax', 'on_ajax_get_views'));
add_action('wp_ajax_td_ajax_get_views',        array('td_ajax', 'on_ajax_get_views'));


// Secure Ajax
add_action('wp_ajax_td_ajax_new_sidebar', array('td_ajax', 'on_ajax_new_sidebar'));        // ajax: admin panel - new sidebar #sec
add_action('wp_ajax_td_ajax_delete_sidebar', array('td_ajax', 'on_ajax_delete_sidebar'));  // ajax: admin panel - delete sidebar #sec

//ajax: translation
add_action('wp_ajax_td_ajax_get_translation', array('td_ajax', 'on_ajax_get_translation')); // ajax: get translations

//ajax: activation
add_action('wp_ajax_td_ajax_check_envato_code', array('td_ajax', 'on_ajax_check_envato_code'));
add_action('wp_ajax_td_ajax_register_forum_user', array('td_ajax', 'on_ajax_register_forum_user'));
//add_action('wp_ajax_td_ajax_manual_activation', array('td_ajax', 'on_ajax_manual_activation'));

//ajax: db check
add_action( 'wp_ajax_td_ajax_db_check', array( 'td_ajax', 'on_ajax_db_check' ) ); // .. seems not to be used anymore @todo check if it's still used and maybe removed

//ajax: theme status check
add_action( 'wp_ajax_td_ajax_theme_status_check', array( 'td_ajax', 'on_ajax_check_theme_status' ) ); // .. seems not to be used anymore @todo check if it's still used and maybe removed

//ajax: system status - TD Log - toggle status ( on/off )
add_action( 'wp_ajax_td_ajax_system_status_toggle_td_log', array( 'td_ajax', 'on_ajax_system_status_toggle_td_log' ) );

//ajax: get style from template
add_action( 'wp_ajax_td_ajax_get_template_style', array( 'td_ajax', 'on_ajax_get_template_style' ) );

//ajax: render shortcodes
add_action( 'wp_ajax_td_render_shortcodes', array( 'td_ajax', 'on_ajax_render_shortcodes' ) );

//ajax: theme updates
add_action( 'wp_ajax_td_ajax_change_theme_version', array( 'td_ajax', 'on_ajax_change_theme_version' ) );
add_action( 'wp_ajax_td_ajax_check_theme_version', array( 'td_ajax', 'on_ajax_check_theme_version' ) );

//ajax: panel backup
add_action( 'wp_ajax_td_ajax_backup_panel', array( 'td_ajax', 'on_ajax_backup_panel' ) );

add_action('wp_ajax_nopriv_td_ajax_backup_limit', array('td_ajax', 'on_ajax_backup_limit'));
add_action('wp_ajax_td_ajax_backup_limit',        array('td_ajax', 'on_ajax_backup_limit'));

// ajax: module video modal
add_action('wp_ajax_nopriv_td_ajax_video_modal', array('td_ajax', 'on_ajax_video_modal'));
add_action('wp_ajax_td_ajax_video_modal',        array('td_ajax', 'on_ajax_video_modal'));

// ajax: flickr album modal
add_action('wp_ajax_nopriv_td_ajax_flickr_modal', array('td_ajax', 'on_ajax_flickr_modal'));
add_action('wp_ajax_td_ajax_flickr_modal',        array('td_ajax', 'on_ajax_flickr_modal'));

// ajax: dark mode
add_action('wp_ajax_nopriv_td_ajax_dark_mode', array('td_ajax', 'on_ajax_dark_mode'));
add_action('wp_ajax_td_ajax_dark_mode',        array('td_ajax', 'on_ajax_dark_mode'));

// ajax: facebook/instagram with access token
require_once( 'td_fb_ig_business.php' ); // facebook/instagram business accounts

// ajax: twitter with access token
require_once( 'td_twitter.php' ); // twitter account support

// ajax: system status video playlist cache video info
add_action( 'wp_ajax_td_ajax_video_cache_videos', array( 'td_ajax', 'on_ajax_video_cache_videos' ) );

// ajax: comments captcha retrieve details
add_action('wp_ajax_nopriv_td_ajax_submit_captcha', array('td_ajax', 'on_ajax_submit_captcha'));
add_action('wp_ajax_td_ajax_submit_captcha',        array('td_ajax', 'on_ajax_submit_captcha'));

// ajax: facebook login retrieve details
add_action('wp_ajax_nopriv_td_ajax_fb_login_get_credentials', array('td_ajax', 'on_ajax_fb_login_get_credentials'));
add_action('wp_ajax_td_ajax_fb_login_get_credentials',        array('td_ajax', 'on_ajax_fb_login_get_credentials'));

// ajax: facebook login retrieve details
add_action('wp_ajax_nopriv_td_ajax_fb_login_user', array('td_ajax', 'on_ajax_fb_login_user'));
add_action('wp_ajax_td_ajax_fb_login_user',        array('td_ajax', 'on_ajax_fb_login_user'));


/**
 * Fix for page templates ( after the wp booster was moved to td composer the page templates select metabox stoped showing to pages admin editor )
 * @since tf refactor to comply with wp requirements
 */
add_filter( 'theme_page_templates', function( $page_templates ){

	$page_templates = array_merge(
		array(
			'page-pagebuilder-latest.php' => 'Pagebuilder + latest articles + pagination',
			'page-pagebuilder-title.php' => 'Pagebuilder + page title',
		),
		$page_templates
	);

	return $page_templates;
});



/**
 * This points the 'Pagebuilder + latest articles + pagination' && 'Pagebuilder + page title' page templates to the ones found in composer legacy
 * @since tf refactor to comply with wp requirements
 */
add_filter( 'page_template', function( $page_template ){
	if ( is_page_template( 'page-pagebuilder-latest.php' ) ) {
		$page_template = TDC_PATH_LEGACY . '/page-pagebuilder-latest.php';
	} elseif ( is_page_template( 'page-pagebuilder-title.php' ) ) {
		$page_template = TDC_PATH_LEGACY . '/page-pagebuilder-title.php';
	}

	return $page_template;
});


//// !!!! MUST
//add_action('wp_footer', 'td_wp_footer_debug');
//function td_wp_footer_debug() {
//	td_api_base::_debug_show_autoloaded_components();
//}


if (TD_DEBUG_IOS_REDIRECT) {
	require_once('td_ios_redirect.php' );
}

// at this point it's not safe to update the Theme API because it's already used
do_action('td_wp_booster_loaded'); //used by our plugins



/* ----------------------------------------------------------------------------
 * Add theme support for features
 */
add_theme_support('post-thumbnails');
add_theme_support('automatic-feed-links');
add_theme_support('html5', array('comment-list', 'comment-form', 'search-form', 'gallery', 'caption'));
add_theme_support('woocommerce');
add_theme_support('bbpress');

//Gutenberg
if ('Newspaper' === TD_THEME_NAME) {
	add_theme_support('align-wide');
	add_theme_support('align-full');
	add_theme_support('editor-font-sizes', array(
		array(
			'name' => 'small',
			'shortName' => 'S',
			'size' => 11,
			'slug' => 'small'
		),
		array(
			'name' => 'regular',
			'shortName' => 'M',
			'size' => 15,
			'slug' => 'regular'
		),
		array(
			'name' => 'large',
			'shortName' => 'L',
			'size' => 32,
			'slug' => 'large'
		),
		array(
			'name' => 'larger',
			'shortName' => 'XL',
			'size' => 50,
			'slug' => 'larger'
		)
	));

} else { //Newsmag
	add_theme_support('editor-font-sizes', array(
		array(
			'name' => 'small',
			'shortName' => 'S',
			'size' => 10,
			'slug' => 'small'
		),
		array(
			'name' => 'regular',
			'shortName' => 'M',
			'size' => 14,
			'slug' => 'regular'
		),
		array(
			'name' => 'large',
			'shortName' => 'L',
			'size' => 30,
			'slug' => 'large'
		),
		array(
			'name' => 'larger',
			'shortName' => 'XL',
			'size' => 48,
			'slug' => 'larger'
		)
	));
}
/*
 * front end js composer file !!!! - check it why is this way
 * without this code - on newsmag the composer.min.css is loaded in footer and overwrite our style.css from head (this happens only on other pages like categoris or post - on frontpage works fine)
 */
add_action('wp_enqueue_scripts',  'load_js_composer_front', 1000);
function load_js_composer_front() {
	wp_enqueue_style('js_composer_front');
}

/*
 * required for woocommerce shortcode when live editor is used
 */
if (td_global::$is_woocommerce_installed === true ) {
	if (tdc_state::is_live_editor_ajax() || tdc_state::is_live_editor_iframe()) {

		remove_action('woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10);
		remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5);
		remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
		remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);
		remove_action('woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10);
		remove_action('woocommerce_before_subcategory', 'woocommerce_template_loop_category_link_open', 10);
		remove_action('woocommerce_shop_loop_subcategory_title', 'woocommerce_template_loop_category_title', 10);
		remove_action('woocommerce_after_subcategory', 'woocommerce_template_loop_category_link_close', 10);
		remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);
		remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5);
		remove_action( 'woocommerce_before_subcategory_title', 'woocommerce_subcategory_thumbnail', 10 );
		remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
		remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );


		add_action('woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10);
		add_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5);
		add_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
		add_action('woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);
		add_action('woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10);
		add_action('woocommerce_before_subcategory', 'woocommerce_template_loop_category_link_open', 10);
		add_action('woocommerce_shop_loop_subcategory_title', 'woocommerce_template_loop_category_title', 10);
		add_action('woocommerce_after_subcategory', 'woocommerce_template_loop_category_link_close', 10);
		add_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);
		add_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5);
		add_action( 'woocommerce_before_subcategory_title', 'woocommerce_subcategory_thumbnail', 10 );
		add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
		add_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );

		add_action( 'admin_enqueue_scripts', function () {

			wp_deregister_script( 'wc-blocks-middleware' );
			wp_deregister_script( 'wc-blocks-data-store' );
			wp_deregister_script( 'wc-blocks-vendors' );
			wp_deregister_script( 'wc-blocks-registry' );
			wp_deregister_script( 'wc-blocks' );
			wp_deregister_script( 'wc-blocks-shared-context' );
			wp_deregister_script( 'wc-blocks-shared-hocs' );

		} , 11 );

	}
}

// dequeue yoast seo js from live editor
if( is_plugin_active( 'wordpress-seo/wp-seo.php' ) || is_plugin_active( 'wordpress-seo-premium/wp-seo-premium.php' ) ) {
	if ( tdc_state::is_live_editor_iframe() || tdc_state::is_live_editor_ajax() ) {
		add_action( 'admin_enqueue_scripts', function () {
			wp_dequeue_script( 'yoast-seo-post-edit' );
		} , 11 );
	}
}


/* ----------------------------------------------------------------------------
 * front end css files
 */
add_action('wp_enqueue_scripts', 'load_front_css', 1001);   // 1001 priority because visual composer uses 1000 (1002 is for legacy)
function load_front_css() {

	if (TD_DEBUG_USE_LESS) {
		wp_enqueue_style('td-theme', td_global::$get_template_directory_uri . '/td_less_style.css.php?part=style.css_v2&theme_name=' . TD_THEME_NAME,  '', TD_THEME_VERSION, 'all' );

		// load WooCommerce LESS only when needed
        if( TD_THEME_NAME == 'Newsmag' || ( TD_THEME_NAME == 'Newspaper' && !defined( 'TD_WOO' ) ) ) {
            if ( td_global::$is_woocommerce_installed === true ) {
                wp_enqueue_style('td-theme-woo', td_global::$get_template_directory_uri . '/td_less_style.css.php?part=woocommerce', '', TD_THEME_VERSION, 'all');
            }
        }
		// load Bbpress LESS only when needed
		if (td_global::$is_bbpress_installed === true ) {
			wp_enqueue_style('td-theme-bbpress', td_global::$get_template_directory_uri . '/td_less_style.css.php?part=bbpress', '', TD_THEME_VERSION, 'all');
		}

	} else {
		wp_enqueue_style('td-theme', get_stylesheet_uri(), '', TD_THEME_VERSION, 'all' );

        if( TD_THEME_NAME == 'Newsmag' || ( TD_THEME_NAME == 'Newspaper' && !defined( 'TD_WOO' ) ) ) {
            // load the WooCommerce CSS only when needed
            if ( td_global::$is_woocommerce_installed === true ) {
                wp_enqueue_style('td-theme-woo', td_global::$get_template_directory_uri . '/style-woocommerce.css', '', TD_THEME_VERSION, 'all');
            }
        }

		// load the Bbpress CSS only when needed
		if (td_global::$is_bbpress_installed === true ) {
			wp_enqueue_style('td-theme-bbpress', td_global::$get_template_directory_uri . '/style-bbpress.css',  '', TD_THEME_VERSION, 'all' );
		}
	}

	ob_start();
	?>
    <style>
        /* custom css - generated by TagDiv Composer */
        @media (max-width: 767px) {
            .td-header-desktop-wrap {
                display: none;
            }
        }
        @media (min-width: 767px) {
            .td-header-mobile-wrap {
                display: none;
            }
        }
    </style>
	<?php

    $custom_style = td_util::remove_style_tag(ob_get_clean());

	wp_add_inline_style( 'td-theme', $custom_style );
}


add_action('wp_enqueue_scripts', 'load_demo_front_css', 1003);   // 1003 priority because visual composer uses 1000 (1002 is for legacy)
function load_demo_front_css() {

	$demo_id = td_util::get_loaded_demo_id();

	if (TD_DEBUG_USE_LESS) {

		if ($demo_id !== false and td_global::$demo_list[$demo_id]['uses_custom_style_css'] === true) {
			wp_enqueue_style('td-theme-demo-style', TDC_URL_LEGACY . '/td_less_style.css.php?part=' . $demo_id . '&theme_name=' . TD_THEME_NAME, '', TD_THEME_VERSION, 'all');
		}
	} else {

		// If we have a DEMO installed - load the demo CSS
		if ($demo_id !== false and td_global::$demo_list[$demo_id]['uses_custom_style_css'] === true) {
			wp_enqueue_style('td-theme-demo-style', TDC_URL_LEGACY . '/includes/demos/' . $demo_id . '/demo_style.css', '', TD_THEME_VERSION, 'all');
		}
	}
}



/* ----------------------------------------------------------------------------
 * CSS fonts / google fonts in front end
 *
 * this function reads the google fonts used by user and all needed info and
 * builds the FULL google font url for ALL fonts including the default ones from td_config to: td_fonts_css_files
 * @since 10.1.2017
 */
add_action('wp_enqueue_scripts', 'td_load_css_fonts');
function td_load_css_fonts() {

    td_util::check_header();

    td_util::check_footer();

    // Filter used to modify the post checked for icon fonts
	$post_id = apply_filters( 'td_filter_google_fonts_post_id', get_the_ID() );

	$new_meta_exists = metadata_exists( 'post', $post_id, 'tdc_google_fonts_settings' );

	if ( $new_meta_exists || is_archive() || is_search() || is_404() || is_front_page() || is_home() ||
         ( td_global::$is_woocommerce_installed && is_product() ) ||
         ( td_global::$is_bbpress_installed && td_global::get_current_template() === 'bbpress' ) ) {

	    // new method to get all used google fonts
        $cur_td_fonts = td_options::get_array('td_fonts'); // get the google fonts used by user

        $unique_google_fonts_ids = array();

        // filter the google fonts used by user
        if (!empty($cur_td_fonts)) {

            foreach ( $cur_td_fonts as $section_font_settings ) {
                if ( isset( $section_font_settings['font_family'] ) ) {
                    $explode_font_family = explode( '_', $section_font_settings['font_family'] );
                    if ( $explode_font_family[0] == 'g' ) {
                        $unique_google_fonts_ids[] = $explode_font_family[1];
                    }
                }
            }
        }

		// add fonts used on pages mega menu
		$unique_google_fonts_ids = array_merge( $unique_google_fonts_ids, td_util::get_mega_menu_pages_google_fonts_ids() );

		// add fonts from modules cloud templates
		$unique_google_fonts_ids = array_merge( $unique_google_fonts_ids, td_util::get_modules_ct_google_fonts_ids($post_id) );

        $panel_fonts_names = td_fonts::get_google_fonts_names( array_unique( $unique_google_fonts_ids ) );

        $google_fonts_ids = array();

        $tds_footer_page = td_util::get_option('tds_footer_page');
        if ( !empty($tds_footer_page) && intval($tds_footer_page) !== $post_id ) {
            $footer_page = get_post( $tds_footer_page );

            if ( $footer_page instanceof WP_Post ) {

                $footer_google_fonts_ids = get_post_meta( $footer_page->ID, 'tdc_google_fonts_settings', true );
                if ( ! empty( $footer_google_fonts_ids ) && is_array( $footer_google_fonts_ids ) ) {
                    foreach ( $footer_google_fonts_ids as $footer_google_fonts_id => $font_weights ) {
                        $google_fonts_ids[ $footer_google_fonts_id ] = $font_weights;
                    }
                }
            }
        }

		$extra_google_fonts_ids = array();

        // 'td_filter_google_fonts_settings' - custom hook used to add google fonts from extra source
        $extra_google_fonts_ids = apply_filters( 'td_filter_google_fonts_settings', $extra_google_fonts_ids );

        $post_google_fonts_ids = get_post_meta( $post_id, 'tdc_google_fonts_settings', true );

        if ( ! empty( $post_google_fonts_ids ) && is_array( $post_google_fonts_ids ) ) {
            foreach ( $post_google_fonts_ids as $post_google_fonts_id => $font_weights ) {
                if ( array_key_exists( $post_google_fonts_id, $extra_google_fonts_ids ) && is_array( $extra_google_fonts_ids[ $post_google_fonts_id ] ) ) {
                    $extra_google_fonts_ids[ $post_google_fonts_id ] = array_unique( array_merge( $extra_google_fonts_ids[ $post_google_fonts_id ], $font_weights) );
                } else {
                    $extra_google_fonts_ids[ $post_google_fonts_id ] = $font_weights;
                }
            }
        }

        foreach ( $extra_google_fonts_ids as $google_fonts_id => $font_weights ) {
            if ( array_key_exists( $google_fonts_id, $google_fonts_ids ) && is_array( $font_weights ) ) {
                $google_fonts_ids[$google_fonts_id] = array_unique( array_merge( $font_weights, $google_fonts_ids[$google_fonts_id] ) );
            } else {
                $google_fonts_ids[$google_fonts_id] = $font_weights;
            }
        }

        $google_fonts_names = td_fonts::get_google_fonts_for_url( $google_fonts_ids );

        $final_fonts_names = '';

        if ( ! empty( $panel_fonts_names )) {
            $final_fonts_names = $panel_fonts_names;
        }

        if ( ! empty( $google_fonts_names )) {
            if ( empty( $final_fonts_names )) {
                $final_fonts_names = $google_fonts_names;
            } else {
                $final_fonts_names .= '|' . $google_fonts_names;
            }
        }

        if ( !empty($final_fonts_names) ) {
            // used to pull fonts from google
            $td_fonts_css_files = '://fonts.googleapis.com/css?family=' . $final_fonts_names . '&display=swap';
        }

    } else {

	    // old method to get all used google fonts
        $cur_td_fonts = td_options::get_array('td_fonts'); // get the google fonts used by user

        $unique_google_fonts_ids = array();

        // filter the google fonts used by user
        if ( !empty($cur_td_fonts) ) {

            foreach ( $cur_td_fonts as $section_font_settings ) {
                if ( isset( $section_font_settings['font_family'] ) ) {
                    $explode_font_family = explode( '_', $section_font_settings['font_family'] );
                    if ( $explode_font_family[0] == 'g' ) {
                        $unique_google_fonts_ids[] = $explode_font_family[1];
                    }
                }
            }
        }

		// add fonts used on pages mega menu
		$unique_google_fonts_ids = array_merge( $unique_google_fonts_ids, td_util::get_mega_menu_pages_google_fonts_ids() );

		// add fonts from modules cloud templates
		$unique_google_fonts_ids = array_merge( $unique_google_fonts_ids, td_util::get_modules_ct_google_fonts_ids($post_id) );

        $extra_google_fonts_ids = array();

        $tds_footer_page = td_util::get_option('tds_footer_page');
        if ( intval($tds_footer_page) !== $post_id ) {
            $footer_page = get_post( $tds_footer_page );
            if ( $footer_page instanceof WP_Post ) {
                $footer_google_fonts_ids = get_post_meta( $footer_page->ID, 'tdc_google_fonts', true );
                if ( ! empty( $footer_google_fonts_ids ) && is_array( $footer_google_fonts_ids ) ) {
                    foreach ( $footer_google_fonts_ids as $footer_google_fonts_id ) {
                        $extra_google_fonts_ids[] = $footer_google_fonts_id;
                    }
                }
            }
        }

        // 'td_filter_google_fonts' - custom hook used to add google fonts from extra source
        $extra_google_fonts_ids = apply_filters( 'td_filter_google_fonts', $extra_google_fonts_ids );

        $post_google_fonts_ids = get_post_meta( $post_id, 'tdc_google_fonts', true );
        if ( !empty( $post_google_fonts_ids ) && is_array( $post_google_fonts_ids ) ) {
            foreach ( $post_google_fonts_ids as $post_google_fonts_id ) {
                $extra_google_fonts_ids[] = $post_google_fonts_id;
            }
        }

        // 'td_filter_google_fonts_settings' - add google fonts from extra source
        $td_filter_google_fonts = [];
        $td_filter_google_fonts_settings = apply_filters( 'td_filter_google_fonts_settings', [] );
        if ( !empty($td_filter_google_fonts_settings) && is_array($td_filter_google_fonts_settings) ) {
            foreach ( $td_filter_google_fonts_settings as $google_fonts_id => $font_weights ) {
                if ( array_key_exists( $google_fonts_id, $td_filter_google_fonts ) && is_array( $font_weights ) ) {
                    $td_filter_google_fonts[$google_fonts_id] = array_unique( array_merge( $font_weights, $td_filter_google_fonts[$google_fonts_id] ) );
                } else {
                    $td_filter_google_fonts[$google_fonts_id] = $font_weights;
                }
            }
        }

        // remove duplicated font ids
        $unique_google_fonts_ids = array_unique( array_merge( $unique_google_fonts_ids, $extra_google_fonts_ids ) );

        $final_fonts_names = '';
        if ( !empty($unique_google_fonts_ids) ) {
            $final_fonts_names = td_fonts::get_google_fonts_names($unique_google_fonts_ids);
        }

        if ( !empty($td_filter_google_fonts) ) {

            // get td_filter_google_fonts_settings names
            $td_filter_google_fonts_settings_names = td_fonts::get_google_fonts_for_url($td_filter_google_fonts);

            if ( empty($final_fonts_names) ) {
                $final_fonts_names = $td_filter_google_fonts_settings_names;
            } else {
                $final_fonts_names .= '|' . $td_filter_google_fonts_settings_names;
            }

        }

        if ( !empty($final_fonts_names) ) {

            // used to pull fonts from google
            $td_fonts_css_files = '://fonts.googleapis.com/css?family=' . $final_fonts_names . '&display=swap';

        }

    }


    /*
	 * add the google link for fonts used by user
	 * td_fonts_css_files: holds the link to fonts.googleapis.com built above
	 * this section will appear in the header of the source of the page
	 */
    $add_not_mobile_google_fonts = true;
    if ( class_exists('Mobile_Detect')) {
	    $mobile_detect = new Mobile_Detect();
	    if ( $mobile_detect->isMobile() ) {

	        if( !empty($td_fonts_css_files) && td_options::get('g_mob_use_google_fonts') !== 'disabled' ) {
                wp_enqueue_style( 'google-fonts-style', td_global::$http_or_https . $td_fonts_css_files, array(), TD_THEME_VERSION );
            }

            $add_not_mobile_google_fonts = false;

	    }
    }

    if ( $add_not_mobile_google_fonts && !empty($td_fonts_css_files) && td_options::get('g_use_google_fonts') !== 'disabled' ) {
        wp_enqueue_style( 'google-fonts-style', td_global::$http_or_https . $td_fonts_css_files, array(), TD_THEME_VERSION );
    }
}




/* ----------------------------------------------------------------------------
 * front end javascript files
 */
add_action('wp_enqueue_scripts', 'load_front_js');
function load_front_js() {
	$td_deploy_mode = TD_DEPLOY_MODE;

	//switch the deploy mode to demo if we have tagDiv speed booster
	if (defined('TD_SPEED_BOOSTER')) {
		$td_deploy_mode = 'demo';
	}

	if ($td_deploy_mode == 'dev') {
        // dev version - load each file separately
        $last_js_file_id = '';
        foreach (td_global::$js_files as $js_file_id => $js_file) {
            if ($last_js_file_id == '') {
                wp_enqueue_script($js_file_id, td_global::$get_template_directory_uri . $js_file, array('jquery'), TD_THEME_VERSION, true); //first, load it with jQuery dependency
            } else {
                wp_enqueue_script($js_file_id, td_global::$get_template_directory_uri . $js_file, array($last_js_file_id), TD_THEME_VERSION, true);  //not first - load with the last file dependency
            }
            $last_js_file_id = $js_file_id;
        }
    } else {
        wp_enqueue_script('td-site-min', TDC_URL_LEGACY . '/js/tagdiv_theme.min.js', array('jquery'), TD_THEME_VERSION, TD_THEME_VERSION);
    }


    if( TD_THEME_NAME == "Newspaper" ) {
        if( tdc_state::is_live_editor_ajax() || tdc_state::is_live_editor_iframe() ) {
            // load the js files meant to be only minified in composer (newspaper only)
            $last_js_file_id = '';

            foreach (td_global::$js_files_for_front_minify_only as $js_file_id => $js_file) {
                $js_file_pathinfo = pathinfo($js_file);

                if ( $last_js_file_id == '' ) {
                    wp_enqueue_script($js_file_id, TDC_SCRIPTS_URL . '/' . $js_file_pathinfo['basename'], array('jquery'), TD_THEME_VERSION, true); //first, load it with jQuery dependency
                } else {
                    wp_enqueue_script($js_file_id, TDC_SCRIPTS_URL . '/' . $js_file_pathinfo['basename'], array($last_js_file_id), TD_THEME_VERSION, true);  //not first - load with the last file dependency
                }
                $last_js_file_id = $js_file_id;
            }
        } else {
            // load current template type specific js files on the front-end
            $last_js_file_id = '';

            foreach( td_global::$js_files_for_front_minify_only as $js_file_id => $js_file ) {
                if(
                    // pages/posts/cpts/categories/custom taxonomies
                    (
                        (
                            is_singular() ||
                            is_category() ||
                            is_tax()
                        ) &&
                        $js_file_id == 'tdSocialSharing'
                    ) ||
                    // pages/posts/cpts
                    ( is_singular() &&
                        (
                            $js_file_id == 'tdModalPostImages' ||
                            $js_file_id == 'tdPostImages'
                        )
                    ) ||
                    // smart sidebar
                    (
                        td_util::get_option('tds_smart_sidebar') == 'enabled' &&
                        $js_file_id == 'tdSmartSidebar'
                    )
                ) {
                    $js_file_pathinfo = pathinfo($js_file);

                    if ( $last_js_file_id == '' ) {
                        wp_enqueue_script($js_file_id, TDC_SCRIPTS_URL . '/' . $js_file_pathinfo['basename'], array('jquery'), TD_THEME_VERSION, true); //first, load it with jQuery dependency
                    } else {
                        wp_enqueue_script($js_file_id, TDC_SCRIPTS_URL . '/' . $js_file_pathinfo['basename'], array($last_js_file_id), TD_THEME_VERSION, true);  //not first - load with the last file dependency
                    }
                }
            }
        }
    }


	//add the comments reply to script on single pages
	if (is_singular()) {
		wp_enqueue_script('comment-reply');
	}

	if (is_user_logged_in()) {
		add_thickbox();
	}
}




/* ----------------------------------------------------------------------------
 * css for wp-admin / backend
 */
add_action('admin_enqueue_scripts', 'load_wp_admin_css', 11);
function load_wp_admin_css() {
	//load the panel font in wp-admin
	$td_protocol = is_ssl() ? 'https' : 'http';
	wp_enqueue_style('google-font-ubuntu', $td_protocol . '://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700,300italic,400italic,500italic,700italic&amp;subset=latin,cyrillic-ext,greek-ext,greek,latin-ext,cyrillic'); //used on content
	if (TD_DEPLOY_MODE == 'dev') {
		wp_enqueue_style('td-wp-admin-td-panel-2', td_global::$get_template_directory_uri . '/td_less_style.css.php?part=wp-admin.css&theme_name=' . TD_THEME_NAME, false, TD_THEME_VERSION, 'all' );
	} else {
		wp_enqueue_style('td-wp-admin-td-panel-2', td_global::$get_template_directory_uri . '/legacy/common/wp_booster/wp-admin/css/wp-admin.css', false, TD_THEME_VERSION, 'all' );
	}


	//load the colorpicker
	wp_enqueue_style( 'wp-color-picker' );

	// load the media library - necessary for block widgets with image params
	// wp_enqueue_media();
}




/* ----------------------------------------------------------------------------
 * farbtastic color picker CSS and JS for wp-admin / backend - loaded only in the widgets screen. Is used by our widget builder!
 */
function td_on_admin_print_scripts_farbtastic() {
	wp_enqueue_script('farbtastic');
}
function td_on_admin_print_styles_farbtastic() {
	wp_enqueue_style('farbtastic');
}
add_action('admin_print_scripts-widgets.php', 'td_on_admin_print_scripts_farbtastic');
add_action('admin_print_styles-widgets.php', 'td_on_admin_print_styles_farbtastic');




/* ----------------------------------------------------------------------------
 * js for wp-admin / backend   admin js - we use this strange thing to make sure that our scripts are depended on each other
 * and appear one after another exactly like we add them in td_global.php
 */
add_action('admin_enqueue_scripts', 'load_wp_admin_js');
function load_wp_admin_js() {

    if (TD_DEPLOY_MODE == 'dev') {
        // dev version - load each file separately
        $last_js_file_id = '';
        foreach (td_global::$js_files_for_wp_admin as $js_file_id => $js_file_params) {
            if ($last_js_file_id == '') {
                wp_enqueue_script($js_file_id, td_global::$get_template_directory_uri . $js_file_params, array('jquery', 'wp-color-picker'), TD_THEME_VERSION, false); //first, load it with jQuery dependency
            } else {
                wp_enqueue_script($js_file_id, td_global::$get_template_directory_uri . $js_file_params, array($last_js_file_id), TD_THEME_VERSION, false);  //not first - load with the last file dependency
            }
            $last_js_file_id = $js_file_id;
        }
    } else {
		wp_enqueue_script('td-wp-admin-js', TDC_URL_LEGACY . '/js/td_wp_admin.min.js', array('jquery', 'wp-color-picker'), TD_THEME_VERSION, false);
	}



    if ( isset( $_GET['page'] ) && ( $_GET['page'] === 'td_theme_panel' || $_GET['page'] === 'td_link_tracker' ) ) {
        $last_js_file_id = '';
        foreach (td_global::$js_files_for_td_theme_panel as $js_file_id => $js_file_params) {
            if ($last_js_file_id == '') {
                wp_enqueue_script($js_file_id, td_global::$get_template_directory_uri . $js_file_params, array('jquery', 'wp-color-picker'), TD_THEME_VERSION, false); //first, load it with jQuery dependency
            } else {
                wp_enqueue_script($js_file_id, td_global::$get_template_directory_uri . $js_file_params, array($last_js_file_id), TD_THEME_VERSION, false);  //not first - load with the last file dependency
            }
            $last_js_file_id = $js_file_id;
        }

    }


	add_thickbox();
}


/*
 * set media-upload is loaded js global
 * used by tdConfirm.js
 */
add_action('admin_print_footer_scripts', 'check_if_media_uploads_is_loaded', 9999);
function check_if_media_uploads_is_loaded() {
	$wp_scripts = wp_scripts();
    $media_upload = $wp_scripts->query('media-upload', 'done');
    if ($media_upload === true) {
        //td_js_buffer::add_to_wp_admin_footer('var td_media_upload_loaded = true;');
        //echo '<script>var td_media_upload_loaded = true;</script>';
        echo '<script>tdConfirm.mediaUploadLoaded = true;</script>';
    }
}

/* ----------------------------------------------------------------------------
 * Prepare the head canonical links on smart lists and pages with pagination.
 * @see https://googlewebmastercentral.blogspot.de/2011/09/pagination-with-relnext-and-relprev.html
 * Applied from July 2019, until now it was only by request
 */
add_action('wp_head', 'td_on_wp_head_canonical',  1);
function td_on_wp_head_canonical(){

	global $post;

    // we don't apply our canonical url when RankMath or AIOSEO are used
    if ( class_exists('RankMath') || class_exists('AIOSEOAbstract') ) {
        return;
    }

    if (is_page() && 'page-pagebuilder-latest.php' === get_post_meta($post->ID, '_wp_page_template', true)) {

		$td_page = get_query_var('page') ? get_query_var('page') : 1; //rewrite the global var
		$td_paged = get_query_var('paged') ? get_query_var('paged') : 1; //rewrite the global var

		$td_page = intval($td_page);
		$td_paged = intval($td_paged);

		//paged works on single pages, page - works on homepage
		if ($td_paged > $td_page) {
			$paged = $td_paged;
		} else {
			$paged = $td_page;
		}

		global $wp_query;

		$td_homepage_loop = td_util::get_post_meta_array($post->ID, 'td_homepage_loop');
		query_posts(td_data_source::metabox_to_args($td_homepage_loop, $paged));

		$max_page = $wp_query->max_num_pages;

		// Remove the wp action links
		remove_action('wp_head', 'rel_canonical');
		remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');

		if (class_exists('WPSEO_Frontend')) {
			// Remove the canonical action of the Yoast SEO plugin
			add_filter( 'wpseo_canonical', '__return_false' );
		}

		echo '<link rel="canonical" href="' . get_pagenum_link($paged) . '"/>';

		if ($paged > 1) {
			echo '<link rel="prev" href="' . get_pagenum_link($paged - 1) . '"/>';
		}
		if ($paged < $max_page) {
			echo '<link rel="next" href="' . get_pagenum_link($paged + 1) . '"/>';
		}
		wp_reset_query();
	}
}



/* ----------------------------------------------------------------------------
 * Prepare head related links
 */
add_action('wp_head', 'hook_wp_head', 1);  //hook on wp_head -> 1 priority, we are first
function hook_wp_head() {
//	if (is_single()) {
//		global $post;
//
		// facebook sharing fix for videos, we add the custom meta data
		// this is not used anymore, the meta are add by Yoast
//		if (has_post_thumbnail($post->ID)) {
//			$td_image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );
//			if (!empty($td_image[0])) {
//				echo '<meta property="og:image" content="' .  $td_image[0] . '" />';
//			}
//		}
//	}

    // set term og:image for share
    $td_class_exists = class_exists('WPSEO_Frontend') || class_exists('RankMath') || class_exists('AIOSEOAbstract');
    if (!$td_class_exists) {
        if (is_tax() || is_category()) {
            $term_id = get_queried_object_id();
            $term_exists = term_exists($term_id);
            if ($term_exists) {
                if (is_category()) {
                    $td_image = td_util::get_category_option($term_id, 'tdc_image');
                    if ($td_image !== '') {
                        echo '<meta property="og:image" content="' . $td_image . '" />';
                    }
                } else {
                    $term_meta_img_attachment_id = get_term_meta($term_id, 'tdb_filter_image', true);
                    $td_image = wp_get_attachment_image_src($term_meta_img_attachment_id, 'full');
                    if (!empty($td_image[0])) {
                        echo '<meta property="og:image" content="' . $td_image[0] . '" />';
                    }
                }
            }
        }
    }

    // Important!
    // This 'reset' is necessary for plugins who make pre requests for our shortcodes (like Yoast or All in One SEO), because these requests affects our common style generated by shortcodes.
    td_global::reset_theme_settings();

	// fav icon support
	$tds_favicon_upload = td_util::get_option('tds_favicon_upload');
	if (!empty($tds_favicon_upload)) {
		echo '<link rel="icon" type="image/png" href="' . $tds_favicon_upload . '">';
	}

    // mobile toolbar color
    $tds_mob_toolbar_color = td_util::get_option('tds_mob_toolbar_color');
	if ( $tds_mob_toolbar_color != '' ) {
		echo '<meta name="theme-color" content="' . $tds_mob_toolbar_color . '">';
	}

	// ios bookmark icon support
	$tds_ios_76 = td_util::get_option('tds_ios_icon_76');
	$tds_ios_120 = td_util::get_option('tds_ios_icon_120');
	$tds_ios_152 = td_util::get_option('tds_ios_icon_152');
	$tds_ios_114 = td_util::get_option('tds_ios_icon_114');
	$tds_ios_144 = td_util::get_option('tds_ios_icon_144');

	if(!empty($tds_ios_76)) {
		echo '<link rel="apple-touch-icon" sizes="76x76" href="' . $tds_ios_76 . '"/>';
	}

	if(!empty($tds_ios_120)) {
		echo '<link rel="apple-touch-icon" sizes="120x120" href="' . $tds_ios_120 . '"/>';
	}

	if(!empty($tds_ios_152)) {
		echo '<link rel="apple-touch-icon" sizes="152x152" href="' . $tds_ios_152 . '"/>';
	}

	if(!empty($tds_ios_114)) {
		echo '<link rel="apple-touch-icon" sizes="114x114" href="' . $tds_ios_114 . '"/>';
	}

	if(!empty($tds_ios_144)) {
		echo '<link rel="apple-touch-icon" sizes="144x144" href="' . $tds_ios_144 . '"/>';
	}


//	$tds_login_sing_in_widget = td_util::get_option('tds_login_sign_in_widget');
//	if (!empty($tds_login_sing_in_widget)) {
//		td_js_buffer::add_variable('tds_login_sing_in_widget', $tds_login_sing_in_widget);
//	}



	// js variable td_viewport_interval_list added to the window object
	td_js_buffer::add_variable('td_viewport_interval_list', td_global::$td_viewport_intervals);



	// !!!! aici se va schimba setarea, iar userii isi pierd setarea existenta
	// lazy loading images - animation effect
	//$tds_lazy_loading_image = td_util::get_option('tds_lazy_loading_image');
	$tds_animation_stack = td_util::get_option('tds_animation_stack');

	// the body css supplementary classes and the global js animation effects variables are set only if the option 'tds_animation_stack' is set
	if (empty($tds_animation_stack) && ! tdc_state::is_live_editor_iframe() ) {

		// js variable td_animation_stack_effect added to the window object
		$td_animation_stack_effect_type = 'type0';
		if (td_options::get('tds_animation_stack_effect') != '' ) {
			$td_animation_stack_effect_type = td_options::get('tds_animation_stack_effect');
		}

		td_js_buffer::add_variable('td_animation_stack_effect', $td_animation_stack_effect_type);
		td_js_buffer::add_variable('tds_animation_stack', true);

		foreach (td_global::$td_animation_stack_effects as $td_animation_stack_effect) {
			if ((($td_animation_stack_effect['val'] == '') and ($td_animation_stack_effect_type == 'type0')) ||
			    ($td_animation_stack_effect['val'] == $td_animation_stack_effect_type)) {

				td_js_buffer::add_variable('td_animation_stack_specific_selectors', $td_animation_stack_effect['specific_selectors']);
				td_js_buffer::add_variable('td_animation_stack_general_selectors', $td_animation_stack_effect['general_selectors']);

				break;
			}
		}
		add_filter('body_class','td_hook_add_custom_body_class');
	}

	$tds_general_modal_image = td_util::get_option('tds_general_modal_image');
	if (!empty($tds_general_modal_image)) {
		td_js_buffer::add_variable('tds_general_modal_image', $tds_general_modal_image);
	}

	$tds_general_modal_image_disable_mob = td_util::get_option('tds_general_modal_image_disable_mob');
	if (!empty($tds_general_modal_image_disable_mob)) {
		td_js_buffer::add_variable('tds_general_modal_image_disable_mob', $tds_general_modal_image_disable_mob);
	}

	$tds_video_scroll = td_util::get_option('tds_video_scroll');
	if (!empty($tds_video_scroll)) {
		td_js_buffer::add_variable('tds_video_scroll', $tds_video_scroll);
	}

	$tds_video_position_h = td_util::get_option('tds_video_position_h');
	if (!empty($tds_video_position_h)) {
		td_js_buffer::add_variable('tds_video_position_h', $tds_video_position_h);
	}

	$tds_video_distance_h = td_util::get_option('tds_video_distance_h');
	if (!empty($tds_video_distance_h)) {
		td_js_buffer::add_variable('tds_video_distance_h', $tds_video_distance_h);
	}

	$tds_video_position_v = td_util::get_option('tds_video_position_v');
	if (!empty($tds_video_position_v)) {
		td_js_buffer::add_variable('tds_video_position_v', $tds_video_position_v);
	}

	$tds_video_distance_v = td_util::get_option('tds_video_distance_v');
	if (!empty($tds_video_distance_v)) {
		td_js_buffer::add_variable('tds_video_distance_v', $tds_video_distance_v);
	}

	$tds_video_width = td_util::get_option('tds_video_width');
	if (!empty($tds_video_width)) {
		td_js_buffer::add_variable('tds_video_width', $tds_video_width);
	}

	$tds_video_playing_one = td_util::get_option('tds_video_playing_one');
	if (!empty($tds_video_playing_one)) {
		td_js_buffer::add_variable('tds_video_playing_one', $tds_video_playing_one);
	}

	$tds_video_pause_hidden = td_util::get_option('tds_video_pause_hidden');
	if (!empty($tds_video_pause_hidden)) {
		td_js_buffer::add_variable('tds_video_pause_hidden', $tds_video_pause_hidden);
	}

	$tds_video_lazy = td_util::get_option('tds_video_lazy');
	if (!empty($tds_video_lazy)) {
		td_js_buffer::add_variable('tds_video_lazy', $tds_video_lazy);
	}


    //load google recaptcha js for login modal ( @td-login-modal.php )
    $show_captcha = td_util::get_option('tds_captcha');
    $captcha_domain = td_util::get_option('tds_captcha_url') !== '' ? 'www.recaptcha.net' : 'www.google.com';
    $captcha_site_key = td_util::get_option('tds_captcha_site_key');
    if ( $show_captcha == 'show' && $captcha_site_key != '' ) { ?>
            <script src="https://<?php echo $captcha_domain ?>/recaptcha/api.js?render=<?php echo $captcha_site_key ?>"></script>
   <?php }

}


/** ----------------------------------------------------------------------------
 * The function hook to alter body css classes.
 * It applies the necessary animation images effect to body @see animation-stack.less
 *
 * @param $classes
 *
 * @return array
 */
function td_hook_add_custom_body_class($classes) {

	if (td_options::get('tds_animation_stack') == '') {

		$td_animation_stack_effect_type = 'type0';
		if (td_options::get('tds_animation_stack_effect') != '') {
			$td_animation_stack_effect_type = td_options::get('tds_animation_stack_effect');
		}

		$classes[] = 'td-animation-stack-' . $td_animation_stack_effect_type;
	}
	return $classes;
}



/* ----------------------------------------------------------------------------
 * localization
 */
add_action('after_setup_theme', 'td_load_text_domains');
function td_load_text_domains() {
	load_theme_textdomain( strtolower( TD_THEME_NAME ), get_template_directory() . '/translation');
}


add_action('after_setup_theme', function() {
    // theme specific config values
	require_once('td_translate.php');
}, 12);


add_action( 'after_setup_theme', function() {
    remove_theme_support( 'title-tag' );
});


/* ----------------------------------------------------------------------------
    Custom <title> wp_title - seo
 */
add_filter( 'wp_title', 'td_wp_title', 10, 2 );
function td_wp_title( $title, $sep ) {
	global $paged, $page;

	if ( is_feed() )
		return $title;

	// Add the site name.
	$title .= get_bloginfo( 'name' );

	// Add the site description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		$title = "$title $sep $site_description";

	// Add a page number if necessary.
	if ( $paged >= 2 || $page >= 2 )
		$title = "$title $sep " . __td('Page', TD_THEME_NAME) . ' ' .  max( $paged, $page );

	return $title;
}

/**
 * - filter 'wpseo_title' is used by WordPress SEO plugin and, by default, it returns a seo title that hasn't the page number inside of it,
 * when it's used on td pages [those who have a custom pagination]. At that seo title is added the page info, and just for pages greater than 1
 */
add_filter('wpseo_title', 'td_wpseo_title', 10, 1);
function td_wpseo_title($seo_title) {

	$is_smart_list = false;

	if (is_singular('post')) {
		global $post;

		$td_post_theme_settings = td_util::get_post_meta_array($post->ID, 'td_post_theme_settings');
		if (is_array($td_post_theme_settings) && array_key_exists('smart_list_template', $td_post_theme_settings)) {
			$is_smart_list = true;
		}
	}

	// outside the loop, it's reliable to check the page template
	if (!in_the_loop() && (is_page_template('page-pagebuilder-latest.php') || $is_smart_list)) {

		$td_page = (get_query_var('page')) ? get_query_var('page') : 1; //rewrite the global var
		$td_paged = (get_query_var('paged')) ? get_query_var('paged') : 1; //rewrite the global var

		if ($td_paged > $td_page) {
			$local_paged = $td_paged;
		} else {
			$local_paged = $td_page;
		}

		// the custom title is when the pagination is greater than 1
		if ($local_paged > 1) {
			return $seo_title . ' - ' . __td('Page', TD_THEME_NAME) . ' ' . $local_paged;
		}
	}

	// otherwise, the param $seo_title is returned as it is
	return $seo_title;
}

/**  ----------------------------------------------------------------------------
 * remove yoast json schema for post with reviews
 */
add_filter('wpseo_json_ld_output', 'td_remove_yoast_json', 10, 1);
function td_remove_yoast_json($data){
	global $post;
	if (is_single()) {
		$td_post_theme_settings = td_util::get_post_meta_array($post->ID, 'td_post_theme_settings');
		if (isset($td_post_theme_settings['has_review'])) {
			$data = array();
		}
	}
	return $data;
}


/**  ----------------------------------------------------------------------------
archive widget - adds .current class in the archive widget and maybe it's used in other places too!
 */
add_filter('get_archives_link', 'theme_get_archives_link');
function theme_get_archives_link ( $link_html ) {
	global $wp;
	static $current_url;
	if ( empty( $current_url ) ) {
	    global $wp;
	    if ( empty ( $wp->query_string ) ) {
		    $current_url = esc_url ( home_url ( $wp->request . '/' ) );
		} else {
			$current_url = esc_url( add_query_arg ( $wp->query_string, '', home_url ( $wp->request . '/' ) ) );
		}
	}
	if ( stristr( $current_url, 'page' ) !== false ) {
		$current_url = substr($current_url, 0, strrpos($current_url, 'page'));
	}
	if ( stristr( $link_html, $current_url ) !== false ) {
		$link_html = preg_replace( '/(<[^\s>]+)/', '\1 class="current"', $link_html, 1 );
	}
	return $link_html;
}




/*  ----------------------------------------------------------------------------
    add span wrap for category number in widget
 */
add_filter('wp_list_categories', 'cat_count_span');
function cat_count_span($links) {
	$pattern = '/<\/a> \(([\d.?]+)\)/';
	$links = preg_replace($pattern, '<span class="td-widget-no">$1</span></a>', $links);

	return $links;
}




/*  ----------------------------------------------------------------------------
    remove gallery style css
 */
add_filter( 'use_default_gallery_style', '__return_false' );




/*  ----------------------------------------------------------------------------
    editor style
 */
add_action( 'after_setup_theme', 'my_theme_add_editor_styles' );
function my_theme_add_editor_styles() {
	if (TD_DEPLOY_MODE == 'dev') {
		// we need the full url here due to a WP strange s*it with ?queries
		add_editor_style(get_stylesheet_directory_uri() . '/tagdiv-less-style.css.php?part=editor-style');
	} else {
		add_editor_style(); // add the default style
	}
}




/*  ----------------------------------------------------------------------------
    the bottom code for css
 */
add_action('wp_footer', 'td_bottom_code');
function td_bottom_code() {
	global $post;

	// try to detect speed booster
	$speed_booster = '';
	if (defined('TD_SPEED_BOOSTER')) {
		$speed_booster = 'Speed booster: ' . TD_SPEED_BOOSTER . "\n";
	}
    if ('enabled' !== td_util::get_option('tds_white_label')) {

        echo '

    <!--

        Theme: ' . TD_THEME_NAME . ' by tagDiv.com 2024
        Version: ' . TD_THEME_VERSION . ' (rara)
        Deploy mode: ' . TD_DEPLOY_MODE . '
        ' . $speed_booster . '
        uid: ' . uniqid() . '
    -->

    ';
    }

	// get and paste user custom css
	$td_custom_css = stripslashes(td_util::get_option('tds_custom_css'));
    $td_custom_css = strip_tags( $td_custom_css );


	// get the custom css for the responsive values
	$responsive_css_values = array();
	foreach (td_global::$theme_panel_custom_css_fields_list as $option_id => $css_params) {
		$responsive_css = td_util::get_option($option_id);
		if ($responsive_css != '') {
            $responsive_css = strip_tags( $responsive_css );
            $responsive_css_values[$css_params['media_query']] = $responsive_css;
		}
	}



	// check if we have to show any css
	if (!empty($td_custom_css) or count($responsive_css_values) > 0) {
		$css_buffy = PHP_EOL . '<style type="text/css" media="screen">';

		//paste custom css
		if(!empty($td_custom_css) ) {
			$css_buffy .= PHP_EOL . '/* custom css theme panel - generated by TagDiv Theme Panel */' . PHP_EOL;
			$css_buffy .= $td_custom_css . PHP_EOL;
		}

		foreach ($responsive_css_values as $media_query => $responsive_css) {
			$css_buffy .= PHP_EOL . PHP_EOL . '/* custom responsive css from theme panel (Advanced CSS) - generated by TagDiv Theme Panel */';
			$css_buffy .= PHP_EOL . $media_query . ' {' . PHP_EOL;
			$css_buffy .= $responsive_css;
			$css_buffy .= PHP_EOL . '}' . PHP_EOL;
		}
		$css_buffy .= '</style>' . PHP_EOL . PHP_EOL;

		// echo the css buffer
		echo PHP_EOL . '<!-- Custom css from theme panel -->' . $css_buffy;
	}

	if ( ! tdc_state::is_live_editor_iframe() ) {

        //get and paste user custom html
        $td_custom_html = stripslashes(td_util::get_option('tds_custom_html'));
        if(!empty($td_custom_html)) {
            echo '<div class="td-container">' . $td_custom_html . '</div>';
        }

		//get and paste user custom javascript
		$td_custom_javascript = stripslashes( td_util::get_option( 'tds_custom_javascript' ) );
		if ( ! empty( $td_custom_javascript ) ) {
			echo '<script type="text/javascript">'
			     . $td_custom_javascript .
			     '</script>';
		}
	}

	//AJAX POST VIEW COUNT
	if(td_util::get_option('tds_ajax_post_view_count') == 'enabled') {

		//Ajax get & update counter views
		if(is_single()) {
			//echo 'post page: '.  $post->ID;
			if($post->ID > 0) {
                if( TD_THEME_NAME == "Newspaper" ) {
                    td_resources_load::render_script( TDC_SCRIPTS_URL . '/tdAjaxCount.js' . TDC_SCRIPTS_VER, 'tdAjaxCount-js', '', 'footer');
                }
				td_js_buffer::add_to_footer('
                    jQuery().ready(function jQuery_ready() {
                        tdAjaxCount.tdGetViewsCountsAjax("post",' . json_encode('[' . $post->ID . ']') . ');
                    });
                ');
			}
		}
	}
}




/*  ----------------------------------------------------------------------------
    google analytics and footer custom JS
 */
if ( ! tdc_state::is_live_editor_iframe() ) {
	add_action( 'wp_head', 'td_header_analytics_code', 40 );
	function td_header_analytics_code() {
		$td_analytics = td_util::get_option( 'td_analytics' );
		echo stripslashes( $td_analytics );
	}

	add_action( 'td_wp_body_open', 'td_body_script_code', 40 );
	function td_body_script_code() {
		$td_body_code = td_util::get_option( 'td_body_code' );
		echo stripslashes( $td_body_code );
	}

	add_action( 'wp_footer', 'td_footer_script_code', 40 );
	function td_footer_script_code() {
		$td_footer_code = td_util::get_option( 'td_footer_code' );
		echo stripslashes( $td_footer_code );
	}
}


/*  ----------------------------------------------------------------------------
    Append page slugs to the body class
 */
add_filter('body_class', 'add_slug_to_body_class');
function add_slug_to_body_class( $classes ) {
	global $post;
	if( is_home() ) {
		$key = array_search( 'blog', $classes );
		if($key > -1) {
			unset( $classes[$key] );
		};
	} elseif( is_page() ) {
		$classes[] = sanitize_html_class( $post->post_name );
	} elseif(is_singular()) {
		$classes[] = sanitize_html_class( $post->post_name );
	};

	$i = 0;
	foreach ($classes as $key => $value) {
		$pos = strripos($value, 'span');
		if ($pos !== false) {
			unset($classes[$i]);
		}

		$pos = strripos($value, 'row');
		if ($pos !== false) {
			unset($classes[$i]);
		}

		$pos = strripos($value, 'container');
		if ($pos !== false) {
			unset($classes[$i]);
		}
		$i++;
	}
	$td_block_template_id = td_options::get('tds_global_block_template', 'td_block_template_1');
	$classes[] = str_replace('td', 'global', str_replace('_', '-', $td_block_template_id));
	return $classes;
}




/*  ----------------------------------------------------------------------------
    remove span row container classes from post_class()
 */
add_filter('post_class', 'add_slug_to_post_class');
function add_slug_to_post_class($classes) {
	global $post;

	// on custom post types, we add the .post class for better css compatibility
	if ( (is_single() and $post->post_type != 'post') || (wp_doing_ajax() && td_global::$is_wordpress_loop) ) {
		$classes[]= 'post';
	}

	$i = 0;
	foreach ($classes as $key => $value) {
		$pos = strripos($value, 'span');
		if ($pos !== false) {
			unset($classes[$i]);
		}

		$pos = strripos($value, 'row');
		if ($pos !== false) {
			unset($classes[$i]);
		}

		$pos = strripos($value, 'container');
		if ($pos !== false) {
			unset($classes[$i]);
		}
		$i++;
	}
	return $classes;
}






/*  -----------------------------------------------------------------------------
 * Add prev and next links to a numbered link list - the pagination on single.
 */
add_filter('wp_link_pages_args', 'wp_link_pages_args_prevnext_add');
function wp_link_pages_args_prevnext_add($args)
{
	global $page, $numpages, $more, $pagenow;

	if (!$args['next_or_number'] == 'next_and_number')
		return $args; # exit early

	$args['next_or_number'] = 'number'; # keep numbering for the main part
	if (!$more)
		return $args; # exit early

	if($page-1) # there is a previous page
		$args['before'] .= _wp_link_page($page-1)
		                   . $args['link_before']. $args['previouspagelink'] . $args['link_after'] . '</a>'
		;

	if ($page<$numpages) # there is a next page
		$args['after'] = _wp_link_page($page+1)
		                 . $args['link_before'] . $args['nextpagelink'] . $args['link_after'] . '</a>'
		                 . $args['after']
		;

	return $args;
}




/*  -----------------------------------------------------------------------------
 * Add, on theme body element, the custom classes from Theme Panel -> Custom Css -> Custom Body class(s)
 */
add_filter('body_class','td_my_custom_class_names_on_body');
function td_my_custom_class_names_on_body($classes) {
	//get the custom classes from theme options
	$custom_classes = td_util::get_option('td_body_classes');

	if(!empty($custom_classes)) {
		// add 'custom classes' to the $classes array
		$classes[] = $custom_classes;
	}

	// return the $classes array
	return $classes;
}







/*  ----------------------------------------------------------------------------
    add extra contact information for author in wp-admin -> users -> your profile
 */
add_filter('user_contactmethods', 'td_extra_contact_info_for_author');
function td_extra_contact_info_for_author($contactmethods) {
	unset($contactmethods['aim']);
	unset($contactmethods['yim']);
	unset($contactmethods['jabber']);
	foreach (td_social_icons::$td_social_icons_array as $td_social_id => $td_social_name) {
		$contactmethods[$td_social_id] = $td_social_name;
	}
	return $contactmethods;
}








/* ----------------------------------------------------------------------------
 * shortcodes in widgets
 */
add_filter('widget_text', 'do_shortcode');







/* ----------------------------------------------------------------------------
 * Visual Composer init
 */
register_activation_hook('js_composer/js_composer.php', 'td_vc_kill_welcome', 11);
function td_vc_kill_welcome() {
	remove_action('vc_activation_hook', 'vc_page_welcome_set_redirect');
}

/**
 * Remove any transient flag used for updating theme. Without composer the theme update can't complete!
 */
register_deactivation_hook( 'td-composer/td-composer.php', function() {

    // clear any flag about wp theme update
    delete_site_transient( 'update_themes' );

     // clear flag to update theme
    delete_transient( 'td_update_theme_' . TD_THEME_NAME );

    // clear flag to update theme to latest version
    delete_transient( 'td_update_theme_latest_version_' . TD_THEME_NAME );

    // clear flag to update theme to specific version
    delete_transient( 'td_update_theme_to_version_' . TD_THEME_NAME );

    // clear flag to update to a specific version
    tagdiv_util::update_option( 'theme_update_to_version', '' );

    // remove td transients wp cron task
	wp_clear_scheduled_hook( 'td_clear_transients' );

    // clear blocks query cache ( td_query transients )
	td_transients_manager::delete_transients();

});

/**
 * visual composer rewrite classes
 * Filter to Replace default css class for vc_row shortcode and vc_column
 */
add_filter('vc_shortcodes_css_class', 'custom_css_classes_for_vc_row_and_vc_column', 10, 2);
function custom_css_classes_for_vc_row_and_vc_column($class_string, $tag) {
	//vc_span4
	if ($tag == 'vc_row' || $tag == 'vc_row_inner') {
		$class_string = str_replace('vc_row-fluid', 'td-pb-row', $class_string);
	}
	if ($tag == 'vc_column' || $tag == 'vc_column_inner') {
		$class_string = preg_replace('/vc_col-sm-(\d{1,2})/', 'td-pb-span$1', $class_string);
		//$class_string = preg_replace('/vc_span(\d{1,2})/', 'td-pb-span$1', $class_string);
	}
	return $class_string;
}

add_action('vc_load_default_templates','my_custom_template_for_vc');
function my_custom_template_for_vc($templates) {

	require_once(TDC_PATH_LEGACY . '/includes/td_templates_builder.php');

	global $td_vc_templates;
	global $vc_manager;

	if (isset($vc_manager) and is_object($vc_manager) and method_exists($vc_manager, 'vc')) {
		$vc = $vc_manager->vc();

		if (isset($vc) and is_object($vc) and method_exists($vc, 'templatesPanelEditor')) {
			$vc_template_panel_editor = $vc->templatesPanelEditor();

			if (isset($vc_template_panel_editor)
			    and is_object($vc_template_panel_editor)
			        and has_filter('vc_load_default_templates_welcome_block', array($vc_template_panel_editor, 'loadDefaultTemplatesLimit'))) {

				remove_filter('vc_load_default_templates_welcome_block', array($vc_template_panel_editor, 'loadDefaultTemplatesLimit'));
			}
		}
	}
	return $td_vc_templates;
}


td_vc_init();
function td_vc_init() {

	// Force Visual Composer to initialize as "built into the theme". This will hide certain tabs under the Settings->Visual Composer page
	if (function_exists('vc_set_as_theme')) {
		vc_set_as_theme(true);
	}

	if (function_exists('vc_map')) {
		//map all of our blocks in page builder
		td_global_blocks::td_vc_map_all();
	}

	if (function_exists('vc_disable_frontend')) {
		vc_disable_frontend();
	}

}


//deregister Visual Composer backend js files from live editor
add_action( 'admin_print_scripts', 'td_deregister_vc_backend_scripts', 100 );
function td_deregister_vc_backend_scripts() {
	if ( td_util::is_vc_installed() && ( tdc_state::is_live_editor_iframe() || tdc_state::is_live_editor_ajax() ) ) {
		wp_deregister_script('vc-backend-min-js');
		wp_deregister_script('vc_accordion_script');
		wp_deregister_script('wpb_php_js');
		wp_deregister_script('wpb_json-js');
		wp_deregister_script('ace-editor');
		wp_deregister_script('webfont');
	}
}


/* ----------------------------------------------------------------------------
 * TagDiv gallery - tinyMCE hooks
 */

//add the gallery tinyMCE hooks only if it's enabled
if (td_api_features::is_enabled('tagdiv_slide_gallery') === true) {
	add_action('print_media_templates', 'td_custom_gallery_settings_hook');
	add_action('print_media_templates', 'td_change_backbone_js_hook');
}

/**
 * custom gallery setting
 */
function td_custom_gallery_settings_hook () {
	// define your backbone template;
	// the "tmpl-" prefix is required,
	// and your input field should have a data-setting attribute
	// matching the shortcode name
	?>
	<script type="text/html" id="tmpl-td-custom-gallery-setting">
		<label class="setting">
			<span>Gallery Type</span>
			<select data-setting="td_select_gallery_slide">
				<option value="">Default </option>
				<option value="slide">TagDiv Slide Gallery</option>
			</select>
		</label>

		<label class="setting">
			<span>Gallery Title</span>
			<input type="text" value="" data-setting="td_gallery_title_input" />
		</label>
	</script>

	<script>

		jQuery(document).ready(function(){

			// add your shortcode attribute and its default value to the
			// gallery settings list; $.extend should work as well...
			_.extend(wp.media.gallery.defaults, {
				td_select_gallery_slide: '', td_gallery_title_input: ''
			});

			// merge default gallery settings template with yours
			wp.media.view.Settings.Gallery = wp.media.view.Settings.Gallery.extend({
				template: function(view){
					return wp.media.template('gallery-settings')(view)
						+ wp.media.template('td-custom-gallery-setting')(view);
				}
//	            ,initialize: function() {
//		            if (typeof this.model.get('td_select_gallery_slide') == 'undefined') {
//			            this.model.set({td_select_gallery_slide: 'slide'});
//		            }
//	            }
			});

			//console.log();
			// wp.media.model.Attachments.trigger('change')
		});

	</script>
<?php
}


/**
 * td-modal-image support in tinymce
 */
function td_change_backbone_js_hook() {
	//change the backbone js template


	// make the buffer for the dropdown
	$image_styles_buffer_for_select = '';
	$image_styles_buffer_for_switch = '';


	foreach (td_global::$tiny_mce_image_style_list as $tiny_mce_image_style_id => $tiny_mce_image_style_params) {
		$image_styles_buffer_for_select .= "'<option value=\"" . $tiny_mce_image_style_id . "\">" . $tiny_mce_image_style_params['text'] . "</option>' + ";
		$image_styles_buffer_for_switch .= "
        case '$tiny_mce_image_style_id':
            td_clear_all_classes(); //except the modal one
            td_add_image_css_class('" . $tiny_mce_image_style_params['class'] . "');
            break;
        ";
	}


	?>
	<script type="text/javascript">

		(function (){

			var td_template_content = jQuery('#tmpl-image-details').text();

			var td_our_content = '' +

                // modal image settings
                '<div class="setting">' +
                '<span>Modal image</span>' +
                '<div class="button-large button-group" >' +
                '<button class="button active td-modal-image-off" value="left">Off</button>' +
                '<button class="button td-modal-image-on" value="left">On</button>' +
                '</div><!-- /setting -->' +

                // image style settings
                '<div class="setting">' +
                '<span><?php echo td_util::get_wl_val('tds_wl_brand', 'tagDiv') ?> image style</span>' +
                '<select class="size td-wp-image-style">' +
                '<option value="">Default</option>' +
                <?php printf( '%1$s', $image_styles_buffer_for_select ) ?>
                '</select>' +
                '</div>' +
                '</div>';

			//inject our settings in the template - before <div class="setting align">
			td_template_content = td_template_content.replace('<fieldset class="setting-group">', td_our_content + '<fieldset class="setting-group">');

			//save the template
			jQuery('#tmpl-image-details').html(td_template_content);

			//modal off - click event
			jQuery(document).on( "click", ".td-modal-image-on", function() {
				if (jQuery(this).hasClass('active')) {
					return;
				}
				td_add_image_css_class('td-modal-image');

				jQuery(".td-modal-image-off").removeClass('active');
				jQuery(".td-modal-image-on").addClass('active');
			});

			//modal on - click event
			jQuery(document).on( "click", ".td-modal-image-off", function() {
				if (jQuery(this).hasClass('active')) {
					return;
				}

				td_remove_image_css_class('td-modal-image');

				jQuery(".td-modal-image-off").addClass('active');
				jQuery(".td-modal-image-on").removeClass('active');
			});

			// select change event
			jQuery(document).on( "change", ".td-wp-image-style", function() {
				switch (jQuery( ".td-wp-image-style").val()) {

					<?php printf( '%1$s', $image_styles_buffer_for_switch) ?>

					default:
						td_clear_all_classes(); //except the modal one
						jQuery('*[data-setting="extraClasses"]').change(); //trigger the change event for backbonejs
				}
			});

			//util functions to edit the image details in wp-admin
			function td_add_image_css_class(new_class) {
				var td_extra_classes_value = jQuery('*[data-setting="extraClasses"]').val();
				jQuery('*[data-setting="extraClasses"]').val(td_extra_classes_value + ' ' + new_class);
				jQuery('*[data-setting="extraClasses"]').change(); //trigger the change event for backbonejs
			}

			function td_remove_image_css_class(new_class) {
				var td_extra_classes_value = jQuery('*[data-setting="extraClasses"]').val();

				//try first with a space before the class
				var td_regex = new RegExp(" " + new_class,"g");
				td_extra_classes_value = td_extra_classes_value.replace(td_regex, '');

				var td_regex = new RegExp(new_class,"g");
				td_extra_classes_value = td_extra_classes_value.replace(td_regex, '');

				jQuery('*[data-setting="extraClasses"]').val(td_extra_classes_value);
				jQuery('*[data-setting="extraClasses"]').change(); //trigger the change event for backbonejs
			}

			//clears all classes except the modal image one
			function td_clear_all_classes() {
				var td_extra_classes_value = jQuery('*[data-setting="extraClasses"]').val();
				if (td_extra_classes_value.indexOf('td-modal-image') > -1) {
					//we have the modal image one - keep it, remove the others
					jQuery('*[data-setting="extraClasses"]').val('td-modal-image');
				} else {
					jQuery('*[data-setting="extraClasses"]').val('');
				}
			}

			//monitor the backbone template for the current status of the picture
			setInterval(function(){
				var td_extra_classes_value = jQuery('*[data-setting="extraClasses"]').val();
				if (typeof td_extra_classes_value !== 'undefined' && td_extra_classes_value != '') {
					// if we have modal on, switch the toggle
					if (td_extra_classes_value.indexOf('td-modal-image') > -1) {
						jQuery(".td-modal-image-off").removeClass('active');
						jQuery(".td-modal-image-on").addClass('active');
					}

					<?php

					foreach (td_global::$tiny_mce_image_style_list as $tiny_mce_image_style_id => $tiny_mce_image_style_params) {
						?>
					//change the select
					if (td_extra_classes_value.indexOf('<?php printf( '%1$s', $tiny_mce_image_style_params['class'] ) ?>') > -1) {
						jQuery(".td-wp-image-style").val('<?php printf( '%1$s', $tiny_mce_image_style_id ) ?>');
					}
					<?php
				}

				?>

				}
			}, 1000);
		})(); //end anon function
	</script>
<?php
}




/* ----------------------------------------------------------------------------
 * TagDiv gallery - front end hooks
 */

//add the gallery frontend hook only if it's enabled
if (td_api_features::is_enabled('tagdiv_slide_gallery') === true) {
	add_filter('post_gallery', 'td_gallery_shortcode', 10, 4);
}

/**
 * @param string $output - is empty !!!
 * @param $atts
 * @param bool $content
 * @return mixed
 */
function td_gallery_shortcode($output = '', $atts = [], $content = false) {

	global $loop_sidebar_position;
	global $post;

	$page_template_slug = get_page_template_slug($post->ID);

	$buffy = '';

	//check for gallery  = slide
	if(!empty($atts) and !empty($atts['td_select_gallery_slide']) and $atts['td_select_gallery_slide'] == 'slide') {

		$td_double_slider2_no_js_limit = 1;
		$td_nr_columns_slide = 'td-slide-on-2-columns';
		$nr_title_chars = 95;

		//check to see if we have or not sidebar on the page, to set the small images when need to show them on center
		//if(td_global::$cur_single_template_sidebar_pos == 'no_sidebar') {


        if ( is_single() ) {
	        if ( $loop_sidebar_position == 'no_sidebar' || $page_template_slug === 'page-pagebuilder-latest.php' ) {
		        $td_double_slider2_no_js_limit = 11;
		        $td_nr_columns_slide           = 'td-slide-on-3-columns';
		        $nr_title_chars                = 170;
	        }
        }

		$title_slide = '';
		//check for the title
		if(!empty($atts['td_gallery_title_input'])) {
			$title_slide = $atts['td_gallery_title_input'];

			//check how many chars the tile have, if more then 84 then that cut it and add ... after
			if(mb_strlen ($title_slide, 'UTF-8') > $nr_title_chars) {
				$title_slide = mb_substr($title_slide, 0, $nr_title_chars, 'UTF-8') . '...';
			}
		}

		$slide_images_thumbs_css = '';
		$slide_display_html  = '';
		$slide_cursor_html = '';

		$image_ids = explode(',', $atts['ids']);

		//check to make sure we have images
		if (count($image_ids) == 1 and !is_numeric($image_ids[0])) {
			return;
		}

		$image_ids = array_map('trim', $image_ids);//trim elements of the $ids_gallery array

		//generate unique gallery slider id
		$gallery_slider_unique_id = td_global::td_generate_unique_id();

		$cur_item_nr = 1;
		foreach($image_ids as $image_id) {

			//get the info about attachment
			$image_attachment = td_util::attachment_get_full_info($image_id);

			//get images url
			$td_temp_image_url_80x60 = wp_get_attachment_image_src($image_id, 'td_80x60'); //for the slide - for small images slide popup
			$td_temp_image_url_full = $image_attachment['src'];                            //default big image - for magnific popup

			//image type and width - used to retrieve retina image
			$thumbnail_type = 'td_0x420';
			$thumbnail_width = '420';

			//if we are on full wight (3 columns use the default images not the resize ones)
			if ($loop_sidebar_position == 'no_sidebar' || $page_template_slug === 'page-pagebuilder-latest.php') {

				switch (TD_THEME_NAME) {
					case 'Newspaper' :
						$td_temp_image_url = wp_get_attachment_image_src($image_id, 'td_1068x0');       //1021x580 images - for big slide
						//change image type and width - used to retrieve retina image
						$thumbnail_type = 'td_1068x0';
						$thumbnail_width = '1068';
						break;

					case 'Newsmag' :
						$td_temp_image_url = wp_get_attachment_image_src($image_id, 'td_1021x580');       //1021x580 images - for big slide
						//image type and width - used to retrieve retina image
						$thumbnail_type = 'td_1021x580';
						$thumbnail_width = '1021';
						break;
				}
			} else {
				$td_temp_image_url = wp_get_attachment_image_src($image_id, 'td_0x420');       //0x420 image sizes - for big slide
			}


			//check if we have all the images
			if(!empty($td_temp_image_url[0]) and !empty($td_temp_image_url_80x60[0]) and !empty($td_temp_image_url_full)) {

				//retina image
				$srcset_sizes = td_util::get_srcset_sizes($image_id, $thumbnail_type, $thumbnail_width, $td_temp_image_url[0]);
				if (td_util::get_option('tds_thumb_td_80x60_retina') == 'yes') {
					$small_thumb = wp_get_attachment_image_src($image_id, 'td_80x60_retina');
					if ($small_thumb !== false) {
						$td_temp_image_url_80x60[0] = $small_thumb[0];
					}
				}

				//css for display the small cursor image
				$slide_images_thumbs_css .= '
                    #' . $gallery_slider_unique_id . '  .td-doubleSlider-2 .td-item' . $cur_item_nr . ' {
                        background: url(' . $td_temp_image_url_80x60[0] . ') 0 0 no-repeat;
                    }';

				//html for display the big image
				$class_post_content = '';

				if(!empty($image_attachment['description']) or !empty($image_attachment['caption'])) {
					$class_post_content = 'td-gallery-slide-content';
				}

				//if picture has caption & description
				$figcaption = '';

				if(!empty($image_attachment['caption']) or !empty($image_attachment['description'])) {
					$figcaption = '<figcaption class = "td-slide-caption ' . $class_post_content . '">';

					if(!empty($image_attachment['caption'])) {
						$figcaption .= '<div class = "td-gallery-slide-copywrite">' . $image_attachment['caption'] . '</div>';
					}

					if(!empty($image_attachment['description'])) {
						$figcaption .= '<span>' . $image_attachment['description'] . '</span>';
					}

					$figcaption .= '</figcaption>';
				}

				$slide_display_html .= '
                    <div class = "td-slide-item td-item' . $cur_item_nr . '">
                        <figure class="td-slide-galery-figure td-slide-popup-gallery">
                            <a class="slide-gallery-image-link" href="' . $td_temp_image_url_full . '" title="' . $image_attachment['title'] . '"  data-caption="' . esc_attr($image_attachment['caption'], ENT_QUOTES) . '"  data-description="' . htmlentities($image_attachment['description'], ENT_QUOTES) . '">
                                <img src="' . $td_temp_image_url[0] . '"' . $srcset_sizes . ' alt="' . htmlentities($image_attachment['alt'], ENT_QUOTES) . '">
                            </a>
                            ' . $figcaption . '
                        </figure>
                    </div>';

				//html for display the small cursor image
				$slide_cursor_html .= '
                    <div class = "td-button td-item' . $cur_item_nr . '">
                        <div class = "td-border"></div>
                    </div>';

				$cur_item_nr++;
			}//end check for images
		}//end foreach

		//check if we have html code for the slider
		if(!empty($slide_display_html) and !empty($slide_cursor_html)) {

			//get the number of slides
			$nr_of_slides = count($image_ids);
			if($nr_of_slides < 0) {
				$nr_of_slides = 0;
			}

			$buffy = '
                <style type="text/css">
                    ' .
			         $slide_images_thumbs_css . '
                </style>

                <div id="' . $gallery_slider_unique_id . '" class="td-gallery ' . $td_nr_columns_slide . '">
                    <div class="post_td_gallery">
                        <div class="td-gallery-slide-top">
                           <div class="td-gallery-title">' . $title_slide . '</div>

                            <div class="td-gallery-controls-wrapper">
                                <div class="td-gallery-slide-count"><span class="td-gallery-slide-item-focus">1</span> ' . __td('of', TD_THEME_NAME) . ' ' . $nr_of_slides . '</div>
                                <div class="td-gallery-slide-prev-next-but">
                                    <i class = "td-icon-left doubleSliderPrevButton"></i>
                                    <i class = "td-icon-right doubleSliderNextButton"></i>
                                </div>
                            </div>
                        </div>

                        <div class = "td-doubleSlider-1 ">
                            <div class = "td-slider">
                                ' . $slide_display_html . '
                            </div>
                        </div>

                        <div class = "td-doubleSlider-2">
                            <div class = "td-slider">
                                ' . $slide_cursor_html . '
                            </div>
                        </div>

                    </div>

                </div>
                ';

			$slide_javascript = '
			<script>
                    //total number of slides
                    var ' . $gallery_slider_unique_id . '_nr_of_slides = ' . $nr_of_slides . ';

                    jQuery(document).ready(function() {
                        //magnific popup
                        jQuery("#' . $gallery_slider_unique_id . ' .td-slide-popup-gallery").magnificPopup({
                            delegate: "a.slide-gallery-image-link",
                            type: "image",
                            tLoading: "Loading image #%curr%...",
                            mainClass: "mfp-img-mobile",
                            gallery: {
                                enabled: true,
                                navigateByImgClick: true,
                                preload: [0,1],
                                tCounter: \'%curr% ' . __td('of', TD_THEME_NAME) . ' %total%\'
                            },
                            image: {
                                tError: "<a href=\'%url%\'>The image #%curr%</a> could not be loaded.",
                                    titleSrc: function(item) {//console.log(item.el);
                                    //alert(jQuery(item.el).data("caption"));
                                    return item.el.attr("data-caption") + "<div>" + item.el.attr("data-description") + "<div>";
                                }
                            },
                            zoom: {
                                    enabled: true,
                                    duration: 300,
                                    opener: function(element) {
                                        return element.find("img");
                                    }
                            },

                            callbacks: {
                                change: function() {
                                    // Will fire when popup is closed
                                    jQuery("#' . $gallery_slider_unique_id . ' .td-doubleSlider-1").iosSlider("goToSlide", this.currItem.index + 1 );
                                }
                            }

                        });

                        jQuery("#' . $gallery_slider_unique_id . ' .td-doubleSlider-1").iosSlider({
                            scrollbar: true,
                            snapToChildren: true,
                            desktopClickDrag: true,
                            infiniteSlider: true,
                            responsiveSlides: true,
                            navPrevSelector: jQuery("#' . $gallery_slider_unique_id . ' .doubleSliderPrevButton"),
                            navNextSelector: jQuery("#' . $gallery_slider_unique_id . ' .doubleSliderNextButton"),
                            scrollbarHeight: "2",
                            scrollbarBorderRadius: "0",
                            scrollbarOpacity: "0.5",
                            onSliderResize: td_gallery_resize_update_vars_' . $gallery_slider_unique_id . ',
                            onSliderLoaded: doubleSlider2Load_' . $gallery_slider_unique_id . ',
                            onSlideChange: doubleSlider2Load_' . $gallery_slider_unique_id . ',
                            keyboardControls: true
                        });

                        //small image slide
                        jQuery("#' . $gallery_slider_unique_id . ' .td-doubleSlider-2 .td-button").each(function(i) {
                            jQuery(this).bind("click", function() {
                                jQuery("#' . $gallery_slider_unique_id . ' .td-doubleSlider-1").iosSlider("goToSlide", i+1);
                            });
                        });
                        
                        
                        
                        
                        // Create slider_2 only when the content elements are wider than the wrapper
                        var $gallery_slider_unique_id = jQuery("#' .  $gallery_slider_unique_id . '");
                        
                        if ( $gallery_slider_unique_id.length ) {
                        
                            var sliderWidth = $gallery_slider_unique_id.width(),
                                elementsWidth = 0;
                        
                            $gallery_slider_unique_id.find( ".td-button").each(function(index, el) {
                                elementsWidth += jQuery(el).outerWidth( true );
                            });
                            
                            //check the number of slides
                            //if( parseInt(' . $gallery_slider_unique_id . '_nr_of_slides) > $td_double_slider2_no_js_limit) {
                            if( elementsWidth > sliderWidth ) {
                                jQuery("#' . $gallery_slider_unique_id . ' .td-doubleSlider-2").iosSlider({
                                    desktopClickDrag: true,
                                    snapToChildren: true,
                                    snapSlideCenter: true,
                                    infiniteSlider: true
                                });
                            } else {
                                jQuery("#' . $gallery_slider_unique_id . ' .td-doubleSlider-2").addClass("td_center_slide2");
                            }
                        } 
                        
                        
                        

                        

                        function doubleSlider2Load_' . $gallery_slider_unique_id . '(args) {
                            //var currentSlide = args.currentSlideNumber;
                            jQuery("#' . $gallery_slider_unique_id . ' .td-doubleSlider-2").iosSlider("goToSlide", args.currentSlideNumber);


                            //put a transparent border around all small sliders
                            jQuery("#' . $gallery_slider_unique_id . ' .td-doubleSlider-2 .td-button .td-border").css("border", "3px solid #ffffff").css("opacity", "0.5");
                            jQuery("#' . $gallery_slider_unique_id . ' .td-doubleSlider-2 .td-button").css("border", "0");

                            //put a white border around the focused small slide
                            jQuery("#' . $gallery_slider_unique_id . ' .td-doubleSlider-2 .td-button:eq(" + (args.currentSlideNumber-1) + ") .td-border").css("border", "3px solid #ffffff").css("opacity", "1");
                            //jQuery("#' . $gallery_slider_unique_id . ' .td-doubleSlider-2 .td-button:eq(" + (args.currentSlideNumber-1) + ")").css("border", "3px solid #ffffff");

                            //write the current slide number
                            td_gallery_write_current_slide_' . $gallery_slider_unique_id . '(args.currentSlideNumber);
                        }

                        //writes the current slider beside to prev and next buttons
                        function td_gallery_write_current_slide_' . $gallery_slider_unique_id . '(slide_nr) {
                            jQuery("#' . $gallery_slider_unique_id . ' .td-gallery-slide-item-focus").html(slide_nr);
                        }


                        /*
                        * Resize the iosSlider when the page is resided (fixes bug on Android devices)
                        */
                        function td_gallery_resize_update_vars_' . $gallery_slider_unique_id . '(args) {
                            if(tdDetect.isAndroid || tdDetect.isIos) {
                                setTimeout(function(){
                                    jQuery("#' . $gallery_slider_unique_id . ' .td-doubleSlider-1").iosSlider("update");
                                }, 1500);
                            }
                        }
                    });
                    </script>
                    ';

            $slide_javascript = td_util::remove_script_tag( $slide_javascript );
			td_js_buffer::add_to_footer( $slide_javascript);
		}//end check if we have html code for the slider
	}//end if slide

	//!!!!!! WARNING
	//$return has to be != empty to overwride the default output
	return $buffy;
}



/* ----------------------------------------------------------------------------
 * add custom classes to the single templates, also mix fixes for white menu and white grid
 */
add_filter('body_class', 'td_add_single_template_class');
function td_add_single_template_class($classes) {

	if (is_single()) {
		global $post;

		$active_single_template = '';
		$td_post_theme_settings = td_util::get_post_meta_array($post->ID, 'td_post_theme_settings');

		if (!empty($td_post_theme_settings['td_post_template'])) {
			// we have a post template set in the post
			$active_single_template = $td_post_theme_settings['td_post_template'];
		} else {
			// we may have a global post template form td panel
            $option_id = 'td_default_site_post_template';
            if (class_exists('SitePress', false )) {
                global $sitepress;
                $sitepress_settings = $sitepress->get_settings();
                if ( isset($sitepress_settings['custom_posts_sync_option'][ 'tdb_templates']) ) {
                    $translation_mode = (int)$sitepress_settings['custom_posts_sync_option']['tdb_templates'];
                    if (1 === $translation_mode) {
                        $option_id .= $sitepress->get_current_language();
                    }
                }
            }
			$td_default_site_post_template = td_util::get_option($option_id);
			if(!empty($td_default_site_post_template)) {
				$active_single_template = $td_default_site_post_template;
			}
		}


		// add the class if we have a post template
		if (!empty($active_single_template)) {

		    if ( td_global::is_tdb_template( $active_single_template ) ) {
                td_global::$cur_single_template = 'single_template';
            } else {
		        td_global::$cur_single_template = $active_single_template;
            }
			$classes []= sanitize_html_class($active_single_template);
		}

	}

	// if main menu background color is white to fix the menu appearance on all headers
	if (td_util::get_option('tds_menu_color') == '#ffffff' or td_util::get_option('tds_menu_color') == 'ffffff') {
		$classes[] = 'white-menu';
	}

	// if grid color is white to fix the menu appearance on all headers
	if (td_util::get_option('tds_grid_line_color') == '#ffffff' or td_util::get_option('tds_grid_line_color') == 'ffffff') {
		$classes[] = 'white-grid';
	}
	return $classes;
}




if( TD_THEME_NAME == 'Newsmag' || ( TD_THEME_NAME == 'Newspaper' && defined('TD_STANDARD_PACK') ) ) {
    /* ----------------------------------------------------------------------------
    * add custom classes to the category templates, also mix fixes for white menu and white grid
    */
    add_filter('body_class', 'td_add_category_template_class');
    function td_add_category_template_class($classes) {
        if(!is_admin() and is_category()) {

            if ( td_global::is_tdb_registered() ) {

                $current_category = get_queried_object();

                $tdb_category_template_global = td_options::get( 'tdb_category_template' );
                $tdb_category_template = td_util::get_category_option( $current_category->cat_ID, 'tdb_category_template');

                if ( empty( $tdb_category_template ) ) {
                    $tdb_category_template = $tdb_category_template_global;
                }

                if ( ! empty( $tdb_category_template ) && ( 'theme_templates' !== $tdb_category_template ) && td_global::is_tdb_template( $tdb_category_template, true ) ) {
                    return $classes;
                }
            }

            $classes [] = sanitize_html_class(td_api_category_template::_helper_get_active_id());
            $classes [] = sanitize_html_class(td_api_category_top_posts_style::_helper_get_active_id());
        }
        return $classes;
    }


    /* ----------------------------------------------------------------------------
     * modify the main query for category pages
     */
    add_action('pre_get_posts', 'td_modify_main_query_for_category_page');
    function td_modify_main_query_for_category_page($query) {

        //checking for category page and main query
        if(!is_admin() and is_category() and $query->is_main_query()) {
            // get the category object - with or without permalinks
            if (empty($query->query_vars['cat'])) {
                td_global::$current_category_obj = get_category_by_path(get_query_var('category_name'), false);  // when we have permalinks, we have to get the category object like this.
            } else {
                td_global::$current_category_obj = get_category($query->query_vars['cat']);
            }


            // we are on a category page with an ID that doesn't exists - wp will show a 404 and we do nothing
            if (is_null(td_global::$current_category_obj)) {
                return;
            }

            // run our filter and check it's returned value. If tdb plugin did it's query modifications this will return 'true' and we do nothing here.
            $tdb_template_overwrite = apply_filters( 'tdb_category_template_query_overwrite', false );

            // if the was overwritten return here
            if ( $tdb_template_overwrite === true ) {
                return;
            }

            //get the number of page where on
            $paged = get_query_var('paged');

            //get the `filter_by` URL($_GET) variable
            $filter_by = '';
            if (isset($_GET['filter_by'])) {
                $filter_by = $_GET['filter_by'];
            }

            //get the limit of posts on the category page
            $limit = get_option('posts_per_page');


            switch ($filter_by) {
                case 'featured':
                    //get the category object
                    $query->set('category_name',  td_global::$current_category_obj->slug);
                    $query->set('cat', get_cat_ID(TD_FEATURED_CAT)); //add the fetured cat
                    break;

                case 'popular':
                    $query->set('meta_key', td_page_views::$post_view_counter_key);
                    $query->set('orderby', 'meta_value_num');
                    $query->set('order', 'DESC');
                    break;

                case 'popular7':
                    $query->set('meta_key', td_page_views::$post_view_counter_7_day_total);
                    $query->set('orderby', 'meta_value_num');
                    $query->set('order', 'DESC');
                    break;

                case 'review_high':
                    $query->set('meta_key', 'td_review_key');
                    $query->set('orderby', 'meta_value_num');
                    $query->set('order', 'DESC');
                    break;

                case 'random_posts':
                    $query->set('orderby', 'rand');
                    break;
            }//end switch


            // how many posts are we showing in the big grid for this category
            $offset = td_api_category_top_posts_style::_helper_get_posts_shown_in_the_loop();


            // offset + custom pagination - if we have offset, WordPress overwrites the pagination and works with offset + limit
            if(empty($query->is_feed)) {
                if ( $paged > 1 ) {
                    $query->set( 'offset', intval($offset) + ( ( $paged - 1 ) * $limit ) );
                } else {
                    $query->set( 'offset', intval($offset) );
                }
            }
            //print_r($query);
        }//end if main query
    }


    /* ----------------------------------------------------------------------------
    * register the default footer sidebars
    */
    add_action( 'widgets_init', function (){

        register_sidebar(
            array(
                'name'=>'Footer 1',
                'id' => 'td-footer-1',
                'before_widget' => '<aside class="widget %2$s">',
                'after_widget' => '</aside>',
                'before_title' => '<div class="block-title"><span>',
                'after_title' => '</span></div>'
            )
        );

        register_sidebar(
            array(
                'name'=>'Footer 2',
                'id' => 'td-footer-2',
                'before_widget' => '<aside class="widget %2$s">',
                'after_widget' => '</aside>',
                'before_title' => '<div class="block-title"><span>',
                'after_title' => '</span></div>'
            )
        );

        register_sidebar(
            array(
                'name'=>'Footer 3',
                'id' => 'td-footer-3',
                'before_widget' => '<aside class="widget %2$s">',
                'after_widget' => '</aside>',
                'before_title' => '<div class="block-title"><span>',
                'after_title' => '</span></div>'
            )
        );

    }, 11);


    // This points to the post format archive template( like video post format )
    add_filter( 'archive_template', function( $taxonomy_post_format ){
        if ( is_tax( 'post_format' ) ) {
            $taxonomy_post_format = TDC_PATH_LEGACY . '/taxonomy-post_format.php';
        }

        return $taxonomy_post_format;
    });
}




/** ----------------------------------------------------------------------------
 *  update category shared terms
 *  @since WordPress 4.2
 *  @link https://make.wordpress.org/core/2015/02/16/taxonomy-term-splitting-in-4-2-a-developer-guide/
 */
add_action('split_shared_term', 'td_category_split_shared_term', 10, 4);
function td_category_split_shared_term($term_id, $new_term_id, $term_taxonomy_id, $taxonomy) {
	$td_options = &td_options::get_all_by_ref();

	if (($taxonomy === 'category') and (isset($td_options['category_options'][$term_id]))) {

		$current_settings = $td_options['category_options'][$term_id];
		$td_options['category_options'][$new_term_id] = $current_settings;
		unset($td_options['category_options'][$term_id]);

		td_options::schedule_save();

		//		update_option(TD_THEME_OPTIONS_NAME, td_global::$td_options);


	}
}




/* ----------------------------------------------------------------------------
 *   TagDiv WordPress booster init
 */

global $content_width;

if ( !isset($content_width) ) {
    $content_width = 1068; // Overwritten by td_init_booster
}

td_init_booster();
function td_init_booster() {

	global $content_width;

	// content width - this is overwritten in post
    switch (TD_THEME_NAME) {
        case 'Newspaper' :
            $content_width = 696;
            break;

        case 'Newsmag' :
            $content_width = 640;
            break;
    }

	/* ----------------------------------------------------------------------------
	 * add_image_size for WordPress - register all the thumbs from the thumblist
	 */
	foreach (td_api_thumb::get_all() as $thumb_array) {
		if (td_util::get_option('tds_thumb_' . $thumb_array['name']) != '') {
			add_image_size($thumb_array['name'], $thumb_array['width'], $thumb_array['height'], $thumb_array['crop']);
			//add retina thumb (only if it is enabled)
			if (td_util::get_option('tds_thumb_' . $thumb_array['name'] . '_retina') != '') {
				add_image_size($thumb_array['name'] . '_retina', $thumb_array['width']*2, $thumb_array['height']*2, $thumb_array['crop']);
			}
		}
	}


	/**
	 * Add default render function for 'td_block_social_counter' shortcode.
	 * It's overwritten by the social counter plugin.
	 */
	/*
	add_shortcode('td_block_social_counter', 'td_block_social_counter_func');
	function td_block_social_counter_func($atts) {
		if ( current_user_can( 'administrator' ) ) {
			$buffer = '';
			$buffer .= '<style>
				.td-block-social-counter {
				  border: 1px solid red;
				  min-height: 50px;
				  line-height: 50px;
				  vertical-align: middle;
				  text-align: center;
				}
				.td-block-social-counter:before {
				  content: "Activate Social Counter plugin";
				}
				</style>';
			$buffer .= '<div class="td-block-social-counter"></div>';
			return $buffer;
		}
		return '';
	}
	*/


	/* ----------------------------------------------------------------------------
	 * Add lazy shortcodes of the registered blocks
	 */
	foreach (td_api_block::get_all() as $block_settings_key => $block_settings_value) {
	    $global_block_class = 'td_global_blocks';
		if ( class_exists( 'tdc_global_blocks', false )) {
            $global_block_class = 'tdc_global_blocks';
        }
        $global_block_class::add_lazy_shortcode($block_settings_key);
    }


	/* ----------------------------------------------------------------------------
	* register the default sidebars + dynamic ones
	*/
	add_action( 'widgets_init', function (){

		register_sidebar(
			array(
				'name'=> TD_THEME_NAME . ' default',
				'id' => 'td-default', //the id is used by the importer
				'before_widget' => '<aside class="widget %2$s">',
				'after_widget' => '</aside>',
				'before_title' => '<div class="block-title"><span>',
				'after_title' => '</span></div>'
			)
		);

		// get our custom dynamic sidebars
		$currentSidebars = td_options::get_array('sidebars');

		// if we have any, register them in wp
		if ( ! empty( $currentSidebars ) ) {
			foreach ( $currentSidebars as $sidebar ) {
				register_sidebar(
					array(
						'name' => $sidebar,
						'id' => 'td-' . td_util::sidebar_name_to_id( $sidebar ),
						'before_widget' => '<aside class="widget %2$s">',
						'after_widget' => '</aside>',
						'before_title' => '<div class="block-title"><span>',
						'after_title' => '</span></div>',
					)
				);
			}
		}

    });

}

//@td_js
require_once('td_js.php');

/*  ----------------------------------------------------------------------------
    check to see if we are on the backend
 */
if ( is_admin() ) {


	// demo importer
	require_once('wp-admin/panel/td_demo_installer.php');
	require_once('wp-admin/panel/td_demo_util.php');

	/*  ----------------------------------------------------------------------------
		The theme panel + plugins panels
	 */
	require_once('wp-admin/panel/panel_core/td_panel_core.php');
	require_once('wp-admin/panel/panel_core/td_panel_generator.php');

	if ( current_user_can('switch_themes' ) ) {
		// add the theme panel only if we have permissions
		require_once('wp-admin/panel/td_panel.php');

		require_once(ABSPATH . 'wp-admin/includes/file.php');
		WP_Filesystem();
	}

	add_action('admin_enqueue_scripts', function ($hook_suffix){

		$theme = strtolower(TD_THEME_NAME);

	    if ( $theme . '_page_td_theme_panel' === $hook_suffix ) {
		    wp_enqueue_script(
			    'td-fb-ig-business',
			    TDC_URL_LEGACY_COMMON . '/wp_booster/wp-admin/js/td_wp_admin_panel_fb_ig_business.js',
			    false,
			    TD_THEME_VERSION,
			    'all'
		    );
		    wp_enqueue_script(
			    'td-twitter',
			    TDC_URL_LEGACY_COMMON . '/wp_booster/wp-admin/js/td_wp_admin_panel_twitter.js',
			    false,
			    TD_THEME_VERSION,
			    'all'
		    );
        }
	});

	/**
	 * the wp-admin TinyMCE editor buttons
	 */
	require_once('wp-admin/tinymce/tinymce.php');

	/**
	 * get tinymce formats
	 */
	td_api_tinymce_formats::_helper_get_tinymce_format();

	/**
	 * Helper pointers
	 */

	add_action('admin_enqueue_scripts', 'td_help_pointers');
	function td_help_pointers() {
		//First we define our pointers
		$pointers = array(
			array(
				'id' => 'vc_columns_pointer',   // unique id for this pointer
				'screen' => 'page', // this is the page hook we want our pointer to show on
				'target' => '.composer-switch .logo-icon', // the css selector for the pointer to be tied to, best to use ID's
				'title' => TD_THEME_NAME . ' (tagDiv) tip',
				'content' => '<img class="td-tip-vc-columns" style="max-width:100%" src="' . td_global::$get_template_directory_uri . '/legacy/common/wp_booster/wp-admin/images/td_helper_pointers/vc-columns.png' . '">',
				'position' => array(
					'edge' => 'top', //top, bottom, left, right
					'align' => 'left' //top, bottom, left, right, middle
				)
			)
			// more as needed
		);

		//Now we instantiate the class and pass our pointer array to the constructor
		new td_help_pointers($pointers);
	}


	// Important! For the shortcode widgets that have 'block_template_id' param, the new instance (the modified instance) is returned.
	// This BECAUSE the new instance have the all modifications (including new params)
	// Changed from 10 to 1 for WPML support
	add_filter('widget_update_callback', 'td_widget_update', 1, 2);
	function td_widget_update($instance, $new_instance) {
		if (array_key_exists('block_template_id', $new_instance)) {
			return $new_instance;
		}
		return $instance;
	}
}




add_filter('redirect_canonical', 'td_fix_wp_441_pagination', 10, 2);
function td_fix_wp_441_pagination($redirect_url, $requested_url) {
	global $wp_query;

	if (is_page() && !is_feed() && isset($wp_query->queried_object) && get_query_var('page') && get_page_template_slug($wp_query->queried_object->ID) == 'page-pagebuilder-latest.php') {
		return false;
	}

	return $redirect_url;
}



/**
 * adds Theme Panel button in wp theme customizer panel
 * useful for clients who don't know where the theme settings are located
 *
 * @since 28.03.2019 this is available only if the td composer plugin is active and the theme panel is enabled from api features
 *
 */
if ( td_api_features::is_enabled('require_panel' ) ) {
	add_action('customize_controls_print_footer_scripts', 'td_customize_js');
}
function td_customize_js() {
    echo "<script type=\"text/javascript\">
            (function() {
                jQuery('#customize-theme-controls > ul').prepend('<li id=\"accordion-section-theme-panel\" class=\"accordion-section control-section\"><h3 class=\"accordion-section-title\">Theme Panel</h3></li>');
                jQuery('#accordion-section-theme-panel').on('click', function(){
                     window.location.replace('" . admin_url() . "admin.php?page=td_theme_panel');
                });
            })()
          </script>
         ";
}

add_filter('admin_body_class', 'td_on_admin_body_class' );
function td_on_admin_body_class( $classes ) {
	$classes .= ' td-theme-' . TD_THEME_NAME;
    // White Label class
    if ('enabled' === td_util::get_option('tds_white_label')) {
        $classes .= ' td-theme-wl';
    }

	return $classes;
}

// Remove 'block_template_id' param from VC params
add_action('vc_edit_form_fields_after_render', 'td_vc_edit_form_fields_after_render');
function td_vc_edit_form_fields_after_render() {
	ob_start();
	?>
	<script type="text/javascript">
		(function(){
			var $panelEditElement = jQuery('#vc_ui-panel-edit-element');
			if ($panelEditElement.length) {
				var $selectBlockTemplateId = $panelEditElement.find("select[name='block_template_id']");
				if ($selectBlockTemplateId.length) {
					$selectBlockTemplateId.closest('.vc_shortcode-param').hide();
				}
			}
		})();

	</script>
	<?php
	echo ob_get_clean();
}



/**
 * Filter sets the global block template to the wp widgets
 * @see 'widget_display_callback' hook on 'class-wp-widget.php'
 */
add_filter('widget_display_callback', 'on_widget_display_callback', 10, 3);
function on_widget_display_callback($currentWidgetInstanceSettings, $currentWidgetInstance, $widgetArgs) {

	if( isset($widgetArgs['widget_id']) && strpos($widgetArgs['widget_id'], 'td_block') !== 0 ) {
//		var_dump($widgetArgs);
//		var_dump($currentWidgetInstance);
		$global_block_template_id = td_options::get('tds_global_block_template', 'td_block_template_1');
		$widgetArgs['before_widget'] = str_replace(' class="', " class=\"$global_block_template_id ", $widgetArgs['before_widget']);

		$block_title_class = 'td-block-title';
		if ($global_block_template_id === 'td_block_template_1') {
			$block_title_class = 'block-title';
		}
		$widgetArgs['before_title'] = '<h4 class="' . $block_title_class . '"><span>';
		$widgetArgs['after_title'] = '</span></h4>';

		call_user_func_array(array($currentWidgetInstance, 'widget'), array($widgetArgs, $currentWidgetInstanceSettings));

		// Returning false will effectively short-circuit display of the widget.
		return false;
	}

	// Returning $currentWidgetInstanceSettings, as the apply_filters of this hook require
	return $currentWidgetInstanceSettings;
}



/**
 * tagDiv AMP/AMP plugins notices
 * @since 28.02.2019 this also disables the tagDiv amp plugin and displays a message that the tagDiv amp plugin has been discontinued and the AMP + tagDiv Mobile Theme now provide full support for amp
 */
add_action( 'init', 'td_add_amp_plugin_action_link_filters_on_init' );
function td_add_amp_plugin_action_link_filters_on_init() {

    // get all the plugins
    $wp_installed_plugins_list = get_plugins();

    foreach ( $wp_installed_plugins_list as $plugin_slug => $plugin_data ) {

        if ( $plugin_data['Title'] === 'tagDiv AMP' ) {

            if ( class_exists( 'td_amp_version_check', false ) ) {
                deactivate_plugins( 'td-amp/td-amp.php' );
            }

            add_filter( 'plugin_action_links_' . $plugin_slug, function ( $actions ) {
                unset( $actions['activate'] );
                return $actions;
            }, 20 );

            add_action( 'after_plugin_row_' . $plugin_slug, function ($plugin_file, $plugin_data, $status){
                echo '
                    <tr class="td-amp-plugin-warning plugin-update-tr">
                        <td colspan="3" class="plugin-update colspanchange">
                            <div class="notice inline notice-warning notice-alt" style="">
                                <p>The <b>tagDiv AMP</b> plugin has been automatically disabled as it was discontinued by the author. <br> NOTE: We recommend using the new AMP solution that is now builtin the <b>tagDiv Mobile Theme</b> plugin. You can find out more <a href="https://tagdiv.com/amp-newspaper-theme/" target="_blank">here</a>.</p>
                            </div>
                        </td>
                    </tr>
                ';
            }, 10, 3 );
        }

        if ( $plugin_data['Title'] === 'AMP' ) {

			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			//don't display notice when both plugins are active
			if ( is_plugin_active('amp/amp.php') && is_plugin_active('td-mobile-plugin/td-mobile-plugin.php') ) {
				return;
			}

            add_action( 'after_plugin_row_' . $plugin_slug, function ($plugin_file, $plugin_data, $status){
                echo '
                    <tr class="td-amp-plugin-warning plugin-update-tr">
                        <td colspan="3" class="plugin-update colspanchange">
                            <div class="notice inline notice-warning notice-alt" style="">
                                <p>The <b>' . td_util::get_wl_val('tds_wl_brand', 'tagDiv') . ' Mobile Theme</b> now works best with the <b>AMP</b> plugin to provide a complete solution for your content on mobiles. You can find out more <a href="https://tagdiv.com/amp-newspaper-theme/" target="_blank">here</a>.</p>
                            </div>
                        </td>
                    </tr>
                ';
            }, 10, 3 );
        }
    }
}





/**
 * - intercept the single template
 * - @since 26.2.2018 - this method of verifying the template is very odd. There is no reason why it's done this way instead of is_singular('post')
 * - RUNS AFTER the hook from the template builder
 * - we do nothing here where a template builder id is detected
 */
add_filter( 'template_include', 'td_template_include_filter');
function td_template_include_filter( $wordpress_template_path ) {

    $td_is_td_template_include_filter = false;

    // check if child theme is active - fix for changing post template, when the template file doesn't exist in child theme
    if (is_child_theme()) {

        if (
            is_single() &&
            (
                $wordpress_template_path == TEMPLATEPATH . '/single.php'  ||
                $wordpress_template_path == STYLESHEETPATH . '/single.php'
            )
        ) {
            // remove the filter to allow Child theme overwrite
            remove_filter( 'template_include', 'tdc_template_include', 99);
            $td_is_td_template_include_filter = true;
        }
    }

    if (
        is_single() && (
            $wordpress_template_path == td_global::$get_template_directory . '/single.php' ||
            $wordpress_template_path == get_stylesheet_directory() . '/single.php'
        )
    ) {
        $td_is_td_template_include_filter = true;
    }

    if ($td_is_td_template_include_filter) {
        global $post;

        $lang = '';
        if (class_exists('SitePress', false)) {
            global $sitepress;
            $sitepress_settings = $sitepress->get_settings();
            if ( isset($sitepress_settings['custom_posts_sync_option'][ 'tdb_templates']) ) {
                $translation_mode = (int)$sitepress_settings['custom_posts_sync_option']['tdb_templates'];
                if (1 === $translation_mode) {
                    $lang = $sitepress->get_current_language();
                }
            }
        }

        // check if we have a specific template set on the current post
        $td_post_theme_settings = td_util::get_post_meta_array( $post->ID, 'td_post_theme_settings' );

        if ( !empty( $td_post_theme_settings['td_post_template'] ) && $post->post_type == 'post'  ) {
            $single_template_id = $td_post_theme_settings['td_post_template'];

            if ( td_global::is_tdb_template($single_template_id)) {

                // make sure the template exists, maybe it was deleted or something
                if ( td_global::is_tdb_template( $single_template_id, true ) ) {

                    $tdb_template_id = td_global::tdb_get_template_id($single_template_id);

                    // run our filter and check it's returned value. If tdb did nothing or it's not installed, we do nothing.
                    $td_single_override = apply_filters( 'td_single_override', $tdb_template_id ); // in: template id    out: tdb view single template path

                    if ( $td_single_override != $tdb_template_id ) {
                        return $td_single_override;
                    }

                } else {
                    // just reset the post template here, the panel default post template will kick in and load, if available
                    $td_post_theme_settings['td_post_template'] = '';
                    update_post_meta( $post->ID, 'td_post_theme_settings', $td_post_theme_settings );
                }

            } else {
                // it's a theme template, load that one
                return td_api_single_template::_get_theme_template( $single_template_id, $wordpress_template_path );
            }
        }

        // if we are on a custom post type, leave the default loaded wordpress template
        if ( $post->post_type != 'post' ) {

            $cpts = td_util::get_cpts();
            $td_cpt = td_util::get_option('td_cpt');
            $option_id = 'td_default_site_post_template' . $lang;

            foreach ($cpts as $cpt) {

                if ( $post->post_type === $cpt->name ) {
                    if ( !empty( $td_post_theme_settings['td_post_template'] ) ) {
                        $single_template_id = $td_post_theme_settings['td_post_template'];

                        if ( td_global::is_tdb_template( $single_template_id, true ) ) {
                            $tdb_template_id = td_global::tdb_get_template_id($single_template_id);

                            // run our filter and check it's returned value. If tdb did nothing or it's not installed, we do nothing.
                            $td_single_override = apply_filters( 'td_single_override', $tdb_template_id ); // in: template id    out: tdb view single template path

                            if ( $td_single_override != $tdb_template_id ) {
                                return $td_single_override;
                            }
                        }
                    } elseif ( !empty($td_cpt[$cpt->name][$option_id]) ) {

                         $default_template_id = $td_cpt[$cpt->name][$option_id];

                         // make sure the template exists, maybe it was deleted or something
                         if ( td_global::is_tdb_template( $default_template_id, true ) ) {

                             // load the default tdb template
                             $tdb_template_id = td_global::tdb_get_template_id($default_template_id);

                             // run our filter and check it's returned value. If tdb did nothing or it's not installed, we do nothing.
                             $td_single_override = apply_filters( 'td_single_override', $tdb_template_id ); // in: template id    out: tdb view single template path

                             if ( $td_single_override != $tdb_template_id ) {
                                 return $td_single_override;
                             }
                         }
                    }
                }
            }
            return $wordpress_template_path;
        }

        // Get primary category - post template settings
        td_global::load_single_post($post);
        $td_primary_category = td_global::get_primary_category_id();

        if ( ! empty( $td_primary_category ) ) {

            $post_category_option = 'tdb_post_category_template' . $lang;

            $post_category_template = td_util::get_category_option( $td_primary_category, $post_category_option );

            // make sure the template exists, maybe it was deleted or something
            if ( td_global::is_tdb_template( $post_category_template, true ) ) {

                $tdb_template_id = td_global::tdb_get_template_id($post_category_template);

                // run our filter and check it's returned value. If tdb did nothing or it's not installed, we do nothing.
                $td_single_override = apply_filters( 'td_single_override', $tdb_template_id ); // in: template id    out: tdb view single template path

                if ( $td_single_override != $tdb_template_id ) {
                    return $td_single_override;
                }
            }
        }

        // read the global setting
        $option_id = 'td_default_site_post_template' . $lang;
        $default_template_id = td_util::get_option($option_id);

        // STOP here and load the default template if there's a single template id - The template builder does it's own thing in it's template_include if it's available!
        if ( td_global::is_tdb_template( $default_template_id ) ) {

            // make sure the template exists, maybe it was deleted or something
            if ( td_global::is_tdb_template( $default_template_id, true ) ) {

                // load the default tdb template
                $tdb_template_id = td_global::tdb_get_template_id($default_template_id);

                // run our filter and check it's returned value. If tdb did nothing or it's not installed, we do nothing.
                $td_single_override = apply_filters( 'td_single_override', $tdb_template_id ); // in: template id    out: tdb view single template path


                if ( $td_single_override != $tdb_template_id ) {
                    return $td_single_override;
                }

            } else {

                // if we have an non-existent cloud template update the default site wide post template
                td_util::update_option('td_default_site_post_template', '' );

                // and load the default theme template
                return $wordpress_template_path;
            }

        } else {

            // this was added for child theme, when default post template is set globally
            // _get_theme_template cannot locate the default post template id (is empty)
            if ( $default_template_id == '' ) {
                $default_template_id = 'single_template';
            }

            // load the default theme template
            return td_api_single_template::_get_theme_template( $default_template_id, $wordpress_template_path );
        }

    }
    return $wordpress_template_path;
}


// remove wp versions on demos
if (TD_DEPLOY_MODE === 'demo') {
    function td_demo_remove_version() {
        return '';
    }
    add_filter('the_generator', 'td_demo_remove_version');

    // do not allow auto updates
    add_filter( 'allow_dev_auto_core_updates', '__return_false' );
    add_filter( 'allow_major_auto_core_updates', '__return_false' );
    add_filter( 'allow_minor_auto_core_updates', '__return_false' );

    /**
     * Send a HTTP header to limit rendering of pages to same origin iframes for security reasons.
     */
    send_frame_options_header();

    /**
     * Disable the XML-RPC API.
     */
    add_filter('xmlrpc_enabled', '__return_false');

}

// remove the "Mobile Theme - Pagebuilder + latest articles + pagination" template from page templates list if the mobile theme plugin is not active
add_filter( 'theme_page_templates', function ($page_templates){

    if ( ! class_exists( 'td_mobile_theme', false ) ) {
        unset( $page_templates['mobile/page-pagebuilder-latest.php'] );
    }

    return $page_templates;
});

/**
 * this point the default post templates towards the legacy '/comments.php' template
 * we use 9 priority to allow plugins to overwrite this using the default priority
 */
add_filter( 'comments_template', function (){
	return TDC_PATH_LEGACY . '/comments.php';
}, 9);

/**
 * add google recapcha on comments
 */
$show_captcha = td_util::get_option('tds_captcha');
$captcha_site_key = td_util::get_option('tds_captcha_site_key');

function add_google_recaptcha($submit_field) {
    $captcha_site_key = td_util::get_option('tds_captcha_site_key');

    $submit_field['submit_field'] = '<input type="hidden" aria-required="true" id="g-recaptcha-response" name="g-recaptcha-response" data-sitekey="' . $captcha_site_key . '">
                                     <input type="hidden" name="action" value="validate_captcha"> 
                                     <input name="buttonSubmit" type="submit" id="buttonSubmit" class="submit" value="' . __td( 'Post Comment', TD_THEME_NAME ) . '" /> 
                                     <input type="hidden" name="comment_post_ID" value="'. get_the_id() . '" id="comment_post_ID" />
                                     <input type="hidden" name="comment_parent" id="comment_parent" value="0" />
                                     <div class="td-warning-captcha">' . __td( 'Captcha verification failed!', TD_THEME_NAME ) . '</div>
                                     <div class="td-warning-captcha-score">' . __td( 'CAPTCHA user score failed. Please contact us!', TD_THEME_NAME ) . '</div>
                                     ';
    return $submit_field;
}
if ( !is_user_logged_in() && $show_captcha && $captcha_site_key != '' ) {
    add_filter('comment_form_defaults','add_google_recaptcha');
}

// get theme panel option for search in taxonomies terms
$tds_search_taxonomies_terms = td_util::get_option('tds_search_taxonomies_terms' );

// add search in taxonomies terms
if ( $tds_search_taxonomies_terms === 'yes' ) {

	// add the search_query argument to td_block queries @see td_data_source::get_wp_query()
	add_filter( 'td_data_source_blocks_query_args', function ( $td_query_args, $td_block_atts ) {

		// set td_block_search_query
		$td_block_search_query = $td_block_atts['search_query'] ?? null;

		// if it's a search query on a td_block
		if ( $td_block_search_query ) {

			// set the td_block_query wp query argument(query var)
			$td_query_args['td_block_query'] = 'search_query';

		}

		return $td_query_args;

	}, 10, 2 );

    // search in taxonomies terms posts_join filter callback
	function td_tax_search_join( $join, $wp_query ) {
		global $wpdb;

		if( ( !is_admin() && is_search() && $wp_query->is_main_query() ) || $wp_query->get( 'td_block_query' ) === 'search_query' ) {
			$join .= "
                LEFT JOIN
                  {$wpdb->term_relationships} AS td_search_tr ON {$wpdb->posts}.ID = td_search_tr.object_id
                LEFT JOIN
                  {$wpdb->term_taxonomy} ON {$wpdb->term_taxonomy}.term_taxonomy_id = td_search_tr.term_taxonomy_id
                LEFT JOIN
                  {$wpdb->terms} ON {$wpdb->terms}.term_id = {$wpdb->term_taxonomy}.term_id
            ";

		}
		return $join;
	}
	//add_filter( 'posts_join', 'td_tax_search_join', 10, 2 );

	// search in taxonomies terms posts_where filter callback
	function td_tax_search_where( $where, $wp_query ) {
		global $wpdb;

		if( ( !is_admin() && is_search() && $wp_query->is_main_query() ) || $wp_query->get('td_block_query') === 'search_query' ) {

            $search_query = $wp_query->get('s');

            // get taxonomies
            $taxonomies = get_taxonomies(
                array(
                    'public' => true,  // only publicly queryable taxonomies
                    'show_ui' => true, // only taxonomies visible in the admin UI
                )
            );

            // post type
            $post_type = $wp_query->get('post_type');

            $post_type_condition = '';
            if ( !empty($post_type) && $post_type !== 'any' ) {
                $post_type = is_array($post_type) ? $post_type : array($post_type);
                $placeholders = array_fill(0, count($post_type), '%s');
                $post_type_condition = $wpdb->prepare("AND {$wpdb->posts}.post_type IN (" . implode(', ', $placeholders ) . ")", $post_type );
            }

            foreach ( $taxonomies as $taxonomy ) {

                $taxonomy_subquery = $wpdb->prepare(
                    //" OR {$wpdb->term_taxonomy}.taxonomy = %s AND ({$wpdb->terms}.name LIKE %s OR {$wpdb->term_taxonomy}.description LIKE %s)",
                    " OR EXISTS (
                                SELECT 1 FROM {$wpdb->term_relationships} AS tr
                                INNER JOIN {$wpdb->term_taxonomy} AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                                INNER JOIN {$wpdb->terms} AS t ON tt.term_id = t.term_id
                                WHERE tr.object_id = {$wpdb->posts}.ID
                                AND tt.taxonomy = %s
                                AND ( t.name LIKE %s OR tt.description LIKE %s )
                                AND {$wpdb->posts}.post_status = 'publish'
                                $post_type_condition
                            )",
                    $taxonomy,
                    '%' . $wpdb->esc_like($search_query) . '%',
                    '%' . $wpdb->esc_like($search_query) . '%'
                );

                $where .= $taxonomy_subquery;

            }

		}

		return $where;

	}
	add_filter( 'posts_where', 'td_tax_search_where', 10, 2 );

	// search in taxonomies terms posts_groupby filter callback
	function td_tax_search_groupby( $groupby, $wp_query ) {
		global $wpdb;
		if( ( !is_admin() && is_search() && $wp_query->is_main_query() ) || $wp_query->get('td_block_query') === 'search_query' ) {
			$groupby = "{$wpdb->posts}.ID";
		}
		return $groupby;
	}
	add_filter( 'posts_groupby', 'td_tax_search_groupby', 10, 2 );

}

//load script for wp-login page
add_action( 'login_enqueue_scripts', 'captcha_script' );
function captcha_script() {

    $show_captcha = td_util::get_option('tds_captcha');
    $captcha_domain = td_util::get_option('tds_captcha_url') !== '' ? 'www.recaptcha.net' : 'www.google.com';
    $captcha_site_key = td_util::get_option('tds_captcha_site_key');

    if ( $show_captcha == 'show' && 'wp-login.php' === $GLOBALS['pagenow'] && isset($_REQUEST['action']) && $_REQUEST['action'] == 'register' ) {
        if ( $captcha_site_key != '' ) { ?>
            <script src="https://<?php echo $captcha_domain ?>/recaptcha/api.js?render=<?php echo $captcha_site_key ?>"></script>
            <script>
                grecaptcha.ready(function () {
                    grecaptcha.execute('<?php echo $captcha_site_key; ?>', { action: 'submit' }).then(function (token) {
                        var recaptchaResponse = document.getElementById('recaptchaResponse');
                        recaptchaResponse.value = token;
                    });
                });
            </script>
        <?php }
    }
}

// WP Register Page
add_action( 'register_form', 'registration_captcha_display'  );
function registration_captcha_display() {
    $show_captcha = td_util::get_option('tds_captcha');
    $captcha_site_key = td_util::get_option('tds_captcha_site_key');
    if ( $show_captcha == 'show' && $captcha_site_key != '' ) {  ?>
        <input type="hidden" name="recaptcha_response" id="recaptchaResponse">
    <?php }
}
//validate captcha for register
add_action( 'registration_errors', 'validate_captcha_registration_field' , 10, 5 );
function validate_captcha_registration_field($errors, $sanitized_user_login, $user_email) {

    // get recaptcha option from panel
    $show_captcha = td_util::get_option('tds_captcha');
    $captcha_domain = td_util::get_option('tds_captcha_url') !== '' ? 'www.recaptcha.net' : 'www.google.com';
    $captcha = !empty($_POST['recaptcha_response']) ? $_POST['recaptcha_response'] : '';

    // recaptcha is active
    if ( $show_captcha == 'show' && $captcha != '' ) {

        // get google secret key from panel
        $captcha_secret_key = td_util::get_option('tds_captcha_secret_key');

        // alter captcha result=>score
        $captcha_score = td_util::get_option('tds_captcha_score');
        if ( $captcha_score == '' ) {
            $captcha_score = 0.5;
        }

        // for cloudflare
        if ( isset( $_SERVER["HTTP_CF_CONNECTING_IP"] ) ) {
            $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }

        // google recaptcha verify
        $post_data = http_build_query(
            array(
                'secret' => $captcha_secret_key,
                'response' => $captcha,
                'remoteip' => $_SERVER['REMOTE_ADDR']
            )
        );
        $opts = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => $post_data
            )
        );
        $context = stream_context_create($opts);
        $response = file_get_contents('https://' . $captcha_domain . '/recaptcha/api/siteverify', false, $context );
        $result = json_decode($response);

        if ( $result->success === false ) {
            return new WP_Error( 'invalid_captcha', esc_html__('CAPTCHA verification failed!',TD_THEME_NAME));
        }

        // check captcha score result - default is 0.5
        if ( $result->success === true && $result->score <= $captcha_score ) {
            return new WP_Error( 'invalid_captcha', esc_html__('CAPTCHA user score failed. Please contact us!',TD_THEME_NAME));
        }
    }

    return $errors;
}

// Resources optimizer
if( TD_THEME_NAME == "Newspaper" ) {
    require_once( 'td_resources_optimize.php' );
}

// Add WP Rocket inline scripts defer exclusions
add_filter( 'rocket_defer_inline_exclusions', function( $inline_exclusions_list ) {
    $inline_exclusions_list[] = 'td_res_context_registered_atts=[';
    return $inline_exclusions_list;
} );