<?php
/**
 * The template for displaying Category Archive pages
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$current_cat    = get_queried_object();
$current_cat_id = ( $current_cat && isset( $current_cat->term_id ) ) ? $current_cat->term_id : get_query_var( 'cat' );
$cat_name       = ( $current_cat && isset( $current_cat->name ) ) ? $current_cat->name : single_cat_title( '', false );
if ( empty( $cat_name ) && $current_cat_id ) {
	$cat_name = get_cat_name( $current_cat_id );
}
if ( empty( $cat_name ) ) {
	$cat_name = __( 'Category', 'spicecraft' );
}
$cat_desc = $current_cat_id ? category_description( $current_cat_id ) : '';
$blog_url = function_exists( 'spicecraft_get_blog_url' ) ? spicecraft_get_blog_url() : home_url( '/blog/' );

// All published categories for filter pills
$categories = get_categories(
	array(
		'taxonomy'   => 'category',
		'hide_empty' => true,
		'orderby'    => 'count',
		'order'      => 'DESC',
	)
);
?>

<!-- Category Hero Header -->
<header class="sc-blog-hero">
	<div class="sc-container">
		<!-- Breadcrumbs -->
		<nav class="sc-breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'spicecraft' ); ?>">
			<ol class="sc-breadcrumbs__list">
				<li class="sc-breadcrumbs__item"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'spicecraft' ); ?></a></li>
				<li class="sc-breadcrumbs__item"><a href="<?php echo esc_url( $blog_url ); ?>"><?php esc_html_e( 'Blog & Insights', 'spicecraft' ); ?></a></li>
				<li class="sc-breadcrumbs__item sc-breadcrumbs__item--active" aria-current="page"><?php echo esc_html( $cat_name ); ?></li>
			</ol>
		</nav>

		<div class="sc-blog-hero__content">
			<span class="sc-eyebrow"><?php esc_html_e( 'Category Archive', 'spicecraft' ); ?></span>
			<h1 class="sc-blog-hero__title"><?php echo esc_html( $cat_name ); ?></h1>
			<?php if ( ! empty( $cat_desc ) ) : ?>
				<div class="sc-blog-hero__subtitle"><?php echo wp_kses_post( $cat_desc ); ?></div>
			<?php else : ?>
				<p class="sc-blog-hero__subtitle">
					<?php
					/* translators: %s: category name */
					printf( esc_html__( 'Exploring articles and expert industry knowledge in %s.', 'spicecraft' ), esc_html( $cat_name ) );
					?>
				</p>
			<?php endif; ?>
		</div>
	</div>
</header>

<div class="sc-blog-main-area">
	<div class="sc-container">

		<!-- Search & Category Filter Bar -->
		<div class="sc-blog-toolbar">
			<!-- Category Filter Pills -->
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
					<label for="sc-category-search-input" class="screen-reader-text"><?php esc_html_e( 'Search articles', 'spicecraft' ); ?></label>
					<div class="sc-blog-search-input-wrap">
						<svg class="sc-blog-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<circle cx="11" cy="11" r="8"/>
							<line x1="21" y1="21" x2="16.65" y2="16.65"/>
						</svg>
						<input 
							type="search" 
							id="sc-category-search-input" 
							name="s" 
							value="" 
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

		<!-- Category Grid -->
		<?php if ( have_posts() ) : ?>
			<div class="sc-blog-grid-header">
				<h2 class="sc-blog-grid-title"><?php echo esc_html( $cat_name ); ?></h2>
				<span class="sc-blog-grid-count">
					<?php
					global $wp_query;
					/* translators: %d: number of posts */
					printf( esc_html( _n( '%d article', '%d articles', $wp_query->found_posts, 'spicecraft' ) ), absint( $wp_query->found_posts ) );
					?>
				</span>
			</div>

			<div class="sc-grid sc-grid--3 sc-blog-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content/article-card' );
				endwhile;
				?>
			</div>

			<!-- Pagination -->
			<nav class="sc-pagination sc-blog-pagination" aria-label="<?php esc_attr_e( 'Category pagination', 'spicecraft' ); ?>">
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
			<!-- Empty State -->
			<div class="sc-empty-state sc-blog-empty">
				<div class="sc-empty-state__icon" aria-hidden="true">
					<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
						<circle cx="11" cy="11" r="8"/>
						<line x1="21" y1="21" x2="16.65" y2="16.65"/>
					</svg>
				</div>
				<h3 class="sc-empty-state__title"><?php esc_html_e( 'No Articles in this Category', 'spicecraft' ); ?></h3>
				<p class="sc-empty-state__text"><?php esc_html_e( 'We have not published any articles under this category yet. Please explore our other categories or check back soon.', 'spicecraft' ); ?></p>
				<div class="sc-empty-state__actions">
					<a href="<?php echo esc_url( $blog_url ); ?>" class="sc-btn sc-btn--primary">
						<?php esc_html_e( 'View All Articles', 'spicecraft' ); ?>
					</a>
				</div>
			</div>
		<?php endif; ?>

	</div>
</div>

<?php
get_footer();
