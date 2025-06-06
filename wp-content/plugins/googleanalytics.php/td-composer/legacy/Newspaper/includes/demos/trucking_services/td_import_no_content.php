<?php


/*  ----------------------------------------------------------------------------
	PAGES
*/
$page_contact_id = td_demo_content::add_page( array(
    'title' => 'Contact',
    'file' => 'contact.txt',
    'demo_unique_id' => '49660c0a588fa63',
));

$page_services_id = td_demo_content::add_page( array(
    'title' => 'Services',
    'file' => 'services.txt',
    'demo_unique_id' => '52660c0a58900ec',
));

$page_about_id = td_demo_content::add_page( array(
    'title' => 'About',
    'file' => 'about.txt',
    'demo_unique_id' => '84660c0a5890675',
));

$page_home_id = td_demo_content::add_page( array(
    'title' => 'Home',
    'file' => 'home.txt',
    'homepage' => true,
    'demo_unique_id' => '75660c0a5890e07',
));



/*  ----------------------------------------------------------------------------
	CLOUD TEMPLATES
*/
$template_tag_template_trucking_id = td_demo_content::add_cloud_template( array(
    'title' => 'Tag Template - Trucking',
    'file' => 'tag_cloud_template.txt',
    'template_type' => 'tag',
));

td_demo_misc::update_global_tag_template( 'tdb_template_' . $template_tag_template_trucking_id );


$template_date_template_trucking_id = td_demo_content::add_cloud_template( array(
    'title' => 'Date Template - Trucking',
    'file' => 'date_cloud_template.txt',
    'template_type' => 'date',
));

td_demo_misc::update_global_date_template( 'tdb_template_' . $template_date_template_trucking_id );


$template_search_template_trucking_id = td_demo_content::add_cloud_template( array(
    'title' => 'Search Template - Trucking',
    'file' => 'search_cloud_template.txt',
    'template_type' => 'search',
));

td_demo_misc::update_global_search_template( 'tdb_template_' . $template_search_template_trucking_id );


$template_author_template_trucking_id = td_demo_content::add_cloud_template( array(
    'title' => 'Author Template - Trucking',
    'file' => 'author_cloud_template.txt',
    'template_type' => 'author',
));

td_demo_misc::update_global_author_template( 'tdb_template_' . $template_author_template_trucking_id );


$template_404_template_trucking_id = td_demo_content::add_cloud_template( array(
    'title' => '404 Template - Trucking',
    'file' => '404_cloud_template.txt',
    'template_type' => '404',
));

$template_category_template_trucking_id = td_demo_content::add_cloud_template( array(
    'title' => 'Category Template - Trucking',
    'file' => 'cat_cloud_template.txt',
    'template_type' => 'category',
));

td_demo_misc::update_global_category_template( 'tdb_template_' . $template_category_template_trucking_id );


$template_single_post_template_trucking_id = td_demo_content::add_cloud_template( array(
    'title' => 'Single Post Template - Trucking',
    'file' => 'post_cloud_template.txt',
    'template_type' => 'single',
));

td_util::update_option( 'td_default_site_post_template', 'tdb_template_' . $template_single_post_template_trucking_id );


$template_footer_trucking_id = td_demo_content::add_cloud_template( array(
    'title' => 'Footer - Trucking',
    'file' => 'footer_trucking_cloud_template.txt',
    'template_type' => 'footer',
));

td_demo_misc::update_global_footer_template( 'tdb_template_' . $template_footer_trucking_id );


$template_header_template_main_trucking_id = td_demo_content::add_cloud_template( array(
    'title' => 'Header Template Main - Trucking',
    'file' => 'header_template_main_trucking_cloud_template.txt',
    'template_type' => 'header',
));

td_demo_misc::update_global_header_template( 'tdb_template_' . $template_header_template_main_trucking_id );


$template_header_template_trucking_id = td_demo_content::add_cloud_template( array(
    'title' => 'Header Template - Trucking',
    'file' => 'header_template_trucking_cloud_template.txt',
    'template_type' => 'header',
));




/*  ----------------------------------------------------------------------------
	GENERAL SETTINGS
*/
td_demo_misc::update_background('', false);

td_demo_misc::update_background_mobile('tdx_pic_2');

td_demo_misc::update_background_login('');

td_demo_misc::update_background_header('');

td_demo_misc::update_background_footer('');

td_demo_misc::update_footer_text('');

td_demo_misc::update_logo(array('normal' => '','retina' => '','mobile' => '',));

td_demo_misc::update_footer_logo(array('normal' => '','retina' => '',));

td_demo_misc::add_social_buttons(array());

$generated_css = td_css_generator();
if ( function_exists('tdsp_css_generator') ) {
    $generated_css .= tdsp_css_generator();
}
td_util::update_option( 'tds_user_compile_css', $generated_css );

// cloud templates metas
td_demo_content::update_meta( $template_single_post_template_trucking_id, 'tdc_header_template_id', $template_header_template_trucking_id );

td_demo_content::update_meta( $template_footer_trucking_id, 'tdc_footer_template_id', $template_footer_trucking_id );

td_demo_content::update_meta( $template_header_template_main_trucking_id, 'tdc_header_template_id', $template_header_template_main_trucking_id );

td_demo_content::update_meta( $template_header_template_trucking_id, 'tdc_header_template_id', $template_header_template_trucking_id );

// pages metas
td_demo_content::update_meta( $page_contact_id, 'tdc_header_template_id', $template_header_template_trucking_id );

td_demo_content::update_meta( $page_about_id, 'tdc_header_template_id', $template_header_template_trucking_id );

td_demo_content::update_meta( $page_home_id, 'tdc_header_template_id', $template_header_template_trucking_id );

td_demo_content::update_meta( $page_home_id, 'tdc_footer_template_id', $template_footer_trucking_id );
