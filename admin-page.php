<?php
  if (!defined('ABSPATH')) exit;
  $APIKey_value = esc_attr(get_option('NPoption_APIKey'));
  $APIEndpoint_value = esc_attr(get_option('NPoption_APIEndpoint'));
  $auto_provision_value = esc_attr(get_option('NPoption_auto_provisionAPI'));
  $default_role_value = esc_attr(get_option('NPoption_default_roleAPI'));
  $password_troubleshoot_value = esc_attr(get_option('NPoption_password_troubleshootAPI'));
  $compact_screen_value = esc_attr(get_option('NPoption_compact_screenAPI'));
  // $logo_value = get_option('NPoption_logoAPI');

?>

<div class="wrap">
  <h1>NoPassword API Authentication</h1>
  <p>If plugin is active administrators can still login with their password at <a href="<?php echo is_ssl() ? home_url('', 'https') : home_url('', 'http') ?>/wp-login.php?passwordlogin=true"><?php echo is_ssl() ? home_url('', 'https') : home_url('', 'http') ?>/wp-login.php?passwordlogin=true</a></p>
  <form action="options.php" method="post">
    <?php
    settings_fields( 'nopasswordAPI-settings' );
    do_settings_sections('nopasswordAPI-settings');
    ?>
    <table class="form-table">
        <tr valign="top">
          <th scope="row">API Key</th>
          <td>
            <input type="<?php echo ($APIKey_value == '') ? 'text' : 'password' ?>" class="regular-text" name="NPoption_APIKey" value="<?php echo $APIKey_value; ?>" />
          </td>
        </tr>

        <tr valign="top">
          <th scope="row">API Endpoint</th>
          <td><input type="text" class="regular-text" name="NPoption_APIEndpoint" value="<?php echo ($APIEndpoint_value == '') ? 'https://api.nopassword.com' : $APIEndpoint_value ?>" /></td>
        </tr>

        <tr valign="top">
          <th scope="row">Create a new user if identity is verified by NoPassword</th>
          <td>
            <p>
              <label><input name="NPoption_auto_provisionAPI" type="radio" value="true" <?php echo($auto_provision_value === 'true') ? 'checked' : 'unchecked' ?>> Yes</label>&nbsp;&nbsp;
              <label><input name="NPoption_auto_provisionAPI" type="radio" value="false" <?php echo($auto_provision_value === 'false' || $auto_provision_value == '') ? 'checked' : 'unchecked' ?>> No</label></p>
          </td>
        </tr>

        <tr valign="top">
          <th scope="row">New user default role</th>
          <td>
            <p>
              <select name="NPoption_default_roleAPI" id="default_role">
                <option <?php if($default_role_value == 'subscriber') echo 'selected="selected"' ?> value="subscriber">Subscriber</option>
                <option <?php if($default_role_value == 'contributor') echo 'selected="selected"' ?> value="contributor">Contributor</option>
                <option <?php if($default_role_value == 'author') echo 'selected="selected"' ?> value="author">Author</option>
                <option <?php if($default_role_value == 'editor') echo 'selected="selected"' ?> value="editor">Editor</option>
                <option <?php if($default_role_value == 'administrator') echo 'selected="selected"' ?> value="administrator">Administrator</option>
              </select>
          </td>
        </tr>

        <tr valign="top">
          <th scope="row">Password troubleshooting link</th>
          <?php
            if($password_troubleshoot_value != ''){
              echo '<td><input type="text" class="regular-text" name="NPoption_password_troubleshootAPI" value="'.$password_troubleshoot_value.'" /></td>';
            }
            else{
              echo '<td><input type="text" class="regular-text" name="NPoption_password_troubleshootAPI" value="'. home_url('', '') .'/wp-login.php?action=lostpassword" /></td>';
            }
          ?>

        </tr>

        <tr valign="top">
          <th scope="row">Compact Login screen</th>
          <td>
            <p>
              <label><input name="NPoption_compact_screenAPI" type="radio" value="true" <?php echo($compact_screen_value === 'true') ? 'checked' : 'unchecked' ?>> Enabled</label>&nbsp;&nbsp;
              <label><input name="NPoption_compact_screenAPI" type="radio" value="false" <?php echo($compact_screen_value === 'false' || $compact_screen_value == '') ? 'checked' : 'unchecked' ?>> Disabled</label></p>
          </td>
        </tr>


  </table>
  <?php submit_button(); ?>
  </form>
