function rgb2hex(rgb, noHashtag) {
    if (/^#[0-9A-F]{6}$/i.test(rgb))
        return rgb;

    rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
    function hex(x) {
        return ("0" + parseInt(x).toString(16)).slice(-2);
    }
    return (noHashtag != 'undefined' && noHashtag ? "" : '#') + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
}
// A static class for converting between Decimal and DMS formats for a location
// ported from: http://andrew.hedges.name/experiments/convert_lat_long/
// Decimal Degrees = Degrees + minutes/60 + seconds/3600
// more info on formats here: http://www.maptools.com/UsingLatLon/Formats.html
// use: LocationFormatter.DMSToDecimal( 45, 35, 38, LocationFormatter.SOUTH );
// or:  LocationFormatter.decimalToDMS( -45.59389 );

function LocationFormatter() {
}
;

LocationFormatter.NORTH = 'N';
LocationFormatter.SOUTH = 'S';
LocationFormatter.EAST = 'E';
LocationFormatter.WEST = 'W';

LocationFormatter.roundToDecimal = function(inputNum, numPoints) {
    var multiplier = Math.pow(10, numPoints);
    return Math.round(inputNum * multiplier) / multiplier;
};

LocationFormatter.decimalToDMS = function(location, hemisphere) {
    if (location < 0)
        location *= -1; // strip dash '-'

    var degrees = Math.floor(location);          // strip decimal remainer for degrees
    var minutesFromRemainder = (location - degrees) * 60;       // multiply the remainer by 60
    var minutes = Math.floor(minutesFromRemainder);       // get minutes from integer
    var secondsFromRemainder = (minutesFromRemainder - minutes) * 60;   // multiply the remainer by 60
    var seconds = LocationFormatter.roundToDecimal(secondsFromRemainder, 2); // get minutes by rounding to integer

    return hemisphere + ' ' + degrees + '° ' + minutes + "' " + seconds + "''";
};

LocationFormatter.decimalLatToDMS = function(location) {
    var hemisphere = (location < 0) ? LocationFormatter.SOUTH : LocationFormatter.NORTH; // south if negative
    return LocationFormatter.decimalToDMS(location, hemisphere);
};

LocationFormatter.decimalLongToDMS = function(location) {
    var hemisphere = (location < 0) ? LocationFormatter.WEST : LocationFormatter.EAST;  // west if negative
    return LocationFormatter.decimalToDMS(location, hemisphere);
};

LocationFormatter.DMSToDecimal = function(degrees, minutes, seconds, hemisphere) {
    var ddVal = degrees + minutes / 60 + seconds / 3600;
    ddVal = (hemisphere == LocationFormatter.SOUTH || hemisphere == LocationFormatter.WEST) ? ddVal * -1 : ddVal;
    return LocationFormatter.roundToDecimal(ddVal, 5);
};
function isCoordX(coord) {
//    return /([EW][\s]*[0-1]?\d{1,2}°[\s]+[0-5]?\d'[\s]+[0-5]?\d?\.?\d*'')|([EW][\s]*[0-1]?\d{1,2}[\s]+[0-5]?\d[\s]+[0-5]?\d?\.?\d*)/.test(coord);
    return /[-]?\d+[.]?\d*/.test(coord);
}
function isCoordY(coord) {
//    return /([NS][\s]*\d{1,2}°[\s]+[0-5]?\d'[\s]+[0-5]?\d?\.?\d*'')|([NS][\s]*\d{1,2}[\s]+[0-5]?\d[\s]+[0-5]?\d?\.?\d*)/.test(coord);
    return /[-]?\d+[.]?\d*/.test(coord);
}
function initMap() {
    // init GMap
    //yAr = $('#emplacement_coords_y').val().split(/([NS])[\s]*(\d{1,2})[°]?[\s]+([0-5]?\d)[']?[\s]+([0-5]?\d?\.?\d*)['']?/g);
    //xAr = $('#emplacement_coords_x').val().split(/([EW])[\s]*([0-1]?\d{1,2})[°]?[\s]+([0-5]?\d)[']?[\s]+([0-5]?\d?\.?\d*)['']?/g);
    //yCard = yAr[1];
    //xCard = xAr[1];
    //yAr = yAr.map(parseFloat);
    //xAr = xAr.map(parseFloat);
    //yD = LocationFormatter.DMSToDecimal(yAr[2], yAr[3], yAr[4], yCard);
    //xD = LocationFormatter.DMSToDecimal(xAr[2], xAr[3], xAr[4], xCard);
    var latlon = new google.maps.LatLng( $('#emplacement_coords_y').val(),  $('#emplacement_coords_x').val());
    var mapOptions = {
        center: latlon,
        zoom: 15
    }
    $('#add-work-map').removeClass('hide');
    var map = new google.maps.Map(document.getElementById('add-work-map'), mapOptions);
    var marker = new google.maps.Marker({
        position: latlon,
        map: map,
        title: $('#oeuvre_emplact').val(),
        draggable: true
    })
    /* Draggable marker -> change coords */
    google.maps.event.addListener(marker, 'drag', function(e) {
//        $('#emplacement_coords_y').val(LocationFormatter.decimalLatToDMS(e.latLng.lat()))
//        $('#emplacement_coords_x').val(LocationFormatter.decimalLongToDMS(e.latLng.lng()))
        $('#emplacement_coords_y').val(e.latLng.lat())
        $('#emplacement_coords_x').val(e.latLng.lng())
        $('#oeuvre_emplact').val('');
        $('#oeuvre_id').val('');
    })
    google.maps.event.addListener(marker, 'dragend', function(e) {
//        $('#emplacement_coords_y').val(LocationFormatter.decimalLatToDMS(e.latLng.lat()))
//        $('#emplacement_coords_x').val(LocationFormatter.decimalLongToDMS(e.latLng.lng()))
        $('#emplacement_coords_y').val(e.latLng.lat())
        $('#emplacement_coords_x').val(e.latLng.lng())
        $('#oeuvre_emplact').val('');
        $('#oeuvre_id').val('');
    })
    map.initialize;
}
function initViewWorkMap(x, y, locationLabel) {
  //  if (isCoordX(x) && isCoordY(y)) {
        // init GMAP
//        yAr = y.split(/([NS])[\s]*(\d{1,2})[°]?[\s]+([0-5]?\d)[']?[\s]+([0-5]?\d?\.?\d*)['']?/g);
//        xAr = x.split(/([EW])[\s]*([0-1]?\d{1,2})[°]?[\s]+([0-5]?\d)[']?[\s]+([0-5]?\d?\.?\d*)['']?/g);
//        yCard = yAr[1];
//        xCard = xAr[1];
//        yAr = yAr.map(parseFloat);
//        xAr = xAr.map(parseFloat);
//        yD = LocationFormatter.DMSToDecimal(yAr[2], yAr[3], yAr[4], yCard);
//        xD = LocationFormatter.DMSToDecimal(xAr[2], xAr[3], xAr[4], xCard);
    if (isCoordX(x) && isCoordY(y)) {
        var latlon = new google.maps.LatLng(y, x);
        var mapOptions = {
            center: latlon,
            zoom: 15
        }
        $('#work-view-map').removeClass('hide');
        var map = new google.maps.Map(document.getElementById('work-view-map'), mapOptions);
        var marker = new google.maps.Marker({
            position: latlon,
            map: map,
            title: locationLabel
        })
        map.initialize;
    } else {
        $('#work-view-map').addClass('hide');
    }
}
function hideAddWMap() {
    $('#add-work-map').addClass('hide');
}
function addViewWorkType(id, name, color) {
    $('#work-view #work-types').append('<div data-id="' + id + '">' + name + '</div>')
    $('#work-view #work-types div[data-id="' + id + '"]').css('background-color', '#' + color)
}
function loadWorkView(workId, locationLabel) {
    $('#work-view span#work-title').hide();
    $('#work-view span#user-add-label').hide();
    $('#work-view span#user-add').hide();
    $('#work-view div#work-details *').hide();
    $('#work-view p#help').hide();
    $('#work-view div#additional-workers-container').hide();
    $('#work-view #work-edit-container').show();
    $('#work-view div#work-details').show();
    $('#work-view #work_id').val(workId);
    $('div#work-edit-container a').attr('href', $('#work-view #work_edit_link').val().replace('__ID__', $('#work-view #work_id').val()));
    // $('#work-view p#work-location').html(locationLabel).show();
    $.ajax({
        type: "POST",
        url: "/index.php/ajax/get-work-details",
        data: {
            workId: workId,
            auth_token: $('#auth_token').val(),
        },
        success: function(response) {
            fm = response.frequency_months;
            fw = response.frequency_weeks;
            fd = response.frequency_days;

            $('p#work-wrapper').show();
            $('span#work-title').html(response.title).show();
            if (fm || fw || fd) {
                $('div#work-frequency').show();
            }
            initViewWorkMap(response.coords_x, response.coords_y, locationLabel);
            if (response.oeuvre_title) {
                $('p#work-oeuvre-title-wrapper').show();
                $('span#work-oeuvre-numero').html(response.oeuvre_numero).show();
                $('span#work-oeuvre-title').html(response.oeuvre_title).show();
            }
            if (response.user_add) {
                $('span#user-add-label').show();
                $('span#user-add').html(response.user_add).show();
            }
            if (response.description) {
                $('p#work-description').html(response.description).show();
            }
            if (response.tools) {
                $('p#work-tools-wrapper').show();
                $('span#work-tools').html(response.tools).show();
            }
            if (response.desc_emplact) {
                $('p#work-desc_emplact').html(response.desc_emplact).show();
            }
            if (response.desc_emplact) {
                $('p#work-desc_emplact').html(response.desc_emplact).show();
            }
            if (isCoordX(response.coords_x) && isCoordY(response.coords_y)) {
                $('p#work-coords_x').html(response.coords_x).show();
                $('p#work-coords_y').html(response.coords_y).show();
            }
            if (response.prio) {
                $('div#work-prio-container').show();
                $('div#work-prio-' + response.prio).show();
            }
            if(response.additional_workers.length) {
                $('div#additional-workers-label').show();
                $('div#additional-workers').show();
                console.log(response.additional_workers)
                $.each(response.additional_workers, function(idx, elt){
                    $('div#additional-workers').append('<span>'+elt+'</span>');
                })
            }
            if (fm && fm > 0) {
                $('div#work-frequency-number').html(response.frequency_months).show();
                if (fm > 1) {
                    $('div#work-frequency-months-many').show();
                } else {
                    $('div#work-frequency-months-one').show();
                }
            } else if (fw && fw > 0) {
                $('div#work-frequency-number').html(response.frequency_weeks).show();
                if (fw > 1) {
                    $('div#work-frequency-weeks-many').show();
                } else {
                    $('div#work-frequency-weeks-one').show();
                }
            } else if (fd && fd > 0) {
                $('div#work-frequency-number').html(response.frequency_days).show();
                if (fd > 1) {
                    $('div#work-frequency-days-many').show();
                } else {
                    $('div#work-frequency-days-one').show();
                }
            }
            if (response.types) {
                $('div#work-types').html('');
                $(response.types).each(function(i, elt) {
                    $('div#work-types').show();
                    addViewWorkType(elt.id, elt.name, elt.color)
                })
            }
            if (response.date_last_done) {
                $('p#work-date_last_done-container').show()
                $('span#work-date_last_done').show().html($.format.date(response.date_last_done + ' 00:00:00.0', 'dd/MM/yyyy'));
            }
        },
        error: function(response) {
            console.log(response)
            console.log('AJAX error')
        }
    })
}
$(document).ready(function() {
    if ($('section#works-list').length > 0) {
        $('div.work-options').hide();
        $('#work-view div#work-details').hide();
        $('#work-view div#work-details p').hide();
        $('#work-view #work-edit-container').hide();
        $('section#works-list li').hover(function() {
            $($(this).children('div.work-options')[0]).show();
        }, function() {
            $($(this).children('div.work-options')[0]).hide();
        })
        $('section#works-list div.work-options').click(function(e) {
            e.stopPropagation();
        })
        $('section#works-list li').click(function() {
            loadWorkView($(this).attr('data-workid'), $(this).children('div.location').text());
            $('div#noticesContainer dialog').hide();
        })
    }
    else if ($('.formWork').length > 0) {
        hideAddWMap();
        var wt = $('[name="worktype"][checked="checked"]').val();
        if ('question' === wt) {
            $('#fieldset-titleDesc').addClass('hide')
            $('#fieldset-titleDescQuestion').removeClass('hide')
        }
        if (!isCoordX($('#emplacement_coords_x').val()) || !isCoordY($('#emplacement_coords_y').val())) {
            hideAddWMap();
        } else {
            initMap();
        }
        if ($('#maponload').val() == 1) {
            initMap();
        }
        $('.prevAddWorker').each(function(idx, elt) {
            var curIdx = 'additional-worker-' + ($('.additional_worker').length + 1);
            var adW = $('#additional_worker_template')
                    .clone()
                    .prop('id', 'additional-workers[]')
                    .prop('name', 'additional-workers[]')
            adW.removeClass('hide')
                    .addClass('additional_worker')
                    .val(elt.value)
                    .insertBefore('#add_additional_worker')
                    .keyup(function() {
                        var hasEmptyValues = false;
                        for (var i = 0; i < $('.additional_worker').length; i++) {
                            console.log($('.additional_worker')[i].value);
                            if ($('.additional_worker')[i].value === '') {
                                hasEmptyValues = true;
                                break;
                            }
                        }
                        if (!hasEmptyValues) {
                            $('#add_additional_worker').show();
                        } else {
                            $('#add_additional_worker').hide();
                        }
                    });
        })
        $('.prevAddWorker').remove();
        $('input#oeuvre_emplact').autocomplete({
            source: function(request, response) {
                $.ajax({
                    type: "GET",
                    url: '/index.php/ajax/get-oeuvres',
                    data: {
                        q: request.term,
                        auth_token: $('#auth_token').val(),
                    },
                    dataType: 'json',
                    async: true,
                    cache: true,
                    success: function(data) {
                        var suggestions = [];
                        $.each(data, function(i, val) {
                            suggestions.push({'id': val.Value, 'value': val.Text})
                        })
                        response(suggestions);
                    },
                    error: function(response) {
                        console.log('AJAX error: get-oeuvres')
                    }
                })
            },
            select: function(event, ui) {
                $('#oeuvre_id').val(ui.item.id);
                $.ajax({
                    type: "POST",
                    url: '/index.php/ajax/get-oeuvre-coords',
                    data: {
                        oeuvreId: ui.item.id,
                        auth_token: $('#auth_token').val(),
                    },
                    success: function(response) {
                        if (response.coords_x && response.coords_y) {
                            $('#emplacement_coords_x').val(response.coords_x);
                            $('#emplacement_coords_y').val(response.coords_y);
                            initMap();
                        }
                        else {
                            $('#emplacement_coords_x').val('');
                            $('#emplacement_coords_y').val('');
                            hideAddWMap();
                        }
                        //refreshMap();
                    },
                    error: function(response) {
                        console.log('AJAX error: get-oeuvres-coords')
                    }
                })
            }
        });
        $('input#oeuvre_emplact').on('keyup change', function() {
            if ($(this).val() === '') {
                $('#oeuvre_id').val('');
                $('#emplacement_coords_x').val('');
                $('#emplacement_coords_y').val('');
            }
        });
        $('input#emplacement_coords_x').on('keyup change', function() {
            $('#oeuvre_id').val('');
            $('input#oeuvre_emplact').val('');
            var coord_x = $('input#emplacement_coords_x').val();
            var coord_y = $('input#emplacement_coords_y').val();
            if (isCoordX(coord_x) && isCoordY(coord_y))
                initMap();
            else
                hideAddWMap();
        });
        $('input#emplacement_coords_y').on('keyup change', function() {
            $('#oeuvre_id').val('');
            $('input#oeuvre_emplact').val('');
            var coord_x = $('input#emplacement_coords_x').val();
            var coord_y = $('input#emplacement_coords_y').val();
            if (isCoordX(coord_x) && isCoordY(coord_y))
                initMap();
            else
                hideAddWMap();
        });
        $('#add_type_color_btn').colpick({onSubmit: function(hsb, hex, rgb, el) {
                $(el).css('background-color', '#' + hex);
                $(el).colpickHide();
            }});
        $('#add_additional_worker').click(function() {
            var curIdx = 'additional-worker-' + ($('.additional_worker').length + 1);
            var adW = $('#additional_worker_template')
                    .clone()
                    .prop('id', 'additional-workers[]')
                    .prop('name', 'additional-workers[]')
            adW.removeClass('hide')
                    .addClass('additional_worker')
                    .insertBefore('#add_additional_worker')
                    .keyup(function() {
                        var hasEmptyValues = false;
                        for (var i = 0; i < $('.additional_worker').length; i++) {
                            console.log($('.additional_worker')[i].value);
                            if ($('.additional_worker')[i].value === '') {
                                hasEmptyValues = true;
                                break;
                            }
                        }
                        console.log(hasEmptyValues)
                        if (!hasEmptyValues) {
                            $('#add_additional_worker').show();
                        } else {
                            $('#add_additional_worker').hide();
                        }
                    });
            $(this).hide();
        })
        /*
         $('#add_additional_worker').click(function(){
         $.ajax({
         type:"POST",
         url:"/index.php/ajax/add-additional-worker",
         data:{
         
         }
         })
         })*/
        $('#add_type_btn').click(function() {
            $.ajax({
                type: "POST",
                url: "/index.php/ajax/create-on-fly-type",
                data: {
                    name: $('#add_type_label').val(),
                    color: rgb2hex($('#add_type_color_btn').css('background-color'), true),
                    auth_token: $('#auth_token').val(),
                },
                success: function(response) {
                    if (response.success == true) {
                        console.log('0eeee')
                        function addListElement(el) {
                            $('dd#types-element').append(el);
                            var labels = $('dd#types-element label').sort(function(a, b) {
                                if ($(a).text() < $(b).text())
                                    return -1;
                                if ($(a).text() > $(b).text())
                                    return 1;
                                return 0;
                            });
                            $('dd#types-element').html(labels);
                            $('dd#types-element label.hide').removeClass('hide')
                        }
                        addListElement('<label class="hide"><input type="checkbox" checked="checked" name="types[]" id="types-' + response.typeId + '" value="' + response.typeId + '">' + response.typeName + '</label>');
                    }
                    else if (response.error == true && response.typeId) {
                        $('input#types-' + response.typeId).prop('checked', true);
                    }
                    if (response.notice)
                        $('div#noticesContainer').empty().prepend(response.notice)
                },
                error: function(response) {
                    console.log('AJAX error')
                }
            })
        })
        $('#add_type_label').keypress(function(e) {
            if (e.which == 13) {
                $('#add_type_btn').click();
                e.preventDefault();
            }
        });
        $('#manage_types_btn').click(function() {
            window.location.href = '/index.php/types/';
        });
        $('input[name="worktype"]').change(function() {
            var wtype = $(this).val();
            if ('question' == wtype) {
                $('#fieldset-titleDesc').addClass('hide')
                $('#fieldset-titleDescQuestion').removeClass('hide')
            } else if ('normal' == wtype || 'markup' == wtype) {
                $('#fieldset-titleDesc').removeClass('hide')
                $('#fieldset-titleDescQuestion').addClass('hide')
            }
        })
    }
})