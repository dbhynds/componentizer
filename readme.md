# Componentizer

Componentizer is a tiny MVC for components, allowing users to create and rearrange components at will. Componentizer primarily handles the routing and controlling, utilizing Advanced Custom Fields PRO for the model layer and the Timber Library for the view layer. It allows you to replace the loop on every template file with, simply:

```
$components = new Componentizer\Components;
while (have_posts()) :
  the_post();
  $components->build();
endwhile;
```

Componentizer makes use of the WordPress template hierarchy, making it easy to customize the design of components depending on their context.

# Gettings Started

## Requirements

* PHP 5.5 or higher
* WordPress 3.4.0 or higher
* [Advanced Custom Fields PRO](https://www.advancedcustomfields.com/pro/) 5.3.8 or higher
* [Timber Library](https://wordpress.org/plugins/timber-library/) 1.0 or higher

## Installation

1. Install and enable the [Advanced Custom Fields PRO](https://www.advancedcustomfields.com/pro/) plugin.
1. Install and enable the [Timber Library](https://wordpress.org/plugins/timber-library/) plugin.
1. Install and enable the Componentizer plugin by downloading this repo and placing it into the `plugins` folder of the site.
1. In the WordPress admin, navigate to *Componentizer > Settings*.
1. Set the directory in the theme where the controllers will live (defaults to `controllers`).
1. Set which post types should use **not** the componentizer (defaults to attachment, revision, nav_menu_item, acf-field-group, acf-field).
1. Save the settings.

## Template Files

Componentizer is designed to simplify the template hierarchy in your theme. In a fully componentized theme, instead of having dozens of template files for post types, taxonomy terms, archives, pages, etc., the theme runs from a single file: `index.php` a simple loop:

```
$components = new Componentizer\Components();

while (have_posts()) :
  the_post();
  $components->build();
endwhile;
```

## Create Components

Create ACF field groups for each component. Then create a corresponding controller in the `controllers` directory specified in the settings. **Important:** Do not use dashes (`-`) when naming controllers; underscores (`_`) are acceptable, however.

In the WordPress admin, navigate to *Componentizer > Field Groups*. Assign the ACF Field Group to the base component (more on this later) that you just created.

Create a new `Componentizer\Context` and populate it with data from your ACF group. Set the properties of the context using the `set()` method.

```
$context = new \Componentizer\Context();

$context->property_key = 'First Property';
$context->set(['a_property' => 'A value', 'another_property', 'More value']);
$acf_data = get_fields();
$context->set($acf_data);
```

Once the `Context` has been populated with data, render or compile the component using Timber.

```
$context->render('my_component.twig');
$component_html = $context->compile();
```

If the ACF field names match the variables in the twig template, and the values of the fields don't need to be modified in any way, use the `simple_render` method to quickly render the view:

```
$context = new \Componentizer\Context();
$context->simple_render();
```

### Components & the Template Hierarchy

The same logic used for the WordPress template hierarchy is employed when choosing which component file to use. Each component must have a base component file. From there, components files can vary according to the logic of [primary and secondary templates](https://developer.wordpress.org/themes/basics/template-hierarchy/#visual-overview).

For example, a base component named `content.php` will be the default component used. However, if viewing an individual post or page, a component named `content-singular.php` will override `content.php`. Similarly `content-single.php` or `content-page.php` will override `content-singular.php` when viewing a post or page respectively.

**Base components files should use underscores (`_`) instead of hyphens (`-`) in their names. For example, the file for "Page Header" should be named `page_header.php`, not `page-header.php`.**

![WordPress Template Hierarchy](https://developer.wordpress.org/files/2014/10/template-hierarchy.png)

All primary and secondary templates (dark and light blue in the above images) will work for components. Most variable templates (orange) are not currently implemented for Componentizer. However, there are a few exceptions. Variable templates work for the following templates:

* custom page templates
* custom post types
* custom post type archives
* custom taxonomies

# Advanced Configuration

## Location Orders

The order that components in the top and bottom sections are not customizable on a per-post basis. However, the admin can assign a custom order to be applied globally on the site. Top and bottom sections can be reordered under "Componentizer > Location Orders".

## Visible on Archive

To limit which field group(s) should be visible on archive pages (as opposed to single posts/pages), navigate to "Componentizer > Visible on Archive."

### Custom Page Templates

Assuming the loop has been replaced in the `index.php` file, the following code can be used to create custom page templates:

```
/**
 * Template Name: Custom Template
 */
include (__DIR__.'/index.php');
```

### Custom Component Orders

Component order can be specified on a template level in order to override the user-defined order by passing an array of base component slugs. (To view available component slugs, go to "Appearance > Componentizer" and look at the section labelled "Component Files".)

`$components->set_components(['first']);`

Reset the list of components to its original state:

`$components->reset_components();`

Get the list of components:

$components->get_components();

### Custom Suffixes

Suffixes get appended to the base template to locate the appropriate component according to the template hierarchy. However, a custom suffix(es) can be designated.

Add an array of suffixes to the current list:

`$components->add_suffixes(['last']);`

Override the current list of suffixes with a new array of suffixes:

`$components->set_suffixes(['last']);`

Reset a list of suffixes to it's original state:

`$components->reset_suffixes();`

Get the list of suffixes:

`$components->get_suffixes();`
