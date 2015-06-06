<?php 
/**
 * Plugin Name: AutoDescription
 * Plugin URI: https://wordpress.org/plugins/autodescription/
 * Description: Automatically adds a description if previously empty based upon content and adds Open Graph tags.
 * Version: 1.3.0
 * Author: Sybre Waaijer
 * Author URI: https://cyberwire.nl/
 * License: GPLv2 or later
 * Text Domain: AutoDescription
 */

/** 
 * Fully supports Genesis themes, this plugin is build upon it.
 * Fully supports WordPress SEO (by Yoast). It pretty much disables this plugin for now, protection functions aren't really helping though.
 * Please notify me if you notice an issue with a specific theme or plugin.
 * Will test extensively in the near future.
 */
 
/**
 * Changelog
 * 1.0.0	: Initial release
 *
 * 1.0.1	: Added filter
 *
 * 1.1.0	: Added Dynamic og:type, cleaned PHP notices, plus more bugfixes
 *			: Misses language file
 *
 * 1.2.0	: Added language file again
 *			: Now uses object caching
 *			: Now displays on even if user's logged in
 *			: Added filter
 *
 * 1.3.0 	: Added SEO plugin detection
 *				: If found, disable certain outputs
 *			: Moved LD+Json script to header, replaced json_encoding with esc_url on urls
 *			: Broadened CDN support
 *			: Added GPLv2+ license in plugin header (whoops, forgot that before)
 *			: Making everything ready for filters (todo: add filters)
 *			: Broadened support for odd server configurations and older WP versions
 *			: Removed og:image if no header image is found
 *
 * 2.0.0+	: This update is so big it needs a new number
 *			: Not so auto anymore
 * 				: Added meta boxes to each page and post for manual SEO (if not Genesis)
 *					: If these fields (or one of them) are left empty, 
 *				: Added global SEO settings (if not genesis)
 * 			: Added global define variable (via wp-config.php): define(IS_HMPNL, true);
 * 				: This variable removes the Genesis SEO settings and overwrites them with this plugin's.
 *				: The same settings are used as for Genesis, this means theme switching is even easier and no settings are lost.
 *
 * + coming soon
 *
 * Big thanks to StudioPress for releasing their software under GPL-2.0+, saved me a LOT of work figuring out things.
 *
 * @todo: Check for empty instances from other SEO plugins and fill them in
 * 		: There should be a reason why they're empty. Add options.
 */

/**
 * Plugin locale 'AutoDescription'
 *
 * File located in plugin folder autodescription/language/
 *
 * @since 1.0.0
 */
function ad_locale_init() {
	$plugin_dir = basename(dirname(__FILE__));
	load_plugin_textdomain( 'AutoDescription', false, $plugin_dir . '/language/');
}
add_action('plugins_loaded', 'ad_locale_init');

/**
 * Extended charset support
 *
 * @uses mb_strlen
 * @uses strlen
 * 
 * @uses mb_substr
 * @uses substr
 *
 * @since 1.3.0
 */
if ( !function_exists('_mb_strlen') ) {
	function _mb_strlen($str) {
		$charset = get_option( 'blog_charset' );
		if ( ! in_array( $charset, array( 'utf8', 'utf-8', 'UTF8', 'UTF-8' ) ) ) {
			return strlen( $str );
		}
		// Use the regex unicode support to separate the UTF-8 characters into an array
		preg_match_all( '/./us', $str, $match );
		return count( $match[0] );
	}
}

if ( !function_exists('mb_strlen') ) {
	function mb_strlen( $str ) {
		return _mb_strlen($str);
	}
}

if ( !function_exists('_mb_substr') ) {
	function _mb_substr($str) {
		$charset = get_option( 'blog_charset' );
		if ( ! in_array( $charset, array( 'utf8', 'utf-8', 'UTF8', 'UTF-8' ) ) ) {
			return substr( $str );
		}
		// Use the regex unicode support to separate the UTF-8 characters into an array
		preg_match_all( '/./us', $str, $match );
		return count( $match[0] );
	}
}

if ( !function_exists('mb_substr') ) {
	function mb_substr( $str ) {
		return _mb_substr($str);
	}
}

