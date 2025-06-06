<div class="td-page-options-tab-wrap">
    <div class="td-page-options-tab td-page-options-tab-active" data-panel-class="td-page-option-general"><a href="#">General</a></div>
    <div class="td-page-options-tab" data-panel-class="td-page-option-unique-articles-2"><a href="#">Unique Articles</a></div>
	<?php

	$td_page_settings_tabs = apply_filters( 'td_page_settings_tabs', array() );

	if ( !empty( $td_page_settings_tabs ) && is_array( $td_page_settings_tabs ) ) {
		foreach ( $td_page_settings_tabs as $tab ) {

			// tabs id/name/file are required
			if ( !isset( $tab['id'], $tab['name'], $tab['file'] ) )
				continue;
			?>

            <div class="td-page-options-tab" data-panel-class="td-page-option-post-2-<?php echo $tab['id'] ?>"><a href="#"><?php echo $tab['name'] ?></a></div>

			<?php
		}
	}

	?>
</div>

<div class="td-meta-box-inside">

    <!-- page option general -->
    <div class="td-page-option-panel td-page-option-panel-active td-page-option-general">

        <p><strong>Note:</strong> The settings from this box only work if you do not use the page builder on this template. The template detects if the page builder is used and it removes the title and sidebars if that's the case. </p>

        <!-- sidebar position -->
        <div class="td-meta-box-row">
            <span class="td-page-o-custom-label">
                Sidebar position:
                <?php
                td_util::tooltip_html('
                        <h3>Sidebar position:</h3>
                        <p>From here you can set the sidebar position for this page only.</p>
                        <ul>
                            <li><strong>This setting overrides</strong> the Theme panel setting from <i>Template settings > Page template</i></li>
                            <li><strong>On default</strong> - the template will load the sidebar position that is set in the Theme panel: <i>Template settings > Page template</i></li>
                            <li>This setting is intended to be used for content pages; when this template detects
                            that the page builder is used, it will switch to a full width layout (with no sidebar). </li>
                            <li>If you want to use a sidebar with the page builder please use the <strong>Widget Sidebar</strong> block</li>

                        </ul>
                    ', 'right')
                ?>
            </span>
            <div class="td-inline-block-wrap">
                <?php
                echo td_panel_generator::visual_select_o(array(
                    'ds' => 'td_page',
                    'item_id' => '',
                    'option_id' => 'td_sidebar_position',
                    'values' => array(
                        array('text' => '', 'title' => 'Sidebar Default', 'val' => '', 'img' => TDC_URL_LEGACY_COMMON . '/wp_booster/wp-admin/images/panel/sidebar/sidebar-default.png'),
                        array('text' => '', 'title' => 'Sidebar Left', 'val' => 'sidebar_left', 'img' => TDC_URL_LEGACY_COMMON . '/wp_booster/wp-admin/images/panel/sidebar/sidebar-left.png'),
                        array('text' => '', 'title' => 'No Sidebar', 'val' => 'no_sidebar', 'img' => TDC_URL_LEGACY_COMMON . '/wp_booster/wp-admin/images/panel/sidebar/sidebar-full.png'),
                        array('text' => '', 'title' => 'Sidebar Right', 'val' => 'sidebar_right', 'img' => TDC_URL_LEGACY_COMMON . '/wp_booster/wp-admin/images/panel/sidebar/sidebar-right.png')
                    ),
                    'selected_value' => $mb->get_the_value('td_sidebar_position')
                ));
                ?>
            </div>
        </div>

        <!-- custom sidebar -->
        <div class="td-meta-box-row">
            <span class="td-page-o-custom-label">
                Custom sidebar:
                <?php
                td_util::tooltip_html('
                        <h3>Custom sidebar:</h3>
                        <p>From here you can set a custom sidebar for this page only.</p>
                        <ul>
                            <li><strong>This setting overrides</strong> the Theme panel setting from <i>Template settings > Page template</i></li>
                            <li><strong>On default</strong> - the template will load the sidebar position set in the Theme panel: <i>Template settings > Page template</i></li>
                            <li>This setting is intended to be used for content pages; when this template detects
                            that the page builder is used, it will switch to a full width layout (with no sidebar). </li>
                            <li>If you want to use a sidebar with the page builder please use the <strong>Widget Sidebar</strong> block</li>
                        </ul>
                    ', 'right')
                ?>
            </span>
            <?php
            echo td_panel_generator::sidebar_pulldown(array(
                'ds' => 'td_page',
                'item_id' => '',
                'option_id' => 'td_sidebar',
                'selected_value' => $mb->get_the_value('td_sidebar')
            ));
            ?>
        </div>

        <div class="td-disble-message" style="display: none;">
            <p>While using a pagebuilder sidebar settings are not available. To add a sidebar on the page, edit it using <?php echo td_util::get_wl_val('tds_wl_brand', 'tagDiv')?> Composer.</p>
        </div>

    </div>

    <!-- unique articles tab -->
    <div class="td-page-option-panel td-page-option-unique-articles-2">

        <p>
            This feature will make sure that only unique articles are loaded on the initial page load.<br>
            <strong>Note:</strong> We recommend not to use the Unique Articles feature if the page contains ajax blocks that have sub categories or pagination.<br>
            <strong>Note:</strong> Unexpected behaviour might also occur when using blocks with offset.
        </p>

        <div class="td-meta-box-row">
            <span class="td-page-o-custom-label">
                Unique articles:
            </span>
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

            <div class="td-page-option-panel td-page-option-post-2-<?php echo $tab['id'] ?>">
				<?php include ( $tab['file'] ); ?>
            </div> <!-- /page option <?php echo $tab['id'] ?> -->

			<?php
		}
	}

	?>

</div>



