<?php

/**
 * Term object test suite.
 *
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		25/03/2016
 */

//
// Include local definitions.
//
require_once(dirname(__DIR__) . "/includes.local.php");
require_once(dirname(__DIR__) . "/arango.local.php");

//
// Include utility functions.
//
require_once( "functions.php" );

//
// Reference class.
//
use Milko\PHPLib\Term;
use Milko\PHPLib\Collection;

//
// Instantiate object.
//
echo( '$url = "tcp://localhost:8529/test_milkolib/test_collection";' . "\n" );
$url = "tcp://localhost:8529/test_milkolib/test_collection";
echo( '$server = new \Milko\PHPLib\ArangoDB\DataServer( $url' . " );\n" );
$server = new \Milko\PHPLib\ArangoDB\DataServer( $url );
echo( '$database = $server->RetrieveDatabase( "test_milkolib" );' . "\n" );
$database = $server->RetrieveDatabase( "test_milkolib" );
echo( '$collection = $database->RetrieveCollection( "test_collection" );' . "\n" );
$collection = $database->RetrieveCollection( "test_collection" );
echo( '$collection->Truncate();' . "\n" );
$collection->Truncate();

echo( "\n====================================================================================\n\n" );

//
// Instantiate namespace.
//
echo( "Instantiate namespace:\n" );
echo( '$namespace = new Milko\PHPLib\Term( $collection, [kTAG_LID => "namespace", kTAG_NAME => "Namespace"] );' . "\n" );
$namespace = new Milko\PHPLib\Term( $collection, [kTAG_LID => "namespace", kTAG_NAME => "Namespace"] );
echo( "Class: " . get_class( $namespace ) . "\n" );
echo( "Modified:   " . (( $namespace->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $namespace->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $namespace->getArrayCopy() );

echo( "\n" );

//
// Insert namespace.
//
echo( "Insert namespace:\n" );
echo( '$key = $namespace->Store();' . "\n" );
$key = $namespace->Store();
echo( "Class: " . get_class( $namespace ) . "\n" );
echo( "Modified:   " . (( $namespace->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $namespace->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $namespace->getArrayCopy() );

echo( "\n====================================================================================\n\n" );

//
// Instantiate term.
//
echo( "Instantiate term:\n" );
echo( '$document = new Milko\PHPLib\Term( $collection );' . "\n" );
$document = new Milko\PHPLib\Term( $collection );
echo( '$document->SetNamespaceByGID( "namespace" );' . "\n" );
$document->SetNamespaceByGID( "namespace" );
print_r( $document->getArrayCopy() );
echo( '$document[ kTAG_LID ] = "code";' . "\n" );
$document[ kTAG_LID ] = "code";
print_r( $document->getArrayCopy() );

echo( "\n" );

//
// Insert term.
//
echo( "Insert term:\n" );
try
{
	echo( '$key = $document->Store();' . "\n" );
	$key = $document->Store();
	echo( "FALIED! - Should have raised an exception.\n" );
}
catch( RuntimeException $error )
{
	echo( "SUCCEEDED! - Has raised an exception.\n" );
	echo( $error->getMessage() . "\n" );
}

echo( "\n" );

//
// Insert term.
//
echo( "Insert term:\n" );
echo( '$document[ kTAG_NAME ] = "A term";' . "\n" );
$document[ kTAG_NAME ] = "A term";
echo( '$key = $document->Store();' . "\n" );
$key = $document->Store();
echo( "Class: " . get_class( $document ) . "\n" );
echo( "Modified:   " . (( $document->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $document->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $document->getArrayCopy() );

echo( "\n====================================================================================\n\n" );

//
// Delete namespace.
//
echo( "Delete namespace:\n" );
try
{
	echo( '$key = $namespace->Delete();' . "\n" );
	$key = $namespace->Delete();
	echo( "FALIED! - Should have raised an exception.\n" );
}
catch( RuntimeException $error )
{
	echo( "SUCCEEDED! - Has raised an exception.\n" );
	echo( $error->getMessage() . "\n" );
}

echo( "\n" );

//
// Delete term.
//
echo( "Delete term:\n" );
echo( '$document->Delete();' . "\n" );
$document->Delete();
echo( "Class: " . get_class( $document ) . "\n" );
echo( "Modified:   " . (( $document->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $document->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $document->getArrayCopy() );

echo( "\n" );

//
// Delete namespace.
//
echo( "Delete namespace:\n" );
try
{
	echo( '$key = $namespace->Delete();' . "\n" );
	$key = $namespace->Delete();
	echo( "Class: " . get_class( $namespace ) . "\n" );
	echo( "Modified:   " . (( $namespace->IsModified() ) ? "Yes\n" : "No\n") );
	echo( "Persistent: " . (( $namespace->IsPersistent() ) ? "Yes\n" : "No\n") );
	echo( "Data: " );
	print_r( $namespace->getArrayCopy() );
}
catch( RuntimeException $error )
{
	echo( "FAILED! - Has raised an exception.\n" );
	echo( $error->getMessage() . "\n" );
}


?>
