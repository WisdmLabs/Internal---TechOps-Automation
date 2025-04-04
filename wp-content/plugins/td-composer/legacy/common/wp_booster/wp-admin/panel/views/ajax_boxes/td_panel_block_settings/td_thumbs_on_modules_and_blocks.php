<?php

/**
 * !!!! modulele astea trebuie incarcate by default
 * module8 category label should be enabled by default
 * module_related
 * module_mega_menu
 * module_slide
 */

?>
<div class="td-box-row">
    <div class="td-box-description td-box-full">
        <span class="td-box-title">More information:</span>
        <p>From here you can enable the thumbnail image that will be cropped for the modules &amp; blocks. If the thumbnail image is not enabled for a specific module that you use, the module will show a default placeholder with the size of the image and instructions about how to enable the thumb for that module</p>
        <p><strong style="color:red">Please regenerate your thumbnails if you change any of the thumb settings!</strong> <?php echo ('enabled' !== td_util::get_option('tds_white_label')) ? '- ' . td_api_text::get('panel_existing_content_url') : '' ?> </p>
    </div>
    <div class="td-box-row-margin-bottom"></div>
</div>

<div class="td-box-section-separator tdb-hide"></div>

<!-- THUMB PLACEHOLDER -->
<div class="td-box-row td-box-thumb-placeholder">
    <div class="td-box-description">
        <span class="td-box-title">THUMB PLACEHOLDER</span>
        <p>Upload a custom placeholder image to be displayed before the thumbnail loads or instead of the no-thumb image.</p>
    </div>
    <div class="td-box-control-full">
        <?php
        echo td_panel_generator::upload_image(array(
            'ds' => 'td_option',
            'option_id' => 'tds_thumb_placeholder'
        ));
        ?>
    </div>
</div>

<div class="td-box-section-separator tdb-hide"></div>

    <div class="td-box-row td-box-thumb-placeholder">
        <div class="td-box-description">
            <span class="td-box-title">Use WebP format</span>
            <p>Enable or disable loading WebP images. <b style="color:red">Important:</b> the theme will not create WebP images format, but will load them on blocks if they exist on your server.</p>
        </div>
        <div class="td-box-control-full">
            <?php
            echo td_panel_generator::checkbox(array(
                'ds' => 'td_option',
                'option_id' => 'tds_load_webp',
                'true_value' => 'yes',
                'false_value' => ''
            ));
            ?>
        </div>
    </div>


<?php
foreach (td_api_thumb::get_all() as $thumb) {
    ?>
    <!-- THUMB -->
    <div class="td-box-row">
        <div class="td-box-description">
            <span class="td-box-title"><?php printf( '%1$d x %2$d', $thumb['width'], $thumb['height'] ) ?></span>
        </div>
        <div class="td-box-control-full">
            <?php
            echo td_panel_generator::checkbox(array(
                'ds' => 'td_option',
                'option_id' => 'tds_thumb_' . $thumb['name'],
                'true_value' => 'yes',
                'false_value' => ''
            ));
            ?>
            <div class="td-help-checkbox-inline">
                <span>Retina:</span>
            </div>
            <?php
            //enable retina thumb
            echo td_panel_generator::checkbox(array(
            'ds' => 'td_option',
            'option_id' => 'tds_thumb_' . $thumb['name'] . '_retina',
            'true_value' => 'yes',
            'false_value' => ''
            ));
            ?>

            <div class="td-help-checkbox-inline">
                <?php
                echo "<span>This thumb size is used for:</span> <ul><li>" . implode("</li><li>", $thumb['used_on']) . "</li></ul>";
                ?>
            </div>
        </div>
    </div>
<?php
}