/**
 * Detect active plugin by constant, class or function existence.
 *
 * @since 1.3.0
 *
 * @param array $plugins Array of array for constants, classes and / or functions to check for plugin existence.
 *
 * @return boolean True if plugin exists or false if plugin constant, class or function not detected.
 *
 * @thanks StudioPress :)
 */
function hmpl_ad_detect_plugin( array $plugins ) {

	//* Check for classes
	if ( isset( $plugins['classes'] ) ) {
		foreach ( $plugins['classes'] as $name ) {
			if ( class_exists( $name ) )
				return true;
		}
	}

	//* Check for functions
	if ( isset( $plugins['functions'] ) ) {
		foreach ( $plugins['functions'] as $name ) {
			if ( function_exists( $name ) )
				return true;
		}
	}

	//* Check for constants
	if ( isset( $plugins['constants'] ) ) {
		foreach ( $plugins['constants'] as $name ) {
			if ( defined( $name ) )
				return true;
		}
	}

	//* No class, function or constant found to exist
	return false;

}

/**
 * SEO plugin detection
 * 
 * @since 1.3.0
 *
 * @thanks StudioPress :)
 */
function hmpl_ad_detect_seo_plugins() {

	return hmpl_ad_detect_plugin(
		// Use this filter to adjust plugin tests.
		apply_filters(
			'hmpl_ad_detect_seo_plugins',
			//* Add to this array to add new plugin checks.
			array(

				// Classes to detect.
				'classes' => array(
					'All_in_One_SEO_Pack',
					'All_in_One_SEO_Pack_p',
					'HeadSpace_Plugin',
					'Platinum_SEO_Pack',
					'wpSEO',
					'SEO_Ultimate',
				),

				// Functions to detect.
				'functions' => array(),

				// Constants to detect.
				'constants' => array( 'WPSEO_VERSION', ),
			)
		)
	);

}

/**
 * Detects if plugins outputting og:type exists
 *
 * @note isn't used in hmpl_og_image()
 *
 * @uses hmpl_ad_detect_plugin()
 *
 * @since 1.3.0
 * @boolean true if exists, false if not
 */
function hmpl_ad_has_og_plugin() {
	
	if ( hmpl_ad_detect_plugin( $plugins = array('classes' => array('WPSEO_OpenGraph') ) ) )
		return true;
	
	return false;
}

/**
 * Detects if plugins outputting ld+json exists
 *
 * @uses hmpl_ad_detect_plugin()
 *
 * @since 1.3.0
 * @boolean true if exists, false if not
 */
function hmpl_ad_has_json_ld_plugin() {
	
	if ( hmpl_ad_detect_plugin( $plugins = array('classes' => array('WPSEO_JSON_LD') ) ) )
		return true;

	return false;
}

/**
 * Get the excerpt of the post 
 * 
 * @since 1.0.0
 * @param output
 */
function hmpl_get_excerpt_by_id($excerpt = '') {
	
	if (!is_404() && !is_search()) {
		global $post_id;
		$the_post = get_post($post_id);
		$content = $the_post->post_content;
	}
	
	if ( empty($excerpt) )
		$excerpt = wp_strip_all_tags(strip_shortcodes($content));
	
	$excerpt = esc_attr( $excerpt );
	
	$excerpt = str_replace(array("\r\n", "\r", "\n"), "\n", $excerpt);
	
	$lines = explode("\n", $excerpt);
	$new_lines = array();
	
	foreach ($lines as $i => $line) {
		if(!empty($line))
			$new_lines[] = trim($line) . ' ';
	}
	
	$output = implode($new_lines);
	
    return $output;
}
 
/**
 * Create description
 *
 * @since 1.0.0
 * @param output
 */
