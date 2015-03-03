=== Plugin Name ===
Plugin Name: Cooked Recipe Plugin
Plugin URI: http://cookedwp.com
Tags: recipes, cooked, food, cooking
Author URI: http://www.boxystudio.com
Author: Boxy Studio
Donate link: http://www.boxystudio.com/#coffee
Requires at least: 4.0
Tested up to: 4.1

== Description ==

Check the changelog tab for a list of the latest updates.

== Changelog ==

= 2.0.3 =
* *FIX:* Did some CSS reworking to hopefully fix some stylistic issues with certain themes.
* *FIX:* Fixed the video lightbox sizing issue.

= 2.0.2 =
* *FIX:* Removed "Total Time" to display both the "Prep" and "Cook" times instead.
* *FIX:* Added "Yields:" text next to the "Yields" value.
* *FIX:* Fixed an issue where recipe meta values would get deleted in Quick Edit mode.
* *FIX:* Adjusted the size of the rating stars to fit better on all screen sizes.

= 2.0.1 =
* *FIX:* Fixed a profile link issue with usernames containing a period and/or underscore.
* *FIX:* Fixed an issue with the max cook/prep time sliders in the Settings panel.
* *FIX:* Fixed an issue where child themes would not display the chosen recipe template properly.
* *FIX:* Fixed some styling issues with the Pending list.
* *FIX:* Fixed an issue with the Pending count showing up in the wrong spot.
* *FIX:* Fixed an issue with direction images getting stretched.

= 2.0.0 =
**Cooked 2.0 is here!**
This is a huge update! Check out the list of new features and fixes below:

* **NEW:** Added a **"Detailed Entry"** mode to both the ingredients and recipe directions for admin-created recipes. Includes the much requested "Image Per Direction" feature!
* **NEW:** The **Recipe RSS Feed** got a *major* overhaul. It now includes the image, serving size, short description, ingredients, directions and additional notes.
* **NEW:** Drastically improved the **Recipe Search Capabilities**. It is *much* faster and more accurate now.
* **NEW:** Added a **custom email template** with the ability to customize the email header image/content.
* **NEW:** Added a **"Pending Recipes"** page to quickly view and approve/delete incoming recipes.
* **NEW:** Added a **"Recipe Limit"** to limit the number of recipes a user can submit.
* **NEW:** Added an **"Auto-Publish"** option to allow users to submit recipes that are automatically published to the site.
* **NEW:** Added support for the **"Really Simple CAPTCHA"** plugin to add captchas to registration and recipe submission forms. Just install and activate!
* **NEW:** Updated the registration form to simply ask for "First Name", "Last Name" and "Email". It now sets up an account and sends the user a welcome email.
* **NEW:** Added an option to change the category, cuisine and cooking method slugs.
* **NEW:** Added an option to make the star rating optional.
* **NEW:** Added new attributes to the [cooked-browse] shortcode to filter by category, cuisine and/or cooking method. You can set the sort order as well!
* *FIX:* Admins can now set ratings to zero again (if Admin Reviews is turned on).
* *FIX:* The "Receipe Excerpt" is now saved as the "post_excerpt" for full excerpt support (with RSS feeds and other plugins, etc.)
* *FIX:* Fixed an issue where the "Display Name" wasn't showing properly in comments, etc.
* *FIX:* Fixed an issue when a user registers with a space in the username, it breaks the profile page.
* *FIX:* Recipe image now shows up in print view.

= 1.4.6 =
* *FIX:* Fixed an issue where in rare cases a user's profile link wouldn't work.
* *FIX:* Fixed an issue with the "Print" button not working in Cooked shortcodes.

= 1.4.5 =
* *FIX:* Fixed a registration issue from 1.4.4, sorry about that!

= 1.4.4 =
* **NEW:** Moved recipe images to use Featured Image instead of a custom image field.
* **NEW:** Added thumbnails to Recipe post list in the admin.
* **NEW:** Added an "Excerpt" field (replaces the "Short Description" on the list views if entered)
* *FIX:* Added more missing translations.
* *FIX:* Fixed login redirect with invalid credentials.
* Much bigger updates coming soon!

= 1.4.3 =
* *FIX:* Fixed a pagination issue on the archive pages.

= 1.4.2 =
* *FIX:* Hide the "Edit Recipe" block on print screen
* *FIX:* Now showing empty categories, cuisines, and cooking methods as options in the submit recipe form.
* *FIX:* Made the cook times NOT required in the submit recipe form.

= 1.4.1 =
* **NEW:** New "horizontal" recipe card style shortcode (add style="horizontal" to the shortcode).
* *FIX:* Private recipes are now hidden from widgets.
* *FIX:* Fixed an issue where the search box would not work properly when two of them were on one page.
* *FIX:* Moved the customization CSS files to the uploads folder so the plugin files don't need to be writeable anymore.
* *FIX:* Fixed the pagination on Recipe archives (category, cuisine, cooking method).

