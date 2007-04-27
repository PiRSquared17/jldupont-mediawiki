<?php
/*
 * addHTML.php
 * 
 * MediaWiki extension
 * @author: Jean-Lou Dupont (http://www.bluecortex.com)
 *
 * Purpose:  Inserts <html> section(s) in the output page.
 *
 * Features:
 * *********
 * - Security: only page protected on edit with 'sysop' restriction
 *             can use this extension.
 *
 * <addhtml id=xyz /> : meant to be used in conjunction
 *                      with some PHP code using the 'addHtml' method
 *                      of this class. The 'tag' is used to position 
 *                      the HTML code in the page.
 *                      An extension which can execute PHP code, such
 *                      as 'Runphp page' can be used to prepare the
 *                      HTML code and inserts through the 'addHtml'
 *                      method.
 *
 * <addhtml [id=xyz] > html code </addhtml>
 *
 * DEPENDANCY:  ExtensionClass >= v1.1
 * 
 * Tested Compatibility:  MW 1.8.2, 1.9.3
 *
 * History:
 * - v1.0
 * - v1.1 : changed hook method for better parser cache integration.
 */
$wgExtensionCredits['other'][] = array( 
	'name'    => 'addHTML Extension', 
	'version' => '$LastChangedRevision$',
	'author'  => 'Jean-Lou Dupont', 
	'url'     => 'http://www.bluecortex.com',
);

addHTMLclass::singleton();

class addHTMLclass extends ExtensionClass
{
	const tag = 'addhtml';
	
	var $hlist;
	var $hookInPlace;

	public static function &singleton( ) // required by ExtensionClass
	{ return parent::singleton( ); }
	
	function addHTMLclass()
	{
		parent::__construct(); // required by ExtensionClass
		$this->hookInPlace = false;
	}
	public function setup()
	{
		global $wgParser;
		$wgParser->setHook( self::tag, array( $this, 'hAddHtmlTag' ) );
	}
	public function hAddHtmlTag( $input, $argv, &$parser )
	{
		// check page protection status
		if (!$this->checkPageEditRestriction( $parser->mTitle ))
			return "unauthorized usage of <b>addHtml</b> extension.";
		
		$id = 0;
		if ( isset($argv['id']) )		
			$id = $argv['id'];
		
		// just place the hook when we really need it.		
		if (!$this->hookInPlace)
		{
			global $wgHooks;	
			$wgHooks['ParserAfterTidy'][]= array($this, 'feedHtml');
			$this->hookInPlace = true;
		}			

		$input = trim( $input );
		if ( !empty( $input ) )
			$this->hlist[ $id ] = $input; 
		
		// let's put an easy marker that we can 'safely' find once we need to render the HTML
		$marker = "<".self::tag." id={$id} />";
		return $marker;
	}
	public function feedHtml( $parser, &$text )
	{
		// Some substitution to do?
		if (empty($this->hlist)) return;

		foreach($this->hlist as $index => $html)
		{
			$marker = "<".self::tag." id={$index} />";
			$text = str_ireplace($marker, $html, $text);
		}
		return true; // continue hook chain.
	}
	public function addHtml( $id, $html ) {	$this->hlist[ $id ] = $html; }
	
} // END CLASS DEFINITION
?>