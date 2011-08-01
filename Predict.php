<?php

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
}
