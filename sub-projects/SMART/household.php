<?php

/**
 * Load household dataset
 *
 * This script can be used to load a household dataset, it will open the provided excel
 * file, load the <tt>household</tt> collection with the data by adding a single variable,
 * <tt>household-id</tt>, which will contain the unique household identifier.
 *
 * The identifier will be a sequence number determined by sorting the original dataset by
 * <tt>COMMUNE</tt>, <tt>EQUIPE</tt>, <tt>GRAPPE</tt> and <tt>MENAGE</tt>.
 *
 * The file is expected to have variable names in the <tt>3rd</tt> row and data starting
 * from the <tt>4th</tt>.
 *
 * USAGE:
 * <tt>php -f household.php file-path database-name</tt>
 *
 *	@package	Batch
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		13/06/2016
 */

/*
 * Initialisation definition.
 */
define( 'kENGINE', 'ARANGO' );

/*
 * Global includes.
 */
require_once(dirname(dirname(__DIR__)) . "/includes.local.php");


/*=======================================================================================
 *																						*
 *										MAIN											*
 *																						*
 *======================================================================================*/

//
// Check arguments.
//
// Expects the file path.
//
if( $argc < 3 )
	exit( "Usage: php -f household.php <household excel file path> <database name>.\n" );
$file = new SplFileObject( $argv[ 1 ], "r" );
if( (! $file->isFile())
 || (! $file->isWritable()) )
	exit( "Invalid file reference [$file].\n" );
$db_name = $argv[ 2 ];

//
// Create benchmark.
//
$benchmark = new Ubench();
$benchmark->start();

//
// Open file.
//
$path = $file->getRealPath();									// Save file path.
$file_type = PHPExcel_IOFactory::identify( $path );				// Identify Excel verision.
$file_reader = PHPExcel_IOFactory::createReader( $file_type );	// Create reader.
$file_object = $file_reader->load( $path );						// Load data.
$file_array = $file_object->getActiveSheet()->toArray( NULL, TRUE, TRUE, TRUE );

//
// Open database connection.
//
$client = new MongoDB\Client( "mongodb://localhost:27017" );
$database = $client->selectDatabase( $db_name );

//
// Open household collection connection.
//
$collection = $database->selectCollection( 'household' );
$collection->drop();

//
// Open household temporary collection connection.
//
$collection_temp = $database->selectCollection( 'TEMP_household' );
$collection_temp->drop();

//
// Get rid of excel reader and data.
//
unset( $file_object );
unset( $file_reader );

//
// Inform.
//
echo( "\n************************************************************\n" );
echo( "* File name:         " . $file->getRealPath() . "\n" );
echo( "* Number of rows:    " . (count( $file_array ) - 3) . "\n" );
echo( "* Database:          " . $collection->getDatabaseName() . "\n" );
echo( "* Collection:        " . $collection->getCollectionName() . "\n" );
echo( "************************************************************\n" );

//
// Iterate rows.
//
$records = [];
$ddict = $file_array[ 3 ];
for( $row = 4; $row < (count( $file_array ) - 3); $row++ )
{
	//
	// Load record.
	//
	$record = [];
	$data = & $file_array[ $row ];
	foreach( $ddict as $column => $variable )
	{
		$value = $data[ $column ];
		if( strlen( trim( $value ) ) )
		{
			//
			// Clean value.
			//
			if( $column == 'A' )
			{
				$value = DateTime::createFromFormat( 'd-m-y', $value );
				$value = $value->format( 'Ymd' );
			}

			//
			// Set value.
			//
			$record[ $variable ] = $value;

		} // Has value.

	} // Iterating columns.

	//
	// Save record.
	//
	if( count( $record ) )
		$records[] = $record;

} // Iterating data.

//
// Write data.
//
$collection_temp->insertMany( $records );

//
// Inform.
//
$benchmark->end();
echo( "* Written:           " . count( $records ) . "\n" );
echo( "* Duration:          " . $benchmark->getTime() . "\n" );
echo( "* Memory usage:      " . $benchmark->getMemoryUsage() . "\n" );
echo( "* Memory peak:       " . $benchmark->getMemoryPeak() . "\n" );
echo( "************************************************************\n" );

//
// Get rid of records buffer.
//
unset( $records );

//
// Inform.
//
echo( "==> Checking variables:\n" );

//
// Normalise data types.
//
$types = [];
foreach( $ddict as $variable )
{
	//
	// Inform.
	//
	echo( "    [$variable]: " );

	//
	// Init loop storage.
	//
	$types[ $variable ] = 'int';

	//
	// Get distinct values.
	//
	$values = $collection_temp->distinct( $variable );
	foreach( $values as $value )
	{
		//
		// Handle string.
		//
		if( is_string( $value ) )
		{
			$types[ $variable ] = 'string';
			break;															// =>
		}

		//
		// Handle float.
		//
		else
		{
			//
			// Determine decimal different from 0.
			//
			$tmp = explode( '.', (string)$value );
			if( (count( $tmp) > 1)
			 && ($tmp[ 1 ] != '0') )
			{
				$types[ $variable ] = 'double';
				break;														// =>
			}
		}
	}

	//
	// Inform.
	//
	echo( $types[ $variable ] . "\n" );
}

//
// Inform.
//
echo( "==> Updating variables.\n" );

//
// Write final collection.
//
$records = [];
$cursor = $collection_temp->find();
foreach( $cursor as $record )
{
	//
	// Convert to array.
	//
	$record = $record->getArrayCopy();

	//
	// Convert data.
	//
	foreach( $types as $variable => $type )
	{
		if( array_key_exists( $variable, $record ) )
		{
			switch( $type )
			{
				case 'int':
					$record[ $variable ] = (int)$record[ $variable ];
					break;

				case 'double':
					$record[ $variable ] = (double)$record[ $variable ];
					break;

				case 'string':
					$record[ $variable ] = (string)$record[ $variable ];
					break;
			}
		}
	}

	//
	// Save record.
	//
	$records[] = $record;
}

//
// Clear collection.
//
$collection_temp->drop();

//
// Inform.
//
echo( "==> Writing data in temp collection.\n" );

//
// Write data.
//
$collection_temp->insertMany( $records );

//
// Inform.
//
echo( "==> Creating final collection.\n" );

//
// Sort by COMMUNE, EQUIPE, GRAPPE and MENAGE.
//
$sort = [ 'COMMUNE' => 1, 'EQUIPE' => 1, 'GRAPPE' => 1, 'MENAGE' => 1 ];
$cursor = $collection_temp->find( [], ['sort' => $sort ] );

//
// Iterate records.
//
$id = 1;
$records = [];
foreach( $cursor as $record )
{
	//
	// Set record identifier.
	//
	$record[ '_id' ] = "h:$id";

	//
	// Set household identifier.
	//
	$record[ '@household_id' ] = $id;

	//
	// Set line identifier.
	//
	$tmp = [];
	$tmp[] = $record[ 'COMMUNE' ];
	$tmp[] = $record[ 'EQUIPE' ];
	$tmp[] = $record[ 'GRAPPE' ];
	$tmp[] = $record[ 'MENAGE' ];
	$record[ 'uid' ] = implode( ':', $tmp );

	//
	// Save record.
	//
	$records[] = $record;

	//
	// Increment identifier.
	//
	$id++;
}

//
// Write data.
//
$collection->insertMany( $records );

//
// Inform.
//
echo( "==> Dropping temporary collection.\n" );

//
// Clear collection.
//
$collection_temp->drop();


?>
