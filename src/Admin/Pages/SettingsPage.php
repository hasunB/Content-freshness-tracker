<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if (! current_user_can('manage_options')) return;

$fresre_defaults = FRESRE_Cron::get_default();
$fresre_settings = get_option(FRESRE_OPTION_NAME, $fresre_defaults);

if (isset($_POST['fresre_save']) && check_admin_referer('fresre_settings', 'fresre_nonce')) {
     
    FRESRE_Logger::log('save settings triggerd', 'info');
    // Stale duration fields
    $fresre_stale_after_value = isset($_POST['stale_after_value']) ? absint($_POST['stale_after_value']) : $fresre_defaults['stale_after_value'];
    $fresre_stale_after_unit  = isset($_POST['stale_after_unit']) ? sanitize_text_field(wp_unslash($_POST['stale_after_unit'])) : $fresre_defaults['stale_after_unit'];

    // Post types
    $fresre_post_types = isset($_POST['post_types'])
        ? array_map('sanitize_text_field', array_keys(wp_unslash($_POST['post_types'])))
        : array('post');

    // Schedule (validate against allowed list)
    $fresre_allowed_schedules = array('every_five_minutes', 'every_fifteen_minutes', 'hourly', 'daily');

    $fresre_schedule = isset( $_POST['schedule'] )
    ? sanitize_text_field( wp_unslash( $_POST['schedule'] ) )
    : '';

    if ( ! in_array( $fresre_schedule, $fresre_allowed_schedules, true ) ) {
        $fresre_schedule = 'every_five_minutes';
    }


    // Email notify checkbox
    $fresre_email_notify = isset($_POST['email_notify']) ? 1 : 0;

    // Roles
    $fresre_roles = isset($_POST['roles'])
        ? array_map('sanitize_text_field', array_keys( wp_unslash($_POST['roles'])))
        : $fresre_defaults['roles'];

    // clear reviewed (never, daily, weekly, monthly)
    $fresre_clear_reviewed = isset($_POST['clear_reviewed'])
        ? sanitize_text_field( wp_unslash($_POST['clear_reviewed']))
        : 'never';

    // Build settings array
    $fresre_new = compact(
        'stale_after_value',
        'stale_after_unit',
        'post_types',
        'schedule',
        'clear_reviewed',
        'email_notify',
        'roles'
    );

    // Save to DB
    update_option(FRESRE_OPTION_NAME, $fresre_new);

    // Reschedule cron
    wp_clear_scheduled_hook('fresre_check_event');
    wp_schedule_event(time(), $fresre_schedule, 'fresre_check_event');

    // Success message
?>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            var successMsg = document.querySelector('.settings-msg.success');
            if (successMsg) {
                successMsg.classList.add('msg-visible');
                setTimeout(function() {
                    successMsg.classList.remove('msg-visible');
                }, 4000);
            }
        });
    </script>
<?php

    $fresre_settings = $fresre_new;
}

?>

