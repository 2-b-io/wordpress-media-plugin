<?php
/*
  Plugin Name: Mr Rocket
  Text Domain: mr-rocket
  Description: MR. ROCKET - SPEED UP WEBSITE PERFORMANCE
  Author: Mr.Rocket
  Author URI: https://www.mr-rocket.io
  License: GPLv2 or later
  Version: 1.0.0
*/

/* Check & Quit */
defined('ABSPATH') OR exit;

/* constants */
define('MR_ROCKET_FILE', __FILE__);
define('MR_ROCKET_DIR', dirname(__FILE__));
define('MR_ROCKET_BASE', plugin_basename(__FILE__));
define('MR_ROCKET_MIN_WP', '3.8');

/* loader */
add_action(
  'plugins_loaded',
  [
    'Mr_Rocket',
    'instance'
  ]
);

/* uninstall */
register_uninstall_hook(
  __FILE__,
  [
    'Mr_Rocket',
    'handle_uninstall_hook'
  ]
);

/* activation */
register_activation_hook(
  __FILE__,
  [
    'Mr_Rocket',
    'handle_activation_hook'
  ]
);

/* autoload init */
spl_autoload_register('MR_ROCKET_autoload');

/* autoload function */
function MR_ROCKET_autoload($class) {
  if (in_array($class, ['Mr_Rocket', 'Mr_Rocket_Rewriter', 'Mr_Rocket_Settings'])) {
    require_once(
      sprintf(
        '%s/inc/%s.class.php',
        MR_ROCKET_DIR,
        strtolower($class)
      ) 
    );
  }
}