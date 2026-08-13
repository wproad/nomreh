<?php
if (!current_user_can('manage_options')) {
    return;
}

$logger = new \Nomreh\Core\Logger();

if (isset($_POST['clear_nomreh_login_logs'])) {
    check_admin_referer('nomreh_login_logs');
    $logger->clear();
    echo '<div class="updated"><p>لاگ ورود پاک شد.</p></div>';
}

$lines = $logger->get_recent_lines(300);
$count = $logger->get_line_count();
?>
<br class="clear">

<div id="wpma-logs" class="tab-content">
    <h2>لاگ ورود موفق</h2>
    <p class="description">
        هر ورود یا ثبت‌نام موفق از طریق نُمره در اینجا ثبت می‌شود. مناسب برای مانیتور چندروزه بتا.
        شماره موبایل به‌صورت جزئی نمایش داده می‌شود.
    </p>

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
                    <li class="<?php echo strpos($line, 'REGISTER OK') !== false ? 'register' : 'login'; ?>">
                        <code><?php echo esc_html($line); ?></code>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    <?php endif; ?>
</div>
