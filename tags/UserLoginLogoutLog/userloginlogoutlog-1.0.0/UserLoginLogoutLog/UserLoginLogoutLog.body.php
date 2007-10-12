<?php
/**
 * @author Jean-Lou Dupont
 * @package UserLoginLogoutLog
 * @version $Id$
 */
//<source lang=php>*/
require_once('UserLoginLogoutLog.i18n.php');

class UserLoginLogoutLog
{
	const thisType = 'other';
	const thisName = 'UserLoginLogoutLog';
	
	const probLog = 0.25;
	
	var $IdUserAboutToLogOut;	
	var $NameUserAboutToLogOut;

	public function __construct()
	{
		# Add a new log type
		global $wgLogTypes, $wgLogNames, $wgLogHeaders, $wgLogActions;
		$wgLogTypes[]							= 'usrloglog';
		$wgLogNames  ['usrloglog']				= 'usrloglog'.'logpage';
		$wgLogHeaders['usrloglog']				= 'usrloglog'.'logpagetext';
		$wgLogActions['usrloglog/usrloglog']	= 'usrloglog'.'logentry';
		$wgLogActions['usrloglog/loginok']     	= 'usrloglog'.'-loginok-entry';
		$wgLogActions['usrloglog/logoutok']     = 'usrloglog'.'-logoutok-entry';				
		$wgLogActions['usrloglog/loginerr']     = 'usrloglog'.'-loginerr-entry';		
		
		global $wgMessageCache;

		$msg = $GLOBALS[ 'msg'.__CLASS__ ];
		
		foreach( $msg as $key => $value )
			$wgMessageCache->addMessages( $msg[$key], $key );	
		
		// some initialisation
		$this->IdUserAboutToLogOut = null;	
		$this->NameUserAboutToLogOut = null;		
	}
	/**
		Used to output debug messages e.g.
		if the user 'WikiAgent' is not defined, the 'login error'
		process will not work correctly.
	 */
	public function hSpecialVersionExtensionTypes( &$sp, &$extensionTypes )
	// setup of this hook occurs in 'ExtensionClass' base class.
	{
		global $wgExtensionCredits;

		// make sure WikiAgent user exists
		$waUser = User::newFromName('WikiAgent');
		if ($waUser->getId() != 0)
			return true;

		$message = ' User [[User:WikiAgent]] does not exist.';
		
		foreach ( $wgExtensionCredits[self::thisType] as $index => &$el )
			if (isset($el['name']))		
				if ($el['name']==self::thisName)
					$el['description'] .= $message;
				
		return true; // continue hook-chain.
	}

	/**
		Just make sure we are faced with a 'login error' event here.
	 */
	public function hUserLoginForm( &$tmpl )
	{
		if ( !empty($tmpl->data['message']) && $tmpl->data['messagetype'] == 'error') 
			$this->handleLoginError( $tmpl );
		
		return true;
	}
	/**
		Handle 'login error' events here.
	 */
	private function handleLoginError( &$tmpl )
	{
		// Probabilistic logging.
		if (!$this->shouldLog())
			return true;
			
		// If WikiAgent user doesn't exist, just bail out.
		// We won't be able to add a coherent log message anyway.
		$waUser = User::newFromName('WikiAgent');
		if ($waUser->getId() == 0)
			return true;
		
		global $wgUser;
		$cId = $wgUser->getId();
		
		if ($cId == 0)
			$name = 'IP: '.wfGetIP();
		else
			$name = '[[User:'.$wgUser->getName().']]';
			
		$message = wfMsgForContent( 'usrloglog-loginerr-text', $name, $tmpl->data['message'] );

		// File under a title that can be easily policed.
		$title = Title::makeTitle( NS_SPECIAL, 'log/usrloglog' );
		
		// Hack to create a meaningful log entry.
		$wgUser->mId = $waUser->getId();
					
		$log = new LogPage( 'usrloglog', false /* no rc entry */);
		$log->addEntry( 'loginerr', $title, $message );
		
		// 'undo' hack.
		$wgUser->mId = $cId;
	}
	/**
		Makes a decision as to if the event should be logged or not.
	 */
	private function shouldLog()
	{
		$rnd = wfRandom();
		
		return ($rnd<self::probLog) ? true:false;
	}
	/**
		Successful log-in event.
	 */
	public function hUserLoginComplete( &$user )
	{
		$message = wfMsgForContent( 'usrloglog-loginok-text', $user->getName() );
	
		// File under a title that can be easily policed.
		$title = Title::makeTitle( NS_SPECIAL, 'log/usrloglog' );
		
		$log = new LogPage( 'usrloglog', false /* no rc entry */);
		$log->addEntry( 'loginok', $title , $message );
		
		// be nice.
		return true;		
	}
	/**
		Keep a copy of the user's id & name about to logout.
		We need this to create a meaningful log entry.
	 */
	public function hUserLogout( &$user )
	{
		$this->IdUserAboutToLogOut   = $user->getId();
		$this->NameUserAboutToLogOut = $user->getName();		
		return true;
	}

	public function hUserLogoutComplete( &$user )
	{
		// The log-out process causes this method
		// to be called twice in the 'same' transaction:
		// First time with the correct 'user'
		// Second time with an 'anonymous user'
		if ($this->IdUserAboutToLogOut == 0)
			return true;
			
		// for the log entry to be filled correctly,
		// we must hack a bit...
		global $wgUser;
		$cId = $wgUser->getId();
		
		// LogPage class regretfully retrieves
		// information about the user from the global $wgUser.
		// We need to hack here in order to log a meaningful entry.
		$wgUser->mId = $this->IdUserAboutToLogOut;

		$message = wfMsgForContent( 'usrloglog-logoutok-text', $this->NameUserAboutToLogOut );

		$log = new LogPage( 'usrloglog', false /* no rc entry */);
		
		// File under a title that can be easily policed.
		$title = Title::makeTitle( NS_SPECIAL, 'log/usrloglog' );
				
		$log->addEntry( 'logoutok', 
						$title, 
						$message );
		
		// restore.
		$wgUser->mId = $cId;
		
		// be nice.
		return true;
	}

} // end class declaration
//</source>