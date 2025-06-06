<?php
abstract class td_module {
    var $post;

    var $title_attribute;
    var $title;             // by default the WordPress title is not escaped on twenty fifteen
    var $href;


    private $module_atts = array();

    /**
     * @var string the source of reviews for this $post
     */
    protected $review_source = 'author';

    /**
     * @var mixed the review metadata - we get it for each $post
     */
    protected $td_review;

    /**
     * @var float the user reviews overall rating
     */
    protected $user_reviews_overall = 0;

	/**
	 * @var bool is true if we have a review for this $post
	 */
	protected $is_review = false;

    /**
     * @var int|null Contains the id of the current $post thumbnail. If no thumbnail is found, the value is NULL
     */
    protected $post_thumb_id = NULL;


    /**
     * @param $post WP_Post
     * @param array $module_atts
     */
    function __construct($post, $module_atts = array()) {

        $this->module_atts = $module_atts;

        if (gettype($post) != 'object' or get_class($post) != 'WP_Post') {
            td_util::error(__FILE__, 'td_module: ' . get_Class($this) . '($post): $post is not WP_Post');
        }


        // this filter is used by td_unique_posts.php - to add unique posts to the array for the datasource
        apply_filters("td_wp_booster_module_constructor", $this, $post);

        $this->post = $post;

        // by default the WordPress title is not escaped on twenty fifteen
        $this->title = get_the_title($post->ID);
        $this->title_attribute = esc_attr(strip_tags($this->title));
        $this->href = esc_url(get_permalink($post->ID));

        if (has_post_thumbnail($this->post->ID)) {
            $tmp_get_post_thumbnail_id = get_post_thumbnail_id($this->post->ID);
            if (!empty($tmp_get_post_thumbnail_id)) {
                // if we have a wrong id, leave the post_thumb_id NULL
                $this->post_thumb_id = $tmp_get_post_thumbnail_id;
            }
        }

        // set the reviews source
        if( isset( $this->module_atts['review_source'] ) && !empty( $this->module_atts['review_source'] ) ) {
            $this->review_source = $this->module_atts['review_source'];
        }

        //get the review metadata
        //$this->td_review = get_post_meta($this->post->ID, 'td_review', true); !!!! $this->td_review variable name must be replaced and the 'get_quotes_on_blocks', 'get_category' methods also
	    $this->td_review = td_util::get_post_meta_array($this->post->ID, 'td_post_theme_settings');

        switch( $this->review_source ) {
            case 'author':
                if(
                    !empty($this->td_review['has_review']) &&
                    (
                        !empty($this->td_review['p_review_stars']) ||
                        !empty($this->td_review['p_review_percents']) ||
                        !empty($this->td_review['p_review_points'])
                    )
                ) {
                    $this->is_review = true;
                }
                break;

            case 'user_reviews':
                if( $this->post->post_type == 'tdc-review' ) {
                    $review_overall_rating = td_util::get_overall_review_rating( $this->post->ID );
                } else {
                    $review_overall_rating = td_util::get_overall_post_rating( $this->post->ID );
                }

                if( $review_overall_rating ) {
                    $this->is_review = true;
                    $this->user_reviews_overall = $review_overall_rating;
                }
                break;
        }

    }


    /**
     * @deprecated - google changed the structured data requirements and we no longer use them on modules
     */
    function get_item_scope() {
        return '';
    }


    /**
     * @deprecated - google changed the structured data requirements and we no longer use them on modules
     */
    function get_item_scope_meta() {
        return '';
    }


    function get_module_classes($additional_classes_array = '') {
        //add the wrap and module id class
        $buffy = get_class($this);


	    // each module setting has a 'class' key to customize css
	    $module_class = td_api_module::get_key(get_class($this), 'class');

	    if ($module_class != '') {
		    $buffy .= ' ' . $module_class;
	    }


        //show no thumb only if no thumb is detected and image placeholders are disabled
        if (is_null($this->post_thumb_id) and td_util::get_option('tds_hide_featured_image_placeholder') == 'hide_placeholder') {
            $buffy .= ' td_module_no_thumb';
        }

        // fix the meta info space when all options are off
        if (td_util::get_option('tds_m_show_author_name') == 'hide' and td_util::get_option('tds_m_show_date') == 'hide' and td_util::get_option('tds_m_show_comments') == 'hide') {
            $buffy .= ' td-meta-info-hide';
        }

	    if ($additional_classes_array != '' && is_array($additional_classes_array)) {
		    $buffy .= ' ' . implode(' ', $additional_classes_array);
	    }

	    // the following case could not be checked
	    // $buffy = implode(' ', array_unique(explode(' ', $buffy)));

        return $buffy;
    }

	function get_author_photo() {
		$buffy = '';

		$buffy .= '<a href="' . get_author_posts_url($this->post->post_author) . '" aria-label="author-photo" class="td-author-photo">' . get_avatar( $this->post->post_author, '96','', get_the_author_meta('display_name', $this->post->post_author) ) . '</a>';

		return $buffy;
	}

    function get_author($show_when_review = false) {
        $buffy = '';

        if ( $show_when_review or ($this->is_review === false or td_util::get_option('tds_m_show_review') == 'hide') ) {
            if ( td_util::get_option('tds_m_show_author_name') != 'hide' && get_the_author_meta('display_name', $this->post->post_author) !== '' ) {
                $buffy .= '<span class="td-post-author-name">';
                $buffy .= '<a href="' . get_author_posts_url($this->post->post_author) . '">' . get_the_author_meta('display_name', $this->post->post_author) . '</a>' ;
                if (td_util::get_option('tds_m_show_author_name') != 'hide' and td_util::get_option('tds_m_show_date') != 'hide') {
                    $buffy .= ' <span>-</span> ';
                }
                $buffy .= '</span>';
            }
        }
        return $buffy;
    }

    function show_author() {
    	echo '<!-- author -->' . $this->get_author();
    }


