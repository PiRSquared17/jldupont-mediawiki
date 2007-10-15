<?php
/*<!--<wikitext>-->
{{Extension
|name        = RegexNamespaceContext
|status      = beta
|type        = hook
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/RegexNamespaceContext/ SVN]
|readme      =
|changelog   =
|description = 
|parameters  =
|rights      =
|example     =
}}
<!--@@
{{#autoredirect: Extension|{{#noext:{{SUBPAGENAME}} }} }}
== File Status ==
This section is only valid when viewing the page in a BizzWiki environment.
<code>(($#extractmtime|@@mtime@@$))  (($#extractfile|@@file@@$))</code>

Status: (($#comparemtime|<b>File system copy is newer - [{{fullurl:{{NAMESPACE}}:{{PAGENAME}}|action=reload}} Reload] </b>|Up to date$))
@@-->

== Purpose==
Supports regex based 'edit form' text preloading and 'header'/'footer' wikitext pages insertion.

== Features ==
* Can load 'preload edit form' text based on a per-namespace regex 'context'
* Can add 'headers' and 'footers' text pages based on a per-namespace regex 'context'
* Creates a hook 'ContextPageParsingComplete'
* Special Word '__DISABLECONTEXT__' 

== Usage ==
On a per-namespace basis (only the ones required), edit the page 'Context' and place+customize the following:
<pre>
== Preload Patterns ==
* Preloads '{{NAMESPACE}}:preloadPage' page upon '*.log' page name
* add other patterns below
{{#varaset:preloadPattern|{{NAMESPACE}}:preloadPage|(.*)\.log}}
...more patterns here...

=== Preload Generation ===
Do not modify the following:
{{#varaset:ContextVars|preloadPageName|
 {{#regx_vars:preloadPattern|{{#varaget:ContextVars|PageName}} }}
}}

== Header Patterns ==
* Header page for all pages in namespace following the '*.log' pattern
* add other patterns below
{{#varaset:headerPattern|{{NAMESPACE}}:LogHeaderPage|(.*)\.log}}
...more patterns here...

=== Header Generation ===
Do not modify the following:
{{#varaset:ContextVars|headerPageName|
 {{#regx_vars:headerPattern|{{#varaget:ContextVars|PageName}} }}
}}

== Footer Patterns ==
* Footer page for all pages in namespace following the '*.log' pattern
* add other patterns below
{{#varaset:footerPattern|{{NAMESPACE}}:LogFooterPage|(.*)\.log}}
...more patterns here...

=== Footer Generation ===
Do not modify the following:
{{#varaset:ContextVars|footerPageName|
 {{#regx_vars:footerPattern|{{#varaget:ContextVars|PageName}} }}
}}
</pre>
== Dependancies ==
* [[Extension:StubManager|StubManager extension]]
* [[Extension:RegexTools|RegexTools extension]]
* [[Extension:PageFunctions|PageFunctions extension]]
* [[Extension:ParserCacheControl|ParserCacheControl extension]]

== Installation ==
To install independantly from BizzWiki:
* Download [[Extension:StubManager]] extension & put in 'extensions' directory
* Download [[Extension:ParserCacheControl]] extension & put in 'extensions' directory
* Download [[Extension:PageFunctions]] extension & put in 'extensions' directory
* Download [[Extension:RegexTools]] extension & put in 'extensions' directory
* Download [[Extension:RegexNamespaceContext]] extension & put in 'extensions' directory
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/StubManager.php');
require('extensions/PageFunctions.php');
require('extensions/ParserCacheControl.php');
require('extensions/RegexTools.php');
require('extensions/RegexNamespaceContext_stub.php');
</source>

== History ==
* Used another parser instance instead of the global wgParser one: better integration with other extensions
* Fixed major bug: needed to 'clone' the wgParser in order to keep all the hooks/parser functions etc.
* Fixed 'skin' related bug: 'ParserAfterTidy' gets called during MediaWiki skin's string processing
* Added special word '__DISABLECONTEXT__' 

== See Also ==
This extension is part of the [[Extension:BizzWiki|BizzWiki Platform]].

== Code ==
<!--</wikitext>--><source lang=php>*/
$wgExtensionCredits[RegexNamespaceContext::thisType][] = array( 
	'name'    => RegexNamespaceContext::thisName,
	'version' => StubManager::getRevisionId('$Id$'),
	'author'  => 'Jean-Lou Dupont',
	'description' => "Supports regex based 'edit form' text preloading and 'header'/'footer' wikitext pages insertion.", 
	'url'		=> 'http://mediawiki.org/wiki/Extension:RegexNamespaceContext',
);

