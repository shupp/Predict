<?php

/**
 *  An example for looking up the current solar position in the sky from a given
 * location (elevation/azimuth)
 */


require_once 'Predict/Solar.php';
require_once 'Predict/QTH.php';

// Use current time in the form of a daynum
$time   = time();
$daynum = Predict_Time::unix2daynum($time);

// Set up the observer position on the ground
$qth = new Predict_QTH();
$qth->lat = 37.786759;
$qth->lon = -122.405162;
$qth->alt = 10; // Altitude above sea level in meters

$sunInfo = Predict_Solar::FindSun($qth, $daynum);

$output = array(
    'elevation' => $sunInfo->el,
    'azimuth'   => $sunInfo->az,
    'timestamp' => $time
);

// output results
echo json_encode($output);
