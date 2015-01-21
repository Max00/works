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

$curCompText = '';
$curCompPic = '';

if($dateAr['days'] >= 1) {
	$compAtLeastOneDay = array(
		'300' => array('text' => 'eee', 'pic' => 'ee'),
		'240' => array('text' => 'le temps qu\'il faut pour aller sur Mars', 'pic' => 'mars.png'),
		'200' => array('text' => 'Autre chose', 'pic' => 'mars2.png'),
	);

	foreach($compAtLeastOneDay as $days => $comp) {
		if($days <= $dateAr['days']) {
			$curCompText = $comp['text'];
			$curCompPic = $comp['pic'];
			break;
		}
	}
}

$result = array(
	'text' => $curCompText,
	'pic' => $curCompPic
);
exit;