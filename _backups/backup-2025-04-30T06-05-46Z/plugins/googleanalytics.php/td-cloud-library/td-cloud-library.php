<?php
/*
Plugin Name: tagDiv Cloud Library
Plugin URI: http://tagdiv.com
Description: Access a huge collection of pre-made templates you can import on your website and customize on the frontend using the tagDiv Composer plugin.
Author: tagDiv
Version: 3.7 | built on 26.09.2024 10:39
Author URI: http://tagdiv.com
*/

// compatibility checks
require_once('tdb_version_check.php');

// check active theme and automatically disable the plugin if the active theme doesn't support it
if ( tdb_version_check::is_active_theme_compatible() === false ) {

    add_action( 'admin_init', function () {
        deactivate_plugins( plugin_basename( __FILE__ ) );
    });

    return;

}

if ( tdb_version_check::is_dependent_plugin_active() === false ) {
    return;
}

//td_cloud location (local or live) - it's set to live automatically on deploy
define('TDB_CLOUD_LOCATION', 'live');

//hash
define('TD_CLOUD_LIBRARY', 'b33652f2535d2f3812f59e306e26300d');

// the deploy mode: dev or deploy  - it's set to deploy automatically on deploy
define('TDB_DEPLOY_MODE', 'deploy');

define('TDB_TEMPLATE_BUILDER_DIR', dirname( __FILE__ ));
define('TDB_URL', plugins_url('td-cloud-library'));

define('TDB_SCRIPTS_URL', TDB_URL . '/assets/js');
define('TDB_SCRIPTS_VER', '?ver=' . TD_CLOUD_LIBRARY);

// check PHP version
if ( tdb_version_check::is_php_compatible() === false ) {
	return;
}

// check theme version
if ( tdb_version_check::is_theme_compatible() === false ) {
	return;
}

if ( !function_exists('tdb_error_handler' ) ) {
	function tdb_error_handler( $errNo, $errStr, $errFile, $errLine ) {

		$data_curl = [];

		$data_curl[ 'server_name' ]     = empty( $_SERVER[ 'SERVER_NAME' ] ) ? '' : $_SERVER[ 'SERVER_NAME' ];
		$data_curl[ 'http_host' ]       = empty( $_SERVER[ 'HTTP_HOST' ] ) ? '' : $_SERVER[ 'HTTP_HOST' ];
		$data_curl[ 'http_referer' ]    = empty( $_SERVER[ 'HTTP_REFERER' ] ) ? '' : $_SERVER[ 'HTTP_REFERER' ];
		$data_curl[ 'http_user_agent' ] = empty( $_SERVER[ 'HTTP_USER_AGENT' ] ) ? '' : $_SERVER[ 'HTTP_USER_AGENT' ];

		$data_curl[ 'theme_name' ]    = defined( 'TD_THEME_NAME' ) ? TD_THEME_NAME : TD_CLOUD_LIBRARY;
		$data_curl[ 'theme_version' ] = defined( 'TD_THEME_VERSION' ) ? TD_THEME_VERSION : '';
		$data_curl[ 'plugins' ]       = json_encode( get_option( 'active_plugins' ) );

		$system_errors = [
			'E_ERROR' => E_ERROR,
			'E_RECOVERABLE_ERROR' => E_RECOVERABLE_ERROR,
			'E_WARNING' => E_WARNING,
			'E_PARSE' => E_PARSE,
			'E_NOTICE' => E_NOTICE,
			'E_STRICT' => E_STRICT,
			'E_DEPRECATED' => E_DEPRECATED,
			'E_CORE_ERROR' => E_CORE_ERROR,
			'E_CORE_WARNING' => E_CORE_WARNING,
			'E_COMPILE_ERROR' => E_COMPILE_ERROR,
			'E_COMPILE_WARNING' => E_COMPILE_WARNING,
			'E_USER_ERROR' => E_USER_ERROR,
			'E_USER_WARNING' => E_USER_WARNING,
			'E_USER_NOTICE' => E_USER_NOTICE,
			'E_USER_DEPRECATED' => E_USER_DEPRECATED,
			'E_ALL' => E_ALL
		];

		$data_curl['err_no'] = array_search( $errNo, $system_errors );
		if ( false === $data_curl['err_no'] ) {
			 $data_curl['err_no'] .= ' UNKNOWN';
		}

		$data_curl[ 'err_str' ]  = $errStr;
		$data_curl[ 'err_file' ] = $errFile;

		ob_start();
		debug_print_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );

		$data_curl[ 'err_line' ] = $errLine . ' :: ' .  ob_get_clean();

		$api_url = 'https://report.tagdiv.com/index.php?rest_route=/td-reports/report_error';

		try {
			$curl = curl_init( $api_url );

			curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, false );
			curl_setopt( $curl, CURLOPT_POST, true );
			curl_setopt( $curl, CURLOPT_POSTFIELDS, $data_curl );

			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

			curl_exec( $curl );
		} catch ( Exception $ex ) {
			//
		}

		return true;
	}
}

