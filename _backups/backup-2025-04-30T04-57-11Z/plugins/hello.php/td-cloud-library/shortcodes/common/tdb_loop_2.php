<?php

/**
 * Class tdb_loop - this works on all wp templates pages that have a loop
 */

class tdb_loop_2 extends td_block {

    static function cssMedia( $res_ctx ) {

        $res_ctx->load_settings_raw( 'style_general_loop_2', 1 );
        $res_ctx->load_settings_raw( 'style_general_module_loop', 1 );

        // modules per row
        $modules_on_row = $res_ctx->get_shortcode_att('modules_on_row');
        if ( $modules_on_row == '' ) {
            $modules_on_row = '100%';
        }
        $res_ctx->load_settings_raw( 'modules_on_row', $modules_on_row );

        $ad_loop_full = $res_ctx->get_shortcode_att('ad_loop_full');
        $ad_loop_repeat = $res_ctx->get_shortcode_att('ad_loop_repeat') != '' ? $res_ctx->get_shortcode_att('ad_loop_repeat') : 2;

        // modules clearfix
        $padding = 'padding';
        switch ($modules_on_row) {
            case '100%':
                $res_ctx->load_settings_raw( 'ad_loop_width', '100%' );
                $res_ctx->load_settings_raw( $padding,  '1' );
                break;
            case '50%':
                $res_ctx->load_settings_raw( 'ad_loop_width', $res_ctx->calc_full_ad_spot_width( 2, 50, $ad_loop_repeat, $ad_loop_full ) . '%' );
                $res_ctx->load_settings_raw( $padding,  '-n+2' );
                break;
            case '33.33333333%':
                $res_ctx->load_settings_raw( 'ad_loop_width', $res_ctx->calc_full_ad_spot_width( 3, 33.33333333, $ad_loop_repeat, $ad_loop_full ) . '%' );
                $res_ctx->load_settings_raw( $padding,  '-n+3' );
                break;
            case '25%':
                $res_ctx->load_settings_raw( 'ad_loop_width', $res_ctx->calc_full_ad_spot_width( 4, 25, $ad_loop_repeat, $ad_loop_full ) . '%' );
                $res_ctx->load_settings_raw( $padding,  '-n+4' );
                break;
            case '20%':
                $res_ctx->load_settings_raw( 'ad_loop_width', $res_ctx->calc_full_ad_spot_width( 5, 20, $ad_loop_repeat, $ad_loop_full ) . '%' );
                $res_ctx->load_settings_raw( $padding,  '-n+5' );
                break;
            case '16.66666667%':
                $res_ctx->load_settings_raw( 'ad_loop_width', $res_ctx->calc_full_ad_spot_width( 6, 16.66666667, $ad_loop_repeat, $ad_loop_full ) . '%' );
                $res_ctx->load_settings_raw( $padding,  '-n+6' );
                break;
            case '14.28571428%':
                $res_ctx->load_settings_raw( 'ad_loop_width', $res_ctx->calc_full_ad_spot_width( 7, 14.28571428, $ad_loop_repeat, $ad_loop_full ) . '%' );
                $res_ctx->load_settings_raw( $padding,  '-n+7' );
                break;
            case '12.5%':
                $res_ctx->load_settings_raw( 'ad_loop_width', $res_ctx->calc_full_ad_spot_width( 8, 12.5, $ad_loop_repeat, $ad_loop_full ) . '%' );
                $res_ctx->load_settings_raw( $padding,  '-n+8' );
                break;
            case '11.11111111%':
                $res_ctx->load_settings_raw( 'ad_loop_width', $res_ctx->calc_full_ad_spot_width( 9, 11.11111111, $ad_loop_repeat, $ad_loop_full ) . '%' );
                $res_ctx->load_settings_raw( $padding,  '-n+9' );
                break;
            case '10%':
                $res_ctx->load_settings_raw( 'ad_loop_width', $res_ctx->calc_full_ad_spot_width( 10, 10, $ad_loop_repeat, $ad_loop_full ) . '%' );
                $res_ctx->load_settings_raw( $padding,  '-n+10' );
                break;
        }

        // modules gap
        $modules_gap = $res_ctx->get_shortcode_att('modules_gap');
        $res_ctx->load_settings_raw( 'modules_gap', $modules_gap );
        if ( $modules_gap == '' ) {
            $res_ctx->load_settings_raw( 'modules_gap', '20px');
        } else if ( is_numeric( $modules_gap ) ) {
            $res_ctx->load_settings_raw( 'modules_gap', $modules_gap / 2 .'px' );
        }

        // modules padding
        $m_padding = $res_ctx->get_shortcode_att('m_padding');
        $res_ctx->load_settings_raw( 'm_padding', $m_padding );
        if ( is_numeric( $m_padding ) ) {
            $res_ctx->load_settings_raw( 'm_padding', $m_padding . 'px' );
        }

        // modules space
        $modules_space = $res_ctx->get_shortcode_att('all_modules_space');
        $res_ctx->load_settings_raw( 'all_modules_space', $modules_space );
        if ( $modules_space == '' ) {
            $res_ctx->load_settings_raw( 'all_modules_space', '18px');
        } else if ( is_numeric( $modules_space ) ) {
            $res_ctx->load_settings_raw( 'all_modules_space', $modules_space / 2 .'px' );
        }

        // modules border size
        $modules_border_size = $res_ctx->get_shortcode_att('modules_border_size');
        $res_ctx->load_settings_raw( 'modules_border_size', $modules_border_size );
        if( $modules_border_size != '' && is_numeric( $modules_border_size ) ) {
            $res_ctx->load_settings_raw( 'modules_border_size', $modules_border_size . 'px' );
        }
        // modules border style
        $res_ctx->load_settings_raw( 'modules_border_style', $res_ctx->get_shortcode_att('modules_border_style') );
        // modules border color
        $res_ctx->load_settings_raw( 'modules_border_color', $res_ctx->get_shortcode_att('modules_border_color') );

        // modules divider
        $res_ctx->load_settings_raw( 'modules_divider', $res_ctx->get_shortcode_att('modules_divider') );
        // modules divider color
        $res_ctx->load_settings_raw( 'modules_divider_color', $res_ctx->get_shortcode_att('modules_divider_color') );



        /*-- ARTICLE IMAGE-- */
        //image alignment
        $res_ctx->load_settings_raw( 'image_alignment', $res_ctx->get_shortcode_att('image_alignment') . '%' );

        // image_height
        $image_height = $res_ctx->get_shortcode_att('image_height');
        if ( is_numeric( $image_height ) ) {
            $res_ctx->load_settings_raw( 'image_height', $image_height . '%' );
        } else {
            $res_ctx->load_settings_raw( 'image_height', $image_height );
        }
        // image radius
        $image_radius = $res_ctx->get_shortcode_att('image_radius');
        $res_ctx->load_settings_raw( 'image_radius', $image_radius );
        if ( is_numeric( $image_radius ) ) {
            $res_ctx->load_settings_raw( 'image_radius', $image_radius . 'px' );
        }
        // image margin
        $image_margin = $res_ctx->get_shortcode_att('image_margin');
        $res_ctx->load_settings_raw( 'image_margin', $image_margin );
        if ( is_numeric( $image_margin ) ) {
            $res_ctx->load_settings_raw( 'image_margin', $image_margin . 'px' );
        }

        // favorite button size
        $fav_size = 36;
        switch ( $res_ctx->get_shortcode_att('fav_size') ) {
            case '1':
                $fav_size = 28;
                break;
            case '2':
                $fav_size = 36;
                break;
            case '3':
                $fav_size = 40;
                break;
            case '4':
                $fav_size = 46;
                break;
        }
        $res_ctx->load_settings_raw( 'fav_size', $fav_size . 'px' );

        // favorite button space
        $fav_space = $res_ctx->get_shortcode_att('fav_space');
        $res_ctx->load_settings_raw( 'fav_space', $fav_space );
        if( $fav_space != '' && is_numeric( $fav_space ) ) {
            $res_ctx->load_settings_raw( 'fav_space', $fav_space . 'px' );
        }

        // video icon size
        $video_icon = $res_ctx->get_shortcode_att('video_icon');
        if ( $video_icon != '' && is_numeric( $video_icon ) ) {
            $res_ctx->load_settings_raw( 'video_icon', $video_icon . 'px' );
        }

        // show video duration
        $res_ctx->load_settings_raw('show_vid_t', $res_ctx->get_shortcode_att('show_vid_t'));
        // video duration margin
        $vid_t_margin = $res_ctx->get_shortcode_att('vid_t_margin');
        $res_ctx->load_settings_raw( 'vid_t_margin', $vid_t_margin );
        if( $vid_t_margin != '' && is_numeric( $vid_t_margin ) ) {
            $res_ctx->load_settings_raw( 'vid_t_margin', $vid_t_margin . 'px' );
        }
        // video duration padding
        $vid_t_padding = $res_ctx->get_shortcode_att('vid_t_padding');
        $res_ctx->load_settings_raw( 'vid_t_padding', $vid_t_padding );
        if( $vid_t_padding != '' && is_numeric( $vid_t_padding ) ) {
            $res_ctx->load_settings_raw( 'vid_t_padding', $vid_t_padding . 'px' );
        }



        /*-- META INFO -- */
        // meta info horizontal align
        $content_align = $res_ctx->get_shortcode_att('meta_info_horiz');
        if ( $content_align == 'content-horiz-center' ) {
            $res_ctx->load_settings_raw( 'meta_horiz_align_center', 1 );
        } else if ( $content_align == 'content-horiz-right' ) {
            $res_ctx->load_settings_raw( 'meta_horiz_align_right', 1 );
        }
        // meta info width
        $meta_info_width = $res_ctx->get_shortcode_att('meta_width');
        $res_ctx->load_settings_raw( 'meta_width', $meta_info_width );
        if( $meta_info_width != '' && is_numeric( $meta_info_width ) ) {
            $res_ctx->load_settings_raw( 'meta_width', $meta_info_width . 'px' );
        }
        // meta info padding
        $meta_padding = $res_ctx->get_shortcode_att('meta_padding');
        $res_ctx->load_settings_raw( 'meta_padding', $meta_padding );
        if ( is_numeric( $meta_padding ) ) {
            $res_ctx->load_settings_raw( 'meta_padding', $meta_padding . 'px' );
        }
        $meta_padding2 = $res_ctx->get_shortcode_att('meta_padding2');
        $res_ctx->load_settings_raw( 'meta_padding2', $meta_padding2 );
        if ( is_numeric( $meta_padding2 ) ) {
            $res_ctx->load_settings_raw( 'meta_padding2', $meta_padding2 . 'px' );
        }

        // meta_info_border_size
        $meta_info_border_size = $res_ctx->get_shortcode_att('meta_info_border_size');
        $res_ctx->load_settings_raw( 'meta_info_border_size', $meta_info_border_size );
        if ( is_numeric( $meta_info_border_size ) ) {
            $res_ctx->load_settings_raw( 'meta_info_border_size', $meta_info_border_size . 'px' );
        }
        $meta_info_border_size2 = $res_ctx->get_shortcode_att('meta_info_border_size2');
        $res_ctx->load_settings_raw( 'meta_info_border_size2', $meta_info_border_size2 );
        if ( is_numeric( $meta_info_border_size2 ) ) {
            $res_ctx->load_settings_raw( 'meta_info_border_size2', $meta_info_border_size2 . 'px' );
        }
        // meta info border style
        $res_ctx->load_settings_raw( 'meta_info_border_style', $res_ctx->get_shortcode_att('meta_info_border_style') );
        // meta info border color
        $res_ctx->load_settings_raw( 'meta_info_border_color', $res_ctx->get_shortcode_att('meta_info_border_color') );
        // meta info border radius
        $meta_info_border_radius = $res_ctx->get_shortcode_att('meta_info_border_radius');
        $res_ctx->load_settings_raw( 'meta_info_border_radius', $meta_info_border_radius );
        if ( is_numeric( $meta_info_border_radius ) ) {
            $res_ctx->load_settings_raw( 'meta_info_border_radius', $meta_info_border_radius . 'px' );
        }
        $meta_info_border_radius2 = $res_ctx->get_shortcode_att('meta_info_border_radius2');
        $res_ctx->load_settings_raw( 'meta_info_border_radius2', $meta_info_border_radius2 );
        if ( is_numeric( $meta_info_border_radius2 ) ) {
            $res_ctx->load_settings_raw( 'meta_info_border_radius2', $meta_info_border_radius2 . 'px' );
        }


        // article title space
        $art_title = $res_ctx->get_shortcode_att('art_title');
        $res_ctx->load_settings_raw( 'art_title', $art_title );
        if ( is_numeric( $art_title ) ) {
            $res_ctx->load_settings_raw( 'art_title', $art_title . 'px' );
        }


        // show excerpt
        $res_ctx->load_settings_raw( 'show_excerpt', $res_ctx->get_shortcode_att('show_excerpt') );
        // article excerpt space
        $art_excerpt = $res_ctx->get_shortcode_att('art_excerpt');
        $res_ctx->load_settings_raw( 'art_excerpt', $art_excerpt );
        if ( is_numeric( $art_excerpt ) ) {
            $res_ctx->load_settings_raw( 'art_excerpt', $art_excerpt . 'px' );
        }


        // show audio player
        $show_audio = $res_ctx->get_shortcode_att('show_audio');
        if( $show_audio == '' || $show_audio == 'block' ) {
            $res_ctx->load_settings_raw( 'show_audio', 1 );
        } else if( $show_audio == 'none' ) {
            $res_ctx->load_settings_raw( 'hide_audio', 1 );
        }
        // article audio player space
        $art_audio = $res_ctx->get_shortcode_att('art_audio');
        $res_ctx->load_settings_raw( 'art_audio', $art_audio );
        if ( is_numeric( $art_audio ) ) {
            $res_ctx->load_settings_raw( 'art_audio', $art_audio . 'px' );
        }
        // article audio size
        $art_audio_size = $res_ctx->get_shortcode_att('art_audio_size');
        if ( is_numeric( $art_audio_size ) ) {
            $res_ctx->load_settings_raw('art_audio_size', 10 + $art_audio_size / 0.5 . 'px');
        }

        // show category tag
        $res_ctx->load_settings_raw( 'show_cat', $res_ctx->get_shortcode_att('show_cat') );
        // category tag space
        $modules_category_margin = $res_ctx->get_shortcode_att('modules_category_margin');
        $res_ctx->load_settings_raw( 'modules_category_margin', $modules_category_margin );
        if( $modules_category_margin != '' && is_numeric( $modules_category_margin ) ) {
            $res_ctx->load_settings_raw( 'modules_category_margin', $modules_category_margin . 'px' );
        }
        // category tag padding
        $modules_category_padding = $res_ctx->get_shortcode_att('modules_category_padding');
        $res_ctx->load_settings_raw( 'modules_category_padding', $modules_category_padding );
        if( $modules_category_padding != '' && is_numeric( $modules_category_padding ) ) {
            $res_ctx->load_settings_raw( 'modules_category_padding', $modules_category_padding . 'px' );
        }
        // category tag border
        $modules_category_border = $res_ctx->get_shortcode_att('modules_category_border');
        $res_ctx->load_settings_raw( 'modules_category_border', $modules_category_border );
        if( $modules_category_border != '' && is_numeric( $modules_category_border ) ) {
            $res_ctx->load_settings_raw( 'modules_category_border', $modules_category_border . 'px' );
        }
        //category tag radius
        $modules_category_radius = $res_ctx->get_shortcode_att('modules_category_radius');
        if ( $modules_category_radius != 0 || !empty($modules_category_radius) ) {
            $res_ctx->load_settings_raw( 'modules_category_radius', $modules_category_radius . 'px' );
        }


        // author photo size
        $author_photo_size = $res_ctx->get_shortcode_att('author_photo_size');
        $res_ctx->load_settings_raw( 'author_photo_size', '20px' );
        if( $author_photo_size != '' && is_numeric( $author_photo_size ) ) {
            $res_ctx->load_settings_raw( 'author_photo_size', $author_photo_size . 'px' );
        }
        // author photo space
        $author_photo_space = $res_ctx->get_shortcode_att('author_photo_space');
        $res_ctx->load_settings_raw( 'author_photo_space', '6px' );
        if( $author_photo_space != '' && is_numeric( $author_photo_space ) ) {
            $res_ctx->load_settings_raw( 'author_photo_space', $author_photo_space . 'px' );
        }
        // author photo radius
        $author_photo_radius = $res_ctx->get_shortcode_att('author_photo_radius');
        $res_ctx->load_settings_raw( 'author_photo_radius', $author_photo_radius );
        if( $author_photo_radius != '' ) {
            if( is_numeric( $author_photo_radius ) ) {
                $res_ctx->load_settings_raw( 'author_photo_radius', $author_photo_radius . 'px' );
            }
        } else {
            $res_ctx->load_settings_raw( 'author_photo_radius', '50%' );
        }




        // info space
        $info_space = $res_ctx->get_shortcode_att('info_space');
        $res_ctx->load_settings_raw( 'info_space', $info_space );
        if ( is_numeric( $info_space ) ) {
            $res_ctx->load_settings_raw( 'info_space', $info_space . 'px' );
        }


        // show meta info details
        $show_author = $res_ctx->get_shortcode_att('show_author');
        $show_date = $res_ctx->get_shortcode_att('show_date');
        $show_review = $res_ctx->get_shortcode_att('show_review');
        $review_space = $res_ctx->get_shortcode_att('review_space');
        $res_ctx->load_settings_raw( 'review_space', $review_space );
        if( $review_space != '' && is_numeric( $review_space ) ) {
            $res_ctx->load_settings_raw( 'review_space', $review_space . 'px' );
        }
        $review_size = $res_ctx->get_shortcode_att('review_size');
        if ( is_numeric( $review_size ) ) {
            $res_ctx->load_settings_raw('review_size', 10 + $review_size / 0.5 . 'px');
        }
        $review_distance = $res_ctx->get_shortcode_att('review_distance');
        $res_ctx->load_settings_raw( 'review_distance', $review_distance );
        if( $review_distance != '' && is_numeric( $review_distance ) ) {
            $res_ctx->load_settings_raw( 'review_distance', $review_distance . 'px' );
        }
        $show_com = $res_ctx->get_shortcode_att('show_com');
        if( $show_author == 'none' && $show_date == 'none' && $show_com == 'none' && $show_review == 'none' ) {
            $res_ctx->load_settings_raw( 'hide_author_date', 1 );
        } else {
            $res_ctx->load_settings_raw( 'show_author_date', 1 );
        }
        $res_ctx->load_settings_raw( 'show_author', $show_author );
        $res_ctx->load_settings_raw( 'show_date', $show_date );
        $res_ctx->load_settings_raw( 'show_review', $show_review );
        $res_ctx->load_settings_raw( 'show_com', $show_com );


        // show button
        $res_ctx->load_settings_raw( 'show_btn', $res_ctx->get_shortcode_att('show_btn') );
        // button margin
        $btn_margin = $res_ctx->get_shortcode_att('btn_margin');
        $res_ctx->load_settings_raw( 'btn_margin', $btn_margin );
        if( $btn_margin != '' && is_numeric( $btn_margin ) ) {
            $res_ctx->load_settings_raw( 'btn_margin', $btn_margin . 'px' );
        }
        // button padding
        $btn_padding = $res_ctx->get_shortcode_att('btn_padding');
        $res_ctx->load_settings_raw( 'btn_padding', $btn_padding );
        if( $btn_padding != '' && is_numeric( $btn_padding ) ) {
            $res_ctx->load_settings_raw( 'btn_padding', $btn_padding . 'px' );
        }
        // button border
        $btn_border_width = $res_ctx->get_shortcode_att('btn_border_width');
        $res_ctx->load_settings_raw( 'btn_border_width', $btn_border_width );
        if( $btn_border_width != '' && is_numeric( $btn_border_width ) ) {
            $res_ctx->load_settings_raw( 'btn_border_width', $btn_border_width . 'px' );
        }
        // button radius
        $btn_radius = $res_ctx->get_shortcode_att('btn_radius');
        $res_ctx->load_settings_raw( 'btn_radius', $btn_radius );
        if( $btn_radius != '' && is_numeric( $btn_radius ) ) {
            $res_ctx->load_settings_raw( 'btn_radius', $btn_radius . 'px' );
        }

        // pagination space
        $pag_space = $res_ctx->get_shortcode_att('pag_space');
        $res_ctx->load_settings_raw( 'pag_space', $pag_space );
        if( $pag_space != '' && is_numeric( $pag_space ) ) {
            $res_ctx->load_settings_raw( 'pag_space', $pag_space . 'px' );
        }
        // pagination padding
        $pag_padding = $res_ctx->get_shortcode_att('pag_padding');
        $res_ctx->load_settings_raw( 'pag_padding', $pag_padding );
        if( $pag_padding != '' && is_numeric( $pag_padding ) ) {
            $res_ctx->load_settings_raw( 'pag_padding', $pag_padding . 'px' );
        }
        // pagination border width
        $pag_border_width = $res_ctx->get_shortcode_att('pag_border_width');
        $res_ctx->load_settings_raw( 'pag_border_width', $pag_border_width );
        if( $pag_border_width != '' && is_numeric( $pag_border_width ) ) {
            $res_ctx->load_settings_raw( 'pag_border_width', $pag_border_width . 'px' );
        }
        // pagination border radius
        $pag_border_radius = $res_ctx->get_shortcode_att('pag_border_radius');
        $res_ctx->load_settings_raw( 'pag_border_radius', $pag_border_radius );
        if( $pag_border_radius != '' && is_numeric( $pag_border_radius ) ) {
            $res_ctx->load_settings_raw( 'pag_border_radius', $pag_border_radius . 'px' );
        }
        // next/prev icons size
        $pag_icons_size = $res_ctx->get_shortcode_att('pag_icons_size');
        $res_ctx->load_settings_raw( 'pag_icons_size', $pag_icons_size );
        if( $pag_icons_size != '' && is_numeric( $pag_icons_size ) ) {
            $res_ctx->load_settings_raw( 'pag_icons_size', $pag_icons_size . 'px' );
        }

        // underline height
        $underline_height = $res_ctx->get_shortcode_att('all_underline_height');
        $res_ctx->load_settings_raw( 'all_underline_height', $underline_height );
        if( $underline_height != '' && is_numeric( $underline_height ) ) {
            $res_ctx->load_settings_raw( 'all_underline_height', $underline_height . 'px' );
        } else {
            $res_ctx->load_settings_raw( 'all_underline_height', '0' );
        }
        // underline color
        $underline_color = $res_ctx->get_shortcode_att('all_underline_color');
        if ( $underline_height != 0 ) {
            if( $underline_color == '' ) {
                $res_ctx->load_settings_raw('all_underline_color', '#000');
            } else {
                $res_ctx->load_settings_raw('all_underline_color', $res_ctx->get_shortcode_att('all_underline_color'));
            }
        }


        /*-- COLORS -- */
        $res_ctx->load_settings_raw( 'm_bg', $res_ctx->get_shortcode_att('m_bg') );
        $res_ctx->load_shadow_settings( 0, 0, 0, 0, 'rgba(0, 0, 0, 0.08)', 'shadow' );
        $res_ctx->load_settings_raw( 'meta_bg', $res_ctx->get_shortcode_att('meta_bg') );
        $res_ctx->load_settings_raw( 'meta_bg2', $res_ctx->get_shortcode_att('meta_bg2') );

        $res_ctx->load_settings_raw( 'title_txt', $res_ctx->get_shortcode_att('title_txt') );
        $res_ctx->load_settings_raw( 'title_txt_hover', $res_ctx->get_shortcode_att('title_txt_hover') );

        $res_ctx->load_settings_raw( 'cat_bg', $res_ctx->get_shortcode_att('cat_bg') );
        $res_ctx->load_settings_raw( 'cat_txt', $res_ctx->get_shortcode_att('cat_txt') );
        $res_ctx->load_settings_raw( 'cat_border', $res_ctx->get_shortcode_att('cat_border') );
        $res_ctx->load_settings_raw( 'cat_bg_hover', $res_ctx->get_shortcode_att('cat_bg_hover') );
        $res_ctx->load_settings_raw( 'cat_txt_hover', $res_ctx->get_shortcode_att('cat_txt_hover') );
        $res_ctx->load_settings_raw( 'cat_border_hover', $res_ctx->get_shortcode_att('cat_border_hover') );

        $res_ctx->load_settings_raw( 'author_txt', $res_ctx->get_shortcode_att('author_txt') );
        $res_ctx->load_settings_raw( 'author_txt_hover', $res_ctx->get_shortcode_att('author_txt_hover') );

        $res_ctx->load_settings_raw( 'date_txt', $res_ctx->get_shortcode_att('date_txt') );

        $res_ctx->load_settings_raw( 'ex_txt', $res_ctx->get_shortcode_att('ex_txt') );

        $res_ctx->load_settings_raw( 'com_bg', $res_ctx->get_shortcode_att('com_bg') );
        $res_ctx->load_settings_raw( 'com_txt', $res_ctx->get_shortcode_att('com_txt') );
        $res_ctx->load_settings_raw( 'rev_txt', $res_ctx->get_shortcode_att('rev_txt') );

        $res_ctx->load_settings_raw( 'audio_btn_color', $res_ctx->get_shortcode_att( 'audio_btn_color' ) );
        $res_ctx->load_settings_raw( 'audio_time_color', $res_ctx->get_shortcode_att( 'audio_time_color' ) );
        $res_ctx->load_settings_raw( 'audio_bar_color', $res_ctx->get_shortcode_att( 'audio_bar_color' ) );
        $res_ctx->load_settings_raw( 'audio_bar_curr_color', $res_ctx->get_shortcode_att( 'audio_bar_curr_color' ) );

        $res_ctx->load_settings_raw( 'btn_bg', $res_ctx->get_shortcode_att('btn_bg') );
        $res_ctx->load_settings_raw( 'btn_bg_hover', $res_ctx->get_shortcode_att('btn_bg_hover') );
        $res_ctx->load_settings_raw( 'btn_txt', $res_ctx->get_shortcode_att('btn_txt') );
        $res_ctx->load_settings_raw( 'btn_txt_hover', $res_ctx->get_shortcode_att('btn_txt_hover') );
        $res_ctx->load_settings_raw( 'btn_border', $res_ctx->get_shortcode_att('btn_border') );
        $res_ctx->load_settings_raw( 'btn_border_hover', $res_ctx->get_shortcode_att('btn_border_hover') );

        $res_ctx->load_settings_raw( 'pag_text', $res_ctx->get_shortcode_att('pag_text') );
        $res_ctx->load_settings_raw( 'pag_bg', $res_ctx->get_shortcode_att('pag_bg') );
        $res_ctx->load_settings_raw( 'pag_border', $res_ctx->get_shortcode_att('pag_border') );
        $res_ctx->load_settings_raw( 'pag_a_text', $res_ctx->get_shortcode_att('pag_a_text') );
        $res_ctx->load_settings_raw( 'pag_a_bg', $res_ctx->get_shortcode_att('pag_a_bg') );
        $res_ctx->load_settings_raw( 'pag_a_border', $res_ctx->get_shortcode_att('pag_a_border') );
        $res_ctx->load_settings_raw( 'pag_h_text', $res_ctx->get_shortcode_att('pag_h_text') );
        $res_ctx->load_settings_raw( 'pag_h_bg', $res_ctx->get_shortcode_att('pag_h_bg') );
        $res_ctx->load_settings_raw( 'pag_h_border', $res_ctx->get_shortcode_att('pag_h_border') );

        $res_ctx->load_settings_raw( 'fav_ico_color', $res_ctx->get_shortcode_att('fav_ico_color') );
        $res_ctx->load_settings_raw( 'fav_ico_color_h', $res_ctx->get_shortcode_att('fav_ico_color_h') );
        $res_ctx->load_settings_raw( 'fav_bg', $res_ctx->get_shortcode_att('fav_bg') );
        $res_ctx->load_settings_raw( 'fav_bg_h', $res_ctx->get_shortcode_att('fav_bg_h') );
        $res_ctx->load_shadow_settings( 4, 1, 1, 0, 'rgba(0, 0, 0, 0.2)', 'fav_shadow' );

        $res_ctx->load_shadow_settings( 0, 0, 0, 0, 'rgba(0, 0, 0, 0.08)', 'shadow_m' );

        // video pop-up
        $res_ctx->load_settings_raw( 'video_rec_color', $res_ctx->get_shortcode_att('video_rec_color') );
        $res_ctx->load_settings_raw( 'video_title_color', $res_ctx->get_shortcode_att('video_title_color') );
        $res_ctx->load_settings_raw( 'video_title_color_h', $res_ctx->get_shortcode_att('video_title_color_h') );
        $res_ctx->load_settings_raw( 'video_bg', $res_ctx->get_shortcode_att('video_bg') );
        $res_ctx->load_settings_raw( 'video_overlay', $res_ctx->get_shortcode_att('video_overlay') );

        // video duration
        $res_ctx->load_settings_raw( 'vid_t_color', $res_ctx->get_shortcode_att('vid_t_color') );
        $res_ctx->load_settings_raw( 'vid_t_bg_color', $res_ctx->get_shortcode_att('vid_t_bg_color') );


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

        // loop ads
        $res_ctx->load_settings_raw( 'ad_loop_color', $res_ctx->get_shortcode_att('ad_loop_color') );



        /*-- FONTS -- */
        $res_ctx->load_font_settings( 'f_header' );
        $res_ctx->load_font_settings( 'f_pag' );
        $res_ctx->load_font_settings( 'f_title' );
        $res_ctx->load_font_settings( 'f_cat' );
        $res_ctx->load_font_settings( 'f_meta' );
        $res_ctx->load_font_settings( 'f_ex' );
        $res_ctx->load_font_settings( 'f_btn' );

        $res_ctx->load_font_settings( 'f_vid_title' );
        $res_ctx->load_font_settings( 'f_vid_time' );

        $res_ctx->load_font_settings( 'f_ad' );

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

        $unique_block_modal_class = $this->block_uid . '_m';

        $compiled_css = '';

        $raw_css =
            "<style>
            
				/* @style_general_loop_2 */
				.tdb_loop_2 .tdb-block-inner {
                    display: flex;
                    flex-wrap: wrap;
				}
				.tdb_loop_2 .td_module_wrap {
				    padding-bottom: 0;
				}
				.tdb_loop_2 .tdb_module_rec {
				    text-align: center;
				}
                .tdb_loop_2 .tdb-author-photo {
                  display: inline-block;
                }
                .tdb_loop_2 .tdb-author-photo,
                .tdb_loop_2 .tdb-author-photo img {
                  vertical-align: middle;
                }
                .tdb_loop_2 .td-post-author-name,
                .tdb_loop_2 .td-post-date,
                .tdb_loop_2 .td-module-comments {
                  vertical-align: text-top;
                }
                .tdb_loop_2 .entry-review-stars {
                  margin-left: 6px;
                  vertical-align: text-bottom;
                }
                .tdb_loop_2 .td-load-more-wrap,
                .tdb_loop_2 .td-next-prev-wrap {
                  margin: 20px 0 0;
                }
                .tdb_loop_2 .page-nav {
                  position: relative;
                  margin: 54px 0 0;
                }
                .tdb_loop_2 .page-nav a,
                .tdb_loop_2 .page-nav span {
                  margin-top: 8px;
                  margin-bottom: 0;
                }
                .tdb_loop_2 .td-next-prev-wrap a {
                  width: auto;
                  height: auto;
                  min-width: 25px;
                  min-height: 25px;
                }
                .tdb_loop_2 {
                  display: inline-block;
                  width: 100%;
                  margin-bottom: 78px;
                  padding-bottom: 0;
                  overflow: visible !important;
                }
                .tdb_module_loop_2 {
                  display: inline-block;
                  width: 100%;
                  padding-bottom: 0;
                }
                .tdb_module_loop_2 .td-module-meta-info {
                  min-height: 0;
                }
                .tdb_module_loop_2 .td-author-photo {
                  display: inline-block;
                  vertical-align: middle;
                }
                .tdb_module_loop_2 .td-read-more {
                  margin: 20px 0 0;
                }
                .tdb_loop_2 .td-spot-id-loop .tdc-placeholder-title:before {
                    content: 'Posts Loop Ad' !important;
                }
                
                .tdb_loop_2.tdc-no-posts .td_block_inner {
                    margin-left: 0 !important;
                    margin-right: 0 !important;
                }
                
                .tdb_loop_2.tdc-no-posts .td_block_inner .no-results h2 {
                    font-size: 13px;
                    font-weight: normal;
                    text-align: left;
                    padding: 20px;
                    border: 1px solid rgba(190, 190, 190, 0.35);
                    color: rgba(125, 125, 125, 0.8);
                }
				
				/* @modules_on_row */
				.$unique_block_class .td_module_wrap {
					width: @modules_on_row;
				}
				/* @ad_loop_width */
				.$unique_block_class .tdb_module_rec {
					width: @ad_loop_width;
				}
				/* @padding */
				.$unique_block_class .td_module_wrap:nth-last-child(@padding) .td-module-container:before {
					display: none;
				}
				/* @modules_gap */
				.$unique_block_class .td_module_wrap {
					padding-left: @modules_gap;
					padding-right: @modules_gap;
				}
				.$unique_block_class .td_block_inner {
					margin-left: -@modules_gap;
					margin-right: -@modules_gap;
				}
				/* @m_padding */
				.$unique_block_class .td-module-container {
					padding: @m_padding;
				}
				/* @all_modules_space */
				.$unique_block_class .tdb-block-inner {
					row-gap: calc(@all_modules_space * 2);
				}
				.$unique_block_class .td-module-container:before {
					bottom: -@all_modules_space;
				}
				/* @modules_border_size */
				.$unique_block_class .td-module-container {
				    border-width: @modules_border_size;
				    border-style: solid;
				    border-color: #000;
				}
				/* @modules_border_style */
				.$unique_block_class .td-module-container {
				    border-style: @modules_border_style;
				}
				/* @modules_border_color */
				.$unique_block_class .td-module-container {
				    border-color: @modules_border_color;
				}
				/* @modules_divider */
				.$unique_block_class .td-module-container:before {
					border-width: 0 0 1px 0;
					border-style: @modules_divider;
					border-color: #eaeaea;
				}
				/* @modules_divider_color */
				.$unique_block_class .td-module-container:before {
					border-color: @modules_divider_color;
				}
				
                /* @all_underline_color */
                @media (min-width: 768px) {
                    .$unique_block_class .td-module-title a {
                        transition: all 0.2s ease;
                        -webkit-transition: all 0.2s ease;
                    }
                }
                .$unique_block_class .td-module-title a {
                    box-shadow: inset 0 0 0 0 @all_underline_color;
                }
                /* @all_underline_height */
                .$unique_block_class .td-module-container:hover .td-module-title a {
                    box-shadow: inset 0 -@all_underline_height 0 0 @all_underline_color;
                }

				/* @image_alignment */
				.$unique_block_class .entry-thumb {
					background-position: center @image_alignment;
				}
				
				/* @image_height */
				.$unique_block_class .td-image-wrap {
					padding-bottom: @image_height;
				}
				/* @image_radius */
				.$unique_block_class .entry-thumb,
				.$unique_block_class .td-image-wrap:before,
				.$unique_block_class .entry-thumb:before,
				.$unique_block_class .entry-thumb:after {
					border-radius: @image_radius;
				}
				/* @image_margin */
				.$unique_block_class .td-image-container {
					margin: @image_margin;
				}
				/* @fav_ico_color */
                body .$unique_block_class .td-favorite svg {
                    fill: @fav_ico_color;
                }
                /* @fav_ico_color_h */
                body .$unique_block_class .td-favorite:hover svg {
                    fill: @fav_ico_color_h;
                }
                /* @fav_bg */
                body .$unique_block_class .td-favorite {
                    background-color: @fav_bg;
                }
                /* @fav_bg_h */
                body .$unique_block_class .td-favorite:hover {
                    background-color: @fav_bg_h;
                }
                /* @fav_shadow */
                body .$unique_block_class .td-favorite {
                    box-shadow: @fav_shadow;
                }
				/* @video_icon */
				.$unique_block_class .td-video-play-ico {
					width: @video_icon;
					height: @video_icon;
					font-size: @video_icon;
				}
				/* @show_vid_t */
				.$unique_block_class .td-post-vid-time {
					display: @show_vid_t;
				}
				/* @vid_t_margin */
				.$unique_block_class .td-post-vid-time {
					margin: @vid_t_margin;
				}
				/* @vid_t_padding */
				.$unique_block_class .td-post-vid-time {
					padding: @vid_t_padding;
				}
				
				
				/* @meta_horiz_align_center */
				.$unique_block_class .td-module-meta-info {
					text-align: center;
				}
				.$unique_block_class .td-image-container {
					margin-left: auto;
                    margin-right: auto;
				}
				.$unique_block_class .td-category-pos-image .td-post-category:not(.td-post-extra-category) {
					left: 50%;
					transform: translateX(-50%);
					-webkit-transform: translateX(-50%);
				}
				.$unique_block_class.td-h-effect-up-shadow .td_module_wrap:hover .td-category-pos-image .td-post-category:not(.td-post-extra-category) {
				    transform: translate(-50%, -2px);
					-webkit-transform: translate(-50%, -2px);
				}
				/* @meta_horiz_align_right */
				.$unique_block_class .td-module-meta-info {
					text-align: right;
				}
				/* @meta_width */
				.$unique_block_class .td-module-meta-info {
					max-width: @meta_width;
				}
				/* @meta_padding */
				.$unique_block_class .td-module-meta-info-top {
					padding: @meta_padding;
				}
				/* @meta_padding2 */
				.$unique_block_class .td-module-meta-info-bottom {
					padding: @meta_padding2;
				}
				
				/* @meta_info_border_size */
				.$unique_block_class .td-module-meta-info-top {
					border-width: @meta_info_border_size;
				}
				/* @meta_info_border_size2 */
				.$unique_block_class .td-module-meta-info-bottom {
					border-width: @meta_info_border_size2;
				}
				/* @meta_info_border_style */
				.$unique_block_class .td-module-meta-info {
					border-style: @meta_info_border_style;
				}
				/* @meta_info_border_color */
				.$unique_block_class .td-module-meta-info {
					border-color: @meta_info_border_color;
				}
				/* @meta_info_border_radius */
				.$unique_block_class .td-module-meta-info-top {
					border-radius: @meta_info_border_radius;
				}
				/* @meta_info_border_radius2 */
				.$unique_block_class .td-module-meta-info-bottom {
					border-radius: @meta_info_border_radius2;
				}
				
				/* @art_title */
				.$unique_block_class .entry-title {
					margin: @art_title;
				}
				
				/* @info_space */
				.$unique_block_class .td-editor-date {
					margin: @info_space;
				}
				
				/* @show_excerpt */
				.$unique_block_class .td-excerpt {
					display: @show_excerpt;
				}
				/* @art_excerpt */
				.$unique_block_class .td-excerpt {
					margin: @art_excerpt;
				}
				/* @fav_size */
                body .$unique_block_class .td-favorite {
                    font-size: @fav_size;
                }
                /* @fav_space */
                body .$unique_block_class .td-favorite {
                    top: @fav_space;
                    right: @fav_space;
                }
				/* @show_audio */
				.$unique_block_class .td-audio-player {
					opacity: 1;
					visibility: visible;
					height: auto;
				}
				/* @hide_audio */
				.$unique_block_class .td-audio-player {
					opacity: 0;
					visibility: hidden;
					height: 0;
				}
				/* @art_audio */
				.$unique_block_class .td-audio-player {
					margin: @art_audio;
				}
				/* @art_audio_size */
				.$unique_block_class .td-audio-player {
					font-size: @art_audio_size;
				}
				
				/* @show_cat */
				.$unique_block_class .td-post-category:not(.td-post-extra-category) {
					display: @show_cat;
				}
				/* @modules_category_margin */
				.$unique_block_class .td-post-category {
					margin: @modules_category_margin;
				}
				/* @modules_category_padding */
				.$unique_block_class .td-post-category {
					padding: @modules_category_padding;
				}
				/* @modules_category_border */
				.$unique_block_class .td-post-category {
					border-width: @modules_category_border;
					border-style: solid;
					border-color: #000;
				}
				/* @modules_category_radius */
				.$unique_block_class .td-post-category {
					border-radius: @modules_category_radius;
				}
				
				/* @hide_author_date */
				.$unique_block_class .td-author-date {
					display: none;
				}
				/* @show_author_date */
				.$unique_block_class .td-author-date {
					display: inline;
				}
				
				/* @show_author */
				.$unique_block_class .td-post-author-name {
					display: @show_author;
				}
				/* @author_photo_size */
				.$unique_block_class .td-author-photo .avatar {
				    width: @author_photo_size;
				    height: @author_photo_size;
				}
				/* @author_photo_space */
				.$unique_block_class .td-author-photo .avatar {
				    margin-right: @author_photo_space;
				}
				/* @author_photo_radius */
				.$unique_block_class .td-author-photo .avatar {
				    border-radius: @author_photo_radius;
				}
				
				/* @show_date */
				.$unique_block_class .td-post-date,
				.$unique_block_class .td-post-author-name span {
					display: @show_date;
				}
				/* @show_review */
				.$unique_block_class .entry-review-stars {
					display: @show_review;
				}
				/* @review_space */
				.$unique_block_class .entry-review-stars {
					margin: @review_space;
				}
				/* @review_size */
				.$unique_block_class .td-icon-star,
                .$unique_block_class .td-icon-star-empty,
                .$unique_block_class .td-icon-star-half {
					font-size: @review_size;
				}
				/* @review_distance */
				.$unique_block_class .entry-review-stars i {
					margin-right: @review_distance;
				}
				.$unique_block_class .entry-review-stars i:last-child {
				    margin-right: 0;
				}
				/* @show_com */
				.$unique_block_class .td-module-comments {
					display: @show_com;
				}
				
				/* @show_btn */
				.$unique_block_class .td-read-more {
					display: @show_btn;
				}
				/* @btn_margin */
				.$unique_block_class .td-read-more {
					margin: @btn_margin;
				}
				/* @btn_padding */
				.$unique_block_class .td-read-more a {
					padding: @btn_padding;
				}
				/* @btn_border_width */
				.$unique_block_class .td-read-more a {
					border-width: @btn_border_width;
					border-style: solid;
					border-color: #000;
				}
				/* @btn_radius */
				.$unique_block_class .td-read-more a {
					border-radius: @btn_radius;
				}
				
				/* @pag_space */
				.$unique_block_class.td_with_ajax_pagination .td-next-prev-wrap,
				.$unique_block_class .page-nav,
				.$unique_block_class .td-load-more-wrap {
					margin-top: @pag_space;
				}
				/* @pag_padding */
				.$unique_block_class.td_with_ajax_pagination .td-next-prev-wrap a,
				.$unique_block_class .page-nav a,
				.$unique_block_class .page-nav .current,
				.$unique_block_class .page-nav .extend,
				.$unique_block_class .page-nav .pages,
				.$unique_block_class .td-load-more-wrap a {
					padding: @pag_padding;
				}
				.$unique_block_class .page-nav .pages {
				    padding-right: 0;
				}
				/* @pag_border_width */
				.$unique_block_class.td_with_ajax_pagination .td-next-prev-wrap a,
				.$unique_block_class .page-nav a,
				.$unique_block_class .page-nav .current,
				.$unique_block_class .page-nav .extend,
				.$unique_block_class .page-nav .pages,
				.$unique_block_class .td-load-more-wrap a {
					border-width: @pag_border_width;
				}
				.$unique_block_class .page-nav .extend {
				    border-style: solid;
				    border-color: transparent;
				}
				.$unique_block_class .page-nav .pages {
				    border-style: solid;
				    border-color: transparent;
				    border-right-width: 0;
				}
				/* @pag_border_radius */
				.$unique_block_class.td_with_ajax_pagination .td-next-prev-wrap a,
				.$unique_block_class .page-nav a,
				.$unique_block_class .page-nav .current,
				.$unique_block_class .td-load-more-wrap a {
					border-radius: @pag_border_radius;
				}
				/* @pag_icons_size */
				.$unique_block_class.td_with_ajax_pagination .td-next-prev-wrap a,
				.$unique_block_class .td-load-more-wrap a i,
				.$unique_block_class .page-nav a i {
					font-size: @pag_icons_size;
				}
				.$unique_block_class .td-load-more-wrap a .td-load-more-icon-svg svg,
				.$unique_block_class.td_with_ajax_pagination .td-next-prev-wrap .td-next-prev-icon-svg svg {
				    width: @pag_icons_size;
				    height: calc( @pag_icons_size + 1px );
				}
				
           
				/* @m_bg */
				.$unique_block_class .td-module-container {
					background-color: @m_bg;
				}
				/* @shadow */
				.$unique_block_class .td-module-container {
				    box-shadow: @shadow;
				}
				
				/* @meta_bg */
				.$unique_block_class .td-module-meta-info-top {
					background-color: @meta_bg;
				}
				/* @meta_bg2 */
				.$unique_block_class .td-module-meta-info-bottom {
					background-color: @meta_bg2;
				}
				
				/* @title_txt */
				.$unique_block_class .td-module-title a {
					color: @title_txt;
				}
				/* @title_txt_hover */
				.$unique_block_class .td_module_wrap:hover .td-module-title a {
					color: @title_txt_hover !important;
				}
				
				/* @cat_bg */
				.$unique_block_class .td-post-category {
					background-color: @cat_bg;
				}
				/* @cat_bg_hover */
				.$unique_block_class .td-post-category:hover {
					background-color: @cat_bg_hover !important;
				}
				/* @cat_txt */
				.$unique_block_class .td-post-category {
					color: @cat_txt;
				}
				/* @cat_txt_hover */
				.$unique_block_class .td-post-category:hover {
					color: @cat_txt_hover;
				}
				/* @modules_cat_border */
                .$unique_block_class .td-post-category {
                    border: @modules_cat_border solid #aaa;
                }
				/* @cat_border */
				.$unique_block_class .td-post-category {
					border-color: @cat_border;
				}
				/* @cat_border_hover */
				.$unique_block_class .td-post-category:hover {
					border-color: @cat_border_hover;
				}
				
				/* @author_txt */
				.$unique_block_class .td-post-author-name a {
					color: @author_txt;
				}
				/* @author_txt_hover */
				.$unique_block_class .td-post-author-name:hover a {
					color: @author_txt_hover;
				}
				
				/* @date_txt */
				.$unique_block_class .td-post-date,
				.$unique_block_class .td-post-author-name span {
					color: @date_txt;
				}
				
				/* @ex_txt */
				.$unique_block_class .td-excerpt {
					color: @ex_txt;
				}
				
				/* @com_bg */
				.$unique_block_class .td-module-comments a {
					background-color: @com_bg;
				}
				.$unique_block_class .td-module-comments a:after {
					border-color: @com_bg transparent transparent transparent;
				}
				/* @com_txt */
				.$unique_block_class .td-module-comments a {
					color: @com_txt;
				}
				/* @rev_txt */
				.$unique_block_class .entry-review-stars {
					color: @rev_txt;
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
				
				/* @btn_bg */
				.$unique_block_class .td-read-more a {
					background-color: @btn_bg !important;
				}
				/* @btn_bg_hover */
				.$unique_block_class .td-read-more:hover a {
					background-color: @btn_bg_hover !important;
				}
				/* @btn_txt */
				.$unique_block_class .td-read-more a {
					color: @btn_txt;
				}
				/* @btn_txt_hover */
				.$unique_block_class .td-read-more:hover a {
					color: @btn_txt_hover;
				}
				/* @btn_border */
				.$unique_block_class .td-read-more a {
					border-color: @btn_border;
				}
				/* @btn_border_hover */
				.$unique_block_class .td-read-more:hover a {
					border-color: @btn_border_hover;
				}
				
				/* @pag_text */
				.$unique_block_class.td_with_ajax_pagination .td-next-prev-wrap a,
				.$unique_block_class .page-nav a,
				.$unique_block_class .td-load-more-wrap a {
					color: @pag_text;
				}
				.$unique_block_class .td-load-more-wrap a .td-load-more-icon-svg svg,
				.$unique_block_class .td-load-more-wrap a .td-load-more-icon-svg svg *,
				.$unique_block_class.td_with_ajax_pagination .td-next-prev-wrap .td-next-prev-icon-svg svg,
				.$unique_block_class.td_with_ajax_pagination .td-next-prev-wrap .td-next-prev-icon-svg svg *,
				.$unique_block_class .page-nav .page-nav-icon-svg svg ,
				.$unique_block_class .page-nav .page-nav-icon-svg svg * {
				    fill: @pag_text;
				}
				/* @pag_bg */
				.$unique_block_class.td_with_ajax_pagination .td-next-prev-wrap a,
				.$unique_block_class .page-nav a,
				.$unique_block_class .td-load-more-wrap a {    
					background-color: @pag_bg;
				}
				/* @pag_border */
				.$unique_block_class.td_with_ajax_pagination .td-next-prev-wrap a,
				.$unique_block_class .page-nav a,
				.$unique_block_class .td-load-more-wrap a {
					border-color: @pag_border;
				}
				/* @pag_a_text */
				.$unique_block_class .page-nav .current {
					color: @pag_a_text;
				}
				/* @pag_a_bg */
				.$unique_block_class .page-nav .current {
					background-color: @pag_a_bg;
				}
				/* @pag_a_border */
				.$unique_block_class .page-nav .current {
					border-color: @pag_a_border;
				}
				/* @pag_h_text */
				.$unique_block_class.td_with_ajax_pagination .td-next-prev-wrap a:hover,
				.$unique_block_class .page-nav a:hover,
				.$unique_block_class .td-load-more-wrap a:hover {
					color: @pag_h_text;
				}
				.$unique_block_class .td-load-more-wrap a .td-load-more-icon-svg svg,
				.$unique_block_class .td-load-more-wrap a .td-load-more-icon-svg svg *,
				.$unique_block_class.td_with_ajax_pagination .td-next-prev-wrap a:hover .td-next-prev-icon-svg svg,
				.$unique_block_class.td_with_ajax_pagination .td-next-prev-wrap a:hover .td-next-prev-icon-svg svg *,
				.$unique_block_class .page-nav a:hover .page-nav-icon-svg svg ,
				.$unique_block_class .page-nav a:hover .page-nav-icon-svg svg * {
				    fill: @pag_h_text;
				}
				/* @pag_h_bg */
				.$unique_block_class.td_with_ajax_pagination .td-next-prev-wrap a:hover,
				.$unique_block_class .page-nav a:hover,
				.$unique_block_class .td-load-more-wrap a:hover {    
					background-color: @pag_h_bg;
				}
				/* @pag_h_border */
				.$unique_block_class.td_with_ajax_pagination .td-next-prev-wrap a:hover,
				.$unique_block_class .page-nav a:hover,
				.$unique_block_class .td-load-more-wrap a:hover {
					border-color: @pag_h_border;
				}
				
				/* @video_rec_color */
				#td-video-modal.$unique_block_modal_class .td-vm-rec-title {
				    color: @video_rec_color;
				}
				/* @video_title_color */
				#td-video-modal.$unique_block_modal_class .td-vm-title a {
				    color: @video_title_color;
				}
				/* @video_title_color_h */
				#td-video-modal.$unique_block_modal_class .td-vm-title a:hover {
				    color: @video_title_color_h;
				}
				/* @video_bg */
				#td-video-modal.$unique_block_modal_class .td-vm-content-wrap {
				    background-color: @video_bg;
				}
				/* @video_overlay */
				#td-video-modal.$unique_block_modal_class .td-vm-overlay {
				    background-color: @video_overlay;
				}
				
				/* @vid_t_color */
				.$unique_block_class .td-post-vid-time {
					color: @vid_t_color;
				}
				/* @vid_t_bg_color */
				.$unique_block_class .td-post-vid-time {
					background-color: @vid_t_bg_color;
				}
				
				/* @ad_loop_color */
				.$unique_block_class .td-adspot-title {
					color: @ad_loop_color;
				}
				
				/* @shadow_m */
				.$unique_block_class .td-module-meta-info {
				    box-shadow: @shadow_m;
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
				

				/* @f_header */
				.$unique_block_class .td-block-title a,
				.$unique_block_class .td-block-title span {
					@f_header
				}
				/* @f_pag */
				.$unique_block_class.td_with_ajax_pagination .td-next-prev-wrap a i,
				.$unique_block_class .page-nav a,
				.$unique_block_class .page-nav span,
				.$unique_block_class .td-load-more-wrap a {
					@f_pag
				}
				/* @f_title */
				.$unique_block_class .entry-title {
					@f_title
				}
				/* @f_cat */
				.$unique_block_class .td-post-category {
					@f_cat
				}
				/* @f_meta */
				.$unique_block_class .td-editor-date,
				.$unique_block_class .td-editor-date .td-post-author-name a,
				.$unique_block_class .td-editor-date .entry-date,
				.$unique_block_class .td-module-comments a {
					@f_meta
				}
				/* @f_ex */
				.$unique_block_class .td-excerpt {
					@f_ex
				}
				/* @f_btn */
				.$unique_block_class .td-read-more a {
					@f_btn
				}
				/* @f_more */
				.$unique_block_class .td-load-more-wrap a {
					@f_more
				}
				/* @f_vid_title */
				#td-video-modal.$unique_block_modal_class .td-vm-title {
					@f_vid_title
				}
				/* @f_vid_time */
				.$unique_block_class .td-post-vid-time {
					@f_vid_time
				}
				
				/* @f_ad */
				.$unique_block_class .td-adspot-title {
					@f_ad
				}
				
				/* @mix_type */
                html:not([class*='ie']) .$unique_block_class .entry-thumb:before {
                    content: '';
                    width: 100%;
                    height: 100%;
                    position: absolute;
                    top: 0;
                    left: 0;
                    opacity: 1;
                    transition: opacity 1s ease;
                    -webkit-transition: opacity 1s ease;
                    mix-blend-mode: @mix_type;
                }
                /* @color */
                html:not([class*='ie']) .$unique_block_class .entry-thumb:before {
                    background: @color;
                }
                /* @mix_gradient */
                html:not([class*='ie']) .$unique_block_class .entry-thumb:before {
                    @mix_gradient;
                }
                
                
                /* @mix_type_h */
                @media (min-width: 1141px) {
                    html:not([class*='ie']) .$unique_block_class .entry-thumb:after {
                        content: '';
                        width: 100%;
                        height: 100%;
                        position: absolute;
                        top: 0;
                        left: 0;
                        opacity: 0;
                        transition: opacity 1s ease;
                        -webkit-transition: opacity 1s ease;
                        mix-blend-mode: @mix_type_h;
                    }
                    html:not([class*='ie']) .$unique_block_class .td-module-container:hover .entry-thumb:after {
                        opacity: 1;
                    }
                }
                
                /* @color_h */
                html:not([class*='ie']) .$unique_block_class .entry-thumb:after {
                    background: @color_h;
                }
                /* @mix_gradient_h */
                html:not([class*='ie']) .$unique_block_class .entry-thumb:after {
                    @mix_gradient_h;
                }
                /* @mix_type_off */
                html:not([class*='ie']) .$unique_block_class .td-module-container:hover .entry-thumb:before {
                    opacity: 0;
                }
                    
                /* @effect_on */
                html:not([class*='ie']) .$unique_block_class .entry-thumb {
                    filter: @fe_brightness @fe_contrast @fe_saturate;
                    transition: all 1s ease;
                    -webkit-transition: all 1s ease;
                }
                /* @effect_on_h */
                @media (min-width: 1141px) {
                    html:not([class*='ie']) .$unique_block_class .td-module-container:hover .entry-thumb {
                        filter: @fe_brightness_h @fe_contrast_h @fe_saturate_h;
                    }
                }
			</style>";


        $td_css_res_compiler = new td_css_res_compiler( $raw_css );
        $td_css_res_compiler->load_settings( __CLASS__ . '::cssMedia', $this->get_all_atts() );

        $compiled_css .= $td_css_res_compiler->compile_css();

        return $compiled_css;
    }

    function render( $atts, $content = null ) {

        global $tdb_state_category, $tdb_state_author, $tdb_state_search, $tdb_state_date, $tdb_state_tag, $tdb_state_single_page;

        $in_composer = td_util::tdc_is_live_editor_iframe() || td_util::tdc_is_live_editor_ajax();

        switch( tdb_state_template::get_template_type() ) {

	        case 'cpt':
	        case '404':
	        case 'attachment':
	        case 'single':
                $loop_data = array();
                break;

	        case 'category':
                $loop_data = $tdb_state_category->loop->__invoke( $atts );
                $atts['category_id'] = $loop_data['category_id'];

                break;

            case 'author':
                $loop_data = $tdb_state_author->loop->__invoke( $atts );
                $atts['autors_id'] = $loop_data['author_id'];

                break;

            case 'search':
                $loop_data = $tdb_state_search->loop->__invoke( $atts );
                $atts['search_query'] = $loop_data['search_query'];

                break;

            case 'date':
                $loop_data = $tdb_state_date->loop->__invoke( $atts );
                $atts['date_query'] = $loop_data['date_query'];

                break;

            case 'tag':
                $loop_data = $tdb_state_tag->loop->__invoke( $atts );
                $atts['tag_slug'] = $loop_data['tag_slug'];

                break;

            case 'cpt_tax':

                if ( $tdb_state_category->is_cpt_post_type_archive() ) {
                    $loop_data = $tdb_state_category->cpt_archive_loop->__invoke($atts);
                    $atts['installed_post_types'] = $loop_data['post_type'];
                } else {
                    if ( is_tax() ) {
                        $tdb_state_category->set_tax();
                    }
                    $loop_data = $tdb_state_category->loop->__invoke($atts);
                    $atts['tag_slug'] = $loop_data['tag_slug'];
                }

                break;

            default:
                $loop_data = $tdb_state_single_page->loop->__invoke( $atts );
        }


        parent::render( $atts ); // sets the live atts, $this->atts, $this->block_uid, $this->td_query (it runs the query)

	    $additional_classes_array = array();

	    // if no posts
	    if ( empty( $loop_data['loop_posts'] ) ) {

		    // on composer iframe && search templates
		    if ( ( tdc_state::is_live_editor_ajax() || tdc_state::is_live_editor_iframe() ) && tdb_state_template::get_template_type() === 'search' ) {

			    // in this case get the posts from block's td_query
                if ( !empty( $this->td_query->posts ) ) {

                    // run through td_query posts & set them as arrays
                    foreach ( $this->td_query->posts as $post ) {
	                    $loop_data['loop_posts'][$post->ID] = array(
		                    'post_id' => $post->ID,
		                    'post_type' => get_post_type( $post->ID ),
		                    'has_post_thumbnail' => has_post_thumbnail( $post->ID ),
		                    'post_thumbnail_id' => get_post_thumbnail_id( $post->ID ),
		                    'post_link' => esc_url( get_permalink( $post->ID ) ),
		                    'post_title' => get_the_title( $post->ID ),
		                    'post_title_attribute' => esc_attr( strip_tags( get_the_title( $post->ID ) ) ),
		                    'post_excerpt' => $post->post_excerpt,
		                    'post_content' => $post->post_content,
		                    'post_date_unix' =>  get_the_time( 'U', $post->ID ),
		                    'post_date' => get_the_time( get_option( 'date_format' ), $post->ID ),
		                    'post_modified' => get_the_modified_date(get_option( 'date_format' ), $post->ID),
		                    'post_author_url' => get_author_posts_url( $post->post_author ),
		                    'post_author_name' => get_the_author_meta( 'display_name', $post->post_author ),
		                    'post_author_email' => get_the_author_meta( 'email', $post->post_author ),
		                    'post_comments_no' => get_comments_number( $post->ID ),
		                    'post_comments_link' => get_comments_link( $post->ID ),
		                    'post_theme_settings' => td_util::get_post_meta_array( $post->ID, 'td_post_theme_settings' ),
	                    );
                      }

                }

                //echo '<pre style="white-space: pre-wrap">';
                //print_r( $this->td_query );
                //echo '</pre>';

		    }

        }

        // pagination
        $pagination = $this->get_att( 'ajax_pagination' );
        if( $pagination != '' && $pagination === 'numbered' ) {
            $additional_classes_array[] = 'tdb-numbered-pagination';
        }

        // hover effect
        $h_effect = $this->get_att('h_effect');
        if( $h_effect != '' ) {
            $additional_classes_array[] = 'td-h-effect-' . $h_effect;
        }

        $buffy = ''; //output buffer

        $buffy .= '<div class="' . $this->get_block_classes( $additional_classes_array ) . ' tdb-category-loop-posts" ' . $this->get_block_html_atts() . '>';

            //get the block css
            $buffy .= $this->get_block_css();

            //get the js for this block
            $buffy .= $this->get_block_js();


            $custom_title = $this->get_att( 'custom_title' );
            if( $custom_title != '' ) {
                //get the filter for this block
                $buffy .= '<div class="td-block-title-wrap">';
                    $buffy .= $this->get_block_title(); //get the block title
                $buffy .= '</div>';
            }

            $buffy .= '<div id=' . $this->block_uid . ' class="td_block_inner tdb-block-inner td-fix-index">';
                if ( !empty( $loop_data['loop_posts'] ) ) {
                    $buffy .= $this->inner( $loop_data['loop_posts'] );  // inner content of the block
                } else {

	                if ( !empty( tdb_state_template::get_template_type() ) && 'search' === tdb_state_template::get_template_type() ) {
		                $buffy .= '<div class="no-results td-pb-padding-side">';
		                $buffy .= '<h2>' . __td('No results', TD_THEME_NAME ) . '</h2>';
		                $buffy .= '</div>';
	                } else {

                        if ( !$in_composer && is_page() && is_paged() ) {
                            /**
                             * The "no posts" message was replaced by a redirect since 07.05.2024 to avoid "no posts" pages from being indexed.
                             * This might not be the ideal solution, but we are following WordPress's approach to post pagination with the "nextpage" attribute.
                             * If issues arise, we can implement this on demand or explore alternative solutions like using "noindex, follow"
                             */
                            $url = get_permalink();
                            wp_redirect( $url, 301 );
                            exit;
                        } else {
                            /**
                             * no posts to display. This function generates the __td('No posts to display').
                             * the text can be overwritten by the template using the global @see td_global::$custom_no_posts_message
                             */
		                $buffy .= td_page_generator::no_posts();
                        }
	                }

                }
	        $buffy .= '</div>';

            if ( !empty( $loop_data['loop_posts'] ) && $pagination != '' ) {
                if ( $pagination === 'numbered' ) {
                    $buffy .= $this->get_numbered_pagination( $loop_data['loop_pagination'] );
                } else {
                    $prev_icon = $this->get_icon_att('prev_tdicon');
                    $prev_icon_class = $this->get_att('prev_tdicon');
                    $next_icon = $this->get_icon_att('next_tdicon');
                    $next_icon_class = $this->get_att('next_tdicon');

                    $buffy .= $this->get_block_pagination($prev_icon, $next_icon, $prev_icon_class, $next_icon_class);
                }
            }

        $buffy .= '</div>';

        return $buffy;
    }

    function inner( $posts ) {

        /*
         * loop ad
         */
        // ad code
        $loop_ad = $this->get_att('ad_loop');
        if ( $loop_ad != '' ) {
            $loop_ad = rawurldecode( base64_decode( strip_tags( $loop_ad ) ) );
        }

        // ad title
        $loop_ad_title = $this->get_att('ad_loop_title');

        // ad repeat
        $loop_ad_repeat = $this->get_att('ad_loop_repeat');
        if ( $loop_ad_repeat == '' ) {
            $loop_ad_repeat = 2;
        }

        // ad disable
        $loop_ad_disable = false;
        if ( $this->get_att('ad_loop_disable')  !='' && ( current_user_can('administrator') || current_user_can('editor') ) ) {
            $loop_ad_disable = true;
        }


        $buffy = '';
        $td_block_layout = new td_block_layout();
        $index = 0;

        if ( !empty( $posts ) ) {
            foreach ( $posts as $post ) {
                $tdb_module_loop_2 = new tdb_module_loop_2( $post, $this->get_all_atts() );
                $buffy .= $tdb_module_loop_2->render( __CLASS__ );

                if( $loop_ad != '' ) {
                    if( !empty( $loop_ad_repeat ) && ( ( $index + 1 ) % $loop_ad_repeat ) == 0 ) {
                        $buffy .= '<div class="tdb_module_loop td_module_wrap tdb_module_rec">';
                            $buffy .= $this->build_ad_spot($loop_ad, $loop_ad_title, $loop_ad_disable);
                        $buffy .= '</div>';
                    }
                }

                $index++;
            }
        }

        $buffy .= $td_block_layout->close_all_tags();

        return $buffy;

    }

    function build_ad_spot( $ad_spot_ad_code, $ad_spot_title, $ad_disable = false ) {

        if ( empty( $ad_spot_ad_code ) ) {
            return '';
        }

        //ad spot title
        $spot_title = '';
        if( !empty( $ad_spot_title ) ) {
            $spot_title = $ad_spot_title;
        }

        $buffy = '';

        if ( td_util::tdc_is_live_editor_iframe() || td_util::tdc_is_live_editor_ajax() || $ad_disable ) {
            $buffy .= '<div class="td-a-ad tdc-a-ad td-spot-id-loop"><span class="td-adspot-title">' . $spot_title . '</span><div class="tdc-placeholder-title"></div></div>';
        } else {
            $buffy .= '<div class="td-a-ad id_ad_loop">';
            $buffy .= '<span class="td-adspot-title">' . $spot_title . '</span>';
            $buffy .= do_shortcode( stripslashes( $ad_spot_ad_code ) );
            $buffy .= '</div>';
        }

        return $buffy;
    }

    function get_numbered_pagination( $loop_pagination_data ) {

        $pagination_data  = $loop_pagination_data;
        $pagenavi_options = $loop_pagination_data['pagenavi_options'];

        $buffy = '';

        if( $pagination_data['max_page'] > 1 || intval( $pagenavi_options['always_show'] ) === true ) {
            $pages_text = str_replace("%CURRENT_PAGE%", number_format_i18n( $pagination_data['paged'] ), $pagenavi_options['pages_text'] );
            $pages_text = str_replace("%TOTAL_PAGES%", number_format_i18n( $pagination_data['max_page'] ), $pages_text );

            $buffy .= '<div class="page-nav td-pb-padding-side">';

            $buffy .= $pagination_data['previous_posts_link'];

            if ( $pagination_data['start_page'] >= 2 && $pagination_data['pages_to_show'] < $pagination_data['max_page'] ) {
                $first_page_text = str_replace( "%TOTAL_PAGES%", number_format_i18n( $pagination_data['max_page'] ), $pagenavi_options['first_text'] );
                $buffy .= '<a href="' . esc_url( get_pagenum_link() ) . '" class="first" title="' . $first_page_text . '">' . $first_page_text . '</a>';
                if ( !empty( $pagenavi_options['dotleft_text'] ) && ( $pagination_data['start_page'] > 2) ) {
                    $buffy .= '<span class="extend">' . $pagenavi_options['dotleft_text'] . '</span>';
                }
            }

            for ( $i = $pagination_data['start_page']; $i <= $pagination_data['end_page']; $i++ ) {
                if ( $i == $pagination_data['paged'] ) {
                    $current_page_text = str_replace("%PAGE_NUMBER%", number_format_i18n( $i ), $pagenavi_options['current_text'] );
                    $buffy .= '<span class="current">' . $current_page_text . '</span>';
                } else {
                    $page_text = str_replace("%PAGE_NUMBER%", number_format_i18n( $i ), $pagenavi_options['page_text'] );
                    $buffy .= '<a href="' . esc_url( get_pagenum_link( $i )) . '" class="page" title="' . $page_text . '">' . $page_text . '</a>';
                }
            }

            if ( $pagination_data['end_page'] < $pagination_data['max_page'] ) {
                if ( !empty( $pagenavi_options['dotright_text']) && ( $pagination_data['end_page'] + 1 < $pagination_data['max_page'] ) ) {
                    $buffy .= '<span class="extend">' . $pagenavi_options['dotright_text'] . '</span>';
                }

                $last_page_text = str_replace( "%TOTAL_PAGES%", number_format_i18n( $pagination_data['max_page'] ), $pagenavi_options['last_text'] );
                $buffy .= '<a href="' . esc_url( get_pagenum_link( $pagination_data['max_page'] ) ) . '" class="last" title="' . $last_page_text . '">' . $last_page_text . '</a>';
            }

            $buffy .= $pagination_data['next_posts_link'];

            if ( !empty( $pages_text ) ) {
                $buffy .= '<span class="pages">' . $pages_text . '</span>';
            }

            $buffy .= '<div class="clearfix"></div>';
            $buffy .= '</div>';
        }

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
                var block = jQuery('.<?php echo $this->block_uid; ?>');
                blockClass = '.<?php echo $this->block_uid; ?>';

                if( block.find('audio').length > 0 ) {
                    jQuery(blockClass + ' audio').mediaelementplayer();
                }
            })();
        </script>
        <?php

        return $buffy . td_util::remove_script_tag( ob_get_clean() );
    }

}
