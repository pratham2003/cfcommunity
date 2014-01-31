=== BP Disable Activation Reloaded ===
Contributors: timersys
Donate link: http://www.timersys.com
Tags: BuddyPress, activation, WPMU
Requires at least: 2.9.2
Tested up to: 3.6
Site Wide Only: true
Stable tag: 1.0

Based on crashutah, apeatling plugin Disables the activation email and automatically activates new users in BuddyPress under a standard WP install and WPMU (multisite).  Also, automatically logs in the new user since the account is already active.

== Description ==

Based on crashutah, apeatling http://wordpress.org/plugins/bp-disable-activation/ Disables the activation email and automatically activates new users in BuddyPress under a standard WP install and WPMU (multisite).  Also, automatically logs in the new user since the account is already active. 

THIS IS BETA , please test it carefully as this plugin was created for a client and i haven't tested it deeply. So again, test this on a production site before making it live

Basically i updated the plugin and added some features like:

-Option to turn off automatic login
-Redirect options after account creation

Known Bugs:
-Doesn't do the automatic login if you allow blog creation during the user creation in WPMU (multisite)

== Installation ==

1. Upload the 'bp-disable-activation-reloaded' folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to the settings page

== Frequently Asked Questions ==

= Won't this allow more spammers to get in? =

Of course it could.  So, you should consider using other plugins and methods for preventing spammers from signing up on your site.  However, many people have seen spammers get through just fine even with email activation enabled.  Plus, some sites are designed so that email activation doesn't matter.  Thus the plugin.

= What if I don't want my users to automatically login? =

Why don't you?  Users will love that feature.  I'll look at adding an option to turn this on/off.  Until then you can comment out those lines if you don't want it.

== Changelog ==

= 1.0 =

* First release