<!-- HEADER STYLE -->

<?php

$tdb_header_template_is_set = false;

if ( td_global::is_tdb_registered() ) {

    $tdb_header_templates = array();

    // read the tdb category templates
    $wp_query_templates = new WP_Query( array(
            'post_type' => 'tdb_templates',
		    'posts_per_page' => -1
	    )
    );

    if ( !empty( $wp_query_templates->posts ) ) {

        foreach ( $wp_query_templates->posts as $post ) {

            $tdb_template_type = get_post_meta( $post->ID, 'tdb_template_type', true );
            $meta_is_mobile_template = get_post_meta($post->ID, 'tdc_is_mobile_template', true);

            if ( $tdb_template_type === 'header' && (empty($meta_is_mobile_template) || '0' === $meta_is_mobile_template)) {
                $tdb_header_templates[] = array(
                    'text' => $post->post_title,
                    'val' => 'tdb_template_' . $post->ID
                );
            }

            $tdb_header_template = td_options::get( 'tdb_header_template' );

            if ( $tdb_template_type === 'header' && $tdb_header_template === 'tdb_template_' . $post->ID ) {
                $tdb_header_template_is_set = true;
            }
        }
    }


?>

<!-- Cloud Library Category template -->
<?php echo td_panel_generator::box_start(); ?>

    <div class="td-box-row">
    <div class="td-box-description">
        <span class="td-box-title">Cloud Library Template</span>
        <p>Set a <a href="<?php echo admin_url( 'admin.php?page=tdb_cloud_templates' ) ?>" target="_blank">Cloud Library</a> header template for all website.</p>
    </div>
    <div class="td-box-control-full">

        <?php

        $option_id = 'tdb_header_template';
        if (class_exists('SitePress', false)) {
	        global $sitepress;
	        $sitepress_settings = $sitepress->get_settings();
	        if ( isset($sitepress_settings['custom_posts_sync_option'][ 'tdb_templates']) ) {
	            $translation_mode = (int)$sitepress_settings['custom_posts_sync_option']['tdb_templates'];
	            if (1 === $translation_mode) {
	                $option_id .= $sitepress->get_current_language();
	            }
	        }
	    }

        echo td_panel_generator::dropdown(array(
            'ds' => 'td_option',
            'option_id' => $option_id,
            'values' => array_merge(
                array(
                    array('text' => '- No Template -' , 'val' => ''),
                ),
                $tdb_header_templates
            )
        ));

        ?>

    </div>
</div>
<?php echo td_panel_generator::box_end();?>
<hr>
<?php } ?>

