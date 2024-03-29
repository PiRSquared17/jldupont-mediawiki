<?php
/**
 * @author Jean-Lou Dupont
 * @package ParserExt
 * @subpackage ParserTools
 * @version @@package-version@@
 * @Id $Id$
 */
//<source lang=php>*/
class ParserTools
{
	// constants.
	const thisName = 'ParserTools';
	const thisType = 'other';
	  
	function __construct(  ) {	}

	public function tag_noparsercaching( &$text, &$params, &$parser )
	{ $parser->disableCache(); }

	/**
	 * Magic Word #parsercacheexpire used to set the expiration
	 * timeout for the parser cache. Affects only the current page.
	 * 
	 * @param $parser Object
	 * @param $expire integer[optional] Expiration timeout in seconds
	 */
	public function mg_parsercacheexpire( &$parser, $expire = null )
	{
		global $wgParserCacheExpireTime;
		
		// make sure we have a
		if ( !is_int( $expire ) )
			return;
			
		$wgParserCacheExpireTime = $expire;
	}

} // end class
//</source>