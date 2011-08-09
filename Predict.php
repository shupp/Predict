<?php

require_once 'Predict/Time.php';
require_once 'Predict/Pass.php';
require_once 'Predict/PassDetail.php';
require_once 'Predict/Vector.php';
require_once 'Predict/Geodetic.php';
require_once 'Predict/ObsSet.php';
require_once 'Predict/Solar.php';
require_once 'Predict/SGPObs.php';

/**
 * Predict
 *
 * A limited PHP port of the gpredict program.  Port by Bill Shupp.
 *
 * @license   GPL 2
 */
class Predict
{
    const de2ra    =  1.74532925E-2;   /* Degrees to Radians */
    const pi       =  3.1415926535898; /* Pi */
    const pio2     =  1.5707963267949; /* Pi/2 */
    const x3pio2   =  4.71238898;      /* 3*Pi/2 */
    const twopi    =  6.2831853071796; /* 2*Pi  */
    const e6a      =  1.0E-6;
    const tothrd   =  6.6666667E-1;    /* 2/3 */
    const xj2      =  1.0826158E-3;    /* J2 Harmonic */
    const xj3      = -2.53881E-6;      /* J3 Harmonic */
    const xj4      = -1.65597E-6;      /* J4 Harmonic */
    const xke      =  7.43669161E-2;
    const xkmper   =  6.378135E3;      /* Earth radius km */
    const xmnpda   =  1.44E3;          /* Minutes per day */
    const ae       =  1.0;
    const ck2      =  5.413079E-4;
    const ck4      =  6.209887E-7;
    const __f      =  3.352779E-3;
    const ge       =  3.986008E5;
    const __s__    =  1.012229;
    const qoms2t   =  1.880279E-09;
    const secday   =  8.6400E4;        /* Seconds per day */
    const omega_E  =  1.0027379;
    const omega_ER =  6.3003879;
    const zns      =  1.19459E-5;
    const c1ss     =  2.9864797E-6;
    const zes      =  1.675E-2;
    const znl      =  1.5835218E-4;
    const c1l      =  4.7968065E-7;
    const zel      =  5.490E-2;
    const zcosis   =  9.1744867E-1;
    const zsinis   =  3.9785416E-1;
    const zsings   = -9.8088458E-1;
    const zcosgs   =  1.945905E-1;
    const zcoshs   =  1;
    const zsinhs   =  0;
    const q22      =  1.7891679E-6;
    const q31      =  2.1460748E-6;
    const q33      =  2.2123015E-7;
    const g22      =  5.7686396;
    const g32      =  9.5240898E-1;
    const g44      =  1.8014998;
    const g52      =  1.0508330;
    const g54      =  4.4108898;
    const root22   =  1.7891679E-6;
    const root32   =  3.7393792E-7;
    const root44   =  7.3636953E-9;
    const root52   =  1.1428639E-7;
    const root54   =  2.1765803E-9;
    const thdt     =  4.3752691E-3;
    const rho      =  1.5696615E-1;
    const mfactor  =  7.292115E-5;
    const __sr__   =  6.96000E5;      /*Solar radius - kilometers (IAU 76)*/
    const AU       =  1.49597870E8;   /*Astronomical unit - kilometers (IAU 76)*/

    /* visibility constants */
    const SAT_VIS_NONE     = 0;
    const SAT_VIS_VISIBLE  = 1;
    const SAT_VIS_DAYLIGHT = 2;
    const SAT_VIS_ECLIPSED = 3;

    /* preferences */
    public $minEle     = 5;  // Minimum elevation
    public $timeRes    = 10; // Pass details: time resolution
    public $numEntries = 20; // Pass details: number of entries
    public $threshold  = -6; // Twilight threshold

    /**
     *  Predict the next pass.
     *
     * This function simply wraps the get_pass function using the current time
     * as parameter.
     *
     * Note: the data in sat will be corrupt (future) and must be refreshed
     *       by the caller, if the caller will need it later on (eg. if the caller
     *       is GtkSatList).
     *
     * @param Predict_Sat $sat   The satellite data.
     * @param Predict_QTH $qth   The observer data.
     * @param int         $maxdt The maximum number of days to look ahead.
     *
     * @return Predict_Pass Pointer instance or NULL if no pass can be
     *         found.
     */
    public function get_next_pass(Predict_Sat $sat, Predict_QTH $qth, $maxdt)
    {
        /* get the current time and call the get_pass function */
        $now = Predict_Time::get_current_daynum();

        return $this->get_pass($sat, $qth, $now, $maxdt);
    }

