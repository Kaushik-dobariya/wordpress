<?php
/**
 * The template for displaying the blog / news & articles index page
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Blog Settings
$hero_title        = function_exists( 'spicecraft_get_blog_option' ) ? spicecraft_get_blog_option( 'hero_title', __( 'Spice Industry Insights & Articles', 'spicecraft' ) ) : __( 'Spice Industry Insights & Articles', 'spicecraft' );
$hero_subtitle     = function_exists( 'spicecraft_get_blog_option' ) ? spicecraft_get_blog_option( 'hero_subtitle', '' ) : '';
$show_featured     = function_exists( 'spicecraft_get_blog_option' ) ? (bool) spicecraft_get_blog_option( 'show_featured_banner', true ) : true;
$lead_cta_heading  = function_exists( 'spicecraft_get_blog_option' ) ? spicecraft_get_blog_option( 'lead_cta_heading', '' ) : '';
$lead_cta_text     = function_exists( 'spicecraft_get_blog_option' ) ? spicecraft_get_blog_option( 'lead_cta_text', '' ) : '';
$lead_cta_btn_text = function_exists( 'spicecraft_get_blog_option' ) ? spicecraft_get_blog_option( 'lead_cta_button_text', '' ) : '';
$lead_cta_btn_url  = function_exists( 'spicecraft_get_blog_option' ) ? spicecraft_get_blog_option( 'lead_cta_button_url', '' ) : '';

$paged             = max( 1, get_query_var( 'paged' ), get_query_var( 'page' ) );
$current_search    = get_search_query();
$blog_url          = function_exists( 'spicecraft_get_blog_url' ) ? spicecraft_get_blog_url() : home_url( '/blog/' );

// Featured article banner (Shown only on page 1 when not searching)
$featured_id  = 0;
$featured_post = null;
if ( $show_featured && 1 === $paged && empty( $current_search ) && ! is_category() && ! is_tag() ) {
	$featured_posts = function_exists( 'spicecraft_get_featured_posts' ) ? spicecraft_get_featured_posts( 1 ) : array();
	if ( ! empty( $featured_posts ) ) {
		$featured_post = $featured_posts[0];
		$featured_id   = $featured_post->ID;
	}
}

// All Categories for filter pills
$categories = get_categories(
	array(
		'taxonomy'   => 'category',
		'hide_empty' => true,
		'orderby'    => 'count',
		'order'      => 'DESC',
	)
);
$current_cat_id = is_category() ? get_queried_object_id() : 0;
?>

<!-- Blog Hero Header -->
<header class="sc-blog-hero">
	<div class="sc-container">
		<!-- Breadcrumbs -->
		<nav class="sc-breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'spicecraft' ); ?>">
			<ol class="sc-breadcrumbs__list">
				<li class="sc-breadcrumbs__item"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'spicecraft' ); ?></a></li>
				<li class="sc-breadcrumbs__item sc-breadcrumbs__item--active" aria-current="page"><?php esc_html_e( 'Blog & Insights', 'spicecraft' ); ?></li>
			</ol>
		</nav>

		<div class="sc-blog-hero__content">
			<span class="sc-eyebrow"><?php esc_html_e( 'Knowledge & Market Intelligence', 'spicecraft' ); ?></span>
			<h1 class="sc-blog-hero__title"><?php echo esc_html( $hero_title ); ?></h1>
			<?php if ( ! empty( $hero_subtitle ) ) : ?>
				<p class="sc-blog-hero__subtitle"><?php echo esc_html( $hero_subtitle ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</header>

<div class="sc-blog-main-area">
	<div class="sc-container">

		<!-- Featured Showcase Banner -->
		<?php if ( $featured_post ) : ?>
			<?php
			$feat_id      = $featured_post->ID;
			$feat_cats    = get_the_category( $feat_id );
			$feat_cat     = ! empty( $feat_cats ) ? $feat_cats[0] : null;
			$feat_time    = function_exists( 'spicecraft_get_reading_time' ) ? spicecraft_get_reading_time( $feat_id ) : 4;
			$feat_sub     = get_post_meta( $feat_id, '_sc_post_subtitle', true );
			?>
			<section class="sc-blog-featured-banner" aria-label="<?php esc_attr_e( 'Featured Article', 'spicecraft' ); ?>">
				<div class="sc-blog-featured-banner__inner">
					<div class="sc-blog-featured-banner__media">
						<a href="<?php echo esc_url( get_permalink( $feat_id ) ); ?>" tabindex="-1" aria-hidden="true">
							<?php if ( has_post_thumbnail( $feat_id ) ) : ?>
								<?php echo get_the_post_thumbnail( $feat_id, 'large', array( 'class' => 'sc-blog-featured-banner__img', 'alt' => get_the_title( $feat_id ) ) ); ?>
							<?php else : ?>
								<div class="sc-blog-featured-banner__placeholder">
									<svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
										<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
										<path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
									</svg>
								</div>
							<?php endif; ?>
						</a>
						<span class="sc-badge sc-badge--featured-banner">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" stroke="none" aria-hidden="true">
								<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
							</svg>
							<?php esc_html_e( 'Featured Spotlight', 'spicecraft' ); ?>
						</span>
					</div>

					<div class="sc-blog-featured-banner__content">
						<div class="sc-blog-featured-banner__meta">
							<?php if ( $feat_cat ) : ?>
								<a href="<?php echo esc_url( get_category_link( $feat_cat->term_id ) ); ?>" class="sc-badge sc-badge--category">
									<?php echo esc_html( $feat_cat->name ); ?>
								</a>
							<?php endif; ?>
							<time datetime="<?php echo esc_attr( get_the_date( 'c', $feat_id ) ); ?>">
								<?php echo esc_html( get_the_date( 'F j, Y', $feat_id ) ); ?>
							</time>
							<?php if ( $feat_time > 0 ) : ?>
								<span class="sc-blog-featured-banner__readtime">
									&bull; <?php printf( esc_html__( '%d min read', 'spicecraft' ), absint( $feat_time ) ); ?>
								</span>
							<?php endif; ?>
						</div>

						<h2 class="sc-blog-featured-banner__title">
							<a href="<?php echo esc_url( get_permalink( $feat_id ) ); ?>">
								<?php echo esc_html( get_the_title( $feat_id ) ); ?>
							</a>
						</h2>

						<?php if ( ! empty( $feat_sub ) ) : ?>
							<p class="sc-blog-featured-banner__subtitle"><?php echo esc_html( $feat_sub ); ?></p>
						<?php endif; ?>

						<p class="sc-blog-featured-banner__excerpt">
							<?php echo esc_html( wp_trim_words( get_the_excerpt( $feat_id ), 26, '...' ) ); ?>
						</p>

						<div class="sc-blog-featured-banner__footer">
							<div class="sc-blog-featured-banner__author">
								<span class="sc-blog-featured-banner__author-avatar" aria-hidden="true">
									<?php echo get_avatar( get_post_field( 'post_author', $feat_id ), 32 ); ?>
								</span>
								<span class="sc-blog-featured-banner__author-name">
									<?php echo esc_html( get_the_author_meta( 'display_name', get_post_field( 'post_author', $feat_id ) ) ); ?>
								</span>
							</div>

							<a href="<?php echo esc_url( get_permalink( $feat_id ) ); ?>" class="sc-btn sc-btn--primary">
								<span><?php esc_html_e( 'Read Full Article', 'spicecraft' ); ?></span>
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
									<line x1="5" y1="12" x2="19" y2="12"></line>
									<polyline points="12 5 19 12 12 19"></polyline>
								</svg>
							</a>
						</div>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<!-- Search & Category Filter Bar -->
		<div class="sc-blog-toolbar">
			<!-- Category Filter Pills -->
			<nav class="sc-blog-filters" aria-label="<?php esc_attr_e( 'Filter articles by category', 'spicecraft' ); ?>">
				<ul class="sc-blog-filter-list">
					<li>
						<a href="<?php echo esc_url( $blog_url ); ?>" class="sc-filter-pill <?php echo ( 0 === $current_cat_id && empty( $current_search ) ) ? 'is-active' : ''; ?>">
							<?php esc_html_e( 'All Articles', 'spicecraft' ); ?>
							<span class="sc-filter-pill__count"><?php echo esc_html( wp_count_posts( 'post' )->publish ); ?></span>
						</a>
					</li>
					<?php foreach ( $categories as $cat ) : ?>
						<li>
							<a href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>" class="sc-filter-pill <?php echo ( (int) $current_cat_id === (int) $cat->term_id ) ? 'is-active' : ''; ?>">
								<?php echo esc_html( $cat->name ); ?>
								<span class="sc-filter-pill__count"><?php echo esc_html( $cat->count ); ?></span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</nav>

			<!-- Article Search Form -->
			<div class="sc-blog-search">
				<form role="search" method="get" class="sc-blog-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
					<label for="sc-blog-search-input" class="screen-reader-text"><?php esc_html_e( 'Search articles', 'spicecraft' ); ?></label>
					<div class="sc-blog-search-input-wrap">
						<svg class="sc-blog-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<circle cx="11" cy="11" r="8"/>
							<line x1="21" y1="21" x2="16.65" y2="16.65"/>
						</svg>
						<input 
							type="search" 
							id="sc-blog-search-input" 
							name="s" 
							value="<?php echo esc_attr( $current_search ); ?>" 
							placeholder="<?php esc_attr_e( 'Search articles, topics...', 'spicecraft' ); ?>"
							class="sc-blog-search-field"
						/>
						<input type="hidden" name="post_type" value="post" />
						<?php if ( ! empty( $current_search ) ) : ?>
							<a href="<?php echo esc_url( $blog_url ); ?>" class="sc-blog-search-clear" aria-label="<?php esc_attr_e( 'Clear search', 'spicecraft' ); ?>" title="<?php esc_attr_e( 'Clear search', 'spicecraft' ); ?>">
								&times;
							</a>
						<?php endif; ?>
						<button type="submit" class="sc-btn sc-btn--secondary sc-blog-search-submit">
							<?php esc_html_e( 'Search', 'spicecraft' ); ?>
						</button>
					</div>
				</form>
			</div>
		</div>

		<!-- Active Filters / Search Summary Notice -->
		<?php if ( ! empty( $current_search ) ) : ?>
			<div class="sc-blog-active-filter-bar">
				<p>
					<?php
					/* translators: %s: search query string */
					printf( esc_html__( 'Showing search results for: "%s"', 'spicecraft' ), esc_html( $current_search ) );
					?>
				</p>
				<a href="<?php echo esc_url( $blog_url ); ?>" class="sc-link-reset">
					<?php esc_html_e( 'Reset to All Articles', 'spicecraft' ); ?> &times;
				</a>
			</div>
		<?php endif; ?>

		<!-- Main Article Grid Query -->
		<?php
		// Exclude the featured hero post from the main grid on page 1 so it doesn't appear twice
		$posts_per_page = get_option( 'posts_per_page', 9 );
		$grid_args = array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'paged'          => $paged,
			'posts_per_page' => $posts_per_page,
		);

		if ( $featured_id > 0 && 1 === $paged && empty( $current_search ) ) {
			$grid_args['post__not_in'] = array( $featured_id );
		}

		if ( ! empty( $current_search ) ) {
			$grid_args['s'] = $current_search;
		}

		if ( $current_cat_id > 0 ) {
			$grid_args['cat'] = $current_cat_id;
		}

		$grid_query = new WP_Query( $grid_args );
		?>

		<?php if ( $grid_query->have_posts() ) : ?>
			<div class="sc-blog-grid-header">
				<h2 class="sc-blog-grid-title">
					<?php
					if ( ! empty( $current_search ) ) {
						esc_html_e( 'Search Results', 'spicecraft' );
					} elseif ( $current_cat_id > 0 ) {
						echo esc_html( get_cat_name( $current_cat_id ) );
					} elseif ( $featured_id > 0 ) {
						esc_html_e( 'Recent Industry Articles', 'spicecraft' );
					} else {
						esc_html_e( 'All Articles', 'spicecraft' );
					}
					?>
				</h2>
				<span class="sc-blog-grid-count">
					<?php
					/* translators: %d: number of posts */
					printf( esc_html( _n( '%d article', '%d articles', $grid_query->found_posts, 'spicecraft' ) ), absint( $grid_query->found_posts ) );
					?>
				</span>
			</div>

			<div class="sc-grid sc-grid--3 sc-blog-grid">
				<?php
				while ( $grid_query->have_posts() ) :
					$grid_query->the_post();
					get_template_part( 'template-parts/content/article-card' );
				endwhile;
				?>
			</div>

			<!-- Pagination -->
			<?php if ( $grid_query->max_num_pages > 1 ) : ?>
				<nav class="sc-pagination sc-blog-pagination" aria-label="<?php esc_attr_e( 'Articles navigation', 'spicecraft' ); ?>">
					<?php
					echo paginate_links(
						array(
							'base'      => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
							'format'    => '?paged=%#%',
							'current'   => $paged,
							'total'     => $grid_query->max_num_pages,
							'prev_text' => '&larr; ' . esc_html__( 'Previous', 'spicecraft' ),
							'next_text' => esc_html__( 'Next', 'spicecraft' ) . ' &rarr;',
							'type'      => 'list',
						)
					);
					?>
				</nav>
			<?php endif; ?>

			<?php wp_reset_postdata(); ?>

		<?php else : ?>
			<!-- Professional Empty State -->
			<div class="sc-empty-state sc-blog-empty">
				<div class="sc-empty-state__icon" aria-hidden="true">
					<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
						<circle cx="11" cy="11" r="8"/>
						<line x1="21" y1="21" x2="16.65" y2="16.65"/>
						<line x1="8" y1="11" x2="14" y2="11"/>
					</svg>
				</div>
				<h3 class="sc-empty-state__title"><?php esc_html_e( 'No Articles Found', 'spicecraft' ); ?></h3>
				<p class="sc-empty-state__text">
					<?php
					if ( ! empty( $current_search ) ) {
						esc_html_e( 'We couldn\'t find any articles matching your search terms. Try searching with different keywords or browse our categories.', 'spicecraft' );
					} else {
						esc_html_e( 'No articles are available in this category yet. Please check back soon for fresh industry insights.', 'spicecraft' );
					}
					?>
				</p>
				<div class="sc-empty-state__actions">
					<a href="<?php echo esc_url( $blog_url ); ?>" class="sc-btn sc-btn--primary">
						<?php esc_html_e( 'View All Articles', 'spicecraft' ); ?>
					</a>
				</div>
			</div>
		<?php endif; ?>

	</div>
</div>

<!-- Bottom Lead / Trade Consultation CTA -->
<?php if ( ! empty( $lead_cta_heading ) ) : ?>
	<section class="sc-blog-bottom-cta">
		<div class="sc-container">
			<div class="sc-blog-bottom-cta__inner">
				<div class="sc-blog-bottom-cta__content">
					<span class="sc-eyebrow"><?php esc_html_e( 'Global Spice Partner', 'spicecraft' ); ?></span>
					<h2 class="sc-blog-bottom-cta__title"><?php echo esc_html( $lead_cta_heading ); ?></h2>
					<?php if ( ! empty( $lead_cta_text ) ) : ?>
						<p class="sc-blog-bottom-cta__text"><?php echo esc_html( $lead_cta_text ); ?></p>
					<?php endif; ?>
				</div>
				<?php if ( ! empty( $lead_cta_btn_text ) ) : ?>
					<div class="sc-blog-bottom-cta__action">
						<a href="<?php echo esc_url( ! empty( $lead_cta_btn_url ) ? $lead_cta_btn_url : home_url( '/#contact' ) ); ?>" class="sc-btn sc-btn--primary">
							<span><?php echo esc_html( $lead_cta_btn_text ); ?></span>
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
						</a>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php
get_footer();