    function get_date($modified_date = '', $show_when_review = false, $time_ago = '', $time_ago_add_txt = '', $time_ago_txt_pos = '') {
        $visibility_class = '';
        if (td_util::get_option('tds_m_show_date') == 'hide') {
            $visibility_class = ' td-visibility-hidden';
        }

        $buffy = '';
        if (!$show_when_review and ( $this->is_review and td_util::get_option('tds_m_show_review') != 'hide' )) {
            //if review show stars
            $buffy .= '<span class="entry-review-stars">';
                if( $this->review_source == 'user_reviews' && !empty( $this->user_reviews_overall ) ) {
                    $buffy .= td_review::number_to_stars( $this->user_reviews_overall );
                } else {
                    $buffy .=  td_review::render_stars( $this->td_review );
                }
            $buffy .= '</span>';

        } else {
            if (td_util::get_option('tds_m_show_date') != 'hide') {

                global $wp_version;
                //old WP support
                if (version_compare($wp_version, '5.3', '<')) {
                    $td_article_date = date(DATE_W3C, get_the_time('U', $this->post->ID));
                    $td_article_modified_date = date(DATE_W3C, get_the_modified_date('U', $this->post->ID));
                } else {
                    // get_post_datetime() used from WP 5.3
                    $td_article_date = get_post_datetime($this->post->ID, 'date', 'gmt');
                    if ( $td_article_date !== false  ) {
                        $td_article_date = $td_article_date->format(DATE_W3C);
                    }
                    $td_article_modified_date = get_post_datetime($this->post->ID, 'modified', 'gmt');
                    if ( $td_article_modified_date !== false  ) {
                        $td_article_modified_date = $td_article_modified_date->format(DATE_W3C);
                    }
                }

                $buffy .= '<span class="td-post-date">';

                if ($modified_date == 'yes' || td_util::get_option('tds_m_show_modified_date') == 'yes') {
                    $display_modified_date = get_the_modified_date(get_option('date_format'), $this->post->ID);

                    if( $time_ago != '' ) {
                        $current_time = current_time( 'timestamp' );
                        $post_time_u  = get_the_modified_date('U', $this->post->ID );
                        $diff = (int) abs( $current_time - $post_time_u );

                        if ( $diff < WEEK_IN_SECONDS ) {
                            $display_modified_date = human_time_diff( $post_time_u, $current_time );
                            if( $time_ago_add_txt != '' ) {
                                if ( $time_ago_txt_pos == 'yes' ) {
                                    $display_modified_date = $time_ago_add_txt . ' ' . $display_modified_date;

                                } else {
                                    $display_modified_date .= ' ' . $time_ago_add_txt;
                                }
                            }
                        }
                    }

                    $buffy .= '<time class="entry-date updated td-module-date' . $visibility_class . '" datetime="' . $td_article_modified_date . '" >' . $display_modified_date . '</time>';
                }
                else {
                    $display_date = get_the_time(get_option('date_format'), $this->post->ID);

                    if( $time_ago != '' ) {
                        $current_time = current_time( 'timestamp' );
                        $post_time_u  = get_the_time('U', $this->post->ID );
                        $diff = (int) abs( $current_time - $post_time_u );

                        if ( $diff < WEEK_IN_SECONDS ) {
                            $display_date = human_time_diff( $post_time_u, $current_time );
                            if ( $time_ago_add_txt != '' ) {
                                if ( $time_ago_txt_pos == 'yes' ) {
                                    $display_date = $time_ago_add_txt . ' ' . $display_date;
                                } else {
                                    $display_date .= ' ' . $time_ago_add_txt;
                                }
                            }
                        }
                    }

                    $buffy .= '<time class="entry-date updated td-module-date' . $visibility_class . '" datetime="' . $td_article_date . '" >' . $display_date . '</time>';
                }

                $buffy .= '</span>';
            }
        }

        return $buffy;
    }


    function show_date($modified_date = '') {
    	echo '<!-- date -->' . $this->get_date( $modified_date );
    }

    function get_review() {
        $buffy = '';

        if( $this->is_review and td_util::get_option('tds_m_show_review') != 'hide' ) {
            $buffy .= '<span class="entry-review-stars">';
                if( $this->review_source == 'user_reviews' && !empty( $this->user_reviews_overall ) ) {
                    $buffy .= td_review::number_to_stars( $this->user_reviews_overall );
                } else {
                    $buffy .=  td_review::render_stars( $this->td_review );
                }
            $buffy .= '</span>';
        }

        return $buffy;
    }


    function get_comments() {
        $buffy = '';
        if ( td_util::get_option('tds_m_show_comments') != 'hide' ) {

	        $comments_number_dsq = td_util::get_dsq_comments_number( $this->post );
			$comments_number = $comments_number_dsq ?: get_comments_number( $this->post->ID );

            $buffy .= '<span class="td-module-comments">';
                $buffy .= '<a href="' . get_comments_link( $this->post->ID ) . '">';
                    $buffy .= $comments_number;
                $buffy .= '</a>';
            $buffy .= '</span>';
        }

        return $buffy;
    }


    function show_comments() {
    	echo '<!-- comments -->' . $this->get_comments();
    }



