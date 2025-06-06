<?php

/**
 * Class td_single_date
 */

class tdb_header_logo extends td_block {

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
                
                /* @style_general_header_logo */
                .tdb_header_logo {
                  margin-bottom: 0;
                  clear: none;
                }
                .tdb_header_logo .tdb-logo-a,
                .tdb_header_logo h1 {
                  display: flex;
                  pointer-events: auto;
                  align-items: flex-start;
                }
                .tdb_header_logo h1 {
                  margin: 0;
                  line-height: 0;
                }
                .tdb_header_logo .tdb-logo-img-wrap img {
                  display: block;
                }
                .tdb_header_logo .tdb-logo-svg-wrap + .tdb-logo-img-wrap {
                  display: none;
                }
                .tdb_header_logo .tdb-logo-svg-wrap svg {
                  width: 50px;
                  display: block;
                  transition: fill .3s ease;
                }
                .tdb_header_logo .tdb-logo-text-wrap {
                  display: flex;
                }
                .tdb_header_logo .tdb-logo-text-title,
                .tdb_header_logo .tdb-logo-text-tagline {
                  -webkit-transition: all 0.2s ease;
                  transition: all 0.2s ease;
                }
                .tdb_header_logo .tdb-logo-text-title {
                  background-size: cover;
                  background-position: center center;
                  font-size: 75px;
                  font-family: serif;
                  line-height: 1.1;
                  color: #222;
                  white-space: nowrap;
                }
                @media (max-width: 767px) {
                  .tdb_header_logo .tdb-logo-text-title {
                    font-size: 36px;
                  }
                }
                .tdb_header_logo .tdb-logo-text-tagline {
                  margin-top: 2px;
                  font-size: 12px;
                  font-family: serif;
                  letter-spacing: 1.8px;
                  line-height: 1;
                  color: #767676;
                }
                @media (max-width: 767px) {
                  .tdb_header_logo .tdb-logo-text-tagline {
                    font-size: 11px;
                  }
                }
                .tdb_header_logo .tdb-logo-icon {
                  position: relative;
                  font-size: 46px;
                  color: #000;
                }
                .tdb_header_logo .tdb-logo-icon-svg {
                  line-height: 0;
                }
                .tdb_header_logo .tdb-logo-icon-svg svg {
                  width: 46px;
                  height: auto;
                }
                .tdb_header_logo .tdb-logo-icon-svg svg,
                .tdb_header_logo .tdb-logo-icon-svg svg * {
                  fill: #000;
                }
                
                /* @inline */
                .$unique_block_class {
                    display: inline-block;
                }
                /* @display */
                .$unique_block_class .tdb-logo-a,
                .$unique_block_class h1 {
                    flex-direction: @display;
                }
                /* @float_right */
                .$unique_block_class {
                    float: right;
                    clear: none;
                }
                
                /* @align_vert_top */
                .$unique_block_class .tdb-logo-a,
                .$unique_block_class h1 {
                   align-items: flex-start;
                }
                /* @align_vert_center */
                .$unique_block_class .tdb-logo-a,
                .$unique_block_class h1 {
                    align-items: center;
                }
                /* @align_vert_bottom */
                .$unique_block_class .tdb-logo-a,
                .$unique_block_class h1 {
                    align-items: flex-end;
                }
                
                /* @align_horiz_left */
                .$unique_block_class .tdb-logo-a,
                .$unique_block_class h1 {
                    align-items: flex-start;
                }
                /* @align_horiz_center */
                .$unique_block_class .tdb-logo-a,
                .$unique_block_class h1 {
                    align-items: center;
                }
                /* @align_horiz_right */
                .$unique_block_class .tdb-logo-a,
                .$unique_block_class h1 {
                    align-items: flex-end;
                }
                
                /* @align_horiz_left2 */
                .$unique_block_class .tdb-logo-a,
                .$unique_block_class h1 {
                    justify-content: flex-start;
                }
                /* @align_horiz_center2 */
                .$unique_block_class .tdb-logo-a,
                .$unique_block_class h1 {
                    justify-content: center;
                }
                /* @align_horiz_right2 */
                .$unique_block_class .tdb-logo-a,
                .$unique_block_class h1 {
                    justify-content: flex-end;
                }
                
                
                
                /* @svg_width */
                .$unique_block_class .tdb-logo-svg-wrap {
                    max-width: @svg_width;
                }
                .$unique_block_class .tdb-logo-svg-wrap svg {
                    width: @svg_width !important;
                    height: auto;
                }
                
                /* @svg_space_top */
                .$unique_block_class .tdb-logo-svg-wrap {
                    margin-top: @svg_space_top;
                }
                .$unique_block_class .tdb-logo-svg-wrap:first-child {
                    margin-top: 0;
                }
                /* @svg_space_bottom */
                .$unique_block_class .tdb-logo-svg-wrap {
                    margin-bottom: @svg_space_bottom;
                }
                .$unique_block_class .tdb-logo-svg-wrap:last-child {
                    margin-bottom: 0;
                }
                /* @svg_space_right */
                .$unique_block_class .tdb-logo-svg-wrap {
                    margin-right: @svg_space_right;
                }
                .$unique_block_class .tdb-logo-svg-wrap:last-child {
                    margin-right: 0;
                }
                /* @svg_space_left */
                .$unique_block_class .tdb-logo-svg-wrap {
                    margin-left: @svg_space_left;
                }
                .$unique_block_class .tdb-logo-svg-wrap:first-child {
                    margin-left: 0;
                }
                
                /* @show_svg */
                .$unique_block_class .tdb-logo-svg-wrap {
                    display: @show_svg;
                }
                /* @show_img_not_svg */
                .$unique_block_class .tdb-logo-svg-wrap + .tdb-logo-img-wrap {
                    display: block;
                }
                /* @show_svg_not_img */
                .$unique_block_class .tdb-logo-svg-wrap + .tdb-logo-img-wrap {
                    display: none;
                }
                
                
                
