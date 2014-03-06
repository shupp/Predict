<?php
/**
 * An example of looking up the lat/lon of the sun for putting on a map.
 * "dark" lat/lon is the anti-solar point, useful for drawing a
 * "solar terminator"
 */

// No autoloading here, require needed files
require_once 'Predict/Solar.php';
require_once 'Predict/SGPObs.php';
require_once 'Predict/Math.php';
require_once 'Predict/Time.php';

// Set up data constructs
$solar_vector   = new Predict_Vector();
$solar_geodetic = new Predict_Geodetic();


// Use current time in the form of a daynum
$time   = time();
$daynum = Predict_Time::unix2daynum($time);

// Do calculations
Predict_Solar::Calculate_Solar_Position($daynum, $solar_vector);
Predict_SGPObs::Calculate_LatLonAlt($daynum, $solar_vector, $solar_geodetic);

// Format to degrees
$solar_lat = Predict_Math::Degrees($solar_geodetic->lat);
$solar_lon = Predict_Math::Degrees($solar_geodetic->lon);

// Reverse values for night circle center
$dark_lat = -$solar_lat;
$dark_lon = -$solar_lon;

$output = array(
    'solar_lat' => $solar_lat,
    'solar_lon' => $solar_lon,
    'dark_lat'  => $dark_lat,
    'dark_lon'  => $dark_lon,
    'timestamp' => $time
);

// output results
var_dump(json_encode($output));
