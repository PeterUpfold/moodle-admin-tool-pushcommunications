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


/********
 * Set up a user for these purposes by following
 * admin/settings.php?section=webservicesoverview
 ********/

class tool_pushcommunications_external extends external_api {
	/**
	 * Defines the parameters for the send_push_communication method.
	 *
	 * @return external_function_parameters
	 */
	public static function send_push_communication_parameters() {
		return new external_function_parameters([
			'email'   => new external_value(PARAM_EMAIL, 'Email address of the target user to send the push communication to'),
			'content' => new external_value(PARAM_TEXT, 'Content of the push communication. Max 2kbytes')
		]);
	}

	/**
	 * Defines the return values for the send_push_communication method.
	 *
	 * @return external_single_structure
	 */
	public static function send_push_communication_returns() {
		return new external_single_structure([
			'success' => new external_value(PARAM_BOOL, 'success or failure'),
			'details' => new external_value(PARAM_TEXT, 'any further details -- i.e. error details')
		]);
	}

	/**
	 * Send a push communication to a specified user (by their email address).
	 */
	public static function send_push_communication($email, $content) {
		global $CFG, $DB;
		require_once(__DIR__ . '/classes/local/pushcommunication_sender.php');
		$sender = new \tool_pushcommunications\local\pushcommunication_sender();

		$params = self::validate_parameters(self::send_push_communication_parameters(), [ 'email' => $email, 'content' => $content ]);
		self::validate_context(context_system::instance()); // performs permissions checks

		// check capability
		if (!has_capability('tool/pushcommunications:send', context_system::instance())) {
			return [
				'success' => false,
				'details' => get_string('no_capability', 'tool_pushcommunication')
			];
		}

		// look up user from email address
		$user = $sender->get_user_from_email_address($params['email']);

		if (!$user) {
			return [
				'success' => false,
				'details' => get_string('unable_to_find_user', 'tool_pushcommunications')
			];	
		}

		// format content
		$data = new \stdClass();
		$data->communication_content = $params['content'];
		$data->intent = $CFG->wwwroot;


		// call into pushcommunication_sender->send_message
		if ($sender->send_message($user, $data)) {
			return [
				'success' => true,
				'details' => ''
			];
		}
		else {
			return [
				'success' => false,
				'details' => get_string('send_message_failed', 'tool_pushcommunications')
			];
		}
	}
};
