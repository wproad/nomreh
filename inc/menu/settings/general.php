<?php
// Get saved options
$nomreh_active_captcha = get_option('nomreh_active_captcha', 'no');
$nomreh_woodmart_support = get_option('nomreh_woodmart_support', 'no');
$nomreh_custom_styles = get_option('nomreh_custom_styles', '');
$nomreh_sms_provider = get_option('nomreh_sms_provider', 'kavenegar');
$nomreh_first_color = get_option('nomreh_first_color', '#d73767');
$nomreh_first_color_alt = get_option('nomreh_first_color_alt', '#c02654');

// Handle form submission
if (isset($_POST['save_nomreh_login_settings'])) {
    // Sanitize and save the values
    $nomreh_active_captcha = isset($_POST['nomreh_active_captcha']) ? 'yes' : 'no';
    $nomreh_woodmart_support = isset($_POST['nomreh_woodmart_support']) ? 'yes' : 'no';
    $nomreh_custom_styles = isset($_POST['nomreh_custom_styles']) ? wp_strip_all_tags($_POST['nomreh_custom_styles']) : '';
    $nomreh_sms_provider = sanitize_text_field($_POST['nomreh_sms_provider']);
    $nomreh_first_color = sanitize_hex_color($_POST['nomreh_first_color'] ?? '') ?: '#d73767';
    $nomreh_first_color_alt = sanitize_hex_color($_POST['nomreh_first_color_alt'] ?? '') ?: '#c02654';

    update_option('nomreh_active_captcha', $nomreh_active_captcha);
    update_option('nomreh_woodmart_support', $nomreh_woodmart_support);
    update_option('nomreh_custom_styles', $nomreh_custom_styles);
    update_option('nomreh_sms_provider', $nomreh_sms_provider);
    update_option('nomreh_first_color', $nomreh_first_color);
    update_option('nomreh_first_color_alt', $nomreh_first_color_alt);

    // Success message
    echo '<div class="updated"><p>تنظیمات ذخیره شد.</p></div>';
}

// Get available SMS providers
$sms_providers = \Nomreh\Sms::get_providers();
?>
<br class="clear">

<h2>عمومی</h2>

<form method="post">
    <table class="form-table">
        <tr>
            <th><label for="nomreh_sms_provider">ارائه دهنده پیامک</label></th>
            <td>
                <select name="nomreh_sms_provider" id="nomreh_sms_provider">
                    <?php foreach ($sms_providers as $provider_name => $provider): ?>
                        <option value="<?php echo esc_attr($provider_name); ?>" <?php selected($nomreh_sms_provider, $provider_name); ?>>
                            <?php echo esc_html($provider->get_display_name()); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="description">ارائه دهنده پیامک مورد نظر خود را انتخاب کنید.</p>
            </td>
        </tr>

        <tr>
            <th><label for="nomreh_active_captcha">کپچا</label></th>
            <td>
                <input type="checkbox" name="nomreh_active_captcha" id="nomreh_active_captcha" value="yes" <?php checked($nomreh_active_captcha, 'yes'); ?>>
                <label for="nomreh_active_captcha">کپچا در فرم لاگین فعال باشد؟</label>
            </td>
        </tr>

        <tr>
            <th><label for="nomreh_woodmart_support">وودمارت</label></th>
            <td>
                <input type="checkbox" name="nomreh_woodmart_support" id="nomreh_woodmart_support" value="yes" <?php checked($nomreh_woodmart_support, 'yes'); ?>>
                <label for="nomreh_woodmart_support">نمایش فرم Nomreh بجای فرم لاگین پیش فرض وودمارت؟</label>
            </td>
        </tr>

        <tr>
            <th><label for="nomreh_first_color">رنگ برند</label></th>
            <td>
                <input type="text" name="nomreh_first_color" id="nomreh_first_color" value="<?php echo esc_attr($nomreh_first_color); ?>" class="nomreh-color-picker" data-default-color="#d73767">
                <p class="description">رنگ اصلی دکمه‌ها و لینک‌های فرانت‌اند.</p>
            </td>
        </tr>

        <tr>
            <th><label for="nomreh_first_color_alt">رنگ هاور / جایگزین</label></th>
            <td>
                <input type="text" name="nomreh_first_color_alt" id="nomreh_first_color_alt" value="<?php echo esc_attr($nomreh_first_color_alt); ?>" class="nomreh-color-picker" data-default-color="#c02654">
                <p class="description">رنگ تیره‌تر برای حالت هاور و انیمیشن بارگذاری دکمه.</p>
            </td>
        </tr>

        <tr>
            <th><label for="nomreh_custom_styles">استایل سفارشی</label></th>
            <td>
                <textarea name="nomreh_custom_styles" id="nomreh_custom_styles" rows="10" cols="50" class="large-text ltr" placeholder=":root { --spd-radius: 12px; --spd-surface: #fafafa; }"><?php echo esc_textarea($nomreh_custom_styles); ?></textarea>
                <p class="description">
                    استایل‌های سفارشی (CSS) خود را وارد کنید. برای تغییر ظاهر، متغیرهای زیر را در <code>:root</code> بازنویسی کنید:
                    <br>
                    <code>--first-color</code>,
                    <code>--first-color-alt</code>,
                    <code>--spd-surface</code>,
                    <code>--spd-border-color</code>,
                    <code>--spd-radius</code>,
                    <code>--spd-button-text</code>,
                    <code>--spd-container-max-width</code>,
                    <code>--spd-container-padding</code>,
                    <code>--spd-input-padding</code>,
                    <code>--spd-success-color</code>,
                    <code>--spd-error-color</code>,
                    <code>--spd-toast-bg</code>,
                    <code>--spd-toast-success-bg</code>,
                    <code>--spd-toast-error-bg</code>,
                    <code>--spd-toast-radius</code>
                </p>
            </td>
        </tr>
    </table>
    <p>
        <input type="submit" name="save_nomreh_login_settings" value="ذخیره تنظیمات" class="button button-primary">
    </p>
</form>