    /**
     * get image - v 3.0  23 ian 2015
     *  - v 4.0 - 12 oct 2018 - added support for lazy loading animation images
     * @param $thumbType
     * @param $css_image
     * @return string
     */
    function get_image( $thumbType, $css_image = false ) {
        $buffy = ''; //the output buffer
        $tds_hide_featured_image_placeholder = td_util::get_option('tds_hide_featured_image_placeholder');
        //retina image
        $srcset_sizes = '';
        $td_use_webp = '';

        // do we have a post thumb or a placeholder?
        if ( !is_null( $this->post_thumb_id ) or ( $tds_hide_featured_image_placeholder != 'hide_placeholder' ) ) {

            if ( !is_null( $this->post_thumb_id ) ) {
                //if we have a thumb
                // check to see if the thumb size is enabled in the panel, we don't have to check for the default wordpress
                // thumbs (the default ones are already cut and we don't have  a panel setting for them)
                if ( td_util::get_option('tds_thumb_' . $thumbType ) != 'yes' and $thumbType != 'thumbnail' and $thumbType != 'medium_large' ) {
                    //the thumb is disabled, show a placeholder thumb from the theme with the "thumb disabled" message
                    global $_wp_additional_image_sizes;

                    if (empty($_wp_additional_image_sizes[$thumbType]['width'])) {
                        $td_temp_image_url[1] = '';
                    } else {
                        $td_temp_image_url[1] = $_wp_additional_image_sizes[$thumbType]['width'];
                    }

                    if (empty($_wp_additional_image_sizes[$thumbType]['height'])) {
                        $td_temp_image_url[2] = '';
                    } else {
                        $td_temp_image_url[2] = $_wp_additional_image_sizes[$thumbType]['height'];
                    }

					// For custom wordpress sizes (not 'thumbnail', 'medium', 'medium_large' or 'large'), get the image path using the api (no_image_path)
	                $thumb_disabled_path = td_global::$get_template_directory_uri;
	                if ( strpos( $thumbType, 'td_' ) === 0 ) {
			            $thumb_disabled_path = td_api_thumb::get_key( $thumbType, 'no_image_path' );
		            }
			        $td_temp_image_url[0] = $thumb_disabled_path . '/images/thumb-disabled/' . $thumbType . '.png';

                    $attachment_alt = 'alt=""';
                    $attachment_title = '';

                } else {
                    // the thumb is enabled from the panel, it's time to show the real thumb
                    $td_temp_image_url = wp_get_attachment_image_src($this->post_thumb_id, $thumbType);
                    $attachment_alt = get_post_meta($this->post_thumb_id, '_wp_attachment_image_alt', true );
                    $attachment_alt = ' alt="' . esc_attr(strip_tags($attachment_alt)) . '"';
                    $attachment_title = ' title="' . esc_attr(strip_tags($this->title)) . '"';

                    if (empty($td_temp_image_url[0])) {
                        $td_temp_image_url[0] = '';
                    }

                    if (empty($td_temp_image_url[1])) {
                        $td_temp_image_url[1] = '';
                    }

                    if (empty($td_temp_image_url[2])) {
                        $td_temp_image_url[2] = '';
                    }

                    //retina image
                    //don't display srcset_sizes on DEMO - it messes up Pagespeed score (8 March 2017)
                    if (TD_DEPLOY_MODE != 'demo') {
                        $srcset_sizes = td_util::get_srcset_sizes($this->post_thumb_id, $thumbType, $td_temp_image_url[1], $td_temp_image_url[0]);
                    }

                } // end panel thumb enabled check



            } else {
                //we have no thumb but the placeholder one is activated
                global $_wp_additional_image_sizes;

                if (empty($_wp_additional_image_sizes[$thumbType]['width'])) {
                    $td_temp_image_url[1] = '';
                } else {
                    $td_temp_image_url[1] = $_wp_additional_image_sizes[$thumbType]['width'];
                }

                if (empty($_wp_additional_image_sizes[$thumbType]['height'])) {
                    $td_temp_image_url[2] = '';
                } else {
                    $td_temp_image_url[2] = $_wp_additional_image_sizes[$thumbType]['height'];
                }

                /**
                 * get thumb height and width via api
                 * first we check the global in case a custom thumb is used
                 *
                 * The api thumb is checked only for additional sizes registered and if at least one of the settings (width or height) is empty.
                 * This should be enough to avoid getting a non existing id using api thumb.
                 */
	            if ( !empty( $_wp_additional_image_sizes ) && array_key_exists( $thumbType, $_wp_additional_image_sizes ) && ( $td_temp_image_url[1] == '' || $td_temp_image_url[2] == '' ) ) {
                    $td_thumb_parameters = td_api_thumb::get_by_id($thumbType);
	                $td_temp_image_url[1] = $td_thumb_parameters['width'];
                    $td_temp_image_url[2] = $td_thumb_parameters['height'];
                }

                $custom_placeholder_exists = false;
                $custom_placeholder = td_util::get_option('tds_thumb_placeholder');
                if ( $custom_placeholder != '' && !empty($_wp_additional_image_sizes) && array_key_exists($thumbType, $_wp_additional_image_sizes) ) {
                    global $wpdb;
                    $placeholder_id = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='%s';", $custom_placeholder ));

                    if( !empty( $placeholder_id ) ) {
                        $custom_placeholder_info = wp_get_attachment_image_src($placeholder_id[0], $thumbType);

                        if( $custom_placeholder_info != false ) {
                            $custom_placeholder_headers = @get_headers($custom_placeholder);

                            if( $custom_placeholder_headers && $custom_placeholder_headers[0] != 'HTTP/1.1 404 Not Found' ) {
                                $td_temp_image_url[0] = $custom_placeholder;
                                $custom_placeholder_exists = true;
                            }
                        }
                    }
                } else if( $custom_placeholder != '' ) {
                    $custom_placeholder_headers = @get_headers($custom_placeholder);

                    if( $custom_placeholder_headers && $custom_placeholder_headers[0] != 'HTTP/1.1 404 Not Found' ) {
                        $td_temp_image_url[0] = $custom_placeholder;
                        $custom_placeholder_exists = true;
                    }
                }

                if( !$custom_placeholder_exists ) {
	            	// For custom wordpress sizes (not 'thumbnail', 'medium', 'medium_large' or 'large'), get the image path using the api (no_image_path)
		            if (strpos($thumbType, 'td_') === 0) {
                        $no_thumb_path = rtrim(td_api_thumb::get_key($thumbType, 'no_image_path'), '/');
                        $td_temp_image_url[0] = $no_thumb_path . '/images/no-thumb/' . $thumbType . '.png';
                    } else {
                        $no_thumb_path = TDC_URL_LEGACY;
                        $td_temp_image_url[0] = $no_thumb_path . '/assets/images/no-thumb/' . $thumbType . '.png';
                    }
                }


                $attachment_alt = 'alt=""';
                $attachment_title = '';
            } //end    if ($this->post_has_thumb) {


            $buffy .= '<div class="td-module-thumb">';
                $post_type = get_post_format($this->post->ID);

                if ( current_user_can('edit_published_posts') ) {
                    $buffy .= '<a class="td-admin-edit" href="' . get_edit_post_link($this->post->ID) . '" title="edit post">edit</a>';
                }

                $video_popup_param_no = '';
                if ( get_class($this) == 'td_module_flex_3' ) {
                    $video_popup_param_no = '2';
                }
                if ( get_class($this) == 'td_module_flex_4' ) {
                    $video_popup_param_no = '3';
                }

                $td_show_video_modal = false;
                $td_global_video_modal = td_util::get_option('tds_m_show_modal_video');
                //general video modal used on Newsmag
                if ( TD_THEME_NAME == 'Newsmag' && $td_global_video_modal == '' ){
                        $td_show_video_modal = true;

                } else { //when it is set on shortcode
                    if ( isset($this->module_atts['video_popup' . $video_popup_param_no]) && $this->module_atts['video_popup' . $video_popup_param_no] != '' ) {
                        $td_show_video_modal = true;
                    }
                }

                $video_popup_class = '';
                $video_popup_data = '';
                if ( $post_type == 'video' && $td_show_video_modal ) {

                    $video_url = get_post_meta($this->post->ID, 'td_post_video');

                    if( isset($video_url[0]['td_video']) && $video_url[0]['td_video'] != '' ) {
                        $video_source = td_video_support::detect_video_service($video_url[0]['td_video']);

                        $autoplay_vid = '';
                        if( isset($this->module_atts['autoplay_vid' . $video_popup_param_no]) ) {
                            $autoplay_vid = $this->module_atts['autoplay_vid' . $video_popup_param_no];
                        }

                        $video_popup_class = 'td-module-video-modal';
                        $video_popup_data = 'data-video-source="' . $video_source . '" data-video-autoplay="' . $autoplay_vid . '" data-video-url="'. esc_url( $video_url[0]['td_video'] ) . '"';

                        $video_rec = '';
                        if( isset($this->module_atts['video_rec' . $video_popup_param_no]) ) {
                            $video_rec = rawurldecode( base64_decode( strip_tags( $this->module_atts['video_rec' . $video_popup_param_no] ) ) );
                        }
                        $video_rec_title = '';
                        if( isset($this->module_atts['video_rec_title' . $video_popup_param_no]) ) {
                            $video_rec_title = $this->module_atts['video_rec_title' . $video_popup_param_no];
                        }
                        $video_rec_disable = false;
                        if( isset($this->module_atts['video_rec_disable' . $video_popup_param_no]) && ( current_user_can('administrator') || current_user_can('editor') ) ) {
                            if( $this->module_atts['video_rec_disable' . $video_popup_param_no] != '' ) {
                                $video_rec_disable = true;
                            }
                        }

                        $video_popup_ad = array(
                            'code' => do_shortcode( stripslashes( $video_rec ) ),
                            'title' => $video_rec_title,
                            'disable' => $video_rec_disable,
                        );

                        if( $video_popup_ad['code'] == '' ) {
                            $video_popup_ad['code'] = stripslashes( td_options::get( 'tds_modal_video_ad') );
                        }
                        if( $video_popup_ad['title'] == '' ) {
                            $video_popup_ad['title'] = td_options::get( 'tds_modal_video_ad_title');
                        }
                        if( !$video_popup_ad['disable'] && ( current_user_can('administrator') || current_user_can('editor') ) ) {
                            if( td_options::get( 'tds_modal_video_ad_disable') != '' ) {
                                $video_popup_ad['disable'] = true;
                            }
                        }

                        if( $video_popup_ad['code'] != '' ) {
                            $video_popup_data .= 'data-video-rec="' . base64_encode( json_encode($video_popup_ad) ) . '"';
                        }
                    }

                    if( TD_THEME_NAME == "Newspaper" ) {
                        // load js
                        td_resources_load::render_script( TDC_SCRIPTS_URL . '/tdAjaxVideoModal.js' . TDC_SCRIPTS_VER, 'tdAjaxVideoModal-js', '', 'footer');
                    }
                }

                $wp_directory = trailingslashit(ABSPATH);
                $wp_dirname = basename(rtrim($wp_directory, '/'));
                $webp_img_path = parse_url($td_temp_image_url[0], PHP_URL_PATH) . '.webp';
                $webp_img_path = str_replace("/$wp_dirname/", '/', $webp_img_path);
                $webp_img_path = rtrim(ABSPATH, '/') . '/' . ltrim($webp_img_path, '/');

                if (file_exists($webp_img_path)) {
                    $td_use_webp = ( td_util::browser_supports_webp() && td_util::get_option('tds_load_webp') === 'yes' ) ? '.webp' : $td_use_webp;
                }

                $target_blank = '';
                if ( isset($this->module_atts['open_in_new_window']) && $this->module_atts['open_in_new_window'] === 'yes' ) {
                    $target_blank = 'target="_blank"';
                }

                $nofollow = '';
                if ( td_util::get_option('tds_m_nofollow_image') == 'yes') {
                    $nofollow = 'nofollow ';
                }

                $buffy .= '<a href="' . $this->href . '" ' . $target_blank . ' rel="' . $nofollow . 'bookmark" class="td-image-wrap ' . $video_popup_class . '" title="' . $this->title_attribute . '" ' . $video_popup_data . '>';

                    $tds_animation_stack = td_util::get_option('tds_animation_stack');
                    $tds_ajax_preloading = '';
                    if(isset($this->module_atts['td_ajax_preloading'])) {
                        $tds_ajax_preloading = $this->module_atts['td_ajax_preloading'];
                    }
                    // if we have the lazy loading animation on, we're not on an ajax call or on composer
                    if ( empty( $tds_animation_stack ) && empty($tds_ajax_preloading) && !wp_doing_ajax() && ! td_util::tdc_is_live_editor_ajax() && ! td_util::tdc_is_live_editor_iframe() && !td_util::is_mobile_theme() && !td_util::is_amp() ) {

                        // retina image
                        $retina_image = '';

                        // here we treat the normal img_tag retina ver
                        if ( td_util::get_option('tds_thumb_' . $thumbType . '_retina') == 'yes' && !empty( $td_temp_image_url[1] ) ) {
                            $retina_url = wp_get_attachment_image_src( $this->post_thumb_id, $thumbType . '_retina' );
                            if ( !empty( $retina_url[0] ) ) {
                                $retina_image = 'data-img-retina-url="' . $retina_url[0] . $td_use_webp . '"';
                            }
                        }

                        // css image
                        if ( $css_image === true ) {

                            // the css_image type
                            $buffy .= '<span class="entry-thumb td-thumb-css" data-type="css_image" data-img-url="' . $td_temp_image_url[0] . $td_use_webp . '" ' . $retina_image . ' ></span>';

                        // normal image
                        } else {

                            $base64 = '';
                            if ( strpos( $thumbType, 'td_' ) === 0 ) {
                                $thumbs = td_api_thumb::get_all();
                                foreach ( $thumbs as $thumb_id => $thumb_data ) {
                                    if ( $thumb_id === $thumbType ) {
                                        if ( isset($thumb_data['b64_encoded'] ) ) {
                                            $base64 = td_api_thumb::get_key( $thumbType, 'b64_encoded' );
                                        }
                                    }
                                }
                            }

                            $src = 'src="' . $base64 . '"';

                            // the normal image_tag type
                            $buffy .= '<img class="entry-thumb" ' . $src . $attachment_alt . $attachment_title . ' data-type="image_tag" data-img-url="' . $td_temp_image_url[0] . $td_use_webp . '" ' . $retina_image . ' width="' . $td_temp_image_url[1] . '" height="' . $td_temp_image_url[2] . '" />';
                        }

                    } else {
                        // css image
                        if ( $css_image === true ) {

                            // retina image
                            $retina_uuid = '';

                            if ( td_util::get_option('tds_thumb_' . $thumbType . '_retina') == 'yes' && !empty( $td_temp_image_url[1] ) ) {
                                $retina_uuid = td_global::td_generate_unique_id();
                                $retina_url = wp_get_attachment_image_src( $this->post_thumb_id, $thumbType . '_retina' );
                                if ( !empty( $retina_url[0] ) ) {
                                    $buffy .= '
                                        <style>
                                            /* custom css - generated by TagDiv Composer */
                                              @media  only screen and (min-device-pixel-ratio: 1.5),
                                              only screen and (min-resolution: 192dpi) {
                                                  .td-thumb-css.' . $retina_uuid . ' {
                                                      background-image: url("' . $retina_url[0] . $td_use_webp . '") !important;
                                                  }
                                              }
                                        </style>
                                    ';
                                }
                            }

                            $buffy .= '<span class="entry-thumb td-thumb-css ' . $retina_uuid . '" style="background-image: url(\'' . $td_temp_image_url[0] . $td_use_webp . '\')" ></span>';

                        // normal image
                        } else {
                            $buffy .= '<img width="' . $td_temp_image_url[1] . '" height="' . $td_temp_image_url[2] . '" class="entry-thumb" src="' . $td_temp_image_url[0] . $td_use_webp . '" ' . $srcset_sizes . ' ' . $attachment_alt . $attachment_title . ' />';
                        }
                    }


                    // on video or audio type posts add the specific icon
                    if ($post_type == 'video' || $post_type == 'audio') {

                        $use_small_post_format_icon_size = false;
                        // search in all the thumbs for the one that we are currently using here and see if it has post_format_icon_size = small
                        foreach (td_api_thumb::get_all() as $thumb_from_thumb_list) {
                            if ($thumb_from_thumb_list['name'] == $thumbType and $thumb_from_thumb_list['post_format_icon_size'] == 'small') {
                                $use_small_post_format_icon_size = true;
                                break;
                            }
                        }

                        // load the small or medium play icon
                        if ($use_small_post_format_icon_size === true) {
                            $buffy .= '<span class="td-video-play-ico td-video-small"><i class="td-icon-' . $post_type . '-thumb-play"></i></span>';
                        } else {
                            $buffy .= '<span class="td-video-play-ico"><i class="td-icon-' . $post_type . '-thumb-play"></i></span>';
                        }
                    } // end on video if

                $buffy .= '</a>';
            $buffy .= '</div>'; //end wrapper

            return $buffy;
        }

        return $buffy;
    }



