<?php

namespace Componentizer\Admin;

// Don't bother on the front end or non-admins
if (!is_admin()) return;

class SettingsPage extends Admin {

  function __construct() {
    // Load up options
    $this->location_orders = get_option('componentizer_location_orders');
    $this->settings = get_option('componentizer_advanced_settings');
    $this->json_path = get_stylesheet_directory().'/'.$this->settings['json_path'];
    // Add the reference page to the admin menu
    add_action( 'admin_menu', [$this,'add_menu_page'] );
    // Register settings
    add_action( 'admin_init', [$this,'register_settings'] );
    // Sync ajax
    // add_action( 'wp_ajax_sync', [$this,'ajax_sync_callback'] );
    // Sync ajax
    // add_action( 'wp_ajax_update_to_group_name', [$this,'ajax_update_to_group_name_callback'] );
  }

  // Add reference page to the Appearance menu
  function add_menu_page() {
    $slug = 'componentizer';
    $cap = 'manage_options';
    // add parent
    add_menu_page(__("Componentizer",$this::NS), __("Componentizer",$this::NS), $cap, $slug, false, 'dashicons-layout', 60, array($this,'assign_components_to_templates'));
    
    // add children
    add_submenu_page($slug, __('Field Groups',$this::NS), __('Field Groups',$this::NS), $cap, $slug, array($this,'assign_components_to_templates') );
    add_submenu_page($slug, __('Location Orders',$this::NS), __('Location Orders',$this::NS), $cap, 'location-orders', array($this,'assign_components_location_orders') );
    add_submenu_page($slug, __('Visible on Archive Pages',$this::NS), __('Visible on Archive',$this::NS), $cap, 'visible-on-archive', array($this,'assign_components_visible_on_archive') );
    add_submenu_page($slug, __('Settings',$this::NS), __('Settings',$this::NS), $cap, 'settings', array($this,'assign_components_advanced_settings') );
    // add_submenu_page($slug, __('Migrate',$this::NS), __('Migrate',$this::NS), $cap, 'migrate', array($this,'assign_migrate') );
    // if (JSON_PATH) add_submenu_page($slug, __('Sync Configuration',$this::NS), __('Sync Configuration',$this::NS), $cap, 'sync-configuration', array($this,'assign_options_for_sync') );
    
  }
  function register_settings() {

    // Save json data
    // add_filter( 'update_option_componentizer_fields', array($this, 'fields_updated') );
    // add_filter( 'update_option_componentizer_location_orders', array($this, 'location_orders_updated') );
    // add_filter( 'update_option_componentizer_visible_on_archive', array($this, 'visible_on_archive_updated') );
    // add_filter( 'update_option_componentizer_advanced_settings', array($this, 'advanced_settings_updated') );

    $this->component_templates = $this->get_component_templates();

    register_setting( 'componentizerSettings', 'componentizer_fields' );
    add_settings_section(
      'componentizer_fields',
      __( 'Field Groups', $this::NS ),
      array($this,'assign_field_groups'),
      'componentizerSettings'
    );

    register_setting( 'componentizerLocationSettings', 'componentizer_location_orders' );
    add_settings_section(
      'componentizer_location_orders',
      __( 'Location Orders', $this::NS ),
      array($this,'assign_location_orders'),
      'componentizerLocationSettings'
    );

    register_setting( 'componentizerArchiveSettings', 'componentizer_visible_on_archive' );
    add_settings_section(
      'componentizer_visible_on_archive',
      __( 'Visible on Archive Pages', $this::NS ),
      array($this,'assign_visible_on_archive'),
      'componentizerArchiveSettings'
    );

    register_setting( 'componentizerAdvancedSettings', 'componentizer_advanced_settings' );
    add_settings_section(
      'componentizer_advanced_settings',
      __( 'Advanced Settings', $this::NS ),
      array($this,'assign_advanced_settings'),
      'componentizerAdvancedSettings'
    );

  }
  function assign_field_groups() {
    $options = get_option( 'componentizer_fields' );
    // var_dump($options);
    // List all ACF Field Groups and their associated base components
    $acf_fields = $this->get_acfs();
    echo '<h3>'.__('Advanced Custom Fields Groups',$this::NS).'</h3>';
    if ($acf_fields && count($acf_fields)) {
      echo '<table id="acf_field_groups" class="wp-list-table widefat fixed striped">';
      echo '<thead>
        <tr>
          <th scope="col" id="title" class="manage-column column-title">'.__('Title',$this::NS).'</th>
          <th scope="col" id="component" class="manage-column column-component">'.__('Component',$this::NS).'</th>
          <th scope="col" id="base-component" class="manage-column column-base-component">'.__('Base Component',$this::NS).'</th>
          <th scope="col" id="location" class="manage-column column-location">'.__('Location',$this::NS).'</th>
        </tr>
      </thead>
      <tbody>';
      foreach ($acf_fields as $acf_field) {
        $field_id = $acf_field->post_name;
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
        
        $in_top = $in_sortable = $in_bottom = $is_component = null;
        if (array_key_exists($field_id, $options)) {
          if ($options[$field_id]['location'] == 'top') {
            $in_top = 'checked';
          } elseif ($options[$field_id]['location'] == 'bottom') {
            $in_bottom = 'checked';
          } else {
            $in_sortable = 'checked';
          }
          if ($options[$field_id]['component']) {
            $is_component = 'checked';
          }
        } else {
          $in_sortable = 'checked';
        }
        
        echo '<tr class="'.$row_class.'">';
        // echo '<td>'.$field_id.'</td>';
        echo '<td>'.$acf_field->post_title.'</td>';
        echo '<td>';
          echo '<input type="hidden" name="componentizer_fields['.$field_id.'][component]" value="0">';
          echo '<label for="'.$field_id.'_top">';
            echo '<input type="checkbox" id="'.$field_id.'_component" name="componentizer_fields['.$field_id.'][component]" '.$is_component.' value="1">';
            _e('Component',$this::NS);
          echo '</label> ';
        echo '</td>';
        echo '<td>'.$template.'</td>';
        echo '<td>';
          echo '<label for="'.$field_id.'_top">';
            echo '<input type="radio" id="'.$field_id.'_top" name="componentizer_fields['.$field_id.'][location]" '.$in_top.' value="top">';
            _e('Top',$this::NS);
          echo '</label> ';
          echo '<label for="'.$field_id.'_sortable">';
            echo '<input type="radio" id="'.$field_id.'_sortable" name="componentizer_fields['.$field_id.'][location]" '.$in_sortable.' value="sortable">';
            _e('Sortable',$this::NS);
          echo '</label> ';
          echo '<label for="'.$field_id.'_bottom">';
            echo '<input type="radio" id="'.$field_id.'_bottom" name="componentizer_fields['.$field_id.'][location]" '.$in_bottom.' value="bottom">';
            _e('Bottom',$this::NS);
          echo '</label> ';
        echo '</td>';
        echo '</tr>';
      }
      echo '</tbody>';
      echo '</table>';
    }

    submit_button();
  }
  function assign_location_orders() {
    if (is_array($this->location_orders)) {
      $top_fields = (array_key_exists('top', $this->location_orders)) ? $this->location_orders['top'] : [];
      $bottom_fields = (array_key_exists('bottom', $this->location_orders)) ? $this->location_orders['bottom'] : [];
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
    
    $field_groups = new \Componentizer\FieldGroups();
    echo '<div class="card">';
      echo '<h3>'.__('Top Components',$this::NS).'</h3>';
      echo '<div class="component-order-sort-wrap">';
      echo '<div id="order-top-components" class="order-components component-order-sort">';
      foreach ($top_fields as $top_field) {
        $title = $field_groups->get_title_by_id($top_field);
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
      echo '<h3>'.__('Bottom Components',$this::NS).'</h3>';
      echo '<div class="component-order-sort-wrap">';
      echo '<div id="order-bottom-components" class="order-components component-order-sort">';
      foreach ($bottom_fields as $bottom_field) {
        $title = $field_groups->get_title_by_id($bottom_field);
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
    if (!$options) $options = [];
    // List all ACF Field Groups and their associated base components
    $acf_fields = $this->get_acfs();
    echo '<div id="visible-on-archive" class="card">';
    if ($acf_fields && count($acf_fields)) {
      foreach ($acf_fields as $acf_field) {
        $checked = (in_array($acf_field->post_name, $options)) ? 'checked' : null;
        // var_dump($acf_field);
        echo '<label for="'.$acf_field->post_name.'">';
          echo '<input type="checkbox" id="'.$acf_field->post_name.'" name="componentizer_visible_on_archive[]" '.$checked.' value="'.$acf_field->post_name.'">';
          echo $acf_field->post_title;
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
      echo '<h1>'.__('Compontentizer',$this::NS).'</h1>';
      echo '<form id="basic" action="options.php" method="post" style="clear:both;">';
        settings_fields( 'componentizerSettings' );
        do_settings_sections( 'componentizerSettings' );
      echo '</form>';

      // List the base components and their subsidiary files
      echo '<h2>'.__('Component Files',$this::NS).'</h2>';
      echo '<p>'.__('These files are located in the',$this::NS).' <code>'.$this->settings['component_path'].'</code> '.__('directory of your theme.',$this::NS).'</p>';
      echo '<table class="wp-list-table widefat fixed striped">';
      echo '<thead>
        <tr>
          <th scope="col" id="base-components" class="manage-column column-base-components column-primary">'.__('Base Components',$this::NS).'</th>
          <th scope="col" id="suffixes" class="manage-column column-suffixes column-primary">'.__('Suffixes',$this::NS).'</th>
          <th scope="col" id="base-files" class="manage-column column-base-files column-primary">'.__('Base Files',$this::NS).'</th>
          <th scope="col" id="sub-files" class="manage-column column-sub-files column-primary">'.__('Sub Files',$this::NS).'</th>
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

  function assign_components_location_orders() {
    ?>
    <div id="componentizer-settings" class="wrap">
      <?php 
      echo '<h1>'.__('Compontentizer',$this::NS).'</h1>';
      echo '<form id="basic" action="options.php" method="post" style="clear:both;">';
        settings_fields( 'componentizerLocationSettings' );
        do_settings_sections( 'componentizerLocationSettings' );
      echo '</form>';
      ?>
    </div>
    <?php
  }

  function assign_components_visible_on_archive() {
    ?>
    <div id="componentizer-settings" class="wrap">
      <?php 
      echo '<h1>'.__('Compontentizer',$this::NS).'</h1>';
      echo '<form id="basic" action="options.php" method="post" style="clear:both;">';
        settings_fields( 'componentizerArchiveSettings' );
        do_settings_sections( 'componentizerArchiveSettings' );
      echo '</form>';
      ?>
    </div>
    <?php
  }

  function assign_options_for_sync() {
    ?>
    <div id="componentizer-settings" class="wrap">
      <?php 
      echo '<h1>'.__('Compontentizer',$this::NS).'</h1>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead>
          <tr>
            <th scope="col" id="sync" class="manage-column column-sync column-primary">'.__('Sync',$this::NS).'</th>
            <th scope="col" id="update-option" class="manage-column column-update-option column-primary">'.__('Option',$this::NS).'</th>
            <th scope="col" id="database-version" class="manage-column column-database-version column-primary">'.__('Database Version',$this::NS).'</th>
            <th scope="col" id="syncable-version" class="manage-column column-syncable-version column-primary">'.__('Syncable Version',$this::NS).'</th>
            <th scope="col" id="value" class="manage-column column-value column-primary">'.__('Value',$this::NS).'</th>
          </tr>
        </thead>
        <tbody>';
        $json_times_db = get_option('componentizer_json_timestamps');
        $json_times_files = $this::get_json_file_timestamps();
        $json_files = $this::get_json_files();
        $json_files_data = [];
        foreach ($json_files as $json_file) {
          $file_name = $this->json_path."/{$json_file}";
          if ($json_times_files[$json_file] !== $json_times_db[$json_file]) {
            $f = fopen($file_name, 'r');
            $componentizer_option = 'componentizer_'.str_replace('.json', null, $json_file);
            $json_file_data = fread($f, filesize($file_name));
            fclose($f);
            $obj_file_data = json_decode($json_file_data, true);
            $option_data = $obj_file_data[str_replace('.json', null, $json_file)];
            $db_timestamp = date('d M Y H:i:s',$json_times_db[$json_file]);
            $file_timestamp = date('d M Y H:i:s',$obj_file_data['timestamp']);
            echo '<tr id="row_'.$componentizer_option.'">';
              echo '<td>';
                echo '<button id="'.$componentizer_option.'" class="button button-primary button-sync-componentizer" value="'.$file_name.'">'.__('Sync',$this::NS).'</button>';
              echo '</td>';
              echo '<td>'.$componentizer_option.'</td>';
              echo '<td>'.$db_timestamp.'</td>';
              echo '<td>'.$file_timestamp.'</td>';
              echo '<td>';
                var_dump($option_data);
              echo '</td>';
            echo '</tr>';
          }
        }
        echo '</tbody></table>';
        echo '<p>Coming soon: <a href="#" id="migrate-to-group-names">'.__('Migrate from IDs to Group Names.',$this::NS).'</a></p>';
      ?>
    </div>
    <?php
  }

  function assign_advanced_settings() {
    $settings = get_option('componentizer_advanced_settings');
    echo '<table class="wp-list-table widefat fixed striped"><tbody>';
      echo '<tr>';
        echo '<th scope="row"><label for="component_path">'.__('Component Path',$this::NS).'</label></th>';
        echo '<td>'.get_stylesheet_directory().'/ <input id="component_path" name="componentizer_advanced_settings[component_path]" value="'.$settings['component_path'].'" /></td>';
      echo '</tr>';
      /*echo '<tr>';
        echo '<th scope="row"><label for="json_path">'.__('JSON Path',$this::NS).'</label></th>';
        echo '<td>'.get_stylesheet_directory().'/ <input id="json_path" name="componentizer_advanced_settings[json_path]" value="'.$settings['json_path'].'" /></td>';
      echo '</tr>';
      echo '<tr>';*/
        echo '<th scope="row"><label for="exclude_post_types">'.__('Exclude Post Types',$this::NS).'</label></th>';
        echo '<td>';
          $post_types = get_post_types();
          foreach ($post_types as $post_type) {
            echo '<label for="exclude_post_types_'.$post_type.'">';
              $checked = (array_key_exists($post_type, $settings['exclude_post_types'])) ? 'checked' : null;
              echo '<input type="checkbox" '.$checked.' id="exclude_post_types_'.$post_type.'" name="componentizer_advanced_settings[exclude_post_types]['.$post_type.']" value="'.$post_type.'" />';
              echo $post_type;
            echo '</label><br />';
          }
        echo '</td>';
      echo '</tr>';
    echo '</tbody></table>';
    submit_button();
  }

  function assign_components_advanced_settings() {
    ?>
    <div id="componentizer-settings" class="wrap">
      <?php
        echo '<h1>'.__('Compontentizer',$this::NS).'</h1>';
        echo '<form id="basic" action="options.php" method="post" style="clear:both;">';
          settings_fields( 'componentizerAdvancedSettings' );
          do_settings_sections( 'componentizerAdvancedSettings' );
        echo '</form>';
      ?>
    </div>
    <?php
  }


  /*function fields_updated( $input ) {
    // $this::save_json_file('fields');
    return $input;
  }
  function location_orders_updated( $input ) {
    // $this::save_json_file('location_orders');
    return $input;
  }
  function visible_on_archive_updated( $input ) {
    // $this::save_json_file('visible_on_archive');
    return $input;
  }
  function ajax_sync_callback() {
    $file_name = $_POST['json_file'];
    $f = fopen($file_name, 'r');
    $json_file_data = fread($f, filesize($file_name));
    fclose($f);
    $option_data = json_decode($json_file_data, true);
    $componentizer_option = 'componentizer_'.str_replace('.json', null, basename($file_name));
    $old_option = get_option($componentizer_option);
    $result = update_option($componentizer_option,$option_data[str_replace('.json', null, basename($file_name))]);
    echo '#row_'.$componentizer_option;
    die();
  }
  function ajax_update_to_group_name_callback() {
    $acf_field_groups = $this->get_acfs();
    $acf_id_names = [];
    foreach ($acf_field_groups->posts as $acf_field_group) {
      $acf_id_names[ $acf_field_group->ID ] = $acf_field_group->post_name;
    }

    var_dump($acf_id_names);

    die();
  }*/


  function get_component_templates() {
    $component_files = scandir(get_stylesheet_directory().'/'.$this->settings['component_path']);
    $component_templates = [];
    foreach ($component_files as $component_file) {
      if (!in_array($component_file, $this->ignore_files)) {
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
