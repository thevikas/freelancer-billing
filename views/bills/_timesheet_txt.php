<?php
/** @var array $tasks */

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableCellStyle;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

if(empty($tasks)) {
    return;
}

$this->title = 'Task Report';

$headers = ['Module','Task','Time (HH:MM)'];

$rows = [];
$output = new BufferedOutput();

array_walk($tasks, function(&$task) {
    $task[2] = new TableCell($task[2], ['style' => new TableCellStyle(['align' => 'right'])]);
});

$total = array_pop($tasks);
$tasks = array_splice($tasks, 1);
$rows = array_merge($tasks, [
    new TableSeparator(),
    $total
]);

$table = new Table($output);
$table->setHeaderTitle($this->title);
$table->setStyle('box-double');
$table
    ->setHeaders($headers)
    ->setRows($rows)
;
$table->render();
echo $output->fetch();
