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
$collection = $database->selectCollection( 'household' );
$collection->drop();

//
// Get rid of excel reader and data.
//
$file_object = $file_reader = NULL;

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
			else
				$value = (int)$value;

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
$collection->insertMany( $records );

//
// Inform.
//
$benchmark->end();
echo( "* Written:           " . count( $records ) . "\n" );
echo( "* Duration:          " . $benchmark->getTime() . "\n" );
echo( "* Memory usage:      " . $benchmark->getMemoryUsage() . "\n" );
echo( "* Memory peak:       " . $benchmark->getMemoryPeak() . "\n" );
echo( "************************************************************\n" );

?>