function hmpl_ad_generate_description($description = '') {
	global $wp_query;
	
	//* Genesis only, checks if description is present
	$theme_info = wp_get_theme()->get('Template');

	if( $theme_info == 'genesis' ) {
		if ( is_front_page() ) {
			$description = genesis_get_seo_option( 'home_description' ) ? genesis_get_seo_option( 'home_description' ) : get_bloginfo( 'description' );
		}
		if ( is_singular() ) {
			if ( genesis_get_custom_field( '_genesis_description' ) )
				$description = genesis_get_custom_field( '_genesis_description' );
		}
		if ( is_category() ) {
			$term = $wp_query->get_queried_object();
			$description = ! empty( $term->meta['description'] ) ? $term->meta['description'] : '';
		}
		if ( is_tag() ) {
			$term = $wp_query->get_queried_object();
			$description = ! empty( $term->meta['description'] ) ? $term->meta['description'] : '';
		}
		if ( is_tax() ) {
			$term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
			$description = ! empty( $term->meta['description'] ) ? wp_kses_stripslashes( wp_kses_decode_entities( $term->meta['description'] ) ) : '';
		}
		if ( is_author() ) {
			$user_description = get_the_author_meta( 'meta_description', (int) get_query_var( 'author' ) );
			$description = $user_description ? $user_description : '';
		}
		if ( is_post_type_archive() && genesis_has_post_type_archive_support() ) {
			$description = genesis_get_cpt_option( 'description' ) ? genesis_get_cpt_option( 'description' ) : '';
		}
	}
	
	//* Fetch WPSEO description
	if ( method_exists('WPSEO_Frontend', 'generate_metadesc') )
		$description = WPSEO_Frontend::get_instance()->metadesc( false );
	
	//* No description found? Create own description based on content
	// Cache this? Cache everything? D:
	if ( !is_string($description) || empty($description) ) { //* HEAVY CODE (adds .5s processing time with 241k characters on 6-core 2.4GHz prefork)
		global $post;
	
		//* These values should've been escaped already prior to fetching them.
		$hmpl_title = get_the_title($post);
		$hmpl_blogname = get_bloginfo('name');
		$hmpl_excut = '';
		$hmpl_on = __('on', 'AutoDescription'); // Post Title "on" Blog Name - the excerpt
		
		$hmpl_excerpt = hmpl_get_excerpt_by_id();
		
		$hmpl_maxcharlength = 160 - mb_strlen($hmpl_title . $hmpl_on . $hmpl_blogname);
		$hmpl_excerptlength = mb_strlen( $hmpl_excerpt );
		
		if ( $hmpl_excerptlength > $hmpl_maxcharlength ) {
			
			$subex = mb_substr( $hmpl_excerpt, 0, $hmpl_maxcharlength );
						
			$exwords = explode( ' ', $subex );
			
			if ( function_exists( mb_strlen() ) ) {
				$excut = - ( mb_strlen( $exwords[ count( $exwords ) - 1 ] ) );
			} else {
				$excut = - ( strlen( $exwords[ count( $exwords ) - 1 ] ) );
			}
			
			if ( $excut < 0 ) {
				$hmpl_excerpt = mb_substr( $subex, 0, $excut );				
			} else {
				$hmpl_excerpt = $subex;
			}
			$hmpl_excut = '...';
		}
		$hmpl_excerpt = str_replace(' ...', '...', $hmpl_excerpt . $hmpl_excut);
		
		$description = sprintf( '%s %s %s - %s', $hmpl_title, $hmpl_on, $hmpl_blogname, $hmpl_excerpt);		
	}
	
	//* Make sure no spaces are added at the end, adds " symbol (used to determine the end of line)
	$description = $description . '"';
	$output = str_replace(' "', '"', $description);
	
	//* Remove the last " to make the code easier to read and manipulate
	if ( function_exists( mb_substr() ) ) {
		$output = mb_substr($output, 0, -1);
	} else {
		$output = substr($output, 0, -1);
	}
	
	return $output;
}
/**
 * Render the description
 * 
 * @uses hmpl_ad_generate_description()
 * @uses hmpl_ad_detect_seo_plugins()
 * 
 * @since 1.3.0
 */
function hmpl_ad_the_description($description = '') {
	
	if ( hmpl_ad_detect_seo_plugins() )
		return;
	
	if ( empty ($description) )
		$description = hmpl_ad_generate_description();
	
	$output = '<meta name="description" content="' . esc_attr($description) . '" />' . "\r\n";
	
	return $output;
}