class RegexNamespaceContext
{
	const thisType = 'other';
	const thisName = 'RegexNamespaceContext';

	const headerNone	= '<!--header_none-->';
	const headerOpen	= '<!--header_open-->';
	const headerClose	= '<!--header_close-->';	
	const footerNone	= '<!--footer_none-->';	
	const footerOpen	= '<!--footer_open-->';
	const footerClose	= '<!--footer_close-->';	

	static $cpBaseName = 'Context';
			
	var $cpNsId;
	var $thisPageName;
	var $cpTitle;
	var $cpArticle;
	var $disable;
	
	//
	var $headerPageName;
	var $footerPageName;
	var $preloadPageName;
	
	public function __construct() 
	{ 
		$this->disable = false;
	}
	/**
		Page preloading happens here.
	 */
	public function hEditFormPreloadText( &$textbox, &$title )
	{
		// stay away from NS_FILESYSTEM namespace!
		$ns = $title->getNamespace();
		if ($ns==NS_FILESYSTEM) return true;		

		// add the preload text to the one already presented in textbox variable
		// just in case other extensions are playing in too e.g. FileManager.
		$textbox .= $this->getPreloadText( $title );	
		return true;
	}
	/**
		Capture of the event to stop header/footer processing.
	 */
	public function hBeforePageDisplay( &$op )
	{
		// if we are about to display the page, 
		// it is the signal to stop processing 'after tidy';
		// MediaWiki uses the parser to handle some text of the
		// skin and we do not want to be adding header/footer there.
		$this->disable = true;	
	}
	/**
		This hook makes sure that the parsing phase is completed for the page being
		_viewed__ *before* adding the 'header' and 'footer' pages.
		IMPORTANT: parser cache saving must be *disabled* upon editing/updating.
		Use [[Extension:ParserCacheControl]] if not operating under the BizzWiki platform.
	 */
	public function hParserAfterTidy( &$parser, &$text ) 
	{
		if ($this->disable)
			return true;
		
		// are we asked to disable?
		$text = str_replace('__DISABLECONTEXT__', '', $text, $count);
		if ( $count>0 )
			return true;
			
		// just do the page asked for in the transaction.
		// This hook also gets called when MediaWiki parses other strings
		static $isDone = false;
		if ($isDone == true )
			return true;
		$isDone= true;
		
		global $action;
		if ($action != 'view') return true;

		// stay away from NS_FILESYSTEM namespace!
		$ns = $parser->mTitle->getNamespace();
		if (NS_FILESYSTEM == $ns) return true;		

		$title = $parser->mTitle;
	
		$this->getHeaderFooterText( $title, $header, $footer );
		$headerText = ( $header === null ) ? (self::headerNone) : (self::headerOpen.$header.self::headerClose);
		$footerText = ( $footer === null ) ? (self::footerNone) : (self::footerOpen.$footer.self::footerClose);		
		$text = $headerText.$text.$footerText;

		/* DEBUG STUFF
		$text .= '<!-- headerpagename: '.$this->headerPageName.' footerpagename:'.$this->footerPageName.' preloadpagename: '.$this->preloadPageName.' -->';
		$text .= '<!-- $oParams: '.bwVarDump($this->oParams).' -->';
		$text .= '<!-- $cp: '.$this->cp.' -->';
		$text .= '<!-- thisPageName:'.$this->thisPageName.'-->';
		*/
				
		return true;
	}
	/**
	 */
	private function removeHeaderFooter( &$c )
	{
		$ph = '/'.self::headerOpen.'(.*)'.self::headerClose.'/';		
		$pf = '/'.self::footerOpen.'(.*)'.self::footerClose.'/';
		
		$c = preg_replace( $ph, '', $c );
		$c = preg_replace( $pf, '', $c );		
	}
	protected function getPreloadText( &$title )
	{
		$this->processContext( $title );

		return $this->getPageContent( $this->preloadPageName );
	}
	protected function getHeaderFooterText( &$a, &$h, &$f )
	{
		$this->processContext( $a );
		
		$hText = $this->getPageContent( $this->headerPageName );
		$fText = $this->getPageContent( $this->footerPageName );
		
		// we need to parse the header and footer pages
		// in the context of the current page.
		// We need to do this with THE fully configured parser.
		global $wgParser;
		$parser = clone $wgParser;

		$h = null;		
		$f = null;		
		if (!empty( $hText ))
		{
			$po = $parser->parse( $hText, $a /* title object */, new ParserOptions() );
			$h = $po->getText();
		}
		if (!empty( $fText ))
		{
			$po = $parser->parse( $fText, $a /* title object */, new ParserOptions() );
			$f = $po->getText();
		}
	}
	private function processContext( &$obj )
	{
		$this->getPageParams( $obj, $ns, $pn );
		
		$this->cpNsId	= 		$ns; 
		$this->thisPageName =	$pn;

		$this->loadContextPage();
		$this->parseContextPage();
	}
	
