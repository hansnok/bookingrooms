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
 * This file keeps track of upgrades to the evaluaciones block
 *
 * Sometimes, changes between versions involve alterations to database structures
 * and other major things that may break installations.
 *
 * The upgrade function in this file will attempt to perform all the necessary
 * actions to upgrade your older installation to the current version.
 *
 * If there's something it cannot do itself, it will tell you what you need to do.
 *
 * The commands in here will all be database-neutral, using the methods of
 * database_manager class
 *
 * Please do not forget to use upgrade_set_timeout()
 * before any action that may take longer time to finish.
 *
 * @since 2.0
 * @package blocks
 * @copyright 2012 Jorge Villalon
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 *
 * @param int $oldversion
 * @param object $block
 */


function xmldb_local_bookingrooms_upgrade($oldversion) {
	global $CFG, $DB;
	
	// loads ddl manager and xmldb classes
	$dbman = $DB->get_manager();
	
	if ($oldversion < 2013051304) {
	
	
		// Define table bookingrooms_period to be created.
		$table = new xmldb_table('bookingrooms_period');
	
		// Adding fields to table reservasalas_modulos.
		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
		$table->add_field('period_name', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
		$table->add_field('start_time', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
		$table->add_field('finish_time', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
		$table->add_field('buildingsid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
	
		// Adding keys to table reservasalas_modulos.
		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
		$table->add_key('buildingsid', XMLDB_KEY_FOREIGN, array('buildingsid'), 'bookingrooms_buildings', array('id'));
	
		// Conditionally launch create table for bookingrooms_period.
		if (!$dbman->table_exists($table)) {
			$dbman->create_table($table);
		}
		
		// Define table bookingrooms_buildings to be created
		$table = new xmldb_table('bookingrooms_buildings');
		
		// Adding fields to table reservasalas_edificios
		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
		$table->add_field('name', XMLDB_TYPE_CHAR, '45', null, null, null, null);
		$table->add_field('campusid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
		
		// Adding keys to table reservasalas_edificios
		$table->add_key('id', XMLDB_KEY_PRIMARY, array('id'));
		$table->add_key('campusid', XMLDB_KEY_FOREIGN, array('campusid'), 'bookingrooms_campus', array('id'));
		
		// Conditionally launch create table for bookingrooms_buildings
		if (!$dbman->table_exists($table)) {
			$dbman->create_table($table);
		}
		
		// Define table bookingrooms_campus to be created
		$table = new xmldb_table('bookingrooms_campus');
		
		// Adding fields to table reservasalas_sedes
		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
		$table->add_field('name', XMLDB_TYPE_CHAR, '45', null, null, null, null);
		
		// Adding keys to table reservasalas_sedes
		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
		
		// Conditionally launch create table for bookingrooms_campus
		if (!$dbman->table_exists($table)) {
			$dbman->create_table($table);
		}
	
		// Define table bookingrooms_otherreservations to be created.
		$table = new xmldb_table('bookingrooms_otherreservations');
	
		// Adding fields to table reservasalas_otrasreservas.
		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
		$table->add_field('reserve_date', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
		$table->add_field('cration_date', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
		$table->add_field('period', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
		$table->add_field('userid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
		$table->add_field('user_comment', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
		$table->add_field('event_name', XMLDB_TYPE_CHAR, '50', null, null, null, 'No name');
		$table->add_field('event_attendees', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
		$table->add_field('event_comment', XMLDB_TYPE_TEXT, null, null, null, null, null);
		$table->add_field('ip', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
		$table->add_field('roomsid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
		$table->add_field('responsibleid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
	
		// Adding keys to table reservasalas_otrasreservas.
		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
		$table->add_key('roomsid', XMLDB_KEY_FOREIGN, array('roomsid'), 'bookingrooms_rooms', array('id'));
	
		// Conditionally launch create table for bookingrooms_otherreservations.
		if (!$dbman->table_exists($table)) {
			$dbman->create_table($table);
		}
		
		// Define table bookingrooms_rooms to be created
		$table = new xmldb_table('bookingrooms_rooms');
		
		// Adding fields to table reservasalas_salas
		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
		$table->add_field('name', XMLDB_TYPE_CHAR, '45', null, null, null, null);
		$table->add_field('pac_name', XMLDB_TYPE_CHAR, '45', null, null, null, null);
		$table->add_field('buildingsid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
		$table->add_field('tipo', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
		$table->add_field('capacidad', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
		
		// Adding keys to table reservasalas_salas
		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
		$table->add_key('buildingsid', XMLDB_KEY_FOREIGN, array('buildingsid'), 'bookingrooms_buildings', array('id'));
		
		// Conditionally launch create table for bookingrooms_rooms
		if (!$dbman->table_exists($table)) {
			$dbman->create_table($table);
		}
	
	
		// Define table reservasalas_salarecursos to be created.
		$table = new xmldb_table('bookingrooms_resoursesrooms');
	
		// Adding fields to table reservasalas_salarecursos.
		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
		$table->add_field('roomsid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
		$table->add_field('resoursesid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
	
		// Adding keys to table reservasalas_salarecursos.
		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
		$table->add_key('resoursesid', XMLDB_KEY_FOREIGN, array('resoursesid'), 'bookingrooms_resourses', array('id'));
		$table->add_key('roomsid', XMLDB_KEY_FOREIGN, array('roomsid'), 'bookingrooms_rooms', array('id'));
	
		// Conditionally launch create table for bookingrooms_resoursesrooms.
		if (!$dbman->table_exists($table)) {
			$dbman->create_table($table);
		}
	
	
		// Define table bookingrooms_resourses to be created.
		$table = new xmldb_table('bookingrooms_resourses');
	
		// Adding fields to table reservasalas_recursos.
		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
		$table->add_field('name', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
	
		// Adding keys to table reservasalas_recursos.
		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
	
		// Conditionally launch create table for bookingrooms_resourses.
		if (!$dbman->table_exists($table)) {
			$dbman->create_table($table);
		}
		//Define table bookingrooms_reservations to be created
		$table = new xmldb_table('bookingrooms_reservations');
		
		// Adding fields to table reservasalas_reservas
		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
		$table->add_field('reservation_date', XMLDB_TYPE_CHAR, '20', null, null, null, null);
		$table->add_field('period', XMLDB_TYPE_INTEGER, '2', null, null, null, null);
		$table->add_field('confirmed', XMLDB_TYPE_BINARY, null, null, null, null, null);
		$table->add_field('active', XMLDB_TYPE_BINARY, null, null, null, null, null);
		$table->add_field('studentid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
		$table->add_field('roomsid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
		$table->add_field('student_comment', XMLDB_TYPE_TEXT, null, null, null, null, null);
		$table->add_field('admin_comment', XMLDB_TYPE_TEXT, null, null, null, null, null);
		$table->add_field('ip', XMLDB_TYPE_CHAR, '50', null, null, null, null);
		$table->add_field('creation_date', XMLDB_TYPE_CHAR, '20', null, null, null, null);
		$table->add_field('nombre_evento', XMLDB_TYPE_TEXT, null, null, null, null, null);
		$table->add_field('attendees', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
		
		// Adding keys to table reservasalas_reservas
		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
		$table->add_key('roomsid', XMLDB_KEY_FOREIGN, array('roomsid'), 'bookingrooms_rooms', array('id'));
		
		// Conditionally launch create table for bookingrooms_reservations
		if (!$dbman->table_exists($table)) {
			$dbman->create_table($table);
		}
		
		// Define table bookingrooms_blocked to be created
		$table = new xmldb_table('bookingrooms_blocked');
		
		// Adding fields to table reservasalas_bloqueados
		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
		$table->add_field('block:date', XMLDB_TYPE_CHAR, '20', null, null, null, null);
		$table->add_field('reservation_date', XMLDB_TYPE_CHAR, '20', null, null, null, null);
		$table->add_field('status', XMLDB_TYPE_BINARY, null, null, null, null, null);
		$table->add_field('comment', XMLDB_TYPE_CHAR, '150', null, null, null, null);
		$table->add_field('studentid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
		
		// Adding keys to table reservasalas_bloqueados
		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
		
		// Conditionally launch create table for bookingrooms_blocked
		if (!$dbman->table_exists($table)) {
			$dbman->create_table($table);
		}
	
		upgrade_plugin_savepoint(true, 2015053101, 'local', 'bookingrooms');
	
	}
	
	
	
	return true;
	}