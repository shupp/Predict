<?php

// Example listing runs

require_once 'EC/XHProf.php';

$namespace = 'predict';
$xhprof    = new EC_XHProf($namespace, '/private/tmp');

if (isset($_GET['clear'])) {
    $xhprof->clear();
    header('Location: ' . $_SERVER['PHP_SELF']);
}
?>


<html>
<head>
    <link type="text/css" rel="stylesheet" href="/build.css" />
</head>
<body>
<h1>XHProf Profiler</h1>
<div class="top">
    <h3>Available XHProf Runs for <?php echo $namespace ?></h3>
</div>
<br />
<table>
    <tr>
        <td width="300">Run ID</td><td>Date</td>
    </tr>
<?php
foreach ($xhprof->getRuns() as $run => $time) {
    $url = "index.php?run={$run}&source={$namespace}";
?>
    <tr>
        <td><a href="<?php echo $url ?>"><?php echo $run ?></a></td><td><?php echo $time ?></td>
    </tr>
<?php
}
?>
</table>
<br />
<br />
<br />
<br />
<a href='list.php?clear=true'>Clear Runs</a>
</body>
</html>
