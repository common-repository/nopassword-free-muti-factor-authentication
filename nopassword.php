<?php
/*
* Plugin Name: NoPassword - Free Muti Factor Authentication
* Version: 1.0.1
* Description: NoPassword - Free and Secure Muti Factor Authentication based on Biometric
* Author: NoPassword
* Author URI: https://www2.nopassword.com/
* Text Domain: nopassword
*/
if (!defined('ABSPATH')) exit;
define( 'PLUGIN_DIR', dirname(__FILE__));

// Get plugin settings from WP
$APIKey_value = esc_attr(get_option('NPoption_APIKey'));
$APIEndpoint_value = esc_attr(get_option('NPoption_APIEndpoint'));
$auto_provision_value = get_option('NPoption_auto_provisionAPI');
$default_role_value = esc_attr(get_option('NPoption_default_roleAPI'));
$password_troubleshoot_value = get_option('NPoption_password_troubleshootAPI');
$compact_screen_value = intval(esc_attr(get_option('NPoption_compact_screenAPI')));
$configured = true;

// If API info is missing
if ($APIKey_value === '' || $APIEndpoint_value === '' || ($auto_provision_value != 'true' && $auto_provision_value != 'false') || ($compact_screen_value != 'true' && $compact_screen_value != 'false')) {
  add_action( 'admin_notices', function(){
    ?>
    <div class="notice error">
      <p><?php _e( 'NoPassword plugin is not set up correctly', 'sample-text-domain' ); ?></p>
    </div>
    <?php
  });
  $configured = false;
}



