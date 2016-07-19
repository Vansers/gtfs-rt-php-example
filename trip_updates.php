<?php
require_once 'vendor/autoload.php';

use transit_realtime\FeedMessage;
date_default_timezone_set('Europe/Brussels');
const TRIP_UPDATES_FILE = 'http://gtfs.irail.be/nmbs/trip_updates.pb';
const TOTAL_TRAINS = 3670;
const LOG_FILE = '/var/www/iRail_gtfs_test/delays.log';
$data = file_get_contents(TRIP_UPDATES_FILE);
$feed = new FeedMessage();
$feed->parse($data);


echo '###########################################' . PHP_EOL;
echo '- File mod. time: ' . date('H:i:s d-m-Y', get_modification_time_url(TRIP_UPDATES_FILE)) . PHP_EOL;
echo '###########################################' . PHP_EOL . PHP_EOL;

$entity_list = $feed->getEntityList();
$counter = 1;
$total_delay_minutes = 0;
foreach($entity_list as $entity)
{
  $delay_time = $entity->getTripUpdate()->delay / 60;

  if($delay_time > 4)
  {
      echo 'Trip id: ' . $entity->getTripUpdate()->trip->trip_id . ' - ';
      $total_delay_minutes += $delay_time;
      $vehicle = $entity->getTripUpdate()->getVehicle();
      echo $vehicle->id . "\t";
      echo $vehicle->label . ': vertraging --> ' . $delay_time;

      $counter++;

      if($entity->hasAlert())
      {
        $alert = $entity->getAlert();
      }

      echo PHP_EOL;
  }
}

echo "Total trains with issues: " . $counter . PHP_EOL;
echo "Percentage of delays: " . round($counter / TOTAL_TRAINS * 100, 2);
echo PHP_EOL . PHP_EOL;

write_to_log_file($total_delay_minutes . ',' . time());


function write_to_log_file($problems_with_trains)
{
  return file_put_contents(LOG_FILE, $problems_with_trains . PHP_EOL, FILE_APPEND);
}


function get_modification_time_url($url)
{

  $curl = curl_init($url);

  //don't fetch the actual page, you only want headers
  curl_setopt($curl, CURLOPT_NOBODY, true);

  //stop it from outputting stuff to stdout
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

  // attempt to retrieve the modification date
  curl_setopt($curl, CURLOPT_FILETIME, true);

  $result = curl_exec($curl);

  if ($result === false) {
      die (curl_error($curl));
  }

  $timestamp = curl_getinfo($curl, CURLINFO_FILETIME);

  file_put_contents(dirname(__FILE__) . '/delays.log', $timestamp);
  return $timestamp;
}
