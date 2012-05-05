<?php
/*
 Plugin Name: Wordpress Click2Client
 Plugin URI: https://github.com/smashcubed/wp-click2client
 Description: Allows theme developers to add click2client support to any post/page
 Version: 1.1.1
 Author: Alex Swan
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

define('WP_CLICK2CLIENT_VERSION', '1.1.1');
require_once 'Services/Twilio.php';

class Click2client {
    static $options = array('wpc2client_twilio_sid' => 'Twilio Account Sid',
                            'wpc2client_twilio_token' => 'Twilio Token',
                            'wpc2client_caller_id' => 'Caller ID',
						);
    static $helptext = array('wpc2client_twilio_sid' => 'You can retrieve this if you login to your account or signup at <a href="http://twilio.com">Twilio</a>',
                             'wpc2client_twilio_token' => 'This can be found on your Twilio account dashboard, <a href="http://twilio.com">Twilio</a>',
                             'wpc2client_caller_id' => 'Must be a valid outgoing caller id approved by your Twilio account.',
						 );
    function init() {
        global $wpdb;

        register_activation_hook(__FILE__, array('Click2client',
                                                 'create_settings'));

        add_action('admin_menu', array('Click2client',
                                       'admin_menu'));
    }

    function admin_menu() {
        
        add_options_page('Click2client Options',
                      'Click2Client',
                      8,
                      'Click2client-handle',
                      array('Click2client',
                            'options'));
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
		echo '<p>Step 3. Create a <a href="https://www.twilio.com/user/account/apps">Twilio App</a> and note its Application Sid (34 characters, starts with "AP")</p>';
		echo '<p>Step 4. Drop this code snippet below anywhere you want a click to client button, then replace ApplicationSid with the Application Sid from Step 3</p>';
		echo '<p class="code">'.htmlspecialchars('<?php wp_c2client("ApplicationSid"); ?>')."</p>";
		echo '<p>You may optionally change the title of the button by entering a second variable.  For example:</p>';
		echo '<p class="code">'.htmlspecialchars('<?php wp_c2client("ApplicationSid", "Call Now!!!1"); ?>')."</p>";
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
        
        add_option('wpc2client_caller_id', '',
                   'What to show up on your friends phone',
                   'yes');
    }

    function update_settings($settings) {
        foreach(self::$options as $option => $title) {
            update_option($option, $settings[$option]);
        }
    }
    
}

/* Wordpres Tag for click2client */
function wp_c2client($applicationSid, $Caption = "Call", $Digits = False) {
	// Click to client
	// Get a token using the AppId
	$capability = new Services_Twilio_Capability(get_option('wpc2client_twilio_sid'), get_option('wpc2client_twilio_token'));
	$capability->allowClientOutgoing($applicationSid);
	$token = $capability->generateToken();
	$callerId = get_option('wpc2client_caller_id');
    $c2c_id = "C2C".uniqid();
    echo "<div id='$c2c_id'>";
    echo "<button id='click2client-button'>$Caption</button>";
    if($Digits)	echo "<input id='$c2c-input' type='text' placeholder='digits' style='width:40px'/>";
    echo '</div>';
	echo <<<END
		<script type="text/javascript">
			var connection = ""
		    Twilio.Device.error(function (e) {
		        console.log(e.message + " for " + e.connection);
		    });
		    jQuery("#$c2c_id #click2client-button").click(function() {
                var self = this
				Twilio.Device.disconnectAll();
		    	if(this.innerHTML != 'Hangup'){
        		    // Set up with TOKEN, a string generated server-side
            		Twilio.Device.setup("$token");
					if (Twilio.Device.status() != "ready") return
        		    self.innerHTML = 'Hangup'
				    connection = Twilio.Device.connect({
				        From: "$callerId"
				    });
                    connection.disconnect(function(connection){
                        self.innerHTML = '$Caption';
                    })
				}
		    });
			jQuery("#$c2c-input").keyup(function(event){
                var self = this
				digit = self.value
				valid = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '*', '#']
				if (valid.indexOf(digit) != -1){
					if(connection != '') connection.sendDigits(digit)
				}
				self.value = ""
			})
		</script>		
END;
}

function wp_c2client_main() {
    return Click2client::init();
}

wp_c2client_main();
} // end class check
