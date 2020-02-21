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
 * Strings in English for this plugin 
 *
 * @package tool_pushcommunications
 * @copyright 2019-2020 Test Valley School {@link https://www.testvalley.hants.sch.uk/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname']                       = 'Push Communications';
$string['pageheader']                       = 'Push Communications';
$string['tool/pushcommunications:send']     = 'Send push communication';
$string['pushcommunications:send']     = 'Send push communication';

$string['communication_content']            = 'Push Communication Content';
$string['communication_content_maxlength']  = 'The push communication content must be 2048 characters or fewer.';
$string['send']                             = 'Send';
$string['user']                             = 'User';
$string['communication_content_required']   = 'The push communication content is required.';
$string['user_required']                    = 'A user to whom the communication should be sent is required.';

$string['or_group']                         = '...or send to all parents associated with pupils in a cohort...';
$string['cohort']                           = 'All parents associated with the given cohort.';
$string['target_heading']                   = 'Recipient(s)';
$string['content_heading']                  = 'Content';
$string['no_cohort']                        = '-- do not send to cohort --';
$string['intent']                           = 'Android only: URL within this Moodle site to navigate to';
$string['intent_internal_url']              = 'The URL to navigate to must be a URL that is on this Moodle site.';

$string['unable_to_find_user']              = 'Unable to find the target user by their email address.';
$string['send_message_failed']              = 'The call to AirNotifier to send the message did not succeed.';
$string['no_capability']                    = 'The web service token provided is not associated with a user who has the appropriate capability.';
$string['sent_pushes']                      = 'Sent push notifications to {$a} users.';
