<?php

require_once TAGDIV_ROOT_DIR . '/includes/wp-booster/wp-admin/tagdiv-view-header.php';
$td_theme = defined('TD_COMPOSER') ? td_util::get_wl_val('tds_wl_theme_name', TD_THEME_NAME) : TD_THEME_NAME;

?>

<div class="about-wrap td-admin-wrap">
    <h1><?php echo $td_theme ?> system status</h1>
    <div class="about-text" style="margin-bottom: 32px;">

        <p>
            Here you can check the system status. Yellow status means that the site will work as expected on the front end but it may cause problems in wp-admin.
            <strong>Memory notice:</strong> the theme is well tested with a limit of 40MB/request but plugins may require more, for example woocommerce requires 256MB.
        </p>


    </div>




    <?php

    /*  ----------------------------------------------------------------------------
        Theme config
     */

    // Theme registration key - display it only if the theme requires activation
    if (td_api_features::is_enabled('require_activation')) {
        td_system_status::add('Theme config', array(
            'check_name' => 'Registration key',
            'tooltip' => 'Registration key',
            'value' =>  td_util::get_registration(),
            'status' => 'info'
        ));
    }

    td_system_status::add('Theme config', array(
        'check_name' => 'Theme name',
        'tooltip' => 'Theme name',
        'value' =>  $td_theme,
        'status' => 'info'
    ));

    // Theme version
    td_system_status::add('Theme config', array(
        'check_name' => 'Theme version',
        'tooltip' => 'Theme current version',
        'value' =>  td_util::get_theme_version(),
        'status' => 'info'
    ));

    // Theme database version
    td_system_status::add('Theme config', array(
        'check_name' => 'Theme database version',
        'tooltip' => 'Theme database version',
        'value' =>  td_util::get_option('td_version'),
        'status' => 'info'
    ));

    // Theme remote http channel used by the theme
    $td_remote_http = td_options::get_array('td_remote_http');
    $http_reset_button = ' <a class="td-button-system-status td-reset-channel" href="admin.php?page=td_system_status&reset_http_channel=1" data-action="reset the theme http channel and remote cache?">Reset channel</a>';

    if (empty($td_remote_http['test_status'])) {
//	    // not runned yet - DO NOTHING BECAUSE IT CREATES PANIC if not runned yet is shown
//	    td_system_status::add('Theme config', array(
//		    'check_name' => 'HTTP channel test',
//		    'tooltip' => 'The test will run when the theme has to get information from other sites. Like the number of likes, tweets etc...',
//		    'value' =>  'Not runned yet',
//		    'status' => 'info'
//	    ));
    } elseif ($td_remote_http['test_status'] == 'all_fail') {
	    // all the http tests failed to run!
	    td_system_status::add('Theme config', array(
		    'check_name' => 'HTTP channel test',
		    'tooltip' => 'The theme cannot connect to other data sources. We are unable to get the number of likes, video information, tweets etc. This is usually due to a
		    misconfigured server or firewall',
		    'value' =>  $td_remote_http['test_status'] . $http_reset_button,
		    'status' => 'red'
	    ));
    } else {
	    // we have a http channel test that works
	    td_system_status::add('Theme config', array(
		    'check_name' => 'HTTP channel test',
		    'tooltip' => 'The theme has multiple ways to get information (like count, tweet count etc) from other sites and this is the channel that was detected to work with your host.',
		    'value' =>  $td_remote_http['test_status'],
		    'status' => 'green'
	    ));
    }


	$td_demo = td_demo_state::get_installed_demo();
	if ($td_demo !== false) {

		$td_demo_api_data = td_global::$demo_list[$td_demo['demo_id']];


		// The demo id + install type
		td_system_status::add('Theme config', array(
			'check_name' => 'Installed demo',
			'tooltip' => '
			Here you can see the installed demo id and the install type. All of our demos can be installed and uninstalled using the - Install demos - panel

			',
			'value' =>
				$td_demo_api_data['text']  .
				'<span class="td-status-small-text">' .
				' - demo ID: ' . $td_demo['demo_id'] . ' | install type: ' . $td_demo['demo_install_type'] . '<br>' .
				'</span>'
			,
			'status' => 'info'
		));
	}

    // Theme mobile version
    function td_set_mobile_theme_settings() {
	    $show_mobile_theme_status = true;
	    $jetpack_mobile_version_is_active = false;
	    $w3_total_cache_is_active = false;
	    $tagdiv_mobile_plugin_is_active = false;
        $td_theme = defined('TD_COMPOSER') ? td_util::get_wl_val('tds_wl_theme_name', TD_THEME_NAME) : TD_THEME_NAME;

	    $td_mobile_theme_tooltip = '';
	    $td_mobile_theme_value = '';
	    $td_mobile_theme_status = '';

	    if ( class_exists( 'Jetpack', false ) ) {

		    global $wp_filter;
		    if (array_key_exists('setup_theme', $wp_filter)) {
			    foreach ($wp_filter['setup_theme'] as $setup_theme_settings) {
				    if (array_key_exists('jetpack_mobile_theme_setup', $setup_theme_settings)) {

					    $jetpack_mobile_version_is_active = true;
					    break;
				    }
			    }
		    }
	    }

	    if (class_exists( 'W3_Config', false)) {
		    $w3_total_cache_is_active = true;
	    }

	    if (class_exists('td_mobile_theme', false)) {
		    $tagdiv_mobile_plugin_is_active = true;
	    }

	    if ($tagdiv_mobile_plugin_is_active === true) {
		    $td_mobile_theme_tooltip = 'The mobile version of the  ' . $td_theme;
		    $td_mobile_theme_value = $td_theme . ' mobile version';
		    $td_mobile_theme_status = 'green';

		    if ($jetpack_mobile_version_is_active === true) {
			    $td_mobile_theme_tooltip = 'The mobile version of the  ' . $td_theme . ' can\'t be seen because the Jetpack mobile theme is still active. Please deactivate it';
			    $td_mobile_theme_value = 'Jetpack mobile version';
			    $td_mobile_theme_status = 'red';
		    } else if ($w3_total_cache_is_active === true) {
			    $td_mobile_theme_tooltip = 'The mobile version of the  ' . $td_theme . ' isn\'t compatible with the W3 Total Cache plugin. Use instead the WP Super Cache plugin';
			    $td_mobile_theme_status = 'red';
		    }
	    } else {
		    if ($jetpack_mobile_version_is_active === true) {
			    $td_mobile_theme_tooltip = 'Jetpack mobile theme is not fully compatible with ' . $td_theme . '. For best results, activate the Mobile Theme plugin and use the ' . $td_theme . ' mobile version';
			    $td_mobile_theme_value = 'Jetpack mobile version';
			    $td_mobile_theme_status = 'yellow';
		    } else {
			    // we do not display any status information
			    $show_mobile_theme_status = false;
		    }
	    }

	    if ($show_mobile_theme_status === true) {
		    td_system_status::add('Theme config', array(
			    'check_name' => 'Theme Mobile',
			    'tooltip' => $td_mobile_theme_tooltip,
			    'value' =>  $td_mobile_theme_value,
			    'status' => $td_mobile_theme_status,
		    ));
	    }
    }
    td_set_mobile_theme_settings();




    // speed booster
    if (defined('TD_SPEED_BOOSTER')) {
        if (defined('TD_SPEED_BOOSTER_INCOMPATIBLE')) {
            td_system_status::add('Theme config', array(
                'check_name' => 'Speed Booster',
                'tooltip' => 'SpeedBooster detected an incompatible plugin, to avoid any possible errors the plugin automatically disabled itself. For more info about this please contact us via the forum - http://forum.tagdiv.com/',
                'value' =>  TD_SPEED_BOOSTER . ' - Disabled - incompatible plugin detected: <strong>' . TD_SPEED_BOOSTER_INCOMPATIBLE . '</strong>',
                'status' => 'yellow'
            ));
        } else {
            if (version_compare(TD_SPEED_BOOSTER, 'v4.0', '<')) {
                td_system_status::add('Theme config', array(
                    'check_name' => 'Speed Booster',
                    'tooltip' => 'You have an old version of SpeedBooster, to avoid any issue please update the plugin.',
                    'value' =>  TD_SPEED_BOOSTER . ' - Old version of speed booster detected. Please uninstall it!',
                    'status' => 'red'
                ));
            } else {
                td_system_status::add('Theme config', array(
                    'check_name' => 'Speed Booster',
                    'tooltip' => 'SpeedBooster is installed and active',
                    'value' =>  TD_SPEED_BOOSTER . ' - Active',
                    'status' => 'info'
                ));
            }


        }


    }



    /*  ----------------------------------------------------------------------------
        Server status
     */

    // server info
    td_system_status::add('php.ini configuration', array(
        'check_name' => 'Server software',
        'tooltip' => 'Server software version',
        'value' =>  esc_html( $_SERVER['SERVER_SOFTWARE'] ),
        'status' => 'info'
    ));

    // php version
    td_system_status::add('php.ini configuration', array(
        'check_name' => 'PHP Version',
        'tooltip' => 'Recommended PHP 7.4 or above',
        'value' => phpversion(),
        'status' => 'info'
    ));

    // post_max_size
    td_system_status::add('php.ini configuration', array(
        'check_name' => 'post_max_size',
        'tooltip' => 'Sets max size of post data allowed. This setting also affects file upload. To upload large files you have to increase this value and in some cases you also have to increase the upload_max_filesize value.',
        'value' =>  ini_get('post_max_size') . '<span class="td-status-small-text"> - You cannot upload images, themes and plugins that have a size bigger than this value. To see how you can change this please check our guide <a target="_blank" href="http://forum.tagdiv.com/system-status-parameters-guide/">here</a>.</span>',
        'status' => 'info'
    ));

    // php time limit
    $max_execution_time = ini_get('max_execution_time');
    if ($max_execution_time == 0 or $max_execution_time >= 60) {
        td_system_status::add('php.ini configuration', array(
            'check_name' => 'max_execution_time',
            'tooltip' => 'This parameter is properly set',
            'value' =>  $max_execution_time,
            'status' => 'green'
        ));
    } else {
        td_system_status::add('php.ini configuration', array(
            'check_name' => 'max_execution_time',
            'tooltip' => 'This sets the maximum time in seconds a script is allowed to run before it is terminated by the parser. The theme demos download images from our servers and depending on the connection speed this process may require a longer time to execute. We recommend that you should increase it 60 or more.',
            'value' =>  $max_execution_time . '<span class="td-status-small-text"> - the execution time should be bigger than 60 if you plan to use the demos. To see how you can change this please check our guide <a target="_blank" href="http://forum.tagdiv.com/system-status-parameters-guide/">here</a>.</span>',
            'status' => 'yellow'
        ));
    }


    // php max input vars
    $max_input_vars = ini_get('max_input_vars');
    if ($max_input_vars == 0 or $max_input_vars >= 2000) {
        td_system_status::add('php.ini configuration', array(
            'check_name' => 'max_input_vars',
            'tooltip' => 'This parameter is properly set',
            'value' =>  $max_input_vars,
            'status' => 'green'
        ));
    } else {
        td_system_status::add('php.ini configuration', array(
            'check_name' => 'max_input_vars',
            'tooltip' => 'This sets how many input variables may be accepted (limit is applied to $_GET, $_POST and $_COOKIE superglobal separately). By default this parameter is set to 1000 and this may cause issues when saving the menu, we recommend that you increase it to 2000 or more. ',
            'value' =>  $max_input_vars . '<span class="td-status-small-text"> - the max_input_vars should be bigger than 2000, otherwise it can cause incomplete saves in the menu panel in WordPress. To see how you can change this please check our guide <a target="_blank" href="http://forum.tagdiv.com/system-status-parameters-guide/">here</a>.</span>',
            'status' => 'yellow'
        ));
    }

    // suhosin
    if (extension_loaded('suhosin') !== true) {
        td_system_status::add('php.ini configuration', array(
            'check_name' => 'SUHOSIN installed',
            'tooltip' => 'Suhosin is not installed on your server.',
            'value' => 'false',
            'status' => 'green'
        ));
    } else {
        td_system_status::add('php.ini configuration', array(
            'check_name' => 'SUHOSIN Installed',
            'tooltip' => 'Suhosin is an advanced protection system for PHP installations. It was designed to protect servers and users from known and unknown flaws in PHP applications and the PHP core. If it\'s installed on your host you have to increase the suhosin.post.max_vars and suhosin.request.max_vars parameters to 2000 or more.',
            'value' =>  'SUHOSIN is installed - <span class="td-status-small-text">it may cause problems with saving the theme panel if it\'s not properly configured. You have to increase the suhosin.post.max_vars and suhosin.request.max_vars parameters to 2000 or more. To see how you can change this please check our guide <a target="_blank" href="http://forum.tagdiv.com/system-status-parameters-guide/">here</a>.</span>',
            'status' => 'yellow'
        ));

        // suhosin.post.max_vars
        if (ini_get( "suhosin.post.max_vars" ) >= 2000){
            td_system_status::add('php.ini configuration', array(
                'check_name' => 'suhosin.post.max_vars',
                'tooltip' => 'This parameter is properly set',
                'value' => ini_get("suhosin.post.max_vars"),
                'status' => 'green'
            ));
        } else {
            td_system_status::add('php.ini configuration', array(
                'check_name' => 'suhosin.post.max_vars',
                'tooltip' => 'You may encounter issues when saving the menu, to avoid this increase suhosin.post.max_vars parameter to 2000 or more.',
                'value' => ini_get("suhosin.post.max_vars"),
                'status' => 'yellow'
            ));
        }

        // suhosin.request.max_vars
        if (ini_get( "suhosin.request.max_vars" ) >= 2000){
            td_system_status::add('php.ini configuration', array(
                'check_name' => 'suhosin.request.max_vars',
                'tooltip' => 'This parameter is properly set',
                'value' => ini_get("suhosin.request.max_vars"),
                'status' => 'green'
            ));
        } else {
            td_system_status::add('php.ini configuration', array(
                'check_name' => 'suhosin.request.max_vars',
                'tooltip' => 'You may encounter issues when saving the menu, to avoid this increase suhosin.request.max_vars parameter to 2000 or more.',
                'value' => ini_get("suhosin.request.max_vars"),
                'status' => 'yellow'
            ));
        }

    }

    //mod_substitute (apache_get_modules function may be missing on some servers)
    if (function_exists('apache_get_modules')) {
	    if (in_array('mod_substitute', apache_get_modules())) {
		    td_system_status::add('php.ini configuration', array(
			    'check_name' => 'mod_substitute',
			    'tooltip' => 'Apache mod_substitute module is active on your server and it may cause possible issues.',
			    'value' =>  'Apache <i>mod_substitute</i> module is active on your server - <span class="td-status-small-text">It\'s default configuration may cause a timeout error on TD Composer when loading large pages. For more details please check our <a target="_blank" href="http://forum.tagdiv.com/' . (TD_THEME_NAME === "Newspaper" ? "requirements-for-newspaper" : "newsmag-requirements-for-newsmag") . '/#td-mod-substitute">documentation</a></span>',
			    'status' => 'yellow'
		    ));
	    }
    } else {
        //todo - display something when apache_get_modules function is missing???
    }




    /*  ----------------------------------------------------------------------------
        PHP extensions
    */
    $gd_doc = 'enabled' !== td_util::get_option('tds_white_label') ? ' - For details on how to enable this extension please check the <a target="_blank" href="https://forum.tagdiv.com/' . (TD_THEME_NAME === "Newspaper" ? "requirements-for-newspaper" : "newsmag-requirements-for-newsmag") . '/#td-ss-parameters-guide">System status parameters guide</a>' : '';

    // mbstring
    if (extension_loaded('mbstring')) {
        td_system_status::add('PHP extensions', array(
            'check_name' => 'mbstring',
            'tooltip' => 'mbstring extension is loaded. The theme uses it\'s functions for various string operations.',
            'value' => 'available',
            'status' => 'green'
        ));
    } else {
        td_system_status::add('PHP extensions', array(
            'check_name' => 'mbstring',
            'tooltip' => 'mbstring extension is not available. The theme uses it\'s functions for various string operations.' ,
            'value' => 'not available' . $gd_doc,
            'status' => 'red'
        ));
    }

    //GD
    if (extension_loaded('gd')) {
	    td_system_status::add('PHP extensions', array(
		    'check_name' => 'GD library',
		    'tooltip' => 'GD library extension is loaded. The theme uses it\'s functions for various image related operations.',
		    'value' => 'available',
		    'status' => 'green'
	    ));
    } else {
        td_system_status::add('PHP extensions', array(
		    'check_name' => 'GD library',
		    'tooltip' => 'GD library extension is not available. The theme uses it\'s functions for various image related operations.',
		    'value' => 'not available' . $gd_doc,
		    'status' => 'red'
	    ));
    }







    /*  ----------------------------------------------------------------------------
        WordPress
    */
    // home url
    td_system_status::add('WordPress and plugins', array(
        'check_name' => 'WP Home URL',
        'tooltip' => 'WordPress Address (URL) - the address where your WordPress core files reside',
        'value' => home_url(),
        'status' => 'info'
    ));

    // site url
    td_system_status::add('WordPress and plugins', array(
        'check_name' => 'WP Site URL',
        'tooltip' => 'Site Address (URL) - the address you want people to type in their browser to reach your WordPress blog',
        'value' => site_url(),
        'status' => 'info'
    ));

    // home_url == site_url
    if (home_url() != site_url()) {
        td_system_status::add('WordPress and plugins', array(
            'check_name' => 'Home URL - Site URL',
            'tooltip' => 'Home URL not equal to Site URL, this may indicate a problem with your WordPress configuration.',
            'value' => 'Home URL != Site URL <span class="td-status-small-text">Home URL not equal to Site URL, this may indicate a problem with your WordPress configuration.</span>',
            'status' => 'yellow'
        ));
    }

    // version
    td_system_status::add('WordPress and plugins', array(
        'check_name' => 'WP version',
        'tooltip' => 'Wordpress version',
        'value' => get_bloginfo('version'),
        'status' => 'info'
    ));


    // is_multisite
    td_system_status::add('WordPress and plugins', array(
        'check_name' => 'WP multisite enabled',
        'tooltip' => 'WP multisite',
        'value' => is_multisite() ? 'Yes' : 'No',
        'status' => 'info'
    ));


    // language
    td_system_status::add('WordPress and plugins', array(
        'check_name' => 'WP Language',
        'tooltip' => 'WP Language - can be changed from Settings -> General',
        'value' => get_locale(),
        'status' => 'info'
    ));



    // memory limit
    $memory_limit = td_system_status::wp_memory_notation_to_number(WP_MEMORY_LIMIT);
    if ( $memory_limit < 268435456 ) {
        td_system_status::add('WordPress and plugins', array(
            'check_name' => 'WP Memory Limit',
            'tooltip' => 'By default in WordPress the PHP memory limit is set to 40MB. With some plugins this limit may be reached and this affects your website functionality. To avoid this increase the memory limit to at least 256MB.',
            'value' => size_format( $memory_limit ) . '/request <span class="td-status-small-text">- We recommend setting memory to at least 256MB. See: <a target="_blank" href="https://wordpress.org/support/article/editing-wp-config-php/#increasing-memory-allocated-to-php">Increasing memory allocated to PHP</a>. You can also check <a target="_blank" href="http://forum.tagdiv.com/system-status-parameters-guide/">Our Guide</a>.</span>',
            'status' => 'yellow'
        ));
    } else {
        td_system_status::add('WordPress and plugins', array(
            'check_name' => 'WP Memory Limit',
            'tooltip' => 'This parameter is properly set.',
            'value' => size_format( $memory_limit ) . '/request',
            'status' => 'green'
        ));
    }


    // wp debug
    if (defined('WP_DEBUG') and WP_DEBUG === true) {
        td_system_status::add('WordPress and plugins', array(
            'check_name' => 'WP_DEBUG',
            'tooltip' => 'The debug mode is intended for development and it may display unwanted messages. You should disable it on your side.',
            'value' => 'WP_DEBUG is enabled. <span class="td-status-small-text">It may display unwanted messages. To see how you can change this please check our guide <a target="_blank" href="http://forum.tagdiv.com/system-status-parameters-guide/">here</a>.</span>',
            'status' => 'yellow'
        ));
    } else {
        td_system_status::add('WordPress and plugins', array(
            'check_name' => 'WP_DEBUG',
            'tooltip' => 'The debug mode is disabled.',
            'value' => 'False',
            'status' => 'green'
        ));
    }






    // caching
    $caching_plugin_list = array(
        'wp-super-cache/wp-cache.php' => array(
            'name' => 'WP super cache - <span class="td-status-small-text">for best performance please check the plugin configuration guide <a target="_blank" href="http://forum.tagdiv.com/cache-plugin-install-and-configure/">here</a>.</span>',
            'status' => 'green',
        ),
        'w3-total-cache/w3-total-cache.php' => array(
            'name' => 'W3 total cache - <span class="td-status-small-text">we recommend <a target="_blank" href="https://ro.wordpress.org/plugins/wp-super-cache/">WP super cache</a></span>',
            'status' => 'yellow',
        ),
        'wp-fastest-cache/wpFastestCache.php' => array(
            'name' => 'WP Fastest Cache - <span class="td-status-small-text">we recommend <a target="_blank" href="https://ro.wordpress.org/plugins/wp-super-cache/">WP super cache</a></span>',
            'status' => 'yellow',
        ),
        'wp-rocket/wp-rocket.php' => array(
            'name' => 'WP Rocket - <span class="td-status-small-text">we recommend <a target="_blank" href="https://ro.wordpress.org/plugins/wp-super-cache/">WP super cache</a></span>',
            'status' => 'yellow',
        ),
    );
    $active_plugins = get_option('active_plugins');
    $caching_plugin = 'No caching plugin detected - <span class="td-status-small-text">for best performance we recommend using <a target="_blank" href="https://wordpress.org/plugins/wp-super-cache/">WP Super Cache</a></span>';
    $caching_plugin_status = 'yellow';
    foreach ($active_plugins as $active_plugin) {
        if (isset($caching_plugin_list[$active_plugin])) {
            $caching_plugin = $caching_plugin_list[$active_plugin]['name'];
            $caching_plugin_status = $caching_plugin_list[$active_plugin]['status'];
            break;
        }
    }
    td_system_status::add('WordPress and plugins', array(
        'check_name' => 'Caching plugin',
        'tooltip' => 'A cache plugin generates static pages and improves the site pagespeed. The cached pages are stored in the memory and when a user makes a request the pages are delivered from the cache. By this the php execution and the database requests are skipped.',
        'value' => $caching_plugin,
        'status' => $caching_plugin_status
    ));

    // blocks cache
    $td_query_all_transients = td_transients_manager::get_transients();
    $td_query_expired_transients = td_transients_manager::get_transients(true);
    if ( $td_query_all_transients ) {

	    $clear_all_btn = '<a class="td-button-system-status" href="' . admin_url('admin.php?page=td_system_status&clear_td_query_cache_all') . '" style="margin: 0;">Clear</a>';
	    $clear_expired_btn = $td_query_expired_transients ? '<a class="td-button-system-status" href="' . admin_url('admin.php?page=td_system_status&clear_td_query_cache_expired') . '" style="margin: 0;">Clear</a>' : '<a class="td-button-system-status disabled" href="#" style="margin: 0;">Clear</a>';

	    td_system_status::add('Blocks Cache', array(
		    'check_name' => 'Clear all cached data',
		    'tooltip' => 'This will remove the cached data (queries results) for all blocks, regardless of whether it has expired or not.',
		    'value' => $clear_all_btn,
		    'status' => 'info'
	    ));

	    td_system_status::add('Blocks Cache', array(
		    'check_name' => 'Clear expired cached data',
		    'tooltip' => 'This will remove only the expired cached data (queries results). This clearance is also done automatically once a day via wp cron to clean the database of expired data.',
		    'value' => $clear_expired_btn,
		    'status' => 'info'
	    ));

    }

    td_system_status::render_tables();

    // Clear the Social Counter cache - only if the reset button is used
    if(!empty($_REQUEST['clear_social_counter_cache']) && $_REQUEST['clear_social_counter_cache'] == 1) {
        //clear social counter cache
        td_remote_cache::delete_group('td_social_api');
        ?>
        <!-- redirect page -->
        <script>window.location.replace("<?php echo admin_url() . 'admin.php?page=td_system_status';?>");</script>
    <?php
    }

    // Clear Remote cache individual items
    if( !empty( $_REQUEST['td_remote_cache_group'] ) && !empty( $_REQUEST['td_remote_cache_item'] ) ) {

	    // ... check if it's an instagram business account cache group and also clear stored images
        if ( $_REQUEST['td_remote_cache_group'] === 'td_instagram' && strpos( $_REQUEST['td_remote_cache_item'], 'td_instagram_tk_' ) !== false ) { // td_instagram_tk_tagdiv
	        $args = array(
		        'post_type' => array( 'attachment' ),
		        'post_status' => 'inherit',
		        'posts_per_page' => '-1',
		        'meta_key' => 'td_ig_business_account_attachment',
		        'meta_value' => $_REQUEST['td_remote_cache_item']
	        );
	        $query = new WP_Query( $args );

	        if ( !empty( $query->posts ) ) {
		        foreach ( $query->posts as $attachment ) {
			        wp_delete_attachment( $attachment->ID, true );
		        }
	        }
        }

        td_remote_cache::delete_item($_REQUEST['td_remote_cache_group'], $_REQUEST['td_remote_cache_item']);
        ?>
        <!-- redirect page -->
        <script>window.location.replace("<?php echo admin_url() . 'admin.php?page=td_system_status';?>");</script>
    <?php
    }

    // Clear the Remote cache - only if the reset button is used
    if( !empty( $_REQUEST['clear_remote_cache'] ) && $_REQUEST['clear_remote_cache'] == 1 ) {

        // ... also clear all stored instagram business accounts images
	    $td_remote_cache = get_option( TD_THEME_OPTIONS_NAME . '_remote_cache' );
        if ( !empty( $td_remote_cache ) && is_array( $td_remote_cache ) && array_key_exists('td_instagram', $td_remote_cache) ) {
	        $args = array(
		        'post_type'      => array( 'attachment' ),
		        'post_status'    => 'inherit',
		        'meta_key'       => 'td_ig_business_account_attachment',
		        'posts_per_page' => '-1'
	        );
	        $query = new WP_Query( $args );

	        if ( !empty( $query->posts ) ) {
		        foreach ( $query->posts as $attachment ) {
			        wp_delete_attachment( $attachment->ID, true );
		        }
	        }

        }

        // clear remote cache
        update_option(TD_THEME_OPTIONS_NAME . '_remote_cache', array() );

        ?>
        <!-- redirect page -->
        <script>window.location.replace("<?php echo admin_url() . 'admin.php?page=td_system_status#td-remote-cache-table';?>");</script>

    <?php
    }

    // Clear the Video playlists cache - only if the reset button is used
    if(!empty($_REQUEST['clear_video_cache']) ) {
        if( $_REQUEST['clear_video_cache'] == 1 ) {
            foreach (td_system_status::get_video_playlists_meta('video_playlists_posts_ids') as $post_ID) {
                update_post_meta($post_ID, 'td_playlist_video', '');
            }
            ?>
            <!-- redirect page -->
            <script>window.location.replace("<?php echo admin_url() . 'admin.php?page=td_system_status#td-video-cache-table';?>");</script>

        <?php
        } else if ( $_REQUEST['clear_video_cache'] == 2 ) {
            update_option('td_playlist_video_video_ids', '');
            update_option('td_playlist_video_channel_id', '');
            update_option('td_playlist_video_username', '');
            update_option('td_playlist_video_playlist_id', '');

            update_option('td_playlist_videos_pool', '');
            ?>

            <!-- redirect page -->
            <script>window.location.replace("<?php echo admin_url() . 'admin.php?page=td_system_status#td-video-cache-table-2';?>");</script>
        <?php
        }
    }

    // Clear the covid19 data cache - only if the reset button is used
    if(!empty($_REQUEST['clear_flickr_cache']) && $_REQUEST['clear_flickr_cache'] == 1) {
        update_option('td_flickr_user_id', '');
        update_option('td_flickr_album_id', '');
        update_option('td_flickr_user_albums', '');

        update_option('flickr_photo_pool', '');
        ?>

        <!-- redirect page -->
        <script>window.location.replace("<?php echo admin_url() . 'admin.php?page=td_system_status#td-flickr-cache-table';?>");</script>
        <?php
    }

    // Clear td_log data - only if the reset button is used
    if(!empty($_REQUEST['clear_td_log_data']) && $_REQUEST['clear_td_log_data'] == 1) {

        //clear td log data
        update_option(TD_THEME_OPTIONS_NAME . '_log', array());

        ?>
        <!-- redirect page -->
        <script>window.location.replace("<?php echo admin_url() . 'admin.php?page=td_system_status#td-log-table';?>");</script>

    <?php
    }

    // Remove the registration key
    if(!empty($_REQUEST['reset_registration']) && $_REQUEST['reset_registration'] == 1) {
        td_util::reset_registration();
        ?>
        <!-- redirect page -->
        <script>window.location.replace("<?php echo admin_url() . 'admin.php?page=td_system_status';?>");</script>

    <?php
    }

    // Remove the registration key
    if(!empty($_REQUEST['reset_http_channel']) && $_REQUEST['reset_http_channel'] == 1) {
        //reset http channel
        td_options::update_array('td_remote_http', array());
        //reset cache
        update_option(TD_THEME_OPTIONS_NAME . '_remote_cache', array());
        ?>
        <!-- redirect page -->
        <script>window.location.replace("<?php echo admin_url() . 'admin.php?page=td_system_status';?>");</script>

    <?php
    }

    // clear all blocks td query cache
    if( isset( $_REQUEST['clear_td_query_cache_all'] ) ) {

        // delete all transients
	    td_transients_manager::delete_transients();

        ?>

        <!-- redirect page -->
        <script>window.location.replace("<?php echo admin_url( 'admin.php?page=td_system_status' ); ?>");</script>

    <?php
    }

    // clear only expired blocks td query cache
    if( isset( $_REQUEST['clear_td_query_cache_expired'] ) ) {

        // delete expired transients
	    td_transients_manager::delete_expired_transients();

        ?>

        <!-- redirect page -->
        <script>window.location.replace("<?php echo admin_url( 'admin.php?page=td_system_status' ); ?>");</script>

    <?php
    }

    // on dev it displays the debug area
    $td_debug_area_visible = '';
    if ( TD_DEPLOY_MODE == 'dev' ) {
        $td_debug_area_visible = ' td-debug-area-reveal';
    }

    ?>
    <div class="td-debug-area<?php echo esc_attr( $td_debug_area_visible ) ?>">
        <?php

        // remote cache panel
        // td_remote_cache::set('group1', '1', array(0 => 'parameter1', 1 => 'parameter2'), time() - 10);
        $td_remote_cache_content = get_option(TD_THEME_OPTIONS_NAME . '_remote_cache');
        td_system_status::render_td_remote_cache($td_remote_cache_content);

        //td video playlist data new
        td_system_status::render_td_video_playlists_new();

        //td video playlist data
        td_system_status::render_td_video_playlists();

        //td video playlist data
        td_system_status::render_td_flickr_data();

        // td log panel
        $td_log_content = get_option(TD_THEME_OPTIONS_NAME . '_log');
        td_system_status::render_td_log($td_log_content);

        // custom log sections
        $custom_log_sections = '';
        $custom_log_sections = apply_filters('td_custom_logs', $custom_log_sections);
        echo $custom_log_sections;

        ?>
    </div>

    <!-- debug area script -->
    <script>

        (function () {

            // show-hide the theme debug area
            var clickCounter = 0;
            var lastClick = 0;
            var debugArea = jQuery('.td-debug-area');
            if (!debugArea.hasClass('td-debug-area-reveal')) {

                jQuery('.td-system-status-name').on( 'click', function () {
                    // calculate the time passed from the last click
                    var curTime = (new Date()).getTime();
                    if( (clickCounter != 0) && (curTime - lastClick > 2000) ) {
                        clickCounter = -1;
                    }
                    lastClick = curTime;

                    // reveal the debug area after 4 clicks
                    if (clickCounter == 3) {
                        debugArea.addClass('td-debug-area-reveal');
                        clickCounter = 0;
                    }
                    clickCounter++;

                });
            }

            // show/hide script - used to display the array data on log and remote cache panels
            jQuery('body').on( 'click', '.td-button-system-status-details', function(){
                var arrayViewer = jQuery(this).parent().parent().find('.td-array-viewer');
                // hide - if the td_array_viewer_visible is present remove it and return
                if (arrayViewer.hasClass('td-array-viewer-visible')) {
                    arrayViewer.removeClass('td-array-viewer-visible');
                    jQuery(this).removeClass('td-button-ss-pressed');
                    return;
                }

                jQuery('.td-array-viewer-visible').removeClass('td-array-viewer-visible');
                jQuery('.td-button-ss-pressed').removeClass('td-button-ss-pressed');
                jQuery(this).addClass('td-button-ss-pressed');
                arrayViewer.addClass('td-array-viewer-visible');
            });

        })();

        jQuery().ready(function() {

            var MutationObserver = window.MutationObserver || window.WebKitMutationObserver || window.MozMutationObserver;

            var element = document.querySelector( "input[name='td_option[td_log_status]']" );

            var observer = new MutationObserver( function( mutations ) {
                mutations.forEach( function( mutation ) {
                    if ( mutation.type === "attributes" ) {
                        var value = element.value;

                        jQuery.ajax({
                            type: 'POST',
                            url: td_ajax_url,
                            data: {
                                action: 'td_ajax_system_status_toggle_td_log',
                                td_log_status: value,
                                td_magic_token: tdWpAdminTdLogSwitchNonce
                            },
                            success: function(data, textStatus, XMLHttpRequest){
                                console.log(data);
                            },
                            error: function(MLHttpRequest, textStatus, errorThrown){
                                console.log(errorThrown);
                            }
                        });
                    }
                });
            });

            if ( typeof( element ) !== 'undefined' && element !== null ) {
                observer.observe( element, { attributes: true } );
            }
        });

    </script>

