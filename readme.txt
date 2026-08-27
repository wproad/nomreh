=== Nomreh ===
Contributors: wproad
Donate link: https://nomreh-landing.vercel.app/
Tags: otp, sms, login, woocommerce, kavenegar, melipayamak
Requires at least: 6.0.0
Tested up to: 7.1.0
Requires PHP: 7.4.0
Stable tag: 0.11.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

افزونه ورود و ثبت‌نام با کد تایید پیامکی برای وردپرس و ووکامرس.

== Description ==

نُمره ورود و ثبت‌نام با پیامک (OTP) را برای وردپرس مدیریت می‌کند. مناسب سایت‌های فارسی و فروشگاه‌های ووکامرس است و از ملی‌پیامک و کاوه‌نگار پشتیبانی می‌کند.

= امکانات =

* ورود و ثبت‌نام با شماره موبایل و کد یک‌بارمصرف
* شورت‌کد `[nomreh_otp_forms]` برای نمایش فرم
* پشتیبانی از کاوه‌نگار و ملی‌پیامک
* سازگاری با ووکامرس و جایگزینی فرم ورود وودمارت
* کپچا، محدودیت نرخ درخواست و ثبت لاگ ورود
* سفارشی‌سازی رنگ، شعاع گوشه و متن پایین فرم

= استفاده =

فرم را با این شورت‌کد در برگه ورود یا حساب کاربری قرار دهید:

`[nomreh_otp_forms]`

سپس از پیشخوان، منوی **نمره**، ارائه‌دهنده پیامک را تنظیم کنید.

== Installation ==

1. پوشه `nomreh` را در `/wp-content/plugins/` آپلود کنید، یا فایل ZIP را از پیشخوان نصب کنید.
2. افزونه را از صفحه افزونه‌ها فعال کنید.
3. به **نمره → تنظیمات** بروید و ارائه‌دهنده پیامک (کاوه‌نگار یا ملی‌پیامک) را پیکربندی کنید.
4. شورت‌کد `[nomreh_otp_forms]` را در برگه ورود قرار دهید.
5. در صورت استفاده از وودمارت، گزینه جایگزینی فرم ورود را فعال کنید.

== Frequently Asked Questions ==

= آیا ووکامرس لازم است؟ =

خیر. افزونه روی وردپرس بدون ووکامرس کار می‌کند. اگر ووکامرس فعال باشد، یکپارچگی فروشگاه هم در دسترس است.

= از کدام سرویس‌های پیامک پشتیبانی می‌شود؟ =

کاوه‌نگار و ملی‌پیامک.

= چطور فرم ورود را نمایش بدهم؟ =

شورت‌کد `[nomreh_otp_forms]` را در برگه دلخواه قرار دهید.

== Changelog ==

= 0.11.3 =
* Add plugin update metadata: readme, changelog, icons, and banners.
* Raise Tested up to WordPress 7.1.
* Point the GitHub update checker at the wproad/nomreh repository.

= 0.11.2 =
* Add optional login logs and a setting to turn them off.
* Add optional footer text under the login form, with TinyMCE formatting.

= 0.11.1 =
* Improve OTP resend timer copy.

= 0.11.0 =
* Replace the OTP field with individual digit inputs.
* Add login and registration logging.
* Improve OTP hint, resend timer, and toast behaviour.

= 0.10.0 =
* Add a Styles settings tab with brand colors, radius, and custom CSS.
* Adjust toast position for RTL.

= 0.9.0 =
* Add Meli Payamak provider and connection testing.
* Rename the plugin to Nomreh.

= 0.8.0 =
* Add captcha, Woodmart support, and permission checks.

== Upgrade Notice ==

= 0.11.3 =
Adds plugin details (changelog, icon, compatibility) for the WordPress update screen.
