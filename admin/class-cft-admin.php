<?php

if (!defined( 'ABSPATH' )) exit;

class CFT_admin {
    public static function init() {
        add_action('admin_menu', array(__CLASS__,'add_admin_menu')); //add page for admin page
    }

    public static function add_admin_menu(){

        //add admin pages
        add_menu_page(
            __('Home', 'content-freshness-tracker'),
            __('Content Freshness', 'content-freshness-tracker'),
            'manage_options',
            'cft-home',
            array(__CLASS__,'home_page'),
            'dashicons-tide',
            25
        );

        //add submenu for home
        add_submenu_page(
            'cft-home',
            __('Home', 'content-freshness-tracker'),
            __('Home', 'content-freshness-tracker'),
            'manage_options',
            'cft-home',
            array(__CLASS__, 'home_page')
        );

        //add submenu for post tarcker
        add_submenu_page(
            'cft-home',
            __('Posts', 'content-freshness-tracker'),
            __('Posts', 'content-freshness-tracker'),
            'manage_options',
            'cft-posts',
            array(__CLASS__, 'posts_page')
        );

        //add submenu for page tarcker
        add_submenu_page(
            'cft-home',
            __('Pages', 'content-freshness-tracker'),
            __('Pages', 'content-freshness-tracker'),
            'manage_options',
            'cft-pages',
            array(__CLASS__, 'pages_page')
        );

        //add submenu for settings
        add_submenu_page(
            'cft-home',
            __('Settings', 'content-freshness-tracker'),
            __('Settings', 'content-freshness-tracker'),
            'manage_options',
            'cft-settings',
            array(__CLASS__, 'settings_page')
        );
    }

    public static function home_page() {
        ?>
            <h1>Home Page</h1>
        <?php
    }

    public static function posts_page() {
        ?>
            <h1>Posts Tracker</h1>
        <?php
    }

    public static function settings_page() {
        ?>
            <h1>Hello world!</h1>
        <?php
    }
}

?>