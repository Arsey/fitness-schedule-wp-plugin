<div id="shcedule">
    <?php
    $programs = WP_Kivi_Schedule_Plugin::fetch_programs();

    $programs_options = '<option value=""></option>';
    foreach ($programs as $program) {
        $programs_options .= '<option value="' . $program['id'] . '">' . $program['title'] . '</option>';
    }
    ?>
    <div id="shcedule_filters">
        <?php $cities = WP_Kivi_Schedule_Plugin::fetch_cities(); ?>
        <label for="select_cities">Шаг1. Выберите город </label>

        <select id="select_cities">
            <option value="" selected></option>
            <?php foreach ($cities as $city) { ?>
                <option value="<?php echo $city['id']; ?>"><?php echo $city['name']; ?></option>
            <?php } ?>
        </select>

        <label for="select_clubs"> Шаг2. Выберите клуб </label> <select name="" id="select_clubs"></select>
        <label for="select_hall"> Шаг3. Выберите зал </label> <select name="" id="select_halls"></select>
        <a href="javascript:void(0)" id="add-new-chedule-row"> Добавить новую запись</a>
    </div>
    <table id="schedule_table">
        <tr>
            <th>Время</th>
            <th>Понедельник</th>
            <th>Вторник</th>
            <th>Среда</th>
            <th>Четверг</th>
            <th>Пятница</th>
            <th>Суббота</th>
            <th>Воскресенье</th>
            <th></th>
        </tr>
        <tr class="db_add_row">
            <td><input type="text" name="sched_time" class="timePicker"/></td>
            <td>
                <select class="sched_1">
                    <?php echo $programs_options; ?>
                </select>
            </td>
            <td>
                <select class="sched_2">
                    <?php echo $programs_options; ?>
                </select>
            </td>
            <td>
                <select class="sched_3">
                    <?php echo $programs_options; ?>
                </select>
            </td>
            <td>
                <select class="sched_4">
                    <?php echo $programs_options; ?>
                </select>
            </td>
            <td>
                <select class="sched_5">
                    <?php echo $programs_options; ?>
                </select>
            </td>
            <td>
                <select class="sched_6">
                    <?php echo $programs_options; ?>
                </select>
            </td>
            <td>
                <select class="sched_7">
                    <?php echo $programs_options; ?>
                </select>
            </td>
            <td>
                <a href="javascript:void(0)" class="save_sched_to_db">Save</a>
            </td>
        </tr>
    </table>
    <table id="schedule_table1">
    </table>
</div>
<?php /* <script type='text/javascript'>
  var year = new Date().getFullYear();
  var month = new Date().getMonth();
  var day = new Date().getDate();

  var eventData = {
  events : [
  {'id':1, 'start': new Date(year, month, day, 12), 'end': new Date(year, month, day, 13, 35),'title':'Lunch with Mike'},
  {'id':2, 'start': new Date(year, month, day, 14), 'end': new Date(year, month, day, 14, 45),'title':'Dev Meeting'},
  {'id':3, 'start': new Date(year, month, day + 1, 18), 'end': new Date(year, month, day + 1, 18, 45),'title':'Hair cut'},
  {'id':4, 'start': new Date(year, month, day - 1, 8), 'end': new Date(year, month, day - 1, 9, 30),'title':'Team breakfast'},
  {'id':5, 'start': new Date(year, month, day + 1, 14), 'end': new Date(year, month, day + 1, 15),'title':'Product showcase'}
  ]
  };

  jQuery(document).ready(function($) {
  $('#calendar').weekCalendar({
  timeslotsPerHour: 6,
  timeslotHeigh: 30,
  hourLine: true,
  data: eventData,
  height: function($calendar) {
  return $(window).height() - $('h1').outerHeight(true);
  },
  eventRender : function(calEvent, $event) {
  if (calEvent.end.getTime() < new Date().getTime()) {
  $event.css('backgroundColor', '#aaa');
  $event.find('.time').css({'backgroundColor': '#999', 'border':'1px solid #888'});
  }
  },
  eventNew: function(calEvent, $event) {
  displayMessage('<strong>Added event</strong><br/>Start: ' + calEvent.start + '<br/>End: ' + calEvent.end);
  alert('You\'ve added a new event. You would capture this event, add the logic for creating a new event with your own fields, data and whatever backend persistence you require.');
  },
  eventDrop: function(calEvent, $event) {
  displayMessage('<strong>Moved Event</strong><br/>Start: ' + calEvent.start + '<br/>End: ' + calEvent.end);
  },
  eventResize: function(calEvent, $event) {
  displayMessage('<strong>Resized Event</strong><br/>Start: ' + calEvent.start + '<br/>End: ' + calEvent.end);
  },
  eventClick: function(calEvent, $event) {
  displayMessage('<strong>Clicked Event</strong><br/>Start: ' + calEvent.start + '<br/>End: ' + calEvent.end);
  },
  eventMouseover: function(calEvent, $event) {
  displayMessage('<strong>Mouseover Event</strong><br/>Start: ' + calEvent.start + '<br/>End: ' + calEvent.end);
  },
  eventMouseout: function(calEvent, $event) {
  displayMessage('<strong>Mouseout Event</strong><br/>Start: ' + calEvent.start + '<br/>End: ' + calEvent.end);
  },
  noEvents: function() {
  displayMessage('There are no events for this week');
  }
  });

  function displayMessage(message) {
  $('#message').html(message).fadeIn();
  }

  $('<div id="message" class="ui-corner-all"></div>').prependTo($('body'));
  });

  </script>
  <div id='calendar'></div> */
?>
</div>