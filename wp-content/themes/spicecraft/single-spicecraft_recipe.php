<?php
/**
 * The template for displaying all single Recipe posts
 *
 * Route: /recipes/{slug}/
 *
 * Implements a premium culinary editorial experience:
 * - Accessible breadcrumbs
 * - Single semantic H1
 * - High-impact photography with LCP priority
 * - Quick facts bar (Prep, Cook, Total, Yield, Difficulty)
 * - Grouped ingredients with client-side interactive checklist
 * - Subtle "View Product" links for ingredients connected to WooCommerce products
 * - "Spices Used In This Recipe" catalog-mode product showcase
 * - Numbered instruction steps with step photography and tips
 * - Optional culinary notes and verified nutrition facts
 * - Web Share API, Copy Link fallback, WhatsApp share, and clean Print UX
 * - Schema.org Recipe JSON-LD structured data
 *
 * @package SpiceCraft
 * @since 1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	$recipe_id = get_the_ID();
	$meta      = function_exists( 'spicecraft_get_recipe_meta' ) ? spicecraft_get_recipe_meta( $recipe_id ) : array();

	// Taxonomies
	$categories   = get_the_terms( $recipe_id, 'spicecraft_recipe_category' );
	$primary_cat  = ( ! empty( $categories ) && ! is_wp_error( $categories ) ) ? $categories[0] : null;
	$cuisines     = get_the_terms( $recipe_id, 'spicecraft_cuisine' );
	$meal_types   = get_the_terms( $recipe_id, 'spicecraft_meal_type' );

	// Times & Facts
	$prep_time_str  = $meta['formatted_prep'] ?? '';
	$cook_time_str  = $meta['formatted_cook'] ?? '';
	$total_time_str = $meta['formatted_total'] ?? '';
	$yield_str      = $meta['yield'] ?? '';
	$difficulty     = $meta['difficulty'] ?? '';
	$diff_label     = function_exists( 'spicecraft_get_recipe_difficulty_label' ) ? spicecraft_get_recipe_difficulty_label( $difficulty ) : '';
	$dietary_claims = $meta['dietary'] ?? array();

	// Hero Image (Priority: Hero override -> Featured image)
	$hero_img_id = ! empty( $meta['hero_image_id'] ) ? $meta['hero_image_id'] : get_post_thumbnail_id( $recipe_id );
	$hero_url    = $hero_img_id ? wp_get_attachment_image_url( $hero_img_id, 'full' ) : '';

	// Linked Products for "Spices Used In This Recipe"
	$linked_product_ids = function_exists( 'spicecraft_get_recipe_linked_products' ) ? spicecraft_get_recipe_linked_products( $recipe_id ) : array();

	// Related Recipes
	$related_recipes = function_exists( 'spicecraft_get_related_recipes' ) ? spicecraft_get_related_recipes( $recipe_id, 3 ) : array();

	// Schema.org Structured Data Assembly
	$schema_data = array(
		'@context' => 'https://schema.org',
		'@type'    => 'Recipe',
		'name'     => get_the_title(),
	);

	$excerpt = get_the_excerpt();
	if ( ! empty( $excerpt ) ) {
		$schema_data['description'] = wp_strip_all_tags( $excerpt );
	}

	if ( $hero_url ) {
		$schema_data['image'] = array( esc_url( $hero_url ) );
	}

	if ( ! empty( $meta['prep_minutes'] ) ) {
		$schema_data['prepTime'] = spicecraft_minutes_to_iso8601( $meta['prep_minutes'] );
	}
	if ( ! empty( $meta['cook_minutes'] ) ) {
		$schema_data['cookTime'] = spicecraft_minutes_to_iso8601( $meta['cook_minutes'] );
	}
	if ( ! empty( $meta['total_minutes'] ) ) {
		$schema_data['totalTime'] = spicecraft_minutes_to_iso8601( $meta['total_minutes'] );
	}
	if ( ! empty( $yield_str ) ) {
		$schema_data['recipeYield'] = esc_attr( $yield_str );
	}
	if ( $primary_cat ) {
		$schema_data['recipeCategory'] = esc_attr( $primary_cat->name );
	}
	if ( ! empty( $cuisines ) && ! is_wp_error( $cuisines ) ) {
		$schema_data['recipeCuisine'] = esc_attr( $cuisines[0]->name );
	}

	// Schema suitableForDiet (Only verified claims)
	if ( in_array( 'vegetarian', $dietary_claims, true ) ) {
		$schema_data['suitableForDiet'][] = 'https://schema.org/VegetarianDiet';
	}
	if ( in_array( 'vegan', $dietary_claims, true ) ) {
		$schema_data['suitableForDiet'][] = 'https://schema.org/VeganDiet';
	}
	if ( in_array( 'gluten_free', $dietary_claims, true ) ) {
		$schema_data['suitableForDiet'][] = 'https://schema.org/GlutenFreeDiet';
	}

	// Schema Ingredients
	$schema_ingredients = array();
	if ( ! empty( $meta['ingredient_groups'] ) ) {
		foreach ( $meta['ingredient_groups'] as $grp ) {
			if ( ! empty( $grp['items'] ) ) {
				foreach ( $grp['items'] as $itm ) {
					$line = trim( ( $itm['quantity'] ? $itm['quantity'] . ' ' : '' ) . ( $itm['unit'] ? $itm['unit'] . ' ' : '' ) . $itm['ingredient'] . ( $itm['note'] ? ', ' . $itm['note'] : '' ) );
					if ( ! empty( $line ) ) {
						$schema_ingredients[] = $line;
					}
				}
			}
		}
	}
	if ( ! empty( $schema_ingredients ) ) {
		$schema_data['recipeIngredient'] = $schema_ingredients;
	}

	// Schema Instructions
	if ( ! empty( $meta['instruction_steps'] ) ) {
		$schema_instructions = array();
		foreach ( $meta['instruction_steps'] as $s_item ) {
			$step_obj = array(
				'@type' => 'HowToStep',
				'text'  => wp_strip_all_tags( $s_item['instruction'] ),
			);
			if ( ! empty( $s_item['heading'] ) ) {
				$step_obj['name'] = esc_attr( $s_item['heading'] );
			}
			if ( ! empty( $s_item['image_id'] ) ) {
				$s_img_url = wp_get_attachment_image_url( $s_item['image_id'], 'large' );
				if ( $s_img_url ) {
					$step_obj['image'] = esc_url( $s_img_url );
				}
			}
			$schema_instructions[] = $step_obj;
		}
		if ( ! empty( $schema_instructions ) ) {
			$schema_data['recipeInstructions'] = $schema_instructions;
		}
	}

	// Schema Nutrition (Only verified values)
	if ( ! empty( $meta['nutrition']['calories'] ) ) {
		$schema_data['nutrition'] = array(
			'@type'    => 'NutritionInformation',
			'calories' => esc_attr( $meta['nutrition']['calories'] ) . ' calories',
		);
		if ( ! empty( $meta['nutrition']['serving_size'] ) ) {
			$schema_data['nutrition']['servingSize'] = esc_attr( $meta['nutrition']['serving_size'] );
		}
		if ( ! empty( $meta['nutrition']['protein'] ) ) {
			$schema_data['nutrition']['proteinContent'] = esc_attr( $meta['nutrition']['protein'] );
		}
		if ( ! empty( $meta['nutrition']['fat'] ) ) {
			$schema_data['nutrition']['fatContent'] = esc_attr( $meta['nutrition']['fat'] );
		}
		if ( ! empty( $meta['nutrition']['carbs'] ) ) {
			$schema_data['nutrition']['carbohydrateContent'] = esc_attr( $meta['nutrition']['carbs'] );
		}
	}
	?>

	<!-- JSON-LD Structured Data Output -->
	<script type="application/ld+json">
		<?php echo wp_json_encode( $schema_data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ); ?>
	</script>

	<article id="recipe-<?php the_ID(); ?>" <?php post_class( 'sc-single-recipe' ); ?>>

		<!-- 1. Accessible Breadcrumbs -->
		<nav class="sc-breadcrumbs sc-single-recipe__breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'spicecraft' ); ?>">
			<div class="sc-container">
				<ol class="sc-breadcrumbs__list" itemscope itemtype="https://schema.org/BreadcrumbList">
					<li class="sc-breadcrumbs__item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" itemprop="item"><span itemprop="name"><?php esc_html_e( 'Home', 'spicecraft' ); ?></span></a>
						<meta itemprop="position" content="1" />
					</li>
					<li class="sc-breadcrumbs__separator" aria-hidden="true">&rsaquo;</li>
					<li class="sc-breadcrumbs__item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
						<a href="<?php echo esc_url( get_post_type_archive_link( 'spicecraft_recipe' ) ); ?>" itemprop="item"><span itemprop="name"><?php esc_html_e( 'Recipes', 'spicecraft' ); ?></span></a>
						<meta itemprop="position" content="2" />
					</li>
					<?php if ( $primary_cat ) : ?>
						<li class="sc-breadcrumbs__separator" aria-hidden="true">&rsaquo;</li>
						<li class="sc-breadcrumbs__item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
							<a href="<?php echo esc_url( get_term_link( $primary_cat ) ); ?>" itemprop="item"><span itemprop="name"><?php echo esc_html( $primary_cat->name ); ?></span></a>
							<meta itemprop="position" content="3" />
						</li>
					<?php endif; ?>
					<li class="sc-breadcrumbs__separator" aria-hidden="true">&rsaquo;</li>
					<li class="sc-breadcrumbs__item sc-breadcrumbs__item--active" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" aria-current="page">
						<span itemprop="name"><?php the_title(); ?></span>
						<meta itemprop="position" content="<?php echo $primary_cat ? '4' : '3'; ?>" />
					</li>
				</ol>
			</div>
		</nav>

		<!-- 2. Recipe Header & Summary -->
		<header class="sc-single-recipe__header">
			<div class="sc-container sc-single-recipe__header-inner">
				<div class="sc-single-recipe__tax-strip">
					<?php if ( $primary_cat ) : ?>
						<a href="<?php echo esc_url( get_term_link( $primary_cat ) ); ?>" class="sc-recipe-badge sc-recipe-badge--cat">
							<?php echo esc_html( $primary_cat->name ); ?>
						</a>
					<?php endif; ?>

					<?php if ( ! empty( $cuisines ) && ! is_wp_error( $cuisines ) ) :
						foreach ( $cuisines as $cui ) : ?>
							<a href="<?php echo esc_url( get_term_link( $cui ) ); ?>" class="sc-recipe-badge sc-recipe-badge--cuisine">
								<?php echo esc_html( $cui->name ); ?>
							</a>
						<?php endforeach;
					endif; ?>

					<?php if ( ! empty( $meal_types ) && ! is_wp_error( $meal_types ) ) :
						foreach ( $meal_types as $mt ) : ?>
							<a href="<?php echo esc_url( get_term_link( $mt ) ); ?>" class="sc-recipe-badge sc-recipe-badge--meal">
								<?php echo esc_html( $mt->name ); ?>
							</a>
						<?php endforeach;
					endif; ?>
				</div>

				<h1 class="sc-single-recipe__title"><?php the_title(); ?></h1>

				<?php if ( ! empty( $excerpt ) ) : ?>
					<p class="sc-single-recipe__excerpt"><?php echo esc_html( $excerpt ); ?></p>
				<?php endif; ?>

				<!-- Dietary badges (only verified) -->
				<?php if ( ! empty( $dietary_claims ) ) : ?>
					<div class="sc-single-recipe__dietary-strip" aria-label="<?php esc_attr_e( 'Dietary Verification', 'spicecraft' ); ?>">
						<?php foreach ( $dietary_claims as $claim ) : ?>
							<span class="sc-dietary-badge" data-claim="<?php echo esc_attr( $claim ); ?>">
								✓ <?php echo esc_html( spicecraft_get_dietary_label( $claim ) ); ?>
							</span>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<!-- Recipe Action Toolbar (Share & Print) -->
				<div class="sc-recipe-toolbar sc-no-print">
					<button type="button" class="sc-btn sc-btn--secondary sc-btn--sm sc-share-btn" data-title="<?php echo esc_attr( get_the_title() ); ?>" data-url="<?php echo esc_url( get_permalink() ); ?>" aria-label="<?php esc_attr_e( 'Share Recipe', 'spicecraft' ); ?>">
						<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
						<span><?php esc_html_e( 'Share', 'spicecraft' ); ?></span>
					</button>

					<?php
					$wa_share_text = rawurlencode( sprintf( __( 'Check out this authentic %s recipe on SpiceCraft: %s', 'spicecraft' ), get_the_title(), get_permalink() ) );
					$wa_share_url  = 'https://api.whatsapp.com/send?text=' . $wa_share_text;
					?>
					<a href="<?php echo esc_url( $wa_share_url ); ?>" class="sc-btn sc-btn--secondary sc-btn--sm sc-whatsapp-share-btn" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Share on WhatsApp', 'spicecraft' ); ?>">
						<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
						<span><?php esc_html_e( 'WhatsApp', 'spicecraft' ); ?></span>
					</a>

					<button type="button" class="sc-btn sc-btn--secondary sc-btn--sm sc-print-btn" onclick="window.print();" aria-label="<?php esc_attr_e( 'Print Recipe', 'spicecraft' ); ?>">
						<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
						<span><?php esc_html_e( 'Print', 'spicecraft' ); ?></span>
					</button>

					<div class="sc-share-toast" id="sc-share-toast" role="status" aria-live="polite">
						<?php esc_html_e( 'Link copied to clipboard!', 'spicecraft' ); ?>
					</div>
				</div>
			</div>
		</header>

		<!-- 3. Hero Photography / Video Media -->
		<div class="sc-container sc-single-recipe__media-container">
			<?php if ( ! empty( $hero_img_id ) ) : ?>
				<div class="sc-single-recipe__hero-media">
					<?php echo wp_get_attachment_image( $hero_img_id, 'full', false, array(
						'class'         => 'sc-single-recipe__hero-img',
						'alt'           => get_the_title(),
						'fetchpriority' => 'high',
						'loading'       => 'eager',
					) ); ?>
				</div>
			<?php endif; ?>

			<!-- 4. Quick Facts Bar (Only non-zero values) -->
			<?php if ( ! empty( $prep_time_str ) || ! empty( $cook_time_str ) || ! empty( $total_time_str ) || ! empty( $yield_str ) || ! empty( $diff_label ) ) : ?>
				<div class="sc-quick-facts-bar" aria-label="<?php esc_attr_e( 'Recipe Metrics', 'spicecraft' ); ?>">
					<?php if ( ! empty( $prep_time_str ) ) : ?>
						<div class="sc-quick-fact-item">
							<span class="sc-quick-fact-label"><?php esc_html_e( 'Prep Time', 'spicecraft' ); ?></span>
							<strong class="sc-quick-fact-val"><?php echo esc_html( $prep_time_str ); ?></strong>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $cook_time_str ) ) : ?>
						<div class="sc-quick-fact-item">
							<span class="sc-quick-fact-label"><?php esc_html_e( 'Cook Time', 'spicecraft' ); ?></span>
							<strong class="sc-quick-fact-val"><?php echo esc_html( $cook_time_str ); ?></strong>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $total_time_str ) ) : ?>
						<div class="sc-quick-fact-item sc-quick-fact-item--highlight">
							<span class="sc-quick-fact-label"><?php esc_html_e( 'Total Time', 'spicecraft' ); ?></span>
							<strong class="sc-quick-fact-val"><?php echo esc_html( $total_time_str ); ?></strong>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $yield_str ) ) : ?>
						<div class="sc-quick-fact-item">
							<span class="sc-quick-fact-label"><?php esc_html_e( 'Servings', 'spicecraft' ); ?></span>
							<strong class="sc-quick-fact-val"><?php echo esc_html( $yield_str ); ?></strong>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $diff_label ) ) : ?>
						<div class="sc-quick-fact-item">
							<span class="sc-quick-fact-label"><?php esc_html_e( 'Difficulty', 'spicecraft' ); ?></span>
							<strong class="sc-quick-fact-val sc-diff-val" data-difficulty="<?php echo esc_attr( $difficulty ); ?>"><?php echo esc_html( $diff_label ); ?></strong>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>

		<!-- 5. Main Content Columns: Story, Ingredients & Instructions -->
		<div class="sc-container sc-single-recipe__layout">
			<div class="sc-single-recipe__main-col">

				<!-- Narrative / Story -->
				<?php if ( get_the_content() ) : ?>
					<section class="sc-recipe-story sc-prose" aria-labelledby="recipe-story-heading">
						<h2 id="recipe-story-heading" class="screen-reader-text"><?php esc_html_e( 'About This Recipe', 'spicecraft' ); ?></h2>
						<?php the_content(); ?>
					</section>
				<?php endif; ?>

				<!-- Structured Repeatable Ingredients -->
				<?php if ( ! empty( $meta['ingredient_groups'] ) ) : ?>
					<section class="sc-recipe-ingredients" aria-labelledby="ingredients-heading">
						<div class="sc-section-subhead">
							<h2 id="ingredients-heading" class="sc-recipe-section-title">
								<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
								<span><?php esc_html_e( 'Ingredients', 'spicecraft' ); ?></span>
							</h2>
							<span class="sc-ingredients-tip sc-no-print"><?php esc_html_e( 'Click checkboxes to track ingredients while cooking', 'spicecraft' ); ?></span>
						</div>

						<div class="sc-ingredients-groups-list">
							<?php foreach ( $meta['ingredient_groups'] as $g_index => $group ) :
								$group_heading = ! empty( $group['group_name'] ) ? $group['group_name'] : '';
								$items         = ! empty( $group['items'] ) && is_array( $group['items'] ) ? $group['items'] : array();
								if ( empty( $items ) ) continue;
								?>
								<div class="sc-ingredient-group-card">
									<?php if ( ! empty( $group_heading ) ) : ?>
										<h3 class="sc-ingredient-group-title"><?php echo esc_html( $group_heading ); ?></h3>
									<?php endif; ?>

									<ul class="sc-ingredient-checklist">
										<?php foreach ( $items as $i_index => $item ) :
											$qty       = $item['quantity'] ?? '';
											$unit      = $item['unit'] ?? '';
											$name      = $item['ingredient'] ?? '';
											$note      = $item['note'] ?? '';
											$prod_id   = absint( $item['product_id'] ?? 0 );
											$item_id   = 'ing_' . $g_index . '_' . $i_index;
											?>
											<li class="sc-ingredient-row">
												<label for="<?php echo esc_attr( $item_id ); ?>" class="sc-ingredient-label">
													<input type="checkbox" id="<?php echo esc_attr( $item_id ); ?>" class="sc-ingredient-checkbox sc-no-print" />
													<span class="sc-ingredient-text">
														<?php if ( ! empty( $qty ) || ! empty( $unit ) ) : ?>
															<strong class="sc-ingredient-amount"><?php echo esc_html( trim( $qty . ' ' . $unit ) ); ?></strong>
														<?php endif; ?>
														<span class="sc-ingredient-name"><?php echo esc_html( $name ); ?></span>
														<?php if ( ! empty( $note ) ) : ?>
															<span class="sc-ingredient-note">(<?php echo esc_html( $note ); ?>)</span>
														<?php endif; ?>
													</span>
												</label>

												<!-- Optional Subtle WooCommerce Product Link -->
												<?php if ( $prod_id > 0 && 'publish' === get_post_status( $prod_id ) ) : ?>
													<a href="<?php echo esc_url( get_permalink( $prod_id ) ); ?>" class="sc-ingredient-product-link sc-no-print" target="_blank" rel="noopener noreferrer" title="<?php printf( esc_attr__( 'View SpiceCraft %s', 'spicecraft' ), esc_attr( get_the_title( $prod_id ) ) ); ?>">
														<span><?php esc_html_e( 'View Product', 'spicecraft' ); ?></span>
														<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
													</a>
												<?php endif; ?>
											</li>
										<?php endforeach; ?>
									</ul>
								</div>
							<?php endforeach; ?>
						</div>
					</section>
				<?php endif; ?>

				<!-- Structured Repeatable Instructions -->
				<?php if ( ! empty( $meta['instruction_steps'] ) ) : ?>
					<section class="sc-recipe-instructions" aria-labelledby="instructions-heading">
						<h2 id="instructions-heading" class="sc-recipe-section-title">
							<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
							<span><?php esc_html_e( 'Step-by-Step Instructions', 'spicecraft' ); ?></span>
						</h2>

						<ol class="sc-instructions-list">
							<?php foreach ( $meta['instruction_steps'] as $s_idx => $step ) :
								$num     = $s_idx + 1;
								$heading = $step['heading'] ?? '';
								$inst    = $step['instruction'] ?? '';
								$img_id  = absint( $step['image_id'] ?? 0 );
								$tip     = $step['tip'] ?? '';
								?>
								<li class="sc-instruction-step" id="step-<?php echo esc_attr( $num ); ?>">
									<div class="sc-step-number" aria-hidden="true"><?php printf( '%02d', $num ); ?></div>

									<div class="sc-step-content">
										<?php if ( ! empty( $heading ) ) : ?>
											<h3 class="sc-step-title"><?php echo esc_html( $heading ); ?></h3>
										<?php endif; ?>

										<div class="sc-step-instruction-prose">
											<?php echo wpautop( esc_html( $inst ) ); ?>
										</div>

										<?php if ( $img_id ) : ?>
											<div class="sc-step-image-wrap">
												<?php echo wp_get_attachment_image( $img_id, 'large', false, array(
													'class'   => 'sc-step-img',
													'alt'     => ! empty( $heading ) ? esc_attr( $heading ) : sprintf( esc_attr__( 'Step %d Preparation', 'spicecraft' ), $num ),
													'loading' => 'lazy',
												) ); ?>
											</div>
										<?php endif; ?>

										<?php if ( ! empty( $tip ) ) : ?>
											<div class="sc-step-tip-box">
												<strong><?php esc_html_e( 'Master Blender Tip:', 'spicecraft' ); ?></strong>
												<span><?php echo esc_html( $tip ); ?></span>
											</div>
										<?php endif; ?>
									</div>
								</li>
							<?php endforeach; ?>
						</ol>
					</section>
				<?php endif; ?>

				<!-- Culinary Notes (Chef, Serving, Storage, Substitutions) -->
				<?php
				$has_notes = ! empty( $meta['notes_chef'] ) || ! empty( $meta['notes_serving'] ) || ! empty( $meta['notes_storage'] ) || ! empty( $meta['notes_subs'] );
				if ( $has_notes ) :
				?>
					<section class="sc-recipe-notes" aria-labelledby="recipe-notes-heading">
						<h2 id="recipe-notes-heading" class="sc-recipe-section-title">
							<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
							<span><?php esc_html_e( 'Chef’s Notes & Guidance', 'spicecraft' ); ?></span>
						</h2>

						<div class="sc-recipe-notes-grid">
							<?php if ( ! empty( $meta['notes_chef'] ) ) : ?>
								<div class="sc-recipe-note-card">
									<h3 class="sc-recipe-note-title"><?php esc_html_e( 'Technique & Flavor Notes', 'spicecraft' ); ?></h3>
									<p><?php echo nl2br( esc_html( $meta['notes_chef'] ) ); ?></p>
								</div>
							<?php endif; ?>

							<?php if ( ! empty( $meta['notes_serving'] ) ) : ?>
								<div class="sc-recipe-note-card">
									<h3 class="sc-recipe-note-title"><?php esc_html_e( 'Serving Suggestions & Accompaniments', 'spicecraft' ); ?></h3>
									<p><?php echo nl2br( esc_html( $meta['notes_serving'] ) ); ?></p>
								</div>
							<?php endif; ?>

							<?php if ( ! empty( $meta['notes_storage'] ) ) : ?>
								<div class="sc-recipe-note-card">
									<h3 class="sc-recipe-note-title"><?php esc_html_e( 'Storage & Shelf Life', 'spicecraft' ); ?></h3>
									<p><?php echo nl2br( esc_html( $meta['notes_storage'] ) ); ?></p>
								</div>
							<?php endif; ?>

							<?php if ( ! empty( $meta['notes_subs'] ) ) : ?>
								<div class="sc-recipe-note-card">
									<h3 class="sc-recipe-note-title"><?php esc_html_e( 'Ingredient Substitutions', 'spicecraft' ); ?></h3>
									<p><?php echo nl2br( esc_html( $meta['notes_subs'] ) ); ?></p>
								</div>
							<?php endif; ?>
						</div>
					</section>
				<?php endif; ?>

				<!-- Optional Nutrition Facts Section (Cleanly hidden if empty) -->
				<?php if ( ! empty( $meta['nutrition'] ) && ! empty( $meta['nutrition']['calories'] ) ) :
					$nut = $meta['nutrition'];
					?>
					<section class="sc-recipe-nutrition-section" aria-labelledby="nutrition-heading">
						<h2 id="nutrition-heading" class="sc-recipe-section-title">
							<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
							<span><?php esc_html_e( 'Nutrition Information', 'spicecraft' ); ?></span>
						</h2>

						<?php if ( ! empty( $nut['serving_size'] ) ) : ?>
							<p class="sc-nutrition-serving-size">
								<strong><?php esc_html_e( 'Serving Size:', 'spicecraft' ); ?></strong> <?php echo esc_html( $nut['serving_size'] ); ?>
							</p>
						<?php endif; ?>

						<div class="sc-nutrition-table-wrap">
							<table class="sc-nutrition-facts-table">
								<tbody>
									<?php if ( ! empty( $nut['calories'] ) ) : ?>
										<tr><th scope="row"><?php esc_html_e( 'Calories', 'spicecraft' ); ?></th><td><?php echo esc_html( $nut['calories'] ); ?> kcal</td></tr>
									<?php endif; ?>
									<?php if ( ! empty( $nut['protein'] ) ) : ?>
										<tr><th scope="row"><?php esc_html_e( 'Protein', 'spicecraft' ); ?></th><td><?php echo esc_html( $nut['protein'] ); ?></td></tr>
									<?php endif; ?>
									<?php if ( ! empty( $nut['carbs'] ) ) : ?>
										<tr><th scope="row"><?php esc_html_e( 'Carbohydrates', 'spicecraft' ); ?></th><td><?php echo esc_html( $nut['carbs'] ); ?></td></tr>
									<?php endif; ?>
									<?php if ( ! empty( $nut['fat'] ) ) : ?>
										<tr><th scope="row"><?php esc_html_e( 'Total Fat', 'spicecraft' ); ?></th><td><?php echo esc_html( $nut['fat'] ); ?></td></tr>
									<?php endif; ?>
									<?php if ( ! empty( $nut['sat_fat'] ) ) : ?>
										<tr><th scope="row"><?php esc_html_e( 'Saturated Fat', 'spicecraft' ); ?></th><td><?php echo esc_html( $nut['sat_fat'] ); ?></td></tr>
									<?php endif; ?>
									<?php if ( ! empty( $nut['fiber'] ) ) : ?>
										<tr><th scope="row"><?php esc_html_e( 'Dietary Fiber', 'spicecraft' ); ?></th><td><?php echo esc_html( $nut['fiber'] ); ?></td></tr>
									<?php endif; ?>
									<?php if ( ! empty( $nut['sugar'] ) ) : ?>
										<tr><th scope="row"><?php esc_html_e( 'Sugars', 'spicecraft' ); ?></th><td><?php echo esc_html( $nut['sugar'] ); ?></td></tr>
									<?php endif; ?>
									<?php if ( ! empty( $nut['sodium'] ) ) : ?>
										<tr><th scope="row"><?php esc_html_e( 'Sodium', 'spicecraft' ); ?></th><td><?php echo esc_html( $nut['sodium'] ); ?></td></tr>
									<?php endif; ?>
								</tbody>
							</table>
						</div>
					</section>
				<?php endif; ?>

				<!-- Optional Photo Gallery -->
				<?php if ( ! empty( $meta['gallery_ids'] ) ) : ?>
					<section class="sc-recipe-gallery-section" aria-labelledby="gallery-heading">
						<h2 id="gallery-heading" class="sc-recipe-section-title">
							<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
							<span><?php esc_html_e( 'Culinary Gallery', 'spicecraft' ); ?></span>
						</h2>

						<div class="sc-recipe-gallery-grid">
							<?php foreach ( $meta['gallery_ids'] as $gal_id ) : ?>
								<div class="sc-recipe-gallery-item">
									<?php echo wp_get_attachment_image( $gal_id, 'medium_large', false, array(
										'class'   => 'sc-recipe-gallery-img',
										'alt'     => get_the_title(),
										'loading' => 'lazy',
									) ); ?>
								</div>
							<?php endforeach; ?>
						</div>
					</section>
				<?php endif; ?>

				<!-- Optional Recipe Video Player -->
				<?php if ( ! empty( $meta['video_url'] ) ) : ?>
					<section class="sc-recipe-video-section sc-no-print" aria-labelledby="video-heading">
						<h2 id="video-heading" class="sc-recipe-section-title">
							<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
							<span><?php esc_html_e( 'Watch Recipe Video', 'spicecraft' ); ?></span>
						</h2>

						<div class="sc-video-embed-container">
							<?php
							echo wp_oembed_get( $meta['video_url'], array( 'width' => 960 ) );
							?>
						</div>
					</section>
				<?php endif; ?>

			</div><!-- .sc-single-recipe__main-col -->
		</div><!-- .sc-single-recipe__layout -->

		<!-- 6. SPICES USED IN THIS RECIPE (WooCommerce Product Integration) -->
		<?php if ( ! empty( $linked_product_ids ) ) : ?>
			<section class="sc-recipe-linked-products-section sc-no-print" aria-labelledby="spices-used-heading">
				<div class="sc-container">
					<header class="sc-section-header sc-section-header--center">
						<span class="sc-eyebrow"><?php esc_html_e( 'The Secret to the Flavor', 'spicecraft' ); ?></span>
						<h2 id="spices-used-heading" class="sc-section-title"><?php esc_html_e( 'Spices Used in This Recipe', 'spicecraft' ); ?></h2>
						<p class="sc-section-subtitle"><?php esc_html_e( 'Formulated with our pure, single-origin and artisanal masala blends. Available for commercial export and retail wholesale.', 'spicecraft' ); ?></p>
					</header>

					<div class="woocommerce columns-4">
						<ul class="products columns-4 sc-products-grid">
							<?php
							foreach ( $linked_product_ids as $prod_id ) :
								$post_object = get_post( $prod_id );
								if ( ! $post_object ) continue;
								setup_postdata( $GLOBALS['post'] =& $post_object );
								wc_get_template_part( 'content', 'product' );
							endforeach;
							wp_reset_postdata();
							?>
						</ul>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<!-- 7. RELATED RECIPES SECTION -->
		<?php if ( ! empty( $related_recipes ) ) : ?>
			<section class="sc-recipe-related-section sc-no-print" aria-labelledby="related-recipes-heading">
				<div class="sc-container">
					<header class="sc-section-header sc-section-header--center">
						<span class="sc-eyebrow"><?php esc_html_e( 'More Culinary Pairings', 'spicecraft' ); ?></span>
						<h2 id="related-recipes-heading" class="sc-section-title"><?php esc_html_e( 'Related Recipes You Might Enjoy', 'spicecraft' ); ?></h2>
					</header>

					<div class="sc-grid sc-grid--3 sc-recipes-grid">
						<?php
						foreach ( $related_recipes as $rel_post ) :
							spicecraft_render_recipe_card( $rel_post );
						endforeach;
						?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<!-- 8. Back to Recipes Archive Link -->
		<div class="sc-recipe-back-bar sc-no-print">
			<div class="sc-container" style="text-align: center;">
				<a href="<?php echo esc_url( get_post_type_archive_link( 'spicecraft_recipe' ) ); ?>" class="sc-btn sc-btn--secondary sc-btn--md">
					<span>&larr; <?php esc_html_e( 'Back to All Recipes', 'spicecraft' ); ?></span>
				</a>
			</div>
		</div>

	</article><!-- #recipe-<?php the_ID(); ?> -->

<?php
endwhile;

get_footer();
