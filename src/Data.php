<?php

declare( strict_types=1 );

namespace Blockify\Utilities;

use WP_Theme;
use function dirname;
use function get_template;
use function get_template_directory;
use function str_contains;
use function wp_get_theme;
use const WP_PLUGIN_DIR;

/**
 * Data object.
 *
 * @since 1.0.0
 */
class Data {

	public static string $file;
	public static string $dir;
	public static string $basename;
	public static string $url;
	public static string $slug;
	public static string $name;
	public static string $description;
	public static string $author;
	public static string $author_uri;
	public static string $version;
	public static string $min_php;
	public static string $min_wp;
	public static string $domain_path;
	public static string $uri;
	public static string $update_uri;

	/**
	 * Data constructor.
	 *
	 * @return void
	 */
	public static function from( string $file ): self {
		$theme_dir  = dirname( get_template_directory() );
		$plugin_dir = WP_PLUGIN_DIR;

		if ( str_contains( $file, $plugin_dir ) ) {
			return self::plugin( $file, get_plugin_data( $file ) );
		} elseif ( str_contains( $file, $theme_dir ) ) {
			return self::theme( wp_get_theme( get_template() ) );
		}

		return new self();
	}

	/**
	 * Plugin constructor.
	 *
	 * @param string $file Path to plugin file.
	 * @param array  $data Plugin file headers.
	 *
	 * @return void
	 */
	private static function plugin( string $file, array $data ): self {
		$static               = new self();
		$static::$file        = $file;
		$static::$dir         = trailingslashit( dirname( $file ) );
		$static::$url         = trailingslashit( plugin_dir_url( $file ) );
		$static::$basename    = plugin_basename( $file );
		$static::$name        = $data['Name'] ?? '';
		$static::$slug        = $data['TextDomain'] ?? '';
		$static::$description = $data['Description'] ?? '';
		$static::$author      = $data['Author'] ?? '';
		$static::$author_uri  = $data['AuthorURI'] ?? '';
		$static::$version     = $data['Version'] ?? '';
		$static::$uri         = $data['PluginURI'] ?? '';
		$static::$domain_path = $data['DomainPath'] ?? '';
		$static::$min_wp      = $data['RequiresWP'] ?? '';
		$static::$min_php     = $data['RequiresPHP'] ?? '';
		$static::$update_uri  = $data['UpdateURI'] ?? '';

		return $static;
	}

	/**
	 * Theme constructor.
	 *
	 * @param WP_Theme $theme Theme instance.
	 *
	 * @return self
	 */
	private static function theme( WP_Theme $theme ): self {
		$static               = new self();
		$static::$dir         = trailingslashit( $theme->get_template_directory() );
		$static::$url         = trailingslashit( $theme->get_template_directory_uri() );
		$static::$slug        = $theme->get_template();
		$static::$file        = self::$dir . DIRECTORY_SEPARATOR . self::$slug . '.php';
		$static::$basename    = basename( self::$dir ) . DIRECTORY_SEPARATOR . basename( self::$file );
		$static::$name        = $theme->get( 'Name' );
		$static::$description = $theme->get( 'Description' );
		$static::$author      = $theme->get( 'Author' );
		$static::$author_uri  = $theme->get( 'AuthorURI' );
		$static::$version     = $theme->get( 'Version' );
		$static::$min_php     = $theme->get( 'RequiresPHP' );
		$static::$min_wp      = $theme->get( 'RequiresWP' );
		$static::$uri         = $theme->get( 'ThemeURI' );
		$static::$domain_path = $theme->get( 'DomainPath' );
		$static::$update_uri  = $theme->get( 'UpdateURI' );

		return $static;
	}

}
