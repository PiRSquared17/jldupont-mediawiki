<?php
/**
 * @author Jean-Lou Dupont
 * @package HeaderFooter
 * @version @@package-version@@
 * @Id $Id$
 */
//<source lang='php'>
class HeaderFooter
{
	var $done = false;
	
	public function hParserBeforeStrip( &$parser, &$text, &$mStripState )
	{
		global $wgTitle, $action;
		
		// nothing to do if not on page view
		if ( $action != 'view' )
			return true;
		
		$thisTitle     = $parser->getTitle();
		$thisTitleNs   = null;
		$thisTitleName = null;

		if (is_object( $thisTitle ) && ( $thisTitle instanceof Title) )
		{
			$thisTitleNs   = $thisTitle->getNsText();
			$thisTitleName = $thisTitle->getPrefixedDBKey();
		}
		$ns = $wgTitle->getNsText();
		$name = $wgTitle->getPrefixedDBKey();
		$protect = $wgTitle->isProtected( 'edit' );
	
		// make sure we are only including the headers/footers to the main article!
		if ($thisTitleName !== $name )
			return true;
	
		// set trap in order to make sure
		// we only parse the main page text.
		if ($this->done)
			return true;
		$this->done = true;
		
		$nsheader = $this->getMsg( "hf-nsheader-$ns" );
		$nsfooter = $this->getMsg( "hf-nsfooter-$ns" );		

		$header = $this->getMsg( "hf-header-$name" );
		$footer = $this->getMsg( "hf-footer-$name" );		

		$text = '<div class="hf-header">'.$this->conditionalInclude( '__NOHEADER__', $header, $protect ).'</div>'.$text;
		$text = '<div class="hf-nsheader">'.$this->conditionalInclude( '__NONSHEADER__', $nsheader, $protect ).'</div>'.$text;

		$text .= '<div class="hf-footer">'.$this->conditionalInclude( '__NOFOOTER__', $footer, $protect ).'</div>';
		$text .= '<div class="hf-nsfooter">'.$this->conditionalInclude( '__NONSFOOTER__', $nsfooter, $protect ).'</div>';
				
		return true;
	}
	/**
	 *
	 */
	public function hOutputPageParserOutput( &$op, &$parserOutput )
	{
		global $wgTitle;
		
		$ns = $wgTitle->getNsText();
		$name = $wgTitle->getPrefixedDBKey();
		$protect = $wgTitle->isProtected( 'edit' );
		
		$text = $parserOutput->getText();
		
		$nsheader = $this->getMsg( "hf-nsheader-$ns" );
		$nsfooter = $this->getMsg( "hf-nsfooter-$ns" );		

		$header = $this->getMsg( "hf-header-$name" );
		$footer = $this->getMsg( "hf-footer-$name" );		

		$text = '<div class="hf-header">'.$this->conditionalInclude( '__NOHEADER__', $header, $protect ).'</div>'.$text;
		$text = '<div class="hf-nsheader">'.$this->conditionalInclude( '__NONSHEADER__', $nsheader, $protect ).'</div>'.$text;

		$text .= '<div class="hf-footer">'.$this->conditionalInclude( '__NOFOOTER__', $footer, $protect ).'</div>';
		$text .= '<div class="hf-nsfooter">'.$this->conditionalInclude( '__NONSFOOTER__', $nsfooter, $protect ).'</div>';
		
		$parserOutput->setText( $text );
		
		return true;
	}	 
	/**
	 * Gets a message from the NS_MEDIAWIKI namespace
	 */
	protected function getMsg( $msgId )
	{
		$msgText = wfMsg( $msgId );
		
		if ( wfEmptyMsg( $msgId, $msgText ))
			return null;
			
		return $msgText;			
	}	 
	protected function conditionalInclude( $disableWord, &$content, $protect )
	{
		// don't need to bother if there is no content.
		if (empty( $content ))
			return null;
		
		// is there a disable command lurking around?
		$disable = strpos( $content, $disableWord ) !== false ;
		
		// if there is, get rid of it
		// make sure that the disableWord does not break the REGEX below!
		$content = preg_replace('/'.$disableWord.'/si', '', $content );

		// if there is a disable command, then obey IFF the page is protected on 'edit'
		if ($disable && $protect)
			return null;
		
		// remove the 'noinclude' sections
		$content = preg_replace( '/<noinclude>.*<\/noinclude>/si', '', $content );
		
		return $content;
	}
		
} // END CLASS DEFINITION
//</source>