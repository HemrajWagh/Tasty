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
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'tastydb' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
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
define( 'AUTH_KEY',         '.{Y1sA3V)0|KcOiiY+chH]On{Nb4!#WMf8l&pLM[$09n@cD`?TqREccfB.HI-}DK' );
define( 'SECURE_AUTH_KEY',  'n5$1DF Yt2?${+UzRuuVREcuB?6Jww3}~l7v_+~>E_v]`YJXmP&w~kLy`U~!J,?Q' );
define( 'LOGGED_IN_KEY',    'g{tZH0X7U|Jjr5i@+PYr7$x/VAV=20mkt*1,IMqvjFFmHokho=DBG.dM3kS|W!Sv' );
define( 'NONCE_KEY',        'Yl2?sIb]wmmf|qxS6aXs=naqJeqQn<xq`ft6-5@[,P#O>?B;.>YE-Nzz,pT2bV9i' );
define( 'AUTH_SALT',        'vLm(c/}m@6nm65D+vkr>(i#,$K)y[! e,KPCS{1P 506)GTKr#M$0.Hn!GBUQ^Jy' );
define( 'SECURE_AUTH_SALT', 'YV/N`9adFizv!oI<D[>wcSKNk6y/vrVrtkEtSl}BECFdxxu/D<PB=<bgRaOwcy `' );
define( 'LOGGED_IN_SALT',   '~~t%d,,fmOdN!GxYO m>A< .yYJL@anAMCFIG ^<;O4 g)O8T..+xQPb7_XMXJ)z' );
define( 'NONCE_SALT',       'T;Q1B.Q[uogq1[I<W8fzwhny[a{3f&J`ci5{969dVF>1a%KW;k%}o@j|.QbM?`yJ' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

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
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
