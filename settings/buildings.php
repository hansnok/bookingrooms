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

require_once(dirname(dirname(__FILE__)) . '/../../config.php'); //mandatory
require_once($CFG->dirroot.'/local/bookingrooms/settings/buildings_form.php');
require_once($CFG->dirroot.'/local/bookingrooms/settings/settings_tables.php');
require_once($CFG->dirroot.'/local/bookingrooms/lib.php');

//Code to set context, url, layout
global $PAGE, $CFG, $OUTPUT, $DB;
require_login();
$url = new moodle_url('/local/bookingrooms/settings/buildings.php'); 
$context = context_system::instance();//context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');

//Capabilities
if(!has_capability('local/bookingrooms:administration', $context)) {
		print_error(get_string('invalidaccess','local_bookingrooms'));
}

//To advert modules bad writes
$warning = '';

//Resscue the action, can ve: view, edit, delete, add, create.
//Every ACTION would show a version of the page that the user is going to use. 
//by default the ACTION is view, so, is going to show a table with all the modules
$action = optional_param('action', 'view', PARAM_TEXT);

//Checks based there since the buildings are within the campus
//Example  campus peñalolen has building A , B , C , D and E.
if(!$DB->get_records('bookingrooms_buildings')){
	$campus=1;
	if(!$DB->get_records('bookingrooms_campus')){
		
		$campus=0;
	}
	if($action=='view'){
		// ACTION changes if there is no seat to show a message according to the situation
	$action = "nocampus";
	}
}

// Captures ACTION edit , this was modified via user action .
// To edit a building already created
if($action == 'edit'){   
	// Retrieve hidden variables sent by the form
	$idbuilding= optional_param('building','0',PARAM_INT);
	$prevaction = optional_param('prevaction', 'view', PARAM_TEXT);
	if($idbuilding!=0){
		$buildingdata = $DB->get_record('bookingrooms_buildings', array('id'=>$idbuilding));
    	$placedata = $DB->get_record('bookingrooms_campus', array('id'=>$buildingdata->campus_id));
		$record = $DB->get_record('bookingrooms_buildings', array('id'=>$idbuilding));
	}else{
		$buildingdata = new stdClass();
		$buildingdata->name="";		
	}	
	// Retrieve the campus
	$campus=$DB->get_records('bookingrooms_campus'); //get the campus
	$campusArray=array();
	foreach($campus as $key=>$value){ //travel the campus to create the array
		$campusArray[$value->id]=$value->name;
	}
	
	//Recover the modules
	$moduledatas = $DB->get_records('bookingrooms_modules', array('building_id'=>$idbuilding));
	$stringmodules=array();
	foreach($moduledatas as $key=>$value){	
		$stringmodules[] ='#'.$value->name_module.','.$value->hour_start.'-'.$value->hour_end.'';	
	}
	$editform = new formBuildingsEdit(null, array('prevaction'=>$prevaction, 'idbuilding'=>$idbuilding, 'buildingname'=>$buildingdata->name,'place'=>$campusArray, 'modules'=>$stringmodules));
// If the issue is canceled, showing the normal view . Table with all buildings
	if ($editform->is_cancelled()) {
		$action = 'view';
	// if the issue I was accepted , I capture the data entered
	}else if ($fromform = $editform->get_data()) {
     
        $hiddenid= optional_param('buildingid',0,PARAM_INT);
		
		if($hiddenid!=0){
		
			$explode = $fromform->modules;
			$modulesArray=array();
			$modulesArray = explode('#', $explode);
			$steps=array();
		// Array is created with all the modules of the building that were entered in editing
			foreach($modulesArray as $moduleArray){			
				$steps[]=$moduleArray;		
			}
				
	   // Brings the information of all the modules of the building to be edited
			$recordtwo = $DB->get_records('bookingrooms_modules', array('building_id'=>$fromform->buildingid));
			$array=array();
			// Create as many rows as under modules have the building
			foreach($recordtwo as $recordt){
				$array[]=array("id"=>$recordt->id);
			}
			
			$result = count($array);
			$i=0;
// Runs modules admitted for form editing
			foreach($steps as $step){
		
				if($step!=null){
					$string=explode(',' , $step);
				
					$time=explode('-' , $string[1]);
					// Retrieves the information entered on the form editing module name , start and end time									
					$moduleName=$string[0];
					$start_module= $time[0];
					$end_module= $time[1];
					
					if(!empty($moduleName)&&!empty($start_module)&&!empty($end_module)){
			       
						if($i < $result){// If the building already had modules edits					
							$param=$array[$i]["id"];
							$info=$DB->get_record("bookingrooms_modules", array('id'=>$param,'building_id'=>$fromform->buildingid));
							$info->name_module = $moduleName;
							$info->hour_start = $start_module;
							$info->hour_end = $end_module;						
							$DB->update_record('bookingrooms_modules', $info);		
							$i++;
						}else{// The building had no modules, creates them.
					
							$recordtwo = new stdClass();
							$recordtwo->name_module = $moduleName;
							$recordtwo->hour_start = $start_module;
							$recordtwo->hour_end = $end_module;
							$recordtwo->building_id = $fromform->buildingid;					
							$DB->insert_record('bookingrooms_modules', $recordtwo);				
						}				
					}else{// Data modules entered in the edit form are not correct
			              // Changes the action to show				
						$warning = get_string('warning', 'local_bookingrooms').'</br>';
						$action = 'view';
					}
				}		
			}
		
			if($i<$result){
			// If previously existed more module which is added or modified via the edition deleted
				$total=$result-$i;
				echo $total;
				$select="building_id'$fromform->buildingid' ORDER BY id DESC limit $total";
				$results = $DB->get_records_select('bookingrooms_modules',$select);
				foreach($results as $result){			
					$DB->delete_records('bookingrooms_modules', array('id'=>$result->id));				
				}			
			}
		// Rename the campus and building and it belongs to the admitted to the form to editing
			$record = $DB->get_record('bookingrooms_buildings', array('id'=>$fromform->buildingid));
			$record->name = $fromform->building;
			$record->campus_id = $fromform->campus;
			$DB->update_record('bookingrooms_buildings', $record);
			$action = $prevaction;
			
		}
		$action = 'view'; 
	}
}


