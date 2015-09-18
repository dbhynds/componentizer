<?php

namespace Components\Admin;

use Components\Options as Options;

// Don't bother on the front end or non-admins
if (!is_admin() && !current_user_can('edit_componentizer_options')) return;

class SettingsPage extends ComponentizerAdmin {

  function __construct() {
    // Load up options
    $this->options = Options\get_options();
    $this->location_orders = get_option('componentizer_location_orders');
    // Add the reference page to the admin menu
    add_action( 'admin_menu', array($this,'add_menu_page'), 20 );
    // Register Settings
    add_action( 'admin_init', array($this,'register_settings') );

  }

  // Add reference page to the Appearance menu
  function add_menu_page() {
    add_theme_page( __('Componentizer','componentizer'), __('Componentizer','componentizer'), 'manage_options', 'componentizer', array($this,'assign_components_to_templates') );
  }
  function register_settings() {
    $this->component_templates = $this->get_component_templates();
    register_setting( 'componentizerSettings', 'componentizer_fields' );
    add_settings_section(
      'componentizer_fields',
      __( 'Field Groups', 'componentizer' ),
      array($this,'assign_field_groups'),
      'componentizerSettings'
    );
    register_setting( 'componentizerSettings', 'componentizer_location_orders' );
    add_settings_section(
      'componentizer_location_orders',
      __( 'Location Orders', 'componentizer' ),
      array($this,'assign_location_orders'),
      'componentizerSettings'
    );
    register_setting( 'componentizerSettings', 'componentizer_visible_on_archive' );
    add_settings_section(
      'componentizer_visible_on_archive',
      __( 'Visible on Archive Pages', 'componentizer' ),
      array($this,'assign_visible_on_archive'),
      'componentizerSettings'
    );
  }
  function assign_field_groups() {
    $options = get_option( 'componentizer_fields' );
    // var_dump($options);
    // List all ACF Field Groups and their associated base components
    $acf_fields = get_posts([
      'post_type' => 'acf',
      'posts_per_page' => -1,
      'order' => 'ASC',
      'orderby' => 'title',
      ]);
    echo '<h4>'.__('Advanced Custom Fields Groups','componentizer').'</h4>';
    if ($acf_fields && count($acf_fields)) {
      echo '<table id="acf_field_groups" class="wp-list-table widefat fixed striped">';
      echo '<thead>
        <tr>
          <th scope="col" id="title" class="manage-column column-title">'.__('Title','componentizer').'</th>
          <th scope="col" id="base-component" class="manage-column column-base-component">'.__('Base Component','componentizer').'</th>
          <th scope="col" id="location" class="manage-column column-location">'.__('Location','componentizer').'</th>
        </tr>
      </thead>
      <tbody>';
      foreach ($acf_fields as $acf_field) {
        $field_id = $acf_field->ID;
        $template = '<select name="componentizer_fields['.$field_id.'][template]">';
        $template .= '<option value="">-- '.__('None','componentizer').' --</option>';
        $selected = $row_class = null;
        foreach ($this->component_templates as $base_component => $value) {
          if (isset($options[$field_id]['template'])) {
            $selected = ($options[$field_id]['template'] == $base_component) ? 'selected' : null;
            if ($options[$field_id]['template'] === "") $row_class = 'no-component';
          } else {
            $row_class = 'no-component';
          }
          
          $template .= '<option '.$selected.'>'.$base_component.'</option>';
        }
        $template .= '</select>';
        
        $in_top = $in_sortable = $in_bottom = null;
        if ($options[$field_id]['location'] == 'top') {
          $in_top = 'checked';
        } elseif ($options[$field_id]['location'] == 'bottom') {
          $in_bottom = 'checked';
        } else {
          $in_sortable = 'checked';
        }
        
        echo '<tr class="'.$row_class.'">';
        // echo '<td>'.$field_id.'</td>';
        echo '<td>'.$acf_field->post_title.'</td>';
        echo '<td>'.$template.'</td>';
        echo '<td>';
          echo '<label for="'.$field_id.'_top">';
            echo '<input type="radio" id="'.$field_id.'_top" name="componentizer_fields['.$field_id.'][location]" '.$in_top.' value="top">';
            _e('Top','componentizer');
          echo '</label> ';
          echo '<label for="'.$field_id.'_sortable">';
            echo '<input type="radio" id="'.$field_id.'_sortable" name="componentizer_fields['.$field_id.'][location]" '.$in_sortable.' value="sortable">';
            _e('Sortable','componentizer');
          echo '</label> ';
          echo '<label for="'.$field_id.'_bottom">';
            echo '<input type="radio" id="'.$field_id.'_bottom" name="componentizer_fields['.$field_id.'][location]" '.$in_bottom.' value="bottom">';
            _e('Bottom','componentizer');
          echo '</label> ';
        echo '</td>';
        echo '</tr>';
      }
      echo '</tbody>';
      echo '</table>';
    }
    submit_button();


    echo '<h4>'.__('Persistant Fields Groups','componentizer').'</h4>';
    $persistant_fields = $this->options['persistant_fields'];
    if (count($persistant_fields)) {
      echo '<table id="acf_field_groups" class="wp-list-table widefat fixed striped">';
      echo '<thead>
        <tr>
          <th scope="col" id="id" class="manage-column column-id">'.__('ID','componentizer').'</th>
          <th scope="col" id="base-component" class="manage-column column-base-component">'.__('Base Component','componentizer').'</th>
          <th scope="col" id="location" class="manage-column column-location">'.__('Location','componentizer').'</th>
        </tr>
      </thead>
      <tbody>';
      foreach ($persistant_fields as $persistant_field) {
        $field_id = $persistant_field;
        $template = '<select name="componentizer_fields['.$field_id.'][template]">';
        $template .= '<option>-- '.__('None','componentizer').' --</option>';
        $selected = $row_class = null;
        foreach ($this->component_templates as $base_component => $value) {
          if (isset($options[$field_id]['template'])) {
            $selected = ($options[$field_id]['template'] == $base_component) ? 'selected' : null;
          } else {
            $row_class = 'no-component';
          }
          
          $template .= '<option '.$selected.'>'.$base_component.'</option>';
        }
        $template .= '</select>';
        $in_top = $in_sortable = $in_bottom = null;
        if ($options[$field_id]['location'] == 'top') {
          $in_top = 'checked';
        } elseif ($options[$field_id]['location'] == 'bottom') {
          $in_bottom = 'checked';
        } else {
          $in_sortable = 'checked';
        }
        echo '<tr class="'.$row_class.'">';
        echo '<td>'.$field_id.'</td>';
        echo '<td>'.$template.'</td>';
        echo '<td>';
          echo '<label for="'.$field_id.'_top">';
            echo '<input type="radio" id="'.$field_id.'_top" name="componentizer_fields['.$field_id.'][location]" '.$in_top.' value="top">';
            _e('Top','componentizer');
          echo '</label> ';
          echo '<label for="'.$field_id.'_sortable">';
            echo '<input type="radio" id="'.$field_id.'_sortable" name="componentizer_fields['.$field_id.'][location]" '.$in_sortable.' value="sortable">';
            _e('Sortable','componentizer');
          echo '</label> ';
          echo '<label for="'.$field_id.'_bottom">';
            echo '<input type="radio" id="'.$field_id.'_bottom" name="componentizer_fields['.$field_id.'][location]" '.$in_bottom.' value="bottom">';
            _e('Bottom','componentizer');
          echo '</label> ';
        echo '</tr>';
      }
      echo '</tbody>';
      echo '</table>';
    }
    submit_button();
  }
  function assign_location_orders() {
    if (is_array($this->location_orders)) {
      $top_fields = (array_key_exists('top', $this->location_orders)) ? $this->location_orders['top'] : array();
      $bottom_fields = (array_key_exists('bottom', $this->location_orders)) ? $this->location_orders['bottom'] : array();
    } else {
      $top_fields = $bottom_fields = array();
    }

    $fields = get_option( 'componentizer_fields' );

    $new_bottom_fields = $new_top_fields = array();
    foreach ($fields as $field_id => $field) {
      $field_id = (string)$field_id;
      if ($field['location'] == 'top') array_push($new_top_fields, $field_id);
      if ($field['location'] == 'bottom') array_push($new_bottom_fields, $field_id);
    }

    foreach ($top_fields as $key => $top_field) {
      if (!in_array($top_field, $new_top_fields)) unset($top_fields[$key]);
    }
    $new_top_fields = array_merge($top_fields,$new_top_fields);
    $top_fields = array_unique($new_top_fields);

    foreach ($bottom_fields as $key => $bottom_field) {
      if (!in_array($bottom_field, $new_bottom_fields)) unset($bottom_fields[$key]);
    }
    $new_bottom_fields = array_merge($bottom_fields,$new_bottom_fields);
    $bottom_fields = array_unique($new_bottom_fields);
    
    echo '<div class="card">';
      echo '<h4>'.__('Top Components','componentizer').'</h4>';
      echo '<div class="component-order-sort-wrap">';
      echo '<div id="order-top-components" class="order-components component-order-sort">';
      foreach ($top_fields as $top_field) {
        $title = get_the_title($top_field);
        if (!$title) $title = ucwords($top_field);
        echo '<div class="postbox component">';
        echo '<input type="checkbox" name="componentizer_location_orders[top][]" value="'.$top_field.'" checked style="display: none;" />';
        echo '<span class="sortable ui-sortable-handle">'.$title.'</span>';
        echo '</div>';
      }
      echo '</div>';
      echo '</div>';
    echo '</div>';

    echo '<div class="card">';
      echo '<h4>'.__('Bottom Components','componentizer').'</h4>';
      echo '<div class="component-order-sort-wrap">';
      echo '<div id="order-bottom-components" class="order-components component-order-sort">';
      foreach ($bottom_fields as $bottom_field) {
        $title = get_the_title($bottom_field);
        if (!$title) $title = ucwords($bottom_field);
        echo '<div class="postbox component">';
        echo '<input type="checkbox" name="componentizer_location_orders[bottom][]" value="'.$bottom_field.'" checked style="display: none;" />';
        echo '<span class="sortable ui-sortable-handle">'.$title.'</span>';
        echo '</div>';
      }
      echo '</div>';
      echo '</div>';
    echo '</div>';

    submit_button();
    
  }

