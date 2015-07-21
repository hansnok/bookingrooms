<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * 
 *
 * @package    local
 * @subpackage reservasalas
 * @copyright  2013 Marcelo Epuyao
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php'); //Mandatory
require_once($CFG->dirroot.'/local/bookingrooms/administration_form.php');
require_once($CFG->dirroot.'/local/bookingrooms/administration_tables.php');
//fourth test
//Code to set context, url, layout
global $PAGE, $CFG, $OUTPUT, $DB;
require_login();
$url = new moodle_url('/local/bookingrooms/estadisticas.php');
$context = context_system::instance();//context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');

if(!has_capability('local/bookingrooms:bockinginfo', $context)) {
	// TODO: Log unsuccessful attempts for security
	print_error(get_string('INVALID_ACCESS','booking_room'));

}

$o = '';
$title = get_string('statistics', 'local_bookingrooms');
$PAGE->navbar->add(get_string('roomsreserve', 'local_bookingrooms'));
$PAGE->navbar->add(get_string('adjustments', 'local_bookingrooms'));
$PAGE->navbar->add($title, 'estadisticas.php');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$o.= $OUTPUT->header();
$o.= $OUTPUT->heading($title);
$now = time();

$todaydate = date('Y-m-d'); 


$module = module_hour($now);

$table = new html_table();
//Total reservation made
$totalbookings = $DB->count_records('bookingrooms_bookings');
$totalvalidated = $DB->count_records('bookingrooms_bookings', array('active'=>1));
$table->data[] = array(get_string('totalbookings', 'local_bookingrooms'), $totalvalidated);

//Number of reservations made by the admin
$admins = get_admins();
$bookingsdeadmin = 0;
foreach ($admins as $admin){
	$count = $DB->count_records_select('bookingrooms_booking', "student_id=$admin->id AND active=1");
	$bookingsdeadmin += $count;
}
$table->data[] = array(get_string('reservationsadm', 'local_bookingrooms'), $bookingsdeadmin);

//Number of reserbations by students
$table->data[] = array(get_string('reservesstudents', 'local_bookingrooms'),$totalvalidated-$bookingsdeadmin);

//total cancelled reservations
$totalcancelled = $DB->count_records('bookingrooms_bookings', array('active'=>0));
$table->data[] = array(get_string('totalcancelled', 'local_bookingrooms'), $totalcancelled."/".$totalbookings);

//confirmed reservatoins
$totalconfirmed= $DB->count_records('bookingrooms_bookings', array('confirmed'=>1, 'active'=>1));
$table->data[] = array(get_string('totalconfirm', 'local_bookingrooms'), $totalconfirmed);

//reservations to be confirmed
$totaltobeconfirmed = $DB->get_records_sql("select * from {bookingrooms_bookings} where confirmed = 0 AND date_booking > '$todaydate' AND active = 1");
$totaltobeconfirmedtoday = $DB->get_records_sql("select * from {bookingrooms_booking} where confirmed = 0 AND date_booking = '$todaydate' AND module > $module AND active = 1");
$table->data[] = array(get_string('totalforconfirm', 'local_bookingrooms'), count($totaltobeconfirmed) + count($totaltobeconfirmedtoday));

//punished reservations
$totalpunished = $DB->get_records_sql("select * from {bookingrooms_booking} where confirmed = 0 AND date_booking > 0 AND fecha_reserva < '$fechahoy' AND activa = 1");
$totalcastigadashoy = $DB->get_records_sql("select * from {reservasalas_reservas} where confirmado = 0 AND fecha_reserva = '$fechahoy' AND modulo <= $modulo AND activa = 1");
$table->data[] = array(get_string('totalpunished', 'local_reservasalas'), count($totalcastigadas) + count($totalcastigadashoy));

//total alumnos bloqueados
$totalbloqueados= $DB->get_records_sql("select * from {reservasalas_bloqueados} group by alumno_id");
$table->data[] = array(get_string('studentblockedhistory', 'local_reservasalas'), count($totalbloqueados));

//alumnos bloqueados actual
$totalbloqueados= $DB->get_records_sql("select * from {reservasalas_bloqueados} where estado=1 group by alumno_id");
$table->data[] = array(get_string('studentblockpresent', 'local_reservasalas'), count($totalbloqueados));



$o.= html_writer::table($table);

$o .= $OUTPUT->footer();
echo $o;