<?php

/**
 * Document object test suite.
 *
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		17/03/2016
 */

//
// Global definitions.
//
define( 'kENGINE', "MONGO" );

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
use Milko\PHPLib\MongoDB\Document;
use Milko\PHPLib\MongoDB\Collection;

//
// Document test classes.
//
class A extends \Milko\PHPLib\Document{}
class B extends \Milko\PHPLib\Document{}
class C extends \Milko\PHPLib\Document
{
	protected function doCreateReference( $theOffset, \Milko\PHPLib\Document $theDocument )
	{
		if( $theOffset == "@9999" )
			return $theDocument[ $theDocument->Collection()->KeyOffset() ];
		return parent::doCreateReference( $theOffset, $theDocument );
	}
	protected function doResolveReference( $theOffset, $theReference )
	{
		if( $theOffset == "@9999" )
			return $this->Collection()->FindByKey( $theReference );
		return parent::doResolveReference( $theOffset, $theReference );
	}
}

//
// Instantiate object.
//
if( kENGINE == "MONGO" )
{
	echo( '$url = "mongodb://localhost:27017/test_milkolib/test_collection";' . "\n" );
	$url = "mongodb://localhost:27017/test_milkolib/test_collection";
	echo( '$server = new \Milko\PHPLib\MongoDB\Server( $url' . " );\n" );
	$server = new \Milko\PHPLib\MongoDB\Server( $url );
}
elseif( kENGINE == "ARANGO" )
{
	echo('$url = "tcp://localhost:8529/test_milkolib/test_collection";' . "\n");
	$url = "tcp://localhost:8529/test_milkolib/test_collection";
	echo( '$server = new \Milko\PHPLib\ArangoDB\Server( $url' . " );\n" );
	$server = new \Milko\PHPLib\ArangoDB\Server( $url );
}
echo( '$database = $server->RetrieveDatabase( "test_milkolib" );' . "\n" );
$database = $server->GetDatabase( "test_milkolib" );
echo( '$collection = $database->RetrieveCollection( "test_collection" );' . "\n" );
$collection = $database->GetCollection( "test_collection" );
echo( '$collection->Truncate();' . "\n" );
$collection->Truncate();

echo( "\n====================================================================================\n\n" );

//
// Instantiate container.
//
echo( "Instantiate container:\n" );
echo( '$container = new Milko\PHPLib\Container( [kTAG_NAME => ["en" => "Jim"], kTAG_MIN_VAL => 21] );' . "\n" );
$container = new Milko\PHPLib\Container( [kTAG_NAME => ["en" => "Jim"], kTAG_MIN_VAL => 21] );
print_r( $container );

echo( "\n" );