<div class="theme-container">
    <!-- Navbar -->
    <nav class="navbar-custom">
        <div class="container-fluid d-flex align-items-center justify-content-center">
            <div class="col-9">
                <div class="d-flex align-items-center navbar-action-gap">
                    <div class="logo" style="border: none;">
                    </div>
                    <div class="d-flex justify-content-start align-items-center mt-1">
                        <h3 class="plugin-name italic">Fresh Reminder
                            <span>v<?php echo esc_html(FRESRE_VERSION); ?>
                            </span>
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-3 d-flex justify-content-end navbar-action-gap">
                <div class="d-flex gap-3">
                    <button class="theme-action-btn goto-home-page" title="Home"><i class="fas fa-home"></i></button>
                    <button class="theme-action-btn goto-check-bucket-page" title="Check Bucket" ><i class="fas fa-bucket"></i></button>
                    <button class="theme-action-btn goto-help-page" title="Help"><i class="fas fa-question"></i></button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid main-content-box">
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
                <div class="tab-content theme-settings-content-box" id="pills-tabContent">
                    <!-- Settings tab -->
                    <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
                        <div class="d-flex">
                            <div class="col-8">
                                <span class="fs-5 fw-semibold">General</span>
                            </div>
                            <div class="col-4 d-flex justify-content-end align-items-center">
                                <span class="settings-msg success">
                                    <i class="fa-solid fa-circle-check"></i>&nbsp;&nbsp;Settings Saved
                                </span>
                                <span class="settings-msg error">
                                    <i class="fa-solid fa-circle-xmark"></i>&nbsp;&nbsp;Error
                                </span>
                            </div>
                        </div>
                        <form method="post">
                            <?php wp_nonce_field('fresre_settings', 'fresre_nonce'); ?>
                            <table class="form-table">
                                <tr>
                                    <th><?php esc_html_e('Stale after', 'fresh-reminder'); ?></th>
                                    <td>
                                        <?php

                                        $fresre_stale_unit = $fresre_settings['stale_after_unit'] ?? 'months';
                                        $fresre_stale_value = $fresre_settings['stale_after_value'] ?? 1;

                                        $fresre_min_attr = 'min="1"';
                                        $fresre_max_attr = '';
                                        if ($fresre_stale_unit == 'minutes') {
                                            $fresre_min_attr = 'min="5"';
                                        } else if ($fresre_stale_unit == 'months') {
                                            $fresre_max_attr = 'max="12"';
                                        }

                                        ?>
                                        <input class="settings-input filter-skin" type="number" name="stale_after_value" id="stale_after_value" value="<?php echo esc_attr($fresre_stale_value); ?>" <?php echo esc_attr($fresre_min_attr); ?> <?php echo esc_attr($fresre_max_attr); ?> min="1" />
                                        <select class="theme-settings-filter-select filter-skin" name="stale_after_unit" id="stale_after_unit">
                                            <option value="minutes" <?php selected($fresre_stale_unit, 'minutes'); ?>>Minutes</option>
                                            <option value="hours" <?php selected($fresre_stale_unit, 'hours'); ?>>Hours</option>
                                            <option value="days" <?php selected($fresre_stale_unit, 'days'); ?>>Days</option>
                                            <option value="months" <?php selected($fresre_stale_unit, 'months'); ?>>Months</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e('Post types', 'fresh-reminder'); ?></th>
                                    <td>
                                        <?php
                                        $fresre_types = get_post_types(array('public' => true), 'objects');

                                        // Define the allowed post types
                                        $fresre_allowed_types = array('post', 'page', 'product');

                                        foreach ($fresre_types as $type) {
                                            if (in_array($type->name, $fresre_allowed_types, true)) {
                                                $fresre_checked = in_array($type->name, $fresre_settings['post_types']) ? 'checked' : '';
                                                echo '<label class="fresre-settings-label">
                                                        <input class="fresre-settings-input" type="checkbox" name="post_types[' . esc_attr($type->name) . ']" value="1" ' . esc_attr($fresre_checked) . ' />
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
                                        <select class="theme-settings-filter-select filter-skin" name="schedule" id="schedule">
                                            <option value="every_five_minutes" <?php selected($fresre_settings['schedule'], 'every_five_minutes'); ?>>
                                                <?php esc_html_e('Every 5 Minutes', 'fresh-reminder'); ?>
                                            </option>
                                            <option value="every_fifteen_minutes" <?php selected($fresre_settings['schedule'], 'every_fifteen_minutes'); ?>>
                                                <?php esc_html_e('Every 15 Minutes', 'fresh-reminder'); ?>
                                            </option>
                                            <option value="hourly" <?php selected($fresre_settings['schedule'], 'hourly'); ?>>
                                                <?php esc_html_e('Hourly', 'fresh-reminder'); ?>
                                            </option>
                                            <option value="daily" <?php selected($fresre_settings['schedule'], 'daily'); ?>>
                                                <?php esc_html_e('Daily', 'fresh-reminder'); ?>
                                            </option>
                                        </select>
                                    </td>

                                </tr>
                                <tr>
                                    <th><?php esc_html_e('Clear Reviewed', 'fresh-reminder'); ?><br><?php esc_html_e('Content', 'fresh-reminder'); ?><span class="reason-mark">*</span></th>
                                    <td>
                                        <select class="theme-settings-filter-select filter-skin" name="clear_reviewed" id="clear_reviewed">
                                            <option value="every_30_minutes" <?php selected($fresre_settings['clear_reviewed'] ?? 'never', 'every_30_minutes'); ?>>
                                                <?php esc_html_e('Every 30 Minutes', 'fresh-reminder'); ?>
                                            </option>
                                            <option value="hourly" <?php selected($fresre_settings['clear_reviewed'] ?? 'never', 'hourly'); ?>>
                                                <?php esc_html_e('Hourly', 'fresh-reminder'); ?>
                                            </option>
                                            <option value="daily" <?php selected($fresre_settings['clear_reviewed'] ?? 'never', 'daily'); ?>>
                                                <?php esc_html_e('Daily', 'fresh-reminder'); ?>
                                            </option>
                                            <option value="never" <?php selected($fresre_settings['clear_reviewed'] ?? 'never', 'never'); ?>>
                                                <?php esc_html_e('Never', 'fresh-reminder'); ?>
                                            </option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e('Email digest', 'fresh-reminder'); ?></th>
                                    <td><label style="color: gray;"><input disabled type="checkbox" name="email_notify" value="1" <?php checked($fresre_settings['email_notify'], 1); ?> /> <?php esc_html_e('Send digest to selected roles', 'fresh-reminder'); ?></label></td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e('Notify roles', 'fresh-reminder'); ?></th>
                                    <td>
                                        <?php
                                        $fresre_roles = wp_roles()->roles;
                                        foreach ($fresre_roles as $fresre_role_key => $role) {
                                            $fresre_checked = in_array($fresre_role_key, $fresre_settings['roles']) ? 'checked' : '';
                                            echo '<label class="fresre-settings-label"><input class="fresre-settings-input" type="checkbox" name="roles[' . esc_attr($fresre_role_key) . ']" value="1" ' . esc_attr($fresre_checked) . ' /> ' . esc_html($role['name']) . '</label>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            </table>
                            <div class="sttings-btn-box">
                                <p class="submit"><input class="fresre-settings-save-btn" type="submit" name="fresre_save" value="<?php esc_attr_e('Save Changes', 'fresh-reminder'); ?>" /></p>
                            </div>
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
                                    <td><?php echo esc_attr(FRESRE_VERSION) ?></td>
                                </tr>
                                <tr>
                                    <th>Author</th>
                                    <td>Hasun Akash Bandara</td>
                                </tr>
                                <tr>
                                    <th>License</th>
                                    <td><a href="https://github.com/hasunB/fresh-reminder?tab=GPL-3.0-1-ov-file#readme" target="_blank">GPLv2</a> or later</td>
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
                    <!-- content-box -->
                    <div class="w-100 h-100 chart-content-box" style="display: none;">
                        <p class="chart-description ps-5 pe-5">A visual breakdown of content status.</p>
                        <div class="pie-chart">
                            <canvas id="fresre_piechart_canvas"></canvas>
                        </div>
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
                            This chart displays the percentage of reviewed versus unreviewed content, providing a quick overview of your content's freshness.
                        </p>
                    </div>
                    <!-- no-content-box -->
                    <div class="w-100 no-chart-content-box" style="display: none;">
                        <div></div>
                        <h5>No Data Found</h5>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
    <!-- mobile responsive filter div -->
    <div class="mobile-responsive-filter-box">
        <div>
            <p>This page is best viewed on a desktop or tablet device for full functionality.</p>
        </div>
    </div>
</div>
