<?php

namespace Nomreh;

class Firewall {

    private $wpdb;
    private $table_name;
    private $limit = 4; // Max attempts
    private $time_frame = 60000; // Time frame in seconds
    private $block_time = 15 * 60; // Block for 15 minutes (in seconds)

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . NOMREH_LOGIN_IP__TABLE_KEY;
    }


    public function check_ip($user_ip) {
        $current_time = current_time('timestamp'); // Get current time as a timestamp
        $ip_record = $this->wpdb->get_row($this->wpdb->prepare("SELECT * FROM $this->table_name WHERE ips = %s", $user_ip));


        if ($ip_record) {
            if ($ip_record->blocked) {
                $blocked_until = strtotime($ip_record->blocked_until);

                if ($current_time < $blocked_until) {
                    return ['success' => false, 'message' => 'به دلیل درخواست های زیاد موقتا دسترسی شما مسدود شده است.'];
                } else {
                    $this->unblock_ip($ip_record);
                }
            }
        } else {
            // If no record exists, create one
            $this->create_ip_record($user_ip, date('Y-m-d H:i:s', $current_time));
            $ip_record = $this->wpdb->get_row($this->wpdb->prepare("SELECT * FROM $this->table_name WHERE ips = %s", $user_ip));
        }

        // Check if last attempt was within the time frame
        $last_attempt_time = strtotime($ip_record->last_attempt); // Convert last attempt to timestamp

        // Check if the time frame has passed
        if (($current_time - $last_attempt_time) < $this->time_frame) {
            // If attempts exceed limit, block further requests
            if ($ip_record->send_count >= $this->limit) {
                $this->block_ip($user_ip, date('Y-m-d H:i:s', $current_time));
                return ['success' => false, 'message' => 'تعداد درخواست های ارسالی از طرف شما زیاد است. مسدود می شوید.'];
            }
        } else {
            // Only reset attempts if the time frame has fully passed
            $this->reset_attempts($user_ip, date('Y-m-d H:i:s', $current_time));
        }

        return ['success' => true, 'ip_record' => $ip_record];
    }

    private function create_ip_record($user_ip, $current_time) {
        $this->wpdb->insert($this->table_name, [
            'ips' => $user_ip,
            'send_count' => 0,
            'last_attempt' => $current_time,
            'date' => $current_time
        ]);
    }

    private function reset_attempts($user_ip, $current_time) {
        $this->wpdb->update($this->table_name, [
            'send_count' => 0,
            'last_attempt' => $current_time
        ], ['ips' => $user_ip]);
    }

    private function block_ip($user_ip, $current_time) {
        $block_until_time = date('Y-m-d H:i:s', strtotime('+' . $this->block_time . ' seconds', strtotime($current_time)));
        $this->wpdb->update($this->table_name, [
            'blocked' => 1,
            'blocked_until' => $block_until_time
        ], ['ips' => $user_ip]);
    }

    private function unblock_ip($ip_record) {
        $this->wpdb->update($this->table_name, [
            'blocked' => 0,
            'blocked_until' => null,
            'send_count' => 0, // Reset the count after unblocking
            'last_attempt' => current_time('mysql', 1)
        ], ['ips' => $ip_record->ips]);
    }

    public function increment_attempts($user_ip) {
        $this->wpdb->update($this->table_name, [
            'send_count' => $this->wpdb->get_var($this->wpdb->prepare("SELECT send_count FROM $this->table_name WHERE ips = %s", $user_ip)) + 1,
            'last_attempt' => current_time('mysql', 1)
        ], ['ips' => $user_ip]);
    }
}