     /**
     * Display image
     * @param $thumbType
     * @param $css_image
     * @return string
     */
    function show_image($thumbType, $css_image = false) {
    	echo '<!-- image -->' . $this->get_image($thumbType, $css_image);
    }



    /**
     * This function returns the title with the appropriate markup.
     * @param string $cut_at - if provided, the method will just cut at that point
     * @param string $title_tag - if provided, will change the default h3 tag on article title
     * and it will cut after that. If not setting is in the database the function will cut at the default value
     * @return string
     */

    function get_title($cut_at = '', $title_tag = '' ) {

        $module_title_tag = 'h3';
        if ( $title_tag != '' ) {
            $module_title_tag = $title_tag;
        }

        $target_blank = '';
        if( isset($this->module_atts['open_in_new_window']) && $this->module_atts['open_in_new_window'] === 'yes' ) {
            $target_blank = 'target="_blank"';
        }

        $buffy = '';
        $buffy .= '<' . $module_title_tag . ' class="entry-title td-module-title">';
        $buffy .='<a href="' . $this->href . '" ' . $target_blank . ' rel="bookmark" title="' . $this->title_attribute . '">';

        //see if we have to cut the title and if we have the title lenght in the panel for ex: td_module_6__title_excerpt
        if ($cut_at != '') {
            //cut at the hard coded size
            $buffy .= td_util::excerpt($this->title, $cut_at, 'show_shortcodes');

        } else {
            $current_module_class = get_class($this);

            //see if we have a default setting for this module, and if so only apply it if we don't get other things form theme panel.
            if (td_api_module::_helper_check_excerpt_title($current_module_class)) {
                $db_title_excerpt = td_util::get_option($current_module_class . '_title_excerpt');
                if ($db_title_excerpt != '') {
                    //cut from the database settings
                    $buffy .= td_util::excerpt($this->title, $db_title_excerpt, 'show_shortcodes');
                } else {
                    //cut at the default size
                    $module_api = td_api_module::get_by_id($current_module_class);
                    $buffy .= td_util::excerpt($this->title, $module_api['excerpt_title'], 'show_shortcodes');
                }
            } else {
                /**
                 * no $cut_at provided and no setting in td_config -> return the full title
                 * @see td_global::$modules_list
                 */
                $buffy .= $this->title;
            }

        }
        $buffy .='</a>';
        $buffy .= '</' . $module_title_tag . '>';
        return $buffy;
    }