    /** \brief Predict first pass after a certain time.
     *  \param sat Pointer to the satellite data.
     *  \param qth Pointer to the location data.
     *  \param start Starting time.
     *  \param maxdt The maximum number of days to look ahead (0 for no limit).
     *  \return Pointer to a newly allocated pass_t structure or NULL if
     *          there was an error.
     *
     * This function will find the first upcoming pass with AOS no earlier than
     * t = start and no later than t = (start+maxdt).
     *
     * \note For no time limit use maxdt = 0.0
     *
     * \note the data in sat will be corrupt (future) and must be refreshed
     *       by the caller, if the caller will need it later on (eg. if the caller
     *       is GtkSatList).
     *
     * \note Prepending to a singly linked list is much faster than appending.
     *       Therefore, the elements are prepended whereafter the GSList is
     *       reversed
     */
    public function get_pass(Predict_Sat $sat_in, Predict_QTH $qth, $start, $maxdt)
    {
        $aos = 0.0;    /* time of AOS */
        $tca = 0.0;    /* time of TCA */
        $los = 0.0;    /* time of LOS */
        $dt = 0.0;     /* time diff */
        $step = 0.0;   /* time step */
        $t0 = $start;
        $t;            /* current time counter */
        $tres = 0.0;   /* required time resolution */
        $max_el = 0.0; /* maximum elevation */
        $pass = null;
        $detail = null;
        $done = false;
        $iter = 0;      /* number of iterations */
        $sat;
        $sat_working;
        /* FIXME: watchdog */

        /*copy sat_in to a working structure*/
        $sat         = clone $sat_in;
        $sat_working = clone $sat_in;

        /* get time resolution; sat-cfg stores it in seconds */
        $tres = $this->timeRes / 86400.0;

        /* loop until we find a pass with elevation > SAT_CFG_INT_PRED_MIN_EL
            or we run out of time
            FIXME: we should have a safety break
        */
        while (!$done) {
            /* Find los of next pass or of current pass */
            $los = $this->find_los($sat, $qth, $t0, $maxdt); // See if a pass is ongoing
            $aos = $this->find_aos($sat, $qth, $t0, $maxdt);
            /* sat_log_log(SAT_LOG_LEVEL_MSG, "%s:%s:%d: found aos %f and los %f for t0=%f", */
            /*          __FILE__,  */
            /*          __FUNCTION__, */
            /*          __LINE__, */
            /*          aos, */
            /*          los,  */
            /*          t0); */
            if ($aos > $los) {
                // los is from an currently happening pass, find previous aos
                $aos = $this->find_prev_aos($sat, $qth, $t0);
            }

            /* aos = 0.0 means no aos */
            if ($aos == 0.0) {
                $done = true;
            } else if (($maxdt > 0.0) && ($aos > ($start + $maxdt)) ) {
                /* check whether we are within time limits;
                    maxdt = 0 mean no time limit.
                */
                $done = true;

            } else {
                //los = find_los (sat, qth, aos + 0.001, maxdt); // +1.5 min later
                $dt = $los - $aos;

                /* get time step, which will give us the max number of entries */
                $step = $dt / $this->numEntries;

                /* but if this is smaller than the required resolution
                    we go with the resolution
                */
                if ($step < $tres) {
                    $step = $tres;
                }

                /* create a pass_t entry; FIXME: g_try_new in 2.8 */
                $pass = new Predict_Pass();

                $pass->aos      = $aos;
                $pass->los      = $los;
                $pass->max_el   = 0.0;
                $pass->aos_az   = 0.0;
                $pass->los_az   = 0.0;
                $pass->maxel_az = 0.0;
                $pass->vis      = '---';
                $pass->satname  = $sat->nickname;
                $pass->details  = array();

                /* iterate over each time step */
                for ($t = $pass->aos; $t <= $pass->los; $t += $step) {

                    /* calculate satellite data */
                    $this->predict_calc($sat, $qth, $t);

                    /* in the first iter we want to store
                        pass->aos_az
                    */
                    if ($t == $pass->aos) {
                        $pass->aos_az = $sat->az;
                        $pass->orbit  = $sat->orbit;
                    }

                    /* append details to sat->details */
                    $detail             = new Predict_PassDetail();
                    $detail->time       = $t;
                    $detail->pos->x     = $sat->pos->x;
                    $detail->pos->y     = $sat->pos->y;
                    $detail->pos->z     = $sat->pos->z;
                    $detail->pos->w     = $sat->pos->w;
                    $detail->vel->x     = $sat->vel->x;
                    $detail->vel->y     = $sat->vel->y;
                    $detail->vel->z     = $sat->vel->z;
                    $detail->vel->w     = $sat->vel->w;
                    $detail->velo       = $sat->velo;
                    $detail->az         = $sat->az;
                    $detail->el         = $sat->el;
                    $detail->range      = $sat->range;
                    $detail->range_rate = $sat->range_rate;
                    $detail->lat        = $sat->ssplat;
                    $detail->lon        = $sat->ssplon;
                    $detail->alt        = $sat->alt;
                    $detail->ma         = $sat->ma;
                    $detail->phase      = $sat->phase;
                    $detail->footprint  = $sat->footprint;
                    $detail->orbit      = $sat->orbit;
                    $detail->vis        = $this->get_sat_vis($sat, $qth, $t);

                    /* also store visibility "bit" */
                    switch ($detail->vis) {
                        case self::SAT_VIS_VISIBLE:
                            $pass->vis[0] = 'V';
                            break;
                        case self::SAT_VIS_DAYLIGHT:
                            $pass->vis[1] = 'D';
                            break;
                        case self::SAT_VIS_ECLIPSED:
                            $pass->vis[2] = 'E';
                            break;
                        default:
                            break;
                    }

                    // Using an array, no need to prepend and reverse the list
                    // as gpredict does
                    $pass->details[] = $detail;

                    /* store elevation if greater than the
                        previously stored one
                    */
                    if ($sat->el > $max_el) {
                        $max_el         = $sat->el;
                        $tca            = $t;
                        $pass->maxel_az = $sat->az;
                    }

                    /*     g_print ("TIME: %f\tAZ: %f\tEL: %f (MAX: %f)\n", */
                    /*           t, sat->az, sat->el, max_el); */
                }

                /* calculate satellite data */
                $this->predict_calc($sat, $qth, $pass->los);
                /* store los_az, max_el and tca */
                $pass->los_az = $sat->az;
                $pass->max_el = $max_el;
                $pass->tca    = $tca;

                /* check whether this pass is good */
                if ($max_el >= $this->minEle) {
                    $done = true;
                } else {
                    $done = false;
                    $t0 = $los + 0.014; // +20 min
                    $pass = null;
                }

                $iter++;
            }
        }

        return $pass;
    }

