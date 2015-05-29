<?php 
/**
 * Plugin Name: AutoDescription
 * Plugin URI: https://wordpress.org/plugins/autodescription/
 * Description: Automatically adds a description if previously empty based upon content and adds Open Graph tags.
 * Version: 1.2.0
 * Author: Sybre Waaijer
 * Author URI: https://cyberwire.nl/
 * Text Domain: AutoDescription
 */

/** 
 * Fully supports Genesis themes, this plugin is build upon it.
 * Doesn't have full support for other SEO plugins/themes but should work.
 * Please notify me if you notice an issue with a specific theme or plugin.
 */
 
/**
 * Changelog
 * 1.0.0	: Initial release
 * 1.0.1	: Added filter
 * 1.1.0	: Added Dynamic og:type, cleaned PHP notices, plus more bugfixes
 *			: Misses language file
 * 1.2.0	: Added language file again
 *			: Now uses object caching
 *			: Now displays on even if user's logged in
 *			: Added filter
 */

function ad_locale_init() {
	$plugin_dir = basename(dirname(__FILE__));
	load_plugin_textdomain( 'AutoDescription', false, $plugin_dir . '/language/');
}
add_action('plugins_loaded', 'ad_locale_init');
 
//* Get the excerpt
function hmpl_get_excerpt_by_id() {	
    $get_hmpl_excerpt = '';
	
	if (!is_404() && !is_search()) {
		global $post_id;
		$the_post = get_post($post_id);
		$get_hmpl_excerpt = $the_post->post_content;
	}
	
    $get_hmpl_excerpt = esc_attr(strip_tags(strip_shortcodes($get_hmpl_excerpt)));
	
	$get_hmpl_excerpt = str_replace(array("\r\n", "\r", "\n"), "\n", $get_hmpl_excerpt);
	$lines = explode("\n", $get_hmpl_excerpt);
	$new_lines = array();
	
	foreach ($lines as $i => $line) {
		if(!empty($line))
			$new_lines[] = trim($line) . ' ';
	}
	
	$get_hmpl_excerpt = implode($new_lines);
	
    return $get_hmpl_excerpt;
}
 
//* Create description
function hmpl_og_description() {
	global $wp_query;

	$hmpl_og_description = '';
	
	//* Genesis only, checks if description is present
	$theme_info = wp_get_theme()->get('Template');

	if( $theme_info == 'genesis' ) {
		if ( is_front_page() ) {
			$hmpl_og_description = genesis_get_seo_option( 'home_description' ) ? genesis_get_seo_option( 'home_description' ) : get_bloginfo( 'description' );
		}
		if ( is_singular() ) {
			if ( genesis_get_custom_field( '_genesis_description' ) )
				$hmpl_og_description = genesis_get_custom_field( '_genesis_description' );
		}
		if ( is_category() ) {
			$term = $wp_query->get_queried_object();
			$hmpl_og_description = ! empty( $term->meta['description'] ) ? $term->meta['description'] : '';
		}
		if ( is_tag() ) {
			$term = $wp_query->get_queried_object();
			$hmpl_og_description = ! empty( $term->meta['description'] ) ? $term->meta['description'] : '';
		}
		if ( is_tax() ) {
			$term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
			$hmpl_og_description = ! empty( $term->meta['description'] ) ? wp_kses_stripslashes( wp_kses_decode_entities( $term->meta['description'] ) ) : '';
		}
		if ( is_author() ) {
			$user_description = get_the_author_meta( 'meta_description', (int) get_query_var( 'author' ) );
			$hmpl_og_description = $user_description ? $user_description : '';
		}
		if ( is_post_type_archive() && genesis_has_post_type_archive_support() ) {
			$hmpl_og_description = genesis_get_cpt_option( 'description' ) ? genesis_get_cpt_option( 'description' ) : '';
		}
	}
	
	// Cache this? Cache everything? D:
	if ( empty ($hmpl_og_description) ) { //* HEAVY CODE (adds .5s processing time with 241k characters on 6-core 2.4GHz prefork)
		global $post,$get_hmpl_excerpt;
		$hmpl_title = get_the_title($post);
		$hmpl_blogname = get_bloginfo('name');
		$hmpl_excut = '';
		$hmpl_on = __('on', 'AutoDescription'); // Post Title "on" Blog Name
		
		$hmpl_excerpt = hmpl_get_excerpt_by_id($get_hmpl_excerpt);
		$hmpl_maxcharlength = 160 - mb_strlen($hmpl_title . $hmpl_blogname);

		if ( mb_strlen( $hmpl_excerpt ) > $hmpl_maxcharlength ) {
			$subex = mb_substr( $hmpl_excerpt, 0, $hmpl_maxcharlength - 3 );
			$exwords = explode( ' ', $subex );
			$excut = - ( mb_strlen( $exwords[ count( $exwords ) - 1 ] ) );
			if ( $excut < 0 ) {
				$hmpl_excerpt = mb_substr( $subex, 0, $excut );
			} else {
				$hmpl_excerpt = $subex;
			}
			$hmpl_excut = '...';
		}
		$hmpl_excerpt = str_replace(' ...', '...', $hmpl_excerpt . $hmpl_excut);
		
		$hmpl_og_description = sprintf( '%s %s %s - %s', $hmpl_title, $hmpl_on, $hmpl_blogname, $hmpl_excerpt);		
	}	
	
	//* Make sure no spaces are added at the end, adds " symbol
	$hmpl_og_description = $hmpl_og_description . '"';
	$hmpl_og_description = str_replace(' "', '"', $hmpl_og_description);
	
	return $hmpl_og_description;
}

