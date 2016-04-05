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
// Global definitions.
//
define( 'kENGINE', "ARANGO" );

//
// Include local definitions.
//
require_once(dirname(__DIR__) . "/includes.local.php");
if( kENGINE == "MONGO" )
	require_once(dirname(__DIR__) . "/mongo.local.php");
elseif( kENGINE == "ARANGO" )
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
if( kENGINE == "MONGO" )
{
	echo( '$url = "mongodb://localhost:27017/test_milkolib/test_collection";' . "\n" );
	$url = "mongodb://localhost:27017/test_milkolib/test_collection";
	echo( '$server = new \Milko\PHPLib\MongoDB\DataServer( $url' . " );\n" );
	$server = new \Milko\PHPLib\MongoDB\DataServer( $url );
}
elseif( kENGINE == "ARANGO" )
{
	echo('$url = "tcp://localhost:8529/test_milkolib/test_collection";' . "\n");
	$url = "tcp://localhost:8529/test_milkolib/test_collection";
	echo( '$server = new \Milko\PHPLib\ArangoDB\DataServer( $url' . " );\n" );
	$server = new \Milko\PHPLib\ArangoDB\DataServer( $url );
}
echo( '$database = $server->RetrieveDatabase( "test_milkolib" );' . "\n" );
$database = $server->RetrieveDatabase( "test_milkolib" );
echo( '$collection = $database->RetrieveTerms( \Milko\PHPLib\Server::kFLAG_CREATE );' . "\n" );
$collection = $database->RetrieveTerms( \Milko\PHPLib\Server::kFLAG_CREATE );
echo( '(string)$collection' . "\n" );
var_dump( (string)$collection );
echo( '$collection->Truncate();' . "\n" );
$collection->Truncate();

echo( "\n====================================================================================\n\n" );

//
// Instantiate namespace.
//
echo( "Instantiate namespace:\n" );
echo( '$namespace = new Milko\PHPLib\Term( $collection, [kTAG_LID => "namespace", kTAG_NAME => ["en" => "Namespace"] );' . "\n" );
$namespace = new Milko\PHPLib\Term( $collection, [kTAG_LID => "namespace", kTAG_NAME => ["en" => "Namespace"]] );
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
echo( '$handle = $namespace->Store();' . "\n" );
$handle = $namespace->Store();
var_dump( $handle );
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
print_r( $document->getArrayCopy() );
echo( '$document[ kTAG_LID ] = "code";' . "\n" );
$document[ kTAG_LID ] = "code";
print_r( $document->getArrayCopy() );

echo( "\n" );

//
// Set namespace by term.
//
echo( "Set namespace by term:\n" );
echo( '$result = $document->SetNamespaceByTerm( $namespace );' . "\n" );
$result = $document->SetNamespaceByTerm( $namespace );
var_dump( $result );
print_r( $document->getArrayCopy() );

echo( "\n" );

//
// Set namespace by GID.
//
echo( "Set namespace by GID:\n" );
echo( '$result = $document->SetNamespaceByGID( $namespace[ kTAG_GID ] );' . "\n" );
$result = $document->SetNamespaceByGID( $namespace[ kTAG_GID ] );
var_dump( $result );
print_r( $document->getArrayCopy() );

echo( "\n" );

//
// Set namespace by term.
//
echo( "Set namespace by term:\n" );
echo( '$result = $document->offsetSet( kTAG_NS, $namespace );' . "\n" );
$result = $document->offsetSet( kTAG_NS, $namespace );
print_r( $document->getArrayCopy() );

echo( "\n" );

//
// Get namespace.
//
echo( "Get namespace:\n" );
echo( '$result = $document->GetNamespaceTerm();' . "\n" );
$result = $document->GetNamespaceTerm();
print_r( $result->getArrayCopy() );

echo( "\n" );

//
// Insert term.
//
echo( "Insert term:\n" );
try
{
	echo( '$handle = $document->Store();' . "\n" );
	$handle = $document->Store();
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
echo( '$document->Name( "en", "A term" );' . "\n" );
$document->Name( "en", "A term" );
echo( '$handle = $document->Store();' . "\n" );
$handle = $document->Store();
var_dump( $handle );
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