  function assign_visible_on_archive() {
    $options = get_option( 'componentizer_visible_on_archive' );
    if ($options === false) $options = array();
    // var_dump($options);
    // List all ACF Field Groups and their associated base components
    $acf_fields = get_posts([
      'post_type' => 'acf',
      'posts_per_page' => -1,
      'order' => 'ASC',
      'orderby' => 'title',
      ]);
    echo '<div id="visible-on-archive" class="card">';
    if ($acf_fields && count($acf_fields)) {
      foreach ($acf_fields as $acf_field) {
        $checked = (in_array($acf_field->ID, $options)) ? 'checked' : null;
        // var_dump($acf_field);
        echo '<label for="'.$acf_field->ID.'">';
          echo '<input type="checkbox" id="'.$acf_field->ID.'" name="componentizer_visible_on_archive[]" '.$checked.' value="'.$acf_field->ID.'">';
          echo $acf_field->post_title;
        echo '</label> ';
      }
    }
    $persistant_fields = $this->options['persistant_fields'];
    if ($persistant_fields && count($persistant_fields)) {
      foreach ($persistant_fields as $persistant_field) {
        // var_dump($persistant_field);
        $checked = (in_array($persistant_field, $options)) ? 'checked' : null;
        echo '<label for="'.$persistant_field.'">';
          echo '<input type="checkbox" id="'.$persistant_field.'" name="componentizer_visible_on_archive[]" '.$checked.' value="'.$persistant_field.'">';
          echo ucwords($persistant_field);
        echo '</label> ';
      }
    }
    echo '</div>';
    submit_button();
  }


