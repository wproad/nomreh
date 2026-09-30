# Changelog

All notable changes to Nomreh are documented in this file.

## 0.11.5

### Added

- Log failed login attempts (wrong or expired OTP) with the phone number, IP and
  reason, and mark them in red in the login log screen.
- Colour legend on the login log screen for successful, failed and registration
  entries.
- The form now flags itself as rendered while the shortcode runs, so the
  front-end assets still load when the form comes from a widget, a block or a
  theme template.

### Changed

- Load `public.css`, `toastify.css`, `public.js` and `toastify.js` only on
  requests that actually render the login/register form: the WooCommerce account
  page, a page containing `[nomreh_otp_forms]`, or the Woodmart sidebar form.
  Other pages fall back to enqueueing in the footer, so nothing is lost.
- `public.js` and `toastify.js` are now deferred, keeping them off the
  render-blocking path on WordPress 6.3 and later (unchanged behaviour on older
  versions).
- `toastify.js` no longer declares a jQuery dependency; it never used it.
- The Woodmart sidebar replacement is now gated on the Woodmart and header
  builder helpers actually existing, so enabling the option without the theme no
  longer triggers a fatal error.
- The decision to render the Woodmart sidebar form lives in a single
  `Woodmart::is_sidebar_form_active()` method, shared by the asset check and the
  render callback so the two cannot disagree.
- `Core\Assets` is now a singleton, and the shortcode tag is exposed as
  `FormShortcodes::SHORTCODE` so registration and detection cannot drift.
- Raise Tested up to WordPress 7.1.2.

## 0.11.4

- Render the Nomreh OTP form in the WooCommerce login form template, so login
  and registration use SMS codes on any page WooCommerce outputs
  `myaccount/form-login.php`, not just the My Account page.
- Redirect guests away from the checkout page to the login page.

## 0.11.3

- Add plugin update metadata: `readme.txt`, changelog, icons, and banners.
- Raise Tested up to WordPress 7.1.
- Point the GitHub update checker at `wproad/nomreh`.

## 0.11.2

- Add optional login logs and a setting to disable them.
- Add optional footer text under the login form, with TinyMCE formatting.

## 0.11.1

- Improve OTP resend timer copy.

## 0.11.0

- Replace the OTP field with individual digit inputs.
- Add login and registration logging.
- Improve OTP hint, resend timer, and toast behaviour.

## 0.10.0

- Add a Styles settings tab with brand colors, radius, and custom CSS.
- Adjust toast position for RTL.

## 0.9.0

- Add Meli Payamak provider and connection testing.
- Rename the plugin to Nomreh.

## 0.8.0

- Add captcha, Woodmart support, and permission checks.
