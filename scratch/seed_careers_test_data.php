<?php
/**
 * Seed Careers Test Data with correct _sc_job_ prefix
 */

require_once __DIR__ . '/../wp-load.php';

echo "=== Seeding Careers Test Data with _sc_job_ prefix ===\n";

// Ensure terms exist
$dept_rd = wp_insert_term('R&D & Quality', 'spicecraft_department');
if (is_wp_error($dept_rd)) {
    $dept_rd = get_term_by('name', 'R&D & Quality', 'spicecraft_department');
    $dept_rd_id = $dept_rd->term_id;
} else {
    $dept_rd_id = $dept_rd['term_id'];
}

$dept_ops = wp_insert_term('Supply Chain & Operations', 'spicecraft_department');
if (is_wp_error($dept_ops)) {
    $dept_ops = get_term_by('name', 'Supply Chain & Operations', 'spicecraft_department');
    $dept_ops_id = $dept_ops->term_id;
} else {
    $dept_ops_id = $dept_ops['term_id'];
}

// 1. Active Job: Senior Food Technologist
$job1_title = 'Senior Food Technologist — Cryo-Milling & Blending';
$job1_slug  = 'senior-food-technologist';

$existing1 = get_page_by_path($job1_slug, OBJECT, 'spicecraft_job');
$job1_id = $existing1 ? $existing1->ID : 0;

$job1_args = [
    'post_title'   => $job1_title,
    'post_name'    => $job1_slug,
    'post_status'  => 'publish',
    'post_type'    => 'spicecraft_job',
    'post_content' => '<p>At SpiceCraft, our state-of-the-art cryogenic milling facility preserves delicate volatile aromatic oils that conventional grinding destroys. We are looking for an experienced Senior Food Technologist to lead milling optimization, batch formulation, and sensory consistency across our export-grade spice portfolio.</p><p>You will work closely with plant managers, procurement specialists, and our in-house microbiological laboratory to validate high-retention spice processing parameters.</p>',
];

if ($job1_id) {
    $job1_args['ID'] = $job1_id;
    wp_update_post($job1_args);
    echo "Updated Job 1 (ID: $job1_id)\n";
} else {
    $job1_id = wp_insert_post($job1_args);
    echo "Created Job 1 (ID: $job1_id)\n";
}

wp_set_post_terms($job1_id, [$dept_rd_id], 'spicecraft_department');

update_post_meta($job1_id, '_sc_job_department', 'R&D & Quality');
update_post_meta($job1_id, '_sc_job_location', 'Ahmedabad, Gujarat (Headquarters)');
update_post_meta($job1_id, '_sc_job_type', 'full_time');
update_post_meta($job1_id, '_sc_job_experience', '4–7 Years');
update_post_meta($job1_id, '_sc_job_openings', '2');
update_post_meta($job1_id, '_sc_job_featured', '1');
update_post_meta($job1_id, '_sc_job_status', 'published');
update_post_meta($job1_id, '_sc_job_deadline', '2026-11-30');
update_post_meta($job1_id, '_sc_job_summary', 'Lead development of cryogenic milling parameters and volatile oil retention protocols for premium export whole and ground spice formulations.');

update_post_meta($job1_id, '_sc_job_responsibilities', [
    'Develop optimized cryogenic grinding parameters for high-oil spices (black pepper, cardamom, coriander).',
    'Establish quality parameters for volatile oil retention, moisture threshold, and granulation mesh sizes.',
    'Collaborate with the Quality Control team to audit batch-to-batch organoleptic and microbiological consistency.',
    'Lead pilot trials on modified atmosphere packaging (MAP) for high-potency spice extracts.'
]);

update_post_meta($job1_id, '_sc_job_qualifications', [
    'B.Tech / M.Sc in Food Technology, Food Engineering, or Applied Chemistry.',
    'Minimum 4 years of hands-on experience in dry milling, spice processing, or food ingredient R&D.'
]);

update_post_meta($job1_id, '_sc_job_preferred_qualifications', [
    'Demonstrated expertise in gas chromatography (GC-MS) volatile aromatic oil quantification.',
    'Familiarity with export standards: US FDA, EU pesticide limits, and ASTA cleanliness specifications.'
]);

update_post_meta($job1_id, '_sc_job_skills', [
    'Cryogenic Grinding Optimization',
    'Essential Oil Analysis (Clevenger distillation)',
    'Sensory & Organoleptic Profiling',
    'HACCP & BRCGS Quality Protocols'
]);

update_post_meta($job1_id, '_sc_job_benefits', [
    'Comprehensive family health coverage and wellness stipends.',
    'Annual performance incentive and research publication bonus.',
    'Modern R&D pilot plant with access to advanced analytical test benches.',
    'Annual continuing education and food industry conference sponsorship.'
]);

// 2. Closed / Expired Job: Logistics & Export Coordinator
$job2_title = 'Logistics & Export Coordinator';
$job2_slug  = 'logistics-export-coordinator';

$existing2 = get_page_by_path($job2_slug, OBJECT, 'spicecraft_job');
$job2_id = $existing2 ? $existing2->ID : 0;

$job2_args = [
    'post_title'   => $job2_title,
    'post_name'    => $job2_slug,
    'post_status'  => 'publish',
    'post_type'    => 'spicecraft_job',
    'post_content' => '<p>Manage containerized spice export documentation, phytosanitary certificates, and ocean freight logistics across Mundra and Pipavav ports.</p>',
];

if ($job2_id) {
    $job2_args['ID'] = $job2_id;
    wp_update_post($job2_args);
    echo "Updated Job 2 (ID: $job2_id)\n";
} else {
    $job2_id = wp_insert_post($job2_args);
    echo "Created Job 2 (ID: $job2_id)\n";
}

wp_set_post_terms($job2_id, [$dept_ops_id], 'spicecraft_department');

update_post_meta($job2_id, '_sc_job_department', 'Supply Chain & Operations');
update_post_meta($job2_id, '_sc_job_location', 'Mundra Port / Ahmedabad');
update_post_meta($job2_id, '_sc_job_type', 'full_time');
update_post_meta($job2_id, '_sc_job_experience', '2–4 Years');
update_post_meta($job2_id, '_sc_job_openings', '1');
update_post_meta($job2_id, '_sc_job_featured', '0');
update_post_meta($job2_id, '_sc_job_status', 'closed'); // Explicitly closed
update_post_meta($job2_id, '_sc_job_deadline', '2026-08-01'); // Past deadline
update_post_meta($job2_id, '_sc_job_summary', 'Coordinate ocean freight forwarding, port customs documentation, and phytosanitary clearance for containerized bulk spice shipments.');

update_post_meta($job2_id, '_sc_job_responsibilities', [
    'Prepare export invoices, packing lists, and certificates of origin.',
    'Liaise with customs brokers and port terminal operators.'
]);

update_post_meta($job2_id, '_sc_job_qualifications', [
    'Bachelor degree in Commerce, Logistics, or Supply Chain Management.',
    '2+ years in export documentation for agricultural or food products.'
]);

echo "=== Seeding Complete ===\n";
