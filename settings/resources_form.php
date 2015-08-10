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


class formResources extends moodleform {
	//Add elements to form
	function definition() {
		global $CFG;

		$mform =& $this->_form; // Don't forget the underscore!
		$mform->addElement('text', 'resource',get_string('newresource', 'local_bookingrooms').': ');
		$mform->setType('resource', PARAM_TEXT);
		$mform->addRule('resource',get_string('indicateresource', 'local_bookingrooms'),'required');
		$mform->addElement('hidden','action','create');
		$mform->setType('action', PARAM_TEXT);
		$this->add_action_buttons(true,get_string('createresource', 'local_bookingrooms'));
	}

	function validation($data, $files){
		global $DB;
		$errors=array();
		$nombredelrecurso = $DB->get_records('bookingrooms_resources', array('name'=>$data['resource']));
		if( !empty($resourcename) ){
			$errors['resource']= get_string('theresourceexist', 'local_bookingrooms');
		}
		return $errors;
	}
}


class formResourcesEdit extends moodleform {
	//Add elements to form
	function definition() {
		global $CFG;

		$mform =& $this->_form; // Don't forget the underscore!
		$instance = $this->_customdata;
		$resourceid = $instance['idresource'];
		$prevaction=$instance['prevaction'];
		$name = $instance['resourcename'];
		$idres = optional_param('idresource', NULL, PARAM_RAW);

		$mform->addElement('text', 'resource',get_string('resourcename', 'local_bookingrooms').': ', array('value'=> $name));
		$mform->setType('resource', PARAM_TEXT);
		$mform->addRule('resource',get_string('indicateresource', 'local_bookingrooms').': ','required');
		$mform->addElement('hidden','action','edit');
		$mform->setType('action', PARAM_TEXT);
		$mform->addElement('hidden','resourceid',$resourceid);
		$mform->setType('resourceid', PARAM_INT);
		$mform->addElement('hidden','prevaction',$prevaction);
		$mform->setType('prevaction', PARAM_TEXT);
		$mform->addElement('hidden','idres',$idres);
		$mform->setType('idres', PARAM_INT);
		$this->add_action_buttons(true,get_string('changeresource', 'local_bookingrooms'));
	}

	function validation($data, $files){
		global $DB;
		$errors=array();
		$searchexistingresource= $DB->get_records('bookingrooms_resources',array('name'=>$data['resource']));
		if(!empty($searchexistingresource)){
			$errors['resource']= get_string('thenameexists', 'local_bookingrooms');
		}
		return $errors;

	}
}


?>