<?php

/**
 * Server object test suite.
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
use Milko\PHPLib\Server;

//
// Test class.
//
class test_Server extends Milko\PHPLib\Server
{
	//
	// Implement virtual interface.
	//
	function connectionCreate( $theOptions = NULL ) {
		return "I am open!";	}
	function connectionDestruct( $theOptions = NULL ) {
		$this->mConnection = "I am closed."; }
}

//
// Instantiate object.
//
echo( '$url = "protocol://user:pass@host:9090/path";' . "\n" );
$url = "protocol://user:pass@host:9090/path";
echo( '$test = new test_Server( $url' . " );\n\n" );
$test = new test_Server( $url );

//
// Retrieve connection.
//
echo( "Retrieve connection:\n" );
echo( '$result = $test->isConnected();' . "\n" );
$result = dumpValue( $test->isConnected() );
echo( "Result: $result\n" );
echo( '$result = $test->Connection();' . "\n" );
$result = dumpValue( $test->Connection() );
echo( "Result: $result\n" );

echo( "\n" );

//
// Connect.
//
echo( "Connect:\n" );
echo( '$result = $test->Connect();' . "\n" );
$result = dumpValue( $test->Connect() );
echo( "Result: $result\n" );
echo( '$result = $test->isConnected();' . "\n" );
$result = dumpValue( $test->isConnected() );
echo( "Result: $result\n" );

echo( "\n" );

//
// Disconnect.
//
echo( "Disconnect:\n" );
echo( '$result = $test->Disconnect();' . "\n" );
$result = dumpValue( $test->Disconnect() );
echo( "Result: $result\n" );
echo( '$result = $test->isConnected();' . "\n" );
$result = dumpValue( $test->isConnected() );
echo( "Result: $result\n" );
echo( '$result = $test->Disconnect();' . "\n" );
$result = dumpValue( $test->Disconnect() );
echo( "Result: $result\n" );

echo( "\n====================================================================================\n\n" );

//
// Change protocol.
//
echo( "Change protocol:\n" );
echo( '$test->Protocol( "Someotherprotocol" );' . "\n" );
$test->Protocol( "Someotherprotocol" );
echo( "==> $test\n" );

echo( "\n" );

//
// Reconnect.
//
echo( "Reconnect:\n" );
echo( '$result = $test->Connect();' . "\n" );
$result = dumpValue( $test->Connect() );
echo( "Result: $result\n" );

echo( "\n" );

//
// Change protocol.
//
echo( "Change protocol, should raise an exception:\n" );
echo( '$test->Protocol( "html" );' . "\n" );
try{ $test->Protocol( "html" ); echo( "Failed!\n" ); }
catch( Exception $error ){ echo( $error->getMessage() . "\n" ); }
echo( '$test[ Milko\PHPLib\Server::PROT ] = "HTML";' . "\n" );
try{ $test[ Milko\PHPLib\Server::PROT ] = "HTML"; echo( "Failed!\n" ); }
catch( Exception $error ){ echo( $error->getMessage() . "\n" ); }


?>

