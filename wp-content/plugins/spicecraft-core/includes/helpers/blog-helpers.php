<?php
/**
 * SpiceCraft Core - Blog & Articles Helper Functions
 *
 * Provides CMS settings accessors, reading-time calculators,
 * featured/related article queries, page auto-provisioning,
 * and SEO/Open Graph/Schema.org output helpers.
 *
 * @package SpiceCraft_Core
 * @since 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const SPICECRAFT_BLOG_OPTION = 'spicecraft_blog_settings';

/**
 * Retrieve Blog CMS default configuration settings.
 *
 * @return array
 */
function spicecraft_get_blog_default_settings() {
	return array(
		// Hero & Header
		'page_title'             => __( 'Industry Insights & Articles', 'spicecraft-core' ),
		'hero_badge'             => __( 'Knowledge & Insights', 'spicecraft-core' ),
		'hero_title'             => __( 'Artisanal Spice Craft, Market Trends & Culinary Science', 'spicecraft-core' ),
		'hero_subtitle'          => __( 'Explore in-depth technical analysis on cryogenic milling, farm origin traceability, sustainable agroforestry, and international export safety standards.', 'spicecraft-core' ),
		'hero_image_id'          => 0,

		// Layout & Listing Controls
		'posts_per_page'         => 9,
		'show_featured_banner'   => 1,
		'featured_post_id'       => 0, // 0 = automatic latest featured post
		'show_category_filter'   => 1,
		'show_search_bar'        => 1,
		'show_author'            => 1,
		'show_published_date'    => 1,
		'show_reading_time'      => 1,
		'show_tags_on_card'      => 0,

		// Related Content Controls
		'related_count'          => 3,
		'related_strategy'       => 'category', // category, tags, latest

		// Social Sharing
		'enable_social_share'    => 1,
		'share_linkedin'         => 1,
		'share_whatsapp'         => 1,
		'share_facebook'         => 1,
		'share_copy_link'        => 1,

		// Article Detail Options
		'show_author_box'        => 1,
		'show_prev_next'         => 1,
		'enable_schema'          => 1,
		'enable_open_graph'      => 1,

		// Bottom Lead / Newsletter CTA
		'show_newsletter_cta'    => 1,
		'newsletter_badge'       => __( 'Industry Intelligence', 'spicecraft-core' ),
		'newsletter_heading'     => __( 'Stay Ahead of Global Spice Trends', 'spicecraft-core' ),
		'newsletter_description' => __( 'Subscribe to our quarterly B2B procurement reports, harvest forecasts, and culinary spice monographs directly from our agronomy team.', 'spicecraft-core' ),
		'newsletter_btn_label'   => __( 'Contact Trade Desk', 'spicecraft-core' ),
		'newsletter_btn_url'     => home_url( '/#contact' ),
	);
}

/**
 * Retrieve all Blog CMS settings with defaults merged.
 *
 * @return array
 */
function spicecraft_get_blog_settings() {
	$defaults = spicecraft_get_blog_default_settings();
	$saved    = get_option( SPICECRAFT_BLOG_OPTION, array() );

	if ( ! is_array( $saved ) ) {
		return $defaults;
	}

	return wp_parse_args( $saved, $defaults );
}

/**
 * Retrieve a specific Blog CMS setting.
 *
 * @param string $key     Setting key.
 * @param mixed  $default Fallback value.
 * @return mixed
 */
function spicecraft_get_blog_setting( $key, $default = '' ) {
	$settings = spicecraft_get_blog_settings();
	return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
}

/**
 * Alias for spicecraft_get_blog_setting.
 *
 * @param string $key     Setting key.
 * @param mixed  $default Fallback value.
 * @return mixed
 */
function spicecraft_get_blog_option( $key, $default = '' ) {
	return spicecraft_get_blog_setting( $key, $default );
}

/**
 * Alias for spicecraft_get_blog_settings.
 *
 * @return array
 */
function spicecraft_get_blog_options() {
	return spicecraft_get_blog_settings();
}

/**
 * Update Blog CMS settings atomically.
 *
 * @param array $settings New settings array.
 * @return bool
 */
function spicecraft_update_blog_settings( $settings ) {
	if ( ! is_array( $settings ) ) {
		return false;
	}
	return update_option( SPICECRAFT_BLOG_OPTION, $settings );
}

/**
 * Retrieve the canonical public URL for the Blog.
 *
 * @return string
 */
