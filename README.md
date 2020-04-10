Yoast Test Helper
=================

[![Build Status](https://api.travis-ci.org/Yoast/yoast-test-helper.svg?branch=master)](https://travis-ci.org/Yoast/wordpress-seo)
[![License: GPL v3](https://img.shields.io/badge/License-GPL%20v3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)

Purpose of this plugin
----------------------

This is a plugin to aid in testing and developing the [Yoast SEO plugin](https://yoa.st/1ul) and its extensions.

Features
--------

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

Installation
------------

1. Download the latest version.
2. Run `composer install`.
3. You're done. You will find the plugin settings under Tools → Yoast Test in your WordPress admin.
