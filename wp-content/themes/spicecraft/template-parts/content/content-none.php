<?php
/**
 * Template part for displaying a message that posts cannot be found
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<section class="no-results not-found sc-card" style="padding: var(--sc-space-8); text-align: center;">
	<header class="page-header">
		<h1 class="page-title"><?php esc_html_e( 'Nothing Found', 'spicecraft' ); ?></h1>
	</header>

	<div class="page-content" style="max-width: 600px; margin: 0 auto;">
		<?php
		if ( is_home() && current_user_can( 'publish_posts' ) ) :
			printf(
				'<p>' . wp_kses(
					/* translators: 1: link to WP admin new post page. */
					__( 'Ready to publish your first post? <a href="%1$s">Get started here</a>.', 'spicecraft' ),
					array(
						'a' => array(
							'href' => array(),
						),
					)
				) . '</p>',
				esc_url( admin_url( 'post-new.php' ) )
			);
		elseif ( is_search() ) :
			?>
			<p><?php esc_html_e( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'spicecraft' ); ?></p>
			<?php
			get_search_form();
		else :
			?>
			<p><?php esc_html_e( 'It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'spicecraft' ); ?></p>
			<?php
			get_search_form();
		endif;
		?>
	</div>
</section>
