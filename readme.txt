=== The Events Calendar Extension: Custom Category Filter ===
Contributors: theeventscalendar
Donate link: https://evnt.is/29
Tags: events, calendar
Requires at least: 4.9
Tested up to: 5.7
Requires PHP: 5.6
Stable tag: 1.0.0
License: GPL version 3 or any later version
License URI: https://www.gnu.org/licenses/gpl-3.0.html



== Description ==

This is the long description.  No limit, and you can use Markdown (as well as in the following sections).

For backwards compatibility, if this section is missing, the full length of the short description will be used, and Markdown parsed.

== Installation ==

Install and activate like any other plugin!

* You can upload the plugin zip file via the *Plugins ‣ Add New* screen
* You can unzip the plugin and then upload to your plugin directory (typically _wp-content/plugins_) via FTP
* Once it has been installed or uploaded, simply visit the main plugin list and activate it

== Setup and Customization ==
Each filter group has a `tec_labs_custom_category_{$alias}_filter_wanted_categories` filter, where {$alias} is replaced with the
filer-specific alias (the number 1-5)

This extension is set up to run one custom category filter group. The `tec_labs_custom_category_filter_groups_number` filter
lets you add additional filter groups (up to 5!). It defaults to one (1) but you can filter this value up to five with
no modifications to this extension. If you need more (!) you will have to copy one of the classes in src/Tec/Filters
and rework it for the new number.

== Frequently Asked Questions ==

= Where can I find more extensions? =

Please visit our [extension library](https://theeventscalendar.com/extensions/) to learn about our complete range of extensions for The Events Calendar and its associated plugins.

= What if I experience problems? =

We're always interested in your feedback and our [Help Desk](https://support.theeventscalendar.com/) are the best place to flag any issues. Do note, however, that the degree of support we provide for extensions like this one tends to be very limited.

== Changelog ==

= [1.0.0] YYYY-MM-DD =

* Initial release
