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
// Include local definitions.
//
require_once(dirname(__DIR__) . "/defines.inc.php");

//
// Include utility functions.
//
require_once( "functions.php" );

//
// Reference class.
//
use Milko\PHPLib\MongoDB\Document;

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

echo( "\n" );

//
// Drop database.
//
echo( "Drop database:\n" );
echo( '$tmp = $server->NewDatabase( "test_milkolib" );' . "\n" );
$tmp = $server->NewDatabase( "test_milkolib" );
echo( '$tmp->Drop();' . "\n" );
$tmp->Drop();

echo( "\n" );

//
// Instantiate wrapper.
//
echo( "Instantiate wrapper:\n" );
echo( '$database = $server->NewWrapper( "test_milkolib" );' . "\n" );
$database = $server->NewWrapper( "test_milkolib" );
echo( "Class: " . get_class( $database ) . "\n" );

echo( "\n" );

//
// Cache data dictionary.
//
echo( "Cache data dictionary:\n" );
echo( '$database->CacheDataDictionary();' . "\n" );
$database->CacheDataDictionary();

echo( "\n" );

//
// Instantiate collection.
//
echo( '$collection = $database->NewCollection( "test_collection" );' . "\n" );
$collection = $database->NewCollection( "test_collection" );

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
// Load test descriptors.
//
echo( "Load test descriptors\n" );
$term = new \Milko\PHPLib\Descriptor( $collection, [
		kTAG_LID => 'test_kTYPE_INT', kTAG_SYMBOL => 'kTYPE_INT',
		kTAG_DATA_TYPE => kTYPE_INT,
		kTAG_DATA_KIND => [ kKIND_DISCRETE ],
		kTAG_NAME => [ 'en' => 'Test integer' ] ]
);
$term[ $collection->KeyOffset() ] = $term[ kTAG_GID ];
$term->Store();

$term = new \Milko\PHPLib\Descriptor( $collection, [
		kTAG_LID => 'test_kTYPE_FLOAT', kTAG_SYMBOL => 'kTYPE_FLOAT',
		kTAG_DATA_TYPE => kTYPE_FLOAT,
		kTAG_DATA_KIND => [ kKIND_DISCRETE ],
		kTAG_NAME => [ 'en' => 'Test float' ] ]
);
$term[ $collection->KeyOffset() ] = $term[ kTAG_GID ];
$term->Store();

$term = new \Milko\PHPLib\Descriptor( $collection, [
		kTAG_LID => 'test_kTYPE_BOOLEAN', kTAG_SYMBOL => 'kTYPE_BOOLEAN',
		kTAG_DATA_TYPE => kTYPE_BOOLEAN,
		kTAG_DATA_KIND => [ kKIND_DISCRETE ],
		kTAG_NAME => [ 'en' => 'Test boolean' ] ]
);
$term[ $collection->KeyOffset() ] = $term[ kTAG_GID ];
$term->Store();

$term = new \Milko\PHPLib\Descriptor( $collection, [
		kTAG_LID => 'test_kTYPE_URL', kTAG_SYMBOL => 'kTYPE_URL',
		kTAG_DATA_TYPE => kTYPE_URL,
		kTAG_DATA_KIND => [ kKIND_DISCRETE ],
		kTAG_NAME => [ 'en' => 'Test URL' ] ]
);
$term[ $collection->KeyOffset() ] = $term[ kTAG_GID ];
$term->Store();

$term = new \Milko\PHPLib\Descriptor( $collection, [
		kTAG_LID => 'test_kTYPE_STRING_DATE', kTAG_SYMBOL => 'kTYPE_STRING_DATE',
		kTAG_DATA_TYPE => kTYPE_STRING_DATE,
		kTAG_DATA_KIND => [ kKIND_DISCRETE ],
		kTAG_NAME => [ 'en' => 'Test string date' ] ]
);
$term[ $collection->KeyOffset() ] = $term[ kTAG_GID ];
$term->Store();

