<?php
/**
 * SDP4 (deep-space) state-vector test.
 *
 * Propagates the test-002 TLE at five step times and compares the resulting
 * position (km) and velocity (km/s) components against the committed reference
 * vectors.  The table's "ok" column shows whether each component matches within
 * tolerance; the script exits non-zero if any component is out of tolerance.
 *
 * Tolerance: abs(diff) <= max(1e-3, 1e-4 * abs(expected))  (a small absolute
 * floor plus a 0.01% relative band), matching test-001.
 *
 * Run from root checkout, not in tests.
 */

require_once 'tests/Table.php';
require_once 'Predict/TLE.php';
require_once 'Predict/Sat.php';
require_once 'Predict/SGPSDP.php';
require_once 'Predict/Math.php';

/**
 * @param float $actual
 * @param float $expected
 * @return bool
 */
function within_tol($actual, $expected)
{
    $tol = max(1e-3, 1e-4 * abs($expected));
    return abs($actual - $expected) <= $tol;
}

/**
 * Percentage divergence of actual from expected.  Returns 'n/a' when a
 * percentage is not meaningful (expected is exactly zero).
 *
 * @param float $actual
 * @param float $expected
 * @return string
 */
function pct_div($actual, $expected)
{
    if ($expected == 0.0) {
        return 'n/a';
    }
    return number_format(100.0 * abs($actual - $expected) / abs($expected), 5) . '%';
}

$start = microtime(true);

$expected = array(
    array(
        'step' => 0.0,
        'x'    => 7473.37066650,
        'y'    => 428.95261765,
        'z'    => 5828.74786377,
        'vx'   => 5.1071513,
        'vy'   => 6.44468284,
        'vz'   => -0.18613096
    ),
    array(
        'step' => 360.0,
        'x'    => -3305.22537232,
        'y'    => 32410.86328125,
        'z'    => -24697.17675781,
        'vx'   => -1.30113538,
        'vy'   => -1.15131518,
        'vz'   => -0.28333528
    ),
    array(
        'step' => 720.0,
        'x'    => 14271.28759766,
        'y'    => 24110.46411133,
        'z'    => -4725.76837158,
        'vx'   => -0.32050445,
        'vy'   => 2.67984074,
        'vz'   => -2.08405289
    ),
    array(
        'step' => 1080.0,
        'x'    => -9990.05883789,
        'y'    => 22717.35522461,
        'z'    => -23616.890662501,
        'vx'   => -1.01667246,
        'vy'   => -2.29026759,
        'vz'   => 0.72892364
    ),
    array(
        'step' => 1440.0,
        'x'    => 9787.86975097,
        'y'    => 33753.34667969,
        'z'    => -15030.81176758,
        'vx'   => -1.09425966,
        'vy'   => 0.92358845,
        'vz'   => -1.52230928
    )
);

$headers = array(
    'step time',
    'label',
    'result',
    'expected',
    'div %',
    'ok',
);

$data     = array();
$failures = 0;

$file = file('tests/test-002.tle');
$tle  = new Predict_TLE($file[0], $file[1], $file[2]);
$sat  = new Predict_Sat($tle);
$sgpsdp  = new Predict_SGPSDP();

foreach ($expected as $e) {
    // Deep-space object (period > 225 min): gpredict's select_ephemeris() routes
    // this to SDP4().  The PHP port has no auto-dispatch, so call SDP4() directly
    // -- calling the near-Earth SGP4() here produces wrong state vectors.
    $sgpsdp->SDP4($sat, $e['step']);
    Predict_Math::Convert_Sat_State($sat->pos, $sat->vel);

    $components = array(
        array('X',  $sat->pos->x, $e['x']),
        array('Y',  $sat->pos->y, $e['y']),
        array('Z',  $sat->pos->z, $e['z']),
        array('VX', $sat->vel->x, $e['vx']),
        array('VY', $sat->vel->y, $e['vy']),
        array('VZ', $sat->vel->z, $e['vz']),
    );

    foreach ($components as $i => $c) {
        $ok = within_tol($c[1], $c[2]);
        if (!$ok) {
            $failures++;
        }
        $data[] = array(
            $i === 0 ? $e['step'] : '',
            $c[0],
            $c[1],
            $c[2],
            pct_div($c[1], $c[2]),
            $ok ? 'ok' : 'FAIL',
        );
    }
}

$tbl = new Console_Table();
$tbl->setHeaders($headers);
$tbl->addData($data);

echo "DEEP_SPACE_EPHEM: " . ($sat->flags & Predict_SGPSDP::DEEP_SPACE_EPHEM_FLAG) . " (expected: 64)\n\n";

echo $tbl->getTable();

echo "Execution time: " . number_format((microtime(true) - $start) * 1000, 2) . "ms\n";
echo ($failures ? "FAIL: $failures check(s) failed" : "PASS") . "\n";
exit($failures ? 1 : 0);
