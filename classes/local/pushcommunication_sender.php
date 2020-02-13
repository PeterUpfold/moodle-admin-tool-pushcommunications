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
	 * @param $data A stdClass with a member communication_content and a member intent with a tvsmoodlemobile://?redirect= URL
	 *
	 * @return bool
	 */
	public function send_message($user, $data) {
		global $CFG, $DB;

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
		$eventdata->intent = $data->intent; // the URL internally in the Moodle app to take the user (in tvsmoodlemobile://?redirect=[original link] form)

		// also send core\message\message -- this populates Notifications tab in app
		require_once($CFG->dirroot . '/lib/messagelib.php');

		//var_dump(\message_get_providers_for_user($user));

		/*$message = new \core\message\message();
		$message->component = 'tool_pushcommunications';
		$message->name = $data->communication_content;
		$message->userfrom = \core_user::get_support_user();
		$message->userto = $user;
		$message->subject = $data->communication_content;
		$message->contexturlname = $data->communication_content;
		$message->fullmessage = $data->communication_content;
		$message->fullmessageformat = FORMAT_PLAIN;
		$message->smallmessage = $data->communication_content;
		$message->contexturl = $data->intent;
		$message->notification = true;
		var_dump($message);
		$message_id = \message_send($message);
		if ($message_id) {
			\debugging('Message sent as ID ' . $message_id, DEBUG_DEVELOPER);
		}
		else {
			\debugging('Message ID was false -- did not send.', DEBUG_DEVELOPER);
		}*/

		//TODO: use a proper API
		$obj = new \stdClass();
		$obj->useridfrom = (\core_user::get_support_user())->id;
		$obj->useridto = $user->id;
		$obj->subject = $data->communication_content;
		$obj->fullmessage = $data->communication_content;
		$obj->fullmessageformat = 2;
		$obj->fullmessagehtml = $data->communication_content;
		$obj->smallmessage = $data->communication_content;
		$obj->component = 'tool_pushcommunications';
		$obj->eventtype = 'pushcommunication';
		$obj->contexturl = $data->intent;
		$obj->contexturlname = 'Intent';
		$obj->timeread = NULL;
		$obj->timecreated = time();
		$obj->customdata = '';

		$message_id = $DB->insert_record('notifications', $obj);

		$popup_obj = new \stdClass();
		$popup_obj->notificationid = $message_id;
		$popup_id = $DB->insert_record('message_popup_notifications', $popup_obj);


		$airnotifier_sender = new \message_output_airnotifier();
		return $airnotifier_sender->send_message($eventdata);
	}	
};
