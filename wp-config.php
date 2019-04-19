<?php
/**
 * The base configuration for ClassicPress
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
 * @package ClassicPress
 */

define('WP_DEBUG', true);

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for ClassicPress */
define('DB_NAME', 'classic');

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
 * You can generate these using the {@link https://api.classicpress.net/secret-key/1.0/salt/ ClassicPress.net secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since WP-2.6.0
 */
define('AUTH_KEY',         'PdRs_;dqX3m- Vnp@(Smmc87AnE#kDnk{R=C[q5)Ko#!Wm8JN(s6wsGE<;bT2k_r');
define('SECURE_AUTH_KEY',  '>+%r[B>Y+Bb*ui)GS({[-LBJVf|3as4 $%vD0xHtd0#KYqBjO[Ee@S8QQ{sEC/eP');
define('LOGGED_IN_KEY',    'ID3^8<38_:T{u~]uWkD+RC91@p%%2h=%N*-D8?+8D~w|AUOU0z1@t6?:#8}kQCpj');
define('NONCE_KEY',        ']O8mH3i,{qA)f9C>Y-YHlOGW:~<Y@PN0J.0t|BB_W6K]a|rr8^5dvh`Z%/D*^-d/');
define('AUTH_SALT',        'plWWSXY`S!BoN,Nft~N_rJXrN|r7d[gh)FI&%Sd%5eu7Rp`>R~Jy=V,9B;ZshYJ?');
define('SECURE_AUTH_SALT', 'DODiOqN%|Yn}=&Mv~@e{=&D#@ntw/=/L]N0n_ojxN}~`/$>B+u~*$?OlKrrknl.l');
define('LOGGED_IN_SALT',   'ADPJKqsQ#VA`CYA;C~[.4CiR.7[n0?O4MzMzGY-277n,Wg=H*DASxUT}bKzCv<qy');
define('NONCE_SALT',       'kB{$EHX6es/l0t(a1C;sydho#I. kWq*4$kJ3e.wU:uzn$i%c0 PAE-f|lov]<g:');

/**#@-*/

/**
 * ClassicPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'cp_';

/**
 * For developers: ClassicPress debugging mode.
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

/** Absolute path to the ClassicPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up ClassicPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
