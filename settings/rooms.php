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
//a

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
require_once(dirname(dirname(__FILE__)) . '/../../config.php'); //required
require_once($CFG->dirroot.'/local/bookingrooms/settings/rooms_form.php');
require_once($CFG->dirroot.'/local/bookingrooms/settings/settings_tables.php');

//códe to set context, url, layout
global $PAGE, $CFG, $OUTPUT, $DB;
require_login();
$url = new moodle_url('/local/bookingrooms/settings/rooms.php'); 
$context = context_system::instance();//context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');

//Capabilities
if(!has_capability('local/bookingrooms:administration', $context)) {
		print_error(get_string('invalidaccess','local_bookingrooms'));
}
			
//rescue the  ACTION, can be: view, edit, delete, add, create, inform
$action = optional_param('action', 'view', PARAM_TEXT);

// check if campus and buildings exist, because there can not be rooms without them.
if(!$DB->get_records('bookingrooms_rooms')){
	$campus=1;
	$buildings=1;
	if(!$DB->get_records('bookingrooms_buildings')){	
		$buildings=0;
		if(!$DB->get_records('bookingrooms_campus')){		
			$campus=0;
		}	
	}
	//if they are not buildings, send the Action nobuildings, this send us to create campus and the buildings to have the conditions to create rooms.
	if($action=='view'){
		$action = "nobuildings";
	}
}

//implement Action edit
//let us edit the atributes of a created room
if($action == 'edit'){
	$idroom = required_param('idroom', PARAM_INT);
	$prevaction = optional_param('prevaction', 'view', PARAM_TEXT);
	$room = $DB->get_record('bookingrooms_rooms', array('id'=>$idroom));
	$building = $DB->get_record('bookingrooms_buildings', array('id'=>$room->buildings_id));
	$campus = $DB->get_record('bookingrooms_campus', array('id'=>$building->campus_id));
	
	$editform = new editroom(null, array('prevaction'=>$prevaction, 'buildingid'=>$building->id));
	if ($editform->is_cancelled()) {
		$action = $prevaction;
	  // if the edition form to have data of the room will be edited .
	} else if ($fromform = $editform->get_data()) {
		//retrieves data entered in edit form
		$room->name = $fromform->changenameroom;
		$room->name_pc = $fromform->changenamepc;
		$room->capaciy = $fromform->cap;
		$room->type = $fromform->roomType;
		//edits the room with the new parameters entered for it
		$DB->update_record('bookingrooms_rooms', $room);		
		$resources = $DB->get_records('bookingrooms_resources');		
		foreach($resources as $resource){				
			$conditional = $resource->id;
			$room_id = $DB->get_record('bookingrooms_rooms', array('name'=>$fromform->changenameroom, 'buildings_id'=>$building->id, 'type'=>$fromform->roomType));
			$resourcechange = $DB->get_records('bookingrooms_roomresource', array('rooms_id' => $room_id->id));
			//determine if there was or not the selected resource in the form of editing
			//Is added or not the relationship room- resource
			if($_REQUEST[$conditional] == '1'){		        
				if($DB->get_records('bookingrooms_roomresource', array('resources_id'=>$conditional, 'rooms_id'=>$room_id->id)) == null){
					$DB->insert_record('bookingrooms_roomresource', array('resources_id'=>$resource->id, 'rooms_id'=>$room_id->id));
				}
			}else if($_REQUEST[$conditional] == '0'){				
				if($DB->get_records('bookingrooms_roomresource', array('resources_id'=>$conditional, 'rooms_id'=>$room_id->id)) != null){				
					$room_id = $DB->get_record('bookingrooms_rooms', array('name'=>$fromform->changenameroom));
					$DB->delete_records('bookingrooms_roomresource', array('resources_id'=>$resource->id, 'rooms_id'=>$room_id->id));				
				}				
			}				
		}		
		$action = $prevaction;
	} 
}

//Implement action delete
//delete a existing room 
if($action == 'delete'){
	$idroom= required_param('idroom',PARAM_INT );
	$prevaction = optional_param('prevaction', 'view', PARAM_TEXT);
	if (confirm_sesskey()) {
   		$DB->delete_records('bookingrooms_rooms', array('id'=>$idroom));
   		$DB->delete_records('bookingrooms_reserves', array('rooms_id'=>$idroom));
   		$DB->delete_records('bookingrooms_roomresource', array('rooms_id'=>$idroom));
		$action = $prevaction;
	}else{
		print_error("ERROR");
	}
	
}

