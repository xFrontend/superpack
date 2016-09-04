=== SuperPack ===

Contributors: xFrontend, moonomo
Donate link: https://xfrontend.com/
Tags: blockquote, column, dropcap, instagram, random posts, recent comments, recent posts, related posts, row, shortcodes, social icons, sticky posts, widgets
Requires at least: 4.1
Tested up to: 4.6
Stable tag: 0.3.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html

Features half-dozen shortocdes, half-dozen widgets, and seamless API for Author's contact-fields.

== Description ==

Provides half-dozen shortocdes, half-dozen widgets, and seamless API for Author's contact-fields.

= Shortcodes =

* `[dropcap]` - Add capital letter at the beginning of a text block that has the depth of two or more lines of regular text.
* `[blockquote]` - Add quotes that floats to the left, or right side of the content, including non-floating blockquote.
* `[code]` - Display Code on your blog.
* `[column]` - Split content into columns with responsive grid-system.
* `[row]` - Group columns into row.
* `[social-icons]` - Add your social links with icons.

More details on shortcodes are given on [FAQ](https://wordpress.org/plugins/superpack/faq/).

= Widgets =

* `About (SuperPack)` - Display your bio/site info on your site.
* `Comments (SuperPack)` - Display the most recent comments (with Gravatar).
* `Instagram (SuperPack)` - Display your latest Instagram photos on your site.
* `Posts (SuperPack)` - Display the blog posts with thumbnails. Includes Recent Posts, Random Posts, Sticky Posts and Related Posts
* `Social Icons (SuperPack)` - Display social profile link icons (require theme supports).
* `Tags (SuperPack)` - Display your most used tags.

= Contact Fields =

SuperPack provides a seamless and powerful API to allow custom contact-fields for Author's Profile. Default fields includes `Facebook URL`, `Twitter URL`, `Google+ URL`, `Linkedin URL` and `Public Email`. Requires a compatible theme.

= What it does & doesn't =

* Does: It provides the features/functions, and handle user generated data/options.
* Doesn't: Styling, a compatible theme should be installed for optimum outlook and all features to work as expected.

= Compatible Themes =

* [Snowbird - Personal WordPress Blog Theme](https://xfrontend.com/themes/snowbird-wordpress-theme/)


= Support =

Provided as is without any support. However feel free to reach the author for feature request, and report if you found any bugs. ;)


== Installation ==

Manual installation:

1. [Download](https://downloads.wordpress.org/plugin/superpack.zip) and extract the zip into your `/wp-content/plugins/` directory.
1. `Activate` the plugin through the 'Plugins' menu in WordPress.

Installation from WordPress.org repository using "Add New Plugin"

1. From your Admin Dashboard, use the menu to select Plugins -> Add New
1. Search for `SuperPack`
1. Click the `Install` button
1. Click the `Activate` button


== Frequently Asked Questions ==

= How do I use the shortcodes? =

Following are the options per shortcodes:

= DROPCAP =

`[dropcap id="" class=""] ... [/dropcap]`
* id: add a unique ID to the shortcode.
* class: add a class or multiple classes to the shortcode.

= BLOCKQUOTE =

`[blockquote type="" cite="" id="" class=""] ... [/blockquote]`
* type: "left", "right", skip adding anything to display regular blockquote.
* cite: accepts any text.
* id: add a unique ID to the shortcode.
* class: add a class or multiple classes to the shortcode.

= CODE =

`[code id="" class=""] ... [/code]`
* id: add a unique ID to the shortcode.
* class: add a class or multiple classes to the shortcode.

= SOCIAL-ICONS =

`[social-icons menu="" id="" class=""]`
* menu: set to a menu name/slug/id to display.
* id: add a unique ID to the shortcode.
* class: add a class or multiple classes to the shortcode.

= ROW =

`[row id="" class=""] ... [/row]`
* id: add a unique ID to the shortcode.
* class: add a class or multiple classes to the shortcode.
Use `[row]` shortcode around `[column]` shortcode to keep columns in a div that clears any floats, and adds a bottom margin to display properly with other contents.

= COLUMN =

`[column type="" id="" class="" last=""] ... [/column]`
* type: "1/1", "1/2", "1/3", "2/3", "1/4", "2/4", "3/4", "1/5", "2/5", "3/5", "4/5".
* last: set to "true" to remove the margin on the last column so that it properly floats.
* id: add a unique ID to the shortcode.
* class: add a class or multiple classes to the shortcode.


== Changelog ==

= v0.3.1 =
Release Date: September 4, 2016

* Fixes - Link output for Email Contact Fields.
* Code improvements.

= v0.3.0 =
Release Date: September 4, 2016

* Added - Contact Fields for Author's Profile.
* Fixes - jQuery deprecated notice.
* Code cleanups.

= v0.2.2 =
Release Date: November 15, 2015

* Shortcode supports for description field in 'About (SuperPack)' widget.
* Added unique class for Row shortcode output.


= v0.2.1 =
Release Date: October 3, 2015

* Fixes - `Select Image` button on 'About (SuperPack)' widget.
* Fixes - Media query CSS for Row/Column (the Grid).


= v0.2.0 =
Release Date: September 18, 2015

* Added Shortcodes: [dropcap], [blockquote], [code], [column], [row], [social-icons].
* Centralized Settings for Theme Supports.
* Added CSS for Row/Column (the Grid).
* Added minified Assets (CSS/JS).


= v0.1.1 =
Release Date: September 8, 2015

* Cleaned up codebase.
* Added - Widget settings. Theme authors can adjust settings via `add_theme_support`.
* Added - Social Icons Widget.
* Fixes - Updated Instagram Widget to properly fetch image data.


= v0.1.0 =
* Initial release.