<?php
if( 'Newsmag' == TD_THEME_NAME || ( 'Newspaper' == TD_THEME_NAME && defined('TD_STANDARD_PACK') ) ) {

    echo td_panel_generator::box_start('Header Style', true, 'tdb-hide'); ?>

    <!-- HEADER STYLE -->
    <div class="td-box-row">
        <div class="td-box-description">
            <span class="td-box-title">HEADER STYLE</span>
            <p>Select the order in which the header elements will be arranged</p>
        </div>
        <div class="td-box-control-full">
            <?php
            echo td_panel_generator::visual_select_o(array(
                'ds' => 'td_option',
                'option_id' => 'tds_header_style',
                'values' => td_api_header_style::_helper_generate_tds_header_style()
            ));
            ?>
        </div>
    </div>

    <?php
    if ('Newspaper' == TD_THEME_NAME) { ?>
        <!-- SEARCH POSITION -->
        <div class="td-box-row">
            <div class="td-box-description">
                <span class="td-box-title">SEARCH POSITION</span>
                <p>Select the search button placement area</p>
            </div>
            <div class="td-box-control-full">
                <?php
                echo td_panel_generator::radio_button_control(array(
                    'ds' => 'td_option',
                    'option_id' => 'tds_search_placement',
                    'values' => array(
                        array('text' => '<strong>Main menu</strong> - Default', 'val' => ''),
                        array('text' => '<strong>Top bar</strong>', 'val' => 'top_bar'),
                        array('text' => '<strong>Hidden</strong>', 'val' => 'hide')
                    )
                ));
                ?>
            </div>
        </div>
    <?php } ?>


    <?php echo td_panel_generator::box_end(); ?>


    <!-- TOP BAR -->
    <?php echo td_panel_generator::box_start('Top Bar', false, 'tdb-hide'); ?>


    <div class="td-box-row">
        <div class="td-box-description td-box-full">
            <p>
                The top bar is the black top menu. It is very useful when you want to add a <i>login option, social
                    icons</i> and pages like <i>About us, Contact us etc..</i>.
            </p>
        </div>
        <div class="td-box-row-margin-bottom"></div>
    </div>

    <!-- Top bar: enable disable -->
    <div class="td-box-row">
        <div class="td-box-description">
            <span class="td-box-title">Enable top bar</span>
            <p>Hide or show the bar.</p>
        </div>
        <div class="td-box-control-full">
            <?php
            echo td_panel_generator::checkbox(array(
                'ds' => 'td_option',
                'option_id' => 'tds_top_bar',
                'true_value' => '',
                'false_value' => 'hide_top_bar'
            ));
            ?>
        </div>
    </div>


    <?php if (count(td_api_top_bar_template::get_all()) > 0) { ?>
        <!-- Top bar template -->
        <div class="td-box-row">
            <div class="td-box-description">
                <span class="td-box-title">Top bar layout</span>
                <p>How to order the top bar items</p>
            </div>
            <div class="td-box-control-full">
                <?php
                echo td_panel_generator::visual_select_o(array(
                    'ds' => 'td_option',
                    'option_id' => 'tds_top_bar_template',
                    'values' => td_api_top_bar_template::_helper_to_panel_values()
                ));
                ?>
            </div>
        </div>
    <?php } ?>


    <div class="td-box-section-separator"></div>


    <!-- Top menu: enable disable -->
    <div class="td-box-row">
        <div class="td-box-description">
            <span class="td-box-title">Show top menu</span>
            <p>Hide or show the top menu. To hide the social icons: Header ⇢ Social networks</p>
        </div>
        <div class="td-box-control-full">
            <?php
            echo td_panel_generator::checkbox(array(
                'ds' => 'td_option',
                'option_id' => 'tds_top_menu',
                'true_value' => '',
                'false_value' => 'hide'
            ));
            ?>
        </div>
    </div>


    <!-- Top menu: select menu -->
    <div class="td-box-row">
        <div class="td-box-description">
            <span class="td-box-title">Select the top menu</span>
            <p>Select a menu for the top section</p>
        </div>
        <div class="td-box-control-full">
            <?php
            echo td_panel_generator::dropdown(array(
                'ds' => 'wp_theme_menu_spot',
                'option_id' => 'top-menu',
                'values' => td_panel_generator::get_user_created_menus()
            ));
            ?>
        </div>
    </div>


    <div class="td-box-section-separator"></div>


    <!-- Social networks: enable disable -->
    <div class="td-box-row">
        <div class="td-box-description">
            <span class="td-box-title">Show social icons</span>
            <p>Enable / Disable social networks in top menu</p>
        </div>
        <div class="td-box-control-full">
            <?php
            echo td_panel_generator::checkbox(array(
                'ds' => 'td_option',
                'option_id' => 'td_social_networks_show',
                'true_value' => 'show',
                'false_value' => ''
            ));
            ?>
        </div>
    </div>


    <div class="td-box-section-separator"></div>


    <!-- Date: enable disable -->
    <div class="td-box-row">
        <div class="td-box-description">
            <span class="td-box-title">SHOW DATE</span>
            <p>Hide or show the date in the top menu</p>
        </div>
        <div class="td-box-control-full">
            <?php
            echo td_panel_generator::checkbox(array(
                'ds' => 'td_option',
                'option_id' => 'tds_data_top_menu',
                'true_value' => 'show',
                'false_value' => ''
            ));
            ?>
        </div>
    </div>


    <!-- Date: format -->
    <div class="td-box-row">
        <div class="td-box-description">
            <span class="td-box-title">DATE FORMAT</span>
            <p>Default value: l, F j, Y. <a href="http://php.net/manual/en/function.date.php">Read more</a> about the
                date format (it's the same with the php date function)</p>
        </div>
        <div class="td-box-control-full">
            <?php
            echo td_panel_generator::input(array(
                'ds' => 'td_option',
                'option_id' => 'tds_data_time_format'
            ));
            ?>
        </div>
    </div>

    <!-- Date: format -->
    <div class="td-box-row">
        <div class="td-box-description">
            <span class="td-box-title">JAVASCRIPT DATE</span>
            <p>Enable this if you use a cache plugin, it displays the local data.</p>
        </div>
        <div class="td-box-control-full">
            <?php
            echo td_panel_generator::checkbox(array(
                'ds' => 'td_option',
                'option_id' => 'tds_data_js',
                'true_value' => 'true',
                'false_value' => ''
            ));
            ?>
        </div>
    </div>


    <div class="td-box-section-separator td-box-weather"></div>
    <!-- Weather: enable disable -->
    <div class="td-box-row td-box-weather">
        <div class="td-box-description">
            <span class="td-box-title">SHOW WEATHER</span>
            <p>Hide or show the weather info in the top menu</p>
        </div>
        <div class="td-box-control-full">
            <?php
            echo td_panel_generator::checkbox(array(
                'ds' => 'td_option',
                'option_id' => 'tds_weather_top_menu',
                'true_value' => 'show',
                'false_value' => ''
            ));
            ?>
        </div>
    </div>

    <!-- Weather: api key -->
    <div class="td-box-row td-box-weather">
        <div class="td-box-description">
            <span class="td-box-title">Api key</span>
            <p><a href="https://forum.tagdiv.com/weather-widget/" target="_blank">How to get an api key</a></p>
        </div>
        <div class="td-box-control-full">
            <?php
            echo td_panel_generator::input(array(
                'ds' => 'td_option',
                'option_id' => 'tds_weather_key_top_menu'
            ));
            ?>
        </div>
    </div>

    <!-- Weather: location -->
    <div class="td-box-row td-box-weather">
        <div class="td-box-description">
            <span class="td-box-title">Location</span>
            <p><a href="http://openweathermap.org/find" target="_blank">Find your location</a> - You can use "city name"
                or "city id" (check weather widget tutorial).</p>
        </div>
        <div class="td-box-control-full">
            <?php
            echo td_panel_generator::input(array(
                'ds' => 'td_option',
                'option_id' => 'tds_weather_location_top_menu'
            ));
            ?>
        </div>
    </div>


    <!-- Weather: Units -->
    <div class="td-box-row td-box-weather">
        <div class="td-box-description">
            <span class="td-box-title">Units</span>
            <p>Choose what units to use when showing the temperature</p>
        </div>
        <div class="td-box-control-full">
            <?php
            echo td_panel_generator::radio_button_control(array(
                'ds' => 'td_option',
                'option_id' => 'tds_weather_units_top_menu',
                'values' => array(
                    array('text' => 'Celsius', 'val' => ''),
                    array('text' => 'Fahrenheit', 'val' => 'imperial')
                )
            ));
            ?>
        </div>
    </div>


    <?php echo td_panel_generator::box_end();
} ?>


<!-- MAIN MENU -->
<?php echo td_panel_generator::box_start('Main Menu', false, 'tdb-hide'); ?>

    <!-- MAIN MENU -->
    <div class="td-box-row">
        <div class="td-box-description">
            <span class="td-box-title">Header menu (main)</span>
            <p>Select a menu for the main header section</p>
        </div>
        <div class="td-box-control-full">
            <?php
            echo td_panel_generator::dropdown(array(
                'ds' => 'wp_theme_menu_spot',
                'option_id' => 'header-menu',
                'values' => td_panel_generator::get_user_created_menus()
            ));
            ?>
        </div>
    </div>

    <?php if( 'Newsmag' == TD_THEME_NAME || ( 'Newspaper' == TD_THEME_NAME && defined('TD_STANDARD_PACK') ) ) { ?>
        <!-- Mega menu preload -->
        <div class="td-box-row td-box-megamenu-preloader">
            <div class="td-box-description">
                <span class="td-box-title">Mega menu preloader</span>
                <p>Preload content for all mega menus. This provides a better user experience but with a performance hit
                    - <a href="http://forum.tagdiv.com/what-is-ajax-preloading/" target="_blank">read more</a></p>
            </div>
            <div class="td-box-control-full">
                <?php
                echo td_panel_generator::radio_button_control(array(
                    'ds' => 'td_option',
                    'option_id' => 'tds_mega_menu_ajax_preloading',
                    'values' => array(
                        array('text' => '<strong>No preloading</strong> - default', 'val' => ''),
                        array('text' => '<strong>Optimized preloading</strong>', 'val' => 'preload'),
                        array('text' => '<strong>Preload all </strong>', 'val' => 'preload_all')
                    )
                ));
                ?>
            </div>
        </div>

        <div class="td-box-section-separator"></div>

        <!-- STICKY MENU -->
        <div class="td-box-row">
            <div class="td-box-description">
                <span class="td-box-title">STICKY MENU</span>
                <p>How to display the header menu on scroll</p>
            </div>
            <div class="td-box-control-full">
                <?php
                echo td_panel_generator::radio_button_control(array(
                    'ds' => 'td_option',
                    'option_id' => 'tds_snap_menu',
                    'values' => array(
                        array('text' => '<strong>Normal menu</strong> - (not sticky)', 'val' => ''),
                        array('text' => '<strong>Always sticky</strong> - stays at the top of the page', 'val' => 'snap'),
                        array('text' => '<strong>Smart snap </strong> - (mobile)', 'val' => 'smart_snap_mobile'),
                        array('text' => '<strong>Smart snap </strong> - (always)', 'val' => 'smart_snap_always'),
                    )
                ));
                ?>
            </div>
        </div>

        <!-- SHOW THE MOBILE LOGO ON THE STICKY MENU -->
        <div class="td-box-row">
            <div class="td-box-description">
                <span class="td-box-title">LOGO ON STICKY MENU</span>
                <p>Show / Hide the Logo on sticky menu</p>
                <p><strong>Notice: </strong>If you choose <strong>Mobile logo</strong>, upload a logo in <strong>Logo
                        for Mobile</strong> section</p>
            </div>
            <div class="td-box-control-full">
                <?php
                echo td_panel_generator::radio_button_control(array(
                    'ds' => 'td_option',
                    'option_id' => 'tds_logo_on_sticky',
                    'values' => array(
                        array('text' => '<strong>Disabled</strong>', 'val' => ''),
                        array('text' => '<strong>Header logo </strong> - show the header logo', 'val' => 'show_header_logo'),
                        array('text' => '<strong>Mobile logo </strong> - show the mobile logo', 'val' => 'show'),
                    )
                ));
                ?>
            </div>
        </div>

        <div class="td-box-section-separator"></div>

        <?php if ('Newspaper' == TD_THEME_NAME) { ?>
            <!-- Social networks: enable disable -->
            <div class="td-box-row">
                <div class="td-box-description">
                    <span class="td-box-title">Show social icons</span>
                    <p>Enable / Disable social networks in main menu</p>
                </div>
                <div class="td-box-control-full">
                    <?php
                    echo td_panel_generator::checkbox(array(
                        'ds' => 'td_option',
                        'option_id' => 'td_social_networks_menu_show',
                        'true_value' => 'show',
                        'false_value' => ''
                    ));
                    ?>
                </div>
            </div>
        <?php }
    }?>

<?php echo td_panel_generator::box_end();?>


<!-- LOGO -->
<?php echo td_panel_generator::box_start('Logo &amp; Favicon', false); ?>

    <!-- LOGO UPLOAD -->
    <div class="td-box-row">
        <div class="td-box-description">
            <span class="td-box-title">LOGO UPLOAD</span>
            <p>Upload your logo (272 x 90px) .png or .jpg</p>
        </div>
        <div class="td-box-control-full">
            <?php
            echo td_panel_generator::upload_image(array(
                'ds' => 'td_option',
                'option_id' => 'tds_logo_upload'
            ));
            ?>
        </div>
    </div>

<div class="td-box-row" style="display:none;">
    <div class="td-box-description">
        <span class="td-box-title">WIDTH</span>
    </div>
    <div class="td-box-control-full">
        <?php
        echo td_panel_generator::input(array(
            'ds' => 'td_option',
            'option_id' => 'tds_logo_upload_width'
        ));
        ?>
    </div>
</div>

<div class="td-box-row" style="display:none;">
    <div class="td-box-description">
        <span class="td-box-title">Height</span>
    </div>
    <div class="td-box-control-full">
        <?php
        echo td_panel_generator::input(array(
            'ds' => 'td_option',
            'option_id' => 'tds_logo_upload_height'
        ));
        ?>
    </div>
</div>

    <!-- RETINA LOGO UPLOAD -->
    <div class="td-box-row">
        <div class="td-box-description">
            <span class="td-box-title">RETINA LOGO UPLOAD</span>
            <p>Upload your retina logo (544 x 180px) .png or .jpg. </p>
            <ul>
                <li>If you do not set any retina logo, the site will load the normal logo on retina displays</li>
                <li>The retina logo has to have the same file format with the normal logo</li>
            </ul>
        </div>
        <div class="td-box-control-full">
            <?php
            echo td_panel_generator::upload_image(array(
                'ds' => 'td_option',
                'option_id' => 'tds_logo_upload_r'
            ));
            ?>
        </div>
    </div>


    <!-- FAVICON -->
    <div class="td-box-row">
        <div class="td-box-description">
            <span class="td-box-title">FAVICON</span>
            <p>Optional - upload a favicon image <br>(16 x 16px) .png</p>
        </div>
        <div class="td-box-control-full">
            <?php
            echo td_panel_generator::upload_image(array(
                'ds' => 'td_option',
                'option_id' => 'tds_favicon_upload'
            ));
            ?>
        </div>
    </div>


    <!-- Logo ALT attribute -->
    <div class="td-box-row">
        <div class="td-box-description">
            <span class="td-box-title">LOGO ALT ATTRIBUTE</span>
            <p><a href="http://www.w3schools.com/tags/att_img_alt.asp" target="_blank">Alt attribute</a> for the logo. This is the alternative text if the logo cannot be displayed. It's useful for SEO and generally is the name of the site.</p>
        </div>
        <div class="td-box-control-full">
            <?php
            echo td_panel_generator::input(array(
                'ds' => 'td_option',
                'option_id' => 'tds_logo_alt'
            ));
            ?>
        </div>
    </div>


    <!-- Logo TITLE attribute -->
    <div class="td-box-row">
        <div class="td-box-description">
            <span class="td-box-title">LOGO TITLE ATTRIBUTE</span>
            <p><a href="http://www.w3schools.com/tags/att_global_title.asp" target="_blank">Title attribute</a> for the logo. This attribute specifies extra information about the logo. Most browsers will show a tooltip with this text on logo hover.</p>
        </div>
        <div class="td-box-control-full">
            <?php
            echo td_panel_generator::input(array(
                'ds' => 'td_option',
                'option_id' => 'tds_logo_title'
            ));
            ?>
        </div>
    </div>

<?php if (td_api_features::is_enabled('text_logo') === true) { ?>

    <!-- Text header LOGO description -->
    <div class="td-box-row" style="margin-top: 85px;">
        <div class="td-box-description td-box-full">
            <span class="td-box-title"><?php echo td_api_text::get('text_header_logo') ?></span>
            <p><?php echo td_api_text::get('text_header_logo_description') ?></p>
        </div>
        <div class="td-box-row-margin-bottom"></div>
    </div>


	<!-- Text LOGO -->
	<div class="td-box-row">
		<div class="td-box-description">
			<span class="td-box-title">TEXT LOGO</span>
			<p>Write a text logo</p>
		</div>
		<div class="td-box-control-full">
			<?php
			echo td_panel_generator::input(array(
				'ds' => 'td_option',
				'option_id' => 'tds_logo_text',
				'placeholder' => strtoupper(TD_THEME_NAME)
			));
			?>
		</div>
	</div>


	<!-- Text LOGO Tagline -->
	<div class="td-box-row">
		<div class="td-box-description">
			<span class="td-box-title">TEXT LOGO TAGLINE</span>
			<p>Write a tagline for the text logo</p>
		</div>
		<div class="td-box-control-full">
			<?php
			echo td_panel_generator::input(array(
				'ds' => 'td_option',
				'option_id' => 'tds_tagline_text',
				'placeholder' => 'DISCOVER THE ART OF PUBLISHING'
			));
			?>
		</div>
	</div>

    <div class="td-box-row">
        <div class="td-box-description">
            <span class="td-box-title">Logo H1 on pages </span>
            <p>Enable/disable H1 tag on logo shortcode on all pages except the frontpage. If it is disabled, the h1 tag will be removed no matter what settings are on the Header Logo shortcode.</p>
            <p>On the frontpage, it can be disabled from Header Logo shortcode.</p>
        </div>
        <div class="td-box-control-full">
            <?php
            echo td_panel_generator::checkbox(array(
                'ds' => 'td_option',
                'option_id' => 'tds_logo_h1_pages',
                'true_value' => '',
                'false_value' => 'false'
            ));
            ?>
        </div>
    </div>

<?php } ?>

<?php echo td_panel_generator::box_end();?>


<!-- LOGO for MOBILE-->
<?php
    echo td_panel_generator::box_start('Mobile Header', false);

    if( 'Newsmag' == TD_THEME_NAME || ( 'Newspaper' == TD_THEME_NAME && defined('TD_STANDARD_PACK') ) ) { ?>

    <div class="td-box-row">
        <div class="td-box-description td-box-full">
            <p>You can optionally load a different logo on mobile phones and small screens. Usually the logo is smaller
                so that it can fit in the smart affix menu. iPhone, iPad, Samsung and a lot of phones use the retina
                logo.</p>
            <p>If you don't upload any Logo Mobile by default will be used the Logo that you uploaded in the section
                above. This Option is recommended when your logo will not scale perfect on mobile devices.</p>
            <p><strong>Notice: </strong>Don't upload a logo for Mobile if you use <strong>Header
                    Style: </strong> <?php echo td_api_text::get('text_header_logo_mobile') ?>, It's not necessary.</p>
        </div>
        <div class="td-box-row-margin-bottom"></div>
    </div>

    <!-- LOGO MOBILE -->
    <div class="td-box-row">
        <div class="td-box-description">
            <span class="td-box-title">LOGO MOBILE</span>
            <p>Upload your logo</p>
            <p><strong>Note: </strong>For best results logo mobile
                size: <?php echo td_api_text::get('text_header_logo_mobile_image') ?></p>
        </div>
        <div class="td-box-control-full">
            <?php
            echo td_panel_generator::upload_image(array(
                'ds' => 'td_option',
                'option_id' => 'tds_logo_menu_upload'
            ));
            ?>
        </div>
    </div>

    <div class="td-box-row" style="display:none;">
        <div class="td-box-description">
            <span class="td-box-title">WIDTH</span>
        </div>
        <div class="td-box-control-full">
            <?php
            echo td_panel_generator::input(array(
                'ds' => 'td_option',
                'option_id' => 'tds_logo_menu_upload_width'
            ));
            ?>
        </div>
    </div>

    <div class="td-box-row" style="display:none;">
        <div class="td-box-description">
            <span class="td-box-title">Height</span>
        </div>
        <div class="td-box-control-full">
            <?php
            echo td_panel_generator::input(array(
                'ds' => 'td_option',
                'option_id' => 'tds_logo_menu_upload_height'
            ));
            ?>
        </div>
    </div>

        <!-- RETINA LOGO MOBILE IN MENU UPLOAD -->
    <div class="td-box-row">
        <div class="td-box-description">
            <span class="td-box-title">RETINA LOGO MOBILE</span>
            <p>Upload your retina logo (double size)</p>
            <p><strong>Note: </strong>For best results retina logo mobile
                size: <?php echo td_api_text::get('text_header_logo_mobile_image_retina') ?></p>
        </div>
        <div class="td-box-control-full">
            <?php
            echo td_panel_generator::upload_image(array(
                'ds' => 'td_option',
                'option_id' => 'tds_logo_menu_upload_r'
            ));
            ?>
        </div>
        <div class="td-box-section-separator"></div>

    </div>

    <?php } ?>



<div class="td-box-row">
    <div class="td-box-description">
        <span class="td-box-title">Show Mobile Menu</span>
        <p>If is inactive, it removes the Mobile Menu HTML (and the button on standard headers).</p>
    </div>
    <div class="td-box-control-full">
        <?php
        echo td_panel_generator::checkbox(array(
            'ds' => 'td_option',
            'option_id' => 'tds_hide_mobile_menu',
            'true_value' => '',
            'false_value' => 'hide'
        ));
        ?>
    </div>
</div>

<div class="td-box-row">
    <div class="td-box-description">
        <span class="td-box-title">Show Mobile Search</span>
        <p>If is inactive, it removes the Mobile Search HTML (and the button on standard headers).</p>
    </div>
    <div class="td-box-control-full">
        <?php
        echo td_panel_generator::checkbox(array(
            'ds' => 'td_option',
            'option_id' => 'tds_hide_mobile_search',
            'true_value' => '',
            'false_value' => 'hide'
        ));
        ?>
    </div>
</div>

<!-- more info -->
<div class="td-box-row">
    <div class="td-box-description td-box-full">
        <span class="td-box-title">More information:</span>
        <p style="margin-bottom: 10px !important;">Disabling these options will also impact the Mobile Menu and Mobile Search shortcodes. Use these settings if you prefer not to utilize the search and mobile menu functionalities.</p>
    </div>
</div>



<?php
    echo td_panel_generator::box_end();
 ?>


<!-- SIGNIN/JOIN-->
<?php echo td_panel_generator::box_start('Sign In / Join', false); ?>

<!-- Sign In / Join: enable disable -->
<div class="td-box-row">
    <div class="td-box-description">
        <span class="td-box-title">Show sign in / join</span>
        <p>Enable/disable the theme header login. (default is disabled).
			<?php td_util::tooltip_html('
                        <h3>Show sign in / join:</h3>
                        <ul>
                            <li>This setting applies to theme\'s predefined header templates. </li>
                            <li>If it\'s enabled the Sign in / Join link shows up in the top menu and mobile menu.</li>
                        </ul>
                ', 'right')?>
        </p>
    </div>
    <div class="td-box-control-full">
		<?php
		echo td_panel_generator::checkbox(array(
			'ds' => 'td_option',
			'option_id' => 'tds_login_sign_in_widget',
			'true_value' => 'show',
			'false_value' => ''
		));
		?>
    </div>
</div>

<div class="td-box-row">
    <div class="td-box-description">
        <span class="td-box-title">Verify email on sign up</span>
        <p>Enable/disable the email verification on register (default is enabled).
            <?php td_util::tooltip_html('
                        <p>By enabling this option, the email address will be verified on registration using is_email() function. <a href="https://developer.wordpress.org/reference/functions/is_email/" target="_blank">More information!</a> </p>
                ', 'right')?>
        </p>
    </div>
    <div class="td-box-control-full">
        <?php
        echo td_panel_generator::checkbox(array(
            'ds' => 'td_option',
            'option_id' => 'tds_verify_email_registration',
            'true_value' => '',
            'false_value' => 'hide'
        ));
        ?>
    </div>
</div>

<?php echo td_panel_generator::box_end();?>


<?php
if( 'Newsmag' == TD_THEME_NAME || ( 'Newspaper' == TD_THEME_NAME && defined('TD_STANDARD_PACK') ) ) {
    if ('Newspaper' == TD_THEME_NAME) { ?>

        <!-- HEADER BACKGROUND -->
        <?php echo td_panel_generator::box_start('Header background', false, 'tdb-hide'); ?>

        <!-- BACKGROUND UPLOAD -->
        <div class="td-box-row">
            <div class="td-box-description">
                <span class="td-box-title">HEADER BACKGROUND</span>
                <p>Upload a header background image</p>
            </div>
            <div class="td-box-control-full">
                <?php
                echo td_panel_generator::upload_image(array(
                    'ds' => 'td_option',
                    'option_id' => 'tds_header_background_image'
                ));
                ?>
            </div>
        </div>

        <!-- Background Repeat -->
        <div class="td-box-row">
            <div class="td-box-description">
                <span class="td-box-title">REPEAT</span>
                <p>How the background image will be displayed</p>
            </div>
            <div class="td-box-control-full">
                <?php
                echo td_panel_generator::radio_button_control(array(
                    'ds' => 'td_option',
                    'option_id' => 'tds_header_background_repeat',
                    'values' => array(
                        array('text' => 'No Repeat', 'val' => ''),
                        array('text' => 'Tile', 'val' => 'repeat'),
                        array('text' => 'Tile Horizontally', 'val' => 'repeat-x'),
                        array('text' => 'Tile Vertically', 'val' => 'repeat-y')
                    )
                ));
                ?>
            </div>
        </div>

        <!-- Background Size -->
        <div class="td-box-row">
            <div class="td-box-description">
                <span class="td-box-title">SIZE</span>
                <p>Set the background image size</p>
            </div>
            <div class="td-box-control-full">
                <?php
                echo td_panel_generator::radio_button_control(array(
                    'ds' => 'td_option',
                    'option_id' => 'tds_header_background_size',
                    'values' => array(
                        array('text' => 'Auto', 'val' => 'auto'),
                        array('text' => 'Full Width', 'val' => '100% auto'),
                        array('text' => 'Full Height', 'val' => 'auto 100%'),
                        array('text' => 'Cover', 'val' => ''),
                        array('text' => 'Contain', 'val' => 'contain')
                    )
                ));
                ?>
            </div>
        </div>

        <!-- Background position -->
        <div class="td-box-row">
            <div class="td-box-description">
                <span class="td-box-title">POSITION</span>
                <p>Position your background image</p>
            </div>
            <div class="td-box-control-full">
                <?php
                echo td_panel_generator::radio_button_control(array(
                    'ds' => 'td_option',
                    'option_id' => 'tds_header_background_position',
                    'values' => array(
                        array('text' => 'Bottom', 'val' => ''),
                        array('text' => 'Center', 'val' => 'center center'),
                        array('text' => 'Top', 'val' => 'center top')
                    )
                ));
                ?>
            </div>
        </div>

        <!-- Background opacity -->
        <div class="td-box-row">
            <div class="td-box-description">
                <span class="td-box-title">BACKGROUND OPACITY</span>
                <p>Set the background image transparency (Example: 0.3)</p>
            </div>
            <div class="td-box-control-full">
                <?php
                echo td_panel_generator::input(array(
                    'ds' => 'td_option',
                    'option_id' => 'tds_header_background_opacity'
                ));
                ?>
            </div>
        </div>

        <?php echo td_panel_generator::box_end(); ?>
    <?php }
} ?>


<!-- iOS Bookmarklet -->
<?php echo td_panel_generator::box_start('iOS Bookmarklet', false); ?>

    <div class="td-box-row">
        <div class="td-box-description td-box-full">
            <p>The bookmarklets work on iOS and Android. When a user adds your site to the home screen, the phone will download one of the icons from here (based on the screen size and device type) and your site will appear with that icon on the home screen</p>
        </div>
        <div class="td-box-row-margin-bottom"></div>
    </div>



    <!-- iOS bookmarklet 76x76 -->
    <div class="td-box-row">
        <div class="td-box-description">
            <span class="td-box-title">IMAGE 76 x 76</span>
            <p>Upload your icon (76 x 76px) .png</p>
        </div>
        <div class="td-box-control-full">
            <?php // ipad mini non retina + ipad 2
            echo td_panel_generator::upload_image(array(
                'ds' => 'td_option',
                'option_id' => 'tds_ios_icon_76'
            ));
            ?>
        </div>
    </div>


    <!-- iOS bookmarklet 114x114 -->
    <div class="td-box-row">
        <div class="td-box-description">
            <span class="td-box-title">IMAGE 114 x 114</span>
            <p>Upload your icon (114 x 114px) .png</p>
        </div>
        <div class="td-box-control-full">
            <?php  // iphone retina ios6
            echo td_panel_generator::upload_image(array(
                'ds' => 'td_option',
                'option_id' => 'tds_ios_icon_114'
            ));
            ?>
        </div>
    </div>


    <!-- iOS bookmarklet 120x120 -->
    <div class="td-box-row">
        <div class="td-box-description">
            <span class="td-box-title">IMAGE 120 x 120</span>
            <p>Upload your icon (120 x 120px) .png</p>
        </div>
        <div class="td-box-control-full">
            <?php // iphone retina ioS7
            echo td_panel_generator::upload_image(array(
                'ds' => 'td_option',
                'option_id' => 'tds_ios_icon_120'
            ));
            ?>
        </div>
    </div>


    <!-- iOS bookmarklet 144x144 -->
    <div class="td-box-row">
        <div class="td-box-description">
            <span class="td-box-title">IMAGE 144 x 144</span>
            <p>Upload your icon (144 x 144px) .png</p>
        </div>
        <div class="td-box-control-full">
            <?php // ipad retina ios6
            echo td_panel_generator::upload_image(array(
                'ds' => 'td_option',
                'option_id' => 'tds_ios_icon_144'
            ));
            ?>
        </div>
    </div>


    <!-- iOS bookmarklet 152x152 -->
    <div class="td-box-row">
        <div class="td-box-description">
            <span class="td-box-title">IMAGE 152 x 152</span>
            <p>Upload your icon (152 x 152px) .png</p>
        </div>
        <div class="td-box-control-full">
            <?php // ipad retina ios7
            echo td_panel_generator::upload_image(array(
                'ds' => 'td_option',
                'option_id' => 'tds_ios_icon_152'
            ));
            ?>
        </div>
    </div>


<?php echo td_panel_generator::box_end();?>

