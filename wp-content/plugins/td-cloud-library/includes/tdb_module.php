<?php

abstract class tdb_module {

    var $post;
    var $title_attribute;
    var $title;
    var $href;

    private $module_atts = array();


    protected $review_source = 'author';
    protected $td_review;
    protected $user_reviews_overall = 0;
    protected $is_review = false;

    protected $post_thumb_id = NULL;

    function __construct( $post_data_array, $module_atts = array() ) {

        $this->module_atts     = $module_atts;
        $this->post            = $post_data_array;
        $this->title           = $this->post['post_title'];
        $this->title_attribute = $this->post['post_title_attribute'];
        $this->href            = $this->post['post_link'];

        // create a post obj for the unique posts filter
        $wp_post_obj = new stdClass();
        $wp_post_obj->ID = $this->post['post_id']; // we add just the id for now @todo if we need other post properties set here..

        // this filter is used by td_unique_posts.php - to add unique posts to the td_unique_posts::$rendered_posts_ids array
        // ... to be applied in the tdb_state_single_page->loop as 'post__not_in' wp query args
        apply_filters( "td_wp_booster_module_constructor", $this, new WP_Post( $wp_post_obj ) );

        if ( has_post_thumbnail( $this->post['post_id'] ) ) {
            $tmp_get_post_thumbnail_id = get_post_thumbnail_id( $this->post['post_id'] );
            if ( !empty( $tmp_get_post_thumbnail_id ) ) {
                $this->post_thumb_id = $tmp_get_post_thumbnail_id;
            }
        }

        // set the reviews source
        if( isset( $this->module_atts['review_source'] ) && !empty( $this->module_atts['review_source'] ) ) {
            $this->review_source = $this->module_atts['review_source'];
        }

        //get the review metadata
        //$this->td_review = get_post_meta($this->post->ID, 'td_review', true); @todo $this->td_review variable name must be replaced and the 'get_quotes_on_blocks', 'get_category' methods also
        $this->td_review = td_util::get_post_meta_array($this->post['post_id'], 'td_post_theme_settings');

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
                if( $this->post['post_type'] == 'tdc-review' ) {
                    $review_overall_rating = td_util::get_overall_review_rating( $this->post['post_id'] );
                } else {
                    $review_overall_rating = td_util::get_overall_post_rating( $this->post['post_id'] );
                }

                if( $review_overall_rating ) {
                    $this->is_review = true;
                    $this->user_reviews_overall = $review_overall_rating;
                }
                break;
        }

    }


    function get_module_classes( $additional_classes_array = '' ) {
        //add the wrap and module id class
        $buffy = get_class( $this );


	    // each module setting has a 'class' key to customize css
	    $module_class = td_api_module::get_key( get_class( $this ), 'class' );

	    if ( $module_class != '' ) {
		    $buffy .= ' ' . $module_class;
	    }


        //show no thumb only if no thumb is detected and image placeholders are disabled
        if ( is_null( $this->post_thumb_id ) and td_util::get_option( 'tds_hide_featured_image_placeholder' ) == 'hide_placeholder' ) {
            $buffy .= ' td_module_no_thumb';
        }

        // fix the meta info space when all options are off
        if ( td_util::get_option('tds_m_show_author_name') == 'hide' and td_util::get_option( 'tds_m_show_date' ) == 'hide' and td_util::get_option( 'tds_m_show_comments' ) == 'hide' ) {
            $buffy .= ' td-meta-info-hide';
        }

	    if ( $additional_classes_array != '' && is_array( $additional_classes_array ) ) {
		    $buffy .= ' ' . implode( ' ', $additional_classes_array );
	    }

        return $buffy;
    }

    function get_author_photo() {
        $buffy = '';

        $buffy .= '<a href="' . $this->post['post_author_url'] . '" aria-label="author-photo" rel="nofollow" class="tdb-author-photo">' . get_avatar( $this->post['post_author_email'], '96', '', $this->post['post_author_name'] ) . '</a>';

        return $buffy;
    }

    function get_author($show_when_review = false) {
        $buffy = '';

        if ($show_when_review or ($this->is_review === false or td_util::get_option('tds_m_show_review') == 'hide')) {
            if (td_util::get_option('tds_m_show_author_name') != 'hide' && !empty($this->post['post_author_name']) ) {
                $buffy .= '<span class="td-post-author-name">';
                    $buffy .= '<a href="' . $this->post['post_author_url'] . '">' . $this->post['post_author_name'] . '</a>';
                    if (td_util::get_option('tds_m_show_author_name') != 'hide' and td_util::get_option('tds_m_show_date') != 'hide') {
                        $buffy .= ' <span>-</span> ';
                    }
                $buffy .= '</span>';
            }
        }

        return $buffy;
    }

    function get_date($modified_date = '', $show_when_review = false, $time_ago = '', $time_ago_add_txt = '', $time_ago_txt_pos = '') {
        $visibility_class = '';
        if ( td_util::get_option('tds_m_show_date') == 'hide' ) {
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
                    $td_article_date = date(DATE_W3C, $this->post['post_date_unix']);
                    $td_article_modified_date = date(DATE_W3C, $this->post['post_date_unix']);
                } else {
                    // get_post_datetime() used from WP 5.3
                    $td_article_date = get_post_datetime($this->post["post_id"], 'date', 'gmt');
                    if ($td_article_date !== false) {
                        $td_article_date = $td_article_date->format(DATE_W3C);
                    }
                    $td_article_modified_date = get_post_datetime($this->post["post_id"], 'modified', 'gmt');
                    if ($td_article_modified_date !== false) {
                        $td_article_modified_date = $td_article_modified_date->format(DATE_W3C);
                    }
                }

                $buffy .= '<span class="td-post-date">';
                    if ($modified_date == 'yes' || td_util::get_option('tds_m_show_modified_date') == 'yes') {
                        $display_modified_date = isset($this->post['post_modified']) ? $this->post['post_modified'] : $this->post['post_date'];

                        if( $time_ago != '' ) {
                            $current_time = current_time( 'timestamp' );
                            $post_time_u  = get_the_modified_date('U', $this->post["post_id"] );
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
                    } else {
                        $display_date = $this->post['post_date'];

                        if( $time_ago != '' ) {
                            $current_time = current_time( 'timestamp' );
                            $post_time_u  = get_the_time('U', $this->post["post_id"] );
                            $diff = (int) abs( $current_time - $post_time_u );

                            if ( $diff < WEEK_IN_SECONDS ) {
                                $display_date = human_time_diff( $post_time_u, $current_time );
                                if( $time_ago_add_txt != '' ) {
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

    function get_review() {
        $buffy = '';

        if ($this->is_review and td_util::get_option('tds_m_show_review') != 'hide' ) {
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
            $buffy .= '<span class="td-module-comments">';
                $buffy .= '<a href="' . $this->post['post_comments_link'] . '">';
                    $buffy .= $this->post['post_comments_no'];
                $buffy .= '</a>';
            $buffy .= '</span>';
        }

        return $buffy;
    }

    function get_title( $cut_at = '', $title_tag = '' ) {

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
        if ( $cut_at != '' ) {
            //cut at the hard coded size
            $buffy .= td_util::excerpt( $this->title, $cut_at, 'show_shortcodes' );

        } else {
            $current_module_class = get_class( $this );

            //see if we have a default setting for this module, and if so only apply it if we don't get other things form theme panel.
            if ( td_api_module::_helper_check_excerpt_title( $current_module_class ) ) {
                $db_title_excerpt = td_util::get_option($current_module_class . '_title_excerpt');
                if ( $db_title_excerpt != '' ) {
                    //cut from the database settings
                    $buffy .= td_util::excerpt( $this->title, $db_title_excerpt, 'show_shortcodes' );
                } else {
                    //cut at the default size
                    $module_api = td_api_module::get_by_id( $current_module_class );
                    $buffy .= td_util::excerpt( $this->title, $module_api['excerpt_title'], 'show_shortcodes' );
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
     * refactored 12.10.2018 - removed the css image parameter and the classic <img> tag type images, now all tdb modules use css images
     * @param $thumbType
     * @return string
     */
    function get_image( $thumbType ) {

        $td_use_webp = '';

        $buffy = '';

            if ( !is_null( $this->post_thumb_id ) ) {

                if ( td_util::get_option( 'tds_thumb_' . $thumbType ) != 'yes' and $thumbType != 'thumbnail' ) {

                    //the thumb is disabled, show a placeholder thumb from the theme with the "thumb disabled" message
                    global $_wp_additional_image_sizes;

                    if ( empty( $_wp_additional_image_sizes[$thumbType]['width'] ) ) {
                        $td_temp_image_url[1] = '';
                    } else {
                        $td_temp_image_url[1] = $_wp_additional_image_sizes[$thumbType]['width'];
                    }

                    if ( empty( $_wp_additional_image_sizes[$thumbType]['height'] ) ) {
                        $td_temp_image_url[2] = '';
                    } else {
                        $td_temp_image_url[2] = $_wp_additional_image_sizes[$thumbType]['height'];
                    }

                    // For custom WordPress sizes (not 'thumbnail', 'medium', 'medium_large' or 'large'), get the image path using the api (no_image_path)
                    $thumb_disabled_path = td_global::$get_template_directory_uri;

                    if ( strpos( $thumbType, 'td_') === 0 ) {
                        $thumb_disabled_path = td_api_thumb::get_key( $thumbType, 'no_image_path' );
                    }

                    $td_temp_image_url[0] = $thumb_disabled_path . '/images/thumb-disabled/' . $thumbType . '.png';

                } else {

                    // the thumb is enabled from the panel, it's time to show the real thumb
                    $td_temp_image_url = wp_get_attachment_image_src( $this->post_thumb_id, $thumbType );

                    if ( empty( $td_temp_image_url[0] ) ) {
                        $td_temp_image_url[0] = '';
                    }

                    if ( empty( $td_temp_image_url[1] ) ) {
                        $td_temp_image_url[1] = '';
                    }

                    if ( empty( $td_temp_image_url[2] ) ) {
                        $td_temp_image_url[2] = '';
                    }

                }

            } else {

                //we have no thumb use the placeholder
                global $_wp_additional_image_sizes;

                if ( empty( $_wp_additional_image_sizes[$thumbType]['width'] ) ) {
                    $td_temp_image_url[1] = '';
                } else {
                    $td_temp_image_url[1] = $_wp_additional_image_sizes[$thumbType]['width'];
                }

                if ( empty( $_wp_additional_image_sizes[$thumbType]['height'] ) ) {
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
                    $td_thumb_parameters = td_api_thumb::get_by_id( $thumbType );
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
                        $no_thumb_path = td_global::$get_template_directory_uri;

                        if ( strpos( $thumbType, 'td_' ) === 0 ) {
                            $no_thumb_path = rtrim( td_api_thumb::get_key( $thumbType, 'no_image_path' ), '/');
                        }

                        $td_temp_image_url[0] = $no_thumb_path . '/images/no-thumb/' . $thumbType . '.png';
                    }
                }

                /**
                 * for custom wp sizes (not 'thumbnail', 'medium', 'medium_large' or 'large'), get the image path using the api (no_image_path)
                 */

            }

            $buffy .= '<div class="td-module-thumb">';
                $post_type = get_post_format($this->post['post_id']);

                // the edit link
                if ( $this->post['post_type'] === 'sample' ) {
                    if ( current_user_can( 'edit_published_posts' ) ) {
                        $buffy .= '<a class="td-admin-edit" href="#">edit</a>';
                    }
                } else {
                    if ( current_user_can( 'edit_published_posts' ) ) {
                        $buffy .= '<a class="td-admin-edit" href="' . get_edit_post_link( $this->post['post_id'] ) . '" title="edit post">edit</a>';
                    }
                }

                $video_popup_class = '';
                $video_popup_data = '';
                if ( $post_type == 'video' && isset($this->module_atts['video_popup']) && $this->module_atts['video_popup'] != '' ) {
                    $video_url = get_post_meta($this->post['post_id'], 'td_post_video');

                    $autoplay_vid = '';
                    if( isset($this->module_atts['autoplay_vid']) ) {
                        $autoplay_vid = $this->module_atts['autoplay_vid'];
                    }

                    if( isset($video_url[0]['td_video']) && $video_url[0]['td_video'] != '' ) {
                        $video_source = td_video_support::detect_video_service($video_url[0]['td_video']);

                        $video_popup_class = 'td-module-video-modal';
                        $video_popup_data = 'data-video-source="' . $video_source . '" data-video-autoplay="' . $autoplay_vid . '" data-video-url="'. $video_url[0]['td_video'] . '"';
                        $video_rec = rawurldecode( base64_decode( strip_tags( $this->module_atts['video_rec'] ) ) );

                        $video_popup_ad = array(
                            'code' => do_shortcode( stripslashes( $video_rec ) ),
                            'title' => $this->module_atts['video_rec_title'],
                            'disable' => false,
                        );

                        if( $this->module_atts['video_rec_disable'] != '' && ( current_user_can('administrator') || current_user_can('editor') ) ) {
                            $video_popup_ad['disable'] = true;
                        }

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

                $wp_directory = trailingslashit(ABSPATH);
                $wp_dirname = basename(rtrim($wp_directory, '/'));
                $webp_img_path = parse_url($td_temp_image_url[0], PHP_URL_PATH) . '.webp';
                $webp_img_path = str_replace("/$wp_dirname/", '/', $webp_img_path);
                $webp_img_path = rtrim(ABSPATH, '/') . '/' . ltrim($webp_img_path, '/');

                if (file_exists($webp_img_path)) {
                    $td_use_webp = ( td_util::browser_supports_webp() && td_util::get_option('tds_load_webp') === 'yes' ) ? '.webp' : $td_use_webp;
                }

                // if we have the lazy loading animation on and the we're not on a wp ajax request or on tdc editor iframe or ajax call
                if ( empty( $tds_animation_stack ) && !wp_doing_ajax() && ! td_util::tdc_is_live_editor_ajax() && ! td_util::tdc_is_live_editor_iframe() ) {

                // retina image
                $retina_image = '';

                // here we treat the normal img_tag retina ver
                if ( td_util::get_option('tds_thumb_' . $thumbType . '_retina') == 'yes' && !empty( $td_temp_image_url[1] ) ) {
                    $retina_url = wp_get_attachment_image_src( $this->post_thumb_id, $thumbType . '_retina' );
                    if ( !empty( $retina_url[0] ) ) {
                        $retina_image = 'data-img-retina-url="' . $retina_url[0] . $td_use_webp . '"';
                    }
                }

                $buffy .= '<span class="entry-thumb td-thumb-css" data-type="css_image" data-img-url="' . $td_temp_image_url[0] . $td_use_webp . '" ' . $retina_image .  '></span>';

            } else {

                // unique id for setting the retina image via style attr
                $retina_uuid = '';

                if ( td_util::get_option('tds_thumb_' . $thumbType . '_retina') == 'yes' && !empty( $td_temp_image_url[1] ) ) {

                    $retina_uuid = td_global::td_generate_unique_id();
                    $retina_url = wp_get_attachment_image_src( $this->post_thumb_id, $thumbType . '_retina' );

                    if ( !empty( $retina_url[0] ) ) {
                        $buffy .= '
                            <style>
                                /* custom css - generated by TagDiv Composer */
                                  @media ( -webkit-max-device-pixel-ratio: 2 ) {
                                      .td-thumb-css.' . $retina_uuid . ' {
                                          background-image: url(' . $retina_url[0] . $td_use_webp . ');
                                      }
                                  }
                            </style>';
                    }
                }

                $buffy .= '<span class="entry-thumb td-thumb-css ' . $retina_uuid . '" style="background-image: url(' . $td_temp_image_url[0] . $td_use_webp . ')"></span>';
            }

            if ($post_type == 'video' || $post_type == 'audio') {

                $use_small_post_format_icon_size = false;

                // search in all the thumbs for the one that we are currently using here and see if it has post_format_icon_size = small
                foreach ( td_api_thumb::get_all() as $thumb_from_thumb_list ) {
                    if ( $thumb_from_thumb_list['name'] == $thumbType and $thumb_from_thumb_list['post_format_icon_size'] == 'small' ) {
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

    function get_favorite_badge() {

        td_resources_load::render_script( TDB_SCRIPTS_URL . '/tdbFavourites.js' . TDB_SCRIPTS_VER, 'tdbFavourites-js', '', 'footer' );

        return '<span class="td-favorite tdb-favorite ' . (td_util::is_article_favourite($this->post['post_id']) ? 'tdb-favorite-selected' : '') . '" data-post-id="' . $this->post['post_id'] . '">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 511.96 511.96" class="td-favorite-ico td-favorite-ico-empty"><path id="Path_1" data-name="Path 1" d="M0,48A48.012,48.012,0,0,1,48,0V441.4l130.1-92.9a23.872,23.872,0,0,1,27.9,0l130,92.9V48H48V0H336a48.012,48.012,0,0,1,48,48V488a23.974,23.974,0,0,1-37.9,19.5L192,397.5,37.9,507.5A23.974,23.974,0,0,1,0,488Z" transform="translate(63.98)"/></svg>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 511.96 512.96" class="td-favorite-ico td-favorite-ico-full"><path id="Path_2" data-name="Path 2" d="M0,48V487.7a24.341,24.341,0,0,0,38.3,19.9L192,400,345.7,507.6A24.341,24.341,0,0,0,384,487.7V48A48.012,48.012,0,0,0,336,0H48A48.012,48.012,0,0,0,0,48Z" transform="translate(63.98)"/></svg>
        </span>';

    }

    function get_excerpt( $cut_at = '' ) {

        //If the user supplied the excerpt in the post excerpt custom field, we just return that
        if ( $this->post['post_excerpt'] != '' ) {
            return $this->post['post_excerpt'];
        }

        $buffy = '';
        if ($cut_at != '') {
            // simple, $cut_at and return
            $buffy .= td_util::excerpt($this->post['post_content'], $cut_at);
        } else {
            $current_module_class = get_class($this);

            //see if we have a default setting for this module, and if so only apply it if we don't get other things form theme panel.
            if ( td_api_module::_helper_check_excerpt_content( $current_module_class ) ) {
                $db_content_excerpt = td_util::get_option($current_module_class . '_content_excerpt');
                if ( $db_content_excerpt != '' ) {
                    //cut from the database settings
                    $buffy .= td_util::excerpt($this->post['post_content'], $db_content_excerpt);
                } else {
                    //cut at the default size
                    $module_api = td_api_module::get_by_id( $current_module_class );
                    $buffy .= td_util::excerpt($this->post['post_content'], $module_api['excerpt_content']);
                }
            } else {
                /**
                 * no $cut_at provided and no setting in td_config -> return the full $this->post->post_content
                 * @see td_global::$modules_list
                 */
                $buffy .= $this->post['post_content'];
            }
        }
        return $buffy;
    }

    function get_audio_embed() {

        $buffy = '';

        if( get_post_format( $this->post['post_id'] ) == 'audio' ) {
            $td_post_audio = td_util::get_post_meta_array($this->post['post_id'], 'td_post_audio');

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

        if( get_post_format( $this->post['post_id'] ) == 'video' ) {
            $video_url = get_post_meta($this->post['post_id'], 'td_post_video');

            if( isset($video_url[0]['td_video']) && $video_url[0]['td_video'] != '' ) {
                if ( metadata_exists('post', $this->post['post_id'], 'td_post_video_duration') ) {
                    $video_duration = get_post_meta( $this->post['post_id'], 'td_post_video_duration', true );
                } else {
                    $video_duration = td_video_support::get_video_duration($video_url[0]['td_video']);
                    update_post_meta( $this->post['post_id'], 'td_post_video_duration', $video_duration );
                }

                $buffy .= '<div class="td-post-vid-time">' . $video_duration . '</div>';
            }
        }

        return $buffy;

    }


    function get_category() {

        $buffy = '';
            $selected_category_obj = '';
            $selected_category_obj_id = '';
            $selected_category_obj_name = '';

            //read the post meta to get the custom primary category
            $post_theme_settings = $this->post['post_theme_settings'];

            if ( !empty( $post_theme_settings['td_primary_cat'] ) ) {
                //we have a custom category selected
                $selected_category_obj = get_category( $post_theme_settings['td_primary_cat'] );
            } else {
                //get one auto
                $categories = get_the_category( $this->post['post_id'] );

                if ( is_category() ) {
                    foreach ( $categories as $category ) {
                        if ( $category->term_id == get_query_var('cat') ) {
                            $selected_category_obj = $category;
                            break;
                        }
                    }
                }

                if ( empty( $selected_category_obj ) and !empty( $categories[0] ) ) {
                    if ( $categories[0]->name === TD_FEATURED_CAT and !empty( $categories[1] ) ) {
                        $selected_category_obj = $categories[1];
                    } else {
                        $selected_category_obj = $categories[0];
                    }
                }
            }

            if ( !empty( $selected_category_obj ) ) {
                $selected_category_obj_id = $selected_category_obj->cat_ID;
                $selected_category_obj_name = $selected_category_obj->name;
            }


        if ( !empty( $selected_category_obj_id ) && !empty( $selected_category_obj_name ) ) { //@todo catch error here

            $td_cat_bg = '';
            $td_cat_color = '';
            $cat_text_color = '';

            if (!empty($this->module_atts['cat_style'])) {

                $cat_style = $this->module_atts['cat_style'];
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

            $style = ( !empty($td_cat_bg) || !empty($td_cat_color) || !empty($cat_text_color) )? ' style="' . $td_cat_bg . $td_cat_color . $cat_text_color . '"' : '';

            $buffy .= '<a href="' . get_category_link( $selected_category_obj_id ) . '" class="td-post-category" ' . $style . '>'  . $selected_category_obj_name . '</a>' ;
        }

        return $buffy;
    }

    function get_quotes_on_blocks() {

        //get quotes data from post theme settings
        $post_theme_settings = $this->post['post_theme_settings'];

        if( !empty( $post_theme_settings['td_quote_on_blocks'] ) ) {
            return '<div class="td_quote_on_blocks">' . $post_theme_settings['td_quote_on_blocks'] . '</div>';
        }

        return '';
    }

    function get_shortcode_att( $att_name ) {
        // returns '' if not set - for loops and other places where modules are not in blocks

        if ( empty( $this->module_atts ) ) {
            return '';
        }

        if ( !isset( $this->module_atts[$att_name] ) ) {
            td_util::error(__FILE__, $att_name . ' - Is not mapped in the shortcode that uses this module ( <strong>' . get_class($this) . '</strong>)', $this->module_atts);
            die;
        }

        $attr_value = $this->module_atts[$att_name];
        if (strpos($attr_value, 'td_encval') === 0) {
            $attr_value = str_replace('td_encval', '', $attr_value);
            $attr_value = base64_decode($attr_value);
        }

        return $attr_value;

    }
}