  function assign_components_to_templates() {
    ?>
    <div id="componentizer-settings" class="wrap">
      <?php 
      echo '<h1>'.__('Compontentizer','componentizer').'</h1>';
      echo '<form id="basic" action="options.php" method="post" style="clear:both;">';
        settings_fields( 'componentizerSettings' );
        do_settings_sections( 'componentizerSettings' );
      echo '</form>';

      // List the base components and their subsidiary files
      echo '<h2>'.__('Component Files','componentizer').'</h2>';
      echo '<p>'.__('These files are located in the <code>'.Options\COMPONENT_PATH.'</code> directory of your theme.','componentizer').'</p>';
      echo '<table class="wp-list-table widefat fixed striped">';
      echo '<thead>
        <tr>
          <th scope="col" id="base-components" class="manage-column column-base-components column-primary">'.__('Base Components','componentizer').'</th>
          <th scope="col" id="suffixes" class="manage-column column-suffixes column-primary">'.__('Suffixes','componentizer').'</th>
          <th scope="col" id="base-files" class="manage-column column-base-files column-primary">'.__('Base Files','componentizer').'</th>
          <th scope="col" id="sub-files" class="manage-column column-sub-files column-primary">'.__('Sub Files','componentizer').'</th>
        </tr>
      </thead>
      <tbody>';
      foreach ($this->component_templates as $base_component => $sub_component) {
        echo '<tr>';
          echo '<td>'.$base_component.'</td>';
          echo '<td>'.implode('<br />',$sub_component).'</td>';
          echo '<td>'.$base_component.'.php</td>';
          $sub_components = array();
          foreach ($sub_component as $sub_component_single) {
            if ($sub_component_single !== '') {
              array_push($sub_components, $base_component.'-'.$sub_component_single.'.php');
            }
          }
          echo '<td>'.implode('<br />',$sub_components).'</td>';
        echo '</tr>';
      }
      echo '</tbody>';
      echo '</table>';

      ?>
    </div>
    <?php
  }

  function get_component_templates() {
    $component_files = scandir(get_stylesheet_directory().'/'.Options\COMPONENT_PATH);
    $ignore_files = array('.','..');
    foreach ($component_files as $component_file) {
      if (!in_array($component_file, $ignore_files)) {
        $component_name = explode('-',str_replace('.php', '', $component_file));
        $component_base = array_shift($component_name);
        $component_templates[$component_base][] = implode('-', $component_name);
      }
    }
    return $component_templates;
  }

}

// Initialize settings page
new SettingsPage();
