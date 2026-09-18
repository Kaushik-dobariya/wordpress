<?php
/**
 * SpiceCraft Core - Shared CMS UI and Sanitization Helpers
 *
 * Provides reusable administrative UI components, repeaters, media selectors,
 * and robust sanitization primitives for Manufacturing, Quality & Sourcing,
 * and future structured CMS modules without code duplication.
 *
 * @package SpiceCraft_Core
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render an administrative single-media uploader component.
 *
 * @param string $input_name    Form input name (e.g. option_name[hero][desktop_image_id]).
 * @param int    $attachment_id Current attachment ID.
 * @param string $label         Optional descriptive label.
 * @param string $button_text   Upload button label.
 */
function spicecraft_render_admin_media_uploader( $input_name, $attachment_id = 0, $label = '', $button_text = '' ) {
	$attachment_id = absint( $attachment_id );
	$image_url     = $attachment_id ? wp_get_attachment_image_url( $attachment_id, 'medium' ) : '';
	$button_text   = ! empty( $button_text ) ? $button_text : __( 'Select Image', 'spicecraft-core' );
	$unique_id     = 'sc_media_' . md5( $input_name );
	?>
	<div class="sc-media-uploader-wrap" id="<?php echo esc_attr( $unique_id ); ?>">
		<?php if ( ! empty( $label ) ) : ?>
			<p class="description" style="margin-bottom: 6px;"><strong><?php echo esc_html( $label ); ?></strong></p>
		<?php endif; ?>

		<div class="sc-media-preview-box" style="margin-bottom: 8px; max-width: 220px; min-height: 80px; background: #f0f0f1; border: 1px dashed #c3c4c7; border-radius: 4px; display: flex; align-items: center; justify-content: center; overflow: hidden; padding: 4px;">
			<?php if ( $image_url ) : ?>
				<img src="<?php echo esc_url( $image_url ); ?>" alt="" style="max-width: 100%; height: auto; display: block; border-radius: 2px;" />
			<?php else : ?>
				<span style="color: #8c8f94; font-size: 12px;"><?php esc_html_e( 'No image selected', 'spicecraft-core' ); ?></span>
			<?php endif; ?>
		</div>

		<input type="hidden" name="<?php echo esc_attr( $input_name ); ?>" class="sc-media-id-input" value="<?php echo esc_attr( $attachment_id ?: '' ); ?>" />

		<div style="display: flex; gap: 6px;">
			<button type="button" class="button button-secondary sc-media-upload-btn">
				<span class="dashicons dashicons-format-image" style="margin-top: -2px;"></span>
				<?php echo esc_html( $button_text ); ?>
			</button>
			<button type="button" class="button sc-media-remove-btn" style="<?php echo empty( $attachment_id ) ? 'display: none;' : ''; ?>">
				<?php esc_html_e( 'Remove', 'spicecraft-core' ); ?>
			</button>
		</div>
	</div>
	<?php
}

/**
 * Render an administrative gallery manager component.
 *
 * @param string $input_name     Form input name for gallery array.
 * @param array  $attachment_ids Array of integer attachment IDs.
 * @param string $label          Optional descriptive label.
 */
