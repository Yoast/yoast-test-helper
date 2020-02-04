Yoast SEO Test Helper
=====================

[![Build Status](https://api.travis-ci.org/Yoast/yoast-test-helper.svg?branch=master)](https://travis-ci.org/Yoast/wordpress-seo)
[![License: GPL v3](https://img.shields.io/badge/License-GPL%20v3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)

Purpose of this plugin
----------------------

This is a plugin to aid in testing and developing the [Yoast SEO plugin](https://yoa.st/1ul) and its extensions.

Features
--------

This test helper plugin has several features:

* Toggle between premium and free Yoast SEO versions easily.
* Saving and restoring Yoast SEO and Yoast SEO extension options, to test upgrade paths.
* Add options debug info to Yoast SEO admin pages.
* Reset the internal link counter and prominent words calculation.
* Add two post types (Books and Movies) with two taxonomies (Category and Genre) each.
* Enable Gutenberg for either or both of these post types.

Installation
------------

1. Download the latest version.
2. Run `composer install`.
3. You're done. You will find the plugin settings under Tools → Yoast Test in your WordPress admin.