function NOPASS_action_login_head(){ // Adding custom styles and scripts to admin login header
  $compact_screen_value = esc_attr(get_option('NPoption_compact_screenAPI'));

  wp_enqueue_script('nopass_general_script', plugins_url('js/script.js', __FILE__), array('jquery'));
  wp_localize_script( 'nopass_general_script', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' )));
  wp_enqueue_script('jquery_cookie', plugins_url('js/jquery-cookie.js', __FILE__), array('jquery'));
  wp_enqueue_script('nopass_popup_script', plugins_url('js/popup.js', __FILE__), array('jquery'));
  wp_enqueue_script('nopass_circle_progress', plugins_url('js/circle-progress.js', __FILE__), array('jquery'));
  wp_enqueue_style('nopass_login_style', plugins_url('css/login_style.css', __FILE__));


  if($compact_screen_value == 'true') wp_enqueue_script('nopass_compact_screen_script', plugins_url('js/compact.js', __FILE__), array('jquery'));
  else wp_enqueue_script('nopass_fullscreen_script', plugins_url('js/fullscreen.js', __FILE__), array('jquery'));

  if (is_user_logged_in()) { // redirect user to admin if logged in
    wp_add_inline_script('nopass_general_script', 'window.location.replace("./wp-admin")', 'before');
  }
}

function NOPASS_action_password_reset(){
  wp_enqueue_script('jquery_cookie', plugins_url('js/jquery-cookie.js', __FILE__), array('jquery'));
  wp_enqueue_script('nopass_lostpassword_script', plugins_url('js/lostpassword.js', __FILE__), array('jquery'));

}

function NOPASS_action_login_message($message){ // Adding the custom login form to the WP login page
  $password_troubleshoot_value = get_option('NPoption_password_troubleshootAPI');
  $compact_screen_value = esc_attr(get_option('NPoption_compact_screenAPI'));

  $createUserPopup = '';
  $createUserPopup .= '<div class="NPCreateUserWait" id="NPCreateUserWait"></div>';
  $createUserPopup .= '<div class="NPCreateUserError" id="NPCreateUserError">';
  $createUserPopup .= '<svg data-filename="warning-icon1.svg" version="1.1" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 29 26" style="enable-background: new 0 0 29 26;" xml:space="preserve"><g><path d="M14.5,8.7c-0.5,0-0.8,0.4-0.8,0.9v6c0,0.5,0.4,0.9,0.8,0.9c0.5,0,0.8-0.4,0.8-0.9v-6C15.3,9.1,15,8.7,14.5,8.7
		L14.5,8.7z" style="fill: #F6506E;"></path><g><path d="M15,19.2c-0.4-0.4-0.8-0.4-1.2,0c-0.1,0.1-0.1,0.5-0.1,0.6c0,0.4,0,0.5,0.1,0.6s0.5,0.1,0.7,0.1
			c0.1,0,0.5,0,0.4-0.1c0.1-0.1,0.4-0.5,0.4-0.6C15.2,19.6,15.2,19.5,15,19.2L15,19.2z" style="fill: #F6506E;"></path><path d="M28.4,19.5L18.2,2.2C17.5,0.9,16.1,0,14.5,0s-2.9,0.9-3.7,2.2L0.6,19.5c-0.8,1.4-0.8,3,0,4.3
			C1.4,25.1,2.8,26,4.4,26h20.3c1.7,0,3-0.9,3.7-2.2C29.2,22.4,29.2,20.7,28.4,19.5L28.4,19.5z M26.9,23c-0.4,0.9-1.2,1.4-2.2,1.4
			H4.5c-0.8,0-1.7-0.5-2.2-1.4c-0.6-0.7-0.6-1.7-0.1-2.6L12.3,3.2c0.4-0.9,1.2-1.4,2.2-1.4s1.9,0.5,2.4,1.4L27,20.5
			C27.5,21.3,27.5,22.3,26.9,23L26.9,23z" style="fill: #F6506E;"></path></g></g></svg>';
  $createUserPopup .= '<h1>Something went wrong!</h1>';
  $createUserPopup .= '<p>We couldn\'t create you a NoPassword account. Please login using your password.</p>';
  $createUserPopup .= '<p class="npButton button-blue" onclick="popup.toggle()" ><a href="#"><span>Continue</span></a></p>';
  $createUserPopup .= '</div>';
  $createUserPopup .= '<div class="NPSuccess" id="NPSuccess">';
  $createUserPopup .= '<svg data-filename="check.svg" version="1.1" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 40 40" style="enable-background: new 0 0 40 40;" xml:space="preserve"><g><g><path d="M20,0C9,0,0,9,0,20s9,20,20,20s20-9,20-20S31,0,20,0z M20,37.4c-9.6,0-17.4-7.8-17.4-17.4
			c0-9.6,7.8-17.4,17.4-17.4S37.4,10.4,37.4,20C37.4,29.6,29.6,37.4,20,37.4z" style="fill: #00ADD0;"></path><path d="M27.2,13.5L16.9,23.8l-4.2-4.2c-0.5-0.5-1.3-0.5-1.9,0c-0.5,0.5-0.5,1.3,0,1.9l5.1,5.1
			c0.3,0.3,0.6,0.4,0.9,0.4c0.3,0,0.7-0.1,0.9-0.4c0,0,0,0,0,0l11.2-11.2c0.5-0.5,0.5-1.3,0-1.9C28.6,12.9,27.8,12.9,27.2,13.5z" style="fill: #00ADD0;"></path></g></g></svg>';
  $createUserPopup .= '<h1>Your NoPassword Account is ready</h1>';
  $createUserPopup .= '<p>Please check your email inbox and activate your NoPassword account. Next time you can use it to login.</p>';
  $createUserPopup .= '<p class="npButton button-blue" onclick="popup.toggle()" ><a href="#"><span>Continue</span></a></p>';
  $createUserPopup .= '</div>';
  $createUserPopup .= '<div class="NPCheckEmail" id="NPCheckEmail">';
  $createUserPopup .= '<svg data-filename="check.svg" version="1.1" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 40 40" style="enable-background: new 0 0 40 40;" xml:space="preserve"><g><g><path d="M20,0C9,0,0,9,0,20s9,20,20,20s20-9,20-20S31,0,20,0z M20,37.4c-9.6,0-17.4-7.8-17.4-17.4
			c0-9.6,7.8-17.4,17.4-17.4S37.4,10.4,37.4,20C37.4,29.6,29.6,37.4,20,37.4z" style="fill: #00ADD0;"></path><path d="M27.2,13.5L16.9,23.8l-4.2-4.2c-0.5-0.5-1.3-0.5-1.9,0c-0.5,0.5-0.5,1.3,0,1.9l5.1,5.1
			c0.3,0.3,0.6,0.4,0.9,0.4c0.3,0,0.7-0.1,0.9-0.4c0,0,0,0,0,0l11.2-11.2c0.5-0.5,0.5-1.3,0-1.9C28.6,12.9,27.8,12.9,27.2,13.5z" style="fill: #00ADD0;"></path></g></g></svg>';
  $createUserPopup .= '<h1>Please activate your NoPassword account</h1>';
  $createUserPopup .= '<p>Please check your email inbox and activate your NoPassword account. Next time you can use it to login.</p>';
  $createUserPopup .= '<p class="npButton button-blue" onclick="popup.toggle()" ><a href="#"><span>Continue</span></a></p>';
  $createUserPopup .= '</div>';
  $createUserPopup .= '<div class="npDialog">';
  $createUserPopup .= '<h1>Create a NoPassword account</h1>';
  $createUserPopup .= '<p class="createUserDescription">By creating a NoPassword account, you will be able to login to this site without using a password.</p>';
  $createUserPopup .= '<div class="line"></div>';
  $createUserPopup .= '<span class="inputSpan">Your email</span><br>';
  $createUserPopup .= '<input type="email" id="npNewUserEmail" class="text required" value="" readonly="true">';
  // $createUserPopup .= '<br><span class="inputSpan">First name</span><br>';
  // $createUserPopup .= '<input id="npNewFirstName" type="text"  class="text required" value="">';
  // $createUserPopup .= '<br><span class="inputSpan">Last name</span><br>';
  // $createUserPopup .= '<input id="npNewLastName" type="text"  class="text required" value="">';
  // $createUserPopup .= '<div class="checkboxWrapper"><input type="checkbox" id="dontAskAgain" value="true"><span>Don\'t ask me again</span></div>';
  $createUserPopup .= '<div class="buttons cf">';
  $createUserPopup .= '<p class="npButton button-grey" id="closePopup"><a href="#"/><span>Cancel</span></a></p>';
  $createUserPopup .= '<p class="npButton button-blue" id="npCreateUserButton" ><a onclick="createuser()" href="#"><span>Create account</span></a></p>';
  $createUserPopup .= '</div>';
  $createUserPopup .= '</div>';
  $createUserPopup .= '</div>';

  if ($compact_screen_value == 'false'){
    $loginScreen = '';
    $loginScreen .= '<div id="createUserPopup" class="fullscreen">';
    $loginScreen .= $createUserPopup;
    $loginScreen .= '<div class="loginScreenPopUpWrapper login-screen-popup-opened">';
    $loginScreen .= '<div class="lsFirst"></div>';
    $loginScreen .= '<div class="lsAwaiting">';
    $loginScreen .= '<img src="'.plugins_url( 'assets/check_icon.png', __FILE__ ).'" width="52" class="check"/>';
    $loginScreen .= '<p class="npTitle">Confirm the request on NoPassword App</p>';
    $loginScreen .= '<div class="phoneWrapper"><div class="phone">';
    $loginScreen .= '<img src="'.plugins_url( 'assets/phone.png', __FILE__ ).'" class="phoneWrapper"/>';
    $loginScreen .= '<img src="'.plugins_url( 'assets/phone_bg.png', __FILE__ ).'" class="phoneBg"/>';
    $loginScreen .= '<span class="progress"></span>';
    $loginScreen .= '<span class="timer"><span class="inner"></span></span>';
    $loginScreen .= '</div></div>';
    $loginScreen .= '<p class="npButton button-carnation button-cancel">';
    $loginScreen .= '<a href="#cancel">';
    $loginScreen .= '<span>Cancel</span>';
    $loginScreen .= '</a>';
    $loginScreen .= '</p>';
    $loginScreen .= '</div>';
    $loginScreen .= '<div class="rsWrapper"><div class="rsInner">';
    $loginScreen .= '<div class="lScreenPopupWrapper">';
    $loginScreen .= '<img src="'.plugins_url( 'assets/logo-blue.svg', __FILE__ ).'" alt="NoPassword">';
    $loginScreen .= '<div class="lScreenPopup cf">';
    $loginScreen .= '<p class="npTitle" id="npTitle">Login with NoPassword account</p>';
    $loginScreen .= '<div class="inputWrapper">';
    $loginScreen .= '<input id="npMail" type="email" name="login_user_name" class="text required"  value="'.$_COOKIE['NPuserLastEmail'].'">';
    $loginScreen .= '</div>';
    $loginScreen .= '<div class="buttons cf">';
    $loginScreen .= '<p class="npButton button-grey back"><a href=".."/><span>Back</span></a></p>';
    $loginScreen .= '<p class="npButton button-blue login"><a onclick="loginclick()" id="npLoginButton" href="#login"><span>Login</span></a></p>';
    $loginScreen .= '</div>';
    $loginScreen .= '<p class="hint nphint"><a href="https://nopassword.com/Login/LoginProblem.aspx" target="_blank"><span>Have trouble logging in?</span></a></p>';
    $loginScreen .= '<p class="hint passwordhint"><a href="'.$password_troubleshoot_value.'" target="_blank"><span>Have trouble logging in?</span></a></p>';
    $loginScreen .= '<p class="hint" id="createUser"><a onclick="popup.toggle()" href="#"><span>Want to login without password?</span></a></p>';
    $loginScreen .= '<p id="npError" class="error"></p>';
    $loginScreen .= '</div>';
    $loginScreen .= '</div>';
    $loginScreen .= '</div></div>';
    $loginScreen .= '</div>';
  }
  else {
    $loginScreen = '';
    $loginScreen .= '<div id="createUserPopup">';
    $loginScreen .= $createUserPopup;
    $loginScreen .= '<div class="compact"><div id="npError">a</div>';
    $loginScreen .= '<div class="loginScreenCompactWrapper">';
    $loginScreen .= '<div id="avaiting" class="compactAwaiting">';
    $loginScreen .= '<div class="phoneWrapper"><div class="phone">';
    $loginScreen .= '<img src="'.plugins_url( 'assets/phone.png', __FILE__ ).'" class="phoneWrapper"/>';
    $loginScreen .= '<img src="'.plugins_url( 'assets/phone_bg.png', __FILE__ ).'" class="phoneBg"/>';
    $loginScreen .= '<span class="progress"></span>';
    $loginScreen .= '<span class="timer"><span class="inner"></span></span>';
    $loginScreen .= '</div></div></div>';
    $loginScreen .= '<h1 id="npTitle">Login with NoPassword</h1>';
    $loginScreen .= '<input id="npMail" type="email" name="login_user_name" placeholder="Enter your email" class="text required" value="'.$_COOKIE['NPuserLastEmail'].'">';
    $loginScreen .= '<p class="npButton button-blue login"><a onclick="loginclick()" id="npLoginButton" href="#login"><span>Login</span></a></p>';
    $loginScreen .= '<p class="hint nphint"><a href="https://nopassword.com/Login/LoginProblem.aspx" target="_blank"><span>Trouble signing in?</span></a></p>';
    $loginScreen .= '<p class="hint passwordhint"><a href="'.$password_troubleshoot_value.'" target="_blank"><span>Trouble signing in?</span></a></p>';
    $loginScreen .= '<p class="hint" id="createUser"><a onclick="popup.toggle()" href="#"><span>Want to login without password?</span></a></p>';

    $loginScreen .= '</div></div>';
  }

  echo $loginScreen;
  return $message;
}

function NOPASS_delete_logout_message(){ // Remove default messages from login page
  return null;
}

function NOPASS_member_redirect($redirect, $request, $user){ // allow only admin
  global $user;
  if(isset($user->roles) && is_array($user->roles)){
    if(in_array("administrator", $user->roles))
    return admin_url('index.php');
    else{
      wp_logout();
      return "../wp-login.php";
    }
  }
  else{
    return home_url();
  }
}

if ($_POST['wp-submit'] || $_GET['passwordlogin'] == 'true') { // If the user is trying to login with password. Without trying NP authentication. Only ADMIN is alowed.
  add_filter("login_redirect", "NOPASS_member_redirect", 10, 3);
}
elseif ($configured && !$_GET['passwordlogin'] == 'true' && !$_GET['action'] == 'resetpassword' && !$_GET['checkemail'] == 'confirm' && (!$_GET['action'] == 'rp' || !$_GET['action'] == 'lostpassword') ) { // Adding event hooks if the plugin is correctly configured.
  add_action( 'login_enqueue_scripts', 'NOPASS_action_login_head');
  add_action( 'login_message', 'NOPASS_action_login_message');
  add_filter( 'wp_login_errors', 'NOPASS_delete_logout_message');
}
elseif ($_GET['action'] == 'lostpassword') {
  add_action( 'login_enqueue_scripts', 'NOPASS_action_password_reset');
}

// Adding NoPassword to admin menu
add_action('admin_menu', function(){
  add_submenu_page('options-general.php', 'NoPassword Plugin', 'NoPassword', 'manage_options', 'nopassword_plugin', 'admin_page');
});

// NP plugin admin page
function admin_page(){
  if ( !current_user_can( 'manage_options' ))  {
    wp_die( __( 'You do not have permissions to access this page.' ) );
  }

  include("admin-page.php");
}

// Create data in database for plugin
add_action( 'admin_init', function() {
  register_setting( 'nopasswordAPI-settings', 'NPoption_APIKey' );
  register_setting( 'nopasswordAPI-settings', 'NPoption_APIEndpoint' );
  register_setting( 'nopasswordAPI-settings', 'NPoption_auto_provisionAPI' );
  register_setting( 'nopasswordAPI-settings', 'NPoption_default_roleAPI' );
  register_setting( 'nopasswordAPI-settings', 'NPoption_password_troubleshootAPI' );
  register_setting( 'nopasswordAPI-settings', 'NPoption_compact_screenAPI' );
  // register_setting( 'nopasswordAPI-settings', 'NPoption_logoAPI' );
});

// Delete plugin data from database if plugin is uninstalled
register_uninstall_hook( __FILE__, 'NOPASS_uninstall' );
function NOPASS_uninstall() {
  $NPoption_names = array('NPoption_APIKey', 'NPoption_APIEndpoint', 'NPoption_auto_provisionAPI', 'NPoption_default_roleAPI', 'NPoption_password_troubleshootAPI', 'NPoption_compact_screenAPI');

  foreach ($NPoption_names as $option) {
    delete_option($option);
    delete_site_option($option);
  }
}

if($configured){
  include(PLUGIN_DIR.'/inc/functions.php'); // Include all the functions
  include(PLUGIN_DIR.'/inc/nopassword-auth.php'); // Nopassword authentication
  include(PLUGIN_DIR.'/inc/create-user.php'); // File for creating user
  include(PLUGIN_DIR.'/inc/password-login.php'); // File for password login
}

// Actions for ajax requests
add_action('wp_ajax_nopass_auth', 'NOPASS_auth_user');
add_action('wp_ajax_nopriv_nopass_auth', 'NOPASS_auth_user');
add_action('wp_ajax_nopass_create_user', 'NOPASS_create_user');
add_action('wp_ajax_nopriv_nopass_create_user', 'NOPASS_create_user');
add_action('wp_ajax_nopass_password_login', 'NOPASS_password_login');
add_action('wp_ajax_nopriv_nopass_password_login', 'NOPASS_password_login');
