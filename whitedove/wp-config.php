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
define( 'DB_NAME', 'whitedove' );

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
define( 'AUTH_KEY',         'itb/d$:?-)~]d5VlgkPy_aE7?||TT<Tw(UgSqo|K0w`iBitbV%w-ve=pY$w=TmN0' );
define( 'SECURE_AUTH_KEY',  '6sU]jD|np[LiH@%/5f[8?08P&@dRH_1OF3Pqr{QL${7l0{a$E=T`bqFotbQb<w7!' );
define( 'LOGGED_IN_KEY',    'N{9!*C#tLL~/Bk;EL7W}x9a0*c6ZgtBwrRIICS6!=HibGmpZ<*YA-u%%GEH#euDQ' );
define( 'NONCE_KEY',        '$[K)1!&V04szF)xQL3f9uJ1tsK7]-b./D:UYO~;A)3&)zJ%Lru]d&>Nc]K|q4@v:' );
define( 'AUTH_SALT',        'U.K5?jnZw4-,[&E7|kf:TF!#6[aFx/4mhR+]Gn)kUMo@5;b[F8dl@N1VIwWKL-us' );
define( 'SECURE_AUTH_SALT', '&?xK*1O>/QS=!s31{v,KBG!)#xK.EapunU}Nsf&%fpVO6:>@d=aASa_9$p8{%p@G' );
define( 'LOGGED_IN_SALT',   '%P3+1{{hcPKR]_ H:{xQ2z-fAwtgJ68`;/&CBOe*.,&6:s)y<X*s!8|837D78vJw' );
define( 'NONCE_SALT',       '=.(QM+(lp<*o_?jV7Mt(3|t)]tb8&}(/Wlf 2sb76,buFzYTM4utBia_NOP;#NHP' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wd_';

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
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', true );

/* Add any custom values between this line and the "stop editing" line. */

define('DEFAULT_PUBLIC_KEY','6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI');
define('DEFAULT_PRIVATE_KEY','6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe');



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
