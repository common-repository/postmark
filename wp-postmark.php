<?php
/**
 * @package Postmark
 * @version 1.0
 */
/*
Plugin Name: Postmark
Plugin URI: http://www.cracklecat.com/wordpress/postmark
Description: Uses the Postmark API to send email
Version: 1.0
Author: Crackle Cat Software
Author URI: http://www.cracklecat.com/
*/

include("Postmark.php");
$postmark_error = "";

add_action('admin_menu', 'postmark_plugin_menu');

function postmark_plugin_menu() {
    add_options_page('Postmark', 'Postmark', 'manage_options', 'postmark', 'postmark_plugin_options');
}

function postmark_plugin_options() {

    if (!current_user_can('manage_options'))  {
        wp_die( __('You do not have sufficient permissions to access this page.') );
    }
  
    $postmark_apikey = get_option('postmark_apikey');
    $postmark_fromname = get_option('postmark_fromname');
    $postmark_fromaddress = get_option('postmark_fromaddress');

    echo '<div class="wrap">';
    ?>
    <form name="postmark_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
      <input type="hidden" name="postmark_hidden" value="yes">
      <?php echo "<h4>" . __( 'Postmark Settings', 'postmark_trdom' ) . "</h4>"; ?>
      <p><?php _e("You need a <a href=\"http://www.postmarkapp.com\">Postmark</a> account to use this plugin." ); ?></p>
    
      <table style="width: 500px;">
        <tr>
          <td colspan="2">
            <p><?php _e("Enter your Postmark API key corresponding to your Postmark server that you want to use. You can retrieve your API key by logging in to your <a href=\"https://postmarkapp.com/login\">Postmark account</a>" ); ?></p>
          </td>
        </tr>
        <tr>
          <td><strong><?php _e("API Key: " ); ?></strong></td>
          <td>
            <input type="text" name="postmark_apikey" style="width: 200px;" value="<?php echo $postmark_apikey; ?>" size="20">
          </td>
    </tr>
    <tr>
      <td colspan="2">
        <p><?php _e("Enter an email address that matches one of your confirmed sender signature addresses. You can add signatures to your account <a href=\"https://postmarkapp.com/signatures\">here</a>" ); ?></p>
      </td>
    </tr>
    <tr>
      <td><strong><?php _e("From address: " ); ?></strong></td>
      <td>
        <input type="text" name="postmark_fromaddress" style="width: 200px;" value="<?php echo $postmark_fromaddress; ?>" size="20">
      </td>
    </tr>
    
    <tr>
      <td colspan="2">
      <p><?php _e("This should match the Sender Name of one of your confirmed sender signatures but anything will work here." ); ?></p>
      </td>
    </tr>
    <tr>
      <td><strong><?php _e("From name: " ); ?></strong></td>
      <td>
        <input type="text" name="postmark_fromname" style="width: 200px;" value="<?php echo $postmark_fromname; ?>" size="20">
      </td>
    </tr>
    
  </table>
    
    <p class="submit">
    <input type="submit" name="Submit" value="<?php _e('Save Settings', 'postmark_trdom' ) ?>" />
    </p>
</form>

<form name="postmark_form_test" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
  <input type="submit" name="postmark_test" value="<?php _e('Test', 'postmark_trdom' ) ?>" />
</form>
    <?php 
  
  echo '</div>';

}
if (!function_exists("wp_mail")) :
function wp_mail( $to, $subject, $message, $headers = '', $attachments = array() ) {
    global $postmark_error; 
    
    define('POSTMARKAPP_MAIL_FROM_NAME', get_option('postmark_fromname'));
    define('POSTMARKAPP_MAIL_FROM_ADDRESS', get_option('postmark_fromaddress'));
    define('POSTMARKAPP_API_KEY', get_option('postmark_apikey')); 
    
    $mail = new Mail_Postmark();
    $mail->messagePlain($message);
    
    $mail->to($to);
    $mail->subject($subject);
    
    try {
        $mail->send(); 
        
    } catch (Exception $e) {
        $postmark_error = $e->getMessage();
        //wp_die( __("An error occured trying to send mail through Postmark. Error: ".$e->getMessage()) );
        return false;
    }  
    $postmark_error = "";
    return true;
    
}
endif;

// handle options form
if($_POST['postmark_hidden'] == 'yes') {
    //Form data sent

    update_option('postmark_apikey', $_POST['postmark_apikey']);
    update_option('postmark_fromname', $_POST['postmark_fromname']);
    update_option('postmark_fromaddress', $_POST['postmark_fromaddress']);
        
    ?>
    <div class="updated"><p><strong><?php _e('Options saved.' ); ?></strong></p></div>
    <?php
} else {
    //Normal page display
    $postmark_apikey = get_option('postmark_apikey');
    $postmark_fromname = get_option('postmark_fromname');
    $postmark_fromaddress = get_option('postmark_fromaddress');
        
}

// handles test
if (isset($_POST['postmark_test'])) {
    if (wp_mail(get_option('postmark_fromaddress'),'Wordpress Postmark Plugin Test','If you are receiving this email then your Wordpress Postmark integration is working.') == false) {
        ?><div class="error"><p><strong><?php _e('Postmark Test: ' . $postmark_error  ); ?></strong></p></div><?php 
    } else {
        ?><div class="updated"><p><strong><?php _e('Test successful. A test message has been sent to: ' .get_option('postmark_fromaddress') ); ?></strong></p></div><?php

    }
}