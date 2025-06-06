<?php

/**
 * Class td_single_featured_image
 */


class tdb_single_featured_image extends td_block {

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
    
                /* @style_general_featured_image */
                .tdb_single_featured_image {
                  margin-bottom: 26px;
                }
                .tdb_single_featured_image.tdb-sfi-stretch {
                  opacity: 0;
                }
                .tdb_single_featured_image.tdb-sfi-stretch,
                .tdb_single_featured_image .tdb-block-inner {
                  -webkit-transition: all 0.3s ease-in-out;
                  transition: all 0.3s ease-in-out;
                }
                .tdb_single_featured_image img {
                  display: block;
                  width: 100%;
                }
                .tdb_single_featured_image video {
                  max-width: 100%;
                }
                .tdb_single_featured_image .tdb-caption-text {
                  z-index: 1;
                  text-align: left;
                  font-size: 11px;
                  font-style: italic;
                  font-weight: normal;
                  line-height: 17px;
                  color: #444;
                }
                .tdb_single_featured_image.tdb-content-horiz-center .tdb-caption-text {
                  text-align: center;
                  left: 0;
                  right: 0;
                  margin-left: auto;
                  margin-right: auto;
                }
                .tdb_single_featured_image.tdb-content-horiz-right .tdb-caption-text {
                  text-align: right;
                  left:  auto;
                  right: 0;
                }
                .tdb-no-featured-img {
                  background-color: #f1f1f1;
                  width: 100%;
                  height: 500px;
                }
                .tdb-no-featured-audio {
                  height: 59px;
                }

                
                /* @hide_img */
                .$unique_block_class {
                    display: none;
                }
                
                /* @audio_size */
                .$unique_block_class .td-audio-player {
                    font-size: @audio_size;
                }

                /* @overlay_color */
                .$unique_block_class:after {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background-color: @overlay_color;
                    pointer-events: none;
                }
                /* @overlay_gradient */
                .$unique_block_class:after {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    @overlay_gradient
                    pointer-events: none;
                }
                /* @radius */
                .$unique_block_class img {
                    border-radius: @radius;
                }
                /* @caption_margin */
                .$unique_block_class .tdb-caption-text {
                    margin: @caption_margin;
                }
                /* @caption_padding */
                .$unique_block_class .tdb-caption-text {
                    padding: @caption_padding;
                }
                /* @caption_pos */
                .$unique_block_class .tdb-caption-text {
                    position: absolute;
                    bottom: 0;
                    left: 0;
                }
                /* @caption_color */
                .$unique_block_class .tdb-caption-text {
                    color: @caption_color;
                }
                /* @caption_bg */
                .$unique_block_class .tdb-caption-text {
                    background-color: @caption_bg;
                }
                /* @caption_bg_gradient */
                .$unique_block_class .tdb-caption-text {
                    @caption_bg_gradient
                }
                /* @hide_caption */
                .$unique_block_class .tdb-caption-text {
                    display: none;
                }
                
                
                /* @audio_btn_color */
                .$unique_block_class .td-audio-player .mejs-button button:after {
                    color: @audio_btn_color;
                }
                /* @audio_time_color */
                .$unique_block_class .td-audio-player .mejs-time {
                    color: @audio_time_color;
                }
                /* @audio_bar_color */
                .$unique_block_class .td-audio-player .mejs-controls .mejs-time-rail .mejs-time-total,
                .$unique_block_class .td-audio-player .mejs-controls .mejs-horizontal-volume-slider .mejs-horizontal-volume-total {
                    background: @audio_bar_color;
                }
                /* @audio_bar_curr_color */
                .$unique_block_class .td-audio-player .mejs-controls .mejs-time-rail .mejs-time-current,
                .$unique_block_class .td-audio-player .mejs-controls .mejs-horizontal-volume-slider .mejs-horizontal-volume-current {
                    background: @audio_bar_curr_color;
                }
                
                
                /* @f_caption */
                .$unique_block_class .tdb-caption-text {
                    @f_caption
                }
                
