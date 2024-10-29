<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class AmplifyLogin {

    public static function addFiles() {

//        $src = plugins_url('css/common.css', dirname(__FILE__));
//        wp_register_style('commonCss', $src);
//        wp_enqueue_style('commonCss');
        wp_localize_script('amplify_magic', 'personaL10n', array(
            'plugin_url' => plugins_url('amplify'),
            'ajax_url' => admin_url('admin-ajax.php', (is_ssl() ? 'https' : 'http')),
        ));
    }

    public static function adminStyle() {
        $src = plugins_url('css/common.css', dirname(__FILE__));
        wp_register_style('custom_wp_admin_css', $src);
        wp_enqueue_style('custom_wp_admin_css');
       
    }

    public function wp_footer() {?>
        <script type="text/javascript">
                window.amplifyInit = function() {
                    Amplify.identify({
                        email: '<?php echo $userEmail ?>',
                    });

                }
            </script>
    <?php }

    public function wp_header() {

        echo "<script type='text/javascript'>
           Amplify_APIKey = '" . get_option('_AMPLIFY_API_KEY') . "';
           Amplify_ProjectID= '" . get_option('_AMPLIFY_PROJECT_ID') . "';</script>";
        wp_register_script('amplify_head', plugins_url('amplify/js/amplify.js'), array('jquery'));
        wp_enqueue_script('amplify_head');
    }

    public static function init() {

//        add_action('parse_request', array('AmplifyLogin', 'connect'));

        add_action('wp_enqueue_scripts', array('AmplifyLogin', 'addFiles'));

       // add_action('admin_enqueue_scripts', array('AmplifyLogin', 'adminStyle'));

        add_action('admin_menu', array('AmplifyLogin', 'amplifyMenu'));

        add_action('wp_footer', array('AmplifyLogin', 'wp_footer'));
        add_action('wp_head', array('AmplifyLogin', 'wp_header'), 1);
        add_action('login_enqueue_scripts', array('AmplifyLogin', 'wp_header'), 1);
    }

    public static function wpLogin() {
        try {
            if (is_user_logged_in() && !isset($_COOKIE['amplifysid'])) {
                $current_user = wp_get_current_user();
                $userLogin = $current_user->user_login;
                $userEmail = $current_user->user_email;
                $userFirstName = $current_user->user_firstname;
                $userLastName = $current_user->user_lastname;
                $userId = $current_user->ID;
//                $identifier = new Amplify();
//                $response = $identifier->identify($userEmail, $userFirstName);
            } else {
//                $identifier = new Amplify();
//                $response = $identifier->identify();
            }
        } catch (Exception $e) {
            
        }
    }

    function connect() {
        global $wpdb;
        $amplify = new Amplify();
        $userprofile = $amplify->userProfile();
        $userEmail = $userprofile->email;

        if (!empty($userEmail) && !is_user_logged_in() && !is_admin()) {

            if (!empty($userprofile->email)) {

                $wp_user_id = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM $wpdb->usermeta WHERE meta_key='amplifyUserId' AND meta_value = %d", $userId));

                if (empty($wp_user_id)) {

                    $wp_user_obj = get_user_by('email', $userprofile->email);

                    $wp_user_id = $wp_user_obj->ID;
                }


                if (!empty($wp_user_id)) {
                    $wp_user_id_tmp = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM $wpdb->usermeta WHERE user_id = %d and meta_key='amplifyUserId'", $wp_user_id));
                    if (empty($wp_user_id_tmp)) {
                        update_user_meta($wp_user_id, 'amplifyUserId', $userprofile->amplifyUserId);
                        update_user_meta($wp_user_id, 'thumbnail', $userprofile->userPhoto);
                    }
                    self::set_cookies($wp_user_id);
                    $redirect = site_url() . $_SERVER['REQUEST_URI'];
                    wp_redirect($redirect);
                } else {

                    if (!get_option('users_can_register')) {
                        wp_redirect('wp-login.php?registration=disabled');
                        exit();
                    }

                    self::add_new_wpuser($userprofile);
                }
            } // check verification status of the email ends.
        }
    }

    public static function clearcookies() {

        setcookie(AUTH_COOKIE, ' ', time() - 31536000, ADMIN_COOKIE_PATH, COOKIE_DOMAIN);

        setcookie(SECURE_AUTH_COOKIE, ' ', time() - 31536000, ADMIN_COOKIE_PATH, COOKIE_DOMAIN);

        setcookie(AUTH_COOKIE, ' ', time() - 31536000, PLUGINS_COOKIE_PATH, COOKIE_DOMAIN);

        setcookie(SECURE_AUTH_COOKIE, ' ', time() - 31536000, PLUGINS_COOKIE_PATH, COOKIE_DOMAIN);

        setcookie(LOGGED_IN_COOKIE, ' ', time() - 31536000, COOKIEPATH, COOKIE_DOMAIN);

        setcookie(LOGGED_IN_COOKIE, ' ', time() - 31536000, SITECOOKIEPATH, COOKIE_DOMAIN);



        // Old cookies

        setcookie(AUTH_COOKIE, ' ', time() - 31536000, COOKIEPATH, COOKIE_DOMAIN);

        setcookie(AUTH_COOKIE, ' ', time() - 31536000, SITECOOKIEPATH, COOKIE_DOMAIN);

        setcookie(SECURE_AUTH_COOKIE, ' ', time() - 31536000, COOKIEPATH, COOKIE_DOMAIN);

        setcookie(SECURE_AUTH_COOKIE, ' ', time() - 31536000, SITECOOKIEPATH, COOKIE_DOMAIN);



        // Even older cookies

        setcookie(USER_COOKIE, ' ', time() - 31536000, COOKIEPATH, COOKIE_DOMAIN);

        setcookie(PASS_COOKIE, ' ', time() - 31536000, COOKIEPATH, COOKIE_DOMAIN);

        setcookie(USER_COOKIE, ' ', time() - 31536000, SITECOOKIEPATH, COOKIE_DOMAIN);

        setcookie(PASS_COOKIE, ' ', time() - 31536000, SITECOOKIEPATH, COOKIE_DOMAIN);
    }

    private static function set_cookies($user_id = 0, $remember = true) {
        if (!function_exists('wp_set_auth_cookie')) {

            return false;
        }

        if (!$user_id) {

            return false;
        }

        if (!$user = get_userdata($user_id)) {

            return false;
        }

        wp_clear_auth_cookie();



        wp_set_auth_cookie($user_id, $remember);



        wp_set_current_user($user_id);



        return true;
    }

    private static function add_new_wpuser($userprofile) {

        global $wpdb;
        $user_pass = wp_generate_password();

        $amplifyUserId = $userprofile->amplifyUserId;
        $thumbnail = $userprofile->userPhoto;



        if (!empty($amplifyUserId)) {
            if (!empty($userprofile->email)) {
                $email = $userprofile->email;
            }


            if (!empty($userprofile->userLogin)) {
                $username = $userprfile->userLogin;
            } else {
                $username = explode('@', $email);
            }
            if (!empty($userprofile->userFirstName)) {
                $fname = $userprofile->userFirstName;
            } else {
                $user_name = explode('@', $email);
                $fname = str_replace("_", " ", $user_name[0]);
            }
            if (!empty($userprofile->userLastName)) {
                $lname = $userprofile->userFirstName;
            }
            $role = get_option('default_role');
            $nameexists = true;
            $index = 0;
            $username = str_replace(' ', '-', $username);

            $userName = $username;
            while ($nameexists == true) {
                if (username_exists($userName) != 0) {
                    $index++;
                    $userName = $username . $index;
                } else {
                    $nameexists = false;
                }
            }



            $username = $userName;

            $userdata = array(
                'user_login' => $username,
                'user_pass' => $user_pass,
                'user_nicename' => sanitize_title($fname),
                'user_email' => $email,
                'display_name' => $fname,
                'nickname' => $fname,
                'first_name' => $fname,
                'last_name' => $lname,
                'role' => $role
            );

            $user_id = wp_insert_user($userdata);
            if (!empty($user_id)) {
                wp_new_user_notification($user_id, $user_pass);
            }



            if (!is_wp_error($user_id)) {

                if (!empty($email)) {
                    update_user_meta($user_id, 'email', $email);
                }
                if (!empty($amplifyUserId)) {
                    update_user_meta($user_id, 'amplifyUserId', $amplifyUserId);
                }

                if (!empty($thumbnail)) {
                    update_user_meta($user_id, 'thumbnail', $thumbnail);
                }
                wp_clear_auth_cookie();
                wp_set_auth_cookie($user_id);
                wp_set_current_user($user_id);
                $redirect = site_url() . $_SERVER['REQUEST_URI'];
                ;
                wp_redirect($redirect);
            } else {
                wp_redirect($redirect);
            }
        }
    }

    function amplifyajexlogin() {
        global $wpdb;
        $amplify = new Amplify();
        $userprofile = $amplify->userProfile();
        $userId = $userprofile->amplifyUserId;
        if (!empty($userId)) {

            if (!empty($userprofile->email)) {

                $wp_user_id = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM $wpdb->usermeta WHERE meta_key='amplifyUserId' AND meta_value = %d", $userId));



                if (empty($wp_user_id)) {

                    $wp_user_obj = get_user_by('email', $userprofile->email);

                    $wp_user_id = $wp_user_obj->ID;
                }


                if (!empty($wp_user_id)) {

                    $wp_user_id_tmp = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM $wpdb->usermeta WHERE user_id = %d and meta_key='amplifyUserId'", $wp_user_id));
                    if (empty($wp_user_id_tmp)) {
                        update_user_meta($wp_user_id, 'amplifyUserId', $userId);
                        update_user_meta($wp_user_id, 'thumbnail', $userprofile->userPhoto);
                    }


                    self::set_cookies($wp_user_id);
                    exit();
                } else {

                    if (!get_option('users_can_register')) {
                        wp_redirect('wp-login.php?registration=disabled');
                        exit();
                    }

                    self::add_new_wpuser($userprofile);
                }
            }
        }
    }

    public static function amplifyMenu() {

        add_menu_page('Amplify', 'Amplify', 'manage_options', 'amplify', 'AmplifyLogin::amplify', plugins_url('images/icon.png', dirname(__FILE__)));
//        add_submenu_page('amplify', 'SocialSharing', 'SocialSharing', 'manage_options', 'socialsharing', 'AmplifyLogin::socialsharing');
//        add_submenu_page('amplify', 'SocialLogin', 'SocialLogin', 'manage_options', 'sociallogin', 'AmplifyLogin::sociallogin');
//        add_submenu_page('amplify', 'AdvanceSettings', 'AdvanceSettings', 'manage_options', 'advancesettings', 'AmplifyLogin::advancesettings');
    }

    public static function socialsharing() {
        include_once 'html/socialsharing.php';
    }

    public static function sociallogin() {
        include_once 'html/sociallogin.php';
    }

    public static function advancesettings() {
        include_once 'html/advancesettings.php';
    }

    public static function amplify() {
        try {

            if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'changekey') {
                require_once('html/configuration.php');
            } else {
                $amplifyApiKey = get_option("_AMPLIFY_API_KEY");
                $amplifyApiSecret = get_option("_AMPLIFY_API_SECRET");
                $amplifyProjectId = get_option("_AMPLIFY_PROJECT_ID");
                $wordpressVersion = get_bloginfo('version');

                if (!empty($amplifyApiKey) && !empty($amplifyApiSecret) && !empty($amplifyProjectId)) {
                    $parameters = array('wordpressVersion' => $wordpressVersion, 'wordpressBoPluginUrl' => $wordpressBoPluginUrl);
                    try {

                        $AMPLIFYSDKObj = new Amplify($amplifyApiKey, $amplifyApiSecret, $amplifyProjectId, $debug);
                        $curlResponse = $AMPLIFYSDKObj->verify();
                    } catch (Exception $ex) {
                        $curlResponse = '{ "error": "' . $ex->getMessage() . '", "responseCode": 500 }';
                        $curlResponse = json_decode($result);
                    }
                    $curlResponse = $curlResponse;
                }

                require_once('html/amplify.php');
            }
        } catch (Exception $ex) {
            
        }
    }

    public function amplify_login_form() {

        if (is_user_logged_in()) {
            return true;
        }


        if (strstr(wp_login_url(), 'wp-login.php') !== false) {
            //rpx_wp_footer();
        }
        if (get_option('amplify_login_loginform', false)) {
            $loginFormWidgetId = get_option("amplify_login_loginform_widgetId");
            echo '<p id="amplify_loginWidget" style="margin-bottom:80px"></p>';
        }
    }

    public function amplify_register_form() {

        if (is_user_logged_in()) {
            return true;
        }


        if (strstr(wp_login_url(), 'wp-login.php') !== false) {
            //rpx_wp_footer();
        }
        if (get_option('amplify_login_registration', false)) {
            $registrationFormWidgetId = get_option("amplify_login_registration_widgetId");
            echo '<p id="amplify_registerWidget" style="margin-bottom:80px"></p>';
        }
    }

    function amplify_tinymce_addbuttons() {
        if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
            return;
        }
        if (get_user_option('rich_editing') == 'true') {
            add_filter("mce_external_plugins", array('AmplifyLogin', 'amplify_tinymce_addplugin'));
            add_filter('mce_buttons', array('AmplifyLogin', 'amplify_tinymce_registerbutton'));
        }
    }

    function amplify_tinymce_registerbutton($buttons) {
        array_push($buttons, 'separator', 'amplify');
        return $buttons;
    }

    function amplify_tinymce_addplugin($plugin_array) {
        $plugin_array['amplify'] = plugins_url('amplify/tinymce/plugins/polls/editor_plugin.js');
        return $plugin_array;
    }

    function amplify_poll_footer_admin() {
        echo '<script type="text/javascript">' . "\n";
        echo '/* <![CDATA[ */' . "\n";
        echo "\t" . 'var amplify = {' . "\n";
        echo "\t\t" . 'login_widget: "' . esc_js(__('social login widget')) . '",' . "\n";
        echo "\t\t" . 'insert_poll: "' . esc_js(__('Insert login Widget')) . '"' . "\n";
        echo "\t" . '};' . "\n";
        echo "\t" . 'if(document.getElementById("ed_toolbar")){' . "\n";
        echo "\t\t" . 'edButtons[edButtons.length] = new edButton("ed_o_amplify",amplify.poll, "", "","");' . "\n";
        echo "\t\t" . 'jQuery(document).ready(function($){' . "\n";
        echo "\t\t\t" . 'var popup_width = jQuery(window).width();' . "\n";
        echo "\t\t\t" . 'var popup_height = jQuery(window).height();' . "\n";
        echo "\t\t\t" . 'popup_width = ( 720 < popup_width ) ? 640 : popup_width - 80;' . "\n";
        echo "\t\t\t" . '$(\'#qt_content_ed_o_amplify\').replaceWith(\'<input type="button" id="qt_content_ed_o_amplify" accesskey="" class="ed_button" onclick="tb_show( \\\'Insert Poll\\\', \\\'#TB_inline?=&height=popup_height&width=popup_width&inlineId=amplifywidget\\\' );" value="\' + amplify.poll + \'" title="\' + amplify.insert_poll + \'" />\');' . "\n";
        echo "\t\t" . '});' . "\n";
        echo "\t" . '}' . "\n";
        echo '/* ]]> */' . "\n";
        echo '</script>' . "\n";
    }

    function amplify_add_poll_popup() {
        ?>
        <div id="amplifywidget" style="display:none;">
            <div id="content">
                <h1><strong>Insert a Widget</strong></h1>
                <h3><strong>Select Widget:</strong></h3>
                <p><?php
                    $amplifyObj = new Amplify();
                    $result = $amplifyObj->fetchwidget('1');
                    ?>
                    <select style="width:140px;margin-left:20px" name="amplify_login_id" id="amplify-login-id">
                        <option value="234">login1</option>
                        <option value="235">login2</option>
                        <option value="236">login3</option>
                    </select>

                </p>
                <p class="submit">
                    <input type="button" id="amplify-login-submit" class="button-primary" value="Insert Login Widget" name="submit" />
                </p>
                <p><strong>Haven't created a widget ?</strong>
                    <a href="http://amply.to" target="_blank">create Widget</a>
                </p>
            </div>
        </div>
        <?php
    }

    public function amplify_og_get_image(){
		if(is_front_page()){
			return "";
		}
		else if(is_home()){
			return "";
		}
		else {
			if (has_post_thumbnail()) {
				return wp_get_attachment_url(get_post_thumbnail_id());
			}
			else {
				$attachment = get_posts(array( 'numberposts' => 1, 'post_type'=>'attachment', 'post_parent' => get_the_ID() ));
				if ($attachment) {
					return wp_get_attachment_thumb_url($attachment[0]->ID);
				}
				else {
					return false;
					
				}
				wp_reset_query();
			}
		}
	}
}
?>
