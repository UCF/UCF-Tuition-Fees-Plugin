# UCF Tuition and Fees Plugin #

Provides an import script for importing tuition data for degrees, as well as a shortcode for displaying tuition and fees data.

## Description ##

Provides an import script for importing tuition data for degrees, as well as a shortcode for displaying tuition and fees data.


## Installation ##

### Manual Installation ###
1. Upload the plugin files (unzipped) to the `/wp-content/plugins` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the "Plugins" screen in WordPress
3. Configure plugin settings from the WordPress admin under "Settings > UCF Tuition and Fees".

### WP CLI Installation ###
1. `$ wp plugin install --activate https://github.com/UCF/UCF-Tuition-Fees-Plugin/archive/master.zip`.  See [WP-CLI Docs](http://wp-cli.org/commands/plugin/install/) for more command options.
2. Configure plugin settings from the WordPress admin under "Settings > UCF Tuition and Fees".


## Changelog ##

### 2.1.4 ###
Enhancements:
* Added composer file.

### 2.1.3 ###
Enhancements:
* Added ability to skip tuition during import

### 2.1.2 ###
Enhancements:
* Creates an additional function/filter for determining if a fee should be added to the total imported.

### 2.1.1 ###
Enhancements:
- Updated the tuition import script to utilize a mappings JSON file for tuition code overrides instead of a hard-coded list.

### 2.1.0 ###
Enhancements:
- Updated tuition data import script for compatibility with the new UCF Search Service and Degree CPT Plugin updates
- Updated import script to account for unique online program schedule codes

### 2.0.2 ###
Enhancements:
- Added importer from the [Main-Site-Utilities-Plugin](https://github.com/UCF/Main-Site-Utilities-Plugin) to this plugin.
- Added option to override post_type as an assoc_arg: `wp tuition import https://url-to-data.edu --post-type=<post_type>`.

### 2.0.1 ###
Changes listed for v2.0.0 were not committed properly to master--v2.0.1 fixes this.

### 2.0.0 ###
Enhancements:
- Updated main 'display' hook to a filter instead of an action, and consolidated the arguments passed to it into an array (`$args`)
- Updated `UCF_Tuition_Fees_Common::display()` to return its value

Bugfixes:
- Added missing first 'category' arg in `setlocale()`
- Fixed duplicate dollar signs in some monetary values

### 1.0.0 ###
* Initial release


## Upgrade Notice ##

### 2.1.0 ###
Note that the import script command options have changed in v2.1.0--notably, the URL of the tuition and fees feed must now be explicitly included in the `api` parameter if you wish to provide a custom override; e.g. `$ wp tuition import --api="https://finacctg.fa.ucf.edu/sas/feed/feed.cfm"`.  The base feed url set in plugin options will be used as the default if one isn't provided in the command.


## Installation Requirements ##

None


## Development & Contributing ##

NOTE: this plugin's readme.md file is automatically generated.  Please only make modifications to the readme.txt file, and make sure the `gulp readme` command has been run before committing readme changes.
