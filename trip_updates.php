<?php
/**
* File to parse GTFS-RT data with trip updates
* @author Serkan Yildiz
* @license MIT
*/

require_once 'vendor/autoload.php';
$config = require_once 'config.php';

use transit_realtime\FeedMessage;

// set timezone to your zone (settings in config.php)
date_default_timezone_set($config['timezone']);

$data = file_get_contents($config['trip_updates_url']);

$feed = new FeedMessage();
$feed->parse($data);

$entity_list = $feed->getEntityList();

foreach($entity_list as $entity)
{
	$trip_update = $entity->getTripUpdate();
	$delay_time = $trip_update->delay; // in seconds

	$trip_id = $trip_update->trip->trip_id;
	$vehicle = $trip_update->getVehicle();
	$vehicle_id = $vehicle->id;
	$vehicle_label = $vehicle->label;
}