// Erases a building that already exists
if($action== 'delete'){
	$idbuilding = required_param('building',PARAM_INT);
	// If the building is associated with the rooms , are searched and deleted
	if($rooms = $DB->get_records('bookingrooms_rooms', array('buildings_id'=>$idbuilding))){
		foreach ($rooms as $room){
			$DB->delete_records('bookingrooms_reserves', array('rooms_id'=>$room->id));
		}
		$DB->delete_records('bookingrooms_rooms', array('buildings_id'=>$idbuilding));
	}
    $DB->delete_records('bookingrooms_buildings', array('id'=>$idbuilding));
	$action = "view";
}


// A new building was created
if($action== 'create'){
// Form creation
	$createbuilding = new createBuildingTwoAdminRoom();
	if ($createbuilding->is_cancelled()) {
		$action= 'view';
	// If the form of creation and has not canceled the building validated data is created
	}else if ($fromform = $createbuilding->get_data()) {
		
		$record = new stdClass();
		$record->name = $fromform->building;
		$record->campus_id = $fromform->campus;
		$explode = $fromform->modules;
		// It creates the building and its campus asociate
 		if($DB->insert_record('bookingrooms_buildings', $record)){			
			$recordtwo = new stdClass();
			$buildingid = $DB->get_record('bookingrooms_buildings', array('name'=>$fromform->building, 'campus_id'=>$fromform->campus));
			$modulesArray=array();
		    $modulesArray = explode('#', $explode);
			$steps=array();
		// It creates the building and its campus asociates
			foreach($modulesArray as $moduleArray){			
					$steps[]=$moduleArray;								
			}
			// Modules are added to the building . The name, start time and end time for each module .
			foreach($steps as $step){
				if($step){
				$string=explode(',' , $step);
				$time=explode('-' , $string[1]);			
				$moduleName=$string[0];
				$start_module= $time[0];
				$end_module= $time[1];			
				if(!empty($moduleName)&&!empty($start_module)&&!empty($end_module)){
				
					$recordtwo->name_module = $moduleName;
					$recordtwo->hour_start = $start_module;
					$recordtwo->hour_end = $end_module;
					$recordtwo->building_id = $buildingid->id;					
					$DB->insert_record('bookingrooms_modules', $recordtwo);			
				}
			}
		}
			
	}else{
 			print_error("ERROR");
 		}
		$action= 'view';
	 }
}


// If the ACTION is seeing creates the table that displays all existing buildings
if($action == 'view'){
	$table = tables::datasPlacesBuildingsAdminRoom();
}


//**************************************************************************************************
// ACTION views each previously implemented are created.

