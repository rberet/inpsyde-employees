<?php

/**
 * Trigger this file on plugin uninstall
 *
 * @package InpsydeEmployees
 */
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}
// Clear Database stored data
//Access the database via SQL
global $wpdb;
$wpdb->query("DELETE FROM wp_posts WHERE post_type = 'worker'");
//$wpdb->query("DELETE FROM wp_postmeta WHERE post_id NOT IN (SELECT id FROM wp_posts)");
