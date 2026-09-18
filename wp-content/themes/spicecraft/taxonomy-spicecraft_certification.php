<?php
/**
 * Taxonomy Template: SpiceCraft Certification Term Detail
 *
 * Dedicated public detail experience for an individual verified certification.
 * Renders comprehensive compliance, authority, validity, scope, documents,
 * and related products. Handles thin-record redirects and internal privacy controls.
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$term = get_queried_object();
if ( ! ( $term instanceof WP_Term ) ) {
	global $wp_query;
	$wp_query->set_404();
	status_header( 404 );
	get_template_part( 404 );
	exit;
}

$term_id = $term->term_id;

// Privacy Enforcement: Internal / Admin Only records must NOT be accessible on frontend
if ( function_exists( 'spicecraft_is_certification_public' ) && ! spicecraft_is_certification_public( $term_id ) ) {
	$archive_page = get_page_by_path( 'certifications' );
	$redirect_url = $archive_page ? get_permalink( $archive_page->ID ) : home_url( '/certifications/' );
	wp_safe_redirect( $redirect_url, 302 );
	exit;
}

// Thin Certification Rule: If dedicated detail page is disabled, redirect to main archive
if ( function_exists( 'spicecraft_has_certification_public_detail' ) && ! spicecraft_has_certification_public_detail( $term_id ) ) {
	$archive_page = get_page_by_path( 'certifications' );
	$redirect_url = $archive_page ? get_permalink( $archive_page->ID ) : home_url( '/certifications/' );
	wp_safe_redirect( $redirect_url, 302 );
	exit;
}

get_header();

$meta             = function_exists( 'spicecraft_get_certification_meta' ) ? spicecraft_get_certification_meta( $term_id ) : array();
$effective_status = function_exists( 'spicecraft_get_certification_effective_status' ) ? spicecraft_get_certification_effective_status( $term_id ) : ( $meta['status'] ?? 'active' );
$status_label     = function_exists( 'spicecraft_get_certification_status_label' ) ? spicecraft_get_certification_status_label( $effective_status ) : ucfirst( $effective_status );
$global_settings  = function_exists( 'spicecraft_get_certification_settings' ) ? spicecraft_get_certification_settings() : array();
$archive_page     = get_page_by_path( 'certifications' );
$archive_url      = $archive_page ? get_permalink( $archive_page->ID ) : home_url( '/certifications/' );
?>

<main id="primary" class="site-main sc-cert-detail-main">
	<!-- Top Breadcrumbs Navigation -->
	<nav class="sc-cert-breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'spicecraft' ); ?>">
		<div class="sc-container">
			<ol class="sc-breadcrumbs-list" itemscope itemtype="https://schema.org/BreadcrumbList">
				<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" itemprop="item"><span itemprop="name"><?php esc_html_e( 'Home', 'spicecraft' ); ?></span></a>
					<meta itemprop="position" content="1" />
					<span class="sc-sep" aria-hidden="true">/</span>
				</li>
				<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
					<a href="<?php echo esc_url( $archive_url ); ?>" itemprop="item"><span itemprop="name"><?php esc_html_e( 'Certifications', 'spicecraft' ); ?></span></a>
					<meta itemprop="position" content="2" />
					<span class="sc-sep" aria-hidden="true">/</span>
				</li>
				<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" aria-current="page">
					<span itemprop="name"><?php echo esc_html( $term->name ); ?></span>
					<meta itemprop="position" content="3" />
				</li>
			</ol>
		</div>
	</nav>

	<article class="sc-cert-detail-article">
		<?php
		// 1. Detail Hero (Logo, Name, Short Name, Status Badge, Authority)
		get_template_part( 'template-parts/certifications/detail-hero', null, array( 'term' => $term, 'meta' => $meta, 'status' => $effective_status, 'status_label' => $status_label ) );
		?>

		<div class="sc-container sc-cert-detail-body">
			<div class="sc-cert-detail-grid">
				<div class="sc-cert-detail-content-col">
					<?php
					// 2. Scope & Facilities
					get_template_part( 'template-parts/certifications/detail-scope', null, array( 'term' => $term, 'meta' => $meta ) );

					// 3. Related Products & Categories
					get_template_part( 'template-parts/certifications/detail-products', null, array( 'term' => $term, 'meta' => $meta ) );
					?>
				</div>

				<aside class="sc-cert-detail-sidebar-col">
					<?php
					// 4. Certificate Specification / Information Table
					get_template_part( 'template-parts/certifications/detail-overview', null, array( 'term' => $term, 'meta' => $meta, 'status' => $effective_status, 'status_label' => $status_label ) );

					// 5. Certificate Image & Public Document View/Download
					get_template_part( 'template-parts/certifications/detail-documents', null, array( 'term' => $term, 'meta' => $meta ) );

					// 6. Official Verification Link
					get_template_part( 'template-parts/certifications/detail-verification', null, array( 'term' => $term, 'meta' => $meta ) );
					?>

					<!-- Cross Navigation Links -->
					<div class="sc-cert-sidebar-card sc-cert-crosslinks-card">
						<h3 class="sc-cert-card-title"><?php esc_html_e( 'Related Standards', 'spicecraft' ); ?></h3>
						<ul class="sc-cert-crosslinks-list">
							<li>
								<a href="<?php echo esc_url( home_url( '/quality-sourcing/' ) ); ?>">
									<span class="dashicons dashicons-shield"></span>
									<span><?php esc_html_e( 'Quality & Sourcing Protocols', 'spicecraft' ); ?></span>
								</a>
							</li>
							<li>
								<a href="<?php echo esc_url( home_url( '/manufacturing/' ) ); ?>">
									<span class="dashicons dashicons-admin-multisite"></span>
									<span><?php esc_html_e( 'Manufacturing Capabilities', 'spicecraft' ); ?></span>
								</a>
							</li>
							<li>
								<a href="<?php echo esc_url( $archive_url ); ?>">
									<span class="dashicons dashicons-awards"></span>
									<span><?php esc_html_e( 'All Accreditations', 'spicecraft' ); ?></span>
								</a>
							</li>
						</ul>
					</div>
				</aside>
			</div>
		</div>

		<?php
		// 7. Commercial & Compliance CTA
		get_template_part( 'template-parts/certifications/final-cta' );
		?>
	</article>
</main>

<?php
get_footer();
