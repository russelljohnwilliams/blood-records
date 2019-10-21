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
define( 'DB_NAME', 'blood_test' );

/** MySQL database username */
define( 'DB_USER', 'admin' );

/** MySQL database password */
define( 'DB_PASSWORD', 'password' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'MVIEB>}BO|zPGrZxlrIbRX`7m!&v.3&WN:vYxhp94}*oZKmtKw68qK|q|8_zKEeo' );
define( 'SECURE_AUTH_KEY',  'Tu[fL^43q>n-`t8k)GKjI43?VO&rx=HKF+-[q/77OyarGuUwNYw`E7%0I3<1Z@[;' );
define( 'LOGGED_IN_KEY',    '#~pE8.vE$RNaY=EQ)DIPV;~xnN> xp_JI5T}@~&h^7)W?dCgZ6D u:JyQRh({NzV' );
define( 'NONCE_KEY',        'EZ.O4%lzvSpHV}Q}X|O4$:R@t4gl^{7%7Ok)<MX70Hl.Qi&RwEHmFnla9)0iE+py' );
define( 'AUTH_SALT',        'zJ%v~TO1~RKb;*s9 &[Lk`e $2vo8L&WRr6x0`|9;e*f=P[U!`? G`LTS.R?Wd5e' );
define( 'SECURE_AUTH_SALT', '%a/o)5=0EX6O_@v;zBy>*c+0}Tt%>pqSNQ7k-R#KzJ$ABi@lRY}9)`&l{~:rVNj+' );
define( 'LOGGED_IN_SALT',   'l{kTCGx0W|;J5GmvU5Sh.l{~p2>LLg%A~3EYCs0jx~tMJibo:9&:e/er=VH-puD5' );
define( 'NONCE_SALT',       '{LtoIDTqH2Kyi,NuCx5~U/BHfH9O]}N0<ZdF %h &kFo38RDOslViUK9+.=O ,a?' );

/**#@-*/

/**
 * WordPress Database Table prefix.
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
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
