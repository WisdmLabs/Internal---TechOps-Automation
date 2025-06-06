<?php
/**
 * this is the default block template
 * Class td_block_header_12
 */
class td_block_template_12 extends td_block_template {

    private static $atts = array();

    static function cssMedia( $res_ctx ) {

        /* --
        -- GENERAL
        -- */
        $res_ctx->load_settings_raw( 'style_general_template_12', 1 );




        /* --
        -- COLORS
        -- */
        $res_ctx->load_settings_raw('button_color', self::$atts['button_color']);
        $res_ctx->load_settings_raw('header_text_color', self::$atts['header_text_color']);
        $res_ctx->load_settings_raw('accent_text_color', self::$atts['accent_text_color']);




        /* --
        -- FONTS
        -- */
        $res_ctx->load_font_settings( 'f_cont' );

    }

    public function get_css() {

        self::$atts = shortcode_atts( array(
            'header_text_color' => '',
            'accent_text_color' => '',
            'button_text' => '',
            'button_color' => '',
            'f_cont_font_family' => '',
            'f_cont_font_size' => '',
            'f_cont_font_line_height' => '',
            'f_cont_font_style' => '',
            'f_cont_font_weight' => '',
            'f_cont_font_transform' => '',
            'f_cont_font_spacing' => ''
        ), $this->get_all_atts() );


        // $unique_block_class - the unique class that is on the block. use this to target the specific instance via css
        $unique_block_class =  $this->get_unique_block_class();

        $compiled_css = '';

        // the css that will be compiled by the block, <style> - will be removed by the compiler
        $raw_css = "
        <style>
        
            /* @style_general_template_12 */
            .td_block_template_12.widget > ul > li {
                margin-left: 0 !important;
            }
            .global-block-template-12 .td-comments-title span {
                margin-left: 0 !important;
                font-size: 20px;
            }
            @media (max-width: 767px) {
                .global-block-template-12 .td-comments-title span {
                    font-size: 15px;
                }
            }
            .td_block_template_12 .td-related-title a {
                margin-right: 20px;
                font-size: 20px;
            }
            @media (max-width: 767px) {
                .td_block_template_12 .td-related-title a {
                    font-size: 15px;
                }
            }
            .td_block_template_12 .td-related-title .td-cur-simple-item {
                color: var(--td_theme_color, #4db2ec);
            }
            .td_block_template_12 .td-related-title > a.td-related-lef {
                margin-left: 0 !important;
            }
            .td_block_template_12 .td-block-title {
                display: flex;
                align-items: center;
                font-size: 26px;
                font-weight: 800;
                margin-top: 0;
                margin-bottom: 26px;
                line-height: 26px;
                padding: 0;
                letter-spacing: -0.6px;
                text-align: left;
            }
            @media (max-width: 1018px) {
                .td_block_template_12 .td-block-title {
                    font-size: 22px;
                    margin-bottom: 20px;
                }
            }
            .td_block_template_12 .td-block-title > * {
                color: var(--td_text_header_color, #000);
            }
            .td_block_template_12 .td-subcat-filter {
                line-height: 1;
                display: table;
            }
            .td_block_template_12 .td-subcat-dropdown .td-subcat-more {
                margin-bottom: 8px !important;
                margin-top: 7px;
            }
            .td_block_template_12 .td-pulldown-category {
                display: flex;
                align-items: center;
                font-family: var(--td_default_google_font_2, 'Roboto', sans-serif);
                font-size: 14px;
                line-height: 26px;
                color: #444;
                font-weight: 500;
                position: absolute;
                right: 0;
                bottom: -2px;
                top: 0;
                margin: auto 0;
            }
            .td_block_template_12 .td-pulldown-category span {
                -webkit-transition: transform 0.5s ease;
                transition: transform 0.5s ease;
            }
            @media (max-width: 767px) {
                .td_block_template_12 .td-pulldown-category span {
                    display: none;
                }
            }
            .td_block_template_12 .td-pulldown-category i {
                font-size: 10px;
                margin-left: 10px;
            }
            .td_block_template_12 .td-pulldown-category:hover {
                opacity: 0.9;
            }
            .td_block_template_12 .td-pulldown-category:hover span {
                transform: translate3d(-6px, 0, 0);
                -webkit-transform: translate3d(-6px, 0, 0);
            }
            @media (min-width: 768px) and (max-width: 1018px) {
                .td-pb-span4 .td_block_template_12 .td-pulldown-category span {
                    display: none;
                }
            }
            

            /* @header_text_color */
            .$unique_block_class .td-block-title > * {
                color: @header_text_color !important;
            }

            /* @accent_text_color */
            .$unique_block_class .td_module_wrap:hover .entry-title a,
            .$unique_block_class .td_quote_on_blocks,
            .$unique_block_class .td-opacity-cat .td-post-category:hover,
            .$unique_block_class .td-opacity-read .td-read-more a:hover,
            .$unique_block_class .td-opacity-author .td-post-author-name a:hover,
            .$unique_block_class .td-instagram-user a,
            .$unique_block_class .td-pulldown-category {
                color: @accent_text_color !important;
            }

            .$unique_block_class .td-next-prev-wrap a:hover,
            .$unique_block_class .td-load-more-wrap a:hover {
                background-color: @accent_text_color !important;
                border-color: @accent_text_color !important;
            }

            .$unique_block_class .td-read-more a,
            .$unique_block_class .td-weather-information:before,
            .$unique_block_class .td-weather-week:before,
            .$unique_block_class .td-exchange-header:before,
            .td-footer-wrapper .$unique_block_class .td-post-category,
            .$unique_block_class .td-post-category:hover {
                background-color: @accent_text_color !important;
            }
            
            /* @button_color */
            .$unique_block_class .td-pulldown-category {
                color: @button_color !important;
            }
            
            /* @f_cont */
            .$unique_block_class .td-pulldown-category {
                @f_cont
            }
            
        </style>
    ";

        $td_css_res_compiler = new td_css_res_compiler( $raw_css );
        $td_css_res_compiler->load_settings( __CLASS__ . '::cssMedia', self::$atts );

        $compiled_css .= $td_css_res_compiler->compile_css();

        return $compiled_css;

    }


    /**
     * renders the block title
     * @return string HTML
     */
    function get_block_title() {

        $custom_title = $this->get_att('custom_title');
        $custom_url = $this->get_att('custom_url');
        $title_tag = 'h4';

        // title_tag used only on Title shortcode
        $block_title_tag = $this->get_att('title_tag');
        if(!empty($block_title_tag)) {
            $title_tag = $block_title_tag ;
        }

        if (empty($custom_title)) {
	        $td_pull_down_items = $this->get_td_pull_down_items();
            if (empty($td_pull_down_items)) {
                //no title selected and we don't have pulldown items
                return '';
            }
            // we don't have a title selected BUT we have pull down items! we cannot render pulldown items without a block title
            $custom_title = 'Block title';
        }


        // there is a custom title
        $buffy = '';
        $buffy .= '<' . $title_tag . ' class="td-block-title">';
        if (!empty($custom_url)) {
            $buffy .= '<a href="' . esc_url($custom_url) . '">' . esc_html($custom_title) . '</a>';
        } else {
            $buffy .= '<span>' . esc_html($custom_title) . '</span>';
        }
        $buffy .= '</' . $title_tag . '>';
        return $buffy;
    }


    /**
     * renders the filter of the block
     * @return string
     */
    function get_pull_down_filter() {

        $buffy = '';

        $custom_url = $this->get_att('custom_url');
        $category_id = $this->get_att('category_id');

        if (empty($custom_url) && empty($category_id)) {
            return '';
        }

        // button text
        $button_text = $this->get_att('button_text');
        if (empty($button_text)) {
	        if (empty($category_id)) {
		        $button_text = 'Read more';
	        } else {
		        $button_text = 'Continue to the category';
	        }
        }

        if (empty($custom_url)) {
            $custom_url = get_category_link($category_id);
        }

        $buffy .= '<a href="' . esc_url($custom_url) . '" class="td-pulldown-category"><span>' . esc_html($button_text) . '</span><i class="td-icon-category"></i></a>';


        return $buffy;
    }




}