function spicecraft_get_blog_url() {
	// 1. Check if a dedicated 'blog' page exists
	$page = get_page_by_path( 'blog' );
	if ( $page && 'publish' === $page->post_status ) {
		return get_permalink( $page->ID );
	}

	// 2. Check WordPress page_for_posts setting
	$page_for_posts = get_option( 'page_for_posts' );
	if ( $page_for_posts ) {
		return get_permalink( $page_for_posts );
	}

	// 3. Fallback
	return home_url( '/blog/' );
}

/**
 * Ensure the public /blog/ page exists in WordPress and is properly configured.
 *
 * @return int Page ID.
 */
function spicecraft_ensure_blog_page() {
	$page = get_page_by_path( 'blog' );

	if ( $page ) {
		// Ensure template is assigned
		$current_tpl = get_post_meta( $page->ID, '_wp_page_template', true );
		if ( 'page-blog.php' !== $current_tpl ) {
			update_post_meta( $page->ID, '_wp_page_template', 'page-blog.php' );
		}
		return $page->ID;
	}

	// Create page
	$page_id = wp_insert_post( array(
		'post_title'     => __( 'Blog & Articles', 'spicecraft-core' ),
		'post_name'      => 'blog',
		'post_status'    => 'publish',
		'post_type'      => 'page',
		'post_content'   => '<!-- wp:paragraph --><p>Latest spice industry updates, culinary science, and market insights.</p><!-- /wp:paragraph -->',
		'comment_status' => 'closed',
		'ping_status'    => 'closed',
	) );

	if ( $page_id && ! is_wp_error( $page_id ) ) {
		update_post_meta( $page_id, '_wp_page_template', 'page-blog.php' );
	}

	return $page_id;
}

/**
 * Calculate estimated reading time in minutes based on content word count.
 * Uses 200 words per minute average reading speed.
 *
 * @param string $content Post content or string.
 * @return int Reading time in minutes (minimum 1).
 */
function spicecraft_calculate_reading_time( $content ) {
	$clean_content = wp_strip_all_tags( strip_shortcodes( (string) $content ) );
	$word_count    = str_word_count( $clean_content );
	$minutes       = ceil( $word_count / 200 );
	return (int) max( 1, $minutes );
}

/**
 * Retrieve estimated reading time for a specific post.
 * Checks for manual override in post meta; falls back to calculated content time.
 *
 * @param int $post_id Post ID (defaults to current post).
 * @return int Reading time in minutes.
 */
function spicecraft_get_post_reading_time( $post_id = 0 ) {
	$post = get_post( $post_id );
	if ( ! $post ) {
		return 1;
	}

	$manual = get_post_meta( $post->ID, '_sc_post_reading_time', true );
	if ( ! empty( $manual ) && is_numeric( $manual ) && $manual > 0 ) {
		return (int) $manual;
	}

	return spicecraft_calculate_reading_time( $post->post_content );
}

/**
 * Check if a post is marked as a Featured Article.
 *
 * @param int $post_id Post ID.
 * @return bool
 */
function spicecraft_is_post_featured( $post_id = 0 ) {
	$post_id = $post_id ?: get_the_ID();
	return '1' === (string) get_post_meta( $post_id, '_sc_post_is_featured', true );
}

/**
 * Retrieve the optional editorial subtitle / deck for a post.
 *
 * @param int $post_id Post ID.
 * @return string Subtitle.
 */
function spicecraft_get_post_subtitle( $post_id = 0 ) {
	$post_id = $post_id ?: get_the_ID();
	return (string) get_post_meta( $post_id, '_sc_post_subtitle', true );
}

/**
 * Retrieve Featured Article(s).
 * Reads manually selected featured post from settings, or queries posts with `_sc_post_is_featured == 1`.
 *
 * @param int $limit Number of featured posts to retrieve (default 1).
 * @return WP_Post[] Array of WP_Post objects.
 */
function spicecraft_get_featured_posts( $limit = 1 ) {
	$settings         = spicecraft_get_blog_settings();
	$configured_id    = ! empty( $settings['featured_post_id'] ) ? absint( $settings['featured_post_id'] ) : 0;

	// 1. If a specific valid post is selected in settings
	if ( $configured_id > 0 ) {
		$post_obj = get_post( $configured_id );
		if ( $post_obj && 'publish' === $post_obj->post_status && 'post' === $post_obj->post_type ) {
			return array( $post_obj );
		}
	}

	// 2. Query posts with _sc_post_is_featured = 1
	$featured_query = new WP_Query( array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'meta_query'     => array(
			array(
				'key'     => '_sc_post_is_featured',
				'value'   => '1',
				'compare' => '=',
			),
		),
		'no_found_rows'  => true,
	) );

	if ( $featured_query->have_posts() ) {
		return $featured_query->posts;
	}

	// 3. Fallback: Return latest published sticky post or latest published post
	$fallback_query = new WP_Query( array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'no_found_rows'  => true,
	) );

	return $fallback_query->posts;
}

