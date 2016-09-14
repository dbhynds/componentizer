<?php

namespace Componentizer;

class Componentizer {

  function __construct()
  {
    add_action('parse_query',[$this,'initializeSuffixes']);
    if (is_admin()) {
      new \Componentizer\Admin\Admin;
      new \Componentizer\Admin\EditorPage;
      new \Componentizer\Admin\SettingsPage;
    }
  }

  function initializeSuffixes() {
    new \Componentizer\Suffixes;
  }

}