function spicecraft_render_admin_gallery_uploader( $input_name, $attachment_ids = array(), $label = '' ) {
	if ( ! is_array( $attachment_ids ) ) {
		$attachment_ids = array();
	}
	$attachment_ids = array_filter( array_map( 'absint', $attachment_ids ) );
	$unique_id      = 'sc_gallery_' . md5( $input_name );
	?>
	<div class="sc-gallery-uploader-wrap" id="<?php echo esc_attr( $unique_id ); ?>">
		<?php if ( ! empty( $label ) ) : ?>
			<p class="description" style="margin-bottom: 8px;"><strong><?php echo esc_html( $label ); ?></strong></p>
		<?php endif; ?>

		<div class="sc-gallery-grid" style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 12px; min-height: 60px; padding: 8px; background: #f0f0f1; border: 1px dashed #c3c4c7; border-radius: 4px;">
			<?php if ( empty( $attachment_ids ) ) : ?>
				<p class="sc-gallery-empty" style="color: #8c8f94; font-size: 13px; margin: auto;"><?php esc_html_e( 'No gallery images selected. Click "Add Images to Gallery" to select from media library.', 'spicecraft-core' ); ?></p>
			<?php else : ?>
				<?php foreach ( $attachment_ids as $att_id ) :
					$thumb = wp_get_attachment_image_url( $att_id, 'thumbnail' );
					if ( ! $thumb ) continue;
					?>
					<div class="sc-gallery-item" data-id="<?php echo esc_attr( $att_id ); ?>" style="position: relative; width: 80px; height: 80px; border: 1px solid #ccd0d4; border-radius: 4px; overflow: hidden; background: #fff;">
						<img src="<?php echo esc_url( $thumb ); ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;" />
						<button type="button" class="sc-gallery-item-remove" style="position: absolute; top: 2px; right: 2px; background: rgba(0,0,0,0.7); color: #fff; border: none; border-radius: 50%; width: 18px; height: 18px; line-height: 16px; text-align: center; cursor: pointer; font-size: 11px;">&times;</button>
						<input type="hidden" name="<?php echo esc_attr( $input_name ); ?>[]" value="<?php echo esc_attr( $att_id ); ?>" />
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>

		<div class="sc-gallery-actions">
			<button type="button" class="button button-secondary sc-gallery-add-btn" data-input-name="<?php echo esc_attr( $input_name ); ?>">
				<span class="dashicons dashicons-images-alt2" style="margin-top: -2px;"></span>
				<?php esc_html_e( 'Add Images to Gallery', 'spicecraft-core' ); ?>
			</button>
			<button type="button" class="button sc-gallery-clear-btn" style="<?php echo empty( $attachment_ids ) ? 'display: none;' : ''; ?>">
				<?php esc_html_e( 'Clear All', 'spicecraft-core' ); ?>
			</button>
		</div>
	</div>
	<?php
}

/**
 * Render a section order and visibility table.
 *
 * @param string $option_name         Top-level settings option key.
 * @param array  $order_map           Array of [section_key => priority].
 * @param array  $enabled_map         Array of [section_key => 1|0].
 * @param array  $section_definitions Array of [section_key => ['name' => ..., 'id' => ..., 'desc' => ...]].
 */
function spicecraft_render_admin_section_order_table( $option_name, $order_map, $enabled_map, $section_definitions ) {
	asort( $order_map, SORT_NUMERIC );
	?>
	<div class="notice notice-info inline" style="margin-bottom: 20px;">
		<p><?php esc_html_e( 'Configure section visibility and order. Lower numbers render higher on the page. Note: Empty sections are cleanly suppressed on the frontend to prevent placeholder output.', 'spicecraft-core' ); ?></p>
	</div>

	<table class="wp-list-table widefat fixed striped" role="presentation">
		<thead>
			<tr>
				<th style="width: 80px;"><?php esc_html_e( 'Enabled', 'spicecraft-core' ); ?></th>
				<th style="width: 100px;"><?php esc_html_e( 'Order', 'spicecraft-core' ); ?></th>
				<th style="width: 220px;"><?php esc_html_e( 'Section Name', 'spicecraft-core' ); ?></th>
				<th style="width: 180px;"><?php esc_html_e( 'Semantic Anchor', 'spicecraft-core' ); ?></th>
				<th><?php esc_html_e( 'Purpose / Content Description', 'spicecraft-core' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $order_map as $sec_key => $ord_val ) :
				$sec_info   = $section_definitions[ $sec_key ] ?? array( 'name' => $sec_key, 'id' => '#' . $sec_key, 'desc' => '' );
				$is_checked = ! empty( $enabled_map[ $sec_key ] );
				?>
				<tr>
					<td>
						<label class="screen-reader-text" for="sec_enable_<?php echo esc_attr( $sec_key ); ?>"><?php echo esc_html( $sec_info['name'] ); ?></label>
						<input type="checkbox" name="<?php echo esc_attr( $option_name ); ?>[sections_enabled][<?php echo esc_attr( $sec_key ); ?>]" id="sec_enable_<?php echo esc_attr( $sec_key ); ?>" value="1" <?php checked( $is_checked ); ?> />
					</td>
					<td>
						<input type="number" name="<?php echo esc_attr( $option_name ); ?>[sections_order][<?php echo esc_attr( $sec_key ); ?>]" value="<?php echo esc_attr( $ord_val ); ?>" class="small-text" step="5" min="0" />
					</td>
					<td>
						<strong><?php echo esc_html( $sec_info['name'] ); ?></strong>
					</td>
					<td>
						<code><?php echo esc_html( $sec_info['id'] ); ?></code>
					</td>
					<td style="color: #646970;">
						<?php echo esc_html( $sec_info['desc'] ); ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php
}

