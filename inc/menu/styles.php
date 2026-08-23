<?php
if (!current_user_can('manage_options')) {
    return;
}

$defaults = [
    'nomreh_first_color'      => '#d73767',
    'nomreh_first_color_alt'  => '#c02654',
    'nomreh_surface_color'    => '#ffffff',
    'nomreh_border_color'     => '#cccccc',
    'nomreh_button_text_color'=> '#ffffff',
    'nomreh_radius'           => '8',
    'nomreh_custom_styles'    => '',
];

$nomreh_first_color       = get_option('nomreh_first_color', $defaults['nomreh_first_color']);
$nomreh_first_color_alt   = get_option('nomreh_first_color_alt', $defaults['nomreh_first_color_alt']);
$nomreh_surface_color     = get_option('nomreh_surface_color', $defaults['nomreh_surface_color']);
$nomreh_border_color      = get_option('nomreh_border_color', $defaults['nomreh_border_color']);
$nomreh_button_text_color = get_option('nomreh_button_text_color', $defaults['nomreh_button_text_color']);
$nomreh_radius            = get_option('nomreh_radius', $defaults['nomreh_radius']);
$nomreh_custom_styles     = get_option('nomreh_custom_styles', $defaults['nomreh_custom_styles']);

// Handle reset
if (isset($_POST['reset_nomreh_style_settings'])) {
    check_admin_referer('nomreh_style_settings');

    foreach ($defaults as $option => $value) {
        update_option($option, $value);
    }

    $nomreh_first_color       = $defaults['nomreh_first_color'];
    $nomreh_first_color_alt   = $defaults['nomreh_first_color_alt'];
    $nomreh_surface_color     = $defaults['nomreh_surface_color'];
    $nomreh_border_color      = $defaults['nomreh_border_color'];
    $nomreh_button_text_color = $defaults['nomreh_button_text_color'];
    $nomreh_radius            = $defaults['nomreh_radius'];
    $nomreh_custom_styles     = $defaults['nomreh_custom_styles'];

    echo '<div class="updated"><p>استایل‌ها به حالت پیش‌فرض بازگردانده شد.</p></div>';
}

// Handle form submission
if (isset($_POST['save_nomreh_style_settings'])) {
    check_admin_referer('nomreh_style_settings');

    $nomreh_custom_styles     = isset($_POST['nomreh_custom_styles']) ? wp_strip_all_tags($_POST['nomreh_custom_styles']) : '';
    $nomreh_first_color       = sanitize_hex_color($_POST['nomreh_first_color'] ?? '') ?: $defaults['nomreh_first_color'];
    $nomreh_first_color_alt   = sanitize_hex_color($_POST['nomreh_first_color_alt'] ?? '') ?: $defaults['nomreh_first_color_alt'];
    $nomreh_surface_color     = sanitize_hex_color($_POST['nomreh_surface_color'] ?? '') ?: $defaults['nomreh_surface_color'];
    $nomreh_border_color      = sanitize_hex_color($_POST['nomreh_border_color'] ?? '') ?: $defaults['nomreh_border_color'];
    $nomreh_button_text_color = sanitize_hex_color($_POST['nomreh_button_text_color'] ?? '') ?: $defaults['nomreh_button_text_color'];

    $radius_raw = isset($_POST['nomreh_radius']) ? sanitize_text_field(wp_unslash($_POST['nomreh_radius'])) : $defaults['nomreh_radius'];
    $nomreh_radius = preg_match('/^\d+(\.\d+)?$/', $radius_raw) ? $radius_raw : $defaults['nomreh_radius'];

    update_option('nomreh_custom_styles', $nomreh_custom_styles);
    update_option('nomreh_first_color', $nomreh_first_color);
    update_option('nomreh_first_color_alt', $nomreh_first_color_alt);
    update_option('nomreh_surface_color', $nomreh_surface_color);
    update_option('nomreh_border_color', $nomreh_border_color);
    update_option('nomreh_button_text_color', $nomreh_button_text_color);
    update_option('nomreh_radius', $nomreh_radius);

    echo '<div class="updated"><p>تنظیمات ذخیره شد.</p></div>';
}
?>
<br class="clear">

