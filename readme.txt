=== Yoast Test Helper ===
Contributors: yoast, joostdevalk, omarreiss, jipmoors, herregroen
Tags: Yoast, Yoast SEO, development
Requires at least: 5.4
Tested up to: 5.5
Stable tag: 1.8
Requires PHP: 5.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==

This plugin makes testing Yoast SEO, Yoast SEO add-ons and integrations and resetting the different features a lot easier. It also makes testing database migrations a lot easier as it allows you to set the database version and see if the upgrade process runs smoothly.

= Features =

This test helper plugin has several features:

* Toggle between premium and free Yoast SEO versions easily.
* Easily enable Yoast SEO development mode.
* Saving and restoring Yoast SEO and Yoast SEO extension options, to test upgrade paths.
* Add options debug info to Yoast SEO admin pages.
* Reset the internal link counter, prominent words calculation and other features.
* Add two post types (Books and Movies) with two taxonomies (Category and Genre) each and optionally disable the block editor for them.
* Easily add an inline script after a selected script.
* Replace your `.test` TLD with `example.com` in your Schema output, so you can easily copy paste to Google's Structured Data Testing Tool.
* Change the number of URLs shown in an XML Sitemap.
* Easily change your MyYoast URL.

If you find bugs or would like to contribute, see our [GitHub repo](https://github.com/Yoast/yoast-test-helper).

== Screenshots ==

1. Screenshot of the Yoast test helper admin page.

== Changelog ==

= 1.8 =

Enhancements:

* Added resets for indexables related options when using the Yoast Test Helper to reset the indexables and migrations.
* Added resets for prominent word related functionality when using the Yoast Test Helper to reset the prominent words calculation.

Bugfixes:

* Fixes the database versions keys the plugin checks for Video SEO and WooCommerce SEO as they've been changed in these plugins.

= 1.7 =

Enhancements:

* Drops the table for prominent words (used by our internal linking functionality, among others) when you hit reset indexables.
* Some minor code style fixes.

= 1.6 =

Enhancements:

* Removed the feature toggle for internal linking as it's no longer in use.
* Changed the order of admin boxes to be more logical.

Bugfixes:

* Fix fatal error with debug panel.

Under the hood:

* If an integration returns an empty string for its form controls, don't output the admin block.
* Several QA fixes and CS fixes.
* Travis now builds for PRs.
* Added a `.gitattributes` for more easy exporting.

= 1.5 =

Release Date: April 3rd, 2020

Enhancements:

* Added a button to reset your database to pre-Indexables state. When running an indexables branch this causes all migrations to re-run and thus all tables to be created cleanly.
* Added a button to reset the configuration wizard state.
* Permalinks now reset after enabling custom post types.

Bugfixes:

* Fixes a bug where saving the Influence schema setting wouldn't work.

General:

* Switched to YoastCS 2.0 and changed the auto-loading process.

= 1.4. =

Release Date: February 5th, 2020

Enhancements:

* Added the option to set Yoast SEO Premium version number.

= 1.3 =

Release Date: February 4th, 2020

Enhancements:

* Added the option to add an inline script after a selected script.
* Added the option to enable and disable Yoast SEO development mode.
* Adds a button to reset the tracking option, thus triggering another tracking request.
* Slight styling improvements.

Bugfixes:

* Fixed the fact that disabling Gutenberg / the block editor on Books and Movies post type didn't actually work.
* Made the plugin option autoload, removing the need for an extra query to get the option.
* Increase the allowed number of characters for the Yoast SEO version number.

= 1.2.5 =

Release Date: July 12th, 2019

Enhancements:

* Configures composer to always use php 5.6 as a platform. This will prevent possible conflicts with symphony dependencies of wordpress-seo.
* Updates fstream to 1.0.12 and tar to 2.2.2.
* From now on, `grunt deploy:trunk` and `grunt deploy:master` can be used to deploy.
