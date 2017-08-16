=== UCF Tuition and Fees Plugin ===
Contributors: ucfwebcom
Tags: ucf, tuition
Requires at least: 4.7.5
Tested up to: 4.7.5
Stable tag: 2.0.0
License: GPLv3 or later
License URI: http://www.gnu.org/copyleft/gpl-3.0.html

Provides a shortcode for displaying tuition and fees

== Description ==

Provides a shortcode and related feed code for pulling UCF tuition and fees.


== Installation ==

= Manual Installation =
1. Upload the plugin files (unzipped) to the `/wp-content/plugins` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the "Plugins" screen in WordPress
3. Configure plugin settings from the WordPress admin under "Settings > UCF Tuition and Fees".

= WP CLI Installation =
1. `$ wp plugin install --activate https://github.com/UCF/UCF-Tuition-Fees-Plugin/archive/master.zip`.  See [WP-CLI Docs](http://wp-cli.org/commands/plugin/install/) for more command options.
2. Configure plugin settings from the WordPress admin under "Settings > UCF Tuition and Fees".


== Changelog ==

= 2.0.1 =
Changes listed for v2.0.0 were not committed properly to master--v2.0.1 fixes this.

= 2.0.0 =
Enhancements:
- Updated main 'display' hook to a filter instead of an action, and consolidated the arguments passed to it into an array (`$args`)
- Updated `UCF_Tuition_Fees_Common::display()` to return its value

Bugfixes:
- Added missing first 'category' arg in `setlocale()`
- Fixed duplicate dollar signs in some monetary values

= 1.0.0 =
* Initial release


== Upgrade Notice ==

n/a


== Installation Requirements ==

None


== Development & Contributing ==

NOTE: this plugin's readme.md file is automatically generated.  Please only make modifications to the readme.txt file, and make sure the `gulp readme` command has been run before committing readme changes.
