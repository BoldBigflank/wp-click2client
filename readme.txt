=== Plugin Name ===
Contributors: minddog
Donate link: http://twilio.com
Tags: twilio, click2client, phone, voice
Requires at least: 2.8.0
Tested up to: 3.3.2
Stable tag: 1.0.5

Wordpress Click2Client adds in-browser call functionality to any wordpress site.  Just configure the control and drop the tag in your theme.

== Description ==

Wordpress Click2Client adds in-browser call functionality to any wordpress site.  Just configure the control and drop the tag in your theme.

== Installation ==
1. Download the zip or clone it from git.
2. Copy the wp-click2client into the `/wp-content/plugins/` directory
3. Activate the wp-click2client plugin.
4. A newly created menu option is created for Click2Client on the admin menu.  
Enter your Twilio Account information
5. Adding Click2Client button:
First, create a Twilio App at https://www.twilio.com/user/account/apps and copy its Application Sid (34 characters, starts with "AP")
Drop this code snippet below anywhere you want a click to client button, then replace ApplicationSid with the Application Sid that you want the button to call
	<?php wp_c2client("ApplicationSid"); ?>

You may optionally change the title of the button by entering a second variable.  For example:
	<?php wp_c2client("ApplicationSid", "Call Now!!!1"); ?>

Thats it!

== Screenshots ==

1. Your very own click2client button.
2. Customize the click2client options.

== Frequently Asked Questions ==

= Where can I go for help or support? =
Twilio will gladly help you with any questions or comments you may have with twilio services.
Email: help@twilio.com

= Where do I get a twilio account =
http://twilio.com

