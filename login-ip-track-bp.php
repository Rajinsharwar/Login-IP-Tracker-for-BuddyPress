<?php
/*
Plugin Name: Login IP Tracker for BuddyPress
Description: Tracks and displays IP addresses of user logins in BuddyPress and BuddyBoss
Version: 1.0
Author: Rajin Sharwar
Author URI: https://linkedin.com/in/rajinsharwar
License: GPL2
*/

add_filter('manage_users_columns', 'littbp_add_user_ip_column');
function littbp_add_user_ip_column($columns) {
    $columns['logged_in_ips'] = 'Logged In IPs';
    return $columns;
}

add_action('manage_users_custom_column', 'littbp_display_user_ip_column', 10, 3);
function littbp_display_user_ip_column($value, $column_name, $user_id) {
    if ($column_name === 'logged_in_ips') {
        $ip_addresses = get_user_meta($user_id, 'logged_in_ips', true);

        if (!empty($ip_addresses)) {
            $ip_counts = array_count_values($ip_addresses);

            $ip_data = array();
            foreach ($ip_counts as $ip => $count) {
                $ip_data[] = '<b>' . $ip . '</b> (' . $count . ')';
            }

            $value = implode('<br>', $ip_data);
        }
    }

    return $value;
}

add_action('wp_login', 'littbp_log_user_ip_address', 10, 2);
function littbp_log_user_ip_address($user_login, $user) {
    $ip_address = littbp_get_user_public_ip_address();
    $existing_ips = get_user_meta($user->ID, 'logged_in_ips', true);

    if (empty($existing_ips)) {
        $existing_ips = array();
    }

    $existing_ips[] = $ip_address;
    update_user_meta($user->ID, 'logged_in_ips', $existing_ips);
}

function littbp_get_user_public_ip_address() {
    if ( ! empty( $_SERVER[ 'CF-Connecting-IP' ] ) ) {
        $public_ip_address = $_SERVER[ 'CF-Connecting-IP' ];
    } elseif( ! empty( $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] ) ) {
        $ip_addresses = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $public_ip_address = trim(end($ip_addresses));
    } elseif ( ! empty( $_SERVER[ 'REMOTE_ADDR' ] ) ) {
        $public_ip_address = $_SERVER['REMOTE_ADDR'];
    } else {
        $public_ip_address = '';
    }

    return $public_ip_address;
}

add_action('show_user_profile', 'littbp_display_user_ip_addresses');
add_action('edit_user_profile', 'littbp_display_user_ip_addresses');
function littbp_display_user_ip_addresses($user) {
    $ip_addresses = get_user_meta($user->ID, 'logged_in_ips', true);

    if (!empty($ip_addresses)) {
        $ip_counts = array_count_values($ip_addresses);

        ?>
        <h3>Logged In IPs</h3>
        <table class="form-table">
            <tr>
                <th><label>IP Addresses:</label></th>
                <td>
                    <?php foreach ($ip_counts as $ip => $count) : ?>
                        <div><b><?php echo $ip; ?></b> (Login count: <?php echo $count; ?>)</div>
                    <?php endforeach; ?>
                </td>
            </tr>
        </table>
        <?php
    }
}