<div id="wpma-styles" class="tab-content">
    <h2>استایل</h2>

    <form method="post">
        <?php wp_nonce_field('nomreh_style_settings'); ?>
        <table class="form-table">
            <tr>
                <th><label for="nomreh_first_color">رنگ برند</label></th>
                <td>
                    <input type="text" name="nomreh_first_color" id="nomreh_first_color" value="<?php echo esc_attr($nomreh_first_color); ?>" class="nomreh-color-picker" data-default-color="<?php echo esc_attr($defaults['nomreh_first_color']); ?>">
                    <p class="description">رنگ اصلی دکمه‌ها و لینک‌های فرانت‌اند.</p>
                </td>
            </tr>

            <tr>
                <th><label for="nomreh_first_color_alt">رنگ هاور / جایگزین</label></th>
                <td>
                    <input type="text" name="nomreh_first_color_alt" id="nomreh_first_color_alt" value="<?php echo esc_attr($nomreh_first_color_alt); ?>" class="nomreh-color-picker" data-default-color="<?php echo esc_attr($defaults['nomreh_first_color_alt']); ?>">
                    <p class="description">رنگ تیره‌تر برای حالت هاور و انیمیشن بارگذاری دکمه.</p>
                </td>
            </tr>

            <tr>
                <th><label for="nomreh_button_text_color">رنگ متن دکمه</label></th>
                <td>
                    <input type="text" name="nomreh_button_text_color" id="nomreh_button_text_color" value="<?php echo esc_attr($nomreh_button_text_color); ?>" class="nomreh-color-picker" data-default-color="<?php echo esc_attr($defaults['nomreh_button_text_color']); ?>">
                    <p class="description">رنگ متن روی دکمه‌های فرم.</p>
                </td>
            </tr>

            <tr>
                <th><label for="nomreh_surface_color">رنگ پس‌زمینه فرم</label></th>
                <td>
                    <input type="text" name="nomreh_surface_color" id="nomreh_surface_color" value="<?php echo esc_attr($nomreh_surface_color); ?>" class="nomreh-color-picker" data-default-color="<?php echo esc_attr($defaults['nomreh_surface_color']); ?>">
                    <p class="description">پس‌زمینه باکس فرم ورود/ثبت‌نام.</p>
                </td>
            </tr>

            <tr>
                <th><label for="nomreh_border_color">رنگ حاشیه ورودی</label></th>
                <td>
                    <input type="text" name="nomreh_border_color" id="nomreh_border_color" value="<?php echo esc_attr($nomreh_border_color); ?>" class="nomreh-color-picker" data-default-color="<?php echo esc_attr($defaults['nomreh_border_color']); ?>">
                    <p class="description">رنگ حاشیه فیلدهای ورودی.</p>
                </td>
            </tr>

            <tr>
                <th><label for="nomreh_radius">گردی گوشه‌ها (px)</label></th>
                <td>
                    <input type="number" name="nomreh_radius" id="nomreh_radius" value="<?php echo esc_attr($nomreh_radius); ?>" min="0" max="48" step="1" class="small-text">
                    <p class="description">شعاع گوشه برای فرم، ورودی‌ها و دکمه‌ها.</p>
                </td>
            </tr>

            <tr>
                <th><label for="nomreh_custom_styles">استایل سفارشی</label></th>
                <td>
                    <textarea name="nomreh_custom_styles" id="nomreh_custom_styles" rows="10" cols="50" class="large-text ltr" placeholder=":root { --spd-container-max-width: 420px; --spd-toast-radius: 12px; }" dir="ltr"><?php echo esc_textarea($nomreh_custom_styles); ?></textarea>
                    <p class="description">
                        استایل‌های سفارشی (CSS) خود را وارد کنید. رنگ‌ها و گردی گوشه از فیلدهای بالا تنظیم می‌شوند؛ برای بقیه متغیرها در <code>:root</code> بازنویسی کنید:
                        <br>
                        <code>--spd-container-max-width</code>,
                        <code>--spd-container-padding</code>,
                        <code>--spd-input-padding</code>,
                        <code>--spd-heading-size</code>,
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
            <input type="submit" name="save_nomreh_style_settings" value="ذخیره تنظیمات" class="button button-primary">
            <input type="submit" name="reset_nomreh_style_settings" value="بازگردانی به پیش‌فرض" class="button" onclick="return confirm('همه تنظیمات استایل به حالت پیش‌فرض بازگردانده شود؟');">
        </p>
    </form>
</div>
