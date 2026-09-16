<?php
require_once dirname( __DIR__ ) . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

$images = array(
    'hero' => array(
        'path'  => 'C:/Users/kumar/.gemini/antigravity-ide/brain/68126d3d-7856-46a1-bbb4-09a5e2aae306/hero_spicecraft_showcase_1789557178352.jpg',
        'title' => 'Master Artisanal Spices Showcase',
        'alt'   => 'Artisanal Indian whole spices and ground powders in traditional brass bowls',
    ),
    'facility' => array(
        'path'  => 'C:/Users/kumar/.gemini/antigravity-ide/brain/68126d3d-7856-46a1-bbb4-09a5e2aae306/spice_facility_cleanroom_1789557196777.jpg',
        'title' => 'SpiceCraft Cleanroom Processing Facility',
        'alt'   => 'Automated cryogenic spice grinding and hygienic packaging infrastructure',
    ),
    'sourcing' => array(
        'path'  => 'C:/Users/kumar/.gemini/antigravity-ide/brain/68126d3d-7856-46a1-bbb4-09a5e2aae306/spice_harvesting_origin_1789557212782.jpg',
        'title' => 'Single Origin Kerala Farm Sourcing',
        'alt'   => 'Traditional sun-drying and meticulous hand-grading of green cardamom and black pepper',
    ),
);

$upload_dir = wp_upload_dir();
$registered = array();

foreach ( $images as $key => $data ) {
    if ( ! file_exists( $data['path'] ) ) {
        echo "File not found: " . $data['path'] . "\n";
        continue;
    }

    $filename = basename( $data['path'] );
    $dest = $upload_dir['path'] . '/' . $filename;
    copy( $data['path'], $dest );

    $filetype = wp_check_filetype( $filename, null );
    $attachment = array(
        'guid'           => $upload_dir['url'] . '/' . $filename,
        'post_mime_type' => $filetype['type'],
        'post_title'     => $data['title'],
        'post_content'   => '',
        'post_status'    => 'inherit',
    );

    $attach_id = wp_insert_attachment( $attachment, $dest );
    $attach_data = wp_generate_attachment_metadata( $attach_id, $dest );
    wp_update_attachment_metadata( $attach_id, $attach_data );
    update_post_meta( $attach_id, '_wp_attachment_image_alt', $data['alt'] );

    $registered[ $key ] = $attach_id;
    echo "Registered {$key} as Attachment ID: {$attach_id}\n";
}

// Update homepage settings with these legitimate attachment IDs so the admin controls have real media
$hp_settings = get_option( 'spicecraft_homepage_settings', array() );
if ( ! empty( $registered['hero'] ) ) {
    $hp_settings['hero']['desktop_image_id'] = $registered['hero'];
    $hp_settings['hero']['mobile_image_id']  = $registered['hero'];
    $hp_settings['hero']['image_alt']        = 'Artisanal Indian Spices';
    $hp_settings['hero']['badge_text']       = '100% Origin Guaranteed';
    $hp_settings['hero']['highlight_text']   = 'Master Crafted';
    $hp_settings['hero']['primary_cta_label'] = 'Explore Spice Catalog';
    $hp_settings['hero']['secondary_cta_label'] = 'Institutional Enquiries';
}
if ( ! empty( $registered['sourcing'] ) ) {
    $hp_settings['quality_sourcing']['main_image_id'] = $registered['sourcing'];
}
if ( ! empty( $registered['facility'] ) ) {
    $hp_settings['manufacturing']['main_image_id'] = $registered['facility'];
}
update_option( 'spicecraft_homepage_settings', $hp_settings );
echo "Updated spicecraft_homepage_settings with registered media IDs!\n";
