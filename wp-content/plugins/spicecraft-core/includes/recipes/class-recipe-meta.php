<?php
/**
 * SpiceCraft Core - Recipe Metadata Architecture & Admin Metaboxes
 *
 * Implements structured, tabbed admin metaboxes for recipes:
 * - Time, Yield & Difficulty
 * - Dietary Classifications
 * - Repeatable Ingredient Groups with WooCommerce Product linkages
 * - Repeatable Instruction Steps with media picker & tips
 * - Culinary Notes (Chef, Serving, Storage, Substitutions)
 * - Optional Nutrition Facts
 * - Product Relationships (Featured Products, Product Categories)
 * - Media & Video Overrides
 *
 * Enforces strict capability validation, nonce verification, autosave guards,
 * and deep sanitization.
 *
 * @package SpiceCraft_Core
 * @since 1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SpiceCraft_Recipe_Meta {

	/**
	 * Nonce action and name.
	 */
	const NONCE_ACTION = 'spicecraft_recipe_meta_action';
	const NONCE_NAME   = 'spicecraft_recipe_meta_nonce';

	/**
	 * Post Type.
	 */
	const POST_TYPE = 'spicecraft_recipe';

	/**
	 * Singleton Instance.
	 *
	 * @var SpiceCraft_Recipe_Meta|null
	 */
	private static $instance = null;

	/**
	 * Get Singleton Instance.
	 *
	 * @return SpiceCraft_Recipe_Meta
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ) );
		add_action( 'save_post_' . self::POST_TYPE, array( $this, 'save_meta' ), 10, 2 );
		add_action( 'admin_notices', array( $this, 'render_completeness_notice' ) );
	}

	/**
	 * Register Metaboxes.
	 */
	public function register_meta_boxes() {
		add_meta_box(
			'spicecraft_recipe_details',
			__( 'Recipe Details & Culinary Data', 'spicecraft-core' ),
			array( $this, 'render_metabox' ),
			self::POST_TYPE,
			'normal',
			'high'
		);
	}

	/**
	 * Render Tabbed Recipe Metabox.
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function render_metabox( $post ) {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

		$meta = function_exists( 'spicecraft_get_recipe_meta' )
			? spicecraft_get_recipe_meta( $post->ID )
			: array();

		// Fetch published WooCommerce products for dropdowns
		$woo_products = get_posts( array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 100,
			'orderby'        => 'title',
			'order'          => 'ASC',
		) );

		// Fetch product categories
		$product_cats = get_terms( array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
		) );
		?>
		<div class="sc-metabox-wrapper">
			<!-- Tab Navigation -->
			<div class="sc-metabox-tabs" role="tablist">
				<button type="button" class="sc-metabox-tab-btn is-active" data-tab="sc-tab-timing">
					<span class="dashicons dashicons-clock"></span> <?php esc_html_e( 'Time & Yield', 'spicecraft-core' ); ?>
				</button>
				<button type="button" class="sc-metabox-tab-btn" data-tab="sc-tab-ingredients">
					<span class="dashicons dashicons-list-view"></span> <?php esc_html_e( 'Ingredients', 'spicecraft-core' ); ?>
				</button>
				<button type="button" class="sc-metabox-tab-btn" data-tab="sc-tab-instructions">
					<span class="dashicons dashicons-editor-ol"></span> <?php esc_html_e( 'Instructions', 'spicecraft-core' ); ?>
				</button>
				<button type="button" class="sc-metabox-tab-btn" data-tab="sc-tab-notes">
					<span class="dashicons dashicons-testimonial"></span> <?php esc_html_e( 'Culinary Notes', 'spicecraft-core' ); ?>
				</button>
				<button type="button" class="sc-metabox-tab-btn" data-tab="sc-tab-nutrition">
					<span class="dashicons dashicons-heart"></span> <?php esc_html_e( 'Nutrition', 'spicecraft-core' ); ?>
				</button>
				<button type="button" class="sc-metabox-tab-btn" data-tab="sc-tab-products">
					<span class="dashicons dashicons-cart"></span> <?php esc_html_e( 'Spice Products', 'spicecraft-core' ); ?>
				</button>
				<button type="button" class="sc-metabox-tab-btn" data-tab="sc-tab-media">
					<span class="dashicons dashicons-camera"></span> <?php esc_html_e( 'Media & Video', 'spicecraft-core' ); ?>
				</button>
			</div>

			<!-- TAB 1: Time, Yield, Difficulty & Dietary -->
			<div class="sc-metabox-panel" id="sc-tab-timing" style="display: block;">
				<h3 style="margin-top: 0;"><?php esc_html_e( 'Preparation Time, Servings & Dietary Classification', 'spicecraft-core' ); ?></h3>
				<p class="description"><?php esc_html_e( 'Enter numeric minutes for accurate time formatting (e.g. 65 mins -> "1 hr 5 mins") and SEO Schema duration tags. Zero-value times are cleanly suppressed.', 'spicecraft-core' ); ?></p>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="sc_prep_min"><?php esc_html_e( 'Preparation Time (mins)', 'spicecraft-core' ); ?></label></th>
						<td>
							<input type="number" id="sc_prep_min" name="_spicecraft_recipe_prep_minutes" value="<?php echo esc_attr( $meta['prep_minutes'] ?: '' ); ?>" class="small-text" min="0" step="1" placeholder="15" />
							<span class="description"><?php esc_html_e( 'Minutes needed for chopping, marinating, measuring.', 'spicecraft-core' ); ?></span>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sc_cook_min"><?php esc_html_e( 'Cooking Time (mins)', 'spicecraft-core' ); ?></label></th>
						<td>
							<input type="number" id="sc_cook_min" name="_spicecraft_recipe_cook_minutes" value="<?php echo esc_attr( $meta['cook_minutes'] ?: '' ); ?>" class="small-text" min="0" step="1" placeholder="30" />
							<span class="description"><?php esc_html_e( 'Active stove, oven, or simmering time.', 'spicecraft-core' ); ?></span>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sc_add_min"><?php esc_html_e( 'Additional Time (mins, optional)', 'spicecraft-core' ); ?></label></th>
						<td>
							<input type="number" id="sc_add_min" name="_spicecraft_recipe_additional_minutes" value="<?php echo esc_attr( $meta['additional_minutes'] ?: '' ); ?>" class="small-text" min="0" step="1" placeholder="0" />
							<span class="description"><?php esc_html_e( 'Resting, cooling, or overnight marination time.', 'spicecraft-core' ); ?></span>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sc_total_min"><?php esc_html_e( 'Total Time Override (mins)', 'spicecraft-core' ); ?></label></th>
						<td>
							<input type="number" id="sc_total_min" name="_spicecraft_recipe_total_minutes" value="<?php echo esc_attr( $meta['total_minutes'] ?: '' ); ?>" class="small-text" min="0" step="1" placeholder="Auto" />
							<span class="description"><?php esc_html_e( 'Leave blank to automatically calculate Prep + Cook + Additional time.', 'spicecraft-core' ); ?></span>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sc_yield"><?php esc_html_e( 'Servings / Yield', 'spicecraft-core' ); ?></label></th>
						<td>
							<input type="text" id="sc_yield" name="_spicecraft_recipe_yield" value="<?php echo esc_attr( $meta['yield'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( '4 servings (or: Makes 1 jar / 250ml)', 'spicecraft-core' ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sc_diff"><?php esc_html_e( 'Difficulty Level', 'spicecraft-core' ); ?></label></th>
						<td>
							<select id="sc_diff" name="_spicecraft_recipe_difficulty">
								<option value=""><?php esc_html_e( '— Not Specified —', 'spicecraft-core' ); ?></option>
								<option value="easy" <?php selected( $meta['difficulty'], 'easy' ); ?>><?php esc_html_e( 'Easy (Beginner Friendly)', 'spicecraft-core' ); ?></option>
								<option value="medium" <?php selected( $meta['difficulty'], 'medium' ); ?>><?php esc_html_e( 'Medium (Intermediate Techniques)', 'spicecraft-core' ); ?></option>
								<option value="advanced" <?php selected( $meta['difficulty'], 'advanced' ); ?>><?php esc_html_e( 'Advanced (Artisanal / Multi-stage)', 'spicecraft-core' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Dietary Classifications', 'spicecraft-core' ); ?></th>
						<td>
							<div class="notice notice-warning inline" style="margin: 0 0 10px 0; padding: 6px 12px;">
								<p style="margin: 0; font-size: 12px;"><?php esc_html_e( 'Administrator Rule: Dietary claims must only be checked after verifying the complete recipe, marinades, and all spice ingredients. Never infer dietary tags automatically.', 'spicecraft-core' ); ?></p>
							</div>
							<?php
							$dietary_options = array(
								'vegetarian'  => __( 'Vegetarian', 'spicecraft-core' ),
								'vegan'       => __( 'Vegan (100% Plant Based)', 'spicecraft-core' ),
								'gluten_free' => __( 'Gluten-Free', 'spicecraft-core' ),
								'dairy_free'  => __( 'Dairy-Free', 'spicecraft-core' ),
								'jain'        => __( 'Jain Friendly (No Root Vegetables / Garlic / Onion)', 'spicecraft-core' ),
								'nut_free'    => __( 'Nut-Free', 'spicecraft-core' ),
							);
							foreach ( $dietary_options as $d_key => $d_label ) :
								$checked = in_array( $d_key, $meta['dietary'], true );
								?>
								<label style="display: inline-block; margin-right: 18px; margin-bottom: 6px;">
									<input type="checkbox" name="_spicecraft_recipe_dietary[]" value="<?php echo esc_attr( $d_key ); ?>" <?php checked( $checked ); ?> />
									<?php echo esc_html( $d_label ); ?>
								</label>
							<?php endforeach; ?>
						</td>
					</tr>
				</table>
			</div>

			<!-- TAB 2: Structured Repeatable Ingredients -->
			<div class="sc-metabox-panel" id="sc-tab-ingredients" style="display: none;">
				<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
					<div>
						<h3 style="margin: 0;"><?php esc_html_e( 'Structured Ingredient Groups', 'spicecraft-core' ); ?></h3>
						<p class="description" style="margin-top: 4px;"><?php esc_html_e( 'Organize ingredients into logical culinary groups (e.g. "For the Marinade", "For the Tadka / Tempering"). Optionally link an ingredient to an authentic SpiceCraft WooCommerce product.', 'spicecraft-core' ); ?></p>
					</div>
					<button type="button" class="button button-primary" id="sc-add-ingredient-group-btn">
						<span class="dashicons dashicons-plus-alt2" style="margin-top: -2px;"></span> <?php esc_html_e( 'Add Ingredient Group', 'spicecraft-core' ); ?>
					</button>
				</div>

				<div id="sc-ingredient-groups-container">
					<?php
					$groups = ! empty( $meta['ingredient_groups'] ) ? $meta['ingredient_groups'] : array();
					// If brand new, provide 1 default empty group structure
					if ( empty( $groups ) ) {
						$groups = array(
							array(
								'group_name' => __( 'Main Ingredients', 'spicecraft-core' ),
								'items'      => array(
									array(
										'quantity'   => '',
										'unit'       => '',
										'ingredient' => '',
										'note'       => '',
										'product_id' => 0,
									),
								),
							),
						);
					}

					foreach ( $groups as $g_idx => $group ) :
						$g_name = ! empty( $group['group_name'] ) ? $group['group_name'] : '';
						$items  = ! empty( $group['items'] ) && is_array( $group['items'] ) ? $group['items'] : array();
						?>
						<div class="sc-ingredient-group-box" data-group-index="<?php echo esc_attr( $g_idx ); ?>" style="border: 1px solid #c3c4c7; background: #fafafa; border-radius: 6px; padding: 14px; margin-bottom: 16px;">
							<div class="sc-group-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid #e2e4e7;">
								<div style="display: flex; align-items: center; gap: 8px; flex-grow: 1; max-width: 450px;">
									<span class="dashicons dashicons-menu sc-drag-handle" style="color: #8c8f94; cursor: grab;"></span>
									<label class="screen-reader-text"><?php esc_html_e( 'Group Heading', 'spicecraft-core' ); ?></label>
									<input type="text" name="_spicecraft_recipe_ingredient_groups[<?php echo esc_attr( $g_idx ); ?>][group_name]" value="<?php echo esc_attr( $g_name ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'Group Name (e.g. For the Marinade)', 'spicecraft-core' ); ?>" style="font-weight: 600;" />
								</div>
								<button type="button" class="button sc-remove-group-btn" style="color: #b32d2e;">
									<span class="dashicons dashicons-trash" style="margin-top: -2px;"></span> <?php esc_html_e( 'Remove Group', 'spicecraft-core' ); ?>
								</button>
							</div>

							<table class="wp-list-table widefat striped sc-group-items-table" style="background: #fff; margin-bottom: 8px;">
								<thead>
									<tr>
										<th style="width: 100px;"><?php esc_html_e( 'Qty', 'spicecraft-core' ); ?></th>
										<th style="width: 90px;"><?php esc_html_e( 'Unit', 'spicecraft-core' ); ?></th>
										<th><?php esc_html_e( 'Ingredient Name', 'spicecraft-core' ); ?></th>
										<th><?php esc_html_e( 'Preparation / Note', 'spicecraft-core' ); ?></th>
										<th style="width: 260px;"><?php esc_html_e( 'Linked Spice Product (Optional)', 'spicecraft-core' ); ?></th>
										<th style="width: 40px; text-align: center;"></th>
									</tr>
								</thead>
								<tbody>
									<?php
									foreach ( $items as $i_idx => $item ) :
										$qty      = $item['quantity'] ?? '';
										$unit     = $item['unit'] ?? '';
										$ing_name = $item['ingredient'] ?? '';
										$note     = $item['note'] ?? '';
										$prod_id  = absint( $item['product_id'] ?? 0 );
										?>
										<tr>
											<td>
												<input type="text" name="_spicecraft_recipe_ingredient_groups[<?php echo esc_attr( $g_idx ); ?>][items][<?php echo esc_attr( $i_idx ); ?>][quantity]" value="<?php echo esc_attr( $qty ); ?>" class="widefat" placeholder="1, 1/2, 2" />
											</td>
											<td>
												<input type="text" name="_spicecraft_recipe_ingredient_groups[<?php echo esc_attr( $g_idx ); ?>][items][<?php echo esc_attr( $i_idx ); ?>][unit]" value="<?php echo esc_attr( $unit ); ?>" class="widefat" placeholder="tsp, g" />
											</td>
											<td>
												<input type="text" name="_spicecraft_recipe_ingredient_groups[<?php echo esc_attr( $g_idx ); ?>][items][<?php echo esc_attr( $i_idx ); ?>][ingredient]" value="<?php echo esc_attr( $ing_name ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'e.g. Kashmiri Chilli Powder', 'spicecraft-core' ); ?>" />
											</td>
											<td>
												<input type="text" name="_spicecraft_recipe_ingredient_groups[<?php echo esc_attr( $g_idx ); ?>][items][<?php echo esc_attr( $i_idx ); ?>][note]" value="<?php echo esc_attr( $note ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'e.g. Special Grade, sieved', 'spicecraft-core' ); ?>" />
											</td>
											<td>
												<select name="_spicecraft_recipe_ingredient_groups[<?php echo esc_attr( $g_idx ); ?>][items][<?php echo esc_attr( $i_idx ); ?>][product_id]" class="widefat">
													<option value="0"><?php esc_html_e( '— No Linked Product —', 'spicecraft-core' ); ?></option>
													<?php foreach ( $woo_products as $wp ) : ?>
														<option value="<?php echo esc_attr( $wp->ID ); ?>" <?php selected( $prod_id, $wp->ID ); ?>>
															<?php echo esc_html( $wp->post_title ); ?>
														</option>
													<?php endforeach; ?>
												</select>
											</td>
											<td style="text-align: center;">
												<button type="button" class="button sc-remove-row-btn" title="<?php esc_attr_e( 'Remove Row', 'spicecraft-core' ); ?>">&times;</button>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>

							<button type="button" class="button sc-add-ingredient-item-btn" data-group-index="<?php echo esc_attr( $g_idx ); ?>">
								<span class="dashicons dashicons-plus-alt" style="margin-top: -2px;"></span> <?php esc_html_e( 'Add Ingredient Row', 'spicecraft-core' ); ?>
							</button>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- TAB 3: Structured Numbered Instructions -->
			<div class="sc-metabox-panel" id="sc-tab-instructions" style="display: none;">
				<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
					<div>
						<h3 style="margin: 0;"><?php esc_html_e( 'Structured Instruction Steps', 'spicecraft-core' ); ?></h3>
						<p class="description" style="margin-top: 4px;"><?php esc_html_e( 'Create distinct numbered culinary steps (01, 02...). Add optional step headings, preparation photos, and master blender tips.', 'spicecraft-core' ); ?></p>
					</div>
					<button type="button" class="button button-primary" id="sc-add-instruction-step-btn">
						<span class="dashicons dashicons-plus-alt2" style="margin-top: -2px;"></span> <?php esc_html_e( 'Add Step', 'spicecraft-core' ); ?>
					</button>
				</div>

				<div id="sc-instruction-steps-container">
					<?php
					$steps = ! empty( $meta['instruction_steps'] ) ? $meta['instruction_steps'] : array();
					if ( empty( $steps ) ) {
						$steps = array(
							array(
								'step_number' => 1,
								'heading'     => '',
								'instruction' => '',
								'image_id'    => 0,
								'tip'         => '',
							),
						);
					}

					foreach ( $steps as $s_idx => $step ) :
						$s_num     = $s_idx + 1;
						$s_head    = $step['heading'] ?? '';
						$s_inst    = $step['instruction'] ?? '';
						$s_img_id  = absint( $step['image_id'] ?? 0 );
						$s_tip     = $step['tip'] ?? '';
						$img_url   = $s_img_id ? wp_get_attachment_image_url( $s_img_id, 'medium' ) : '';
						?>
						<div class="sc-instruction-step-box" data-step-index="<?php echo esc_attr( $s_idx ); ?>" style="border: 1px solid #c3c4c7; background: #fff; border-radius: 6px; padding: 16px; margin-bottom: 16px;">
							<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid #f0f0f1;">
								<div style="display: flex; align-items: center; gap: 8px;">
									<span class="dashicons dashicons-menu sc-drag-handle" style="color: #8c8f94; cursor: grab;"></span>
									<span class="sc-step-badge" style="background: #2b7a78; color: #fff; font-weight: 700; padding: 2px 10px; border-radius: 12px; font-size: 13px;">
										<?php printf( esc_html__( 'Step %02d', 'spicecraft-core' ), $s_num ); ?>
									</span>
								</div>
								<button type="button" class="button sc-remove-step-btn" style="color: #b32d2e;">
									<span class="dashicons dashicons-trash" style="margin-top: -2px;"></span> <?php esc_html_e( 'Remove Step', 'spicecraft-core' ); ?>
								</button>
							</div>

							<div style="display: grid; grid-template-columns: 1fr 200px; gap: 16px;">
								<div>
									<div style="margin-bottom: 10px;">
										<label style="font-weight: 600; display: block; margin-bottom: 4px;"><?php esc_html_e( 'Step Heading (Optional)', 'spicecraft-core' ); ?></label>
										<input type="text" name="_spicecraft_recipe_instruction_steps[<?php echo esc_attr( $s_idx ); ?>][heading]" value="<?php echo esc_attr( $s_head ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'e.g. Marinate the Protein / Prepare the Tadka', 'spicecraft-core' ); ?>" />
									</div>

									<div style="margin-bottom: 10px;">
										<label style="font-weight: 600; display: block; margin-bottom: 4px;"><?php esc_html_e( 'Step Instruction', 'spicecraft-core' ); ?> <span style="color: red;">*</span></label>
										<textarea name="_spicecraft_recipe_instruction_steps[<?php echo esc_attr( $s_idx ); ?>][instruction]" rows="3" class="widefat" placeholder="<?php esc_attr_e( 'Describe this preparation step clearly...', 'spicecraft-core' ); ?>"><?php echo esc_textarea( $s_inst ); ?></textarea>
									</div>

									<div>
										<label style="font-weight: 600; display: block; margin-bottom: 4px;"><?php esc_html_e( 'Chef’s Tip (Optional)', 'spicecraft-core' ); ?></label>
										<input type="text" name="_spicecraft_recipe_instruction_steps[<?php echo esc_attr( $s_idx ); ?>][tip]" value="<?php echo esc_attr( $s_tip ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'e.g. Maintain low flame to avoid scorching whole spices.', 'spicecraft-core' ); ?>" />
									</div>
								</div>

								<!-- Step Photography Media Picker -->
								<div>
									<label style="font-weight: 600; display: block; margin-bottom: 4px;"><?php esc_html_e( 'Step Photo (Optional)', 'spicecraft-core' ); ?></label>
									<div class="sc-step-media-preview" style="width: 100%; height: 110px; background: #f0f0f1; border: 1px dashed #c3c4c7; border-radius: 4px; display: flex; align-items: center; justify-content: center; overflow: hidden; margin-bottom: 6px;">
										<?php if ( $img_url ) : ?>
											<img src="<?php echo esc_url( $img_url ); ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;" />
										<?php else : ?>
											<span style="color: #8c8f94; font-size: 11px;"><?php esc_html_e( 'No step image', 'spicecraft-core' ); ?></span>
										<?php endif; ?>
									</div>
									<input type="hidden" name="_spicecraft_recipe_instruction_steps[<?php echo esc_attr( $s_idx ); ?>][image_id]" class="sc-step-image-id" value="<?php echo esc_attr( $s_img_id ?: '' ); ?>" />
									<div style="display: flex; gap: 4px;">
										<button type="button" class="button button-small sc-step-media-upload-btn"><?php esc_html_e( 'Select', 'spicecraft-core' ); ?></button>
										<button type="button" class="button button-small sc-step-media-remove-btn" style="<?php echo empty( $s_img_id ) ? 'display: none;' : ''; ?>"><?php esc_html_e( 'Remove', 'spicecraft-core' ); ?></button>
									</div>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- TAB 4: Culinary Notes -->
			<div class="sc-metabox-panel" id="sc-tab-notes" style="display: none;">
				<h3 style="margin-top: 0;"><?php esc_html_e( 'Culinary Notes & Guidance', 'spicecraft-core' ); ?></h3>
				<p class="description"><?php esc_html_e( 'Only populated sections will render on the frontend detail page. Empty notes are suppressed.', 'spicecraft-core' ); ?></p>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="sc_notes_chef"><?php esc_html_e( 'Chef / Kitchen Notes', 'spicecraft-core' ); ?></label></th>
						<td>
							<textarea id="sc_notes_chef" name="_spicecraft_recipe_notes_chef" rows="3" class="large-text" placeholder="<?php esc_attr_e( 'Expert techniques, spice roasting nuances, or regional historical context.', 'spicecraft-core' ); ?>"><?php echo esc_textarea( $meta['notes_chef'] ); ?></textarea>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sc_notes_serving"><?php esc_html_e( 'Serving Suggestions & Pairings', 'spicecraft-core' ); ?></label></th>
						<td>
							<textarea id="sc_notes_serving" name="_spicecraft_recipe_notes_serving" rows="3" class="large-text" placeholder="<?php esc_attr_e( 'Recommended flatbreads, fragrant rice pairings, or side accompaniments.', 'spicecraft-core' ); ?>"><?php echo esc_textarea( $meta['notes_serving'] ); ?></textarea>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sc_notes_storage"><?php esc_html_e( 'Storage & Shelf-Life Guidelines', 'spicecraft-core' ); ?></label></th>
						<td>
							<textarea id="sc_notes_storage" name="_spicecraft_recipe_notes_storage" rows="2" class="large-text" placeholder="<?php esc_attr_e( 'Refrigeration duration, freezing guidance, or flavor maturity over 24-48 hours.', 'spicecraft-core' ); ?>"><?php echo esc_textarea( $meta['notes_storage'] ); ?></textarea>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sc_notes_subs"><?php esc_html_e( 'Ingredient Substitutions', 'spicecraft-core' ); ?></label></th>
						<td>
							<textarea id="sc_notes_subs" name="_spicecraft_recipe_notes_subs" rows="2" class="large-text" placeholder="<?php esc_attr_e( 'Suitable vegetable or protein alternatives while preserving spice harmony.', 'spicecraft-core' ); ?>"><?php echo esc_textarea( $meta['notes_subs'] ); ?></textarea>
						</td>
					</tr>
				</table>
			</div>

			<!-- TAB 5: Nutrition Facts (Optional) -->
			<div class="sc-metabox-panel" id="sc-tab-nutrition" style="display: none;">
				<h3 style="margin-top: 0;"><?php esc_html_e( 'Nutrition Information (Optional)', 'spicecraft-core' ); ?></h3>
				<div class="notice notice-warning inline" style="margin-bottom: 16px;">
					<p><?php esc_html_e( 'Administrator Rule: Enter nutrition information only when it has been calculated or verified from an appropriate lab or culinary database. If left blank, the entire Nutrition section is cleanly hidden from the frontend.', 'spicecraft-core' ); ?></p>
				</div>

				<?php
				$nut = $meta['nutrition'];
				?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="sc_nut_size"><?php esc_html_e( 'Serving Size', 'spicecraft-core' ); ?></label></th>
						<td>
							<input type="text" id="sc_nut_size" name="_spicecraft_recipe_nutrition[serving_size]" value="<?php echo esc_attr( $nut['serving_size'] ?? '' ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'e.g. 1 bowl (250g)', 'spicecraft-core' ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sc_nut_cal"><?php esc_html_e( 'Calories (kcal)', 'spicecraft-core' ); ?></label></th>
						<td>
							<input type="text" id="sc_nut_cal" name="_spicecraft_recipe_nutrition[calories]" value="<?php echo esc_attr( $nut['calories'] ?? '' ); ?>" class="small-text" placeholder="320" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sc_nut_prot"><?php esc_html_e( 'Protein (g)', 'spicecraft-core' ); ?></label></th>
						<td>
							<input type="text" id="sc_nut_prot" name="_spicecraft_recipe_nutrition[protein]" value="<?php echo esc_attr( $nut['protein'] ?? '' ); ?>" class="small-text" placeholder="12g" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sc_nut_carbs"><?php esc_html_e( 'Carbohydrates (g)', 'spicecraft-core' ); ?></label></th>
						<td>
							<input type="text" id="sc_nut_carbs" name="_spicecraft_recipe_nutrition[carbs]" value="<?php echo esc_attr( $nut['carbs'] ?? '' ); ?>" class="small-text" placeholder="28g" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sc_nut_fat"><?php esc_html_e( 'Total Fat (g)', 'spicecraft-core' ); ?></label></th>
						<td>
							<input type="text" id="sc_nut_fat" name="_spicecraft_recipe_nutrition[fat]" value="<?php echo esc_attr( $nut['fat'] ?? '' ); ?>" class="small-text" placeholder="14g" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sc_nut_sat_fat"><?php esc_html_e( 'Saturated Fat (g)', 'spicecraft-core' ); ?></label></th>
						<td>
							<input type="text" id="sc_nut_sat_fat" name="_spicecraft_recipe_nutrition[sat_fat]" value="<?php echo esc_attr( $nut['sat_fat'] ?? '' ); ?>" class="small-text" placeholder="3.5g" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sc_nut_fiber"><?php esc_html_e( 'Dietary Fiber (g)', 'spicecraft-core' ); ?></label></th>
						<td>
							<input type="text" id="sc_nut_fiber" name="_spicecraft_recipe_nutrition[fiber]" value="<?php echo esc_attr( $nut['fiber'] ?? '' ); ?>" class="small-text" placeholder="6g" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sc_nut_sugar"><?php esc_html_e( 'Sugars (g)', 'spicecraft-core' ); ?></label></th>
						<td>
							<input type="text" id="sc_nut_sugar" name="_spicecraft_recipe_nutrition[sugar]" value="<?php echo esc_attr( $nut['sugar'] ?? '' ); ?>" class="small-text" placeholder="4g" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sc_nut_sod"><?php esc_html_e( 'Sodium (mg)', 'spicecraft-core' ); ?></label></th>
						<td>
							<input type="text" id="sc_nut_sod" name="_spicecraft_recipe_nutrition[sodium]" value="<?php echo esc_attr( $nut['sodium'] ?? '' ); ?>" class="small-text" placeholder="450mg" />
						</td>
					</tr>
				</table>
			</div>

			<!-- TAB 6: Product Relationships -->
			<div class="sc-metabox-panel" id="sc-tab-products" style="display: none;">
				<h3 style="margin-top: 0;"><?php esc_html_e( 'WooCommerce Product Relationships', 'spicecraft-core' ); ?></h3>
				<p class="description"><?php esc_html_e( 'Connect this recipe to actual SpiceCraft products. These will display in the "Spices Used In This Recipe" showcase on the frontend, and reciprocal links will appear on the product pages.', 'spicecraft-core' ); ?></p>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Featured Spice Products', 'spicecraft-core' ); ?></th>
						<td>
							<p class="description" style="margin-bottom: 6px;"><?php esc_html_e( 'Select products prominently showcased for this culinary creation (in addition to any products connected in the ingredient rows). Results are automatically deduplicated on the frontend.', 'spicecraft-core' ); ?></p>
							<?php
							if ( function_exists( 'spicecraft_render_admin_post_multiselect' ) ) {
								spicecraft_render_admin_post_multiselect( 'product', '_spicecraft_recipe_featured_products', $meta['featured_products'] );
							}
							?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Related Product Categories', 'spicecraft-core' ); ?></th>
						<td>
							<p class="description" style="margin-bottom: 6px;"><?php esc_html_e( 'Select categories to support broader discovery (e.g. Blended Masalas, Whole Spices).', 'spicecraft-core' ); ?></p>
							<div class="sc-multiselect-scrollbox" style="max-height: 160px; overflow-y: auto; border: 1px solid #ccd0d4; padding: 8px 12px; background: #fff; border-radius: 4px;">
								<?php
								if ( ! empty( $product_cats ) && ! is_wp_error( $product_cats ) ) {
									foreach ( $product_cats as $cat ) {
										$is_checked = in_array( $cat->term_id, $meta['related_categories'], true );
										?>
										<label style="display: block; margin-bottom: 4px;">
											<input type="checkbox" name="_spicecraft_recipe_related_categories[]" value="<?php echo esc_attr( $cat->term_id ); ?>" <?php checked( $is_checked ); ?> />
											<?php echo esc_html( $cat->name ); ?>
										</label>
										<?php
									}
								} else {
									echo '<p class="description">' . esc_html__( 'No product categories found.', 'spicecraft-core' ) . '</p>';
								}
								?>
							</div>
						</td>
					</tr>
				</table>
			</div>

			<!-- TAB 7: Media & Video -->
			<div class="sc-metabox-panel" id="sc-tab-media" style="display: none;">
				<h3 style="margin-top: 0;"><?php esc_html_e( 'Media, Gallery & Video', 'spicecraft-core' ); ?></h3>
				<p class="description"><?php esc_html_e( 'Set an optional hero image override (uses Featured Image by default), mobile hero banner, recipe gallery images, and video URL.', 'spicecraft-core' ); ?></p>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Desktop Hero Override', 'spicecraft-core' ); ?></th>
						<td>
							<?php
							if ( function_exists( 'spicecraft_render_admin_media_uploader' ) ) {
								spicecraft_render_admin_media_uploader(
									'_spicecraft_recipe_hero_image_id',
									$meta['hero_image_id'],
									__( 'Wide landscape banner (Recommended: 1920x800). If empty, standard featured image is used.', 'spicecraft-core' )
								);
							}
							?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Mobile Hero Banner', 'spicecraft-core' ); ?></th>
						<td>
							<?php
							if ( function_exists( 'spicecraft_render_admin_media_uploader' ) ) {
								spicecraft_render_admin_media_uploader(
									'_spicecraft_recipe_mobile_hero_image_id',
									$meta['mobile_hero_image_id'],
									__( 'Optimized portrait / square banner for mobile screens (e.g. 768x600).', 'spicecraft-core' )
								);
							}
							?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Recipe Photo Gallery', 'spicecraft-core' ); ?></th>
						<td>
							<?php
							if ( function_exists( 'spicecraft_render_admin_gallery_uploader' ) ) {
								spicecraft_render_admin_gallery_uploader(
									'_spicecraft_recipe_gallery_ids',
									$meta['gallery_ids'],
									__( 'Additional preparation shots, ingredient plating, and final culinary presentation.', 'spicecraft-core' )
								);
							}
							?>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sc_video_url"><?php esc_html_e( 'Recipe Video URL', 'spicecraft-core' ); ?></label></th>
						<td>
							<input type="url" id="sc_video_url" name="_spicecraft_recipe_video_url" value="<?php echo esc_url( $meta['video_url'] ); ?>" class="large-text" placeholder="https://www.youtube.com/watch?v=... or https://vimeo.com/..." />
							<p class="description"><?php esc_html_e( 'Supports YouTube, Vimeo, or standard WordPress oEmbed endpoints. Cleanly hidden if empty.', 'spicecraft-core' ); ?></p>
						</td>
					</tr>
				</table>
			</div>
		</div>
		<?php
	}

	/**
	 * Save Recipe Metadata with Capability & Nonce Verification.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public function save_meta( $post_id, $post ) {
		// 1. Nonce verification
		if ( ! isset( $_POST[ self::NONCE_NAME ] ) || ! wp_verify_nonce( sanitize_key( $_POST[ self::NONCE_NAME ] ), self::NONCE_ACTION ) ) {
			return;
		}

		// 2. Autosave and revision checks
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		// 3. Capability check
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// 4. Sanitize and save Times, Yield, Difficulty
		$prep_min = isset( $_POST['_spicecraft_recipe_prep_minutes'] ) ? absint( $_POST['_spicecraft_recipe_prep_minutes'] ) : 0;
		$cook_min = isset( $_POST['_spicecraft_recipe_cook_minutes'] ) ? absint( $_POST['_spicecraft_recipe_cook_minutes'] ) : 0;
		$add_min  = isset( $_POST['_spicecraft_recipe_additional_minutes'] ) ? absint( $_POST['_spicecraft_recipe_additional_minutes'] ) : 0;
		$total_min= isset( $_POST['_spicecraft_recipe_total_minutes'] ) ? absint( $_POST['_spicecraft_recipe_total_minutes'] ) : 0;

		// Calculate total if not manually specified
		if ( ! $total_min && ( $prep_min || $cook_min || $add_min ) ) {
			$total_min = $prep_min + $cook_min + $add_min;
		}

		update_post_meta( $post_id, '_spicecraft_recipe_prep_minutes', $prep_min );
		update_post_meta( $post_id, '_spicecraft_recipe_cook_minutes', $cook_min );
		update_post_meta( $post_id, '_spicecraft_recipe_additional_minutes', $add_min );
		update_post_meta( $post_id, '_spicecraft_recipe_total_minutes', $total_min );

		$yield = isset( $_POST['_spicecraft_recipe_yield'] ) ? sanitize_text_field( $_POST['_spicecraft_recipe_yield'] ) : '';
		update_post_meta( $post_id, '_spicecraft_recipe_yield', $yield );

		$diff = isset( $_POST['_spicecraft_recipe_difficulty'] ) ? sanitize_key( $_POST['_spicecraft_recipe_difficulty'] ) : '';
		if ( in_array( $diff, array( 'easy', 'medium', 'advanced' ), true ) ) {
			update_post_meta( $post_id, '_spicecraft_recipe_difficulty', $diff );
		} else {
			delete_post_meta( $post_id, '_spicecraft_recipe_difficulty' );
		}

		// Dietary
		$allowed_dietary = array( 'vegetarian', 'vegan', 'gluten_free', 'dairy_free', 'jain', 'nut_free' );
		$clean_dietary   = array();
		if ( isset( $_POST['_spicecraft_recipe_dietary'] ) && is_array( $_POST['_spicecraft_recipe_dietary'] ) ) {
			foreach ( $_POST['_spicecraft_recipe_dietary'] as $d_val ) {
				$sanitized = sanitize_key( $d_val );
				if ( in_array( $sanitized, $allowed_dietary, true ) ) {
					$clean_dietary[] = $sanitized;
				}
			}
		}
		update_post_meta( $post_id, '_spicecraft_recipe_dietary', array_unique( $clean_dietary ) );

		// 5. Sanitize and save Ingredient Groups & collect linked product IDs
		$clean_groups        = array();
		$linked_product_ids  = array();

		if ( isset( $_POST['_spicecraft_recipe_ingredient_groups'] ) && is_array( $_POST['_spicecraft_recipe_ingredient_groups'] ) ) {
			foreach ( $_POST['_spicecraft_recipe_ingredient_groups'] as $group_data ) {
				if ( ! is_array( $group_data ) ) {
					continue;
				}

				$group_name  = isset( $group_data['group_name'] ) ? sanitize_text_field( $group_data['group_name'] ) : '';
				$clean_items = array();

				if ( isset( $group_data['items'] ) && is_array( $group_data['items'] ) ) {
					foreach ( $group_data['items'] as $item_data ) {
						if ( ! is_array( $item_data ) ) {
							continue;
						}

						$ing_name = isset( $item_data['ingredient'] ) ? sanitize_text_field( $item_data['ingredient'] ) : '';
						if ( empty( $ing_name ) ) {
							continue; // Skip completely empty ingredient rows
						}

						$qty     = isset( $item_data['quantity'] ) ? sanitize_text_field( $item_data['quantity'] ) : '';
						$unit    = isset( $item_data['unit'] ) ? sanitize_text_field( $item_data['unit'] ) : '';
						$note    = isset( $item_data['note'] ) ? sanitize_text_field( $item_data['note'] ) : '';
						$prod_id = isset( $item_data['product_id'] ) ? absint( $item_data['product_id'] ) : 0;

						if ( $prod_id > 0 ) {
							$linked_product_ids[] = $prod_id;
						}

						$clean_items[] = array(
							'quantity'   => $qty,
							'unit'       => $unit,
							'ingredient' => $ing_name,
							'note'       => $note,
							'product_id' => $prod_id,
						);
					}
				}

				// Only save group if it has a title or contains ingredients
				if ( ! empty( $group_name ) || ! empty( $clean_items ) ) {
					$clean_groups[] = array(
						'group_name' => $group_name,
						'items'      => $clean_items,
					);
				}
			}
		}
		update_post_meta( $post_id, '_spicecraft_recipe_ingredient_groups', $clean_groups );

		// 6. Sanitize and save Instruction Steps
		$clean_steps = array();
		if ( isset( $_POST['_spicecraft_recipe_instruction_steps'] ) && is_array( $_POST['_spicecraft_recipe_instruction_steps'] ) ) {
			$step_count = 1;
			foreach ( $_POST['_spicecraft_recipe_instruction_steps'] as $step_data ) {
				if ( ! is_array( $step_data ) ) {
					continue;
				}

				$instruction = isset( $step_data['instruction'] ) ? sanitize_textarea_field( $step_data['instruction'] ) : '';
				if ( empty( $instruction ) ) {
					continue; // Skip empty instructions
				}

				$heading  = isset( $step_data['heading'] ) ? sanitize_text_field( $step_data['heading'] ) : '';
				$image_id = isset( $step_data['image_id'] ) ? absint( $step_data['image_id'] ) : 0;
				$tip      = isset( $step_data['tip'] ) ? sanitize_text_field( $step_data['tip'] ) : '';

				$clean_steps[] = array(
					'step_number' => $step_count++,
					'heading'     => $heading,
					'instruction' => $instruction,
					'image_id'    => $image_id,
					'tip'         => $tip,
				);
			}
		}
		update_post_meta( $post_id, '_spicecraft_recipe_instruction_steps', $clean_steps );

		// 7. Culinary Notes
		$notes_chef    = isset( $_POST['_spicecraft_recipe_notes_chef'] ) ? sanitize_textarea_field( $_POST['_spicecraft_recipe_notes_chef'] ) : '';
		$notes_serving = isset( $_POST['_spicecraft_recipe_notes_serving'] ) ? sanitize_textarea_field( $_POST['_spicecraft_recipe_notes_serving'] ) : '';
		$notes_storage = isset( $_POST['_spicecraft_recipe_notes_storage'] ) ? sanitize_textarea_field( $_POST['_spicecraft_recipe_notes_storage'] ) : '';
		$notes_subs    = isset( $_POST['_spicecraft_recipe_notes_subs'] ) ? sanitize_textarea_field( $_POST['_spicecraft_recipe_notes_subs'] ) : '';

		update_post_meta( $post_id, '_spicecraft_recipe_notes_chef', $notes_chef );
		update_post_meta( $post_id, '_spicecraft_recipe_notes_serving', $notes_serving );
		update_post_meta( $post_id, '_spicecraft_recipe_notes_storage', $notes_storage );
		update_post_meta( $post_id, '_spicecraft_recipe_notes_subs', $notes_subs );

		// 8. Nutrition Facts
		$clean_nutrition = array();
		if ( isset( $_POST['_spicecraft_recipe_nutrition'] ) && is_array( $_POST['_spicecraft_recipe_nutrition'] ) ) {
			$raw_nut = $_POST['_spicecraft_recipe_nutrition'];
			$nut_keys = array( 'serving_size', 'calories', 'protein', 'carbs', 'fat', 'sat_fat', 'fiber', 'sugar', 'sodium' );
			$has_values = false;
			foreach ( $nut_keys as $k ) {
				$val = isset( $raw_nut[ $k ] ) ? sanitize_text_field( $raw_nut[ $k ] ) : '';
				if ( ! empty( $val ) ) {
					$has_values = true;
				}
				$clean_nutrition[ $k ] = $val;
			}
			if ( ! $has_values ) {
				$clean_nutrition = array();
			}
		}
		update_post_meta( $post_id, '_spicecraft_recipe_nutrition', $clean_nutrition );

		// 9. Product Relationships (Featured & Categories)
		$clean_feat_prods = array();
		if ( isset( $_POST['_spicecraft_recipe_featured_products'] ) && is_array( $_POST['_spicecraft_recipe_featured_products'] ) ) {
			$clean_feat_prods = array_filter( array_map( 'absint', $_POST['_spicecraft_recipe_featured_products'] ) );
			$linked_product_ids = array_merge( $linked_product_ids, $clean_feat_prods );
		}
		update_post_meta( $post_id, '_spicecraft_recipe_featured_products', $clean_feat_prods );

		// Save aggregated unique linked product IDs for fast indexed queries
		$unique_linked_prods = array_values( array_unique( array_filter( $linked_product_ids ) ) );
		update_post_meta( $post_id, '_spicecraft_recipe_linked_product_ids', $unique_linked_prods );

		$clean_rel_cats = array();
		if ( isset( $_POST['_spicecraft_recipe_related_categories'] ) && is_array( $_POST['_spicecraft_recipe_related_categories'] ) ) {
			$clean_rel_cats = array_filter( array_map( 'absint', $_POST['_spicecraft_recipe_related_categories'] ) );
		}
		update_post_meta( $post_id, '_spicecraft_recipe_related_categories', $clean_rel_cats );

		// 10. Media & Video
		$hero_id        = isset( $_POST['_spicecraft_recipe_hero_image_id'] ) ? absint( $_POST['_spicecraft_recipe_hero_image_id'] ) : 0;
		$mobile_hero_id = isset( $_POST['_spicecraft_recipe_mobile_hero_image_id'] ) ? absint( $_POST['_spicecraft_recipe_mobile_hero_image_id'] ) : 0;
		$gallery_ids    = isset( $_POST['_spicecraft_recipe_gallery_ids'] ) && is_array( $_POST['_spicecraft_recipe_gallery_ids'] )
			? array_filter( array_map( 'absint', $_POST['_spicecraft_recipe_gallery_ids'] ) )
			: array();
		$video_url      = isset( $_POST['_spicecraft_recipe_video_url'] ) ? esc_url_raw( $_POST['_spicecraft_recipe_video_url'] ) : '';

		update_post_meta( $post_id, '_spicecraft_recipe_hero_image_id', $hero_id );
		update_post_meta( $post_id, '_spicecraft_recipe_mobile_hero_image_id', $mobile_hero_id );
		update_post_meta( $post_id, '_spicecraft_recipe_gallery_ids', $gallery_ids );
		update_post_meta( $post_id, '_spicecraft_recipe_video_url', $video_url );
	}

	/**
	 * Render Non-blocking Admin Completeness Notice.
	 */
	public function render_completeness_notice() {
		$screen = get_current_screen();
		if ( ! $screen || self::POST_TYPE !== $screen->post_type || 'post' !== $screen->base ) {
			return;
		}

		global $post;
		if ( ! $post || 'publish' !== $post->post_status ) {
			return;
		}

		$missing = array();
		if ( ! has_post_thumbnail( $post->ID ) ) {
			$missing[] = __( 'Featured Photo', 'spicecraft-core' );
		}

		$meta = function_exists( 'spicecraft_get_recipe_meta' ) ? spicecraft_get_recipe_meta( $post->ID ) : array();
		if ( empty( $meta['ingredient_groups'] ) ) {
			$missing[] = __( 'Ingredients List', 'spicecraft-core' );
		}
		if ( empty( $meta['instruction_steps'] ) ) {
			$missing[] = __( 'Instruction Steps', 'spicecraft-core' );
		}

		if ( ! empty( $missing ) ) {
			?>
			<div class="notice notice-warning is-dismissible">
				<p>
					<strong><?php esc_html_e( 'Recipe Completeness Advisory:', 'spicecraft-core' ); ?></strong>
					<?php
					printf(
						esc_html__( 'This published recipe is missing: %s. While it remains viewable, complete content ensures the best visitor experience and SEO schema validity.', 'spicecraft-core' ),
						'<strong>' . esc_html( implode( ', ', $missing ) ) . '</strong>'
					);
					?>
				</p>
			</div>
			<?php
		}
	}
}
