<?php

class Predict_Sat
{
    public $name     = null;
    public $nickname = null;
    public $website  = null;

    public $tle      = null;   /*!< Keplerian elements */
    public $flags    = 0;      /*!< Flags for algo ctrl */
    public $sgps     = null;
    public $dps      = null;
    public $deep_arg = null;
    public $pos      = null;   /*!< Raw position and range */
    public $vel      = null;   /*!< Raw velocity */

    /*** FIXME: REMOVE */
    public $bearing = null;   /*!< Az, El, range and vel */
    public $astro   = null;   /*!< Ra and Decl */
    /*** END */

    /* time keeping fields */
    public $jul_epoch = null;
    public $jul_utc   = null;
    public $tsince    = null;
    public $aos       = null;    /*!< Next AOS. */
    public $los       = null;    /*!< Next LOS */

    public $az         = null;   /*!< Azimuth [deg] */
    public $el         = null;   /*!< Elevation [deg] */
    public $range      = null;   /*!< Range [km] */
    public $range_rate = null;   /*!< Range Rate [km/sec] */
    public $ra         = null;   /*!< Right Ascension [deg] */
    public $dec        = null;   /*!< Declination [deg] */
    public $ssplat     = null;   /*!< SSP latitude [deg] */
    public $ssplon     = null;   /*!< SSP longitude [deg] */
    public $alt        = null;   /*!< altitude [km] */
    public $velo       = null;   /*!< velocity [km/s] */
    public $ma         = null;   /*!< mean anomaly */
    public $footprint  = null;   /*!< footprint */
    public $phase      = null;   /*!< orbit phase */
    public $meanmo     = null;   /*!< mean motion kept in rev/day */
    public $orbit      = null;   /*!< orbit number */
    public $otype      = null;   /*!< orbit type. */

    public function __construct(Predict_TLE $tle)
    {
        $headerParts    = explode(' ', $tle->header);
        $this->name     = $headerParts[0];
        $this->nickname = $this->name;

        $this->select_ephemeris();
    }

    /* Selects the apropriate ephemeris type to be used */
    /* for predictions according to the data in the TLE */
    /* It also processes values in the tle set so that  */
    /* they are apropriate for the sgp4/sdp4 routines   */
    public function select_ephemeris()
    {
        /* Preprocess tle set */
        $this->tle->xnodeo *= Predict::de2ra;
        $this->tle->omegao *= Predict::de2ra;
        $this->tle->xmo    *= Predict::de2ra;
        $this->tle->xincl  *= Predict::de2ra;
        $temp = Predict::twopi / Predict::xmnpda / Predict::xmnpda;

        /* store mean motion before conversion */
        $this->meanmo       = $this->tle->xno;
        $this->tle->xno     = $this->tle->xno * $temp * Predict::xmnpda;
        $this->tle->xndt2o *= $temp;
        $this->tle->xndd6o  = $this->tle->xndd6o * $temp / Predict::xmnpda;
        $this->tle->bstar  /= Predict::ae;

        /* Period > 225 minutes is deep space */
        $dd1 = Predict::xke / $this->tle->xno;
        $dd2 = Predict::tothrd;
        $a1 = pow($dd1, $dd2);
        $r1 = cos($this->tle->xincl);
        $dd1 = 1.0 - $this->tle.eo * $this->tle->eo;
        $temp = Predict::ck2 * 1.5 * ($r1 * $r1 * 3.0 - 1.0) / pow($dd1, 1.5);
        $del1 = $temp / ($a1 * $a1);
        $ao = $a1 * (1.0 - $del1 * (Predict::tothrd * 0.5 + $del1 *
                                 ($del1 * 1.654320987654321 + 1.0)));
        $delo = $temp / ($ao * $ao);
        $xnodp = $this->tle->xno / ($delo + 1.0);

        /* Select a deep-space/near-earth ephemeris */
        if (twopi/xnodp/xmnpda >= .15625) {
            $this->flags |= Predict_SGP::DEEP_SPACE_EPHEM_FLAG;
        } else {
            $this->flags &= ~Predict_SGP::DEEP_SPACE_EPHEM_FLAG;
        }
    }
}
