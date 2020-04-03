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

	/**
	 * Return a user record from email address. Note that the email address may not be unique,
	 * and this will return the first user matched.
	 *
	 * @param string $email Email address.
	 *
	 * @return stdClass user
	 */
	public function get_user_from_email_address($email) {
		global $DB, $CFG;
		/*  note that login/lib.php:100 explains that the email address may not be unique. As that code does,
		 *  we will naively ignore multiple, which I guess takes the first valid result and will send to that
		 *  user only.
		 */
		$select = $DB->sql_like('email', ':email', false, true, false, '|') .
			" AND mnethostid = :mnethostid AND deleted=0 AND suspended=0";
		$select_params = array('email' => $DB->sql_like_escape($email, '|'), 'mnethostid' => $CFG->mnet_localhost_id);
		$user = $DB->get_record_select('user', $select, $select_params, '*', IGNORE_MULTIPLE);
		return $user;
	}

	/**
	 * Format the intent properly as (for example) tvsmoodlemobile://?redirect=[original link]
	 *
	 * @param string $intent The original intent URL
	 *
	 * @return string Formatted intent URL with mobile scheme.
	 */
	public function format_intent($intent) {
		// a bit of a hack -- determine the correct app URI scheme based on the last part of the android_app identifier from tool_mobile
		$mobilesettings = \get_config('tool_mobile');
		$appid = explode('.', $mobilesettings->androidappid);

		return $appid[count($appid)-1] . '://?redirect=' . $intent; 
	}

	/**
	 * Return each user in the target cohort.
	 * System context cohorts only at this time.
	 *
	 * @param int $cohort_id Numeric identifier of the cohort.
	 *
	 * @return array List of user objects
	 */
	public function find_users_in_cohort($cohort_id) {
		global $CFG;
		require_once("$CFG->dirroot/cohort/locallib.php");

		$context = \context_system::instance();
		$selector = new \cohort_existing_selector('cohort-selector', [ 'cohortid' => $cohort_id, 'accesscontext' => $context ]);

		$cohort_users = $selector->find_users('');

		if (!is_array($cohort_users)) {
			throw new \Exception('Failed to search the cohort for current users.');
		}
		if (!array_key_exists('Current users', $cohort_users)) {
			throw new \Exception('Failed to identify any current users in the cohort.');
		}

		return $cohort_users['Current users'];
	}

	/**
	 * For each user in the target cohort, find the users which have parent role in the context of that user.
	 * System context cohorts only at this time.
	 *
	 * @param int $cohort_id Numeric identifier of cohort
	 *
	 * @return array List of user objects
	 */
	public function find_parent_users_in_cohort($cohort_id) {
		global $CFG;

		// look up 'magic' parent role ID
		if (!property_exists($CFG, 'report_parentprogressview_parent_roleid')) {
			throw new \Exception('The configuration settings for Parent Progress View could not be found. The report_parentprogressview module must be installed and the Parent Role ID configuration setting must be set in order to use cohort lookup.');
		}
		$roleid = (int)$CFG->report_parentprogressview_parent_roleid;
		if ($roleid === 0) {
			throw new \Exception('The configuration settings for Parent Progress View could not be evaluated correctly. The report_parentprogressview module must be installed and the Parent Role ID configuration setting must be set in order to use cohort lookup.');
		}

		$recipient_users = [];
	
		foreach($this->find_users_in_cohort($cohort_id) as $pupil) {
			// find parents with the roleid attached to this pupil and add them to recipients
			$user_context = \context_user::instance($pupil->id);
			$role_users = \get_role_users($roleid, $user_context, false, 'u.id, u.username, ' . get_all_user_name_fields(true, 'u'));

			// for each parent
			foreach($role_users as $parent) {
				$recipient_users[] = $parent;
				\debugging('Add parent ' . $parent->username . ' to pupil ' . $pupil->id, DEBUG_DEVELOPER);
			}
		}
		return $recipient_users;
	}	

	/**
	 * Get a cohort object from its name.
	 *
	 * @param string $cohort_name The name of the cohort.
	 *
	 * @return stdClass cohort
	 */
	public function get_cohort_by_name($cohort_name) {
		global $DB;

		return $DB->get_record('cohort', [ 'name' => $cohort_name ]);
	}

};
