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
if ($data = $form->get_data()) {

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

		// look up 'magic' parent role ID
		if (!property_exists($CFG, 'report_parentprogressview_parent_roleid')) {
			throw new \Exception('The configuration settings for Parent Progress View could not be found. The report_parentprogressview module must be installed and the Parent Role ID configuration setting must be set in order to use cohort lookup.');
		}
		$roleid = (int)$CFG->report_parentprogressview_parent_roleid;
		if ($roleid === 0) {
			throw new \Exception('The configuration settings for Parent Progress View could not be evaluated correctly. The report_parentprogressview module must be installed and the Parent Role ID configuration setting must be set in order to use cohort lookup.');
		}


		// for each user in the target cohort, find the users which have parent role in the context of that user and add to recipient_users

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

	require_once(__DIR__ . '/classes/local/pushcommunication_sender.php');
	$sender = new \tool_pushcommunications\local\pushcommunication_sender();

	foreach($recipient_users as $user) {
		if ($sender->send_message($user, $data)) {
			\debugging('Successfully sent a push notification via AirNotifier', DEBUG_DEVELOPER);
		}
		else {
			throw new \Exception('The call to send the AirNotifier message did not succeed.');
		}
	}
}

// create renderable
$renderable = new \tool_pushcommunications\output\index_page();

echo $output->render($renderable);
echo $output->footer();
