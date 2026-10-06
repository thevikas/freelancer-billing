<?php

require __DIR__ . "/vendor/autoload.php";

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$TIMELOG_GITREPO = $_ENV['TIMELOG_GITREPO'];

$jsonfile = $TIMELOG_GITREPO . "/cache/current.json";
$month = $argv[1] ?? 'this_month';
if (!in_array($month, ['this_month', 'last_month'], true)) {
    fwrite(STDERR, "Usage: php gtr2.php [this_month|last_month]\n");
    exit(1);
}
if ($month === 'last_month') {
    date_default_timezone_set($_ENV['TIMEZONE'] ?? 'Asia/Kolkata');
    $firstDay = strtotime('-1 month', strtotime(date('Y-m-01')));
    $jsonfile = $TIMELOG_GITREPO . '/cache/' . date('Y-m-d', $firstDay) . '.json';
}
$json = json_decode(file_get_contents($jsonfile), true);
$BillableProjects = $json['summary']['BillableProjects'];

$sorted = require_once __DIR__ . "/gtr2.projects.php";

$totalEstMonthHours = 0;
$totalOptimalHours = 160;

printf("%8s  %s\n", 'Hours', 'Project / subproject');
echo str_repeat('-', 40) . "\n";

foreach ($sorted as $code) {
    //echo "Project: $code\t\t\t";
    if(empty($BillableProjects[$code]))
        continue;

    $project = $BillableProjects[$code];
    
    $EstimatedTotalHours = $month === 'last_month'
        ? $project['stats']['Total']
        : $project['stats']['EstimatedTotalHours'];

    $subprojects = [];
    $mainMinutes = 0;
    foreach ($project['times']['Tasks'] ?? [] as $task => $duration) {
        [$hours, $minutes] = explode(':', $duration, 2);
        $taskMinutes = (int) $hours * 60 + (int) $minutes;
        $parts = explode(':', $task, 2);
        $subproject = trim($parts[0]);
        if (count($parts) == 2 && $subproject !== '') {
            $subprojects[$subproject] = ($subprojects[$subproject] ?? 0) + $taskMinutes;
        } else {
            $mainMinutes += $taskMinutes;
        }
    }

    if ($subprojects) {
        // Use the same hours basis as the parent project's total.
        $totalMinutes = array_sum($subprojects) + $mainMinutes;
        $estimatedHoursPerMinute = $totalMinutes ? $EstimatedTotalHours / $totalMinutes : 0;
        foreach ($subprojects as $subproject => $minutes) {
            printf("%8.0f    %s\n", $minutes * $estimatedHoursPerMinute, $subproject);
        }
        if ($mainMinutes > 0) {
            printf("%8.0f    %s (no subproject)\n", $mainMinutes * $estimatedHoursPerMinute, $code);
        }
    }

    printf("%8.0f  %s%s\n\n", $EstimatedTotalHours, $code, $subprojects ? ' (total)' : '');
    $totalEstMonthHours += $EstimatedTotalHours;
}

echo str_repeat('-', 40) . "\n";
$totalLabel = $month === 'last_month' ? 'Total Hours' : 'Total Est Hours';
printf("%s: %.0f\n", $totalLabel, $totalEstMonthHours);
$efficiency = ($totalEstMonthHours / $totalOptimalHours) * 100;
printf("Efficiency: %.2f%%\n", $efficiency);