    /**
     * Show the title with the appropriate markup.
     * @param string $cut_at - if provided, the method will just cut at that point
     * and it will cut after that. If not setting is in the database the function will cut at the default value
     * @return string
     */

    function show_title($cut_at = '') {
    	echo '' . $this->get_title( $cut_at );
    }


    /**
     * This method is used by modules to get content that has to be excerpted (cut)
     * IT RETURNS THE EXCERPT FROM THE POST IF IT'S ENTERED IN THE EXCERPT CUSTOM POST FIELD BY THE USER
     * @param string $cut_at - if provided the method will just cat at that point
     * @return string
     */
    function get_excerpt($cut_at = '') {

        //If the user supplied the excerpt in the post excerpt custom field, we just return that
        if ($this->post->post_excerpt != '') {
            return $this->post->post_excerpt;
        }

        $buffy = '';
        if ($cut_at != '') {
            // simple, $cut_at and return
            $buffy .= td_util::excerpt($this->post->post_content, $cut_at);
        } else {
            $current_module_class = get_class($this);

            //see if we have a default setting for this module, and if so only apply it if we don't get other things form theme panel.
            if (td_api_module::_helper_check_excerpt_content($current_module_class)) {
                $db_content_excerpt = td_util::get_option($current_module_class . '_content_excerpt');
                if ($db_content_excerpt != '') {
                    //cut from the database settings
                    $buffy .= td_util::excerpt($this->post->post_content, $db_content_excerpt);
                } else {
                    //cut at the default size
                    $module_api = td_api_module::get_by_id($current_module_class);
                    $buffy .= td_util::excerpt($this->post->post_content, $module_api['excerpt_content']);
                }
            } else {
                /**
                 * no $cut_at provided and no setting in td_config -> return the full $this->post->post_content
                 * @see td_global::$modules_list
                 */
                $buffy .= $this->post->post_content;
            }
        }
        return $buffy;
    }