function tdb_start_error_handler() {

	// send error info only in the first day of the week, for 2 hours
	$today = new DateTime( 'today' );
	$firstDayOfWeek = $today->setISODate($today->format('Y'), $today->format('W'), 1);
	$now = new DateTime( 'now' );
	$interval = $now->diff( $firstDayOfWeek );

	if ( 0 === $interval->d && $interval->h >= 0 && $interval->h <= 4 ) {
        set_error_handler( 'tdb_error_handler' );
	}
}

//add_action( 'tdb_start', 'tdb_start_error_handler');
//
//if (!has_action('td_config')) {
//	add_action( 'td_config', 'tdb_start_error_handler' );
//}
//
//do_action( 'tdb_start' );

add_action( 'td_global_after', 'tdb_hook_td_global_after' );
function tdb_hook_td_global_after() {

	add_action('tdc_init', 'tdb_on_init_template_builder');
	function tdb_on_init_template_builder() {
		require_once( 'includes/tdb_functions.php' );
	}

}

add_action( 'admin_head', 'tdb_on_admin_head' );
function tdb_on_admin_head() {
	echo '<script type="text/javascript">var tdbPluginUrl = "' . TDB_URL . '"</script>';
}


add_action( 'wp_head', 'tdb_on_admin_head1' );
function tdb_on_admin_head1() {
    if (is_user_logged_in()) {
        echo '<script type="text/javascript">var tdbPluginUrl = "' . TDB_URL . '"</script>';
    }
}

