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
require_once($CFG->dirroot.'/local/reservasalas/administration_tables.php');


global $PAGE, $CFG, $OUTPUT, $DB;
//Check that the user that access the page is loged in to the sistem 
require_login();
$url = new moodle_url('/local/bookingrooms/unblocked.php'); 
$context = context_system::instance();//context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');

//Capabilities
//Validates capabilities of the user for him to see the content
//In this case only the admin of the module can access.
if(!has_capability('local/bookingrooms:blocking', $context)) {
		print_error(get_string('INVALID_ACCESS','booking_room'));
}
//Migas de pan
$PAGE->navbar->add(get_string('roomsreserve', 'local_bookingrooms'));
$PAGE->navbar->add(get_string('users', 'local_bookingrooms'));
$PAGE->navbar->add(get_string('unblockstudent', 'local_bookingrooms'));

//$PAGE->set_title('UAI Webcursos');
//$PAGE->set_heading('UAI Webcursos');

//Form to unblock a student
$unblockform = new UnblockStudentForm();

if($fromform = $unblcokform->get_data()){
	//if the form is sent, the student exists and it is blocked, then it will be unblocked.
	//else it will show a mesage according to the error.
	if($user = $DB->get_record('user',array('username'=>$fromform->user))){
		$datenow = date('Y-m-d');
		if($block = $DB->get_record('bookingrooms_blocked',array('student_id'=>$user->id,'status'=>1))){//('reservasalas_bloqueados', array('alumno_id'=>$user->id));
			$record = new stdClass();
			$record->id = $block->id;
			$record->id_booking = $block->id_booking;
			$record->comments = $fromform->comment;
			$record->status = 0;
	
			$DB->update_record('bookingrooms_blocked', $record);
			$unblock = true;
		}else{
			print_error(get_string('noblock', 'local_bookingrooms'));
		}		
	}else{
		print_error(get_string('noexist', 'local_bookingrooms'));
	}
}

//the page loads al least the titule, head anda breadcrumbs.
$o = '';
$title = get_string('unblockstudent', 'local_bookingrooms');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$o .= $OUTPUT->header();
$o .= $OUTPUT->heading($title);

//if its the first time the page is loaded it will show the unblock students form,
//if the info is already entered and it's correct a unblock mesage will appear and
//if it's incorrect the corresponding error will appear 
if(isset($unblock)){
	$o.= get_string('thestudent', 'local_bookingrooms').$user->firstname." ".$user->lastname.get_string('beenunlocked', 'local_bookingrooms');
	$o .= $OUTPUT->single_button('unblock.php', get_string('unblockagain', 'local_bookingrooms'));
}else{
	ob_start();
    $unblockform->display();
    $o .= ob_get_contents();
    ob_end_clean();
}
$o .= $OUTPUT->footer();
echo $o;
