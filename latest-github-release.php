<?php
/**
 * Plugin Name: Latest Github Release
 * Description: Automatically add a download link to the latest Github repo release zips with a shortcode like [latest_github_release user="Github" repo="years-since"]
 * Version: 1.1.0
 * Author: Laurence Bahiirwa
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
if (!defined('ABSPATH')) {
	die();
}

class LatestGithubRelease {

	protected $lg_release_zip;
	
	public function __construct() {
		
		$this->lg_release_zip = 'lg_release_zip'; // Transient Name

	}

	/**
	 * Add action to Process shortcodes.
	 *
	 * @since 0.1.0
	 *
	 */
	public function register() {
	
		add_shortcode('latest_github_release', array($this, 'process_shortcode'));

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
	public function process_shortcode($atts) {
		
		// Default values for when not passed in shortcode.
		$defaults = [
			'repo' => '',
			'user' => '',
			// set default button name to Download
			'name' => 'Download Zip',
		];

		// Replace any missing shortcode arguments with defaults.
		$atts = shortcode_atts(
			$defaults,
			$atts,
			'latest_github_release');

		// Get any existing copy of our transient data	
		$transient_name =  $this->lg_release_zip . '_' . $atts['repo'];

		if ( !empty( true == get_transient( $transient_name ) ) ) {
			
			return '<a href="' . get_transient( $transient_name ) . '" class="cp-release-link">' . $atts['name'] . '</a>';
			
		}

		else {
			// Get Release API URL with the user & repo names
			$combine_link =	'https://api.github.com/repos/' . $atts['user'] . '/' . $atts['repo'] . '/releases/latest';
			
			/**
			 *  @param string Pass the Release API URL into function
			 *  @return string zip URL from function.
			 */
			$final_url = $this->run_link_processor($combine_link, $atts['user'], $atts['repo']);

			if ( empty( $final_url ) ) {
				return;
			}

			// Save API link for zip in 5 minute expiry transient inside DB to reduce network calls.
			set_transient($this->lg_release_zip . $atts['repo'], $final_url, 5 * MINUTE_IN_SECONDS );

			return '<a href="' . $final_url . '" class="cp-release-link">' . $atts['name'] . '</a>';

		}

	}

	/**
	 * Process the chosen type of option for the release zip
	 *
	 * @since 0.1.0
	 *
	 * @param array $atts Combined link, Shortcode user & Repo arguments.
	 * @return string Link URL to zip release file if no url_link is set in Shortcode
	 * 
	 */
	public function run_link_processor($zip_link, $user, $repo) {

		// Make API Call.
		$response = wp_remote_get( esc_url_raw($zip_link) );
		// Error catch for failed API Call.
		if ( is_wp_error( $response ) ) {
			echo "Something went wrong";
			var_dump($response);
			return;
		}
		
		/* Will result in $api_response being an array of data,
		parsed from the JSON response of the API listed above */
		$api_response = json_decode( wp_remote_retrieve_body( $response ), true );

		// Catch tag_name link. If the repo has no releases, it returns no links so, Echo message and exit.
		$assets = $api_response['assets'];

		if ( ! empty( $assets ) ) {
			$final_url = $assets[0]['browser_download_url'];
			return $final_url;
		} else {
			$version = $api_response['tag_name'];
			if (empty($version)) {
				// Return error message.
				echo '<p style="color: red;">' . $repo . ' ' . esc_html__( 'repository has no releases. Talk to the Repository Admin.', 'my-text-domain' ) . '</p>';
				return;
			}
			$final_url = 'https://github.com/' . $user . '/' . $repo . '/archive/' . $version . '.zip';
			return $final_url;
		}
		
	}

	/**
	 * On deactivation. Clear the links transient created in DB.
	 *
	 * @since 0.1.0
	 */
	public function deactivation($atts) {

		$transient = $this->lg_release_zip . '_' . $atts['repo'];
		if ( true == get_transient( $transient ) ) {
			delete_transient( $transient );
		}
		
	}

}

// On Activation. Start the Plugin class.
$CP_release_link = new LatestGithubRelease;
$CP_release_link->register();

register_deactivation_hook(__FILE__, array( 'LatestGithubRelease', 'deactivation' ) );