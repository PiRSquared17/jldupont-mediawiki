<?php
/**
 * @author Jean-Lou Dupont
 * @package FileManager
 * @version $Id$
 */
//<source lang=php>
// Format for global variables is:
// 'var-type'.$classname
//  where 'var-type' supported are:
//   'log', 'msg'
//
// Everything is anchored on the classname.

global $msgFileManager;		// required for StubManager
global $logFileManager;		// required for StubManager

// required for StubManager. The format is important:  'log'.$classname
$logFileManager = 'commitfil';	

// the format is important here too: 'msg'.$classname
$msgFileManager['en'] = array(
	'commitfil'							=> 'Commit File log',
	'commitfil'.'logpage'               => 'Commit File log',
	'commitfil'.'logpagetext'           => 'This is a log of <i>file commit</i> actions',
	'commitfil'.'logentry'              => '',
	'commitfil'.'-commitok-entry'    	=> 'File successfully committed',
	'commitfil'.'-commitfail-entry'  	=> 'File commit failed',
	'commitfil'.'-commitfail2-entry' 	=> 'File commit failed - invalid filename',	
	'commitfil'.'-commit-text'       	=> "[[$1:$2|$3]]",
	'filemanager'.'-script-exists'       => 'File <b>$1</b> exists',
	'filemanager'.'-script-notexists'    => 'File <b>$1</b> does not exist',	
	#'' => '',
);
//</source>