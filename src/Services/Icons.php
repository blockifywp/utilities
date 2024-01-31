<?php

declare( strict_types=1 );

namespace Blockify\Core\Services;

use Blockify\Core\Interfaces\Hookable;
use Blockify\Core\Traits\HookAnnotations;
use Blockify\Core\Utilities\Icon;
use WP_REST_Request;
use WP_REST_Server;
use function basename;
use function current_user_can;
use function file_get_contents;
use function glob;
use function trim;

/**
 * Icons trait.
 *
 * @since 1.0.0
 */
class Icons implements Hookable {

	use HookAnnotations;

	/**
	 * Registers icon REST endpoint.
	 *
	 * @since 0.0.1
	 *
	 * @hook  rest_api_init
	 *
	 * @return void
	 */
	public function register_rest_route(): void {
		register_rest_route(
			'blockify/v1',
			'/icons/',
			[
				'permission_callback' => static fn() => current_user_can( 'edit_posts' ),
				'callback'            => static fn( WP_REST_Request $request ): array => $this->get_icon_data( $request ),
				'methods'             => WP_REST_Server::READABLE,
				[
					'args' => [
						'sets' => [
							'required' => false,
							'type'     => 'string',
						],
						'set'  => [
							'required' => false,
							'type'     => 'string',
						],
					],
				],
			]
		);
	}

	/**
	 * Returns icon data for rest endpoint
	 *
	 * @since 0.4.8
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return mixed array|string
	 */
	public function get_icon_data( WP_REST_Request $request ) {
		$icon_data = [];
		$icon_sets = Icon::get_icon_sets();

		foreach ( $icon_sets as $icon_set => $set_dir ) {
			$icons = glob( $set_dir . '/*.svg' );

			foreach ( $icons as $icon ) {
				$name = basename( $icon, '.svg' );
				$icon = file_get_contents( $icon );

				if ( $icon_set === 'WordPress' ) {
					$icon = str_replace(
						[ 'fill="none"' ],
						[ 'fill="currentColor"' ],
						$icon
					);
				}

				// Remove comments.
				$icon = preg_replace( '/<!--(.|\s)*?-->/', '', $icon );

				// Remove new lines.
				$icon = preg_replace( '/\s+/', ' ', $icon );

				// Remove tabs.
				$icon = preg_replace( '/\t+/', '', $icon );

				// Remove spaces between tags.
				$icon = preg_replace( '/>\s+</', '><', $icon );

				$icon_data[ $icon_set ][ $name ] = trim( $icon );
			}
		}

		if ( $request->get_param( 'set' ) ) {
			$set = $request->get_param( 'set' );

			if ( $request->get_param( 'icon' ) ) {

				// TODO: Is string being used anywhere?
				return $icon_data[ $set ][ $request->get_param( 'icon' ) ];
			}

			return $icon_data[ $set ];
		}

		if ( $request->get_param( 'sets' ) ) {
			return array_keys( $icon_data );
		}

		return $icon_data;
	}
}
