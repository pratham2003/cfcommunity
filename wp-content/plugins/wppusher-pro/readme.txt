=== WP Pusher ===
Tags: git, deploy, deployment, github, workflow
Requires at least: 3.9
Tested up to: 4.1
Stable tag: 1.0.7
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Deploy directly from Github and never again copy files over FTP. It works everywhere - even on cheap shared hosting!

== Description ==

= Features =

* Install and update your WordPress themes and plugins directly from GitHub
* BitBucket and GitLab support
* Easy version control of your clients code
* Works everywhere because it hooks in to the WordPress core auto updater
* No Git or SSH needed on the server

= PRO features =

* **Push-to-deploy** can automatically trigger updates when whenever you push to GitHub (Coming soon)
* Support for branches
* E-mail **support**

[Learn more about PRO](http://wppusher.com/pro)

= Get started =

If you already use Git for your projects and your themes and plugins are in their own repositories on GitHub, getting started with WP Pusher is simple and easy. Just go to "New plugin" or "New theme" in the WP Pusher menu and type in the repository for the package: github-username/repository-name

If any of your plugins or themes are in private repositories on GitHub, WP Pusher will need a token to access them. You can read GitHub's guide to application tokens [here](https://help.github.com/articles/creating-an-access-token-for-command-line-use/). Paste in the token at WP Pusher settings page.

= Conventions =

* Theme stylesheets _must_ be named the same as the repository
* Plugin directories _must_ be named the same as the repository
* GitHub version tags _must_ be numeric, such as '1.0' or '1.0.1', with an optional preceding 'v', such as 'v1.0.1'
* WordPress version tags _must_ be numeric, such as '1.0' or '1.0.1'

= Git workflow =

The way WP Pusher works, packages (themes and plugins) need to be in their own repositories. If your packages are in their own repositories already, you can safely skip this section. Some developers prefer having their whole WordPress installation under Git, which potentially makes things a bit more complicated. By having all packages in their own repositories, you can easily share code across clients / projects. Since you shouldn’t be editing the core WordPress code, in most cases having the whole project under Git shouldn’t be necessary. However, if for some reason your project require that you have one Git repository for the whole project, you will have to use Git submodules, so that you can still have every package in its own (sub) repository.

== Installation ==

= Using The WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Search for 'WP Pusher'
3. Click 'Install Now'
4. Activate the plugin on the Plugin dashboard

= Uploading in WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Navigate to the 'Upload' area
3. Select `wp-pusher.zip` from your computer
4. Click 'Install Now'
5. Activate the plugin in the Plugin dashboard

= Using FTP =

1. Download `wp-pusher.zip`
2. Extract the `wppusher` directory to your computer
3. Upload the `wppusher` directory to the `/wp-content/plugins/` directory
4. Activate the plugin in the Plugin dashboard

== Screenshots ==

1. Plugins installed and managed with WP Pusher
2. The WP Pusher dashboard
3. Manage themes and plugins from the dashboard

== Changelog ==

= 1.0.7 =

* Bug when installing and updating themes

= 1.0.6 =

* Support for including a wppusher.php file to run commands on installation and updating: https://gist.github.com/petersuhm/9b7f4e4886519fc3a8a8
* Actions after packages are installed or updated
* Support for GitLab

= 1.0.5 =

* Add dry run feature in order to set up already installed plugins

= 1.0.4 =

* Auto updates

= 1.0.3 =

* Bugfix: Make sure plugin is reactivated after update
* Activation links on multisite
* Support for dots in repository names

= 1.0.2 =

* Added support for multisite setups

= 1.0.1 =
* Add support for branches in Pro version

= 1.0.0 =
* No more beta
* Release of WP Pusher Pro

= 0.1.2 =
* Minor bugfixes

= 0.1.1 =
* Bitbucket support for public and private repositories
* Activate repositories directly after installation
* Better house keeping after plugin deletion

= 0.1.0 =
* Public BETA release
