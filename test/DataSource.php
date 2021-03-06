<?php

/**
 * DataSource object test suite.
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		06/02/2016
 */

//
// Include local definitions.
//
require_once(dirname(__DIR__) . "/includes.local.php");

//
// Include utility functions.
//
require_once( "functions.php" );

//
// Reference class.
//
use Milko\PHPLib\Datasource;

//
// Instantiate object.
//
echo( '$url = "protocol://user:password@host:9090/dir/file?arg1=val1& arg2 =val2&arg3#frag";' . "\n" );
$url = "protocol://user:password@host:9090/dir/file?arg1=val1& arg2 =val2&arg3#frag";
echo( '$test = new Milko\PHPLib\DataSource( $url' . " );\n\n" );
$test = new Milko\PHPLib\Datasource( $url );

//
// Retrieve data source name.
//
echo( "Data source name:\n" );
echo( '$result = (string) $test;' . "\n" );
$result = dumpValue( (string) $test );
echo( "Result: $result\n" );

echo( "\n====================================================================================\n\n" );

//
// Retrieve protocol.
//
echo( "Retrieve protocol:\n" );
echo( '$result = $test[ Milko\PHPLib\DataSource::PROT ];' . "\n" );
$result = dumpValue( $test[ Milko\PHPLib\Datasource::PROT ] );
echo( "Result: $result\n" );
echo( '$result = $test->Protocol();' . "\n" );
$result = dumpValue( $test->Protocol() );
echo( "Result: $result\n" );

echo( "\n" );

//
// Retrieve host.
//
echo( "Retrieve host:\n" );
echo( '$result = $test[ Milko\PHPLib\DataSource::HOST ];' . "\n" );
$result = dumpValue( $test[ Milko\PHPLib\Datasource::HOST ] );
echo( "Result: $result\n" );
echo( '$result = $test->Host();' . "\n" );
$result = dumpValue( $test->Host() );

echo( "\n" );

//
// Retrieve port.
//
echo( "Retrieve port:\n" );
echo( '$result = $test[ Milko\PHPLib\DataSource::PORT ];' . "\n" );
$result = dumpValue( $test[ Milko\PHPLib\Datasource::PORT ] );
echo( "Result: $result\n" );
echo( '$result = $test->Port();' . "\n" );
$result = dumpValue( $test->Port() );

echo( "\n" );

//
// Retrieve user.
//
echo( "Retrieve user:\n" );
echo( '$result = $test[ Milko\PHPLib\DataSource::USER ];' . "\n" );
$result = dumpValue( $test[ Milko\PHPLib\Datasource::USER ] );
echo( "Result: $result\n" );
echo( '$result = $test->User();' . "\n" );
$result = dumpValue( $test->User() );

echo( "\n" );

//
// Retrieve password.
//
echo( "Retrieve password:\n" );
echo( '$result = $test[ Milko\PHPLib\DataSource::PASS ];' . "\n" );
$result = dumpValue( $test[ Milko\PHPLib\Datasource::PASS ] );
echo( "Result: $result\n" );
echo( '$result = $test->Password();' . "\n" );
$result = dumpValue( $test->Password() );

echo( "\n" );

//
// Retrieve path.
//
echo( "Retrieve path:\n" );
echo( '$result = $test[ Milko\PHPLib\DataSource::PATH ];' . "\n" );
$result = dumpValue( $test[ Milko\PHPLib\Datasource::PATH ] );
echo( "Result: $result\n" );
echo( '$result = $test->Path();' . "\n" );
$result = dumpValue( $test->Path() );

echo( "\n" );

//
// Retrieve fragment.
//
echo( "Retrieve fragment:\n" );
echo( '$result = $test[ Milko\PHPLib\DataSource::FRAG ];' . "\n" );
$result = dumpValue( $test[ Milko\PHPLib\Datasource::FRAG ] );
echo( "Result: $result\n" );
echo( '$result = $test->Fragment();' . "\n" );
$result = dumpValue( $test->Fragment() );

echo( "\n" );

//
// Retrieve query.
//
echo( "Retrieve query:\n" );
echo( '$result = $test[ Milko\PHPLib\DataSource::QUERY ];' . "\n" );
$result = $test[ Milko\PHPLib\Datasource::QUERY ];
echo( "Result:\n" );
var_dump( $result );
echo( '$result = $test->Query();' . "\n" );
$result = $test->Query();
var_dump( $result );

