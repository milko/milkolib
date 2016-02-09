<?php

/**
 * Container object test suite.
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		06/02/2016
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
use Milko\PHPLib\Container;

//
// Test class.
//
class test_Container extends Milko\PHPLib\Container
{
	//
	// Declare test attribute.
	//
	var $attribute;

	//
	// Declare attribute accessor method.
	//
	function Attribute( $theValue = NULL )
	{
		return $this->manageAttribute( $this->attribute, $theValue );
	}

	//
	// Declare property accessor method.
	//
	function Property( $theProperty, $theValue = NULL )
	{
		return $this->manageProperty( $theProperty, $theValue );
	}
}

//
// Instantiate object.
//
echo( '$test = new test_Container();' . "\n\n" );
$test = new test_Container();

//
// Retrieve attributes.
//
echo( "Retrieve attribute:\n" );
echo( '$result = $test->Attribute();' . "\n" );
$result = dumpValue( $test->Attribute() );
$attribute = dumpValue( $test->attribute );
echo( "Result: $result Attribute: $attribute\n" );

echo( "\n" );


//
// Set attributes.
//
echo( "Set attribute:\n" );
echo( '$result = $test->Attribute( "A value" );' . "\n" );
$result = dumpValue( $test->Attribute( "A value" ) );
$attribute = dumpValue( $test->attribute );
echo( "Result: $result Attribute: $attribute\n" );

echo( "\n" );

//
// Retrieve attributes.
//
echo( "Retrieve attribute:\n" );
echo( '$result = $test->Attribute();' . "\n" );
$result = dumpValue( $test->Attribute() );
$attribute = dumpValue( $test->attribute );
echo( "Result: $result Attribute: $attribute\n" );

echo( "\n" );

//
// Reset attributes.
//
echo( "Reset attribute:\n" );
echo( '$result = $test->Attribute( FALSE );' . "\n" );
$result = dumpValue( $test->Attribute( FALSE ) );
$attribute = dumpValue( $test->attribute );
echo( "Result: $result Attribute: $attribute\n" );

echo( "\n====================================================================================\n\n" );

//
// Retrieve properties.
//
echo( "Retrieve property:\n" );
echo( '$result = Property( "Property" );' . "\n" );
$result = dumpValue( $test->Property( "Property" ) );
$property = dumpValue( $test[ "Property" ] );
echo( "Result: $result Property: $property\n" );

echo( "\n" );

//
// Set properties.
//
echo( "Set property:\n" );
echo( '$result = Property( "Property", "A value" );' . "\n" );
$result = dumpValue( $test->Property( "Property", "A value" ) );
$property = dumpValue( $test[ "Property" ] );
echo( "Result: $result Property: $property\n" );

echo( "\n" );

//
// Retrieve properties.
//
echo( "Retrieve property:\n" );
echo( '$result = Property( "Property" );' . "\n" );
$result = dumpValue( $test->Property( "Property" ) );
$property = dumpValue( $test[ "Property" ] );
echo( "Result: $result Property: $property\n" );

echo( "\n" );

//
// Delete properties.
//
echo( "Delete property:\n" );
echo( '$result = Property( "Property", FALSE );' . "\n" );
$result = dumpValue( $test->Property( "Property", FALSE ) );
$property = dumpValue( $test[ "Property" ] );
echo( "Result: $result Property: $property\n" );

echo( "\n====================================================================================\n\n" );

//
// Retrieve offset.
//
echo( "Retrieve offset:\n" );
echo( '$result = $test[ "Property" ];' . "\n" );
$result = dumpValue( $test[ "Property" ] );
echo( "Result: $result\n" );

echo( "\n" );

//
// Set offset.
//
echo( "Set offset:\n" );
echo( '$result = $test[ "Property" ] = "A value";' . "\n" );
$test[ "Property" ] = "A value";
$result = dumpValue( $test[ "Property" ] );
echo( "Result: $result\n" );

echo( "\n" );

//
// Get offset keys.
//
echo( "Get offset keys:\n" );
echo( '$result = $test->arrayKeys();' . "\n" );
$result = $test->arrayKeys();
print_r( $result );

echo( "\n" );

//
// Get offset values.
//
echo( "Get offset values:\n" );
echo( '$result = $test->arrayValues();' . "\n" );
$result = $test->arrayValues();
print_r( $result );

echo( "\n" );

//
// Get properties.
//
echo( "Get properties:\n" );
echo( '$result = $test->toArray();' . "\n" );
$result = $test->toArray();
print_r( $result );

echo( "\n" );

//
// Retrieve offset.
//
echo( "Retrieve offset:\n" );
echo( '$result = $test[ "Property" ];' . "\n" );
$result = dumpValue( $test[ "Property" ] );
echo( "Result: $result\n" );

echo( "\n" );

//
// Delete offset.
//
echo( "Delete offset:\n" );
echo( '$result = $test[ "Property" ] = NULL;' . "\n" );
$test[ "Property" ] = NULL;
$result = dumpValue( $test[ "Property" ] );
echo( "Result: $result\n" );

echo( "\n" );

//
// Get properties.
//
echo( "Get properties:\n" );
echo( '$result = $test->toArray();' . "\n" );
$result = $test->toArray();
print_r( $result );

echo( "\n====================================================================================\n\n" );

//
// Instantiate object.
//
echo( '$test = new test_Container( [ "property" => "value" ] );' . "\n\n" );
$test = new test_Container( [ "property" => "value" ] );

//
// Get properties.
//
echo( "Get properties:\n" );
echo( '$result = $test->toArray();' . "\n" );
$result = $test->toArray();
print_r( $result );

?>