                /* @image_width */
                .$unique_block_class .tdb-logo-img {
                    max-width: @image_width;
                }
                
                /* @img_space_top */
                .$unique_block_class .tdb-logo-img-wrap {
                    margin-top: @img_space_top;
                }
                .$unique_block_class .tdb-logo-img-wrap:first-child {
                    margin-top: 0;
                }
                /* @img_space_bottom */
                .$unique_block_class .tdb-logo-img-wrap {
                    margin-bottom: @img_space_bottom;
                }
                .$unique_block_class .tdb-logo-img-wrap:last-child {
                    margin-bottom: 0;
                }
                /* @img_space_right */
                .$unique_block_class .tdb-logo-img-wrap {
                    margin-right: @img_space_right;
                }
                .$unique_block_class .tdb-logo-img-wrap:last-child {
                    margin-right: 0;
                }
                /* @img_space_left */
                .$unique_block_class .tdb-logo-img-wrap {
                    margin-left: @img_space_left;
                }
                .$unique_block_class .tdb-logo-img-wrap:first-child {
                    margin-left: 0;
                }
                
                /* @show_image */
                .$unique_block_class .tdb-logo-img-wrap {
                    display: @show_image;
                }
                
                
                
                /* @ttl_tag_space_top */
                .$unique_block_class .tdb-logo-text-tagline {
                    margin-top: @ttl_tag_space_top;
                    margin-left: 0;
                }
                /* @ttl_tag_space_left */
                .$unique_block_class .tdb-logo-text-tagline {
                    margin-top: 0;
                    margin-left: @ttl_tag_space_left;
                }
                
                /* @show_title */
                .$unique_block_class .tdb-logo-text-title {
                    display: @show_title;
                }
                /* @title_wrap */
                .$unique_block_class .tdb-logo-text-title {
                    white-space: normal;
                }
                /* @show_tagline */
                .$unique_block_class .tdb-logo-text-tagline {
                    display: @show_tagline;
                }
                /* @tagline_pos_under */
                .$unique_block_class .tdb-logo-text-wrap {
                    flex-direction: column;
                }
                /* @tagline_pos_inline */
                .$unique_block_class .tdb-logo-text-wrap {
                    flex-direction: row;
                    align-items: baseline;
                }
                
                /* @tagline_align_horiz_left */
                .$unique_block_class .tdb-logo-text-wrap {
                    align-items: flex-start;
                }
                /* @tagline_align_horiz_center */
                .$unique_block_class .tdb-logo-text-wrap {
                    align-items: center;
                }
                /* @tagline_align_horiz_right */
                .$unique_block_class .tdb-logo-text-wrap {
                    align-items: flex-end;
                }
                
                /* @tagline_align_vert_top */
                .$unique_block_class .tdb-logo-text-wrap {
                    align-items: flex-start;
                }
                /* @tagline_align_vert_center */
                .$unique_block_class .tdb-logo-text-wrap {
                    align-items: center;
                }
                
                
                
                /* @icon_size */
                .$unique_block_class .tdb-logo-icon {
                    font-size: @icon_size;
                }
                /* @icon_svg_size */
                .$unique_block_class .tdb-logo-icon-svg svg {
                    width: @icon_svg_size;
                }
                
                /* @icon_space_right */
                .$unique_block_class .tdb-logo-icon {
                    margin-right: @icon_space_right;
                }
                .$unique_block_class .tdb-logo-icon:last-child {
                    margin-right: 0;
                }
                /* @icon_space_bottom */
                .$unique_block_class .tdb-logo-icon {
                    margin-bottom: @icon_space_bottom;
                }
                .$unique_block_class .tdb-logo-icon:last-child {
                    margin-bottom: 0;
                }
                
                /* @icon_space_left */
                .$unique_block_class .tdb-logo-icon {
                    margin-left: @icon_space_left;
                }
                .$unique_block_class .tdb-logo-icon:first-child {
                    margin-left: 0;
                }
                /* @icon_space_top */
                .$unique_block_class .tdb-logo-icon {
                    margin-top: @icon_space_top;
                }
                .$unique_block_class .tdb-logo-icon:first-child {
                    margin-top: 0;
                }
                
                /* @icon_space_top_bottom */
                .$unique_block_class .tdb-logo-icon {
                    margin-top: @icon_space_top_bottom;
                    margin-bottom: @icon_space_top_bottom;
                }
                .$unique_block_class .tdb-logo-icon:first-child {
                    margin-top: 0;
                }
                .$unique_block_class .tdb-logo-icon:last-child {
                    margin-bottom: 0;
                }
                /* @icon_space_left_right */
                .$unique_block_class .tdb-logo-icon {
                    margin-left: @icon_space_left_right;
                    margin-right: @icon_space_left_right;
                }
                .$unique_block_class .tdb-logo-icon:first-child {
                    margin-left: 0;
                }
                .$unique_block_class .tdb-logo-icon:last-child {
                    margin-right: 0;
                }
                /* @icon_padd */
                .$unique_block_class .tdb-logo-icon {
                    padding: @icon_padd;
                }
                /* @icon_radius */
                .$unique_block_class .tdb-logo-icon {
                    border-radius: @icon_radius;
                }
                
                /* @icon_align */
                .$unique_block_class .tdb-logo-icon {
                    top: @icon_align;
                }
                
                /* @show_icon */
                .$unique_block_class .tdb-logo-icon {
                    display: @show_icon;
                }
                
                
                
                /* @svg_color */
                .$unique_block_class .tdb-logo-svg-wrap > * {
                    fill: @svg_color;
                }
				/* @svg_color_h */
                .$unique_block_class .tdb-logo-a:hover .tdb-logo-svg-wrap > * {
                    fill: @svg_color_h;
                }
                