//Implementation action create
//asks the number of rooms to create and then add redicciona to action
if($action == 'create'){
	$createroomsform = new createRoomsTwo();
	if ($createroomsform->is_cancelled()) {
		$action = 'view';//($redirecturl);
	} else if ($fromform = $createroomsform->get_data()) {
			// send redirects and url parameters
			$redirecturl = new moodle_url('rooms.php', array('action'=>'add', 'campus'=>$fromform->campusbuilding, 'rooms'=>$fromform->room, 'type'=> $fromform->roomType));
			redirect($redirecturl);
	}
}

//implementation of the action create
//allows you to add the amount of rooms, previamante reported in the action to create
if($action == 'add'){	
	$buildingid = optional_param('campus', 0, PARAM_INT);
	$roomType = optional_param('type', 1, PARAM_INT);
	if($building = $DB->get_record('bookingrooms_buildings', array('id'=>$buildingid))){
		$campus = $DB->get_record('bookingrooms_campus', array('id'=>$building->campus_id));
	}	
	$room = optional_param('rooms', 0, PARAM_INT);
	$addroomsform = new createrooms(null, array('room'=>$room, 'campusbuilding'=>$buildingid, 'type'=>$roomType));
	
	$redirecturl = new moodle_url('index.php', array('action'=>'addrooms'));
	
	if ($addroomsform->is_cancelled()) {
		$action= 'view';
		
	} else if ($fromform = $addroomsform->get_data()) {
		//action = view report validates the information entered for verification of rooms to further add to the DDBB
		
		
		/*// Before checking whether the room here already existed , but added to the DDBB
		$nocreate = '';
		var_dump($addroomsform);
		for ($i = 0; $i < $fromform->number; $i++) {
			$nroom="room".$i;
			$pc="pc".$i;
			$ntype = "cap$i";
			$nres = "res$i";
			if($DB->get_record('bookingrooms_rooms', Array('buildings_id'=>$fromform->building,'name'=>$_REQUEST[$nroom], 'type'=>$fromform->typeRoom))){
				$nocreate.= "-".$_REQUEST[$nroom]." room no create because the name was used in that campus<br>";
			}else{
 				$DB->insert_record('bookingrooms_rooms',Array('id'=>'','name'=>$_REQUEST[$nroom],'name_pc'=>$_REQUEST[$pc],'buildings_id'=>$fromform->building, 'type'=>$fromform->typeRoom, 'capacity'=>$_REQUEST[$ntype]));
				$o.= "-".$_REQUEST[$room]." room created correctly <br> ";
			
				$resources = $DB->get_records('bookingrooms_resources');
				foreach($resources as $resource){
					
				    $conditional = $i.$resource->id;
					if($_REQUEST[$conditional] == '1'){
						
						$room_id = $DB->get_record('bookingrooms_rooms', array('name'=>$_REQUEST[$nroom], 'buildings_id'=>$fromform->building, 'type'=>$fromform->typeRoom));
						$DB->insert_record('bookingrooms_roomresource', array('resources_id'=>$resource->id, 'rooms_id'=>$room_id->id));
					}
				}
			}
		}*/
		$action = "report";		 
	}	
}

//Implement the action view
//generates a table all existing rooms
if($action == 'view'){
	$table= tables::getrooms(); 
	
}else if($action == 'viewbybuilding'){
	
	$buildingid = optional_param('building', NULL, PARAM_INT);
	$building = $DB->get_record('bookingrooms_buildings', array('id'=>$buildingid));
	$campus = $DB->get_record('bookingrooms_campus', array('id'=>$building->campus_id));
 	
	$table= tables::getrooms($buildingid); 
}