/**
 * Render a post multi-select checkbox list.
 *
 * @param string $post_type   Post type (e.g. 'product').
 * @param string $input_name  Input name for the array.
 * @param array  $selected_ids Array of checked post IDs.
 */
function spicecraft_render_admin_post_multiselect( $post_type, $input_name, $selected_ids = array() ) {
	if ( ! is_array( $selected_ids ) ) {
		$selected_ids = array();
	}
	$selected_ids = array_map( 'absint', $selected_ids );

	$posts = get_posts( array(
		'post_type'      => $post_type,
		'post_status'    => 'publish',
		'posts_per_page' => 50,
		'orderby'        => 'title',
		'order'          => 'ASC',
	) );

	if ( empty( $posts ) ) {
		echo '<p class="description">' . sprintf( esc_html__( 'No published %s found.', 'spicecraft-core' ), esc_html( $post_type ) ) . '</p>';
		return;
	}
	?>
	<div class="sc-multiselect-scrollbox" style="max-height: 160px; overflow-y: auto; border: 1px solid #ccd0d4; padding: 8px 12px; background: #fff; border-radius: 4px;">
		<?php foreach ( $posts as $p ) :
			$is_checked = in_array( $p->ID, $selected_ids, true );
			?>
			<label style="display: block; margin-bottom: 4px;">
				<input type="checkbox" name="<?php echo esc_attr( $input_name ); ?>[]" value="<?php echo esc_attr( $p->ID ); ?>" <?php checked( $is_checked ); ?> />
				<?php echo esc_html( $p->post_title ); ?>
			</label>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * Render a taxonomy terms multi-select checkbox list.
 *
 * @param string $taxonomy     Taxonomy slug (e.g. 'product_cat', 'spicecraft_certification').
 * @param string $input_name   Input name for the array.
 * @param array  $selected_ids Array of checked term IDs.
 */
function spicecraft_render_admin_taxonomy_multiselect( $taxonomy, $input_name, $selected_ids = array() ) {
	if ( ! is_array( $selected_ids ) ) {
		$selected_ids = array();
	}
	$selected_ids = array_map( 'absint', $selected_ids );

	$terms = get_terms( array(
		'taxonomy'   => $taxonomy,
		'hide_empty' => false,
	) );

	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		echo '<p class="description">' . sprintf( esc_html__( 'No %s terms found.', 'spicecraft-core' ), esc_html( $taxonomy ) ) . '</p>';
		return;
	}
	?>
	<div class="sc-multiselect-scrollbox" style="max-height: 160px; overflow-y: auto; border: 1px solid #ccd0d4; padding: 8px 12px; background: #fff; border-radius: 4px;">
		<?php foreach ( $terms as $t ) :
			$is_checked = in_array( $t->term_id, $selected_ids, true );
			?>
			<label style="display: block; margin-bottom: 4px;">
				<input type="checkbox" name="<?php echo esc_attr( $input_name ); ?>[]" value="<?php echo esc_attr( $t->term_id ); ?>" <?php checked( $is_checked ); ?> />
				<?php echo esc_html( $t->name ); ?> (<?php echo esc_html( $t->count ); ?>)
			</label>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * Render repeatable statistics rows for an admin tab.
 *
 * @param string $option_name Top-level option name.
 * @param array  $items       Array of stat items.
 * @param string $container_id DOM container ID.
 * @param string $add_btn_id   Add button DOM ID.
 */
function spicecraft_render_admin_stat_rows( $option_name, $items = array(), $container_id = 'sc-stat-rows', $add_btn_id = 'sc-add-stat-btn' ) {
	?>
	<div id="<?php echo esc_attr( $container_id ); ?>">
		<?php
		if ( ! empty( $items ) && is_array( $items ) ) :
			foreach ( $items as $idx => $st ) :
				?>
				<div class="sc-repeatable-row" style="display: flex; gap: 8px; align-items: center; margin-bottom: 8px; background: #f9f9f9; padding: 8px; border: 1px solid #ccd0d4; border-radius: 4px;">
					<input type="text" name="<?php echo esc_attr( $option_name ); ?>[statistics][items][<?php echo esc_attr( $idx ); ?>][value]" value="<?php echo esc_attr( $st['value'] ?? '' ); ?>" placeholder="Value (e.g. 50)" style="width: 90px;" />
					<input type="text" name="<?php echo esc_attr( $option_name ); ?>[statistics][items][<?php echo esc_attr( $idx ); ?>][suffix]" value="<?php echo esc_attr( $st['suffix'] ?? '' ); ?>" placeholder="Suffix (+, %, MT)" style="width: 80px;" />
					<input type="text" name="<?php echo esc_attr( $option_name ); ?>[statistics][items][<?php echo esc_attr( $idx ); ?>][label]" value="<?php echo esc_attr( $st['label'] ?? '' ); ?>" placeholder="Metric Label" class="regular-text" style="flex-grow: 1;" />
					<input type="text" name="<?php echo esc_attr( $option_name ); ?>[statistics][items][<?php echo esc_attr( $idx ); ?>][description]" value="<?php echo esc_attr( $st['description'] ?? '' ); ?>" placeholder="Short Note (Optional)" style="flex-grow: 1;" />
					<input type="number" name="<?php echo esc_attr( $option_name ); ?>[statistics][items][<?php echo esc_attr( $idx ); ?>][order]" value="<?php echo esc_attr( $st['order'] ?? 10 ); ?>" placeholder="Order" style="width: 60px;" />
					<button type="button" class="button sc-remove-row-btn">&times;</button>
				</div>
				<?php
			endforeach;
		endif;
		?>
	</div>
	<button type="button" class="button button-secondary" id="<?php echo esc_attr( $add_btn_id ); ?>" data-option-name="<?php echo esc_attr( $option_name ); ?>" data-container="<?php echo esc_attr( $container_id ); ?>">
		<span class="dashicons dashicons-plus-alt2" style="margin-top: -2px;"></span>
		<?php esc_html_e( 'Add Statistic Item', 'spicecraft-core' ); ?>
	</button>
	<?php
}

/**
 * Render repeatable process steps.
 *
 * @param string $option_name Option name.
 * @param array  $items       Process items.
 * @param string $container_id DOM ID.
 * @param string $add_btn_id   Button ID.
 */
function spicecraft_render_admin_process_rows( $option_name, $items = array(), $container_id = 'sc-process-rows', $add_btn_id = 'sc-add-process-btn' ) {
	?>
	<div id="<?php echo esc_attr( $container_id ); ?>">
		<?php
		if ( ! empty( $items ) && is_array( $items ) ) :
			foreach ( $items as $idx => $pr ) :
				?>
				<div class="sc-repeatable-row sc-card" style="padding: 12px; margin-bottom: 10px; background: #f9f9f9; border: 1px solid #ccd0d4; border-radius: 4px;">
					<div style="display: flex; gap: 8px; margin-bottom: 8px;">
						<input type="text" name="<?php echo esc_attr( $option_name ); ?>[process][items][<?php echo esc_attr( $idx ); ?>][step_number]" value="<?php echo esc_attr( $pr['step_number'] ?? ( $idx + 1 ) ); ?>" placeholder="Step (e.g. 01)" style="width: 80px;" />
						<input type="text" name="<?php echo esc_attr( $option_name ); ?>[process][items][<?php echo esc_attr( $idx ); ?>][title]" value="<?php echo esc_attr( $pr['title'] ?? '' ); ?>" placeholder="Process Stage Title" class="regular-text" style="flex-grow: 1;" />
						<input type="number" name="<?php echo esc_attr( $option_name ); ?>[process][items][<?php echo esc_attr( $idx ); ?>][order]" value="<?php echo esc_attr( $pr['order'] ?? 10 ); ?>" placeholder="Order" style="width: 70px;" />
						<button type="button" class="button sc-remove-row-btn">&times;</button>
					</div>
					<textarea name="<?php echo esc_attr( $option_name ); ?>[process][items][<?php echo esc_attr( $idx ); ?>][description]" placeholder="Detailed description of this stage..." rows="2" style="width: 100%; margin-bottom: 8px;"><?php echo esc_textarea( $pr['description'] ?? '' ); ?></textarea>
					<div style="display: flex; align-items: center; gap: 12px;">
						<label style="font-size: 12px; color: #50575e;"><?php esc_html_e( 'Stage Image:', 'spicecraft-core' ); ?></label>
						<?php
						spicecraft_render_admin_media_uploader(
							"{$option_name}[process][items][{$idx}][image_id]",
							$pr['image_id'] ?? 0,
							'',
							__( 'Select Stage Image', 'spicecraft-core' )
						);
						?>
					</div>
				</div>
				<?php
			endforeach;
		endif;
		?>
	</div>
	<button type="button" class="button button-secondary" id="<?php echo esc_attr( $add_btn_id ); ?>" data-option-name="<?php echo esc_attr( $option_name ); ?>" data-container="<?php echo esc_attr( $container_id ); ?>">
		<span class="dashicons dashicons-plus-alt2" style="margin-top: -2px;"></span>
		<?php esc_html_e( 'Add Process Step', 'spicecraft-core' ); ?>
	</button>
	<?php
}

/**
 * Render repeatable technology / equipment cards with specification sub-rows.
 *
 * @param string $option_name Top option name.
 * @param array  $items       Equipment array.
 */
function spicecraft_render_admin_equipment_rows( $option_name, $items = array() ) {
	?>
	<div id="sc-equipment-rows">
		<?php
		if ( ! empty( $items ) && is_array( $items ) ) :
			foreach ( $items as $idx => $eq ) :
				$specs = $eq['specs'] ?? array();
				?>
				<div class="sc-repeatable-row sc-card sc-equipment-card" style="padding: 14px; margin-bottom: 12px; background: #fdfdfd; border: 1px solid #ccd0d4; border-radius: 4px;">
					<div style="display: flex; gap: 8px; margin-bottom: 8px;">
						<input type="text" name="<?php echo esc_attr( $option_name ); ?>[equipment][items][<?php echo esc_attr( $idx ); ?>][name]" value="<?php echo esc_attr( $eq['name'] ?? '' ); ?>" placeholder="Equipment / Machine Name" class="large-text" style="flex-grow: 1;" />
						<input type="number" name="<?php echo esc_attr( $option_name ); ?>[equipment][items][<?php echo esc_attr( $idx ); ?>][order]" value="<?php echo esc_attr( $eq['order'] ?? 10 ); ?>" placeholder="Order" style="width: 70px;" />
						<button type="button" class="button sc-remove-row-btn">&times;</button>
					</div>
					<textarea name="<?php echo esc_attr( $option_name ); ?>[equipment][items][<?php echo esc_attr( $idx ); ?>][description]" placeholder="Operational description..." rows="2" style="width: 100%; margin-bottom: 8px;"><?php echo esc_textarea( $eq['description'] ?? '' ); ?></textarea>

					<div style="display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 8px;">
						<div style="flex: 1; min-width: 200px;">
							<p style="margin: 0 0 4px; font-weight: 600; font-size: 12px;"><?php esc_html_e( 'Equipment Photo', 'spicecraft-core' ); ?></p>
							<?php
							spicecraft_render_admin_media_uploader(
								"{$option_name}[equipment][items][{$idx}][image_id]",
								$eq['image_id'] ?? 0,
								'',
								__( 'Select Photo', 'spicecraft-core' )
							);
							?>
						</div>
						<div style="flex: 2; min-width: 280px;">
							<p style="margin: 0 0 4px; font-weight: 600; font-size: 12px;"><?php esc_html_e( 'Technical Specifications (Label / Value)', 'spicecraft-core' ); ?></p>
							<div class="sc-spec-rows-container" data-parent-idx="<?php echo esc_attr( $idx ); ?>">
								<?php
								if ( ! empty( $specs ) && is_array( $specs ) ) :
									foreach ( $specs as $s_idx => $sp ) :
										?>
										<div class="sc-spec-row" style="display: flex; gap: 6px; margin-bottom: 4px;">
											<input type="text" name="<?php echo esc_attr( $option_name ); ?>[equipment][items][<?php echo esc_attr( $idx ); ?>][specs][<?php echo esc_attr( $s_idx ); ?>][label]" value="<?php echo esc_attr( $sp['label'] ?? '' ); ?>" placeholder="Label (e.g. Material)" style="width: 45%;" />
											<input type="text" name="<?php echo esc_attr( $option_name ); ?>[equipment][items][<?php echo esc_attr( $idx ); ?>][specs][<?php echo esc_attr( $s_idx ); ?>][value]" value="<?php echo esc_attr( $sp['value'] ?? '' ); ?>" placeholder="Value (e.g. SS 316 Food Grade)" style="width: 45%;" />
											<button type="button" class="button sc-remove-spec-btn">&times;</button>
										</div>
										<?php
									endforeach;
								endif;
								?>
							</div>
							<button type="button" class="button button-small sc-add-spec-btn" data-option-name="<?php echo esc_attr( $option_name ); ?>" data-parent-idx="<?php echo esc_attr( $idx ); ?>">
								+ <?php esc_html_e( 'Add Spec Row', 'spicecraft-core' ); ?>
							</button>
						</div>
					</div>
				</div>
				<?php
			endforeach;
		endif;
		?>
	</div>
	<button type="button" class="button button-secondary" id="sc-add-equipment-btn" data-option-name="<?php echo esc_attr( $option_name ); ?>">
		<span class="dashicons dashicons-plus-alt2" style="margin-top: -2px;"></span>
		<?php esc_html_e( 'Add Equipment / Technology Item', 'spicecraft-core' ); ?>
	</button>
	<?php
}

/**
 * Render repeatable structured sourcing regions.
 *
 * @param string $option_name Option name.
 * @param array  $items       Region items.
 */
function spicecraft_render_admin_region_rows( $option_name, $items = array() ) {
	?>
	<div id="sc-region-rows">
		<?php
		if ( ! empty( $items ) && is_array( $items ) ) :
			foreach ( $items as $idx => $rg ) :
				?>
				<div class="sc-repeatable-row sc-card" style="padding: 12px; margin-bottom: 10px; background: #f9f9f9; border: 1px solid #ccd0d4; border-radius: 4px;">
					<div style="display: flex; gap: 8px; margin-bottom: 8px; flex-wrap: wrap;">
						<input type="text" name="<?php echo esc_attr( $option_name ); ?>[regions][items][<?php echo esc_attr( $idx ); ?>][name]" value="<?php echo esc_attr( $rg['name'] ?? '' ); ?>" placeholder="Region (e.g. Salem)" style="flex: 2; min-width: 140px;" />
						<input type="text" name="<?php echo esc_attr( $option_name ); ?>[regions][items][<?php echo esc_attr( $idx ); ?>][state]" value="<?php echo esc_attr( $rg['state'] ?? '' ); ?>" placeholder="State/Province" style="flex: 1; min-width: 110px;" />
						<input type="text" name="<?php echo esc_attr( $option_name ); ?>[regions][items][<?php echo esc_attr( $idx ); ?>][country]" value="<?php echo esc_attr( $rg['country'] ?? '' ); ?>" placeholder="Country (e.g. India)" style="flex: 1; min-width: 90px;" />
						<input type="text" name="<?php echo esc_attr( $option_name ); ?>[regions][items][<?php echo esc_attr( $idx ); ?>][ingredient]" value="<?php echo esc_attr( $rg['ingredient'] ?? '' ); ?>" placeholder="Spice / Crop" style="flex: 1.5; min-width: 120px;" />
						<input type="number" name="<?php echo esc_attr( $option_name ); ?>[regions][items][<?php echo esc_attr( $idx ); ?>][order]" value="<?php echo esc_attr( $rg['order'] ?? 10 ); ?>" placeholder="Order" style="width: 60px;" />
						<button type="button" class="button sc-remove-row-btn">&times;</button>
					</div>
					<textarea name="<?php echo esc_attr( $option_name ); ?>[regions][items][<?php echo esc_attr( $idx ); ?>][description]" placeholder="Geographic/agro-climatic characteristics and sourcing details..." rows="2" style="width: 100%; margin-bottom: 8px;"><?php echo esc_textarea( $rg['description'] ?? '' ); ?></textarea>
					<div style="display: flex; align-items: center; gap: 12px;">
						<label style="font-size: 12px; color: #50575e;"><?php esc_html_e( 'Regional Photo:', 'spicecraft-core' ); ?></label>
						<?php
						spicecraft_render_admin_media_uploader(
							"{$option_name}[regions][items][{$idx}][image_id]",
							$rg['image_id'] ?? 0,
							'',
							__( 'Select Photo', 'spicecraft-core' )
						);
						?>
					</div>
				</div>
				<?php
			endforeach;
		endif;
		?>
	</div>
	<button type="button" class="button button-secondary" id="sc-add-region-btn" data-option-name="<?php echo esc_attr( $option_name ); ?>">
		<span class="dashicons dashicons-plus-alt2" style="margin-top: -2px;"></span>
		<?php esc_html_e( 'Add Sourcing Region', 'spicecraft-core' ); ?>
	</button>
	<?php
}

/**
 * Sanitization Primitives.
 */

function spicecraft_sanitize_order_array( $input, $valid_keys = array() ) {
	$sanitized = array();
	if ( ! is_array( $input ) ) return $sanitized;
	foreach ( $valid_keys as $k ) {
		$sanitized[ $k ] = isset( $input[ $k ] ) ? absint( $input[ $k ] ) : 100;
	}
	return $sanitized;
}

function spicecraft_sanitize_enabled_array( $input, $valid_keys = array() ) {
	$sanitized = array();
	foreach ( $valid_keys as $k ) {
		$sanitized[ $k ] = ( isset( $input[ $k ] ) && '1' === (string) $input[ $k ] ) ? 1 : 0;
	}
	return $sanitized;
}

function spicecraft_sanitize_stat_items( $items ) {
	$clean = array();
	if ( ! is_array( $items ) ) return $clean;
	foreach ( $items as $it ) {
		$val = sanitize_text_field( $it['value'] ?? '' );
		$lbl = sanitize_text_field( $it['label'] ?? '' );
		if ( empty( $val ) && empty( $lbl ) ) continue;
		$clean[] = array(
			'value'       => $val,
			'suffix'      => sanitize_text_field( $it['suffix'] ?? '' ),
			'label'       => $lbl,
			'description' => sanitize_text_field( $it['description'] ?? '' ),
			'order'       => absint( $it['order'] ?? 10 ),
		);
	}
	usort( $clean, fn( $a, $b ) => $a['order'] <=> $b['order'] );
	return $clean;
}

function spicecraft_sanitize_process_items( $items ) {
	$clean = array();
	if ( ! is_array( $items ) ) return $clean;
	foreach ( $items as $it ) {
		$title = sanitize_text_field( $it['title'] ?? '' );
		$desc  = sanitize_textarea_field( $it['description'] ?? '' );
		if ( empty( $title ) && empty( $desc ) ) continue;
		$clean[] = array(
			'step_number' => sanitize_text_field( $it['step_number'] ?? '' ),
			'title'       => $title,
			'description' => $desc,
			'image_id'    => absint( $it['image_id'] ?? 0 ),
			'icon'        => sanitize_text_field( $it['icon'] ?? '' ),
			'order'       => absint( $it['order'] ?? 10 ),
		);
	}
	usort( $clean, fn( $a, $b ) => $a['order'] <=> $b['order'] );
	return $clean;
}

function spicecraft_sanitize_equipment_items( $items ) {
	$clean = array();
	if ( ! is_array( $items ) ) return $clean;
	foreach ( $items as $it ) {
		$name = sanitize_text_field( $it['name'] ?? '' );
		$desc = sanitize_textarea_field( $it['description'] ?? '' );
		if ( empty( $name ) && empty( $desc ) ) continue;

		$specs = array();
		if ( ! empty( $it['specs'] ) && is_array( $it['specs'] ) ) {
			foreach ( $it['specs'] as $sp ) {
				$sl = sanitize_text_field( $sp['label'] ?? '' );
				$sv = sanitize_text_field( $sp['value'] ?? '' );
				if ( empty( $sl ) && empty( $sv ) ) continue;
				$specs[] = array( 'label' => $sl, 'value' => $sv );
			}
		}

		$clean[] = array(
			'name'        => $name,
			'description' => $desc,
			'image_id'    => absint( $it['image_id'] ?? 0 ),
			'specs'       => $specs,
			'order'       => absint( $it['order'] ?? 10 ),
		);
	}
	usort( $clean, fn( $a, $b ) => $a['order'] <=> $b['order'] );
	return $clean;
}

function spicecraft_sanitize_region_items( $items ) {
	$clean = array();
	if ( ! is_array( $items ) ) return $clean;
	foreach ( $items as $it ) {
		$name = sanitize_text_field( $it['name'] ?? '' );
		$desc = sanitize_textarea_field( $it['description'] ?? '' );
		if ( empty( $name ) && empty( $desc ) ) continue;
		$clean[] = array(
			'name'        => $name,
			'state'       => sanitize_text_field( $it['state'] ?? '' ),
			'country'     => sanitize_text_field( $it['country'] ?? '' ),
			'ingredient'  => sanitize_text_field( $it['ingredient'] ?? '' ),
			'description' => $desc,
			'image_id'    => absint( $it['image_id'] ?? 0 ),
			'order'       => absint( $it['order'] ?? 10 ),
		);
	}
	usort( $clean, fn( $a, $b ) => $a['order'] <=> $b['order'] );
	return $clean;
}

function spicecraft_sanitize_gallery_ids( $ids ) {
	if ( ! is_array( $ids ) ) return array();
	return array_values( array_filter( array_map( 'absint', $ids ) ) );
}
