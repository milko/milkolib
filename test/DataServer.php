<?php

/**
 * Data server object test suite.
 *
 * This test suite will use a ficticious test class, to perform more in depth test use the
 * concrete classes derived from DataServer.
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		18/02/2016
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
use Milko\PHPLib\DataServer;

//
// DataServer test class.
//
class test_DataServer extends Milko\PHPLib\DataServer
{
	//
	// Implement Server virtual interface.
	//
	protected function connectionCreate()	{	return "I am open!";	}
	protected function connectionDestruct()	{	$this->mConnection = "I am closed.";	}

	//
	// Implement DataServer virtual interface.
	//
	protected function databaseList() {
		return array_merge( ["db1", "db2", "db3" ], $this->WorkingDatabases() );	}
	protected function databaseCreate( $theDatabase, $theOptions ) {
		return new test_Database( $this, (string)$theDatabase, $theOptions );	}
	protected function databaseRetrieve( $theDatabase, $theOptions ) {
		return (string) $theDatabase;	}
	protected function databaseDrop( Milko\PHPLib\Database $theDatabase, $theOptions ) {}
}

//
// Database test class.
//
class test_Database extends Milko\PHPLib\Database
{
	//
	// Implement Database virtual interface.
	//
	protected function newDatabase( $theDatabase, $theOptions )	{
		return (string)$theDatabase;	}
	protected function databaseName() {
		return $this->mNativeObject;	}
	protected function collectionList() {
		return array_merge( ["cl1", "cl2", "cl3" ], $this->WorkingCollections() );	}
	protected function collectionCreate( $theCollection, $theOptions ) {
		return new test_Collection( $this, $theCollection, $theOptions );	}
	protected function collectionRetrieve( $theCollection, $theOptions ) {
		return new test_Collection( $this, $theCollection, $theOptions );	}
	protected function collectionEmpty( Milko\PHPLib\Collection $theCollection, $theOptions ) {}
	protected function collectionDrop( Milko\PHPLib\Collection $theCollection, $theOptions ) {}
}

//
// Collection test class.
//
class test_Collection extends Milko\PHPLib\Collection
{
	//
	// Implement Collection virtual interface.
	//
	protected function newCollection( $theCollection, $theOptions ) {
		return (string)$theCollection;	}
	protected function databaseName() {
		return $this->mNativeObject;	}

	//
	// Declare record management interface,
	// will not be tested here.
	//
	protected function insert( $theRecord, $theOptions, $doMany ) {}
	protected function update( $theCriteria, $theFilter, $theOptions, $doMany ) {}
	protected function replace( $theRecord, $theFilter, $theOptions ) {}
	protected function find( $theFilter, $theOptions, $doMany ) {}
	protected function query( $theQuery, $theOptions ) {}
	protected function delete( $theFilter, $theOptions, $doMany ) {}
}

//
// Instantiate object.
//
echo( '$url = "protocol://user:pass@host:9090/db0/col0";' . "\n" );
$url = "protocol://user:pass@host:9090/db0/col0";
echo( '$test = new test_DataServer( $url' . " );\n" );
$test = new test_DataServer( $url );

echo( "\n" );

//
// Display object contents.
//
echo( "Display object contents:\n" );
print_r( $test );

echo( "\n" );

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

echo( "\n" );

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

echo( "\n====================================================================================\n\n" );

//
// List server databases.
//
echo( "List databases:\n" );
echo( '$list = $test->ListDatabases();' . "\n" );
$list = $test->ListDatabases();
print_r( $list );

echo( "\n" );

//
// List working databases.
//
echo( "List databases:\n" );
echo( '$list = $test->WorkingDatabases();' . "\n" );
$list = $test->WorkingDatabases();
print_r( $list );

echo( "\n" );

//
// Retrieve database.
//
echo( "Retrieve database:\n" );
echo( '$db = $test->RetrieveDatabase( "db0" );' . "\n" );
$db = $test->RetrieveDatabase( "db0" );
print_r( $db );

echo( "\n" );

//
// Create database.
//
echo( "Create database:\n" );
echo( '$db = $test->RetrieveDatabase( "NewDB" );' . "\n" );
$db = $test->RetrieveDatabase( "NewDB" );
echo( '$list = $test->WorkingDatabases();' . "\n" );
$list = $test->WorkingDatabases();
print_r( $list );

echo( "\n" );

//
// Forget database.
//
echo( "Forget database:\n" );
echo( '$db = $test->ForgetDatabase( "NewDB" );' . "\n" );
$db = $test->ForgetDatabase( "NewDB" );
echo( '$list = $test->WorkingDatabases();' . "\n" );
$list = $test->WorkingDatabases();
print_r( $list );

echo( "\n" );

//
// Drop database.
//
echo( "Drop database:\n" );
echo( '$db = $test->DropDatabase( "db0" );' . "\n" );
$db = $test->DropDatabase( "db0" );
echo( '$list = $test->WorkingDatabases();' . "\n" );
$list = $test->WorkingDatabases();
print_r( $list );


?>