if($action == 'edit'){
	
	$o= '';
	$title = get_string('editbuilding', 'local_bookingrooms');
	$PAGE->navbar->add(get_string('roomsreserve', 'local_bookingrooms'));
	$PAGE->navbar->add(get_string('adjustments', 'local_bookingrooms'));
	$PAGE->navbar->add(get_string('seeandmodbuildings', 'local_bookingrooms'), 'buildings.php');
	$PAGE->navbar->add($title, '');
	$PAGE->set_title($title);
	$PAGE->set_heading($title);
	$o .= $OUTPUT->header();
	$o .= $OUTPUT->heading(get_string('editbuilding', 'local_bookingrooms'));
	
	 
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
	
	$title = get_string('seeandmodbuildings', 'local_bookingrooms');
	$PAGE->navbar->add(get_string('roomsreserve', 'local_bookingrooms'));
	$PAGE->navbar->add(get_string('adjustments', 'local_bookingrooms'));
	$PAGE->navbar->add($title, 'buildings.php');
	$PAGE->set_title($title);
	$PAGE->set_heading($title);
	
	$o.= $OUTPUT->header();
	//$o.= $OUTPUT->heading("buildingd");
	$o .= $OUTPUT->tabtree($toprow, get_string('buildings', 'local_bookingrooms'));
	$urlbuilding = new moodle_url("buildings.php", array('action'=>'create'));
	$o.= $OUTPUT->single_button($urlbuilding, get_string('createbuildings', 'local_bookingrooms'));
	$o .= html_writer::table($table);
	$o.= $OUTPUT->single_button($urlbuilding, get_string('createbuilding', 'local_bookingrooms'));
	$o .= $OUTPUT->footer();
	
}else if($action== 'create'){
	$o = '';
	$title = get_string('createbuildings', 'local_bookingrooms');
	$PAGE->navbar->add(get_string('roomsreserve', 'local_bookingrooms'));
	$PAGE->navbar->add(get_string('adjustments', 'local_bookingrooms'));
	$PAGE->navbar->add(get_string('seeandmodbuildings', 'local_bookingrooms'), 'buildings.php');
	$PAGE->navbar->add($title);
	$PAGE->set_title($title);
	$PAGE->set_heading($title);
	$o .= $OUTPUT->header();
	$o .= $OUTPUT->heading($title);
	ob_start();
    $createbuilding->display();
    $o .= ob_get_contents();
    ob_end_clean();
        
	$o .= $OUTPUT->footer();
}else if($action == "nocampus"){
	$o = '';
	$toprow = array();
	$toprow[] = new tabobject(get_string('sites', 'local_bookingrooms'), new moodle_url('/local/bookingrooms/settings/campus.php'), get_string('sites', 'local_bookingrooms'));
	$toprow[] = new tabobject(get_string('buildings', 'local_bookingrooms'), new moodle_url('/local/bookingrooms/settings/buildings.php'), get_string('buildings', 'local_bookingrooms'));
	$toprow[] = new tabobject(get_string('studyrooms', 'local_bookingrooms'), new moodle_url('/local/bookingrooms/settings/rooms.php'), get_string('rooms', 'local_bookingrooms'));
	$toprow[] = new tabobject(get_string('resources', 'local_bookingrooms'), new moodle_url('/local/bookingrooms/settings/resources.php'), get_string('resources', 'local_bookingrooms'));
	
	$title = get_string('seeandmodbuildings', 'local_bookingrooms');
	$PAGE->navbar->add(get_string('roomsreserve', 'local_bookingrooms'));
	$PAGE->navbar->add(get_string('adjustments', 'local_bookingrooms'));
	$PAGE->navbar->add($title, 'buildings.php');
	$PAGE->set_title($title);
	$PAGE->set_heading($title);
	
	$o.= $OUTPUT->header();
	$o .= $OUTPUT->tabtree($toprow, get_string('buildings', 'local_bookingrooms'));
	$o.= $OUTPUT->heading(get_string('buildings', 'local_bookingrooms'));
	

	if($campus==0){
		$url = new moodle_url("campus.php", array('action'=>'create'));
		$o.= "<center><strong>".get_string('nosites', 'local_bookingrooms')."<strong><center>";
		$o.= $OUTPUT->single_button($url, get_string('campuscreate', 'local_bookingrooms'));
		
	}else{
		
		$url = new moodle_url("buildings.php", array('action'=>'create'));
		$o.= "<center><strong>".get_string('nobuildings', 'local_bookingrooms')."<strong><center>";
		$o.= $OUTPUT->single_button($url, get_string('createbuildings', 'local_bookingrooms'));
	}
	$o .= $OUTPUT->footer();
	
}else{
	print_error(get_string('invalidaction', 'local_bookingrooms'));
}

echo $o;