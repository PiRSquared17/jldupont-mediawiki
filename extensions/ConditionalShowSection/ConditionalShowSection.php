<?php
/*
 * ConditionalShowSection WikiMedia extension
 *
 * @author Jean-Lou Dupont
 * @package MediaWiki
 * @subpackage Extensions
 * @licence GNU General Public Licence 2.0
 * This extension enables to conditionally show/hide a section
 * of wikitext that appears between the <cshow> </cshow> tags.
 *
 * Add to LocalSettings.php
 * with: include("extensions/ConditionalShowSection.php");
 *
 * HISTORY:
 * 1.1: corrected bug when "ingroup" option not present, wrong results.
 * 1.2: used "recursiveTagParse" to get cleaner parsing.
 * 1.3: corrected error with default initialisation of userReqLogged
 * 1.4: changed to using 'getEffectiveGroups' in order to have also access
 *      to the implicit '*' group and the default group for logged users 'user'.
 */
$wgExtensionCredits['other'][] = array(
    'name'   => "ConditionalShowSection [http://www.bluecortex.com]",
	'version'=> 'v1.4 $LastChangedRevision$',
	'author' => 'Jean-Lou Dupont [http://www.bluecortex.com]' 
);

$wgExtensionFunctions[] = "wfConditionalShowSection";

function wfConditionalShowSection() {
    global $wgParser;
    $wgParser->setHook( "cshow", "ConditionalShowSection" );
}

function ConditionalShowSection( $input, $argv, &$parser ) {
    #
    # By default, the section is HIDDEN unless the following conditions are met:
    # Argument: Logged = '1' or '0'  
    # Argument: InGroup = 'group XYZ'
    #
    # If no arguments are provided for:
    # - Logged   --> assume 'don't care' 
    # - InGroup  --> assume ''           (no group)
	#
	# In other words, if no 'ingroup' parameter is given,
	# then the condition 'ingroup' is never met.
    #
	# If no "logged" parameter is given, then this condition is always met.
    # 
    # Examples: <cshow Logged="1" InGroup="sysop"> text to show upon conditions met </cshow>
    # if the user viewing the page is LOGGED and part of the SYSOP group then show the section.
	#
	# <cshow ingroup='user'> Test </cshow>
	# shows 'Test' if the user viewing the page is logged and by default part of the 'user' group. 
    #
	global $wgUser;
	
	$userReqLogged = null;	 # default is "don't care"
	$userReqGroup  = "" ;    # assuming no group membership required
	$output = ""; 	         # assuming the section is hidden by default.

	$cond1 = false;			 
	$cond2 = false;			 # by default, don't show the section to just anybody
     
	# Extract the parameters passed
	# the parser lowers the case of all the parameters passed...
	if (isset($argv["logged"]))
	{
		$userReqLogged = $argv["logged"];
		
		if ($userReqLogged==="1" && ($wgUser->isLoggedIn()===true)) 
			$cond1=true;
		
		if ($userReqLogged==="0" && ($wgUser->isLoggedIn()===false))
			$cond1=true;
	}
	if (isset($argv["ingroup"]))
	{
		$userReqGroup  = $argv["ingroup"];
		# which groups is the user part of?
		$ugroups = $wgUser->getEffectiveGroups();  // changed in v1.4
		$result  = array_search($userReqGroup,$ugroups);
	
		if ($result!==false)
			$cond2=true;
	}
	# if both conditions are met, then SHOW else HIDE
	if (($cond1===true) and ($cond2===true))
	 $output=$parser->recursiveTagParse($input);
   
	return $output;
}
?>