                /* @text_bg */
                .$unique_block_class .tdb-logo-text-title {
                    background-image: url(@text_bg);
					-webkit-background-clip: text;
					-webkit-text-fill-color: transparent;
                }
				html[class*='ie'] .$unique_block_class .tdb-logo-text-title {
				    background: none;
				}
                /* @text_bg_h */
                .$unique_block_class .tdb-logo-a:hover .tdb-logo-text-title {
                    background-image: url(@text_bg_h);
					-webkit-background-clip: text;
					-webkit-text-fill-color: transparent;
                }
				html[class*='ie'] .$unique_block_class .tdb-logo-a:hover .tdb-logo-text-title {
				    background: none;
				}
				
				/* @text_color_solid */
                .$unique_block_class .tdb-logo-text-title {
                    color: @text_color_solid;
                }
                /* @text_color_gradient */
                .$unique_block_class .tdb-logo-text-title {
                     @text_color_gradient
					-webkit-background-clip: text;
					-webkit-text-fill-color: transparent;
                }
				html[class*='ie'] .$unique_block_class .tdb-logo-text-title {
				    background: none;
					color: @text_color_gradient_1;
				}
				/* @text_color_h */
                .$unique_block_class .tdb-logo-a:hover .tdb-logo-text-title {
                    color: @text_color_h;
                    background: none;
                    -webkit-text-fill-color: initial;
                    background-position: center center;
                }
                
                /* @tagline_bg */
                .$unique_block_class .tdb-logo-text-tagline {
                    background-image: url(@tagline_bg);
					-webkit-background-clip: text;
					-webkit-text-fill-color: transparent;
                }
				html[class*='ie'] .$unique_block_class .tdb-logo-text-tagline {
				    background: none;
				}
                /* @tagline_bg_h */
                .$unique_block_class .tdb-logo-a:hover .tdb-logo-text-tagline {
                    background-image: url(@tagline_bg_h);
					-webkit-background-clip: text;
					-webkit-text-fill-color: transparent;
                }
				html[class*='ie'] .$unique_block_class .tdb-logo-a:hover .tdb-logo-text-tagline {
				    background: none;
				}
				
                /* @tagline_color_solid */
                .$unique_block_class .tdb-logo-text-tagline {
                    color: @tagline_color_solid;
                }
                /* @tagline_color_gradient */
                .$unique_block_class .tdb-logo-text-tagline {
                     @tagline_color_gradient
					-webkit-background-clip: text;
					-webkit-text-fill-color: transparent;
                }
				html[class*='ie'] .$unique_block_class .tdb-logo-text-tagline {
				    background: none;
					color: @tagline_color_gradient_1;
				}
				/* @tagline_color_h */
                .$unique_block_class .tdb-logo-a:hover .tdb-logo-text-tagline {
                    color: @tagline_color_h;
                    background: none;
                    -webkit-text-fill-color: initial;
                    background-position: center center;
                }
				
                /* @icon_color_solid */
                .$unique_block_class .tdb-logo-icon {
                    color: @icon_color_solid;
                }
                .$unique_block_class .tdb-logo-icon-svg svg,
                .$unique_block_class .tdb-logo-icon-svg svg * {
                    fill: @icon_color_solid;
                }
                /* @icon_color_gradient */
                .$unique_block_class .tdb-logo-icon {
                     @icon_color_gradient;
					-webkit-background-clip: text;
					-webkit-text-fill-color: transparent;
                }
                .$unique_block_class .tdb-logo-icon-svg svg,
                .$unique_block_class .tdb-logo-icon-svg svg * {
                    fill: @icon_color_gradient_1;
                }
				/* @icon_color_h */
                .$unique_block_class .tdb-logo-a:hover .tdb-logo-icon {
                    color: @icon_color_h;
                    background: none;
                    -webkit-text-fill-color: initial;
                }
                .$unique_block_class .tdb-logo-a:hover .tdb-logo-icon-svg svg,
                .$unique_block_class .tdb-logo-a:hover .tdb-logo-icon-svg svg * {
                    fill: @icon_color_h;
                }
                /* @icon_bg */
                .$unique_block_class .tdb-logo-icon {
                    background-color: @icon_bg;
                }
                /* @icon_bg_h */
                .$unique_block_class .tdb-logo-a:hover .tdb-logo-icon {
                    background-color: @icon_bg_h;
                }
				
				
				
                /* @f_text */
                .$unique_block_class .tdb-logo-text-title {
                    @f_text;
                }
                /* @f_tagline */
                .$unique_block_class .tdb-logo-text-tagline {
                    @f_tagline;
                }
				
			</style>";


        $td_css_res_compiler = new td_css_res_compiler( $raw_css );
        $td_css_res_compiler->load_settings( __CLASS__ . '::cssMedia', $this->get_all_atts() );

