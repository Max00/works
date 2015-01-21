$('document').ready(function(){
	$('p#daysLabelSingle').hide();
	$('p#hoursLabelSingle').hide();
	$('p#minutesLabelSingle').hide();
	$('p#secondsLabelSingle').hide();
	function gt(){
		$.ajax({
			type: "POST",
			url: 'http://mkif.fr/wp-content/themes/twentyfifteen-child/ajax-get-time.php',
			
			success: function(response) {
				date = $.parseJSON(response);
				$('p#days').text(date.days);
				if(date.days == 1) {
					$('p#daysLabel').hide();
					$('p#daysLabelSingle').show();
				}
				else {
					$('p#daysLabel').show();
					$('p#daysLabelSingle').hide();
				}
				$('p#hours').text(date.hours);
				if(date.hours == 1) {
					$('p#hoursLabel').hide();
					$('p#hoursLabelSingle').show();
				}
				else {
					$('p#hoursLabel').show();
					$('p#hoursLabelSingle').hide();
				}
				$('p#minutes').text(date.minutes);
				if(date.minutes == 1) {
					$('p#minutesLabel').hide();
					$('p#minutesLabelSingle').show();
				}
				else {
					$('p#minutesLabel').show();
					$('p#minutesLabelSingle').hide();
				}
				$('p#seconds').text(date.seconds);
				if(date.seconds == 1) {
					$('p#secondsLabel').hide();
					$('p#secondsLabelSingle').show();
				}
				else {
					$('p#secondsLabel').show();
					$('p#secondsLabelSingle').hide();
				}
			}
		})
		$.ajax({
			type: "POST",
			url: 'http://mkif.fr/wp-content/themes/twentyfifteen-child/ajax-get-comp.php',
			success: function(response) {
			console.log(response);
				$pic = $.parseJSON(response);
				$('div#compText').text($compText);
				$('img#compPic').prop('src', 'http://mkif.fr/wp-content/themes/twentyfifteen-child/img/'+$compPic);
			},
			error: function(response) {
				console.log(response);
			}
		})
	}
	gt();
	//setInterval(gt, 1000);
});