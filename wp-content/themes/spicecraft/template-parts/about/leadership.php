<?php
/**
 * About Section: Leadership & People
 *
 * Premium portrait presentation of executive leadership, agronomists,
 * and master blenders consuming the spicecraft_team Custom Post Type.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lead_sec = function_exists( 'spicecraft_get_about_section' )
	? spicecraft_get_about_section( 'leadership' )
	: array();

if ( empty( $lead_sec ) ) {
	return;
}

$limit        = absint( $lead_sec['limit'] ?? 6 );
$selected_ids = $lead_sec['selected_ids'] ?? array();

$members = function_exists( 'spicecraft_get_about_team_members' )
	? spicecraft_get_about_team_members( $limit, $selected_ids )
	: array();

if ( empty( $members ) ) {
	return;
}

$eyebrow     = $lead_sec['eyebrow'] ?? '';
$heading     = $lead_sec['heading'] ?? '';
$description = $lead_sec['description'] ?? '';
?>

<section id="leadership-team" class="sc-about-team" aria-label="<?php echo esc_attr( ! empty( $heading ) ? $heading : __( 'Leadership & Team', 'spicecraft' ) ); ?>">
	<div class="sc-container">
		<?php if ( ! empty( $eyebrow ) || ! empty( $heading ) || ! empty( $description ) ) : ?>
			<div class="sc-section-header sc-section-header--center" style="margin-bottom: var(--sc-space-12);">
				<?php if ( ! empty( $eyebrow ) ) : ?>
					<span class="sc-section-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
				<?php endif; ?>

				<?php if ( ! empty( $heading ) ) : ?>
					<h2 class="sc-section-title"><?php echo esc_html( $heading ); ?></h2>
				<?php endif; ?>

				<?php if ( ! empty( $description ) ) : ?>
					<p class="sc-section-subtitle"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="sc-team-grid">
			<?php foreach ( $members as $member ) :
				$post_id  = $member->ID;
				$name     = get_the_title( $post_id );
				$role     = get_post_meta( $post_id, '_sc_team_role', true );
				$linkedin = get_post_meta( $post_id, '_sc_team_linkedin', true );
				$bio      = get_post_field( 'post_content', $post_id );
				?>
				<article class="sc-team-card" aria-label="<?php echo esc_attr( $name ); ?>">
					<div class="sc-team-card__media">
						<?php if ( has_post_thumbnail( $post_id ) ) : ?>
							<?php echo get_the_post_thumbnail( $post_id, 'medium_large', array( 'class' => 'sc-team-card__img', 'loading' => 'lazy' ) ); ?>
						<?php else : ?>
							<div class="sc-team-card__placeholder" aria-hidden="true">
								<span class="dashicons dashicons-businessperson"></span>
							</div>
						<?php endif; ?>
					</div>

					<div class="sc-team-card__body">
						<h3 class="sc-team-card__name"><?php echo esc_html( $name ); ?></h3>

						<?php if ( ! empty( $role ) ) : ?>
							<span class="sc-team-card__role"><?php echo esc_html( $role ); ?></span>
						<?php endif; ?>

						<?php if ( ! empty( $bio ) ) : ?>
							<p class="sc-team-card__bio"><?php echo esc_html( wp_trim_words( $bio, 28, '&hellip;' ) ); ?></p>
						<?php endif; ?>

						<?php if ( ! empty( $linkedin ) ) : ?>
							<div class="sc-team-card__social">
								<a href="<?php echo esc_url( $linkedin ); ?>" target="_blank" rel="noopener noreferrer" class="sc-team-card__linkedin" aria-label="<?php echo esc_attr( sprintf( __( 'View %s on LinkedIn', 'spicecraft' ), $name ) ); ?>">
									<span class="dashicons dashicons-networking" aria-hidden="true"></span>
									<span>LinkedIn</span>
								</a>
							</div>
						<?php endif; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
