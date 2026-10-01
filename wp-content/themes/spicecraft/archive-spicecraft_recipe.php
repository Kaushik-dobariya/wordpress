<?php
/**
 * The template for displaying Recipe Archive & Culinary Inspiration Hub
 *
 * Route: /recipes/
 *
 * Implements an editorial culinary journal:
 * - Single H1 hero
 * - Editorial Featured Recipe showcase
 * - Comprehensive multi-dimensional filtering (Category, Cuisine, Meal Type, Difficulty)
 * - Live/GET search preserving URL state & browser history
 * - Accessible mobile filter drawer
 * - Reusable card grid with standard WordPress pagination
 * - Product Discovery & Final CTAs
 *
 * @package SpiceCraft
 * @since 1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Fetch settings
$settings = function_exists( 'spicecraft_get_recipe_settings' ) ? spicecraft_get_recipe_settings() : array();

// Hero Content
$eyebrow      = ! empty( $settings['eyebrow'] ) ? $settings['eyebrow'] : __( 'Culinary Inspiration & Test Kitchen', 'spicecraft' );
$heading      = ! empty( $settings['heading'] ) ? $settings['heading'] : __( 'Artisanal Spice Recipes', 'spicecraft' );
$introduction = ! empty( $settings['introduction'] ) ? $settings['introduction'] : __( 'Discover time-honored recipes, spice-pairings, and culinary techniques formulated by our master blenders to bring out the authentic soul of Indian and global cuisine.', 'spicecraft' );
$desktop_hero_id = ! empty( $settings['desktop_hero_id'] ) ? absint( $settings['desktop_hero_id'] ) : 0;
$mobile_hero_id  = ! empty( $settings['mobile_hero_id'] ) ? absint( $settings['mobile_hero_id'] ) : 0;

// Filter URL parameters
$search_query   = isset( $_GET['sc_search'] ) ? sanitize_text_field( wp_unslash( $_GET['sc_search'] ) ) : ( isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '' );
$filter_cat     = isset( $_GET['sc_cat'] ) ? sanitize_key( $_GET['sc_cat'] ) : '';
$filter_cuisine = isset( $_GET['sc_cuisine'] ) ? sanitize_key( $_GET['sc_cuisine'] ) : '';
$filter_meal    = isset( $_GET['sc_meal'] ) ? sanitize_key( $_GET['sc_meal'] ) : '';
$filter_diff    = isset( $_GET['sc_diff'] ) ? sanitize_key( $_GET['sc_diff'] ) : '';
$filter_sort    = isset( $_GET['sc_sort'] ) ? sanitize_key( $_GET['sc_sort'] ) : ( $settings['default_sort'] ?? 'date_desc' );

// Check if currently on a taxonomy archive
$current_term = get_queried_object();
if ( is_tax( 'spicecraft_recipe_category' ) && ! empty( $current_term->slug ) ) {
	$filter_cat = $current_term->slug;
	$heading    = sprintf( __( 'Recipes: %s', 'spicecraft' ), $current_term->name );
	if ( ! empty( $current_term->description ) ) {
		$introduction = $current_term->description;
	}
} elseif ( is_tax( 'spicecraft_cuisine' ) && ! empty( $current_term->slug ) ) {
	$filter_cuisine = $current_term->slug;
	$heading        = sprintf( __( '%s Cuisine Recipes', 'spicecraft' ), $current_term->name );
	if ( ! empty( $current_term->description ) ) {
		$introduction = $current_term->description;
	}
} elseif ( is_tax( 'spicecraft_meal_type' ) && ! empty( $current_term->slug ) ) {
	$filter_meal = $current_term->slug;
	$heading     = sprintf( __( '%s Recipes', 'spicecraft' ), $current_term->name );
	if ( ! empty( $current_term->description ) ) {
		$introduction = $current_term->description;
	}
}

// Build Filter Query
$paged = max( 1, get_query_var( 'paged' ), get_query_var( 'page' ) );
$per_page = ! empty( $settings['recipes_per_page'] ) ? absint( $settings['recipes_per_page'] ) : 9;

$query_args = array(
	'post_type'      => 'spicecraft_recipe',
	'post_status'    => 'publish',
	'posts_per_page' => $per_page,
	'paged'          => $paged,
);

// Search Query
if ( ! empty( $search_query ) ) {
	$query_args['s'] = $search_query;
}

// Tax Queries
$tax_queries = array();
if ( ! empty( $filter_cat ) ) {
	$tax_queries[] = array(
		'taxonomy' => 'spicecraft_recipe_category',
		'field'    => 'slug',
		'terms'    => $filter_cat,
	);
}
if ( ! empty( $filter_cuisine ) ) {
	$tax_queries[] = array(
		'taxonomy' => 'spicecraft_cuisine',
		'field'    => 'slug',
		'terms'    => $filter_cuisine,
	);
}
if ( ! empty( $filter_meal ) ) {
	$tax_queries[] = array(
		'taxonomy' => 'spicecraft_meal_type',
		'field'    => 'slug',
		'terms'    => $filter_meal,
	);
}
if ( ! empty( $tax_queries ) ) {
	$query_args['tax_query'] = array_merge( array( 'relation' => 'AND' ), $tax_queries );
}

// Meta Queries (Difficulty & Timings)
$meta_queries = array();
if ( ! empty( $filter_diff ) ) {
	$meta_queries[] = array(
		'key'   => '_spicecraft_recipe_difficulty',
		'value' => $filter_diff,
	);
}
if ( ! empty( $meta_queries ) ) {
	$query_args['meta_query'] = $meta_queries;
}

// Sorting
switch ( $filter_sort ) {
	case 'title_asc':
		$query_args['orderby'] = 'title';
		$query_args['order']   = 'ASC';
		break;
	case 'prep_time':
		$query_args['meta_key'] = '_spicecraft_recipe_prep_minutes';
		$query_args['orderby']  = 'meta_value_num';
		$query_args['order']    = 'ASC';
		break;
	case 'total_time':
		$query_args['meta_key'] = '_spicecraft_recipe_total_minutes';
		$query_args['orderby']  = 'meta_value_num';
		$query_args['order']    = 'ASC';
		break;
	case 'date_desc':
	default:
		$query_args['orderby'] = 'date';
		$query_args['order']   = 'DESC';
		break;
}

$recipes_query = new WP_Query( $query_args );

// Available Filter Terms (only terms with published recipes)
$avail_cats = get_terms( array(
	'taxonomy'   => 'spicecraft_recipe_category',
	'hide_empty' => true,
) );
$avail_cuisines = get_terms( array(
	'taxonomy'   => 'spicecraft_cuisine',
	'hide_empty' => true,
) );
$avail_meals = get_terms( array(
	'taxonomy'   => 'spicecraft_meal_type',
	'hide_empty' => true,
) );

// Check if any filters are currently active
$has_active_filters = ! empty( $search_query ) || ! empty( $filter_cat ) || ! empty( $filter_cuisine ) || ! empty( $filter_meal ) || ! empty( $filter_diff );

// Featured Recipe (Editorial Showcase - only on unfiltered first page of main archive)
$featured_recipe = null;
if ( ! $has_active_filters && 1 === $paged && ! is_tax() && ! empty( $settings['featured_recipe_id'] ) ) {
	$feat_post = get_post( absint( $settings['featured_recipe_id'] ) );
	if ( $feat_post && 'publish' === $feat_post->post_status && 'spicecraft_recipe' === $feat_post->post_type ) {
		$featured_recipe = $feat_post;
	}
}

// Hero styles
$hero_bg_desktop = $desktop_hero_id ? wp_get_attachment_image_url( $desktop_hero_id, 'full' ) : '';
$hero_bg_mobile  = $mobile_hero_id ? wp_get_attachment_image_url( $mobile_hero_id, 'full' ) : '';
?>

<div class="sc-recipe-archive-wrap">

	<!-- 1. Editorial Hero Banner -->
	<header class="sc-recipe-hero" <?php echo $hero_bg_desktop ? 'style="background-image: url(' . esc_url( $hero_bg_desktop ) . ');"' : ''; ?>>
		<div class="sc-recipe-hero__overlay"></div>
		<div class="sc-container sc-recipe-hero__inner">
			<?php if ( ! empty( $eyebrow ) ) : ?>
				<span class="sc-eyebrow sc-recipe-hero__eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
			<?php endif; ?>

			<h1 class="sc-recipe-hero__title"><?php echo esc_html( $heading ); ?></h1>

			<?php if ( ! empty( $introduction ) ) : ?>
				<p class="sc-recipe-hero__intro"><?php echo esc_html( $introduction ); ?></p>
			<?php endif; ?>
		</div>
	</header>

	<div class="sc-container sc-recipe-archive-container">

		<!-- 2. Promoted Featured Recipe Showcase (Only when configured on first page) -->
		<?php if ( $featured_recipe ) :
			$f_id   = $featured_recipe->ID;
			$f_meta = function_exists( 'spicecraft_get_recipe_meta' ) ? spicecraft_get_recipe_meta( $f_id ) : array();
			$f_cats = get_the_terms( $f_id, 'spicecraft_recipe_category' );
			$f_cat_name = ( ! empty( $f_cats ) && ! is_wp_error( $f_cats ) ) ? $f_cats[0]->name : __( 'Featured Recipe', 'spicecraft' );
			?>
			<section class="sc-recipe-featured-showcase" aria-labelledby="featured-recipe-title">
				<div class="sc-featured-card">
					<div class="sc-featured-card__media">
						<a href="<?php echo esc_url( get_permalink( $f_id ) ); ?>" tabindex="-1" aria-hidden="true">
							<?php if ( has_post_thumbnail( $f_id ) ) : ?>
								<?php echo get_the_post_thumbnail( $f_id, 'large', array( 'class' => 'sc-featured-card__img', 'alt' => get_the_title( $f_id ) ) ); ?>
							<?php else : ?>
								<div class="sc-recipe-card__placeholder" style="height: 380px;">
									<span><?php esc_html_e( 'Test Kitchen Signature', 'spicecraft' ); ?></span>
								</div>
							<?php endif; ?>
						</a>
						<span class="sc-recipe-badge sc-recipe-badge--featured"><?php esc_html_e( 'Master Blender’s Choice', 'spicecraft' ); ?></span>
					</div>

					<div class="sc-featured-card__content">
						<span class="sc-featured-card__cat"><?php echo esc_html( $f_cat_name ); ?></span>
						<h2 id="featured-recipe-title" class="sc-featured-card__title">
							<a href="<?php echo esc_url( get_permalink( $f_id ) ); ?>"><?php echo esc_html( get_the_title( $f_id ) ); ?></a>
						</h2>
						<p class="sc-featured-card__excerpt">
							<?php echo esc_html( wp_trim_words( get_the_excerpt( $f_id ), 24 ) ); ?>
						</p>

						<div class="sc-recipe-card__meta-bar" style="margin-bottom: 20px;">
							<?php if ( ! empty( $f_meta['formatted_total'] ) ) : ?>
								<span class="sc-recipe-meta-pill">
									<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
									<span><?php echo esc_html( $f_meta['formatted_total'] ); ?></span>
								</span>
							<?php endif; ?>
							<?php if ( ! empty( $f_meta['yield'] ) ) : ?>
								<span class="sc-recipe-meta-pill">
									<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
									<span><?php echo esc_html( $f_meta['yield'] ); ?></span>
								</span>
							<?php endif; ?>
							<?php if ( ! empty( $f_meta['difficulty'] ) ) : ?>
								<span class="sc-recipe-meta-pill sc-recipe-meta-pill--diff" data-difficulty="<?php echo esc_attr( $f_meta['difficulty'] ); ?>">
									<span><?php echo esc_html( spicecraft_get_recipe_difficulty_label( $f_meta['difficulty'] ) ); ?></span>
								</span>
							<?php endif; ?>
						</div>

						<a href="<?php echo esc_url( get_permalink( $f_id ) ); ?>" class="sc-btn sc-btn--primary sc-btn--md">
							<span><?php esc_html_e( 'View Full Recipe', 'spicecraft' ); ?></span>
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
						</a>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<!-- 3. Recipe Discovery Bar & Filters -->
		<section class="sc-recipe-discovery" aria-label="<?php esc_attr_e( 'Recipe Discovery & Filters', 'spicecraft' ); ?>" id="recipe-discovery">
			<form method="get" action="<?php echo esc_url( get_post_type_archive_link( 'spicecraft_recipe' ) ); ?>" class="sc-recipe-filter-form" id="sc-recipe-filter-form">

				<div class="sc-discovery-bar">
					<!-- Search Input -->
					<?php if ( ! empty( $settings['show_search'] ) ) : ?>
						<div class="sc-discovery-search">
							<label for="sc-recipe-search-input" class="screen-reader-text"><?php esc_html_e( 'Search Recipes or Ingredients', 'spicecraft' ); ?></label>
							<div class="sc-search-input-wrap">
								<svg class="sc-icon sc-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
								<input type="search" id="sc-recipe-search-input" name="sc_search" value="<?php echo esc_attr( $search_query ); ?>" placeholder="<?php esc_attr_e( 'Search recipes or ingredients...', 'spicecraft' ); ?>" />
								<?php if ( ! empty( $search_query ) ) : ?>
									<button type="button" class="sc-search-clear-btn" id="sc-clear-search-btn" aria-label="<?php esc_attr_e( 'Clear Search', 'spicecraft' ); ?>">&times;</button>
								<?php endif; ?>
							</div>
						</div>
					<?php endif; ?>

					<!-- Mobile Filter Trigger -->
					<div class="sc-discovery-mobile-toggle">
						<button type="button" class="sc-btn sc-btn--secondary sc-btn--sm sc-filter-drawer-open" aria-expanded="false" aria-controls="sc-recipe-filter-drawer">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
							<span><?php esc_html_e( 'Filters', 'spicecraft' ); ?></span>
							<?php if ( $has_active_filters ) : ?>
								<span class="sc-filter-count-badge" aria-label="<?php esc_attr_e( 'Active Filters', 'spicecraft' ); ?>">●</span>
							<?php endif; ?>
						</button>
					</div>

					<!-- Desktop Dropdowns -->
					<div class="sc-discovery-dropdowns">
						<!-- Recipe Category -->
						<?php if ( ! empty( $settings['show_category_filter'] ) && ! empty( $avail_cats ) && ! is_wp_error( $avail_cats ) ) : ?>
							<div class="sc-filter-field">
								<label for="sc-filter-cat" class="screen-reader-text"><?php esc_html_e( 'Category', 'spicecraft' ); ?></label>
								<select name="sc_cat" id="sc-filter-cat" class="sc-filter-select">
									<option value=""><?php esc_html_e( 'All Categories', 'spicecraft' ); ?></option>
									<?php foreach ( $avail_cats as $cat ) : ?>
										<option value="<?php echo esc_attr( $cat->slug ); ?>" <?php selected( $filter_cat, $cat->slug ); ?>>
											<?php echo esc_html( $cat->name ); ?> (<?php echo esc_html( $cat->count ); ?>)
										</option>
									<?php endforeach; ?>
								</select>
							</div>
						<?php endif; ?>

						<!-- Cuisine -->
						<?php if ( ! empty( $settings['show_cuisine_filter'] ) && ! empty( $avail_cuisines ) && ! is_wp_error( $avail_cuisines ) ) : ?>
							<div class="sc-filter-field">
								<label for="sc-filter-cuisine" class="screen-reader-text"><?php esc_html_e( 'Cuisine', 'spicecraft' ); ?></label>
								<select name="sc_cuisine" id="sc-filter-cuisine" class="sc-filter-select">
									<option value=""><?php esc_html_e( 'All Cuisines', 'spicecraft' ); ?></option>
									<?php foreach ( $avail_cuisines as $cui ) : ?>
										<option value="<?php echo esc_attr( $cui->slug ); ?>" <?php selected( $filter_cuisine, $cui->slug ); ?>>
											<?php echo esc_html( $cui->name ); ?> (<?php echo esc_html( $cui->count ); ?>)
										</option>
									<?php endforeach; ?>
								</select>
							</div>
						<?php endif; ?>

						<!-- Meal Type -->
						<?php if ( ! empty( $settings['show_meal_type_filter'] ) && ! empty( $avail_meals ) && ! is_wp_error( $avail_meals ) ) : ?>
							<div class="sc-filter-field">
								<label for="sc-filter-meal" class="screen-reader-text"><?php esc_html_e( 'Meal Type', 'spicecraft' ); ?></label>
								<select name="sc_meal" id="sc-filter-meal" class="sc-filter-select">
									<option value=""><?php esc_html_e( 'All Meals', 'spicecraft' ); ?></option>
									<?php foreach ( $avail_meals as $meal ) : ?>
										<option value="<?php echo esc_attr( $meal->slug ); ?>" <?php selected( $filter_meal, $meal->slug ); ?>>
											<?php echo esc_html( $meal->name ); ?> (<?php echo esc_html( $meal->count ); ?>)
										</option>
									<?php endforeach; ?>
								</select>
							</div>
						<?php endif; ?>

						<!-- Difficulty -->
						<?php if ( ! empty( $settings['show_difficulty_filter'] ) ) : ?>
							<div class="sc-filter-field">
								<label for="sc-filter-diff" class="screen-reader-text"><?php esc_html_e( 'Difficulty', 'spicecraft' ); ?></label>
								<select name="sc_diff" id="sc-filter-diff" class="sc-filter-select">
									<option value=""><?php esc_html_e( 'Any Difficulty', 'spicecraft' ); ?></option>
									<option value="easy" <?php selected( $filter_diff, 'easy' ); ?>><?php esc_html_e( 'Easy', 'spicecraft' ); ?></option>
									<option value="medium" <?php selected( $filter_diff, 'medium' ); ?>><?php esc_html_e( 'Medium', 'spicecraft' ); ?></option>
									<option value="advanced" <?php selected( $filter_diff, 'advanced' ); ?>><?php esc_html_e( 'Advanced', 'spicecraft' ); ?></option>
								</select>
							</div>
						<?php endif; ?>

						<!-- Sort Order -->
						<div class="sc-filter-field sc-filter-field--sort">
							<label for="sc-filter-sort" class="screen-reader-text"><?php esc_html_e( 'Sort by', 'spicecraft' ); ?></label>
							<select name="sc_sort" id="sc-filter-sort" class="sc-filter-select">
								<option value="date_desc" <?php selected( $filter_sort, 'date_desc' ); ?>><?php esc_html_e( 'Newest First', 'spicecraft' ); ?></option>
								<option value="title_asc" <?php selected( $filter_sort, 'title_asc' ); ?>><?php esc_html_e( 'Alphabetical (A–Z)', 'spicecraft' ); ?></option>
								<option value="prep_time" <?php selected( $filter_sort, 'prep_time' ); ?>><?php esc_html_e( 'Fastest Prep', 'spicecraft' ); ?></option>
								<option value="total_time" <?php selected( $filter_sort, 'total_time' ); ?>><?php esc_html_e( 'Fastest Total Time', 'spicecraft' ); ?></option>
							</select>
						</div>
					</div>
				</div>

				<!-- Active Filter Chips / Clear All -->
				<?php if ( $has_active_filters ) : ?>
					<div class="sc-active-filters-strip" aria-label="<?php esc_attr_e( 'Active Filters', 'spicecraft' ); ?>">
						<span class="sc-active-filters-label"><?php esc_html_e( 'Active Filters:', 'spicecraft' ); ?></span>

						<?php if ( ! empty( $search_query ) ) : ?>
							<span class="sc-filter-chip">
								<span>"<?php echo esc_html( $search_query ); ?>"</span>
								<a href="<?php echo esc_url( remove_query_arg( array( 'sc_search', 's' ) ) ); ?>" class="sc-filter-chip__remove" aria-label="<?php esc_attr_e( 'Remove search query', 'spicecraft' ); ?>">&times;</a>
							</span>
						<?php endif; ?>

						<?php if ( ! empty( $filter_cat ) ) :
							$c_obj = get_term_by( 'slug', $filter_cat, 'spicecraft_recipe_category' );
							$c_label = $c_obj ? $c_obj->name : $filter_cat;
							?>
							<span class="sc-filter-chip">
								<span><?php echo esc_html( $c_label ); ?></span>
								<a href="<?php echo esc_url( remove_query_arg( 'sc_cat' ) ); ?>" class="sc-filter-chip__remove" aria-label="<?php esc_attr_e( 'Remove category filter', 'spicecraft' ); ?>">&times;</a>
							</span>
						<?php endif; ?>

						<?php if ( ! empty( $filter_cuisine ) ) :
							$cui_obj = get_term_by( 'slug', $filter_cuisine, 'spicecraft_cuisine' );
							$cui_label = $cui_obj ? $cui_obj->name : $filter_cuisine;
							?>
							<span class="sc-filter-chip">
								<span><?php echo esc_html( $cui_label ); ?></span>
								<a href="<?php echo esc_url( remove_query_arg( 'sc_cuisine' ) ); ?>" class="sc-filter-chip__remove" aria-label="<?php esc_attr_e( 'Remove cuisine filter', 'spicecraft' ); ?>">&times;</a>
							</span>
						<?php endif; ?>

						<?php if ( ! empty( $filter_meal ) ) :
							$m_obj = get_term_by( 'slug', $filter_meal, 'spicecraft_meal_type' );
							$m_label = $m_obj ? $m_obj->name : $filter_meal;
							?>
							<span class="sc-filter-chip">
								<span><?php echo esc_html( $m_label ); ?></span>
								<a href="<?php echo esc_url( remove_query_arg( 'sc_meal' ) ); ?>" class="sc-filter-chip__remove" aria-label="<?php esc_attr_e( 'Remove meal type filter', 'spicecraft' ); ?>">&times;</a>
							</span>
						<?php endif; ?>

						<?php if ( ! empty( $filter_diff ) ) : ?>
							<span class="sc-filter-chip">
								<span><?php echo esc_html( spicecraft_get_recipe_difficulty_label( $filter_diff ) ); ?></span>
								<a href="<?php echo esc_url( remove_query_arg( 'sc_diff' ) ); ?>" class="sc-filter-chip__remove" aria-label="<?php esc_attr_e( 'Remove difficulty filter', 'spicecraft' ); ?>">&times;</a>
							</span>
						<?php endif; ?>

						<a href="<?php echo esc_url( get_post_type_archive_link( 'spicecraft_recipe' ) ); ?>" class="sc-clear-all-link">
							<?php esc_html_e( 'Clear All', 'spicecraft' ); ?>
						</a>
					</div>
				<?php endif; ?>

			</form>
		</section>

		<!-- 4. Mobile Filter Off-Canvas Drawer -->
		<div id="sc-recipe-filter-drawer" class="sc-filter-drawer" aria-hidden="true" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Filter Recipes', 'spicecraft' ); ?>">
			<div class="sc-filter-drawer__backdrop"></div>
			<div class="sc-filter-drawer__panel">
				<div class="sc-filter-drawer__header">
					<h2 class="sc-filter-drawer__title"><?php esc_html_e( 'Filter Recipes', 'spicecraft' ); ?></h2>
					<button type="button" class="sc-filter-drawer__close" aria-label="<?php esc_attr_e( 'Close filter drawer', 'spicecraft' ); ?>">&times;</button>
				</div>
				<div class="sc-filter-drawer__body" id="sc-mobile-filter-body">
					<!-- Populated by JS cloning or native mobile form -->
				</div>
				<div class="sc-filter-drawer__footer">
					<button type="button" class="sc-btn sc-btn--primary sc-btn--full sc-filter-drawer__apply">
						<?php esc_html_e( 'Apply Filters', 'spicecraft' ); ?>
					</button>
				</div>
			</div>
		</div>

		<!-- 5. Recipe Grid & Results -->
		<div class="sc-recipe-results-section" id="sc-recipe-grid-container">
			<?php if ( $recipes_query->have_posts() ) : ?>
				<div class="sc-grid sc-grid--3 sc-recipes-archive-grid">
					<?php
					while ( $recipes_query->have_posts() ) :
						$recipes_query->the_post();
						spicecraft_render_recipe_card( get_post() );
					endwhile;
					?>
				</div>

				<!-- 6. Pagination -->
				<?php
				$total_pages = $recipes_query->max_num_pages;
				if ( $total_pages > 1 ) :
					$page_links = paginate_links( array(
						'base'      => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
						'format'    => '?paged=%#%',
						'current'   => $paged,
						'total'     => $total_pages,
						'prev_text' => '&larr; ' . __( 'Previous', 'spicecraft' ),
						'next_text' => __( 'Next', 'spicecraft' ) . ' &rarr;',
						'type'      => 'list',
					) );
					if ( ! empty( $page_links ) ) :
						?>
						<nav class="sc-pagination sc-recipe-pagination" aria-label="<?php esc_attr_e( 'Recipe Archive Pagination', 'spicecraft' ); ?>">
							<?php echo wp_kses_post( $page_links ); ?>
						</nav>
						<?php
					endif;
				endif;
				wp_reset_postdata();
				?>

			<?php else : ?>
				<!-- Empty Results State -->
				<div class="sc-empty-state sc-recipe-empty-state">
					<div class="sc-empty-state__icon">
						<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
					</div>
					<h2 class="sc-empty-state__title"><?php esc_html_e( 'No Recipes Found', 'spicecraft' ); ?></h2>
					<p class="sc-empty-state__desc">
						<?php if ( $has_active_filters ) : ?>
							<?php esc_html_e( 'We couldn’t find any recipes matching your current filter criteria. Try adjusting your search term or clearing one of the filters.', 'spicecraft' ); ?>
						<?php else : ?>
							<?php esc_html_e( 'Our test kitchen is currently crafting authentic recipes and spice pairings. Check back soon for new creations!', 'spicecraft' ); ?>
						<?php endif; ?>
					</p>
					<?php if ( $has_active_filters ) : ?>
						<a href="<?php echo esc_url( get_post_type_archive_link( 'spicecraft_recipe' ) ); ?>" class="sc-btn sc-btn--secondary sc-btn--sm">
							<?php esc_html_e( 'Reset All Filters', 'spicecraft' ); ?>
						</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>

		<!-- 7. Product Discovery Banner ("Explore the Spices Used in Our Kitchen") -->
		<section class="sc-recipe-product-discovery-banner">
			<div class="sc-product-discovery-inner">
				<div class="sc-product-discovery-text">
					<span class="sc-eyebrow"><?php esc_html_e( 'Artisanal Sourcing', 'spicecraft' ); ?></span>
					<h2 class="sc-product-discovery-title"><?php esc_html_e( 'Authentic Taste Starts with Unadulterated Spices', 'spicecraft' ); ?></h2>
					<p class="sc-product-discovery-desc">
						<?php esc_html_e( 'From single-origin Tellicherry black peppercorns to slow-roasted garam masala blends, explore our catalog of export-grade spices engineered for culinary excellence.', 'spicecraft' ); ?>
					</p>
					<a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ) ); ?>" class="sc-btn sc-btn--primary sc-btn--md">
						<span><?php esc_html_e( 'Explore All Spices', 'spicecraft' ); ?></span>
						<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
					</a>
				</div>
			</div>
		</section>

		<!-- 8. Final Call to Action Section -->
		<?php if ( ! empty( $settings['final_cta_enabled'] ) ) :
			$cta_h        = ! empty( $settings['cta_heading'] ) ? $settings['cta_heading'] : __( 'Bring These Recipes to Life with Authentic Spices', 'spicecraft' );
			$cta_desc     = ! empty( $settings['cta_description'] ) ? $settings['cta_description'] : __( 'Browse our catalog of pure, cold-ground spices and master artisanal blends engineered for superior flavor retention and aroma.', 'spicecraft' );
			$cta_bg_id    = ! empty( $settings['cta_image_id'] ) ? absint( $settings['cta_image_id'] ) : 0;
			$cta_bg_url   = $cta_bg_id ? wp_get_attachment_image_url( $cta_bg_id, 'full' ) : '';
			$whatsapp_num = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'whatsapp_number', '' ) : '';
			$contact_url  = home_url( '/#contact' );
			?>
			<section class="sc-recipe-final-cta" <?php echo $cta_bg_url ? 'style="background-image: url(' . esc_url( $cta_bg_url ) . ');"' : ''; ?>>
				<div class="sc-recipe-final-cta__overlay"></div>
				<div class="sc-container sc-recipe-final-cta__inner">
					<h2 class="sc-recipe-final-cta__title"><?php echo esc_html( $cta_h ); ?></h2>
					<?php if ( ! empty( $cta_desc ) ) : ?>
						<p class="sc-recipe-final-cta__desc"><?php echo esc_html( $cta_desc ); ?></p>
					<?php endif; ?>

					<div class="sc-recipe-final-cta__actions">
						<a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ) ); ?>" class="sc-btn sc-btn--primary sc-btn--lg">
							<span><?php esc_html_e( 'Browse Spices Catalog', 'spicecraft' ); ?></span>
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
						</a>

						<?php if ( ! empty( $settings['cta_whatsapp_enabled'] ) && ! empty( $whatsapp_num ) ) :
							$clean_wa = preg_replace( '/[^0-9]/', '', $whatsapp_num );
							$wa_text  = rawurlencode( __( 'Hello SpiceCraft, I am exploring your recipes and would like to enquire about ordering spices.', 'spicecraft' ) );
							$wa_link  = 'https://wa.me/' . $clean_wa . '?text=' . $wa_text;
							?>
							<a href="<?php echo esc_url( $wa_link ); ?>" class="sc-btn sc-btn--secondary sc-btn--lg" target="_blank" rel="noopener noreferrer">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
								<span><?php esc_html_e( 'WhatsApp Us', 'spicecraft' ); ?></span>
							</a>
						<?php endif; ?>

						<?php if ( ! empty( $settings['cta_email_enabled'] ) ) : ?>
							<a href="<?php echo esc_url( $contact_url ); ?>" class="sc-btn sc-btn--outline sc-btn--lg" style="color: #fff; border-color: rgba(255,255,255,0.4);">
								<span><?php esc_html_e( 'Commercial Trade Enquiry', 'spicecraft' ); ?></span>
							</a>
						<?php endif; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

	</div><!-- .sc-recipe-archive-container -->

</div><!-- .sc-recipe-archive-wrap -->

<?php
get_footer();
