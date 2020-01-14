<?php
/*
Parent Progress View, a module for Moodle to allow the viewing of documents and pupil data by authorised parents.
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
 * Settings for the plugin
 *
 * @package tool_pushcommunications
 * @author Test Valley School
 */

defined('MOODLE_INTERNAL') || die();

$ADMIN->add(
	'messaging',
	new admin_category(
		'tool_pushcommunications',
		get_string('pluginname', 'tool_pushcommunications')
	)
);

$ADMIN->add(
 	'tool_pushcommunications',
	new admin_externalpage(
		'tool_pushcommunications_main',
		get_string('pluginname', 'tool_pushcommunications'),
		"{$CFG->wwwroot}/{$CFG->admin}/tool/pushcommunications/index.php",
		'tool/pushcommunications:send'
	)
);

