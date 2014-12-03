<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wordpress');

/** MySQL database username */
define('DB_USER', 'wordpress');

/** MySQL database password */
define('DB_PASSWORD', 'w0rdpr3ss');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

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
define('AUTH_KEY',         'c@bl6CUF0Y6x pZSrVI(=f/eQ]{Gl/^kmUkKkAt9o+XkV_hR];|P^!UjN[-6ox=B');
define('SECURE_AUTH_KEY',  'C74X-/p`qlt@@^h-+HHh%@_@~bUWFCy*UNTbLJgb,Gp,g%%WWkui?&e=-+`Q2ooe');
define('LOGGED_IN_KEY',    'AhtMV{tOGG)1?Wu~jtFrz^U;7nD3#89aN|j<HEbc{r`EMU7gy9fYM/ceZp=KlJxg');
define('NONCE_KEY',        '|w32OY8jr}-Cc8v.it%fJI^K34vjuCI%Ot1;H!|YbAhIm>y+U{+iUeL%K}9 xWTs');
define('AUTH_SALT',        'NvM|HdIO 3^?h-Uj0UIe#+UB]cCnf<FvO5TpIKTr/Y|B,#|ldw.[&e>.>u+M*v/<');
define('SECURE_AUTH_SALT', 'I[y5u<-PqD^0p~86E?>9%p{.|w |4brz_IhV~$p;Lm+n=HQViR~^&#Q(}.*<4W-L');
define('LOGGED_IN_SALT',   '0LoPkZ{?_QEqh|u){Ogqxlf.+5nG+ee&2{|x~^Y5vG|d (ZQljYx(9!P^-^F3A@#');
define('NONCE_SALT',       'V;}Miu[1K4Y[;mdH-X+$X?*qq2q,MCH[}Bsa#E@n-MV6A9GNU,_%06y_l8wj z8m');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
