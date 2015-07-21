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
//third git test
//code for setting context, url, layout
global $PAGE, $CFG, $OUTPUT, $DB;
require_login();
$url = new moodle_url('/local/bookingrooms/diagnose.php'); 
$context = context_system::instance();//context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');

if(!has_capability('local/bookingrooms:bockinginfo', $context)) {
	// TODO: Log unsuccessful attempts for security
	print_error(get_string('INVALID_ACCESS','booking_room'));

}

$o = '';
$title = get_string('diagnose', 'local_bookingrooms');
$PAGE->navbar->add(get_string('roomsreserve', 'local_bookingrooms'));
$PAGE->navbar->add(get_string('adjustments', 'local_bookingrooms'));
$PAGE->navbar->add($title, 'diagnose.php');

$PAGE->set_title($title);
$PAGE->set_heading($title);
$o.= $OUTPUT->header();
$o.= $OUTPUT->heading($title);
	


$timenow = time();
$datenow = date('Y-m-d');
$nownow = date('H:i:s');


//list($hour,$minute) = explode(":",$modulehour['start']);

// Creates a table that shows the version of the module, date, hour and actual unix hour.
// Also information of the starting and ending hour of the module
$table = new html_table();
$table->data[] = array(get_string('version', 'local_bookingrooms'), '2013050400');
$table->data[] = array(get_string('datediagnostic', 'local_bookingrooms'), $datenow);
$table->data[] = array(get_string('hour', 'local_bookingrooms'), $nownow);
$table->data[] = array(get_string('unixtime', 'local_bookingrooms'), $timenow);


$o.= "<div class='no-overflow'>";
$o .= html_writer::table($table);
$o.= "</div>";


$o .= $OUTPUT->footer();
echo $o;



