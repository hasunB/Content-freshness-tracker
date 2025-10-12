<?php
if (! current_user_can('manage_options')) return;

$defaults = FR_Cron::get_default();
$settings = get_option(FR_OPTION_NAME, $defaults);

if (isset($_POST['fr_save']) && check_admin_referer('fr_settings', 'fr_nonce')) {
    $months = isset($_POST['months']) ? absint($_POST['months']) : $defaults['months'];
    $post_types = isset($_POST['post_types']) ? array_map('sanitize_text_field', array_keys($_POST['post_types'])) : array('post');
    $schedule = isset($_POST['schedule']) && in_array($_POST['schedule'], array('hourly', 'twicedaily', 'daily')) ? $_POST['schedule'] : 'daily';
    $email_notify = isset($_POST['email_notify']) ? 1 : 0;
    $roles = isset($_POST['roles']) ? array_map('sanitize_text_field', array_keys($_POST['roles'])) : $defaults['roles'];

    $new = compact('months', 'post_types', 'schedule', 'email_notify', 'roles');
    update_option(FR_OPTION_NAME, $new);

    // reschedule
    wp_clear_scheduled_hook('fr_check_event');
    wp_schedule_event(time(), $schedule, 'fr_check_event');

    echo '<div class="updated"><p>' . esc_html__('Settings saved', 'fresh-reminder') . '</p></div>';
    $settings = $new;
}

?>
<div class="wrap">
    <h1><?php esc_html_e('Content Freshness Tracker', 'fresh-reminder'); ?></h1>
    <form method="post">
        <?php wp_nonce_field('fr_settings', 'fr_nonce'); ?>
        <table class="form-table">
            <tr>
                <th><label for="months"><?php esc_html_e('Stale after (months)', 'fresh-reminder'); ?></label></th>
                <td><input type="number" name="months" id="months" value="<?php echo esc_attr($settings['months']); ?>" min="1" class="small-text" /></td>
            </tr>
            <tr>
                <th><?php esc_html_e('Post types', 'fresh-reminder'); ?></th>
                <td>
                    <?php
                    $types = get_post_types(array('public' => true), 'objects');
                    foreach ($types as $type) {
                        $checked = in_array($type->name, $settings['post_types']) ? 'checked' : '';
                        echo '<label style="display:block"><input type="checkbox" name="post_types[' . esc_attr($type->name) . ']" value="1" ' . $checked . ' /> ' . esc_html($type->labels->singular_name) . '</label>';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e('Schedule', 'fresh-reminder'); ?></th>
                <td>
                    <label><input type="radio" name="schedule" value="hourly" <?php checked($settings['schedule'], 'hourly'); ?> /> Hourly</label><br />
                    <label><input type="radio" name="schedule" value="twicedaily" <?php checked($settings['schedule'], 'twicedaily'); ?> /> Twice daily</label><br />
                    <label><input type="radio" name="schedule" value="daily" <?php checked($settings['schedule'], 'daily'); ?> /> Daily</label>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e('Email digest', 'fresh-reminder'); ?></th>
                <td><label style="color: gray;"><input disabled type="checkbox" name="email_notify" value="1" <?php checked($settings['email_notify'], 1); ?> /> <?php esc_html_e('Send digest to selected roles', 'fresh-reminder'); ?></label></td>
            </tr>
            <tr>
                <th><?php esc_html_e('Notify roles', 'fresh-reminder'); ?></th>
                <td>
                    <?php
                    $roles = wp_roles()->roles;
                    foreach ($roles as $role_key => $role) {
                        $checked = in_array($role_key, $settings['roles']) ? 'checked' : '';
                        echo '<label style="display:block"><input type="checkbox" name="roles[' . esc_attr($role_key) . ']" value="1" ' . $checked . ' /> ' . esc_html($role['name']) . '</label>';
                    }
                    ?>
                </td>
            </tr>
        </table>
        <p class="submit"><input type="submit" name="fr_save" class="button button-primary" value="<?php esc_attr_e('Save Changes', 'fresh-reminder'); ?>" /></p>
    </form>
</div>