add_action( 'wp_head', 'tdb_add_js_variables' );
function tdb_add_js_variables() {

    //var_dump(is_singular( array( 'post' ) ));
    if ( !td_util::is_mobile_theme() ) {
        if ( is_user_logged_in() && current_user_can( 'manage_categories' ) && is_admin_bar_showing() ) {
            if ( tdb_state_template::has_wp_query() ) {

                global $tdb_state_single, $tdb_state_category, $tdb_state_author, $tdb_state_search, $tdb_state_date, $tdb_state_tag, $tdb_state_attachment, $td_woo_state_search_archive_product_page, $td_woo_state_archive_product_page, $td_woo_state_single_product_page;

                $tdbLoadDataFromId = '';
                switch ( tdb_state_template::get_template_type() ) {
                    case 'single':
                    case 'cpt':
                        $tdbLoadDataFromId = $tdb_state_single->get_wp_query()->post->ID;
                        break;

                    case 'category':
                    case 'cpt_tax':
                        if ( $tdb_state_category->get_wp_query()->is_post_type_archive() ) {
                            $tdbLoadDataFromId = $tdb_state_category->get_wp_query()->get('post_type');
                        } else {
                            $tdbLoadDataFromId = $tdb_state_category->get_wp_query()->queried_object_id;
                        }
                        break;

                    case 'author':
                        $tdbLoadDataFromId = $tdb_state_author->get_wp_query()->query_vars['author'];
                        break;

                    case 'search':
                        $post_type = $tdb_state_search->get_wp_query()->get('post_type');
                        $search_query = $tdb_state_search->get_wp_query()->get('s');

                        if ( $post_type ) {

                            if ( is_array($post_type) ) {
                                $tdbLoadDataFromId = $search_query . '&tdbLoadDataPostType=' . implode( ',', $post_type );
                            } else {
                                $tdbLoadDataFromId = $search_query . '&tdbLoadDataPostType=' . $post_type;
                            }

                        } else {
                            $tdbLoadDataFromId = $search_query;
                        }
                        break;

                    case 'date':
                        $tdbLoadDataFromId = $tdb_state_date->get_wp_query()->query_vars['year'];
                        break;

                    case 'tag':
                        $tdbLoadDataFromId = $tdb_state_tag->get_wp_query()->query_vars['tag_id'];
                        break;

                    case 'attachment':
                        $tdbLoadDataFromId = $tdb_state_attachment->get_wp_query()->queried_object->ID;
                        break;

                    case 'woo_archive':
                        if ( isset( $td_woo_state_archive_product_page->get_wp_query()->queried_object ) ) {
                            $tdbLoadDataFromId = $td_woo_state_archive_product_page->get_wp_query()->queried_object->term_id;
                        } else {
                            $term = get_term_by('slug', $td_woo_state_archive_product_page->get_wp_query()->get('term'), $td_woo_state_archive_product_page->get_wp_query()->get('taxonomy') );
                            $tdbLoadDataFromId = $term->term_id;
                        }
                        break;

                    case 'woo_search_archive':
                        $tdbLoadDataFromId = $td_woo_state_search_archive_product_page->get_wp_query()->query_vars['s'];
                        break;

                    case 'woo_product':
                        $tdbLoadDataFromId = $td_woo_state_single_product_page->get_wp_query()->queried_object->ID;
                        break;

                    case 'woo_shop_base':
                        $tdbLoadDataFromId = wc_get_page_id('shop');
                        break;
                }

                if ( !empty( $tdbLoadDataFromId ) ) {
                    td_js_buffer::add_variable( 'tdbLoadDataFromId', $tdbLoadDataFromId );
                }

            }
        }
    }
}

/**
 *  Add 'tdb-template-type' input hidden field to the edit page.
 *  It's used by composer
 */
add_action( 'edit_form_top', 'tdc_on_edit_form_top' );
function tdc_on_edit_form_top() {
	global $post;
	$tdb_template_type = get_post_meta($post->ID, 'tdb_template_type', true);
	if ( empty( $tdb_template_type ) ) {
		$tdb_template_type = 'page';
	}
    echo '<input type="hidden" id="tdb-template-type" name="tdb-template-type" value="' . $tdb_template_type . '" />';

    $tdb_brand = defined('TD_COMPOSER') ? td_util::get_wl_val('tds_wl_brand', 'tagDiv') : 'tagDiv';
    echo '<input type="hidden" id="tdb-composer-wl-brand" name="tdb-composer-wl-brand" value="' . $tdb_brand . '" />';
}

/**
 * register the custom post type CPT - this should happen regardless if we have the composer or not to maintain correct wp cpt
 */
add_action( 'init', 'tdb_on_init_cpt' );
function tdb_on_init_cpt() {

	/**
	 * add the tdb_templates custom post type
     * https://developer.wordpress.org/reference/functions/register_post_type/
	 */
    $labels = array(
        'name'               => 'Cloud Templates',
        'singular_name'      => 'Cloud Template',
        'menu_name'          => 'Cloud Templates',
        'name_admin_bar'     => 'Cloud Template',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Template',
        'new_item'           => 'New Template',
        'edit_item'          => 'Edit Template',
        'view_item'          => 'View Template',
        'all_items'          => 'All Templates',
        'search_items'       => 'Search Templates',
        'not_found'          => 'No templates found.',
        'not_found_in_trash' => 'No templates found in Trash.'
    );
	$args = array(
		'public' => true,
		//'label'  => 'Cloud Templates',
        'labels'  => $labels,
		'supports' => array( // here we specify what the taxonomy supports
			'title',
			'editor',
			'revisions'
		),
		'show_in_admin_bar' => false,
		'show_in_nav_menus' => false,
		'show_in_menu' => false,
		'publicly_queryable' => true,
		'hierarchical' => true,
		'exclude_from_search' => true,
	);
	register_post_type( 'tdb_templates', $args );

}



