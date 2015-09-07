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
    var latlon = new google.maps.LatLng( $('#emplacement_coords_y').val(),  $('#emplacement_coords_x').val());
    var mapOptions = {
        center: latlon,
        zoom: 15
    };
    $('#add-work-map').removeClass('hide');
    var map = new google.maps.Map(document.getElementById('add-work-map'), mapOptions);
    var marker = new google.maps.Marker({
        position: latlon,
        map: map,
        title: $('#oeuvre_emplact').val(),
        draggable: true
    });
    /* Draggable marker -> change coords */
    google.maps.event.addListener(marker, 'drag', function(e) {
//        $('#emplacement_coords_y').val(LocationFormatter.decimalLatToDMS(e.latLng.lat()))
//        $('#emplacement_coords_x').val(LocationFormatter.decimalLongToDMS(e.latLng.lng()))
        $('#emplacement_coords_y').val(e.latLng.lat());
        $('#emplacement_coords_x').val(e.latLng.lng());
        $('#oeuvre_emplact').val('');
        $('#oeuvre_id').val('');
    });
    google.maps.event.addListener(marker, 'dragend', function(e) {
//        $('#emplacement_coords_y').val(LocationFormatter.decimalLatToDMS(e.latLng.lat()))
//        $('#emplacement_coords_x').val(LocationFormatter.decimalLongToDMS(e.latLng.lng()))
        $('#emplacement_coords_y').val(e.latLng.lat());
        $('#emplacement_coords_x').val(e.latLng.lng());
        $('#oeuvre_emplact').val('');
        $('#oeuvre_id').val('');
    });
    map.initialize;
}
function initViewWorkMap(x, y, locationLabel) {
    if (isCoordX(x) && isCoordY(y) && google !== undefined) {
        var latlon = new google.maps.LatLng(y, x);
        var mapOptions = {
            center: latlon,
            zoom: 15
        };
        $('#wv_map').show();
        var map = new google.maps.Map(document.getElementById('wv_map'), mapOptions);
        var marker = new google.maps.Marker({
            position: latlon,
            map: map,
            title: locationLabel
        });
        map.initialize;
    } else {
        $('#wv_map').hide();
    }
}
function hideAddWMap() {
    $('#add-work-map').addClass('hide');
}
function addViewWorkType(id, name, color) {
    $('#work-view #work-types').append('<div data-id="' + id + '">' + name + '</div>');
    $('#work-view #work-types div[data-id="' + id + '"]').css('background-color', '#' + color);
}

function loadWorkView(workId) {
    $('#wv_set_urgent').removeClass('active');
    $('#wv_set_normal').removeClass('active');
    $('#wv_set_done').removeClass('active');
    $('#wv_frequency_container').hide();
    $('#wv_tools_container').hide();
    $('#wv_workers_container').hide();
    $('#wv_oeuvre_container').hide();
    $('#wv_workers_container').hide();
    $('#wv_map').hide();
    $('#wv_types_container').html('');
    $('#wv_workers').html('');
    $('#wv_desc_emplact').html('');
    
    $.ajax({
        type: "POST",
        url: "/index.php/ajax/get-work-details",
        data: {
            workId: workId,
            auth_token: $('#auth_token').val()
        },
        success: function(response) {
            $('#wv_id').val(workId);
            $('#wv_title').html(response.title);
            $('#wv_title_inside').html(response.title);
            if(response.prio=="1") {
                $('#wv_set_urgent').addClass('active');
            } else if(response.prio=="2") {
                $('#wv_set_normal').addClass('active');
            } else {
                $('#wv_set_done').addClass('active');
            }
            fd=response.frequency_days;
            fw=response.frequency_weeks;
            fm=response.frequency_months;
            fcur=null;
            if(fd) {
                fcur = fd;
                if(fd == 1)
                    $('#wv_frequency_type').html('jour');
                else
                    $('#wv_frequency_type').html('jours');
            } else if(fw) {
                fcur = fw;
                if(fd == 1)
                    $('#wv_frequency_type').html('semaine');
                else
                    $('#wv_frequency_type').html('semaines');
            } else if(fm) {
                fcur = fm;
                $('#wv_frequency_type').html('mois');
            }
            if(fcur) {
                $('#wv_frequency_container').show();
                $('#wv_frequency_number').html(fcur);
            }
            if(response.types) {
                $(response.types).each(function(i){
                    $('#wv_types_container').append('<a class="ui label" style="background-color:#'+this.color+'">'+this.name+'</a>')
                })
            }
            if(response.description) {
                $('#wv_description').html(response.description);
            }
            if(response.tools) {
                $('#wv_tools_container').show();
                $('#wv_tools').html(response.tools);
            }
            if(response.additional_workers.length) {
                $('#wv_workers_container').show();
                $(response.additional_workers).each(function(i){
                    $('#wv_workers').append('<li>'+this+'</li>')
                })
            }
            if(response.coords_x) {
                x = Math.round(response.coords_x * 10000) / 10000;
                y = Math.round(response.coords_y * 10000) / 10000;
                $('#wv_coords').html(x + ', ' + y);
            }
            if(response.oeuvre_title) {
                $('#wv_oeuvre_container').show();
                $('#wv_oeuvre').html(response.oeuvre_title);
                initViewWorkMap(response.coords_x, response.coords_y, response.oeuvre_title);
            } else if(response.coords_x) {
                initViewWorkMap(response.coords_x, response.coords_y, response.title);
            }
            if(response.desc_emplact) {
                $('#wv_desc_emplact').html(response.desc_emplact);
            }
            
            $('#work_view').modal('show');
        },
        error: function(response) {
            console.log('AJAX error Get Work');
        }
    });
}