$term = new \Milko\PHPLib\Descriptor( $collection, [
		kTAG_LID => 'test_kTYPE_STRING_LAT', kTAG_SYMBOL => 'kTYPE_STRING_LAT',
		kTAG_DATA_TYPE => kTYPE_STRING_LAT,
		kTAG_DATA_KIND => [ kKIND_DISCRETE ],
		kTAG_NAME => [ 'en' => 'Test string latitude' ] ]
);
$term[ $collection->KeyOffset() ] = $term[ kTAG_GID ];
$term->Store();

$term = new \Milko\PHPLib\Descriptor( $collection, [
		kTAG_LID => 'test_kTYPE_STRING_LON', kTAG_SYMBOL => 'kTYPE_STRING_LON',
		kTAG_DATA_TYPE => kTYPE_STRING_LON,
		kTAG_DATA_KIND => [ kKIND_DISCRETE ],
		kTAG_NAME => [ 'en' => 'Test string longitude' ] ]
);
$term[ $collection->KeyOffset() ] = $term[ kTAG_GID ];
$term->Store();

$term = new \Milko\PHPLib\Descriptor( $collection, [
		kTAG_LID => 'test_kTYPE_REF', kTAG_SYMBOL => 'kTYPE_REF',
		kTAG_DATA_TYPE => kTYPE_REF,
		kTAG_DATA_KIND => [ kKIND_DISCRETE ],
		kTAG_NAME => [ 'en' => 'Test object reference' ] ]
);
$term[ $collection->KeyOffset() ] = $term[ kTAG_GID ];
$term->Store();

$term = new \Milko\PHPLib\Descriptor( $collection, [
		kTAG_LID => 'test_kTYPE_REF_TERM', kTAG_SYMBOL => 'kTYPE_REF_TERM',
		kTAG_DATA_TYPE => kTYPE_REF_TERM,
		kTAG_DATA_KIND => [ kKIND_DISCRETE ],
		kTAG_NAME => [ 'en' => 'Test term reference' ] ]
);
$term[ $collection->KeyOffset() ] = $term[ kTAG_GID ];
$term->Store();

echo( "\n====================================================================================\n\n" );

//
// Set invalid integer.
//
echo( "Set invalid integer:\n" );
echo( '$B[ "test_kTYPE_INT" ] = "pippo";' . "\n" );
$B[ "test_kTYPE_INT" ] = "pippo";
try
{
	echo( '$B->Validate();' . "\n" );
	$B->Validate();
	echo( "FALIED! - Should have raised an exception.\n" );
}
catch( RuntimeException $error )
{
	echo( "SUCCEEDED! - Has raised an exception.\n" );
	echo( $error->getMessage() . "\n" );
	echo( '$B[ "test_kTYPE_INT" ] = "25";' . "\n" );
	$B[ "test_kTYPE_INT" ] = "25";
	echo( '$B->Validate();' . "\n" );
	$B->Validate();
	var_dump( $B[ "test_kTYPE_INT" ] );
}

echo( "\n" );

//
// Set invalid float.
//
echo( "Set invalid float:\n" );
echo( '$B[ "test_kTYPE_FLOAT" ] = "pippo";' . "\n" );
$B[ "test_kTYPE_FLOAT" ] = "pippo";
try
{
	echo( '$B->Validate();' . "\n" );
	$B->Validate();
	echo( "FALIED! - Should have raised an exception.\n" );
}
catch( RuntimeException $error )
{
	echo( "SUCCEEDED! - Has raised an exception.\n" );
	echo( $error->getMessage() . "\n" );
	echo( '$B[ "test_kTYPE_FLOAT" ] = "25";' . "\n" );
	$B[ "test_kTYPE_FLOAT" ] = "25";
	echo( '$B->Validate();' . "\n" );
	$B->Validate();
	var_dump( $B[ "test_kTYPE_FLOAT" ] );
}

echo( "\n" );

