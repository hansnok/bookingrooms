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
class createRoomsTwo extends moodleform {
	function definition() {
		global $CFG, $DB;

		$mform =& $this->_form;

		$buildings = $DB->get_records('bookingrooms_buildings');
		foreach ($buildings as $building){
			$campus = $DB->get_record('bookingrooms_campus', array('id'=>$building->campus_id));
			$campusbuilding[$building->id] = $campus->name." - ".$building->name;
		}

		$types = array('1'=>get_string('class', 'local_bookingrooms'), '2'=>get_string('study', 'local_bookingrooms'),'3'=>get_string('reunion', 'local_bookingrooms'));

		$mform->addElement('select', 'campusbuilding', get_string('selectabuilding', 'local_bookingrooms').': ', $campusbuilding);
		$mform->setType('campusbuilding', PARAM_INT);
		$mform->addElement('select', 'roomType', get_string('selectTypeRoom', 'local_bookingrooms').': ', $types);
		$mform->setType('roomType', PARAM_INT);
		$mform->addElement('text', 'room', get_string('numbersofrooms', 'local_bookingrooms').': ');
		$mform->setType('room', PARAM_TEXT);
		$mform->addRule('room',get_string('indicatenumbersofrooms', 'local_bookingrooms'),'required');

		$mform->addRule('room',get_string('indicatenumbersofrooms', 'local_bookingrooms'),'nonzero');
		$mform->addElement('hidden','action','create');
		$mform->setType('action', PARAM_TEXT);
		$this->add_action_buttons(true, get_string('next', 'local_bookingrooms'));

	}

	function validation($data, $files){
		$errors=array();
		$rooms=$data['room'];
		if(empty($data['room']) || is_null($data['room'])){
			$errors['room']=get_string('enteravalidnumericvalue', 'local_bookingrooms');
		}
		if( !is_number($data['room']) ){
			$errors['room']=get_string('enteravalidnumericvalue', 'local_bookingrooms');
		}
		if($data['room']<0){
			$errors['room']=get_string('enteravalidnumericvalue', 'local_bookingrooms');
		}
		return $errors;
	}
}




class createrooms extends moodleform {
	//Add elements to form
	function definition() {
		Global $DB;
		$mform =& $this->_form;
		$instance = $this->_customdata;
		$numberofrooms =$instance['room'];
		$roomtype = $instance['type'];
		$buildingid=$instance['campusbuilding'];

		if($building = $DB->get_record("bookingrooms_buildings", array('id'=>$buildingid))){

			$campus = $DB -> get_record('bookingrooms_campus', array('id' => $building->campus_id));
			$mform->addElement('hidden','campus',$campus->id);
			$mform->setType('campus', PARAM_INT);
		}

		$seeResources = $DB->get_records('bookingrooms_resources');
		$mform->addElement('hidden','building',$buildingid);
		$mform->setType('building', PARAM_INT);
		$mform->addElement('hidden','namebuilding',$building->name); // name of the building
		$mform->setType('namebuilding', PARAM_TEXT);
		$mform->addElement('hidden','namecampus',$campus->name); // name of the campus
		$mform->setType('namecampus', PARAM_TEXT);
		//$mform->addElement('hidden','namber',$instance['room']);
		//$mform->setType('numero', PARAM_INT);
			
		for($i=0;$i<$numberofrooms;$i++){
			$resourcesArray=array();
			foreach($seeResources as $seeResource){
				$nresources = $seeResource->id;
				$checkName=$i.$nresources;
				$resourcesArray[] =& $mform->createElement('advcheckbox', $checkName, $seeResource->name, $seeResource->name.' ', array('group' => 1), array(0, 1));
			}
			$nroom =strval("room$i");
			$npc = "pc$i";
			$ntype = "cap$i";
			$nres= "res$i";
			$value=101+$i;
			$mform->addElement('text', $nroom, get_string('roomsname', 'local_bookingrooms'), array('value' => $value )); //***
			$mform->addRule($nroom, get_string('roomsname', 'local_bookingrooms'), 'required'); // *******
			$mform->setType($nroom, PARAM_INT);
			$mform->addElement('text', $ntype, get_string('roomcapacity', 'local_bookingrooms').': ');
			$mform->setType($ntype, PARAM_INT);
			$mform->setDefault($ntype,0);
			$mform->AddGroup($resourcesArray,'', get_string('resources', 'local_bookingrooms').': ');
			$mform->addElement('text', $npc ,get_string('pcname', 'local_bookingrooms').': ', array('value' => 'Pc de room '.($i+1)));
			$mform->setType($npc, PARAM_INT);
			$mform->addElement('static'); //to create a space between the pc :)
		}
		$mform->addElement('hidden', 'typeRoom', $roomtype);
		$mform->setType('typeRoom', PARAM_INT);
		$mform->addElement('hidden', 'number', $numberofrooms);
		$mform->setType('number', PARAM_INT);
		$mform->addElement('hidden','action','add');
		$mform->setType('action', PARAM_TEXT);
		$this->add_action_buttons(true, get_string('roomscreates', 'local_bookingrooms'));
	}
	function validation ($data, $files){
		/*
		 global $DB;
		 $errors=array();

		 for($i=0;$i<$data['number'];$i++){
			$nroom =strval("room$i");
			//$aux=101+$i;
			if($DB->get_record('bookingrooms_rooms', array('buildings_id'=>$data['building'],'name'=>$data[$nroom],'type'=>$data['typeRoom']))){
			$errors[$nroom]= "Este name ya existe en el Edficio";
			}
			}
			return $errors;
			/*
			$j=0;
			$todaslasrooms=$DB->get_records('bookingrooms_rooms');
			foreach ($todaslasrooms as $room){
			$nroom="room".$j;
			if($room->name == $data[$nroom]){
			$errors[$nroom]= "Este name ya existe en el Edficio";
			}
			$j++;
			}


			return $errors;

			/*global $DB;

			$errors = array();
			for($i=0; $i<$data['number']; $i++){
			$nroom = 101+$i;
			if($DB->get_record('bookingrooms_rooms', Array('buildings_id'=>$data['building'],'name'=>$data[$nroom], 'type'=>$data['typeRoom']))){
			$errors[$nroom] = "*room no creada debido a que el name ya fue utilizado en esta campus";
			}
			}
			return $errors;
			*/}
}



