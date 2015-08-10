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
* @copyright  2014 Francisco García Ralph (francisco.garcia.ralph@gmail.com)
* 					Nicolás Bañados Valladares (nbanados@alumnos.uai.cl)
*             2015 Sebastian Riveros(sriveros@alumnos.uai.cl)
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
require_once(dirname(dirname(__FILE__)) . '/../../config.php'); //obligatorio
require_once($CFG->dirroot.'/local/bookingrooms/settings/campus_form.php');
require_once($CFG->dirroot.'/local/bookingrooms/settings/settings_tables.php');

//códe to ser the context, url, layout
global $PAGE, $CFG, $OUTPUT, $DB;
require_login();

$placeid = optional_param("idplace",null,PARAM_INT);
$action = optional_param("action","view",PARAM_TEXT);

$url = new moodle_url('/local/bookingrooms/settings/campus.php');
$context = context_system::instance();//context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');

//Capabilities
if(!has_capability('local/bookingrooms:administration', $context)) {
	print_error(get_string('invalidaccess','local_bookingrooms'));
}
	
//rescue the ACTION, can ve : view, edit, delete, add, create
$action = optional_param('action', 'view', PARAM_TEXT);


//Control of the Actions
if($action == 'create'){

	$campusform = new formCampus();
	if ($campusform->is_cancelled()) {
		$action= 'view';
			
	} else if ($fromform = $campusform->get_data()) {
		$record = new stdClass();
		$record->name = $fromform->campus;
		$DB->insert_record('bookingrooms_campus', $record);
		$action = "view";
	}


}

if($action == 'edit'){
	if ($placeid == null){
		print_error(get_string('noexistcampus','local_bookingrooms'));	
    	$action = "view";
}else{
	if( $campus = $DB->get_record('bookingrooms_campus', array('id'=>$placeid)) ){
	   $editform = new formeditplaces(null, array( 'idplace'=>$placeid) );
	   $defaultdata = new stdClass();
	   $defaultdata->name = $campus->name;
	   $editform->set_data($defaultdata);
	   if( $editform->is_cancelled() ){
	   	$action = "view";
	   } 
	   else if ($fromform = $editform->get_data()) {
            $record = new stdClass();
	   	    $record->id = $fromform->idplace;
			$record->name = $fromform->name;
			$DB->update_record('bookingrooms_campus', $record);
			$action = "view";
	   }
	}else{
		print_error(get_string('noexist','local_bookingrooms'));
		$action="view";
	}
}
}


if($action == 'delete'){

	$idcampus= required_param('idplace',PARAM_INT );
	if (confirm_sesskey()) {
		$buildings = $DB->get_records('bookingrooms_buildings',  array('campus_id'=>$idcampus));
		foreach ($buildings as $building){
			$rooms = $DB->get_records('bookingrooms_rooms', array('buildings_id'=>$building->id));
			foreach ($rooms as $room){
				$DB->delete_records('bookingrooms_reserves', array('rooms_id'=>$room->id));
			}
			$DB->delete_records('bookingrooms_rooms', array('buildings_id'=>$building->id));
				
		}
		$DB->delete_records('bookingrooms_buildings', array('campus_id'=>$idcampus));
		$DB->delete_records('bookingrooms_campus', array('id'=>$idcampus));

		$action = "view";
	}else{
		print_error("ERROR");
	}

}
if($action == 'view'){

	$table= tables::getPlacesAdminRoom();

}

//View of the Actions
//**************************************************************************************************************************************************
if($action == 'edit'){

	$o= '';
	$title = get_string('editcampus', 'local_bookingrooms');
	$PAGE->navbar->add(get_string('roomsreserve', 'local_bookingrooms'));
	$PAGE->navbar->add(get_string('adjustments', 'local_bookingrooms'));
	$PAGE->navbar->add(get_string('seeandmodplaces', 'local_bookingrooms'), 'campus.php');
	$PAGE->navbar->add($title, '');
	$PAGE->set_title($title);
	$PAGE->set_heading($title);
	$o .= $OUTPUT->header();
	$o .= $OUTPUT->heading(get_string('editcampus', 'local_bookingrooms'));
	if(isset($placename->name)){
		$o .= "<h4>".get_string('campus', 'local_bookingrooms').": $placename->name </h4>";
	}
	$o .= "<br>";

	ob_start();
	$editform->display();
	$o .= ob_get_contents();
	ob_end_clean();

	$o .= $OUTPUT->footer();

}else if($action == 'view'){
	$o = '';

	$toprow = array();
	$toprow[] = new tabobject(get_string('sites', 'local_bookingrooms'), new moodle_url('/local/bookingrooms/settings/campus.php'), get_string('places', 'local_bookingrooms'));
	$toprow[] = new tabobject(get_string('buildings', 'local_bookingrooms'), new moodle_url('/local/bookingrooms/settings/buildings.php'), get_string('buildings', 'local_bookingrooms'));
	$toprow[] = new tabobject(get_string('studyrooms', 'local_bookingrooms'), new moodle_url('/local/bookingrooms/settings/rooms.php'), get_string('rooms', 'local_bookingrooms'));
	$toprow[] = new tabobject(get_string('resources', 'local_bookingrooms'), new moodle_url('/local/bookingrooms/settings/resources.php'), get_string('resources', 'local_bookingrooms'));

	$title = get_string('seeandmodplaces', 'local_bookingrooms');
	$PAGE->navbar->add(get_string('roomsreserve', 'local_bookingrooms'));
	$PAGE->navbar->add(get_string('adjustments', 'local_bookingrooms'));
	$PAGE->navbar->add($title, 'campus.php');
	$PAGE->set_title($title);
	$PAGE->set_heading($title);

	$o.= $OUTPUT->header();
	//$o.= $OUTPUT->heading($title);
	$o .= $OUTPUT->tabtree($toprow, get_string('sites', 'local_bookingrooms'));
	$url = new moodle_url("campus.php", array('action'=>'create'));

	$o .= html_writer::table($table);
	$o.= $OUTPUT->single_button($url, get_string('createnewplace', 'local_bookingrooms'));
	$o .= $OUTPUT->footer();
}else if($action = "create"){
	$o= '';
	$title = get_string('campuscreate', 'local_bookingrooms');
	$PAGE->navbar->add(get_string('roomsreserve', 'local_bookingrooms'));
	$PAGE->navbar->add(get_string('adjustments', 'local_bookingrooms'));
	$PAGE->navbar->add(get_string('places', 'local_bookingrooms'), 'campus.php');
	$PAGE->navbar->add($title);
	$PAGE->set_title($title);
	$PAGE->set_heading($title);
	$o .= $OUTPUT->header();
	$o .= $OUTPUT->heading($title);
	ob_start();
	$campusform->display();
	$o .= ob_get_contents();
	ob_end_clean();
	$o .= $OUTPUT->footer();
}else{
	print_error(get_string('invalidaction', 'local_bookingrooms'));
}
echo $o;
