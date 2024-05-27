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
define( 'DB_NAME', 'marketo_db' );

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
define( 'AUTH_KEY',         '&$N}TjUhG)h&ow#elNo+*$X_AjvnZ<}gl!&;x#<cmT)K:zNBcHx11q<84zi~zU]^' );
define( 'SECURE_AUTH_KEY',  'Ek>3HFmlvd_?41G,&d~(gbjN^$`K^}IV]$_{T&F Obn2Z{P^f@h:0EJtxD[/TtO{' );
define( 'LOGGED_IN_KEY',    '9z|!PK3cze}SNZ/3[d5yzSFrOEWJy9Tco.plm{7ZAh5|#E+,9k=a=LaPV,K{?Lg ' );
define( 'NONCE_KEY',        'or+Q)Zm|s#DPmN*}_R@Va69mE9+?Z*ZwMU.K 9?3$ }Tn]]&}_SLckmP)-PAgV[o' );
define( 'AUTH_SALT',        'Afr^Oc9-OvcZv0$)d~{~_tm%5G.r]J((jOD,(uvt/K{+|mJ@A{og]]mN~@`-$O@|' );
define( 'SECURE_AUTH_SALT', 'AUk|mz,>$^&o$@.O}s&u0@$1Ld$Ndl*>D&h,Da8|=w7s_x=|Xgu`]Y>/Icdp.S/i' );
define( 'LOGGED_IN_SALT',   'm}ap4JS~ 7TrG~0h*mXk~g7bBPC =RduuhHcl&&I7p}sR2p|I;p<r^v.exJ2`v*l' );
define( 'NONCE_SALT',       'M;hb@7{f*1)0iQ$WOt+;!nax:{|9nU,0pXDzxHp!:8&24}fR<;F/B%7=+P)N+Vs0' );

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

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
