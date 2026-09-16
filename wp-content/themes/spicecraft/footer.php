<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #primary div and all content up to </html>
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
</main><!-- #primary -->

<footer id="colophon" class="site-footer">
	<?php get_template_part( 'template-parts/footer/site-footer' ); ?>
</footer>

<!-- Floating Scroll-to-Top Button -->
<button type="button" id="sc-scroll-top" class="sc-scroll-top" aria-label="<?php esc_attr_e( 'Back to top', 'spicecraft' ); ?>">
	<svg class="sc-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
		<polyline points="18 15 12 9 6 15"></polyline>
	</svg>
</button>

<?php wp_footer(); ?>

</body>
</html>
