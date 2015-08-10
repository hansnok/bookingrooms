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
require_once(dirname(dirname(__FILE__)) . '/../../config.php');; //obligatorio
require_once($CFG->dirroot.'/local/bookingrooms/settings/resources_form.php');
require_once($CFG->dirroot.'/local/bookingrooms/settings/settings_tables.php');

//code to set context, url, layout
global $PAGE, $CFG, $OUTPUT, $DB;
require_login();
$url = new moodle_url('/local/bookingrooms/settings/resources.php');
$context = context_system::instance();//context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');

//Capabilities
//Review if the person who enters to the page have the capabilitie to watch some info.
//ex: A student would not have access to the resources.
if(!has_capability('local/bookingrooms:administration', $context)) {
	print_error(get_string('invalidaccess','local_bookingrooms'));
}
	
//Rescue the action, can ve : view, edit, create, delete. 
$action = optional_param('action', 'view', PARAM_TEXT);

if(!$DB->get_records('bookingrooms_resources')){

	if($action=='view'){
		$action = "noresources";
	}
}


//Implementation of the action create
//let us create a resource
if($action == 'create'){
	$resourceform = new formResources();
	if ($resourceform->is_cancelled()) {
		$action= 'view';
	} else if ($fromform = $resourceform->get_data()) {
		// Checking whether the resource exists and runs through part of the form
		// Create the new resource
		$record = new stdClass();
		$record->name = $fromform->resource;
		$DB->insert_record('bookingrooms_resources', $record);
		$action = "view";
	}
}

//Implementation of action  edit
// edit an existing resource
if($action == 'edit'){
	$idresource= optional_param('idresource','0',PARAM_INT );
	$resourceid= optional_param('resourceid','0',PARAM_INT );
	$prevaction = optional_param('prevaction', 'view', PARAM_TEXT);
	$resourcename = $DB->get_record('bookingrooms_resources', array('id'=>$idresource));
	$record = $DB->get_record('bookingrooms_resources', array('id'=>$idresource));
	$editform = new formResourcesEdit(null,
			array('prevaction'=>$prevaction, 'idresource'=>$idresource, 'resourcename'=>$resourcename->name));
	if ($editform->is_cancelled()) {
		$action = $prevaction;
	}else if ($fromform = $editform->get_data()) {
		if($resourceid!=0){
	// The resource is edited, its name is changed by one nonexistent
			$record = $DB->get_record('bookingrooms_resources', array('id'=>$resourceid));
			$record->name = $fromform->resource;
			$DB->update_record('bookingrooms_resources', $record);
			$action = $prevaction;
		}
	}
}

// Delete action Implementation
// Remove an existing resource
if($action == 'delete'){
	$idresource= required_param('idresource',PARAM_INT );
	if (confirm_sesskey()) {
		$resources = $DB->get_records('bookingrooms_resources',  array('id'=>$idresource));
		foreach ($resources as $resource){
			$DB->delete_records('bookingrooms_roomresource', array('resources_id'=>$resource->id));
		}
		$DB->delete_records('bookingrooms_resources', array('id'=>$idresource));
		$action = "view";
	}else{
		print_error("ERROR");
	}
}

// Implementation action view
// Displays a table with all the resources
if($action == 'view'){
	$table= tables::getResources();
}


