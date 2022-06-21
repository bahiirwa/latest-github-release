= Latest Github Release =
Contributors: laurencebahiirwa, jnylen0
Donate link: https://omukiguy.com/
Tags: Github, Release, Latest, zip
Requires at least: 4.9.0
Tested up to: 6.0
Requires PHP: 5.6
Stable tag: 2.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Automatically add a download link to the latest Github repo release zips with
a shortcode [latest_github_release user="github" repo="hub"]

== Description ==

Automatically add a download link to the latest Github repo release zips with
a shortcode [latest_github_release user="github" repo="hub"]

== Usage ==
Add the shortcode ```[latest_github_release user="github" repo="hub"]``` to desired post/page/widget and save to have the code working.

=== options ===
One can add some customization to the shortcode such as 

* Name of the button = ```[latest_github_release name="Desired Name"]```

==== Use latest_github_release_link filter Hook ====

```
add_filter( 'latest_github_release_link', 'gmlatest_github_release_link', 10, 3 );

function gmlatest_github_release_link( $html, $atts, $zip_url ) {

    // Add another class to the <a>.
	$atts['class'] .= ' another-class';

    // Add <h2> around the tag.
	$html = (
		'<h2><a href="' . esc_attr( $zip_url ) . '"'
		. ' class="' . esc_attr( $atts['class'] ) . '">'
		. esc_html( $atts['name'] )
		. '</a></h2>'
	);

	return $html;
}
```

**Note:** 
1. Name attribute defaults to the word "Download"
1. Name is usedin combination with the other attribbutes user & repo.

== Contribute/Issues/Feedback ==
If you have any feedback, just write an issue. Or fork the code and submit a PR [on Github](https://github.com/bahiirwa/Latest-Github-Release).

== Changelog ==

** 2.1.0 **
- Test for WP 6.0
- Add HTML Wrap to facilitate an HTML Wrap code around the link.

** 2.0.0 **
- Updates the plugin to use static functions and to have code that is re-usable by other plugins.

** 1.2.0 **
- Fix Caching errors.
- Better docs.
- Coding style/consistency fixes.

** 1.1.0 **
- Code improvements.

** 1.0.0 **
- Initial Release.
