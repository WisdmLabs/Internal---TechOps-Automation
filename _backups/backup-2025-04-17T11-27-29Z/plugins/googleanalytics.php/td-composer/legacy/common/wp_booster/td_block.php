<?php

/**
 * Class td_block - base class for blocks
 * v 5.1 - td-composer edition :)
 */


class td_block {
	var $block_uid; // the block unique id on the page, it changes on every render
	var $td_query; //the query used to rendering the current block
	protected $td_block_template_data;

	private $atts = array(); //the atts used for rendering the current block
	private $td_block_template_instance; // the current block template instance that this block is using


	// by default all the blocks are loop blocks
	private $is_loop_block = true; // if it's a loop block, we will generate AJAX js, pulldown items and other stuff
	private $is_products_block = false; // if it's a products block, we will build query using products type

    /**
     * the base render function. This is called by all the child classes of this class
     * @param $atts
     * @param $content
     * @return string ''
     */
    function render( $atts, $content = null ) {

	    // build the $this->atts
        $this->atts = (array) $atts;

        // block_type
	    $this->atts['block_type'] = get_class($this);

        // bring default values for atts that are not set or that are missing
        // NOTE: atts that are already set in $atts remain the same (big blocks, mega menu and related articles set atts dynamically in the
        // shortcode)
        // NOTE 2: DOES NOT SUPPORT STYLES YET. we have to bring here the style atts also!
        $block_map = td_api_block::get_by_id(get_class($this));
        if ( isset( $block_map['params'] ) ) {
            $mapped_params = $block_map['params'];
            foreach ( $mapped_params as $mapped_param ) {
                $value = $mapped_param['value'];


                // for arrays
                if ( is_array( $value ) ) {


                    if (empty($value)) {
                        /**
                         * some map helper functions return an empty array on the frontend for optimizations.
                         * They don't load the full map only in wp-admin
                         * @see td_util::get_block_template_ids
                         * @see td_util::get_category2id_array
                         */
                        $value = '';
                    } else {
                        /**
                         * if the array has values, we select the first one
                         */
                        foreach ( $value as $key => $val ) {
                            $value = $val;
                            break;
                        }
                    }


                }
                $param_name = $mapped_param['param_name'];
                //var_dump($this->atts);
                if ( !isset( $this->atts[$param_name] ) ) {
                    $this->atts[$param_name] = $value;
                }
            }
        }

        $this->atts = $this->add_live_filter_atts($this->atts); // add live filter atts

        // !!!! code in this class relies on this to be set regardless of what is mapped.
        // !!!! ori le scoatem intr-o clasa separata pentru block-uri cu module ori scoatem dependinta pt atributele astea
        $this->set_default_atts(array(
            'block_template_id' => '',
            'td_column_number' => td_global::vc_get_column_number(),
            'header_color' => '',
            'ajax_pagination_infinite_stop' => '',
            'offset' => '',
            'limit' => '5',
            'td_ajax_preloading' => '',
            'td_ajax_filter_type' => '',
            'td_filter_default_txt' => '',
            'td_ajax_filter_ids' => '',
            'el_class' => '',
            'color_preset' => '',
            'ajax_pagination' => '',
            'ajax_pagination_next_prev_swipe' => '',
            'border_top' => '',
            'css' => '', //custom css - used by VC
            'tdc_css' => ''
        ));


	    //update unique id on each render
        $this->block_uid = td_global::td_generate_unique_id();

	    /** add the unique class to the block. The _rand class is used by the blocks js. @see tdBlocks.js  */
	    $unique_block_class = $this->block_uid;
	    $this->add_class($unique_block_class);

	    // Set the 'tdc_css_class' parameter
	    $this->atts['tdc_css_class'] = $unique_block_class;

	    /** The _rand_style class is used by td-element-style to add style */
	    $unique_block_class_style = $this->block_uid . '_rand_style';
	    $this->atts['tdc_css_class_style'] = $unique_block_class_style;

	    $td_pull_down_items = array();

	    // do the query and make the AJAX filter only on loop blocks
		if ( $this->is_loop_block() === true ) {

		    // Adapt _current category id to work with global $tdb_state_category
		    if ( isset( $atts['category_id'] ) ) {

		        switch ( $this->atts['category_id'] ) {
                    case '_current_cat':
                        global $tdb_state_category;
                        $category_wp_query = $tdb_state_category->get_wp_query();

                        if ( isset( $category_wp_query->query['cat'] ) ) {
                            $category_obj = get_category( $category_wp_query->query['cat'] );
                        } elseif( isset( $category_wp_query->query_vars['category_name'] ) ) {
                            $category_obj = get_category_by_slug( $category_wp_query->query_vars['category_name'] );
                        }

                        if ( ! empty( $category_obj ) ) {
                            $this->atts['category_id'] = $category_obj->term_id;
                        }
                        break;
                    case '_more_author':
                    case '_related_cat':
                    case '_related_tag':
                    case '_related_tax':
                    case '_related_siblings':
                        global $tdb_state_single;

                        if ( ! empty( $tdb_state_single ) ) {

                            $single_wp_query = $tdb_state_single->get_wp_query();

                            if ( ! empty ( $single_wp_query ) ) {

                                if ( ! empty ( $single_wp_query->queried_object ) ) {

                                    if ( '_more_author' === $this->atts['category_id'] && ! empty( $single_wp_query->queried_object->post_author )) {

                                        $this->atts['live_filter'] = 'cur_post_same_author';
                                        $this->atts['live_filter_cur_post_author'] = $single_wp_query->queried_object->post_author;

                                    } else {

                                        if ( '_related_cat' === $this->atts['category_id'] ) {
                                            $this->atts['live_filter'] = 'cur_post_same_categories';
                                        } else if ( '_related_tag' === $this->atts['category_id'] ) {
                                            $this->atts['live_filter'] = 'cur_post_same_tags';
                                        } else if ( '_related_tax' === $this->atts['category_id'] ) {
                                            $this->atts['live_filter'] = 'cur_post_same_taxonomies';
                                        } else if ( '_related_siblings' === $this->atts['category_id'] ) {
                                            $this->atts['live_filter'] = 'cur_post_siblings';
                                        }

                                        $this->atts['live_filter_cur_post_id'] = $single_wp_query->queried_object->ID;
                                        $this->atts['live_filter_cur_post_parent_id'] = $single_wp_query->queried_object->post_parent;
                                    }

                                } else if ( ! empty( $single_wp_query->post ) && $single_wp_query->post instanceof WP_Post ) {

                                    if ( '_more_author' === $this->atts['category_id'] ) {
                                        $this->atts['live_filter'] = 'cur_post_same_author';
                                        $this->atts['live_filter_cur_post_author'] = $single_wp_query->post->post_author;

                                    } else {

                                        if ( '_related_cat' === $this->atts['category_id'] ) {
                                            $this->atts['live_filter'] = 'cur_post_same_categories';
                                        } else if ( '_related_tag' === $this->atts['category_id'] ) {
                                            $this->atts['live_filter'] = 'cur_post_same_tags';
                                        } else if ( '_related_tax' === $this->atts['category_id'] ) {
                                            $this->atts['live_filter'] = 'cur_post_same_taxonomies';
                                        } else if ( '_related_siblings' === $this->atts['category_id'] ) {
                                            $this->atts['live_filter'] = 'cur_post_siblings';
                                        }

                                        $this->atts['live_filter_cur_post_id'] = $single_wp_query->post->ID;
                                        $this->atts['live_filter_cur_post_parent_id'] = $single_wp_query->post->post_parent;
                                    }
                                }
                            }
                        }
                        break;
                    case '_current_author':
                        global $tdb_state_author;

                        if ( ! empty( $tdb_state_author ) ) {
                            $author_wp_query = $tdb_state_author->get_wp_query();

                            if ( ! empty( $author_wp_query ) ) {
                                $this->atts[ 'live_filter' ] = 'cur_post_same_author';

                                if (!empty( $author_wp_query->query_vars['author'])){
                                    $this->atts[ 'live_filter_cur_post_author' ] = $author_wp_query->query_vars['author'];
                                } else if ( !empty( $author_wp_query->queried_object ) ) {
	                                $this->atts[ 'live_filter_cur_post_author' ] = $author_wp_query->queried_object->ID;
                                }
                            }
                        }

                        break;
                    case '_current_tag':
                        global $tdb_state_tag;

                        if ( !empty( $tdb_state_tag ) ) {
                            $tag_wp_query = $tdb_state_tag->get_wp_query();

                            if ( !empty( $tag_wp_query ) ) {

                                if ( !empty( $tag_wp_query->queried_object_id ) ) {
	                                $tag_obj = get_tag( $tag_wp_query->queried_object_id );
                                } elseif ( !empty( $tag_wp_query->queried_object ) ) {
	                                $tag_obj = get_tag( $tag_wp_query->queried_object->ID );
                                } elseif ( isset( $tag_wp_query->query['tag'] ) ) {
	                                $tag_obj = get_term_by( 'slug', $tag_wp_query->query['tag'], 'post_tag' );
                                }

	                            if ( !empty( $tag_obj ) ) {
		                            $this->atts['tag_id'] = $tag_obj->term_id;
	                            }

                            }
                        }

                        break;
                    case '_current_date':
                        global $tdb_state_date;

                        if ( !empty( $tdb_state_date ) ) {
                            $date_wp_query = $tdb_state_date->get_wp_query();

                            if ( !empty( $date_wp_query ) ) {

                                $current_date_query = array();

	                            $current_date_query['year'] = isset( $date_wp_query->query['year'] ) ? (int) $date_wp_query->query['year'] : '';
	                            $current_date_query['month'] = isset( $date_wp_query->query['monthnum'] ) ? ltrim( $date_wp_query->query['monthnum'], '0' ) : '';
	                            $current_date_query['day'] = isset( $date_wp_query->query['day'] ) ? (int) $date_wp_query->query['day'] : '';

	                            $this->atts['date_query'] = $current_date_query;

                            }
                        }

                        break;
                    case '_current_search':
                        global $wp_query, $tdb_state_search;

                        if ( !empty( $tdb_state_search ) ) {
	                        $search_wp_query = $tdb_state_search->get_wp_query();

	                        if ( !empty( $search_wp_query ) ) {

		                        $search_template_wp_query = $wp_query;
		                        $wp_query = $search_wp_query;

		                        $this->atts['search_query'] = get_search_query();

		                        $wp_query = $search_template_wp_query;

	                        }
                        }

                        break;
                    case '_current_tax':
	                    global $tdb_state_category;

	                    if ( !empty( $tdb_state_category ) ) {
		                    $tax_wp_query = $tdb_state_category->get_wp_query();

		                    if ( !empty( $tax_wp_query ) ) {

			                    if ( isset( $tax_wp_query->queried_object->term_id ) ) {
				                    $this->atts['category_id'] = $tax_wp_query->queried_object->term_id;
			                    } elseif ( isset( $tax_wp_query->query['tax_query'][0]['terms'] ) ) {
				                    $this->atts['category_id'] = $tax_wp_query->query['tax_query'][0]['terms'];
			                    }

		                    }
	                    }

                        break;
                }

            }

            // process {{cf_tag-slug}} filters
            if ( isset( $this->atts['tag_slug'] ) ) {
                $this->atts['tag_slug'] = td_util::get_custom_field_value_from_string($this->atts['tag_slug']);
            }

			// these products blocks work with products ids data type
			if ( $this->is_products_block() ) {
				$this->td_query = &td_data_source::get_wp_query( $this->atts, '', 'products', get_class($this) );
			} else {
                // exclude current post from blocks
                if ( is_single() ) {
                    global $post;
                    if ( !empty($post) ) {
                        $post_id = "$post->ID"; //string needed for strpos()
                        // do not run if the post id is already in the list
                        if (isset($this->atts['post_ids']) && strpos($this->atts['post_ids'], $post_id) === false) {
                            if ($this->atts['post_ids'] == '') {
                                $this->atts['post_ids'] = '-' . $post_id;
                            } else {
                                $this->atts['post_ids'] = '-' . $post_id . ',' . $this->atts['post_ids'];
                            }
                        }
                    }
                }

                // paged
                $paged = '';
				if ( isset( $atts['page'] ) ) {
					$paged = intval( $atts['page'] );
                }

				// by ref do the query
				$this->td_query = &td_data_source::get_wp_query( $this->atts, $paged, '', get_class($this) );
            }

			// get the pull down items
			$td_pull_down_items = $this->block_loop_get_pull_down_items();

		}

        /**
         * Make a new block template instance (NOTE: ON EACH RENDER WE GENERATE A NEW BLOCK TEMPLATE)
         * td_block_template_x - Loaded via autoload
         * @see td_autoload_classes::loading_classes
         */

	    if ( td_util::is_mobile_theme() ) {

		    // The mobile theme uses only 'td_block_template_1' (in api this is the only registered block template)
		    $td_block_template_id = 'td_block_template_1';

	    } else {
		    $td_block_template_id = $this->atts['block_template_id'];
		    if ( empty( $td_block_template_id ) ) {
			    $td_block_template_id = td_options::get( 'tds_global_block_template', 'td_block_template_1' );
		    }

		    /**
	         * This allows us to overwrite the block templates that are in the theme on each demo.
	         * it loads the block template from the demo folder ONLY IF it exists
	         * @since 7/12/2016
	         */
	        $demo_id = td_util::get_loaded_demo_id();
	        if ( $demo_id !== false ) {
                $custom_block_template_path = td_global::$demo_list[$demo_id]['folder'] . realpath( $td_block_template_id ) . '.php';

                if ( file_exists($custom_block_template_path) ) {
                    require_once $custom_block_template_path;
                }

	        }
	    }

        $this->td_block_template_data = array(
            'atts' => $this->atts,
            'block_uid' => $this->block_uid,
            'unique_block_class' => $unique_block_class,
            'td_pull_down_items' => $td_pull_down_items,
        );

        if( in_array( $td_block_template_id, array_keys( td_api_block_template::get_all() ), true ) ) {
            $this->td_block_template_instance = new $td_block_template_id($this->td_block_template_data);
        }

	    return '';
    }


    /**
     * @deprecated Fix the 8.6 - 8.7 transition. If the old td-composer plugin from 8.6 was used on 8.7 we got an error.
     * @param $atts
     */
    function set_font_settings($atts) {

    }


    private function set_default_atts($default_atts) {
        foreach ($default_atts as $att => $att_value) {
            if (!isset($this->atts[$att])) {
                $this->atts[$att] = $att_value;
            }
        }
    }


