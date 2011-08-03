<?php

/*
 * Functions from sgp_time.c and time-tools.c ported to PHP by Bill Shupp
 */

require_once 'Predict.php';
require_once 'Predict/Math.php';

/*
 * Unit SGP_Time
 *       Author:  Dr TS Kelso
 * Original Version:  1992 Jun 02
 * Current Revision:  2000 Jan 22
 * Modified for Y2K:  1999 Mar 07
 *          Version:  2.05
 *        Copyright:  1992-1999, All Rights Reserved
 * Version 1.50 added Y2K support. Due to limitations in the current
 * format of the NORAD two-line element sets, however, only dates
 * through 2056 December 31/2359 UTC are valid.
 * Version 1.60 modifies Calendar_Date to ensure date matches time
 * resolution and modifies Time_of_Day to make it more robust.
 * Version 2.00 adds Julian_Date, Date_Time, and Check_Date to support
 * checking for valid date/times, permitting the use of Time_to_UTC and
 * Time_from_UTC for UTC/local time conversions.
 * Version 2.05 modifies UTC_offset to allow non-integer offsets.
 *
 *   Ported to C by: Neoklis Kyriazis  April 9  2001
 */
class Predict_Time
{
    /* The function Julian_Date_of_Epoch returns the Julian Date of     */
    /* an epoch specified in the format used in the NORAD two-line      */
    /* element sets. It has been modified to support dates beyond       */
    /* the year 1999 assuming that two-digit years in the range 00-56   */
    /* correspond to 2000-2056. Until the two-line element set format   */
    /* is changed, it is only valid for dates through 2056 December 31. */
    public static function Julian_Date_of_Epoch($epoch)
    {
        $year = 0;

        /* Modification to support Y2K */
        /* Valid 1957 through 2056     */
        $day = self::modf($epoch * 1E-3, $year) * 1E3;
        if ($year < 57) {
            $year = $year + 2000;
        } else {
            $year = $year + 1900;
        }
        /* End modification */

        return self::Julian_Date_of_Year($year) + $day;
    }

    /* Equivalent to the C modf function */
    public static function modf($x, &$ipart) {
        $ipart = (int)$x;
        return $x - $ipart;
    }

    /* The function Julian_Date_of_Year calculates the Julian Date  */
    /* of Day 0.0 of {year}. This function is used to calculate the */
    /* Julian Date of any date by using Julian_Date_of_Year, DOY,   */
    /* and Fraction_of_Day. */
    public static function Julian_Date_of_Year($year)
    {
        /* Astronomical Formulae for Calculators, Jean Meeus, */
        /* pages 23-25. Calculate Julian Date of 0.0 Jan year */
        $year = $year - 1;
        $i = $year / 100;
        $A = $i;
        $i = $A / 4;
        $B = 2 - $A + $i;
        $i = 365.25 * $year;
        $i += 30.6001 * 14;
        $jdoy = $i + 1720994.5 + $B;

        return $jdoy;
    }

    /* The function ThetaG calculates the Greenwich Mean Sidereal Time */
    /* for an epoch specified in the format used in the NORAD two-line */
    /* element sets. It has now been adapted for dates beyond the year */
    /* 1999, as described above. The function ThetaG_JD provides the   */
    /* same calculation except that it is based on an input in the     */
    /* form of a Julian Date. */
    public static function ThetaG($epoch, Predict_DeepArg $deep_arg)
    {
        /* Reference:  The 1992 Astronomical Almanac, page B6. */
        // double year,day,UT,jd,TU,GMST,_ThetaG;

        /* Modification to support Y2K */
        /* Valid 1957 through 2056     */
        $year = 0;
        $day = self::modf($epoch * 1E-3, $year) * 1E3;

        if ($year < 57) {
            $year += 2000;
        } else {
            $year += 1900;
        }
        /* End modification */

        $UT = modf($day, $day);
        $jd = self::Julian_Date_of_Year($year) + $day;
        $TU = ($jd - 2451545.0) / 36525;
        $GMST = 24110.54841 + TU * (8640184.812866 + $TU * (0.093104 - $TU * 6.2E-6));
        $GMST = Predict_Math::Modulus($GMST + Predict::secday * Predict::omega_E * $UT, Predict::secday);
        $deep_arg->ds50 = $jd - 2433281.5 + $UT;

        return Predict_Math::FMod2p(6.3003880987 * $deep_arg->ds50 + 1.72944494);
    }

    public static function ThetaG_JD($jd)
    {
        /* Reference:  The 1992 Astronomical Almanac, page B6. */
        $UT   = Predict_Math::Frac($jd + 0.5);
        $jd   = $jd - $UT;
        $TU   = ($jd - 2451545.0) / 36525;
        $GMST = 24110.54841 + $TU * (8640184.812866 + $TU * (0.093104 - $TU * 6.2E-6));
        $GMST = Predict_Math::Modulus($GMST + Predict::secday * Predict::omega_E * $UT, Predict::secday);

        return Predict::twopi * $GMST / Predict::secday;
    }
}
