<?php
/**
 * @author Jean-Lou Dupont
 * @package ImagePageEx
 */
//<source lang=php>
class ImagePageEx extends ImagePage
{
	const thisType = 'other';
	const thisName = 'ImagePageEx';
	
	/**
		Called during the extension initialization phase.
	 */
	public static function setup()
	{
		global $wgHooks;
		$wgHooks['ArticleFromTitle'][] = __CLASS__.'::hArticleFromTitle';		
	}
	
	public function doDelete( $reason )
	{
		wfRunHooks('ImageDoDeleteBegin', array( &$this, &$reason ) );
		
		// there is no return code at the moment
		/*$ret = */ parent::doDelete( $reason );
		
		wfRunHooks('ImageDoDeleteEnd', array( &$this, &$reason ) );
		
		/*return $ret;*/
	}

	/**
		Return an object of the class 'ImagePageEx' if the title's namespace
		falls in the NS_IMAGE namespace.
	 */
	public static function hArticleFromTitle( &$title, &$article )
	{
		// we are only interested in the NS_IMAGE namespace here.
		$ns = $title->getNamespace();
		if ( NS_IMAGE != $ns )
			return true;
		
		$article = new ImagePageEx( $title );
		
		return true;
	}
}
//</source>