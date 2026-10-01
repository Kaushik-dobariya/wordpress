<?php
/**
 * The template for displaying search results pages
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$search_query   = get_search_query();
$post_type      = get_query_var( 'post_type' );
$is_blog_search = ( 'post' === $post_type );
$blog_url       = function_exists( 'spicecraft_get_blog_url' ) ? spicecraft_get_blog_url() : home_url( '/blog/' );

if ( $is_blog_search ) :
	// Categories for filter pills
	$categories = get_categories(
		array(
			'taxonomy'   => 'category',
			'hide_empty' => true,
			'orderby'    => 'count',
			'order'      => 'DESC',
		)
	);
	?>

	<!-- Blog Search Hero Header -->
	<header class="sc-blog-hero">
		<div class="sc-container">
			<nav class="sc-breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'spicecraft' ); ?>">
				<ol class="sc-breadcrumbs__list">
					<li class="sc-breadcrumbs__item"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'spicecraft' ); ?></a></li>
					<li class="sc-breadcrumbs__item"><a href="<?php echo esc_url( $blog_url ); ?>"><?php esc_html_e( 'Blog & Insights', 'spicecraft' ); ?></a></li>
					<li class="sc-breadcrumbs__item sc-breadcrumbs__item--active" aria-current="page"><?php esc_html_e( 'Search Results', 'spicecraft' ); ?></li>
				</ol>
			</nav>

			<div class="sc-blog-hero__content">
				<span class="sc-eyebrow"><?php esc_html_e( 'Articles & Knowledge Search', 'spicecraft' ); ?></span>
				<h1 class="sc-blog-hero__title">
					<?php
					/* translators: %s: search query */
					printf( esc_html__( 'Search: %s', 'spicecraft' ), esc_html( $search_query ) );
					?>
				</h1>
				<p class="sc-blog-hero__subtitle">
					<?php
					global $wp_query;
					/* translators: %d: number of articles found, %s: search query */
					printf( esc_html( _n( 'Found %1$d article matching "%2$s"', 'Found %1$d articles matching "%2$s"', $wp_query->found_posts, 'spicecraft' ) ), absint( $wp_query->found_posts ), esc_html( $search_query ) );
					?>
				</p>
			</div>
		</div>
	</header>

	<div class="sc-blog-main-area">
		<div class="sc-container">

			<!-- Search & Filter Toolbar -->
			<div class="sc-blog-toolbar">
				<nav class="sc-blog-filters" aria-label="<?php esc_attr_e( 'Filter articles by category', 'spicecraft' ); ?>">
					<ul class="sc-blog-filter-list">
						<li>
							<a href="<?php echo esc_url( $blog_url ); ?>" class="sc-filter-pill">
								<?php esc_html_e( 'All Articles', 'spicecraft' ); ?>
								<span class="sc-filter-pill__count"><?php echo esc_html( wp_count_posts( 'post' )->publish ); ?></span>
							</a>
						</li>
						<?php foreach ( $categories as $cat ) : ?>
							<li>
								<a href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>" class="sc-filter-pill">
									<?php echo esc_html( $cat->name ); ?>
									<span class="sc-filter-pill__count"><?php echo esc_html( $cat->count ); ?></span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</nav>

				<div class="sc-blog-search">
					<form role="search" method="get" class="sc-blog-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
						<label for="sc-search-results-input" class="screen-reader-text"><?php esc_html_e( 'Search articles', 'spicecraft' ); ?></label>
						<div class="sc-blog-search-input-wrap">
							<svg class="sc-blog-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
								<circle cx="11" cy="11" r="8"/>
								<line x1="21" y1="21" x2="16.65" y2="16.65"/>
							</svg>
							<input 
								type="search" 
								id="sc-search-results-input" 
								name="s" 
								value="<?php echo esc_attr( $search_query ); ?>" 
								placeholder="<?php esc_attr_e( 'Search articles, topics...', 'spicecraft' ); ?>"
								class="sc-blog-search-field"
							/>
							<input type="hidden" name="post_type" value="post" />
							<button type="submit" class="sc-btn sc-btn--secondary sc-blog-search-submit">
								<?php esc_html_e( 'Search', 'spicecraft' ); ?>
							</button>
						</div>
					</form>
				</div>
			</div>

			<!-- Active Filter Summary -->
			<div class="sc-blog-active-filter-bar">
				<p>
					<?php
					/* translators: %s: search query */
					printf( esc_html__( 'Showing search results for: "%s"', 'spicecraft' ), esc_html( $search_query ) );
					?>
				</p>
				<a href="<?php echo esc_url( $blog_url ); ?>" class="sc-link-reset">
					<?php esc_html_e( 'Clear Search', 'spicecraft' ); ?> &times;
				</a>
			</div>

			<!-- Results Grid -->
			<?php if ( have_posts() ) : ?>
				<div class="sc-grid sc-grid--3 sc-blog-grid">
					<?php
					while ( have_posts() ) :
						the_post();
						get_template_part( 'template-parts/content/article-card' );
					endwhile;
					?>
				</div>

				<!-- Pagination -->
				<nav class="sc-pagination sc-blog-pagination" aria-label="<?php esc_attr_e( 'Search pagination', 'spicecraft' ); ?>">
					<?php
					echo paginate_links(
						array(
							'prev_text' => '&larr; ' . esc_html__( 'Previous', 'spicecraft' ),
							'next_text' => esc_html__( 'Next', 'spicecraft' ) . ' &rarr;',
							'type'      => 'list',
						)
					);
					?>
				</nav>

			<?php else : ?>
				<div class="sc-empty-state sc-blog-empty">
					<div class="sc-empty-state__icon" aria-hidden="true">
						<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
							<circle cx="11" cy="11" r="8"/>
							<line x1="21" y1="21" x2="16.65" y2="16.65"/>
							<line x1="8" y1="11" x2="14" y2="11"/>
						</svg>
					</div>
					<h3 class="sc-empty-state__title"><?php esc_html_e( 'No Matching Articles', 'spicecraft' ); ?></h3>
					<p class="sc-empty-state__text">
						<?php
						/* translators: %s: search query */
						printf( esc_html__( 'No articles found matching "%s". Try checking for spelling errors or searching for broader terms like "turmeric", "milling", or "quality".', 'spicecraft' ), esc_html( $search_query ) );
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

<?php else : ?>

	<!-- Default / Products Search Template -->
	<div class="sc-container" style="padding: var(--sc-space-12, 3rem) var(--sc-space-4, 1rem);">
		<header class="page-header" style="margin-bottom: var(--sc-space-8); text-align: center;">
			<h1 class="page-title">
				<?php
				/* translators: %s: search query. */
				printf( esc_html__( 'Search Results for: %s', 'spicecraft' ), '<span style="color: var(--sc-color-primary);">' . esc_html( $search_query ) . '</span>' );
				?>
			</h1>
		</header>

		<div class="sc-content-area">
			<?php
			if ( have_posts() ) :
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content/content', 'search' );
				endwhile;

				the_posts_pagination(
					array(
						'prev_text' => '&larr; ' . esc_html__( 'Previous', 'spicecraft' ),
						'next_text' => esc_html__( 'Next', 'spicecraft' ) . ' &rarr;',
					)
				);
			else :
				get_template_part( 'template-parts/content/content', 'none' );
			endif;
			?>
		</div>
	</div>

<?php
endif;

get_footer();