	/**
		Load the {{NAMESPACE}}:Context  page
	 */
	private function loadContextPage()
	{
		// context page is located in the same namespace
		// under the defined base name
		$this->cp = $this->getPageContent( $this->cpNsId, self::$cpBaseName, $article, $title );
		
		$this->cpTitle = $title;
		$this->cpArticle = $article;
		
	}
	/**
			Just returns the page content.
			Accepts either:
			- a namespace id in $ns parameter OR
			- a string corresponding to a 'prefixed DBkey' serving as page title name.
	 */
	public function getPageContent( &$ns, &$pagename=null, &$article=null, &$title=null )
	{
		if ( $ns === null )
			return null;
			
		if (is_string( $ns ))
			$title = Title::newFromText( $ns );
		else
			$title = Title::makeTitle( $ns, $pagename );
		
		// paranoia.
		if (!is_object( $title ))
			return null;
		
		$article = new Article( $title );

		if ($article->getID() == 0)
			return null;

		return $article->getContent();
	}
	/**
		Parses the loaded context page.
		Magic Words registered with the MW Parser will do all the job;
		this is where the companion extension 'RegexTools' comes in handy.
	 */
	private function parseContextPage()
	{
		if (empty( $this->cp ))
			return;

		$params = array(
							'NamespaceId'	=> $this->cpNsId,
							'PageName'		=> $this->thisPageName,
						);

		// Pass some parameters in the 'Page' variables
		// Requires the 'PageFunctions' extension
		//
		// The parameter 'PageName' is especially important.
		
		wfRunHooks('PageVarSet', array( 'ContextVars', &$params) );

		// grab a new parser in order not to disrupt the current transaction.
		// We need to keep all the hooks/parser functions though; 
		// so clone the global one.
		global $wgParser;
		$parser = clone $wgParser;
		$parser->parse( $this->cp, $this->cpTitle, new ParserOptions );
		
		//This does not work.
		#$parser->preprocess( $this->cp, $this->cpTitle, new ParserOptions );		
		
		// Grab the result from the 'Page' variables
		wfRunHooks('PageVarGet', array( 'ContextVars', &$oParams) );

		// if we are asked by another extension to disable
		// context processing, then obey (!)
		if (!isset($oParams['disable']))
		{
			$this->headerPageName	= null;		
			$this->footerPageName	= null;			
			$this->preloadPageName	= null;
						
			if (isset($oParams['headerPageName']))
				$this->headerPageName	= $oParams['headerPageName'];
	
			if (isset($oParams['footerPageName']))
				$this->footerPageName	= $oParams['footerPageName'];
	
			if (isset($oParams['preloadPageName']))
				$this->preloadPageName	= $oParams['preloadPageName'];
		}
		
		wfRunHooks('ContextPageParsingComplete', array( &$this, 'ContextVars' ) );		
	}	 
	/**
		Versatile function for getting information about a page from either:
		- a Title class object
		- an Article class object
		- or just a prefixed DBkey text string as page title name
	 */
	protected function getPageParams( &$obj, &$ns, &$pn )
	{
		if (!is_object( $obj ))
			return null;

		if (is_a( $obj, 'Article' ))
			$title = $obj->mTitle;
		
		if (is_a( $obj, 'Title' ))
			$title = $obj;

		if (!is_a( $title, 'Title'))
			return false;

		$ns = $title->getNamespace();
		$pn = $title->getDBkey();

		return true;			
	} 	
} // end declaration.
//</source>