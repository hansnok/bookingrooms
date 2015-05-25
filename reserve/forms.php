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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.
// a

/**
 *
 * @package local
 * @subpackage reservasalas
 * @copyright 2014 Francisco García Ralph (francisco.garcia.ralph@gmail.com)
 * @copyright Nicolás Bañados Valladares (nbanados@alumnos.uai.cl)
 * @copyright 2015 Nicolás Pérez Urzúa (niperez@alumnos.uai.cl)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once (dirname ( __FILE__ ) . '/../../config.php');
require_once ($CFG->libdir . '/formslib.php');
require_once ($CFG->dirroot . '/local/reservasalas/reserve/tables.php');


//TODO: change every spanish name to english, in new repository
class formSearchRoom extends moodleform {
	function definition() {
		global $CFG, $DB;
		
		$mform = & $this->_form;
		// Copy center instructions
		$mform->addElement ( 'header', 'headerdate', get_string ( 'basicoptions', 'local_reservasalas' ) );
		$mform->addElement ( 'date_selector', 'fecha', get_string ( 'date', 'local_reservasalas' ) . ': ', array (
				'startyear' => date ( 'Y' ),
				'stopyear' => date ( 'Y' ) + 2 
		) );
		$sedeedificio = array ();
		$edificios = $DB->get_records ( 'reservasalas_edificios' );
		$sedeedificio [0] = " ";
		foreach ( $edificios as $edificio ) {
			$sede = $DB->get_record ( 'reservasalas_sedes', array (
					'id' => $edificio->sedes_id 
			) );
			$sedeedificio [$edificio->id] = $sede->nombre . " - " . $edificio->nombre;
		}
		
		$select = $mform->addElement ( 'select', 'SedeEdificio', get_string ( 'choose_buildings', 'local_reservasalas' ), $sedeedificio );
		if (has_capability ( 'local/reservasalas:advancesearch', context_system::instance () )) {
			
			if (has_capability ( 'local/reservasalas:typeroom', context_system::instance () )) {
				$options = array (
						0 => "",
						1 => get_string ( 'class', 'local_reservasalas' ),
						2 => get_string ( 'study', 'local_reservasalas' ),
						3 => get_string ( 'reunion', 'local_reservasalas' ) 
				);
				$mform->addElement ( 'select', 'roomstype', get_string ( 'selectTypeRoom', 'local_reservasalas' ) . ': ', $options );
				$mform->setDefault ( 'roomstype', '2' );
			}
			
			// Copy center instructions
			$mform->addElement ( 'header', 'headeradvanced', get_string ( 'advanceoptions', 'local_reservasalas' ) );
			$mform->setExpanded ( 'headeradvanced', false );
			
			$mform->addElement ( 'advcheckbox', 'addmultiply', get_string ( 'activateadvanceoptions', 'local_reservasalas' ) . ': ' );
			
			$mform->addElement ( 'date_selector', 'enddate', get_string ( 'enddate', 'local_reservasalas' ) );
			$mform->disabledIf ( 'enddate', 'addmultiply', 'notchecked' );
			
			$array = Array ();
			$array [] = $mform->createElement ( 'advcheckbox', 'monday', '', get_string ( 'monday', 'local_reservasalas' ) );
			$array [] = $mform->createElement ( 'advcheckbox', 'tuesday', '', get_string ( 'tuesday', 'local_reservasalas' ) );
			$array [] = $mform->createElement ( 'advcheckbox', 'wednesday', '', get_string ( 'wednesday', 'local_reservasalas' ) );
			$array [] = $mform->createElement ( 'advcheckbox', 'thursday', '', get_string ( 'thursday', 'local_reservasalas' ) );
			$array [] = $mform->createElement ( 'advcheckbox', 'friday', '', get_string ( 'friday', 'local_reservasalas' ) );
			$array [] = $mform->createElement ( 'advcheckbox', 'saturday', '', get_string ( 'saturday', 'local_reservasalas' ) );
			
			$mform->addGroup ( $array, 'ss', get_string ( 'select', 'local_reservasalas' ) );
			$mform->disabledIf ( 'ss', 'addmultiply', 'notchecked' );
			
			$selectArray = array ();
			$options = array (
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4' 
			);
			$selectArray [] = $mform->createElement ( 'select', 'frequency', '', $options );
			$selectArray [] = $mform->createElement ( 'static', '', '', get_string ( 'week', 'local_reservasalas' ) );
			$mform->addGroup ( $selectArray, 'fr', get_string ( 'frequency', 'local_reservasalas' ) );
			$mform->disabledIf ( 'fr', 'addmultiply', 'notchecked' );
			
			$options = array (
					'0' => get_string ( 'notapplicable', 'local_reservasalas' ),
					'1-25' => '0-25',
					'26-45' => '26-45',
					'46-75' => '46-75',
					'75-+' => '+75' 
			);
			
			$mform->addElement ( 'select', 'size', get_string ( 'capacity', 'local_reservasalas' ) . ': ', $options );
			$mform->disabledIf ( 'size', 'addmultiply', 'notchecked' );
		}
		$this->add_action_buttons ( false, get_string ( 'search', 'local_reservasalas' ) );
	}
	function validation($data, $files) {
		global $DB;
		
		// Se crea objeto DateTime
		$today = new DateTime ();
		// Se deja hora y minutos en 0 (medianoche)
		$today->setTime ( 0, 0, 0 );
		// Se clona objeto para hoy
		$week = clone $today;
		// Se calcula la misma fecha con 7 dias hacia adelante
		$week = $week->modify ( '+7 days' );
		
		$errors = array ();
		
		// Verifica que la fecha del formulario sea al menos hoy
		if ($data ['fecha'] < $today->getTimestamp ()) {
			$errors ['fecha'] = get_string ( 'checkthedate', 'local_reservasalas' );
		}
		
		// Verifica que la fecha solicitada no sea en Domingo
		if (date ( 'N', $data ['fecha'] ) == 7) {
			
			$errors ['fecha'] = get_string ( 'cannotreservesunday', 'local_reservasalas' );
		}
		
		if (! (has_capability ( 'local/reservasalas:libreryrules', context_system::instance () ))) {
			// Si no es biblioteca verifica que no reserve mas alla de 7 dias adelante
			if ($data ['fecha'] > $week->getTimestamp ()) {
				$errors ['fecha'] = get_string ( 'checkthedate', 'local_reservasalas' );
			}
		}
		
		if (has_capability ( 'local/reservasalas:typeroom', context_system::instance () )) {
			if ($data ['roomstype'] == 0) {
				$errors ['roomstype'] = get_string ( 'selectaroom', 'local_reservasalas' );
			}
		}
		if (isset ( $data ['alumno'] )) {
			if ($data ['alumno'] != "") {
				if (! $DB->get_record ( 'user', array (
						'username' => $data ['alumno'] 
				) )) {
					$errors ['alumno'] = get_string ( 'notexiststudent', 'local_reservasalas' );
				}
			}
		}
		if (! has_capability ( 'local/reservasalas:typeroom', context_system::instance () )) {
			if ($data ['SedeEdificio'] == 0) {
				$errors ['SedeEdificio'] = get_string ( 'selectbuilding', 'local_reservasalas' );
			} elseif (! $DB->get_records ( 'reservasalas_salas', array (
					'edificios_id' => $data ['SedeEdificio'],
					'tipo' => '2' 
			) )) {
				$errors ['SedeEdificio'] = get_string ( 'arenotrooms', 'local_reservasalas' );
			}
		}
		
		if (has_capability ( 'local/reservasalas:advancesearch', context_system::instance () )) {
			
			$diasArray = $data ['ss'];
			$fecha1 = mktime ( 0, 0, 0, date ( "m", $data ['fecha'] ), date ( "d", $data ['fecha'] ), date ( "Y", $data ['fecha'] ) );
			$fecha2 = mktime ( 0, 0, 0, date ( "m", $data ['enddate'] ), date ( "d", $data ['enddate'] ), date ( "Y", $data ['enddate'] ) );
			
			$diferencia = $fecha2 - $fecha1;
			$dias = $diferencia / (60 * 60 * 24);
			if ($data ['SedeEdificio'] == 0) {
				$errors ['SedeEdificio'] = get_string ( 'selectbuilding', 'local_reservasalas' );
			} else if (has_capability ( 'local/reservasalas:typeroom', context_system::instance () )) {
				
				if (! $DB->get_records ( 'reservasalas_salas', array (
						'edificios_id' => $data ['SedeEdificio'],
						'tipo' => $data ['roomstype'] 
				) )) {
					$errors ['SedeEdificio'] = get_string ( 'arenotrooms', 'local_reservasalas' );
				}
			} else if (! $DB->get_records ( 'reservasalas_modulos', array (
					'edificio_id' => $data ['SedeEdificio'] 
			) )) {
				$errors ['SedeEdificio'] = get_string ( 'arenotmodules', 'local_reservasalas' );
			}
			
			if ($data ['addmultiply'] == 1) {
				if ($diasArray ['monday'] == 0 && $diasArray ['tuesday'] == 0 && $diasArray ['wednesday'] == 0 && $diasArray ['thursday'] == 0 && $diasArray ['friday'] == 0 && $diasArray ['saturday'] == 0 && $diasArray ['sunday'] == 0) {
					
					$errors ['ss'] = get_string ( 'selectatleastoneday', 'local_reservasalas' );
				} else if ($dias < 7) {
					$param = false;
					for($i = 0; $i <= $dias; $i ++) {
						$siguiente = strtotime ( '+' . $i . ' day', $data ['fecha'] );
						
						$dia_siguiente = strtolower ( date ( "l", $siguiente ) );
						
						if ($diasArray [$dia_siguiente] == 1) {
							$i = $dias + 1;
							$param = true;
						}
					}
					if ($param == false) {
						$errors ['ss'] = get_string ( 'checkthedays', 'local_reservasalas' );
					}
				}
				if ($data ['enddate'] < $data ['fecha'] || $data ['fecha'] == $data ['enddate']) {
					$errors ['enddate'] = get_string ( 'checkthedate', 'local_reservasalas' );
				}
			}
		}
		
		return $errors;
	}
}

//Utilizado en reservausuarios.php, formulario para la busqueda del historial de reservas de un usuario especifico.
class searchUserReservations extends moodleform{
	function definition() {
		global $CFG, $DB, $OUTPUT;
		$usertype=$CFG->user;
		$mform =& $this->_form;
		$mform->addElement('text', 'usuario', get_string('uaiemail', 'local_reservasalas').': ');
		$mform->setType('usuario', PARAM_TEXT);
		$mform->addRule('usuario',get_string('indicateuser', 'local_reservasalas'),'required');
		$mform->addElement('static','','', $usertype);
		$mform->addElement('hidden','action','buscarusuario');
		$mform->setType('action', PARAM_ACTION);
		$this->add_action_buttons(false, get_string('search', 'local_reservasalas'));
	}
	function validation($data,$files) {
		global $DB;
		$errors=array();

		if(!$user = $DB->get_record('user', array('username'=>$data['usuario']))){
				
			$errors['usuario'] = '*'.get_string('notuser', 'local_reservasalas');
		}
		return $errors;
	}
}