    function show_excerpt($cut_at = '') {
    	echo '<!-- excerpt -->' . $this->get_excerpt( $cut_at );
    }


    /**
     * This method is used by modules to get the audio embed based on
     * the featured audio field
     * @return mixed|string
     */
    function get_audio_embed() {

        $buffy = '';

        if( get_post_format( $this->post->ID ) == 'audio' ) {
            $td_post_audio = td_util::get_post_meta_array($this->post->ID, 'td_post_audio');

            if( isset( $td_post_audio['td_audio'] ) && $td_post_audio['td_audio'] != '' ) {
                $buffy .= td_audio_support::render_audio($td_post_audio['td_audio']);
            }
        }

        return $buffy;

    }


    /**
     * This method is used by modules to get the featured video duration
     * @return string
     */
    function get_video_duration() {

        $buffy = '';

        if( get_post_format( $this->post->ID ) == 'video' ) {
            $video_url = get_post_meta($this->post->ID, 'td_post_video');

            if( isset($video_url[0]['td_video']) && $video_url[0]['td_video'] != '' ) {
                if ( metadata_exists('post', $this->post->ID, 'td_post_video_duration') && get_post_meta( $this->post->ID, 'td_post_video_duration', true ) != '' ) {
                    $video_duration = get_post_meta( $this->post->ID, 'td_post_video_duration', true );
                } else {
                    $video_duration = td_video_support::get_video_duration($video_url[0]['td_video']);

                    if ( $video_duration != '' ) {
                        update_post_meta($this->post->ID, 'td_post_video_duration', $video_duration);
                    }
                }

                if( $video_duration != '' ) {
                    $buffy .= '<div class="td-post-vid-time">' . $video_duration . '</div>';
                }
            }
        }

        return $buffy;

    }

    function get_favorite_badge() {

        /* -- Bail if Cloud Library is not active. -- */
        if( !defined( 'TD_CLOUD_LIBRARY' ) ) {
            return '';
        }


        /* -- Load the necessary tdbFavourites.js script -- */
        /* -- and return the add to favourites button. -- */
        td_resources_load::render_script( TDB_SCRIPTS_URL . '/tdbFavourites.js' . TDB_SCRIPTS_VER, 'tdbFavourites-js', '', 'footer' );

        return
            '<span class="td-favorite tdb-favorite ' . (td_util::is_article_favourite($this->post->ID) ? 'tdb-favorite-selected' : '') . '" data-post-id="' . $this->post->ID . '">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 511.96 511.96" class="td-favorite-ico td-favorite-ico-empty"><path id="Path_1" data-name="Path 1" d="M0,48A48.012,48.012,0,0,1,48,0V441.4l130.1-92.9a23.872,23.872,0,0,1,27.9,0l130,92.9V48H48V0H336a48.012,48.012,0,0,1,48,48V488a23.974,23.974,0,0,1-37.9,19.5L192,397.5,37.9,507.5A23.974,23.974,0,0,1,0,488Z" transform="translate(63.98)"/></svg>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 511.96 512.96" class="td-favorite-ico td-favorite-ico-full"><path id="Path_2" data-name="Path 2" d="M0,48V487.7a24.341,24.341,0,0,0,38.3,19.9L192,400,345.7,507.6A24.341,24.341,0,0,0,384,487.7V48A48.012,48.012,0,0,0,336,0H48A48.012,48.012,0,0,0,0,48Z" transform="translate(63.98)"/></svg>
            </span>';

    }



