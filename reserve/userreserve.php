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
 * @subpackage reservasalas
 * @copyright  2013 Marcelo Epuyao
 * 					
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php'); //obligatorio
require_once($CFG->dirroot.'/local/reservasalas/reserve/forms.php');
require_once($CFG->dirroot.'/local/reservasalas/reserve/tables.php');

// coe for context, url and layout settings
global $PAGE, $CFG, $OUTPUT, $DB;

// ACTION, cab be: confirmar, cancelar, ver y buscarusuario.
$action = optional_param('action', 'buscarusuario', PARAM_ACTION);

require_login();

$url = new moodle_url('/local/reservasalas/reserve/userreserve.php');
$context = context_system::instance();//context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('userReserves', 'local_reservasalas'));

//Capabilities, verify if the logged person has the permission to see the page content, a student has no permission.

if(!has_capability('local/reservasalas:bockinginfo', $context)) {
	print_error(get_string('INVALID_ACCESS','Reserva_Sala'));
}

//Confirmar action
// Lets an administraor to confirm a reservation, made by him or not.
if($action == 'confirmar'){
	$idreserva= required_param('idreserva', PARAM_INT);
	//$sesskey = required_param('sesskey', PARAM_INT);

	if(confirm_sesskey()){
		// Update reservation and change status to confirmado.
		$record = new stdClass();
		$record->id = $idreserva;
		$record->confirmado = true;
		$record->ip = gethostbyaddr($_SERVER['REMOTE_ADDR']);
		$record->comentario_alumno = get_string('confirmedbyadmin', 'local_reservasalas');
		if(!$DB->update_record('reservasalas_reservas', $record)){
			print_error(var_dump($record));
		}
		$reserva = $DB->get_record('reservasalas_reservas', array('id'=>$idreserva));
		$usuario = $DB->get_record('user', array('id'=>$reserva->alumno_id));
		$action = 'ver';
	}else{
		print_error('ERROR');
	}

	
// Cancel action
// Lets an administraor to cancel a reservation, made by him or not.
}else if($action == 'cancelar'){
	if(confirm_sesskey()){
		$idreserva= required_param('idreserva', PARAM_INT);
	
		// Updates a reservation status to inactiva .
		$data = new stdClass();
		$data->id= $idreserva;
		$data->activa = 0;
		$DB->update_record('reservasalas_reservas', $data);

		$reserva = $DB->get_record('reservasalas_reservas', array('id'=>$idreserva));
		$usuario = $DB->get_record('user', array('id'=>$reserva->alumno_id));
		$action = 'ver';
	}else{
		print_error('ERROR');
	}

}

// Buscarusuario action, first view for default
// Lets search for a user by his institutional e-mal, to show the user reservations.
if($action == 'buscarusuario'){

	$searchform = new searchUserReservations();
	if ($fromform = $searchform->get_data()) {
		$useremail =  $fromform->usuario;
		if(!$usuario = $DB->get_record('user', array('username'=>$useremail))){
			print_error(get_string('unregistereduser', 'local_reservasalas'));
		}
		$action = 'ver';
	}
}

// Ver action.
// Shows in a table one specific users reservations.
if($action == 'ver'){
	$reservationtable = myReservations($usuario->id);
}

//********************************************************************************************
$o = '';
if($action == 'ver'){
	$title = get_string('reservations', 'local_reservasalas')." ".$usuario->firstname." ".$usuario->lastname; //*
	$PAGE->navbar->add(get_string('roomsreserve', 'local_reservasalas'));
	$PAGE->navbar->add(get_string('adjustments', 'local_reservasalas'));
	$PAGE->navbar->add(get_string('userReserves', 'local_reservasalas'), 'reservasusuarios.php');
	$PAGE->navbar->add($title);
	$o.= $OUTPUT->header();
	$o.= $OUTPUT->heading($title);
	if($reservationtable->data){
		$o.= html_writer::table($reservationtable);
	}else{
		$o.= get_string('userhasnotbooked', 'local_reservasalas');
	}
	$o.=$OUTPUT->single_button('reservasusuarios.php', get_string('return', 'local_reservasalas')).'<br>'; // Prints Back link.

	$o .= $OUTPUT->footer();

}else if($action == 'buscarusuario'){
	$title = get_string('usersearch', 'local_reservasalas');
	$PAGE->navbar->add(get_string('roomsreserve', 'local_reservasalas'));
	$PAGE->navbar->add(get_string('adjustments', 'local_reservasalas'));
	$PAGE->navbar->add(get_string('userReserves', 'local_reservasalas'), 'reservasusuarios.php');
	$PAGE->navbar->add($title, '');
	$PAGE->set_title($title);
	$PAGE->set_heading($title);
	$o .= $OUTPUT->header();
	$o .= $OUTPUT->heading($title);

	ob_start();
	$searchform->display();
	$o .= ob_get_contents();
	ob_end_clean();

	$o .= $OUTPUT->footer();


}else{
	print_error(get_string('invalidaction', 'local_reservasalas'));
}
echo $o;