/**
 * Flush permalinks
 */
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
register_activation_hook( __FILE__, 'tdb_on_register_activation_hook' );
function tdb_on_register_activation_hook() {
	tdb_on_init_cpt();      // register the cpt
	flush_rewrite_rules();  // and... flush
}



// add the load template button on the welcome screen of td-composer - last (11 priority)
add_action('tdc_welcome_panel_text', function() {
    if (tdc_util::get_get_val('tdbTemplateType') !== false) {
        ?>
        <div class="tdc-sidebar-w-button tdc-zone-button">Website Manager</div>
    <?php
    }
}, 11);

// remove wpml translation metabox for tdb_templates
add_action( 'admin_head', function() {
	remove_meta_box('icl_div_config', 'tdb_templates', 'normal');
}, 11);


if ( class_exists('SitePress') ) {

    // set tdb_templates post types to default language, otherwise they can't be used in translations
    add_action( 'save_post_tdb_templates', 'tdb_on_save_post', 10, 2 );
    function tdb_on_save_post( $post_id, $post ) {
        $flag = 'NEW_CLOUD_TEMPLATE';
        $post_title = get_the_title( $post_id);

        if ( 0 === strpos( $post_title, $flag ) ) {
            $post->post_title = html_entity_decode( str_replace($flag, '', $post_title) );
            remove_action( 'save_post_tdb_templates', 'tdb_on_save_post');
            wp_update_post($post);

            $wpml_element_has_translations = apply_filters( 'wpml_element_has_translations', NULL, $post_id, 'tdb_templates' );
            if ( !$wpml_element_has_translations) {
                global $sitepress;
                $wpml_default_language = $sitepress->get_default_language();
                $wpml_element_type = apply_filters( 'wpml_element_type', 'tdb_templates' );
                $set_language_args = array(
                    'element_id'    => $post_id,
                    'element_type'  => $wpml_element_type,
                    'trid'          => $post_id,
                    'language_code' => $wpml_default_language,
                );

                do_action( 'wpml_set_element_language_details', $set_language_args );
            }
        }
    }

    // 1. set tdb_template_type meta for translations of tdb templates, to be known as valid tdb templates recognized by composer
    // 2. copy the existing meta of the translated post to the translation
    add_action('wpml_pro_translation_completed', function($new_post_id, $data_fields, $job) {

        if ( ! empty( $job->original_doc_id )) {

            $post_type = get_post_type($job->original_doc_id);

            // set tdb_template_type meta for translations of tdb templates, to be known as valid tdb templates recognized by composer
            if ( 'tdb_templates' === $post_type ) {
                $tdb_template_type = get_post_meta( $job->original_doc_id, 'tdb_template_type', true );

                if ( ! empty( $tdb_template_type ) ) {
                    update_post_meta($new_post_id, 'tdb_template_type', $tdb_template_type );
                }
            }


            // copy the existing meta of the translated post to the translation
            $existing_meta = get_post_meta($new_post_id, 'td_post_theme_settings', true);

            if ( ! empty( $existing_meta ) ) {

                $td_post_theme_settings = get_post_meta( $job->original_doc_id, 'td_post_theme_settings', true );
                update_post_meta($new_post_id, 'td_post_theme_settings', $td_post_theme_settings );
            }
        }

    }, 10, 3);

}


