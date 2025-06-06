<?php

/**
 * Class td_js
 */

define ('TD_JS_THEME_VERSION_URL', 'http://td_cake.themesafe.com/td_cake/version.php');


class td_js {

    private static $instance = null;

    public static function get_instance(){

        if ( is_null(self::$instance) ) {
            self::$instance = new td_js();
        }
	    return self::$instance;
    }

    /**
     * is running on each page load
     * || td_api_features::is_enabled('require_activation') === false
     */
    function __construct() {
        // not admin
        if ( !is_admin()) {
            return;
        }

        $td_js_status_time = td_util::get_option_('td_cake_status_time');    // last time the status changed
        $td_js_status = td_util::get_option_('td_cake_status');              // the current status time
        $td_js_lp_status = td_util::get_option_('td_cake_lp_status');

        // verify if we have a status time, if we don't have one, the theme did not changed the status ever
        if (!empty($td_js_status_time)) {


            // the time since the last status change
            $status_time_delta = time() - $td_js_status_time;

            // late version check after 30
            if (TD_DEPLOY_MODE == 'dev') {
                $delta_max = 40;
            } else {
                $delta_max = 2592000;
            }

            if ($status_time_delta > $delta_max and $td_js_lp_status != 'lp_sent') {
                td_util::update_option_('td_cake_lp_status', 'lp_sent');
                //$td_theme_version = @wp_remote_get(TD_JS_THEME_VERSION_URL . '?cs=' . $td_js_status . '&lp=true&v=' . TD_THEME_VERSION . '&n=' . TD_THEME_NAME, array('blocking' => false));
                return;
            }

            // the theme is registered, return
            if ($td_js_status == 2) {

                // add the menu
                add_action('admin_menu', array($this, 'td_js_licence_panel'), 12);

                return;
            }

            // add the menu
            add_action('admin_menu', array($this, 'td_js_register_panel'), 12);


            if (TD_DEPLOY_MODE == 'dev') {
                $delta_max = 40;
            } else {
                $delta_max = 0; // 14 days
            }
            if ($status_time_delta > $delta_max) {
                add_action( 'admin_notices', array($this, 'td_js_msg_2') );
                if ($td_js_status != '4') {
                    td_util::update_option_('td_cake_status', '4');
                }
                return;
            }

            if (TD_DEPLOY_MODE == 'dev') {
                $delta_max = 20;
            } else {
                $delta_max = 604800; // 7 days
            }
            if ($status_time_delta > $delta_max) {
                add_action( 'admin_notices', array($this, 'td_js_msg') );
                if ($td_js_status != '3') {
                    td_util::update_option_('td_cake_status', '3');
                }
                return;
            }

            // if some time passed and status is empty - do ping
            if ($status_time_delta > 0 and empty($td_js_status)) {
                td_util::update_option_('td_cake_status_time', time());
                td_util::update_option_('td_cake_status', '1');
                //$td_theme_version = @wp_remote_get(TD_JS_THEME_VERSION_URL . '?v=' . TD_THEME_VERSION . '&n=' . TD_THEME_NAME, array('blocking' => false)); // check for updates
                return;
            }

        } else {
            // update the status time first time - we do nothing
            td_util::update_option_('td_cake_status_time', time());
            // add the menu
            add_action('admin_menu', array($this, 'td_js_register_panel'), 12);
        }

    }

