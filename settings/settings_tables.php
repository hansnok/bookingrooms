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
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/local/bookingrooms/lib.php');
class tables{

	public function __construct(){

	}
	public static function getrooms($buildingid = null){
		global $DB, $OUTPUT;
		if($buildingid){
			$rooms = $DB->get_records('bookingrooms_rooms', array('buildings_id'=>$buildingid));
				
		}else{
			$rooms = $DB->get_records('bookingrooms_rooms');
			//$rooms = $DB->get_records_sql('select * from {bookingrooms_rooms} order by buildings_id');
		}
		$table = new html_table();
		$table->head = array(get_string('campus', 'local_bookingrooms'), get_string('building', 'local_bookingrooms'), get_string('room', 'local_bookingrooms'),get_string('roomtype', 'local_bookingrooms'),get_string('capacity', 'local_bookingrooms'), get_string('adjustments', 'local_bookingrooms'));
		foreach($rooms as $room){
			$building= $DB->get_record('bookingrooms_buildings', array('id'=>$room->buildings_id));
			$campus= $DB->get_record('bookingrooms_campus', array('id'=>$building->campus_id));
				
				
			if($buildingid){
				$editurl_room = new moodle_url('rooms.php', array('action'=>'edit', 'prevaction'=>'verporedificio','idroom'=>$room->id, 'sesskey'=>sesskey()));
				$deleteurl_room = new moodle_url('rooms.php', array('action'=>'delete', 'prevaction'=>'verporedificio','building'=>$building->id,'idroom'=>$room->id, 'sesskey'=>sesskey()));
			}else{
				$editurl_room = new moodle_url('rooms.php', array('action'=>'edit', 'idroom'=>$room->id, 'sesskey'=>sesskey()));
				$deleteurl_room = new moodle_url('rooms.php', array('action'=>'delete', 'idroom'=>$room->id, 'sesskey'=>sesskey()));
			}
				
				
			$editicon_room = new pix_icon('t/editstring', get_string('modify', 'local_bookingrooms'));
			$editaction_room = $OUTPUT->action_icon($editurl_room, $editicon_room, new confirm_action(get_string('areyoueditroom', 'local_bookingrooms')));
				
				
			$deleteicon_room = new pix_icon('t/delete', get_string('remove', 'local_bookingrooms'));
			$deleteaction_room = $OUTPUT->action_icon($deleteurl_room, $deleteicon_room, new confirm_action(get_string('areyouremoveroom', 'local_bookingrooms')));
			
			$typeRoom='';
			if($room->type == 1){
				
				$typeRoom=get_string('classroom', 'local_bookingrooms');
				
			}else if($room->type == 2){
				
				$typeRoom=get_string('studyroom', 'local_bookingrooms');
				
			}else if($room->type == 3){
				
				$typeRoom=get_string('reunionroom', 'local_bookingrooms');
				
			}
				
			$table->data[]= array($campus->name, $building->name, $room->name,$typeRoom,$room->capaciy, $editaction_room.$deleteaction_room);
		}
		return $table;

	}

