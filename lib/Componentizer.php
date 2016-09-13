<?php

namespace Componentizer;

class Componentizer {

  function __construct()
  {
    new Suffixes;
    if (is_admin()) {
      new \Componentizer\Admin\Admin;
      new \Componentizer\Admin\EditorPage;
      new \Componentizer\Admin\SettingsPage;
    }
  }

}