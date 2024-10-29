<?php
/*
  Plugin Name: Amplify
  Plugin URI: http://www.betaout.com
  Description: Manage all your Wordpress sites and Editorial team from a single interface
  Version: 1.0
  Author: BetaOut (support@betaout.com)
  Author URI: http://www.betaout.com
  License: GPLv2 or later
 */

defined('AMPLIFY_HOST')
        || define('AMPLIFY_HOST', 'getamplify.com');

defined('AMPLIFY_VERSION')
        || define('AMPLIFY_VERSION', 'v1');

include_once 'includes/amplify.php';
include_once 'includes/amplifylogin.php';




//------------------------------------------------------------------------------
//the plugin will work function if cURL and add_function exist and the appropriate version of PHP is available.
$adminErrorMessage = "";

if (version_compare(PHP_VERSION, '5.2.0', '<')) {
    $adminErrorMessage .= "PHP 5.2 or newer not found!<br/>";
}

if (!function_exists("curl_init")) {
    $adminErrorMessage .= "cURL library was not found!<br/>";
}

if (!function_exists("session_start")) {
    $adminErrorMessage .= "Sessions are not enabled!<br/>";
}

if (!function_exists("json_decode")) {
    $adminErrorMessage .= "JSON was not enabled!<br/>";
}

if(!empty($adminErrorMessage)){
    add_action( 'admin_notices', '$adminErrorMessage' );
    exit;
}





add_action('init', array('AmplifyLogin','init'));
//add_action('init',  array('AmplifyLogin','amplify_tinymce_addbuttons'));
//add_action('admin_footer-post-new.php', array('AmplifyLogin','amplify_poll_footer_admin'));
//add_action('admin_footer-post.php', array('AmplifyLogin','amplify_poll_footer_admin'));
//add_action('admin_footer-page-new.php', array('AmplifyLogin','amplify_poll_footer_admin'));
//add_action('admin_footer-page.php', array('AmplifyLogin','amplify_poll_footer_admin'));
//add_filter('admin_footer_text', array('AmplifyLogin','amplify_add_poll_popup'));

add_action('wp_ajax_verify_key', 'verify_key_callback');

function verify_key_callback() {
          $amplifyApiKey = $_POST['amplifyApiKey'];
          $amplifyApiSecret=$_POST['amplifyApiSecret'];
          $amplifyProjectId=$_POST['amplifyProjectId'];
	  $AMPLIFYSDKObj = new Amplify($amplifyApiKey, $amplifyApiSecret, $amplifyProjectId, $debug);
          $curlResponse=$AMPLIFYSDKObj->verify();
         
           if ($curlResponse->responseCode=="200") {
               update_option("_AMPLIFY_API_KEY",$amplifyApiKey);
               update_option("_AMPLIFY_API_SECRET",$amplifyApiSecret);
               update_option("_AMPLIFY_PROJECT_ID",$amplifyProjectId);
              echo json_encode($curlResponse);
           }else{
               return false;
           }
	die(); // this is required to return a proper result
}





//add_action( 'plugins_loaded',array('AmplifyLogin','wpLogin'));
//
//add_action('comment_post', array('AmplifyLogin','commentTrack'));
//add_action('wp_ajax_personalogout', array('AmplifyLogin','personalogout'));
//add_action('wp_ajax_nopriv_personalogin',array('AmplifyLogin','personaajexlogin'));
//
//add_filter('login_form', array('AmplifyLogin','amplify_login_form'));
//add_action('register_form', array('AmplifyLogin','amplify_register_form'));
//
//register_activation_hook(__FILE__, 'Persona_UserDataManagement::myplugin_activate');
//register_deactivation_hook(__FILE__, 'Persona_UserDataManagement::myplugin_deactivate');
//register_uninstall_hook(__FILE__, 'Persona_UserDataManagement::myplugin_uninstall');
//
//if(get_option("_PERSONA_COMMENT")){
//add_filter('comments_template', array('persona_plugin','persona_comment_template'));
//}




//add_action('admin_bar_init', 'myfunction');


