<?php

/**
 * Mr_Rocket_Settings
 *
 * @since 0.0.1
 */

class Mr_Rocket_Settings {
  /**
   * register settings
   *
   * @since   0.0.1
   * @change  0.0.1
   */

  public static function register_settings() {
    register_setting(
      'mr_rocket',
      'mr_rocket',
      [
        __CLASS__,
        'validate_settings',
      ]
    );
  }


  /**
   * validation of settings
   *
   * @since   0.0.1
   * @change  1.0.5
   *
   * @param   array  $data  array with form data
   * @return  array         array with validated values
   */

  public static function validate_settings($data) {
    return [
      'url'             => esc_url($data['url']),
      'dirs'            => esc_attr($data['dirs']),
      'excludes'        => esc_attr($data['excludes'])
    ];
  }


  /**
   * add settings page
   *
   * @since   0.0.1
   * @change  0.0.1
   */

  public static function add_settings_page() {
    $page = add_options_page(
      'Mr Rocket',
      'Mr Rocket',
      'manage_options',
      'mr_rocket',
      [
        __CLASS__,
        'settings_page',
      ]
    );
  }


  /**
   * settings page
   *
   * @since   0.0.1
   * @change  1.0.6
   *
   * @return  void
   */

  public static function settings_page() {
    $options = Mr_Rocket::get_options()
  ?>
    <div class="wrap">
     <h2>
       <?php _e("Mr Rocket Settings", "mr-rocket"); ?>
     </h2>

      <form method="post" action="options.php">
        <?php settings_fields('mr_rocket') ?>

        <table class="form-table">

          <tr valign="top">
            <th scope="row">
                <?php _e("CDN URL", "mr-rocket"); ?>
            </th>
            <td>
              <fieldset>
                <label for="mr_rocket_url">
                  <input type="text" name="mr_rocket[url]" id="mr_rocket_url" value="<?php echo $options['url']; ?>" size="64" class="regular-text code" />
                </label>

                <p class="description">
                  <?php _e("Enter the CDN URL without trailing", "mr-rocket"); ?> <code>/</code>
                </p>
              </fieldset>
            </td>
          </tr>

          <tr valign="top">
            <th scope="row">
                <?php _e("Included Directories", "mr-rocket"); ?>
            </th>
            <td>
              <fieldset>
                <label for="mr_rocket_dirs">
                  <input type="text" name="mr_rocket[dirs]" id="mr_rocket_dirs" value="<?php echo $options['dirs']; ?>" size="64" class="regular-text code" />
                  <?php _e("Default: <code>wp-content,wp-includes</code>", "mr-rocket"); ?>
                </label>

                <p class="description">
                  <?php _e("Assets in these directories will be pointed to the CDN URL. Enter the directories separated by", "mr-rocket"); ?> <code>,</code>
                </p>
              </fieldset>
            </td>
          </tr>

          <tr valign="top">
            <th scope="row">
              <?php _e("Exclusions", "mr-rocket"); ?>
            </th>
            <td>
              <fieldset>
                <label for="mr_rocket_excludes">
                  <input type="text" name="mr_rocket[excludes]" id="mr_rocket_excludes" value="<?php echo $options['excludes']; ?>" size="64" class="regular-text code" />
                  <?php _e("Default: <code>.php</code>", "mr-rocket"); ?>
                </label>

                <p class="description">
                  <?php _e("Enter the exclusions (directories or extensions) separated by", "mr-rocket"); ?> <code>,</code>
                </p>
              </fieldset>
            </td>
          </tr>

        </table>

        <?php submit_button() ?>
       </form>
    </div><?php
  }
}
