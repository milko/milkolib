<?php

/**
 * Database server object test suite.
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		16/02/2016
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
use Milko\PHPLib\DatabaseServer;

//
// Test classes.
//
class test_Database extends Milko\PHPLib\Database
{
	public function __construct( $theName )
	{
		$this->mNativeObject = (string) $theName;
	}

	public function __toString()
	{
		return (string) $this->mNativeObject;										// ==>
	}

	public function Drop()
	{
		$this->mNativeObject = NULL;
	}
}

class test_DatabaseServer extends Milko\PHPLib\DatabaseServer
{
	//
	// Test databases list.
	//
	private $list = [ 'Database 1', 'Database 2', 'Database 3' ];

	//
	// Implement connection creation.
	//
	function connectionCreate()
	{
		return "I am open!";
	}

	//
	// Implement connection destruction.
	//
	function connectionDestruct()
	{
		$this->mConnection = "I am closed.";
	}

	//
	// Get databases list.
	//
	protected function databaseList()
	{
		return $this->list;															// ==>
	}

	//
	// Create database object.
	//
	protected function databaseCreate( $theDatabase )
	{
		$database = new test_Database( $theDatabase );
		if( ! in_array( $theDatabase, $this->list ) )
			$database = $this->list[] = $theDatabase;

		return $database;															// ==>
	}

	//
	// Get database object.
	//
	protected function databaseGet( $theDatabase )
	{
		if( in_array( $theDatabase, $this->list ) )
			return new test_Database( $theDatabase );								// ==>

		return NULL;																// ==>
	}

	//
	// Drop provided database.
	//
	protected function databaseDrop( Milko\PHPLib\Database $theDatabase )
	{
		$name = (string) $theDatabase;
		$theDatabase->Drop();
		$position = array_search( $name, $this->list );
		if( $position !== FALSE )
			unset( $this->list[ $position ] );
	}
}

//
// Instantiate database server.
//
echo( '$url = "protocol://user:pass@host:9090/databaseX";' . "\n" );
$url = "protocol://user:pass@host:9090/databaseX";
echo( '$server = new test_DatabaseServer( $url' . " );\n\n" );
$test = new test_DatabaseServer( $url );

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
$test->Protocol( "Someotherprotocol" );;
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

echo( "\n====================================================================================\n\n" );

//
// List databases.
//
echo( "List databases:\n" );
echo( '$list = $test->ListDatabases();' . "\n" );
$list = $test->ListDatabases();
var_dump( $list );

echo( "\n" );

//
// Get database.
//
echo( "Get database:\n" );
echo( '$db = $test->GetDatabase( "Database 2" );' . "\n" );
$db = $test->GetDatabase( "Database 2" );
var_dump( $db );

echo( "\n" );

//
// Get default database.
//
echo( "Get default database:\n" );
echo( '$db = $test->GetDatabase();' . "\n" );
$db = $test->GetDatabase();
var_dump( $db );

echo( "\n" );

//
// Create database.
//
echo( "Create database:\n" );
echo( '$db = $test->GetDatabase( "pippo" );' . "\n" );
$db = $test->GetDatabase( "pippo" );
var_dump( $db );

echo( "\n" );

//
// List databases.
//
echo( "List databases:\n" );
echo( '$list = $test->ListDatabases();' . "\n" );
$list = $test->ListDatabases();
var_dump( $list );

echo( "\n" );

//
// Drop database.
//
echo( "Drop database:\n" );
echo( '$test->DropDatabase( "pippo" );' . "\n" );
$test->DropDatabase( "pippo" );
echo( '$list = $test->ListDatabases();' . "\n" );
$list = $test->ListDatabases();
var_dump( $list );


?>
