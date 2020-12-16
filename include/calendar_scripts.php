<script type="text/javascript">
  var calendar = new DayPilot.Calendar("dpCalendar");
  var modalEvent = null;
  <?php if(!$is_admin) { ?>
	calendar.eventMoveHandling = "Disabled";
	calendar.eventResizeHandling = "Disabled";
	calendar.eventCLickHandling = "Disabled";
  <?php } ?>
  calendar.weekStarts = 1;
  calendar.heightSpec = "Fixed";
  calendar.height = 350;
  calendar.width = "100%";
  calendar.cellHeight = 18;
  calendar.viewType = "Week";
  calendar.timeFormat = "Clock24Hours";
  calendar.dayBeginsHour = 6;
  calendar.dayEndsHour = 23;
  calendar.businessBeginsHour = 8;
  calendar.businessEndsHour = 21;
  calendar.headerDateFormat = "yyyy-MM-dd";
  calendar.init();
  calendar.events.load("calendar/calendar_events.php");

	<?php if($is_admin) { ?>
		var timeStart = '';
		var timeEnd = '';
		calendar.onEventMoved = function (args) {
			DayPilot.Http.ajax({
				url: "calendar/calendar_move.php",
				data: args,
				success: function(ajax) {
					var response = ajax.data;
				}
			});
		};
		calendar.onTimeRangeSelected = function (args) {
			timeStart = args.start;
			timeEnd = args.end;	
			$('#newEventModal').modal('show');
		};
		function saveEvent() {
			$('#newEventModal').modal('hide');
			var e = new DayPilot.Event({
				start: timeStart,
				end: timeEnd,
				id: DayPilot.guid(),
				text: $('#eventTitle').val() + " (" + $('#eventPlace').val() + ")"
			});
			calendar.events.add(e);
			DayPilot.Http.ajax({
				url: "calendar/calendar_create.php",
				data: { 
						start: timeStart,
						end: timeEnd,
						text: $('#eventTitle').val(),
						place: $('#eventPlace').val(),
						coordinates: $('#eventCoordinates').val()
					},
				success: function(ajax) {
					var response = ajax.data;
				}
			});
		}
		calendar.eventDoubleClickHandling = "Enabled";
		calendar.onEventDoubleClick = function (args) {
			if(confirm("Ar norite ištrinti įvykį?") == true) {
				DayPilot.Http.ajax({
					url: "calendar/calendar_delete.php",
					data: args,
					success: function(ajax) {
						var response = ajax.data;
						calendar.events.load("calendar/calendar_events.php");
					}
				});
			}
		};
		calendar.eventClickHandling = "Enabled";
		calendar.onEventClick = function (args) {
			// var name = prompt("Edit event name:", args.e.data.text);
			// if (!name) return;
			// DayPilot.Http.ajax({
			// 	url: "calendar/calendar_update_name.php",
			// 	data: { name: name, id: args.e.data.id},
			// 	success: function(ajax) {
			// 		var response = ajax.data;
			// 		calendar.events.load("calendar/calendar_events.php");
			// 	}
            // });
            $('#eventInfoModal').modal('show');
            $('#eventInfoTitle').val(args.e.data.text);
            $('#eventInfoPlace').val(args.e.data.place);
            $.ajax({
                url: "calendar/calendar_get_users.php",
                type: "post",
                data: { 
                    event_id: args.e.data.id,
                },
                success: function(data) {
                    html = "Prie treniruotės prisiregistravo " + data.length + " žmonės";
                    if(data.length > 0) {
                        html += ":<br>";
                        for (index = 0; index < data.length; ++index) {
                            if(data[index].arrived == "1") {
                                arrived = " checked";
                            } else {
                                arrived = "";
                            }
                            html += '<div class="form-check">';
                            html += '<input class="form-check-input" type="checkbox" onclick="updateArrived(' + data[index].user_id + ', ' + args.e.data.id + ')" id="check' + data[index].user_id + '" ' + arrived + '>';
                            html += '<label class="form-check-label" for="check' + data[index].user_id + '">' + data[index].email + '</label>';
                            html += '</div>';
                        }
                    }
                    $("#eventInfoText").html(html);
                }
            });
        };
        function updateArrived(user_id, event_id) {
            console.log($('#check' + user_id).is(":checked"));
            $.ajax({
                url: "include/change_arrived.php",
                type: "post",
                data: { 
                    event: event_id,
                    user: user_id,
                    arrived: $('#check' + user_id).is(":checked")
                },
            });
        }
	<?php } else if($logged_in) { ?>
		calendar.eventClickHandling = "Enabled";
		calendar.onEventClick = function (args) {
            if(Date.parse(args.e.data.start) > Date.now()) {
                modalEvent = args.e.data;
                if(args.e.data.registered == 0) {
                    checkFreebusy();
                    $('#registrationTitle').val(args.e.data.text);
                    $('#registrationPlace').val(args.e.data.place);
                    $('#registrationTime').val((args.e.data.start).toString().replace('T', ' '));
                    $('#registerEventModal').modal('show');
                } else if(Date.parse(args.e.data.start)-3600000 > Date.now()){
                    $('#cancelTitle').html(args.e.data.text);
                    $('#cancelPlace').html(args.e.data.place);
                    $('#cancelTime').html((args.e.data.start).toString().replace('T', ' '));
                    $('#cancelEventModal').modal('show');
                }
            }
            var location = args.e.data.coordinates.split(",");
            var position = {
                lat: parseFloat(location[0]),
                lng: parseFloat(location[1]),
            };
            for (index = 0; index < markerData.length; ++index) {
                if(markerData[index].coordinates == args.e.data.coordinates) {
                    google.maps.event.trigger(markerArray[index], 'click');
                    break;
                }
            }
            map.setCenter(position);
        };
        function saveRegistration() {
            $.ajax({
                url: "include/login_handler.php",
                type: "post",
                data: { 
                    event: modalEvent.id,
                    timeStart: modalEvent.start.toString(),
                    timeEnd: modalEvent.end.toString(),
                    title: modalEvent.text,
                    place: modalEvent.place,
                    calendar_id: $('#googleCalendar').val()
                },
                success: function(data) {
                    $('#registerEventModal').modal('hide');
                    alert("Registracija sėkminga");
                    calendar.events.load("calendar/calendar_events.php");
                }
            });
        }
        function cancelRegistration() {
            $.ajax({
                url: "include/login_handler.php",
                type: "post",
                data: { 
                    cancel_event: modalEvent.id,
                },
                success: function(data) {
                    $('#cancelEventModal').modal('hide');
                    if(data.result == "OK") {
                        alert("Registracija atšaukta");
                        calendar.events.load("calendar/calendar_events.php");
                    } else {
                        alert("Atsiprašome, atšaukti registracijos nepavyko");
                    }
                }
            });
        }
        function checkFreebusy() {
            $('#registerSubmitButton').attr('disabled', 'disabled');
            $.ajax({
                url: "include/login_handler.php",
                type: "post",
                data: { 
                    freebusy: modalEvent.id,
                    timeStart: modalEvent.start.toString(),
                    timeEnd: modalEvent.end.toString(),
                    calendar_id: $('#googleCalendar').val()
                },
                success: function(data) {
                    var obj = JSON.parse(data);
                    if(obj.busy.length > 0) {
                        html = "Pasirinktu laiku kalendoriuje yra pridėta kitų įvykių:<br>";
                        for (index = 0; index < obj.busy.length; ++index) {
                            html += obj.busy[index].start.split("T")[1].split("+")[0] + " - " + obj.busy[index].end.split("T")[1].split("+")[0] + "<br>";
                        }
                        html += "Ar tikrai norite registruotis treniruotei?"
                    } else {
                        html = "Pasirinktu laiku kalendoriuje nėra pridėta kitų įvykių.";
                    }
                    $('#registerSubmitButton').removeAttr("disabled");
                    $('#freebusy_response').html(html);
                },
                error: function(data) {
                    $('#registerSubmitButton').removeAttr("disabled");
                    $('#freebusy_response').html(data);
                }
            });
        }
    <?php } else { ?>
        calendar.eventClickHandling = "Enabled";
		calendar.onEventClick = function (args) {
            var location = args.e.data.coordinates.split(",");
            var position = {
                lat: parseFloat(location[0]),
                lng: parseFloat(location[1]),
            };
            for (index = 0; index < markerData.length; ++index) {
                if(markerData[index].coordinates == args.e.data.coordinates) {
                    google.maps.event.trigger(markerArray[index], 'click');
                    break;
                }
            }
            map.setCenter(position);
		};
    <?php } ?>
</script>