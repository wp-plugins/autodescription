=== AutoDescription ===
Contributors: Cybr
Tags: seo, description, og, type, meta, ogtype, multisite, search, engine, optimization
Requires at least: 3.6.0
Tested up to: 4.2.2
Stable tag: 4.2.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

AutoDescription makes sure your SEO is always up-to-date without any configuration needed.

== Description ==

= AutoDescription =

This plugin makes sure your site uses Description, Open Graph and LD+JSON.

No configuration is needed. Either Network Activate this or use it on a single site.

***How it works***

**Description/og:description**

1. Autodescription first looks for any Genesis SEO content.
1. If not found, it will look through your page content.
1. Then it will strip all shortcodes and HTML.
1. From there it will make a sentence of maximum 160 characters and strips all words exceeding it.
1. It will add ... if it exceeds.

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
2. On any other page it will add: "page title - blogname"

**og:url**

1. The current url of the page

**og:site_name**

1. The blogname

**LD+JSON**

1. This will create a script in the header and Google will try to use this to allow users to further search in your website from their search engine.

= Caching =

This plugin can be heavy on large pages (with book-like content). Therefor *a caching plugin is adviced*.
This plugin will not output anything if no object caching plugin is found and the user is logged in.

This plugin will always output the meta data when the user isn't logged in. Because Google is never logged in, your SEO is always correct.

**If you use object caching**

The output will be stored for each page, if you've edited a page the meta will stay the same until the object cache expires. So be sure to clear your object cache if it's a drastic change.

**Other notes**

*This plugin fully supports Genesis themes, this plugin takes the Genesis SEO content under each post and page and the global Genesis SEO configuration page into account.*
*This plugin will work with any theme, however it will not look through Widgets or shortcodes for description. This means that one-page themes or pages built with page builders might leave an empty description.*

== Installation ==

1. Install AutoDescription either via the WordPress.org plugin directory, or by uploading the files to your server.
1. Either Network Activate this plugin or activate it on a single site.
1. That's it! There is no configuration needed.

== Changelog ==

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

== Requirements ==

WordPress 4.2.0 brings mb_strlen into the game, even if your server doesn't support it. But this plugin should be working **from at least 3.6.0** if your server supports mb_strlen.

== Upgrade Notice ==

== Filters ==

= Add any of these filters to your theme's functions.php or a plugin to change this plugin's output =

***Disable the plugin for a theme or page:***
`add_filter('hmpl_ad_load', '__return_false');`

***Only allow this plugin to output if the user isn't logged in:***
`add_filter('hmpl_ad_load_logged_out_only', '__return_true');`

***Always output meta data, regardless of caching:***
`add_filter('hmpl_ad_load_logged_out_only', '__return_false');`