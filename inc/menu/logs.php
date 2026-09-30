<?php
use Nomreh\Utilities\Jalali;

if (!current_user_can('manage_options')) {
    return;
}

$logger = new \Nomreh\Core\Logger();
$logs_enabled = \Nomreh\Core\Logger::is_enabled();

if (isset($_POST['clear_nomreh_login_logs'])) {
    check_admin_referer('nomreh_login_logs');
    $logger->clear();
    echo '<div class="updated"><p>لاگ ورود پاک شد.</p></div>';
}

// The log file is trimmed to the last Logger::MAX_LINES entries, so this shows
// everything that is stored. Read the constant so the two cannot drift apart.
$lines = $logger->get_recent_lines(\Nomreh\Core\Logger::MAX_LINES);
$count = $logger->get_line_count();
$settings_url = admin_url('options-general.php?page=nomreh&tab=settings&section=general');

/**
 * Split "[2026-09-30 08:30:12] MESSAGE" into a Jalali date and the message.
 *
 * The conversion happens here rather than in the Logger on purpose: what is
 * written to disk stays Gregorian ISO, so the file remains greppable and
 * existing logs pick up the new format with no rewrite. A line the regex does
 * not recognise is still shown, just without a date.
 */
$rows = array();
foreach ($lines as $line) {
    $row = array(
        'status'  => strpos($line, 'REGISTER OK') !== false
            ? 'register'
            : (strpos($line, 'LOGIN FAILED') !== false ? 'failed' : 'login'),
        'date'    => '',
        'gregorian' => '',
        'message' => $line,
    );

    if (preg_match('/^\[(?<stamp>\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]\s*(?<message>.*)$/', $line, $matches)) {
        // The stamp is already in site local time, so it is only ever read back
        // field by field - no timezone conversion is involved.
        $stamp = DateTime::createFromFormat('!Y-m-d H:i:s', $matches['stamp']);

        if ($stamp !== false) {
            $row['gregorian'] = $matches['stamp'];
            $row['date'] = Jalali::persian_digits(Jalali::format_datetime(
                (int) $stamp->format('Y'),
                (int) $stamp->format('n'),
                (int) $stamp->format('j'),
                (int) $stamp->format('G'),
                (int) $stamp->format('i'),
                (int) $stamp->format('s')
            ));
        }

        $row['message'] = $matches['message'];
    }

    $rows[] = $row;
}
?>
<br class="clear">

<div id="wpma-logs" class="tab-content">
    <h2>لاگ ورود (موفق و ناموفق)</h2>
    <p class="description">
        هر ورود یا ثبت‌نام موفق، و همچنین تلاش‌های ناموفق (مثلاً کد تایید اشتباه یا منقضی شده) از طریق نُمره در اینجا ثبت می‌شود.
        شماره موبایل به‌صورت جزئی نمایش داده می‌شود.
        از <a href="<?php echo esc_url($settings_url); ?>">تنظیمات عمومی</a> می‌توانید ثبت لاگ را خاموش کنید.
    </p>

    <?php if (!$logs_enabled) : ?>
        <div class="notice notice-warning inline">
            <p>
                ثبت لاگ ورود خاموش است. ورودهای جدید نوشته نمی‌شوند.
                <a href="<?php echo esc_url($settings_url); ?>">فعال کردن در تنظیمات</a>
            </p>
        </div>
    <?php endif; ?>

    <p>
        <strong>تعداد کل خطوط:</strong> <?php echo (int) $count; ?>
        <?php if (!empty($lines)) : ?>
            — <strong>نمایش:</strong> <?php echo count($lines); ?> مورد اخیر
        <?php endif; ?>
    </p>

    <form method="post" style="margin-bottom: 12px;">
        <?php wp_nonce_field('nomreh_login_logs'); ?>
        <input type="submit" name="clear_nomreh_login_logs" class="button" value="پاک کردن لاگ"
               onclick="return confirm('همه خطوط لاگ ورود پاک شود؟');">
    </form>

    <?php if (empty($lines)) : ?>
        <div class="notice notice-info inline"><p>هنوز ورود موفقی ثبت نشده است.</p></div>
    <?php else : ?>
        <div class="nomreh-login-log">
            <ol class="nomreh-login-log-list">
                <?php foreach ($rows as $row) : ?>
                    <li class="<?php echo esc_attr($row['status']); ?>">
                        <?php if ($row['date'] !== '') : ?>
                            <span class="nomreh-login-log-date" title="<?php echo esc_attr($row['gregorian']); ?>"><?php echo esc_html($row['date']); ?></span>
                        <?php endif; ?>
                        <code><?php echo esc_html($row['message']); ?></code>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
        <div style="margin-top:12px; font-size:12px; color:#666;">
            <span style="color:#2271b1;">■</span> موفق &nbsp;
            <span style="color:#d63638;">■</span> ناموفق (تلاش) &nbsp;
            <span style="color:#00a32a;">■</span> ثبت نام
        </div>
    <?php endif; ?>
</div>
