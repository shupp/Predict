<?php
/**
 * Run from root checkout, not in tests
 */

require_once 'tests/Table.php';
require_once 'Predict/TLE.php';
require_once 'Predict/Sat.php';
require_once 'Predict/SGPSDP.php';
require_once 'Predict/Math.php';

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
);

$data = array();


$file = file('tests/test-001.tle');
$tle  = new Predict_TLE($file[0], $file[1], $file[2]);
$sat  = new Predict_Sat($tle);
$sgpsdp  = new Predict_SGPSDP();

$count = 0;
foreach ($expected as $e) {
    $sgpsdp->SGP4($sat, $e['step']);
    Predict_Math::Convert_Sat_State($sat->pos, $sat->vel);

    $count++;
    $data[$count]   = array();
    $data[$count][0] = $e['step'];
    $data[$count][1] = 'X';
    $data[$count][2] = $sat->pos->x;
    $data[$count][3] = $e['x'];

    $count++;
    $data[$count]   = array();
    $data[$count][0] = '';
    $data[$count][1] = 'Y';
    $data[$count][2] = $sat->pos->y;
    $data[$count][3] = $e['y'];

    $count++;
    $data[$count]   = array();
    $data[$count][0] = '';
    $data[$count][1] = 'Z';
    $data[$count][2] = $sat->pos->z;
    $data[$count][3] = $e['z'];

    $count++;
    $data[$count]   = array();
    $data[$count][0] = '';
    $data[$count][1] = 'VX';
    $data[$count][2] = $sat->vel->x;
    $data[$count][3] = $e['vx'];

    $count++;
    $data[$count]   = array();
    $data[$count][0] = '';
    $data[$count][1] = 'VY';
    $data[$count][2] = $sat->vel->y;
    $data[$count][3] = $e['vy'];

    $count++;
    $data[$count]   = array();
    $data[$count][0] = '';
    $data[$count][1] = 'VZ';
    $data[$count][2] = $sat->vel->z;
    $data[$count][3] = $e['vz'];
}
// exit;

$tbl = new Console_Table();
$tbl->setHeaders($headers);
$tbl->addData($data);

echo "DEEP_SPACE_EPHEM: " . ($sat->flags & Predict_SGPSDP::DEEP_SPACE_EPHEM_FLAG) . " (expected: 0)\n\n";

echo $tbl->getTable();

echo "Execution time: " . number_format((microtime(true) - $start) * 1000, 2) . "ms\n";
