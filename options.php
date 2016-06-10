<?php

namespace Componentizer;

$settings = get_option('componentizer_advanced_settings');
define(__NAMESPACE__.'\\COMPONENT_PATH',$settings['component_path']);
define(__NAMESPACE__.'\\JSON_PATH',get_stylesheet_directory().'/'.$settings['json_path']);
unset($settings);
