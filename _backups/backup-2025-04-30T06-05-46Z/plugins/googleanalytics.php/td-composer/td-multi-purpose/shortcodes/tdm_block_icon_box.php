<?php
class tdm_block_icon_box extends td_block {

    private $icon_box_style;
    protected $shortcode_atts = array(); //the atts used for rendering the current block
    private $unique_block_class;

    /**
     * Disable loop block features. This block does not use a loop and it dosn't need to run a query.
     */
    function __construct() {
        parent::disable_loop_block_features();
    }

    public function get_local_css() {

        $compiled_css = '';

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

        $raw_css =
            "<style>
			
			    /* @style_general_icon_box */
			    .tdm_block_icon_box .tds-icon-svg svg {
			        display: block;
			    }
			    .tdm_block_icon_box .tdm-descr {
                  font-size: 14px;
                  line-height: 24px;
                  -webkit-transition: color 0.2s ease;
                  transition: color 0.2s ease;
                }

			    
			    /* @icon_size */
				.$unique_block_class .tds-icon-box .tds-icon {
				    font-size: @icon_size;
				    text-align: center;
				}
				/* @svg_size */
                .$unique_block_class svg {
                    width: @svg_size;
                    height: auto;
                }
				
				/* @icon_spacing */
				.$unique_block_class .tds-icon-box .tds-icon {
				    width: @icon_spacing;
				    height: @icon_spacing;
				    line-height: @icon_line_height;
				}
				/* @svg_spacing */
				.$unique_block_class .tds-icon-svg-wrap {
				    width: @svg_spacing;
				    height: @svg_spacing;
				    display: flex;
                    align-items: center;
                    justify-content: center;
				}
				
				/* @content_align_horizontal_center */
				.$unique_block_class .tds-icon-svg-wrap {
				    margin: 0 auto;
				}
				/* @content_align_horizontal_right */
				.$unique_block_class .tds-icon-svg-wrap {
				    margin-left: auto;
				}
          

			</style>";


        $td_css_res_compiler = new td_css_res_compiler( $raw_css );
        $td_css_res_compiler->load_settings( __CLASS__ . '::cssMedia', $this->shortcode_atts);

        $compiled_css .= $td_css_res_compiler->compile_css();
        return $compiled_css;
    }

    /**
     * Callback pe media
     *
     * @param $res_ctx td_res_context
     */
    static function cssMedia( $res_ctx ) {

        $res_ctx->load_settings_raw( 'style_general_icon_box', 1 );

        $icon = $res_ctx->get_icon_att( 'tdicon_id' );
        $svg_code = rawurldecode( base64_decode( strip_tags( $res_ctx->get_shortcode_att('svg_code') ) ) );

        /*-- ICON -- */
        // icon size
        $icon_size = $res_ctx->get_shortcode_att( 'icon_size' ) . 'px';
        if( $svg_code != '' || base64_encode( base64_decode( $icon ) ) == $icon ) {
            $res_ctx->load_settings_raw( 'svg_size', $icon_size );
        } else {
            $res_ctx->load_settings_raw( 'icon_size', $icon_size );
        }

        // icon spacing
        $tds_icon = td_util::get_option( 'tds_icon', 'tds_icon1' );
        $icon_spacing = $res_ctx->get_shortcode_att( 'icon_size' ) * $res_ctx->get_shortcode_att( 'icon_padding' ) + intval($res_ctx->get_style_att( 'all_border_size', $tds_icon ) ) * 2 . 'px';
        if( $svg_code != '' || base64_encode( base64_decode( $icon ) ) == $icon ) {
            $res_ctx->load_settings_raw('svg_spacing', $icon_spacing);
        } else {
            $res_ctx->load_settings_raw('icon_spacing', $icon_spacing);
        }

        // icon line height
        $res_ctx->load_settings_raw( 'icon_line_height', $res_ctx->get_shortcode_att( 'icon_size' ) * $res_ctx->get_shortcode_att( 'icon_padding' ) . 'px' );

        // content horiz align
        $content_horiz_align = $res_ctx->get_shortcode_att( 'content_align_horizontal' );
        if( $content_horiz_align == 'content-horiz-center' ) {
            $res_ctx->load_settings_raw('content_align_horizontal_center', 1);
        } else if ( $content_horiz_align == 'content-horiz-right' ) {
            $res_ctx->load_settings_raw('content_align_horizontal_right', 1);
        }

    }

    function render($atts, $content = null) {
        parent::render($atts);

        // $unique_block_class - the unique class that is on the block. use this to target the specific instance via css
        $this->unique_block_class = $this->block_uid;

        $this->shortcode_atts = shortcode_atts(
			array_merge(
				td_api_multi_purpose::get_mapped_atts( __CLASS__ ),
                td_api_style::get_style_group_params( 'tds_icon_box' ),
                td_api_style::get_style_group_params( 'tds_title' ),
                td_api_style::get_style_group_params( 'tds_button' ),
                td_api_style::get_style_group_params( 'tds_icon' ))
			, $atts);

	    $content_align_horizontal = $this->get_shortcode_att( 'content_align_horizontal' );

        $additional_classes = array();


        // content align horizontal
        if ( ! empty( $content_align_horizontal ) ) {
            $additional_classes[] = 'tdm-' . $content_align_horizontal;
        }

        $additional_classes[] = $this->get_shortcode_att('tds_icon_box') . '_wrap';

        $buffy = '';

        $buffy .= '<div class="tdm_block ' . $this->get_block_classes($additional_classes) . '" ' . $this->get_block_html_atts() . '>';

        //get the block css
        $buffy .= $this->get_block_css();

        // Icon box
        $tds_icon_box = $this->get_shortcode_att('tds_icon_box');
        if ( empty( $tds_icon_box ) ) {
            $tds_icon_box = td_util::get_option( 'tds_icon_box', 'tds_icon_box1');
        }
        $this->icon_box_style = new $tds_icon_box( $this->shortcode_atts, $this->unique_block_class );
        $buffy .= $this->icon_box_style->render();

        $buffy .= '<style>' . $this->get_local_css() . '</style>';

        $buffy .= '</div>';


        return $buffy;
    }
}