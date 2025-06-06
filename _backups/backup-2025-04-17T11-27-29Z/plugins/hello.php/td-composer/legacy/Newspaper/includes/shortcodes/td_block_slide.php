<?php

class td_block_slide extends td_block {

    static function cssMedia( $res_ctx ) {

        $res_ctx->load_settings_raw( 'style_general_block_slide', 1 );

        // content width
        $content_width = $res_ctx->get_shortcode_att('content_width');
        if( $content_width != '' ) {
            $res_ctx->load_settings_raw( 'content_width', $content_width );
        } else {
            $res_ctx->load_settings_raw( 'content_width', '100%' );
        }

        // image alignment
        $res_ctx->load_settings_raw( 'image_alignment', $res_ctx->get_shortcode_att('image_alignment') . '%' );

        // image height
        $image_height = $res_ctx->get_shortcode_att('image_height');
        if( $image_height != '' && is_numeric($image_height) ) {
            $res_ctx->load_settings_raw( 'image_height', $image_height . 'px' );
        } else {
            $res_ctx->load_settings_raw( 'image_height', $image_height );
        }



        // meta info horizontal align
        $meta_info_horiz = $res_ctx->get_shortcode_att('meta_info_horiz');
        if( $meta_info_horiz == 'content-horiz-center' ) {
            $res_ctx->load_settings_raw( 'meta_info_horiz_center', 1 );
        }
        if( $meta_info_horiz == 'content-horiz-right' ) {
            $res_ctx->load_settings_raw( 'meta_info_horiz_right', 1 );
        }

        // meta info horizontal align
        $meta_info_vert = $res_ctx->get_shortcode_att('meta_info_vert');
        if( $meta_info_vert == 'content-vert-top' ) {
            $res_ctx->load_settings_raw( 'meta_info_vert_top', 1 );
        }
        if( $meta_info_vert == 'content-vert-center' ) {
            $res_ctx->load_settings_raw( 'meta_info_vert_center', 1 );
        }

        // meta info padding
        $meta_padding = $res_ctx->get_shortcode_att('meta_padding');
        $res_ctx->load_settings_raw( 'meta_padding', $meta_padding );
        if( $meta_padding != '' && is_numeric( $meta_padding ) ) {
            $res_ctx->load_settings_raw( 'meta_padding', $meta_padding . 'px' );
        }

        // article title space
        $art_title_space = $res_ctx->get_shortcode_att('art_title_space');
        $res_ctx->load_settings_raw( 'art_title_space', $art_title_space );
        if ( is_numeric( $art_title_space ) ) {
            $res_ctx->load_settings_raw( 'art_title_space', $art_title_space . 'px' );
        }

        // category tag space
        $category_margin = $res_ctx->get_shortcode_att('category_margin');
        $res_ctx->load_settings_raw( 'category_margin', $category_margin );
        if( $category_margin != '' && is_numeric( $category_margin ) ) {
            $res_ctx->load_settings_raw( 'category_margin', $category_margin . 'px' );
        }
        // category tag padding
        $category_padding = $res_ctx->get_shortcode_att('category_padding');
        $res_ctx->load_settings_raw( 'category_padding', $category_padding );
        if( $category_padding != '' && is_numeric( $category_padding ) ) {
            $res_ctx->load_settings_raw( 'category_padding', $category_padding . 'px' );
        }
        //category tag radius
        $category_radius = $res_ctx->get_shortcode_att('category_radius');
        if ( $category_radius != 0 || !empty($category_radius) ) {
            $res_ctx->load_settings_raw( 'category_radius', $category_radius . 'px' );
        }

        // show meta info details
        $show_author = $res_ctx->get_shortcode_att('show_author');
        $show_date = $res_ctx->get_shortcode_att('show_date');
        $show_com = $res_ctx->get_shortcode_att('show_com');
        if( $show_author == 'none' && $show_date == 'none' && $show_com == 'none' ) {
            $res_ctx->load_settings_raw( 'hide_author_date', 1 );
        }
        $res_ctx->load_settings_raw( 'show_cat', $res_ctx->get_shortcode_att('show_cat') );
        $res_ctx->load_settings_raw( 'show_author', $show_author );
        $res_ctx->load_settings_raw( 'show_date', $show_date );
        $res_ctx->load_settings_raw( 'show_com', $show_com );


        // navigation icons size
        $res_ctx->load_settings_raw( 'nav_icon_size', $res_ctx->get_shortcode_att('nav_icon_size') . 'px' );

        // exclusive label
        if( is_plugin_active('td-subscription/td-subscription.php') && !empty( has_filter('td_composer_map_exclusive_label_array', 'td_subscription::add_exclusive_label_settings') ) ) {
            // show exclusive label
            $excl_show = $res_ctx->get_shortcode_att('excl_show');
            $res_ctx->load_settings_raw( 'excl_show', $excl_show );
            if( $excl_show == '' ) {
                $res_ctx->load_settings_raw( 'excl_show', 'inline-block' );
            }

            // exclusive label text
            $res_ctx->load_settings_raw( 'excl_txt', $res_ctx->get_shortcode_att('excl_txt') );

            // exclusive label margin
            $excl_margin = $res_ctx->get_shortcode_att('excl_margin');
            $res_ctx->load_settings_raw( 'excl_margin', $excl_margin );
            if( $excl_margin != '' && is_numeric( $excl_margin ) ) {
                $res_ctx->load_settings_raw( 'excl_margin', $excl_margin . 'px' );
            }

            // exclusive label padding
            $excl_padd = $res_ctx->get_shortcode_att('excl_padd');
            $res_ctx->load_settings_raw( 'excl_padd', $excl_padd );
            if( $excl_padd != '' && is_numeric( $excl_padd ) ) {
                $res_ctx->load_settings_raw( 'excl_padd', $excl_padd . 'px' );
            }

            // exclusive label border size
            $excl_border = $res_ctx->get_shortcode_att('all_excl_border');
            $res_ctx->load_settings_raw( 'all_excl_border', $excl_border );
            if( $excl_border != '' && is_numeric( $excl_border ) ) {
                $res_ctx->load_settings_raw( 'all_excl_border', $excl_border . 'px' );
            }

            // exclusive label border style
            $res_ctx->load_settings_raw( 'all_excl_border_style', $res_ctx->get_shortcode_att('all_excl_border_style') );

            // exclusive label border radius
            $excl_radius = $res_ctx->get_shortcode_att('excl_radius');
            $res_ctx->load_settings_raw( 'excl_radius', $excl_radius );
            if( $excl_radius != '' && is_numeric( $excl_radius ) ) {
                $res_ctx->load_settings_raw( 'excl_radius', $excl_radius . 'px' );
            }


            $res_ctx->load_settings_raw( 'excl_color', $res_ctx->get_shortcode_att('excl_color') );
            $res_ctx->load_settings_raw( 'excl_color_h', $res_ctx->get_shortcode_att('excl_color_h') );
            $res_ctx->load_settings_raw( 'excl_bg', $res_ctx->get_shortcode_att('excl_bg') );
            $res_ctx->load_settings_raw( 'excl_bg_h', $res_ctx->get_shortcode_att('excl_bg_h') );
            $excl_border_color = $res_ctx->get_shortcode_att('all_excl_border_color');
            $res_ctx->load_settings_raw( 'all_excl_border_color', $excl_border_color );
            if( $excl_border_color == '' ) {
                $res_ctx->load_settings_raw( 'all_excl_border_color', '#000' );
            }
            $res_ctx->load_settings_raw( 'excl_border_color_h', $res_ctx->get_shortcode_att('excl_border_color_h') );


            $res_ctx->load_font_settings( 'f_excl' );
        }


        // colors
        $res_ctx->load_color_settings( 'color_overlay', 'overlay', 'overlay_gradient', '', '' );
        $res_ctx->load_color_settings( 'color_overlay_h', 'overlay_h', 'overlay_gradient_h', '', '' );
        $res_ctx->load_settings_raw( 'title_color', $res_ctx->get_shortcode_att('title_color') );
        $res_ctx->load_settings_raw( 'cat_bg', $res_ctx->get_shortcode_att('cat_bg') );
        $res_ctx->load_settings_raw( 'cat_txt', $res_ctx->get_shortcode_att('cat_txt') );
        $res_ctx->load_settings_raw( 'cat_bg_hover', $res_ctx->get_shortcode_att('cat_bg_hover') );
        $res_ctx->load_settings_raw( 'cat_txt_hover', $res_ctx->get_shortcode_att('cat_txt_hover') );
        $res_ctx->load_settings_raw( 'author_txt', $res_ctx->get_shortcode_att('author_txt') );
        $res_ctx->load_settings_raw( 'author_txt_hover', $res_ctx->get_shortcode_att('author_txt_hover') );
        $res_ctx->load_settings_raw( 'date_txt', $res_ctx->get_shortcode_att('date_txt') );
        $res_ctx->load_settings_raw( 'comm_txt', $res_ctx->get_shortcode_att('comm_txt') );
        $res_ctx->load_settings_raw( 'review_stars', $res_ctx->get_shortcode_att('review_stars') );
        $res_ctx->load_settings_raw( 'nav_icons_color', $res_ctx->get_shortcode_att('nav_icons_color') );


        // fonts
        $res_ctx->load_font_settings( 'f_header' );
        $res_ctx->load_font_settings( 'f_ajax' );
//        $res_ctx->load_font_settings( 'f_more' );

        // module slide fonts
        $res_ctx->load_font_settings( 'msf_title' );
        $res_ctx->load_font_settings( 'msf_cat' );
        $res_ctx->load_font_settings( 'msf_meta' );

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

                /* @style_general_block_slide */
                .td_block_slide {
                  position: relative;
                  overflow: hidden;
                  *zoom: 1;
                }
                .td_block_slide:before,
                .td_block_slide:after {
                  display: table;
                  content: '';
                  line-height: 0;
                }
                .td_block_slide:after {
                  clear: both;
                }
                .td_block_slide .td_block_inner {
                  position: relative;
                }
                .td_block_slide .td-module-thumb,
                .td_block_slide .td-video-play-ico,
                .td_block_slide .td-slide-meta,
                .td_block_slide i {
                  -webkit-touch-callout: none;
                  -webkit-user-select: none;
                  user-select: none;
                }
                .td_block_slide .td-module-thumb {
                  margin-bottom: 0;
                  z-index: -1;
                  position: static;
                }
                .td_block_slide .td-video-play-ico {
                  width: 40px !important;
                  height: 40px !important;
                  font-size: 40px !important;
                  border-width: 0.05em !important;
                }
                @media (max-width: 1018px) {
                  .td_block_slide .td-video-play-ico {
                    top: 12px;
                    left: auto;
                    right: 12px;
                    -webkit-transform: none;
                    transform: none;
                  }
                }
                .td_block_slide .td-admin-edit {
                  height: auto !important;
                }
                .td_block_slide .td_module_slide {
                  z-index: 1;
                }
                .td_block_slide .td-image-gradient:before {
                  height: 100%;
                }
                .td_block_slide .td-module-thumb,
                .td_block_slide .entry-thumb {
                  height: 100%;
                }
                .td_block_slide .entry-thumb {
                  background-size: cover;
                }
                .td_block_slide .td-slide-meta {
                  z-index: 2;
                  position: absolute;
                  bottom: 10px;
                  width: 100%;
                  padding: 0 22px;
                  color: #fff;
                  left: 0;
                  right: 0;
                  margin: 0 auto;
                }
                @media (max-width: 767px) {
                  .td_block_slide .td-slide-meta {
                    padding: 0 12px;
                    bottom: 3px;
                  }
                }
                .td_block_slide .td-slide-meta a,
                .td_block_slide .td-slide-meta span {
                  color: #fff;
                }
                .td_block_slide .entry-title {
                  margin: 5px 0;
                }
                .td_block_slide .entry-review-stars {
                  margin-right: 22px;
                  top: 0;
                }
                .td_block_slide .td-post-date {
                  color: #fff;
                  margin-left: 4px;
                }
                .td_block_slide .td-post-views {
                  display: inline-block;
                  vertical-align: top;
                  margin-right: 22px;
                  line-height: 15px;
                }
                .td_block_slide .td-icon-views {
                  position: relative;
                  line-height: 17px;
                  font-size: 14px;
                  margin-right: 5px;
                  vertical-align: top;
                }
                .td_block_slide .td-post-comments {
                  position: relative;
                  top: 2px;
                  display: inline-block;
                  vertical-align: top;
                  margin-left: 10px;
                }
                .td_block_slide .td-icon-comments {
                  margin-right: 5px;
                  font-size: 9px;
                  position: relative;
                  top: 1px;
                }
                .td_block_slide .td-slide-nav {
                  padding: 20px;
                  position: absolute;
                  display: block;
                  height: 80px;
                  margin-top: -40px;
                  top: 50%;
                  font-size: 38px;
                  color: #fff;
                  opacity: 0;
                  -webkit-transition: opacity 0.4s;
                  transition: opacity 0.4s;
                }
                .td_block_slide .td-slide-nav-svg {
                  display: inline-flex;
                  align-items: center;
                  justify-content: center;
                }
                .td_block_slide .td-slide-nav-svg svg {
                  width: 38px;
                  height: auto;
                }
                .td_block_slide .td-slide-nav-svg svg,
                .td_block_slide .td-slide-nav-svg svg * {
                  fill: #fff;
                }
                .td_block_slide .prevButton {
                  left: 0;
                }
                .td_block_slide .nextButton {
                  right: 0;
                }
                .td_block_slide .td_module_wrap:hover .entry-title a {
                  color: #fff;
                }
                .td-ss-main-sidebar .td_block_slide,
                .td-ss-row .td-pb-span4 .td_block_slide {
                  overflow: visible;
                }
                .td-theme-slider .slide-meta-cat a {
                  font-family: var(--td_default_google_font_2, 'Roboto', sans-serif);
                  font-size: 12px;
                  font-weight: 500;
                  text-transform: uppercase;
                  display: inline-block;
                  margin: 0 0 5px 0;
                  padding: 4px 7px 3px;
                  line-height: 14px;
                  background-color: rgba(0, 0, 0, 0.7);
                  -webkit-transition: background-color 0.3s ease;
                  transition: background-color 0.3s ease;
                }
                @media (max-width: 767px) {
                  .td-theme-slider .slide-meta-cat a {
                    font-size: 10px;
                    padding: 2px 5px 2px;
                    margin-bottom: 0;
                    line-height: 13px;
                  }
                }
                .td-theme-slider:hover .td-slide-nav {
                  opacity: 1;
                  z-index: 1;
                }
                .td-theme-slider:hover .slide-meta-cat a {
                  background-color: var(--td_theme_color, #4db2ec);
                }
                @-moz-document url-prefix() {
                  .td-theme-slider .slide-meta-cat a {
                    padding: 3px 7px 4px;
                  }
                  @media (max-width: 767px) {
                    .td-theme-slider .slide-meta-cat a {
                      line-height: 12px;
                    }
                  }
                }
                .iosSlider-col-3,
                .iosSlider-col-3 .td_module_slide {
                  height: 580px;
                }
                @media (min-width: 1019px) and (max-width: 1140px) {
                  .iosSlider-col-3,
                  .iosSlider-col-3 .td_module_slide {
                    height: 532px;
                  }
                }
                @media (min-width: 768px) and (max-width: 1018px) {
                  .iosSlider-col-3,
                  .iosSlider-col-3 .td_module_slide {
                    height: 402px;
                  }
                }
                @media (max-width: 767px) {
                  .iosSlider-col-3,
                  .iosSlider-col-3 .td_module_slide {
                    height: 298px;
                  }
                }
                @media (max-width: 500px) {
                  .iosSlider-col-3,
                  .iosSlider-col-3 .td_module_slide {
                    height: 163px;
                  }
                }
                .iosSlider-col-3 .entry-title {
                  font-size: 48px;
                  line-height: 58px;
                }
                @media (min-width: 1019px) and (max-width: 1140px) {
                  .iosSlider-col-3 .entry-title {
                    font-size: 42px;
                    line-height: 52px;
                  }
                }
                @media (min-width: 768px) and (max-width: 1018px) {
                  .iosSlider-col-3 .entry-title {
                    font-size: 34px;
                    line-height: 44px;
                  }
                }
                @media (max-width: 767px) {
                  .iosSlider-col-3 .entry-title {
                    font-size: 26px;
                    line-height: 32px;
                  }
                }
                @media (max-width: 500px) {
                  .iosSlider-col-3 .entry-title {
                    font-size: 18px;
                    line-height: 24px;
                  }
                }
                .iosSlider-col-2,
                .iosSlider-col-2 .td_module_slide {
                  height: 385px;
                }
                @media (min-width: 1019px) and (max-width: 1140px) {
                  .iosSlider-col-2,
                  .iosSlider-col-2 .td_module_slide {
                    height: 354px;
                  }
                }
                @media (min-width: 768px) and (max-width: 1018px) {
                  .iosSlider-col-2,
                  .iosSlider-col-2 .td_module_slide {
                    height: 268px;
                  }
                }
                @media (max-width: 767px) {
                  .iosSlider-col-2,
                  .iosSlider-col-2 .td_module_slide {
                    height: 303px;
                  }
                }
                @media (max-width: 500px) {
                  .iosSlider-col-2,
                  .iosSlider-col-2 .td_module_slide {
                    height: 166px;
                  }
                }
                .iosSlider-col-2 .entry-title {
                  font-size: 26px;
                  line-height: 32px;
                }
                @media (min-width: 768px) and (max-width: 1018px) {
                  .iosSlider-col-2 .entry-title {
                    font-size: 22px;
                    line-height: 28px;
                  }
                }
                @media (max-width: 500px) {
                  .iosSlider-col-2 .entry-title {
                    font-size: 18px;
                    line-height: 24px;
                  }
                }
                .td_block_slide .iosSlider-col-1,
                .td_block_slide .iosSlider-col-1 .td_module_slide {
                  height: 385px;
                }
                @media (min-width: 1019px) and (max-width: 1140px) {
                  .td_block_slide .iosSlider-col-1,
                  .td_block_slide .iosSlider-col-1 .td_module_slide {
                    height: 354px;
                  }
                }
                @media (min-width: 768px) and (max-width: 1018px) {
                  .td_block_slide .iosSlider-col-1,
                  .td_block_slide .iosSlider-col-1 .td_module_slide {
                    height: 268px;
                  }
                }
                @media (max-width: 767px) {
                  .td_block_slide .iosSlider-col-1,
                  .td_block_slide .iosSlider-col-1 .td_module_slide {
                    height: 303px;
                  }
                }
                @media (max-width: 500px) {
                  .td_block_slide .iosSlider-col-1,
                  .td_block_slide .iosSlider-col-1 .td_module_slide {
                    height: 200px;
                  }
                }
                .td_block_slide .iosSlider-col-1 .entry-title {
                  font-size: 18px;
                  line-height: 24px;
                }
                @media (max-width: 767px) {
                  .td_block_slide .iosSlider-col-1 .entry-title {
                    font-size: 26px;
                    line-height: 32px;
                  }
                }
                @media (max-width: 500px) {
                  .td_block_slide .iosSlider-col-1 .entry-title {
                    font-size: 18px;
                    line-height: 24px;
                  }
                }
                .td_block_slide .td_module_slide {
                  visibility: hidden !important;
                }
                .td_block_slide .td_module_slide:first-child,
                .td-js-loaded .td_block_slide .td_module_slide {
                  visibility: visible !important;
                }

                
                
                /* @content_width */
                .$unique_block_class .td-slide-meta {
                    max-width: calc(@content_width + 44px);
                }
                @media (max-width: 767px) {
                    .$unique_block_class .td-slide-meta {
                        max-width: calc(@content_width + 24px);
                    }
                }
                
				/* @image_alignment */
				.$unique_block_class .entry-thumb {
				    background-position: center @image_alignment;
				}
				/* @image_height */
				.$unique_block_class .td_block_inner,
				.$unique_block_class .td-theme-slider,
				.$unique_block_class .td_module_slide {
				    height: @image_height !important;
				}
				
				/* @meta_info_horiz_center */
				.$unique_block_class .td-slide-meta {
					text-align: center;
				}
				/* @meta_info_horiz_right */
				.$unique_block_class .td-slide-meta {
					text-align: right;
				}
			
				/* @meta_info_vert_top */
				.$unique_block_class .td-slide-meta {
					top: 0;
					bottom: auto;
				}
				/* @meta_info_vert_center */
				.$unique_block_class .td-slide-meta {
					top: 50%;
					bottom: auto;
					transform: translateY(-50%);
				}
				/* @meta_info_vert_bottom */
				.$unique_block_class .td-slide-meta {
					bottom: 0;
				}
				
				/* @meta_padding */
				.$unique_block_class .td-slide-meta {
					padding: @meta_padding;
				}
				
				/* @art_title_space */
				.$unique_block_class .entry-title {
					margin: @art_title_space;
				}
				
				/* @category_margin */
				.$unique_block_class .slide-meta-cat a {
					margin: @category_margin;
				}
				/* @category_padding */
				.$unique_block_class .slide-meta-cat a {
					padding: @category_padding;
				}
				/* @category_radius */
				.$unique_block_class .slide-meta-cat a {
					border-radius: @category_radius;
				}
				
				/* @show_cat */
				.$unique_block_class .slide-meta-cat {
					display: @show_cat;
				}
				/* @show_author */
				.$unique_block_class .td-post-author-name {
					display: @show_author;
				}
				/* @show_date */
				.$unique_block_class .td-post-date,
				.$unique_block_class .td-post-author-name span {
					display: @show_date;
				}
				/* @show_com */
				.$unique_block_class .td-post-comments {
					display: @show_com;
				}
				/* @hide_author_date */
				.$unique_block_class .td-module-meta-info {
					display: none;
				}
				
				
				/* @nav_icon_size */
				.$unique_block_class .td-slide-nav {
				    font-size: @nav_icon_size;
				}
				.$unique_block_class .td-slide-nav-svg svg {
				    width: @nav_icon_size;
				}
                
                
                /* @excl_show */
                .$unique_block_class .td-module-exclusive .td-module-title a:before {
                    display: @excl_show;
                }
                /* @excl_txt */
                .$unique_block_class .td-module-exclusive .td-module-title a:before {
                    content: '@excl_txt';
                }
                /* @excl_margin */
                .$unique_block_class .td-module-exclusive .td-module-title a:before {
                    margin: @excl_margin;
                }
                /* @excl_padd */
                .$unique_block_class .td-module-exclusive .td-module-title a:before {
                    padding: @excl_padd;
                }
                /* @all_excl_border */
                .$unique_block_class .td-module-exclusive .td-module-title a:before {
                    border: @all_excl_border @all_excl_border_style @all_excl_border_color;
                }
                /* @excl_radius */
                .$unique_block_class .td-module-exclusive .td-module-title a:before {
                    border-radius: @excl_radius;
                }
                /* @excl_color */
                .$unique_block_class .td-module-exclusive .td-module-title a:before {
                    color: @excl_color;
                }
                /* @excl_color_h */
                .$unique_block_class .td-module-exclusive:hover .td-module-title a:before {
                    color: @excl_color_h;
                }
                /* @excl_bg */
                .$unique_block_class .td-module-exclusive .td-module-title a:before {
                    background-color: @excl_bg;
                }
                /* @excl_bg_h */
                .$unique_block_class .td-module-exclusive:hover .td-module-title a:before {
                    background-color: @excl_bg_h;
                }
                /* @excl_border_color_h */
                .$unique_block_class .td-module-exclusive:hover .td-module-title a:before {
                    border-color: @excl_border_color_h;
                }
                /* @f_excl */
                .$unique_block_class .td-module-exclusive .td-module-title a:before {
                    @f_excl
                }
				
				
				/* @overlay */
				.$unique_block_class .td-image-gradient:before {
					background: @overlay;
				}
				/* @overlay_gradient */
				.$unique_block_class .td-image-gradient:before {
					@overlay_gradient
				}
				/* @overlay_h */
				.$unique_block_class .td-image-gradient:hover:before {
					background: @overlay_h;
				}
				/* @overlay_gradient_h */
				.$unique_block_class .td-image-gradient:hover:before {
					@overlay_gradient_h
				}
				
				/* @title_color */
				.$unique_block_class .td-module-title a {
					color: @title_color;
				}
				
				/* @cat_bg */
				.$unique_block_class span.slide-meta-cat a {
					background-color: @cat_bg;
				}
				/* @cat_bg_hover */
				.$unique_block_class .td_module_slide:hover span.slide-meta-cat a,
				.$unique_block_class .td-theme-slider:hover .slide-meta-cat a {
					background-color: @cat_bg_hover;
				}
				/* @cat_txt */
				.$unique_block_class span.slide-meta-cat a {
					color: @cat_txt;
				}
				/* @cat_txt_hover */
				.$unique_block_class .td_module_slide:hover span.slide-meta-cat a {
					color: @cat_txt_hover;
				}
				
				
				/* @author_txt */
				.$unique_block_class .td-post-author-name a {
					color: @author_txt;
				}
				/* @author_txt_hover */
				.$unique_block_class .td_module_slide:hover .td-post-author-name a {
					color: @author_txt_hover;
				}
				/* @date_txt */
				.$unique_block_class span.td-post-date,
				.$unique_block_class .td-post-author-name span {
					color: @date_txt;
				}
				/* @comm_txt */
				.$unique_block_class .td-post-comments i,
				.$unique_block_class .td-post-comments a {
				    color: @comm_txt;
				}
				/* @review_stars */
				.$unique_block_class .entry-review-stars {
				    color: @review_stars;
				}
				
				/* @nav_icons_color */
				.$unique_block_class .td-slide-nav {
				    color: @nav_icons_color;
				}
				.$unique_block_class .td-slide-nav-svg svg,
				.$unique_block_class .td-slide-nav-svg svg * {
				    fill: @nav_icons_color;
				}



				/* @f_header */
				.$unique_block_class .td-block-title a,
				.$unique_block_class .td-block-title span {
					@f_header
				}
				/* @f_ajax */
				.$unique_block_class .td-subcat-list a,
				.$unique_block_class .td-subcat-dropdown span,
				.$unique_block_class .td-subcat-dropdown a {
					@f_ajax
				}
				/* @msf_title */
				.$unique_block_class .td_module_slide .entry-title {
					@msf_title
				}
				/* @msf_cat */
				.$unique_block_class .td_module_slide .slide-meta-cat a {
					@msf_cat
				}
				/* @msf_meta */
				.$unique_block_class .td_module_slide .td-module-meta-info,
				.$unique_block_class .td_module_slide .td-module-comments a {
					@msf_meta
				}
				
				/* @mix_type */
                .$unique_block_class .entry-thumb:before {
                    content: '';
                    width: 100%;
                    height: 100%;
                    position: absolute;
                    opacity: 1;
                    transition: opacity 1s ease;
                    -webkit-transition: opacity 1s ease;
                    mix-blend-mode: @mix_type;
                }
                /* @color */
                .$unique_block_class .entry-thumb:before {
                    background: @color;
                }
                /* @mix_gradient */
                .$unique_block_class .entry-thumb:before {
                    @mix_gradient;
                }
                
                
                /* @mix_type_h */
                @media (min-width: 1141px) {
                    .$unique_block_class .entry-thumb:after {
                        content: '';
                        width: 100%;
                        height: 100%;
                        position: absolute;
                        opacity: 0;
                        transition: opacity 1s ease;
                        -webkit-transition: opacity 1s ease;
                        mix-blend-mode: @mix_type_h;
                    }
                    .$unique_block_class .td-theme-slider:hover .entry-thumb:after {
                        opacity: 1;
                    }
                }
                
                /* @color_h */
                .$unique_block_class .entry-thumb:after {
                    background: @color_h;
                }
                /* @mix_gradient_h */
                .$unique_block_class .entry-thumb:after {
                    @mix_gradient_h;
                }
                /* @mix_type_off */
                .$unique_block_class .td-theme-slider:hover .entry-thumb:before {
                    opacity: 0;
                }
                    
                /* @effect_on */
                .$unique_block_class .entry-thumb {
                    filter: @fe_brightness @fe_contrast @fe_saturate;
                    transition: all 1s ease;
                    -webkit-transition: all 1s ease;
                }
                /* @effect_on_h */
                @media (min-width: 1141px) {
                    .$unique_block_class .td-theme-slider:hover .entry-thumb {
                        filter: @fe_brightness_h @fe_contrast_h @fe_saturate_h;
                    }
                }
			</style>";


        $td_css_res_compiler = new td_css_res_compiler( $raw_css );
        $td_css_res_compiler->load_settings( __CLASS__ . '::cssMedia', $this->get_all_atts() );

        $compiled_css .= $td_css_res_compiler->compile_css();

        return $compiled_css;
    }

    function render( $atts, $content = null ){

        parent::render($atts); // sets the live atts, $this->atts, $this->block_uid, $this->td_query (it runs the query)

        extract(shortcode_atts( array( 'autoplay' => '' ), $atts ) );

        $buffy = ''; //output buffer

        if ( $this->td_query->have_posts() and $this->td_query->found_posts >= 1 ) {

            $buffy .= '<div class="' . $this->get_block_classes() . '" ' . $this->get_block_html_atts() . '>';

		        //get the block js
		        $buffy .= $this->get_block_css();

		        //get the js for this block
		        $buffy .= $this->get_block_js();

                // block title wrap
                $buffy .= '<div class="td-block-title-wrap">';
                    $buffy .= $this->get_block_title(); //get the block title
                    $buffy .= $this->get_pull_down_filter(); //get the sub category filter for this block
                $buffy .= '</div>';

                $buffy .= '<div id=' . $this->block_uid . ' class="td_block_inner">';
                    $buffy .= $this->inner($this->td_query->posts, '' , $autoplay);
                $buffy .= '</div>';
            $buffy .= '</div> <!-- ./block1 -->';

        } else if ( td_util::tdc_is_live_editor_iframe() or td_util::tdc_is_live_editor_ajax() ) {
	        $buffy .= '<div class="td_block_wrap tdc-no-posts"><div class="td_block_inner"></div></div>';
        }
        return $buffy;
    }

	/**
	 * @param $posts
	 * @param string $td_column_number - get the column number
	 * @param string $autoplay - not use via ajax
	 * @param bool $is_ajax - if true the script will return the js inline, if not, it will use the td_js_buffer class
	 *
	 * @return string
	 */
    function inner( $posts, $td_column_number = '', $autoplay = '', $is_ajax = false ) {
        $buffy = '';

        if ( empty( $td_column_number ) ) {
            $td_column_number = td_global::vc_get_column_number(); // get the column width of the block from the page builder API
        }

        $td_post_count = 0; // the number of posts rendered

        $td_unique_id_slide = td_global::td_generate_unique_id();

        $prev_icon = $this->get_icon_att('prev_tdicon');
        $prev_icon_data = '';
        if( td_util::tdc_is_live_editor_iframe() || td_util::tdc_is_live_editor_ajax() ) {
            $prev_icon_data = 'data-td-svg-icon="' . $this->get_att('prev_tdicon') . '"';
        }
        if( $prev_icon == '' ) {
            $prev_icon = '<i class="td-slide-nav td-icon-left prevButton"></i>';
        } else {
            if( base64_encode( base64_decode( $prev_icon ) ) == $prev_icon ) {
                $prev_icon = '<span class="td-slide-nav td-slide-nav-svg prevButton" ' . $prev_icon_data . '>' . base64_decode( $prev_icon ) . '</span>';
            } else {
                $prev_icon = '<i class="td-slide-nav ' . $prev_icon . ' prevButton"></i>';
            }
        }
        $next_icon = $this->get_icon_att('next_tdicon');
        $next_icon_data = '';
        if( td_util::tdc_is_live_editor_iframe() || td_util::tdc_is_live_editor_ajax() ) {
            $next_icon_data = 'data-td-svg-icon="' . $this->get_att('next_tdicon') . '"';
        }
        if( $next_icon == '' ) {
            $next_icon = '<i class="td-slide-nav td-icon-right nextButton"></i>';
        } else {
            if( base64_encode( base64_decode( $next_icon ) ) == $next_icon ) {
                $next_icon = '<span class="td-slide-nav td-slide-nav-svg nextButton" ' . $next_icon_data . '>' . base64_decode( $next_icon ) . '</span>';
            } else {
                $next_icon = '<i class="td-slide-nav ' . $next_icon . ' nextButton"></i>';
            }
        }

        //@generic class for sliders : td-theme-slider
        $buffy .= '<div id="' . $td_unique_id_slide . '" class="td-theme-slider iosSlider-col-' . $td_column_number . ' td_mod_wrap">';
            $buffy .= '<div class="td-slider ">';
                if ( !empty( $posts ) ) {
                    foreach ( $posts as $post ) {
                        //$buffy .= td_modules::mod_slide_render($post, $td_column_number, $td_post_count);
                        $td_module_slide = new td_module_slide($post, $this->get_all_atts());
                        $buffy .= $td_module_slide->render($td_column_number, $td_post_count, $td_unique_id_slide);
                        $td_post_count++;

	                    // Show only the first frame in tagDiv composer
	                    if ( td_util::tdc_is_live_editor_iframe() or td_util::tdc_is_live_editor_ajax() ) {
		                    break;
	                    }
                    }
                }
            $buffy .= '</div>'; // close slider

            $buffy .= $prev_icon;
            $buffy .= $next_icon;

        $buffy .= '</div>'; // close ios

	    // Suppress any iosSlider in tagDiv composer
	    if ( td_util::tdc_is_live_editor_iframe() or td_util::tdc_is_live_editor_ajax() ) {
		    return $buffy;
	    }

        if ( !empty( $autoplay ) ) {
            $autoplay_string =  '
            autoSlide: true,
            autoSlideTimer: ' . $autoplay * 1000 . ',
            ';
        } else {
            $autoplay_string = '';
        }

        // add resize events
        //$add_js_resize = '';
        //if( $td_column_number > 1 ) {
            $add_js_resize = ',
                //onSliderLoaded : td_resize_normal_slide,
                //onSliderResize : td_resize_normal_slide_and_update';
        //}

        $slide_js = '
jQuery(document).ready(function() {
    jQuery("#' . $td_unique_id_slide . '").iosSlider({
        snapToChildren: true,
        desktopClickDrag: true,
        keyboardControls: false,
        responsiveSlideContainer: true,
        responsiveSlides: true,
        ' . $autoplay_string. '

        infiniteSlider: true,
        navPrevSelector: jQuery("#' . $td_unique_id_slide . ' .prevButton"),
        navNextSelector: jQuery("#' . $td_unique_id_slide . ' .nextButton")
        ' . $add_js_resize . '
    });
});
    ';

        if ($is_ajax) {
            $buffy .= '<script>' . $slide_js . '</script>';
        } else {
            td_js_buffer::add_to_footer($slide_js);
        }

        return $buffy;
    }

}