//
// Instantiate document.
//
echo( "Instantiate document:\n" );
echo( '$document = new Milko\PHPLib\Document( $collection, $container->getArrayCopy() );' . "\n" );
$document = new Milko\PHPLib\Document( $collection, $container->getArrayCopy() );
echo( "Class: " . get_class( $document ) . "\n" );
echo( "Modified:   " . (( $document->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $document->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $document->getArrayCopy() );

echo( "\n" );

//
// Instantiate A.
//
echo( "Instantiate A:\n" );
echo( '$A = new A( $collection, $container->getArrayCopy() );' . "\n" );
$A = new A( $collection, $container->getArrayCopy() );
echo( "Class: " . get_class( $A ) . "\n" );
echo( "Modified:   " . (( $A->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $A->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $A->getArrayCopy() );

echo( "\n" );

//
// Instantiate B.
//
echo( "Instantiate B:\n" );
echo( '$B = new B( $collection, $A->getArrayCopy() );' . "\n" );
$B = new B( $collection, $A->getArrayCopy() );
echo( "Class: " . get_class( $B ) . "\n" );
echo( "Modified:   " . (( $B->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $B->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $B->getArrayCopy() );

echo( "\n====================================================================================\n\n" );

//
// Set key.
//
echo( "Set key:\n" );
echo( '$B[ $collection->KeyOffset() ] = "pippo";' . "\n" );
$B[ $collection->KeyOffset() ] = "pippo";
echo( "Class: " . get_class( $B ) . "\n" );
echo( "Modified:   " . (( $B->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $B->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $B->getArrayCopy() );

echo( "\n" );

//
// Set class.
//
echo( "Set class:\n" );
try
{
	echo( '$B[ $collection->ClassOffset() ] = "A";' . "\n" );
	$B[ $collection->ClassOffset() ] = "A";
	echo( "FALIED! - Should have raised an exception.\n" );
}
catch( RuntimeException $error )
{
	echo( "SUCCEEDED! - Has raised an exception.\n" );
	echo( $error->getMessage() . "\n" );
}

echo( "\n" );

//
// Set revision.
//
echo( "Set revision:\n" );
try
{
	echo( '$B[ $collection->RevisionOffset() ] = 33143106288;' . "\n" );
	$B[ $collection->RevisionOffset() ] = 33143106288;
	echo( "Class: " . get_class( $B ) . "\n" );
	echo( "Modified:   " . (( $B->IsModified() ) ? "Yes\n" : "No\n") );
	echo( "Persistent: " . (( $B->IsPersistent() ) ? "Yes\n" : "No\n") );
	echo( "Data: " );
	print_r( $B->getArrayCopy() );
}
catch( RuntimeException $error )
{
	echo( "FALIED! - Should not have raised an exception.\n" );
	echo( $error->getMessage() . "\n" );
}

echo( "\n====================================================================================\n\n" );

//
// Insert B.
//
echo( "Insert B:\n" );
echo( '$handle = $B->Store();' . "\n" );
$handle = $B->Store();
var_dump( $handle );
echo( "Class: " . get_class( $B ) . "\n" );
echo( "Modified:   " . (( $B->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $B->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $B->getArrayCopy() );

echo( "\n====================================================================================\n\n" );

//
// Set key.
//
echo( "Set key:\n" );
try
{
	echo( '$B[ $collection->KeyOffset() ] = "pippo";' . "\n" );
	$B[ $collection->KeyOffset() ] = "pippo";
	echo( "FALIED! - Should have raised an exception.\n" );
}
catch( RuntimeException $error )
{
	echo( "SUCCEEDED! - Has raised an exception.\n" );
	echo( $error->getMessage() . "\n" );
}

echo( "\n" );

//
// Set class.
//
echo( "Set class:\n" );
try
{
	echo( '$B[ $collection->ClassOffset() ] = "A";' . "\n" );
	$B[ $collection->ClassOffset() ] = "A";
	echo( "FALIED! - Should have raised an exception.\n" );
}
catch( RuntimeException $error )
{
	echo( "SUCCEEDED! - Has raised an exception.\n" );
	echo( $error->getMessage() . "\n" );
}

echo( "\n" );

//
// Set revision.
//
echo( "Set revision:\n" );
try
{
	echo( '$B[ $collection->RevisionOffset() ] = 33143106288;' . "\n" );
	$B[ $collection->RevisionOffset() ] = 33143106288;
	echo( "FALIED! - Should have raised an exception.\n" );
}
catch( RuntimeException $error )
{
	echo( "SUCCEEDED! - Has raised an exception.\n" );
	echo( $error->getMessage() . "\n" );
}

echo( "\n====================================================================================\n\n" );

//
// Create embedded document 1.
//
echo( "Create embedded document 1:\n" );
echo( '$sub1 = new A( $collection, [$collection->KeyOffset() => "sub1", kTAG_NAME => ["en" => "Object 1"]] );' . "\n" );
$sub1 = new A( $collection, [$collection->KeyOffset() => "sub1", kTAG_NAME => ["en" => "Object 1"]] );
echo( "Class: " . get_class( $sub1 ) . "\n" );
echo( "Modified:   " . (( $sub1->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $sub1->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $sub1->getArrayCopy() );

echo( "\n" );

//
// Create embedded document 2.
//
echo( "Create embedded document 2:\n" );
echo( '$sub2 = new B( $collection, [$collection->KeyOffset() => "sub2", kTAG_NAME => ["en" => "Object 2"]] );' . "\n" );
$sub2 = new B( $collection, [$collection->KeyOffset() => "sub2", kTAG_NAME => ["en" => "Object 2"]] );
echo( "Class: " . get_class( $sub2 ) . "\n" );
echo( "Modified:   " . (( $sub2->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $sub2->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $sub2->getArrayCopy() );

echo( "\n" );

//
// Create container document.
//
echo( "Create container document:\n" );
echo( '$document = new C( $collection, [kTAG_NAME => ["en" => "container"]] );' . "\n" );
$document = new C( $collection, [kTAG_NAME => ["en" => "container"]] );
echo( "Class: " . get_class( $document ) . "\n" );
echo( "Modified:   " . (( $document->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $document->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $document->getArrayCopy() );

echo( "\n" );

//
// Add embedded documents.
//
echo( "Add embedded documents:\n" );
echo( '$document[ "@9998" ] = $sub1;' . "\n" );
$document[ "@9998" ] = $sub1;
echo( '$document[ "@9999" ] = $sub2;' . "\n" );
$document[ "@9999" ] = $sub2;

echo( "\n" );

//
// Store document.
//
echo( "Store document:\n" );
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
// Retrieve embedded document 1.
//
echo( "Retrieve embedded document 1:\n" );
echo( '$sub1 = $document->ResolveReference( "@9998" );' . "\n" );
$sub1 = $document->ResolveReference( "@9998" );
echo( "Class: " . get_class( $sub1 ) . "\n" );
echo( "Modified:   " . (( $sub1->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $sub1->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $sub1->getArrayCopy() );

echo( "\n" );

//
// Retrieve embedded document 2.
//
echo( "Retrieve embedded document 2:\n" );
echo( '$sub2 = $document->ResolveReference( "@9999" );' . "\n" );
$sub2 = $document->ResolveReference( "@9999" );
echo( "Class: " . get_class( $sub2 ) . "\n" );
echo( "Modified:   " . (( $sub2->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $sub2->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $sub2->getArrayCopy() );

echo( "\n" );

//
// Retrieve embedded document 2.
//
echo( "Retrieve embedded document 2:\n" );
echo( '$sub2 = $document->ResolveReference( "@9999", $document[ "@9999" ] );' . "\n" );
$sub2 = $document->ResolveReference( "@9999", $document[ "@9999" ] );
var_dump( $document[ "sub2" ] );
echo( "Class: " . get_class( $sub2 ) . "\n" );
echo( "Modified:   " . (( $sub2->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $sub2->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $sub2->getArrayCopy() );


?>
