<?php
/**
 * The template for displaying all single posts (native 'post' type)
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Print Schema.org JSON-LD
if ( function_exists( 'spicecraft_output_article_schema' ) ) {
	spicecraft_output_article_schema( get_the_ID() );
}

$post_id          = get_the_ID();
$categories       = get_the_category( $post_id );
$primary_cat      = ! empty( $categories ) ? $categories[0] : null;
$reading_time     = function_exists( 'spicecraft_get_reading_time' ) ? spicecraft_get_reading_time( $post_id ) : 3;
$subtitle         = get_post_meta( $post_id, '_sc_post_subtitle', true );
$show_author      = function_exists( 'spicecraft_get_blog_option' ) ? (bool) spicecraft_get_blog_option( 'show_author', true ) : true;
$show_reading     = function_exists( 'spicecraft_get_blog_option' ) ? (bool) spicecraft_get_blog_option( 'show_reading_time', true ) : true;
$show_date        = function_exists( 'spicecraft_get_blog_option' ) ? (bool) spicecraft_get_blog_option( 'show_date', true ) : true;
$show_related     = function_exists( 'spicecraft_get_blog_option' ) ? (bool) spicecraft_get_blog_option( 'show_related_posts', true ) : true;
$social_share     = function_exists( 'spicecraft_get_blog_option' ) ? (bool) spicecraft_get_blog_option( 'enable_social_share', true ) : true;
$blog_url         = function_exists( 'spicecraft_get_blog_url' ) ? spicecraft_get_blog_url() : home_url( '/blog/' );

$permalink        = get_permalink( $post_id );
$encoded_url      = rawurlencode( $permalink );
$encoded_title    = rawurlencode( get_the_title( $post_id ) );

// Author info
$author_id        = get_the_author_meta( 'ID' );
$author_name      = get_the_author();
$author_bio       = get_the_author_meta( 'description' );

// Related Posts
$related_posts    = ( $show_related && function_exists( 'spicecraft_get_related_posts' ) ) ? spicecraft_get_related_posts( $post_id, 3 ) : array();

// Prev & Next Posts
$prev_post        = get_previous_post();
$next_post        = get_next_post();
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'sc-article-single' ); ?>>

	<!-- Article Header Section -->
	<header class="sc-article-single__header">
		<div class="sc-container sc-container--narrow">
			<!-- Breadcrumbs -->
			<nav class="sc-breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'spicecraft' ); ?>">
				<ol class="sc-breadcrumbs__list">
					<li class="sc-breadcrumbs__item"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'spicecraft' ); ?></a></li>
					<li class="sc-breadcrumbs__item"><a href="<?php echo esc_url( $blog_url ); ?>"><?php esc_html_e( 'Blog & Insights', 'spicecraft' ); ?></a></li>
					<?php if ( $primary_cat ) : ?>
						<li class="sc-breadcrumbs__item">
							<a href="<?php echo esc_url( get_category_link( $primary_cat->term_id ) ); ?>">
								<?php echo esc_html( $primary_cat->name ); ?>
							</a>
						</li>
					<?php endif; ?>
					<li class="sc-breadcrumbs__item sc-breadcrumbs__item--active" aria-current="page"><?php the_title(); ?></li>
				</ol>
			</nav>

			<!-- Category Badge -->
			<?php if ( $primary_cat ) : ?>
				<div class="sc-article-single__badge-wrap">
					<a href="<?php echo esc_url( get_category_link( $primary_cat->term_id ) ); ?>" class="sc-badge sc-badge--category">
						<?php echo esc_html( $primary_cat->name ); ?>
					</a>
				</div>
			<?php endif; ?>

			<!-- Single H1 Title -->
			<h1 class="sc-article-single__title"><?php the_title(); ?></h1>

			<!-- Subtitle / Deck -->
			<?php if ( ! empty( $subtitle ) ) : ?>
				<p class="sc-article-single__deck"><?php echo esc_html( $subtitle ); ?></p>
			<?php endif; ?>

			<!-- Metadata Row -->
			<div class="sc-article-single__meta">
				<?php if ( $show_author ) : ?>
					<div class="sc-article-single__author">
						<span class="sc-article-single__author-avatar" aria-hidden="true">
							<?php echo get_avatar( $author_id, 40 ); ?>
						</span>
						<div class="sc-article-single__author-text">
							<span class="sc-article-single__author-label"><?php esc_html_e( 'Written by', 'spicecraft' ); ?></span>
							<span class="sc-article-single__author-name"><?php echo esc_html( $author_name ); ?></span>
						</div>
					</div>
				<?php endif; ?>

				<div class="sc-article-single__meta-details">
					<?php if ( $show_date ) : ?>
						<time class="sc-article-single__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
								<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
								<line x1="16" y1="2" x2="16" y2="6"/>
								<line x1="8" y1="2" x2="8" y2="6"/>
								<line x1="3" y1="10" x2="21" y2="10"/>
							</svg>
							<span><?php echo esc_html( get_the_date( 'F j, Y' ) ); ?></span>
						</time>
					<?php endif; ?>

					<?php if ( $show_reading && $reading_time > 0 ) : ?>
						<span class="sc-article-single__reading-time" title="<?php esc_attr_e( 'Estimated reading duration', 'spicecraft' ); ?>">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
								<circle cx="12" cy="12" r="10"/>
								<polyline points="12 6 12 12 16 14"/>
							</svg>
							<span><?php printf( esc_html__( '%d min read', 'spicecraft' ), absint( $reading_time ) ); ?></span>
						</span>
					<?php endif; ?>
				</div>

				<!-- Quick Share Action Bar -->
				<?php if ( $social_share ) : ?>
					<div class="sc-article-single__quick-share">
						<button type="button" class="sc-share-btn sc-share-btn--copy" data-url="<?php echo esc_url( $permalink ); ?>" aria-label="<?php esc_attr_e( 'Copy article link', 'spicecraft' ); ?>" title="<?php esc_attr_e( 'Copy article link', 'spicecraft' ); ?>">
							<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
								<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
								<path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
							</svg>
							<span><?php esc_html_e( 'Share', 'spicecraft' ); ?></span>
						</button>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</header>

	<!-- Featured Image Hero -->
	<?php if ( has_post_thumbnail() ) : ?>
		<div class="sc-article-single__media-wrap">
			<div class="sc-container">
				<figure class="sc-article-single__figure">
					<?php
					the_post_thumbnail(
						'full',
						array(
							'class'   => 'sc-article-single__featured-img',
							'alt'     => the_title_attribute( array( 'echo' => false ) ),
							'loading' => 'eager',
						)
					);
					?>
					<?php if ( get_the_post_thumbnail_caption() ) : ?>
						<figcaption class="sc-article-single__caption">
							<?php echo esc_html( get_the_post_thumbnail_caption() ); ?>
						</figcaption>
					<?php endif; ?>
				</figure>
			</div>
		</div>
	<?php endif; ?>

	<!-- Article Body Area -->
	<div class="sc-article-single__content-wrap">
		<div class="sc-container sc-container--narrow">

			<div class="sc-article-single__body sc-prose entry-content">
				<?php
				the_content();

				wp_link_pages(
					array(
						'before'      => '<nav class="page-links" aria-label="' . esc_attr__( 'Page', 'spicecraft' ) . '">' . esc_html__( 'Pages:', 'spicecraft' ),
						'after'       => '</nav>',
						'link_before' => '<span class="page-number">',
						'link_after'  => '</span>',
					)
				);
				?>
			</div>

			<!-- Tags -->
			<?php
			$post_tags = get_the_tags();
			if ( ! empty( $post_tags ) ) :
				?>
				<div class="sc-article-single__tags" aria-label="<?php esc_attr_e( 'Article Tags', 'spicecraft' ); ?>">
					<span class="sc-article-single__tags-label">
						<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
							<line x1="7" y1="7" x2="7.01" y2="7"/>
						</svg>
						<?php esc_html_e( 'Tags:', 'spicecraft' ); ?>
					</span>
					<div class="sc-article-single__tags-list">
						<?php foreach ( $post_tags as $tag ) : ?>
							<a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>" class="sc-tag-pill">
								#<?php echo esc_html( $tag->name ); ?>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<!-- Social Sharing Section -->
			<?php if ( $social_share ) : ?>
				<div class="sc-article-share-card">
					<h3 class="sc-article-share-card__title"><?php esc_html_e( 'Share this article', 'spicecraft' ); ?></h3>
					<p class="sc-article-share-card__desc"><?php esc_html_e( 'Found this market analysis valuable? Share it with your procurement and culinary network.', 'spicecraft' ); ?></p>
					
					<div class="sc-article-share-buttons">
						<!-- LinkedIn -->
						<a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo $encoded_url; ?>" 
							target="_blank" 
							rel="noopener noreferrer" 
							class="sc-share-btn sc-share-btn--linkedin"
							aria-label="<?php esc_attr_e( 'Share on LinkedIn', 'spicecraft' ); ?>">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>
							<span>LinkedIn</span>
						</a>

						<!-- WhatsApp -->
						<a href="https://api.whatsapp.com/send?text=<?php echo $encoded_title . '%20' . $encoded_url; ?>" 
							target="_blank" 
							rel="noopener noreferrer" 
							class="sc-share-btn sc-share-btn--whatsapp"
							aria-label="<?php esc_attr_e( 'Share on WhatsApp', 'spicecraft' ); ?>">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
							<span>WhatsApp</span>
						</a>

						<!-- X / Twitter -->
						<a href="https://twitter.com/intent/tweet?text=<?php echo $encoded_title; ?>&url=<?php echo $encoded_url; ?>" 
							target="_blank" 
							rel="noopener noreferrer" 
							class="sc-share-btn sc-share-btn--twitter"
							aria-label="<?php esc_attr_e( 'Share on X (Twitter)', 'spicecraft' ); ?>">
							<svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
							<span>X</span>
						</a>

						<!-- Facebook -->
						<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $encoded_url; ?>" 
							target="_blank" 
							rel="noopener noreferrer" 
							class="sc-share-btn sc-share-btn--facebook"
							aria-label="<?php esc_attr_e( 'Share on Facebook', 'spicecraft' ); ?>">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
							<span>Facebook</span>
						</a>

						<!-- Copy Link Button with Tooltip -->
						<button type="button" 
							class="sc-share-btn sc-share-btn--copy" 
							data-url="<?php echo esc_url( $permalink ); ?>" 
							aria-label="<?php esc_attr_e( 'Copy link to clipboard', 'spicecraft' ); ?>">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
							<span class="sc-share-btn__label"><?php esc_html_e( 'Copy Link', 'spicecraft' ); ?></span>
						</button>
					</div>
				</div>
			<?php endif; ?>

			<!-- Author Bio Box -->
			<?php if ( $show_author && ! empty( $author_bio ) ) : ?>
				<div class="sc-article-author-card">
					<div class="sc-article-author-card__avatar">
						<?php echo get_avatar( $author_id, 72 ); ?>
					</div>
					<div class="sc-article-author-card__content">
						<span class="sc-article-author-card__eyebrow"><?php esc_html_e( 'About the Author', 'spicecraft' ); ?></span>
						<h3 class="sc-article-author-card__name"><?php echo esc_html( $author_name ); ?></h3>
						<p class="sc-article-author-card__bio"><?php echo esc_html( $author_bio ); ?></p>
						<a href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?>" class="sc-link-arrow">
							<span><?php esc_html_e( 'More articles by this author', 'spicecraft' ); ?></span>
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
						</a>
					</div>
				</div>
			<?php endif; ?>

			<!-- Previous / Next Navigation -->
			<?php if ( $prev_post || $next_post ) : ?>
				<nav class="sc-article-nav" aria-label="<?php esc_attr_e( 'Article navigation', 'spicecraft' ); ?>">
					<div class="sc-article-nav__inner">
						<?php if ( $prev_post ) : ?>
							<a href="<?php echo esc_url( get_permalink( $prev_post->ID ) ); ?>" class="sc-article-nav__link sc-article-nav__link--prev">
								<span class="sc-article-nav__label">
									<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
									<?php esc_html_e( 'Previous Article', 'spicecraft' ); ?>
								</span>
								<span class="sc-article-nav__title"><?php echo esc_html( get_the_title( $prev_post->ID ) ); ?></span>
							</a>
						<?php else : ?>
							<div class="sc-article-nav__empty"></div>
						<?php endif; ?>

						<?php if ( $next_post ) : ?>
							<a href="<?php echo esc_url( get_permalink( $next_post->ID ) ); ?>" class="sc-article-nav__link sc-article-nav__link--next">
								<span class="sc-article-nav__label">
									<?php esc_html_e( 'Next Article', 'spicecraft' ); ?>
									<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
								</span>
								<span class="sc-article-nav__title"><?php echo esc_html( get_the_title( $next_post->ID ) ); ?></span>
							</a>
						<?php endif; ?>
					</div>
				</nav>
			<?php endif; ?>

			<!-- Comments Template -->
			<?php
			if ( comments_open() || get_comments_number() ) :
				comments_template();
			endif;
			?>

		</div>
	</div>

	<!-- Related Articles Section -->
	<?php if ( ! empty( $related_posts ) ) : ?>
		<section class="sc-article-related-section" aria-labelledby="related-insights-title">
			<div class="sc-container">
				<div class="sc-article-related-section__header">
					<div class="sc-article-related-section__header-content">
						<span class="sc-eyebrow"><?php esc_html_e( 'Further Reading', 'spicecraft' ); ?></span>
						<h2 id="related-insights-title" class="sc-section-title"><?php esc_html_e( 'Related Industry Insights', 'spicecraft' ); ?></h2>
					</div>
					<div class="sc-article-related-section__header-action">
						<a href="<?php echo esc_url( $blog_url ); ?>" class="sc-link-arrow">
							<span><?php esc_html_e( 'View All Articles', 'spicecraft' ); ?></span>
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
						</a>
					</div>
				</div>

				<div class="sc-grid sc-grid--3 sc-blog-grid">
					<?php
					global $post;
					foreach ( $related_posts as $related_item ) :
						$post = $related_item; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
						setup_postdata( $post );
						get_template_part( 'template-parts/content/article-card' );
					endforeach;
					wp_reset_postdata();
					?>
				</div>
			</div>
		</section>
	<?php endif; ?>

</article>

<?php
get_footer();
