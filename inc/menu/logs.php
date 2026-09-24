<?php
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

$lines = $logger->get_recent_lines(300);
$count = $logger->get_line_count();
$settings_url = admin_url('options-general.php?page=nomreh&tab=settings&section=general');
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
                <?php foreach ($lines as $line) : ?>
                    <li class="<?php echo strpos($line, 'REGISTER OK') !== false ? 'register' : (strpos($line, 'LOGIN FAILED') !== false ? 'failed' : 'login'); ?>">
                        <code><?php echo esc_html($line); ?></code>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
        <div style="margin-top:12px; font-size:12px; color:#666;">
            <span style="color:#2271b1;">■</span> موفق &nbsp;
            <span style="color:#d63638;">■</span> ناموفق (تلاش) &nbsp;
            <span style="color:#d63638;">■</span> ارسال شد اما وارد نشد &nbsp;
            <span style="color:#00a32a;">■</span> ثبت نام
        </div>
    <?php endif; ?>
</div>
