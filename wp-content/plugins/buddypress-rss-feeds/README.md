BuddyPress RSS Feeds
====================

BuddyPress RSS Feeds plugin gives ability to define a custom RSS feed that will be imported right in the global activity stream.

<<<<<<< HEAD
Users and groups can define their own unique RSS to display content from other sites.
=======
Users and groups can define their own unique RSS to display content from other sites. Plugin honors the date items were published in the external feed and import them on the same date in local activity stream.
>>>>>>> b8886a69bb4442e38487958ecd3d8138c30acf56

You can change the frequency of grabbing data, which is done only when someone opens the member or group RSS feed page. Otherwise on middle and large networks WP-Cron will hurt your site very quickly (requesting and saving feeds are quite expensive tasks).

Plugin requires:

* PHP 5.3 and above
* BuddyPress Settings Component activated (for members RSS feeds)
* BuddyPress Groups Component activated (for groups RSS feeds)
* Writable `/wp-content/uploads/` directory, that is properly registered if the path was changed (for storing images)
