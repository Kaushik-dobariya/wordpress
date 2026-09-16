<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'spicescms' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '+56<7lqNxU#^+IgOo+DW`W@ h=RdN>L|*-t j}Jha^wnu)R:vo+=/0LE+!@t:Q|F' );
define( 'SECURE_AUTH_KEY',  'Hgz**|-0TLN<kj4GbKNeyX@C3aXzJR7{!uI]zaUB?1L7!/>ZvGwWeE&e}xO7tB0f' );
define( 'LOGGED_IN_KEY',    '{.{;VkAp`|*NWfZ$(kC~c@]>:<icGM<GI~0ImZr?M4)e|ScxTRAPB#-YYOlGCoJD' );
define( 'NONCE_KEY',        '0<SL 1kOI+-lG`,a7gFR:%-J{GcwfxdHla*YpxS,0Q@I X-.(x]bM}r0)p]|dI26' );
define( 'AUTH_SALT',        '=m*J$7))}&j+U+<Xf^)s3&p^:f!i3sUU#A FjWGXOG7Rde;{z0Cg$2[f33[+|u:_' );
define( 'SECURE_AUTH_SALT', '3AkgM6ji9D`um  +.fqK!gJ$m0?P-5I`Sq&apyPUhW8hicNJ.D}.;/= X3*P_k{i' );
define( 'LOGGED_IN_SALT',   '&H_v F#Qf^y|HV#y.Wja+Y1 ~Am`~YrITD2%$<L?ye8:.}})T-G6^?a3|i]]z4ro' );
define( 'NONCE_SALT',       'K)$=I)OX3R]/Lc*SNrp!JJ(sTDncG]S;z2o(:BzZGLq_.z5v6T6QhJeW;)h?UmZc' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'sc_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