/**
 * Render og:description
 * 
 * @uses hmpl_ad_generate_description()
 * @uses hmpl_ad_has_og_plugin()
 * 
 * @since 1.3.0
 */
function hmpl_og_description($description = '') {
	
	if ( hmpl_ad_has_og_plugin() !== false ) 
		return;
	
	if ( empty ($description) )
		$description = hmpl_ad_generate_description();
	
	$output = '<meta property="og:description" content="' . esc_attr($description) . '" />' . "\r\n";
	
	return $output;
}

/**
 * Render the locale
 * 
 * @uses hmpl_ad_has_og_plugin()
 * 
 * @since 1.0.0
 */
function hmpl_og_locale($locale = '') {
	
	if ( hmpl_ad_has_og_plugin() !== false ) 
		return;
	
	if ( empty ($locale) )
		$locale = get_locale();
	
	$output = '<meta property="og:locale" content="' . esc_attr($locale) . '" />' . "\r\n";
	
	return $output;
}

/**
 * Get the title
 *
 * @uses hmpl_ad_has_og_plugin()
 *
 * @since 1.0.0
 */
function hmpl_og_title($title = '') {
	
	if ( hmpl_ad_has_og_plugin() !== false )
		return;
	
	global $post;
	
	if ( empty ($title) ) {	
		$blogname = get_bloginfo('name');
		
		if ( is_front_page() ) {
			$tagline = get_bloginfo( 'description', 'raw');
			
			$title = sprintf( '%s - %s', $blogname, $tagline);
		} else {
			$posttitle = get_the_title($post);
			
			$title = $posttitle . ' - ' . $blogname;
		}
	}
	
	$output = '<meta property="og:title" content="' . esc_attr($title) . '" />' . "\r\n";
	
	return $output;
}

/**
 * Get the type
 *
 * @uses hmpl_ad_has_og_plugin()
 *
 * @since 1.1.0
 */
function hmpl_og_type($type = '') {	
	
	if ( hmpl_ad_has_og_plugin() !== false )
		return;
	
	if ( empty ($type) ) {	
		$type = 'website';
		
		if ( is_single() ) {
			$type = 'article';
		}
		
		if ( is_front_page() || is_page() ) {
			$type = 'website';
		}
		
		if ( is_author() ) {
			$type = 'profile';
		}
	}
	
	$output = '<meta property="og:type" content="' . esc_attr($type) . '" />' . "\r\n";
	
	return $output;
}

/**
 * LD+JSON search helper output
 * 
 * @uses hmpl_ad_has_json_ld_plugin()
 * 
 * @since 1.2.0
 * @output echo in header
 */
function hmpl_ld_json($render = '') {
	
	//* Check for WPSEO LD+JSON
	if ( hmpl_ad_has_json_ld_plugin() !== false )
		return;
	
	//* Only display on front page
	if ( !is_front_page() )
		return;
		
	global $blog_id;
	
	$context = json_encode( 'http://schema.org' );
	$webtype = json_encode( 'WebSite' );
	$url = esc_url( home_url( '/' ) );
	$name = json_encode( get_bloginfo('name') );
	$actiontype = json_encode( 'SearchAction' );
	$target = esc_url( home_url( '/' )) . '?s={search_term}';
	$queryaction = json_encode( 'required name=search_term' );
	
	if ( empty ($render) )
		$render = sprintf( '{"@context":%s,"@type":%s,"url":%s,"name":%s,"potentialAction":{"@type":%s,"target":%s,"query-input":%s}}', $context, $webtype, $url, $name, $actiontype, $target, $queryaction );
	
	$output = "<script type='application/ld+json'>" . $render . "</script>" . "\r\n";	
	
	return $output;
}

/**
 * Adds og:image
 *
 * @uses get_header_image
 *
 * @param string image url for image
 *
 * @since 1.3.0
 * @todo add filter for image (url)
 */
function hmpl_og_image($image = '') {
	
	if ( empty ($image) )
		$image = get_header_image();
	
	$headerimage = esc_url_raw($image);
	
	if ( !empty( $image ) )
		$output = '<meta property="og:image" content="' . $headerimage . '" />' . "\r\n";
	
	return $output;
}