    //function td_footer_manual_activation($text) {
    //    //add manual activation button
    //    $text .= '<a href="#" class="td-manual-activation-btn">Activate the theme manually</a>';
    //    //add auto activation button
    //    $text .= '<a href="#" class="td-auto-activation-btn" style="display: none;">Back to automatic activation</a>';
    //    //button script
    //    $text .= '<script type="text/javascript">
    //                //manual activation
    //                jQuery(\'.td-manual-activation-btn\').click(function(event){
    //                    event.preventDefault();
    //                    jQuery(\'.td-manual-activation\').css(\'display\', \'block\');
    //                    //hide manual activation button
    //                    jQuery(this).hide();
    //                    //hide auto activation panel
    //                    jQuery(\'.td-auto-activation\').hide();
    //                    //display back to automatic activation button
    //                    jQuery(\'.td-auto-activation-btn\').show();
    //                });
    //
    //                //automatic activation
    //                jQuery(\'.td-auto-activation-btn\').click(function(event){
    //                    event.preventDefault();
    //                    jQuery(\'.td-manual-activation\').css(\'display\', \'none\');
    //                    //hide back to automatic activation button
    //                    jQuery(this).hide();
    //                    //show auto activation panel
    //                    jQuery(\'.td-auto-activation\').show();
    //                    //display manual activation button
    //                    jQuery(\'.td-manual-activation-btn\').show();
    //                });
    //             </script>';
    //    echo '<!-- manual activation -->' . $text;
    //}

    private function td_js_server_id() {
        ob_start();
        phpinfo(INFO_GENERAL);
        echo TD_THEME_NAME;
        return md5(ob_get_clean());
    }

    private function syntax_check() {
        $key = td_util::get_option('envato_key');
        $key = preg_replace('#([a-z0-9]{8})-?([a-z0-9]{4})-?([a-z0-9]{4})-?([a-z0-9]{4})-?([a-z0-9]{12})#','$1-$2-$3-$4-$5',strtolower($key));
        if (strlen($key) == 36){
            return true;
        }
        return false;
    }

