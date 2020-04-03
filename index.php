<?php

// This module for Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This module for Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Send out push communications to the mobile app.
 *
 * @package tool_pushcommunications
 * @copyright 2019-2020 Test Valley School {@link https://www.testvalley.hants.sch.uk/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('tool_pushcommunications_main', '', null, '', []);
// admin_externalpage_setup does access validation checks for us

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/admin/tool/pushcommunications/index.php'));
$PAGE->set_title(get_string('pageheader', 'tool_pushcommunications'));
$PAGE->set_pagelayout('admin');

$output = $PAGE->get_renderer('tool_pushcommunications');

echo $output->header();
echo $output->heading($pagetitle);

// handle the submission of the push communication send form
require_once("$CFG->dirroot/admin/user/lib.php");
require_once("$CFG->dirroot/cohort/lib.php");

$form_populate_data = \get_selection_data(new \user_filtering());
$form_populate_data['cohorts'] = \cohort_get_all_cohorts(0, 100, '');

$form = new \tool_pushcommunications\local\composepushcommunication_form(null, $form_populate_data, 'post');

$result = '';

if ($data = $form->get_data()) {
	require_once(__DIR__ . '/classes/local/pushcommunication_sender.php');
	$sender = new \tool_pushcommunications\local\pushcommunication_sender();

	$recipient_users = []; // these Moodle user IDs will receive the push communication

	// was a cohort send chosen?
	//
	if ($data->target_cohort != 0) {

		// check cohort is valid

		$valid_cohorts = [];
		foreach ($form_populate_data['cohorts']['cohorts'] as $cohort) {
			$valid_cohorts[] = $cohort->id;
		}

		if (!in_array($data->target_cohort, $valid_cohorts)) {
			throw new \InvalidArgumentException('An invalid cohort ID was specified');
		}

		// for each user in the target cohort, find the users which have parent role in the context of that user and add to recipient_users
		$recipient_users = array_merge($recipient_users, $sender->find_parent_users_in_cohort($data->target_cohort));
	}

	// individual send
	else {
		// look up individual user id in $data->target
		//
		require_once("$CFG->dirroot/user/lib.php");
		$users = \user_get_users_by_id([$data->target]);
		if (count($users) != 1) {
			throw new \Exception('Unable to locate one user with the passed user id.');
		}	

		$recipient_users[] = array_values($users)[0]; // array is keyed by user id
	}

	// de-duplicate recipient_users
	$seen_recipient_user_ids = [];
	$recipient_users_final = [];
	foreach($recipient_users as $recipient_user) {
		if (!in_array($recipient_user->id, $seen_recipient_user_ids)) {
			$recipient_users_final[] = $recipient_user;
			$seen_recipient_user_ids[] = $recipient_user->id;
		}
		else {
			\debugging('Deduplicating user '. $recipient_user->id, DEBUG_DEVELOPER);
		}
	}




	// format the intent properly-- tvsmoodlemobile://?redirect=[original link]
	if (!empty($data->intent)) {
		$this->format_intent($data->intent);
	}

	$sent_pushes = 0;

	foreach($recipient_users_final as $user) {
		\debugging('Attempt to send to user ' . $user->id . ' ' . $user->username . ' by AirNotifier', DEBUG_DEVELOPER);
		if ($sender->send_message($user, $data)) {
			\debugging('Successfully sent a push notification via AirNotifier', DEBUG_DEVELOPER);
			++$sent_pushes;
		}
		else {
			throw new \Exception('The call to send the AirNotifier message did not succeed.');
		}
	}

	$result = get_string('sent_pushes', 'tool_pushcommunications', $sent_pushes);
}

// create renderable
$renderable = new \tool_pushcommunications\output\index_page($result);

echo $output->render($renderable);
echo $output->footer();