	public static function dataCampusBuildings(){	
		
		global $DB, $OUTPUT;
		//$buildings = $DB->get_records('bookingrooms_buildings');
		$buildings = $DB->get_records_sql('select * from {bookingrooms_buildings} order by campus_id');
		$table = new html_table();
		$table->head = array(get_string('campus', 'local_bookingrooms'), get_string('building', 'local_bookingrooms'), get_string('seechangerooms', 'local_bookingrooms'), get_string('deletebuilding', 'local_bookingrooms'));
	
		foreach ($buildings as $building){
			$campus = $DB->get_record('bookingrooms_campus', array('id' => $building->campus_id));
	
			$editurl_buildings =  new moodle_url('rooms.php', array('action'=>'viewbybuilding', 'building'=>$building->id));
			$editurl_icon = new pix_icon('t/preview', 'View/Modify');
			$editaction_buildings = $OUTPUT->action_icon($editurl_buildings, $editurl_icon);
	
			$deleteurl_campus = new moodle_url('buildings.php', array('action'=>'delete', 'building'=>$building->id, 'sesskey'=>sesskey()));
			$deleteicon_campus = new pix_icon('t/delete', 'delete');
			$deleteaction_campus = $OUTPUT->action_icon($deleteurl_campus, $deleteicon_campus, new confirm_action(get_string('deletebuildingofcampus', 'local_bookingrooms')));
			$table->data[] = array($campus->name, $building->name, $editaction_buildings, $deleteaction_campus);
	
		}
		return $table;
	
	}

// Table used in buildings.php shows all the buildings created.
	public static function datasPlacesBuildingsAdminRoom(){
    
		global $DB, $OUTPUT;
		//$buildings = $DB->get_records('bookingrooms_buildings');
		$buildings = $DB->get_records_sql('select * from {bookingrooms_buildings} order by campus_id');
		$table = new html_table();
		$table->head = array(get_string('campus', 'local_bookingrooms'), get_string('building', 'local_bookingrooms'), get_string('adjustments', 'local_bookingrooms'));

		foreach ($buildings as $building){
			$campus = $DB->get_record('bookingrooms_campus', array('id' => $building->campus_id));
				
			$editurl_buildings =  new moodle_url('buildings.php', array('action'=>'edit', 'buildings'=>$building->id));
			$editurl_icon = new pix_icon('t/editstring', get_string('modify', 'local_bookingrooms'));
			$editaction_buildings = $OUTPUT->action_icon($editurl_buildings, $editurl_icon, new confirm_action(get_string('areyousureyouwanttoedit', 'local_bookingrooms')));
				
			$deleteurl_campus = new moodle_url('buildings.php', array('action'=>'delete', 'building'=>$building->id, 'sesskey'=>sesskey()));
			$deleteicon_campus = new pix_icon('t/delete', get_string('remove', 'local_bookingrooms'));
			$deleteaction_campus = $OUTPUT->action_icon($deleteurl_campus, $deleteicon_campus, new confirm_action(get_string('thisabouttoremove', 'local_bookingrooms')));
			
			//CHANGE URL
			$seeurl_rooms = new moodle_url('rooms.php', array('action'=>'viewbybuilding', 'building'=>$building->id, 'sesskey'=>sesskey()));
			$seeicon_rooms = new pix_icon('i/preview', get_string('seestudyrooms', 'local_bookingrooms'));
			$seeaction_rooms = $OUTPUT->action_icon($seeurl_rooms, $seeicon_rooms);
				
			$table->data[] = array($campus->name, $building->name, $seeaction_rooms.$editaction_buildings.$deleteaction_campus);
				
		}
		return $table;

	}
	
	
	
