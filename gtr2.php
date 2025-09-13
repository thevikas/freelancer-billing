#/opt/local/bin/php
<?php

require __DIR__ . "/vendor/autoload.php";

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$TIMELOG_GITREPO = $_ENV['TIMELOG_GITREPO'];

$jsonfile = $TIMELOG_GITREPO . "/cache/current.json";
$json = json_decode(file_get_contents($jsonfile), true);
$BillableProjects = $json['summary']['BillableProjects'];

$sorted = require_once __DIR__ . "/gtr2.projects.php";

$totalEstMonthHours = 0;
$totalOptimalHours = 160;

foreach ($sorted as $code) {
    //echo "Project: $code\t\t\t";
    if(empty($BillableProjects[$code]))
        continue;

    $project = $BillableProjects[$code];
    
    $EstimatedTotalHours = $project['stats']['EstimatedTotalHours'];

    echo "$EstimatedTotalHours\t$code\n";
    $totalEstMonthHours += $EstimatedTotalHours;
}

echo "---------------------\n";
echo "Total Est Hours: $totalEstMonthHours\n";
$efficiency = ($totalEstMonthHours / $totalOptimalHours) * 100;
echo "Efficiency: " . round($efficiency, 2) . "%\n";


