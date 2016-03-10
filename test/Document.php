<?php

/**
 * Document object test suite.
 *
 * This test suite will use a ficticious test class, to perform more in depth test use the
 * concrete classes derived from Document.
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		10/03/2016
 */

//
// Include local definitions.
//
require_once( dirname( __DIR__ ) . "/includes.local.php" );

//
// Include utility functions.
//
require_once( "functions.php" );

//
// Reference class.
//
use Milko\PHPLib\Document;

//
// DataServer test class.
//
class test_Document extends Milko\PHPLib\Document
{
	//
	// Implement Document virtual interface.
	//
	public function Key( $theValue = NULL )	{	return $this->manageProperty( '_key', $theValue );	}
	public function ID( $theValue = NULL )	{	return $this->manageProperty( '_id', $theValue );	}
	public function Record()				{	return $this->toArray();							}
}

//
// Instantiate empty document.
//
echo( "Instantiate empty document:\n" );
echo( '$test = new test_Document(' . ");\n" );
$test = new test_Document();
var_dump( $test );

echo( "\n" );

//
// Add identifier.
//
echo( "Add identifier:\n" );
echo( '$result = $test->ID( "ID" );' . "\n" );
$result = $test->ID( "ID" );
var_dump( $result );
print_r( $test );

echo( "\n" );

//
// Add key.
//
echo( "Add key:\n" );
echo( '$result = $test->Key( "KEY" );' . "\n" );
$result = $test->Key( "KEY" );
var_dump( $result );
print_r( $test );

echo( "\n" );

//
// Add value.
//
echo( "Add value:\n" );
echo( '$test[ "value" ] = "some value";' . "\n" );
$test[ "value" ] = "some value";
print_r( $test );

echo( "\n====================================================================================\n\n" );

//
// Get ID.
//
echo( "Get ID:\n" );
echo( '$result = $test->ID();' . "\n" );
$result = $test->ID();
var_dump( $result );

echo( "\n" );

//
// Get key.
//
echo( "Get key:\n" );
echo( '$result = $test->Key();' . "\n" );
$result = $test->Key();
var_dump( $result );

echo( "\n" );

//
// Serialise object.
//
echo( "Serialise object:\n" );
echo( '$result = $test->Record();' . "\n" );
$result = $test->Record();
var_dump( $result );

echo( "\n====================================================================================\n\n" );

//
// Add subdocument.
//
echo( "Add subdocument:\n" );
echo( '$test[ "subdocument" ] = new test_Document( ["field1" => "value 1", "field2" => 47] );' . "\n" );
$test[ "subdocument" ] = new test_Document( ["field1" => "value 1", "field2" => 47] );
var_dump( $test );

echo( "\n" );

//
// Serialise object.
//
echo( "Serialise object:\n" );
echo( '$result = $test->Record();' . "\n" );
$result = $test->Record();
var_dump( $result );


?>
