<?php
/**
* Mr_Rocket
*
* @since 0.0.1
*/

class Mr_Rocket {
  /**
   * pseudo-constructor
   *
   * @since   0.0.1
   * @change  0.0.1
   */
  public static function instance() {
    new self();
  }

  /**
   * constructor
   *
   * @since   0.0.1
   * @change  1.0.9
   */
  public function __construct() {
    /* Mr Rocket rewriter hook */
    add_action(
      'template_redirect',
      [
        __CLASS__,
        'handle_rewrite_hook'
      ]
    );

    /* Rewrite rendered content in REST API */
    add_filter(
      'the_content',
      [
        __CLASS__,
        'rewrite_the_content',
      ],
      100
    );

    /* Hooks */
    add_action(
      'admin_init',
      [
        __CLASS__,
        'register_textdomain',
      ]
    );

    add_action(
      'admin_init',
      [
        'Mr_Rocket_Settings',
        'register_settings',
      ]
    );

    add_action(
      'admin_menu',
      [
        'Mr_Rocket_Settings',
        'add_settings_page',
      ]
    );

    add_filter(
      'plugin_action_links_' . MR_ROCKET_BASE,
      [
        __CLASS__,
        'add_action_link',
      ]
    );

    /* admin notices */
    add_action(
      'all_admin_notices',
      [
        __CLASS__,
        'mr_rocket_requirements_check',
      ]
    );

    /* add admin purge link */
    add_action(
      'admin_bar_menu',
      [
        __CLASS__,
        'add_admin_links',
      ],
      90
    );
  }

  /**
   * add Zone purge link
   *
   * @since   1.0.5
   * @change  1.0.6
   *
   * @hook    mixed
   *
   * @param   object  menu properties
   */

  public static function add_admin_links($wp_admin_bar) {
    global $wp;
    $options = self::get_options();

    // check user role
    if (!is_admin_bar_showing() or !apply_filters('user_can_clear_cache', current_user_can('manage_options'))) {
      return;
    }

    // verify Zone settings are set
    if (!is_int($options['mrrocket_zone_id']) or $options['mrrocket_zone_id'] <= 0) {
      return;
    }

    if (!array_key_exists('mrrocket_api_key', $options) or strlen($options['mrrocket_api_key']) < 20) {
      return;
    }

    // redirect to admin page if necessary so we can display notification
    $current_url = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' .
                    $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $goto_url = get_admin_url();

    if (stristr($current_url, get_admin_url())) {
      $goto_url = $current_url;
    }

    // add admin purge link
    $wp_admin_bar->add_menu(
      [
        'id' => 'purge-cdn',
        'href' => wp_nonce_url( add_query_arg('_cdn', 'purge', $goto_url), '_cdn__purge_nonce'),
        'parent' => 'top-secondary',
        'title' => '<span class="ab-item">'.esc_html__('Purge CDN', 'mr-rocket').'</span>',
        'meta' => ['title' => esc_html__('Purge CDN', 'mr-rocket')],
      ]
    );

    if (!is_admin()) {
      // add admin purge link
      $wp_admin_bar->add_menu(
        [
          'id' => 'purge-cdn',
          'href' => wp_nonce_url( add_query_arg('_cdn', 'purge', $goto_url), '_cdn__purge_nonce'),
          'parent' => 'top-secondary',
          'title' => '<span class="ab-item">'.esc_html__('Purge CDN', 'mr-rocket').'</span>',
          'meta' => ['title' => esc_html__('Purge CDN', 'mr-rocket')],
        ]
      );
    }
  }

  /**
   * add action links
   *
   * @since   0.0.1
   * @change  0.0.1
   *
   * @param   array  $data  alreay existing links
   * @return  array  $data  extended array with links
   */

  public static function add_action_link($data) {
    // check permission
    if (!current_user_can('manage_options')) {
      return $data;
    }

    return array_merge(
      $data,
      [
        sprintf(
          '<a href="%s">%s</a>',
          add_query_arg(
            [
              'page' => 'mr_rocket',
            ],
            admin_url('options-general.php')
          ),
          __("Settings")
        ),
      ]
    );
  }

  /**
   * run uninstall hook
   *
   * @since   0.0.1
   * @change  0.0.1
   */
  public static function handle_uninstall_hook() {
    delete_option('mr_rocket');
  }

  /**
   * run activation hook
   *
   * @since   0.0.1
   * @change  1.0.5
   */
  public static function handle_activation_hook() {
    add_option(
      'mr_rocket',
      [
        'url'            => get_option('home'),
        'dirs'           => 'wp-content,wp-includes',
        'excludes'       => '.php'
      ]
    );
  }

  /**
   * check plugin requirements
   *
   * @since   0.0.1
   * @change  0.0.1
   */
  public static function mr_rocket_requirements_check() {
    // WordPress version check
    if (version_compare($GLOBALS['wp_version'], MR_ROCKET_MIN_WP . 'alpha', '<')) {
      show_message(
        sprintf(
          '<div class="error"><p>%s</p></div>',
          sprintf(
            __("Mr Rocket is optimized for WordPress %s. Please disable the plugin or upgrade your WordPress installation (recommended).", "mr-rocket"),
            MR_ROCKET_MIN_WP
          )
        )
      );
    }
  }

  /**
   * register textdomain
   *
   * @since   1.0.3
   * @change  1.0.3
   */
  public static function register_textdomain() {
    load_plugin_textdomain(
      'mr-rocket',
      false,
      'mr-rocket/lang'
    );
  }

  /**
   * return plugin options
   *
   * @since   0.0.1
   * @change  1.0.5
   *
   * @return  array  $diff  data pairs
   */
  public static function get_options() {
    return wp_parse_args(
      get_option('mr_rocket'),
      [
        'url'             => get_option('home'),
        'dirs'            => 'wp-content,wp-includes',
        'excludes'        => '.php'
      ]
    );
  }

  /**
   * return new rewriter
   *
   * @since   1.0.9
   * @change  1.0.9
   *
   */
  public static function get_rewriter() {
    $options = self::get_options();

    $excludes = array_map('trim', explode(',', $options['excludes']));

    return new Mr_Rocket_Rewriter(
      get_option('home'),
      $options['url'],
      $options['dirs'],
      $excludes
    );
  }

  /**
   * run rewrite hook
   *
   * @since   0.0.1
   * @change  1.0.9
   */
  public static function handle_rewrite_hook() {
    $options = self::get_options();

    // check if origin equals cdn url
    if (get_option('home') == $options['url']) {
      return;
    }

    $rewriter = self::get_rewriter();
    ob_start(array(&$rewriter, 'rewrite'));
  }

  /**
   * rewrite html content
   *
   * @since   1.0.9
   * @change  1.0.9
   */
  public static function rewrite_the_content($html) {
    $rewriter = self::get_rewriter();
    return $rewriter->rewrite($html);
  }
}