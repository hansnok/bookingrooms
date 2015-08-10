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
class formCampus extends moodleform {
	//Add elements to form
	function definition() {
		global $CFG,$DB;

		$mform =& $this->_form; // Don't forget the underscore!

		$mform->addElement('text', 'campus',get_string('newcampus', 'local_bookingrooms').': ');
		$mform->setType('campus', PARAM_TEXT);
		$mform->addRule('campus',get_string('thenameexists', 'local_bookingrooms'),'required');
		$mform->addElement('hidden','action','create');
		$mform->setType('action', PARAM_TEXT);

		$this->add_action_buttons(true,get_string('campuscreate', 'local_bookingrooms'));
	}
	function validation($data, $files){
		global $DB;
		$errors=array();
		$recovercampus=$DB->get_record('bookingrooms_campus',array('name'=>$data['campus']));
		if(!empty($recovercampus)){
			$errors['campus']=get_string('thenameexists', 'local_bookingrooms');
		}
		if(empty($data['campus'])){
			$errors['campus']=get_string('thecampisempty', 'local_bookingrooms');
		}
		return $errors;
	}

}


class formeditplaces extends moodleform {
	//Add elements to form
	function definition() {
		global $CFG,$DB;

		$mform =& $this->_form; // Don't forget the underscore!
		$instance = $this->_customdata;
		
		$placeid = $instance['idplace'];

		$mform->addElement('text', 'name',get_string('campusname', 'local_bookingrooms'));
		$mform->setType('place', PARAM_TEXT);
		
		$mform->addElement('hidden','action','edit');
		$mform->setType('action', PARAM_TEXT);
		
		$mform->addElement('hidden','idplace',$placeid);
		$mform->setType('idplace', PARAM_INT);
	
		$this->add_action_buttons(true,get_string('changecampusname', 'local_bookingrooms'));
	}

	function validation($data, $files){
		global $DB;
		$errors=array();
		$recovercampus = $DB->get_records('bookingrooms_campus',array('name'=>$data['name']));
		if(!empty($recovercampus)){
			$errors['name']= get_string('thenameexists', 'local_bookingrooms');
		}
		return $errors;
	}

}
?>