<?php
/**
 * The template for displaying search results pages
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="sc-container">
	<header class="page-header" style="margin-bottom: var(--sc-space-8); text-align: center;">
		<h1 class="page-title">
			<?php
			/* translators: %s: search query. */
			printf( esc_html__( 'Search Results for: %s', 'spicecraft' ), '<span style="color: var(--sc-color-primary);">' . get_search_query() . '</span>' );
			?>
		</h1>
	</header>

	<div class="sc-content-area">
		<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/content/content', 'search' );
			endwhile;

			the_posts_pagination(
				array(
					'prev_text' => '&larr; ' . esc_html__( 'Previous', 'spicecraft' ),
					'next_text' => esc_html__( 'Next', 'spicecraft' ) . ' &rarr;',
				)
			);
		else :
			get_template_part( 'template-parts/content/content', 'none' );
		endif;
		?>
	</div>
</div>

<?php
get_footer();
