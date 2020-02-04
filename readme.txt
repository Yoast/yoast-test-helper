=== Yoast test helper ===

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
