<?php
/**
 * About Section: Vision & Mission
 *
 * Paired architectural composition presenting corporate vision and manufacturing mission
 * side-by-side with high typographic dignity.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$vm = function_exists( 'spicecraft_get_about_section' )
	? spicecraft_get_about_section( 'vision_mission' )
	: array();

if ( empty( $vm ) ) {
	return;
}

$eyebrow         = $vm['eyebrow'] ?? '';
$heading         = $vm['heading'] ?? '';
$description     = $vm['description'] ?? '';
$vision_enabled  = ! empty( $vm['vision_enabled'] );
$vision_heading  = $vm['vision_heading'] ?? '';
$vision_content  = $vm['vision_content'] ?? '';
$mission_enabled = ! empty( $vm['mission_enabled'] );
$mission_heading = $vm['mission_heading'] ?? '';
$mission_content = $vm['mission_content'] ?? '';

// Check if at least one pillar has content
$has_vision  = $vision_enabled && ( ! empty( $vision_heading ) || ! empty( $vision_content ) );
$has_mission = $mission_enabled && ( ! empty( $mission_heading ) || ! empty( $mission_content ) );

if ( ! $has_vision && ! $has_mission ) {
	return;
}
?>

<section id="vision-mission" class="sc-about-vm" aria-label="<?php echo esc_attr( ! empty( $heading ) ? $heading : __( 'Vision & Mission', 'spicecraft' ) ); ?>">
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

		<div class="sc-about-vm__grid <?php echo ( ! $has_vision || ! $has_mission ) ? 'sc-about-vm__grid--single' : ''; ?>">
			<?php if ( $has_vision ) : ?>
				<div class="sc-about-vm__pillar sc-about-vm__pillar--vision">
					<div class="sc-about-vm__tag"><?php esc_html_e( 'Our Purpose', 'spicecraft' ); ?></div>
					<h3 class="sc-about-vm__pillar-title">
						<?php echo esc_html( ! empty( $vision_heading ) ? $vision_heading : __( 'Our Vision', 'spicecraft' ) ); ?>
					</h3>
					<?php if ( ! empty( $vision_content ) ) : ?>
						<p class="sc-about-vm__pillar-text"><?php echo nl2br( esc_html( $vision_content ) ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( $has_mission ) : ?>
				<div class="sc-about-vm__pillar sc-about-vm__pillar--mission">
					<div class="sc-about-vm__tag"><?php esc_html_e( 'Our Commitment', 'spicecraft' ); ?></div>
					<h3 class="sc-about-vm__pillar-title">
						<?php echo esc_html( ! empty( $mission_heading ) ? $mission_heading : __( 'Our Mission', 'spicecraft' ) ); ?>
					</h3>
					<?php if ( ! empty( $mission_content ) ) : ?>
						<p class="sc-about-vm__pillar-text"><?php echo nl2br( esc_html( $mission_content ) ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
