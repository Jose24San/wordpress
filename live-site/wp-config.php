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
define('DB_NAME', 'wordpr251_testwp');

/** MySQL database username */
define('DB_USER', 'wordpr251_testwp');

/** MySQL database password */
define('DB_PASSWORD', '&Laq@.yH(T!q');

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
define('AUTH_KEY',         '#3CtSOf;&X3~=;z,dKn2iw)^t*T#HkAV2ocfK{6*K<c:%dp<#ag5GoDU$y*k&rJg');
define('SECURE_AUTH_KEY',  'F&n}?P(E)%67fi5nS8j60KZwxx?dFb$Cbv/&<S;w=05e{TGYpuX-/Ph%hfi2*ujm');
define('LOGGED_IN_KEY',    '*hWsK}h<<zvXMVV@t<v{oJIVx%8L$2$1M2;$QU1+kG3q1s8v4D%QTSpC+sM]%HR+');
define('NONCE_KEY',        'KW_(xhl H`+q=,6m9@@l8MDE/>jYDb>/;sinR3AN?FdiLaDl_p.g(N7b<i-4JJP|');
define('AUTH_SALT',        '8k!=Cp=LI-L;|LRpZ/_WOcB^MgVy$.q>q/$l/mq=cNL :8}`]+NQBll)ng`oTm&B');
define('SECURE_AUTH_SALT', '1JE%P.TUiXn0x_P-A^/pUglpyaW}-yzA^B<6?^kr5S}a6Q3(,vR/nTHv}1cl-1E^');
define('LOGGED_IN_SALT',   'ScEEC]9S4]DbXo:Urdlkt)5pNIT 3jwp+AW`R)fn7M&.OJLFE},;V*tTMVwNm9}k');
define('NONCE_SALT',       ')WY,XN7AhZ4BIez0*C<f4za2JI6ZoEfj~Br58Xu2D=f?cj=(Me09&w5wR&/A~8+F');

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