	public static function getCampus(){
		global $DB, $OUTPUT;
		$campus = $DB->get_records('bookingrooms_campus');

		$table = new html_table();
		$table->head = array(get_string('campus', 'local_bookingrooms'), get_string('deletecampus', 'local_bookingrooms'));
		foreach ($campuses as $campus){
			$deleteurl_campus = new moodle_url('campus.php', array('action'=>'delete', 'idcampus'=>$campus->id, 'sesskey'=>sesskey()));
			$deleteicon_campus = new pix_icon('t/delete', 'delete');
			$deleteaction_campus = $OUTPUT->action_icon($deleteurl_campus, $deleteicon_campus, new confirm_action(get_string('deletecampus', 'local_bookingrooms')));
			$table->data[] = array($campus->name, $deleteaction_campus);
		}
		return $table;

	}
	
	
	//Uses on campus.php, table that list all the campuses that exist.
	public static function getPlacesAdminRoom(){
		global $DB, $OUTPUT;
		$places = $DB->get_records('bookingrooms_campus');
	
		$table = new html_table();
		$table->head = array(get_string('campus', 'local_bookingrooms'), get_string('adjustments', 'local_bookingrooms'));
		foreach ($places as $campus){
			$deleteurl_campus = new moodle_url('campus.php', array('action'=>'delete', 'idplace'=>$campus->id, 'sesskey'=>sesskey()));
			$deleteicon_campus = new pix_icon('t/delete', get_string('remove', 'local_bookingrooms'));
			$deleteaction_campus = $OUTPUT->action_icon($deleteurl_campus, $deleteicon_campus, new confirm_action(get_string('doyouwantdeletesite', 'local_bookingrooms')));
			
			$editurl_campus = new moodle_url('campus.php', array('action'=>'edit', 'idplace'=>$campus->id));
			$editicon_campus = new pix_icon('i/edit', get_string('edit', 'local_bookingrooms'));
			$editaction_campus = $OUTPUT->action_icon($editurl_campus, $editicon_campus, new confirm_action(get_string('doyouwanteditsite', 'local_bookingrooms')));
				
			$table->data[] = array($campus->name, $editaction_campus.$deleteaction_campus);
		}
		return $table;
	
	}
	
// Used in resources.php it generates table with all existing resource.
	public static function getResources(){
		global $DB, $OUTPUT;
		$resources = $DB->get_records('bookingrooms_resources');
	
		$table = new html_table();
		$table->head = array(get_string('resources', 'local_bookingrooms'), get_string('adjustments', 'local_bookingrooms'));
		foreach ($resources as $resource){
			$deleteurl_resource = new moodle_url('resources.php', array('action'=>'delete', 'idresource'=>$resource->id, 'sesskey'=>sesskey()));
			$deleteicon_resource = new pix_icon('t/delete', get_string('remove', 'local_bookingrooms'));
			$deleteaction_resource = $OUTPUT->action_icon($deleteurl_resource, $deleteicon_resource, new confirm_action(get_string('doyouwantdelete', 'local_bookingrooms')));
				
			$editurl_resource = new moodle_url('resources.php', array('action'=>'edit', 'prevaction'=>'view', 'idresource'=>$resource->id, 'sesskey'=>sesskey()));
			$editicon_resource = new pix_icon('i/edit', get_string('edit', 'local_bookingrooms'));
			$editaction_resource = $OUTPUT->action_icon($editurl_resource, $editicon_resource, new confirm_action(get_string('doyouwantedit', 'local_bookingrooms')));
	
			$table->data[] = array($resource->name, $editaction_resource.$deleteaction_resource);
		}
		return $table;
	
	}
	
	
	public static function AlldataReservations($reservations, $max, $page){
		global $CFG, $DB, $OUTPUT;
		$page = $page +1;

		$table = new html_table();
		$table->head = array(get_string('campus', 'local_bookingrooms'), get_string('building', 'local_bookingrooms'), get_string('room', 'local_bookingrooms'),get_string('reservedate', 'local_bookingrooms'),get_string('createdate', 'local_bookingrooms'),get_string('student', 'local_bookingrooms'), get_string('module', 'local_bookingrooms'), get_string('state', 'local_bookingrooms'), get_string('confirmedfrom', 'local_bookingrooms'), "PC", get_string('reservecomment', 'local_bookingrooms'), get_string('admincomment', 'local_bookingrooms'));

		$count = 1;
		foreach ($reservations as $reservation){
				
			if($count <= $page*$max && $count > ($page-1)*$max ){

					
				$room = $DB -> get_record('bookingrooms_rooms', array('id' => $reservation->rooms_id));
				$building = $DB -> get_record('bookimgrooms_buildings', array('id' => $room->buildings_id));
				$campus = $DB -> get_record('bookingrooms_campus', array('id' => $building->campus_id));
				$student = $DB -> get_record('user', array('id' => $reservation->student_id));

				if($reservation->confirmado==1){
					$status = "Confirm";
				}else if($reservation->confirmed==0){
					$status = "Pending";
				}
				$editurl_room = new moodle_url('historial.php', array('action'=>'commentary', 'reservation'=>$reservation->id ));
				$editicon_room = new pix_icon('t/edit', get_string('edit', 'local_bookingrooms'));
				$editaction_room = $OUTPUT->action_icon($editurl_room, $editicon_room, new confirm_action(get_string('areyousureaddcomments', 'local_bookingrooms')));
				if($reservation->commentary_admin != NULL){
					$commentary = $reserva->commentary_admin;
				}
				else{
					$commentary = $editaction_room;
				}

				$moduleName = $DB -> get_record('bookingrooms_modules', array('id' => $reservation->module));
				
				$table->data[] = array($campus->name, $building->name, $room->name, $reservation->date_reservation,$reservation->date_creation, $student->username, $moduleName->nsmr_module, $status,$reservation->ip , $room->name_pc, $reservation->coment_student, $commentary );
					

			}
			$count++;
		}

		return $table;


	}
	public static function myReservations($userid = null){
		global $DB, $USER, $OUTPUT;

		if($userid == null){
			$user_id = $USER->id;
		}else{
			$user_id = $userid;
		}

		$table = new html_table();
		$table->head = array(get_string('date', 'local_bookingrooms'), get_string('campus', 'local_bookingrooms'),get_string('building', 'local_bookingrooms'), get_string('rooms', 'local_bookingrooms'), get_string('module', 'local_bookingrooms'), get_string('confirm', 'local_bookingrooms'), get_string('cancel', 'local_bookingrooms'));
		$reservas = $DB->get_records('bookingrooms_reservas', array('student_id' => $user_id, 'active' => '1'));
		foreach ($reservations as $reservation) {
				
			if($userid == null){
				$con_url = new moodle_url('myreservations.php', array('action'=>'confirm', 'idreservation'=>$reservation->id, 'sesskey'=>sesskey()));//('confirm.php?idconfirmar='. $reservation->id);
				$del_url = new moodle_url('myreservations.php', array('action'=>'cancel', 'idreservation'=>$reservation->id, 'sesskey' =>sesskey()));
			}else{
				$con_url = new moodle_url('usersreservation.php', array('action'=>'confirm', 'idreservation'=>$reservation->id, 'sesskey'=>sesskey()));//('confirm.php?idconfirm='. $reservation->id);
				$del_url = new moodle_url('usersreservation.php', array('action'=>'cancel', 'idreservation'=>$reservation->id, 'sesskey' =>sesskey()));
			}
				
			$room = $DB->get_record('bookingrooms_rooms', array('id'=>$reservation->rooms_id));
			$building = $DB->get_record('bookingrooms_buildings', array('id'=>$room->buildings_id));
			$campus = $DB->get_record('bookingrooms_campus', array('id'=>$building->campus_id));
			$module = $DB->get_record('bookingrooms_modules', array('id'=>$reservation->module));

			$hourmodule = hour_module($reservation->module);
			$actualtime=time();
			$starttime=$hourmodule[0];
			$endtime=$hourmodule[1];
			$starttime=  $starttime->modify('-5 minutes');
			$before=$starttime->getTimestamp();
			$starttime=  $starttime->modify('+20 minutes');
			$despues=$starttime->getTimestamp();
			$starttime=  $starttime->modify('-80 minutes');
			$cancelhour=$starttime->getTimestamp();
			if($reservation->confirm){
				$confaction_reservation = 'Confirm';
			}else if($actualtime < $after && $actualtime > $before){ 
				$confurl_reservation = $con_url;
				$conficon_reservation = new pix_icon('i/valid', get_string('confirm', 'local_bookingrooms'));
				$confaction_reservation = $OUTPUT->action_icon($confurl_reservation, $conficon_reservation);
			}else if($actualtime>$after && $reservation->confirm == 0){
				$confaction_reservation = get_string('thetimetoconfirm', 'local_bookingrooms');
			}else{
				$confaction_reservation = $OUTPUT->pix_icon('t/block',get_string('stillcannotconfirm', 'local_bookingrooms'));
			}
				
			if($actualtime < $cancelhour){
				$delurl_reservation = $del_url;
				$delicon_reservation = new pix_icon('i/invalid', get_string('cancel', 'local_bookingrooms'));
				$delaction_reservation = $OUTPUT->action_icon($delurl_reservation, $delicon_reservation, new confirm_action(get_string('areyousuretocancel', 'local_bookingrooms')));
			}else{
				$delaction_reservation = get_string('timetocancel', 'local_bookingrooms');
			}

				
			$table->data[]=array($reservation->date_reservation,$campus->nome,$building->name,$room->name,$module->name_module."<br>(".$module->hour_start." - ".$module->hour_end.")",$confaction_reservation,$delaction_reservatio );;
				
		}
		$table->align = array('center', 'center','center','center','center','center','center');
		$table->size = array('12%', '16%','14%','14%','16%','14%','14%');
		return $table;
	}

	
	public static function searchRooms($data){
		global $DB, $USER, $OUTPUT;
		
		
		$table = new html_table();
		$table->head = array(get_string('campus', 'local_bookingrooms'), get_string('building', 'local_bookingrooms'),get_string('room', 'local_bookingrooms'), get_string('event', 'local_bookingrooms'), get_string('reservedate', 'local_bookingrooms'), get_string('createdate', 'local_bookingrooms'), get_string('usercharge', 'local_bookingrooms'),get_string('module', 'local_bookingrooms'),
				html_writer::checkbox("","0",false,'',array('onClick'=>'checkAll()','id'=>'check')));
		
		$i=0;	
	foreach($data as $info){
		
		$room=$DB->get_record('bookingrooms_rooms',array('id'=>$info->rooms_id));
		$building=$DB->get_record('bookingrooms_buildings',array('id'=>$room->buildings_id));
		$campus=$DB->get_record('bookingrooms_campus',array('id'=>$building->campus_id));
		
			$name_event=$info->name_event;
			$dateR=$info->date_reservation;
			$dateC=$info->date_creation;
			$dateC=date("Y-m-d",$dateC);
			$assistants=$info->assistants;
			$user=$DB->get_record('user',array('id'=>$info->student_id));
			
			
			$module=$DB->get_record('bookingrooms_modulos',array('id'=>$info->module));
			
			 
		$table->data[] = array($campus->name,$building->name,$room->name,$name_event,$dateR,$dateC, $user->firstname.' '.$user->lastname,
		$module->name_module,html_writer::checkbox("check_list[".$i."]",$info->id,false,'',array('class'=>'check')));
		$i++;
	}
	
	
		
		

		$table->size = array('8%', '8%','8%','23%','10%','10%','20%','5%','3%');
		return $table;
	}

