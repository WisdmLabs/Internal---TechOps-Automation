<?php
class td_block_author extends td_block {

    /**
     * Disable loop block features. This block does not use a loop and it dosn't need to run a query.
     */
    function __construct() {
        parent::disable_loop_block_features();
    }

    public function get_custom_css() {
        // $unique_block_class - the unique class that is on the block. use this to target the specific instance via css
        $in_composer = td_util::tdc_is_live_editor_iframe() || td_util::tdc_is_live_editor_ajax();
        $in_element = td_global::get_in_element();
        $unique_block_class_prefix = '';
        if( $in_element || $in_composer ) {
            $unique_block_class_prefix = 'tdc-row .';

            if( $in_element && $in_composer ) {
                $unique_block_class_prefix = 'tdc-row-composer .';
            }
        }
        $unique_block_class = $unique_block_class_prefix . $this->block_uid;

        $compiled_css = '';

        $raw_css =
            "<style>

                /* @style_general_block_author */
                .td_block_author .block-title {
                  float: none;
                  margin-bottom: 20px;
                  width: 100%;
                }
                .td_block_author .td_author_wrap {
                  font-family: Verdana, BlinkMacSystemFont, -apple-system, \"Segoe UI\", Roboto, Oxygen, Ubuntu, Cantarell, \"Open Sans\", \"Helvetica Neue\", sans-serif;
                  text-align: center;
                }
                .td_block_author .td-author-image {
                  position: relative;
                  margin-bottom: 7px;
                  display: inline-block;
                }
                .td_block_author .td-author-image:before,
                .td_block_author .td-author-image:after {
                  border-radius: 500px;
                }
                .td_block_author .avatar {
                  border-radius: 500px;
                  display: block;
                }
                .td_block_author .td-author-name {
                  position: relative;
                  margin-bottom: 21px;
                  font-size: 18px;
                  font-weight: 600;
                  text-transform: uppercase;
                }
                .td_block_author .td-author-description {
                  padding: 2px 0 0 0;
                  font-family: var(--td_default_google_font_1, 'Open Sans', 'Open Sans Regular', sans-serif);
                  font-size: 15px;
                  line-height: 21px;
                  font-style: italic;
                  color: #555;
                }
                .td_block_author .td-author-page {
                  font-family: var(--td_default_google_font_1, 'Open Sans', 'Open Sans Regular', sans-serif);
                  font-size: 15px;
                  margin-top: 12px;
                  font-weight: 600;
                }
                .td_block_author a {
                  color: #000;
                }
                .td_block_author a:hover {
                  color: var(--td_theme_color, #4db2ec);
                }

                
                /* @photo_size */
				.$unique_block_class .avatar {
					width: @photo_size;
				}
                /* @photo_radius */
				.$unique_block_class .avatar,
				.$unique_block_class .td-author-image:before,
				.$unique_block_class .td-author-image:after {
					border-radius: @photo_radius;
				}
				
				
				/* @author_name_color */
				.$unique_block_class .td-author-name a {
					color: @author_name_color;
                }
				/* @author_description_color */
				.$unique_block_class .td-author-description {
					color: @author_description_color;
				}
				


				/* @f_header */
				.$unique_block_class .td-block-title a,
				.$unique_block_class .td-block-title span {
					@f_header
				}
				/* @f_name */
				.$unique_block_class .td-author-name {
					@f_name
				}
				/* @f_descr */
				.$unique_block_class .td-author-description {
					@f_descr
				}
				
				/* @mix_type */
                .$unique_block_class .td-author-image:before {
                    content: '';
                    width: 100%;
                    height: 100%;
                    position: absolute;
                    opacity: 1;
                    transition: opacity 1s ease;
                    -webkit-transition: opacity 1s ease;
                    mix-blend-mode: @mix_type;
                    z-index: 1;
                    top: 0;
                    left: 0;
                }
                /* @color */
                .$unique_block_class .td-author-image:before {
                    background: @color;
                }
                /* @mix_gradient */
                .$unique_block_class .td-author-image:before {
                    @mix_gradient;
                }
                
                
                /* @mix_type_h */
                @media (min-width: 1141px) {
                    .$unique_block_class .td-author-image:after {
                        content: '';
                        width: 100%;
                        height: 100%;
                        position: absolute;
                        opacity: 0;
                        transition: opacity 1s ease;
                        -webkit-transition: opacity 1s ease;
                        mix-blend-mode: @mix_type_h;
                        z-index: 1;
                        top: 0;
                        left: 0;
                    }
                    .$unique_block_class .td-author-image:hover:after {
                        opacity: 1;
                    }
                    .$unique_block_class .td-author-image {
                        pointer-events: auto;
                    }
                }
                
                /* @color_h */
                .$unique_block_class .td-author-image:after {
                    background: @color_h;
                }
                /* @mix_gradient_h */
                .$unique_block_class .td-author-image:after {
                    @mix_gradient_h;
                }
                /* @mix_type_off */
                .$unique_block_class .td-author-image:hover:before {
                    opacity: 0;
                }
                .$unique_block_class .tdb-author-photo {
                    pointer-events: auto;
                }
                    
                /* @effect_on */
                .$unique_block_class .avatar {
                    filter: @fe_brightness @fe_contrast @fe_saturate;
                    transition: all 1s ease;
                    -webkit-transition: all 1s ease;
                }
                /* @effect_on_h */
                @media (min-width: 1141px) {
                    .$unique_block_class .td-author-image:hover .avatar {
                        filter: @fe_brightness_h @fe_contrast_h @fe_saturate_h;
                    }
                }
				
			</style>";


        $td_css_res_compiler = new td_css_res_compiler( $raw_css );
        $td_css_res_compiler->load_settings( __CLASS__ . '::cssMedia', $this->get_all_atts() );

        $compiled_css .= $td_css_res_compiler->compile_css();
        return $compiled_css;
    }

    static function cssMedia( $res_ctx ) {

        $res_ctx->load_settings_raw( 'style_general_block_author', 1 );

        /*-- IMAGE -- */
        // author image size
        $author_photo_size = $res_ctx->get_shortcode_att('photo_size');
        $res_ctx->load_settings_raw( 'photo_size', $author_photo_size );
        if( $author_photo_size != '' && is_numeric( $author_photo_size ) ) {
            $res_ctx->load_settings_raw( 'photo_size', $author_photo_size . 'px' );
        }

        // author image radius
        $author_photo_radius = $res_ctx->get_shortcode_att('photo_radius');
        $res_ctx->load_settings_raw( 'photo_radius', $author_photo_radius );
        if( $author_photo_radius != '' && is_numeric( $author_photo_radius ) ) {
            $res_ctx->load_settings_raw( 'photo_radius', $author_photo_radius . 'px' );
        }



        /*-- TEXT -- */
        // author name color
        $res_ctx->load_settings_raw( 'author_name_color', $res_ctx->get_shortcode_att('author_name_color') );

        // author description color
        $res_ctx->load_settings_raw( 'author_description_color', $res_ctx->get_shortcode_att('author_description_color') );



        /*-- FONTS -- */
        $res_ctx->load_font_settings( 'f_header' );
        $res_ctx->load_font_settings( 'f_name' );
        $res_ctx->load_font_settings( 'f_descr' );

        // mix blend
        $mix_type = $res_ctx->get_shortcode_att('mix_type');
        if ( $mix_type != '' ) {
            $res_ctx->load_settings_raw('mix_type', $res_ctx->get_shortcode_att('mix_type'));
        }
        $res_ctx->load_color_settings( 'mix_color', 'color', 'mix_gradient', '', '' );

        $mix_type_h = $res_ctx->get_shortcode_att('mix_type_h');
        if ( $mix_type_h != '' ) {
            $res_ctx->load_settings_raw('mix_type_h', $res_ctx->get_shortcode_att('mix_type_h'));
        } else {
            $res_ctx->load_settings_raw('mix_type_off', 1);
        }
        $res_ctx->load_color_settings( 'mix_color_h', 'color_h', 'mix_gradient_h', '', '' );

        // effects
        $res_ctx->load_settings_raw('fe_brightness', 'brightness(1)');
        $res_ctx->load_settings_raw('fe_contrast', 'contrast(1)');
        $res_ctx->load_settings_raw('fe_saturate', 'saturate(1)');

        $fe_brightness = $res_ctx->get_shortcode_att('fe_brightness');
        if ($fe_brightness != '1') {
            $res_ctx->load_settings_raw('fe_brightness', 'brightness(' . $fe_brightness . ')');
            $res_ctx->load_settings_raw('effect_on', 1);
        }
        $fe_contrast = $res_ctx->get_shortcode_att('fe_contrast');
        if ($fe_contrast != '1') {
            $res_ctx->load_settings_raw('fe_contrast', 'contrast(' . $fe_contrast . ')');
            $res_ctx->load_settings_raw('effect_on', 1);
        }
        $fe_saturate = $res_ctx->get_shortcode_att('fe_saturate');
        if ($fe_saturate != '1') {
            $res_ctx->load_settings_raw('fe_saturate', 'saturate(' . $fe_saturate . ')');
            $res_ctx->load_settings_raw('effect_on', 1);
        }

        // effects hover
        $res_ctx->load_settings_raw('fe_brightness_h', 'brightness(1)');
        $res_ctx->load_settings_raw('fe_contrast_h', 'contrast(1)');
        $res_ctx->load_settings_raw('fe_saturate_h', 'saturate(1)');

        $fe_brightness_h = $res_ctx->get_shortcode_att('fe_brightness_h');
        $fe_contrast_h = $res_ctx->get_shortcode_att('fe_contrast_h');
        $fe_saturate_h = $res_ctx->get_shortcode_att('fe_saturate_h');

        if ($fe_brightness_h != '1') {
            $res_ctx->load_settings_raw('fe_brightness_h', 'brightness(' . $fe_brightness_h . ')');
            $res_ctx->load_settings_raw('effect_on_h', 1);
        }
        if ($fe_contrast_h != '1') {
            $res_ctx->load_settings_raw('fe_contrast_h', 'contrast(' . $fe_contrast_h . ')');
            $res_ctx->load_settings_raw('effect_on_h', 1);
        }
        if ($fe_saturate_h != '1') {
            $res_ctx->load_settings_raw('fe_saturate_h', 'saturate(' . $fe_saturate_h . ')');
            $res_ctx->load_settings_raw('effect_on_h', 1);
        }
        // make hover to work
        if ($fe_brightness_h != '1' || $fe_contrast_h != '1' || $fe_saturate_h != '1') {
            $res_ctx->load_settings_raw('effect_on', 1);
        }
        if ($fe_brightness != '1' || $fe_contrast != '1' || $fe_saturate != '1') {
            $res_ctx->load_settings_raw('effect_on_h', 1);
        }

    }


    function render($atts, $content = null) {

        parent::render($atts);

        extract(shortcode_atts(
            array(
                'author_id' => '1', // ID 1 for admin
                'author_slug'=> '',
                'author_url_text' => '',
                'author_url' => '',
                'open_in_new_window' => ''
            ), $atts));

        $td_target = '';
        if (!empty($open_in_new_window)) {
            $td_target = ' target="_blank"';
        }

        $photo_size = '196';
        if (isset($atts['photo_size']) && (strpos($atts['photo_size'], '%') === false && !empty($atts['photo_size']))) {
            $photo_size = $atts['photo_size'];
        }

        if(!empty($author_slug)) {
            $td_author = get_user_by('slug', $author_slug);
        }
        else {
            $td_author = get_user_by('id', $author_id);
        }

	    if ( false === $td_author ) {
		    $buffy = '';
		    $buffy .= '<div class="' . $this->get_block_classes() . '" ' . $this->get_block_html_atts() . '>';
			    $buffy .= '<div class="td_author_wrap td-fix-index">';
				    $buffy .= '<a class="td-author-image" href="#">' . get_avatar('', $photo_size) . '</a>';

				    $buffy .= '<div class="item-details">';
					    $buffy .= '<div class="td-author-name">';
				        $buffy .= '<a href="#">Author name</a>';
					    $buffy .= '</div>';
				    $buffy .= '</div>';
			    $buffy .= '</div>';
		    $buffy .= '</div>';
		    return $buffy;
	    }




        $buffy = '';
        $buffy .= '<div class="' . $this->get_block_classes() . '" ' . $this->get_block_html_atts() . '>';


        //get the block css
        $buffy .= $this->get_block_css();

        // block title wrap
        $buffy .= '<div class="td-block-title-wrap">';
            $buffy .= $this->get_block_title(); //get the block title
            $buffy .= $this->get_pull_down_filter();
        $buffy .= '</div>';


        $buffy .= '<div class="td_author_wrap td-fix-index">';
        $buffy .= '<a class="td-author-image" href="' . get_author_posts_url($td_author->ID) . '">' . get_avatar($td_author->user_email, $photo_size, '', $td_author->display_name) . '</a>';
        $buffy .= '<div class="item-details">';

        $buffy .= '<div class="td-author-name">';
        $buffy .= '<a href="' . get_author_posts_url($td_author->ID) . '">' . $td_author->display_name . '</a>';
        $buffy .= '</div>';

        $buffy .= '<div class="td-author-description">';
        $buffy .= get_the_author_meta('description', $td_author->ID );
        $buffy .= '</div>';

        if(!empty($author_url_text)) {

            //we need to decode the square bracket case
            if ( strpos( $author_url_text, 'td_encval' ) === 0 ) {
                $author_url_text = str_replace('td_encval', '', $author_url_text);
                $author_url_text = base64_decode( $author_url_text );
            }

            $buffy .= '<div class="td-author-page">';
            $buffy .= '<a href="' . $author_url . '"' . $td_target . '>' . $author_url_text . '</a>';
            $buffy .= '</div>';
        }

        $buffy .= '</div>';

        $buffy .= '</div>';

        $buffy .= '</div>';


        return $buffy;

    }
}