// Views of the action
//**************************************************************************************************************************************************
if($action == 'edit'){

	$o= '';
	$title = get_string('editresource', 'local_bookingrooms');
	$PAGE->navbar->add(get_string('roomsreserve', 'local_bookingrooms'));
	$PAGE->navbar->add(get_string('adjustments', 'local_bookingrooms'));
	$PAGE->navbar->add(get_string('seeandmodresources', 'local_bookingrooms'), 'resources.php');
	$PAGE->navbar->add($title, '');
	$PAGE->set_title($title);
	$PAGE->set_heading($title);
	$o .= $OUTPUT->header();
	$o .= $OUTPUT->heading(get_string('editresource', 'local_bookingrooms'));
	$o .= '<h4>'.get_string('resource', 'local_bookingrooms').': '.$resourcename->name.'</h4>';
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

	$title = get_string('seeandmodresources', 'local_bookingrooms');
	$PAGE->navbar->add(get_string('roomsreserve', 'local_bookingrooms'));
	$PAGE->navbar->add(get_string('adjustments', 'local_bookingrooms'));
	$PAGE->navbar->add($title, 'resources.php');
	$PAGE->set_title($title);
	$PAGE->set_heading($title);

	$o.= $OUTPUT->header();
	//$o.= $OUTPUT->heading($title);
	$o .= $OUTPUT->tabtree($toprow, get_string('resources', 'local_bookingrooms'));
	$url = new moodle_url("resources.php", array('action'=>'create'));
	$o.= $OUTPUT->single_button($url, get_string('createnewresource', 'local_bookingrooms'));
	$o .= html_writer::table($table);
	$o.= $OUTPUT->single_button($url, get_string('createnewresource', 'local_bookingrooms'));
	$o .= $OUTPUT->footer();
}else if($action == 'create'){
	$o= '';
	$title = get_string('createresource', 'local_bookingrooms');
	$PAGE->navbar->add(get_string('roomsreserve', 'local_bookingrooms'));
	$PAGE->navbar->add(get_string('adjustments', 'local_bookingrooms'));
	$PAGE->navbar->add(get_string('resources', 'local_bookingrooms'), 'resources.php');
	$PAGE->navbar->add($title);
	$PAGE->set_title($title);
	$PAGE->set_heading($title);
	$o .= $OUTPUT->header();
	$o .= $OUTPUT->heading($title);
	ob_start();
	$resourceform->display();
	$o .= ob_get_contents();
	ob_end_clean();
	$o .= $OUTPUT->footer();
}
else if($action == "noresources"){
	$o = '';
	$toprow = array();
	$toprow[] = new tabobject(get_string('sites', 'local_bookingrooms'), new moodle_url('/local/bookingrooms/settings/campus.php'), get_string('places', 'local_bookingrooms'));
	$toprow[] = new tabobject(get_string('buildings', 'local_bookingrooms'), new moodle_url('/local/bookingrooms/settings/buildings.php'), get_string('buildings', 'local_bookingrooms'));
	$toprow[] = new tabobject(get_string('studyrooms', 'local_bookingrooms'), new moodle_url('/local/bookingrooms/settings/rooms.php'), get_string('rooms', 'local_bookingrooms'));
	$toprow[] = new tabobject(get_string('resources', 'local_bookingrooms'), new moodle_url('/local/bookingrooms/settings/resources.php'), get_string('resources', 'local_bookingrooms'));

	$title = get_string('seeandmodrooms', 'local_bookingrooms');
	$PAGE->navbar->add(get_string('roomsreserve', 'local_bookingrooms'));
	$PAGE->navbar->add(get_string('adjustments', 'local_bookingrooms'));
	$PAGE->navbar->add(get_string('seeandmodrooms', 'local_bookingrooms'), 'resources.php');
	$PAGE->set_title($title);
	$PAGE->set_heading($title);

	$o.= $OUTPUT->header();
	$o .= $OUTPUT->tabtree($toprow, get_string('resources', 'local_bookingrooms'));
	$o.= $OUTPUT->heading(get_string('roomsreserve', 'local_bookingrooms'));



	$url = new moodle_url("resources.php", array('action'=>'create'));
	$o.= "<center><strong>".get_string('nosystemresources', 'local_bookingrooms')."<strong><center>";
	$o.= $OUTPUT->single_button($url, get_string('createresource', 'local_bookingrooms'));
	$o .= $OUTPUT->footer();
}
else{
	print_error(get_string('invalidaction', 'local_bookingrooms'));
}
echo $o;