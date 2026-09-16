<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="sc-container sc-container--narrow">
	<section class="error-404 not-found sc-card" style="padding: var(--sc-space-12) var(--sc-space-6); text-align: center; margin: var(--sc-space-8) auto;">
		<span style="font-size: 4rem; display: block; margin-bottom: var(--sc-space-2);">🌶️</span>
		<h1 class="page-title" style="font-size: 3rem; margin-bottom: var(--sc-space-2);"><?php esc_html_e( '404 - Page Not Found', 'spicecraft' ); ?></h1>
		<p class="sc-lead" style="color: var(--sc-color-text-muted); font-size: 1.15rem; max-width: 540px; margin: 0 auto var(--sc-space-6);">
			<?php esc_html_e( 'The page you are looking for might have been moved, renamed, or is temporarily unavailable.', 'spicecraft' ); ?>
		</p>

		<div style="max-width: 480px; margin: 0 auto var(--sc-space-8);">
			<?php get_search_form(); ?>
		</div>

		<div>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="sc-btn sc-btn--primary">
				&larr; <?php esc_html_e( 'Return to Homepage', 'spicecraft' ); ?>
			</a>
		</div>
	</section>
</div>

<?php
get_footer();