                /* @mix_type */
                .$unique_block_class .tdb-block-inner:before {
                    content: '';
                    width: 100%;
                    height: 100%;
                    position: absolute;
                    opacity: 1;
                    transition: opacity 1s ease;
                    -webkit-transition: opacity 1s ease;
                    mix-blend-mode: @mix_type;
                    top: 0;
                    z-index: 1;
                }
                /* @color */
                .$unique_block_class .tdb-block-inner:before {
                    background: @color;
                }
                /* @mix_gradient */
                .$unique_block_class .tdb-block-inner:before {
                    @mix_gradient;
                }
                
                
                /* @mix_type_h */
                @media (min-width: 1141px) {
                    .$unique_block_class .tdb-block-inner:after {
                        content: '';
                        width: 100%;
                        height: 100%;
                        position: absolute;
                        opacity: 0;
                        transition: opacity 1s ease;
                        -webkit-transition: opacity 1s ease;
                        mix-blend-mode: @mix_type_h;
                        top: 0;
                        z-index: 1;
                    }
                    .$unique_block_class:hover .tdb-block-inner:after {
                        opacity: 1;
                    }
                }
                
                /* @color_h */
                .$unique_block_class .tdb-block-inner:after {
                    background: @color_h;
                }
                /* @mix_gradient_h */
                .$unique_block_class .tdb-block-inner:after {
                    @mix_gradient_h;
                }
                /* @mix_type_off */
                .$unique_block_class:hover .tdb-block-inner:before {
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
                    .$unique_block_class:hover .entry-thumb {
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

        $res_ctx->load_settings_raw( 'style_general_featured_image', 1 );

        global $tdb_state_single;
        $post_featured_image_data = $tdb_state_single->post_featured_image->__invoke( $res_ctx->get_atts() );
        if ( $res_ctx->get_shortcode_att('hide_img') == 'yes' && ( $post_featured_image_data['featured_image_info']['src'] == '' && ( !isset( $post_featured_image_data['video'] ) || empty( $post_featured_image_data['video'] ) ) && ( !isset( $post_featured_image_data['audio'] ) || empty( $post_featured_image_data['audio'] ) ) ) ) {
            $res_ctx->load_settings_raw('hide_img', 1);
        }

        // audio player size
        $audio_size = $res_ctx->get_shortcode_att('audio_size');
        if ( is_numeric( $audio_size ) ) {
            $res_ctx->load_settings_raw('audio_size', 10 + $audio_size / 0.5 . 'px');
        }
        // overlay color
        $res_ctx->load_color_settings( 'overlay', 'overlay_color', 'overlay_gradient', '', '' );


        if( $post_featured_image_data['featured_image_info']['src'] != '' ) {
            // image radius
            $radius = $res_ctx->get_shortcode_att( 'radius' );
            $res_ctx->load_settings_raw( 'radius', $radius );
            if( $radius != '' && is_numeric( $radius ) ) {
                $res_ctx->load_settings_raw( 'radius', $radius . 'px' );
            }


            /*-- CAPTION -- */
            // caption margin
            $caption_margin = $res_ctx->get_shortcode_att( 'caption_margin' );
            $res_ctx->load_settings_raw( 'caption_margin', $caption_margin );
            if( $caption_margin != '' ) {
                if( is_numeric( $caption_margin ) ) {
                    $res_ctx->load_settings_raw( 'caption_margin', $caption_margin . 'px' );
                }
            } else {
                $res_ctx->load_settings_raw( 'caption_margin', '6px 0 0' );
            }

            // caption padding
            $caption_padding = $res_ctx->get_shortcode_att( 'caption_padding' );
            $res_ctx->load_settings_raw( 'caption_padding', $caption_padding );
            if( $caption_padding != '' && is_numeric( $caption_padding ) ) {
                $res_ctx->load_settings_raw( 'caption_padding', $caption_padding . 'px' );
            }

            // caption position
            $caption_position = $res_ctx->get_shortcode_att( 'caption_pos' );
            $res_ctx->load_settings_raw('caption_pos', $caption_position);

            // caption color
            $res_ctx->load_settings_raw( 'caption_color', $res_ctx->get_shortcode_att( 'caption_color' ) );

            // caption background color
            $res_ctx->load_color_settings( 'caption_bg', 'caption_bg', 'caption_bg_gradient', '', '' );

            // hide caption
            $res_ctx->load_settings_raw( 'hide_caption', $res_ctx->get_shortcode_att( 'hide_caption' ) );

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

            /*-- FONTS -- */
            $res_ctx->load_font_settings( 'f_caption' );
        }


        if ( isset( $post_featured_image_data['audio'] ) and !empty( $post_featured_image_data['audio'] ) ) {
            // Audio buttons color
            $res_ctx->load_settings_raw( 'audio_btn_color', $res_ctx->get_shortcode_att( 'audio_btn_color' ) );

            // Audio time text color
            $res_ctx->load_settings_raw( 'audio_time_color', $res_ctx->get_shortcode_att( 'audio_time_color' ) );

            // Audio bars background color
            $res_ctx->load_settings_raw( 'audio_bar_color', $res_ctx->get_shortcode_att( 'audio_bar_color' ) );

            // Audio bars progress color
            $res_ctx->load_settings_raw( 'audio_bar_curr_color', $res_ctx->get_shortcode_att( 'audio_bar_curr_color' ) );
        }
    }

    /**
     * Disable loop block features. This block does not use a loop and it doesn't need to run a query.
     */
    function __construct() {
        parent::disable_loop_block_features();
    }


    function render( $atts, $content = null ) {
        parent::render($atts); // sets the live atts, $this->atts, $this->block_uid, $this->td_query (it runs the query)

        global $tdb_state_single;
        $post_featured_image_data = $tdb_state_single->post_featured_image->__invoke( $this->get_all_atts() );


        $additional_classes = array();

        // content align horizontal
        $content_align_horizontal = $this->get_att( 'caption_align_horiz' );
        if( !empty( $content_align_horizontal ) ) {
            $additional_classes[] = 'tdb-' . $content_align_horizontal;
        }

        // auto stretch block class
        $stretch = $this->get_att( 'stretch' );
        $stretch_reverse = $this->get_att( 'stretch_rev' );
        if( !empty( $stretch ) || !empty( $stretch_reverse ) ) {
            $additional_classes[] = 'tdb-sfi-stretch';
        }
        if( !empty( $stretch_reverse ) ) {
            $additional_classes[] = 'tdb-sfi-stretch-right';
        } else if ( !empty( $stretch ) ) {
            $additional_classes[] = 'tdb-sfi-stretch-left';
        }

        $buffy = ''; //output buffer


        $buffy .= '<div class="' . $this->get_block_classes( $additional_classes ) . '" ' . $this->get_block_html_atts() . '>';

            //get the block css
            $buffy .= $this->get_block_css();

            //get the js for this block
            $buffy .= $this->get_block_js();


            $buffy .= '<div class="tdb-block-inner td-fix-index">';
                if( $post_featured_image_data['featured_image_info']['src'] != '' ) {

                    if ( isset( $post_featured_image_data['video'] ) and !empty( $post_featured_image_data['video'] ) ) {
                        $buffy .= $post_featured_image_data['video'];
                    } if ( isset( $post_featured_image_data['audio'] ) and !empty( $post_featured_image_data['audio'] ) ) {
                        $buffy .= $post_featured_image_data['audio'];
                    } else {
                        $show_td_modal_image = $this->get_att( 'lightbox' );
                        $post_featured_image_info = $post_featured_image_data['featured_image_info'];

                        $post_featured_image_full_size_src = '';

                        if ( !empty( $post_featured_image_data['featured_image_full_size_src'] ) and is_array( $post_featured_image_data['featured_image_full_size_src'] ) ) {
                            $post_featured_image_full_size_src = $post_featured_image_data['featured_image_full_size_src']['src'];
                        }

                        if ( TD_DEPLOY_MODE == 'demo' && empty($post_featured_image_info['alt']) ) {
                            $post_featured_image_info['alt'] = $post_featured_image_info['title'];
                        }

                        if ( $show_td_modal_image === 'yes' ) {
                            $image_html = '
                                    <a 
                                        href="' . $post_featured_image_full_size_src . '" 
                                        data-caption="' . esc_attr( $post_featured_image_info['caption'], ENT_QUOTES) . '"
                                    >
                                    ';

                            $image_html .= '
                                    <img 
                                        fetchpriority="high" 
                                        width="' . $post_featured_image_info['width'] . '" 
                                        height="' . $post_featured_image_info['height'] . '" 
                                        class="entry-thumb td-modal-image" 
                                        src="' . $post_featured_image_info['src'] . '"' . $post_featured_image_data['srcset_sizes'] . ' 
                                        alt="' . $post_featured_image_info['alt']  . '" 
                                        title="' . $post_featured_image_info['title'] . '"
                                    />
                                    ';

                            $image_html .= '</a>';
                        } else {
                            $image_html = '
                                    <img 
                                        fetchpriority="high" 
                                        width="' . $post_featured_image_info['width'] . '" 
                                        height="' . $post_featured_image_info['height'] . '" 
                                        class="entry-thumb" 
                                        src="' . $post_featured_image_info['src'] . '"' .  $post_featured_image_data['srcset_sizes'] . ' 
                                        alt="' . $post_featured_image_info['alt']  . '" 
                                        title="' . $post_featured_image_info['title'] . '"
                                    />
                                    ';
                        }

                        // caption - put html5 wrapper on when we have a caption
                        if ( !empty( $post_featured_image_info['caption'] ) ) {
                            $buffy .= '<figure>';
                            $buffy .= $image_html;

                            $buffy .= '<figcaption class="tdb-caption-text">' . $post_featured_image_info['caption'] . '</figcaption>';
                            $buffy .= '</figure>';
                        } else {
                            $buffy .= $image_html;
                        }
                    }
                } else if ( isset( $post_featured_image_data['video'] ) and !empty( $post_featured_image_data['video'] ) ) {
                    $buffy .= $post_featured_image_data['video'];
                } else if ( isset( $post_featured_image_data['audio'] ) and !empty( $post_featured_image_data['audio'] ) ) {
                    $buffy .= $post_featured_image_data['audio'];
                } else {
                    if( $post_featured_image_data['post_format'] == 'audio' ) {
                        $buffy .= '<div class="tdb-no-featured-img tdb-no-featured-audio"></div>';
                    } else {
                        $buffy .= '<div class="tdb-no-featured-img"></div>';
                    }
                }
            $buffy .= '</div>';

            ob_start();
            ?>
            <script>
                jQuery(window).on( 'load', function () {
                    var block = jQuery('.<?php echo $this->block_uid; ?>'),
                        blockClass = '.<?php echo $this->block_uid; ?>',
                        blockInner = block.find('.tdb-block-inner'),
                        blockOffsetLeft;

                    if( block.find('audio').length > 0 ) {
                        jQuery(blockClass + ' audio').mediaelementplayer();
                    }

                    if( block.hasClass('tdb-sfi-stretch') ) {
                        jQuery(window).resize(function () {
                            blockOffsetLeft = block.offset().left;

                            if( block.hasClass('tdb-sfi-stretch-left') ) {
                                blockInner.css('margin-left', -blockOffsetLeft + 'px');
                            } else {
                                blockInner.css('margin-right', -(jQuery(window).width() - (blockOffsetLeft + block.outerWidth())) + 'px');
                            }
                        });
                        jQuery(window).resize();
                    }

                    setTimeout(function () {
                        block.css('opacity', 1);
                    }, 500);
                });
            </script>
            <?php
            td_js_buffer::add_to_footer( "\n" . td_util::remove_script_tag( ob_get_clean() ) );
        $buffy .= '</div>';

        return $buffy;

    }

    function js_tdc_callback_ajax() {
        $buffy = '';

        // add a new composer block - that one has the delete callback
        $buffy .= $this->js_tdc_get_composer_block();

        ob_start();

        ?>
        <script>
            /* global jQuery:{} */
            (function () {
                var block = jQuery('.<?php echo $this->block_uid; ?>'),
                    blockClass = '.<?php echo $this->block_uid; ?>',
                    blockInner = block.find('.tdb-block-inner'),
                    blockOffsetLeft;

                if( block.find('audio').length > 0 ) {
                    jQuery(blockClass + ' audio').mediaelementplayer();
                }

                if( block.hasClass('tdb-sfi-stretch') ) {
                    jQuery(window).resize(function () {
                        blockOffsetLeft = block.offset().left;

                        if( block.hasClass('tdb-sfi-stretch-left') ) {
                            blockInner.css('margin-left', -blockOffsetLeft + 'px');
                        } else {
                            blockInner.css('margin-right', -(jQuery(window).width() - (blockOffsetLeft + block.outerWidth())) + 'px');
                        }
                    });
                    jQuery(window).resize();
                }

                setTimeout(function () {
                    block.css('opacity', 1);
                }, 500);
            })();


        </script>
        <?php

        return $buffy . td_util::remove_script_tag( ob_get_clean() );
    }

}