    function get_category() {

        $buffy = '';
	    $selected_category_obj = '';
	    $selected_category_obj_id = '';
	    $selected_category_obj_name = '';

	    $current_post_type = get_post_type($this->post->ID);
	    $builtin_post_types = get_post_types(array('_builtin' => true));

	    if (array_key_exists($current_post_type, $builtin_post_types)) {

		    // default post type

		    //read the post meta to get the custom primary category
		    $td_post_theme_settings = td_util::get_post_meta_array($this->post->ID, 'td_post_theme_settings');
		    if (!empty($td_post_theme_settings['td_primary_cat'])) {
			    //we have a custom category selected
			    $selected_category_obj = get_category($td_post_theme_settings['td_primary_cat']);
		    } else {

			    //get one auto
			    $categories = get_the_category($this->post->ID);

			    if (is_category()) {
				    foreach ($categories as $category) {
					    if ($category->term_id == get_query_var('cat')) {
						    $selected_category_obj = $category;
						    break;
					    }
				    }
			    }

			    if (empty($selected_category_obj) and !empty($categories[0])) {
				    if ($categories[0]->name === TD_FEATURED_CAT and !empty($categories[1])) {
					    $selected_category_obj = $categories[1];
				    } else {
					    $selected_category_obj = $categories[0];
				    }
			    }
		    }

		    if (!empty($selected_category_obj)) {
			    $selected_category_obj_id = $selected_category_obj->cat_ID;
			    $selected_category_obj_name = $selected_category_obj->name;
		    }

	    } else {

		    // custom post type
            $td_post_theme_settings = td_util::get_post_meta_array($this->post->ID, 'td_post_theme_settings');
            // get primary taxonomy term if is set
            if (!empty($td_post_theme_settings['td_primary_cat'])) {
                //we have a custom category selected
                $selected_tax_id = $td_post_theme_settings['td_primary_cat'];
                $selected_category_obj = get_term($selected_tax_id);
                $selected_category_obj_id = $selected_category_obj->term_id;
                $selected_category_obj_name = $selected_category_obj->name;
            } else {

                // Validate that the current queried term is a term
                global $wp_query;
                $current_queried_term = $wp_query->get_queried_object();

                if ($current_queried_term instanceof WP_Term) {
                    $current_term = term_exists($current_queried_term->name, $current_queried_term->taxonomy);

                    if ($current_term !== 0 && $current_term !== null) {
                        $selected_category_obj = $current_queried_term;
                    }
                } else { //get one auto

                    $td_taxonomy_terms = array();
                    $post_type = get_post_type($this->post);
                    $td_taxonomies = get_object_taxonomies($post_type);

                    foreach ( $td_taxonomies as $td_taxonomy ) {
                        if ( ! is_taxonomy_hierarchical( $td_taxonomy ) ) {
                            continue;
                        }

                        $terms = get_the_terms($this->post, $td_taxonomy);

                        // needs to be an array
                        if( is_array($terms) ) {

                            foreach ($terms as $term) {
                                $td_taxonomy_terms[] = $term;
                            }
                        }
                    }

                    if(current($td_taxonomy_terms) !== false) {
                        $selected_category_obj = current($td_taxonomy_terms);
                    }

                }

                // Get and validate the custom taxonomy according to the validated queried term
                if (!empty($selected_category_obj)) {

                    $taxonomy_objects = get_object_taxonomies($this->post, 'objects');
                    $custom_taxonomy_object = '';

                    foreach ($taxonomy_objects as $taxonomy_object) {

                        if ($taxonomy_object->_builtin !== 1 && $taxonomy_object->name === $selected_category_obj->taxonomy) {
                            $custom_taxonomy_object = $taxonomy_object;
                            break;
                        }
                    }

                    // Invalid taxonomy
                    if (empty($custom_taxonomy_object)) {
                        return $buffy;
                    }

                    $selected_category_obj_id = $selected_category_obj->term_id;
                    $selected_category_obj_name = $selected_category_obj->name;
                }
            }
	    }


        if (!empty($selected_category_obj_id) && !empty($selected_category_obj_name)) { //!!!! catch error here

            $td_cat_bg = '';
            $td_cat_color = '';
            $cat_text_color = '';

            if ( !empty($this->module_atts['cat_style']) || !empty($this->module_atts['tds_module_loop_1_style-cat_style'])  || !empty($this->module_atts['tds_module_loop_2_style-cat_style'])) {

                if ( !empty($this->module_atts['tds_module_loop_1_style-cat_style']) ) {
                    $cat_style = $this->module_atts['tds_module_loop_1_style-cat_style'];
                } elseif(!empty($this->module_atts['tds_module_loop_2_style-cat_style'])) {
                    $cat_style = $this->module_atts['tds_module_loop_2_style-cat_style'];
                } else {
                    $cat_style = $this->module_atts['cat_style'];
                }

                $category_meta__color = td_util::get_category_option($selected_category_obj_id, 'tdc_color');

                if (!empty($category_meta__color)) {
                    // set title color based on background color contrast
                    $td_cat_title_color = td_util::readable_colour($category_meta__color, 200, 'rgba(0, 0, 0, 0.9)', '#fff');
                    $td_cat_bg = ' background-color:' . $category_meta__color . '; border-color:' . $category_meta__color . ';';
                    if ($td_cat_title_color == '#fff') {
                        $td_cat_color = '';
                    } else {
                        $td_cat_color = ' color:' . $td_cat_title_color . ';';
                    }
                    if ($cat_style == 'tdb-cat-style2') {
                        $td_cat_bg = ' background-color:' . td_util::hex2rgba($category_meta__color, 0.85) . '; border-color:' . $category_meta__color . ';';
                    }
                    if ($cat_style == 'tdb-cat-style3') {
                        $td_cat_bg = ' background-color:' . td_util::hex2rgba($category_meta__color, 0.2) . '; border-color:' . td_util::hex2rgba($category_meta__color, 0.05) . ';';
                        $cat_text_color = ' color:' . $category_meta__color . ';';
                    }
                }
            }

            $style = !empty($td_cat_bg) || !empty($td_cat_color) || !empty($cat_text_color) ? ' style="' . $td_cat_bg . $td_cat_color . $cat_text_color . '"' : '';

            $buffy .= '<a href="' . get_category_link($selected_category_obj_id) . '" class="td-post-category" ' . $style . '>' . $selected_category_obj_name . '</a>' ;
        }

        //return print_r($post, true);
        return $buffy;
    }


    function show_category() {
        echo '<!-- category -->' . $this->get_category();
    }


    //get quotes on blocks
    function get_quotes_on_blocks() {

        // do not show the quote on WordPress loops
        if (td_global::$is_wordpress_loop === true or td_global::vc_get_column_number() != 1) {
            return '';
        }


        //get quotes data from database
        $post_data_from_db = td_util::get_post_meta_array($this->post->ID, 'td_post_theme_settings');

        if(!empty($post_data_from_db['td_quote_on_blocks'])) {
            return '<div class="td_quote_on_blocks">' . $post_data_from_db['td_quote_on_blocks'] . '</div>';
        }
    }


    function show_quotes_on_blocks() {
    	echo '<!-- quotes on blocks -->' . $this->get_quotes_on_blocks();
    }


    /**
     * Gets a shortcode att but only if the module received them
     * @param $att_name
     * @param $default_value
     * @param $style_class
     * @return mixed|string
     */
    function get_shortcode_att($att_name, $default_value = '', $style_class = '') {
        // returns '' if not set - for loops and other places where modules are not in blocks

        $att_prefix = '';
        if( $style_class != '' ) {
            $att_prefix = $style_class . '-';
        }

        if (empty($this->module_atts)) {
            return '';
        }
        if (!isset($this->module_atts[$att_prefix . $att_name])) {
            td_util::error(__FILE__, $att_prefix . $att_name . ' - Is not mapped in the shortcode that uses this module ( <strong>' . get_class($this) . '</strong>)', $this->module_atts);

            //die;
            return $default_value;
        }

        //we need to decode the square bracket case
        $attr_value = $this->module_atts[$att_prefix . $att_name];
        if (strpos($attr_value, 'td_encval') === 0) {
            $attr_value = str_replace('td_encval', '', $attr_value);
            $attr_value = base64_decode($attr_value);
        }

        return $attr_value;
    }

    function get_icon_att( $att_name ) {
        $icon_class = $this->get_shortcode_att($att_name);
        $svg_list = td_global::$svg_theme_font_list;

        if( array_key_exists( $icon_class, $svg_list ) ) {
            return $svg_list[$icon_class];
        }

        return $icon_class;
    }


