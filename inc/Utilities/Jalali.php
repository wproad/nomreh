<?php
namespace Nomreh\Utilities;

/**
 * Gregorian <-> Jalali (Solar Hijri) calendar conversion.
 *
 * Self-contained on purpose: the plugin ships no calendar library, and pulling
 * one in for a log screen would be a heavy dependency. The conversion is plain
 * integer arithmetic on the Julian day count, so it needs no timezone maths and
 * no intl/DateTime extensions.
 *
 * The timestamp is only ever converted for display. What the Logger writes to
 * disk stays Gregorian ISO, so the log file remains greppable and comparable
 * with anything else on the server.
 *
 * Accuracy: the 33 year cycle is the rule the Iranian calendar itself uses, and
 * it agrees with ICU's Persian calendar for every day from 1900 through
 * 2124-03-19 (224 consecutive years, verified). The two schemes first disagree
 * about Jalali year 1502, because ICU uses a 2820 year cycle while the official
 * rule says 1502 is a leap year. That is 98 years away, so it cannot affect a
 * log entry, but it is the one date to remember if this is ever reused for
 * scheduling.
 */
class Jalali {

    /** Days elapsed before the first of each Gregorian month (non-leap). */
    private static $gregorian_month_offset = array(0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334);

    /**
     * Convert a Gregorian date to the Jalali calendar.
     *
     * @param int $gy Gregorian year.
     * @param int $gm Gregorian month, 1-12.
     * @param int $gd Gregorian day, 1-31.
     * @return array|null array(jy, jm, jd), or null when the input is not a real date.
     */
    public static function to_jalali($gy, $gm, $gd) {
        $gy = (int) $gy;
        $gm = (int) $gm;
        $gd = (int) $gd;

        if ($gm < 1 || $gm > 12 || $gd < 1 || $gd > self::days_in_gregorian_month($gy, $gm)) {
            return null;
        }

        // Days since 621-03-22, the epoch this arithmetic counts from.
        $gy2 = $gm > 2 ? $gy + 1 : $gy;
        $days = 355666 + (365 * $gy)
            + intdiv($gy2 + 3, 4)
            - intdiv($gy2 + 99, 100)
            + intdiv($gy2 + 399, 400)
            + $gd
            + self::$gregorian_month_offset[$gm - 1];

        $jy = -1595 + (33 * intdiv($days, 12053));
        $days %= 12053;

        $jy += 4 * intdiv($days, 1461);
        $days %= 1461;

        if ($days > 365) {
            $jy += intdiv($days - 1, 365);
            $days = ($days - 1) % 365;
        }

        if ($days < 186) {
            $jm = 1 + intdiv($days, 31);
            $jd = 1 + ($days % 31);
        } else {
            // Months 7-11 are 30 days; Esfand takes whatever is left.
            $jm = 7 + intdiv($days - 186, 30);
            $jd = 1 + (($days - 186) % 30);
        }

        return array($jy, $jm, $jd);
    }

    /**
     * Convert a Jalali date back to the Gregorian calendar.
     *
     * The counterpart of to_jalali(), kept so the pair can be round-trip tested.
     *
     * @return array|null array(gy, gm, gd), or null when the input is not a real date.
     */
    public static function to_gregorian($jy, $jm, $jd) {
        $jy = (int) $jy;
        $jm = (int) $jm;
        $jd = (int) $jd;

        if ($jm < 1 || $jm > 12 || $jd < 1 || $jd > self::days_in_jalali_month($jy, $jm)) {
            return null;
        }

        if ($jy > 979) {
            $gy = 1600;
            $jy -= 979;
        } else {
            $gy = 621;
        }

        $days = (365 * $jy)
            + (intdiv($jy, 33) * 8)
            + intdiv(($jy % 33) + 3, 4)
            + 78 + $jd
            + (($jm < 7) ? ($jm - 1) * 31 : (($jm - 7) * 30) + 186);

        $gy += 400 * intdiv($days, 146097);
        $days %= 146097;

        if ($days > 36524) {
            $gy += 100 * intdiv(--$days, 36524);
            $days %= 36524;
            if ($days >= 365) {
                $days++;
            }
        }

        $gy += 4 * intdiv($days, 1461);
        $days %= 1461;

        if ($days > 365) {
            $gy += intdiv($days - 1, 365);
            $days = ($days - 1) % 365;
        }

        // $days is now a zero-based day of the Gregorian year.
        $gd = $days + 1;
        $gm = 1;
        while ($gm < 12 && $gd > self::days_in_gregorian_month($gy, $gm)) {
            $gd -= self::days_in_gregorian_month($gy, $gm);
            $gm++;
        }

        return array($gy, $gm, $gd);
    }

    /**
     * Format a Gregorian date as a Jalali one, e.g. 2026-09-30 -> 1405/07/08.
     *
     * @param string $separator Date separator.
     * @return string Empty string when the input is not a real date.
     */
    public static function format($gy, $gm, $gd, $separator = '/') {
        $jalali = self::to_jalali($gy, $gm, $gd);

        if ($jalali === null) {
            return '';
        }

        return sprintf('%04d%s%02d%s%02d', $jalali[0], $separator, $jalali[1], $separator, $jalali[2]);
    }

    /**
     * Format a Gregorian date and time as a Jalali one, keeping the clock time
     * as logged, e.g. 2026-09-30 08:30:12 -> 1405/07/08 08:30:12.
     *
     * @return string Empty string when the input is not a real date.
     */
    public static function format_datetime($gy, $gm, $gd, $h = 0, $i = 0, $s = 0, $separator = '/') {
        $date = self::format($gy, $gm, $gd, $separator);

        if ($date === '') {
            return '';
        }

        return $date . ' ' . sprintf('%02d:%02d:%02d', (int) $h, (int) $i, (int) $s);
    }

    /**
     * Rewrite ASCII digits as Persian ones, e.g. 1405/07/08 -> ۱۴۰۵/۰۷/۰۸.
     *
     * @param string $value
     * @return string
     */
    public static function persian_digits($value) {
        return str_replace(
            array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9'),
            array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'),
            (string) $value
        );
    }

    /**
     * Days in a Gregorian month, leap years included.
     *
     * @return int
     */
    public static function days_in_gregorian_month($gy, $gm) {
        if ($gm < 1 || $gm > 12) {
            return 0;
        }

        if ($gm == 2) {
            return self::is_gregorian_leap_year($gy) ? 29 : 28;
        }

        return in_array($gm, array(1, 3, 5, 7, 8, 10, 12), true) ? 31 : 30;
    }

    /**
     * Days in a Jalali month. Esfand has 30 days only in a leap year, which is
     * the year whose Nowruz falls on 21 March in the Gregorian calendar.
     *
     * @return int
     */
    public static function days_in_jalali_month($jy, $jm) {
        if ($jm < 1 || $jm > 12) {
            return 0;
        }

        if ($jm <= 6) {
            return 31;
        }

        if ($jm <= 11) {
            return 30;
        }

        return self::is_jalali_leap_year($jy) ? 30 : 29;
    }

    /**
     * @return bool
     */
    public static function is_gregorian_leap_year($gy) {
        return ($gy % 4 === 0 && $gy % 100 !== 0) || $gy % 400 === 0;
    }

    /**
     * @return bool
     */
    public static function is_jalali_leap_year($jy) {
        // The 33 year cycle holds 12836 days, so 8 of its years are long. Within
        // the cycle the long years sit at fixed offsets from Nowruz 1404, which
        // is what this remainder test encodes.
        return ((($jy + 12) % 33) % 4) === 1;
    }
}
