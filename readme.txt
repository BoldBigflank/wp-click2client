=== Plugin Name ===
Contributors: boldbigflank
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=VNYQSLTNWQLT2
Tags: twilio, click2client, phone, voice, call, client
Requires at least: 2.8.0
Tested up to: 3.3.2
Stable tag: 1.1.1

Wordpress Click2Client adds in-browser phone call functionality to any wordpress site.  Just configure the control and drop the tag in your theme.

== Description ==

Wordpress Click2Client adds in-browser phone call functionality to any wordpress site.  Just configure the control and drop the tag in your theme.

== Installation ==
1. Download the zip or clone it from git.
2. Copy the wp-click2client into the `/wp-content/plugins/` directory
3. Activate the wp-click2client plugin.
4. A newly created menu option is created for Click2Client on the admin menu.  
Enter your Twilio Account information
5. Create a Twilio App at https://www.twilio.com/user/account/apps and note its Application Sid (34 characters, starts with "AP")
6. Adding Click2Client button: Drop this code snippet below anywhere you want a click to client button, then replace ApplicationSid with the Application Sid that you want the button to call

	`<?php wp_c2client("ApplicationSid"); ?>`

7. You may add a couple other parameters: choose the button text(string, default is "Call"), and show a box that allows users to input digits (boolean, default is FALSE).  For example:

	`<?php wp_c2client("ApplicationSid", "Button Text", TRUE); ?>`

Thats it!

== Screenshots ==

1. A couple buttons with custom text, one with a digits box, one without
2. Three settings are all the basic information you need

== Changelog ==

= 1.1.0 =
* Added the ability to hide the digits box

== Upgrade Notice ==

= 1.1.0 =
Added the ability to hide the digits box

== Frequently Asked Questions ==

= I don't see the button =
Make sure you have set up php execution in your wordpress (You may need to get the PHP Execution plugin)

= Where can I go for help or support? =
Twilio will gladly help you with any questions or comments you may have with twilio services.
Email: help@twilio.com

= Where do I get a twilio account =
http://twilio.com
