<?php

class FR_Logger {
    public static function log($message, $level = 'info') {
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }

        $file = WP_CONTENT_DIR . '/fresh-reminder.log';
        $time = gmdate('Y-m-d H:i:s');
        $entry = "[$time] [$level] $message" . PHP_EOL;

        error_log($entry, 3, $file);
    }
}


?>