</div>

<?php
   class td_system_status {

       static $system_status = array();

       static function add($section, $status_array) {
           self::$system_status[$section][]= $status_array;
       }

       static function render_tables() {
           foreach (self::$system_status as $section_name => $section_statuses) {
                ?>
                <table class="widefat td-system-status-table" cellspacing="0">
                    <thead>
                        <tr>
                           <th colspan="4"><?php printf( '%1$s', $section_name ) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                <?php

                    foreach ($section_statuses as $status_params) {
                        ?>
                        <tr>
                            <td class="td-system-status-name"><?php printf( '%1$s', $status_params['check_name'] ) ?></td>
                            <td class="td-system-status-help"><!--<a href="#" class="help_tip">[?]</a>--></td>
                            <td class="td-system-status-status">
                                <?php
                                    switch ($status_params['status']) {
                                        case 'green':
                                            echo '<div class="td-system-status-led td-system-status-green td-tooltip" data-position="right" title="' . $status_params['tooltip'] . '"></div>';
                                            break;
                                        case 'yellow':
                                            echo '<div class="td-system-status-led td-system-status-yellow td-tooltip" data-position="right" title="' . $status_params['tooltip'] . '"></div>';
                                            break;
                                        case 'red' :
                                            echo '<div class="td-system-status-led td-system-status-red td-tooltip" data-position="right" title="' . $status_params['tooltip'] . '"></div>';
                                            break;
                                        case 'info':
                                            echo '<div class="td-system-status-led td-system-status-info td-tooltip" data-position="right" title="' . $status_params['tooltip'] . '">i</div>';
                                            break;

                                    }


                                ?>
                            </td>
                            <td class="td-system-status-value"><?php printf( '%1$s', $status_params['value'] ) ?></td>
                        </tr>
                        <?php
                    }

                ?>
                    </tbody>
                </table>
                <?php
           }
       }

       /**
        * It renders the data from td_log
        *
        * @param array $td_log_content - the key used to store the log is: TD_THEME_OPTIONS_NAME . '_log'  (ex: td_011_log)
        */
       static function render_td_log($td_log_content) {
           if (!empty($td_log_content) and is_array($td_log_content)) {
               ?>
               <!-- TD Log data -->
               <table id="td-log-table" class="widefat td-system-status-table td-log-table" cellspacing="0">
                   <thead>
                   <tr>
                       <th colspan="2" style="border-right: 1px solid #dadada;">
                           <div class="td-admin-checkbox td-small-checkbox" style="border: none !important;">
                               <div class="td-demo-install-content">
                                   <p style="margin: 0 10px 0 0; vertical-align: middle;">TD Log</p>
                                   <?php
                                   echo td_panel_generator::checkbox(array(
                                       'ds' => 'td_option',
                                       'option_id' => 'td_log_status',
                                       'true_value' => 'on',
                                       'false_value' => 'off'
                                   ));
                                   ?>
                               </div>
                           </div>
                       </th>
                       <th colspan="3" style="border-right: 1px solid #dadada;">TD Log clear data: <a class="td-remote-cache-reset td-button-system-status td-reset-channel" href="<?php admin_url(); ?>admin.php?page=td_system_status&clear_td_log_data=1">Clear TD Log data</a>
                       </th>
                   </tr>
                   <tr>
                       <th class="td-log-header_file" style="border-right: 1px solid #dadada;">File</th>
                       <th class="td-log-header_function" style="border-right: 1px solid #dadada;">Function:</th>
                       <th class="td-log-header_msg" style="border-right: 1px solid #dadada;">Msg:</th>
                       <th class="td-log-header_more_data" style="border-right: 1px solid #dadada;">More_data:</th>
                       <th class="td-log-header-timestamp" style="border-right: 1px solid #dadada;">Timestamp:</th>
                   </tr>
                   </thead>
                   <tbody>
                   <?php foreach ($td_log_content as $td_log_params) {

                       if (empty($td_log_params['file'])) {
                           $td_log_params['file'] = '';
                       }

                       if (empty($td_log_params['function'])) {
                           $td_log_params['function'] = '';
                       }

                       if (empty($td_log_params['msg'])) {
                           $td_log_params['msg'] = '';
                       }

                       if (empty($td_log_params['more_data'])) {
                           $td_log_params['more_data'] = '';
                       }

                       if (empty($td_log_params['timestamp'])) {
                           $td_log_params['timestamp'] = '';
                       }
                       ?>
                       <tr>
                           <td style="border-right: 1px solid #dadada;">
                               <?php
                               // explode the url and echo only the file name
                               $td_log_url_parts = explode('\\',$td_log_params['file']);
                               echo '<div title="' . $td_log_params['file'] . '">' . end($td_log_url_parts) . '</div>';
                               ?>
                           </td>
                           <td style="border-right: 1px solid #dadada;"><?php printf( '%1$s', $td_log_params['function'] ) ?></td>
                           <td style="border-right: 1px solid #dadada;"><?php printf( '%1$s', $td_log_params['msg'] ) ?></td>
                           <td class="td_log_more_data" style="border-right: 1px solid #dadada;">
                               <div class="td_log_more_data_container">
                                   <?php
                                   //array or object display it in a container
	                               if (is_array($td_log_params['more_data']) || is_object($td_log_params['more_data'])) {
		                               // details button
		                               echo '<div><a class="td-button-system-status-details">View Details</a></div>';
		                               // array data container
		                               echo '<div class="td-array-viewer"><pre>';
		                               print_r( $td_log_params['more_data'] );
		                               echo '</pre></div>';

		                           //string > 200 characters display it in a container
	                               } elseif (is_string($td_log_params['more_data']) && strlen($td_log_params['more_data']) > 200) {
		                               // details button
		                               echo '<div><a class="td-button-system-status-details">View Details</a></div>';
		                               // array data container
		                               echo '<div class="td-array-viewer">';
		                               echo htmlentities($td_log_params['more_data']);
		                               echo '</div>';

                                   //string < 200 characters
                                   } elseif (is_string($td_log_params['more_data'])){
                                       echo htmlentities($td_log_params['more_data']); //display small strings directly in the table

                                   //other type of data
                                   } else {
                                       // details button
                                       echo '<div><a class="td-button-system-status-details">View Details</a></div>';
                                       // object data container
                                       echo '<div class="td-array-viewer"><pre>';
                                       var_dump($td_log_params['more_data']);
                                       echo '</pre></div>';
                                   }?>
                               </div>
                           </td>

                           <td style="border-right: 1px solid #dadada;"><?php echo gmdate("H:i:s", time() - $td_log_params['timestamp'])?> ago</td>
                       </tr>
                   <?php
                   } ?>
                   </tbody>
               </table>
           <?php
           }

           if ( empty( $td_log_content ) ) {
               ?>

               <!-- TD Log no data -->
               <table id="td-log-table" class="widefat td-system-status-table td-log-table" cellspacing="0">
                   <tr>
                       <td><p style="padding: 10px 0 0 10px;">There is no log data stored!</p></td>
                   </tr>
                   <tr>
                       <td>
                           <div class="td-admin-checkbox td-small-checkbox">
                               <div class="td-demo-install-content">
                                   <p style="margin: 0 10px 0 0; vertical-align: middle;">Turn on system status data logging: </p>
                                   <?php
                                   echo td_panel_generator::checkbox(array(
                                       'ds' => 'td_option',
                                       'option_id' => 'td_log_status',
                                       'true_value' => 'on',
                                       'false_value' => 'off'
                                   ));
                                   ?>
                               </div>
                           </div>
                       </td>
                   </tr>

               </table>

               <?php
           }
       }

       static function render_td_remote_cache($td_remote_cache_content) {
           if (!empty($td_remote_cache_content) and is_array($td_remote_cache_content)) {
               ?>
               <!-- TD Remote Cache data -->
               <table id="td-remote-cache-table" class="widefat td-system-status-table" cellspacing="0">
                   <thead>
                   <tr>
                       <th colspan="2" style="border-right: 1px solid #dadada;">TD Remote Cache</th>
                       <th colspan="3">
                           TD Remote Cache clear data:
                           <a class="td-remote-cache-reset td-button-system-status td-reset-channel" href="<?php admin_url(); ?>admin.php?page=td_system_status&clear_remote_cache=1">Clear the Remote cache</a>
                       </th>
                   </tr>
                   <tr>
                       <th>Group</th>
                       <th>Item ID:</th>
                       <th class="td-remote-header-value">Value:</th>
                       <th class="td-remote-header-expires">Expires:</th>
                       <th class="td-remote-header-timestamp">Timestamp:</th>
                   </tr>
                   </thead>
                   <tbody>
                   <?php

                   foreach ($td_remote_cache_content as $td_remote_cache_group => $td_remote_cache_group_content) {

                       foreach ($td_remote_cache_group_content as $td_remote_cache_group_id => $td_remote_cache_group_parameters) {
                       ?>

                       <tr>
                           <td><?php printf( '%1$s', $td_remote_cache_group ) ?></td> <!-- Group -->

                               <td><a class="td-remote-cache-item" href="<?php admin_url(); ?>admin.php?page=td_system_status&td_remote_cache_group=<?php printf( '%1$s', $td_remote_cache_group ) ?>&td_remote_cache_item=<?php printf( '%1$s', $td_remote_cache_group_id ) ?>"><?php printf( '%1$s', $td_remote_cache_group_id ) ?></a></td> <!-- ID -->

                               <td> <!-- Value -->
                                   <div class="td-remote-value-data-container">
                                       <?php
                                       //array or object display it in a container
                                       if (is_array($td_remote_cache_group_parameters['value']) || is_object($td_remote_cache_group_parameters['value'])) {
                                           // details button
                                           echo '<div><a class="td-button-system-status-details">View Details</a></div>';
                                           // array data container
                                           echo '<div class="td-array-viewer"><pre>';
                                           print_r($td_remote_cache_group_parameters['value']);
                                           echo '</pre></div>';

	                                   //string > 200 characters display it in a container
                                       } elseif ( is_string($td_remote_cache_group_parameters['value']) && strlen($td_remote_cache_group_parameters['value']) > 200) {
	                                       // details button
	                                       echo '<div><a class="td-button-system-status-details">View Details</a></div>';
	                                       // array data container
	                                       echo '<div class="td-array-viewer">';
	                                       echo  htmlentities($td_remote_cache_group_parameters['value']);
	                                       echo '</div>';

                                       } else {
                                           echo htmlentities($td_remote_cache_group_parameters['value']); // if it's not an array-object it displays the string
                                       }
                                       ?>
                                   </div>
                               </td>

                               <td><?php printf( '%1$s', $td_remote_cache_group_parameters['expires'] ) ?></td> <!-- Expires -->
                               <td><?php echo gmdate("H:i:s", time() - $td_remote_cache_group_parameters['timestamp']); ?>ago</td> <!-- Timestamp -->
                           <?php } ?>

                       </tr>
                   <?php
                   } ?>
                   </tbody>
               </table>
           <?php }

           if ( empty($td_remote_cache_content) ) {
               ?>

               <!-- TD Remote Cache no data -->
               <table id="td-remote-cache-table" class="widefat td-system-status-table td-remote-cache-table" cellspacing="0">
                   <?php echo '<tr><td>There is no remote cached data stored!</td></tr>'; ?>
               </table>

               <?php
           }
       }

       static function render_td_video_playlists_new() {

            $td_playlist_videos = td_system_status::get_video_playlists_meta_new();
            $td_videos_pool = get_option('td_playlist_videos_pool');
            if( !is_array( $td_videos_pool ) ) {
               $td_videos_pool = array();
            }

            if ( !empty( $td_playlist_videos ) && !empty($td_videos_pool) ) { ?>
                <table id="td-video-cache-table-2" class="widefat td-system-status-table td-video-table" cellspacing="0">
                    <thead>
                        <tr>
                            <th colspan="2" style="border-right: 1px solid #dadada;">Video playlist cached youtube and vimeo ids</th>
                            <th colspan="3">Video playlist cache reset:<a class="td-video-cache-reset td-button-system-status td-reset-channel" href="<?php admin_url(); ?>admin.php?page=td_system_status&clear_video_cache=2">Clear the Video playlist cache</a></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="5" class="td-system-status-inner-table-wrap">
                                <?php foreach ( $td_playlist_videos as $video_service => $video_service_data ) { ?>
                                    <table class="td-system-status-inner-table">
                                        <thead>
                                            <tr>
                                                <th colspan="5" style="border-bottom:0;padding-bottom:0">
                                                    <?php if ( $video_service == 'youtube_ids' ) {
                                                        printf ('%1$s', 'YOUTUBE');
                                                    } else if ( $video_service == 'vimeo_ids' ) {
                                                        printf ('%1$s', 'VIMEO');
                                                    } ?>
                                                </th>
                                            </tr>
                                            <tr>
                                                <th colspan="1">Source type</th>
                                                <th colspan="4" class="td-system-status-inner-table-wrap">
                                                    <table class="td-system-status-inner-table">
                                                        <tr>
                                                            <th class="td-system-status-inner-table-name">Source name</th>
                                                            <th class="td-system-status-inner-table-video">Video</th>
                                                            <th class="td-system-status-inner-table-time">Timestamp</th>
                                                        </tr>
                                                    </table>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            foreach ( $video_service_data as $source => $source_data ) { ?>
                                                <tr>
                                                    <td colspan="1" style="width:20%"><?php
                                                        switch( $source ) {
                                                            case 'video_ids':
                                                                printf ('%1$s', 'Video IDs');
                                                                break;
                                                            case 'channel_id':
                                                                printf ('%1$s', 'Channels (by channel id)');
                                                                break;
                                                            case 'username':
                                                                printf ('%1$s', 'Channels (by username)');
                                                                break;
                                                            case 'playlist_id':
                                                                printf ('%1$s', 'Playlists (by id)');
                                                                break;
                                                            default:
                                                                break;
                                                        }
                                                        ?></td>
                                                    <?php
                                                    switch( $source ) {
                                                        case 'video_ids': ?>
                                                            <td colspan="4" class="td-system-status-inner-table-wrap" style="width:80%">
                                                                <table class="td-system-status-inner-table">
                                                                    <tr>
                                                                        <td class="td-system-status-inner-table-name"></td>
                                                                        <td class="td-system-status-inner-table-video">
                                                                            <a href="#" class="td-button-system-status td-button-system-status-view" data-service="<?php echo $video_service ?>" data-source="<?php echo $source ?>" data-source-name="">View Details</a>

                                                                            <div class="td-system-status-inner-table-videos-info"></div>
                                                                        </td>
                                                                        <td class="td-system-status-inner-table-time"></td>
                                                                    </tr>
                                                                </table>
                                                            </td>

                                                            <?php
                                                            break;

                                                            break;
                                                        case 'channel_id':
                                                        case 'username':
                                                        case 'playlist_id': ?>
                                                            <td colspan="4" class="td-system-status-inner-table-wrap" style="width:80%">
                                                                <?php
                                                                foreach ( $source_data as $name => $name_data ) { ?>
                                                                    <table class="td-system-status-inner-table">
                                                                        <tr>
                                                                            <td class="td-system-status-inner-table-name">
                                                                                <?php printf('%1$s', $name) ?>
                                                                            </td>
                                                                            <td class="td-system-status-inner-table-video">
<!--                                                                                --><?php
//                                                                                foreach( $name_data['items'] as $video ) {
//                                                                                    if( $video['status'] == 'public' ) { ?>
<!--                                                                                        <div class="td-remote-value-data-container">-->
<!--                                                                                            <div class="td-video-id-details"><a class="td-button-system-status-details" title="--><?php //printf('%1$s', $td_videos_pool[$video_service][$video['id']]['title']) ?><!--">--><?php //printf('%1$s', $td_videos_pool[$video_service][$video['id']]['title']) ?><!--</a></div>-->
<!--                                                                                            <div class="td-array-viewer"><pre>-->
<!--                                                                                                --><?php //print_r( $td_videos_pool[$video_service][$video['id']] ) ?>
<!--                                                                                            </pre></div>-->
<!--                                                                                        </div>-->
<!--                                                                                    --><?php //}
//                                                                                } ?>
                                                                                <a href="#" class="td-button-system-status td-button-system-status-view" data-service="<?php echo $video_service ?>" data-source="<?php echo $source ?>" data-source-name="<?php echo $name ?>">View Details</a>

                                                                                <div class="td-system-status-inner-table-videos-info"></div>
                                                                            </td>
                                                                            <td class="td-system-status-inner-table-time">
                                                                                <?php printf('%1$s', date('H:i:s', time() - $name_data['timestamp']) . ' ago') ?>
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                <?php } ?>
                                                            </td>
                                                            <?php
                                                            break;

                                                        default:
                                                            break;
                                                    } ?>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                <?php } ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            <?php } else { ?>
                <!-- video playlists no data -->
                <table id="td-video-cache-table-2" class="widefat td-system-status-table td-remote-cache-table" cellspacing="0">
                    <?php echo '<tr><td>There is no cached data for youtube and/or vimeo video playlists!</td></tr>'; ?>
                </table>
            <?php
            }
        }

       static function render_td_video_playlists () {

           $td_playlist_videos = td_system_status::get_video_playlists_meta();
           /*
           echo '<pre>';
           print_r($td_playlist_videos);
           echo '</pre>';
           */

           ?>

           <!-- Video playlist cached youtube and vimeo ids from the DB -->
           <?php if ( !empty( $td_playlist_videos ) ) { ?>
                    <table id="td-video-cache-table" class="widefat td-system-status-table td-video-table" cellspacing="0">
                        <thead>
                           <tr>
                               <th colspan="2" style="border-right: 1px solid #dadada;">Video playlist cached youtube and vimeo ids</th>
                               <th colspan="3">Video playlist cache reset:<a class="td-video-cache-reset td-button-system-status td-reset-channel" href="<?php admin_url(); ?>admin.php?page=td_system_status&clear_video_cache=1">Clear the Video playlist cache</a></th>
                           </tr>
                           <tr>
                               <th colspan="1">Item ID:</th>
                               <th colspan="2">Youtube ids:</th>
                               <th colspan="2">Vimeo ids:</th>
                           </tr>
                       </thead>
                        <tbody>
                    <?php foreach ($td_playlist_videos as $post_id => $post_video_data) { ?>
                       <tr>
                           <td><?php printf( '%1$s', $post_id ) ?></td>

                           <?php
                           foreach ( $post_video_data as $video_service => $video_service_ids ) {
                               if ( $video_service === "youtube_ids" ) {
                                   echo '<td colspan="2">';
                                   foreach ($video_service_ids as $video_service_id => $data ) {
                                       echo '<div class="td-remote-value-data-container">';
                                       // the youtube video ID
                                       echo '<div class="td-video-id-container">' . $video_service_id . '</div>';
                                       // details button
                                       echo '<div class="td-video-id-details"><a class="td-button-system-status-details">View Details</a></div>';
                                       // array data container
                                       echo '<div class="td-array-viewer"><pre>';
                                       print_r($data);
                                       echo '</pre></div>';
                                       echo '</div>';
                                   }
                                   echo "</td>";
                               }

                               if ( $video_service === "vimeo_ids" ) {
                                   echo '<td colspan="2">';
                                   foreach ($video_service_ids as $video_service_id => $data ) {
                                       echo '<div class="td-remote-value-data-container">';
                                       // the vimeo video ID
                                       echo '<div class="td-video-id-container">' . $video_service_id . '</div>';
                                       // details button
                                       echo '<div class="td-video-id-details"><a class="td-button-system-status-details">View Details</a></div>';
                                       // array data container
                                       echo '<div class="td-array-viewer"><pre>';
                                       print_r($data);
                                       echo '</pre></div>';
                                       echo '</div>';
                                   }
                                   echo "</td>";
                               }
                           }
                           ?>
                       </tr>
                   <?php } ?>
                   </tbody>
                    </table>
           <?php } ?>

           <?php
       }

       static function render_td_flickr_data() {

           $td_flkr_user_photos = get_option('td_flickr_user_id');
           if( !is_array( $td_flkr_user_photos ) ) {
               $td_flkr_user_photos = array();
           }
           $td_flk_album_ids = get_option('td_flickr_album_id');
           if( !is_array( $td_flk_album_ids ) ) {
               $td_flk_album_ids = array();
           }
           $td_flk_user_albums = get_option('td_flickr_user_albums');
           if( !is_array( $td_flk_user_albums ) ) {
               $td_flk_user_albums = array();
           }
           $flickr_photo_pool = get_option('flickr_photo_pool');
           if( !is_array( $flickr_photo_pool ) ) {
               $flickr_photo_pool = array();
           }

           if( !empty( $td_flkr_user_photos ) || !empty( $td_flk_album_ids ) || !empty( $td_flk_user_albums ) || !empty( $flickr_photo_pool ) ) {
               ?>
               <table id="td-flickr-cache-table" class="widefat td-system-status-table td-remote-cache-table" cellspacing="0">
                   <thead>
                   <tr>
                       <th colspan="2" style="border-right: 1px solid #dadada;">Flickr data cache</th>
                       <th colspan="3">Cache reset:<a class="td-video-cache-reset td-button-system-status td-reset-channel" href="<?php admin_url(); ?>admin.php?page=td_system_status&clear_flickr_cache=1">Clear the cached data</a></th>
                   </tr>
                   </thead>
               </table>
           <?php } else { ?>
               <!-- video playlists no data -->
               <table id="td-flickr-cache-table" class="widefat td-system-status-table td-remote-cache-table" cellspacing="0">
                   <?php echo '<tr><td>There is no cached Flickr data!</td></tr>'; ?>
               </table>
           <?php }
       }



       /**
        * @param string $return_type
        * @return array|string - the posts ids for posts that use video playlists or the posts video playlists meta
        */
       static function get_video_playlists_meta($return_type = 'video_playlists_meta') {
           $posts_video_playlist_meta_array = array();
           $posts_with_video_playlists_array = array();

           $args = array(
               'numberposts' => 500,
               'post_type' => array( 'post', 'page'),
               'meta_key' => 'td_playlist_video'
           );

           $posts = get_posts($args);

           foreach ( $posts as $post) {
               $post_video_playlist_meta = td_util::get_post_meta_array($post->ID, 'td_playlist_video');

               if ( !empty ($post_video_playlist_meta)) {
                   $posts_video_playlist_meta_array[$post->ID]=$post_video_playlist_meta;

                   //update the video playlists posts array with the post id
                   $posts_with_video_playlists_array[]=$post->ID;
               }
           }

           if (!empty($posts_video_playlist_meta_array) && $return_type === "video_playlists_meta"){
               return $posts_video_playlist_meta_array;
           } elseif ( !empty($posts_with_video_playlists_array) && $return_type === "video_playlists_posts_ids") {
               return $posts_with_video_playlists_array;
           } else {
               return array();
           }
       }

       static function get_video_playlists_meta_new () {
            $posts_video_playlist_meta_array = array();

            $playlists_sources_video_ids = get_option('td_playlist_video_video_ids');
            if( is_array($playlists_sources_video_ids) ) {
                foreach ( $playlists_sources_video_ids as $video_service => $video_service_data ) {
                    $posts_video_playlist_meta_array[$video_service]['video_ids'] = $video_service_data;
                }
            }

            $playlists_sources_channel_id = get_option('td_playlist_video_channel_id');
            if( is_array($playlists_sources_channel_id) ) {
                foreach ( $playlists_sources_channel_id as $video_service => $video_service_data ) {
                    $posts_video_playlist_meta_array[$video_service]['channel_id'] = $video_service_data;
                }
            }

            $playlists_sources_username = get_option('td_playlist_video_username');
            if( is_array($playlists_sources_username) ) {
                foreach ( $playlists_sources_username as $video_service => $video_service_data ) {
                    $posts_video_playlist_meta_array[$video_service]['username'] = $video_service_data;
                }
            }

            $playlists_sources_playlist_id = get_option('td_playlist_video_playlist_id');
            if( is_array($playlists_sources_playlist_id) ) {
                foreach ( $playlists_sources_playlist_id as $video_service => $video_service_data ) {
                    $posts_video_playlist_meta_array[$video_service]['playlist_id'] = $video_service_data;
                }
            }

           return $posts_video_playlist_meta_array;

       }

       static function render_diagnostics() {

       }

       static function wp_memory_notation_to_number( $size ) {
           $l   = substr( $size, -1 );
           $ret = substr( $size, 0, -1 );
           switch ( strtoupper( $l ) ) {
               case 'P':
                   $ret *= 1024;
               case 'T':
                   $ret *= 1024;
               case 'G':
                   $ret *= 1024;
               case 'M':
                   $ret *= 1024;
               case 'K':
                   $ret *= 1024;
           }
           return $ret;
       }
   }