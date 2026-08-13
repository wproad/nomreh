<?php
namespace Nomreh;

class FormShortcodes {
    public function __construct() {
        add_shortcode('nomreh_otp_forms', array($this, 'render_otp_forms'));
    }

    public function render_otp_forms() {
        // Store the redirect URL
        $redirect_url = $_GET['redirect'] ?? '/';

        // Start output buffering
        ob_start();

        // Check login status and return JavaScript redirect if logged in
        if (is_user_logged_in()) {
            ?>
            <script>
                window.location.href = <?php echo json_encode($redirect_url); ?>;
            </script>
            <?php
            return ob_get_clean();
        }
        ?>
        <div class="spd-otp-container">
            <!-- Send OTP Form -->
            <form id="send-otp-form" class="otp-form">
                <h3>ورود/ثبت نام</h3>
                <label for="phone">شماره موبایل</label>
                <input type="tel" id="phone" name="phone" placeholder="0912xxxxxxx" inputmode="numeric" autocomplete="tel" maxlength="11" required>
                <?php if(Captcha::is_captcha_enabled()):?>
                <div class="spd-captcha">
                    <input type="text" id="captcha-input" name="captcha" placeholder="کد امنیتی" required>
                    <img id="spd-captcha-image" src="<?php echo esc_url(NOMREH_PLUGIN_URL . '/inc/captcha/captcha-image.php?v=' .uniqid() );?>" />
                </div>
                <?php endif; ?>
                <button class="spd-button" type="submit"><span class="text">ارسال کد تایید</span></button>
                <div id="send-otp-message" class="form-message"></div>
            </form>

            <!-- Verify OTP Form (Initially hidden) -->
            <form id="verify-otp-form" class="otp-form" style="display: none;">
                <h3>اعتبارسنجی</h3>
                <label for="otp_digit_1">کد تایید</label>
                <div class="spd-otp-digits" dir="ltr">
                    <input type="text" id="otp_digit_1" class="otp-digit" inputmode="numeric" autocomplete="one-time-code" maxlength="1" pattern="[0-9]*" required aria-label="رقم ۱">
                    <input type="text" id="otp_digit_2" class="otp-digit" inputmode="numeric" autocomplete="one-time-code" maxlength="1" pattern="[0-9]*" required aria-label="رقم ۲">
                    <input type="text" id="otp_digit_3" class="otp-digit" inputmode="numeric" autocomplete="one-time-code" maxlength="1" pattern="[0-9]*" required aria-label="رقم ۳">
                    <input type="text" id="otp_digit_4" class="otp-digit" inputmode="numeric" autocomplete="one-time-code" maxlength="1" pattern="[0-9]*" required aria-label="رقم ۴">
                </div>
                <input type="hidden" id="otp_code" name="otp_code" value="">
                <p class="spd-otp-hint">
                    کد تایید به شماره <span id="phone-clone"></span> فرستاده شد.
                    <a href="#" id="change-phone" class="spd-change-phone">تصحیح شماره</a>؟
                </p>
                <input type="hidden" id="redirect_url" name="redirect_url" value="<?php echo esc_attr($redirect_url); ?>">
                <button class="spd-button" type="submit"><span class="text">تایید</span></button>
                <div id="verify-otp-message" class="form-message"></div>
                <div id="resend-otp">
                    <span class="timer"></span>
                    <span class="resend-btn">ارسال مجدد کد یکبارمصرف</span>
                </div>
            </form>

            <!-- Complete Registration Form -->
            <form id="complete-registration-form" class="otp-form spd-hidden-labels" style="display: none;">
                <h3>ثبت نام</h3>
                <label for="first_name">نام</label>
                <input type="text" id="first_name" name="first_name" placeholder="نام" required>
                <label for="last_name">نام خانوادگی:</label>
                <input type="text" id="last_name" name="last_name" placeholder="نام خانوادگی" required>
                <label for="email">ایمیل (اختیاری)</label>
                <input type="email" id="email" name="email" placeholder="ایمیل (اختیاری)">
                <input type="hidden" id="reg_phone" name="reg_phone">
                <input type="hidden" id="reg_redirect_url" name="reg_redirect_url">
                <button class="spd-button" type="submit"><span class="text">تکمیل ثبت نام</span></button>
                <div id="registration-message" class="form-message"></div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
}

// Instantiate the Shortcodes class to register the shortcode
new FormShortcodes();