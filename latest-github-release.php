<?php
/**
 * Plugin Name: Latest Github Release
 * Description: Automatically add a download link to the latest Github repo release zips with a shortcode [latest_github_release user="Github" repo="years-since"]
 * Version: 0.1.0
 * Author: Laurence Bahiirwa
 * Author URI: https://omukiguy.com
 * Plugin URI: https://github.com/bahiirwa/latest-github-release
 * Text Domain: latest_github_release
 * 
 * This is free software released under the terms of the General Public License,
 * version 2, or later. It is distributed WITHOUT ANY WARRANTY; without even the
 * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. Full
 * text of the license is available at https://www.gnu.org/licenses/gpl-2.0.txt.
 */

// Prevent direct access.
if (!defined('ABSPATH')) {
	die();
}

class LatestGithubRelease {

	/**
	 * Add action to Process shortcodes.
	 *
	 * @author Laurence Bahiirwa
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
	 * @author Laurence Bahiirwa
	 *
	 * @since 0.1.0
	 *
	 * @param array $atts Shortcode arguments.
	 * @return string <a href="url" class="cp-release-link" target="_blank">$atts[name] . ' ' . $atts[type]</a>
	 */
	public function process_shortcode($atts) {
		
		// Default values for when not passed in shortcode.
		$defaults = [
			'repo' => '',
			'user' => '',
			// set default button name to Download
			'name' => 'Download',
		];

		// Replace any missing shortcode arguments with defaults.
		$atts = shortcode_atts(
			$defaults,
			$atts,
			'latest_github_release');

		// Get any existing copy of our transient data
		if ( true == get_transient('lg_release_zip_link') ) {
			$final_url = get_transient('lg_release_zip_link');
			return '<a href="' . $final_url . '" class="cp-release-link" target="_blank">' . $atts['name'] . '</a>';
		}
		else {
			//https://api.github.com/repos/bahiirwa/years-since/zipball/1.2.0
			$combine_link =	'https://api.github.com/repos/' . $atts['user'] . '/' . $atts['repo'] . '/releases/latest';
			// Pass the Release API URL with the transient name
			$final_url = $this->run_link_processor( $combine_link, 'lg_release_zip_link');
			return '<a href="' . $final_url . '" class="cp-release-link" rel="noopener" target="_blank">' . $atts['name'] . '</a>';
		}

	}

	/**
	 * Process the chosen type of option for the release zip
	 *
	 * @author Laurence Bahiirwa
	 *
	 * @since 0.1.0
	 *
	 * @param array $atts Shortcode arguments.
	 * @return string Link URL to zip release file if no url_link is set in Shortcode
	 */
	public function run_link_processor($zip_link, $transient_name) {

		// Get any existing copy of our transient data
		if ( true == get_transient($transient_name) ) {
			$link_core_return_url = get_transient($transient_name);
			return $link_core_url;
		} 
		// Else create a transient option
		else {
			// Make API Call.
			$response = wp_remote_get( esc_url_raw($zip_link) );
			// Error catch for failed API Call.
			if ( is_wp_error( $response ) ) {
				echo "Something went wrong";
				var_dump($response);
			} 
			else {
				/* Will result in $api_response being an array of data,
				parsed from the JSON response of the API listed above */
				$api_response = json_decode( wp_remote_retrieve_body( $response ), true );
				// Catch Zipball_url link
				$link_core_return_url =  $api_response['zipball_url'] ;
				// Set 5 minute expiry trnasient with the DB to reduce network calls.
				set_transient($transient_name, $link_core_return_url, 5 * MINUTE_IN_SECONDS );
				// If no url_link is set in shortcode then save variable of the API link for zip.
				$link_core_url = ( $ghb_url ) ? $ghb_url : $link_core_return_url;
				// Return link
				return $link_core_url;
			}
		}
		
	}
}

// On Activation. Start the Plugin class.
$CP_release_link = new LatestGithubRelease;
$CP_release_link->register();

// On deactivation. Clear the links transient created in DB.
function lgr_release_link_deactivation() {

	if ( true == get_transient( 'lg_release_zip_link' ) ) {
		delete_transient( 'lg_release_zip_link' );
	}
	
}

register_deactivation_hook(__FILE__, 'lgr_release_link_deactivation' );