/**
 * Adds og:url
 *
 * @uses wp
 *
 * @param string output	the output
 *
 * @since 1.3.0
 */
function hmpl_og_url($url = '') {
	
	//* if WPSEO is active
	if ( hmpl_ad_has_og_plugin() !== false )
		return;
	
	global $wp;
	
	if ( empty ($url) ) 
		$url = home_url( add_query_arg( array(), $wp->request ) );
	
	$output = '<meta property="og:url" content="' . esc_url_raw( $url ) . '" />' . "\r\n";
	
	return $output;
}

/**
 * Adds og:site_name
 *
 * @uses wp
 *
 * @param string output	the output
 *
 * @since 1.3.0
 */
function hmpl_og_sitename($sitename = '') {
	
	//* if WPSEO is active
	if ( hmpl_ad_has_og_plugin() !== false )
		return;
	
	if ( empty ($sitename) )
		$sitename = get_bloginfo('name');
		
	$output = '<meta property="og:site_name" content="' . esc_attr( $sitename ) . '" />' . "\r\n";
	
	return $output;
}

/**
 * Output the header meta and script
 *
 * @since 1.0.0
 *
 * @param blog_id : the blog id
 *
 * @uses hmpl_ad_description()
 * @uses hmpl_og_image()
 * @uses hmpl_og_locale()
 * @uses hmpl_og_type()
 * @uses hmpl_og_title()
 * @uses hmpl_og_description()
 * @uses hmpl_og_url()
 * @uses hmpl_og_sitename()
 * @uses hmpl_ld_json()
 *
 * @output echo in header
 */
function add_hmpl_meta_tags() {
	global $blog_id;
	
	$page_id = get_queried_object_id();
		
	/**
	 * Override Woo Themes Title
	 * @todo test this
	 */
	//add_filter( 'woo_title', 'hmpl_og_title', 99 );
	
	$output = wp_cache_get( 'hmpl_autodescription_output_' . $blog_id . '_' . $page_id );
	if ( false === $output ) {
			
		$output	= hmpl_ad_the_description()
				. hmpl_og_image()
				. hmpl_og_locale()
				. hmpl_og_type()
				. hmpl_og_title()
				. hmpl_og_description()
				. hmpl_og_url()
				. hmpl_og_sitename()
				. hmpl_ld_json()
				;
				
		wp_cache_set( 'hmpl_autodescription_output_' . $blog_id . '_' . $page_id, $output );
	}
	
	echo $output;	
}

/**
 * Run the plugin 
 *
 * @since 1.0.0
 * 
 * @filter hmpl_ad_load : Disables plugin output
 *						: add_filter('hmpl_ad_load', '__return_false');
 * @filter hmpl_ad_load_logged_out_only : Disabled plugin output based on object cache / user login
 *						: add_filter('hmpl_ad_load_logged_out_only', '__return_true'); // Force output for logged out only
 * 						: add_filter('hmpl_ad_load_logged_out_only', '__return_true'); // Force output for always 
 *
 * @param ad_wpmu_load : filter hmpl_ad_load
 * @param logged_out_only : filter hmpl_ad_load_logged_out_only
 */
function hmpl_auto_description_run() {
	
	$ad_wpmu_load = apply_filters( 'hmpl_ad_load', '__return_true' );
	
	if ($ad_wpmu_load !== false) {
		
		//* Set to logged out only if there's no object caching
		if ( ! wp_using_ext_object_cache() || ! file_exists( WP_CONTENT_DIR . '/object-cache.php') ) {
			$logged_out_only = true;
		} else {
			$logged_out_only = false;
		}
		
		$logged_out_only = apply_filters( 'hmpl_ad_load_logged_out_only', $logged_out_only );
		
		//* This could be combined with the conditional tag above. Not sure how to combine that with the filter yet.
		if ( $logged_out_only ) {
			$logged_in = is_user_logged_in();
		} else {
			$logged_in = false;
		}
	
		if ( ! $logged_in ) {
			//* Genesis only, checks if description is present
			$theme_info = wp_get_theme()->get('Template');

			if( $theme_info == 'genesis' ) {
				remove_action( 'genesis_meta', 'genesis_seo_meta_description', 10 );
				add_action( 'genesis_meta', 'add_hmpl_meta_tags', 10 );
			} else {
				add_action( 'wp_head', 'add_hmpl_meta_tags', 1 );
			}
		}
	}
}
add_action( 'init', 'hmpl_auto_description_run', 10 );

