<?php

/* Create a NoPassword account if the user already has a WordPress Account */

if (!defined('ABSPATH')) exit;

function NOPASS_create_user(){

  if (!empty($_POST['email']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
    $response = [];

    $APIKey = get_option('NPoption_APIKey');
    $endpoint = get_option('NPoption_APIEndpoint').'/auth/login';
    $email = sanitize_email($_POST['email']);

    $user = get_user_by( 'email', $email );
    $firstname= $user->first_name;
    $lastname= $user->last_name;

    if($firstname == ""){
      $firstname = explode("@", $email)[0];
    }
    if($lastname == ""){
      $lastname = $firstname;
    }

    $data_array =  array(
      "APIKey" => $APIKey,
      "Username" => $email,
      "IPAddress" => NOPASS_getClientIP(),
      "FirstName" => $firstname,
      "LastName" => $lastname,
      "BrowserId" => NOPASS_getBrowserID(),
      "DeviceName" => NOPASS_getBrowser() . ", " . NOPASS_getOS()
    );

    if(email_exists($email)){// Check if user exists in WP

      $result = NOPASS_callAPI($endpoint, json_encode($data_array));
      $resultArr = json_decode($result, true);

      if($resultArr['Succeeded'] && $resultArr['Value']['AuthStatus'] = "UnpairedUser"){
          $response['Status'] = 'Success';
          $response['Message'] = 'Your NoPassword account is ready to use';
      }
      else{
        $response['Status'] = 'Fail';
        $response['Message'] = 'Can\'t create NoPassword account';
      }

    }
    else{
      $response['Status'] = 'Fail';
      $response['Message'] = 'User does not exists in WordPress';
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
