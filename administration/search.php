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
 * @subpackage reservarooms
 * @copyright  2014 Francisco García Ralph (francisco.garcia.ralph@gmail.com)
 * 					Nicolás Bañados Valladares (nbanados@alumnos.uai.cl)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//user booking page
//capabilities of: booking, modifing, canceling, consulting
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/local/bookingrooms/administration_form.php');
require_once($CFG->dirroot.'/local/bookingrooms/lib.php');
require_once($CFG->dirroot.'/local/bookingrooms/administration_tables.php');

global $DB,$USER;
$params = Array();

$baseurl = new moodle_url('/local/bookingrooms/search.php'); //Important to create the page class
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url($baseurl);

$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('reserveroom', 'local_bookingrooms'));
$PAGE->set_heading(get_string('reserveroom', 'local_bookingrooms'));
$PAGE->navbar->add(get_string('roomsreserve', 'local_bookingrooms'));
$PAGE->navbar->add(get_string('searchroom', 'local_bookingrooms'),'search.php');

echo  $OUTPUT->header(); //Prints the header
$action = optional_param('action', 'see', PARAM_TEXT);
if($action=="see"){
echo $OUTPUT->heading(get_string('searchroom', 'local_bookingrooms'));
$buscador = new roomSearch();
$buscador->get_data();
$buscador->display();
$condition = '0';
if($fromform = $search->get_data()){
	
	$select ='1=1 ';
	
	$date=$fromform->startdate;
	$date=date("Y-m-d",$date);
	
	$endDate=$fromform->enddate;
	$multiple=$fromform->addmultiply;
	
	
	if($multiple == 0){
		$select.="AND date_booking='$date'";
		
	}else if($multiple == 1){
		
	
		$date1=mktime(0,0,0,date("m", $fromform->startdate),date("d", $fromform->startdate),date("Y", $fromform->startdate));
		$date2=mktime(0,0,0,date("m", $fromform->enddate),date("d", $fromform->enddate),date("Y", $fromform->enddate));
		$difrence=$date2-$date1;
		$days=$diference/(60*60*24)+1;
		//$dow   = $array[$f];
	
		$step  = 1;
		$unit  = 'D';
	
		$start = new DateTime($date);
		$end   = clone $start; // ¿?
		
		//$start->modify($dow); // Move to first occurence
		$days=intval($days);
		
		$end->add(new DateInterval('P'.$days.'D')); // Move to 1 year from start
	
		$interval = new DateInterval("P{$step}{$unit}");
		$period   = new DatePeriod($start, $interval, $end);
	
		foreach ($period as $date) {
			$repetir[]= "'".$date->format('Y-m-d')."'";
				
		}
		$repeat=implode(",",$repeat);
		$select.="AND date_booking in($repeat) ";
	
	
	}
	
	if(isset($fromform->name)){
		//$select.="AND nombre_evento like '%$fromform->name%' ";
		$select.= "AND ".$DB->sql_like('name_event', ':name_event')." ";
		$params['name_event'] = "$fromform->name";
		
	}
	if($fromform->responsable){
		
		// search by user email		
		if( $user=$DB->get_record("user",array("email"=>$fromform->responsable)) ){
			$select.="AND alumno_id='$user->id' ";
		}	
	}
	
	$id_rooms=array();
	if(isset($fromform->campus)){
		$campus=$fromform->campus;
		$h=count($campus);
		
		if($h=!0){
			$h=$h-1;
		}
		if($fromform->eventType==0 && $fromform->roomsname==null){
		
			
		for($i=0;$i<=$h;$i++){
		$rooms=$DB->get_records('bookingrooms_rooms',array('building_id'=>$campus[$i]));
		foreach($rooms as $room){
			$id_rooms[]="'".$room->id."'";
			
		}
		}
		if(empty($id_rooms)){
			
			$condition = '1';
		}
		}
		else if($fromform->eventType!=0 && $fromform->roomsname==null){
			
			for($i=0;$i<=$h;$i++){
				$rooms=$DB->get_records('bookingrooms_rooms',array('building_id'=>$campus[$i],'type'=>$fromform->eventType));
				foreach($rooms as $room){
					$id_rooms[]="'".$room->id."'";
						
				}
			}
			if(empty($id_rooms)){
					
				$condition = '1';
			}
		}
		else if($fromform->eventType!=0 && $fromform->roomsname!=null){
			
			for($i=0;$i<=$h;$i++){
				$rooms=$DB->get_records('bookingrooms_rooms',array('buildings_id'=>$campus[$i],'type'=>$fromform->eventType,'name'=>$fromform->roomsname));
				foreach($rooms as $room){
					$id_rooms[]="'".$room->id."'";
			
				}
			}
			if(empty($id_rooms)){
					
				$condition = '1';
			}
		}
		else if($fromform->eventType==0 && $fromform->roomsname!=null){
		
			for($i=0;$i<=$h;$i++){
				$rooms=$DB->get_records('bookingrooms_rooms',array('buildings_id'=>$campus[$i],'name'=>$fromform->roomsname));
				foreach($rooms as $room){
					$id_rooms[]="'".$room->id."'";
						
				}
			}
			if(empty($id_rooms)){
					
				$condition = '1';
			}
		}
	
		if (!empty($id_rooms)){
		$string_id_rooms=implode(",",$id_rooms);
	
	$select.="AND rooms_id in ($string_id_rooms) ";
		}
	}	
elseif($fromform->eventType!=0){

	if($fromform->roomsname!=null){
		
		$rooms=$DB->get_records('bookingrooms_rooms',array('type'=>$fromform->eventType,'name'=>$fromform->roomsname));
		foreach($rooms as $room){
		$id_rooms[]="'".$room->id."'";
			
		}
		if(empty($id_rooms)){
				
			$condition = '1';
		}
	}else{
		
		$rooms=$DB->get_records('bookingrooms_rooms',array('type'=>$fromform->eventType));
		foreach($rooms as $room){
			$id_rooms[]="'".$room->id."'";
				
		}
			
	if(empty($id_rooms)){
			
			$condition = '1';
		}
	}
	
		
		if (!empty($id_rooms)){
	
		$string_id_rooms=implode(",",$id_rooms);
	
		$select.="AND rooms_id in ($string_id_rooms) ";
		}
		
}
	elseif($fromform->roomsname!=null){
		
		$rooms=$DB->get_records('bookingrooms_rooms',array('name'=>$fromform->roomsname));
		foreach($rooms as $room){
			$id_rooms[]="'".$room->id."'";
				
		}
		if(empty($id_rooms)){
				
			$condition = '1';
		}
	if (!empty($id_rooms)){
		$string_id_rooms=implode(",",$id_rooms);
	
	$select.="AND rooms_id in ($string_id_rooms) ";
		}
	}
	$select.="AND activa=1";
	//$result = $DB->get_records_select('reservarooms_reservas',$select);
	$result = $DB->get_records_select('bookingrooms_bookings',$select,$params);
	if(empty($result) || $condition == 1){ // $condition=1 significa que no hay rooms
		
		echo '<h5>'.get_string('noreservesarefound', 'local_bookingrooms').'</h5>';
		
	}else{
	
	$table = tables::searchRooms($result);
	
	echo html_writer::tag('<form','',array('name'=>'search','method'=>'POST'));
	
	echo html_writer::table($table);
	if(has_capability('local/bookingrooms:delete', $context)) {
	echo'<input type="submit" name="action" value="remove" onClick="return ComfirmDeleteOrder();">';
	}
	if(has_capability('local/bookingrooms:changewith', $context)) {
	echo'<input type="submit" name="action" value="swap">';
	}
	
	echo html_writer::end_tag('form');
	}
}
}
else if($action=="remove"){
	
	echo $OUTPUT->heading(get_string('reserveseliminated', 'local_bookingrooms').'!');
	
	
	if(!has_capability('local/bookingrooms:delete', $context)) {
		print_error(get_string('INVALID_ACCESS','booking_rooms'));
	}
	
	
	$check_list=$_REQUEST['check_list'];
	$table = new html_table();
	$table->head = array(get_string('campus', 'local_bookingrooms'), get_string('building', 'local_bookingrooms'),get_string('room', 'local_bookingrooms'), get_string('event', 'local_bookingrooms'), get_string('reservedate', 'local_bookingrooms'), get_string('createdate', 'local_bookingrooms'), get_string('usercharge', 'local_bookingrooms'),get_string('module', 'local_bookingrooms'));
	foreach($check_list as $check){
		
        $data = $DB->get_record('bookingrooms_bookings', array ('id'=>$check));
        $room = $DB->get_record('bookingrooms_rooms', array ('id'=>$data->rooms_id));
        $building = $DB->get_record('bookingrooms_buildingss', array('id'=>$room->buildings_id));
        $campus = $DB->get_record('bookingrooms_headquarters', array('id'=>$building->headquarters_id));
        $module = $DB->get_record('bookingrooms_modules', array('id'=>$data->module));	
        $responsable = $DB->get_record('user', array('id'=>$data->student_id));
        $table->data[] = array($campus->name, $building->name, $room->name, $data->name_event, $data->date_booking, date("Y-m-d",$data->date_creation), $responsable->firstname.' '.$responsable->lastname, $module->name_module);
		$DB->delete_records('bookingrooms_bookings', array ('id'=>$check)) ;
	}
	$table->size = array('8%', '8%','8%','23%','10%','10%','20%','5%','3%');
	echo html_writer::table($table);
	echo $OUTPUT->single_button('search.php', get_string('return', 'local_bookingrooms'));
}
else if($action=="swap"){

	echo $OUTPUT->heading(get_string('change', 'local_bookingrooms'));
	
	if(!has_capability('local/bookingrooms:changewith', $context)) {
		print_error(get_string('INVALID_ACCESS','booking_room'));
	}
	
	if(isset($_REQUEST['check_list'])){
		$check_list=$_REQUEST['check_list'];
	}else{
		$check_list="";
	}

	$form = new ChangeBookedRoom(null,array('x'=>$check_list));
	
	
if($fromform = $form->get_data()){
	
	$info=json_decode($fromform->info);	
	$room=$DB->get_record('bookingrooms_rooms',array('name'=>$fromform->name,'buildings_id'=>$fromform->campus));

foreach($info as $check){

	$booking=$DB->get_record('bookingrooms_bookings',array('id'=>$check));
		$module=$DB->get_record('bookingrooms_modules',array('id'=>$booking->module));
		if(strpos($module->name_module, "|")){
			$next=$booking->module+1;
			$previous=$booking->module-1;
			
			$select="name_module LIKE '%|%' and id in('$next','$previous')";
			$results = $DB->get_records_select('bookingrooms_modules',$select);
			foreach($results as $result){
				$module=$result;
			}
		
		
		}else{
			$module= new stdClass;
			$module->id=0;
		}

		
		
		
		$select="module in ('$booking->module','$module->id') AND date_booking = '$booking->date_booking' AND
		rooms_id='$room->id'";
		$newbooking = $DB->get_record_select('bookingrooms_bookings',$select);
		
	
		
		
		if($newbooking){
			
			
			$newroom=$newbooking->rooms_id;
			$newbooking->rooms_id=$booking->rooms_id;
			$booking->rooms_id=$newroom;
			
			$DB->update_record('bookingrooms_bookings', $booking);
			$DB->update_record('bookingrooms_bookings', $newbooking);
			echo get_string('ithasbeenchanged', 'local_bookingrooms');
		}
		else{
			
			$booking->rooms_id=$room->id;
		$DB->update_record('bookingrooms_bookings',$booking);
		echo get_string('ithasbeenadded', 'local_bookingrooms');
		}
		
	}

	echo $OUTPUT->single_button('search.php', get_string('return', 'local_bookingrooms'));
}
else if ($form->is_cancelled()) {
	echo get_string('nochanges', 'local_bookingrooms').'<br/><br/>'.$OUTPUT->single_button('search.php', get_string('return', 'local_bookingrooms'));
}
else{
$form->display();

	

}
}
echo $OUTPUT->footer(); //Prints the footer
?>
<script>
function ComfirmDeleteOrder()
{
  var r=confirm("�Are you sure you want to delete the selected reservations?");
  if(r == true){
   return true;
  }else{
   return false;
  }
}



function checkAll(){
	var check = document.getElementById("check").checked
	var value = document.getElementById("check").value
	
		
		var inputs = document.getElementsByClassName("check");
	

	if(check){
		
			for(var i = 0; i < inputs.length; i++)

		    if(inputs[i].type == "checkbox"){
			
				inputs[i].checked = true;
			}
			
			
		    }
	if(!check){
		for(var i = 0; i < inputs.length; i++)

		    if(inputs[i].type == "checkbox"){
			
				inputs[i].checked = false;
			}
			
			
        }
    
	}
	
 
</script>

