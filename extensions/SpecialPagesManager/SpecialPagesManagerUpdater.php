<?php
/*<wikitext>
{| border=1
| <b>File</b> || SpecialPagesManagerUpdater.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
== Code ==
</wikitext>*/

class SpecialPagesManagerUpdater extends SpecialPage
{
	static $instance = null;
	
	// constants
	const actionSubmit  = 'submit';
	const actionSuccess = 'success';
	const actionFailure = 'failure';
	
	static $sourcePath  = '/SpecialPages';
	
	function SpecialPagesManagerUpdater( )
	{
		SpecialPage::SpecialPage("SpecialPagesManagerUpdater", 'siteupdate' );
		self::loadMessages();
		
		if (self::$instance === null)
			self::$instance = $this;
			
		return self::$instance;
	}
	static function singleton() 
	{ 
		if (self::$instance === null)
			self::$instance = new SpecialPagesManagerUpdater();
			
		return self::$instance;
	}
	function loadMessages()
	{
		static $messagesLoaded = false;
		if ( $messagesLoaded ) return;
		$messagesLoaded = true;

		global $wgSpecialPagesManagerUpdaterMessages, $wgMessageCache;

		require( dirname( __FILE__ ) . '/SpecialPagesManagerUpdater.i18n.php' );

		foreach ( $wgSpecialPagesManagerUpdaterMessages as $lang => $langMessages ) 
		        $wgMessageCache->addMessages( $langMessages, $lang );	
	}

	public function execute( $par )
	{
		global $wgRequest, $wgUser, $wgOut;

		if( !$wgUser->isAllowed( 'siteupdate' ) ) 
			return $wgOut->permissionRequired( 'siteupdate' );
	
		$action = $wgRequest->getVal( 'action' );
		
		switch ( $action )
		{
			case self::actionSubmit:
				if ( $wgRequest->wasPosted() &&
					$wgUser->matchEditToken( $wgRequest->getVal( 'wpEditToken' ) ) ) 
					$this->submit();
				else
					$this->show();											
				break;
			case self::actionSuccess:
				$this->show('specialpagesmanagerupdater_success');
				break;
			case self::actionFailure:
				$this->show('specialpagesmanagerupdater_failure');
				break;
			default:
				$this->show();						
				break;
		}
	}

	function show( $err ='' ) 
	{
		global $wgOut, $wgUser;

		$this->showTitle( $wgOut );
		
		if ( !empty($err) )
		{
			$wgOut->setSubtitle( wfMsg( $err ) );
			$wgOut->addWikiText( wfMsg( 'specialpagesmanagerupdater_looklog' ) );		
		}
		
		$wgOut->addWikiText( wfMsg( 'specialpagesmanagerupdater_source', self::getPath() ) );
		$wgOut->addWikiText( wfMsg( 'specialpagesmanagerupdater_confirm' ) );
		
		$titleObj = SpecialPage::getTitleFor( 'specialpagesmanagerupdater' );		
		$action = $titleObj->escapeLocalURL( 'action='.self::actionSubmit );;
		
		$btn = htmlspecialchars( wfMsg( 'specialpagesmanagerupdater_button' ) );
		$token = htmlspecialchars( $wgUser->editToken() );
				
		$wgOut->addHTML( <<<END
<form id="SPupdate" method="post" action="{$action}">
 <input type="submit" name="wpSPupdate" value="{$btn}" />
 <input type="hidden" name="wpEditToken" value="{$token}" />
</form>
END
);
	}
	function showTitle( &$out )
	{	$out->setPageTitle( wfMsg('specialpagesmanagerupdater') );	}
	
	function submit()
	{
		global $wgOut;
		
		$r = $this->doUpdate();

		$titleObj = SpecialPage::getTitleFor( 'specialpagesmanagerupdater' );
		
		if ( $r ) $p = 'action='.self::actionSuccess;
		else	  $p = 'action='.self::actionFailure;		
		
		$wgOut->redirect( $titleObj->getFullURL( $p ) );
	}

// -------------------------
	static function getPath()
	{ global $bwPath; return $bwPath.self::$sourcePath; }
	
	function doUpdate()
	{
		// Read each file from the directory one by one
		// and create/update the corresponding pages in the database
		// The namespace 'NS_BIZZWIKI' is assumed and the base for special pages 
		// is derived from 'SpecialPagesManagerClass::$spPage'
		$fl = $this->getFileList();
		
		$result = true; // assume best case.
		foreach( $fl as $index => $fn )
		{
			$this->getFileData( $fn, $data );
			$title = $this->buildTitle( $fn );
			$r = $this->doUpdatePage( $title, $data );
			if ( $r == false)
				$result = false;
			#$log[] = array( $fn => $r ); // TODO
		}

		return $result;	
	}
	private function doUpdatePage( &$title, &$data )
	{
		// does the page even exist?
		if ( $title->getArticleID() == 0 )
			return $this->createPage( $title, $data );
		else
			return $this->updatePage( $title, $data );
	}
	private function createPage( &$title, &$data )
	{
		$article = new Article( $title );
		return $article->insertNewArticle( $data , '', false, false, false, false);		
	}
	private function updatePage( &$title, &$data )
	{
		$article = new Article( $title );
		return $article->updateArticle( $data, '', false, false, '', null );
	}
	function buildTitle( &$name )
	{
		static $base = null;
		
		// build a page title in the form of
		// $base/$name
		if ($base === null)
			$base = SpecialPagesManagerClass::singleton()->spPage;

		return Title::newFromText( $base.'/'.$name );
	}
	private function getFileData( &$fn, &$data )
	{ $data = @file_get_contents( self::getPath().'/'.$fn ); }
	
	private function getFileList()
	{
		// scan through the directory and fetch all filenames
		$d = @dir( self::getPath() );
		
		// some version of PHP seem to return NULL object
		// if there are no entries
		if (!is_object( $d ))
			return null;
			
		while (false !== ($entry = $d->read() ) )
		{
			if (($entry=='.') || ($entry=='..'))
				continue;

			// eliminate directories
			if (is_dir( self::getPath().'/'.$entry)) continue;

			$fl[] = $entry;
		}
		$d->close();
		
		return $fl;
	}
} // end class declaration
?>