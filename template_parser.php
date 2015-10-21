<?php namespace Templates;

/**
 * Configuration
 */

// Relative to the theme's directory
$html_template_path = 'html_components';
/**
 * Usage
 * Include the following code in your php file:
 * 
 *   Templates/parse();
 *   
 */

/**
 * That's all.
 */

// Set the directory where the templates will live as a constant
define(__NAMESPACE__ . '\DIR',get_stylesheet_directory().'/'.$html_template_path);

/**
 * Scrape the directory for the defined file types.
 * @param  array $file_types File extensions to look for in the directory
 */
function register_files( $file_types = false ) {
  if (is_admin()) {
    if (!$file_types) {
      $file_types = array('svg','html','kit');
    }
    $files = scandir(DIR);
    $db_entry = array();
    if ($files) foreach ($files as $file) {
      $file = DIR.'/'.$file;
      $file_ext = pathinfo($file, PATHINFO_EXTENSION);

      if (in_array($file_ext, $file_types)==1) {
        $file_name = basename($file, '.'.$file_ext);
        if (array_key_exists($file_name, $db_entry)) {
          $db_entry[$file_name.'.'.$file_ext] = file_get_contents($file);
        } else {
          $db_entry[$file_name] = file_get_contents($file);
        } // if else end
      } // if end
    } // foreach end

    update_option('parsehtml', $db_entry);
  }
}
add_action('after_setup_theme',  __NAMESPACE__ . '\register_files');

/**
 * Retrieve the template HTML from the database.
 * @param  mixed  $template_name String or Array.
 *                               If string, name of the template you want without ".html"
 *                               If Array:
 *                                 $template[0] should be the name of the template you want without ".html"
 *                                 $template[1] should be the part of the template you want
 * @return string                HTML of the template
 */

function get_part( $template_name ) {
  if (is_string($template_name)) {
    $part = false;
  } else {
    $part = $template_name[1];
    $template_name = $template_name[0];
  }

  if ( $part ) {
    $templates = get_option('parsehtml');
    $template = explode('<!-- '.$part.' -->', $templates[$template_name]);
    return $template[1];
  } else {
    $templates = get_option('parsehtml');
    return $templates[$template_name];
  }
}
 
/**
 * Parse a template with the provided content
 * @param  mixed  $part     String or Array.
 *                          If string, name of the template you want without ".html"
 *                          If Array:
 *                            $part[0] should be the name of the template you want without ".html"
 *                            $part[1] should be the part of the template you want
 * @param  array $content   The content that should be injected into the template
 * @return string           HTML of the template
 */
function parse( $part, $content = array() ) {
  $template = get_part($part);

  if (!empty($content)) {
    foreach ($content as $key => $value) {

      if ( mb_substr($key, 0,2) == '{{' ) {
        $template = explode($key, $template);
        $temp = explode('}}', $template[1],2);
        $template[1] = $temp[0];
        array_push($template, $temp[1]);
      } else {
        $template = explode('<!-- '.$key.' -->', $template);
      }

      if (isset($template[1]) && substr($template[1], -1) == '"') {
        //gets the attributes start position
        $attribute_start = strpos($template[1], '="');
        //gets name of the attrubte
        $name = mb_substr($template[1], 0, $attribute_start);
        //removes the guts of the attribute
        $guts = mb_substr($template[1], $attribute_start, -1);
        $template[1] = $name.'="'.$value.'"';
      } else {
        $template[1] = $value;
      }

      $template = implode('', $template);

    } // end foreach
  } // end if !empty

  return preg_replace('/<!--(.*)-->/Uis', '', $template);
}