//View of the Actions
//**************************************************************************************************************************************************
if($action == 'view'){
	$o = '';
	
	$toprow = array();
	$toprow[] = new tabobject(get_string('sites', 'local_bookingrooms'), new moodle_url('/local/bookingrooms/settings/campus.php'), get_string('places', 'local_bookingrooms'));
	$toprow[] = new tabobject(get_string('buildings', 'local_bookingrooms'), new moodle_url('/local/bookingrooms/settings/buildings.php'), get_string('buildings', 'local_bookingrooms'));
	$toprow[] = new tabobject(get_string('studyrooms', 'local_bookingrooms'), new moodle_url('/local/bookingrooms/settings/rooms.php'), get_string('rooms', 'local_bookingrooms'));
	$toprow[] = new tabobject(get_string('resources', 'local_bookingrooms'), new moodle_url('/local/bookingrooms/settings/resources.php'), get_string('resources', 'local_bookingrooms'));
	
	$title = get_string('seeandmodrooms', 'local_bookingrooms');
	$PAGE->navbar->add(get_string('roomsreserve', 'local_bookingrooms'));
	$PAGE->navbar->add(get_string('adjustments', 'local_bookingrooms'));
	$PAGE->navbar->add(get_string('seeandmodrooms', 'local_bookingrooms'), 'rooms.php');
	$PAGE->set_title($title);
	$PAGE->set_heading($title);
	
	$o.= $OUTPUT->header();
	//$o.= $OUTPUT->heading("rooms of study");
	$o .= $OUTPUT->tabtree($toprow, get_string('studyrooms', 'local_bookingrooms'));
	$url = new moodle_url("rooms.php", array('action'=>'create'));
	if(isset($nocreate)){
		$o.=$nocreate;
	}
	$o.= $OUTPUT->single_button($url, get_string('createnewrooms', 'local_bookingrooms'));

	//echo html_writter::table($table);
	
	
	$o .= html_writer::table($table);
	$o.= $OUTPUT->single_button($url, get_string('createnewrooms', 'local_bookingrooms'));
	$o .= $OUTPUT->footer();
}else if($action == 'viewbybuilding'){

	$o = '';
	$title = get_string('seeandmodrooms', 'local_bookingrooms');
	$PAGE->navbar->add(get_string('roomsreserve', 'local_bookingrooms'));
	$PAGE->navbar->add(get_string('adjustments', 'local_bookingrooms'));
	$PAGE->navbar->add(get_string('seeandmodrooms', 'local_bookingrooms'), 'rooms.php');
	$PAGE->set_title($title);
	$PAGE->set_heading($title);
	$o.= $OUTPUT->header();
	$o.= $OUTPUT->heading(get_string('roomsreserve', 'local_bookingrooms'));
		
	$secondtitle = "<h4>".get_string('campus', 'local_bookingrooms').": ".$campus->name."</h4><h4>".get_string('building', 'local_bookingrooms').": ".$building->name."</h4>";
	
	$o.= "<h2>".$secondtitle."</h2><br>";
	$o .= html_writer::table($table);
    $o.= "<hr>";
    $o.= $OUTPUT->single_button('buildings.php', get_string('backtobuildings', 'local_bookingrooms'));
	$o .= $OUTPUT->footer();
	
}else if($action == 'create'){
	$o= '';
	$title = get_string('roomscreates', 'local_bookingrooms');
	$PAGE->navbar->add(get_string('roomsreserve', 'local_bookingrooms'));
	$PAGE->navbar->add(get_string('adjustments', 'local_bookingrooms'));
	$PAGE->navbar->add(get_string('seeandmodrooms', 'local_bookingrooms'), '');
	$PAGE->navbar->add($title, 'rooms.php?action=create');
	$PAGE->set_title($title);
	$PAGE->set_heading($title);
	$o .= $OUTPUT->header();
	$o .= $OUTPUT->heading($title);
	ob_start();
    $createroomsform->display();
    $o .= ob_get_contents();
    ob_end_clean();
	$o .= $OUTPUT->footer();
}else if($action == 'add'){
	$o = '';
	
	$title = get_string('roomscreates', 'local_bookingrooms');
	$PAGE->navbar->add(get_string('roomsreserve', 'local_bookingrooms'));
	$PAGE->navbar->add(get_string('adjustments', 'local_bookingrooms'));
	$PAGE->navbar->add(get_string('seeandmodrooms', 'local_bookingrooms'), 'rooms.php');
	$PAGE->navbar->add($title,'rooms.php?action=create' );
	$PAGE->set_title($title);
	$PAGE->set_heading($title);
	$o .= $OUTPUT->header();
	$o .= $OUTPUT->heading($title);

	$secondtitle ="<h4>".get_string('campus', 'local_bookingrooms').": ".$campus->name."</h4><h4>".get_string('building', 'local_bookingrooms').": ".$building->name."</h4>";
	$o .="<h2>".$secondtitle."</h2><br>";


	ob_start();
    $addroomsform->display();
    $o .= ob_get_contents();
    ob_end_clean();
    $o .= $OUTPUT->footer();
	
}else if($action == 'edit'){
	$o= '';
	$title = get_string('roomedit', 'local_bookingrooms');
	$PAGE->navbar->add(get_string('roomsreserve', 'local_bookingrooms'));
	$PAGE->navbar->add(get_string('adjustments', 'local_bookingrooms'));
	$PAGE->navbar->add(get_string('seeandmodrooms', 'local_bookingrooms'), 'rooms.php');
	$PAGE->navbar->add($title, '');
	$PAGE->set_title($title);
	$PAGE->set_heading($title);
	$o .= $OUTPUT->header();
	$o .= $OUTPUT->heading(get_string('roomedit', 'local_bookingrooms'));
	$o .= "<h4>".get_string('campus', 'local_bookingrooms').": $campus->name </h4>";
	$o .= "<h4>".get_string('building', 'local_bookingrooms').": $building->name </h4>";
	$o .= "<br>";
	 	
	ob_start();
    $editform->display();
    $o .= ob_get_contents();
    ob_end_clean();
	 		
	$o .= $OUTPUT->footer();
	

}else if($action == 'report'){ // new action, inform about the result of the creted rooms 
	$o = '';
	
	$title = get_string('report', 'local_bookingrooms');
	$PAGE->navbar->add(get_string('roomsreserve', 'local_bookingrooms'));
	$PAGE->navbar->add(get_string('adjustments', 'local_bookingrooms'));
	$PAGE->navbar->add(get_string('seeandmodrooms', 'local_bookingrooms'), 'rooms.php');
	$PAGE->navbar->add(get_string('roomscreates', 'local_bookingrooms'),'rooms.php?action=create' );
	$PAGE->navbar->add($title,'rooms.php?action=report' );
	$PAGE->set_title($title);
	$PAGE->set_heading($title);
	$o .= $OUTPUT->header();
	$o .= $OUTPUT->heading($title);
	
	
	$secondtitle ="<h4>".get_string('campus', 'local_bookingrooms').": ".$fromform->namecampus."</h4><h4>".get_string('building', 'local_bookingrooms').": ".$fromform->namebuilding."</h4>";
	$o .="<h2>".$secondtitle."</h2><br>";

	ob_start();	
	//creates a table that reports on each room that try to create
	//reports the error that allowed creating it, or if he can successfully create
	$table = new html_table();
	$table->head = array(get_string('room', 'local_bookingrooms'),get_string('capacity', 'local_bookingrooms'),get_string('created', 'local_bookingrooms'),get_string('report', 'local_bookingrooms')); //*******
	// through all the new rooms
	for ($i = 0; $i < $fromform->number; $i++) {
		$nroom="room".$i;
		$pc="pc".$i;
		$ntype = "cap$i";
		$nres = "res$i";
		// check enters the room already exists
		if($DB->get_record('bookingrooms_rooms', Array('buildings_id'=>$fromform->building,'name'=>$_REQUEST[$nroom], 'type'=>$fromform->typeRoom))){
			$row = new html_table_row(array($_REQUEST[$nroom], $_REQUEST[$ntype],'No',get_string('nameoftheexisting', 'local_bookingrooms')));
			$table->data[] = $row;
		}else{
			//if the room does not exist, and validates its type and capacity is created
			if(($_REQUEST[$ntype]+1)>1 || $_REQUEST[$ntype]==0){ //if the capacity of the room is an integer , create
				$DB->insert_record('bookingrooms_rooms',Array('id'=>'','name'=>$_REQUEST[$nroom],'name_pc'=>$_REQUEST[$pc],'buildings_id'=>$fromform->building, 'type'=>$fromform->typeRoom, 'capaciy'=>$_REQUEST[$ntype]));
				$row = new html_table_row(array($_REQUEST[$nroom], $_REQUEST[$ntype],get_string('yes', 'local_bookingrooms'),get_string('clasroomsuccesscreated', 'local_bookingrooms')));
				$table->data[] = $row;	
				$resources = $DB->get_records('bookingrooms_resources');
				// relations creates room -resources
				foreach($resources as $resource){					
					$conditional = $i.$resource->id;
					if($_REQUEST[$conditional] == '1'){	
						$room_id = $DB->get_record('bookingrooms_rooms', array('name'=>$_REQUEST[$nroom], 'buildings_id'=>$fromform->building, 'type'=>$fromform->typeRoom));
						$DB->insert_record('bookingrooms_roomresource', array('resources_id'=>$resource->id, 'rooms_id'=>$room_id->id));
					}
				}
			}else{
				$row = new html_table_row(array($_REQUEST[$nroom],$_REQUEST[$ntype], 'No',get_string('roomcapacityacepted', 'local_bookingrooms')));
				$table->data[] = $row;
			}
		}
	}
	$url = new moodle_url("rooms.php", array('action'=>'create'));
	$url2 = new moodle_url("rooms.php", array('action'=>'view'));
	$row = new html_table_row(array('','','',''));
	$table->data[] = $row;
	$row = new html_table_row(array($OUTPUT->single_button($url, get_string('createnewrooms', 'local_bookingrooms')),$OUTPUT->single_button($url2,get_string('next', 'local_bookingrooms')),'',''));
	$table->data[] = $row;
	echo html_writer::table($table);
	$o .= ob_get_contents();
	ob_end_clean();
	$o .= $OUTPUT->footer();


}else if($action == "nobuildings"){
	$o = '';
	$toprow = array();
	$toprow[] = new tabobject(get_string('sites', 'local_bookingrooms'), new moodle_url('/local/bookingrooms/settings/campus.php'), get_string('places', 'local_bookingrooms'));
	$toprow[] = new tabobject(get_string('buildings', 'local_bookingrooms'), new moodle_url('/local/bookingrooms/settings/buildings.php'), get_string('buildings', 'local_bookingrooms'));
	$toprow[] = new tabobject(get_string('studyrooms', 'local_bookingrooms'), new moodle_url('/local/bookingrooms/settings/rooms.php'), get_string('rooms', 'local_bookingrooms'));
	$toprow[] = new tabobject(get_string('resources', 'local_bookingrooms'), new moodle_url('/local/bookingrooms/settings/resources.php'), get_string('resources', 'local_bookingrooms'));
	
	
	$title = get_string('seeandmodrooms', 'local_bookingrooms');
	$PAGE->navbar->add(get_string('roomsreserve', 'local_bookingrooms'));
	$PAGE->navbar->add(get_string('adjustments', 'local_bookingrooms'));
	$PAGE->navbar->add(get_string('seeandmodrooms', 'local_bookingrooms'), 'rooms.php');
	$PAGE->set_title($title);
	$PAGE->set_heading($title);
	
	$o.= $OUTPUT->header();
	$o .= $OUTPUT->tabtree($toprow, get_string('studyrooms', 'local_bookingrooms'));
	$o.= $OUTPUT->heading(get_string('roomsreserve', 'local_bookingrooms'));
	

	if($campus==0){
		$url = new moodle_url("campus.php", array('action'=>'create'));
		$o.= "<center><strong>".get_string('therearenotsites', 'local_bookingrooms')."<strong><center>";
		$o.= $OUTPUT->single_button($url, get_string('campuscreate', 'local_bookingrooms'));
		
	}
	elseif($buildings==0){
		$url = new moodle_url("buildings.php", array('action'=>'create'));
		$o.= "<center><strong>".get_string('therearenotbuildings', 'local_bookingrooms')."<strong><center>";
		$o.= $OUTPUT->single_button($url, get_string('createbuildings', 'local_bookingrooms'));
		
	}
	else{
		
		$url = new moodle_url("rooms.php", array('action'=>'create'));
		$o.= "<center><strong>".get_string('thereisnotrooms', 'local_bookingrooms')."<strong><center>";
		$o.= $OUTPUT->single_button($url, get_string('createrooms', 'local_bookingrooms'));
	
	}
	$o .= $OUTPUT->footer();
	
}else{
	print_error(get_string('invalidaction', 'local_bookingrooms'));
}

echo $o;