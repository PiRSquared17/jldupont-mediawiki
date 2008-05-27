<?php
/**
 * @author Jean-Lou Dupont
 * @package HeaderFooter
 * @version 2.0.1
 * @Id $Id: HeaderFooter.php 821 2008-01-04 02:36:30Z jeanlou.dupont $
 */
//<source lang=php>
if (class_exists( 'StubManager' ))
{
	$wgExtensionCredits['other'][] = array( 
		'name'        => 'HeaderFooter', 
		'version'     => '2.0.1',
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