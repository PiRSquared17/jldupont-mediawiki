<?php
/**
 * Internationalisation file for NewUserEmailNotification extension.
 *
 * $Id$
 * 
*/
// Format for global variables is:
// 'var-type'.$classname
//  where 'var-type' supported are:
//   'log', 'msg'
//
// Everything is anchored on the classname.

global $msgNewUserEmailNotification;		// required for StubManager

// the format is important here too: 'msg'.$classname
$msgNewUserEmailNotification['en'] = array(
'newuseremailnotification-subject'	=> '$1 site: New User Account Creation Notification',
'newuseremailnotification-body'		=> "Site Name: $1\nUser name: $2\nReal user name: $3\n",
#'' => '',
);

?>