function hmpl_og_locale() {
	$hmpl_og_locale = get_locale();
	return $hmpl_og_locale;
}

//* Get the title for og:title
function hmpl_og_title() {
	global $post;
	
	$hmpl_blogname = get_bloginfo('name');
	$hmpl_title = get_the_title($post);
	$hmpl_tagline = get_bloginfo( 'description', 'raw');
	
	$hmpl_og_title = $hmpl_title . ' - ' . $hmpl_blogname;
	
	if ( is_front_page() )
		$hmpl_og_title = sprintf( '%s - %s', $hmpl_blogname, $hmpl_tagline);	
		
	return $hmpl_og_title;
}

function hmpl_og_type() {	
	$hmpl_og_type = 'website';
		
	if ( is_single() ) {
		$hmpl_og_type = 'article';
	}
	if ( is_front_page() || is_page() ) {
		$hmpl_og_type = 'website';
	}
	if ( is_author() ) {
		$hmpl_og_type = 'profile';
	}
	
	return $hmpl_og_type;
}

//* Output custom header description and og meta 
// NOTE: removed the " in description and og:description deliberately.
function add_hmpl_meta_tags() {
	global $wp,$blog_id;
	
	$page_id = get_queried_object_id();
	
	$output = wp_cache_get( 'hmpl_autodescription_output_' . $blog_id . '_' . $page_id );
	if ( false === $output ) {
			
		$output	= '<meta name="description" content="' . hmpl_og_description() . ' />' . "\r\n" // Misses " deliberately
				. '<meta property="og:image" content="' . esc_url_raw(wp_make_link_relative(get_header_image())) . '" />' . "\r\n"
				. '<meta property="og:locale" content="' . hmpl_og_locale() . '" />' . "\r\n"
				. '<meta property="og:type" content="' . hmpl_og_type() . '" />' . "\r\n"
				. '<meta property="og:title" content="' . esc_attr(hmpl_og_title()) . '" />' . "\r\n"
				. '<meta property="og:description" content="' . hmpl_og_description() . ' />' . "\r\n" // Misses " deliberately 
				. '<meta property="og:url" content="' . esc_url_raw(home_url(add_query_arg(array(),$wp->request))) . '" />' . "\r\n"
				. '<meta property="og:site_name" content="' . get_bloginfo('name') . '" />' . "\r\n"
				;
				
		wp_cache_set( 'hmpl_autodescription_output_' . $blog_id . '_' . $page_id, $output );
	}
	
	echo $output;
	
}

//* LD+JSON search helper
function hmpl_filter_footer(){
	if ( !is_search() ) {
		add_action( 'wp_footer', 'hmpl_ld_json' );
	}
}

//* LD+JSON search helper output
function hmpl_ld_json($output) {
	global $blog_id;
	
	$output = wp_cache_get( 'hmpl_ad_ldjson_output_' . $blog_id );
	if ( false === $output ) {
		$context = json_encode( 'http://schema.org' );
		$webtype = json_encode( 'WebSite' );
		$url = json_encode( esc_url( home_url( '/' ) ) );
		$name = json_encode( get_bloginfo('name') );
		$actiontype = json_encode( 'SearchAction' );
		$target = json_encode( esc_url( home_url( '/' )) . '?s={search_term}' );
		$queryaction = json_encode( 'required name=search_term' );
		
		$pre_output = sprintf( '{"@context":%s,"@type":%s,"url":%s,"name":%s,"potentialAction":{"@type":%s,"target":%s,"query-input":%s}}', $context, $webtype, $url, $name, $actiontype, $target, $queryaction );
		
		$output = '<script type=\'application/ld+json\'>' . $pre_output . '</script>';
		
		wp_cache_set( 'hmpl_ad_ldjson_output_' . $blog_id, $output );
	}
	
	echo $output;
}

//* Run the plugin
add_action( 'init', 'hmpl_auto_description_output', 10 );

//* # Filter 'hmpl_ad_load'
//* To disable output on a specific site, add this in a plugin or your theme's functions.php: 

//* add_filter('hmpl_ad_load', '__return_false'); 						=> Disable this plugin's output

//* # Filter 'hmpl_ad_load_logged_in_only'
//* Enables or disables output for logged in users. Based upon object caching.

//* add_filter('hmpl_ad_load_logged_out_only', '__return_true'); 		=> Only show description and JSON script if user isn't logged in
//* add_filter('hmpl_ad_load_logged_out_only', '__return_false'); 		=> Always show description and JSON script

//* I should really convert this to the new comment scheme, who can even read this?
function hmpl_auto_description_output() {
	
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
				add_action( 'genesis_meta', 'add_hmpl_meta_tags', 12 );				
				add_action( 'get_footer', 'hmpl_filter_footer');
			} else {
				add_action( 'wp_head', 'add_hmpl_meta_tags', 12 );			
				add_action( 'wp_footer', 'hmpl_filter_footer');
			}
		}
	}
}