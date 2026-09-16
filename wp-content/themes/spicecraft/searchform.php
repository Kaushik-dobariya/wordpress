<?php
/**
 * The template for displaying the search form
 *
 * Fully accessible HTML5 search form supporting screen readers, keyboard navigation,
 * and high-contrast focus indicators.
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$unique_id = wp_unique_id( 'sc-search-form-' );
?>

<form role="search" method="get" class="sc-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label for="<?php echo esc_attr( $unique_id ); ?>" class="screen-reader-text">
		<?php esc_html_e( 'Search spices, products, and articles', 'spicecraft' ); ?>
	</label>
	<div class="sc-search-form__inner" style="position: relative; display: flex; align-items: center; width: 100%;">
		<input 
			type="search" 
			id="<?php echo esc_attr( $unique_id ); ?>" 
			class="sc-search-field" 
			placeholder="<?php echo esc_attr_x( 'Search whole spices, ground powders, or pages...', 'placeholder', 'spicecraft' ); ?>" 
			value="<?php echo get_search_query(); ?>" 
			name="s" 
			required
			style="width: 100%; padding: var(--sc-space-3) var(--sc-space-12) var(--sc-space-3) var(--sc-space-4); border: 2px solid var(--sc-color-border, #d1d5db); border-radius: var(--sc-radius-full, 9999px); font-size: 1rem; outline: none; transition: border-color 0.2s;"
		/>
		<button 
			type="submit" 
			class="sc-search-submit" 
			aria-label="<?php esc_attr_e( 'Submit Search', 'spicecraft' ); ?>"
			style="position: absolute; right: 6px; top: 50%; transform: translateY(-50%); background: var(--sc-color-primary, #2b7a78); border: none; width: 36px; height: 36px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; color: #ffffff; cursor: pointer; transition: background-color 0.2s;"
		>
			<svg class="sc-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
				<circle cx="11" cy="11" r="8"/>
				<line x1="21" y1="21" x2="16.65" y2="16.65"/>
			</svg>
			<span class="screen-reader-text"><?php esc_html_e( 'Search', 'spicecraft' ); ?></span>
		</button>
	</div>
</form>
