<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'raabiha_db' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', '127.0.0.1' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', 'utf8mb4_unicode_ci' );

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
define( 'AUTH_KEY',          ':%I2!T5?:nIumQIO~U<)SAqJybU9v.VTb=>~6mm^9| Re(ToUrI]MXUe])u [o^W' );
define( 'SECURE_AUTH_KEY',   'coJ)#oA`$7P2kp;Jfhl^ta/Z;5?qdVwI~D29Z}+a6dzh&O8nWXB[);C !UCtWW&`' );
define( 'LOGGED_IN_KEY',     'Lr`m(gD$^L*!{h;D)$[@xHTC!:d+h3!XD0Yx@nQ/Fz#PA}{Ie8Q+OeX,;8)cjtE5' );
define( 'NONCE_KEY',         'B4`f!-1&#^>nN:_Xf()b-%(&!GqL7C=Qo|MmER3JQaL>@)C&_H4g}}SZ)3(kPRJ(' );
define( 'AUTH_SALT',         'oO1C:P}~:JN$)r>RNvf8xA,7J_5bsp~j e)?jF2F}!tR];]/c9^*s(%p(xTB>N>q' );
define( 'SECURE_AUTH_SALT',  '5`=:NG/3[j.UKXak3yc?RNsC;-nv:C/QMyIR@yE5pf_|rgv^89&ThD$#{>J TW/K' );
define( 'LOGGED_IN_SALT',    'omQ= `l+)Iz.]0~,JGWJ*cTSI68t:eT?{n8z@6g^N(/nmp(gUE`HKw9Q%[ZNiZ>4' );
define( 'NONCE_SALT',        '0J?,+`6Xr7cGU5H0bS}MSb{p/D&q$PiyJ#)..`Bg#mLt}DMg6Y*S^7!<S<}asi><' );
define( 'WP_CACHE_KEY_SALT', 'r]T|Gn2+>na.6-{yJTd&(+zvs|8*kVT//~e{|dz_wpx,~cs_xGST!S,HP`u>30WN' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'raa_';


/* Add any custom values between this line and the "stop editing" line. */

define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
@ini_set( 'display_errors', 0 );
define( 'WP_MEMORY_LIMIT', '256M' );
define( 'SCRIPT_DEBUG', false );
define( 'WP_POST_REVISIONS', 5 );
define( 'AUTOSAVE_INTERVAL', 120 );
define( 'WP_AUTO_UPDATE_CORE', 'minor' );
define( 'FS_METHOD', 'direct' );


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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
