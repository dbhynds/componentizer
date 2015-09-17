# Componentizer

## Installation

1. Install Componentizer
1. Configure Componentizer
1. Create Components
1. Create Advanced Custom Field Groups
1. Associate Groups and Components
1. Update Theme Files

### Install Componentizer
1. Install and enable the Advanced Custom Fields plugin.
1. Clone Componetizer into your theme directory.
1. Navigate to the newly created `componentizer` directory.
1. Rename `config-sample.php` to `config.php`.
1. Create a folder titled `components` is the root of your theme file.
1. Add `require('componentizer/componentizer.php');` to your theme's `functions.php` file.

### Configure Componentizer
Componentizer allows you to configure several variables in `config.php`. For help configuring Componentizer for your site, navigate to the Compontentizer page under Appearance
#### $component\_path
The component path is the relative path in your theme where component template files are located. In step 4 of the install, we set this to `components`. However, you can move or rename or move as long as it's located within your theme.
#### $persistant\_fields
These are fields that appear in the back and front end but aren't ACF field groups. WordPress' content editor is included by default, but others can be added or removed if desired.
#### $exclude_order\_for\_post\_types
The Component Order metabox will appear on all public post types except those specified here.  The default is `nav_menu_item`, `revision`, and `attachment`.

### Create Components
Build component files and place them in the `$component_path` directory.

The same logic used for the WordPress template hierarchy is employed when choosing which component file to use. Each component must have a base component file. From there, components files can vary according to the logic of [primary and secondary templates](https://developer.wordpress.org/themes/basics/template-hierarchy/#visual-overview).

For example, a base component named `content.php` will be the default component used. However, if viewing an individual post or page, a component named `content-singular.php` will override `content.php`. Similarly `content-single.php` or `content-page.php` will override `content-singular.php` when viewing a post or page respectively.

**Base components files should use underscores (`_`) instead of hyphens (`-`) in their names. For example, the file for "Page Header" should be named `page_header.php`, not `page-header.php`.**

![WordPress Template Hierarchy](https://developer.wordpress.org/files/2014/10/template-hierarchy.png)

All primary and secondary templates (dark and light blue in the above images) will work for components. Most variable templates (orange) are not currently implemented for Componentizer. However, there are a few exceptions. Variable templates work for the following templates:

* custom page templates
* custom post types
* custom post type archives
* custom taxonomies

### Create Field Groups
Create Advanced Custom Field groups to associate with component files. Each base component should have a field group associated with it. Multiple field groups can be created for the same component. However field groups variations should use identically `Field Names` (although labels can vary). Fields can be added or removed from variations.

### Associate Groups and Components
Once field groups and component files have been created, navigate to "Settings > Componentizer" in the WordPress admin. Assign each field group to a base component. Assign it a location if field groups should appear at the top or bottom of a page and not be sortable by the authors.

After saving locations for field groups, reorder the top and bottom sections under "Location Orders". **Important: After changing the location of a field from sortable to top or bottom, the order must be resaved in order to go into effect. If you change a field group to top or bottom, save the page twice.**

Finally designate which field groups should be visible on archive pages.

### Update Theme Files
After connecting groups and components, update your theme files. Replace your loop with the following code:

```
while (have_posts()) :
	the_post();
	Components\build();
endwhile;
```

After replacing the loop in the `index.php`, most other single and archive page template files can be removed. See Advanced Configuration for details.

## Advanced Configuration

### Custom Page Templates
Assuming the loop has been replaced in the `index.php` file, the following code can be used to create custom page templates:

```
<?php
/**
 * Template Name: Custom Template
 */
require('index.php'); ?>
```

## Questions
Can the fields on the page reflect the order in the sidebar?