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
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

class tool_pushcommunications_external extends external_api {
	/**
	 * Defines the parameters for the send_push_communication method.
	 *
	 * @return external_function_parameters
	 */
	public static function send_push_communication_parameters() {
		return new external_function_parameters([
			new external_single_structure([
				'email'   => new external_value(PARAM_EMAIL, 'Email address of the target user to send the push communication to'),
				'content' => new external_value(PARAM_TEXT, 'Content of the push communication. Max 2kbytes')
			])
		]);
	}

	/**
	 * Defines the return values for the send_push_communication method.
	 *
	 * @return external_single_structure
	 */
	public static function send_push_communication_returns() {
		return new external_single_structure([ 'success' => new external_value(PARAM_BOOL, 'success or failure') ]);
	}

	/**
	 * Send a push communication to a specified user (by their email address).
	 */
	public static function send_push_communication($user) {
		$params = self::validate_parameters(self::send_push_communication_parameters(), [ 'user' => $user ]);

		// look up user from email address
		//
		// call into pushcommunication_sender->send_message

	}
};
