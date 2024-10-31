<?php

/*
Authenticate the user using his NoPassword account and log him in to WP.
If the user does not have NP account, then he can create one.
*/
if (!defined('ABSPATH')) exit;

function NOPASS_auth_user(){
  if (!empty($_POST['email']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){ // Login user just by email
    $response = [];

    // Get the plugin settings
    $create_user = get_option('NPoption_auto_provisionAPI');
    $APIKey = get_option('NPoption_APIKey');
    $endpoint = get_option('NPoption_APIEndpoint');
    $default_role_value = esc_attr(get_option('NPoption_default_roleAPI'));

    $email = sanitize_email($_POST['email']);
    $userStatus = NOPASS_getUserStatus($email, $APIKey, $endpoint);

    if($userStatus == 'UnpairedUser'){
      // We call the API just to send him an activation email.
      $data_array =  array(
        "APIKey" => $APIKey,
        "Username" => $email,
        "IPAddress" => NOPASS_getClientIP(),
        "DeviceName" => NOPASS_getBrowser() . ", " . NOPASS_getOS(),
        "BrowserId" => $browserID
      );
      $result = NOPASS_callAPI($endpoint.'/auth/login', json_encode($data_array));
      $response['Status'] = 'Fail';
      $response['Message'] = 'User is not paired';
      if( email_exists( $email ) ){
        $response['Method'] = 'DefaultLogin';
        $response['Value'] = 'CheckEmail';
      }
    }
    elseif($userStatus == 'InvalidUser') {
      if( email_exists( $email ) ){
        $response['Status'] = 'Fail';
        $response['Method'] = 'DefaultLogin';
        $response['Value'] = 'CreateAccount';
        $response['Message'] = 'The user does not have a NoPassword account';
      }
      else{
        $response['Status'] = 'Fail';
        $response['Message'] = 'Invalid User';
      }
    }
    elseif($userStatus == 'Success'){
      $data_array =  array(
        "APIKey" => $APIKey,
        "Username" => $email,
        "IPAddress" => NOPASS_getClientIP(),
        "DeviceName" => NOPASS_getBrowser() . ", " . NOPASS_getOS(),
        "BrowserId" => $browserID
      );
      $result = NOPASS_callAPI($endpoint.'/auth/login', json_encode($data_array));
      $resultArr = json_decode($result, true);

      if($resultArr['Value']['AuthStatus'] == 'Success'){ // If the user succesfully authenticated himself on app

        if( email_exists( $email ) ){ // If the user has a WP account
          $user = get_user_by( 'email', $email );
          $uID = $user->ID;
          wp_set_current_user( $uID, $user->user_login );
          wp_set_auth_cookie( $uID, true );
          do_action( 'wp_login', $user->user_login );

          $response['Status'] = 'Success';
          $response['NewUser'] = false;
        }
        elseif($create_user == 'true'){ // If user doesn't have a WP account, but we create him a new one
          $userdata = array(
            'user_login'			=> $email,
            'user_pass'				=> wp_generate_password(),
            'user_email'			=> $email,
            'role'					=> $default_role_value ? $default_role_value : 'subscriber'
          );
          $user_id = wp_insert_user( $userdata ) ;
          $user = get_user_by( 'id', $user_id );
          $uID = $user->ID;
          wp_set_current_user( $uID, $user->user_login );
          wp_set_auth_cookie( $uID, true );
          do_action( 'wp_login', $user->user_login );

          $response['Status'] = "Success";
          $response['NewUser'] = true;
        }
        else{ // We can't create a new account for the user
          $response['Status'] = "Fail";
          $response['Message'] = 'User does not exists';
        }
      }
      elseif($resultArr['Value']['AuthStatus'] == 'InvalidUser'){
        if( email_exists( $email ) ){
          $response['Status'] = 'Fail';
          $response['Method'] = 'DefaultLogin';
          $response['Value'] = 'CreateAccount';
          $response['Message'] = 'The user does not have a NoPassword account';
        }
        else{
          $response['Status'] = 'Fail';
          $response['Message'] = 'Invalid User';
        }
      }
      elseif($resultArr['Value']['AuthStatus'] == 'UnpairedUser'){
        $response['Status'] = 'Fail';
        $response['Method'] = 'DefaultLogin';
        $response['Value'] = 'CheckEmail';
        $response['Message'] = 'User is not paired';
      }
      elseif($resultArr['Value']['AuthStatus'] == "LockedUser"){
        $response['Status'] = "Fail";
        $response['Message'] = "Invalid User";
      }
      elseif($resultArr['Value']['AuthStatus'] == "Alert"){
        $response['Status'] = "Fail";
        $response['Message'] = "Access is denied. Please try again!";
      }
      elseif($resultArr['Value']['AuthStatus'] == "Denied"){
        $response['Status'] = "Fail";
        $response['Message'] = "Access is denied. Please try again!";
      }
      elseif($resultArr['Value']['AuthStatus'] == "Denied by Geofencing"){
        $response['Status'] = "Fail";
        $response['Message'] = "Access is denied by Geofencing";
      }
      elseif($resultArr['Value']['AuthStatus'] == "Denied by Policy"){
        $response['Status'] = "Fail";
        $response['Message'] = "Access is denied by Policy";
      }
      elseif($resultArr['Value']['AuthStatus'] == "No Location"){
        $response['Status'] = "Fail";
        $response['Message'] = "Access is denied. Please try again!";
      }
      elseif($resultArr['Value']['AuthStatus'] == "NoResponse"){
        $response['Status'] = "Fail";
        $response['Message'] = "We didn't get your confirmation. Try again!";
      }
      elseif($resultArr['Value']['AuthStatus'] == "LogError"){
        $response['Status'] = "Fail";
        $response['Message'] = "Access is denied. Please try again!";
      }
      elseif($resultArr['Value']['AuthStatus'] == "BadRequestError"){
        $response['Status'] = "Fail";
        $response['Message'] = "Access is denied. Please try again!";
      }
      elseif($resultArr['Message']){
        $response['Status'] = "Fail";
        $response['Message'] = $resultArr['Message'];
      }
      else{
        $response['Status'] = "Fail";
        $response['Error'] = "Unknown error";
      }
    }
    else{
      $response['Status'] = 'Fail';
      $response['Method'] = 'DefaultLogin';
      $response['Message'] = 'Unknown error';
    }
  }
  else {
    $response['Status'] = "Fail";
    $response['Error'] = "Permission denied";
  }

  echo json_encode($response);

  die();
}

?>
