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
 * @subpackage bookingrooms
 * @copyright  2015 Sebastian Riveros
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


//page to block students.
//git tests
require_once(dirname(dirname(__FILE__)) . '/../../config.php'); //mandatory
require_once($CFG->dirroot.'/local/bookingrooms/administration/administration_form.php');


global $PAGE, $CFG, $OUTPUT, $DB;
require_login();
$url = new moodle_url('/local/bookingrooms/blocked.php'); 
$context = context_system::instance();//context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');

//Capabilities
//Validates capabilities of the user for him to see the content
//In this case only the admin of the module can access.
if(!has_capability('local/bookingrooms:blocking', $context)) {
		print_error(get_string('invalidaccess','local_bookingrooms'));
}

//Breadcrumbs
$PAGE->navbar->add(get_string('roomsreserve', 'local_bookingrooms'),'book.php');
$PAGE->navbar->add(get_string('users', 'local_bookingrooms'));
$PAGE->navbar->add(get_string('blockstudent', 'local_bookingrooms'),'blocked.php');


//Form to block a student.
$search = new UserSearch(null);
if($fromform = $search->get_data()){
	//Blocks the user in the database
	if($user = $DB->get_record('user',array('email'=>$fromform->email))){
		$record = new stdClass();
		$record->comments = $fromform->commentary;
		$record->student_id = $user->id;
		$record->status = 1;
		$record->date_block = date('Y-m-d');
		$record->id_reserve = ""; 
		
		$DB->insert_record('bookingrooms_blocked', $record);
		$block = true;
	}
}

//loads the page, al least title, head and breadcrumbs.

$title = get_string('blockstudent', 'local_bookingrooms');
$PAGE->set_title($title);
$PAGE->set_heading($title);
echo   $OUTPUT->header();
echo   $OUTPUT->heading($title);


//Depending if the institutional mail is correct, and at the same time
//the user is not blocked, or it it blocked
//the infromation will be deployed to the corresponding success or falure in the operation.
if(isset($blocked)){
	echo get_string('thestudent', 'local_bookingrooms').$user->firstname." ".$user->lastname.get_string('suspendeduntilthe', 'local_bookingrooms').date('d-m-Y', strtotime("+ 3 days"));
	echo $OUTPUT->single_button('blocked.php', get_string('blockagain', 'local_bookingrooms'));
}else{
    $search->display();  
}

echo $OUTPUT->footer();

