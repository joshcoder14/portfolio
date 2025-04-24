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
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'nexdinero' );

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
define( 'AUTH_KEY',         'L7K47R~)Dw%S2AuI >k&@oH>VM^Vol.~RKE7OfQcSqy>Llg_mYDGVDM+Ma[V>dZl' );
define( 'SECURE_AUTH_KEY',  'Y{<~iF+Cm@](98f@%=Nlkc3X&@+HT4-D!?=xL!Vz@ 1y@A|a<= t0A^r/9^A(y6/' );
define( 'LOGGED_IN_KEY',    'U,9mG}]i*Hd,{6SFM^lG^.(sb?=i[@U^^00Dj(r7z|T=i7z_Rja%Yz2DKltq3UKc' );
define( 'NONCE_KEY',        '. ^IHqD?vG],98yj1Ja7eWBFOa*@F*<x1BdboKqXYpvD..kXy*)qCC.5@C95G-S&' );
define( 'AUTH_SALT',        '`e|uAkIj~QzF ER*p*Ll=4oZhI4]t87+Wd!1,~G%C/WfH=MXW#Ryi[iN-y=~l8^/' );
define( 'SECURE_AUTH_SALT', 'Sz(Z}jk mkD~pRLJAl22R<-YK&i{N`/?9*$ajLTF{BTfL3@W`q}_wo7[x7.d1F6r' );
define( 'LOGGED_IN_SALT',   'Cni4Q;rZa&>S`Otdrq-H,JzexkE]weM-IT10&S4i7ylK`s_|YW(2_]a-D$}vSW|i' );
define( 'NONCE_SALT',       'QMQkt{M3VfyI3E<?70xM$-OQ7^,v|v;b&UPFd? )rzm$~`> q~dP?2r1~4<$C+W*' );

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
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );
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
