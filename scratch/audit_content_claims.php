<?php
/**
 * SpiceCraft - Content Claim Audit & Sanitization Script
 *
 * Removes fabricated/invented claims from the homepage CMS options.
 * Complies with Phase 2 Step 2B Content Claim Audit rules:
 * - Retain genuine administrator-configured CMS/product/certification data
 * - Remove invented/hard-coded claims without inventing replacements
 * - Allow template parts to gracefully suppress empty elements
 */

require_once __DIR__ . '/../wp-load.php';

$settings = get_option( 'spicecraft_homepage_settings', array() );

$audit_log = array();

// 1. Hero Badge Text ("100% Origin Guaranteed")
if ( ! empty( $settings['hero']['badge_text'] ) && stripos( $settings['hero']['badge_text'], 'Origin Guaranteed' ) !== false ) {
    $audit_log[] = 'Removed fabricated hero badge claim: "' . $settings['hero']['badge_text'] . '"';
    $settings['hero']['badge_text'] = '';
}

// 2. Brand Story Heritage Stats ("35+ Years of Heritage Sourcing")
if ( ! empty( $settings['brand_story']['stat_value'] ) || ! empty( $settings['brand_story']['stat_label'] ) ) {
    $audit_log[] = 'Removed fabricated brand story stat: "' . ($settings['brand_story']['stat_value'] ?? '') . ' ' . ($settings['brand_story']['stat_label'] ?? '') . '"';
    $settings['brand_story']['stat_value'] = '';
    $settings['brand_story']['stat_label'] = '';
}

// 3. Manufacturing Facility Stats ("25,000 MT Annual Capacity", "SS 316", "< 40°C", "100% Batch COA Tested")
if ( ! empty( $settings['manufacturing']['stats'] ) ) {
    foreach ( $settings['manufacturing']['stats'] as $st ) {
        $audit_log[] = 'Removed fabricated manufacturing stat: "' . ($st['value'] ?? '') . ' - ' . ($st['label'] ?? '') . '"';
    }
    $settings['manufacturing']['stats'] = array();
}

// 4. Why Choose Us Items: Remove invented "Cryogenic Cold Milled" differentiator
if ( ! empty( $settings['why_choose_us']['items'] ) && is_array( $settings['why_choose_us']['items'] ) ) {
    $filtered_items = array();
    foreach ( $settings['why_choose_us']['items'] as $item ) {
        if ( stripos( $item['title'], 'Cryogenic' ) !== false ) {
            $audit_log[] = 'Removed fabricated differentiator from Why Choose Us: "' . $item['title'] . '"';
            continue;
        }
        $filtered_items[] = $item;
    }
    $settings['why_choose_us']['items'] = $filtered_items;
}

update_option( 'spicecraft_homepage_settings', $settings );

echo "=== CONTENT CLAIM AUDIT SANITIZATION ===\n\n";
if ( empty( $audit_log ) ) {
    echo "[i] No fabricated claims found in spicecraft_homepage_settings.\n";
} else {
    foreach ( $audit_log as $msg ) {
        echo "[x] " . $msg . "\n";
    }
    echo "\n[+] Successfully updated spicecraft_homepage_settings in database.\n";
}