echo( "\n====================================================================================\n\n" );

//
// Change fragment.
//
echo( "Change fragment:\n" );
echo( '$result = $test->Fragment( "newFrag" );' . "\n" );
$result = dumpValue( $test->Fragment( "newFrag" ) );
$dsname = (string) $test;
echo( "Result: $result URL: $dsname\n" );

echo( "\n" );

//
// Delete fragment.
//
echo( "Delete fragment:\n" );
echo( '$result = $test->Fragment( FALSE );' . "\n" );
$result = dumpValue( $test->Fragment( FALSE ) );
$dsname = (string) $test;
echo( "Result: $result URL: $dsname\n" );

echo( "\n" );

//
// Delete user.
//
echo( "Delete user:\n" );
echo( '$result = $test->User( FALSE );' . "\n" );
$result = dumpValue( $test->User( FALSE ) );
$dsname = (string) $test;
echo( "Result: $result URL: $dsname\n" );

echo( "\n====================================================================================\n\n" );

//
// Remove protocol.
//
echo( "Remove protocol, should raise an exception:\n" );
echo( '$result = $test->Protocol( FALSE );' . "\n" );
try{ $result = $test->Protocol( FALSE ); echo( "Failed!\n" ); }
catch( Exception $error ){ echo( $error->getMessage() . "\n" ); }
echo( '$result = $test[ Milko\PHPLib\DataSource::PROT ] = NULL;' . "\n" );
try{ $result = $test[ Milko\PHPLib\Datasource::PROT ] = NULL; echo( "Failed!\n" ); }
catch( Exception $error ){ echo( $error->getMessage() . "\n" ); }

echo( "\n" );

//
// Remove host.
//
echo( "Remove host, should raise an exception:\n" );
echo( '$result = $test->Host( FALSE );' . "\n" );
try{ $result = $test->Host( FALSE ); echo( "Failed!\n" ); }
catch( Exception $error ){ echo( $error->getMessage() . "\n" ); }
echo( '$result = $test[ Milko\PHPLib\DataSource::HOST ] = NULL;' . "\n" );
try{ $result = $test[ Milko\PHPLib\Datasource::HOST ] = NULL; echo( "Failed!\n" ); }
catch( Exception $error ){ echo( $error->getMessage() . "\n" ); }

echo( "\n" );

//
// Set wrong port.
//
echo( "Set wrong port, should raise an exception:\n" );
echo( '$result = $test->Port( "should be an integer" );' . "\n" );
try{ $result = $test->Port( "should be an integer" ); echo( "Failed!\n" ); }
catch( Exception $error ){ echo( $error->getMessage() . "\n" ); }
echo( '$result = $test[ Milko\PHPLib\DataSource::PORT ] = "should be an integer";' . "\n" );
try{ $test[ Milko\PHPLib\Datasource::PORT ] = "should be an integer"; echo( "Failed!\n" ); }
catch( Exception $error ){ echo( $error->getMessage() . "\n" ); }
echo( '$result = $test->Port( "9090" );' . "\n" );
try{ $result = $test->Port( "9090" ); echo( "This is supported!\n" ); }
catch( Exception $error ){ echo( $error->getMessage() . "\nFailed!\n" ); }

echo( "\n====================================================================================\n\n" );

//
// Multiple hosts.
//
echo( '$url = "protocol://user:password@host1:9090,host2,host3:9191/dir/file?key=value#fragment";' . "\n" );
$url = "protocol://user:password@host1:9090,host2,host3:9191/dir/file?key=value#fragment";
echo( '$test = new Milko\PHPLib\DataSource( $url' . " );\n" );
$test = new Milko\PHPLib\Datasource( $url );
echo( "$test\n" );
echo( '$result = $test[ Milko\PHPLib\DataSource::PROT ];' . "\n" );
$result = dumpValue( $test[ Milko\PHPLib\Datasource::PROT ] );
echo( "Result: $result\n" );
echo( '$result = $test[ Milko\PHPLib\DataSource::HOST ];' . "\n" );
print_r( $test[ Milko\PHPLib\Datasource::HOST ] );
echo( '$result = $test[ Milko\PHPLib\DataSource::PORT ];' . "\n" );
print_r( $test[ Milko\PHPLib\Datasource::PORT ] );
echo( '$result = (string)$test;' . "\n" );
$result = (string)$test;
echo( "$result\n" );

?>
