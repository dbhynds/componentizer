# Componentizer

## Install
1. Install and enable the Advanced Custom Fields plugin.
1. Add componentizer to your theme files.
1. Rename `config-sample.php` to `config.php`.
1. Create a folder titled `components` is the root of your theme file.
1. Add `require('componentizer/componentizer.php');` to your theme's `functions.php` file.

## Configure
Componentizer allows you to configure several variables in `config.php`. For help configuring Componentizer for your site, navigate to the Compontentizer page under Appearance
### $component\_path
The component path is the relative path in your theme where component template files are located. In step 4 of the install, we set this to `components`. However, you can move or rename or move as long as it's located within your theme.
### $persistant\_fields
These are fields that appear in the back and front end but aren't ACF field groups. WordPress' content editor is included by default, but others can be added or removed if desired.
### $exclude_order\_for\_post\_types
The Component Order metabox will appear on all public post types except those specified here.  The default is `nav\_menu\_item`, `revision`, and `attachment`.

## Questions
Can the fields on the page reflect the order in the sidebar?