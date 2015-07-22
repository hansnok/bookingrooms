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

	$dbman = $DB->get_manager();
	if ($oldversion < 2015072202) {
	
		// Define table bookingrooms_campus to be created.
		$table = new xmldb_table('bookingrooms_campus');
	
		// Adding fields to table bookingrooms_campus.
		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
		$table->add_field('name', XMLDB_TYPE_CHAR, '45', null, null, null, null);
	
		// Adding keys to table bookingrooms_campus.
		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
	
		// Conditionally launch create table for bookingrooms_campus.
		if (!$dbman->table_exists($table)) {
			$dbman->create_table($table);
		}
		// Define table bookingrooms_buildings to be created.
		$table = new xmldb_table('bookingrooms_buildings');
		
		// Adding fields to table bookingrooms_buildings.
		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
		$table->add_field('name', XMLDB_TYPE_CHAR, '45', null, null, null, null);
		$table->add_field('campus_id', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
		
		// Adding keys to table bookingrooms_buildings.
		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
		$table->add_key('campus_id', XMLDB_KEY_FOREIGN, array('campus_id'), 'bookingrooms_campus', array('id'));
		
		// Conditionally launch create table for bookingrooms_buildings.
		if (!$dbman->table_exists($table)) {
			$dbman->create_table($table);
		}
		// Define table bookingrooms_rooms to be created.
		$table = new xmldb_table('bookingrooms_rooms');
		
		// Adding fields to table bookingrooms_rooms.
		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
		$table->add_field('name', XMLDB_TYPE_CHAR, '45', null, null, null, null);
		$table->add_field('name_pc', XMLDB_TYPE_CHAR, '45', null, null, null, null);
		$table->add_field('buildings_id', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
		$table->add_field('type', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
		$table->add_field('capaciy', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
		
		// Adding keys to table bookingrooms_rooms.
		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
		$table->add_key('buildings_id', XMLDB_KEY_FOREIGN, array('buildings_id'), 'bookingrooms_buildings', array('id'));
		
		// Conditionally launch create table for bookingrooms_rooms.
		if (!$dbman->table_exists($table)) {
			$dbman->create_table($table);
		}
		// Define table bookingrooms_reserves to be created.
		$table = new xmldb_table('bookingrooms_reserves');
		
		// Adding fields to table bookingrooms_reserves.
		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
		$table->add_field('date_reserve', XMLDB_TYPE_CHAR, '20', null, null, null, null);
		$table->add_field('module', XMLDB_TYPE_INTEGER, '2', null, null, null, null);
		$table->add_field('confirmed', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
		$table->add_field('active', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
		$table->add_field('student_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
		$table->add_field('rooms_id', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
		$table->add_field('coment_student', XMLDB_TYPE_TEXT, null, null, null, null, null);
		$table->add_field('coment_admin', XMLDB_TYPE_TEXT, null, null, null, null, null);
		$table->add_field('ip', XMLDB_TYPE_CHAR, '50', null, null, null, null);
		$table->add_field('date_creation', XMLDB_TYPE_CHAR, '20', null, null, null, null);
		$table->add_field('name_event', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
		$table->add_field('assistant', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
		
		// Adding keys to table bookingrooms_reserves.
		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
		$table->add_key('rooms_id', XMLDB_KEY_FOREIGN, array('rooms_id'), 'bookingrooms_rooms', array('id'));
		
		// Conditionally launch create table for bookingrooms_reserves.
		if (!$dbman->table_exists($table)) {
			$dbman->create_table($table);
		}
		// Define table bookingrooms_blocked to be created.
		$table = new xmldb_table('bookingrooms_blocked');
		
		// Adding fields to table bookingrooms_blocked.
		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
		$table->add_field('date_block', XMLDB_TYPE_CHAR, '20', null, null, null, null);
		$table->add_field('id_reserve', XMLDB_TYPE_CHAR, '20', null, null, null, null);
		$table->add_field('status', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
		$table->add_field('commentary', XMLDB_TYPE_CHAR, '150', null, null, null, null);
		$table->add_field('student_id', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
		
		// Adding keys to table bookingrooms_blocked.
		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
		
		// Conditionally launch create table for bookingrooms_blocked.
		if (!$dbman->table_exists($table)) {
			$dbman->create_table($table);
		}
		// Define table bookingrooms_modules to be created.
		$table = new xmldb_table('bookingrooms_modules');
		
		// Adding fields to table bookingrooms_modules.
		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
		$table->add_field('name_module', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
		$table->add_field('hour_start', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
		$table->add_field('hour_end', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
		$table->add_field('building_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
		
		// Adding keys to table bookingrooms_modules.
		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
		$table->add_key('building_id', XMLDB_KEY_FOREIGN, array('building_id'), 'bookingrooms_buildings', array('id'));
		
		// Conditionally launch create table for bookingrooms_modules.
		if (!$dbman->table_exists($table)) {
			$dbman->create_table($table);
		}
		// Define table bookingrooms_otherreserves to be created.
		$table = new xmldb_table('bookingrooms_otherreserves');
		
		// Adding fields to table bookingrooms_otherreserves.
		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
		$table->add_field('date_reserve', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
		$table->add_field('date_creation', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
		$table->add_field('module', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
		$table->add_field('user_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
		$table->add_field('coment_user', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
		$table->add_field('name_event', XMLDB_TYPE_CHAR, '50', null, null, null, 'No name');
		$table->add_field('assistant_event', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
		$table->add_field('coment_event', XMLDB_TYPE_TEXT, null, null, null, null, null);
		$table->add_field('ip', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
		$table->add_field('rooms_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
		$table->add_field('id_responsible', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
		
		// Adding keys to table bookingrooms_otherreserves.
		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
		$table->add_key('rooms_id', XMLDB_KEY_FOREIGN, array('rooms_id'), 'bookingrooms_rooms', array('id'));
		
		// Conditionally launch create table for bookingrooms_otherreserves.
		if (!$dbman->table_exists($table)) {
			$dbman->create_table($table);
		}
		// Define table bookingrooms_roomresource to be created.
		$table = new xmldb_table('bookingrooms_roomresource');
		
		// Adding fields to table bookingrooms_roomresource.
		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
		$table->add_field('rooms_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
		$table->add_field('resources_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
		
		// Adding keys to table bookingrooms_roomresource.
		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
		$table->add_key('resources_id', XMLDB_KEY_FOREIGN, array('resources_id'), 'bookingrooms_resorces', array('id'));
		$table->add_key('rooms_id', XMLDB_KEY_FOREIGN, array('rooms_id'), 'bookingrooms_rooms', array('id'));
		
		// Conditionally launch create table for bookingrooms_roomresource.
		if (!$dbman->table_exists($table)) {
			$dbman->create_table($table);
		}   
		// Define table bookingrooms_resources to be created.
        $table = new xmldb_table('bookingrooms_resources');

        // Adding fields to table bookingrooms_resources.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table bookingrooms_resources.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for bookingrooms_resources.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
	
		// Bookingrooms savepoint reached.
		upgrade_plugin_savepoint(true, 2015072202, 'local', 'bookingrooms');
	}	
	return true;
	}
