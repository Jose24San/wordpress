<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'coupon');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'root');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '9_xw8@#o{~1]firrGzd36h(8``)^jXM>^E4%6l(v(WmbE0cnBBsfG;-K75T5(#d.');
define('SECURE_AUTH_KEY',  '( N;v1+f3A*dm3MutZ4gjy<$wWv5`H#AQc!oLRl>c@(dVsq}aig]SJ(%Iu:XHyR5');
define('LOGGED_IN_KEY',    'C1+:`J2n.galK-[wD{)%iRKR4Ov7(@LsM$r:MM|nZ<DI_n}#x}n._vytQ+{r<X2=');
define('NONCE_KEY',        'BYo?H%G!c(t_Fj*+8jJs<R0dA.yrO*^%c26i2eVk.|t.{[>NCDb?o AE1=kV3-U.');
define('AUTH_SALT',        'xHW{/fTx0+(]o(raIZRF._/RZ&be;j>pTZ5.:NWAPfYO$P}EtI@10.o52YUu[#J>');
define('SECURE_AUTH_SALT', 'Y&gU]TPh:W:%S^_8bvh]2=ZT$#-pTMv-2XwqiKhzXsw/8Siaj|,`L[>2Zj?[-{Hf');
define('LOGGED_IN_SALT',   '=kuJ_gLm4.io^#6eQ_$25<;x!($*wti?,t!>zW)Q|+<Fs2K{-UNk&D$zusJt|L_9');
define('NONCE_SALT',       '3i|],PKC9}eSi+Q(t(1/ |o6Kq!fs[8PcGovuqZh)#X(7b<#<K;c^c4VTPL!Q.A=');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