    private function td_js_manual($s_id, $e_id, $t_id) {
        if (md5($s_id . $e_id) == $t_id) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * the cake panel t
     */
    function td_js_register_panel() {
        if (td_api_features::is_enabled('require_activation') === true) {
            add_submenu_page( "td_theme_welcome", 'Activate theme', 'Activate theme', "edit_posts", 'td_cake_panel', array( $this, 'td_js_panel' ), null );
        }
    }


    /**
     * the licence panel
     */
    function td_js_licence_panel() {

        $dlk = td_util::get_registration() != '' && ( strpos( td_util::get_registration(), '445743e6d221') || strpos(td_util::get_registration(),'0000000live') );
        if ( td_api_features::is_enabled('require_activation') === true && !$dlk ) {
            add_submenu_page( "td_theme_welcome", 'My license', 'My license', "edit_posts", 'td_licence_panel', array( $this, 'td_licence_panel' ), null );
        }
    }

    /**
     * show the activate theme panel
     */
    function td_js_panel() {

        // add manual activation link (visible only on this page)
        //add_filter('admin_footer_text', array($this, 'td_footer_manual_activation'));

        $buy_url = '<a href="https://themeforest.net/item/newspaper/5489609?utm_source=NP_theme_panel&utm_medium=click&utm_campaign=cta&utm_content=buy_new_activ" target="_blank">Buy Newspaper Theme</a>';
        if ('Newsmag' == TD_THEME_NAME) {
            $buy_url = '<a href="https://themeforest.net/item/newsmag-news-magazine-newspaper/9512331?utm_source=NM_t[…]l&utm_medium=click&utm_campaign=cta&utm_content=buy_new_activ" target="_blank">Buy Newsmag Theme</a>';
        }

        ?>
        <style type="text/css">
            .updated, .error {
                display: none !important;
            }
        </style>

        <div class="td-activate-page-wrap">

            <?php require_once TAGDIV_ROOT_DIR . '/includes/wp-booster/wp-admin/tagdiv-view-header.php' ?>

            <div class="about-wrap td-admin-wrap">

                <div class="td-activate-wrap">

                    <!-- Auto activation -->
                    <div class="td-auto-activation">

                        <!-- Step 1 - Envato Code -->
                        <div class="td-activate-section td-activate-envato-code">

                            <div class="td-activate-subtitle">Activate <?php echo td_util::get_wl_val('tds_wl_theme_name', TD_THEME_NAME) ?></div>

                            <p class="td-activate-description">
                                Activate <?php echo td_util::get_wl_val('tds_wl_theme_name', TD_THEME_NAME) ?> WordPress Theme to enjoy the full benefits of a great product. Add your code to get access to the knowledge base, video tutorials, a community of amazing people passionate about WordPress and our outstanding customer support center.
                            </p>

                            <div class="td-activate-input-wrap td-envato-code">
                                <div class="td-input-title">Envato purchase code:</div>
                                <input type="text" name="td-envato-code" value="" placeholder="Your Envato code"/>
                                <span class="td-activate-input-bar"></span>
                                <span class="td-activate-err td-envato-missing" style="display:none;">Code is required</span>
                                <span class="td-activate-err td-envato-length" style="display:none;">Code is too short</span>
                                <span class="td-activate-err td-envato-invalid" style="display:none;">Code is not valid</span>
                                <span class="td-activate-err td-envato-check-error" style="display:none;">Envato API is down, please try again later.</span>
                            </div>
                            <div class="td-activate-input-wrap td-envato-email">
                                <div class="td-input-title">Email:</div>
                                <input type="text" name="td-envato-email" value="" placeholder="Your email"/>
                                <span class="td-activate-input-bar"></span>
                                <span class="td-activate-err td-activate-email-missing" style="display:none;">Email is required</span>
                                <span class="td-activate-err td-activate-email-syntax" style="display:none;">Email syntax is incorrect</span>
                            </div>
                            <div class="td-emails-consent td-admin-checkbox td-small-checkbox" style="padding-top: 0; border: 0;">
                                <div class="td-checkbox td-checkbox-active" style="border-radius: 30px;" data-uid="td_emails_consent_input" data-val-true="" data-val-false="no">
                                    <div class="td-checbox-buton td-checbox-buton-active" style="border-radius: 30px;"></div>
                                </div>
                                <p style="margin: 0 5px 0 0; font-size: 12px; color: #777;">Agree to receive information about the theme updates, product discounts and services.</p>

                                <input type="hidden" name="td_activate_emails" id="td_emails_consent_input" value="">
                            </div>

                            <?php if ('enabled' === td_util::get_option('tds_white_label')) { ?>
                                <div class="td-create-support-account td-admin-checkbox td-small-checkbox" style="padding-top: 0; border: 0;display: none;">
                                    <div class="td-checkbox td-checkbox-active" style="border-radius: 30px;" data-uid="td_create_account" data-val-true="" data-val-false="no">
                                        <div class="td-checbox-buton td-checbox-buton-active" style="border-radius: 30px;"></div>
                                    </div>
                                    <input type="hidden" name="td_activate_registration" id="td_create_account" value="no">
                                </div>
                            <?php } else { ?>
                                        <div class="td-create-support-account td-admin-checkbox td-small-checkbox" style="padding-top: 0; border: 0;">
                                            <div class="td-checkbox td-checkbox-active" style="border-radius: 30px;" data-uid="td_create_account" data-val-true="" data-val-false="no">
                                                <div class="td-checbox-buton td-checbox-buton-active" style="border-radius: 30px;"></div>
                                            </div>
                                            <p style="margin: 0 5px 0 0; font-size: 12px; color: #777;">Create Support Account</p>
                                            <input type="hidden" name="td_activate_registration" id="td_create_account" value="">
                                        </div>
                            <?php } ?>
                            <button class="td-activate-button td-envato-code-button">Activate</button>
                            <?php if ('enabled' !== td_util::get_option('tds_white_label')) { ?>
                            <div class="td-envato-code-info"><a href="http://forum.tagdiv.com/how-to-find-your-envato-purchase-code/" target="_blank">Find your Envato code</a><span><svg style="vertical-align: middle; margin-left: 20px;" width="17" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><g><path d="M22,9a1,1,0,0,0,0,1.42l4.6,4.6H3.06a1,1,0,1,0,0,2H26.58L22,21.59A1,1,0,0,0,22,23a1,1,0,0,0,1.41,0l6.36-6.36a.88.88,0,0,0,0-1.27L23.42,9A1,1,0,0,0,22,9Z"/></g></svg> If you don't have a license key, you can get one now. </span><?php echo $buy_url?></div>

                            <div class="td-gpdr-activate-notice">
                                <p>
                                    To deliver you a better customer support service, access to features and to prevent piracy when the theme is activated the following data is sent to our servers:
                                </p>
                                <ul>
                                    <li>The Envato purchase code for the item</li>
                                    <li>The Envato username</li>
                                    <li>The server IP address that has the theme installed</li>
                                    <li>The site URL (if it’s available)</li>
                                    <li>The theme version</li>
                                </ul>
                                <p>
                                    We use this data to customize your experience within our support center. The data is stored in the US, and we do not share any of this information with third-party partners.
                                </p>

                                <p>
                                    When you activate the theme, you agree with our privacy policy terms, and you give us your consent to store and handle this data.
                                    For GDPR requests, please write us an email at contact@tagdiv.com.
                                </p>
                            </div>
                            <?php } ?>
                        </div>

                        <!-- Step 2 - Forum Registration -->
                        <div class="td-activate-section td-activate-registration" style="display: none;">

                            <div class="td-activate-subtitle">Create your Forum Support Account</div>

                            <p class="td-activate-description">
                                You’re almost there! Fill the form to create your forum account, and you are ready to access a community of amazing people passionate about WordPress and our outstanding customer support center.
                            </p>

                            <div class="td-registration-err td-forum-connection-failed" style="display:none;">Forum connection failed, please try again.</div>

                            <!-- Username -->
                            <div class="td-activate-input-wrap td-activate-username">
                                <div class="td-input-title">Username:</div>
                                <input type="text" name="td-activate-username" value="" placeholder="Username" />
                                <span class="td-activate-input-bar"></span>
                                <span class="td-activate-err td-activate-username-missing" style="display:none;">Username is required</span>
                                <span class="td-activate-err td-activate-username-used" style="display:none;">Current username is already used, try another one</span>
                            </div>

                            <!-- Email -->
                            <div class="td-activate-input-wrap td-activate-email">
                                <div class="td-input-title">Your Email:</div>
                                <input type="text" name="td-activate-email" value="" placeholder="Email" />
                                <span class="td-activate-input-bar"></span>
                                <span class="td-activate-err td-activate-email-missing" style="display:none;">Email is required</span>
                                <span class="td-activate-err td-activate-email-syntax" style="display:none;">Email syntax is incorrect</span>
                                <span class="td-activate-err td-activate-email-used" style="display:none;">Current email is registered with another account</span>
                                <div class="td-small-bottom">The email is private and we will not share it with anyone. You'll also get updates about tagDiv products.</div>
                            </div>

                            <!-- Password -->
                            <div class="td-activate-input-wrap td-activate-password">
                                <div class="td-input-title">Password:</div>
                                <input type="password" name="td-activate-password" autocomplete="off" value="" placeholder="Password" />
                                <span class="td-activate-input-bar"></span>
                                <span class="td-activate-err td-activate-password-missing" style="display:none;">Password is required</span>
                                <span class="td-activate-err td-activate-password-length" style="display:none;">Minimum password length is 6 characters</span>
                            </div>

                            <!-- Password Confirmation -->
                            <div class="td-activate-input-wrap td-activate-password-confirmation">
                                <div class="td-input-title">Password confirmation:</div>
                                <input type="password" name="td-activate-password-confirmation" autocomplete="off" value="" placeholder="Password confirmation" />
                                <span class="td-activate-input-bar"></span>
                                <span class="td-activate-err td-activate-password-confirmation-missing" style="display:none;">Password confirmation is required</span>
                                <span class="td-activate-err td-activate-password-mismatch" style="display:none;">Password and password confirmation don't match</span>
                            </div>

                            <button class="td-activate-button td-registration-button">Create Account</button>
                            <div class="td-activate-info"><a href="http://forum.tagdiv.com/privacy-policy-2/" target="_blank">Privacy policy</a></div>
                        </div>

                    </div>

                    <!-- Manual activation -->
                    <!--<div class="td-manual-activation">-->
                        <!--<div class="td-activate-subtitle">Manual activation</div>-->
                        <!--<div class="td-registration-err td-manual-activation-failed" style="display:none;">Manual activation failed, check each field and try again.</div>-->
                        <!--<div class="td-manual-info">-->
                            <!--<ol>-->
                                <!--<li>Go to our <a href="http://tagdiv.com/td_cake/manual.php?td_server_id=<?php /*echo esc_attr( $this->td_js_server_id() ) */?>" target="_blank">manual activation page</a></li>-->
                                <!--<li>Paste your <em>Server ID</em> there and the <a href="http://forum.tagdiv.com/how-to-find-your-envato-purchase-code/" target="_blank">Envato purchase code</a></li>-->
                                <!--<li>Return with the <a href="http://forum.tagdiv.com/wp-content/uploads/2017/06/activation_key.png" target="_blank">activation key</a> and paste it in this form</li>-->
                            <!--</ol>-->
                        <!--</div>-->
                        <!-- Your server ID -->
                        <!--<div class="td-activate-input-wrap td-manual-server-id">-->
                            <!--<div class="td-input-title">Your server ID:</div>-->
                            <!--<input type="text" name="td-manual-server-id" value="<?php /*echo esc_attr( $this->td_js_server_id() ) */?>" readonly/>-->
                            <!--<span class="td-activate-input-bar"></span>-->
                            <!--<div class="td-small-bottom">Copy this id and paste it in our manual activation page</div>-->
                        <!--</div>-->
                        <!-- Envato code -->
                        <!--<div class="td-activate-input-wrap td-manual-envato-code">-->
                            <!--<div class="td-input-title">Envato purchase code:</div>-->
                            <!--<input type="text" name="td-manual-envato-code" value="" placeholder="Envato purcahse code" />-->
                            <!--<span class="td-activate-input-bar"></span>-->
                            <!--<span class="td-activate-err td-manual-envato-code-missing" style="display:none;">Envato code is required</span>-->
                        <!--</div>-->
                        <!-- Activation key -->
                        <!--<div class="td-activate-input-wrap td-manual-activation-key">-->
                            <!--<div class="td-input-title">tagDiv activation key:</div>-->
                            <!--<input type="text" name="td-manual-activation-key" value="" placeholder="Activation key" />-->
                            <!--<span class="td-activate-input-bar"></span>-->
                            <!--<span class="td-activate-err td-manual-activation-key-missing" style="display:none;">Activation key is required</span>-->
                        <!--</div>-->
                        <!--<button class="td-activate-button td-manual-activate-button">Activate</button>-->
                    <!--</div>-->

                </div>

                <!--<form method="post" action="admin.php?page=td_cake_panel">-->
                <!--    <input type="hidden" name="td_magic_token" value="--><?php //echo wp_create_nonce("td-validate-license") ?><!--"/>-->
                <!--    <table class="form-table">-->
                <!--        <tr valign="top">-->
                <!--            <th scope="row">Envato purchase code:</th>-->
                <!--            <td>-->
                <!--                <input style="width: 400px" type="text" name="td_envato_code" value="--><?php //printf( '%1$s', $td_envato_code ) ?><!--" />-->
                <!--                <br/>-->
                <!--                <div class="td-small-bottom"><a href="http://forum.tagdiv.com/how-to-find-your-envato-purchase-code/" target="_blank">Where to find your purchase code ?</a></div>-->
                <!--            </td>-->
                <!--        </tr>-->
                <!--    </table>-->
                <!--    <input type="hidden" name="td_active" value="auto">-->
                <!--    --><?php //submit_button('Activate theme'); ?>
                <!--</form>-->

            </div>
        </div>


        <?php
    }

	/**
	 * show the licence panel
	 */
    function td_licence_panel() {
        ?>

        <div class="td-licence-page-wrap">

            <?php require_once TAGDIV_ROOT_DIR . '/includes/wp-booster/wp-admin/tagdiv-view-header.php' ?>

            <div class="about-wrap td-admin-wrap td-license-page" style="margin-top: 0">

                <div id="tdb-check-licence" class="td-white-box">
                    <router-view></router-view>
                </div>
            </div>
        </div>

        <?php
    }


    // all admin pages that begin with td_ do now show the message
    private function check_if_is_our_page() {
        if (isset($_GET['page']) and substr($_GET['page'], 0, 3) == 'td_') {
            return true;
        }
        return false;
    }

    function td_js_msg() {
        if ($this->check_if_is_our_page() === true || td_api_features::is_enabled('require_activation') === false) {
            return;
        }
        $td_activate_url = 'https://forum.tagdiv.com/newspaper-6-how-to-activate-the-theme/';
        if ('Newsmag' == TD_THEME_NAME) {
            $td_activate_url = 'https://forum.tagdiv.com/newsmag-how-to-activate-the-theme/';
        }
        ?>
        <div class="error">
            <p><?php echo '<strong style="color:red"> Please activate the theme! </strong> - <a href="' . wp_nonce_url( admin_url( 'admin.php?page=td_cake_panel' ) ) . '">Click here to enter your code</a> - if this is an error please contact us at contact@tagdiv.com - <a href="' . $td_activate_url . '">How to activate the theme</a>'; ?></p>
        </div>
        <?php
    }

    function td_js_msg_2() {
        if ($this->check_if_is_our_page() === true || td_api_features::is_enabled('require_activation') === false) {
            return;
        }
        $td_activate_url = 'https://forum.tagdiv.com/newspaper-6-how-to-activate-the-theme/';
        $buy_url = '<a href="https://themeforest.net/item/newspaper/5489609?utm_source=NP_theme_panel&utm_medium=click&utm_campaign=cta&utm_content=buy_new_red" target="_blank">Buy Newspaper Theme</a>';
        $td_brand_url = td_util::get_wl_val('tds_wl_logo_url', 'https://tagdiv.com?utm_source=theme&utm_medium=logo&utm_campaign=tagdiv&utm_content=click_hp');
        $td_brand_logo = td_util::get_wl_val('tds_wl_logo', td_global::$get_template_directory_uri . '/legacy/common/wp_booster/wp-admin/images/logo-tagdiv.png');

        if ('Newsmag' == TD_THEME_NAME) {
            $td_activate_url = 'https://forum.tagdiv.com/newsmag-how-to-activate-the-theme/';
            $buy_url = '<a href="https://themeforest.net/item/newsmag-news-magazine-newspaper/9512331?utm_source=NM_t[…]l&utm_medium=click&utm_campaign=cta&utm_content=buy_new_red" target="_blank">Buy Newsmag Theme</a>';
        }
        ?>
        <div class="td-error-activate">
            <div class="about-wrap td-wp-admin-header ">
                <div class="td-wp-admin-top">
                    <div class="td-tagdiv-brand-wrap">
                        <img class="td-tagdiv-gradient" src="<?php echo td_global::$get_template_directory_uri ?>/legacy/common/wp_booster/wp-admin/images/gradient.png" />
                        <a class="td-tagdiv-link" href="<?php echo $td_brand_url ?>"><img class="td-tagdiv-brand" src="<?php echo $td_brand_logo ?> " /></a>
                    </div>
                    <div class="td-wp-admin-theme">
                        <h1>Your license of <?php echo td_util::get_wl_val('tds_wl_theme_name', TD_THEME_NAME) ?> Theme is <b style="color: red;">not registered!</b></h1>
                        <p>
                            Activate <?php echo td_util::get_wl_val('tds_wl_theme_name', TD_THEME_NAME) ?> to enjoy the full benefits of the theme. The activation system gives you <strong>access to the support center and premium features</strong>.
                            It also prevents piracy, allowing us to provide <strong>free updates, upcoming premium features, top-notch support, and compatibility with the latest WordPress versions</strong>.
                        </p>
                        <p><?php echo '<a class="td-wp-admin-button" href="' . wp_nonce_url( admin_url( 'admin.php?page=td_cake_panel' ) ) . '">Activate now</a>'?>
                            <?php if ('enabled' !== td_util::get_option('tds_white_label')) {
                             echo '<a href="' . $td_activate_url . '">How to activate the theme</a>'; ?>
                            <span style="font-size: 12px"><svg style="vertical-align: middle; margin-left: 20px;" width="17" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><g><path d="M22,9a1,1,0,0,0,0,1.42l4.6,4.6H3.06a1,1,0,1,0,0,2H26.58L22,21.59A1,1,0,0,0,22,23a1,1,0,0,0,1.41,0l6.36-6.36a.88.88,0,0,0,0-1.27L23.42,9A1,1,0,0,0,22,9Z"/></g></svg> If you don't have a license key, you can get one now. </span>
                            <?php echo $buy_url ?>
                            <?php } ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}



class td_version_check {
    private $cron_task_name = 'td_check_version';


    function __construct() {

        // the booster loaded hook runs before this is added..
        //add_action('td_wp_booster_loaded', array($this, '_compare_theme_versions'));

        // checks for updates
        $this->_compare_theme_versions();

        add_action($this->cron_task_name, array($this, '_check_for_updates'));

        add_filter( 'cron_schedules', array($this, '_schedule_modify_add_three_days') );

        if ( wp_next_scheduled( $this->cron_task_name ) === false ) {
            wp_schedule_event(time(), 'three_days', $this->cron_task_name);
        }

        add_action('switch_theme', array($this, 'on_switch_theme_remove_cron'));

    }


    /**
     * connect to api server and check if a new version is available
     */
    function _check_for_updates() {
        // default base currency is eur and it returns all rates
        $api_url = 'http://td_cake.tagdiv.com/td_cake/get_current_version.php?n=' . TD_THEME_NAME . '&v=' . TD_THEME_VERSION;
        $json_api_response = td_remote_http::get_page($api_url, __CLASS__);

        // check for a response
        if ($json_api_response === false) {
            td_log::log(__FILE__, __FUNCTION__, 'Api call failed', $api_url);
        }

        // try to decode the json
        $api_response = @json_decode($json_api_response, true);
        if ($api_response === null and json_last_error() !== JSON_ERROR_NONE) {
            td_log::log(__FILE__, __FUNCTION__, 'Error decoding the json', $api_response);
        }

        //valid response
        if (!empty($api_response['version']) && !empty($api_response['update_url'])) {
            td_util::update_option('td_latest_version', $api_response['version']);
            td_util::update_option('td_update_url', $api_response['update_url']);
        }
    }


    /**
     * compare current version with latest version
     */
    function _compare_theme_versions() {
        $td_theme_version = TD_THEME_VERSION;

        //don't run on deploy
        if ( $td_theme_version == '__td_deploy_version__' ) {
            return;
        }

        $td_latest_version = td_util::get_option('td_latest_version' );
        //latest version is not set
        if ( empty( $td_latest_version ) ) {
            return;
        }

        $td_update_url = td_util::get_option('td_update_url');
        //update url is not set
        if ( empty( $td_update_url ) ) {
            return;
        }

        //compare theme's current version with the official version
        $compare_versions = version_compare($td_theme_version, $td_latest_version, '<');

        if ( $compare_versions === true ) {
            //update is available - add variables used by td_theme_update js function
            td_js_buffer::add_to_wp_admin_footer('var tdUpdateAvailable = "' . $td_latest_version . '";');
            td_js_buffer::add_to_wp_admin_footer('var tdUpdateUrl = "' . $td_update_url . '";');
        }
    }


    /**
     * on switch theme remove wp cron task
     */
    function on_switch_theme_remove_cron() {
        wp_clear_scheduled_hook($this->cron_task_name);
    }


    /**
     * @param $schedules
     * @return mixed
     */
    function _schedule_modify_add_three_days( $schedules ) {
        $schedules['three_days'] = array(
            'interval' => 259200, // 3 days in seconds
            'display' => 'three_days'
        );
        return $schedules;

    }
}

//execute only if the updates flag is enabled
if ( td_api_features::is_enabled('check_for_updates') ) {
    new td_version_check();
}

td_js::get_instance();
