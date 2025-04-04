<?php
/**
 * Template use to render on the front end for single posts
 * - we start with the content context
 * tdb_state_template has a wp-query already, we only get in this template if a template is set, otherwise we load the
 * theme default template
 */

if ( tdb_state::is_ajax() ) {

?>

<!doctype html >
<!--[if IE 8]>    <html class="ie8" lang="en"> <![endif]-->
<!--[if IE 9]>    <html class="ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html <?php language_attributes(); ?>> <!--<![endif]-->

<head>

<title><?php wp_title('|', true, 'right'); ?></title>
<meta charset="<?php bloginfo( 'charset' );?>" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />

<?php
    // remove unnecessary head actions
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'amp_frontend_add_canonical');

    wp_head();
?>

</head>

<body <?php body_class() ?> itemscope="itemscope" itemtype="<?php echo td_global::$http_or_https?>://schema.org/WebPage">
<div id="td-outer-wrap" class="td-theme-wrap">

<?php
    do_action('td_wp_booster_after_header'); //used by unique articles
} else {
    get_header();
}

global $wp_query, $post, $tdb_state_single;

// autoload post data
$post_id = $tdb_state_single->get_wp_query()->post->ID;
$post_title = $tdb_state_single->get_wp_query()->post->post_title;
$post_url = get_permalink($post_id);
$post_type = $tdb_state_single->post_type->__invoke();
$template_id = tdb_state_template::get_wp_query()->post->ID;

// tpl autoload settings
$template_autoload_type = '';
$template_autoload_status = '';
$template_autoload_count = '';
$template_autoload_scroll_percent = '';

// check for autoload options on template's settings meta
$tdb_template_settings = get_post_meta( $template_id, 'tdb_template_settings', true );
if ( $tdb_template_settings ) {
    $autoload_options = $tdb_template_settings['autoload_options'] ?? [];

    if ( $autoload_options ) {
        $template_autoload_type = $autoload_options['type'] ?? [];
        $template_autoload_status = $autoload_options['status'] ?? [];
        $template_autoload_count = $autoload_options['count'] ?? [];
        $template_autoload_scroll_percent = $autoload_options['scroll_percent'] ?? [];
    }

}

    //echo '<pre class="td-container">';
    //
    //    echo '$tdb_state_single > post type: ';
    //    print_r( $post_type );
    //    echo '<br>';
    //
    //    echo 'tdbTemplate Type: ';
    //    print_r( tdb_state_template::get_template_type() );
    //    echo '<br>';
    //    echo 'tdbTemplate ID: ';
    //    print_r( tdb_state_template::get_wp_query()->post->ID );
    //    echo '<br>';
    //    echo '$template_autoload_status: ';
    //    print_r($template_autoload_status);
    //    echo '<br>';
    //    echo '$template_autoload_type: ';
    //    print_r($template_autoload_type);
    //    echo '<br>';
    //    echo '$template_autoload_count: ';
    //    print_r($template_autoload_count);
    //    echo '<br>';
    //    echo '$template_autoload_scroll_percent: ';
    //    print_r($template_autoload_scroll_percent);
    //    echo '<br>';
    //
    //echo '</pre>';

// autoload(inf) loading settings
$tdb_p_autoload_status = !empty($template_autoload_status) ? $template_autoload_status : td_util::get_option('tdb_p_autoload_status', 'off' );
$tdb_p_autoload_type = !empty($template_autoload_type) ? $template_autoload_type : td_util::get_option('tdb_p_autoload_type', '' );
$tdb_p_autoload_count = !empty($template_autoload_count) ? $template_autoload_count : td_util::get_option('tdb_p_autoload_count', 5 );
$tdb_p_autoload_scroll_percent = !empty($template_autoload_scroll_percent) ? $template_autoload_scroll_percent : td_util::get_option('tdb_p_autoload_scroll_percent', 50 );

// this class is common and added to all posts loaded via ajax > iframe ... when the autoload feature is enabled on single cloud templates
$tdb_p_autoload_content_wrap_class = ( $tdb_p_autoload_status === 'on' ) ? ' tdb-autoload-wrap' : '';

