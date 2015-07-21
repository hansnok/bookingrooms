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
require_once(dirname(__FILE__) . '/../../config.php'); //obligatorio
require_once($CFG->dirroot.'/local/bookingrooms/administration_form.php');
require_once($CFG->dirroot.'/local/bookingrooms/administration_tables.php');

//Code to set context, url, layout
global $PAGE, $CFG, $OUTPUT, $DB;
require_login();
$url = new moodle_url('/local/bookingrooms/bookinghistory.php'); 
$context = context_system::instance();//context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');



if(!has_capability('local/bookingrooms:bockinginfo', $context)) {
	// TODO: Log unsuccessful attempts for security

	print_error(get_string('INVALID_ACCESS','booking_room'));

}

$action = optional_param('action', 'see', PARAM_TEXT);

//action commentary implementation
// allows the  administrator to add a comment on a reservation
if($action == "comment"){
	$bookingid = required_param('booking', PARAM_INT);
	$commentform = new AdminComment (null, array('bookingid'=>$reservaid));
	
	if($commentform->is_cancelled()){
		$action = "see";
	}else if($fromform = $commentform->get_data()){
		$booking = $DB->get_record('bookingrooms_bookings', array('id'=>bookingid));
		$booking->comment_admin = $fromform->comment;
		$DB->update_record('bookingrooms_bookings', $bookings);
		$action = "see";
		
	}
}
// 'See' action implementation
// Shows a table per page of all the room reservations that are active in decreasing order by date
// with a link that allows you to add a comment or see, if exist, a comment
if($action == "see"){
	$max = 15;
	$page = optional_param('page', 0, PARAM_INT);
	//$bokkings = $DB->get_records('bookingrooms_bookings');
	$bookings = $DB->get_records_sql('select * from {bookingrooms_bookings} where active = 1 order by date_bookings desc');
	$count = count($bookings);
	$totalpages = ceil($count/$max);
	$table = tables::allbookingdata($reservas,$max, $page);
}



//view of the actions: see & comment
$o = '';
$title = get_string('bookinghistory', 'local_bookingrooms');
$PAGE->navbar->add(get_string('roomsreserve', 'local_bookingrooms'));
$PAGE->navbar->add(get_string('adjustments', 'local_bookingrooms'));
$PAGE->navbar->add($title, 'bookinghistory.php');


if($action == "see"){

	$PAGE->set_title($title);
	$PAGE->set_heading($title);
	$o.= $OUTPUT->header();
	$o.= $OUTPUT->heading($title);
	$o.= "version 2013031400";
	$o.= "<right><h4> ".get_string('totalbookings', 'local_bookingrooms')." ".$count."  </h4></right>";
	$o.= "<div class='no-overflow'>";
	$o .= html_writer::table($table);
	$o.= "</div>";
	
	$o .= $OUTPUT->paging_bar($count, $page, $max, 'bookinghistory.php?ver=1&page=');
	
}else if($action == "comment"){
	
	$newtitle = get_string('admincomment', 'local_bookingrooms');
	$PAGE->navbar->add($newtitle, '');
	$PAGE->set_title($newtitle);
	$PAGE->set_heading($newtitle);
	$o.= $OUTPUT->header();
	$o.= $OUTPUT->heading($newtitle);
	ob_start();
    $commentform->display();
    $o .= ob_get_contents();
    ob_end_clean();
}
$o .= $OUTPUT->footer();
echo $o;
