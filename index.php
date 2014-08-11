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

$schedule->addActivity('Coding', 20, [
    'Weekday' => ['07:00' => '08:00', '16:00' => '17:00']
]);
$schedule->addActivity('Learning', 7);
$schedule->addActivity('Reading', 13);
$schedule->addActivity('Gaming', 15);

echo '<pre>';
$builder = $schedule->build();
print_r($builder->getActivityTime());
print_r($builder->getActivityTimeByDay());
print_r($builder->getTotalTime());
echo $builder->convertIndexes()->toJSON();
echo '</pre>';
