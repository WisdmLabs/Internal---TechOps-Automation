<?php

/**
 * Class td_single_tags
 */

class tdb_single_ctags extends td_block {

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

                /* @style_general_single_tags */
                .tdb_single_tags {
                  margin-bottom: 2px;
                  font-family: var(--td_default_google_font_1, 'Open Sans', 'Open Sans Regular', sans-serif);
                  font-weight: 600;
                }
                .tdb_single_tags span,
                .tdb_single_tags a {
                  font-size: 11px;
                }
                .tdb_single_tags li span {
                  text-transform: uppercase;
                }
                .tdb_single_tags a:hover {
                  background-color: var(--td_theme_color, #4db2ec);
                  border-color: var(--td_theme_color, #4db2ec);
                  color: #fff;
                }
                .tdb_single_tags ul {
                  display: inline-block;
                  margin: 0;
                  list-style-type: none;
                  font-size: 0;
                }
                .tdb_single_tags li {
                  display: inline-block;
                  margin-left: 0;
                }
                
                /* @add_space */
                .$unique_block_class li span {
                    margin-right: @add_space;
                }
                /* @add_padding */
                .$unique_block_class li span {
                    padding: @add_padding;
                }
                /* @add_color */
                .$unique_block_class li span {
                    color: @add_color;
                }
                /* @add_bg */
                .$unique_block_class li span {
                    background-color: @add_bg;
                }
                
                
                /* @tags_space */
                .$unique_block_class a {
                    margin-right: @tags_space;
                }
                /* @tags_padding */
                .$unique_block_class a {
                    padding: @tags_padding;
                }
                /* @all_tags_border */
                .$unique_block_class a {
                    border: @all_tags_border solid @all_tags_border_color;
                }
                /* @tags_color */
                .$unique_block_class a {
                    color: @tags_color;
                }
                /* @tags_bg */
                .$unique_block_class a {
                    background-color: @tags_bg;
                }
                /* @tags_h_color */
                .$unique_block_class a:hover {
                    color: @tags_h_color;
                }
                /* @tags_h_bg */
                .$unique_block_class a:hover {
                    background-color: @tags_h_bg;
                }
                /* @tags_border_h_color */
                .$unique_block_class a:hover {
                    border-color: @tags_border_h_color;
                }
                /* @tags_radius */
                .$unique_block_class a,
                .$unique_block_class span {
                    border-radius: @tags_radius;
                }
				/* @f_add */
				.$unique_block_class li span {
					@f_add
				}
				/* @f_tags */
				.$unique_block_class a {
					@f_tags
				}   
				
			</style>";


        $td_css_res_compiler = new td_css_res_compiler( $raw_css );
        $td_css_res_compiler->load_settings( __CLASS__ . '::cssMedia', $this->get_all_atts() );

        $compiled_css .= $td_css_res_compiler->compile_css();
        return $compiled_css;
    }

    static function cssMedia( $res_ctx ) {

        $res_ctx->load_settings_raw( 'style_general_single_tags', 1 );

        /*-- ADDITIONAL TEXT -- */
        // additional text space
        $add_space = $res_ctx->get_shortcode_att( 'add_space' );
        $res_ctx->load_settings_raw( 'add_space', '4px' );
        if( $add_space != '' && is_numeric( $add_space ) ) {
            $res_ctx->load_settings_raw( 'add_space', $add_space . 'px' );
        }

        // additional text padding
        $add_padding = $res_ctx->get_shortcode_att( 'add_padding' );
        $res_ctx->load_settings_raw( 'add_padding', $add_padding );
        if( $add_padding == '' ) {
            $res_ctx->load_settings_raw( 'add_padding', '2px 8px 3px' );
        } else {
            if ( is_numeric( $add_padding ) ) {
                $res_ctx->load_settings_raw( 'add_padding', $add_padding . 'px' );
            }
        }

        // additional text color
        $add_color = $res_ctx->get_shortcode_att('add_color');
        $res_ctx->load_settings_raw( 'add_color', '#fff' );
        if( $add_color != '' ) {
            $res_ctx->load_settings_raw( 'add_color', $add_color );
        }

        // additional text background
        $add_bg = $res_ctx->get_shortcode_att('add_bg');
        $res_ctx->load_settings_raw( 'add_bg', '#222' );
        if( $add_bg != '' ) {
            $res_ctx->load_settings_raw( 'add_bg', $add_bg );
        }

	    // cat_radius
	    $tags_radius = $res_ctx->get_shortcode_att('tags_radius');
	    if ( $tags_radius != 0 || !empty($tags_radius) ) {
		    $res_ctx->load_settings_raw( 'tags_radius', $tags_radius . 'px' );
	    }

        /*-- SOURCE NAME -- */
        // tags space
        $tags_space = $res_ctx->get_shortcode_att( 'tags_space' );
        $res_ctx->load_settings_raw( 'tags_space', '4px' );
        if( $tags_space != '' && is_numeric( $tags_space ) ) {
            $res_ctx->load_settings_raw( 'tags_space', $tags_space . 'px' );
        }

        // tags padding
        $source_padding = $res_ctx->get_shortcode_att( 'tags_padding' );
        $res_ctx->load_settings_raw( 'tags_padding', $source_padding );
        if( $source_padding != '' ) {
            if(is_numeric( $source_padding )  ) {
                $res_ctx->load_settings_raw( 'tags_padding', $source_padding . 'px' );
            }
        } else {
            $res_ctx->load_settings_raw( 'tags_padding', '1px 7px 2px' );
        }

        // tags border width
        $tags_border = $res_ctx->get_shortcode_att( 'all_tags_border' );
        $res_ctx->load_settings_raw( 'all_tags_border', '1px' );
        if( $tags_border != '' && is_numeric( $tags_border ) ) {
            $res_ctx->load_settings_raw( 'all_tags_border', $tags_border . 'px' );
        }

        // tags text color
        $tags_color = $res_ctx->get_shortcode_att('tags_color');
        $res_ctx->load_settings_raw( 'tags_color', '#111' );
        if( $tags_color != '' ) {
            $res_ctx->load_settings_raw( 'tags_color', $tags_color );
        }

        // tags background
        $res_ctx->load_settings_raw( 'tags_bg', $res_ctx->get_shortcode_att('tags_bg') );

        // tags border color
        $all_tags_border_color = $res_ctx->get_shortcode_att('all_tags_border_color');
        $res_ctx->load_settings_raw( 'all_tags_border_color', '#ededed' );
        if( $all_tags_border_color != '' ) {
            $res_ctx->load_settings_raw( 'all_tags_border_color', $all_tags_border_color );
        }

        // tags text hover color
        $res_ctx->load_settings_raw( 'tags_h_color', $res_ctx->get_shortcode_att('tags_h_color') );

        // tags hover background
        $res_ctx->load_settings_raw( 'tags_h_bg', $res_ctx->get_shortcode_att('tags_h_bg') );

        // tags border hover color
        $res_ctx->load_settings_raw( 'tags_border_h_color', $res_ctx->get_shortcode_att('tags_border_h_color') );



        /*-- FONTS -- */
        $res_ctx->load_font_settings( 'f_add' );
        $res_ctx->load_font_settings( 'f_tags' );

    }

    /**
     * Disable loop block features. This block does not use a loop and it doesn't need to run a query.
     */
    function __construct() {
        parent::disable_loop_block_features();
    }


    function render( $atts, $content = null ) {
        parent::render( $atts ); // sets the live atts, $this->atts, $this->block_uid, $this->td_query (it runs the query)

        global $tdb_state_single;

        $post_tags_data = $tdb_state_single->post_ctags->__invoke($this->get_all_atts());

        // additional text
        $add_text = $this->get_att( 'add_text' );

        $buffy = ''; //output buffer

            $buffy .= '<div class="' . $this->get_block_classes(array('tdb_single_tags')) . '" ' . $this->get_block_html_atts() . '>';

                //get the block css
                $buffy .= $this->get_block_css();

                //get the js for this block
                $buffy .= $this->get_block_js();

        if ( empty( $post_tags_data ) or !is_array( $post_tags_data ) ) {

            if ( td_util::tdc_is_live_editor_iframe() || td_util::tdc_is_live_editor_ajax() ) {

                $buffy .= '<div id=' . $this->block_uid . ' class="td_block_inner td-fix-index">';
                $buffy .= td_util::get_block_error('CPT Custom Tags', 'Fill up the taxonomy name');
                $buffy .= '</div>';

                $buffy .= '</div>';

                return $buffy;

            }

            return $post_tags_data;

        } elseif( !empty( $post_tags_data ) ) {

                $buffy .= '<div class="tdb-block-inner td-fix-index">';
                    $buffy .= '<ul class="tdb-tags">';
                        if( $add_text != '' ) {
                            $buffy .= '<li><span>' . $add_text . '</span></li>';
                        }

                        foreach ( $post_tags_data as $tag_name => $tag_params ) {
                            $buffy .=  '<li><a href="' . $tag_params['url'] . '">' . $tag_name . '</a></li>';
                        }
                    $buffy .= '</ul>';
                $buffy .= '</div>';

            $buffy .= '</div>';
        }

        return $buffy;
    }

}
