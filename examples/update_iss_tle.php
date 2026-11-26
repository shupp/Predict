<?php

$filename = __DIR__ . '/iss.tle';
$tempFilename = __DIR__ . '/new_iss.tle';

error_reporting(E_ALL);

$url = 'http://celestrak.com/NORAD/elements/gp.php?CATNR=25544&FORMAT=TLE';
$contentsOriginal = file_get_contents($url) or die('Could not updated tle get file');
file_put_contents($tempFilename, $contentsOriginal);
$contents = file($tempFilename);

$newFile = implode("\n", array(trim($contents[0]), trim($contents[1]), $contents[2]));
file_put_contents($filename, $newFile) or die('could not write');
unlink($tempFilename);