/**
 * Retrieve related articles for a given post.
 * Uses a 4-tier waterfall strategy:
 * 1. Manual related posts specified in post metadata.
 * 2. Shared categories (excluding current post).
 * 3. Shared tags (excluding current post).
 * 4. Recent published posts (fallback).
 *
 * @param int $post_id Post ID.
 * @param int $limit   Maximum number of related posts.
 * @return WP_Post[] Array of related WP_Post objects.
 */
function spicecraft_get_related_posts( $post_id = 0, $limit = 3 ) {
	$post = get_post( $post_id );
	if ( ! $post ) {
		return array();
	}

	$target_id = $post->ID;
	$related   = array();
	$seen_ids  = array( $target_id );

	// 1. Check manual post meta overrides
	$manual_ids = get_post_meta( $target_id, '_sc_post_related_ids', true );
	if ( ! empty( $manual_ids ) && is_array( $manual_ids ) ) {
		$manual_ids = array_map( 'absint', array_filter( $manual_ids ) );
		foreach ( $manual_ids as $m_id ) {
			if ( $m_id !== $target_id && ! in_array( $m_id, $seen_ids, true ) ) {
				$m_post = get_post( $m_id );
				if ( $m_post && 'publish' === $m_post->post_status && 'post' === $m_post->post_type ) {
					$related[]  = $m_post;
					$seen_ids[] = $m_id;
					if ( count( $related ) >= $limit ) {
						return $related;
					}
				}
			}
		}
	}

	// 2. Query shared categories
	$categories = wp_get_post_categories( $target_id );
	if ( ! empty( $categories ) ) {
		$cat_query = new WP_Query( array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => $limit - count( $related ),
			'post__not_in'   => $seen_ids,
			'category__in'   => $categories,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => true,
		) );

		foreach ( $cat_query->posts as $p ) {
			$related[]  = $p;
			$seen_ids[] = $p->ID;
			if ( count( $related ) >= $limit ) {
				return $related;
			}
		}
	}

	// 3. Query shared tags
	$tags = wp_get_post_tags( $target_id, array( 'fields' => 'ids' ) );
	if ( ! empty( $tags ) && count( $related ) < $limit ) {
		$tag_query = new WP_Query( array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => $limit - count( $related ),
			'post__not_in'   => $seen_ids,
			'tag__in'        => $tags,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => true,
		) );

		foreach ( $tag_query->posts as $p ) {
			$related[]  = $p;
			$seen_ids[] = $p->ID;
			if ( count( $related ) >= $limit ) {
				return $related;
			}
		}
	}

	// 4. Fallback: Recent published posts
	if ( count( $related ) < $limit ) {
		$fallback_query = new WP_Query( array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => $limit - count( $related ),
			'post__not_in'   => $seen_ids,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => true,
		) );

		foreach ( $fallback_query->posts as $p ) {
			$related[]  = $p;
			$seen_ids[] = $p->ID;
			if ( count( $related ) >= $limit ) {
				break;
			}
		}
	}

	return $related;
}

/**
 * Output Schema.org Article / BlogPosting JSON-LD.
 * Compatible with existing SEO setup; outputs clean structured data.
 *
 * @param int $post_id Post ID.
 */
function spicecraft_output_article_schema( $post_id = 0 ) {
	$post = get_post( $post_id );
	if ( ! $post || 'post' !== $post->post_type ) {
		return;
	}

	$headline    = wp_strip_all_tags( get_the_title( $post ) );
	$description = wp_strip_all_tags( get_the_excerpt( $post ) );
	$permalink   = get_permalink( $post );
	$date_pub    = get_the_date( 'c', $post );
	$date_mod    = get_the_modified_date( 'c', $post );
	$author_name = get_the_author_meta( 'display_name', $post->post_author );

	$image_url = '';
	if ( has_post_thumbnail( $post ) ) {
		$image_url = get_the_post_thumbnail_url( $post, 'full' );
	}

	$schema = array(
		'@context'         => 'https://schema.org',
		'@type'            => 'BlogPosting',
		'mainEntityOfPage' => array(
			'@type' => 'WebPage',
			'@id'   => $permalink,
		),
		'headline'         => $headline,
		'description'      => $description,
		'datePublished'    => $date_pub,
		'dateModified'     => $date_mod,
		'author'           => array(
			'@type' => 'Person',
			'name'  => $author_name,
		),
		'publisher'        => array(
			'@type' => 'Organization',
			'name'  => get_bloginfo( 'name' ),
			'url'   => home_url( '/' ),
		),
	);

	if ( ! empty( $image_url ) ) {
		$schema['image'] = array(
			'@type' => 'ImageObject',
			'url'   => $image_url,
		);
	}

	echo "\n<!-- SpiceCraft Article Schema -->\n";
	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) . "</script>\n";
}

