<div class="td-page-options-tab-wrap">
    <div class="td-page-options-tab td-page-options-tab-active" data-panel-class="td-page-option-general"><a href="#">General</a></div>
    <div class="td-page-options-tab" data-panel-class="td-page-option-post-list"><a href="#">Posts Loop Settings</a></div>
    <div class="td-page-options-tab" data-panel-class="td-page-option-unique-articles"><a href="#">Unique Articles</a></div>
	<?php

	$td_page_settings_tabs = apply_filters( 'td_page_settings_tabs', array() );

	if ( !empty( $td_page_settings_tabs ) && is_array( $td_page_settings_tabs ) ) {
		foreach ( $td_page_settings_tabs as $tab ) {

			// tabs id/name/file are required
			if ( !isset( $tab['id'], $tab['name'], $tab['file'] ) )
				continue;
			?>

            <div class="td-page-options-tab" data-panel-class="td-page-option-post-<?php echo $tab['id'] ?>"><a href="#"><?php echo $tab['name'] ?></a></div>

			<?php
		}
	}

	?>
</div>

<div class="td-meta-box-inside">

    <!-- page option general -->
    <div class="td-page-option-panel td-page-option-panel-active td-page-option-general">

        <p><strong>Note:</strong> Unlike the default template, the settings from this panel applies to the bottom part of the page (where the loop + sidebar is). </p>

        <!-- sidebar position -->
        <div class="td-meta-box-row">
            <span class="td-page-o-custom-label">
                Sidebar position:
                <?php
                td_util::tooltip_html('
                        <h3>Sidebar position:</h3>
                        <p>From here you can set the sidebar position for the bottom part of the page.</p>
                        <ul>
                            <li><strong>With no selection</strong> - the template will load the sidebar on the right</li>
                        </ul>
                    ', 'right')
                ?>
            </span>
            <?php
            echo td_panel_generator::visual_select_o(array(
                'ds' => 'td_homepage_loop',
                'item_id' => '',
                'option_id' => 'td_sidebar_position',
                'values' => array(
                    array('text' => '', 'title' => 'Sidebar Left', 'val' => 'sidebar_left', 'img' => TDC_URL_LEGACY . '/assets/images/panel/sidebar/sidebar-left.png'),
                    array('text' => '', 'title' => 'No Sidebar', 'val' => 'no_sidebar', 'img' => TDC_URL_LEGACY . '/assets/images/panel/sidebar/sidebar-full.png'),
                    array('text' => '', 'title' => 'Sidebar Right', 'val' => 'sidebar_right', 'img' => TDC_URL_LEGACY . '/assets/images/panel/sidebar/sidebar-right.png')
                ),
                'selected_value' => $mb->get_the_value('td_sidebar_position')
            ));
            ?>
        </div>

        <!-- sidebar -->
        <div class="td-meta-box-row">
            <span class="td-page-o-custom-label">
                Sidebar:
                <?php
                td_util::tooltip_html('
                        <h3>Sidebar:</h3>
                        <p>From here you can set select a custom sidebar for the bottom part of the page.</p>
                        <ul>
                            <li><strong>With no selection</strong> - the template will load the <i>' . TD_THEME_NAME . ' default</i> sidebar</li>
                        </ul>
                    ', 'right')
                ?>
            </span>
            <?php
            echo td_panel_generator::sidebar_pulldown(array(
                'ds' => 'td_homepage_loop',
                'item_id' => '',
                'option_id' => 'td_sidebar',
                'selected_value' => $mb->get_the_value('td_sidebar')
            ));
            ?>
        </div>

        <div class="td-meta-box-row">
            <span class="td-page-o-custom-label">Template layout:</span>
            <img class="td-doc-image-wp td-doc-image-homepage-loop" style="max-width: 100%" src="<?php echo TDC_URL_LEGACY ?>/assets/images/pagebuilder/info-homepage-loop.png" />
        </div>

    </div>

    <!-- Posts loop settings -->
    <div class="td-page-option-panel td-page-option-post-list">
        <!-- Layout -->
        <?php if( 'Newsmag' == TD_THEME_NAME || ( 'Newspaper' == TD_THEME_NAME && defined('TD_STANDARD_PACK') ) ) { ?>
            <div class="td-meta-box-row">
                <span class="td-page-o-custom-label">
                    Layout:
                    <?php
                    td_util::tooltip_html('
                            <h3>Layout select:</h3>
                            <p>Select a custom module to be used in the loop of this page.</p>
                            <ul>
                                <li>If you want to make you own modules, please go to our <a href="http://forum.tagdiv.com/api-modules-introduction/" target="_blank">API section</a> of the documentation</li>
                            </ul>
                        ', 'right')
                    ?>
                </span>
                    <div class="td-page-o-visual-select-modules">
                        <?php
                        echo td_panel_generator::visual_select_o(array(
                            'ds' => 'td_homepage_loop',
                            'item_id' => '',
                            'option_id' => 'td_layout',
                            'values' => td_panel_generator::helper_display_modules('default+enabled_on_loops'),
                            'selected_value' => $mb->get_the_value('td_layout')
                        ));
                        ?>
                    </div>
            </div>
        <?php } ?>

        <!-- show or hide the title -->
        <div class="td-meta-box-row">
            <?php $mb->the_field('list_custom_title_show'); ?>
            <span class="td-page-o-custom-label">
                Show list title:
                <?php
                td_util::tooltip_html('
                        <h3>Show the list title:</h3>
                        <p>Hide or show the loop title. It can be something like "Latest articles" etc.</p>
                    ', 'right')
                ?>
            </span>
            <div class="td-select-style-overwrite">
                <select name="<?php $mb->the_name(); ?>" class="td-panel-dropdown">
                    <option value="">Show title</option>
                    <option value="hide_title"<?php $mb->the_select_state('hide_title'); ?>>Hide title</option>
                </select>
            </div>
       </div>

        <!-- custom title for article list -->
        <div class="td-meta-box-row">
            <?php $mb->the_field('list_custom_title'); ?>
            <span class="td-page-o-custom-label">
                Article list title:
                <?php
                td_util::tooltip_html('
                        <h3>The title to use for the loop:</h3>
                        <p>It can be something like "Latest articles" etc.</p>
                    ', 'right')
                ?>
            </span>
            <input type="text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>"/>
            <span class="td-page-o-info">Custom title for the article list section</span>
        </div>

        <div class="td-meta-box-row td-meta-box-row-separator">
            <h3>Filters for the loop:</h3>
        </div>

        <?php

        class td_set_homepage_loop_filter {

            public function __construct()  { }

            /**
             *  setting the array that will be used for homepage filter
             * @return array
             */
            function homepage_filter_get_map () {

                //get the generic filter array
                $generic_filter_array = td_config::get_map_filter_array();

                //remove items from array
                $offset = 0;
                foreach ($generic_filter_array as $key=>&$field_array) {

                    if ( !empty($field_array['hide_on_page_loop']) ) {
                        unset($generic_filter_array[$key]);
                    } elseif ($field_array['param_name'] == "hide_title") {
                        array_splice($generic_filter_array, $offset, 1);
                    } else if ($field_array['param_name'] == 'limit') {
                        $field_array['value'] = 10;
                    }
                    $offset++;
                }

                //change the default limit
                //$generic_filter_array[6]['value'] = 10;

                //add the show featured posts in the loop setting
                array_push ($generic_filter_array,
                    array(
                        "param_name" => "show_featured_posts",
                        "type" => "dropdown",
                        "value" => array('- Show featured posts -' => '', 'Hide featured posts' => 'hide_featured'),
                        "heading" => 'Featured posts:',
                        "description" => "",
                        "holder" => "div",
                        "class" => ""
                    )
                );

                return array(
                    "name" => 'Templates with articles',
                    "base" => "",
                    "class" => "",
                    "controls" => "full",
                    "category" => "",
                    'icon' => '',
                    "params" => $generic_filter_array
                );
            }

        }

        $obj_td_homepage_filter_add = new td_set_homepage_loop_filter;
        // instantiates the filter render object, passing metabox object
        $td_metabox_generator = new td_metabox_generator($mb);

        // call to create the filter
        $td_metabox_generator->td_render_homepage_loop_filter($obj_td_homepage_filter_add->homepage_filter_get_map());

        ?>
    </div>

    <!-- page option unique articles -->
    <div class="td-page-option-panel td-page-option-unique-articles">
        <p>
            This feature will make sure that only unique articles are loaded on the initial page load.<br>
            <strong>Note:</strong> We recommend not to use the Unique Articles feature if the page contains ajax blocks that have sub categories or pagination.<br>
            <strong>Note:</strong> Unexpected behaviour might also occur when using blocks with offset.
        </p>

        <div class="td-meta-box-row">
            <span class="td-page-o-custom-label">Unique articles:</span>
            <?php $mb->the_field('td_unique_articles'); ?>
            <div class="td-select-style-overwrite td-inline-block-wrap">
                <select name="<?php $mb->the_name(); ?>" class="td-panel-dropdown">
                    <option value=""> - Disabled - </option>
                    <option value="enabled"<?php $mb->the_select_state('enabled'); ?>>Enabled</option>
                </select>
            </div>
        </div>
    </div>

    <!-- post settings from filters -->
	<?php

	if ( !empty( $td_page_settings_tabs ) && is_array( $td_page_settings_tabs ) ) {
		foreach ( $td_page_settings_tabs as $tab ) {

			// tabs id/name/file are required
			if ( !isset( $tab['id'], $tab['name'], $tab['file'] ) )
				continue;
			?>

            <div class="td-page-option-panel td-page-option-post-<?php echo $tab['id'] ?>">
				<?php include ( $tab['file'] ); ?>
            </div> <!-- /page option <?php echo $tab['id'] ?> -->

			<?php
		}
	}

	?>

</div>


