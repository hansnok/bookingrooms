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
defined ( 'MOODLE_INTERNAL' ) || die ();
require_once ('lib.php');

function myReservations($userid = null) {
	global $DB, $USER, $OUTPUT;
	
	if ($userid == null) {
		$user_id = $USER->id;
	} else {
		$user_id = $userid;
	}
	
	$table = new html_table ();
	$table->head = array (
			get_string ( 'date', 'local_reservasalas' ),
			get_string ( 'campus', 'local_reservasalas' ),
			get_string ( 'building', 'local_reservasalas' ),
			get_string ( 'rooms', 'local_reservasalas' ),
			get_string ( 'module', 'local_reservasalas' ),
			get_string ( 'confirm', 'local_reservasalas' ),
			get_string ( 'cancel', 'local_reservasalas' ) 
	);
	$reservas = $DB->get_records ( 'reservasalas_reservas', array (
			'alumno_id' => $user_id,
			'activa' => '1' 
	) );
	foreach ( $reservas as $reserva ) {
		
		if ($userid == null) {
			$con_url = new moodle_url ( 'misreservas.php', array (
					'action' => 'confirmar',
					'idreserva' => $reserva->id,
					'sesskey' => sesskey () 
			) ); // ('confirmar.php?idconfirmar='. $reserva->id);
			$del_url = new moodle_url ( 'misreservas.php', array (
					'action' => 'cancelar',
					'idreserva' => $reserva->id,
					'sesskey' => sesskey () 
			) );
		} else {
			$con_url = new moodle_url ( 'reservasusuarios.php', array (
					'action' => 'confirmar',
					'idreserva' => $reserva->id,
					'sesskey' => sesskey () 
			) ); // ('confirmar.php?idconfirmar='. $reserva->id);
			$del_url = new moodle_url ( 'reservasusuarios.php', array (
					'action' => 'cancelar',
					'idreserva' => $reserva->id,
					'sesskey' => sesskey () 
			) );
		}
		
		$sala = $DB->get_record ( 'reservasalas_salas', array (
				'id' => $reserva->salas_id 
		) );
		$edificio = $DB->get_record ( 'reservasalas_edificios', array (
				'id' => $sala->edificios_id 
		) );
		$sede = $DB->get_record ( 'reservasalas_sedes', array (
				'id' => $edificio->sedes_id 
		) );
		$modulo = $DB->get_record ( 'reservasalas_modulos', array (
				'id' => $reserva->modulo 
		) );
		
		$horamodulo = hora_modulo ( $reserva->modulo );
		$tiempoactual = time ();
		$horainicio = $horamodulo [0];
		$horafin = $horamodulo [1];
		$horainicio = $horainicio->modify ( '-5 minutes' );
		$antes = $horainicio->getTimestamp ();
		$horainicio = $horainicio->modify ( '+20 minutes' );
		$despues = $horainicio->getTimestamp ();
		$horainicio = $horainicio->modify ( '-80 minutes' );
		$horacancelar = $horainicio->getTimestamp ();
		if ($reserva->confirmado) {
			$confaction_reserva = 'Confirmado';
		} else if ($tiempoactual < $despues && $tiempoactual > $antes) {
			$confurl_reserva = $con_url;
			$conficon_reserva = new pix_icon ( 'i/valid', get_string ( 'confirm', 'local_reservasalas' ) );
			$confaction_reserva = $OUTPUT->action_icon ( $confurl_reserva, $conficon_reserva );
		} else if ($tiempoactual > $despues && $reserva->confirmado == 0) {
			$confaction_reserva = get_string ( 'thetimetoconfirm', 'local_reservasalas' );
		} else {
			$confaction_reserva = $OUTPUT->pix_icon ( 't/block', get_string ( 'stillcannotconfirm', 'local_reservasalas' ) );
		}
		
		if ($tiempoactual < $horacancelar) {
			$delurl_reserva = $del_url;
			$delicon_reserva = new pix_icon ( 'i/invalid', get_string ( 'cancel', 'local_reservasalas' ) );
			$delaction_reserva = $OUTPUT->action_icon ( $delurl_reserva, $delicon_reserva, new confirm_action ( get_string ( 'areyousuretocancel', 'local_reservasalas' ) ) );
		} else {
			$delaction_reserva = get_string ( 'timetocancel', 'local_reservasalas' );
		}
		
		$table->data [] = array (
				$reserva->fecha_reserva,
				$sede->nombre,
				$edificio->nombre,
				$sala->nombre,
				$modulo->nombre_modulo . "<br>(" . $modulo->hora_inicio . " - " . $modulo->hora_fin . ")",
				$confaction_reserva,
				$delaction_reserva 
		);
		;
	}
	$table->align = array (
			'center',
			'center',
			'center',
			'center',
			'center',
			'center',
			'center' 
	);
	$table->size = array (
			'12%',
			'16%',
			'14%',
			'14%',
			'16%',
			'14%',
			'14%' 
	);
	return $table;
}
		

