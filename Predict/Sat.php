<?php

class Predict_Sat
{
    $tle      = null;   /*!< Keplerian elements */
    $flags    = 0;      /*!< Flags for algo ctrl */
    $sgps     = null;
    $dps      = null;
    $deep_arg = null;
    $pos      = null;   /*!< Raw position and range */
    $vel      = null;   /*!< Raw velocity */

    /*** FIXME: REMOVE */
    $bearing = null;   /*!< Az, El, range and vel */
    $astro   = null;   /*!< Ra and Decl */
    /*** END */

    /* time keeping fields */
    $jul_epoch = null;
    $jul_utc   = null;
    $tsince    = null;
    $aos       = null;    /*!< Next AOS. */
    $los       = null;    /*!< Next LOS */

    $az         = null;   /*!< Azimuth [deg] */
    $el         = null;   /*!< Elevation [deg] */
    $range      = null;   /*!< Range [km] */
    $range_rate = null;   /*!< Range Rate [km/sec] */
    $ra         = null;   /*!< Right Ascension [deg] */
    $dec        = null;   /*!< Declination [deg] */
    $ssplat     = null;   /*!< SSP latitude [deg] */
    $ssplon     = null;   /*!< SSP longitude [deg] */
    $alt        = null;   /*!< altitude [km] */
    $velo       = null;   /*!< velocity [km/s] */
    $ma         = null;   /*!< mean anomaly */
    $footprint  = null;   /*!< footprint */
    $phase      = null;   /*!< orbit phase */
    $meanmo     = null;   /*!< mean motion kept in rev/day */
    $orbit      = null;   /*!< orbit number */
    $otype      = null;   /*!< orbit type. */

    public function __construct($name, $nickname, $website = null)
    {
        $this->name = $name;
        $this->nickname = $nickname;
        $this->website = $website;
    }
}
