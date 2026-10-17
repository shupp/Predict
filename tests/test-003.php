<?php
/**
 * Regression test for filterVisiblePasses(): visible_max_el_az must be an
 * azimuth, even when the first visible sample of a pass is also its highest
 * visible point (e.g. the ISS emerging from Earth's shadow after culmination).
 *
 * Expected azimuths were computed independently with Skyfield 1.55 (sgp4 2.27)
 * from the same TLE (tests/test-003.tle), observer and sample times.
 *
 * Run from root checkout, not in tests.  Exits non-zero on failure.
 */

date_default_timezone_set('UTC');

require_once 'tests/Table.php';
require_once 'Predict.php';
require_once 'Predict/Sat.php';
require_once 'Predict/QTH.php';
require_once 'Predict/Time.php';
require_once 'Predict/TLE.php';

// Sydney, Australia
$lat = -33.8688;
$lon = 151.2093;

$expected = array(
    // Morning pass, ISS leaves Earth's shadow while descending from max
    array(
        'start'     => 2459623.20,
        'max_el'    => 39.65,
        'max_el_az' => 113.70,
        'direction' => 'ESE',
    ),
    // Evening pass, sky darkens past the twilight threshold mid-pass
    array(
        'start'     => 2459626.86,
        'max_el'    => 27.74,
        'max_el_az' => 176.59,
        'direction' => 'S',
    ),
);

$headers = array(
    'visible tca (UTC)',
    'label',
    'result',
    'expected',
    'div %',
    'ok',
);

$data     = array();
$failures = 0;

$file    = file('tests/test-003.tle');
$predict = new Predict();
$qth     = new Predict_QTH();
$qth->alt = 0;
$qth->lat = $lat;
$qth->lon = $lon;

foreach ($expected as $e) {
    $tle    = new Predict_TLE($file[0], $file[1], $file[2]);
    $sat    = new Predict_Sat($tle);
    $passes = $predict->filterVisiblePasses(
        $predict->get_passes($sat, $qth, $e['start'], 0.05)
    );

    if (count($passes) == 0) {
        $failures++;
        $data[] = array($e['start'], 'no visible pass', '', '', 'n/a', 'FAIL');
        continue;
    }
    $pass = $passes[0];
    $time = Predict_Time::daynum2readable($pass->visible_tca, 'UTC', 'Y-m-d H:i:s');
    $dir  = $predict->azDegreesToDirection($pass->visible_max_el_az);

    $checks = array(
        array('max el',    round($pass->visible_max_el, 2),    $e['max_el'],
            abs($pass->visible_max_el - $e['max_el']) < 1.0,
            number_format(100.0 * abs($pass->visible_max_el - $e['max_el']) / abs($e['max_el']), 5) . '%'),
        array('max el az', round($pass->visible_max_el_az, 2), $e['max_el_az'],
            abs($pass->visible_max_el_az - $e['max_el_az']) < 1.0,
            number_format(100.0 * abs($pass->visible_max_el_az - $e['max_el_az']) / abs($e['max_el_az']), 5) . '%'),
        // direction is a compass label, not a scalar -> percentage divergence is not applicable
        array('direction', $dir, $e['direction'], $dir === $e['direction'], 'n/a'),
    );
    foreach ($checks as $i => $c) {
        if (!$c[3]) {
            $failures++;
        }
        $data[] = array($i == 0 ? $time : '', $c[0], $c[1], $c[2], $c[4], $c[3] ? 'ok' : 'FAIL');
    }
}

$tbl = new Console_Table();
$tbl->setHeaders($headers);
$tbl->addData($data);

echo $tbl->getTable();

echo ($failures ? "FAIL: $failures check(s) failed" : "PASS") . "\n";
exit($failures ? 1 : 0);
