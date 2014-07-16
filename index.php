<?php require __DIR__ . '/vendor/autoload.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);


$schedule = new Danzabar\Schedule\Schedule('Ultra-learn');

$schedule->setWorkHours([
    'Weekday' => ['07:00' => '18:00']
]);

$schedule->setExcludes([
    'Weekday' => ['00:00' => '07:00', '22:00' => '00:00'],
    'Weekend' => ['00:00' => '10:00', '23:00' => '00:00']
]);

$schedule->addActivity('Coding', 12);
$schedule->addActivity('Gaming', 5);
$schedule->addActivity('Learning', 5);
$schedule->addActivity('Break', 10);
$schedule->addActivity('Go',  5);

echo '<pre>';
$schedule->build();
echo '</pre>';
