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

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../../config.php');
require_once($CFG->libdir . '/clilib.php');

list($options, $unrecognised) = cli_get_params(
[
	'emails'     => false, /* seems to be 'false' by convention and the value has no meaning? */
	'cohorts'    => false,
	'to-parents' => false, /* send to parents of the cohort, not the cohort themselves */
	'message'    => false,
	'url'        => false,
	'help'       => false 
], [ /* short option aliases */
	'h'          => 'help',
	'p'          => 'to-parents'
]
);

if ($unrecognised) {
	$unrecognised = implode(PHP_EOL. ' ', $unrecognised);
	cli_error(get_string('cliunknowoption', 'core_admin', $unrecognised)); // yep 'unknow' option is the string
}

if ($options['help']) {
	cli_writeln(get_string('cli_send_help', 'tool_pushcommunications'));
	exit(2);
}

$emails = explode(',', $options['emails']);
$cohorts = explode(',', $options['cohorts']);

// detect missing arguments which explode to an array of string(0)"" 
if ($emails === false || (count($emails) == 1 && empty($emails[0]))) {
	$emails = [];
}
if ($cohorts === false || (count($cohorts) == 1 && empty($cohorts[0]))) {
	$cohorts = [];
}

require_once(__DIR__ . '/../classes/local/pushcommunication_sender.php');

if (count($emails) < 1 && count($cohorts) < 1) {
	cli_error(get_string('cli_no_users_selected', 'tool_pushcommunications'));
}

if (empty($options['message'])) {
	cli_error(get_string('cli_no_message', 'tool_pushcommunications'));
}

$sender = new \tool_pushcommunications\local\pushcommunication_sender();

// prepare $data
$data = new \stdClass();
$data->communication_content = $options['message'];
$data->intent = '';
if (parse_url($options['url'], PHP_URL_HOST) == parse_url($CFG->wwwroot, PHP_URL_HOST)) {
	$data->intent = $options['url'];
}

$recipient_users = [];

foreach($emails as $email) {
	$user = $sender->get_user_from_email_address($email);
	if (!$user) {
		cli_writeln(get_string('cli_unable_to_find_user', 'tool_pushcommunications', $email) . PHP_EOL);
		continue;
	}
	$recipient_users[] = $user;
}

foreach($cohorts as $cohort) {

	// look up cohort id from name
	$cohort_id = ($sender->get_cohort_by_name($cohort))->id;

	if (!$cohort_id) {
		cli_writeln(get_string('cli_unable_to_find_cohort', 'tool_pushcommunications', $cohort) . PHP_EOL);
		continue;
	}

	if ($options['to-parents']) {
		try {
			$recipient_users = array_merge($recipient_users, $sender->find_parent_users_in_cohort($cohort_id));	
		}
		catch (\Exception $e) {
			cli_writeln($cohort . ': ');
			cli_writeln(get_string('cli_exception_on_cohort', 'tool_pushcommunications', $e->getMessage()) . PHP_EOL);
			continue;
		}
	}
	else {
		try {
			$recipient_users = array_merge($recipient_users, $sender->find_users_in_cohort($cohort_id));	
		}
		catch (\Exception $e) {
			cli_writeln($cohort . ': ');
			cli_writeln(get_string('cli_exception_on_cohort', 'tool_pushcommunications', $e->getMessage()) . PHP_EOL);
			continue;
		}
	}
}

if (count($recipient_users) < 1) {
	cli_writeln(get_string('cli_no_users', 'tool_pushcommunications'));
}

foreach($recipient_users as $user) {
	try {
		if ($sender->send_message($user, $data)) {
			cli_writeln(get_string('cli_sent_to_user', 'tool_pushcommunications', $user->username));
		}
		else {
			cli_writeln(get_string('cli_failed_to_send_to_user', 'tool_pushcommunications', $user->username));
		}
	}
	catch (\Exception $e) {
		cli_writeln($user->username . ': ');
		cli_writeln(get_string('cli_exception_sending_to_user', 'tool_pushcommunications', $e->getMessage()) . PHP_EOL);
		continue;
	}
}


