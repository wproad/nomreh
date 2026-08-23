<?php
namespace Nomreh\Core;

class Logger {
    private $logDir;
    private $logFile;

    /** Keep the last N lines in the file so it stays readable for a beta. */
    const MAX_LINES = 1000;

    public function __construct() {
        $this->logDir = WP_CONTENT_DIR . '/nomreh-logs';
        $this->logFile = $this->logDir . '/login.log';

        $this->ensure_log_dir();
    }

    private function ensure_log_dir() {
        if (!file_exists($this->logDir)) {
            wp_mkdir_p($this->logDir);
        }

        $htaccess = $this->logDir . '/.htaccess';
        if (!file_exists($htaccess)) {
            file_put_contents($htaccess, "Deny from all\n");
        }

        $index = $this->logDir . '/index.php';
        if (!file_exists($index)) {
            file_put_contents($index, "<?php\n// Silence is golden.\n");
        }
    }

    public function logEvent($message) {
        $current_time = current_time('Y-m-d H:i:s');
        $log_message = "[$current_time] $message" . PHP_EOL;
        file_put_contents($this->logFile, $log_message, FILE_APPEND | LOCK_EX);
        $this->trim_if_needed();
    }

    /**
     * Log a successful Nomreh login (existing user).
     */
    public function log_login_success($user_id, $phone) {
        $user = get_userdata($user_id);
        $name = $user ? $user->display_name : '-';
        $ip = $this->client_ip();

        $this->logEvent(sprintf(
            'LOGIN OK | user_id=%d | phone=%s | name=%s | ip=%s',
            (int) $user_id,
            self::mask_phone($phone),
            self::sanitize_log_value($name),
            self::sanitize_log_value($ip)
        ));
    }

    /**
     * Log a successful Nomreh registration (new user, then logged in).
     */
    public function log_register_success($user_id, $phone) {
        $user = get_userdata($user_id);
        $name = $user ? $user->display_name : '-';
        $ip = $this->client_ip();

        $this->logEvent(sprintf(
            'REGISTER OK | user_id=%d | phone=%s | name=%s | ip=%s',
            (int) $user_id,
            self::mask_phone($phone),
            self::sanitize_log_value($name),
            self::sanitize_log_value($ip)
        ));
    }

    /**
     * Newest lines first.
     *
     * @return string[]
     */
    public function get_recent_lines($limit = 200) {
        if (!is_readable($this->logFile)) {
            return [];
        }

        $lines = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false || empty($lines)) {
            return [];
        }

        $lines = array_reverse($lines);
        return array_slice($lines, 0, max(1, (int) $limit));
    }

    public function get_line_count() {
        if (!is_readable($this->logFile)) {
            return 0;
        }
        $lines = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        return is_array($lines) ? count($lines) : 0;
    }

    public function clear() {
        if (file_exists($this->logFile)) {
            file_put_contents($this->logFile, '');
        }
    }

    public static function mask_phone($phone) {
        $phone = preg_replace('/\D+/', '', (string) $phone);
        $len = strlen($phone);
        if ($len < 8) {
            return '****';
        }
        return substr($phone, 0, 4) . str_repeat('*', $len - 6) . substr($phone, -2);
    }

    private static function sanitize_log_value($value) {
        $value = wp_strip_all_tags((string) $value);
        return str_replace(["\r", "\n", '|'], ['', '', '/'], $value);
    }

    private function client_ip() {
        return isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '-';
    }

    private function trim_if_needed() {
        if (!is_readable($this->logFile)) {
            return;
        }
        $lines = file($this->logFile, FILE_IGNORE_NEW_LINES);
        if ($lines === false || count($lines) <= self::MAX_LINES) {
            return;
        }
        $keep = array_slice($lines, -self::MAX_LINES);
        file_put_contents($this->logFile, implode(PHP_EOL, $keep) . PHP_EOL, LOCK_EX);
    }
}