//
// Set invalid boolean.
//
echo( "Set invalid boolean:\n" );
echo( '$B[ "test_kTYPE_BOOLEAN" ] = "pippo";' . "\n" );
$B[ "test_kTYPE_BOOLEAN" ] = "pippo";
try
{
	echo( '$B->Validate();' . "\n" );
	$B->Validate();
	echo( "FALIED! - Should have raised an exception.\n" );
}
catch( RuntimeException $error )
{
	echo( "SUCCEEDED! - Has raised an exception.\n" );
	echo( $error->getMessage() . "\n" );
	echo( '$B[ "test_kTYPE_BOOLEAN" ] = "Y";' . "\n" );
	$B[ "test_kTYPE_BOOLEAN" ] = "Y";
	echo( '$B->Validate();' . "\n" );
	$B->Validate();
	var_dump( $B[ "test_kTYPE_BOOLEAN" ] );
}

echo( "\n" );

//
// Set invalid URL.
//
echo( "Set invalid URL:\n" );
echo( '$B[ "test_kTYPE_URL" ] = "pippo";' . "\n" );
$B[ "test_kTYPE_URL" ] = "pippo";
try
{
	echo( '$B->Validate();' . "\n" );
	$B->Validate();
	echo( "FALIED! - Should have raised an exception.\n" );
}
catch( RuntimeException $error )
{
	echo( "SUCCEEDED! - Has raised an exception.\n" );
	echo( $error->getMessage() . "\n" );
	echo( '$B[ "test_kTYPE_URL" ] = "http://www.apple.com";' . "\n" );
	$B[ "test_kTYPE_URL" ] = "http://www.apple.com";
	echo( '$B->Validate();' . "\n" );
	$B->Validate();
	var_dump( $B[ "test_kTYPE_URL" ] );
}

echo( "\n" );

//
// Set invalid string date.
//
echo( "Set invalid string date:\n" );
echo( '$B[ "test_kTYPE_STRING_DATE" ] = "pippo";' . "\n" );
$B[ "test_kTYPE_STRING_DATE" ] = "pippo";
try
{
	echo( '$B->Validate();' . "\n" );
	$B->Validate();
	echo( "FALIED! - Should have raised an exception.\n" );
}
catch( RuntimeException $error )
{
	echo( "SUCCEEDED! - Has raised an exception.\n" );
	echo( $error->getMessage() . "\n" );
	echo( '$B[ "test_kTYPE_STRING_DATE" ] = "  198702  ";' . "\n" );
	$B[ "test_kTYPE_STRING_DATE" ] = "  198702  ";
	echo( '$B->Validate();' . "\n" );
	$B->Validate();
	var_dump( $B[ "test_kTYPE_STRING_DATE" ] );
}

echo( "\n" );

//
// Set invalid string latitude.
//
echo( "Set invalid string latitude:\n" );
echo( '$B[ "test_kTYPE_STRING_LAT" ] = "pippo";' . "\n" );
$B[ "test_kTYPE_STRING_LAT" ] = "pippo";
try
{
	echo( '$B->Validate();' . "\n" );
	$B->Validate();
	echo( "FALIED! - Should have raised an exception.\n" );
}
catch( RuntimeException $error )
{
	echo( "SUCCEEDED! - Has raised an exception.\n" );
	echo( $error->getMessage() . "\n" );
	try
	{
		echo( '$B[ "test_kTYPE_STRING_LAT" ] = "91°n";' . "\n" );
		$B[ "test_kTYPE_STRING_LAT" ] = "91°n";
		echo( '$B->Validate();' . "\n" );
		$B->Validate();
		echo( "FALIED! - Should have raised an exception.\n" );
	}
	catch( RuntimeException $error )
	{
		echo( "SUCCEEDED! - Has raised an exception.\n" );
		echo( $error->getMessage() . "\n" );
		echo( '$B[ "test_kTYPE_STRING_LAT" ] = "22°33\'44.1234\"n";' . "\n" );
		$B[ "test_kTYPE_STRING_LAT" ] = "22°33'44.1234\"n";
		echo( '$B->Validate();' . "\n" );
		$B->Validate();
		var_dump( $B[ "test_kTYPE_STRING_LAT" ] );
	}
}

