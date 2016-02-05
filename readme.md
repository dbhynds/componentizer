# Componentizer

1. [Requirements](#requirements)
1. [Installation](#installation)
1. [Advanced Configuration](#advanced-configuration)
1. [Feature Requests](#feature-requests)
1. [Additional Information](#additional-information)

## Requirements

* [PHP](http://php.net/manual/en/install.php) >= 5.4
* [WordPress](https://wordpress.org/download/) >= 3.4.0
* [Advanced Custom Fields Pro Plugin](http://www.advancedcustomfields.com/pro/) >= 5.3.3.2

## Installation

1. [Install Componentizer](#install-componentizer)
1. [Configure Componentizer](#configure-componentizer)
1. [Create Components](#create-components)
1. [Create Field Groups](#create-field-groups)
1. [Associate Groups and Components](#associate-groups-and-components)
1. [Update Theme Files](#update-theme-files)

### Install Componentizer
1. Install and enable the Advanced Custom Fields Pro plugin.
1. Clone Componetizer into your theme directory.
1. Navigate to the newly created `componentizer` directory.
1. Rename `config-sample.php` to `config.php`.
1. Create a folder titled `controllers` is the root of your theme file.
1. Add `require(get_stylesheet_directory().'/'.componentizer/componentizer.php');` to your theme's `functions.php` file.

### Create Controllers
Build controllers files and place them in the `controllers` directory. (This directory can be changed in `config.php`).

The same logic used for the WordPress template hierarchy is employed when choosing which controller file to use. Each controller must have a base controller file. From there, components files can vary according to the logic of [primary and secondary templates](https://developer.wordpress.org/themes/basics/template-hierarchy/#visual-overview).

For example, a base controller named `content.php` will be the default controller used. However, if viewing an individual post or page, a controller named `content-singular.php` will override `content.php`. Similarly `content-single.php` or `content-page.php` will override `content-singular.php` when viewing a post or page respectively.

**Base components files should use underscores (`_`) instead of hyphens (`-`) in their names. For example, the file for "Page Header" should be named `page_header.php`, not `page-header.php`.**

![WordPress Template Hierarchy](https://developer.wordpress.org/files/2014/10/template-hierarchy.png)

All primary and secondary templates (dark and light blue in the above images) will work for components. Most variable templates (orange) are not currently implemented for Componentizer. However, there are a few exceptions. Variable templates work for the following templates:

* custom page templates (`basecontroller-$custom.php`)
* custom post types (`basecontroller-single-$posttype.php`)
* custom post type archives (`basecontroller-archive-$posttype.php`)
* custom taxonomies (`basecontroller-taxonomy-$taxonomy.php`)

### Create Field Groups
Create Advanced Custom Field groups to associate with controller files. Each base controller should have a field group associated with it. Multiple field groups can be created for the same controller.

### Associate Groups and Components
Once field groups and controller files have been created, navigate to "Componentizer > Field Groups" in the WordPress admin. Assign each field group to a base controller. Assign it a location if field groups should appear at the top or bottom of a page and not be sortable by the authors.

After saving locations for field groups, reorder the top and bottom sections under "Componentizer > Location Orders".

Finally designate which field groups should be visible on archive pages under "Componentizer > Visible on Archive".

### Update Theme Files
After connecting groups and controllers, update your theme files. Replace the loop on `index.php` with the following code:

```
while (have_posts()) :
	the_post();
	Components\build();
endwhile;
```

After replacing the loop in the `index.php`, most other single and archive page template files can be removed. See Advanced Configuration for details.

## Advanced Configuration

Componentizer allows you to configure several variables in `config.php`.

#### $controller\_path
The controller path is the relative path in your theme where controller files are located. In step 5 of the install, we set this to `controllers`. However, you can move or rename it as long as it's located within your theme.
#### $persistant\_fields
These are fields that appear in the back and front end but aren't ACF field groups. WordPress' content editor is included by default, but others can be added or removed if desired.
#### $exclude_order\_for\_post\_types
The Component Order metabox will appear on all public post types except those specified here.  The default is `nav_menu_item`, `revision`, and `attachment`.

### Custom Page Templates
Assuming the loop has been replaced in the `index.php` file, the following code can be used to create custom page templates:

```
/**
 * Template Name: Custom Template
 */
require('index.php');
```

### Override User-defined Order
Component order can be specified on a template level in order to override the user-defined order by passing an array of base component slugs.

```
$custom_order = array(
	'page_header',
	'content'
	'comments'
);
Components\build($custom_order);
```

To view available component slugs, go to "Appearance > Componentizer" and look at the section labelled "Component Files"

### Specify a Custom Suffix
Suffixes get appended to the base template to locate the appropriate component according to the template hierarchy. However, a custom suffix(es) can be designated.

Specify a custom suffix:

```
$suffix = 'special';
Components\build(false,$suffix);
```

Specify a collection of custom suffixes:

```
$suffixes = array(
	'extra-special',
	'special'
);
Components\build(false,$suffixes);
```

Note: `false` should be passed as the first argument in order to not override the user-specified component order.

## Feature Requests

* Fields on the page reflect the order in the sidebar.

## Additional Information

### Version: 0.10