/**
 * Output Open Graph and Twitter Card tags for articles and blog index.
 */
function spicecraft_output_blog_open_graph() {
	if ( ! is_singular( 'post' ) && ! is_home() && ! is_page( 'blog' ) && ! is_category() && ! is_tag() ) {
		return;
	}

	$site_name = get_bloginfo( 'name' );

	if ( is_singular( 'post' ) ) {
		$post        = get_queried_object();
		$title       = wp_strip_all_tags( get_the_title( $post ) );
		$description = wp_strip_all_tags( get_the_excerpt( $post ) );
		$url         = get_permalink( $post );
		$image       = has_post_thumbnail( $post ) ? get_the_post_thumbnail_url( $post, 'large' ) : '';
		$type        = 'article';
		$published   = get_the_date( 'c', $post );
		$modified    = get_the_modified_date( 'c', $post );
		$categories  = get_the_category( $post->ID );
		$section     = ! empty( $categories ) ? $categories[0]->name : '';

		echo "\n<!-- SpiceCraft Open Graph & Social Cards -->\n";
		echo '<meta property="og:site_name" content="' . esc_attr( $site_name ) . '" />' . "\n";
		echo '<meta property="og:type" content="' . esc_attr( $type ) . '" />' . "\n";
		echo '<meta property="og:title" content="' . esc_attr( $title . ' — ' . $site_name ) . '" />' . "\n";
		echo '<meta property="og:description" content="' . esc_attr( $description ) . '" />' . "\n";
		echo '<meta property="og:url" content="' . esc_url( $url ) . '" />' . "\n";
		if ( $image ) {
			echo '<meta property="og:image" content="' . esc_url( $image ) . '" />' . "\n";
		}
		echo '<meta property="article:published_time" content="' . esc_attr( $published ) . '" />' . "\n";
		echo '<meta property="article:modified_time" content="' . esc_attr( $modified ) . '" />' . "\n";
		if ( $section ) {
			echo '<meta property="article:section" content="' . esc_attr( $section ) . '" />' . "\n";
		}

		// Twitter Cards
		echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
		echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '" />' . "\n";
		echo '<meta name="twitter:description" content="' . esc_attr( $description ) . '" />' . "\n";
		if ( $image ) {
			echo '<meta name="twitter:image" content="' . esc_url( $image ) . '" />' . "\n";
		}
	} elseif ( is_home() || is_page( 'blog' ) || is_page_template( 'page-blog.php' ) ) {
		$settings    = spicecraft_get_blog_settings();
		$title       = ! empty( $settings['page_title'] ) ? $settings['page_title'] : __( 'Blog & Industry Insights', 'spicecraft-core' );
		$description = ! empty( $settings['hero_subtitle'] ) ? $settings['hero_subtitle'] : __( 'Articles, culinary science, and market analysis from SpiceCraft.', 'spicecraft-core' );
		$url         = spicecraft_get_blog_url();

		echo "\n<!-- SpiceCraft Open Graph & Social Cards -->\n";
		echo '<meta property="og:site_name" content="' . esc_attr( $site_name ) . '" />' . "\n";
		echo '<meta property="og:type" content="website" />' . "\n";
		echo '<meta property="og:title" content="' . esc_attr( $title . ' — ' . $site_name ) . '" />' . "\n";
		echo '<meta property="og:description" content="' . esc_attr( $description ) . '" />' . "\n";
		echo '<meta property="og:url" content="' . esc_url( $url ) . '" />' . "\n";
		echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
		echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '" />' . "\n";
		echo '<meta name="twitter:description" content="' . esc_attr( $description ) . '" />' . "\n";
	}
}
add_action( 'wp_head', 'spicecraft_output_blog_open_graph', 5 );

/**
 * Handle blog search requests on /blog/?s= and route cleanly to global search with post_type=post.
 */
function spicecraft_handle_blog_search_redirect() {
	if ( ! is_admin() && is_page( 'blog' ) && ! empty( $_GET['s'] ) ) {
		$search_term = sanitize_text_field( wp_unslash( $_GET['s'] ) );
		$target_url  = add_query_arg(
			array(
				's'         => $search_term,
				'post_type' => 'post',
			),
			home_url( '/' )
		);
		wp_safe_redirect( $target_url, 301 );
		exit;
	}
}
add_action( 'template_redirect', 'spicecraft_handle_blog_search_redirect', 5 );
