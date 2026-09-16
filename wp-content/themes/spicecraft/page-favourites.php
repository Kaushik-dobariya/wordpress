<?php
/**
 * Template Name: SpiceCraft Favourites
 *
 * Dedicated page template for viewing and managing saved favourite spice products.
 * Powered by browser localStorage (spicecraft_favourites) and dynamic AJAX cards.
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="sc-container sc-woocommerce-container">
	<div class="sc-woocommerce-content sc-favourites-page-content">

		<!-- Breadcrumb Navigation -->
		<nav class="sc-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'spicecraft' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'spicecraft' ); ?></a>
			<span class="sc-breadcrumb__sep" aria-hidden="true"> / </span>
			<?php if ( function_exists( 'wc_get_page_permalink' ) ) : ?>
				<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Catalog', 'spicecraft' ); ?></a>
				<span class="sc-breadcrumb__sep" aria-hidden="true"> / </span>
			<?php endif; ?>
			<span class="sc-breadcrumb__item" aria-current="page"><?php esc_html_e( 'Favourites', 'spicecraft' ); ?></span>
		</nav>

		<?php
		// Render favourites UI
		echo do_shortcode( '[spicecraft_favourites]' );
		?>

	</div><!-- .sc-woocommerce-content -->
</div><!-- .sc-container -->

<?php
get_footer();