echo( "\n" );

//
// Set invalid string longitude.
//
echo( "Set invalid string longitude:\n" );
echo( '$B[ "test_kTYPE_STRING_LON" ] = "pippo";' . "\n" );
$B[ "test_kTYPE_STRING_LON" ] = "pippo";
try
{
	echo( '$B->Validate();' . "\n" );
	$B->Validate();
	echo( "FALIED! - Should have raised an exception.\n" );
}
catch( RuntimeException $error )
{
	echo( "SUCCEEDED! - Has raised an exception.\n" );
	echo( $error->getMessage() . "\n" );
	try
	{
		echo( '$B[ "test_kTYPE_STRING_LON" ] = "181°n";' . "\n" );
		$B[ "test_kTYPE_STRING_LON" ] = "181°n";
		echo( '$B->Validate();' . "\n" );
		$B->Validate();
		echo( "FALIED! - Should have raised an exception.\n" );
	}
	catch( RuntimeException $error )
	{
		echo( "SUCCEEDED! - Has raised an exception.\n" );
		echo( $error->getMessage() . "\n" );
		echo( '$B[ "test_kTYPE_STRING_LON" ] = "123°33\'44.1234\"e";' . "\n" );
		$B[ "test_kTYPE_STRING_LON" ] = "123°33'44.1234\"e";
		echo( '$B->Validate();' . "\n" );
		$B->Validate();
		var_dump( $B[ "test_kTYPE_STRING_LON" ] );
	}
}

echo( "\n" );

//
// Set invalid reference.
//
echo( "Set invalid reference:\n" );
echo( '$B[ "test_kTYPE_REF" ] = "pippo";' . "\n" );
$B[ "test_kTYPE_REF" ] = "pippo";
try
{
	echo( '$B->Validate();' . "\n" );
	$B->Validate();
	echo( "FALIED! - Should have raised an exception.\n" );
}
catch( RuntimeException $error )
{
	echo( "SUCCEEDED! - Has raised an exception.\n" );
	echo( $error->getMessage() . "\n" );
	echo( '$handle_col = $database->NewDescriptorsCollection();' . "\n" );
	$handle_col = $database->NewDescriptorsCollection();
	echo( '$B[ "test_kTYPE_REF" ] = $handle_col->BuildDocumentHandle( kTAG_GID, $handle_col );' . "\n" );
	$B[ "test_kTYPE_REF" ] = $handle_col->BuildDocumentHandle( kTAG_GID, $handle_col );
	echo( '$B->Validate();' . "\n" );
	$B->Validate();
	var_dump( $B[ "test_kTYPE_REF" ] );
}

echo( "\n" );

//
// Set invalid term reference.
//
echo( "Set invalid term reference:\n" );
echo( '$B[ "test_kTYPE_REF_TERM" ] = "pippo";' . "\n" );
$B[ "test_kTYPE_REF_TERM" ] = "pippo";
try
{
	echo( '$B->Validate();' . "\n" );
	$B->Validate();
	echo( "FALIED! - Should have raised an exception.\n" );
}
catch( RuntimeException $error )
{
	echo( "SUCCEEDED! - Has raised an exception.\n" );
	echo( $error->getMessage() . "\n" );
	echo( '$handle_col = $database->NewTermsCollection();' . "\n" );
	$handle_col = $database->NewTermsCollection();
	echo( '$B[ "test_kTYPE_REF_TERM" ] = kTYPE_REF_TERM;' . "\n" );
	$B[ "test_kTYPE_REF_TERM" ] = kTYPE_REF_TERM;
	echo( '$B->Validate();' . "\n" );
	$B->Validate();
	var_dump( $B[ "test_kTYPE_REF_TERM" ] );
}
exit;

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