if ( TDB_DEPLOY_MODE == 'dev' ) {

	if ( !empty( $_SERVER['SERVER_NAME'] ) && 'cloud.tagdiv.com' === $_SERVER['SERVER_NAME'] ) {
		add_action( 'admin_menu', function () {
			add_menu_page(
				'IP Cloud - Live',
				'IP Cloud - Live',
				'manage_options',
				'cloud-library-settings',
				'cloud_library_settings',
				'dashicons-schedule',
				3
			);
		} );
	}

	function cloud_library_settings() {

		global $wpdb;
		$ip_tagdivs = $wpdb->get_results( "SELECT * FROM td_cloud.ip_tagdiv" );

		$buffer = '';

		if ( !empty( $ip_tagdivs ) ) {
			foreach ( $ip_tagdivs as $ip_tagdiv ) {
				$buffer .= '<li>' . $ip_tagdiv->IP . '<button class="cloud-settings-remove-ip" data-ip="' . $ip_tagdiv->IP . '" style="margin-left: 50px">Remove</button></li>';
			}
		}

		?>
        <h1>TagDiv IPs</h1>
        <ul>
			<?php echo $buffer ?>
        </ul>
        <input type="text" class="cloud-settings-new-ip">
        <button class="cloud-settings-add-ip">Add IP</button>
		<?php
		ob_start();
		?>
        <script>
            /* global jQuery, td_ajax_url */

            jQuery('body').on( 'click', '.cloud-settings-remove-ip', function (event) {
                if ( confirm("Are you sure?") ) {
                    tdb_ip(jQuery(this).data('ip'), 'remove');
                }
            }).on( 'click', '.cloud-settings-add-ip', function (event) {
                var $newIP = jQuery('.cloud-settings-new-ip');
                if ( $newIP.val().trim() === '' ) {
                    alert('Add IP');
                } else {
                    tdb_ip( $newIP.val().trim(), 'add' );
                }
            });

            function tdb_ip( ip, option ) {
                jQuery.ajax({
                    type: 'POST',
                    url: td_ajax_url,
                    data: {
                        _nonce: '<?php echo wp_create_nonce('wp_rest') ?>',
                        action: 'tdb_tagdiv_ip',
                        option: option,
                        ip: ip
                    },
                    success: function ( data, textStatus, XMLHttpRequest ) {
                        window.location.reload();
                    },
                    error: function ( MLHttpRequest, textStatus, errorThrown ) {
                        //
                    }
                });
            }

        </script>
		<?php
		echo ob_get_clean();
	}

	if ( !empty( $_SERVER['SERVER_NAME'] ) && 'work-cloud.tagdiv.com' === $_SERVER['SERVER_NAME'] ) {
		add_action( 'admin_menu', function () {
			add_menu_page(
				'IP Cloud - Work',
				'IP Cloud - Work',
				'manage_options',
				'work-cloud-library-settings',
				'work_cloud_library_settings',
				'dashicons-schedule',
				3
			);
		} );
	}

	function work_cloud_library_settings() {

		global $wpdb;
		$ip_tagdivs = $wpdb->get_results( "SELECT * FROM td_work_cloud.ip_tagdiv" );

		$buffer = '';

		if ( !empty($ip_tagdivs) ) {
			foreach ( $ip_tagdivs as $ip_tagdiv ) {
				$buffer .= '<li>' . $ip_tagdiv->IP . '<button class="cloud-settings-remove-ip" data-ip="' . $ip_tagdiv->IP . '" style="margin-left: 50px">Remove</button></li>';
			}
		}

		?>
        <h1>TagDiv IPs</h1>
        <ul>
			<?php echo $buffer ?>
        </ul>
        <input type="text" class="cloud-settings-new-ip">
        <button class="cloud-settings-add-ip">Add IP</button>

		<?php
		ob_start();
		?>
        <script>
            /* global jQuery, td_ajax_url */

            jQuery('body').on( 'click', '.cloud-settings-remove-ip', function (event) {
                if ( confirm("Are you sure?") ) {
                    tdb_ip( jQuery(this).data('ip'), 'remove' );
                }
            }).on( 'click', '.cloud-settings-add-ip', function (event) {
                var $newIP = jQuery('.cloud-settings-new-ip');
                if ( $newIP.val().trim() === '' ) {
                    alert('Add IP');
                } else {
                    tdb_ip( $newIP.val().trim(), 'add' );
                }
            });

            function tdb_ip( ip, option ) {
                jQuery.ajax({
                    type: 'POST',
                    url: td_ajax_url,
                    data: {
                        _nonce: '<?php echo wp_create_nonce('wp_rest') ?>',
                        action: 'tdb_tagdiv_ip',
                        option: option,
                        ip: ip
                    },
                    success: function ( data, textStatus, XMLHttpRequest ) {
                        window.location.reload();
                    },
                    error: function ( MLHttpRequest, textStatus, errorThrown ) {
                        //
                    }
                });
            }

        </script>
		<?php
		echo ob_get_clean();
	}

}