	public static function getInfo($h,$idroo ,$repeat,$idmodule,$idmoduleA=null){//$reserve, $module, $creation, $active){
		global $DB, $OUTPUT;
		
		$table = new html_table();
		$table->head = array(get_string('date', 'local_bookingrooms'), get_string('event', 'local_bookingrooms'), get_string('assistants', 'local_bookingrooms'),get_string('usercharge', 'local_bookingrooms'), get_string('module', 'local_bookingrooms'));
		
		
			$datessql = implode ( ",", $repeat );
			
			
			$data=$DB->get_records_sql("select * from {bookingrooms_reserves} where rooms_id = '$idroom' AND  date_reserve in('$datessql')
					AND module in ('$idmodule','$idmoduleA') ORDER BY date_reserve ASC");
			
		   
			foreach($datas as $data){
					
				$user=$DB->get_record('user', array('id'=>$data->student_id));
				$module = $DB->get_record('bookingrooms_modules', array('id'=>$data->module));
				$pieces=explode("|",$module->name_module);
				if(count($pieces)>1){
				$module->name_module=$pieces[0].$pieces[1];
				}
				$table->data[] = array($data->date_reserve, $data->name_event, $data->assistant, $user->firstname.' '.$user->lastname, $module->name_module);
					
			}
		
		
		
	
		
		$table->size = array('15%', '35%','15%','35%');
		return $table;
	}
	
	public static function getMail($n,$messageRoom,$messageDate,$messageModule, $messageCap){
		global $DB, $OUTPUT;
		
		$table = new html_table();
		$table->head = array(get_string('date', 'local_bookingrooms'), get_string('room', 'local_bookingrooms'), get_string('module', 'local_bookingrooms'), get_string('capacity', 'local_bookingrooms')); //date, campus, building, room, module, capacity
		if($n!=0){
		
		$n=$n-1;
		}
		
		for($i=0; $i <= $n; $i++){
			
			$datelatino = explode('-',$messageDate[$i]);
			$datemail = $datelatino[2].'-'.$datelatino[1].'-'.$datelatino[0];
			$table->data[] = array(str_replace("'", "", $datemail), $messageRoom[$i], $messageModule[$i], $messageCap[$i]);
			
		}
		
		
	return $table;
	}
	
	public static function getMailStudent($RoomsOne, $ModuleOne, $RoomsTwo, $ModuleTwo){
		global $DB, $OUTPUT;
	
		$table = new html_table();
		$table->head = array(get_string('room', 'local_bookingrooms'), get_string('module', 'local_bookingrooms'));
			
			$table->data[] = array($RoomsOne, $ModuleOne);
			$table->data[] = array($RoomsTwo, $ModuleTwo);
	
		return $table;
	}
	public static function getList(){ // No try
		global $DB;
		$table = new html_table();
		$sheets=$DB->get_records('bookingrooms_sheets');
		$table->head = array(get_string('namelist', 'local_bookingrooms') , get_string('adjustments', 'local_bookingrooms'));
		if(!empty($sheets)){
			foreach($sheets as $sheet){
				$deleteurl_resource = new moodle_url('modules.php', array('action'=>'delete', 'idresource'=>$resource->id, 'sesskey'=>sesskey()));
				$deleteicon_resource = new pix_icon('t/delete', get_string('remove', 'local_bookingrooms'));
				$deleteaction_resource = $OUTPUT->action_icon($deleteurl_resource, $deleteicon_resource, new confirm_action(get_string('doyouwantdelete', 'local_bookingrooms')));
				
				$editurl_resource = new moodle_url('resources.php', array('action'=>'edit', 'prevaction'=>'view', 'idresource'=>$resource->id, 'sesskey'=>sesskey()));
				$editicon_resource = new pix_icon('i/edit', get_string('edit', 'local_bookingrooms'));
				$editaction_resource = $OUTPUT->action_icon($editurl_resource, $editicon_resource, new confirm_action(get_string('doyouwantedit', 'local_bookingrooms')));
				
				$table->data[] = array($sheet->name, $editaction_resource.$deleteaction_resource);
				
			}
		}
		return $table;// If, it return the table in blank, create botton to create modules. 
		
		
		
		
	}
	
}