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
* @subpackage reservasalas
* @copyright  2014 Francisco García Ralph (francisco.garcia.ralph@gmail.com)
* 				   Nicolás Bañados Valladares (nbanados@alumnos.uai.cl)
*             2015 Mihail Pozarski Rada (mipozarski@alumnos.uai.cl)
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
//Form used in blocked.php
//Ask for a institutional email to get a module blocked
class UserSearch extends moodleform{
	function definition() {
		global $CFG;
		$usertype=$CFG->user;
		$mform =& $this->_form;
		$mform->addElement('text', 'user', get_string('uaiemail', 'local_bookingrooms').': ');
		$mform->setType('user', PARAM_TEXT);
		$mform->addRule('user', get_string('uaiemail', 'local_bookingrooms'), 'required');
		$mform->addElement('static','','', $usertype);
		$mform->addElement('textarea', 'comment', get_string('comment', 'local_bookingrooms').': ', 'cols="40" rows="10"' );

		$this->add_action_buttons(false, get_string('block', 'local_bookingrooms'));
	}
	function validation($data,$files) {
		global $DB;
		$errors=array();

		if($user = $DB->get_record('user', array('mail'=>$data['user']))){
			if($blocked = $DB->get_records('bookingrooms_blocked', array('student_id'=>$user->id))){
				foreach($blocked as block){
					if($block->status ==1){
						$errors['user'] = '*'.get_string('blockuser', 'local_bookingrooms');
					}
				}
			}
		}else{
			$errors['user'] = '*'.get_string('notuser', 'local_bookingrooms').': ';
		}
		return $errors;
	}
}
//Form used to unblock a student
class UnblockStudentForm extends moodleform{
	function definition(){
		global $CFG, $DB, $OUTPUT;
		$usertype=$CFG->user;
		$mform =& $this->_form;
		$mform->addElement('text', 'user', get_string('uaiemail', 'local_bookingrooms').': ');
		$mform->addRule('user', get_string('uaiemail', 'local_bookingrooms'), 'required');
		$mform->setType('user', PARAM_TEXT);
		$mform->addElement('static','','', $usertype);
		$mform->addElement('textarea', 'comment', get_string('comment', 'local_bookingrooms').': ', 'cols="40" rows="10"' );

		$this->add_action_buttons(false, get_string('unblock', 'local_bookingrooms'));
	}
	function validation($data,$files) {
		global $DB;
		$errors=array();
		$datenow = date('Y-m-d');
		$blocked = false;
		if($user = $DB->get_record('user', array('username'=>$data['user']))){

			if($DB->get_record('bookingrooms_blocked',array('student_id'=>$user->id,'status'=>1))){
				$blocked = true;
			}

			if($blocked==false){
				$errors['user'] = '*'.get_string('unblockuser', 'local_bookingrooms').': ';
			}
		}else{
			$errors['user'] = '*'.get_string('notuser', 'local_bookingrooms').': ';
		}
		return $errors;
	}
}
//Form used to change room booking
class ChangeBookedRoom extends moodleform{
	function definition() {
		global $DB;
		$mform =& $this->_form;
		$instance = $this->_customdata;
		$buildingheadquarter = array();
		$building = $DB->get_records('bookingrooms_buildings');
		foreach ($buildings as $building){
			$headquarter = $DB->get_record('bookingrooms_headquarter', array('id'=>$building->headquarter_id));
			$buildingheadquarter[$building->id] = $headquarter->name." - ".$building->name;
		}

		$info=json_encode($instance['x']);
		$mform->addElement('select', 'campus', get_string('choose_buildings','local_bookingrooms'),$buildingheadquarter);
		$mform->addElement('text', 'name', get_string('roomsname', 'local_bookingrooms').': ');
		$mform->setType('name', PARAM_TEXT);
		$mform->addElement('hidden', 'info',$info);
		$mform->setType('info', PARAM_TEXT);
		$mform->addElement('hidden', 'action','change with');
		$mform->setType('action', PARAM_TEXT);
		$this->add_action_buttons(true, get_string('changewith', 'local_bookingrooms'));
	}
	function validation($data,$files) {
		global $DB;
		$errors=array();

		if(!$DB->get_record('bookingrooms_rooms',array('name'=>$data['name'],'building_id'=>$data['campus']))){

			$errors['name'] = '*'.get_string('notrooms', 'local_bookingrooms').': ';
		}
		return $errors;
	}
}
//From used un bookinghistory.php
class AdminComment extends moodleform{
	function definition(){
		$mform =& $this->_form;
		$instance = $this->_customdata;

		$bookingid =$instance['bookingid'];
		$mform->addElement('textarea', 'comment', get_string('comment', 'local_bookingrooms').': ', 'wrap="virtual" rows="5" cols="40"');
		$mform->addElement('hidden','action','comment');
		$mform->setType('action', PARAM_TEXT);
		$mform->addElement('hidden','booking',$bookingid);
		$mform->setType('booking', PARAM_INT);
		$this->add_action_buttons(true, get_string('tocomment', 'local_bookingrooms'));

	}
}
?>