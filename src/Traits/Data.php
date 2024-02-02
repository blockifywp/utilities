<?php

declare( strict_types=1 );

namespace Blockify\Utilities\Traits;

use WP_Theme;
use function debug_backtrace;
use function dirname;
use function end;
use function get_template;
use function get_template_directory;
use function str_contains;
use function wp_get_theme;
use const WP_PLUGIN_DIR;

trait Data {

	public string $file;
	public string $dir;
	public string $basename;
	public string $url;
	public string $slug;
	public string $name;
	public string $description;
	public string $author;
	public string $author_uri;
	public string $version;
	public string $min_php;
	public string $min_wp;
	public string $domain_path;
	public string $uri;
	public string $update_uri;

	public function __construct() {
		$file       = $this->get_calling_file();
		$theme_dir  = dirname( get_template_directory() );
		$plugin_dir = WP_PLUGIN_DIR;

		if ( str_contains( $file, $theme_dir ) ) {
			$this->set_from_theme( wp_get_theme( get_template() ) );
		} elseif ( str_contains( $file, $plugin_dir ) ) {
			$this->set_from_plugin( $file, get_plugin_data( $file ) );
		}
	}

	private function get_calling_file(): string {
		static $file = null;

		if ( ! is_null( $file ) ) {
			return $file;
		}

		$backtrace  = debug_backtrace();
		$file_trace = [];
		$autoload   = null;

		foreach ( $backtrace as $trace ) {

			if ( isset( $trace['file'] ) ) {
				$file_trace[] = $trace['file'];

				if ( $autoload ) {
					break;
				}

				if ( str_contains( $trace['file'], 'vendor/autoload.php' ) ) {
					$autoload = $trace['file'];
				}
			}
		}

		return end( $file_trace );
	}

	/**
	 * Plugin constructor.
	 *
	 * @param string $file Path to plugin file.
	 * @param array  $data Plugin file headers.
	 *
	 * @return void
	 */
	private function set_from_plugin( string $file, array $data ) {
		$this->file        = $file;
		$this->dir         = trailingslashit( dirname( $file ) );
		$this->url         = trailingslashit( plugin_dir_url( $file ) );
		$this->basename    = plugin_basename( $file );
		$this->name        = $data['Name'] ?? '';
		$this->slug        = $data['TextDomain'] ?? '';
		$this->description = $data['Description'] ?? '';
		$this->author      = $data['Author'] ?? '';
		$this->author_uri  = $data['AuthorURI'] ?? '';
		$this->version     = $data['Version'] ?? '';
		$this->uri         = $data['PluginURI'] ?? '';
		$this->domain_path = $data['DomainPath'] ?? '';
		$this->min_wp      = $data['RequiresWP'] ?? '';
		$this->min_php     = $data['RequiresPHP'] ?? '';
		$this->update_uri  = $data['UpdateURI'] ?? '';
	}

	/**
	 * Theme constructor.
	 *
	 * @param WP_Theme $theme Theme instance.
	 *
	 * @return void
	 */
	private function set_from_theme( WP_Theme $theme ) {
		$this->dir         = trailingslashit( $theme->get_template_directory() );
		$this->url         = trailingslashit( $theme->get_template_directory_uri() );
		$this->slug        = $theme->get_template();
		$this->file        = $this->dir . DIRECTORY_SEPARATOR . $this->slug . '.php';
		$this->basename    = basename( $this->dir ) . DIRECTORY_SEPARATOR . basename( $this->file );
		$this->name        = $theme->get( 'Name' );
		$this->description = $theme->get( 'Description' );
		$this->author      = $theme->get( 'Author' );
		$this->author_uri  = $theme->get( 'AuthorURI' );
		$this->version     = $theme->get( 'Version' );
		$this->min_php     = $theme->get( 'RequiresPHP' );
		$this->min_wp      = $theme->get( 'RequiresWP' );
		$this->uri         = $theme->get( 'ThemeURI' );
		$this->domain_path = $theme->get( 'DomainPath' );
		$this->update_uri  = $theme->get( 'UpdateURI' );
	}

}
