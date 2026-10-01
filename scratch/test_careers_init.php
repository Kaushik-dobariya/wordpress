<?php
require_once __DIR__ . '/../wp-load.php';

echo "=== CAREERS INITIALIZATION TEST ===\n";

// 1. Check CPTs
$has_job_cpt = post_type_exists( 'spicecraft_job' );
$has_app_cpt = post_type_exists( 'spicecraft_app' );
$has_dept_tax = taxonomy_exists( 'spicecraft_department' );

echo "Job CPT exists: " . ( $has_job_cpt ? "YES" : "NO" ) . "\n";
echo "Application CPT exists: " . ( $has_app_cpt ? "YES" : "NO" ) . "\n";
echo "Department Taxonomy exists: " . ( $has_dept_tax ? "YES" : "NO" ) . "\n";

// 2. Check Careers Profile Email
$profile_email = spicecraft_get_careers_profile_email();
echo "Careers Profile Receiving Email: " . $profile_email . "\n";
echo "Matches default career@cubeontechs.com: " . ( 'career@cubeontechs.com' === $profile_email ? "YES" : "NO" ) . "\n";

// 3. Flush rewrite rules cleanly
flush_rewrite_rules( false );
echo "Rewrite rules flushed successfully.\n";

$archive_link = get_post_type_archive_link( 'spicecraft_job' );
echo "Careers Archive Link: " . $archive_link . "\n";