    /**
     * Displays the user ratings stars
     * @param $full_star_icon
     * @param $half_star_icon
     * @param $empty_star_icon
     * @return string
     */
    function show_user_ratings_stars( $full_star_icon = '', $half_star_icon = '', $empty_star_icon = '', $show_empty_rating = false ) {

        $buffy = '';

        // Rating stars
        $full_star_icon_html = '<i class="td-icon-user-rev-star-full"></i>';
        $full_star_icon_data = '';
        if( $full_star_icon != '' ) {
            $full_star_icon_att = $this->get_icon_att( $full_star_icon );
            if( td_util::tdc_is_live_editor_iframe() || td_util::tdc_is_live_editor_ajax() ) {
                $full_star_icon_data = 'data-td-svg-icon="' . $this->get_shortcode_att( $full_star_icon ) . '"';
            }
            if ( !empty( $full_star_icon_att ) ) {
                if( base64_encode( base64_decode( $full_star_icon_att ) ) == $full_star_icon_att ) {
                    $full_star_icon_html = base64_decode( $full_star_icon_att ) ;
                } else {
                    $full_star_icon_html = '<i class="' . $full_star_icon_att . '"></i>';
                }
            }
        }

        $half_star_icon_html = '<i class="td-icon-user-rev-star-half"></i>';
        $half_star_icon_data = '';
        if( $half_star_icon != '' ) {
            $half_star_icon_att = $this->get_icon_att( $half_star_icon );
            if( td_util::tdc_is_live_editor_iframe() || td_util::tdc_is_live_editor_ajax() ) {
                $half_star_icon_data = 'data-td-svg-icon="' . $this->get_shortcode_att( $half_star_icon ) . '"';
            }
            if ( !empty( $half_star_icon_att ) ) {
                if( base64_encode( base64_decode( $half_star_icon_att ) ) == $half_star_icon_att ) {
                    $half_star_icon_html = base64_decode( $half_star_icon_att ) ;
                } else {
                    $half_star_icon_html = '<i class="' . $half_star_icon_att . '"></i>';
                }
            }
        }

        $empty_star_icon_html = '<i class="td-icon-user-rev-star-empty"></i>';
        $empty_star_icon_data = '';
        if( $empty_star_icon != '' ) {
            $empty_star_icon_att = $this->get_icon_att( $empty_star_icon );
            if( td_util::tdc_is_live_editor_iframe() || td_util::tdc_is_live_editor_ajax() ) {
                $empty_star_icon_data = 'data-td-svg-icon="' . $this->get_shortcode_att( $empty_star_icon ) . '"';
            }
            if ( !empty( $empty_star_icon_att ) ) {
                if( base64_encode( base64_decode( $empty_star_icon_att ) ) == $empty_star_icon_att ) {
                    $empty_star_icon_html = base64_decode( $empty_star_icon_att ) ;
                } else {
                    $empty_star_icon_html = '<i class="' . $empty_star_icon_att . '"></i>';
                }
            }
        }

        $overall_post_rating = td_util::get_overall_post_rating($this->post->ID);
        if( $overall_post_rating ) {
            // Display the ratings
            $buffy = td_util::display_user_ratings_stars($overall_post_rating, $full_star_icon_html, $full_star_icon_data, $half_star_icon_html, $half_star_icon_data, $empty_star_icon_html, $empty_star_icon_data);
        } else {
            if( $show_empty_rating ) {
                // Display the ratings
                $buffy = td_util::display_user_ratings_stars(0, $full_star_icon_html, $full_star_icon_data, $half_star_icon_html, $half_star_icon_data, $empty_star_icon_html, $empty_star_icon_data);
            }
        }

        return $buffy;

    }


    /**
     * Get a custom field data
     * @param $field_name
     * @return array
     */
    function get_custom_field_data($field_name) {

        $post_id = $this->post->ID;

        $field_data = array(
            'value' => '',
            'type' => '',
            'meta_exists' => false,
        );

        if( $field_name == 'td_source_title' ) {
            $source_post_id = get_post_meta( $post_id, 'tdc-parent-post-id', true );

            if ( !empty( $source_post_id ) ) {
                $field_data['value'] = get_the_title($source_post_id);
                $field_data['type'] = 'text';
                $field_data['meta_exists'] = true;
            }
        } else {
            $field_data = td_util::get_acf_field_data( $field_name, $post_id );

            if( !$field_data['meta_exists'] ) {
                if( metadata_exists('post', $post_id, $field_name ) ) {
                    $field_data['value'] = get_post_meta( $post_id, $field_name, true );
                    $field_data['type'] = 'text';
                    $field_data['meta_exists'] = true;
                }
            }
        }

        return $field_data;

    }


    /**
     * Get a custom field data
     * @param $field_name
     * @return string
     */
    function show_custom_field_value($field_name) {

        $buffy = '';


        $custom_field_data = $this->get_custom_field_data($field_name);

        if( !empty( $custom_field_data ) ) {
            switch ( $custom_field_data['type'] ) {
                case 'image';
                    $img_url = '';

                    if( is_array( $custom_field_data['value'] ) ) {
                        $img_url = $custom_field_data['value']['url'];
                    } else if( is_string( $custom_field_data['value'] ) ) {
                        $img_url = $custom_field_data['value'];
                    } else if ( is_numeric( $custom_field_data['value'] ) ) {
                        $img_id = $custom_field_data['value'];
                        $img_info = get_post( $img_id );

                        if( $img_info ) {
                            $img_url = $img_info->guid;
                        }
                    }

                    $buffy .= $img_url;

                    break;

                case 'taxonomy':
                    foreach ( $custom_field_data['value'] as $field_value ) {
                        $term_type = $custom_field_data['taxonomy'];
                        $term_data = $field_value;
                        if( is_numeric( $field_value ) ) {
                            $term_data = get_term_by('term_id', $field_value, $term_type);
                        }

                        if( $term_data ) {
                            $buffy .= '<a href="' . get_term_link($term_data->term_id, $term_type) . '">' . $term_data->name . '</a>';
                        }
                    }

                    break;

                default:
                    if( is_array( $custom_field_data['value'] ) ) {
                        foreach ( $custom_field_data['value'] as $key => $value ) {
                            if( is_array( $value ) ) {
                                $buffy .= $value['label'];
                            } else if( td_util::isAssocArray( $custom_field_data['value'] ) ) {
                                if( $key == 'label' ) {
                                    $buffy .= $value;
                                }
                            } else {
                                $buffy .= $value;
                            }

                            if( $key != array_key_last( $custom_field_data['value'] ) ) {
                                $buffy .= ', ';
                            }
                        }
                    } else {
                        $buffy .= $custom_field_data['value'];
                    }

                    break;
            }
        }


        return $buffy;

    }
}