= 1.4 =
* **NEW:** Users can now edit their own recipes.
* **NEW:** Users can now make their own recipes "public" or "private".
* **NEW:** Added custom upload field styling.
* **NEW:** Added a [cooked-recipe-card id=1234] shortcode for displaying a recipe card anywhere.
* **NEW:** Added a "stacked" option to the [cooked-search style="stacked"] shortcode option to show a stacked search box.
* **NEW:** Added a new widget to display the recipe search box (in stacked style).
* **NEW:** Re-coded register form to include a password field.
* **NEW:** Removed the color scheme functionality and replaced it with WordPress color pickers.
* **NEW:** If you choose to hide certain elements, those related fields will now also be hidden on the front-end submit form.
* **NEW:** Moved reviews above the review comment box instead of below.
* **NEW:** Reviewer names are now linked to their profile (if they are a registered member and a profile page is selected in the settings).
* *FIX:* Fixed messy dropdown arrows on iOS devices.
* *FIX:* Made the review avatars match the custom user avatar (if one is available).

= 1.3.1 =
* *FIX:* Fixed an issue where the taxonomies wouldn't get saved from a front-end submission.
* *FIX:* Fixed an issue where the Nutrition Facts were overlayed in print-mode.

= 1.3 =
* **NEW:** Added the [cooked-search] shortcode to display the recipe search box anywhere you need it.
* **NEW:** Added the [cooked-directory] shortcode. You can now display a list of all users that shows number of recipes, favorites, reviews, etc.
* **NEW:** Users can now update their avatar from the Edit Profile tab.
* **NEW:** You can choose to show the author's avatar next to their name on recipes.
* **NEW:** The front-end submission form now allows more than one selection for category, cuisine and cooking methods.
* **NEW:** Added default "blank" images for recipes without images.
* **NEW:** Print button now opens in a new window for better printing.
* **NEW:** Added two new recipe layouts for page templates with sidebars.
* *FIX:* Added a few missing translations.

= 1.2.7 =
* FIXED: An issue where in some themes (including Basil), the recipe image would disappear at certain widths.
* Many more updates coming in v1.3, this is just a minor release. :)

= 1.2.5 =
* **NEW:** Added options to set the max times for Prep and Cook time sliders.
* FIXED: You can now use ANY page slug for the Profile page (not just /profile/).
* FIXED: Masonry layouts now work in areas less than 900px.
* FIXED: A small checkbox issue in the Settings screen.

= 1.2 =
* **NEW:** Added some widgets! Single Recipe and Recipe List with sorting options.
* **NEW:** Added Difficulty Levels (Beginner, Intermediate, and Advanced)
* **NEW:** Added the ability to hide/show Category, Cuisine, and/or Cooking Method
* **NEW:** Added a field for "Additional Recipe Notes" for displaying sources, cooking tips, etc.
* **NEW:** Added shortcodes to display the Recipe Browser, Profile and a Single Recipe.
* **NEW:** Added the option to make recipe actions (print, favorite, full-screen) "Premium" features, which means the user is required to be logged in to use them.
* **NEW:** Added WooCommerce integration. If you have WooCommerce installed and active, the "My Account" page content will show up as a tab on the Profile template.
* **NEW:** Added recipe tag support.

= 1.1.03 =
* More bug fixes related to commenting

= 1.1.02 =
* Some quick bug fixes

= 1.1.0 =
* **NEW:** Front-End Submissions (includes a setting to choose the user roles who have access and a custom form for submissions).
* **NEW:** User Profiles that can display submitted recipes, recent reviews and a favorites list.
* **NEW:** An "Edit Profile" tab on the Profile page for logged in users to edit their name, email, website, bio, and update their password if needed.
* **NEW:** Added video support to recipes. Just add a video link and it will add a lightbox video popup icon to the recipe image.
* **NEW:** Added a [cooked-login] shortcode to display a login form. Great for the default Profile page.
* **NEW:** Added a “Nutrition Facts” block.
* **NEW:** Added options to toggle certain recipe information items like rating, timing, categories, etc.
* **NEW:** Added a bunch of Rich Snippet improvements. Your recipes will now show up with a photo, rating and more in search results!
* **NEW:** Added the ability to change the "recipe" slug in the URL to something else entirely.
* **NEW:** New Settings panel with tabbed interface.
* *FIX:* Fixed issue with the pagination when Browse Recipes view is set as the homepage.
* *FIX:* Fixed the update conflict with free "Cooked" plugin on WordPress.org, sorry about that!
* Minor stylistic updates throughout.

= 1.0.2 =
* Fixed issue where it would always show the recipe template on a single post

= 1.0.1 =
* Some quick bug fixes

= 1.0.0 =
* Initial Release!