/* Start Meta boxes
----------------------------------------------------------------------------------------------------*/

/**
 * Initiate the SEO meta boxes
 *
 * @since 2.0.0
 */
function hmpl_auto_description_admin_run() {
	
	//* Don't show meta boxes if another SEO plugin is active
	if ( hmpl_ad_detect_seo_plugins() )
		return;
	
	$theme_info = wp_get_theme()->get('Template');
	
	//* Replace Genesis meta boxes with AutoDescription
	if( $theme_info == 'genesis' ) {
		remove_action( 'admin_menu', 'genesis_add_inpost_seo_box', 99 );
	}
	add_action( 'admin_menu', 'hmpl_ad_add_inpost_seo_box' );
	
}
//add_action( 'admin_menu', 'hmpl_auto_description_admin_run', 99); // not working yet

/**
 * Save post meta / custom field data for a post or page.
 *
 * It verifies the nonce, then checks we're not doing autosave, ajax or a future post request. It then checks the
 * current user's permissions, before finally* either updating the post meta, or deleting the field if the value was not
 * truthy.
 *
 * By passing an array of fields => values from the same metabox (and therefore same nonce) into the $data argument,
 * repeated checks against the nonce, request and permissions are avoided.
 *
 * @since 2.0.0
 *
 * @param array    $data         Key/Value pairs of data to save in '_field_name' => 'value' format.
 * @param string   $nonce_action Nonce action for use with wp_verify_nonce().
 * @param string   $nonce_name   Name of the nonce to check for permissions.
 * @param WP_Post|integer $post  Post object or ID.
 * @param integer  $deprecated   Deprecated (formerly accepted a post ID).
 *
 * @return mixed Return null if permissions incorrect, doing autosave, ajax or future post, false if update or delete
 *               failed, and true on success.
 *
 * @thanks StudioPress
 */
function hmpl_ad_save_custom_fields( array $data, $nonce_action, $nonce_name, $post ) {

	//* Verify the nonce
	if ( ! isset( $_POST[ $nonce_name ] ) || ! wp_verify_nonce( $_POST[ $nonce_name ], $nonce_action ) )
		return;

	//* Don't try to save the data under autosave, ajax, or future post.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return;
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
		return;
	if ( defined( 'DOING_CRON' ) && DOING_CRON )
		return;

	//* Grab the post object
	$post = get_post( $post );

	//* Don't save if WP is creating a revision (same as DOING_AUTOSAVE?)
	if ( 'revision' === get_post_type( $post ) )
		return;

	//* Check that the user is allowed to edit the post
	if ( ! current_user_can( 'edit_post', $post->ID ) )
		return;

	//* Cycle through $data, insert value or delete field
	foreach ( (array) $data as $field => $value ) {
		//* Save $value, or delete if the $value is empty
		if ( $value )
			update_post_meta( $post->ID, $field, $value );
		else
			delete_post_meta( $post->ID, $field );
	}

}

/**
 * Adds SEO Meta boxes beneath every page/post edit screen
 * 
 * @since 2.0.0
 * @dependable Genesis : If a Genesis theme is active, don't activate the meta boxes
 * 
 * @options Genesis : Merge these options with Genesis options, either/or.
 *
 * @thanks StudioPress :)
 */
function hmpl_ad_add_inpost_seo_box() {
	
	//* @todo: check this
	foreach ( (array) get_post_types( array( 'public' => true, /*'capability_type' => array() */ ) ) as $type ) {
		if ( post_type_supports( $type ) ) {
		//	if ( post_type_supports ( 'page' ) )
				add_meta_box( 'hmpl_ad_inpost_seo_box', __( 'Page SEO Settings', 'AutoDescription' ), 'hmpl_ad_inpost_seo_box', $type, 'normal', 'high' );
		//	if ( post_type_supports ( 'post' ) )
		//		add_meta_box( 'hmpl_ad_inpost_seo_box', __( 'Post SEO Settings', 'AutoDescription' ), 'hmpl_ad_inpost_seo_box', $type, 'normal', 'high' );
		}
	}

}

