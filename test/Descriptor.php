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
// Enable exception logging.
//
//triagens\ArangoDb\Exception::enableLogging();

//
// Instantiate server.
//
echo( "Instantiate server:\n" );
if( kENGINE == "MONGO" )
{
	echo( '$url = "mongodb://localhost:2701";' . "\n" );
	$url = "mongodb://localhost:27017";
	echo( '$server = new \Milko\PHPLib\MongoDB\Server( $url' . " );\n" );
	$server = new \Milko\PHPLib\MongoDB\Server( $url );
}
elseif( kENGINE == "ARANGO" )
{
	echo('$url = "tcp://localhost:8529";' . "\n");
	$url = "tcp://localhost:8529";
	echo( '$server = new \Milko\PHPLib\ArangoDB\Server( $url' . " );\n" );
	$server = new \Milko\PHPLib\ArangoDB\Server( $url );
}

echo( "\n" );

//
// Instantiate wrapper.
//
echo( "Instantiate wrapper:\n" );
echo( '$wrapper = $server->NewWrapper( "test_milkolib" );' . "\n" );
$wrapper = $server->NewWrapper( "test_milkolib" );
echo( "Class: " . get_class( $wrapper ) . "\n" );

echo( "\n" );

//
// Instantiate terms collection.
//
echo( "Instantiate terms collection:\n" );
echo( '$terms = $wrapper->NewTermsCollection();' . "\n" );
$terms = $wrapper->NewTermsCollection();
echo( "Count: " . $terms->Count() . "\n" );

echo( "\n" );

//
// Instantiate descriptors collection.
//
echo( "Instantiate descriptors collection:\n" );
echo( '$descriptors = $wrapper->NewDescriptorsCollection();' . "\n" );
$descriptors = $wrapper->NewDescriptorsCollection();
echo( "Count: " . $descriptors->Count() . "\n" );
echo( '$descriptors->Truncate();' . "\n" );
$descriptors->Truncate();

echo( "\n====================================================================================\n\n" );

