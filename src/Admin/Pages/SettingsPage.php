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
<!-- <div class="wrap">
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
</div> -->

<div class="theme-container">
    <!-- Navbar -->
    <nav class="navbar-custom">
        <div class="container-fluid d-flex align-items-center justify-content-center">
            <div class="col-9">
                <div class="d-flex align-items-center navbar-action-gap">
                    <div class="logo">FR</div>
                    <div class="d-flex justify-content-start align-items-center mt-1">
                        <h3 class="plugin-name italic">Fresh Reminder
                            <span>v<?php echo esc_html(FR_VERSION); ?>
                            </span>
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-3 d-flex justify-content-end navbar-action-gap">
                <div class="d-flex gap-3">
                    <button class="theme-action-btn goto-home-page"><i class="fas fa-home"></i></button>
                    <button class="theme-action-btn goto-check-bucket-page rotate-45"><i class="fas fa-thumbtack"></i></button>
                    <button class="theme-action-btn goto-help-page"><i class="fas fa-question"></i></button>
                </div>
                <div class="logo">
                    <?php
                    $curent_user = wp_get_current_user();
                    if ($curent_user) {
                        //profile image
                        $profile_image = get_avatar_url($curent_user->ID, array('size' => 32));
                        if ($profile_image) {
                            echo '<img src="' . esc_url($profile_image) . '" alt="User Avatar" class="user-avatar">';
                        } else {
                            echo '<img src="' . FR_PLUGIN_URL . '/assets/images/fr-default-user-profile.webp" alt="Default User Avatar" class="user-avatar">';
                        }
                    } else {
                        echo '<img src="' . FR_PLUGIN_URL . '/assets/images/fr-default-user-profile.webp" alt="Default User Avatar" class="user-avatar">';
                    }
                    ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid d-flex">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Stale Posts -->
            <div class="theme-stale-content widget-skin" data-current-page="check-bucket-page">
                <!-- tabs -->
                <ul class="nav nav-pills theme-tab-box" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#pills-home" type="button" role="tab" aria-controls="pills-home" aria-selected="true">Home</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-about-tab" data-bs-toggle="pill" data-bs-target="#pills-about" type="button" role="tab" aria-controls="pills-about" aria-selected="false">About</button>
                    </li>
                </ul>

                <!-- content -->
                <div class="tab-content theme-content-box" id="pills-tabContent">
                    <!-- Settings tab -->
                    <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fs-5 fw-semibold">Settings</span>
                        </div>
                        <form method="post">
                            <?php wp_nonce_field('fr_settings', 'fr_nonce'); ?>
                            <table class="form-table">
                                <tr>
                                    <th><?php esc_html_e('Stale after', 'fresh-reminder'); ?></th>
                                    <!-- <td><input type="number" name="months" id="months" value="<?php echo esc_attr($settings['months']); ?>" min="1" class="small-text" /></td> -->
                                    <td>
                                        <input class="settings-input filter-skin" type="number" name="stale_after_value" id="stale_after_value" value="1" min="1" />
                                        <select class="theme-filter-select filter-skin" name="stale_after_unit" id="stale_after_unit">
                                            <option value="minutes">Minutes</option>
                                            <option value="hours">Hours</option>
                                            <option value="days">Days</option>
                                            <option value="weeks">Weeks</option>
                                            <option value="months">Months</option>
                                            <option value="years">Years</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e('Post types', 'fresh-reminder'); ?></th>
                                    <td>
                                        <?php
                                        $types = get_post_types(array('public' => true), 'objects');

                                        // Define the allowed post types
                                        $allowed_types = array('post', 'page', 'product');

                                        foreach ($types as $type) {
                                            if (in_array($type->name, $allowed_types, true)) {
                                                $checked = in_array($type->name, $settings['post_types']) ? 'checked' : '';
                                                echo '<label style="display:block">
                                                        <input type="checkbox" name="post_types[' . esc_attr($type->name) . ']" value="1" ' . $checked . ' />
                                                        ' . esc_html($type->labels->singular_name) . '
                                                    </label>';
                                            }
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e('Schedule', 'fresh-reminder'); ?></th>
                                    <td>
                                        <select class="theme-filter-select filter-skin" name="schedule_after_unit" id="schedule_after_unit">
                                            <option value="0">Every 5 Minutes</option>
                                            <option value="1">Every 15 Minutes</option>
                                            <option value="2">Hourly</option>
                                            <option value="3">Daily</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e('Clear Reviewed', 'fresh-reminder'); ?><br><?php esc_html_e('Content', 'fresh-reminder'); ?><span class="reason-mark">*</span></th>
                                    <td>
                                        <select class="theme-filter-select filter-skin" name="schedule_after_unit" id="schedule_after_unit">
                                            <option value="0">Every 30 Minutes</option>
                                            <option value="2">Hourly</option>
                                            <option value="3">Daily</option>
                                            <option value="4">Never</option>
                                        </select>
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

                    <!-- about tab -->
                    <div class="tab-pane fade" id="pills-about" role="tabpanel" aria-labelledby="pills-about-tab">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fs-5 fw-semibold">About</span>
                        </div>
                        <table class="form-table">
                            <tbody>
                                <tr>
                                    <th>Plugin Name</th>
                                    <td><strong>Fresh Reminder</strong></td>
                                </tr>
                                <tr>
                                    <th>Version</th>
                                    <td>1.1.0</td>
                                </tr>
                                <tr>
                                    <th>Auther</th>
                                    <td>Hasun Akash Bandara</td>
                                </tr>
                                <tr>
                                    <th>License</th>
                                    <td>GPLv2 or later</td>
                                </tr>
                                <tr>
                                    <th>GitHub</th>
                                    <td><a href="https://github.com/hasunB/fresh-reminder" target="_blank">View on GitHub</a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4 d-flex align-items-end flex-column">

            <!-- chart-widget -->
            <div class="theme-chart widget-skin">
                <div class="w-100 h-100">
                    <h5 class="chart-title">Freshness Tracking</h5>
                    <p class="chart-description ps-5 pe-5">Your saving continue to grow by 5.0% every month</p>
                    <!-- chart -->
                    <div class="pie-chart">
                        <canvas id="fr_piechart_canvas"></canvas>
                    </div>
                    <!-- legend -->
                    <div class="w-100 chart-legend">
                        <div class="w-50 h-100">
                            <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                <span class="legend-percentage reviewed">0%</span>
                                <div class="d-flex flex-row align-items-center justify-content-center gap-2">
                                    <div class="legend-indicator indicator-reviewed"></div>
                                    <span class="legend-label">Reviewed</span>
                                </div>
                            </div>
                        </div>
                        <div class="w-50 h-100">
                            <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                <span class="legend-percentage unreviewed">0%</span>
                                <div class="d-flex flex-row align-items-center justify-content-center gap-2">
                                    <div class="legend-indicator indicator-unreviewed"></div>
                                    <span class="legend-label">Unreviewed</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="chart-muted ps-5 pe-5 mt-3 mb-0">
                        Your saving continue to grow by 5.0% every month. Your saving continue to grow by 5.0% every month.
                    </p>
                </div>

            </div>
            <div class="spliter"></div>

            <!-- calendar-widget -->
            <!-- <div class="theme-chart widget-skin"></div> -->
        </div>
    </div>
</div>