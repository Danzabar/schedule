<?php require __DIR__ . '/vendor/autoload.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$schedule = new Danzabar\Schedule\Schedule('Ultra-learn');

$schedule->setExcludes([
    'Weekday' => ['08:00' => '16:00']
], 'Work');

$schedule->setExcludes([
    'Weekday' => ['00:00' => '07:00', '23:00' => '24:00'],
    'Weekend' => ['00:00' => '09:00', '23:00' => '24:00']
], 'Sleep');

$schedule->addActivity('Coding', 23);
$schedule->addActivity('Learning', 5);
$schedule->addActivity('Reading', 25);
$schedule->addActivity('Gaming', 3);

echo '<pre>';
$schedule->build();
echo '</pre>';
