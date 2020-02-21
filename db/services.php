<?php
/*
Push Communications Tool for Moodle
    Copyright (C) 2016-20 Test Valley School.

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License,
    or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/


/**
 * Web services definitions.
 * @package tool_pushcommunications
 * @author Test Valley School
 */

defined('MOODLE_INTERNAL') || die();

$services = [
	'pushcommunications' => [
		'functions' => [ 'tool_pushcommunications_send_push_communication' ],
		'requiredcapability' => 'tool/pushcommunications:send',
		'restrictedusers'    => true,
		'enabled'            => true
	]
];

$functions = [
	'tool_pushcommunications_send_push_communication' => [
		'classname'          => 'tool_pushcommunications_external',
		'methodname'         => 'send_push_communication',
		'classpath'          => 'admin/tool/pushcommunications/externallib.php',
		'description'        => 'Send a push communication to a registered user\'s mobile device',
		'type'               => 'write',
		'ajax'               => true /* not sure about this */
	]
];
