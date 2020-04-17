<?php
/**
 * Plugin Name: Latest Github Release
 * Description: Automatically add a download link to the latest Github repo release zips with a shortcode like [latest_github_release user="github" repo="hub"]
 * Version: 2.0.0
 * Author: Laurence Bahiirwa, James Nylen
 * Author URI: https://omukiguy.com
 * Plugin URI: https://github.com/bahiirwa/latest-github-release
 * Text Domain: latest_github_release
 * Requires at least: 4.9
 * Tested up to: 5.3.2
 * 
 * This is free software released under the terms of the General Public License,
 * version 2, or later. It is distributed WITHOUT ANY WARRANTY; without even the
 * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. Full
 * text of the license is available at https://www.gnu.org/licenses/gpl-2.0.txt.
 *
 */

namespace bahiirwa\LatestGithubRelease;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

class LatestGithubRelease {
	/**
	 * Add action to process shortcodes.
	 *
	 * @since 0.1.0
	 * @since 2.0.0 The function is now static.
	 *
	 */
	public static function register() {
		add_shortcode( 'latest_github_release', [ __CLASS__, 'process_shortcode' ] );
	}

	/**
	 * Process shortcode.
	 *
	 * This public function processes the cp_release_link shortcode into HTML markup.
	 *
	 * @since 0.1.0
	 * @since 2.0.0 The function is now static.
	 *
	 * @param array $atts Shortcode arguments.
	 * @return string <a href="(zip url)" class="cp-release-link">$atts[name]</a>
	 */
	public static function process_shortcode( $atts ) {
		// Default values for when not passed in shortcode.
		$defaults = [
			'user'  => '',
			'repo'  => '',
			'name'  => 'Download Zip', // default button name
			'class' => 'latest-github-release-link',
		];

		// Replace any missing shortcode arguments with defaults.
		$atts = shortcode_atts(
			$defaults,
			$atts,
			'latest_github_release'
		);

		// Validate the user and the repo.
		if ( empty( $atts['user'] ) || empty( $atts['repo'] ) ) {
			return '<!-- [latest_github_release] Missing user or repo! -->';
		}

		// Get the release data from GitHub.
		$release_data = self::get_release_data_cached( $atts );
		if ( is_wp_error( $release_data ) ) {
			return (
				'<!-- [latest_github_release] '
				. esc_html( $release_data->get_error_message() )
				. ' -->'
			);
		}

		// Get the URL to a release zipfile.
		$zip_url = self::get_zip_url_for_release( $release_data );
		if ( is_wp_error( $zip_url ) ) {
			return (
				'<!-- [latest_github_release] '
				. esc_html( $zip_url->get_error_message() )
				. ' -->'
			);
		}

		$html = (
			'<a href="' . esc_attr( $zip_url ) . '"'
			. ' class="' . esc_attr( $atts['class'] ) . '">'
			. esc_html( $atts['name'] )
			. '</a>'
		);

		/**
		 * Filters the HTML for the release link.
		 *
		 * @since 2.0.0
		 *
		 * @param string $html The link HTML.
		 * @param array  $atts The full array of shortcode attributes.
		 */
		return apply_filters( 'latest_github_release_link', $html, $atts );
	}

	/**
	 * Fetch release data from GitHub or return it from a cached value.
	 *
	 * @since 2.0.0
	 *
	 * @param array $atts Array containing 'user' and 'repo' arguments.
	 * @return array|\WP_Error Release data from GitHub, or an error object.
	 */
	public static function get_release_data_cached( $atts ) {
		// Get any existing copy of our transient data
		$release_data = get_transient( self::get_transient_name( $atts ) );

		if ( empty( $release_data ) ) {
			$release_data = self::get_release_data( $atts );

			if ( is_wp_error( $release_data ) ) {
				return $release_data;
			}

			// Save release data in transient inside DB to reduce network calls.
			set_transient(
				self::get_transient_name( $atts ),
				$release_data,
				15 * MINUTE_IN_SECONDS
			);
		}

		return $release_data;
	}

	/**
	 * Return the name of the transient that should be used to cache the
	 * release information for a repository.
	 *
	 * @since 1.2.0
	 * @since 2.0.0 The function is now static, and the transient names have
	 * changed because the full release data is stored instead of just the URL
	 * to a zip file.
	 *
	 * @param array $atts Array containing 'user' and 'repo' arguments.
	 * @return string Transient name to use for caching this repository.
	 */
	public static function get_transient_name( $atts ) {
		return (
			'lgr_api_'
			. substr( md5( $atts['user'] . '/' . $atts['repo'] ), 0, 16 )
		);
	}

	/**
	 * Fetch release data from GitHub.
	 *
	 * @since 2.0.0
	 *
	 * @internal - use self::get_release_data_cached() instead.
	 *
	 * @param array $atts Array containing 'user' and 'repo' arguments.
	 * @return array|\WP_Error Release data from GitHub, or an error object.
	 */
	private static function get_release_data( $atts ) {
		// Build the GitHub API URL for the latest release.
		$api_url = (
			'https://api.github.com/repos/'
			. $atts['user'] . '/' . $atts['repo']
			. '/releases/latest'
		);

		// Make API call.
		$response = wp_remote_get( esc_url_raw( $api_url ) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// Parse the JSON response from the API into an array of data.
		$response_body = wp_remote_retrieve_body( $response );
		$response_json = json_decode( $response_body, true );
		$status_code = wp_remote_retrieve_response_code( $response );

		if ( empty( $response_json ) || $status_code !== 200 ) {
			return new \WP_Error(
				'invalid_data',
				'Invalid data returned from GitHub',
				[
					'code' => $status_code,
					'body' => empty( $response_json ) ? $response_body : $response_json,
				]
			);
		}

		return $response_json;
	}

	/**
	 * Given a set of release data from the GitHub API, return a release zip URL.
	 *
	 * @since 2.0.0
	 *
	 * @param array $release_data Release data from the GitHub API.
	 * @return string URL of latest zip release file on GitHub.
	 */
	public static function get_zip_url_for_release( $release_data ) {
		// If any files were uploaded for this release, use the first one.
		// TODO: Allow specifying which file to use somehow (name regex?)
		if ( ! empty( $release_data['assets'] ) ) {
			return $release_data['assets'][0]['browser_download_url'];
		}

		// Otherwise, build a URL based on the tag name of the latest release.
		$version = $release_data['tag_name'];

		// Extract the user and repo name from the GitHub API URL.
		preg_match(
			'#^https://api\.github\.com/repos/([^/]+)/([^/]+)/releases/#',
			$release_data['url'],
			$matches
		);
		$user = $matches[1];
		$repo = $matches[2];

		return (
			'https://github.com/'
			. $user . '/' . $repo
			. '/archive/' . $version . '.zip'
		);
	}
}

LatestGithubRelease::register();
