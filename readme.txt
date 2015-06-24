=== AutoDescription ===
Contributors: Cybr
Tags: seo, description, title, og, type, meta, ogtype, multisite, search, engine, optimization, manual, canonical, rel, options, domain, mapping, genesis, robots, nofollow, noindex, noarchive, noodp, noydir
Requires at least: 3.6.0
Tested up to: 4.2.2
Stable tag: 4.2.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

AutoDescription makes sure your SEO is always up-to-date without any configuration needed.

== Description ==

= AutoDescription =

This plugin makes sure your site uses Description, Open Graph, LD+JSON, robots and a Canonical tag.

No configuration is needed. Either Network Activate this or use it on a single site.

You can also fine-tune each page's SEO, these options can be found beneath the content on the post's edit page.

***How it works***

**Description/og:description**

1. Autodescription first looks for any Genesis SEO content.
1. If not found, it will look through your page content.
1. Then it will strip all shortcodes and HTML.
1. From there it will make a sentence of maximum 160 characters and strips all words exceeding it.
1. It will add ... if it exceeds.

1. You can also add your own description on each page or post.
2. This will override the automatically generated description.

**og:image**

1. AutoDescription will look for a header image if set

**og:locale**

1. AutoDescription will look for the current blog's language settings

**og:type**

1. If the page is a blog post, it will output "article"
1. If it's an author page, it will output "profile"
1. If it's any other page, it will output "website"

**og:title**

1. On the front page it will add "blogname - blog description"
1. On any other page it will add: "page title - blogname"

1. You can also add your own description on each page or post.
2. This will override the automatically generated title.

**og:url**

1. The current url of the page

**og:site_name**

1. The blogname

**LD+JSON**

1. This will create a script in the header and Google will try to use this to allow users to further search in your website from their search engine.

**Canonical**

1. This will tell search engines where to look and continue from there. This value can be adjusted and works perfectly with [Domain Mapping by WPMUdev].

[Domain Mapping by WPMUdev]: https://premium.wpmudev.org/project/domain-mapping/
	"Get Domain Mapping"

**Robots**

1. Set the nofollow, noindex and noarchive tags per page or post
1. It's up to the search engine to honor these

= Caching =

This plugin can be heavy on large pages (with book-like content). Therefor *a caching plugin is adviced*.
This plugin will not output anything if no object caching plugin is found and the user is logged in.

This plugin will always output the meta data when the user isn't logged in. Because Google is never logged in, your SEO is always correct.

**If you use object caching**

The output will be stored for each page, if you've edited a page the meta will stay the same until the object cache expires. So be sure to clear your object cache if it's a drastic change.

**Other notes**

*This plugin fully supports Genesis themes and WPMUdev's Domain Mapping, this plugin takes the Genesis SEO content under each post and page and the global Genesis SEO configuration page into account.*
*This plugin will work with any theme, however it will not look through Widgets or shortcodes for description. This means that one-page themes or pages built with page builders might leave an empty description.*
*To counter this issue, fill in the meta boxes beneath each post or page edit screen's content.*

== Installation ==

1. Install AutoDescription either via the WordPress.org plugin directory, or by uploading the files to your server.
1. Either Network Activate this plugin or activate it on a single site.
1. That's it! 
1. Let the plugin automatically work or fine-tune each page with the meta boxes beneath the content.

== Changelog ==

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

***Disable indicator in HTML output:***
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