<?php

/**
 * Node object test suite.
 *
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		04/05/2016
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
use Milko\PHPLib\MongoDB\Collection;

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
$x = $database->GetDescriptor( kTAG_DATA_TYPE );
print_r( $x );
var_dump( $database->CheckEnumerations( kTAG_DATA_TYPE, [ kTYPE_STRUCT, kTYPE_LANG_STRINGS ] ) );
var_dump( $database->CheckEnumerations( kTAG_DATA_TYPE, [ kTYPE_STRUCT, "pippo" ] ) );
var_dump( $database->CheckEnumerations( kTAG_NS, [ kTYPE_STRUCT, kTYPE_LANG_STRINGS ] ) );
var_dump( $database->CheckEnumerations( "pippo", [ kTYPE_STRUCT, kTYPE_LANG_STRINGS ] ) );
exit;

echo( "\n" );

//
// Instantiate terms collection.
//
echo( "Instantiate terms collection:\n" );
echo( '$terms = $database->NewTermsCollection();' . "\n" );
$terms = $database->NewTermsCollection();

echo( "\n" );

//
// Instantiate nodes collection.
//
echo( "Instantiate nodes collection:\n" );
echo( '$nodes = $database->NewCollection( "test_nodes" );' . "\n" );
$nodes = $database->NewCollection( "test_nodes" );

echo( "\n====================================================================================\n\n" );

//
// Instantiate node.
//
echo( "Instantiate node:\n" );
echo( '$node = new Milko\PHPLib\Node( $nodes );' . "\n" );
$node = new Milko\PHPLib\Node( $nodes );
echo( '$node[ kTAG_NODE_REF ] = $terms->BuildDocumentHandle( kTYPE_ENUM );' . "\n" );
$node[ kTAG_NODE_REF ] = $terms->BuildDocumentHandle( kTYPE_ENUM );
echo( "Class: " . get_class( $node ) . "\n" );
echo( "Modified:   " . (( $node->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $node->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $node->getArrayCopy() );

echo( "\n" );

//
// Insert node.
//
echo( "Insert node:\n" );
echo( '$node_handle = $node->Store();' . "\n" );
$node_handle = $node->Store();
var_dump( $node_handle );
echo( "Class: " . get_class( $node ) . "\n" );
echo( "Modified:   " . (( $node->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $node->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $node->getArrayCopy() );


?>
