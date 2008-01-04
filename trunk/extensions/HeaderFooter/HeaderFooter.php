<?php
/**
 * @author Jean-Lou Dupont
 * @package HeaderFooter
 * @version @@package-version@@
 * @Id $Id$
 */
//<source lang=php>
if (class_exists( 'StubManager' ))
{
	$wgExtensionCredits['other'][] = array( 
		'name'        => 'HeaderFooter', 
		'version'     => '@@package-version@@',
		'author'      => 'Jean-Lou Dupont', 
		'description' => 'Enables per-page/per-namespace headers and footers',
		'url' 		=> 'http://mediawiki.org/wiki/Extension:HeaderFooter',			
	);

	StubManager::createStub2(	array(	'class' 		=> 'HeaderFooter', 
										'classfilename'	=> dirname(__FILE__).'/HeaderFooter.body.php',
										'hooks'			=> array( 'OutputPageParserOutput' )
									)
							);
}
else
	echo '[[Extension:HeaderFooter]] requires [[Extension:StubManager]].';
//</source>