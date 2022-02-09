<?php

/**
 * XHProf benchmarking of the Predict package
 *
 * You'll need the xhprof extension installed and the EC_XHprof
 *
 * You'll also need to point a web server to the xhprof_lib directory and pull
 * up the list.php script to list your runs.
 *
 * Run this from the root checkout, not in examples
 */

require_once 'Predict.php';
require_once 'Predict/Sat.php';
require_once 'Predict/QTH.php';
require_once 'Predict/Time.php';
require_once 'Predict/TLE.php';
require_once 'Predict/Time.php';


// Set to true to profile
$profile = true;

// Example check at the top of your application for enabling profiling
if ($profile && extension_loaded('xhprof')) {
    $xproflib = './xhprof_lib';
    include_once $xproflib . '/utils/xhprof_lib.php';
    include_once $xproflib . '/utils/xhprof_runs.php';
    xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);

    function stop_xhprof_profiling()
    {
        $xhprofData      = xhprof_disable();
        $xhprofNameSpace = 'predict';
        $xhprofRuns      = new XHProfRuns_Default();
        $xhprofRunID     = $xhprofRuns->save_run($xhprofData, $xhprofNameSpace);
    }
    register_shutdown_function('stop_xhprof_profiling');
}

$start = microtime(true);

$predict  = new Predict();
$qth      = new Predict_QTH();
$qth->lat = 37.6550;
$qth->lon = -122.4070;
$qth->alt = 0;

$tleFile = file('examples/iss.tle');
$tle     = new Predict_TLE($tleFile[0], $tleFile[1], $tleFile[2]);
$sat     = new Predict_Sat($tle);
$now     = 2459620.2339725;
// $now     = Predict_Time::get_current_daynum();

$results = $predict->get_passes($sat, $qth, $now, 10);

echo "Execution time:  " . number_format((microtime(true) - $start) * 1000, 2) . "ms\n"; exit;
