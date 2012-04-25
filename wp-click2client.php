<?php
/*
 Plugin Name: Wordpress Click2Client
 Plugin URI: http://twilio.com
 Description: Allows theme developers to add click2client support to any post/page
 Version: 0.9.0
 Author: Alex Swan (original by Adam Ballai)
 Author URI: http://www.bold-it.com
*/

if (!function_exists('add_action')) {
    $wp_root = '../../..';
    if (file_exists($wp_root.'/wp-load.php')) {
        require_once($wp_root.'/wp-load.php');
    } else {
        require_once($wp_root.'/wp-config.php');
    }
}
    
if(!class_exists('Click2client')) {

define('WP_CLICK2CLIENT_VERSION', '1.0.4');
require_once 'Services/Twilio.php';

class Click2client {
    static $options = array('wpc2client_twilio_sid' => 'Twilio Account Sid',
                            'wpc2client_twilio_token' => 'Twilio Token',
                            'wpc2client_caller_id' => 'Caller ID',
                            'wpc2client_primary_phone' => 'Primary Phone To Call',
                            'wpc2client_primary_extension' => 'Primary Phone Extension (optional)',
                            'wpc2client_example_text' => 'Placeholder text',
                            'wpc2client_show_logo' => 'Show Twilio Logo',
                            'wpc2client_custom_greeting' => 'Custom greeting for caller',
                            );
    static $helptext = array('wpc2client_twilio_sid' => 'You can retrieve this if you login to your account or signup at <a href="http://twilio.com">Twilio</a>',
                             'wpc2client_twilio_token' => 'This can be found on your Twilio account dashboard, <a href="http://twilio.com">Twilio</a>',
                             'wpc2client_caller_id' => 'Must be a valid outgoing caller id approved by your Twilio account.',
                             'wpc2client_primary_phone' => 'What number do you want people to call you on?',
                             'wpc2client_primary_extension' => 'If you are behind an existing phone system, add your extension.',
                             'wpc2client_example_text' => 'This is the placholder text shown in your widget before someone starts typing their number.',
                             'wpc2client_show_logo' => 'Do you want to hide the Twilio logo?',
                             'wp2c2_custom_greeting' => 'Type in what you want the caller to be greeted with before connecting.',
                             );
    function init() {
        global $wpdb;

        register_activation_hook(__FILE__, array('Click2client',
                                                 'create_settings'));

        add_action('admin_menu', array('Click2client',
                                       'admin_menu'));

        add_action('wp_head', array('Click2client',
                                    'head_scripts'));
    }

    function dial($number) {
		
        $twilio = new Services_Twilio(get_option('wpc2client_twilio_sid'),
                                       get_option('wpc2client_twilio_token'),
                                       'https://api.twilio.com/2008-08-01');
        $phone = get_option('wpc2client_primary_phone');
        $ext = get_option('wpc2client_primary_extension');
        $connecting_url = plugins_url('wp-click2client/wp-click2client.php?ext='.urlencode($ext).'&connect_to='.urlencode($phone));
        
		$response = $twilio->account->calls->create($number, get_option('wpc2client_caller_id'), $connecting_url);

        $data = array('error' => false, 'message' => '');
        if($response->IsError) {
            $data['error'] = true;
            $data['message'] = $response->ErrorMessage;
        }

        echo json_encode($data);
    }

    function connect($number, $ext)
    {
        header('X-WP-Click2Client: '.WP_CLICK2CLIENT_VERSION);
        $twilio = new Services_Twilio_Twiml();
        $greeting = get_option('wpc2client_custom_greeting');
        if(!empty($greeting)) {
            $twilio->say($greeting);
        }
        $dial = $twilio->dial(NULL, array());
		$dial->number($number, array('sendDigits'=>$ext));
        echo $twilio; 
    }

    function admin_menu() {
        
        add_menu_page('Click2client Options',
                      'Click2client',
                      8,
                      __FILE__,
                      array('Click2client',
                            'options'));
    }

    function head_scripts() {
        wp_enqueue_script('wp-click2client', plugins_url('wp-click2client/click2client.js'), array('jquery', 'swfobject'), WP_CLICK2CLIENT_VERSION, true);
        wp_localize_script('wp-click2client', 'click2clientL10n', array(
                                                                    'plugin_url' => plugins_url('wp-click2client'),
                                                                    'exampletext' => htmlspecialchars(get_option('wpc2client_example_text')),
                                                                    'showlogo' => get_option('wpc2client_show_logo') == 'yes'? 1 : 0,
                                                                    ));
        wp_print_scripts('wp-click2client');
    }

    function options() {
        $message = '';
        if(!empty($_POST['submit'])) {
            self::update_settings($_POST);
            $message = "Settings have been updated";
        }
        echo '<div class="wrap">';
        echo '<h2>Click2Client</h2>';
        echo '<h3>'.$message.'</h3>';

        echo '<p>Step 1. To get started with setting up your click2client button, you first must get your Twilio Account Sid and Token.  Login or signup at <a href="http://twilio.com">twilio.com</a></p>';
        echo '<p>Step 2. Customize the settings below.</p>';
		echo '<p>Step 3. Drop this code snippet in your theme to create a click2client button</p>';
		echo '<p class="code">'.htmlspecialchars('<?php wp_c2client("AppID"); ?>')."</p>";
        echo '<form name="c2c-options" action="" method="post">';
        foreach(self::$options as $option => $title) {
            $value = get_option($option, '');
            $type = preg_match('/wpc2client_show_(.*)/', $option)? 'checkbox' : 'text';
            $checked = '';
            if($type == 'checkbox') {
                if($value == 'yes') {
                    $checked = 'checked="checked"';
                }
                
                $value = "yes";
            }
            echo '<div id="'.htmlspecialchars($option).'_div" class="stuffbox">';
            echo '<h3 style="margin:0; padding: 10px">';
            echo '<label for="'.htmlspecialchars($option).'">'.htmlspecialchars($title).'</label>';
            echo '</h3>';
            echo '<div class="inside" style="margin: 10px">';
            echo '<input id="'.htmlspecialchars($option).'" type="'.$type.'" name="'.htmlspecialchars($option).'" value="'.htmlspecialchars($value).'" '.$checked.' size="50"/>';
            echo '<p style="margin: 10px">'.self::$helptext[$option].'</p>';
            echo '</div>';
            echo '</div>';
                 
        }
        echo '<input type="submit" name="submit" value="Save" />';

        echo '</form>';
        echo '</div>';
    }
    
    function create_settings() {
        add_option('wpc2client_twilio_sid', '',
                   'Twilio Account Sid',
                   'yes');
        
        add_option('wpc2client_twilio_token', '',
                   'Twilio Account Token',
                   'yes');
        
        add_option('wpc2client_primary_phone', '',
                   'Primary phone number to call',
                   'yes');

        add_option('wpc2client_primary_extension', '',
                   'Primary phone extension to dial',
                   'yes');

        add_option('wpc2client_caller_id', '',
                   'What to show up on your friends phone',
                   'yes');

        add_option('wpc2client_example_text', '(415) 867 5309',
                   'Example text',
                   'yes');

        add_option('wpc2client_show_logo', 'yes',
                   'Show Twilio logo',
                   'yes');
    }

    function update_settings($settings) {
        foreach(self::$options as $option => $title) {
            update_option($option, $settings[$option]);
        }
    }
    
}

/* Wordpres Tag for click2client */
function wp_c2client() {
	
    $c2c_id = "C2C".uniqid();
    echo '<div id="'.$c2c_id.'" class="click2client"></div>';
}

function wp_c2client_main() {
    
    if(!empty($_REQUEST['caller']))
    {
        Click2client::dial($_REQUEST['caller']);
        exit;
    }
    
    if(!empty($_REQUEST['connect_to']))
    {
        Click2client::connect($_REQUEST['connect_to'], $_REQUEST['ext']);
        exit;
    }
    
    return Click2client::init();
}

wp_c2client_main();
} // end class check
