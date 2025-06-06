<?php
/**
 * this is the default block template
 * Class td_block_header_13
 */
class td_block_template_13 extends td_block_template {



    /**
     * renders the CSS for each block, each template may require a different css generated by the theme
     * @return string CSS the rendered css and <style> block
     */
    function get_css() {


        // $unique_block_class - the unique class that is on the block. use this to target the specific instance via css
        $unique_block_class =  $this->get_unique_block_class();

        // the css that will be compiled by the block, <style> - will be removed by the compiler
        $raw_css = "
        <style>
                                   
            /* @style_general_template_13 */
            .td_block_template_13.widget > ul > li {
                margin-left: 0 !important;
            }
            .global-block-template-13 .td-comments-title span {
                margin-left: 0 !important;
                font-size: 20px;
            }
            @media (max-width: 767px) {
                .global-block-template-13 .td-comments-title span {
                    font-size: 15px;
                }
            }
            .td_block_template_13 .td-related-title a {
                margin-right: 20px;
                font-size: 20px;
            }
            @media (max-width: 767px) {
                .td_block_template_13 .td-related-title a {
                    font-size: 15px;
                }
            }
            .td_block_template_13 .td-related-title .td-cur-simple-item {
                color: var(--td_theme_color, #4db2ec);
            }
            .td_block_template_13 .td-related-title > a.td-related-right {
                margin-left: 0 !important;
            }
            .td_block_template_13 .td-block-title {
                font-size: 26px;
                font-weight: 800;
                margin-bottom: 26px;
                line-height: 26px !important;
                padding: 0;
                letter-spacing: -0.6px;
                margin-top: 36px;
                transform: translateZ(0);
                -webkit-transform: translateZ(0);
                text-align: left;
            }
            .td_block_template_13 .td-block-title a {
                color: #111;
            }
            @media (max-width: 1018px) {
                .td_block_template_13 .td-block-title {
                    font-size: 22px;
                    margin-bottom: 16px;
                    margin-top: 26px;
                }
            }
            @media (max-width: 767px) {
                .td_block_template_13 .td-block-title {
                    margin-top: 26px !important;
                    margin-bottom: 16px !important;
                }
            }
            .td_block_template_13 .td-block-title > a,
            .td_block_template_13 .td-block-title > span {
                margin-left: 12px;
                color: var(--td_text_header_color, #111);
            }
            @media (max-width: 767px) {
                .td_block_template_13 .td-block-title > a,
                .td_block_template_13 .td-block-title > span {
                    margin-left: 12px !important;
                }
            }
            .td_block_template_13 .td-subcat-filter {
                line-height: 1;
                display: table;
            }
            .td_block_template_13 .td-subcat-dropdown .td-subcat-more {
                margin-bottom: 8px !important;
                margin-top: 7px;
            }
            .td_block_template_13 .td-pulldown-category {
                font-family: var(--td_default_google_font_2, 'Roboto', sans-serif);
                font-size: 14px;
                line-height: 26px !important;
                color: #444;
                font-weight: 500;
                position: absolute;
                right: 0;
                bottom: -2px;
                top: 0;
                margin: auto 0;
                display: table;
            }
            .td_block_template_13 .td-pulldown-category span {
                display: inline-block;
                -webkit-transition: transform 0.5s ease;
                transition: transform 0.5s ease;
            }
            @media (max-width: @responsive_p_phone_max) {
                .td_block_template_13 .td-pulldown-category span {
                    display: none;
                }
            }
            .td_block_template_13 .td-pulldown-category i {
                font-size: 10px;
                margin-left: 10px;
            }
            .td_block_template_13 .td-pulldown-category:hover {
                opacity: 0.9;
            }
            .td_block_template_13 .td-pulldown-category:hover span {
                transform: translate3d(-6px, 0, 0);
                -webkit-transform: translate3d(-6px, 0, 0);
            }
            .td_block_template_13 .td-block-subtitle {
                font-size: 90px;
                text-transform: uppercase;
                position: absolute;
                left: -4px;
                z-index: -1;
                bottom: -20px;
                white-space: nowrap;
                color: #f3f3f3;
                line-height: 1;
            }
            @media (min-width: 768px) and (max-width: 1018px) {
                .td_block_template_13 .td-block-subtitle {
                    font-size: 70px;
                    bottom: -15px;
                }
            }
            @media (max-width: 767px) {
                .td_block_template_13 .td-block-subtitle {
                    font-size: 60px;
                    bottom: -12px;
                }
            }
            .td_block_template_13 .td-title-align {
                margin-top: 0 !important;
            }
            .td_block_template_13 .td-title-align  > a,
            .td_block_template_13 .td-title-align > span {
                margin-left: 0 !important;
            }
            @media (min-width: 768px) and (max-width: 1018px) {
                .td-pb-span4 .td_block_template_13 .td-pulldown-category span {
                    display: none;
                }
            }
            @media (min-width: 768px) {
                .td-pb-span4 .td_block_template_13 .td-block-subtitle {
                    display: none;
                }
            }
            .td-pb-span4 .td_block_template_13 .td-block-title {
                margin-top: 0;
            }
            .td-pb-span4 .td_block_template_13 .td-block-title * > {
                margin-left: 0;
            }
            .td-pb-span12 .td_block_template_13 .td-block-title {
                margin-bottom: 40px;
            }
            @media (min-width: 768px) and (max-width: 1018px) {
                .td-pb-span12 .td_block_template_13 .td-block-title {
                    margin-bottom: 26px;
                }
            }

            

            /* @header_text_color */
            .$unique_block_class .td-block-title > a,
            .$unique_block_class .td-block-title > span {
                color: @header_text_color !important;
            }

            /* @big_text_color */
            .$unique_block_class .td-block-subtitle {
                color: @big_text_color !important;
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
        </style>
    ";

        $td_css_compiler = new td_css_compiler(self::get_common_css() . $raw_css );

        /*-- GENERAL -- */
        $td_css_compiler->load_setting_raw( 'style_general_template_13', 1 );

        $td_css_compiler->load_setting_raw('button_color', $this->get_att('button_color'));
        $td_css_compiler->load_setting_raw('header_text_color', $this->get_att('header_text_color'));
        $td_css_compiler->load_setting_raw('accent_text_color', $this->get_att('accent_text_color'));
        $td_css_compiler->load_setting_raw('big_text_color', $this->get_att('big_text_color'));

        $compiled_style = $td_css_compiler->compile_css();


        return $compiled_style;
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

        // description text
        $title_alignment = '';
        $description_text = $this->get_att('big_title_text');
        if (empty($description_text)) {
            $title_alignment = ' td-title-align';
        }


        // there is a custom title
        $buffy = '';
        $buffy .= '<' . $title_tag . ' class="td-block-title' . $title_alignment . '">';
        if (!empty($custom_url)) {
            $buffy .= '<a href="' . esc_url($custom_url) . '">' . esc_html($custom_title) . '</a>';
        } else {
            $buffy .= '<span>' . esc_html($custom_title) . '</span>';
        }

        $buffy .= '<div class="td-block-subtitle">' . esc_html($description_text) . '</div>';

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