//
// Instantiate descriptor.
//
echo( "Instantiate descriptor:\n" );
echo( '$descriptor = new Milko\PHPLib\Descriptor( $descriptors, [ kTAG_LID => "descriptor", kTAG_NAME => ["en" => "Descriptor"] ] );' . "\n" );
$descriptor = new Milko\PHPLib\Descriptor( $descriptors, [ kTAG_LID => "descriptor", kTAG_NAME => ["en" => "Descriptor"] ] );
echo( "Class: " . get_class( $descriptor ) . "\n" );
echo( "Modified:   " . (( $descriptor->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $descriptor->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $descriptor->getArrayCopy() );

echo( "\n" );

//
// Insert descriptor.
//
echo( "Insert descriptor:\n" );
try
{
	echo( '$handle = $descriptor->Store();' . "\n" );
	$handle = $descriptor->Store();
	echo( "FALIED! - Should have raised an exception.\n" );
}
catch( RuntimeException $error )
{
	echo( "SUCCEEDED! - Has raised an exception.\n" );
	echo( $error->getMessage() . "\n" );
}

echo( "\n====================================================================================\n\n" );

//
// Insert descriptor.
//
echo( "Insert descriptor:\n" );
echo( '$descriptor[ kTAG_SYMBOL ] = "DESCRIPTOR";' . "\n" );
$descriptor[ kTAG_SYMBOL ] = "DESCRIPTOR";
echo( '$descriptor[ kTAG_DATA_TYPE ] = kTYPE_STRING;' . "\n" );
$descriptor[ kTAG_DATA_TYPE ] = kTYPE_STRING;
echo( '$handle = $descriptor->Store();' . "\n" );
$handle = $descriptor->Store();
var_dump( $handle );
echo( "Class: " . get_class( $descriptor ) . "\n" );
echo( "Modified:   " . (( $descriptor->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $descriptor->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $descriptor->getArrayCopy() );

echo( "\n" );

//
// Insert builtin descriptor.
//
echo( "Insert builtin descriptor:\n" );
echo( '$data = [ kTAG_LID => "builtin", kTAG_NAME => ["en" => "Built-in"], kTAG_SYMBOL => "BUILTIN", kTAG_DATA_TYPE => kTYPE_INT ];' . "\n" );
$data = [ kTAG_LID => "builtin", kTAG_NAME => ["en" => "Built-in"], kTAG_SYMBOL => "BUILTIN", kTAG_DATA_TYPE => kTYPE_INT ];
echo( '$descriptor = new Milko\PHPLib\Descriptor( $descriptors, $data );' . "\n" );
$descriptor = new Milko\PHPLib\Descriptor( $descriptors, $data );
echo( '$descriptor->offsetSet( $descriptors->KeyOffset(), $descriptor->offsetGet( kTAG_GID ) );' . "\n" );
$descriptor->offsetSet( $descriptors->KeyOffset(), $descriptor->offsetGet( kTAG_GID ) );
print_r( $descriptor->getArrayCopy() );
echo( '$handle = $descriptor->Store();' . "\n" );
$handle = $descriptor->Store();
var_dump( $handle );
echo( "Class: " . get_class( $descriptor ) . "\n" );
echo( "Modified:   " . (( $descriptor->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $descriptor->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $descriptor->getArrayCopy() );

echo( "\n" );

//
// Insert another user descriptor.
//
echo( "Insert another user descriptor:\n" );
echo( '$descriptor = new Milko\PHPLib\Descriptor( $descriptors, $data );' . "\n" );
$descriptor = new Milko\PHPLib\Descriptor( $descriptors, $data );
echo( '$descriptor->offsetSet( kTAG_LID, "other" );' . "\n" );
$descriptor->offsetSet( kTAG_LID, "other" );
echo( '$handle = $descriptor->Store();' . "\n" );
$handle = $descriptor->Store();
var_dump( $handle );
echo( "Class: " . get_class( $descriptor ) . "\n" );
echo( "Modified:   " . (( $descriptor->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $descriptor->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $descriptor->getArrayCopy() );

echo( "\n====================================================================================\n\n" );

//
// Instantiate descriptor.
//
echo( "Instantiate descriptor:\n" );
echo( '$descriptor = new Milko\PHPLib\Descriptor( $descriptors, [ kTAG_LID => "test", kTAG_NAME => ["en" => "Test"] ] );' . "\n" );
$descriptor = new Milko\PHPLib\Descriptor( $descriptors, [ kTAG_LID => "test", kTAG_NAME => ["en" => "Test"] ] );
echo( "Class: " . get_class( $descriptor ) . "\n" );
echo( "Modified:   " . (( $descriptor->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $descriptor->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $descriptor->getArrayCopy() );

echo( "\n" );

//
// Get namespace term.
//
echo( "Get namespace term:\n" );
echo( '$namespace = Milko\PHPLib\Descriptor::GetByGID( $terms, ":type:property" );' . "\n" );
$namespace = Milko\PHPLib\Descriptor::GetByGID( $terms, ":type:property" );
print_r( $namespace->getArrayCopy() );

echo( "\n" );

//
// Set namespace by term.
//
echo( "Set namespace by term:\n" );
echo( '$result = $descriptor->SetNamespaceByTerm( $namespace );' . "\n" );
$result = $descriptor->SetNamespaceByTerm( $namespace );
var_dump( $result );
print_r( $descriptor->getArrayCopy() );

echo( "\n" );

//
// Set namespace by GID.
//
echo( "Set namespace by GID:\n" );
echo( '$result = $descriptor->SetNamespaceByGID( $namespace[ kTAG_GID ] );' . "\n" );
$result = $descriptor->SetNamespaceByGID( $namespace[ kTAG_GID ] );
var_dump( $result );
print_r( $descriptor->getArrayCopy() );

echo( "\n" );

//
// Set namespace by term.
//
echo( "Set namespace by term:\n" );
echo( '$descriptor->offsetSet( kTAG_NS, $namespace );' . "\n" );
$descriptor->offsetSet( kTAG_NS, $namespace );
print_r( $descriptor->getArrayCopy() );

echo( "\n" );

//
// Get namespace.
//
echo( "Get namespace:\n" );
echo( '$result = $descriptor->GetNamespaceTerm();' . "\n" );
$result = $descriptor->GetNamespaceTerm();
print_r( $result->getArrayCopy() );

echo( "\n====================================================================================\n\n" );

//
// Generate a global identifier.
//
echo( "Generate a global identifier:\n" );
echo( '$result = \Milko\PHPLib\Descriptor::MakeGID( "ID" );' . "\n" );
$result = \Milko\PHPLib\Descriptor::MakeGID( "ID" );
var_dump( $result );
echo( '$result = \Milko\PHPLib\Descriptor::MakeGID( "ID", "" );' . "\n" );
$result = \Milko\PHPLib\Descriptor::MakeGID( "ID", "" );
var_dump( $result );
echo( '$result = \Milko\PHPLib\Descriptor::MakeGID( "ID", "ns" );' . "\n" );
$result = \Milko\PHPLib\Descriptor::MakeGID( "ID", "ns" );
var_dump( $result );

echo( "\n" );

//
// Get a descriptor by global identifier.
//
echo( "Get a term by global identifier:\n" );
echo( '$result = \Milko\PHPLib\Descriptor::GetByGID( $descriptors, "builtin" );' . "\n" );
$result = \Milko\PHPLib\Descriptor::GetByGID( $descriptors, "builtin" );
echo( "Class: " . get_class( $result ) . "\n" );
echo( "Modified:   " . (( $result->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $result->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $result->getArrayCopy() );

echo( "\n====================================================================================\n\n" );

//
// Get descriptors from cache.
//
echo( "Get descriptors from cache:\n" );
echo( '$result = $wrapper->GetDescriptor( "builtin" );' . "\n" );
$result = $wrapper->GetDescriptor( "builtin" );
print_r( $result );


?>
