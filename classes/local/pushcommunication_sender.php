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
 * A utility class for sending a push communication.
 *
 * @package tool_pushcommunications
 * @copyright 2019-2020 Test Valley School {@link https://www.testvalley.hants.sch.uk/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_pushcommunications\local;

/**
 * A utility class for sending a push communication.
 *
 * @package tool_pushcommunications
 * @copyright 2019-2020 Test Valley School {@link https://www.testvalley.hants.sch.uk/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class pushcommunication_sender {

	/*
	 * Send a push communication to a specified Moodle user.
	 *
	 * @param $user A Moodle user account as an stdClass
	 * @param $data A stdClass with a member communication_content
	 *
	 * @return bool
	 */
	public function send_message($user, $data) {
		global $CFG;

		require_once($CFG->dirroot . '/message/output/airnotifier/message_output_airnotifier.php');

		error_log('Invoked send_message');


		// check user is valid
		if (!($user instanceof \stdClass)) {
			throw new \Exception('user must be an instance of stdClass');
		}

		$eventdata = new \stdClass();


		$eventdata->userto = $user; 
		if ($eventdata->userto == NULL) {
			throw new \Exception('user to send message to was null');
		}


		$eventdata->fullmessage = $data->communication_content;


		$airnotifier_sender = new \message_output_airnotifier();
		return $airnotifier_sender->send_message($eventdata);
	}	
};