        $compiled_css .= $td_css_res_compiler->compile_css();
        return $compiled_css;
    }

    static function cssMedia( $res_ctx ) {

        $res_ctx->load_settings_raw( 'style_general_header_logo', 1 );
        $res_ctx->load_settings_raw( 'style_general_header_align', 1 );

        // display inline
        $res_ctx->load_settings_raw( 'inline', $res_ctx->get_shortcode_att('inline') );
        // display
        $display = $res_ctx->get_shortcode_att('display');
        $res_ctx->load_settings_raw( 'display', $display );
        // float right
        $res_ctx->load_settings_raw( 'float_right', $res_ctx->get_shortcode_att('float_right') );
        // vertical align
        $align_vert = $res_ctx->get_shortcode_att('align_vert');
        if( $display == 'row' || $display == '' ) {
            if( $align_vert == 'content-vert-top' ) {
                $res_ctx->load_settings_raw( 'align_vert_top', 1 );
            } else if( $align_vert == 'content-vert-center' ) {
                $res_ctx->load_settings_raw( 'align_vert_center', 1 );
            } else if ( $align_vert == 'content-vert-bottom' ) {
                $res_ctx->load_settings_raw( 'align_vert_bottom', 1 );
            }
        }
        // horizontal align
        $align_horiz = $res_ctx->get_shortcode_att('align_horiz');
        if( $display == 'column' ) {
            if( $align_horiz == 'content-horiz-left' ) {
                $res_ctx->load_settings_raw( 'align_horiz_left', 1 );
            } else if( $align_horiz == 'content-horiz-center' ) {
                $res_ctx->load_settings_raw( 'align_horiz_center', 1 );
            } else if ( $align_horiz == 'content-horiz-right' ) {
                $res_ctx->load_settings_raw( 'align_horiz_right', 1 );
            }
        } else if( $display == 'row' || $display == '' ) {
            if( $align_horiz == 'content-horiz-left' ) {
                $res_ctx->load_settings_raw( 'align_horiz_left2', 1 );
            } else if( $align_horiz == 'content-horiz-center' ) {
                $res_ctx->load_settings_raw( 'align_horiz_center2', 1 );
            } else if ( $align_horiz == 'content-horiz-right' ) {
                $res_ctx->load_settings_raw( 'align_horiz_right2', 1 );
            }
        }




        $display = $res_ctx->get_shortcode_att('display');

        /*-- LOGO SVG -- */
        // logo svg width
        $svg_width = $res_ctx->get_shortcode_att('svg_width');
        $res_ctx->load_settings_raw( 'svg_width', $svg_width );
        if( $svg_width != '' && is_numeric( $svg_width ) ) {
            $res_ctx->load_settings_raw( 'svg_width', $svg_width . 'px' );
        }

        // logo svg space
        $svg_pos = $res_ctx->get_shortcode_att('svg_pos');
        $svg_txt_space = $res_ctx->get_shortcode_att('svg_txt_space');
        if( $svg_txt_space != '' && is_numeric( $svg_txt_space ) ) {
            if( $display == 'row' || $display == '' ) {
                if( $svg_pos == '' ) {
                    $res_ctx->load_settings_raw( 'svg_space_top', '0px' );
                    $res_ctx->load_settings_raw( 'svg_space_bottom', '0px' );
                    $res_ctx->load_settings_raw( 'svg_space_right', $svg_txt_space . 'px' );
                    $res_ctx->load_settings_raw( 'svg_space_left', '0px' );
                } else if ( $svg_pos == 'between' ) {
                    $res_ctx->load_settings_raw( 'svg_space_top', $svg_txt_space . 'px' );
                    $res_ctx->load_settings_raw( 'svg_space_bottom', $svg_txt_space . 'px' );
                    $res_ctx->load_settings_raw( 'svg_space_right', '0px' );
                    $res_ctx->load_settings_raw( 'svg_space_left', '0px' );
                } else if ( $svg_pos == 'after' ) {
                    $res_ctx->load_settings_raw( 'svg_space_top', '0px' );
                    $res_ctx->load_settings_raw( 'svg_space_bottom', '0px' );
                    $res_ctx->load_settings_raw( 'svg_space_right', '0px' );
                    $res_ctx->load_settings_raw( 'svg_space_left', $svg_txt_space . 'px' );
                }
            } else if ( $display == 'column' ) {
                if( $svg_pos == '' ) {
                    $res_ctx->load_settings_raw( 'svg_space_top', '0px' );
                    $res_ctx->load_settings_raw( 'svg_space_bottom', $svg_txt_space . 'px' );
                    $res_ctx->load_settings_raw( 'svg_space_right', '0px' );
                    $res_ctx->load_settings_raw( 'svg_space_left', '0px' );
                } else if( $svg_pos == 'between' ) {
                    $res_ctx->load_settings_raw( 'svg_space_top', $svg_txt_space . 'px' );
                    $res_ctx->load_settings_raw( 'svg_space_bottom', $svg_txt_space . 'px' );
                    $res_ctx->load_settings_raw( 'svg_space_right', '0px' );
                    $res_ctx->load_settings_raw( 'svg_space_left', '0px' );
                } else if ( $svg_pos == 'after' ) {
                    $res_ctx->load_settings_raw( 'svg_space_top', $svg_txt_space . 'px' );
                    $res_ctx->load_settings_raw( 'svg_space_bottom', '0px' );
                    $res_ctx->load_settings_raw( 'svg_space_right', '0px' );
                    $res_ctx->load_settings_raw( 'svg_space_left', '0px' );
                }
            }
        }

        // show / hide svg
        $show_svg = $res_ctx->get_shortcode_att('show_svg');
        $show_img = $res_ctx->get_shortcode_att('show_image');
        $res_ctx->load_settings_raw( 'show_svg', $show_svg );
        if( $show_img != 'none' ) {
            if( $show_svg == 'none' ) {
                $res_ctx->load_settings_raw( 'show_img_not_svg', 1 );
            } else if( $show_svg == '' || $show_svg == 'block' ) {
                $res_ctx->load_settings_raw( 'show_svg_not_img', 1 );
            }
        }




        /*-- LOGO IMAGE -- */
        // logo image width
        $image_width = $res_ctx->get_shortcode_att('image_width');
        $res_ctx->load_settings_raw( 'image_width', $image_width );
        if( $image_width != '' && is_numeric( $image_width ) ) {
            $res_ctx->load_settings_raw( 'image_width', $image_width . 'px' );
        }

        // logo image space
        $img_pos = $res_ctx->get_shortcode_att('image_pos');
        $img_txt_space = $res_ctx->get_shortcode_att('img_txt_space');
        if( $img_txt_space != '' && is_numeric( $img_txt_space ) ) {
            if( $display == 'row' || $display == '' ) {
                if( $img_pos == '' ) {
                    $res_ctx->load_settings_raw( 'img_space_top', '0px' );
                    $res_ctx->load_settings_raw( 'img_space_bottom', '0px' );
                    $res_ctx->load_settings_raw( 'img_space_right', $img_txt_space . 'px' );
                    $res_ctx->load_settings_raw( 'img_space_left', '0px' );
                } else if ( $img_pos == 'between' ) {
                    $res_ctx->load_settings_raw( 'img_space_top', $img_txt_space . 'px' );
                    $res_ctx->load_settings_raw( 'img_space_bottom', $img_txt_space . 'px' );
                    $res_ctx->load_settings_raw( 'img_space_right', '0px' );
                    $res_ctx->load_settings_raw( 'img_space_left', '0px' );
                } else if ( $img_pos == 'after' ) {
                    $res_ctx->load_settings_raw( 'img_space_top', '0px' );
                    $res_ctx->load_settings_raw( 'svg_space_bottom', '0px' );
                    $res_ctx->load_settings_raw( 'img_space_right', '0px' );
                    $res_ctx->load_settings_raw( 'img_space_left', $img_txt_space . 'px' );
                }
            } else if ( $display == 'column' ) {
                if( $img_pos == '' ) {
                    $res_ctx->load_settings_raw( 'img_space_top', '0px' );
                    $res_ctx->load_settings_raw( 'img_space_bottom', $img_txt_space . 'px' );
                    $res_ctx->load_settings_raw( 'img_space_right', '0px' );
                    $res_ctx->load_settings_raw( 'img_space_left', '0px' );
                } else if( $img_pos == 'between' ) {
                    $res_ctx->load_settings_raw( 'img_space_top', $img_txt_space . 'px' );
                    $res_ctx->load_settings_raw( 'img_space_bottom', $img_txt_space . 'px' );
                    $res_ctx->load_settings_raw( 'img_space_right', '0px' );
                    $res_ctx->load_settings_raw( 'img_space_left', '0px' );
                } else if ( $img_pos == 'after' ) {
                    $res_ctx->load_settings_raw( 'img_space_top', $img_txt_space . 'px' );
                    $res_ctx->load_settings_raw( 'img_space_bottom', '0px' );
                    $res_ctx->load_settings_raw( 'img_space_right', '0px' );
                    $res_ctx->load_settings_raw( 'img_space_left', '0px' );
                }
            }
        }

        // show / hide image
        $res_ctx->load_settings_raw( 'show_image', $res_ctx->get_shortcode_att('show_image') );



        /*-- LOGO TEXT -- */
        // space between title & tagline
        $tagline_pos = $res_ctx->get_shortcode_att('tagline_pos');
        $ttl_tag_space = $res_ctx->get_shortcode_att('ttl_tag_space');
        if( $tagline_pos == '' ) {
            $res_ctx->load_settings_raw( 'ttl_tag_space_top', '2px' );
            if( $ttl_tag_space != '' && is_numeric( $ttl_tag_space ) ) {
                $res_ctx->load_settings_raw( 'ttl_tag_space_top', $ttl_tag_space . 'px' );
            }
        } else if ( $tagline_pos == 'inline' ) {
            $res_ctx->load_settings_raw( 'ttl_tag_space_left', '2px' );
            if( $ttl_tag_space != '' && is_numeric( $ttl_tag_space ) ) {
                $res_ctx->load_settings_raw( 'ttl_tag_space_left', $ttl_tag_space . 'px' );
            }
        }
        // tagline position
        if( $tagline_pos == '' ) {
            $res_ctx->load_settings_raw( 'tagline_pos_under', 1 );
        } else if ( $tagline_pos == 'inline' ) {
            $res_ctx->load_settings_raw( 'tagline_pos_inline', 1 );
        }

        // show title
        $res_ctx->load_settings_raw( 'show_title', $res_ctx->get_shortcode_att('show_title') );
        // title wrap
        $res_ctx->load_settings_raw( 'title_wrap', $res_ctx->get_shortcode_att('title_wrap') );
        // show tagline
        $res_ctx->load_settings_raw( 'show_tagline', $res_ctx->get_shortcode_att('show_tagline') );

        // tagline horizontal align
        $tagline_align_horiz = $res_ctx->get_shortcode_att('tagline_align_horiz');
        if( $tagline_pos == '' ) {
            if( $tagline_align_horiz == 'content-horiz-left' ) {
                $res_ctx->load_settings_raw( 'tagline_align_horiz_left', 1 );
            } else if( $tagline_align_horiz == 'content-horiz-center' ) {
                $res_ctx->load_settings_raw( 'tagline_align_horiz_center', 1 );
            } else if ( $tagline_align_horiz == 'content-horiz-right' ) {
                $res_ctx->load_settings_raw( 'tagline_align_horiz_right', 1 );
            }
        }
        // tagline vertical align
        $tagline_align_vert = $res_ctx->get_shortcode_att('tagline_align_vert');
        if( $tagline_pos == 'inline' ) {
            if ($tagline_align_vert == 'content-vert-top') {
                $res_ctx->load_settings_raw('tagline_align_vert_top', 1);
            } else if ($tagline_align_vert == 'content-vert-center') {
                $res_ctx->load_settings_raw('tagline_align_vert_center', 1);
            }
        }



        /*-- LOGO ICON -- */
        $icon = $res_ctx->get_icon_att('tdicon');
        // icon size
        $icon_size = $res_ctx->get_shortcode_att('icon_size');
        if( $icon_size != '' && is_numeric( $icon_size ) ) {
            if( base64_encode( base64_decode( $icon ) ) == $icon ) {
                $res_ctx->load_settings_raw( 'icon_svg_size', $icon_size . 'px' );
            } else {
                $res_ctx->load_settings_raw( 'icon_size', $icon_size . 'px' );
            }
        }

        // icon space
        $tagline_pos = $res_ctx->get_shortcode_att('tagline_pos');
        $logo_icon_pos = $res_ctx->get_shortcode_att('icon_pos');
        $icon_space = $res_ctx->get_shortcode_att('icon_space');
        $res_ctx->load_settings_raw( 'icon_space', $icon_space );
        if( $icon_space != '' && is_numeric( $icon_space ) ) {
            if( $logo_icon_pos == '' ) {
                if( $display == 'row' || $display == '' ) {
                    $res_ctx->load_settings_raw( 'icon_space_right', $icon_space . 'px' );
                } else if ( $display == 'column' ) {
                    $res_ctx->load_settings_raw( 'icon_space_bottom', $icon_space . 'px' );
                }
            } else if ( $logo_icon_pos == 'after' ) {
                if( $display == 'row' || $display == '' ) {
                    $res_ctx->load_settings_raw( 'icon_space_left', $icon_space . 'px' );
                } else if ( $display == 'column' ) {
                    $res_ctx->load_settings_raw( 'icon_space_top', $icon_space . 'px' );
                }
            } else if ( $logo_icon_pos == 'between' ) {
                if( $tagline_pos == '' ) {
                    $res_ctx->load_settings_raw( 'icon_space_top_bottom', $icon_space . 'px' );
                } else if ( $tagline_pos == 'inline' ) {
                    $res_ctx->load_settings_raw( 'icon_space_left_right', $icon_space . 'px' );
                }
            }
        }

        // icon padding
        $icon_padd = $res_ctx->get_shortcode_att('icon_padd');
        $res_ctx->load_settings_raw( 'icon_padd', $icon_padd );
        if( $icon_padd != '' && is_numeric( $icon_padd ) ) {
            $res_ctx->load_settings_raw( 'icon_padd', $icon_padd . 'px' );
        }

        // icon border radius
        $icon_radius = $res_ctx->get_shortcode_att('icon_radius');
        $res_ctx->load_settings_raw( 'icon_radius', $icon_radius );
        if( $icon_radius != '' && is_numeric( $icon_radius ) ) {
            $res_ctx->load_settings_raw( 'icon_radius', $icon_radius . 'px' );
        }

        // icon align
        $res_ctx->load_settings_raw( 'icon_align', $res_ctx->get_shortcode_att('icon_align') . 'px' );

        // show icon
        $res_ctx->load_settings_raw( 'show_icon', $res_ctx->get_shortcode_att('show_icon') );




        /*-- COLORS -- */
        $res_ctx->load_settings_raw( 'svg_color', $res_ctx->get_shortcode_att('svg_color') );
        $res_ctx->load_settings_raw( 'svg_color_h', $res_ctx->get_shortcode_att('svg_color_h') );

        $res_ctx->load_color_settings( 'text_color', 'text_color_solid', 'text_color_gradient', 'text_color_gradient_1', '' );
        $res_ctx->load_settings_raw( 'text_bg', tdc_util::get_image_or_placeholder( $res_ctx->get_shortcode_att('text_bg') ) );
        $res_ctx->load_settings_raw( 'text_bg_h', tdc_util::get_image_or_placeholder( $res_ctx->get_shortcode_att('text_bg_h') ) );
        $res_ctx->load_settings_raw( 'text_color_h', $res_ctx->get_shortcode_att('text_color_h') );

        $res_ctx->load_color_settings( 'tagline_color', 'tagline_color_solid', 'tagline_color_gradient', 'tagline_color_gradient_1', '' );
        $res_ctx->load_settings_raw( 'tagline_color_h', $res_ctx->get_shortcode_att('tagline_color_h') );
        $res_ctx->load_settings_raw( 'tagline_bg', tdc_util::get_image_or_placeholder( $res_ctx->get_shortcode_att('tagline_bg') ) );
        $res_ctx->load_settings_raw( 'tagline_bg_h', tdc_util::get_image_or_placeholder( $res_ctx->get_shortcode_att('tagline_bg_h') ) );

        $res_ctx->load_color_settings( 'icon_color', 'icon_color_solid', 'icon_color_gradient', 'icon_color_gradient_1', '' );
        $res_ctx->load_settings_raw( 'icon_color_h', $res_ctx->get_shortcode_att('icon_color_h') );

        $res_ctx->load_settings_raw( 'icon_bg', $res_ctx->get_shortcode_att('icon_bg') );
        $res_ctx->load_settings_raw( 'icon_bg_h', $res_ctx->get_shortcode_att('icon_bg_h') );



        /*-- FONTS -- */
        $res_ctx->load_font_settings( 'f_text' );
        $res_ctx->load_font_settings( 'f_tagline' );

    }

    /**
     * Disable loop block features. This block does not use a loop and it doesn't need to run a query.
     */
    function __construct() {
        parent::disable_loop_block_features();
    }


    function render( $atts, $content = null ) {
        parent::render( $atts ); // sets the live atts, $this->atts, $this->block_uid, $this->td_query (it runs the query)


        $buffy = ''; //output buffer

        $aria_label_alt_svg = '';
        $tds_animation_stack = td_util::get_option('tds_animation_stack');
        $td_lazy_load_on_logo = $this->get_att('lazy_load');
        $td_use_lazy_load = false;
        $td_animation_stack_class = '';
        if ( empty($tds_animation_stack) && $td_lazy_load_on_logo == 'yes' ) { //lazyload panel and shortcode options
            $td_animation_stack_class = ' td-animation-stack ';
            $td_use_lazy_load = true;
        }

        $buffy .= '<div class="' . $td_animation_stack_class . $this->get_block_classes() . ' tdb-header-align" ' . $this->get_block_html_atts() . '>';

        $td_use_h1_logo = false;
        $td_disable_h1_on_logo = $this->get_att('disable_h1');

        if ( is_page()) {
            if ( $td_disable_h1_on_logo == '' ) {
                global $post;
                $_wp_page_template = get_post_meta($post->ID, '_wp_page_template', true);

                //check TP option
                $td_use_h1_logo = td_util::get_option('tds_logo_h1_pages') == '' ;

                // use only if pagebuilder content is found
                if ( !td_util::is_pagebuilder_content($post) ) {
                    $td_use_h1_logo = false;
                } elseif ( 'page-pagebuilder-latest.php' === $_wp_page_template  || ( is_front_page() && td_util::is_pagebuilder_content($post) ) ) {
                    $td_use_h1_logo = true;
                }
            }
        } else {
            if ( $td_disable_h1_on_logo == '' ) {
                if ( tdc_state::is_live_editor_ajax() || tdc_state::is_live_editor_iframe() ) {
                    $td_use_h1_logo = true;
                } else {
                    if ( is_home() ) {
                        $td_use_h1_logo = true;
                    }
                }
            }
        }

        // logo text
        $logo_text = $this->get_att('text');
        if( $logo_text == '' ) {
            $logo_text = stripslashes(td_util::get_option('tds_logo_text'));
        }
        // logo tagline
        $logo_tagline = rawurldecode( base64_decode( strip_tags( $this->get_att('tagline') ) ) );
        if( $logo_tagline == '' ) {
            $logo_tagline = td_util::get_option('tds_tagline_text');
        }


        // logo icon
        $logo_icon = $this->get_icon_att('tdicon');
        $tdicon_data = '';
        if( td_util::tdc_is_live_editor_iframe() || td_util::tdc_is_live_editor_ajax() ) {
            $tdicon_data = 'data-td-svg-icon="' . $this->get_att('tdicon') . '"';
        }
        // logo icon html
        $logo_icon_html = '';
        if( $logo_icon != '' ) {
            if( base64_encode( base64_decode( $logo_icon ) ) == $logo_icon ) {
                $logo_icon_html .= '<span class="tdb-logo-icon tdb-logo-icon-svg" ' . $tdicon_data . '>' . base64_decode( $logo_icon ) . '</span>';
            } else {
                $logo_icon_html .= '<i class="tdb-logo-icon ' . $logo_icon . '"></i>';
            }
        }
        // logo icon position
        $logo_icon_pos = $this->get_att('icon_pos');


        // logo url
        $url = $this->get_att('url');
        if( $url == '' ) {
            $url = esc_url( home_url( '/' ) );
        }
        // open in new window
        $target = '';
        if ( $this->get_att( 'open_in_new_window' ) !== '' ) {
            $target = ' target="_blank"';
        }

        //set rel attribute on logo url
        $td_logo_rel = '';
        if ('' !== $this->get_att('url_rel')) {
            $td_logo_rel = ' rel="' . $this->get_att('url_rel') . '" ';
        }

        // logo svg
        $logo_svg = rawurldecode( base64_decode( strip_tags( $this->get_att('svg_code') ) ) );
        //we need title atr on link when svg is used
        $title_link_atr = '';

        // logo image
        $logo_image = tdc_util::get_image_or_placeholder( $this->get_att('image') );
        $logo_image_width_html = '';
        $logo_image_height_html = '';
        if( $logo_image == '' ) {
            $logo_image = td_util::get_option('tds_logo_upload');
        }
        if( $logo_image != '' ) {
            $info_img = wp_get_attachment_image_src( $this->get_att('image'), 'full');
            if (is_array($info_img)) {
                $logo_image_width_html = ' width="' . $info_img[1] . '"';
                $logo_image_height_html = ' height="' . $info_img[2] . '"';
            }
        }
        // logo retina image
        $logo_retina_image = tdc_util::get_image_or_placeholder( $this->get_att('image_retina') );
        if( $logo_retina_image == '' ) {
            $logo_retina_image = td_util::get_option('tds_logo_upload_r');
        }
        // alt atr
        $alt_atr = rawurldecode( base64_decode( strip_tags( $this->get_att('alt') ) ) );
        if( $alt_atr == '' ) {
            //panel option
            $alt_atr = td_util::get_option('tds_logo_alt');
            //if is not set, solve accessibility warning
            if( $alt_atr == '' ) {
                $alt_atr = 'Logo';
            }
        }
        // title atr
        $title_atr = rawurldecode( base64_decode( strip_tags( $this->get_att('title') ) ) );
        if ( $title_atr == '' ) {
            $title_atr = td_util::get_option('tds_logo_title');
        }
        $title_link_atr = $title_atr === '' ? '' : ' title="' . $title_atr . '"';


        // logo image html
        $logo_image_html = '';
        if ( tdc_state::is_live_editor_ajax() || tdc_state::is_live_editor_iframe() ) {
            if( $logo_svg == '' && $logo_image == '' && $logo_text == '' && $logo_tagline == '' ) {
                $logo_image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAG0AAAAbBAMAAACErRy5AAAAKlBMVEWfn58AAACfn5+fn5+fn5+fn5+fn5+fn5+fn5+fn5+fn5+fn5+fn5+fn58wOh9aAAAADnRSTlNzADZUa0UhCA8uXBdkTapN5ToAAAIvSURBVDjLrZS5TxtBFMbH9toBZKQMBHsdC4mshJIojZ1DShNpkyJBSoMF4hKFuURBwyIXiAqDKOhsgaBCAhqQaHABNUdFZ4l/iHfMtVvzitn387xvZvab5xUQH6SJnYkw/+PY4efQ+/VFkz/6XTzORpC9jetuBMWM5iXmJ6byOFG2mdQNCA5P/bIhVPwkHFH0J6Ernuq6bII9xC2ho2Z1PGFn4pylVSzGdXT883kc04a/zsFwATSE1Dd6RwpXV8B8ms3wmpb3iGQdN4pkoQLPMVc3COlfvU+NuRd5OYcj1HsLat+sq7uGtI3JMCS/iXnK7+Al6FVkFxYwOrY5wz6GQuQs2zuqUtaArO7oKlBtVkhbtm+xYFYYcXQhnU6yBf1SgnMpqP+GUZXv4HTWv66jUwfh7sswY46R4t9MYeV1dPecbWvdWUyX51k/oYM+6uGsBb7Q+72xuvcwNmm2lPClC9XG2V49DgTBOOqGsdA423J0LW4vNqyHbiMfKXtTtMuYVCi2Hd26amDZYEdwrmZ0RezqSHVFxu2XIZzBfg75igepkfkgKe7aS+xWeOaMjhbCwtX9rmDLy/Tf7vgHgnQNxKnNE3xU2S1lWd3CP/e7wLqCJS+K6Up2osMHN3GPxzX0X8Z08lDnV+YDwNEfAZVCRX2dhM6vcJqOVAffMefbhLuq8qOM6bBwErOHplRROqXd2gpXQlzkE3VwoINdvQ0+r0obxcUgWLNYXgyOIur8F7Z8eft6u608AAAAAElFTkSuQmCC';

                $logo_image_html .= '<span class="tdb-logo-img-wrap">';
                $logo_image_html .= '<img class="tdb-logo-img" src="' . $logo_image . '" alt="' . $alt_atr . '"' . $title_link_atr . '/>';
                $logo_image_html .= '</span>';
            }
        }

        if( $logo_image_html == '' && ( $logo_svg != '' || $logo_retina_image != '' || $logo_image != '' ) ) {
            if( $logo_svg != '' ) {
                $aria_label_alt_svg = ' aria-label="' . $alt_atr . '"';
                $logo_image_html .= '<span class="tdb-logo-svg-wrap">';
                $logo_image_html .= $logo_svg;
                $logo_image_html .= '</span>';
            }

            if( $logo_retina_image != '' || $logo_image != '' ) {
                $logo_image_html .= '<span class="tdb-logo-img-wrap">';

                if ( $td_use_lazy_load && !td_util::tdc_is_live_editor_ajax() && !td_util::tdc_is_live_editor_iframe() && !td_util::is_mobile_theme() && !td_util::is_amp()) {
                    if( $logo_retina_image != '' ) {
                        $logo_image_html .= '<img class="tdb-logo-img td-retina-data" data-retina="' . esc_attr($logo_retina_image) . '" src="' . $logo_image . '" alt="' . $alt_atr . '"  title="' . $title_atr . '" ' . $logo_image_width_html . $logo_image_height_html . ' />';
                    } else if( $logo_image != '' ) {
                        $logo_image_html .= '<img class="tdb-logo-img td-lazy-img" data-type="image_tag" data-img-url="' . $logo_image . '" alt="' . $alt_atr . '"  title="' . $title_atr . '" ' . $logo_image_width_html . $logo_image_height_html . ' />';
                    }
                } else {
                    if ($logo_retina_image != '') {
                        $logo_image_html .= '<img class="tdb-logo-img td-retina-data" data-retina="' . esc_attr($logo_retina_image) . '" src="' . $logo_image . '" alt="' . $alt_atr . '"  title="' . $title_atr . '" ' . $logo_image_width_html . $logo_image_height_html . ' />';
                    } else if ($logo_image != '') {
                        $logo_image_html .= '<img class="tdb-logo-img" src="' . $logo_image . '" alt="' . $alt_atr . '"  title="' . $title_atr . '" ' . $logo_image_width_html . $logo_image_height_html . ' />';
                    }
                }
                $logo_image_html .= '</span>';
            }
        }

        // logo image position
        $logo_image_pos = $this->get_att('image_pos');
        if( $logo_svg != '' ) {
            $logo_image_pos = $this->get_att('svg_pos');
        }




        //get the block css
        $buffy .= $this->get_block_css();

        //get the js for this block
        $buffy .= $this->get_block_js();


        $buffy .= '<div class="tdb-block-inner td-fix-index">';

        $buffy .= '<a class="tdb-logo-a" href="' . $url . '"' . $aria_label_alt_svg . $target . $td_logo_rel . $title_link_atr . '>';
        if( $td_use_h1_logo ) {
            $buffy .= '<h1>';
        }

        if( $logo_image_pos == '' ) {
            $buffy .= $logo_image_html;
        }

        if ( $logo_icon_pos == '' ) {
            $buffy .= $logo_icon_html;
        }

        if( $logo_text != '' || $logo_tagline != '' || ( $logo_image_pos == 'between' && $logo_image_html != '' ) || ( $logo_icon_pos == 'between' && $logo_icon_html != '' ) ) {
            $buffy .= '<span class="tdb-logo-text-wrap">';
            if( $logo_text != '' ) {
                $buffy .= '<span class="tdb-logo-text-title">' . $logo_text . '</span>';
            }

            if( $logo_image_pos == 'between' && $logo_image_html != '' ) {
                $buffy .= $logo_image_html;
            }

            if( $logo_icon_pos == 'between' && $logo_icon_html != '' ) {
                $buffy .= $logo_icon_html;
            }

            if( $logo_tagline != '' ) {
                $buffy .= '<span class="tdb-logo-text-tagline">' . $logo_tagline . '</span>';
            }
            $buffy .= '</span>';
        }

        if( $logo_image_pos == 'after' ) {
            $buffy .= $logo_image_html;
        }

        if ( $logo_icon_pos == 'after' ) {
            $buffy .= $logo_icon_html;
        }

        if( $td_use_h1_logo ) {
            $buffy .= '</h1>';
        }
        $buffy .= '</a>';

        $buffy .= '</div>';

        $buffy .= '</div> <!-- ./block -->';

        return $buffy;
    }

}
