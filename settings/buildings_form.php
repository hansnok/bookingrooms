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
* @package    local
* @subpackage bookingrooms
* @copyright  2014 Francisco García Ralph (francisco.garcia.ralph@gmail.com)
* 				   Nicolás Bañados Valladares (nbanados@alumnos.uai.cl)
*             2015 Mihail Pozarski Rada (mipozarski@alumnos.uai.cl)
*             2015 Sebastian Riveros (sriveros@alumnos.uai.cl)
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
//Form used in blocked.php
//Ask for a institutional email to get a module blocked
require_once(dirname(dirname(__FILE__)) . '/../../config.php'); //mandatory
require_once($CFG->libdir.'/formslib.php');




class createBuildingTwoAdminRoom extends moodleform {

	function definition() {
		global $CFG, $DB;

		$mform =& $this->_form;
		$campus=$DB->get_records('bookingrooms_campus'); //i get the campus
		$campusArray=array();
		foreach($campus as $key=>$value){ //walk the campus to create the array
			$campusArray[$value->id]=$value->name;
		}
		$mform->addElement('select', 'campus',get_string('campus', 'local_bookingrooms').': ' , $campusArray); //i add the select for the campus
		$mform->addElement('text', 'building',get_string('newbuilding', 'local_bookingrooms').': ');//i add new buildings
		$mform->setType('building', PARAM_TEXT);
		$mform->addRule('building',get_string('indicatenametobuilding', 'local_bookingrooms'),'required');
		$mform->addElement('textarea', 'modules', get_string('modules', 'local_bookingrooms').': ');
		$mform->setType('modules', PARAM_TEXT);
		$mform->addRule('modules', get_string('indicatemodules', 'local_bookingrooms'), 'required');
		$mform->addElement('static', 'rule', get_string('modulerule', 'local_bookingrooms'));
		$mform->addElement('static', 'condition',get_string('modulecondition', 'local_bookingrooms'));
		$mform->addElement('hidden','action','create');
		$mform->setType('action', PARAM_TEXT);
		$this->add_action_buttons(true,get_string('createnewbuilding', 'local_bookingrooms'));
	}
	function validation($data, $files){
		global $DB;
		$errors = array();
		if($DB->get_records('bookingrooms_buildings', array('name'=>$data['building'], 'campus_id'=>$data['campus']))){
			$errors['building'] = '*'.get_string('buildingExists', 'local_bookingrooms');
		}

		$line = '';
		$linearray = array();
		$linestring = '';
		$explode = $data['modules'];
		$modulesArray=array();
		$modulesArray = explode('#', $explode);
		$steps=array();
		foreach($modulesArray as $moduleArray){
			$steps[]=$moduleArray;
		}
		foreach($steps as $step){
			if($step){
				$string=explode(',' , $step);
				$time=explode('-' , $string[1]);
				$moduleName=$string[0];
				$start_module= $time[0];
				$end_module= $time[1];
				$line ++;
					
				if(empty($moduleName)||empty($start_module)||empty($end_module)){

					$linearray[] = $line++;
					$linestring = implode(', ', $linearray);
					$errors['modules'] = get_string('checkthestructure', 'local_bookingrooms').$linestring.get_string('usethereference', 'local_bookingrooms');
				}
			}
		}
		$linearray = array();
		$line = '';
		return $errors;
	}
}





class formBuildingsEdit extends moodleform {
	//Add elements to form
	function definition() {
		global $CFG, $DB;

		$mform =& $this->_form; // Don't forget the underscore!
		$instance = $this->_customdata;
		$buildingid = $instance['idbuilding'];
		$prevaction=$instance['prevaction'];
		$name = $instance['buildingname'];
		$modules=$instance['modules'];
		$places=$instance['place'];
		$idres = optional_param('building', NULL, PARAM_RAW);
		$moduleforline = implode('', $modules);
		$buildingplace = $DB->get_record('bookingrooms_buildings', array('id'=>$buildingid));
		$placename = $DB->get_record('bookingrooms_campus', array('id'=>$buildingplace->campus_id));
		
		
		$mform->addElement('select', 'campus', get_string('campus', 'local_bookingrooms').': ', $places); //add the select to the campus
		$mform->setDefault('campus',$placename->id);
		$mform->setType('campus', PARAM_INT);
		$mform->addElement('text', 'building',get_string('newbuildingname', 'local_bookingrooms').': ', array('value'=>$name));// Add new edificos
		$mform->setType('building', PARAM_TEXT);
		$mform->addElement('hidden', 'testbuilding', $name);
		$mform->setType('testbuilding', PARAM_TEXT);
		$mform->addRule('building',get_string('indicatenametobuilding', 'local_bookingrooms').': ','required');
		$mform->addElement('textarea', 'modules', get_string('modules', 'local_bookingrooms').': ');
		$mform->setDefault('modules',$moduleforline);
		$mform->setType('modules', PARAM_TEXT);
		$mform->addRule('modules', get_string('indicatemodules', 'local_bookingrooms'), 'required');
		$mform->addElement('static', 'rule', get_string('modulerule', 'local_bookingrooms').': ');
		$mform->addElement('static', 'condition',  get_string('modulecondition', 'local_bookingrooms'));
		$mform->addElement('hidden','action','edit');
		$mform->setType('action', PARAM_TEXT);
		$mform->addElement('hidden','buildingid',$buildingid);
		$mform->setType('buildingid', PARAM_INT);
		$mform->addElement('hidden','prevaction',$prevaction);
		$mform->setType('prevaction', PARAM_INT);
		$this->add_action_buttons(true,get_string('changebuilding', 'local_bookingrooms'));
	}

	function validation($data, $files){

		global $DB;

		$errors = array();
		$buildings = $DB->get_records('bookingrooms_buildings', array('campus_id'=>$data['campus']));
		foreach($buildings as $building){
			if($data['building'] != $data['testbuilding'] && $building->name == $data['building']){
				$errors['building'] = '*'.get_string('buildingExists', 'local_bookingrooms');
			}
		}

		$line = '';
		$linearray = array();
		$linestring = '';
		$explode = $data['modules'];
		$modulesArray=array();
		$modulesArray = explode('#', $explode);
		$steps=array();
		foreach($modulesArray as $moduleArray){
			$steps[]=$moduleArray;
		}
		foreach($steps as $step){
			if($step){
				$string=explode(',' , $step);
				$time=explode('-' , $string[1]);
				$moduleName=$string[0];
				$start_module= $time[0];
				$end_module= $time[1];
				$line ++;

				if(empty($moduleName)||empty($start_module)||empty($end_module)){
						
					$linearray[] = $line++;
					$linestring = implode(', ', $linearray);
					$errors['modules'] = get_string('checkthestructure', 'local_bookingrooms').$linestring.get_string('usethereference', 'local_bookingrooms');
				}
			}
		}

		$linearray = array();
		$line = '';
		return $errors;
	}
}

?>
