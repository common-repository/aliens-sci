=== Custom fields to api ===
Contributors: 4samy
Tags: Custom fields, api
Requires at least: 1.0.1
Tested up to: 4.6.1
Stable tag: 2.9
License: MIT
License URI: http://opensource.org/licenses/MIT

Puts all code from Custom fields to api.
== Description ==

Puts all code from Custom fields to api.
== Installation ==

1. Unzip and upload the `aliens-sci` directory to `/wp-content/plugins/`.
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= requires you to add the following to your functions.php file to allow filtering on the WP-API by meta_key =

add_filter( 'json_query_vars', 'filterJsonQueryVars' );

function filterJsonQueryVars( $vars ) {
    $vars[] = 'meta_key';
    return $vars;
}

= Once you added that, you can filter using these arguments =

dowmian.com/wp-json/posts?filter[orderby]=meta_value_num&filter[meta_key]=order&filter[order]=ASC

= Usage =


filter[orderby]: Either meta_value or meta_value_num depending on whether you're filtering on an alphanumeric value or a numeric value
filter[meta_key]: The key you want to filter on, this is the name of the ACF field
filter[order]: The order to receive the data in, ASC or DESC

See the `CONTRIBUTING.md` file.

= How can I filter posts on a custom field?

== Screenshots ==

== Changelog ==

= 1.0.0 =

* Initial release.

### 1.0.1

* Bug fixes and performance improvements.