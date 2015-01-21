<?php
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html>
<head>
<title>Timer</title>
<meta charset='utf-8'>
<link rel="stylesheet" type="text/css" href="<?php echo get_bloginfo('stylesheet_url') ?>" />
<link href='http://fonts.googleapis.com/css?family=Oswald:400,700,300' rel='stylesheet' type='text/css'>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="<?php echo dirname(get_bloginfo('stylesheet_url')) ?>/site.js"></script>
</head>

<body>
<header></header>
<section id="remaining">
<p id="days" class="figure"></p>
<p id="hours" class="figure"></p>
<p id="minutes" class="figure"></p>
<p id="seconds" class="figure"></p><br>
<p id="daysLabel" class="label">Jours</p>
<p id="hoursLabel" class="label">Heures</p>
<p id="minutesLabel" class="label">Minutes</p>
<p id="secondsLabel" class="label">Secondes</p>
<p id="daysLabelSingle" class="label">Jour</p>
<p id="hoursLabelSingle" class="label">Heure</p>
<p id="minutesLabelSingle" class="label">Minute</p>
<p id="secondsLabelSingle" class="label">Seconde</p>
<aside id="comp">
<div id="equal"></div>
<div id="textComp"></div>
<img id="picComp"/>
</aside>
</section>
<footer></footer>
</body>

</html>