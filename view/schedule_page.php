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
    <?php 
    $data = WP_Kivi_Schedule_Plugin::fetch_schedule_data();
    ?>
    <table id="schedule_table1">
    </table>
</div>
</div>