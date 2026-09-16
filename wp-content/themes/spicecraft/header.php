<?php
/**
 * The header for our theme
 *
 * Displays all of the <head> section and everything up till <main id="primary">
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'spicecraft' ); ?></a>

<header id="masthead" class="site-header">
	<?php get_template_part( 'template-parts/header/site-nav' ); ?>
</header>

<main id="primary" class="site-main">
