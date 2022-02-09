<?php
/**
 * This is an example of how to use Predict for determining upcoming visible
 * passes of the International Space Station.  Please read the inline comments
 * for details.
 *
 * Run this from the root checkout, not from examples, or the include paths
 * will not work.
 */

date_default_timezone_set('America/Los_Angeles');

require_once 'Predict.php';
require_once 'Predict/Sat.php';
require_once 'Predict/QTH.php';
require_once 'Predict/Time.php';
require_once 'Predict/TLE.php';

// Track execution time of this script
$start = microtime(true);

// The observer or groundstation is called QTH in ham radio terms
$predict  = new Predict();
$qth      = new Predict_QTH();
$qth->alt = 0; // Altitude in meters

// South San Francisco, example west of the meridian
$qth->lat = 37.6550;   // Latitude North
$qth->lon = -122.4070; // Longitude East

// Munich, example east of the meridian
// $qth->lat = 48.1505; // Lat North
// $qth->lon = 11.5809; // Lon East

// The iss.tle file is the first 3 lines of
// http://celestrak.com/NORAD/elements/stations.txt
// Make sure you update this content, it goes out of date within a day or two
$tleFile = file('examples/iss.tle'); // Load up the ISS data file from NORAD
$tle     = new Predict_TLE($tleFile[0], $tleFile[1], $tleFile[2]); // Instantiate it
$sat     = new Predict_Sat($tle); // Load up the satellite data
$now     = 2459620.2339725;
// $now     = Predict_Time::get_current_daynum(); // get the current time as Julian Date (daynum)

// You can modify some preferences in Predict(), the defaults are below
//
// $predict->minEle     = 10; // Minimum elevation for a pass
// $predict->timeRes    = 10; // Pass details: time resolution in seconds
// $predict->numEntries = 20; // Pass details: number of entries per pass
// $predict->threshold  = -6; // Twilight threshold (sun must be at this lat or lower)

// Get the passes and filter visible only, takes about 4 seconds for 10 days
$results  = $predict->get_passes($sat, $qth, $now, 10);
$filtered = $predict->filterVisiblePasses($results);

$zone   = 'America/Los_Angeles'; // Pacific time zone
$format = 'm-d-Y H:i:s';         // Time format from PHP's date() function

// Format the output similar to the heavens-above.com website
foreach ($filtered as $pass) {
    echo "AOS Daynum: " . $pass->visible_aos . "\n";
    echo "AOS Time: " . Predict_Time::daynum2readable($pass->visible_aos, $zone, $format) . "\n";
    echo "AOS Az: " . $predict->azDegreesToDirection($pass->visible_aos_az) . "\n";
    echo "AOS El: " . round($pass->visible_aos_el) . "\n";
    echo "Max Time: " . Predict_Time::daynum2readable($pass->visible_tca, $zone, $format) . "\n";
    echo "Max Az: " . $predict->azDegreesToDirection($pass->visible_max_el_az) . "\n";
    echo "Max El: " . round($pass->visible_max_el) . "\n";
    echo "LOS Time: " . Predict_Time::daynum2readable($pass->visible_los, $zone, $format) . "\n";
    echo "LOS Az: " . $predict->azDegreesToDirection($pass->visible_los_az) . "\n";
    echo "LOS El: " . round($pass->visible_los_el) . "\n";
    echo "Magnitude: " . number_format($pass->max_apparent_magnitude, 1) . "\n";
    echo "\n";
}

// How long did this take?
echo "Execution time:  " . number_format((microtime(true) - $start) * 1000, 2) . "ms\n"; exit;
