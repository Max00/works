<?php

/* Jour du mariage ! */
$weddingDate = '2015-08-08 14:00:00';

date_default_timezone_set('Europe/Paris');
$datetime1 = new DateTime(date('Y-m-d H:i:s'));
$datetime2 = new DateTime($weddingDate);
$interval = $datetime1->diff($datetime2);

$dateAr = array(
	'days' => $interval->format('%a'),
	'hours' => $interval->format('%H'),
	'minutes' => $interval->format('%i'),
	'seconds' => $interval->format('%S'));

echo json_encode($dateAr);
exit;