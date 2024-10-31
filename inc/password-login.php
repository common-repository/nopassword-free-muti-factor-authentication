<?php

/* Login the user using his email and password. */

if (!defined('ABSPATH')) exit;

function NOPASS_password_login(){

  if (!empty($_POST['email']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) && !empty($_POST['password'])){ // Login user using email and password
    $response = [];

    $email = sanitize_email($_POST['email']);
    $password = esc_attr($_POST['password']);

    if( email_exists( $email ) ){ // Check if user exists in WP

      $user = wp_authenticate( $email, $password );
      if( is_wp_error($user) ) {
        $response['Status'] = "Fail";
        $response['Message'] = "Wrong password";
      }
      else {
        $uID = $user->ID;
        wp_set_current_user( $uID, $user->user_login );
        wp_set_auth_cookie( $uID, true );
        do_action( 'wp_login', $user->user_login );

        $response['Status'] = "Success";
        $response['Message'] = "Succesfull login";
      }
    }
    else{
      $response['Status'] = 'Fail';
      $response['Message'] = 'User does not exists';
    }
  }
  else{
    $response['Status'] = 'Fail';
    $response['Message'] = 'Invalid request';
  }

  echo json_encode($response);
  die();
}
?>
