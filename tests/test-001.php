<?php
/**
 * SGP4 (near-Earth) state-vector test.
 *
 * Propagates the test-001 TLE at five step times and compares the resulting
 * position (km) and velocity (km/s) components against the committed reference
 * vectors.  The table's "ok" column shows whether each component matches within
 * tolerance; the script exits non-zero if any component is out of tolerance.
 *
 * Tolerance: abs(diff) <= max(1e-3, 1e-4 * abs(expected))  (a small absolute
 * floor plus a 0.01% relative band), which the committed vectors satisfy.
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
        'x'    => 2328.97048951,
        'y'    => -5995.22076416,
        'z'    => 1719.97067261,
        'vx'   => 2.91207230,
        'vy'   => -0.98341546,
        'vz'   => -7.09081703
    ),
    array(
        'step' => 360.0,
        'x'    => 2456.10705566,
        'y'    => -6071.93853760,
        'z'    => 1222.89727783,
        'vx'   => 2.67938992,
        'vy'   => -0.44829041,
        'vz'   => -7.22879231
    ),
    array(
        'step' => 720.0,
        'x'    => 2567.56195068,
        'y'    => -6112.50384522,
        'z'    => 713.96397400,
        'vx'   => 2.44024599,
        'vy'   => 0.09810869,
        'vz'   => -7.31995916
    ),
    array(
        'step' => 1080.0,
        'x'    => 2663.09078980,
        'y'    => -6115.48229980,
        'z'    => 196.39640427,
        'vx'   => 2.19611958,
        'vy'   => 0.65241995,
        'vz'   => -7.36282432
    ),
    array(
        'step' => 1440.0,
        'x'    => 2742.55133057,
        'y'    => -6079.67144775,
        'z'    => -326.38095856,
        'vx'   => 1.94850229,
        'vy'   => 1.21106251,
        'vz'   => -7.35619372
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

$file = file('tests/test-001.tle');
$tle  = new Predict_TLE($file[0], $file[1], $file[2]);
$sat  = new Predict_Sat($tle);
$sgpsdp  = new Predict_SGPSDP();

foreach ($expected as $e) {
    $sgpsdp->SGP4($sat, $e['step']);
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

echo "DEEP_SPACE_EPHEM: " . ($sat->flags & Predict_SGPSDP::DEEP_SPACE_EPHEM_FLAG) . " (expected: 0)\n\n";

echo $tbl->getTable();

echo "Execution time: " . number_format((microtime(true) - $start) * 1000, 2) . "ms\n";
echo ($failures ? "FAIL: $failures check(s) failed" : "PASS") . "\n";
exit($failures ? 1 : 0);
