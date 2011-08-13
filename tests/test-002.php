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
);

$data = array();


$file = file('tests/test-002.tle');
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


echo "DEEP_SPACE_EPHEM: " . ($sat->flags & Predict_SGPSDP::DEEP_SPACE_EPHEM_FLAG) . " (expected: 64)\n\n";

echo $tbl->getTable();

echo "Execution time: " . number_format((microtime(true) - $start) * 1000, 2) . "ms\n";
