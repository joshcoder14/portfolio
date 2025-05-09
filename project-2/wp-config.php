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
define( 'DB_NAME', 'prestaluz' );

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
define( 'AUTH_KEY',         '!D`W@aS q^gDJM^[)3J#unr@pp2>6uB,&28tRMPwt|SZqX=x,24hAd|YFJ2(@Q!1' );
define( 'SECURE_AUTH_KEY',  'tAy>s^5|x?:hK%o,WNE#3v:EvqQ`]&L/cz+3puhw^6(g0vcTRKUV<II1!hV&mYOC' );
define( 'LOGGED_IN_KEY',    '%e=LUu@{Uvb4=r<}R?Ot@)6X},Nu#MB#FXR9$ns[u7~$@*a}lB_%5St<EAo-j8 o' );
define( 'NONCE_KEY',        'H;[8,*!+$gQlXep|C1uM>=l8)ZgK[lqpP$cN(iv2,^sEb7)y(7Oyu3VXCf,)MnAF' );
define( 'AUTH_SALT',        '8d OLY((.BE9dOK[/@2gAwZegALMW= SRmG2c%9xoR4u<*4$>wq8eU<bpF%8bB7a' );
define( 'SECURE_AUTH_SALT', 'MZu%U!c@47%@&HXBI_*+}K,&3 dnFQDW(4d%>K:Gu,2V9m4$4L1zS,r-O1cdq&gx' );
define( 'LOGGED_IN_SALT',   'Na<5USu;iA=A,st|[T%.?U|fi-^!C?v3{M>wjgpEqyo1%V[3)OlQ~-@>%CPxm,c}' );
define( 'NONCE_SALT',       '2[L+g>V7-y)cK@]49e}~v ,vmrUh{KOdTh)~m,tIhP&UDZ{<P<w[C#=F:zVUz//b' );

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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

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
