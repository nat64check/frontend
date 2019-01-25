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

require_once( __DIR__ . '/vendor/autoload.php' );
( new \Dotenv\Dotenv( __DIR__ . '/' ) )->load();

# Basic site settings
define( "WP_SITEURL", getenv( 'WP_SITEURL' ) );
define( "WP_HOME", getenv( 'WP_HOME' ) );
define( 'WP_DEFAULT_THEME', 'nat64check' );

# MySQL settings
define( "IS_DEVEL", (bool) getenv( 'IS_DEVEL' ) );
define( "IS_STAGING", (bool) getenv( 'IS_STAGING' ) );
define( "IS_LIVE", ! IS_DEVEL && ! IS_STAGING );

define( 'DB_NAME', getenv( 'DB_NAME' ) );
define( 'DB_USER', getenv( 'DB_USER' ) );
define( 'DB_PASSWORD', getenv( 'DB_PASSWORD' ) );
define( 'DB_HOST', getenv( 'DB_HOST' ) );

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
define( 'AUTH_KEY', getenv( 'AUTH_KEY' ) );
define( 'AUTH_SALT', getenv( 'AUTH_SALT' ) );
define( 'SECURE_AUTH_KEY', getenv( 'SECURE_AUTH_KEY' ) );
define( 'SECURE_AUTH_SALT', getenv( 'SECURE_AUTH_SALT' ) );
define( 'LOGGED_IN_KEY', getenv( 'LOGGED_IN_KEY' ) );
define( 'LOGGED_IN_SALT', getenv( 'LOGGED_IN_SALT' ) );
define( 'NONCE_KEY', getenv( 'NONCE_KEY' ) );
define( 'NONCE_SALT', getenv( 'NONCE_SALT' ) );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = getenv( 'DB_PREFIX' ) or 'wp_';

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
define( 'WP_DEBUG', IS_DEVEL );

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/wp/' );
}

/** Relocate wp-content to outside the wp directory. */
define( 'WP_CONTENT_DIR', __DIR__ . '/wp-content' );
define( 'WP_CONTENT_URL', WP_SITEURL . '/wp-content' );

define( 'WP_PLUGIN_DIR', __DIR__ . '/plugins' );
define( 'WP_PLUGIN_URL', WP_SITEURL . '/plugins' );
define( 'PLUGINDIR', WP_PLUGIN_DIR );

define( 'UPLOADS', '../wp-content/uploads' );

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