function sortWList(list){
    var rows = $(list + ' tbody  tr').get();

    rows.sort(function(a, b) {

    var A = $(a).children('td').children('.work_title').eq(0).text().toUpperCase();
    var B = $(b).children('td').children('.work_title').eq(0).text().toUpperCase();

    if(A < B) {
      return -1;
    }

    if(A > B) {
      return 1;
    }

    return 0;

    });

    $.each(rows, function(index, row) {
      $(list).children('tbody').append(row);
    });
}

$(document).ready(function() {
    $(document).tooltip({track:true});
    $('.ui.dropdown').dropdown({
        allowCategorySelection: true
    });
    $('table.works_table td.item').click(function() {
        loadWorkView($(this).parent('tr').attr('data-workid'));
    });
    $('.clickable_link').click(function() {
        document.location.href=$(this).attr('data-href');
    });
    // Modals
    $('.ui.modal').modal();
    $('.delete_work_button').click(function(){
        $('input#waiting_action').attr('data-href', $(this).attr('data-href'));
        $('#delete_work_modal')
                .modal({
                    onApprove:function(){location.reload()}
                })
                .modal('show');
    })
    $('.set_work_done_button').click(function(){
        $('input#waiting_action').attr('data-href', $(this).attr('data-href'));
        $('#set_work_done_modal')
                .modal({
                    onApprove:function(){location.reload()}
                })
                .modal('show');
    })
    // Notices
    $('#noticesContainer .message').delay(3000).fadeOut();
    // List
    // WV Set prios
    $('#wv_set_urgent').click(function(){
        wid = $('#wv_id').val();
        $.ajax({
            type: "GET",
            url: '/index.php/ajax/change-work-prio',
            data: {
                id: wid,
                p: 1,
                auth_token: $('#auth_token').val()
            },
            success: function(response) {
                $('#wv_set_urgent').addClass('active');
                $('#wv_set_normal').removeClass('active');
                $('#wv_set_done').removeClass('active');
                wm = $('tr[data-workid="'+wid+'"]').detach();
                $('#works_1').append(wm);
                sortWList('#works_1');
            },
            error: function(response) {
                console.log('AJAX error: wv_set_urgent');
            }
        });
    })
    $('#wv_set_normal').click(function(){
        wid = $('#wv_id').val();
        $.ajax({
            type: "GET",
            url: '/index.php/ajax/change-work-prio',
            data: {
                id: wid,
                p: 2,
                auth_token: $('#auth_token').val()
            },
            success: function(response) {
                $('#wv_set_urgent').removeClass('active');
                $('#wv_set_normal').addClass('active');
                $('#wv_set_done').removeClass('active');
                wm = $('tr[data-workid="'+wid+'"]').detach();
                $('#works_2').append(wm);
                sortWList('#works_2');
            },
            error: function(response) {
                console.log('AJAX error: wv_set_urgent');
            }
        });
    })
    $('#wv_set_done').click(function(){
        wid = $('#wv_id').val();
        $.ajax({
            type: "GET",
            url: '/index.php/ajax/change-work-prio',
            data: {
                id: wid,
                p: 3,
                auth_token: $('#auth_token').val()
            },
            success: function(response) {
                $('#wv_set_urgent').removeClass('active');
                $('#wv_set_normal').removeClass('active');
                $('#wv_set_done').addClass('active');
                wm = $('tr[data-workid="'+wid+'"]').detach();
                $('#works_3').append(wm);
                sortWList('#works_3');
            },
            error: function(response) {
                console.log('AJAX error: wv_set_urgent');
            }
        });
    })
    
    /* OLD */
    if ($('section#works-list').length > 0) {
        $('div.work-options').hide();
        $('#work-view div#work-details').hide();
        $('#work-view div#work-details p').hide();
        $('#work-view #work-edit-container').hide();
        $('#work-view div#nearby-label').hide();
        $('section#works-list li').hover(function() {
            $($(this).children('div.work-options')[0]).show();
        }, function() {
            $($(this).children('div.work-options')[0]).hide();
        });
        $('section#works-list div.work-options').click(function(e) {
            e.stopPropagation();
        });
        $('section#works-list li').click(function() {
            loadWorkView($(this).parent('tr').attr('data-workid'));
            $('div#noticesContainer dialog').hide();
        });
        $('#a_editWork').click(function(){
           document.location.href = $(this).attr('data-url');
        });
    }
    else if ($('.formWork').length > 0) {
        hideAddWMap();
        var wt = $('[name="worktype"][checked="checked"]').val();
        $('#add_edit_work').click(function(){
            $('#formAddWork').submit();
        })
        if ('question' === wt) {
            $('#fieldset-titleDesc').addClass('hide');
            $('#fieldset-titleDescQuestion').removeClass('hide');
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
                    .prop('name', 'additional-workers[]');
            adW.removeClass('hide')
                    .addClass('additional_worker')
                    .val(elt.value)
                    .insertBefore('#add_additional_worker')
                    .keyup(function() {
                        var hasEmptyValues = false;
                        for (var i = 0; i < $('.additional_worker').length; i++) {
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
        });
        $('.prevAddWorker').remove();
        $('input#oeuvre_emplact').autocomplete({
            source: function(request, response) {
                $.ajax({
                    type: "GET",
                    url: '/index.php/ajax/get-oeuvres',
                    data: {
                        q: request.term,
                        auth_token: $('#auth_token').val()
                    },
                    dataType: 'json',
                    async: true,
                    cache: true,
                    success: function(data) {
                        var suggestions = [];
                        $.each(data, function(i, val) {
                            suggestions.push({'id': val.Value, 'value': val.Text});
                        });
                        response(suggestions);
                    },
                    error: function(response) {
                        console.log('AJAX error: get-oeuvres');
                    }
                });
            },
            select: function(event, ui) {
                $('#oeuvre_id').val(ui.item.id);
                $.ajax({
                    type: "POST",
                    url: '/index.php/ajax/get-oeuvre-coords',
                    data: {
                        oeuvreId: ui.item.id,
                        auth_token: $('#auth_token').val()
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
                        console.log('AJAX error: get-oeuvres-coords');
                    }
                });
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
                    .prop('name', 'additional-workers[]');
            adW.removeClass('hide')
                    .addClass('additional_worker')
                    .insertBefore('#add_additional_worker')
                    .keyup(function() {
                        var hasEmptyValues = false;
                        for (var i = 0; i < $('.additional_worker').length; i++) {
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
            $(this).hide();
        });
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
                    auth_token: $('#auth_token').val()
                },
                success: function(response) {
                    if (response.success == true) {
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
                            $('dd#types-element label.hide').removeClass('hide');
                        }
                        addListElement('<label class="hide"><input type="checkbox" checked="checked" name="types[]" id="types-' + response.typeId + '" value="' + response.typeId + '">' + response.typeName + '</label>');
                    }
                    else if (response.error == true && response.typeId) {
                        $('input#types-' + response.typeId).prop('checked', true);
                    }
                    if (response.notice) {
                        $('div#noticesContainer').empty().prepend(response.notice);
                        $('#noticesContainer .message').show();
                        $('#noticesContainer .message').delay(3000).fadeOut();
                    }
                },
                error: function(response) {
                    console.log('AJAX error');
                }
            });
        });
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
                $('#title').addClass('hide');
                $('#title_question').removeClass('hide');
                $('#title-element .errors').remove();
            } else if ('normal' == wtype || 'markup' == wtype) {
                $('#title_question').addClass('hide');
                $('#title').removeClass('hide');
                $('#title_question-element .errors').remove();
            }
        });
    }
});