=== Yoast Test Helper ===
Contributors: yoast, joostdevalk, omarreiss, jipmoors, herregroen
Tags: Yoast, Yoast SEO, development
Requires at least: 5.6
Tested up to: 5.8
Stable tag: 1.15
Requires PHP: 5.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==

This plugin makes testing Yoast SEO, Yoast SEO add-ons and integrations and resetting the different features a lot easier. It also makes testing database migrations a lot easier as it allows you to set the database version and see if the upgrade process runs smoothly.

= Features =

This test helper plugin has several features:

* Toggle between premium and free Yoast SEO versions easily.
* Easily start a co-browsing session with Yoast Support.
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

= 1.15 =

Release date October 19th, 2021

Enhancements:

* Add Indexable data to Query Monitor.

Bugfix:

* Fixes a bug where not all the transients that hold unindexed counts were deleted when users hit the "Reset Indexables & Migrations" button.

= 1.14 =

Release Date: April 28th, 2021

Enhancements:

* Adds the option to reset SEO roles & capabilities.

= 1.13 =

Release Date: March 18th, 2021

Bugfixes:

* Fixes a bug where all MyYoast requests would go to live my.yoast.com instead of the selected value of the domain dropdown.

= 1.12 =

Release Date: December 24th, 2020

Enhancements:

* Adds an "enable support session" box to the top of the Yoast Test helper screen. Enabling the checkbox on that box will load a HelpScout beacon and a CoBrowse script on both front and backend of the site, for the current user only, for the duration of 4 hours. This allows the user to co-browse with a Yoast support agent. Please only enable this when instructed to do so by Yoast support.

= 1.11 =

Release Date: December 2nd, 2020

Bugfixes:

* Fixes a bug where a deprecated jQuery function was used.

= 1.10 =

Release Date: November 17th, 2020

Bugfixes:

* Fixes a bug where the `Start SEO Data optimization` button was not shown after resetting the indexables tables and migrations.
* Fixes a bug where no notification was shown to reindex your site when resetting the indexables tables and migrations, the prominent words table, and the internal link count.
* Fixes a bug where the `Reset indexables tables & migrations` functionality did not reset the internal link count transients.

Other:

* Makes the plugin translatable.

= 1.9 =

Release Date: October 6th, 2020

Bugfixes:

* Fixes a bug where the link columns could not be emptied due to an incorrect table name.
* Fixes a bug where links could be attached to the wrong indexables when resetting the indexable tables and migrations.

= 1.8 =

Release Date: July 8th, 2020

Enhancements:

* Added resets for indexables related options when using the Yoast Test Helper to reset the indexables and migrations.
* Added resets for prominent word related functionality when using the Yoast Test Helper to reset the prominent words calculation.

Bugfixes:

* Fixes the database versions keys the plugin checks for Video SEO and WooCommerce SEO as they've been changed in these plugins.

= 1.7 =

Release Date: June 2nd, 2020

Enhancements:

* Drops the table for prominent words (used by our internal linking functionality, among others) when you hit reset indexables.
* Some minor code style fixes.

= 1.6 =

Release Date: April 9th, 2020

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
