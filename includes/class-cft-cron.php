<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class CFT_Cron {
    public static function activate(){
        error_log("plugin activated");
    }
    public static function deactivate(){
        error_log("plugin deactivated");
    }
}
?>