class editroom extends moodleform{
	function definition(){
		global $CFG, $DB;
		$mform =& $this->_form;
		$instance = $this->_customdata;
		$prevaction=$instance['prevaction'];
		$buildingid=$instance['buildingid'];
		$idroom = optional_param('idroom', NULL, PARAM_RAW);
		$nameroom = $DB->get_record('bookingrooms_rooms', array('id'=>$idroom));
		if(empty($nameroom)){
			$nameroom = new stdClass();
			$nameroom->name = "101";
			$nameroom->name_pc = "PC 1";
			$nameroom->capaciy = '0';
				
		}

		$resourcesArray=array();
		$seeResources = $DB->get_records('bookingrooms_resources');

		foreach($seeResources as $seeResource){
			$nresources = $seeResource->id;
			$checkName=$nresources;

			$resourcesArray[] =& $mform->createElement('advcheckbox', $checkName, $seeResource->name, $seeResource->name.' ');
			if($DB->get_records('bookingrooms_roomresource', array('rooms_id'=>$idroom, 'resources_id'=>$seeResource->id))!=null){
					
				$mform->setDefault($checkName, '1');
			}
		}

		$mform->addElement('text', 'changenameroom', get_string('roomsname', 'local_bookingrooms').': ', array('value' => $nameroom->name));
		$mform->setType('changenameroom', PARAM_TEXT);
		$mform->addRule('changenameroom',get_string('indicateroomname', 'local_bookingrooms'),'required');
		$mform->addElement('text', 'changenamepc', get_string('pcname', 'local_bookingrooms').': ', array('value' => $nameroom->name_pc));
		$mform->setType('changenamepc', PARAM_TEXT);
		$mform->addRule('changenamepc',get_string('indicatepcname', 'local_bookingrooms'),'required');
		$mform->addElement('hidden','action','edit');

		$types = array('1'=>get_string('class', 'local_bookingrooms'), '2'=>get_string('study', 'local_bookingrooms'),'3'=>get_string('reunion', 'local_bookingrooms'));

		$mform->addElement('select', 'roomType', get_string('selectTypeRoom', 'local_bookingrooms').': ', $types);
		$mform->setDefault('roomType', $nameroom->type);
		$mform->setType('roomType', PARAM_INT);
		$mform->addElement('text', 'cap', get_string('roomcapacity', 'local_bookingrooms').': ', array('value'=>$nameroom->capaciy));
		$mform->setType('cap', PARAM_INT);
		$mform->AddGroup($resourcesArray,'', get_string('resources', 'local_bookingrooms').': ');
		$mform->setType('action', PARAM_TEXT);
		$mform->addElement('hidden','building',$buildingid);
		$mform->setType('building', PARAM_INT);
		$mform->addElement('hidden','prevaction',$prevaction);
		$mform->setType('prevaction', PARAM_TEXT);
		$mform->addElement('hidden','idroom',$idroom);
		$mform->setType('idroom', PARAM_TEXT);
		$this->add_action_buttons();

	}
	function validation($data, $files){
		global $DB;
		$errors = array();
		if($DB->get_records_sql('select * from {bookingrooms_rooms} where name = ? AND buildings_id = ? AND type = ? AND id != ?', array($data['changenameroom'],$data['building'], $data['roomType'], $data['idroom']))){
			$errors['changenameroom'] = '*'.get_string('roomNameExists', 'local_bookingrooms');
		}
		return $errors;
	}

}


    ?>