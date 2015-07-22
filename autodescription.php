<?php 
/**
 * Plugin Name: AutoDescription
 * Plugin URI: https://wordpress.org/plugins/autodescription/
 * Description: Automatically adds a description if previously empty based upon content and adds Open Graph tags.
 * Version: 2.1.1
 * Author: Sybre Waaijer
 * Author URI: https://cyberwire.nl/
 * License: GPLv2 or later
 * Text Domain: AutoDescription
 */

/** 
 * Fully supports Genesis themes, this plugin is build upon it.
 * Fully supports WordPress SEO (by Yoast). It pretty much disables this plugin for now, protection functions aren't really helping though.
 * Please notify me if you notice an issue with a specific theme or plugin.
 */
 
/**
 * Dev todo
 *
 * 2.2.0+	: Added global & front-page SEO settings
 *			: Give more reasons for this plugin to be standalone
 *			: Sitemaps! // Delaying this because there are so many good options out there already.
 *
 * 3.0.0	: I'm nothing like Valve.
 * 
 * + Coming soon
 *
 * @todo SEO things :	article:publisher	// facebook uri -> site settings
 *					:	fb:app_id			// facebook appid  -> site settings
 *					:	twitter:card		// twittercard -> site settings
 *					:	twitter:description	// Same as description 
 *					:	twitter:title		// Custom? -> site settings
 *					:	twitter:site		// @someone -> site settings
 *					:	twitter:domain		// twitterdomainname -> site settings
 *					:	twitter:image:src 	// Same as og:image
 *					:	ld+json organisation -> site settings
 *					:	msvalidate			// bing code -> site settings
 *					: 	google-site-verification // google code -> site settings
 *					: 	next/prev 			// blog navigation -> todo at theme level?
 *
 * Big thanks to StudioPress for releasing their software under GPL-2.0+, saved me a LOT of work figuring out things.
 * It also made my life easier :D Now I don't have to update my site's SEO meta (almost all my sites use Genesis)
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
 * Defined constants
 *
 * @since 2.0.0
 */
function hmpl_ad_constants() {
	define( 'HMPL_AD_SEO_SETTINGS_FIELD', apply_filters( 'hmpl_ad_settings_field', 'hmpl-ad-seo-settings' ) ); // this is called wrong, should be in a function -> check genesis
	define( 'HMPL_AD_VERSION', '2.1.0' );
}
add_action( 'init', 'hmpl_ad_constants');
add_action( 'admin_init', 'hmpl_ad_constants');

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
 * Allow this plugin to load through filter
 *
 * Applies hmpl_ad_load filters.
 *
 * return bool load 	allow loading of plugin
 *
 * @since 2.1.0
 */
function hmpl_ad_load() {
	$load = apply_filters( 'hmpl_ad_load', '__return_true' );
	
	return $load;
}

/**
 * Checks if the website is using a genesis theme
 *
 * return bool
 *
 * @since 2.1.0
 */