/**
 * Mark up content with code tags.
 *
 * Escapes all HTML, so `<` gets changed to `&lt;` and displays correctly.
 *
 * Used almost exclusively within labels and text in user interfaces added by Genesis.
 *
 * @since 2.0.0
 *
 * @param  string $content Content to be wrapped in code tags.
 *
 * @return string Content wrapped in code tags.
 */
function hmpl_ad_code( $content ) {

	return '<code>' . esc_html( $content ) . '</code>';

}

/**
 * Callback for in-post SEO meta box.
 *
 * @since 2.0.0
 *
 * @uses hmpl_ad_get_custom_field() Get custom field value.
 */
function hmpl_ad_inpost_seo_box() {

	wp_nonce_field( 'genesis_inpost_seo_save', 'genesis_inpost_seo_nonce' );
	?>

	<p><label for="genesis_title"><b><?php _e( 'Custom Document Title', 'AutoDescription' ); ?></b> <abbr title="&lt;title&gt; Tag">[?]</abbr> <span class="hide-if-no-js"><?php printf( __( 'Characters Used: %s', 'AutoDescription' ), '<span id="genesis_title_chars">'. mb_strlen( hmpl_ad_get_custom_field( '_genesis_title' ) ) .'</span>' ); ?></span></label></p>
	<p><input class="large-text" type="text" name="genesis_seo[_genesis_title]" id="genesis_title" value="<?php echo esc_attr( hmpl_ad_get_custom_field( '_genesis_title' ) ); ?>" /></p>

	<p><label for="genesis_description"><b><?php _e( 'Custom Post/Page Meta Description', 'AutoDescription' ); ?></b> <abbr title="&lt;meta name=&quot;description&quot; /&gt;">[?]</abbr> <span class="hide-if-no-js"><?php printf( __( 'Characters Used: %s', 'AutoDescription' ), '<span id="genesis_description_chars">'. mb_strlen( hmpl_ad_get_custom_field( '_genesis_description' ) ) .'</span>' ); ?></span></label></p>
	<p><textarea class="widefat" name="genesis_seo[_genesis_description]" id="genesis_description" rows="4" cols="4"><?php echo esc_textarea( hmpl_ad_get_custom_field( '_genesis_description' ) ); ?></textarea></p>

	<p><label for="genesis_keywords"><b><?php _e( 'Custom Post/Page Meta Keywords, comma separated', 'AutoDescription' ); ?></b> <abbr title="&lt;meta name=&quot;keywords&quot; /&gt;">[?]</abbr></label></p>
	<p><input class="large-text" type="text" name="genesis_seo[_genesis_keywords]" id="genesis_keywords" value="<?php echo esc_attr( hmpl_ad_get_custom_field( '_genesis_keywords' ) ); ?>" /></p>

	<p><label for="genesis_canonical"><b><?php _e( 'Custom Canonical URL', 'AutoDescription' ); ?></b> <a href="http://www.mattcutts.com/blog/canonical-link-tag/" target="_blank" title="&lt;link rel=&quot;canonical&quot; /&gt;">[?]</a></label></p>
	<p><input class="large-text" type="text" name="genesis_seo[_genesis_canonical_uri]" id="genesis_canonical" value="<?php echo esc_url( hmpl_ad_get_custom_field( '_genesis_canonical_uri' ) ); ?>" /></p>

	<p><label for="genesis_redirect"><b><?php _e( 'Custom Redirect URL', 'AutoDescription' ); ?></b> <a href="http://www.google.com/support/webmasters/bin/answer.py?hl=en&amp;answer=93633" target="_blank" title="301 Redirect">[?]</a></label></p>
	<p><input class="large-text" type="text" name="genesis_seo[redirect]" id="genesis_redirect" value="<?php echo esc_url( hmpl_ad_get_custom_field( 'redirect' ) ); ?>" /></p>

	<br />

	<p><b><?php _e( 'Robots Meta Settings', 'AutoDescription' ); ?></b></p>

	<p>
		<label for="genesis_noindex"><input type="checkbox" name="genesis_seo[_genesis_noindex]" id="genesis_noindex" value="1" <?php checked( hmpl_ad_get_custom_field( '_genesis_noindex' ) ); ?> />
		<?php printf( __( 'Apply %s to this post/page', 'AutoDescription' ), hmpl_ad_code( 'noindex' ) ); ?> <a href="http://yoast.com/articles/robots-meta-tags/" target="_blank">[?]</a></label><br />

		<label for="genesis_nofollow"><input type="checkbox" name="genesis_seo[_genesis_nofollow]" id="genesis_nofollow" value="1" <?php checked( hmpl_ad_get_custom_field( '_genesis_nofollow' ) ); ?> />
		<?php printf( __( 'Apply %s to this post/page', 'AutoDescription' ), hmpl_ad_code( 'nofollow' ) ); ?> <a href="http://yoast.com/articles/robots-meta-tags/" target="_blank">[?]</a></label><br />

		<label for="genesis_noarchive"><input type="checkbox" name="genesis_seo[_genesis_noarchive]" id="genesis_noarchive" value="1" <?php checked( hmpl_ad_get_custom_field( '_genesis_noarchive' ) ); ?> />
		<?php printf( __( 'Apply %s to this post/page', 'AutoDescription' ), hmpl_ad_code( 'noarchive' ) ); ?> <a href="http://yoast.com/articles/robots-meta-tags/" target="_blank">[?]</a></label>
	</p>
	<?php

}

