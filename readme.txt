=== AutoDescription ===
Contributors: Cybr
Tags: seo, description, title, og, type, meta, ogtype, multisite, search, engine, optimization, manual, canonical, rel, options, domain, mapping, genesis, robots, nofollow, noindex, noarchive, noodp, noydir
Requires at least: 3.6.0
Tested up to: 4.2.2
Stable tag: 2.1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

AutoDescription makes sure your SEO is always up-to-date without any configuration needed.

== Description ==

= AutoDescription =

**The all in one SEO optimization plugin for WordPress**

This plugin makes sure your site uses a correct Title, Description, Open Graph, LD+JSON, Robots and a Canonical tag.

This plugin also allows you to edit the Title, Description, Open Graph, Robots, Canonical Tag and a 301 Redirect for each post or page in a compact settings box.

No configuration is needed. Either Network Activate this or use it on a single site.

> <strong>Written for MultiSite</strong><br>
> This plugin has been written for WordPress Multisite with a WordPress.com like environment in mind.
> 
> This means that this plugin is fully compatible with the [Domain Mapping plugin by WPMUdev](https://premium.wpmudev.org/project/domain-mapping/).
>
> This also means that it's completely nailed down for security and best of all: ad-free.
> 
> It takes a lot of time to pin down every aspect of SEO optimization so expect this plugin to be updated regularly with new features.

You can also fine-tune each page's SEO, these options can be found beneath the content on the post's edit page.

= Caching =

This plugin can be heavy on large pages (with book-like content). Therefor *a caching plugin is adviced*.
This plugin will not output anything if no object caching plugin is found and the user is logged in.

**This plugin will always output the meta data when the user isn't logged in. Because Google is never logged in, your SEO meta output is always displayed correctly.**

**If you use object caching**

The output will be stored for each page, if you've edited a page the meta will stay the same until the object cache expires. So be sure to clear your object cache if your object cache expire time is set extremely high.

= Other notes =

*This plugin copies data from the Genesis SEO meta, this means that when you use Genesis, you can easily upgrade to this plugin without editing each page!*

*The Automatic Description Generation will work with any installation. But it will exclude shortcodes. This means that if you use shortcodes or a page builder, be sure to enter your custom description!*

> <strong>Check out the "Other Notes" tab for advanced features</strong>

= Translating =

This plugin is fully translated to Dutch. If you wish to submit a translation, please contact me at the [CyberWire contact page](https://cyberwire.nl/contact/).

== Installation ==

1. Install AutoDescription either via the WordPress.org plugin directory, or by uploading the files to your server.
1. Either Network Activate this plugin or activate it on a single site.
1. That's it! 
1. Let the plugin automatically work or fine-tune each page with the meta boxes beneath the content.

== Changelog ==

= 2.1.1 =
* Pushed the meta tags higher up in the wp_head
* Added WPMUdev's Avatars 'blog avatar' og:image support
* Forced og:image to always output even if the URL is empty for better syntax.
* Other minor improvements

= 2.1.0a =
* Fixed translation

= 2.1.0 =
* Added a "redirected" post state on edit.php (all posts/all pages admin screen)
* Added a "noindex" post state on edit.php
* Optimized code and filters
* Added more specific post/page sentences for the meta boxes
* Added 404 title
* Updated translation files

= 2.0.9 =
* Added a custom 301 redirect URL option field on each page/post. This url accepts no query args by default, but can be activated through a filter. Read "other notes" for more information.
* Cleaned up HTML code in Post/Page edit screen
* Changed the explanation URL's in Post/Page edit screens so they can be easily translated (using Google Search Console help pages)
* Updated translations for Dutch

= 2.0.8 =
* Fixed double slash in javascript file call on edit pages

= 2.0.7 =
* Removed title tag seperator when blog tagline is missing

= 2.0.6 =
* Changed og:url output to match canonical output to comply with Facebook standards

= 2.0.5 =
* Added featured image to og:image (if set)
* Added expanded filter to og:image, read "other notes" for more information
* Fixed all PHP warnings
* Added filter for title seperator (hmpl_ad_title_seperator)

= 2.0.4 =
* Fixed Domain Mapping Canonical URL
* Fixed Canonical URL scheme

= 2.0.3 =
* Applied the title output to the Title tag
* Cleaned up code
* Various bugfixes
* Added Dutch translation

= 2.0.2 =
* Fixed Javascript bug

= 2.0.1 =
* Fixed bug where Genesis robots & canonical was still being shown
* Made robots output more reliable
* Renamed functions for more consistent plugin recognition
* Improved performance on search and 404 pages by removing some meta
* Added filter to og:image
* Added generator tag with filter, if not used no generator tag will be displayed
* Added filter for before and after output
* The filters can be found under "Other notes" in this plugin's page.
* Added filterable indicators in html code to show where the output starts and ends
* More than 1337 lines :(

= 2.0.0 =
* This update is so big, it needs a new number :)
* Added Canonical URL tag with WPMUdev's Domain Mapping support
* Added per page/post options within a meta box beneath the content
 * Add your own SEO meta title
 * Add your own SEO meta description
 * Add your own Canonical URL
 * Disallow indexing by search engines (noindex)
 * Disallow archiving by search engines (noarchive)
 * Disallow link tracking on urls by search engines (nofollow)
 * All these settings are merged with Genesis' and will be overwritten on save
* Each page now has noopd and noydir by default if you're not using Genesis for better SEO consistency
* More filters
* Bugfixes
* Exactly 1337 lines of glory :)

= 1.3.0 =
* Added SEO plugin detection (mainly WordPress SEO by Yoast)
* Moved LD+Json script to header, replaced json_encoding with esc_url on urls
* Broadened CDN support
* Broadened support for odd server configurations and older WP versions
* Removed og:image if no header image is found
* Cleaned up code

= 1.2.0 =
* Added language file, English and Dutch are now supported
* Now uses object caching
* Added LD+Json SearchAction scheme
* Added filter hmpl_ad_load_logged_out_only (true/false)
* Now shows output when user's logged in when object caching is available, else only if logged out.

= 1.1.0 =
* Added dynamic og:type
* Cleaned up PHP notices
* Bugfixes

= 1.0.1 = 
* Added filter hmpl_ad_load (return false to disable the plugin output)

= 1.0.0 =
* Initial Release

== Filters ==

= Add any of these filters to your theme's functions.php or a plugin to change this plugin's output: =

***Disable the plugin for a theme or page:***
`add_filter('hmpl_ad_load', '__return_false');`

***Only allow this plugin to output if the user isn't logged in:***
`add_filter('hmpl_ad_load_logged_out_only', '__return_true');`

***Always output meta data, regardless of caching of user log in:***
`add_filter('hmpl_ad_load_logged_out_only', '__return_false');`

***Disable meta boxes in post/page edit screen:***
`add_filter( 'hmpl_ad_seobox', '__return_false' );`

***Disable plugin usage indicator in HTML output:***
`add_filter( 'hmpl_ad_indicator', '__return_false' );`

***Add custom meta before the output, example:***
`add_filter('hmpl_ad_pre', 'my_before_autodescription' );
function my_before_autodescription() {
	
	//* Add prefetching
	$prefetch 	= 	'<link rel="dns-prefetch" href="//fonts.googleapis.com/">' . "\r\n"
				.	'<link rel="dns-prefetch" href="//fonts.gstatic.com/">' . "\r\n"
				;
	
	//* Add mobile scaling viewport
	$viewport 	= '<meta name="viewport" content="initial-scale=1.0,width=device-width,user-scalable=no" />' . "\r\n";
	
	//* Add the two together in another variable
	$output = $prefetch . $viewport;
	
	//* Return the output
	return $output;
}`

***Add custom meta after the output, example:***
`add_filter('hmpl_ad_pro', 'my_after_autodescription' );
function my_after_autodescription() {
	
	//* Add your app icons for apple and Windows (Phone) 8, don't forget to escape the urls!
	$appicons 	= '<link rel="icon" type="image/x-icon" href="' .  esc_url(home_url( '/path/to/favicon.ico' ) ) . '" sizes="16x16">' . "\r\n"
				. '<link rel="apple-touch-icon-precomposed" href="' .  esc_url(home_url( '/path/to/yourimage152px.png' ) ) . '" />' . "\r\n"
				. '<meta name="msapplication-TileImage" content="' .  esc_url(home_url( '/path/to/yourimage144px.png' ) ) .'" />' . "\r\n"
				. '<meta name="msapplication-TileColor" content="#f1f1f1" />' . "\r\n"
				;
	
	return $appicons;
}`

***Add custom og image, example (deprecated, use hmpl_og_image_args ):***
`add_filter('hmpl_og_image', 'my_og_image' );
function my_og_image() {

	//* You don't have to escape this url :)
	$output = home_url( '/path/to/yourimage200px.jpg' );
	
	return $output;	
}`

***Add custom og image arguments, example:***
`add_filter('hmpl_og_image_args', 'my_awesome_og_image' );
function my_awesome_og_image() {

	//* You don't have to escape this url :)
	$args['image'] = home_url( '/path/to/yourimage200_to_1500px.jpg' );
	
	//* Set this to true if you don't want featured images to be used in og:image
	//* args['image'] has to be set for this to work
	$args['override'] = false;
	
	//* Set this to false if you wish that the homepage featured image overrides the URL set above
	$args['frontpage'] = true;
	
	return $args;	
}`

***Add custom generator tag, example:***
`add_filter('hmpl_ad_generator', 'my_custom_generator' );
function my_custom_generator() {
	
	$output = 'MyAwesomeCompany';
	
	return $output;
	
}`

***Allow only local links for custom 301 url (requires WP 4.1.0 and up for best results):***
`add_filter('hmpl_ad_301_external', '__return_false' );`

***Allow query string parameters in custom 301 redirect url:***
`add_filter('hmpl_ad_301_noqueries', '__return_false' );`

***Disallow the appearance of SEO states in the edit.php screen:***
`add_filter('hmpl_ad_states', '__return_false' );`

***Start over by renaming the option group: ADVANCED & untested***
`function my_new_settings_field_group() {
	add_filter( 'hmpl_ad_settings_field', 'my_new_settings_field_group_name' );
}
add_action( 'init', 'my_new_settings_field_group' );
add_action( 'admin_init', 'my_new_settings_field_group' );

function my_new_settings_field_group_name() {
	//The new settings name, only visible in database
	$name = 'hmpl-ad-seo-settings-new';
	
	return $name;
}`