    static function get_common_css() {
        $raw_css =
            "<style>
                /* @style_general_cat_bgf */
                .tdb-category-grids {
                  width: 100%;
                  padding-bottom: 0;
                }
                .tdb-category-grids .tdb-block-inner:after,
                .tdb-category-grids .tdb-block-inner .tdb-cat-grid-post:after {
                  content: '';
                  display: table;
                  clear: both;
                }
                @media (max-width: 767px) {
                  .tdb-category-grids .tdb-block-inner {
                    margin-left: -20px;
                    margin-right: -20px;
                  }
                }
                .tdb-category-grids .tdb-cat-grid-post {
                  position: relative;
                  float: left;
                  padding-bottom: 0;
                }
                .tdb-category-grids .td-image-container {
                  position: relative;
                  flex: 0 0 100%;
                  width: 100%;
                  height: 100%;
                }
                .tdb-category-grids .td-image-wrap {
                  position: relative;
                  display: block;
                  overflow: hidden;
                }
                .tdb-category-grids .td-image-wrap:before {
                  position: absolute;
                  bottom: 0;
                  left: 0;
                  width: 100%;
                  height: 100%;
                  -webkit-transition: background-color 0.3s ease;
                  transition: background-color 0.3s ease;
                  z-index: 1;
                }
                .tdb-category-grids .td-module-thumb {
                  position: relative;
                  margin-bottom: 0;
                }
                .tdb-category-grids .td-module-thumb:after {
                  position: absolute;
                  top: 0;
                  left: 0;
                  width: 100%;
                  height: 100%;
                }
                .tdb-category-grids .td-module-thumb .td-thumb-css {
                  -webkit-transition: all 0.3s ease;
                  transition: all 0.3s ease;
                }
                .tdb-category-grids .td-thumb-css {
                  width: 100%;
                  height: 100%;
                  position: absolute;
                  background-size: cover;
                  background-position: center center;
                }
                .tdb-category-grids .td-module-meta-info {
                  position: absolute;
                  left: 0;
                  margin-bottom: 0;
                  width: 100%;
                  pointer-events: none;
                  z-index: 1;
                }
                .tdb-category-grids .td-post-category {
                  padding: 3px 7px;
                  background-color: rgba(0, 0, 0, 0.7);
                  font-family: var(--td_default_google_font_2, 'Roboto', sans-serif);
                  line-height: 13px;
                  font-weight: 500;
                  text-transform: uppercase;
                  pointer-events: auto;
                  -webkit-transition: background-color 0.2s ease;
                  transition: background-color 0.2s ease;
                }
                .tdb-category-grids .td-module-title a,
                .tdb-category-grids .td-post-author-name span,
                .tdb-category-grids .td-module-container:hover .entry-title a,
                .tdb-category-grids .td-post-author-name a,
                .tdb-category-grids .td-post-date {
                  color: #fff;
                }
                .tdb-category-grids .td-module-title {
                  margin: 0;
                }
                .tdb-category-grids .td-module-title a {
                  text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
                }
                .tdb-category-grids .td-editor-date {
                  display: inline-block;
                }
                .tdb-category-grids .td-post-author-name a,
                .tdb-category-grids .td-post-author-name span,
                .tdb-category-grids .td-post-date {
                  text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.3);
                }
                .tdb-category-grids .tdb-cat-grid-post-empty .td-image-wrap {
                  background-color: #e5e5e5;
                }
                .tdb-category-grids .tdb-cat-grid-post-empty .td-image-wrap:before {
                  display: none;
                }
                @media (min-width: 767px) {
                  .tdb-cat-grid-lightsky .td-image-wrap:after {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 160%;
                    height: 100%;
                    background: rgba(255, 255, 255, 0.2);
                    -webkit-transform: scale3d(1.9, 1.4, 1) rotate3d(0, 0, 1, 45deg) translate3d(0, -120%, 0);
                    transform: scale3d(1.9, 1.4, 1) rotate3d(0, 0, 1, 45deg) translate3d(0, -120%, 0);
                    -webkit-transition: transform 0.7s ease 0s;
                    transition: transform 0.7s ease 0s;
                    z-index: 1;
                  }
                  .tdb-cat-grid-lightsky .td-module-container:hover .td-image-wrap:after {
                    -webkit-transform: scale3d(1.9, 1.4, 1) rotate3d(0, 0, 1, 45deg) translate3d(0, 146%, 0);
                    transform: scale3d(1.9, 1.4, 1) rotate3d(0, 0, 1, 45deg) translate3d(0, 146%, 0);
                  }
                }
                @media (max-width: 767px) {
                  div.tdb-cat-grid-scroll .tdb-cat-grid-post {
                    float: none;
                  }
                  div.tdb-cat-grid-scroll .tdb-cat-grid-scroll-holder {
                    overflow-x: auto;
                    overflow-y: hidden;
                    white-space: nowrap;
                    font-size: 0;
                    -webkit-overflow-scrolling: touch;
                  }
                  div.tdb-cat-grid-scroll .tdb-cat-grid-scroll-holder .tdb-cat-grid-post {
                    display: inline-block;
                    vertical-align: top;
                  }
                  div.tdb-cat-grid-scroll .td-module-title a {
                    white-space: normal;
                  }
                }

                
                /* @style_general_bgf */
                .td-big-grid-flex {
                    width: 100%;
                    padding-bottom: 0;
                }
                .td-big-grid-flex .td_block_inner:after,
                .td-big-grid-flex .td_block_inner .td-big-grid-flex-post:after {
                    content: '';
                    display: table;
                    clear: both;
                }
                @media (max-width: 767px) {
                    .td-big-grid-flex .td_block_inner {
                        margin-left: -20px;
                        margin-right: -20px;
                    }
                }
                .td-big-grid-flex .td-big-grid-flex-post {
                    position: relative;
                    float: left;
                    padding-bottom: 0;
                }
                .td-big-grid-flex .td-image-container {
                    position: relative;
                    flex: 0 0 100%;
                    width: 100%;
                    height: 100%;
                }
                .td-big-grid-flex .td-image-wrap {
                    position: relative;
                    display: block;
                    overflow: hidden;
                }
                .td-big-grid-flex .td-image-wrap:before {
                    position: absolute;
                    bottom: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    -webkit-transition: background-color 0.3s ease;
                    transition: background-color 0.3s ease;
                    z-index: 1;
                }
                .td-big-grid-flex .td-module-thumb {
                    position: relative;
                    margin-bottom: 0;
                }
                .td-big-grid-flex .td-module-thumb:after {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                }
                .td-big-grid-flex .td-thumb-css {
                    width: 100%;
                    height: 100%;
                    position: absolute;
                    background-size: cover;
                    background-position: center center;
                }
                .td-big-grid-flex .td-module-thumb .td-thumb-css {
                    transition: opacity 0.3s, transform 0.3s;
                    -webkit-transition: opacity 0.3s, transform 0.3s;
                }
                .td-big-grid-flex .td-post-category {
                    transition: background-color 0.2s ease;
                    -webkit-transition: background-color 0.2s ease;
                }
                .td-big-grid-flex .td-module-meta-info {
                    position: absolute;
                    left: 0;
                    margin-bottom: 0;
                    width: 100%;
                    pointer-events: none;
                    z-index: 1;
                }
                .td-big-grid-flex .td-post-category {
                    padding: 3px 7px;
                    background-color: rgba(0, 0, 0, 0.7);
                    font-family: var(--td_default_google_font_2, 'Roboto', sans-serif);
                    line-height: 13px;
                    font-weight: 500;
                    text-transform: uppercase;
                    pointer-events: auto;
                }
                .td-big-grid-flex .td-module-title a,
                .td-big-grid-flex .td-post-author-name span,
                .td-big-grid-flex .td-module-container:hover .entry-title a,
                .td-big-grid-flex .td-post-author-name a,
                .td-big-grid-flex .td-post-date {
                    color: #fff;
                }
                .td-big-grid-flex .td-module-title {
                    margin: 0;
                }
                .td-big-grid-flex .td-module-title a {
                    text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
                }
                .td-big-grid-flex .td-editor-date {
                    display: inline-block;
                }
                .td-big-grid-flex .td-post-author-name a,
                .td-big-grid-flex .td-post-author-name span,
                .td-big-grid-flex .td-post-date {
                    text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.3);
                }
                .td-big-grid-flex .td-big-grid-flex-post-empty .td-image-wrap {
                    background-color: #e5e5e5;
                }
                .td-big-grid-flex .td-big-grid-flex-post-empty .td-image-wrap:before {
                    display: none;
                }
                @media (min-width: 767px) {
                    .td-big-grid-flex-lightsky .td-image-wrap:after {
                        content: '';
                        position: absolute;
                        top: 0;
                        left: 0;
                        width: 160%;
                        height: 100%;
                        background: rgba(255, 255, 255, 0.2);
                        transform: scale3d(1.9, 1.4, 1) rotate3d(0, 0, 1, 45deg) translate3d(0, -120%, 0);
                        -webkit-transform: scale3d(1.9, 1.4, 1) rotate3d(0, 0, 1, 45deg) translate3d(0, -120%, 0);
                        transition: transform 0.7s ease 0s;
                        -webkit-transition: transform 0.7s ease 0s;
                        z-index: 1;
                    }
                    .td-big-grid-flex-lightsky .td-module-container:hover .td-image-wrap:after {
                        transform: scale3d(1.9, 1.4, 1) rotate3d(0, 0, 1, 45deg) translate3d(0, 146%, 0);
                        -webkit-transform: scale3d(1.9, 1.4, 1) rotate3d(0, 0, 1, 45deg) translate3d(0, 146%, 0);
                    }
                }
                @media (max-width: 767px) {
                    div.td-big-grid-flex-scroll .td-big-grid-flex-post {
                        float: none;
                    }
                    div.td-big-grid-flex-scroll .td-big-grid-flex-scroll-holder {
                        overflow-x: auto;
                        overflow-y: hidden;
                        white-space: nowrap;
                        font-size: 0;
                        -webkit-overflow-scrolling: touch;
                    }
                    div.td-big-grid-flex-scroll .td-big-grid-flex-scroll-holder .td-big-grid-flex-post {
                        display: inline-block;
                        vertical-align: top;
                    }
                    div.td-big-grid-flex-scroll .td-module-title a {
                        white-space: normal;
                    }
                }
                
                
                
                /* @style_general_video */
                .td_video_playlist_title {
                    position: relative;
                    z-index: 1;
                    background-color: #222;
                }
                .td_video_playlist_title .td_video_title_text {
                    font-family: var(--td_default_google_font_1, 'Open Sans', 'Open Sans Regular', sans-serif);
                    font-weight: bold;
                    font-size: 15px;
                    color: #ffffff;
                    margin-left: 17px;
                    margin-right: 17px;
                    vertical-align: middle;
                    line-height: 24px;
                    padding: 10px 0 10px 0;
                }
                @media (max-width: 767px) {
                    .td_video_playlist_title .td_video_title_text {
                        text-align: center;
                    }
                }
                .td_wrapper_video_playlist {
                    z-index: 1;
                    position: relative;
                    display: flex;
                }
                .td_wrapper_video_playlist .td_video_controls_playlist_wrapper {
                    background-color: @td_theme_color;
                    position: relative;
                }
                .td_wrapper_video_playlist .td_video_controls_playlist_wrapper:before {
                    content: '';
                    background:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAAGBAMAAADwPukCAAAAElBMVEUAAAAAAAAAAAAAAAAAAAAAAADgKxmiAAAABnRSTlM9KRgMBADiSB2HAAAAFElEQVR4XmNgYBBgUGAwYHBgCAAAA3wA8fpXm6EAAAAASUVORK5CYII=) repeat-x;
                    width: 100%;
                    height: 6px;
                    position: absolute;
                    bottom: -6px;
                    z-index: 1;
                }
                .td_wrapper_video_playlist .td_video_stop_play_control {
                    position: relative;
                    width: 65px;
                    height: 65px;
                    outline: 0 !important;
                }
                .td_wrapper_video_playlist .td_video_stop_play_control:after {
                    content: '';
                    width: 1px;
                    height: 37px;
                    background-color: rgba(255, 255 ,255 ,0.2);
                    position: absolute;
                    top: 14px;
                    right: 0;
                }
                .td_wrapper_video_playlist .td_youtube_control,
                .td_wrapper_video_playlist .td_vimeo_control {
                    position: relative;
                    top: 12px;
                    left: 11px;
                    cursor: pointer;
                }
                .td_wrapper_video_playlist .td_video_title_playing {
                    position: absolute;
                    top: 13px;
                    left:80px;
                    font-family: Verdana, Geneva, sans-serif;
                    font-size: 13px;
                    line-height: 19px;
                    font-weight: bold;
                    color: #ffffff;
                    padding-right: 7px;
                    max-height: 37px;
                    overflow: hidden;
                }
                @media (min-width: 481px) and (max-width: 1018px) {
                    .td_wrapper_video_playlist .td_video_title_playing {
                        max-height: 20px;
                        top: 23px;
                    }
                }
                @media (max-width: 480px) {
                    .td_wrapper_video_playlist .td_video_title_playing {
                        max-height: 37px;
                        top: 13px;
                    }
                }
                .td_wrapper_video_playlist .td_video_time_playing {
                    position: absolute;
                    bottom:0;
                    right:5px;
                    font-family: var(--td_default_google_font_1, 'Open Sans', 'Open Sans Regular', sans-serif);
                    font-size: 10px;
                    font-style: italic;
                    color: #ffffff;
                    line-height: 17px;
                    padding-right: 1px
                }
                .td_wrapper_video_playlist .td_video_currently_playing {
                    background-color: #404040;
                }
                .td_wrapper_video_playlist .td_video_currently_playing:after {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    border-left: 3px solid #4db2ec !important;
                    width: 3px;
                    height: inherit;
                }
                .td_wrapper_video_playlist .td_click_video {
                    height: 60px;
                    display: block;
                    width: 100%;
                    position: relative;
                }
                .td_wrapper_video_playlist .td_click_video:hover {
                    background-color: #333333;
                    cursor: pointer;
                }
                .td_wrapper_video_playlist .td_video_thumb {
                    position: relative;
                    top: 10px;
                    width: 72px;
                    height: 40px;
                    overflow: hidden;
                    margin-left: 16px;
                }
                .td_wrapper_video_playlist .td_video_thumb img {
                    position: relative;
                    top: -6px;
                }
                .td_wrapper_video_playlist .td_video_title_and_time {
                    position: absolute;
                    top: 10px;
                    width: 100%;
                    padding: 0 30px 0 103px;
                }
                .td_wrapper_video_playlist .td_video_title_and_time .td_video_title {
                    font-family: var(--td_default_google_font_1, 'Open Sans', 'Open Sans Regular', sans-serif);
                    font-size: 12px;
                    color: #ffffff;
                    line-height: 15px;
                    max-height: 30px;
                    overflow: hidden;
                }
                .td_wrapper_video_playlist .td_video_time {
                    font-family: var(--td_default_google_font_1, 'Open Sans', 'Open Sans Regular', sans-serif);
                    font-size: 10px;
                    font-style: italic;
                    color: #cacaca;
                    line-height: 13px;
                }
                .td_wrapper_video_playlist .td_wrapper_player {
                    background-color: #000;
                    overflow: hidden;
                }
                @media (max-width: 1018px) {
                    .td_wrapper_video_playlist .td_wrapper_player {
                        flex: auto !important;
                    }
                }
                @media (max-width: 767px) {
                    .td_wrapper_video_playlist .td_wrapper_player {
                        margin-bottom: -5px;
                    }
                }
                .td_wrapper_video_playlist .td_wrapper_player iframe {
                    width: 100%;
                    height: 100% !important;
                }
                .td_wrapper_video_playlist .td_container_video_playlist {
                    display: flex;
                    flex-direction: column;
                    background-color: #222;
                    vertical-align: top;
                    overflow: hidden;
                }
                .td_wrapper_video_playlist .td_playlist_clickable {
                    overflow-y: auto;
                    overflow-x: hidden;
                }
                @media (max-width: 1018px) {
                    .td_video_playlist_column_3 .td_wrapper_video_playlist {
                        flex-direction: column;
                    }
                }
                .td_video_playlist_column_3 .td_wrapper_player,
                .td_video_playlist_column_3 .td_container_video_playlist {
                    height: 409px;
                }
                @media (min-width: 1019px) and (max-width: 1140px) {
                    .td_video_playlist_column_3 .td_wrapper_player,
                    .td_video_playlist_column_3 .td_container_video_playlist {
                        height: 365px;
                    }
                }
                .td_video_playlist_column_3 .td_wrapper_player {
                    display: block;
                    flex: 1;
                }
                @media (min-width: 768px) and (max-width: 1018px) {
                    .td_video_playlist_column_3 .td_wrapper_player {
                        width: 100%;
                        height: 416px;
                    }
                }
                @media (max-width: 767px) {
                    .td_video_playlist_column_3 .td_wrapper_player {
                        width: 100%;
                        height: 260px;
                    }
                }
                .td_video_playlist_column_3 .td_container_video_playlist {
                    width: 341px;
                }
                @media (min-width: 1019px) and (max-width: 1140px) {
                    .td_video_playlist_column_3 .td_container_video_playlist {
                        width: 331px;
                    }
                }
                @media (max-width: 1018px) {
                    .td_video_playlist_column_3 .td_container_video_playlist {
                        width: 100%;
                        height: 305px;
                    }
                }
                @media screen and (-webkit-min-device-pixel-ratio:0) and (min-width: 768px) and (max-width: 1018px) {
                    .td_video_playlist_column_3 .td_playlist_clickable.td_add_scrollbar_to_playlist_for_mobile {
                        margin-right: 10px;
                    }
                }
                @media screen and (-webkit-min-device-pixel-ratio:0) and (max-width: 767px) {
                    .td_video_playlist_column_3 .td_playlist_clickable.td_add_scrollbar_to_playlist_for_mobile {
                        margin-right: 10px;
                    }
                }
                @media (min-width: 768px) and (max-width: 1018px) {
                    .td_video_playlist_column_3 .td_playlist_clickable.td_add_scrollbar_to_playlist_for_mobile {
                        overflow-x: hidden;
                        overflow-y: auto;
                    }
                    .td_video_playlist_column_3 .td_playlist_clickable.td_add_scrollbar_to_playlist_for_mobile::-webkit-scrollbar-track {
                        background-color: #383838;
                    }
                    .td_video_playlist_column_3 .td_playlist_clickable.td_add_scrollbar_to_playlist_for_mobile::-webkit-scrollbar {
                        width: 6px;
                        background-color: #F5F5F5;
                    }
                    .td_video_playlist_column_3 .td_playlist_clickable.td_add_scrollbar_to_playlist_for_mobile::-webkit-scrollbar-thumb {
                        background-color: #919191;
                        border-radius: 10px;
                    }
                }
                @media (max-width: 767px) {
                    .td_video_playlist_column_3 .td_playlist_clickable.td_add_scrollbar_to_playlist_for_mobile {
                        overflow-x: hidden;
                        overflow-y: auto;
                    }
                    .td_video_playlist_column_3 .td_playlist_clickable.td_add_scrollbar_to_playlist_for_mobile::-webkit-scrollbar-track {
                        background-color: #383838;
                    }
                    .td_video_playlist_column_3 .td_playlist_clickable.td_add_scrollbar_to_playlist_for_mobile::-webkit-scrollbar {
                        width: 6px;
                        background-color: #F5F5F5;
                    }
                    .td_video_playlist_column_3 .td_playlist_clickable.td_add_scrollbar_to_playlist_for_mobile::-webkit-scrollbar-thumb {
                        background-color: #919191;
                        border-radius: 10px;
                    }
                }
                @media screen and (-webkit-min-device-pixel-ratio:0) {
                    .td_video_playlist_column_3 .td_playlist_clickable.td_add_scrollbar_to_playlist {
                        margin-right: 10px;
                    }
                }
                .td_video_playlist_column_3 .td_playlist_clickable.td_add_scrollbar_to_playlist::-webkit-scrollbar-track {
                    background-color: #383838;
                }
                .td_video_playlist_column_3 .td_playlist_clickable.td_add_scrollbar_to_playlist::-webkit-scrollbar {
                    width: 6px;
                    background-color: #F5F5F5;
                }
                .td_video_playlist_column_3 .td_playlist_clickable.td_add_scrollbar_to_playlist::-webkit-scrollbar-thumb {
                    background-color: #919191;
                    border-radius: 10px;
                }
                .td_video_playlist_column_2 .td_wrapper_video_playlist {
                    flex-direction: column;
                }
                .td_video_playlist_column_2 .td_video_title_playing {
                    max-height: 20px;
                    top: 23px;
                }
                @media (max-width: 480px) {
                    .td_video_playlist_column_2 .td_video_title_playing {
                        max-height: 37px;
                        top: 13px;
                    }
                }
                .td_video_playlist_column_2 .td_wrapper_player {
                    display: block;
                    height: 391px;
                }
                @media (min-width: 1019px) and (max-width: 1140px) {
                    .td_video_playlist_column_2 .td_wrapper_player {
                        height: 360px;
                    }
                }
                @media (min-width: 768px) and (max-width: 1018px) {
                    .td_video_playlist_column_2 .td_wrapper_player {
                        height: 272px;
                    }
                }
                @media (max-width: 767px) {
                    .td_video_playlist_column_2 .td_wrapper_player {
                        display: block;
                        height: auto;
                    }
                }
                .td_video_playlist_column_2 .td_container_video_playlist {
                    height: 305px;
                }
                @media (max-width: 480px) {
                    .td_video_playlist_column_2 .td_container_video_playlist {
                        height: 245px;
                    }
                }
                @media screen and (-webkit-min-device-pixel-ratio:0) {
                    .td_video_playlist_column_2 .td_container_video_playlist .td_playlist_clickable.td_add_scrollbar_to_playlist_for_mobile,
                    .td_video_playlist_column_2 .td_container_video_playlist .td_playlist_clickable.td_add_scrollbar_to_playlist {
                        margin-right: 10px;
                    }
                }
                .td_video_playlist_column_2 .td_container_video_playlist .td_playlist_clickable.td_add_scrollbar_to_playlist_for_mobile::-webkit-scrollbar,
                .td_video_playlist_column_2 .td_container_video_playlist .td_playlist_clickable.td_add_scrollbar_to_playlist::-webkit-scrollbar-track {
                    background-color: #383838;
                }
                .td_video_playlist_column_2 .td_container_video_playlist .td_playlist_clickable.td_add_scrollbar_to_playlist_for_mobile::-webkit-scrollbar,
                .td_video_playlist_column_2 .td_container_video_playlist .td_playlist_clickable.td_add_scrollbar_to_playlist::-webkit-scrollbar {
                    width: 6px;
                    background-color: #F5F5F5;
                }
                .td_video_playlist_column_2 .td_container_video_playlist .td_playlist_clickable.td_add_scrollbar_to_playlist_for_mobile::-webkit-scrollbar-thumb,
                .td_video_playlist_column_2 .td_container_video_playlist .td_playlist_clickable.td_add_scrollbar_to_playlist::-webkit-scrollbar-thumb {
                    background-color: #919191;
                    border-radius: 10px;
                }
                .td_video_playlist_column_1 .td_wrapper_video_playlist {
                    flex-direction: column;
                }
                .td_video_playlist_column_1 .td_wrapper_player {
                    display: block;
                    height: 182px;
                }
                @media (min-width: 1019px) and (max-width: 1140px) {
                    .td_video_playlist_column_1 .td_wrapper_player {
                        height: 169px;
                    }
                }
                @media (min-width: 768px) and (max-width: 1018px) {
                    .td_video_playlist_column_1 .td_wrapper_player {
                        height: 128px;
                    }
                }
                @media (max-width: 767px) {
                    .td_video_playlist_column_1 .td_wrapper_player {
                        display: block;
                        width: 100%;
                        height: auto;
                    }
                }
                .td_video_playlist_column_1 .td_container_video_playlist {
                    height: 412px;
                }
                @media (max-width: 480px) {
                    .td_video_playlist_column_1 .td_container_video_playlist {
                        height: 245px;
                    }
                }
                @media screen and (-webkit-min-device-pixel-ratio:0) {
                    .td_video_playlist_column_1 .td_container_video_playlist .td_playlist_clickable.td_add_scrollbar_to_playlist_for_mobile,
                    .td_video_playlist_column_1 .td_container_video_playlist .td_playlist_clickable.td_add_scrollbar_to_playlist {
                        margin-right: 10px;
                    }
                }
                .td_video_playlist_column_1 .td_container_video_playlist .td_playlist_clickable.td_add_scrollbar_to_playlist_for_mobile::-webkit-scrollbar,
                .td_video_playlist_column_1 .td_container_video_playlist .td_playlist_clickable.td_add_scrollbar_to_playlist::-webkit-scrollbar-track {
                    background-color: #383838;
                }
                .td_video_playlist_column_1 .td_container_video_playlist .td_playlist_clickable.td_add_scrollbar_to_playlist_for_mobile::-webkit-scrollbar,
                .td_video_playlist_column_1 .td_container_video_playlist .td_playlist_clickable.td_add_scrollbar_to_playlist::-webkit-scrollbar {
                    width: 6px;
                    background-color: #F5F5F5;
                }
                .td_video_playlist_column_1 .td_container_video_playlist .td_playlist_clickable.td_add_scrollbar_to_playlist_for_mobile::-webkit-scrollbar-thumb,
                .td_video_playlist_column_1 .td_container_video_playlist .td_playlist_clickable.td_add_scrollbar_to_playlist::-webkit-scrollbar-thumb {
                    background-color: #919191;
                    border-radius: 10px;
                }
                .wp-video-shortcode:focus {
                    outline: 0 !important;
                }
                
                /* @style_general_sm */
                .tdc-elements .td-post-sm .tdb-item {
                  position: relative;
                }
                .tdc-elements .td-post-sm .td-spot-id-sm_ad .tdc-placeholder-title:before {
                  content: 'Smart List Ad' !important;
                }
                .tdb_single_smartlist {
                  margin-bottom: 0;
                }
                .tdb_single_smartlist .td-a-ad {
                  clear: both;
                  text-align: center;
                }
                .tdb_single_smartlist .td-a-ad > div {
                  margin-top: 0;
                }
                .tdb_single_smartlist .td-a-ad img {
                  margin: 0;
                  width: 100%;
                }
                .tdb_single_smartlist .td-a-ad .adsbygoogle {
                  margin-top: 0;
                  position: relative;
                  left: 50%;
                  -webkit-transform: translateX(-50%);
                  transform: translateX(-50%);
                }
                .tdb_single_smartlist .td-spot-id-sm_ad .td-adspot-title,
                .tdb-sml-description {
                  display: block;
                }
                .tdb_single_smartlist .td-spot-id-sm_ad .tdc-placeholder-title:before {
                  content: 'Smart List Ad';
                }
                .tdb-number-and-title {
                  width: 100%;
                }
                .tdb-number-and-title h1,
                .tdb-number-and-title h2,
                .tdb-number-and-title h3,
                .tdb-number-and-title h4,
                .tdb-number-and-title h5,
                .tdb-number-and-title h6 {
                  margin: 0;
                }
                .tdb-sml-current-item-nr span {
                  display: block;
                  background-color: #222;
                  font-weight: 700;
                  text-align: center;
                  color: #fff;
                }
                .tdb-sml-current-item-title {
                  font-weight: 700;
                }
                .tdb-slide-smart-list-figure img {
                  display: inline-block;
                  vertical-align: top;
                }
                .tdb-sml-caption {
                  font-family: Verdana, BlinkMacSystemFont, -apple-system, \"Segoe UI\", Roboto, Oxygen, Ubuntu, Cantarell, \"Open Sans\", \"Helvetica Neue\", sans-serif;
                  font-style: italic;
                  font-size: 11px;
                  line-height: 17px;
                  margin-top: 5px;
                  margin-bottom: 21px;
                }
                
                /* @style_general_review */
                .td-review {
                  width: 100%;
                  margin-bottom: 34px;
                  font-size: 13px;
                }
                .td-review td {
                  padding: 7px 14px;
                }
                .td-review i {
                  margin-top: 5px;
                }
                
                /* @style_general_author_box */
                .tdb-author-box .tdb-author-photo,
                .tdb-author-box .tdb-author-info {
                  display: table-cell;
                  vertical-align: top;
                }
                .tdb-author-box .tdb-author-photo img {
                  display: block;
                }
                .tdb-author-box .tdb-author-counters span {
                  display: inline-block;
                  background-color: #222;
                  margin: 0 10px 0 0;
                  padding: 5px 10px 4px;
                  font-family: var(--td_default_google_font_2, 'Roboto', sans-serif);
                  font-size: 11px;
                  font-weight: 700;
                  line-height: 1;
                  color: #fff;
                }
                .tdb-author-box .tdb-author-name,
                .tdb-author-box .tdb-author-url {
                  display: block;
                }
                .tdb-author-box .tdb-author-name {
                  margin: 7px 0 8px;
                  font-family: var(--td_default_google_font_1, 'Open Sans', 'Open Sans Regular', sans-serif);
                  font-size: 15px;
                  line-height: 21px;
                  font-weight: 700;
                  color: #222;
                }
                .tdb-author-box .tdb-author-name:hover {
                  color: #4db2ec;
                }
                .tdb-author-box .tdb-author-url {
                  margin-bottom: 6px;
                  font-size: 11px;
                  font-style: italic;
                  line-height: 21px;
                  color: #444;
                }
                .tdb-author-box .tdb-author-url:hover {
                  color: #4db2ec;
                }
                .tdb-author-box .tdb-author-descr {
                  font-size: 12px;
                }
                .tdb-author-box .tdb-author-social {
                  margin-top: 4px;
                }
                .tdb-author-box .tdb-social-item {
                  position: relative;
                  display: inline-block;
                  -webkit-transition: all 0.2s;
                  transition: all 0.2s;
                  text-align: center;
                  -webkit-transform: translateZ(0);
                  transform: translateZ(0);
                }
                .tdb-author-box .tdb-social-item:last-child {
                  margin-right: 0 !important;
                }
                .tdb-author-box .tdb-social-item i {
                  color: #000;
                  -webkit-transition: all 0.2s;
                  transition: all 0.2s;
                }
                .tdb-author-box .tdb-social-item:hover i {
                  color: #000;
                }
                
                /* @style_general_related_post */
                .tdb-single-related-posts {
                  display: inline-block;
                  width: 100%;
                  padding-bottom: 0;
                  overflow: visible;
                }
                .tdb-single-related-posts .tdb-block-inner:after,
                .tdb-single-related-posts .tdb-block-inner .td_module_wrap:after {
                  content: '';
                  display: table;
                  clear: both;
                }
                .tdb-single-related-posts .td-module-container {
                  display: flex;
                  flex-direction: column;
                  position: relative;
                }
                .tdb-single-related-posts .td-module-container:before {
                  content: '';
                  position: absolute;
                  bottom: 0;
                  left: 0;
                  width: 100%;
                  height: 1px;
                }
                .tdb-single-related-posts .td-image-wrap {
                  display: block;
                  position: relative;
                  padding-bottom: 70%;
                }
                .tdb-single-related-posts .td-image-container {
                  position: relative;
                  flex: 0 0 100%;
                  width: 100%;
                  height: 100%;
                }
                .tdb-single-related-posts .td-module-thumb {
                  margin-bottom: 0;
                }
                .tdb-single-related-posts .td-module-meta-info {
                  padding: 7px 0 0 0;
                  margin-bottom: 0;
                  z-index: 1;
                  border: 0 solid #eaeaea;
                }
                .tdb-single-related-posts .tdb-author-photo {
                  display: inline-block;
                }
                .tdb-single-related-posts .tdb-author-photo,
                .tdb-single-related-posts .tdb-author-photo img {
                  vertical-align: middle;
                }
                .tdb-single-related-posts .td-post-author-name,
                .tdb-single-related-posts .td-post-date,
                .tdb-single-related-posts .td-module-comments {
                  vertical-align: text-top;
                }
                .tdb-single-related-posts .entry-review-stars {
                  margin-left: 6px;
                  vertical-align: text-bottom;
                }
                .tdb-single-related-posts .td-author-photo {
                  display: inline-block;
                  vertical-align: middle;
                }
                .tdb-single-related-posts .td-thumb-css {
                  width: 100%;
                  height: 100%;
                  position: absolute;
                  background-size: cover;
                  background-position: center center;
                }
                .tdb-single-related-posts .td-category-pos-image .td-post-category,
                .tdb-single-related-posts .td-post-vid-time {
                  position: absolute;
                  z-index: 2;
                  bottom: 0;
                }
                .tdb-single-related-posts .td-category-pos-image .td-post-category {
                  left: 0;
                }
                .tdb-single-related-posts .td-post-vid-time {
                  right: 0;
                  background-color: #000;
                  padding: 3px 6px 4px;
                  font-family: var(--td_default_google_font_1, 'Open Sans', 'Open Sans Regular', sans-serif);
                  font-size: 10px;
                  font-weight: 600;
                  line-height: 1;
                  color: #fff;
                }
                .tdb-single-related-posts .td-module-title {
                  font-family: var(--td_default_google_font_2, 'Roboto', sans-serif);
                  font-weight: 500;
                  font-size: 13px;
                  line-height: 20px;
                  margin: 0;
                }
                @media (max-width: 767px) {
                  .tdb-single-related-posts .td-module-title {
                    font-size: 17px;
                    line-height: 23px;
                  }
                }
                .tdb-single-related-posts .td-excerpt {
                  margin: 20px 0 0;
                  line-height: 21px;
                }
                .tdb-single-related-posts .td-read-more,
                .tdb-single-related-posts .td-next-prev-wrap {
                  margin: 20px 0 0;
                }
                .tdb-single-related-posts div.tdb-block-inner:after {
                  content: '' !important;
                  padding: 0;
                  border: none;
                }
                .tdb-single-related-posts .td-next-prev-wrap a {
                  width: auto;
                  height: auto;
                  min-width: 25px;
                  min-height: 25px;
                }
                .single-tdb_templates .tdb-single-related-posts .td-next-prev-wrap a:active {
                  pointer-events: none;
                }
                
                /* @style_general_post_meta */
                .tdb-post-meta {
                  margin-bottom: 16px;
                  color: #444;
                  font-family: var(--td_default_google_font_1, 'Open Sans', 'Open Sans Regular', sans-serif);
                  font-size: 11px;
                  font-weight: 400;
                  clear: none;
                  vertical-align: middle;
                  line-height: 1;
                }
                .tdb-post-meta span,
                .tdb-post-meta i,
                .tdb-post-meta time {
                  vertical-align: middle;
                }
                
                /* @style_general_module_loop */
                [class*=\"tdb_module_loop\"] .td-module-container {
                  display: flex;
                  flex-direction: column;
                  position: relative;
                }
                [class*=\"tdb_module_loop\"] .td-module-container:before {
                  content: '';
                  position: absolute;
                  bottom: 0;
                  left: 0;
                  width: 100%;
                  height: 1px;
                }
                [class*=\"tdb_module_loop\"] .td-image-wrap {
                  display: block;
                  position: relative;
                  padding-bottom: 50%;
                }
                [class*=\"tdb_module_loop\"] .td-image-container {
                  position: relative;
                  flex: 0 0 auto;
                  width: 100%;
                  height: 100%;
                }
                [class*=\"tdb_module_loop\"] .td-module-thumb {
                  margin-bottom: 0;
                }
                [class*=\"tdb_module_loop\"] .td-module-meta-info {
                  width: 100%;
                  padding: 13px 0 0 0;
                  margin-bottom: 0;
                  z-index: 1;
                  border: 0 solid #eaeaea;
                }
                [class*=\"tdb_module_loop\"] .td-thumb-css {
                  width: 100%;
                  height: 100%;
                  position: absolute;
                  background-size: cover;
                  background-position: center center;
                }
                [class*=\"tdb_module_loop\"] .td-category-pos-image .td-post-category:not(.td-post-extra-category),
                [class*=\"tdb_module_loop\"] .td-post-vid-time {
                  position: absolute;
                  z-index: 2;
                  bottom: 0;
                }
                [class*=\"tdb_module_loop\"] .td-category-pos-image .td-post-category:not(.td-post-extra-category) {
                  left: 0;
                }
                [class*=\"tdb_module_loop\"] .td-post-vid-time {
                  right: 0;
                  background-color: #000;
                  padding: 3px 6px 4px;
                  font-family: var(--td_default_google_font_1, 'Open Sans', 'Open Sans Regular', sans-serif);
                  font-size: 10px;
                  font-weight: 600;
                  line-height: 1;
                  color: #fff;
                }
                [class*=\"tdb_module_loop\"] .td-excerpt {
                  margin: 20px 0 0;
                  line-height: 21px;
                }
                
                /* @style_general_module_header */
                .tdb_module_header {
                  width: 100%;
                  padding-bottom: 0;
                }
                .tdb_module_header .td-module-container {
                  display: flex;
                  flex-direction: column;
                  position: relative;
                }
                .tdb_module_header .td-module-container:before {
                  content: '';
                  position: absolute;
                  bottom: 0;
                  left: 0;
                  width: 100%;
                  height: 1px;
                }
                .tdb_module_header .td-image-wrap {
                  display: block;
                  position: relative;
                  padding-bottom: 70%;
                }
                .tdb_module_header .td-image-container {
                  position: relative;
                  width: 100%;
                  flex: 0 0 auto;
                }
                .tdb_module_header .td-module-thumb {
                  margin-bottom: 0;
                }
                .tdb_module_header .td-module-meta-info {
                  width: 100%;
                  margin-bottom: 0;
                  padding: 7px 0 0 0;
                  z-index: 1;
                  border: 0 solid #eaeaea;
                  min-height: 0;
                }
                .tdb_module_header .entry-title {
                  margin: 0;
                  font-size: 13px;
                  font-weight: 500;
                  line-height: 18px;
                }
                .tdb_module_header .td-post-author-name,
                .tdb_module_header .td-post-date,
                .tdb_module_header .td-module-comments {
                  vertical-align: text-top;
                }
                .tdb_module_header .td-post-author-name,
                .tdb_module_header .td-post-date {
                  top: 3px;
                }
                .tdb_module_header .td-thumb-css {
                  width: 100%;
                  height: 100%;
                  position: absolute;
                  background-size: cover;
                  background-position: center center;
                }
                .tdb_module_header .td-category-pos-image .td-post-category:not(.td-post-extra-category),
                .tdb_module_header .td-post-vid-time {
                  position: absolute;
                  z-index: 2;
                  bottom: 0;
                }
                .tdb_module_header .td-category-pos-image .td-post-category:not(.td-post-extra-category) {
                  left: 0;
                }
                .tdb_module_header .td-post-vid-time {
                  right: 0;
                  background-color: #000;
                  padding: 3px 6px 4px;
                  font-family: var(--td_default_google_font_1, 'Open Sans', 'Open Sans Regular', sans-serif);
                  font-size: 10px;
                  font-weight: 600;
                  line-height: 1;
                  color: #fff;
                }
                .tdb_module_header .td-excerpt {
                  margin: 20px 0 0;
                  line-height: 21px;
                }
                .tdb_module_header .td-read-more {
                  margin: 20px 0 0;
                }
                
                /* @style_general_header_align */
                .tdb-header-align {
                  vertical-align: middle;
                }
                
                
            </style>";

        return $raw_css;
    }


	protected function get_custom_css() {
		return '';
	}

	protected function get_inline_css($block_uid = '') {
        if (method_exists( $this,'get_raw_css')) {
            return $this->get_raw_css( true );
        }
        return '';
    }

    protected function get_inline_js($block_uid = '') {
        return '';
    }


	/**
	 * Returns the block css.
	 *
	 * !!WARNING - blocks that don't use this will not work with the TD composer design tab when Visual Composer is disabled
	 *              BUT the block will work just fine when VC is enabled
	 * @since 30 may 2016 - before it was echoed on render - no bueno
	 */
	protected function get_block_css() {
	    $buffy_style = '';
        $buffy = '';

	    if( isset( $this->atts['custom_title'] ) ) {
	        if( $this->atts['custom_title'] != '' ) {
                $buffy .= $this->block_template()->get_css();
            }
        } elseif( 'tdb_single_comments' === $this->atts['block_type'] ) {
			$buffy .= $this->block_template()->get_css();
		}

		$css = $this->get_att('css');

		// VC adds the CSS att automatically so we don't have to do it
		if ( !td_util::is_vc_installed() && !empty($css) ) {
			$buffy .= PHP_EOL . '/* inline css att - generated by TagDiv Composer */' . PHP_EOL . $css;
		}

		$custom_css = $this->get_custom_css();
		if ( !empty($custom_css) ) {
			$buffy_style .= PHP_EOL . '<style>' . PHP_EOL . '/* custom css - generated by TagDiv Composer */' . PHP_EOL . $custom_css . PHP_EOL . '</style>';
		}

		if ( td_util::tdc_is_live_editor_iframe() || !empty( tdc_util::get_get_val('tda_action')) ) {

			$inline_css = $this->get_inline_css();
			if ( !empty( $inline_css ) ) {
				$inline_css  = td_util::remove_style_tag( $inline_css );
				$buffy_style .= PHP_EOL . '<style class="tdc-pattern">' . PHP_EOL . '/* inline css */' . PHP_EOL . $inline_css . PHP_EOL . '</style>';
			}

			$inline_js = $this->get_inline_js();
			if ( !empty( $inline_js ) ) {
				$inline_js   = td_util::remove_script_tag( $inline_js );
				$buffy_style .= PHP_EOL . '<script type="text/javascript" class="tdc-pattern-js">' . PHP_EOL . '/* inline js */' . PHP_EOL . $inline_js . PHP_EOL . '</script>';
			}

		}

		$tdcCss = $this->get_att('tdc_css');
		$clearfixColumns = false;
		$cssOutput = '';
		$beforeCssOutput = '';
		$afterCssOutput = '';
        $tdcHiddenLabelCssOutput = '';

        if ( !empty( $tdcCss ) ) {
            $buffy .= $this->generate_css( $tdcCss, $clearfixColumns, $cssOutput, $beforeCssOutput, $afterCssOutput, $tdcHiddenLabelCssOutput );
        }

        if ( !empty( $buffy ) ) {
            $buffy       = PHP_EOL . '<style>' . PHP_EOL . $buffy . PHP_EOL . '</style>';
            $buffy_style = $buffy . $buffy_style;
        }

		$tdcElementStyleCss = '';
        if ( !empty($cssOutput) || !empty($beforeCssOutput) || !empty($afterCssOutput) || !empty($tdcHiddenLabelCssOutput) ) {
			if ( !empty($beforeCssOutput) ) {
				$beforeCssOutput = PHP_EOL . '<div class="td-element-style-before"><style>' . $beforeCssOutput . '</style></div>';
			}
			$tdcElementStyleCss = PHP_EOL . '<div class="' . $this->get_att( 'tdc_css_class_style' ) . ' td-element-style">' . $beforeCssOutput . '<style>' . $cssOutput . ' ' . $afterCssOutput . '</style></div>';

            if( !empty( $tdcHiddenLabelCssOutput ) ) {
                $tdcElementStyleCss .= PHP_EOL . '<div class="' . $this->get_att( 'tdc_css_class_style' ) . '_tdc_hidden_label tdc-hidden-elem-label"><style>' . $tdcHiddenLabelCssOutput . '</style></div>';
            }
		}

		$has_style = false;
		if ( !empty($buffy_style) || !empty($tdcElementStyleCss) ) {
		    $has_style = true;
		}

		$final_style = '';

		if ( $has_style ) {
            if (!empty($buffy_style)) {
                if (!empty($tdcElementStyleCss)) {
                    $buffy_style .= $tdcElementStyleCss;
                }
                $final_style = $buffy_style;
            } else if (!empty($tdcElementStyleCss)) {
                $final_style = $tdcElementStyleCss;
            }
		}

		return $final_style;

	}



	private function getBorderWidth($borderWidthCssProps) {

		$borderWidthCss = '';

		$borderSet = false;
		$borderSettings = array(
			'border-top-width' => '0px',
			'border-right-width' => '0px',
			'border-bottom-width' => '0px',
			'border-left-width' => '0px',
		);

		foreach ($borderWidthCssProps as $key => $val) {
			switch ($key) {
				case 'border-top-width':
					if (!empty($val)) {
						$borderSet = true;
						$borderSettings[$key] = $val;
					}
					break;

				case 'border-right-width':
					if (!empty($val)) {
						$borderSet = true;
						$borderSettings[$key] = $val;
					}
					break;

				case 'border-bottom-width':
					if (!empty($val)) {
						$borderSet = true;
						$borderSettings[$key] = $val;
					}
					break;

				case 'border-left-width':
					if (!empty($val)) {
						$borderSet = true;
						$borderSettings[$key] = $val;
					}
					break;
			}
		}

		if ($borderSet) {

			$borderWidthCss = 'border-width:';
			foreach ($borderSettings as $key => $val) {
				$borderWidthCss .= ' ' . $val;
			}
			$borderWidthCss .= ' !important;' . PHP_EOL;
		}

		return $borderWidthCss;
	}


	/**
	 * Generate css for blocks, inner columns, inner rows, columns and rows
	 * For inner rows and rows a new '.tdc-css' child element is added and its css is generated (This solution was adopted because we need an ::after element, and rows and inner rows already have an ::after element)
	 *
	 * @param $tdcCss - the property that will be decoded and parsed
	 * @param bool $clearfixColumns - flag used to know outside if the '.clearfix' element is added as last child in vc_row and vc_row_inner
	 * @param string $cssOutput - css output for td-element-style
	 * @param string $beforeCssOutput - css output for td-element-style::before
	 * @param string $afterCssOutput - css output for td-element-style::after
	 * @param string  $tdcHiddenLabelCssOutput - css output for tdc hidden label
	 *
	 * @return string
	 */
	protected function generate_css( $tdcCss, &$clearfixColumns = false, &$cssOutput = '', &$beforeCssOutput = '', &$afterCssOutput = '', &$tdcHiddenLabelCssOutput = '' ) {

        $in_composer = tdc_state::is_live_editor_iframe() || tdc_state::is_live_editor_ajax();

		//
		// Very Important! For stretched rows move the 'border' css settings on ::before, for all viewport settings
		//
		$moveBorderSettingsOnBefore = false;

		if (class_exists('vc_row') && $this instanceof vc_row ) {

			// Important! get_custom_att was introduced in composer only after 01.08.2017 (because 'full_width' att was moved from tdc_composer_block to vc_row)
			// This check is done to allow older versions of composer to use 'full_width'.

			if ( method_exists( $this, 'get_custom_att' ) ) {
				$attFullWidth = $this->get_custom_att( 'full_width' );
			} else {
				$attFullWidth = $this->get_att( 'full_width' );
			}
			if ($attFullWidth !== '') {
				$moveBorderSettingsOnBefore = true;
			}
		}


        //
        // Get information about the current shortcode
        //
        $shortcode_details = array();

        $shortcode_key = get_class( $this );
        switch( $shortcode_key ) {
            case 'tdc_zone':
            case 'vc_row':
            case 'vc_row_inner':
            case 'vc_column':
            case 'vc_column_inner':
            case 'vc_raw_html':
            case 'vc_empty_space':
            case 'vc_widget_sidebar':
            case 'vc_separator':
            case 'tdc_woo_shortcodes':
                $shortcode_details = tdc_mapper::get_attributes( $shortcode_key );
                break;

            default:
                $shortcode_details = td_api_base::get_by_id( $shortcode_key );
                break;
        }


		$buffy = '';

		$tdcCssDecoded = false;

		if ( td_util::tdc_is_installed() ) {
		    $tdcCssDecoded = tdc_b64_decode($tdcCss);
        }

		if ( $tdcCssDecoded !== false ) {
			$tdcCssArray = json_decode( $tdcCssDecoded, true );

			if (!is_null($tdcCssArray) && is_array($tdcCssArray)) {
				$tdcCssProcessed = '';



				// Values of these properties must be numeric
				$numericCssProps = array(
					'border-radius',

					'width',
					'height',

					'margin-top',
					'margin-right',
					'margin-bottom',
					'margin-left',

					'border-top-width',
					'border-right-width',
					'border-bottom-width',
					'border-left-width',

					'padding-top',
					'padding-right',
					'padding-bottom',
					'padding-left',

					'shadow-size',
					'shadow-offset-h',
					'shadow-offset-v',
				);

				$borderWidthCssProps = array(
					'border-top-width' => '',
					'border-right-width' => '',
					'border-bottom-width' => '',
					'border-left-width' => '',
				);

				$beforeCssProps = array(
					'background-image',
					'background-size',
					'background-position',
					'background-repeat',
					'opacity',
				);

				$elementStyleProps = array(
					'background-color',
				);

				if ($moveBorderSettingsOnBefore) {
					$beforeCssProps[] = 'border-style';
                    $beforeCssProps[] = 'border-color';
                    $beforeCssProps[] = 'border-width';
					$beforeCssProps[] = 'border-top-width';
					$beforeCssProps[] = 'border-right-width';
					$beforeCssProps[] = 'border-bottom-width';
					$beforeCssProps[] = 'border-left-width';
				}

				$afterCssProps = array(
					'color-1-overlay',
					'color-2-overlay',
					'gradient-direction',
				);

				$cssBeforeSettings =
					"content:'' !important;" . PHP_EOL .
			        "width:100% !important;" . PHP_EOL .
			        "height:100% !important;" . PHP_EOL .
			        "position:absolute !important;" . PHP_EOL .
			        "top:0 !important;" . PHP_EOL .
			        "left:0 !important;" . PHP_EOL .
			        "display:block !important;" . PHP_EOL .
			        "z-index:0 !important;" . PHP_EOL;

				$cssAfterSettings =
					"content:'' !important;" . PHP_EOL .
				    "width:100% !important;" . PHP_EOL .
				    "height:100% !important;" . PHP_EOL .
				    "position:absolute !important;" . PHP_EOL .
				    "top:0 !important;" . PHP_EOL .
				    "left:0 !important;" . PHP_EOL .
				    "z-index:0 !important;" . PHP_EOL .
				    "display:block !important;" . PHP_EOL;


				$mediaCssAll = '';
				$cssBeforeAll = '';
				$cssElementStyleAll = '';
				$cssAfterAll = array();
				$tdcShowHiddenLabelAll = false;

                $mediaCssAllInnerRow = '';

				$mediaCssDesktop = '';

				$borderInAll = false;
				$backgroundInAll = false;

				// 'all' css settings
				if (array_key_exists('all', $tdcCssArray)) {

					foreach ($tdcCssArray['all'] as $k1 => $v1) {

                        if( $k1 == 'width' && $v1 != 'auto' && class_exists('vc_row_inner') && $this instanceof vc_row_inner ) {
                            $gap = $this->get_att('gap');
                            $gap_value = '';
                            $gap_value_default = '24px';

                            if( td_util::is_base64( $gap ) ) {
                                $gap_decoded = json_decode( base64_decode( $gap ) );

                                if( property_exists($gap_decoded, 'all') ) {
                                    $gap_value = $gap_decoded->all;
                                } else {
                                    $gap_value = $gap_value_default;
                                }
                            } else {
                                $gap_value = $gap != '' ? $gap : $gap_value_default;
                            }

                            $v1 .= is_numeric( $v1 ) ? 'px' : '';
                            $gap_value .= is_numeric( $gap_value ) ? 'px' : '';

                            if( $in_composer ) {
                                $mediaCssAllInnerRow .= $k1 . ':' . $v1 . ' !important;' . PHP_EOL;

                                $v1 = '100%';
                            }

                            if( $gap_value != '' ) {
                                $v1 = 'calc(' . $v1 . ' + ( ' . $gap_value . ' * 2 ) )';
                            }
                        }

						if (in_array($k1, $numericCssProps) && is_numeric($v1)) {
							$v1 .= 'px';
						}

						// Check for 'border'
						// Default values are added!
						if (!$borderInAll && strpos($k1, 'border') !== false) {
							$borderInAll = true;
						}

						// Check for 'background'
						// Default values are added!
						if (!$backgroundInAll && strpos($k1, 'background') !== false) {
							$backgroundInAll = true;
						}

						if ('background-style' === $k1) {
							$setting = 'background-size';
							if ($v1 === 'repeat' || $v1 === 'no-repeat') {
								$setting = 'background-repeat';
							} else if ($v1 === 'contain') {
								$cssBeforeAll .= 'background-repeat: no-repeat !important;' . PHP_EOL;
							}
							$cssBeforeAll .= $setting . ':' . $v1 . ' !important;' . PHP_EOL;
							continue;
						}

						if (array_key_exists($k1, $borderWidthCssProps)) {
							$borderWidthCssProps[$k1] = $v1;
							continue;
						}

//						if (array_key_exists($k1, $beforeCssProps)) {
//							$beforeCssProps[$k1] = $v1;
//							continue;
//						}

						if (in_array($k1, $elementStyleProps)) {
							$cssElementStyleAll .= $k1 . ':' . $v1 . ' !important;' . PHP_EOL;
							continue;
						}

                        if( $k1 == 'border-radius' ) {
                            $cssElementStyleAll .= $k1 . ':' . $v1 . ' !important;' . PHP_EOL;
                        }

						if (in_array($k1, $beforeCssProps)) {
							$cssBeforeAll .= $k1 . ':' . $v1 . ' !important;' . PHP_EOL;
							continue;
						}

						if (in_array($k1, $afterCssProps)) {
							$cssAfterAll[$k1] = $v1;
							continue;
						}

						if ( 'content-h-align' === $k1 ) {
							$k1 = 'text-align';
							$v1 = str_replace( 'content-horiz-', '', $v1 );

							// These settings were introduced because of vertical align
							switch ( $v1 ){
								case 'center' : $mediaCssAll .= 'justify-content:center !important;' . PHP_EOL; break;
								case 'right' : $mediaCssAll .= 'justify-content:flex-end !important;' . PHP_EOL; break;
							}
						}



						// Shadow settings
						if ( 'shadow-size' === $k1 && ! empty( $v1 ) ) {
							$shadow_offset_h = 0;
							if ( ! empty( $tdcCssArray['all']['shadow-offset-h'] ) ) {
								$shadow_offset_h = $tdcCssArray['all']['shadow-offset-h'] . 'px';
							}
							$shadow_offset_v = 0;
							if ( ! empty( $tdcCssArray['all']['shadow-offset-v'] ) ) {
								$shadow_offset_v = $tdcCssArray['all']['shadow-offset-v'] . 'px';
							}
							$shadow_color = '#888888';
							if ( ! empty( $tdcCssArray['all']['shadow-color'] ) ) {
								$shadow_color = $tdcCssArray['all']['shadow-color'];
							}
							$mediaCssAll .= 'box-shadow:' . $shadow_offset_h . ' ' . $shadow_offset_v . ' ' . $v1 . ' ' . $shadow_color . ' !important;' . PHP_EOL;
							continue;
						}

						if ( in_array( $k1, array( 'shadow-color', 'shadow-offset-h', 'shadow-offset-v' ) ) ) {
							continue;
						}


						// Display settings
						if ( 'display' === $k1 ) {
                            if( 'none' === $v1 && $in_composer && td_util::get_option('tdcShowHiddenElements') == 'true' ) {
                                $mediaCssDesktop .=
                                    'opacity:.3;'  . PHP_EOL .
                                    'filter: grayscale(1);' . PHP_EOL;
                                $tdcShowHiddenLabelAll = true;
                                continue;
                            }

							if ( 'show' !== $v1 && '' !== $v1 ) {
								$mediaCssDesktop .= $k1 . ':' . $v1 . ' !important;' . PHP_EOL;
							}
							continue;
						}


						$mediaCssAll .= $k1 . ':' . $v1 . ' !important;' . PHP_EOL;
					}



					// Add default value for 'border-style'
					// Add default value for 'border-color'
					if ($borderInAll) {
						if (!isset($tdcCssArray['all']['border-style'])) {
							if ($moveBorderSettingsOnBefore) {
								$cssBeforeAll .= 'border-style:solid !important;' . PHP_EOL;
							} else {
								$mediaCssAll .= 'border-style:solid !important;' . PHP_EOL;
							}
						}
						if (!isset($tdcCssArray['all']['border-color'])) {
							if ($moveBorderSettingsOnBefore) {
								$cssBeforeAll .= 'border-color:#888888 !important;' . PHP_EOL;
							} else {
								$mediaCssAll .= 'border-color:#888888 !important;' . PHP_EOL;
							}
						}
						if (!isset($tdcCssArray['all']['border-top-width']) &&
							!isset($tdcCssArray['all']['border-right-width']) &&
							!isset($tdcCssArray['all']['border-bottom-width']) &&
							!isset($tdcCssArray['all']['border-left-width'])) {
							if ($moveBorderSettingsOnBefore) {
								$cssBeforeAll .= 'border-width:0 !important;' . PHP_EOL;
							} else {
								$mediaCssAll .= 'border-width:0 !important;' . PHP_EOL;
							}
						}
					}




					// Set border width css for 'all'
					$borderCss = $this->getBorderWidth($borderWidthCssProps);

					if ($borderCss !== '') {
						if ($moveBorderSettingsOnBefore) {
							$cssBeforeAll .= $borderCss;
						} else {
							$mediaCssAll .= $borderCss;
						}
					}




					$positionElement = false;

					// all td-element-style
					if ($cssElementStyleAll !== '') {
						$cssOutput .= PHP_EOL . '.' . $this->get_att( 'tdc_css_class_style' ) . '{' . PHP_EOL . $cssElementStyleAll . '}' . PHP_EOL;

						$positionElement = true;
					}


					// all td-element-style::before
					if ($cssBeforeAll !== '') {

						// Add default value for 'background-size'
						if ($backgroundInAll) {
							if (!isset($tdcCssArray['all']['background-style'])) {
								$cssBeforeAll .= 'background-size:cover !important;' . PHP_EOL;
							}
							if (!isset($tdcCssArray['all']['background-position'])) {
								$cssBeforeAll .= 'background-position:center top !important;' . PHP_EOL;
							}
						}

						//$tdcCssProcessed .= PHP_EOL . '.' . $this->get_att( 'tdc_css_class' ) . '::before{' . PHP_EOL . $cssBeforeSettings . $cssBeforeAll . '}' . PHP_EOL;
						$beforeCssOutput .= PHP_EOL . '.' . $this->get_att( 'tdc_css_class_style' ) . ' > .td-element-style-before {' . PHP_EOL . $cssBeforeSettings . $cssBeforeAll . '}' . PHP_EOL;

						$positionElement = true;
					}

					// all td-element-style::after
					if (!empty($cssAfterAll)) {

						$css = '';
						$deg = '';

						if (array_key_exists('gradient-direction', $cssAfterAll )) {
							$deg = $cssAfterAll['gradient-direction'] . 'deg,';
						}

						if (array_key_exists('color-1-overlay', $cssAfterAll) && array_key_exists('color-2-overlay', $cssAfterAll)) {
							$css .= 'background: linear-gradient(' . $deg . $cssAfterAll['color-1-overlay'] . ', '  . $cssAfterAll['color-2-overlay'] . ') !important;' . PHP_EOL;
						} else if (array_key_exists('color-1-overlay', $cssAfterAll)) {
							$css .= 'background: ' . $cssAfterAll['color-1-overlay'] .' !important;' . PHP_EOL;
						} else if (array_key_exists('color-2-overlay', $cssAfterAll)) {
							$css .= 'background: ' . $cssAfterAll['color-2-overlay'] .' !important;' . PHP_EOL;
						}

						if (array_key_exists('opacity', $cssAfterAll)) {
							$css .= 'opacity: ' . $cssAfterAll['opacity'] .' !important;' . PHP_EOL;
						}


						if ( '' !== $css ) {

							// Important!
							if ( $this instanceof vc_row || $this instanceof vc_row_inner ) {
								$clearfixColumns = true;
							}

							//$tdcCssProcessed .= PHP_EOL . '.' . $this->get_att( 'tdc_css_class' ) . $childElement . '::after{' . PHP_EOL . $cssAfterSettings . $css . '}' . PHP_EOL;
							$afterCssOutput .= PHP_EOL . '.' . $this->get_att( 'tdc_css_class_style' ) . '::after {' . PHP_EOL . $cssAfterSettings . $css . '}' . PHP_EOL;

							$positionElement = true;
						}
					}

                    if( $tdcShowHiddenLabelAll ) {

                        $cssTDCHiddenLabelAfterAll = '';
                        if( !empty( $shortcode_details ) ) {
                            $cssTDCHiddenLabelAfterAll = PHP_EOL . '.' . $this->get_att( 'tdc_css_class_style' ) . '_tdc_hidden_label:after {' . PHP_EOL . 'content: "Hidden: ' . $shortcode_details['name'] . '"' . PHP_EOL . '}';
                        }

                        $limit_bottom = td_global::$td_viewport_intervals[ count( td_global::$td_viewport_intervals ) - 1 ]['limitBottom'];

                        $tdcHiddenLabelCssOutput .= PHP_EOL . '/* desktop */' . PHP_EOL . '@media(min-width: ' . ( $limit_bottom + 1 ) . 'px) {' . PHP_EOL;
                            $tdcHiddenLabelCssOutput .= '.' . $this->get_att( 'tdc_css_class_style' ) . '_tdc_hidden_label {' . PHP_EOL . 'display:block;' . PHP_EOL . '}';
                            $tdcHiddenLabelCssOutput .= !empty( $cssTDCHiddenLabelAfterAll ) ? $cssTDCHiddenLabelAfterAll : '';
                        $tdcHiddenLabelCssOutput .= PHP_EOL .'}' . PHP_EOL;

                    }

					if ($positionElement) {
						$mediaCssAll .= 'position:relative;' . PHP_EOL;
					}

					// all css
					if ($mediaCssAll !== '') {
						$tdcCssProcessed .= PHP_EOL . '.' . $this->get_att('tdc_css_class') . '{' . PHP_EOL . $mediaCssAll . '}' . PHP_EOL;

						if ((class_exists('vc_row') && $this instanceof vc_row) || (class_exists('vc_row_inner') && $this instanceof vc_row_inner)) {
							$tdcCssProcessed .= PHP_EOL . '.' . $this->get_att('tdc_css_class') . ' .td_block_wrap{ text-align:left }' . PHP_EOL;
						}
					}

                    // all css inner row
                    if ($mediaCssAllInnerRow !== '') {
                        if( td_global::get_in_element() ) {
                            $tdcCssProcessed .= PHP_EOL . '.tdc-inner-row-composer#' . $this->block_uid . '{' . PHP_EOL . $mediaCssAllInnerRow . '}' . PHP_EOL;
                        } else {
                            $tdcCssProcessed .= PHP_EOL . '.tdc-element-inner-row[data-tdc-inner-row-uid="' . $this->block_uid . '"]{' . PHP_EOL . $mediaCssAllInnerRow . '}' . PHP_EOL;
                        }
                    }

					// desktop css
					if ($mediaCssDesktop !== '') {

						$limit_bottom = td_global::$td_viewport_intervals[ count( td_global::$td_viewport_intervals ) - 1 ]['limitBottom'];

                        $meta_is_mobile_template = get_post_meta(get_the_ID(), 'tdc_is_mobile_template', true);

                        // we are on mobile template and desktop is landscape tablet
                        // so, we get limit_bottom the phone viewport
                        if ( !empty($meta_is_mobile_template) ){
                            $limit_bottom = td_global::$td_viewport_intervals[ count( td_global::$td_viewport_intervals ) - 3 ]['limitBottom'] ;
                        }

                        $tdcCssProcessed .= PHP_EOL . '/* desktop */ @media(min-width: ' . ( $limit_bottom + 1 ) . 'px) { ' . '.' . $this->get_att('tdc_css_class') . ' { ' . PHP_EOL . $mediaCssDesktop . '} }' . PHP_EOL;

                    }

					// Temporarily commented
					//unset($tdcCssArray['all']);
				}






				// !!!! The css media queries must be output in reverse order. Maybe this generated css should be managed all at once, in page.
				// Multiple solutions have been tested to sort them in reverse order at creation. Issues: json.stringify work well only with object, and an object does not keep the order of its properties.
				// For this, javascript array should be used, but in that case, an array with undefined elements it would be created by json_decode php function. So, not good.

                if( class_exists('vc_row_inner') && $this instanceof vc_row_inner ) {
                    if( isset( $tdcCssArray['all'] ) && isset( $tdcCssArray['all']['width'] ) ) {
                        $desktop_width = $tdcCssArray['all']['width'];

                        if( !isset( $tdcCssArray['landscape']['width'] ) ) {
                            $tdcCssArray['landscape']['width'] = $desktop_width;
                            $tdcCssArray['landscape_max_width'] = 1140;
                            $tdcCssArray['landscape_min_width'] = 1019;
                        }
                        if( !isset( $tdcCssArray['portrait']['width'] ) ) {
                            $tdcCssArray['portrait']['width'] = $desktop_width;
                            $tdcCssArray['portrait_max_width'] = 1018;
                            $tdcCssArray['portrait_min_width'] = 768;
                        }
                        if( !isset( $tdcCssArray['phone']['width'] ) ) {
                            $tdcCssArray['phone']['width'] = $desktop_width;
                            $tdcCssArray['phone_max_width'] = 767;
                        }
                    }
                }

				$limits = array();
				foreach ($tdcCssArray as $key => $val) {

					if (stripos($key, '_max_width') !== false) {

						$new_key = str_replace('_max_width', '', $key);

						if ( !isset($limits[$new_key])) {
							$limits[$new_key] = array();
						}
						$limits[$new_key]['max_width'] = $val;
					}

					if (stripos($key, '_min_width') !== false) {

						$new_key = str_replace( '_min_width', '', $key);

						if (!isset($limits[$new_key])) {
							$limits[$new_key] = array();
						}
						$limits[$new_key]['min_width'] = $val;
					}
				}



				foreach ($limits as $key => $val) {

					$mediaArray = $tdcCssArray[ $key ];

					$mediaCss = '';
                    $mediaCssInnerRow = '';
					$cssBefore = '';
					$cssElementStyle = '';
					$cssAfter = array();
                    $tdcShowHiddenLabel = false;

					$borderInLimit = false;
					$backgroundInLimit = false;


					// Reset $beforeCss
					foreach ($borderWidthCssProps as $borderCssKey => $borderCssValue) {
						$borderWidthCssProps[$borderCssKey] = '';
					}

//					// Reset $beforeCssProps
//					foreach ($beforeCssProps as $beforeCssKey => $beforeCssValue) {
//						$beforeCssProps[$beforeCssKey] = '';
//					}

                    if( $key == 'phone' && !isset( $mediaArray['width'] ) && class_exists('vc_row_inner') && $this instanceof vc_row_inner ) {
                        $mediaArray['width'] = '100%';
                    }


					foreach ($mediaArray as $k2 => $v2) {

                        if( $k2 == 'width' && $v2 != 'auto' && class_exists('vc_row_inner') && $this instanceof vc_row_inner ) {
                            $gap_value = '';
                            
                            if( $key != 'phone' ) {
                                $gap = $this->get_att('gap');
                                $gap_value_default = '';
    
                                if( $key == 'landscape' ) {
                                    $gap_value_default = '20px';
                                } else if( $key == 'portrait' ) {
                                    $gap_value_default = '14px';
                                }
    
                                if( td_util::is_base64( $gap ) ) {
                                    $gap_decoded = json_decode( base64_decode( $gap ) );
    
                                    if( property_exists($gap_decoded, $key) ) {
                                        $gap_value = $gap_decoded->{$key};
                                    } else {
                                        if( property_exists($gap_decoded, 'all') ) {
                                            $gap_value = $gap_decoded->all;
                                        } else {
                                            $gap_value = $gap_value_default;
                                        }
                                    }
                                } else {
                                    $gap_value = $gap != '' ? $gap : $gap_value_default;
                                }

                                $v2 .= is_numeric( $v2 ) ? 'px' : '';
                                $gap_value .= is_numeric( $gap_value ) ? 'px' : '';
                            }

                            if( $in_composer ) {
                                $mediaCssInnerRow .= $k2 . ':' . $v2 . ' !important;' . PHP_EOL;

                                $v2 = '100%';
                            }

                            if( $gap_value != '' ) {
                                $v2 = 'calc(' . $v2 . ' + ( ' . $gap_value . ' * 2 ) )';
                            }
                        }

						if (in_array($k2, $numericCssProps) && is_numeric($v2)) {
							$v2 .= 'px';
						}

						// Check for 'border'
						// Default values are added!
						if (!$borderInLimit && strpos($k2, 'border') !== false) {
							$borderInLimit = true;
						}

						// Check for 'background'
						// Default values are added!
						if (!$backgroundInLimit && strpos($k2, 'background') !== false) {
							$backgroundInLimit = true;
						}

						if ('background-style' === $k2) {
							$setting = 'background-size';
							if ($v2 === 'repeat' || $v2 === 'no-repeat') {
								$setting = 'background-repeat';
							} else if ($v2 === 'contain') {
								$cssBeforeAll .= 'background-repeat: no-repeat !important;' . PHP_EOL;
							}
							$cssBeforeAll .= $setting . ':' . $v2 . ' !important;' . PHP_EOL;
							continue;
						}

						if (array_key_exists($k2, $borderWidthCssProps)) {
							$borderWidthCssProps[$k2] = $v2;
							continue;
						}


						// Change to 'transparent' for 'border-color: no_value'
						// Change to 'transparent' for 'background-color: no_value'
						// Change to 'transparent' for 'color-1-overlay: no_value'
						// Change to 'transparent' for 'color-2-overlay: no_value'
						// Change to 'none' for 'background-image: no_value'
						if ($v2 === 'no_value') {
							switch ($k2) {
								case 'border-color':
								case 'background-color':
								case 'color-1-overlay':
								case 'color-2-overlay': $v2 = 'transparent'; break;
								case 'background-image': $v2 = 'none'; break;
							}
						}


//							if (array_key_exists($k2, $beforeCssProps)) {
//								$beforeCssProps[$k2] = $v2;
//								continue;
//							}

						if (in_array($k2, $elementStyleProps)) {
							$cssElementStyle .= $k2 . ':' . $v2 . ' !important;' . PHP_EOL;
							continue;
						}

                        if( $k2 == 'border-radius' ) {
                            $cssElementStyle .= $k2 . ':' . $v2 . ' !important;' . PHP_EOL;
                        }

						if (in_array($k2, $beforeCssProps)) {
							$cssBefore .= $k2 . ':' . $v2 . ' !important;' . PHP_EOL;
							continue;
						}

						if (in_array($k2, $afterCssProps)) {
							$cssAfter[$k2] = $v2;
							continue;
						}


						if ( 'content-h-align' === $k2 ) {
							$k2 = 'text-align';
							$v2 = str_replace( 'content-horiz-', '', $v2 );

							// These settings were introduced because of vertical align
							switch ( $v2 ){
								case 'center' : $mediaCss .= 'justify-content:center !important;' . PHP_EOL; break;
								case 'right' : $mediaCss .= 'justify-content:flex-end !important;' . PHP_EOL; break;
							}
						}

						// Do nothing for these keys - they will be checked later
						if ( in_array( $k2, array( 'shadow-size', 'shadow-color', 'shadow-offset-h', 'shadow-offset-v' ) ) ) {
							continue;
						}


						// Display
						if ( 'display' === $k2 ) {
                            if( 'none' === $v2 && $in_composer && td_util::get_option('tdcShowHiddenElements') == 'true' ) {
                                $mediaCss .=
                                    'opacity:.3!important;'  . PHP_EOL .
                                    'filter: grayscale(1)!important;' . PHP_EOL;
                                $tdcShowHiddenLabel = true;
                                continue;
                            }

							if ( 'show' !== $v2 && '' !== $v2 ) {
								$mediaCss .= $k2 . ':' . $v2 . ' !important;' . PHP_EOL;
							}
							continue;
						}


						$mediaCss .= $k2 . ':' . $v2 . ' !important;' . PHP_EOL;
					}




					// Shadow settings
					$shadow_size = 0;

					if ( array_key_exists( 'shadow-size', $mediaArray ) ) {
						$shadow_size = $mediaArray['shadow-size'] . 'px';
					}

					// check media-all
					if ( empty( $shadow_size ) && array_key_exists('all', $tdcCssArray) && array_key_exists( 'shadow-size', $tdcCssArray['all'] ) && ! empty( $tdcCssArray['all']['shadow-size'] ) ) {
						$shadow_size = $tdcCssArray['all']['shadow-size'] . 'px';
					}


					if ( ! empty( $shadow_size ) ) {

						$shadow_offset_h = 0;
						if ( array_key_exists( 'shadow-offset-h', $mediaArray ) ) {
							$shadow_offset_h = $mediaArray['shadow-offset-h'] . 'px';
						}
						if ( empty( $shadow_offset_h ) && ! empty( $tdcCssArray['all'] ) && array_key_exists( 'shadow-offset-h', $tdcCssArray['all'] ) && ! empty( $tdcCssArray['all']['shadow-offset-h'] ) ) {
							$shadow_offset_h = $tdcCssArray['all']['shadow-offset-h'] . 'px';
						}

						$shadow_offset_v = 0;
						if ( array_key_exists( 'shadow-offset-v', $mediaArray ) ) {
							$shadow_offset_v = $mediaArray['shadow-offset-v'] . 'px';
						}
						if ( empty( $shadow_offset_v ) && ! empty( $tdcCssArray['all'] ) && array_key_exists( 'shadow-offset-v', $tdcCssArray['all'] ) && ! empty( $tdcCssArray['all']['shadow-offset-v'] ) ) {
							$shadow_offset_v = $tdcCssArray['all']['shadow-offset-v'] . 'px';
						}

						$shadow_color = 0;
						if ( array_key_exists( 'shadow-color', $mediaArray ) ) {
							$shadow_color = $mediaArray['shadow-color'];
						}
						if ( empty( $shadow_color ) ) {
							if ( ! empty( $tdcCssArray['all'] ) && array_key_exists( 'shadow-color', $tdcCssArray['all'] ) && ! empty( $tdcCssArray['all']['shadow-color'] ) ) {
								$shadow_color = $tdcCssArray['all']['shadow-color'];
							} else {
								$shadow_color = '#888888';
							}
						}
						$mediaCss .= 'box-shadow:' . $shadow_offset_h . ' ' . $shadow_offset_v . ' ' . $shadow_size . ' ' . $shadow_color . ' !important;' . PHP_EOL;
					}





					// Add default value for 'border-style'
					// Add default value for 'border-color'
					if ($borderInLimit && !$borderInAll) {
						if (!isset($mediaArray['border-style'])) {
							if ($moveBorderSettingsOnBefore) {
								$cssBefore .= 'border-style:solid !important;' . PHP_EOL;
							} else {
								$mediaCss .= 'border-style:solid !important;' . PHP_EOL;
							}
						}
						if (!isset($mediaArray['border-color'])) {
							if ($moveBorderSettingsOnBefore) {
								$cssBefore .= 'border-color:#888888 !important;' . PHP_EOL;
							} else {
								$mediaCss .= 'border-color:#888888 !important;' . PHP_EOL;
							}
						}
					}





					// Set border width css for 'all'
					$borderCss = $this->getBorderWidth($borderWidthCssProps);

					if ($borderCss !== '') {
						if ($moveBorderSettingsOnBefore) {
							$cssBefore .= $borderCss;
						} else {
							$mediaCss .= $borderCss;
						}
					}




//					// Set background css for limit
//					$backgroundCss = $this->getBackground($beforeCssProps);
//
//					if ($backgroundCss !== '') {
//						$mediaCss .= $backgroundCss;
//					}




					if ( $cssElementStyle !== '' || $cssBefore !== '' || $cssAfter !== '' || $tdcShowHiddenLabel ) {

						$mediaQuery = '';
						if ( isset( $val['min_width'] ) ) {
							$mediaQuery = '(min-width: ' . $val['min_width'] . 'px)';
						}

						if ( isset( $val['max_width'] ) ) {

							if ( '' !== $mediaQuery ) {
								$mediaQuery .= ' and ';
							}
							$mediaQuery .= '(max-width: ' . $val['max_width'] . 'px)';
						}

						if ( '' !== $mediaQuery ) {

							$positionElement = false;

							if ($cssElementStyle !== '') {
								$cssOutput .= PHP_EOL . '/* ' . $key . ' */' . PHP_EOL;
								$cssOutput .= '@media ' . $mediaQuery . PHP_EOL;
								$cssOutput .= '{'. PHP_EOL;
								$cssOutput .= '.' . $this->get_att('tdc_css_class_style') . '{' . PHP_EOL . $cssElementStyle . '}' . PHP_EOL;
								$cssOutput .= '}'. PHP_EOL;

								$positionElement = true;
							}

							if ($cssBefore !== '') {

								// Add default value for 'background-style'
								if ($backgroundInLimit && !$backgroundInAll) {
									if (!isset($mediaArray['background-style'])) {
										$cssBefore .= 'background-size:cover !important;' . PHP_EOL;
									}
									if (!isset($mediaArray['background-position'])) {
										$cssBefore .= 'background-position:center top !important;' . PHP_EOL;
									}
								}

								//$tdcCssProcessed .= '.' . $this->get_att('tdc_css_class') . '::before{' . PHP_EOL . $cssBeforeSettings . $cssBefore . '}' . PHP_EOL;

								$beforeCssOutput .= PHP_EOL . '/* ' . $key . ' */' . PHP_EOL;
								$beforeCssOutput .= '@media ' . $mediaQuery . PHP_EOL;
								$beforeCssOutput .= '{'. PHP_EOL;
								//$beforeCssOutput .= '.' . $this->get_att('tdc_css_class_style') . '::before{' . PHP_EOL . $cssBeforeSettings . $cssBefore . '}' . PHP_EOL;
								$beforeCssOutput .= '.' . $this->get_att('tdc_css_class_style') . ' > .td-element-style-before{' . PHP_EOL . $cssBeforeSettings . $cssBefore . '}' . PHP_EOL;
								$beforeCssOutput .= '}'. PHP_EOL;

								$positionElement = true;
							}

							if (!empty($cssAfter)) {

								$css = '';
								$deg = '';

								if (array_key_exists('gradient-direction', $cssAfter )) {
									$deg = $cssAfter['gradient-direction'] . 'deg,';
								}


								if (array_key_exists('color-1-overlay', $cssAfter) && array_key_exists('color-2-overlay', $cssAfter)) {
									$css .= 'background: linear-gradient(' . $deg . $cssAfter['color-1-overlay'] . ', '  . $cssAfter['color-2-overlay'] . ') !important;' . PHP_EOL;
								} else if (array_key_exists('color-1-overlay', $cssAfter)) {
									if (array_key_exists('color-2-overlay', $cssAfterAll)) {
										$css .= 'background: linear-gradient(' . $deg . $cssAfter['color-1-overlay'] . ', ' . $cssAfterAll['color-2-overlay'] . ') !important;' . PHP_EOL;
									} else {
										$css .= 'background: ' . $cssAfter['color-1-overlay'] .' !important;' . PHP_EOL;
									}
								} else if (array_key_exists('color-2-overlay', $cssAfter)) {
									if (array_key_exists('color-1-overlay', $cssAfterAll)) {
										$css .= 'background: linear-gradient(' . $deg . $cssAfterAll['color-1-overlay'] . ', ' . $cssAfter['color-2-overlay'] . ') !important;' . PHP_EOL;
									} else {
										$css .= 'background: ' . $cssAfter['color-2-overlay'] .' !important;' . PHP_EOL;
									}
								} else {
									$css .= 'background: linear-gradient(' . $deg . $cssAfterAll['color-1-overlay'] . ', ' . $cssAfterAll['color-2-overlay'] . ') !important;' . PHP_EOL;
								}

								if (array_key_exists('opacity', $cssAfter)) {
									$css .= 'opacity: ' . $cssAfter['opacity'] .' !important;' . PHP_EOL;
								}

								if ( '' !== $css ) {

									// Important!
									if ( $this instanceof vc_row || $this instanceof vc_row_inner ) {
										$clearfixColumns = true;
									}

									//$tdcCssProcessed .= PHP_EOL . '.' . $this->get_att( 'tdc_css_class' ) . $childElement . '::after{' . PHP_EOL . $cssAfterSettings . $css . '}' . PHP_EOL;
									$afterCssOutput .= PHP_EOL . '/* ' . $key . ' */' . PHP_EOL;
									$afterCssOutput .= '@media ' . $mediaQuery . PHP_EOL;
									$afterCssOutput .= '{'. PHP_EOL;
									$afterCssOutput .= PHP_EOL . '.' . $this->get_att( 'tdc_css_class_style' ) . '::after{' . PHP_EOL . $cssAfterSettings . $css . '}' . PHP_EOL;
									$afterCssOutput .= '}'. PHP_EOL;

									$positionElement = true;
								}
							}

                            if( !empty( $tdcShowHiddenLabel ) ) {

                                $cssTDCHiddenLabelAfter = '';
                                if( !empty( $shortcode_details ) ) {
                                    $cssTDCHiddenLabelAfter = PHP_EOL . '.' . $this->get_att( 'tdc_css_class_style' ) . '_tdc_hidden_label:after {' . PHP_EOL . 'content: "Hidden: ' . $shortcode_details['name'] . '"' . PHP_EOL . '}';
                                }

                                $tdcHiddenLabelCssOutput .= PHP_EOL . '/* ' . $key . ' */' . PHP_EOL . '@media ' . $mediaQuery . PHP_EOL . '{' . PHP_EOL;
                                    $tdcHiddenLabelCssOutput .= '.' . $this->get_att( 'tdc_css_class_style' ) . '_tdc_hidden_label {' . PHP_EOL . 'display:block;' . PHP_EOL . '}';
                                    $tdcHiddenLabelCssOutput .= !empty( $cssTDCHiddenLabelAfter ) ? $cssTDCHiddenLabelAfter : '';
                                $tdcHiddenLabelCssOutput .= PHP_EOL . '}' . PHP_EOL;

                            }

							if ($positionElement) {
								$mediaCss .= 'position:relative;' . PHP_EOL;
							}

							if ($mediaCss !== '') {
								$tdcCssProcessed .= PHP_EOL . '/* ' . $key . ' */' . PHP_EOL;
								$tdcCssProcessed .= '@media ' . $mediaQuery . PHP_EOL;
								$tdcCssProcessed .= '{' . PHP_EOL;
								$tdcCssProcessed .= '.' . $this->get_att( 'tdc_css_class' ) . '{' . PHP_EOL . $mediaCss . '}' . PHP_EOL;
								$tdcCssProcessed .= '}' . PHP_EOL;
							}

                            if ($mediaCssInnerRow !== '') {
                                $tdcCssProcessed .= PHP_EOL . '/* ' . $key . ' */' . PHP_EOL;
								$tdcCssProcessed .= '@media ' . $mediaQuery . PHP_EOL;
								$tdcCssProcessed .= '{' . PHP_EOL;
                                $tdcCssProcessed .= '.tdc-element-inner-row[data-tdc-inner-row-uid="' . $this->block_uid . '"]{' . PHP_EOL . $mediaCssInnerRow . '}' . PHP_EOL;
								$tdcCssProcessed .= '}' . PHP_EOL;
                            }
						}
					}
				}
				if (!empty($tdcCssProcessed)) {
					$buffy .= PHP_EOL . '/* inline tdc_css att - generated by TagDiv Composer */' . PHP_EOL . $tdcCssProcessed;
				}
			}
		}

		return $buffy;

	}



	/**
	 * This runs only on loop blocks!
	 * @return array the $td_pull_down_items
	 */
	private function block_loop_get_pull_down_items() {

		$td_pull_down_items = array();

		$td_ajax_filter_type   = $this->get_att('td_ajax_filter_type');
		$td_filter_default_txt = $this->get_att('td_filter_default_txt');
		$td_ajax_filter_ids    = $this->get_att('td_ajax_filter_ids');

		// td_block_mega_menu has it's own pull down implementation!
		if ( get_class($this) != 'td_block_mega_menu' ) {
			// prepare the array for the td_pull_down_items, we send this array to the block_template

			if ( !empty( $td_ajax_filter_type ) ) {

				// make the default current pull down item (the first one is the default)
				$td_pull_down_items[0] = array (
					'name' => $td_filter_default_txt,
					'id' => ''
				);

				switch( $td_ajax_filter_type ) {
					case 'td_products_category_ids_filter': // by product category

						$td_product_categories = get_terms( array(
							'taxonomy' => 'product_cat',
							'include' => $td_ajax_filter_ids,
							'number' => 100 // limit the number of product cats shown in the drop down
						));

						// check if there's any id in the list
						if ( ! empty( $td_ajax_filter_ids ) ) {

							// break the categories string
							$td_ajax_filter_ids = explode(',', $td_ajax_filter_ids );

							// order the categories - match the order set in the block settings
							foreach ( $td_ajax_filter_ids as $td_product_category_id ) {
								$td_product_category_id = trim( $td_product_category_id );

								foreach ( $td_product_categories as $td_product_category ) {

									// retrieve the category
									if ( $td_product_category_id == $td_product_category->term_id ) {
										$td_pull_down_items [] = array(
											'name' => $td_product_category->name,
											'id' => $td_product_category->term_id,
										);
										break;
									}
								}
							}

                        // if no prod categories ids are added
						} else {
							foreach ( $td_product_categories as $td_product_category ) {
								$td_pull_down_items [] = array(
									'name' => $td_product_category->name,
									'id' => $td_product_category->term_id,
								);
							}
						}
						break;

					case 'td_category_ids_filter': // by category
						$td_categories = get_categories( array(
							'include' => $td_ajax_filter_ids,
							'exclude' => '1',
							'number' => 100 //limit the number of categories shown in the drop down
						));

						// check if there's any id in the list
						if ( !empty( $td_ajax_filter_ids ) ) {
							// break the categories string
							$td_ajax_filter_ids = explode(',', $td_ajax_filter_ids);

							// order the categories - match the order set in the block settings
							foreach ( $td_ajax_filter_ids as $td_category_id ) {
								$td_category_id = trim( $td_category_id );

								foreach ( $td_categories as $td_category ) {

									// retrieve the category
									if ( $td_category_id == $td_category->cat_ID ) {
										$td_pull_down_items [] = array(
											'name' => $td_category->name,
											'id' => $td_category->cat_ID,
										);
										break;
									}
								}
							}

							// if no category ids are added
						} else {
							foreach ( $td_categories as $td_category ) {
								$td_pull_down_items [] = array(
									'name' => $td_category->name,
									'id' => $td_category->cat_ID,
								);
							}
						}
						break;

					case 'td_taxonomy_ids_filter': // by taxonomy

						if ( !empty( $td_ajax_filter_ids ) ) {
							$tax_ids = explode(',', $td_ajax_filter_ids );
							foreach ( $tax_ids as $tax_id ) {
								$tax = get_term( $tax_id );
								$td_pull_down_items [] = array(
									'name' => $tax->name,
									'id' => $tax_id,
								);
							}
						}
						break;

					case 'td_author_ids_filter': // by author
						$td_authors = get_users(array('who' => 'authors', 'include' => $td_ajax_filter_ids));
						foreach ($td_authors as $td_author) {
							$td_pull_down_items []= array (
								'name' => $td_author->display_name,
								'id' => $td_author->ID,
							);
						}
						break;

					case 'td_tag_slug_filter': // by tag slug
						$tag_ids = explode(',', trim($td_ajax_filter_ids));
						$td_tags = get_tags(array(
							'include' => $tag_ids,
							'orderby' => 'include'
						));
						foreach ($td_tags as $td_tag) {
							$td_pull_down_items []= array (
								'name' => $td_tag->name,
								'id' => $td_tag->term_id,
							);
						}
						break;

					case 'td_popularity_filter_fa': // by popularity
						$td_pull_down_items []= array (
							'name' => __td('Featured', TD_THEME_NAME),
							'id' => 'featured',
						);
						$td_pull_down_items []= array (
							'name' => __td('All time popular', TD_THEME_NAME),
							'id' => 'popular',
						);
						break;
				}
			}
		}

		return $td_pull_down_items;
	}




    /**
     * this function adds the live filters atts when $atts['live_filter'] is set. The attributs are imediatly available to all
     * after the render method is called
     *   - $atts['live_filter_cur_post_id'] - the current post id
     *   - $atts['live_filter_cur_post_author'] - the current post author
     * @since 21.2.2018 - leave the live_filter_cur_post_id & live_filter_cur_post_author alone if they are sent via the shortcode atts.
     * Why? We manually send them in tagDiv Template Builder
     * @param $atts
     * @return mixed
     */
    private function add_live_filter_atts($atts) {
        if (!empty($atts['live_filter'])) {
            if (!isset($atts['live_filter_cur_post_id'])) {
                $atts['live_filter_cur_post_id'] = get_queried_object_id(); //add the current post id
            }
            if (!isset($atts['live_filter_cur_post_author'])) {
                $atts['live_filter_cur_post_author'] = get_post_field( 'post_author', $atts['live_filter_cur_post_id']); //get the current author
            }

        }
        return $atts;
    }



    /**
     * Used by blocks that need auto generated titles
     * @return string
     */
    function get_block_title() {
        return $this->block_template()->get_block_title();
    }


    /**
     * shows a pull down filter based on the $this->atts
     * @return string
     */
    function get_pull_down_filter() {
        return $this->block_template()->get_pull_down_filter();
    }


	/**
	 * the block pagination
	 *
	 * @param string $prev_icon
	 * @param string $prev_icon_class
	 * @param string $nex_icon
	 * @param string $next_icon_class
	 *
	 * @return string
	 */
    function get_block_pagination( $prev_icon = '', $nex_icon = '', $prev_icon_class = '',  $next_icon_class = '' ) {

	    $offset = 0;

	    if ( isset( $this->atts['offset'] ) ) {
		    $offset = (int)$this->atts['offset'];
	    }

        $prev_icon_data = '';
        if( $prev_icon_class != '' && ( td_util::tdc_is_live_editor_iframe() || td_util::tdc_is_live_editor_ajax() ) ) {
            $prev_icon_data = 'data-td-svg-icon="' . $prev_icon_class . '"';
        }

        $next_icon_data = '';
        if( $prev_icon_class != '' && ( td_util::tdc_is_live_editor_iframe() || td_util::tdc_is_live_editor_ajax() ) ) {
            $next_icon_data = 'data-td-svg-icon="' . $next_icon_class . '"';
        }

	    $buffy = '';

	    $ajax_pagination = $this->get_att('ajax_pagination');

	    $limit = (int)$this->get_att('limit');
	    $found_posts = $this->is_products_block() ? (int)$this->td_query['total'] : ( $this->is_loop_block() ? (int)$this->td_query->found_posts : 0 );

        switch ( $ajax_pagination ) {

            case 'next_prev':
                    if ( $prev_icon == '' ) {
                        $prev_icon = '<i class="td-next-prev-icon td-icon-font td-icon-menu-left"></i>';
                    } else {
                        if( base64_encode( base64_decode( $prev_icon ) ) == $prev_icon ) {
                            $prev_icon = '<span class="td-next-prev-icon td-next-prev-icon-svg " ' . $prev_icon_data . '>' . base64_decode( $prev_icon ) . '</span>';
                        } else {
                            $prev_icon = '<i class="td-next-prev-icon ' . $prev_icon . '"></i>';
                        }
                    }
                    if ( $nex_icon == '' ) {
                        $nex_icon = '<i class="td-next-prev-icon td-icon-font td-icon-menu-right"></i>';
                    } else {
                        if( base64_encode( base64_decode( $nex_icon ) ) == $nex_icon ) {
                            $nex_icon = '<span class="td-next-prev-icon td-next-prev-icon-svg " ' . $next_icon_data . '>' . base64_decode( $nex_icon ) . '</span>';
                        } else {
                            $nex_icon = '<i class="td-next-prev-icon ' . $nex_icon . '"></i>';
                        }
                    }

                    $buffy .= '<div class="td-next-prev-wrap">';
                    $buffy .= '<a href="#" class="td-ajax-prev-page ajax-page-disabled" aria-label="prev-page" id="prev-page-' . $this->block_uid . '" data-td_block_id="' . $this->block_uid . '">' . $prev_icon . '</a>';

                    if ( $found_posts - $offset <= $limit ) {
                        // hide next page because we don't have enough results
                        $buffy .= '<a href="#"  class="td-ajax-next-page ajax-page-disabled" aria-label="next-page-disabled" id="next-page-' . $this->block_uid . '" data-td_block_id="' . $this->block_uid . '">' . $nex_icon . '</a>';
                    } else {
                        $buffy .= '<a href="#"  class="td-ajax-next-page" aria-label="next-page" id="next-page-' . $this->block_uid . '" data-td_block_id="' . $this->block_uid . '">' . $nex_icon . '</a>';
                    }

                    $buffy .= '</div>';
                break;

            case 'load_more':
                if ( $nex_icon == '' ) {
                    $nex_icon = '<i class="td-load-more-icon td-icon-font td-icon-menu-right"></i>';
                } else {
                    if( base64_encode( base64_decode( $nex_icon ) ) == $nex_icon ) {
                        $nex_icon = '<span class="td-load-more-icon td-load-more-icon-svg " ' . $next_icon_data . '>' . base64_decode( $nex_icon ) . '</span>';
                    } else {
                        $nex_icon = '<i class="td-load-more-icon ' . $nex_icon . '"></i>';
                    }
                }

	            if ( $found_posts - $offset > $limit ) {
		            $buffy .= '<div class="td-load-more-wrap">';
                        $buffy .= '<a href="#" class="td_ajax_load_more td_ajax_load_more_js" aria-label="'. __td("Load more", TD_THEME_NAME).'" id="next-page-' .     $this->block_uid . '" data-td_block_id="' . $this->block_uid . '">' . __td('Load more', TD_THEME_NAME);
                            $buffy .= $nex_icon;
		                $buffy .= '</a>';
		            $buffy .= '</div>';
	            }
                break;

            case 'infinite':
				// show the infinite pagination only if we have more posts
		        if ( $found_posts - $offset > $limit ) {
		            $buffy .= '<div class="td_ajax_infinite" id="next-page-' . $this->block_uid . '" data-td_block_id="' . $this->block_uid . '">';
		            $buffy .= ' ';
		            $buffy .= '</div>';

		            $buffy .= '<div class="td-load-more-wrap td-load-more-infinite-wrap" id="infinite-lm-' . $this->block_uid . '">';
                    $buffy .= '<a href="#" class="td_ajax_load_more td_ajax_load_more_js" aria-label="'. __td("Load more", TD_THEME_NAME).'" id="next-page-' . $this->block_uid . '" data-td_block_id="' . $this->block_uid . '">' . __td('Load more', TD_THEME_NAME);
		            $buffy .= '<i class="td-icon-font td-icon-menu-down"></i>';
		            $buffy .= '</a>';
		            $buffy .= '</div>';
	            }
                break;

        }

        if( TD_THEME_NAME == "Newspaper" && ( $ajax_pagination != '' && $ajax_pagination != 'numbered' ) ) {
            if ( $ajax_pagination == 'infinite' ) {
                td_resources_load::render_script( TDC_SCRIPTS_URL . '/tdInfiniteLoader.js' . TDC_SCRIPTS_VER, 'tdInfiniteLoader-js', '', 'footer');
            }
            td_resources_load::render_script( TDC_SCRIPTS_URL . '/tdLoadingBox.js' . TDC_SCRIPTS_VER, 'tdLoadingBox-js', '', 'footer');
        }

        return $buffy;
    }















    function get_block_js() {
	    // td-composer PLUGIN uses this hook to call $this->js_callback_ajax
	    // @see tdc_ajax.php -> on_ajax_render_shortcode in td-composer
	    do_action('td_block__get_block_js', array(&$this));

	    // Allow the scripts of mega menu blocks
	    if (!($this instanceof td_block_mega_menu) && td_util::tdc_is_live_editor_iframe()) {
		    td_js_buffer::add_to_footer($this->js_tdc_get_composer_block());
		    return '';
	    }

		// do not run in ajax requests
	    if (td_util::tdc_is_live_editor_ajax()) {
		    return '';
	    }

        //get the js for this block - do not load it in inline mode in visual composer
        if (td_util::vc_is_inline()) {
            return '';
        }


	    // do not output the block js if it's not a loop block
	    if ($this->is_loop_block() === false) {
		    return '';
	    }



	    // new tdBlock() item for ajax blocks / loop_blocks
	    // we don't get here on blocks that are not loop blocks

	    $block_item = 'block_' . $this->block_uid;

	    $offset = 0;

	    if ( isset( $this->atts['offset'] ) ) {
		    $offset = (int)$this->atts['offset'];
	    }

	    $limit = (int)$this->get_att('limit');
	    $found_posts = $this->is_products_block() ? (int)$this->td_query['total'] : (int)$this->td_query->found_posts;
	    $post_count = $this->is_products_block() ? (int)$this->td_query['per_page'] : (int)$this->td_query->post_count;
	    $max_num_pages = $this->is_products_block() ? (int)$this->td_query['total_pages'] : (int)$this->td_query->max_num_pages;

	    $buffy = '<script>';
		    $atts = $this->atts;
		    $buffy .= 'var ' . $block_item . ' = new tdBlock();' . "\n";
		    $buffy .= $block_item . '.id = "' . $this->block_uid . '";' . "\n";
		    $buffy .= $block_item . ".atts = '" . str_replace("'", "\u0027", json_encode($this->atts)) . "';" . "\n";
		    $buffy .= $block_item . '.td_column_number = "' . $atts['td_column_number'] . '";' . "\n";
		    $buffy .= $block_item . '.block_type = "' . get_class($this) . '";' . "\n";

		    // wordpress wp query params
		    $buffy .= $block_item . '.post_count = "' . $post_count . '";' . "\n";
		    $buffy .= $block_item . '.found_posts = "' . $found_posts . '";' . "\n";

		    $buffy .= $block_item . '.header_color = "' . $atts['header_color'] . '";' . "\n"; // the header_color is needed for the animated loader
		    $buffy .= $block_item . '.ajax_pagination_infinite_stop = "' . $atts['ajax_pagination_infinite_stop'] . '";' . "\n";


		    // The max_num_pages is computed so it considers the offset and the limit atts settings
		    // There were necessary these changes because on the user interface there are js scripts that use the max_num_pages js variable to show/hide some ui components
		    if ( $offset > 0 ) {

			    if ( $limit != 0 ) {
				    $buffy .= $block_item . '.max_num_pages = "' . ceil( ( $found_posts - $offset ) / $limit ) . '";' . "\n";

			    } else if ( get_option('posts_per_page') != 0 ) {
				    $buffy .= $block_item . '.max_num_pages = "' . ceil( ( $found_posts - $offset ) / get_option('posts_per_page') ) . '";' . "\n";
			    }

		    } else {
			    $buffy .= $block_item . '.max_num_pages = "' . $max_num_pages . '";' . "\n";
		    }

		    $buffy .= 'tdBlocksArray.push(' . $block_item . ');' . "\n";
	    $buffy .= '</script>';





        $td_column_number = $this->get_att('td_column_number');
        if ( empty( $td_column_number ) ) {
            $td_column_number = td_global::vc_get_column_number(); // get the column width of the block so we can sent it to the server. If the shortcode already has a user defined column number, we use that
        }


        // ajax subcategories preloader
        // !!!! preloading "all" filter content should happen regardless of the setting
        if ( !empty($this->td_block_template_data['td_pull_down_items']) and !empty($this->atts['td_ajax_preloading']) ) {

	        /*  -------------------------------------------------------------------------------------
	            add 'ALL' item to the cache
	        */
            // pagination - we need to compute the pagination for each cache entry
            $td_hide_next = false;
            if ( ! empty( $offset ) && ! empty( $limit ) && ( $limit != 0 ) ) {
                if ( 1 >= ceil(( $found_posts - $offset ) / $limit ) ) {
                    $td_hide_next = true; //hide link on last page
                }
            } else if ( 1 >= $this->td_query->max_num_pages ) {
                $td_hide_next = true; //hide link on last page
            }

            // this will be send to JS bellow
            $buffyArray = array(
                'td_data' => $this->inner($this->td_query->posts, $td_column_number),
                'td_block_id' => $this->block_uid,
                'td_hide_prev' => true,  // this is the first page
                'td_hide_next' => $td_hide_next
            );


	        /*  -------------------------------------------------------------------------------------
	            add the rest of the items to the local cache
	        */
            ob_start();
            // we need to clone the object to set is_ajax_running to true
            // first we set an object for the all filter
            ?>
            <script>
                var tmpObj = JSON.parse(JSON.stringify(<?php printf( '%1$s', $block_item ) ?>));
                tmpObj.is_ajax_running = true;
                var currentBlockObjSignature = JSON.stringify(tmpObj);
                tdLocalCache.set(currentBlockObjSignature, JSON.stringify(<?php echo json_encode($buffyArray) ?>));
                <?php
                    foreach ($this->td_block_template_data['td_pull_down_items'] as $count => $item) {

                     	//removes the offset on preloading for blocks pull down filter items excepting the "All" filter tab which will load posts with the offset
						if (!empty($this->atts['offset'])){
							unset($this->atts['offset']);
						}

                        if (empty($item['id'])) {
                            continue;
                        }

                        // preload only 6 or 20 items depending on the setting
                        if ($this->atts['td_ajax_preloading'] == 'preload_all' and $count > 20) {
                            break;
                        }
                        else if ($this->atts['td_ajax_preloading'] == 'preload' and $count > 6) {
                            break;
                        }

                        $ajax_parameters = array (
                            'td_atts' => $this->atts,            // original block atts
                            'td_column_number' => $td_column_number,    // should not be 0 (1 - 2 - 3)
                            'td_current_page' => 1,    // the current page of the block
                            'td_block_id' => $this->block_uid,        // block uid
                            'block_type' => get_class($this),         // the type of the block / block class
                            'td_filter_value' => $item['id']     // the id for this specific filter type. The filter type is in the td_atts
                        );
                        ?>
                            tmpObj = JSON.parse(JSON.stringify(<?php printf( '%1$s', $block_item ) ?>));
                            tmpObj.is_ajax_running = true;
                            tmpObj.td_current_page = 1;
                            tmpObj.td_filter_value = <?php echo json_encode($item['id']) ?>;
                            var currentBlockObjSignature = JSON.stringify(tmpObj);
                            tdLocalCache.set(currentBlockObjSignature, JSON.stringify(<?php echo td_ajax::on_ajax_block($ajax_parameters) ?>));
                        <?php
                    }
                ?>
            </script>
            <?php
            //ob_clean();
            $buffy.= ob_get_clean();
        } // end preloader if





        return $buffy;
    }


	/**
	 * tagDiv composer specific code:
	 * This is a callback that is retrieve and injected into the iFrame by td-composer on Ajax operations
	 * This js runs on the client after a drag and drop operation in td-composer
	 * @return string JS code that is sent straight to an eval() on the client side
	 */
	function js_tdc_callback_ajax() {

		$buffy = '';


		$buffy .= $this->js_tdc_get_composer_block();



		// If this is not a loop block or if we don't have pull down ajax filters, do not run. This is just to fix the pulldown items on
		// content blocks

		if (($this->is_loop_block() === true && !empty($this->td_block_template_data['td_pull_down_items'])) ) {
			ob_start();
			?>
			<script>

                // block subcategory ajax filters!
				var jquery_object_container = jQuery('.<?php printf( '%1$s', $this->block_uid ) ?>');
				if ( jquery_object_container.length) {
					var horizontal_jquery_obj = jquery_object_container.find('.td-subcat-list:first');

					if ( horizontal_jquery_obj.length) {
						// make a new item
						var pulldown_item_obj = new tdPullDown.item();
						pulldown_item_obj.blockUid = jquery_object_container.data('td-block-uid'); // get the block UID
						pulldown_item_obj.horizontal_jquery_obj = horizontal_jquery_obj;
						pulldown_item_obj.vertical_jquery_obj = jquery_object_container.find('.td-subcat-dropdown:first');
						pulldown_item_obj.horizontal_element_css_class = 'td-subcat-item';
						pulldown_item_obj.container_jquery_obj = horizontal_jquery_obj.closest('.td-block-title-wrap');
						pulldown_item_obj.excluded_jquery_elements = [pulldown_item_obj.container_jquery_obj.find('.td-pulldown-size')];
						tdPullDown.add_item(pulldown_item_obj); // add the item

					}
				}

			</script>
			<?php
			$buffy .= td_util::remove_script_tag(ob_get_clean());
		}



		if ( 'tdb_single_post_share' === get_class($this) || 'td_block_social_share' === get_class($this)) {
			ob_start();
			?>
			<script>

                // single cloud lib share block pulldown
				var jquery_object_container = jQuery('.<?php printf( '%1$s', $this->block_uid ) ?>');

                if (jquery_object_container.length) {

                    if ( jquery_object_container.hasClass('td-post-sharing-show-all-icons') ) {

                        blockUid = jquery_object_container.data('td-block-uid'); // get the block UID
                        jQuery('#' + blockUid).addClass('td-social-show-all');

                    } else  {

                        var horizontal_jquery_obj = jquery_object_container.find( '.td-post-sharing-visible:first' );

                        if ( horizontal_jquery_obj.length ) {

                            var pulldown_item_obj = new tdPullDown.item();
                            pulldown_item_obj.blockUid = jquery_object_container.data('td-block-uid'); // get the block UID
                            pulldown_item_obj.horizontal_jquery_obj = horizontal_jquery_obj;
                            pulldown_item_obj.vertical_jquery_obj = jquery_object_container.find('.td-social-sharing-hidden:first');
                            pulldown_item_obj.horizontal_element_css_class = 'td-social-sharing-button-js';
                            pulldown_item_obj.container_jquery_obj = horizontal_jquery_obj.parents('.wpb_wrapper:first');
                            tdPullDown.add_item(pulldown_item_obj);

                        }
                    }
                }


			</script>
			<?php
			$buffy .= td_util::remove_script_tag(ob_get_clean());
		}



		if ( 'tdb_category_sibling_categories' === get_class($this) ) {
			ob_start();
			?>
			<script>

                // block subcategory ajax filters!
				var jquery_object_container = jQuery('.<?php printf( '%1$s', $this->block_uid ) ?>');
				if ( jquery_object_container.length) {

				    if( jquery_object_container.hasClass('tdb-category-siblings-inline') ) {

                        var horizontal_jquery_obj = jquery_object_container.find('.td-category:first');

                        if ( horizontal_jquery_obj.length ) {
                            var pulldown_item_obj = new tdPullDown.item();
                            pulldown_item_obj.blockUid = jquery_object_container.data('td-block-uid'); // get the block UID
                            pulldown_item_obj.horizontal_jquery_obj = horizontal_jquery_obj;
                            pulldown_item_obj.vertical_jquery_obj = jquery_object_container.find('.td-subcat-dropdown:first');
                            pulldown_item_obj.horizontal_element_css_class = 'entry-category';
                            pulldown_item_obj.container_jquery_obj = horizontal_jquery_obj.parents('.tdb-category-siblings:first');
                            tdPullDown.add_item(pulldown_item_obj);
                        }

                    }

				}

			</script>
			<?php
			$buffy .= td_util::remove_script_tag(ob_get_clean());
		}



		return $buffy;

	}





	/**
	 * tagDiv composer specific code:
	 *  - it's added to the end of the iFrame when the live editor is active (when @see td_util::tdc_is_live_editor_iframe()  === true)
	 *  - it is injected int he iFrame and evaluated there in the global scoupe when a new block is added to the page via AJAX!
	 * @return string the JS without <script> tags
	 */
	function js_tdc_get_composer_block() {
		ob_start();
		?>
		<script>
			(function () {
				// js_tdc_get_composer_block code for "<?php echo get_class($this) ?>"

                var tdComposerBlockItem = new tdcComposerBlocksApi.item();
				tdComposerBlockItem.blockUid = '<?php printf( '%1$s', $this->block_uid ) ?>';

                tdComposerBlockItem.callbackAdd = function (blockUid) {
                    if ( 'undefined' !== typeof window.tdReadingProgressBar ) {
                        let $progressBar = jQuery('.tdb_single_reading_progress[data-td-block-uid="' + blockUid + '"]');


                        if( $progressBar.length ) {
                            let barPosition = $progressBar.data('bar-position');

                            let $readingProgressBarItem = new tdReadingProgressBar.item();
                            $readingProgressBarItem.blockUid = blockUid;
                            $readingProgressBarItem.barPosition = barPosition;
                            tdReadingProgressBar.addItem($readingProgressBarItem);

                            if( barPosition === 'top' || barPosition === 'bottom' ) {
                                tdReadingProgressBar.createFixedBar($readingProgressBarItem, 0, 30);
                            }
                        }
                    }
                };

				tdComposerBlockItem.callbackDelete = function(blockUid) {

					if ( 'undefined' !== typeof window.tdPullDown ) {
						// delete the existing pulldown if it exists
						tdPullDown.deleteItem(blockUid);
					}

					if ( 'undefined' !== typeof window.tdAnimationSprite ) {
						// delete the animation sprite if it exits
						tdAnimationSprite.deleteItem(blockUid);
					}

					if ( 'undefined' !== typeof window.tdTrendingNow ) {
						// delete the animation sprite if it exits
						tdTrendingNow.deleteItem(blockUid);
					}

					if ( 'undefined' !== typeof window.tdHomepageFull ) {
						// delete the homepagefull if it exits
						tdHomepageFull.deleteItem( blockUid );
					}

                    if ( 'undefined' !== typeof window.tdPopupModal ) {
                        // delete the modal if it exists
                        tdPopupModal.deleteItem( blockUid );
                    }

                    if ( 'undefined' !== typeof window.tdReadingProgressBar ) {
                        // delete the progress bar if it exists
                        tdReadingProgressBar.deleteItem( blockUid );
                    }

                    if ( 'undefined' !== typeof window.tdbLocationFinder ) {
                        // delete the location finder block if it exists
                        tdbLocationFinder.deleteItem( blockUid );
                    }

                    if ( 'undefined' !== typeof window.tdbLocationDisplay ) {
                        // delete the location display block if it exists
                        tdbLocationDisplay.deleteItem( blockUid );
                    }

                    if ( 'undefined' !== typeof window.tdbMenu ) {
                        tdbMenu.deleteItem( blockUid );
                    }

                    if ( 'undefined' !== typeof window.tdbSearch ) {
                        tdbSearch.deleteItem( blockUid );
                    }

                    if ( 'undefined' !== typeof window.tdwSearch ) {
                        tdwSearch.deleteItem( blockUid );
                    }

					// delete the weather item if available NOTE USED YET
					//tdWeather.deleteItem(blockUid);

					tdcDebug.log('td_block.php js_tdc_get_composer_block  -  callbackDelete(' + blockUid + ') - td_block base callback runned');
				};

				tdcComposerBlocksApi.addItem(tdComposerBlockItem);
			})();
		</script>
		<?php
		return td_util::remove_script_tag(ob_get_clean());
	}






	// get atts
	protected function get_block_html_atts() {
		return ' data-td-block-uid="' . $this->block_uid . '" ';
	}


	/**
     * Add only 'td_block_wrap' or 'td_block_wrap-composer' to the block. 'td_block_wrap-composer' is used to bypass blocks when
     * composer renders pages in menu
     *
	 * @return string
	 */
	protected function get_wrapper_class() {
		$block_class = 'td_block_wrap';

        if ( td_global::get_in_element() && ( tdc_state::is_live_editor_ajax() || tdc_state::is_live_editor_iframe() ) ) {
		    $block_class .= '-composer';
        }

        return $block_class;
	}


    /**
     * @param $additional_classes_array array - of classes to add to the block
     * @return string
     */
    protected function get_block_classes($additional_classes_array = array() ) {


	    $class = $this->get_att('class');
	    $el_class = $this->get_att('el_class');
	    $color_preset = $this->get_att('color_preset');
		$ajax_pagination = $this->get_att('ajax_pagination');
		$ajax_pagination_next_prev_swipe= $this->get_att('ajax_pagination_next_prev_swipe');
	    $border_top = $this->get_att('border_top');
	    $css = $this->get_att('css');
	    $tdc_css = $this->get_att('tdc_css');
	    $block_template_id = $this->get_att('block_template_id');
	    $td_ajax_preloading = $this->get_att('td_ajax_preloading');

	    $block_class = 'td_block_wrap';

        if ( td_global::get_in_element() && ( tdc_state::is_live_editor_ajax() || tdc_state::is_live_editor_iframe() ) ) {
		    $block_class .= '-composer';
        }

        //add the block wrap and block id class
        $block_classes = array(
            $block_class,
	        get_class($this)
        );


	    // get the design tab css classes
	    $css_classes_array = $this->parse_css_att($css);
	    if ( $css_classes_array !== false ) {
		    $block_classes = array_merge (
			    $block_classes,
			    $css_classes_array
		    );
	    }

	    $css_classes_array = $this->parse_css_att($tdc_css);
	    if ( $css_classes_array !== false ) {
		    $block_classes = array_merge (
			    $block_classes,
			    $css_classes_array
		    );
	    }




	    //add the classes that we receive via shortcode. @17 aug 2016 - this att may be used internally - by ra
        if (!empty($class)) {
            $class_array = explode(' ', $class);
            $block_classes = array_merge (
                $block_classes,
                $class_array
            );
        }

        //marge the additional classes received from blocks code
        if (!empty($additional_classes_array)) {
            $block_classes = array_merge (
                $block_classes,
                $additional_classes_array
            );
        }


        //add the full cell class + the color preset class
        if (!empty($color_preset)) {
            $block_classes[]= 'td-pb-full-cell';
            $block_classes[]= $color_preset;
        }


	    /**
	     * - used to add td_block_loading css class on the blocks having pagination
	     * - the class has a force css transform for lazy devices
	     */
	    if (!empty($ajax_pagination)) {
		    $block_classes[] = 'td_with_ajax_pagination';

            if( $ajax_pagination == 'next_prev' && $ajax_pagination_next_prev_swipe != '' ) {
                $block_classes[] = 'td_with_ajax_pagination_next_prev_swipe';
                if( TD_THEME_NAME == "Newspaper" ) {
                    td_resources_load::render_script( TDC_SCRIPTS_URL . '/hammer.min.js' . TDC_SCRIPTS_VER, 'hammer-js', '', 'footer');
                    td_resources_load::render_script( TDC_SCRIPTS_URL . '/jquery.hammer.min.js' . TDC_SCRIPTS_VER, 'jquery_hammer-js', '', 'footer');
                }
            }
	    }


        /**
         * add the border top class - this one comes from the atts
         */
        if (empty($border_top)) {
            $block_classes[] = 'td-pb-border-top';
        }

	    // this is the field that all the shortcodes have (or at least should have)
	    if (!empty($el_class)) {
		    $el_class_array = explode(' ', $el_class);
		    $block_classes = array_merge (
			    $block_classes,
			    $el_class_array
		    );
	    }


	    /**
	     * add block template id - comes from atts
	     */
	    if (empty($block_template_id)) {
		    $block_template_id = td_options::get('tds_global_block_template', 'td_block_template_1');
	    }
	    $block_classes[] = $block_template_id;


	    /**
	     * Add 'tdc-no-posts' class that show info msg for blocks without any modules. Its style is in tagDiv composer
	     */
	    $found_posts = $this->is_products_block() ? (int)$this->td_query['total'] : ( $this->is_loop_block() ? (int)$this->td_query->found_posts : 0 );
	    if ( ( $this->is_loop_block() || $this->is_products_block() ) && $found_posts === 0 ) {
		    $block_classes[] = 'tdc-no-posts';
	    }

        /**
         * - used to add td_block_loading css class on the blocks having pagination
         * - the class has a force css transform for lazy devices
         */
        if ( !empty( $td_ajax_preloading ) ) {
            $block_classes[]= 'td_ajax_preloading_' . $td_ajax_preloading;
        }


        //remove duplicates
        $block_classes = array_unique($block_classes);

	    return implode(' ', $block_classes);
    }


    /**
     * adds a class to the current block's ats
     * @param $raw_class_name string the class name is not sanitized, so make sure you send a sanitized one
     */
    private function add_class($raw_class_name) {
        if (!empty($this->atts['class'])) {
            $this->atts['class'] = $this->atts['class'] . ' ' . $raw_class_name;
        } else {
            $this->atts['class'] = $raw_class_name;
        }
    }


    /**
     * gets the current template instance, if no instance it's found throws error
     * @return mixed the template instance
     */
    protected function block_template() {
        if (isset($this->td_block_template_instance)) {
            return $this->td_block_template_instance;
        } else {
	        td_util::error(__FILE__, "td_block: " . get_class($this) . " did not call render, no td_block_template_instance in td_block");
	        die;
        }
    }


    /**
     * set the initial atts when using blocks via ajax.
     * On ajax the blocks do not call render, just inner directly
     * @param $atts
     */
    public function set_all_atts($atts) {
        $this->atts = $atts;
    }

    /**
     * - Get an attribute from the base class IF render was called
     * - OR - very importantly get's an attribute from the ajax request if the block has front end ajax stuff. Used by modules to maintain settings
     * between ajax requests
     * @updated in 25.1.2018 to use all the mapped attributes + the received atts from render()
     * @param $att_name
     * @param $default_value
     * @return mixed
     */
	protected function get_att($att_name, $default_value = '') {

        // the td_block->render() was not called
		if ( empty( $this->atts ) ) {
			td_util::error(__FILE__, get_class($this) . '->get_att(' . $att_name . ') Internal error: The atts are not set yet(AKA: the render method of the block was not called yet and the system tried to read an att)');
			die;
		}

		// the att does not exist
		if ( !isset( $this->atts[$att_name] ) ) {
            td_util::error(
                __FILE__,
                'Internal error: The system tried to use an att that does not exists! class_name: ' . get_class($this) . '  Att name: "' . $att_name . '" The list with available shorcode_att is computed at run time by each shortcode',
	            $this->atts
            );

            //die;
            return $default_value;
        }

		//we need to decode the square bracket case
        $attr_value = $this->atts[$att_name];
        if ( is_string( $attr_value ) && strpos($attr_value, 'td_encval') === 0 ) {
            $attr_value = str_replace('td_encval', '', $attr_value);
            $attr_value = base64_decode( $attr_value );
        }

		return $attr_value;
	}


    /**
     * - Set the value of an attribute from the base class
     * @param $att_name
     * @param $value
     * @return void
     */
    protected function set_att( $att_name, $value = '' ) {

        $this->atts[$att_name] = $value;

    }


	/**
     * Reads a shortcode_att. AS of 25.1.2018 some shortcodes have a shortcode_atts property where they read the atts from the map
	 * @param $att_name
	 * @param $default_value
	 *
	 * @return mixed
	 */
	protected function get_shortcode_att( $att_name, $default_value = '') {
		if ( empty($this->shortcode_atts ) ) {
			td_util::error(__FILE__, get_class($this) . '->get_shortcode_att(' . $att_name . ') Internal error: The atts are not set yet(AKA: the render method was not called yet and the system tried to read a shorcode_att)');
			die;
		}

		if ( ! isset($this->shortcode_atts[ $att_name ] ) ) {
			//var_dump( $this->shortcode_atts );
			td_util::error(__FILE__, 'Internal error: The system tried to use a shorcode_att that does not exists! class_name: ' . get_class($this) . '  Att name: "' . $att_name . '" The list with available shorcode_att is computed at run time by each shortcode');

			//die;
            return $default_value;
		}

        //we need to decode the square bracket case
        $attr_value = $this->shortcode_atts[ $att_name ];
        if ( strpos($attr_value, 'td_encval') === 0 ) {
            $attr_value = str_replace('td_encval', '', $attr_value);
            $attr_value = base64_decode( $attr_value );
        }

        return $attr_value;
	}


    protected function get_icon_att( $att_name ) {
        $icon_class = $this->get_att($att_name);
        $svg_list = td_global::$svg_theme_font_list;

        if( array_key_exists( $icon_class, $svg_list ) ) {
            return $svg_list[$icon_class];
        }

        return $icon_class;
    }


	/**
	 * parses a design panel generated css string and get's the classes and the
	 *   - It's not private because it's used by @see td_block_ad_box because that block uses special classes to avoid adblock
	 *   - it should be the same with @see tdc_composer_block::parse_css_att from the tdc plugin
	 * @param $user_css_att
	 *
	 * @return array|bool - array of results or false if no classes are available
	 */
	protected function parse_css_att($user_css_att) {
		if (empty($user_css_att)) {
			return false;
		}

		$matches = array();
		$preg_match_ret = preg_match_all ( '/\s*\.\s*([^\{]+)\s*\{\s*([^\}]+)\s*\}\s*/', $user_css_att, $matches);


		if ( $preg_match_ret === 0 || $preg_match_ret === false || empty($matches[1]) || empty($matches[2]) ) {
			return false;
		}

		// get only the selectors
		return $matches[1];
	}


	/**
 	 * Disable loop block features. If this is disable, the block does not use a loop and it doesn't need to run a query.
 	 *  - no query
	 *  - no pulldown items lis (ajax filters)
	 *  - no ajax JS ex: NO new tdBlock()
	 */
	protected function disable_loop_block_features() {
		$this->is_loop_block = false;
	}

	private function is_loop_block() {
		return $this->is_loop_block;
	}

	/**
	 *  - set products query
	 */
	protected function set_products_block() {
		$this->is_products_block = true;
	}

	private function is_products_block() {
		return $this->is_products_block;
	}


    /**
     * used to send the atts to a new module instance that is used on shortcode
     * @return array
     */
	protected function get_all_atts() {
	    return $this->atts;
    }

    /**
     * @deprecated
     * @param $atts_map
     * @return array
     */
	protected function map_atts_to_module($atts_map) {



	    $module_atts = array();

        foreach ($atts_map as $shortcode_att => $module_att) {
            $module_atts[$module_att] = $this->get_att($shortcode_att);
        }
        return $module_atts;
    }

	/**
	 * Generate css compiled code
	 *
	 * @param $css_compiler
	 * @param $shortcode_att_id
	 * @param $color_id
	 * @param $gradient_id
	 * @param $instance - shortcode or style template
	 */
	static function load_color_settings( $instance, $css_compiler, $shortcode_att_id, $color_id = '', $gradient_id = '', $gradient_color = '', $gradient_params = '' ) {
		if ( $instance instanceof td_block  ) {
			$shortcode_att = $instance->get_shortcode_att( $shortcode_att_id );
		} else {
			$shortcode_att = $instance->get_style_att( $shortcode_att_id );
		}

	    if ( ! empty( $shortcode_att ) ) {

	        $decoded_shortcode_att = false;

	        if ( td_util::tdc_is_installed() ) {
	            try {
		            $decoded_shortcode_att = tdc_b64_decode( $shortcode_att, true );
	            } catch( Exception $ex ) {
	                //
                }
            }

		    if ( false !== $decoded_shortcode_att && $decoded_shortcode_att !== $shortcode_att ) {
			    $att = json_decode( $decoded_shortcode_att, true );
			    if ( ! empty ( $gradient_id ) && ! empty ( $att['css'] ) ) {
			        $css_compiler->load_setting_raw( $gradient_id, $att['css'] );
				    if ( ! empty ( $gradient_color ) && ! empty( $att['color1'] ) ) {
					    $css_compiler->load_setting_raw( $gradient_color, $att['color1'] );
				    }
				    if ( ! empty ( $gradient_params ) && ! empty( $att['cssParams'] ) ) {
					    $css_compiler->load_setting_raw( $gradient_params, $att['cssParams'] );
				    }
			    }
		    } else {
			    if ( ! empty ( $color_id ) ) {
				    $css_compiler->load_setting_raw( $color_id, $shortcode_att );
			    }
		    }
	    }
	}




    /**
     * Checks if the block should be restricted on fron-end.
     *
     * @return bool
     */
    protected function is_display_restricted() {

        /* --
        -- Early bail conditions.
        -- */
        /* -- Bail if block has no atts. -- */
        if ( empty( $this->atts ) ) {
            return false;
        }


        /* -- Bail if the 'hide for user type' attribute doesn't exist. -- */
        if ( !isset( $this->atts['hide_for_user_type'] ) ) {
            return false;
        }



        /* --
        -- Check for restrictions.
        -- */
        $hide_for_user_type = $this->get_att('hide_for_user_type');

        if ( $hide_for_user_type != '' ) {
            // Logged in users or guests.
            if ( !( td_util::tdc_is_live_editor_ajax() || td_util::tdc_is_live_editor_iframe() ) &&
                (
                    ( $hide_for_user_type == 'logged-in' && is_user_logged_in() ) ||
                    ( $hide_for_user_type == 'guests' && !is_user_logged_in() )
                )
            ) {
                return true;
            }
        } else {
            // Based on subscription plan.
            $author_plan_ids = $this->get_att('author_plan_id');
            $all_users_plan_ids = $this->get_att('logged_plan_id');

            if ( !td_util::plan_limit($author_plan_ids, $all_users_plan_ids) ) {
                return true;
            }
        }


        
        return false;

    }

}

