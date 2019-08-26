<?php
/**
 * Plugin Name: Latest Github Release
 * Description: Automatically add a download link to the latest Github repo release zips with a shortcode like [latest_github_release user="github" repo="hub"]
 * Version: 1.2.0
 * Author: Laurence Bahiirwa, James Nylen
 * Author URI: https://omukiguy.com
 * Plugin URI: https://github.com/bahiirwa/latest-github-release
 * Text Domain: latest_github_release
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
	 *
	 */
	public function register() {
		add_shortcode( 'latest_github_release', [ $this, 'process_shortcode' ] );
	}

	/**
	 * Process shortcode.
	 *
	 * This public function processes the cp_release_link shortcode into HTML markup.
	 *
	 * @since 0.1.0
	 *
	 * @param array $atts Shortcode arguments.
	 * @return string <a href="url" class="cp-release-link">$atts[name] . ' ' . $atts[type]</a>
	 */
	public function process_shortcode( $atts ) {
		// Default values for when not passed in shortcode.
		$defaults = [
			'user' => '',
			'repo' => '',
			// set default button name to Download
			'name' => 'Download Zip',
		];

		// Replace any missing shortcode arguments with defaults.
		$atts = shortcode_atts(
			$defaults,
			$atts,
			'latest_github_release'
		);

		// Validate the user and the repo.
		if ( empty( $atts['user'] ) || empty( $atts['repo'] ) ) {
			return '<!-- [latest_github_release] missing user or repo! -->';
		}

		// Get any existing copy of our transient data
		$zip_url = get_transient( $this->get_transient_name( $atts ) );

		if ( empty( $zip_url ) ) {
			$zip_url = $this->get_release_zip_url( $atts );

			if ( empty( $zip_url ) ) {
				// An error occurred, and the `get_release_zip_url()` function
				// should have already echoed an appropriate message inside a
				// comment.
				return '';
			}

			// Save zip link in transient inside DB to reduce network calls.
			set_transient(
				$this->get_transient_name( $atts ),
				$zip_url,
				15 * MINUTE_IN_SECONDS
			);
		}

		return (
			'<a href="' . $zip_url . '" class="cp-release-link">'
			. $atts['name']
			. '</a>'
		);
	}

	/**
	 * Call out to the GitHub API to get a release zip URL for the given repository.
	 *
	 * @since 1.2.0
	 *
	 * @param array $atts Array containing 'user' and 'repo' arguments.
	 * @return string URL of latest zip release file on GitHub.
	 */
	public function get_release_zip_url( $atts ) {
		// Build the GitHub API URL for the latest release.
		$api_url = (
			'https://api.github.com/repos/'
			. $atts['user'] . '/' . $atts['repo']
			. '/releases/latest'
		);

		// Make API call.
		$response = wp_remote_get( esc_url_raw( $api_url ) );
		// Error catch for failed API call.
		if ( is_wp_error( $response ) ) {
			echo '<!-- [latest_github_release] error: ';
			echo esc_html( $response->get_error_message() );
			echo ' -->';
			return null;
		}

		// Parse the JSON response from the API into an array of data.
		$api_response = json_decode( wp_remote_retrieve_body( $response ), true );

		// If any files were uploaded for this release, use the first one.
		// TODO: Allow specifying which file to use somehow (name regex?)
		if ( ! empty( $api_response['assets'] ) ) {
			return $api_response['assets'][0]['browser_download_url'];
		}

		// Otherwise, build a URL based on the tag name of the latest release.
		$version = $api_response['tag_name'];

		if ( empty( $version ) ) {
			echo '<!-- [latest_github_release] no releases found! -->';
			return null;
		}

		return (
			'https://github.com/'
			. $atts['user'] . '/' . $atts['repo']
			. '/archive/' . $version . '.zip'
		);
	}

	/**
	 * Return the name of the transient that should be used to cache the
	 * release information for a repository.
	 *
	 * @since 1.2.0
	 *
	 * @param array $atts Array containing 'user' and 'repo' arguments.
	 * @return string Transient name to use for caching this repository.
	 */
	public function get_transient_name( $atts ) {
		return 'lgr_' . substr( md5( $atts['user'] . '/' . $atts['repo'] ), 0, 16 );
	}
}

// On Activation. Start the Plugin class.
$CP_release_link = new LatestGithubRelease;
$CP_release_link->register();
