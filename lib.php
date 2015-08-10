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
//function library, automatically included with by config.php

//Define here the functions that you would use in some pages.
defined('MOODLE_INTERNAL') || die();
require_once(dirname(__FILE__) . '/../../config.php'); //requiered
require_once("$CFG->libdir/formslib.php");

function hour_module($module) {
	global $DB,$USER;
	//in format HH:MM:SS (varchar)
	//Define lthe hours for misreservas.php

	$modules=$DB->get_record('bookingrooms_modules',array("id"=>$module));
	
	$start=explode(":",$modules->hour_start);
	$end=explode(":",$modules->hour_end);
	
	$a=$start[0];$b=$start[1];$c=00; //hour;minute;second
	$d=$end[0];$e=(int)$end[1];$f=00; //hour;minute;second
	$minutes = str_replace(' ', '',$e);
	
	$ModuleStart = new DateTime();
	// Is left hour and minutes in 0 (midnigth)
	$ModuleStart->setTime($a,$b,0);
	$ModuleEnd = new DateTime();
	// Is left hour and minutes in 0 (midnigth)
	$ModuleEnd->setTime($d,$e,0);
$hour=array(
		$ModuleStart,
		$ModuloEnd
);
//var_dump($hour);
	return $hour;
	//returns $hour[start] y $hour[end]
}
function module_hour($unixtime, $factor = null){

	$hour = date('G', $unixtime);
	$minute = date('i', $unixtime);
	$second = $hour*60*60 + $minute*60;
	if($factor== null){
		$factor = 15*60;
	}
	
	if($second > 19*60*60 + 10*60 +$factor){
		return 8;
	}else if($second > 15*60*60 + 40*60 + $factor){
		return 7;
	}else if($second > 16*60*60 + 10*60 + $factor){
		return 6;
	}else if($second > 14*60*60 + 10*60 + $factor){
		return 5;
	}else if($second > 12*60*60 + 40*60 + $factor){
		return 4;
	}else if($second > 11*60*60 + 30*60 + $factor){
		return 3;
	}else if($second > 10*60*60 + 0*60 + $factor){
		return 2;
	}else if($second > 8*60*60 + 30*60 + $factor){
		return 1;
	}else{
		return 0;
	}
}

function booking_availability($date){
	global $DB,$USER,$CFG;
	//format YYYY-MM-DD
	$today = date('Y-m-d',time());
	if( !$isbloked = $DB->get_record('bookingrooms_blocked', array("student_id"=>$USER->id, 'status'=>1))){
		
		$sqlWeekBookings = "SELECT *
						FROM {bookingrooms_reserves}
						WHERE date_reserve >= ?
						AND date_reserve <= ADDDATE(?, 7)
						AND student_id = ? AND active = 1";
	
		$weekBookings = $DB->get_records_sql($sqlWeekBookings, array($today, $today, $USER->id));
		$todayBookings = $DB->count_records ( 'bookingrooms_reserves', array (
				'student_id' => $USER->id,
				'date_reserve' => date('Y-m-d',$date),
				'active' => 1));
	
		$books= array(count($weekBookings),$todayBookings);

	}else{
		$books = array($CFG->reservesWeek,$CFG->reservesDay);
	}
	return $books;
}