    /**
     * Calculate satellite visibility.
     *
     * @param Predict_Sat $sat The satellite structure.
     * @param Predict_QTH $qth The QTH
     * @param float $jul_utc The time at which the visibility should be calculated.
     *
     * @return void The visiblity code.
     */
    public function get_sat_vis(Predict_Sat $sat, Predict_QTH $qth, $jul_utc)
    {
        /* gboolean sat_sun_status;
        gdouble  sun_el;
        gdouble  threshold;
        gdouble  eclipse_depth;
        sat_vis_t vis = SAT_VIS_NONE; */

        $eclipse_depth  = 0.0;
        $zero_vector    = new Predict_Vector();
        $obs_geodetic   = new Predict_Geodetic();

        /* Solar ECI position vector  */
        $solar_vector = new Predict_Vector();

        /* Solar observed az and el vector  */
        $solar_set = new Predict_ObsSet();

        /* FIXME: could be passed as parameter */
        $obs_geodetic->lon   = $qth->lon * self::de2ra;
        $obs_geodetic->lat   = $qth->lat * self::de2ra;
        $obs_geodetic->alt   = $qth->alt / 1000.0;
        $obs_geodetic->theta = 0;

        Predict_Solar::Calculate_Solar_Position($jul_utc, $solar_vector);
        Predict_SGPObs::Calculate_Obs($jul_utc, $solar_vector, $zero_vector, $obs_geodetic, $solar_set);

        if (Predict_Solar::Sat_Eclipsed($sat->pos, $solar_vector, $eclipse_depth)) {
            /* satellite is eclipsed */
            $sat_sun_status = false;
        } else {
            /* satellite in sunlight => may be visible */
            $sat_sun_status = true;
        }

        if ($sat_sun_status) {
            $sun_el = Predict_Math::Degrees($solar_set->el);

            if ($sun_el <= $this->threshold && $sat->el >= 0.0) {
                $vis = self::SAT_VIS_VISIBLE;
            } else {
                $vis = self::SAT_VIS_DAYLIGHT;
            }
        } else {
            $vis = self::SAT_VIS_ECLIPSED;
        }

        return $vis;
    }
}