function hmpl_ad_is_genesis() {
	
	$theme_info = wp_get_theme()->get('Template');
	
	if( $theme_info == 'genesis' )
		return true;
		
	return false;
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
 * @note isn't used in hmpl_ad_og_image()
 *
 * @uses hmpl_ad_detect_plugin()
 *
 * @since 1.3.0
 * @boolean true if exists, false if not
 */
function hmpl_ad_has_og_plugin() {
	
	if ( hmpl_ad_detect_plugin( $plugins = array('classes' => array('WPSEO_OpenGraph', 'All_in_One_SEO_Pack_Opengraph') ) ) )
		return true;

	return false;
}

/**
 * Detects if plugins outputting ld+json exists
 *
 * @uses hmpl_ad_detect_plugin()
 *
 * @since 1.3.0
 *
 * @return bool
 */
function hmpl_ad_has_json_ld_plugin() {
	
	if ( hmpl_ad_detect_plugin( $plugins = array('classes' => array('WPSEO_JSON_LD',) ) ) )
		return true;

	return false;
}

/**
 * Detecs sitemap plugins
 *
 * @uses hmpl_ad_detect_plugin()
 *
 * @since 2.1.0
 *
 * @return bool 
 */
function hmpl_ad_has_sitemap_plugin() {
	
	if ( hmpl_ad_detect_plugin( $plugins = array(
			'classes' => array('GoogleSitemapGeneratorLoader', 'xml_sitemaps', 'All_in_One_SEO_Pack_Sitemap',), 
			'functions' => array('wpss_init', 'gglstmp_sitemapcreate',), 
			) ) )
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
	global $post_id;
	
	$the_post = get_post($post_id);
	$content = $the_post->post_content;
	
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
 * @param string description the description
 *
 * @todo 	Save output into the option for reduced load.
 * 			Counter intuitive?
 *
 * @return string output	The description
 */
function hmpl_ad_generate_description($description = '') {
	global $wp_query;
		
	//* Fetch WPSEO description (this whole function isn't processed with WPSEO as of now, so this is useless)
	if ( method_exists('WPSEO_Frontend', 'generate_metadesc') )
		$description = WPSEO_Frontend::get_instance()->metadesc( false );
	
	if ( empty($description) ) {
		$description = hmpl_ad_get_custom_field( '_genesis_description' ) ? hmpl_ad_get_custom_field( '_genesis_description' ) : '';
		
		$genesis = hmpl_ad_is_genesis();
		
		if( $genesis ) {
			if ( is_front_page() ) {	
				//* @Todo create this option
				//	$description = hmpl_ad_get_seo_option( 'home_description' ) ? hmpl_ad_get_seo_option( 'home_description' ) : get_bloginfo( 'description' );	
				
				//* Genesis fallback until option is created
				//	if ( empty($description) )
					$description = genesis_get_seo_option( 'home_description' ) ? genesis_get_seo_option( 'home_description' ) : '';
			}
			if ( is_singular() ) {
				if ( hmpl_ad_get_custom_field( '_genesis_description' ) )					
					$description = hmpl_ad_get_custom_field( '_genesis_description' ) ? hmpl_ad_get_custom_field( '_genesis_description' ) : '';
				
				//* Genesis fallback
				if ( empty($description) ) 
					$description = genesis_get_custom_field( '_genesis_description' );
			}
			if ( is_category() ) {
				$term = $wp_query->get_queried_object();
				$description = ! empty( $term->meta['description'] ) ? $term->meta['description'] : ''; // genesis only, for now
			}
			if ( is_tag() ) {
				$term = $wp_query->get_queried_object();
				$description = ! empty( $term->meta['description'] ) ? $term->meta['description'] : ''; // genesis only, for now
			}
			if ( is_tax() ) {
				$term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
				$description = ! empty( $term->meta['description'] ) ? wp_kses_stripslashes( wp_kses_decode_entities( $term->meta['description'] ) ) : ''; // genesis only, for now
			}
			if ( is_post_type_archive() && genesis_has_post_type_archive_support() ) {
				$description = genesis_get_cpt_option( 'description' ) ? genesis_get_cpt_option( 'description' ) : ''; // genesis only, for now
			}
		}
	}		
	
	//* Fetch author page description
	if ( is_author() ) {
		$user_description = get_the_author_meta( 'meta_description', (int) get_query_var( 'author' ) );
		$description = $user_description ? $user_description : '';
	}
		
	//* Generate static front-page description from option or 
	//* @TODO: create the option
	/*
	if ( is_front_page() ) {
		$description = hmpl_ad_get_seo_option( 'home_description' ) ? hmpl_ad_get_seo_option( 'home_description' ) : get_bloginfo( 'description' );
	}
	*/
	
	//* Fetch AutoDescription description if not found yet
	if ( !is_string($description) || empty($description) )
		$description = hmpl_ad_get_custom_field( '_genesis_description' ) ? hmpl_ad_get_custom_field( '_genesis_description' ) : '';
	
	//* Still description found? Create auto description based on content
	// Cache this? Cache everything? D:
	if ( !is_string($description) || empty($description) ) { //* HEAVY CODE (adds .5s processing time with 241k characters on 6-core 2.4GHz prefork)
		global $post;
	
		//* These values should've been escaped prior to fetching them.
		$title = get_the_title($post);
		$blogname = get_bloginfo('name');
		$excut = '';
		$sep = '';
		$on = __( 'on', 'AutoDescription' ); // Post Title "on" Blog Name - the excerpt
		
		$hmpl_excerpt = hmpl_get_excerpt_by_id();
		
		$hmpl_maxcharlength = 160 - mb_strlen($title . $on . $blogname);
		$hmpl_excerptlength = mb_strlen( $hmpl_excerpt );
		
		if ( $hmpl_excerptlength > $hmpl_maxcharlength ) {
			
			$subex = mb_substr( $hmpl_excerpt, 0, $hmpl_maxcharlength );
						
			$exwords = explode( ' ', $subex );
			
			$excut = - ( mb_strlen( $exwords[ count( $exwords ) - 1 ] ) );
						
			if ( $excut < 0 ) {
				$hmpl_excerpt = mb_substr( $subex, 0, $excut );				
			} else {
				$hmpl_excerpt = $subex;
			}
			$excut = '...';
		}
		$hmpl_excerpt = str_replace(' ...', '...', $hmpl_excerpt . $excut);
				
		if ( !empty($hmpl_excerpt ) )
			$sep = '-';
		
		$description = sprintf( '%s %s %s %s %s', $title, $on, $blogname, $sep, $hmpl_excerpt);		
	}
	
	$output = trim( $description );
	
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
function hmpl_ad_og_description($description = '') {
	
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
function hmpl_ad_og_locale($locale = '') {
	
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
 * @since 1.0.0
 *
 * 
 */
function hmpl_ad_title($title = '', $sep = '', $seplocation = 'right') {	
	global $post,$wp_query;
	
	if ( is_feed() )
		return trim( $title );
	
	/**
	 * Filters the seperator (too lazy to document this further D:)
	 *
	 * @since 2.0.5
	 */
	$sep = apply_filters( 'hmpl_ad_title_seperator', $sep = '-');
	
	//* Get title from custom field, empty it if it's not there to override the default title
	$title = hmpl_ad_get_custom_field( '_genesis_title' ) ? hmpl_ad_get_custom_field( '_genesis_title' ) : '';
	
	if ( empty ($title) ) {
		$genesis = hmpl_ad_is_genesis();		
		if( $genesis ) {
			$title = genesis_get_custom_field( '_genesis_title' ) ? genesis_get_custom_field( '_genesis_title' ) : '';
		}
	}
	
	if ( empty ($title) ) {	
		$blogname = get_bloginfo('name');
		
		if ( is_front_page() ) {
			$tagline = get_bloginfo( 'description', 'raw');
			
			$title = ! empty( $tagline ) ? $blogname . " $sep " . $tagline : $blogname;
		}
		
		if ( empty ($title) ) {
			$posttitle = get_the_title($post);
			
			$title = $posttitle . " $sep " . $blogname;
		}
		
		if ( is_category() ) {
			$term  = $wp_query->get_queried_object();
			$title = ! empty( $term->meta['doctitle'] ) ? $blogname . " $sep " . $term->meta['doctitle'] : $title;
		}

		if ( is_tag() ) {
			$term  = $wp_query->get_queried_object();
			$title = ! empty( $term->meta['doctitle'] ) ? $blogname . " $sep " . $term->meta['doctitle'] : $title;
		}

		if ( is_tax() ) {
			$term  = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
			$title = ! empty( $term->meta['doctitle'] ) ? $blogname . " $sep " . wp_kses_stripslashes( wp_kses_decode_entities( $term->meta['doctitle'] ) ) : $title;
		}

		if ( is_author() ) {
			$user_title = get_the_author_meta( 'doctitle', (int) get_query_var( 'author' ) );
			$title      = $user_title ? $blogname . " $sep " . $user_title : $title;
		}
		
		if ( is_404() ) {
			$title 		= '404' . " $sep " . $blogname;
		}
		
	}
	
	$title = esc_html( trim( $title ) );
	
	return $title;
}

/**
 * Process the title to WordPress
 *
 * @uses hmpl_ad_title()
 * @uses hmpl_ad_has_og_plugin()
 *
 * @since 2.0.3
 */
function hmpl_ad_og_title() {
	
	if ( hmpl_ad_has_og_plugin() !== false )
		return;
	
	$output = '<meta property="og:title" content="' . hmpl_ad_title( '' ) . '" />' . "\r\n";
	
	return $output;
}
 
/**
 * Get the type
 *
 * @uses hmpl_ad_has_og_plugin()
 *
 * @since 1.1.0
 */
function hmpl_ad_og_type($type = '') {	
	
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
 * @output echo in header on front page
 */
function hmpl_ad_ld_json($render = '') {
	
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
	$target = esc_url( home_url( '/' ) ) . '?s={search_term}';
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
 * @filter hmpl_og_image_args 	: @param image the image url
 *								: @param override always use the set url
 *								: @param frontpage always use the set url on the front page
 * The image set in the filter will always be used as fallback
 *
 * @since 1.3.0
 *
 * @todo create options and upload area
 */
function hmpl_ad_og_image($image = '', $args = array() ) {
	global $post,$blog_id;
	
	$post_id = $post->ID;
		
	$defaults = array(
			'post_id'	=> $post_id,
			'size'		=> 'full',
			'icon'		=> 0,
			'attr'		=> '',
			'image'		=> '',
			'override'	=> false,
			'frontpage'	=> true,
		);
		
	/**
	 * @since 2.0.5
	 */
	$defaults = apply_filters( 'hmpl_og_image_args', $defaults, $args );
	
	$args = wp_parse_args( $args, $defaults );
	
	//* Get image from args filter
	if ( empty($image) )
		$image = $args['image'];
	
	/**
	 * Deprecated filter hmpl_og_image
	 * used for og_image url
	 *
	 * @since 2.0.5
	 */
	$deprecated = apply_filters( 'hmpl_og_image', $deprecated = '' );
	if ( !empty( $deprecated ) ) {
		_deprecated_function( 'hmpl_og_image', '2.0.5', 'hmpl_og_image_args()' );
		$image = $deprecated;
	}
	
	$is_front = $post_id == get_option( 'page_on_front' ) ? true : false;
	
	/**
	 * Bit confusing with so many arguments... but it works as intended
	 *
	 * Fetch image if
	 * + no image found (always go)
	 * + override is true (always go)
	 * + is not front page AND frontpage override is set to true
	 */
	if ( empty($image) || $args['override'] !== false || (!$is_front && $args['frontpage'] !== false) ) {			
		if ( has_post_thumbnail( $args['post_id'] ) ) {
			$id = get_post_thumbnail_id( $args['post_id'] );
			$src = wp_get_attachment_image_src( $id, $args['size'], $args['icon'], $args['attr'] );
			
			/* 
			// @todo
			// Still looking for a good way to implement this :)
			// Image needs to be saved somewhere, but I don't want to automatically fill up the library for unaware users. Create option?
			
			$w = $src[1];
			$h = $src[2];
			
			//* Prefered 1500px, resize it
			if ( $w > 1500 || $h > 1500 ) {
				//* Square
				if ( $w == $h ) {
					$w = 1500;
					$h = 1500;
					
				//* Landscape
				} elseif ( $w > $h ) {
					$dev = $w / 1500;
					
					$h = $h / $dev;
					
					$h = round( $h );
					$w = 1500;
				
				//* Portrait
				} elseif ( $h > $w ) {
					$dev = $h / 1500;
					
					$w = $w / $dev;
					
					$w = round( $w );
					$h = 1500;
				}
				
				// Save image here
				
				// Make custom callback function and place it higher up in this function to prevent double looping
				
				// Fetch the new image ID = $id
				
				$src = wp_get_attachment_image_src( $id, array($w,$h), $args['icon'], $args['attr'] );
			}
			*/
			
			$image = $src[0];
		}
	}
	
	//* Fallback: Get header image if exists
	if ( empty ($image) )
		$image = get_header_image();

	//* If there still is no image, get the "site avatar" from WPMUdev Avatars
	if ( empty ($image) ) {
		if ( is_plugin_active( 'avatars/avatars.php' ) ) {
			global $ms_avatar,$current_site;
			
			$size = '256';
			
			$file = $ms_avatar->blog_avatar_dir . $ms_avatar->encode_avatar_folder( $blog_id ) . '/blog-' . $blog_id . '-' . $size . '.png';
			
			if ( is_file( $file ) ) {
				$upload_dir = wp_upload_dir();
				$upload_url = $upload_dir['baseurl'];
				$debug = '0';
				
				// Isn't there a more elegant core option? =/
				// I'm basically backwards enginering the wp_upload_dir
				// function to get the base url without /sites/blogid or /blogid.
				if ( is_multisite() && ! ( is_main_network() && is_main_site() && defined( 'MULTISITE' ) ) ) {
					if ( ! get_site_option( 'ms_files_rewriting' ) ) {
						if ( defined( 'MULTISITE' ) ) {
							$upload_url = str_replace( '/sites/' . $blog_id, '', $upload_url );
							$debug = '1';
						} else {
							$upload_url = str_replace( '/' . $blog_id, '', $upload_url );
							$debug = '2';
						}
					} elseif ( defined( 'UPLOADS' ) && ! ms_is_switched() ) {
						// I'm not sure what to do here so I'm just gonna fall back to default
						// I'll wait for a bug report (most unlikely)
						$upload_url = $upload_url;
						$debug = '3';
					}
				}
					
				$avatars_url = trailingslashit( trailingslashit($upload_url) . basename(dirname($ms_avatar->blog_avatar_dir)) );
			//	$path = preg_replace( '/' . preg_quote( dirname($ms_avatar->blog_avatar_dir) . '/', '/') . '/', $avatars_url, $file ) . ' debug: ' . $debug . ' url: ' . $upload_url;
				$path = preg_replace( '/' . preg_quote( dirname($ms_avatar->blog_avatar_dir) . '/', '/') . '/', $avatars_url, $file );
			}
			$image = !empty( $path ) ? $path : '';
		}
	}
	
	if ( !empty( $image ) ) {
	//	$url = esc_url_raw( $image );
		$url = $image;
	} else {
		$url = '';
	}
	
	/**
	 * Always output
	 *
	 * @since 2.1.1
	 */
	$output = '<meta property="og:image" content="' . $url . '" />' . "\r\n";
		
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
function hmpl_ad_og_sitename($sitename = '') {
	
	//* if WPSEO is active
	if ( hmpl_ad_has_og_plugin() !== false )
		return;
	
	if ( empty ($sitename) )
		$sitename = get_bloginfo('name');
		
	$output = '<meta property="og:site_name" content="' . esc_attr( $sitename ) . '" />' . "\r\n";
	
	return $output;
}

/**
 * Creates canonical url
 *
 * @uses WPMUdev's domain mapping
 *
 * @param string output	the output
 *
 * @since 2.0.0
 */
function hmpl_the_url($url = '') {
	global $wp;
		
	if ( empty($url) ) {				
		
		//* Get url from options
		$url = hmpl_ad_get_custom_field( '_genesis_canonical_uri' ) ? hmpl_ad_get_custom_field( '_genesis_canonical_uri' ) : '';
		
		//* Genesis fallback
		if ( empty($url) ) {
			$genesis = hmpl_ad_is_genesis();
			if( $genesis ) {
				$url = genesis_get_custom_field( '_genesis_canonical_uri' ) ? genesis_get_custom_field( '_genesis_canonical_uri' ) : '';
			}
		}
		
		//* Generate URL
		if ( empty ($url) ) {
			
			//* Domain Mapping canonical url
			if ( is_plugin_active( 'domain-mapping/domain-mapping.php' ) ) {
				global $wpdb,$blog_id;
				
				//* Get the URL path
				$path = $wp->request;
				
				//* Check if the domain is mapped
				$mapped_domain = wp_cache_get('wap_mapped_domain_' . $blog_id, 'domain_mapping' );
				if ( false === $mapped_domain ) {
					$mapped_domain = $wpdb->get_var( $wpdb->prepare( "SELECT domain FROM {$wpdb->base_prefix}domain_mapping WHERE blog_id = %d", $blog_id ) ); //string
					wp_cache_set('wap_mapped_domain_' . $blog_id, $mapped_domain, 'domain_mapping', 3600 ); // 1 hour
				}
				
				if ( !empty($mapped_domain) ) {
					
					//* Fetch scheme
					$mappedscheme = wp_cache_get('wap_mapped_scheme_' . $blog_id, 'domain_mapping' );
					if ( false === $mappedscheme ) {
						$mappedscheme = $wpdb->get_var( $wpdb->prepare( "SELECT scheme FROM {$wpdb->base_prefix}domain_mapping WHERE blog_id = %d", $blog_id ) ); //bool
						wp_cache_set('wap_mapped_scheme_' . $blog_id, $mappedscheme, 'domain_mapping', 3600 ); // 1 hour
					}
					
					if ($mappedscheme === '1') {
						$scheme_full = 'https://';
						$scheme = 'https';
					} else if ($mappedscheme === '0') {
						$scheme_full = 'http://';
						$scheme = 'http';
					}
					
					// Put it all together
					$url = trailingslashit( $scheme_full . $mapped_domain ) . $path;				
				}
			}
			
			//* Non-domainmap URL
			if ( empty($url) ) {
				$url = home_url(add_query_arg(array(), $path));
				$scheme = is_ssl() ? 'https' : 'http';
			}
		}
	}
	
	$scheme = !empty($scheme) ? $scheme : '';
	
	$output = trailingslashit( esc_url( $url, $scheme ) );
	
	return $output;
}

/**
 * Outputs canonical url
 *
 * @since 2.0.6
 *
 * @uses hmpl_the_url()
 *
 * @return string canonical url meta
 */
function hmpl_ad_canonical() {
	
	//* if WPSEO is active
	if ( hmpl_ad_has_og_plugin() !== false )
		return;
	
	$output = '<link rel="canonical" href="' . hmpl_the_url() . '" />' . "\r\n";
	
	return $output;
}

/**
 * Adds og:url
 *
 * @return string og:url the url meta
 *
 * @since 1.3.0
 * 
 * @uses hmpl_the_url()
 */
function hmpl_ad_og_url() {
	
	//* if WPSEO is active
	if ( hmpl_ad_has_og_plugin() !== false )
		return;
	
	$output = '<meta property="og:url" content="' . hmpl_the_url() . '" />' . "\r\n";
	
	return $output;
}

/**
 * Output the `index`, `follow`, `noodp`, `noydir`, `noarchive` robots meta code in the document `head`.
 *
 * @since 2.0.0
 *
 * @uses genesis_get_seo_option()   Get SEO setting value.
 * @uses genesis_get_custom_field() Get custom field value.
 *
 * @global WP_Query $wp_query Query object.
 *
 * @return null Return early if blog is not public.
 */
function hmpl_ad_robots($output = '') {
	//* If the blog is private, then following logic is unnecessary as WP will insert noindex and nofollow
	if ( ! get_option( 'blog_public' ) )
		return;
	
	//* Genesis only, checks if description is present
	$genesis = hmpl_ad_is_genesis();
		
	if( $genesis ) {
		global $wp_query;
		
		//* Defaults
		$meta = array(
			'noindex'   => '',
			'nofollow'  => '',
			'noarchive' => genesis_get_seo_option( 'noarchive' ) ? 'noarchive' : '',
			'noodp'     => genesis_get_seo_option( 'noodp' ) ? 'noodp' : '',
			'noydir'    => genesis_get_seo_option( 'noydir' ) ? 'noydir' : '',
		);

		//* Check home page SEO settings, set noindex, nofollow and noarchive
		if ( is_front_page() ) {
			$meta['noindex']   = genesis_get_seo_option( 'home_noindex' ) ? 'noindex' : $meta['noindex'];
			$meta['nofollow']  = genesis_get_seo_option( 'home_nofollow' ) ? 'nofollow' : $meta['nofollow'];
			$meta['noarchive'] = genesis_get_seo_option( 'home_noarchive' ) ? 'noarchive' : $meta['noarchive'];
		}

		if ( is_category() ) {
			$term = $wp_query->get_queried_object();

			$meta['noindex']   = $term->meta['noindex'] ? 'noindex' : $meta['noindex'];
			$meta['nofollow']  = $term->meta['nofollow'] ? 'nofollow' : $meta['nofollow'];
			$meta['noarchive'] = $term->meta['noarchive'] ? 'noarchive' : $meta['noarchive'];

			$meta['noindex']   = genesis_get_seo_option( 'noindex_cat_archive' ) ? 'noindex' : $meta['noindex'];
			$meta['noarchive'] = genesis_get_seo_option( 'noarchive_cat_archive' ) ? 'noarchive' : $meta['noarchive'];

			//* noindex paged archives, if canonical archives is off
			$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
			$meta['noindex'] = $paged > 1 && ! genesis_get_seo_option( 'canonical_archives' ) ? 'noindex' : $meta['noindex'];
		}

		if ( is_tag() ) {
			$term = $wp_query->get_queried_object();

			$meta['noindex']   = $term->meta['noindex'] ? 'noindex' : $meta['noindex'];
			$meta['nofollow']  = $term->meta['nofollow'] ? 'nofollow' : $meta['nofollow'];
			$meta['noarchive'] = $term->meta['noarchive'] ? 'noarchive' : $meta['noarchive'];

			$meta['noindex']   = genesis_get_seo_option( 'noindex_tag_archive' ) ? 'noindex' : $meta['noindex'];
			$meta['noarchive'] = genesis_get_seo_option( 'noarchive_tag_archive' ) ? 'noarchive' : $meta['noarchive'];

			//* noindex paged archives, if canonical archives is off
			$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
			$meta['noindex'] = $paged > 1 && ! genesis_get_seo_option( 'canonical_archives' ) ? 'noindex' : $meta['noindex'];
		}

		if ( is_tax() ) {
			$term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );

			$meta['noindex']   = $term->meta['noindex'] ? 'noindex' : $meta['noindex'];
			$meta['nofollow']  = $term->meta['nofollow'] ? 'nofollow' : $meta['nofollow'];
			$meta['noarchive'] = $term->meta['noarchive'] ? 'noarchive' : $meta['noarchive'];

			//* noindex paged archives, if canonical archives is off
			$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
			$meta['noindex'] = $paged > 1 && ! genesis_get_seo_option( 'canonical_archives' ) ? 'noindex' : $meta['noindex'];
		}

		if ( is_post_type_archive() && genesis_has_post_type_archive_support() ) {
			$meta['noindex']   = genesis_get_cpt_option( 'noindex' ) ? 'noindex' : $meta['noindex'];
			$meta['nofollow']  = genesis_get_cpt_option( 'nofollow' ) ? 'nofollow' : $meta['nofollow'];
			$meta['noarchive'] = genesis_get_cpt_option( 'noarchive' ) ? 'noarchive' : $meta['noarchive'];

			//* noindex paged archives, if canonical archives is off
			$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
			$meta['noindex'] = $paged > 1 && ! genesis_get_seo_option( 'canonical_archives' ) ? 'noindex' : $meta['noindex'];
		}

		if ( is_author() ) {
			$meta['noindex']   = get_the_author_meta( 'noindex', (int) get_query_var( 'author' ) ) ? 'noindex' : $meta['noindex'];
			$meta['nofollow']  = get_the_author_meta( 'nofollow', (int) get_query_var( 'author' ) ) ? 'nofollow' : $meta['nofollow'];
			$meta['noarchive'] = get_the_author_meta( 'noarchive', (int) get_query_var( 'author' ) ) ? 'noarchive' : $meta['noarchive'];

			$meta['noindex']   = genesis_get_seo_option( 'noindex_author_archive' ) ? 'noindex' : $meta['noindex'];
			$meta['noarchive'] = genesis_get_seo_option( 'noarchive_author_archive' ) ? 'noarchive' : $meta['noarchive'];

			//* noindex paged archives, if canonical archives is off
			$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
			$meta['noindex'] = $paged > 1 && ! genesis_get_seo_option( 'canonical_archives' ) ? 'noindex' : $meta['noindex'];
		}

		if ( is_date() ) {
			$meta['noindex']   = genesis_get_seo_option( 'noindex_date_archive' ) ? 'noindex' : $meta['noindex'];
			$meta['noarchive'] = genesis_get_seo_option( 'noarchive_date_archive' ) ? 'noarchive' : $meta['noarchive'];
		}

		if ( is_search() ) {
			$meta['noindex']   = genesis_get_seo_option( 'noindex_search_archive' ) ? 'noindex' : $meta['noindex'];
			$meta['noarchive'] = genesis_get_seo_option( 'noarchive_search_archive' ) ? 'noarchive' : $meta['noarchive'];
		}

		if ( is_singular() ) {			
			
			$meta['noindex']   = hmpl_ad_get_custom_field( '_genesis_noindex' ) ? 'noindex' : $meta['noindex'];
			if ( empty($meta['noindex']) )
				$meta['noindex']   = genesis_get_custom_field( '_genesis_noindex' ) ? 'noindex' : $meta['noindex'];
			
			$meta['nofollow']  = hmpl_ad_get_custom_field( '_genesis_nofollow' ) ? 'nofollow' : $meta['nofollow'];
			if ( empty($meta['nofollow']) )
				$meta['nofollow']  = genesis_get_custom_field( '_genesis_nofollow' ) ? 'nofollow' : $meta['nofollow'];
			
			$meta['noarchive'] = hmpl_ad_get_custom_field( '_genesis_noarchive' ) ? 'noarchive' : $meta['noarchive'];
			if ( empty($meta['noarchive']) )	
				$meta['noarchive'] = genesis_get_custom_field( '_genesis_noarchive' ) ? 'noarchive' : $meta['noarchive'];
			
		}
	} else {
		$meta = array(
			'noindex'   => '',
			'nofollow'  => '',
			//* @Todo: create these in global options
			'noarchive' => hmpl_ad_get_seo_option( 'noarchive' ) ? 'noarchive' : '',
			'noodp'     => true ? 'noodp' : 'noodp', // not optional atm
		//	'noodp'     => hmpl_ad_get_seo_option( 'noodp' ) ? 'noodp' : 'noodp',
			'noydir'    => true ? 'noydir' : 'noydir', // not optional atm
		//	'noydir'    => hmpl_ad_get_seo_option( 'noydir' ) ? 'noydir' : 'noydir',
		);
		
		// Fall back to default if not found
		$meta['noindex']   = hmpl_ad_get_custom_field( '_genesis_noindex' ) ? 'noindex' : $meta['noindex'];
		$meta['nofollow']  = hmpl_ad_get_custom_field( '_genesis_nofollow' ) ? 'nofollow' : $meta['nofollow'];
		$meta['noarchive'] = hmpl_ad_get_custom_field( '_genesis_noarchive' ) ? 'noarchive' : $meta['noarchive'];
	}

	//* Strip empty array items
	$meta = array_filter( $meta );

	//* Add meta if any exist
	if ( $meta )
		$output = sprintf( '<meta name="robots" content="%s" />' . "\r\n", implode( ',', $meta ) );

	return $output;
}

/**
 * Output the header meta and script
 *
 * @since 1.0.0
 *
 * @param blog_id : the blog id
 *
 * @filter hmpl_ad_pre 	: Adds content before
 * 						: @param before
 *						: cached
 * @filter hmpl_ad_pro 	: Adds content after
 *						: @param after
 *						: cached
 *
 * @uses hmpl_ad_description()
 * @uses hmpl_ad_og_image()
 * @uses hmpl_ad_og_locale()
 * @uses hmpl_ad_og_type()
 * @uses hmpl_ad_og_title()
 * @uses hmpl_ad_og_description()
 * @uses hmpl_ad_og_url()
 * @uses hmpl_ad_og_sitename()
 * @uses hmpl_ad_ld_json()
 * @uses hmpl_ad_canonical()
 *
 * @output echo in header
 */
function add_hmpl_meta_tags() {
	global $blog_id;
	
	$page_id = get_queried_object_id();
	
	$output = wp_cache_get( 'hmpl_autodescription_output_' . $blog_id . '_' . $page_id, 'autodescription' );
	if ( false === $output ) {
		
		$indicator = apply_filters( 'hmpl_ad_indicator', '__return_true' );
		
		$indicatorbefore = '';
		$indicatorafter = '';
		
		if ( $indicator !== false ) {
			$indicatorbefore = '<!-- Start AutoDescription by Sybre Waaijer -->' . "\r\n"; 
			$indicatorafter = '<!-- End AutoDescription by Sybre Waaijer -->' . "\r\n";
		}
				
		$before = apply_filters( 'hmpl_ad_pre', $before = '' );
		
		$robots = hmpl_ad_robots();
		
		//* Limit processing on 404 or search
		if ( !is_404() && !is_search() ) {
			$output	= hmpl_ad_the_description()
					. hmpl_ad_og_image()
					. hmpl_ad_og_locale()
					. hmpl_ad_og_type()
					. hmpl_ad_og_title()
					. hmpl_ad_og_description()
					. hmpl_ad_og_url()
					. hmpl_ad_og_sitename()
					. hmpl_ad_ld_json()
					. hmpl_ad_canonical()
					;
		} else {
			$output	= hmpl_ad_og_locale()
					. hmpl_ad_og_type()
					. hmpl_ad_og_title()
					. hmpl_ad_og_url()
					. hmpl_ad_og_sitename()
					. hmpl_ad_ld_json()
					. hmpl_ad_canonical()
					;
		}
		
		$after = apply_filters( 'hmpl_ad_pro', $after = '' );
		
		//* This should get its own function?
		// @todo create generator function
		$generator = apply_filters( 'hmpl_ad_generator', $generator = '' );
		
		if ( !empty($generator) )
			$generator = '<meta name="generator" content="' . esc_attr($generator) . '" />' . "\r\n";
		
		$output = "\r\n" . $indicatorbefore . $robots . $before . $output . $after . $generator . $indicatorafter;
		
		wp_cache_set( 'hmpl_autodescription_output_' . $blog_id . '_' . $page_id, $output, 'autodescription' );
	}
		
	echo $output;
}

/**
 * Run the plugin 
 *
 * @since 1.0.0
 * 
 * @filter hmpl_ad_load_logged_out_only : Disabled plugin output based on object cache / user login
 *						: add_filter('hmpl_ad_load_logged_out_only', '__return_true'); // Force output for logged out only
 * 						: add_filter('hmpl_ad_load_logged_out_only', '__return_true'); // Force output for always 
 *
 * @param logged_out_only : filter hmpl_ad_load_logged_out_only
 */
function hmpl_auto_description_run() {
	
	$load = hmpl_ad_load();
	
	if ($load !== false) {
		
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
					
		//* Remove Genesis output
		$genesis = hmpl_ad_is_genesis();
		
		if( $genesis ) {
			remove_action( 'genesis_meta', 'genesis_seo_meta_description', 10 ); //genesis seo
			remove_action( 'genesis_meta','genesis_seo_meta_keywords' ); //clean up residue (meta tags)
			remove_action( 'wp_head','genesis_canonical', 5 ); //genesis canonical
			remove_action( 'genesis_meta', 'genesis_robots_meta' ); //genesis robots
		}
		
		//* Remove canonical header from WP
		remove_action( 'wp_head', 'rel_canonical' );
		
		//* Remove generator tag from WP
		add_filter( 'the_generator', '__return_false' );
		
		//* Override WordPress Title
		add_filter( 'wp_title', 'hmpl_ad_title', 99, 3 );
		
		/**
		 * Override Woo Themes Title
		 */
		add_filter( 'woo_title', 'hmpl_ad_title', 99 );
		
		/**
		 * Always output on HMPL
		 */	
		if ( ! $logged_in || ( defined( 'IS_HMPL' ) && IS_HMPL ) ) {
			$genesis = hmpl_ad_is_genesis();
			
			if( $genesis ) {
				add_action( 'genesis_meta', 'add_hmpl_meta_tags', 5 );
			} else {
				add_action( 'wp_head', 'add_hmpl_meta_tags', 5 );
			}
		}
	}
}
add_action( 'init', 'hmpl_auto_description_run', 99 );

/**
 * Redirect singular page to an alternate URL.
 *
 * Called outside hmpl_auto_description_run
 *
 * @since 2.0.9
 */
function hmpl_ad_custom_field_redirect() {
	
	$load = hmpl_ad_load();
	
	//* Prevent redirect from options on uneditable pages or when this plugin is set to be disabled 
	if ( ! is_singular() || ! $load )
		return;
	
	if ( $url = hmpl_ad_get_custom_field( 'redirect' ) ) {
		
		$allow_external = apply_filters( 'hmpl_ad_301_external', '__return_true' );
			
		/** 
		 * If the URL is made relative, prevent scheme issues
		 * Always do this if IS_HMPL
		 *
		 * Removes http:// and https://
		 * 
		 * esc_url_raw uses is_ssl() to make the url good again :)
		 *
		 * @todo consider this to be an "always on" feature, only if link is pointing to the current domain of course.
		 */
		if ( ! $allow_external || ( defined( 'IS_HMPL' ) && IS_HMPL ) ) {
			$pattern 	= 	'/'
						.	'(((http)(s)?)\:)' 	// 1: http: https:
						. 	'(\/\/)'			// 2: slash slash
						.	'/s'
						;
			
			$url = preg_replace( $pattern, '', $url );
		}
		
		wp_redirect( esc_url_raw( $url ), 301 );
		exit;

	}

}
add_action( 'template_redirect', 'hmpl_ad_custom_field_redirect' );

/**
 * Add post state on edit.php to the page or post that has been altered
 *
 * Called outside hmpl_auto_description_run
 *
 * Applies `hmpl_ad_states` filters.
 *
 * @uses hmpl_ad_add_state
 *
 * @since 2.1.0
 */
function hmpl_ad_edit_screen_indication() {
	
	$load = hmpl_ad_load();
	
	$ad_allow_states = apply_filters( 'hmpl_ad_states', '__return_true' );
	
	//* Prevent this function from running if this plugin is set to disabled.
	if ( ! $load || ! $ad_allow_states )
		return;
	
	add_filter( 'display_post_states', 'hmpl_ad_add_state' ); 

}
add_action( 'admin_init', 'hmpl_ad_edit_screen_indication' );

/**
 * Adds post states in post/page edit.php query
 *
 * @param array states 		the current post state 
 * @param string redirected	hmpl_ad_get_custom_field( 'redirect' );
 * @param string noindex	hmpl_ad_get_custom_field( '_genesis_noindex' );
 *
 * @since 2.1.0
 */
function hmpl_ad_add_state( $states = array() ) {
	global $post;
	
	$post_id = $post->ID;
	
	$redirected = hmpl_ad_get_custom_field( 'redirect', $post_id ) ? true : false;	
	$noindex = hmpl_ad_get_custom_field( '_genesis_noindex', $post_id );
	$noindex = ! empty( $noindex ) ? true : false;
	
	//$noindex 	= ! empty( hmpl_ad_get_custom_field( '_genesis_noindex', $post_id ) ) ? true : false; // requires php 5.5 :(
	
	if ( $redirected === true )
		$states[] = __( 'Redirected', 'autodescription' );
	
	if ( $noindex === true )
		$states[] = __( 'NoIndex', 'autodescription' ); // No Search Index / Blocked from Search <- too long?
	
	return $states;
}

/* Start Meta boxes
 * ------------------------------------------------------------------------------------------------- */

/**
 * Removes the Genesis SEO meta box
 *
 * Called outside hmpl_auto_description_run
 *
 * @since 2.0.0
 */
function hmpl_auto_description_admin_run() {
	
	$load = hmpl_ad_load();
	
	if ($load !== false) {
		//* Replace Genesis meta boxes with AutoDescription
		
		$genesis = hmpl_ad_is_genesis();
		
		if( $genesis ) {
			remove_action( 'admin_menu', 'genesis_add_inpost_seo_box', 10);
		}
	}

}
add_action( 'after_setup_theme', 'hmpl_auto_description_admin_run', 10);

/**
 * Return SEO options from the SEO options database.
 *
 * @since todo 2.1.0+
 *
 * @uses hmpl_ad_get_option() Return option from the options table and cache result.
 * @uses HMPL_AD_SEO_SETTINGS_FIELD
 *
 * @param string  $key       Option name.
 * @param boolean $use_cache Optional. Whether to use the cache value or not. Defaults to true.
 *
 * @return mixed The value of this $key in the database.
 */
function hmpl_ad_get_seo_option( $key, $use_cache = true ) {
	return hmpl_ad_get_option( $key, HMPL_AD_SEO_SETTINGS_FIELD, $use_cache );
}

/**
 * Return option from the options table and cache result.
 *
 * Applies `hmpl_ad_options` filters.
 * Applies `genesis_options` filters.
 * This filter retrieves the (previous) values from Genesis if exists.
 *
 * Values pulled from the database are cached on each request, so a second request for the same value won't cause a
 * second DB interaction.
 *
 * @since 2.0.0
 *
 * @param string  $key        Option name.
 * @param string  $setting    Optional. Settings field name. Eventually defaults to null if not passed as an argument.
 * @param boolean $use_cache  Optional. Whether to use the cache value or not. Default is true.
 *
 * @return mixed The value of this $key in the database.
 *
 * @thanks StudioPress :)
 */
function hmpl_ad_get_option( $key, $setting = null, $use_cache = true ) {

	//* If we need to bypass the cache
	if ( ! $use_cache ) {
		$options = get_option( $setting );

		if ( ! is_array( $options ) || ! array_key_exists( $key, $options ) )
			return '';

		return is_array( $options[$key] ) ? stripslashes_deep( $options[$key] ) : stripslashes( wp_kses_decode_entities( $options[$key] ) );
	}

	//* Setup caches
	static $settings_cache = array();
	static $options_cache  = array();

	//* Check options cache
	if ( isset( $options_cache[$setting][$key] ) )
		//* Option has been cached
		return $options_cache[$setting][$key];

	//* Check settings cache
	if ( isset( $settings_cache[$setting] ) )
		//* Setting has been cached
		$options = apply_filters( 'hmpl_ad_options', $settings_cache[$setting], $setting );
	else
		//* Set value and cache setting
		$options = $settings_cache[$setting] = apply_filters( 'hmpl_ad_options', get_option( $setting ), $setting );

	//* Check for non-existent option
	if ( ! is_array( $options ) || ! array_key_exists( $key, (array) $options ) )
		//* Cache non-existent option
		$options_cache[$setting][$key] = '';
	else
		//* Option has not been previously been cached, so cache now
		$options_cache[$setting][$key] = is_array( $options[$key] ) ? stripslashes_deep( $options[$key] ) : stripslashes( wp_kses_decode_entities( $options[$key] ) );

	return $options_cache[$setting][$key];
}

/**
 * Render the SEO meta box
 * 
 * Called outside hmpl_auto_description_run
 *
 * Applies hmpl_ad_seobox filters. Return false to disable the meta boxes
 *
 * @since 2.0.0
 */
function hmpl_ad_add_inpost_seo_box_init() {
	if ( hmpl_ad_detect_seo_plugins() )
		return;
	
	$load = hmpl_ad_load();
	
	$hmpl_ad_seobox = apply_filters( 'hmpl_ad_seobox', '__return_true' );
	
	if ( $hmpl_ad_seobox !== false || $load !== false )
		add_action( 'add_meta_boxes', 'hmpl_ad_add_inpost_seo_box', 10 );
}
add_action( 'add_meta_boxes', 'hmpl_ad_add_inpost_seo_box_init', 9 );

/**
 * Adds SEO Meta boxes beneath every page/post edit screen
 *
 * High priority, this box is seen right below the post/page edit screen.
 * 
 * @since 2.0.0
 * 
 * @options Genesis : Merge these options with Genesis options. Prevents lost data.
 */
function hmpl_ad_add_inpost_seo_box() {	
	
	$post_page = array( get_post_types( array( 'public' => true ) ), 'post', 'page' );
	
	//* Adds meta boxes on Posts/Pages
	foreach ( $post_page as $type ) {
		$post = $type == 'post' ? __( 'Post', 'AutoDescription' ) : __( 'Page', 'AutoDescription' );
		
		add_meta_box( 'hmpl_ad_inpost_seo_box', sprintf( __( '%s SEO Settings', 'AutoDescription' ), $post ), 'hmpl_ad_inpost_seo_box', $type, 'normal', 'high', array( $post ) );
	}
	
	//* Add javascript file
	if ( $post_page[0] )
		add_action( 'admin_enqueue_scripts', 'hmpl_ad_enqueue_javascript', 11 );

}

/**
 * Javascript, adds counter to SEO title and description
 *
 * @since 2.0.2
 *
 * @used by hmpl_ad_add_inpost_seo_box
 *
 * @param $hook the current page : unused
 */
function hmpl_ad_enqueue_javascript( $hook ) {
	wp_enqueue_script( 'hmpl_ad_script', plugin_dir_url( __FILE__ ) . 'js/autodescription.js', array( 'jquery' ), HMPL_AD_VERSION, true );
}

/**
 * Callback for in-post SEO meta box.
 *
 * @since 2.0.0
 *
 * @param array post 	The post object
 * @param array type 	The post type callback arg (translated)
 *
 * @uses hmpl_ad_get_custom_field() Get custom field value.
 * @uses _genesis_meta to pull data from Genesis themes. *
 */
function hmpl_ad_inpost_seo_box( $post, $type = array() ) {
	
	wp_nonce_field( 'hmpl_ad_inpost_seo_save', 'hmpl_ad_inpost_seo_nonce' );
	
	//* Language shorttag to be used in Google help pages, 
	//* e.g. en for English, nl for Dutch, fi for Finish, de for German.
	$language = __( 'en', 'AutoDescription' );
	
	//* Pick one
	$type = $type['args'][0];
	//$type = reset( $type['args'] );
	
	?>

	<p>
		<label for="autodescription_title"><strong><?php _e( 'Custom Document Title', 'AutoDescription' ); ?></strong> 
			<a href="https://support.google.com/webmasters/answer/35624?hl=<?php echo $language; ?>#3" target="_blank" title="<?php _e( 'Recommended Length: 50 to 55 characters', 'AutoDescription' ) ?>">[?]</a>
			<span class="hide-if-no-js"><?php printf( __( 'Characters Used: %s', 'AutoDescription' ), '<span id="autodescription_title_chars">'. mb_strlen( hmpl_ad_get_custom_field( '_genesis_title' ) ) .'</span>' ); ?></span>
		</label>
	</p>
	<p>
		<input class="large-text" type="text" name="autodescription[_genesis_title]" id="autodescription_title" value="<?php echo esc_attr( hmpl_ad_get_custom_field( '_genesis_title' ) ); ?>" />
	</p>

	<p>
		<label for="autodescription_description">
			<strong><?php printf( __( 'Custom %s Meta Description', 'AutoDescription' ), $type ); ?></strong>
			<a href="https://support.google.com/webmasters/answer/35624?hl=<?php echo $language; ?>#1" target="_blank" title="<?php _e( 'Recommended Length: 150 to 160 characters', 'AutoDescription' ) ?>">[?]</a>
			<span class="hide-if-no-js"><?php printf( __( 'Characters Used: %s', 'AutoDescription' ), '<span id="autodescription_description_chars">'. mb_strlen( hmpl_ad_get_custom_field( '_genesis_description' ) ) .'</span>' ); ?></span>
		</label>
	</p>
	<p>
		<textarea class="widefat" name="autodescription[_genesis_description]" id="autodescription_description" rows="4" cols="4"><?php echo esc_textarea( hmpl_ad_get_custom_field( '_genesis_description' ) ); ?></textarea>
	</p>

	<p>
		<label for="autodescription_canonical">
			<strong><?php _e( 'Custom Canonical URL', 'AutoDescription' ); ?></strong> 
			<a href="https://support.google.com/webmasters/answer/139066?hl=<?php echo $language; ?>" target="_blank" title="&lt;link rel=&quot;canonical&quot; /&gt;">[?]</a>
		</label>
	</p>	
	<p>
		<input class="large-text" type="text" name="autodescription[_genesis_canonical_uri]" id="autodescription_canonical" value="<?php echo esc_url( hmpl_ad_get_custom_field( '_genesis_canonical_uri' ) ); ?>" />
	</p>

	<br>

	<p><strong><?php _e( 'Robots Meta Settings', 'AutoDescription' ); ?></strong></p>
	<p>
		<label for="autodescription_noindex"><input type="checkbox" name="autodescription[_genesis_noindex]" id="autodescription_noindex" value="1" <?php checked( hmpl_ad_get_custom_field( '_genesis_noindex' ) ); ?> />
			<?php printf( __( 'Apply %s to this %s', 'AutoDescription' ), hmpl_ad_code( 'noindex' ), $type ); ?> 
			<a href="https://support.google.com/webmasters/answer/93710?hl=<?php echo $language; ?>" target="_blank" title="<?php printf( __( 'Tell Search Engines not to show this page in their search results', 'AutoDescription' ) ) ?>">[?]</a>
		</label>
		
		<br>

		<label for="autodescription_nofollow"><input type="checkbox" name="autodescription[_genesis_nofollow]" id="autodescription_nofollow" value="1" <?php checked( hmpl_ad_get_custom_field( '_genesis_nofollow' ) ); ?> />
			<?php printf( __( 'Apply %s to this %s', 'AutoDescription' ), hmpl_ad_code( 'nofollow' ), $type ); ?> 
			<a href="https://support.google.com/webmasters/answer/96569?hl=<?php echo $language; ?>" target="_blank" title="<?php printf( __( 'Tell Search Engines not to follow links on this page', 'AutoDescription' ) ) ?>">[?]</a>
		</label>
		
		<br>

		<label for="autodescription_noarchive"><input type="checkbox" name="autodescription[_genesis_noarchive]" id="autodescription_noarchive" value="1" <?php checked( hmpl_ad_get_custom_field( '_genesis_noarchive' ) ); ?> />
			<?php printf( __( 'Apply %s to this %s', 'AutoDescription' ), hmpl_ad_code( 'noarchive' ), $type ); ?> 
			<a href="https://support.google.com/webmasters/answer/79812?hl=<?php echo $language; ?>" target="_blank" title="<?php printf( __( 'Tell Search Engines not to save a cached copy this page', 'AutoDescription' ) ) ?>">[?]</a>
		</label>
	</p>
	
	<br>
	
	<p>
		<label for="autodescription_redirect">
			<strong><?php _e( 'Custom 301 Redirect URL', 'AutoDescription' ); ?></strong> 
			<a href="https://support.google.com/webmasters/answer/93633?hl=<?php echo $language; ?>" target="_blank" title="301 Redirect">[?]</a>
		</label>
	</p>
	<p>
		<input class="large-text" type="text" name="autodescription[redirect]" id="genesis_redirect" value="<?php echo esc_url( hmpl_ad_get_custom_field( 'redirect' ) ); ?>" />
	</p>
	
	<?php

}

/**
 * Mark up content with code tags.
 *
 * Escapes all HTML, so `<` gets changed to `&lt;` and displays correctly.
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
 * Return custom field post meta data.
 *
 * Return only the first value of custom field. Return false if field is blank or not set.
 *
 * @since 2.0.0
 *
 * @param string $field	Custom field key.
 * @param int $post_id	The post ID
 *
 * @return string|boolean Return value or false on failure.
 * 
 * @thanks StudioPress :)
 */
function hmpl_ad_get_custom_field( $field, $post_id = null ) {

	if ( null === $post_id )
		$post_id = get_the_ID();
		
	if ( null === $post_id )
		return '';
	
	$custom_field = get_post_meta( $post_id, $field, true );

	if ( ! $custom_field )
		return '';

	//* Return custom field, slashes stripped, sanitized if string
	return is_array( $custom_field ) ? stripslashes_deep( $custom_field ) : stripslashes( wp_kses_decode_entities( $custom_field ) );

}

/**
 * Save the SEO settings when we save a post or page.
 *
 * Some values get sanitized, the rest are pulled from identically named subkeys in the $_POST['autodescription'] array.
 *
 * @since 2.0.0
 *
 * @uses hmpl_ad_save_custom_fields() Perform checks and saves post meta / custom field data to a post or page.
 *
 * @param integer  $post_id  Post ID.
 * @param stdClass $post     Post object.
 *
 * @return mixed Returns post id if permissions incorrect, null if doing autosave, ajax or future post, false if update
 *               or delete failed, and true on success.
 */
function hmpl_ad_inpost_seo_save( $post_id, $post ) {

	if ( ! isset( $_POST['autodescription'] ) )
		return;

	//* Merge user submitted options with fallback defaults
	$data = wp_parse_args( $_POST['autodescription'], array(
		'_genesis_title'         => '',
		'_genesis_description'   => '',
		'_genesis_canonical_uri' => '',
		'redirect'               => '',
		'_genesis_noindex'       => 0,
		'_genesis_nofollow'      => 0,
		'_genesis_noarchive'     => 0,
	) );

	foreach ( (array) $data as $key => $value ) {
		//* Sanitize the title and description
		if ( in_array( $key, array( '_genesis_title', '_genesis_description' ) ) ) {
			$data[ $key ] = strip_tags( $value );
		}
		
		//* Sanitize the URL. Make sure it's an absolute URL
		if ( in_array( $key, array( 'redirect' ) ) ) {
			$url = strip_tags($value);
			
			if ( !empty ($url) ) {
				
				$allow_external = apply_filters( 'hmpl_ad_301_external', '__return_true' );
			
				/** 
				 * Sanitize the redirect URL to only a relative link and removes first slash
				 * Always do this if IS_HMPL
				 *
				 * @requires WP 4.1.0 and up for best results
				 */
				if ( ! $allow_external || ( defined( 'IS_HMPL' ) && IS_HMPL ) )
					$url = ltrim(wp_make_link_relative( $url ), '/');
					
				//* URL pattern without path
				$pattern 	= 	'((((http)(s)?)?)\:)?' 	// 1: maybe http: https:
							. 	'(\/\/)?'				// 2: maybe slash slash
							. 	'((www.)?)'				// 3: maybe www.
							.	'(.*\.[a-zA-Z0-9]*)'	// 4: any legal domain with tld
							.	'(?:\/)'				// 5: trailing slash
							;
				
				//* If link is relative, make it full again
				if ( ! preg_match( $pattern, $url ) ) {
										
					//* The url is a relative path
					$path = $url;
					
					$ismapped = '0';
					
					//* Do some extra work on domain mapping
					if ( is_plugin_active( 'domain-mapping/domain-mapping.php' ) ) {
						global $wpdb,$blog_id;
						
						//* Check if the domain is mapped
						$mapped_domain = wp_cache_get('wap_mapped_domain_' . $blog_id, 'domain_mapping' );
						if ( false === $mapped_domain ) {
							$mapped_domain = $wpdb->get_var( $wpdb->prepare( "SELECT domain FROM {$wpdb->base_prefix}domain_mapping WHERE blog_id = %d", $blog_id ) ); //string
							wp_cache_set('wap_mapped_domain_' . $blog_id, $mapped_domain, 'domain_mapping', 3600 ); // 1 hour
						}
						
						if ( !empty($mapped_domain) ) {
							
							//* Set that the domain is mapped
							$ismapped = '1';
							
							//* Fetch scheme
							$mappedscheme = wp_cache_get('wap_mapped_scheme_' . $blog_id, 'domain_mapping' );
							if ( false === $mappedscheme ) {
								$mappedscheme = $wpdb->get_var( $wpdb->prepare( "SELECT scheme FROM {$wpdb->base_prefix}domain_mapping WHERE blog_id = %d", $blog_id ) ); //bool
								wp_cache_set('wap_mapped_scheme_' . $blog_id, $mappedscheme, 'domain_mapping', 3600 ); // 1 hour
							}
							
							if ($mappedscheme === '1') {
								$scheme_full = 'https://';
								$scheme = 'https';
							} else if ($mappedscheme === '0') {
								$scheme_full = 'http://';
								$scheme = 'http';
							}
							
							// Put it all together
							$url = trailingslashit( $scheme_full . $mapped_domain ) . $path;				
						}
						
					}
					
					//* Non-mapped URL
					if ( $ismapped !== '1' ) {
						$url = home_url(add_query_arg(array(), $path));
						$scheme = is_ssl() ? 'https' : 'http';
					}
					
					$scheme = !empty($scheme) ? $scheme : '';
				
					$url = esc_url_raw( $url, $scheme );
				
				}
			}
			
			$noqueries = apply_filters( 'hmpl_ad_301_noqueries', '__return_true' );
			
			/**
			 * Remove queries from the URL
			 * 
			 * Returns plain home url if $allow_external is set to false and only a query has been supplied
			 * But that's okay. The url was rogue anyway :)
			 */
			if ( $noqueries ) {
				//* Sanitize the whole thing.				
				$pattern 	= 	'/'
							.	'(\?|\&)' 	// 1: ? or &
							. 	'([^=]+)'	// 2: text until =
							.	'\='		// =
							.	'([^&]+)'	// 3: until & if found
							.	'/s'
							;
			
				$url = preg_replace( $pattern, '', $url );
				
				//* Add the missing slash if destroyed
				// @todo make this more/less specific?
				if ( preg_match( $pattern, $url ) )
					$url = trailingslashit($url);
				
				$url = esc_url( $url );
				
			} else {
				//* Allow query string parameters. Warning: don't trust anyone :)
				//* XSS safe.
				
				//* @todo check if esc_url in the html doesn't affect this outcome (on 2nd save?)
				$url = esc_url_raw( $url );	
			}
			
			//* Save url
			$data[ $key ] = $url;
			
		}
	}

	hmpl_ad_save_custom_fields( $data, 'hmpl_ad_inpost_seo_save', 'hmpl_ad_inpost_seo_nonce', $post );
}
add_action( 'save_post', 'hmpl_ad_inpost_seo_save', 1, 2 );

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

/* Start Sitemaps
----------------------------------------------------------------------------------------------------*/

//* @todo: everything

/**
 * Edits the robots.txt output
 *
 * Requires not to have a robots.txt file in the root directory
 *
 * @uses robots_txt filter located at WP core
 *
 * @since 2.1.0
 *
 * @todo create options
 * @todo maybe combine with noindex/noarchive/(nofollow) -> only when object caching?
 */
function hmpl_ad_robots_txt($output = '', $public = '') {
	
	$blog_id = get_current_blog_id();
	
	$output = wp_cache_get('msrobots_' . $blog_id, 'msrobots' );
	if ( false === $output ) {
		$site_url = parse_url( site_url() );
		$path = ( !empty( $site_url['path'] ) ) ? $site_url['path'] : '';
		
		$output = "User-agent: *\n";
		
		//* If the blog isn't public, disallow root.
		$public = get_option('blog_public');	
		if ('0' == $public) {
			$output .= "Disallow: /\n";
		} else {
			//* Output defaults
			$output .= "Disallow: $path/wp-admin/\r\n";
			$output .= "Disallow: $path/wp-includes/\r\n";
			
			//* Add our own
			$output .= "Disallow: $path/wp-login.php\r\n";
			$output .= "Disallow: $path/wp-activate.php\r\n";
			
			// Prevents query caching
			$output .= "Disallow: $path/*?*\r\n";
		}
		
		//* Add whitespace
		$output .= "\r\n";
		
		//* Add sitemap full url
		//* Becomes relative if host is empty.
		$host = ( !empty( $site_url['host'] ) ) ? $site_url['host'] : '';		
		$scheme = ( !empty( $site_url['scheme'] ) && !empty( $host ) ) ? $site_url['scheme'] . '://' : '';
		$output .= "Sitemap: $scheme$host/sitemap.xml\r\n";
		
		wp_cache_set('msrobots_' . $blog_id , $output, 'msrobots', 86400 ); // 24 hours
	}
	
	return $output;	
}
//add_filter( 'robots_txt', 'hmpl_ad_robots_txt' );