/**
 * Return custom field post meta data.
 *
 * Return only the first value of custom field. Return false if field is blank or not set.
 *
 * @since 2.0.0
 *
 * @param string $field Custom field key.
 *
 * @return string|boolean Return value or false on failure.
 * 
 * @thanks StudioPress :)
 */
function hmpl_ad_get_custom_field( $field ) {

	if ( null === get_the_ID() )
		return '';

	$custom_field = get_post_meta( get_the_ID(), $field, true );

	if ( ! $custom_field )
		return '';

	//* Return custom field, slashes stripped, sanitized if string
	return is_array( $custom_field ) ? stripslashes_deep( $custom_field ) : stripslashes( wp_kses_decode_entities( $custom_field ) );

}

/**
 * Save the SEO settings when we save a post or page.
 *
 * Some values get sanitized, the rest are pulled from identically named subkeys in the $_POST['genesis_seo'] array.
 *
 * @since 0.1.3
 *
 * @uses hmpl_ad_save_custom_fields() Perform checks and saves post meta / custom field data to a post or page.
 *
 * @param integer  $post_id Post ID.
 * @param stdClass $post    Post object.
 *
 * @return mixed Returns post id if permissions incorrect, null if doing autosave, ajax or future post, false if update
 *               or delete failed, and true on success.
 */
function hmpl_ad_inpost_seo_save( $post_id, $post ) {

	if ( ! isset( $_POST['genesis_seo'] ) )
		return;

	//* Merge user submitted options with fallback defaults
	$data = wp_parse_args( $_POST['genesis_seo'], array(
		'_genesis_title'         => '',
		'_genesis_description'   => '',
		'_genesis_keywords'      => '',
		'_genesis_canonical_uri' => '',
		'redirect'               => '',
		'_genesis_noindex'       => 0,
		'_genesis_nofollow'      => 0,
		'_genesis_noarchive'     => 0,
	) );

	//* Sanitize the title, description, and tags
	foreach ( (array) $data as $key => $value ) {
		if ( in_array( $key, array( '_genesis_title', '_genesis_description', '_genesis_keywords' ) ) )
			$data[ $key ] = strip_tags( $value );
	}

	hmpl_ad_save_custom_fields( $data, 'hmpl_ad_inpost_seo_save', 'genesis_inpost_seo_nonce', $post );

}
//add_action( 'save_post', 'hmpl_ad_inpost_seo_save', 1, 2 );