// these data fields are used by posts autoload js on url change @see tdbAutoload._locationChange()
$data_post_url = ( $tdb_p_autoload_status === 'on' ) ? ' data-post-url="' . esc_url($post_url) . '"' : '';
$data_post_edit_url = ( $tdb_p_autoload_status === 'on' ) ? ' data-post-edit-url="' . get_edit_post_link($post_id) . '"' : '';
$data_post_title = ( $tdb_p_autoload_status === 'on' ) ? ' data-post-title="' . esc_attr($post_title) . '"' : '';

?>
    <div id="tdb-autoload-article" data-autoload="<?php echo esc_attr($tdb_p_autoload_status); ?>" data-autoload-org-post-id="<?php echo esc_attr($post_id); ?>" data-autoload-tpl-id="<?php echo esc_attr($template_id); ?>" data-autoload-type="<?php echo esc_attr($tdb_p_autoload_type); ?>" data-autoload-count="<?php echo esc_attr($tdb_p_autoload_count); ?>" data-autoload-scroll-percent="<?php echo esc_attr($tdb_p_autoload_scroll_percent); ?>">
    <style>
        .tdb-autoload-wrap {
            position: relative;
        }
        .tdb-autoload-wrap .tdb-loader-autoload {
            top: auto !important;
            bottom: 50px !important;
        }
        .tdb-autoload-debug {
            display: none;
            width: 1068px;
            margin-right: auto;
            margin-left: auto;
        }
        @media (min-width: 1019px) and (max-width: 1018px) {
            .tdb-autoload-debug {
                width: 740px;
            }
        }
        @media (max-width: 767px) {
            .tdb-autoload-debug {
                display: none;
                width: 100%;
                padding-left: 20px;
                padding-right: 20px;
            }
        }
    </style>

<?php

if ( have_posts() ) {

    // save the content wp_query - mainly for the top black bar for now and to revert to it at the end of the template
    tdb_state_content::set_wp_query($wp_query);

    $post_item_scope = '';
    $post_item_scope_meta = '';

    td_global::load_single_post($post);

    if ( !tdc_state::is_live_editor_ajax() && !tdc_state::is_live_editor_iframe() ) {

        // increment the views counter
        td_page_views::update_page_views($post->ID);

        // get the post item scope
        $post_item_scope = $tdb_state_single->post_item_scope->__invoke();

        // get the post item scope meta
        $post_item_scope_meta = $tdb_state_single->post_item_scope_meta->__invoke();

    }

    $wp_query = tdb_state_template::get_wp_query(); // set the global wp_query as the template one

    while ( have_posts() ) : the_post();

        ?>
        <div class="td-main-content-wrap td-container-wrap">
            <div class="tdc-content-wrap<?php echo $tdb_p_autoload_content_wrap_class ?>">
                <article id="template-id-<?php the_ID() ?>"
                    <?php post_class() ?>
                    <?php echo $post_item_scope ?>
                    <?php echo $data_post_url; ?>
                    <?php echo $data_post_edit_url; ?>
                    <?php echo $data_post_title; ?>
                >
	                <?php //the_content(); ?>
                    <?php
                        add_filter( 'tdb_template_content', 'do_shortcode' );
                        $content = $post->post_content;
                        echo apply_filters( 'tdb_template_content', $content );
                    ?>
                    <?php echo $post_item_scope_meta ?>
                </article>
            </div>
        </div>
        <?php

    endwhile;

    // revert the content wp_query
    $wp_query = tdb_state_content::get_wp_query();

} else {
    /**
     * no posts to display. This function generates the __td('No posts to display').
     * the text can be overwritten by the template using the global @see td_global::$custom_no_posts_message
     */
    echo td_page_generator::no_posts(); //@todo trebuie facut ceva intern
}

?>

    </div> <!-- #tdb-autoload-article -->

<?php
if ( tdb_state::is_ajax() ) { ?>

</div><!--close td-outer-wrap-->

    <?php

    // remove unnecessary footer actions
    remove_action( 'wp_footer', 'td_theme_style_footer' );
    remove_action( 'wp_footer', 'tdc_on_add_css_live_components' );
    remove_action( 'wp_footer', 'tdc_on_live_css_inject_editor', 100000 );
    remove_action( 'wp_footer', 'td_more_article_box::on_wp_footer_render_box' );

    wp_footer();

    ?>
</body>
</html>
    <?php

} else {
    get_footer();
}




















