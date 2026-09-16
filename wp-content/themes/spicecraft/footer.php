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

<?php wp_footer(); ?>

</body>
</html>
