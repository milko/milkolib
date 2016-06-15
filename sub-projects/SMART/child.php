<?php

/**
 * Load child dataset
 *
 * This script can be used to load a child dataset, it will open the provided excel
 * file, load the <tt>child</tt> collection with the data by adding a single variable,
 * <tt>@child_id</tt>, which will contain the unique child identifier.
 *
 * The identifier will be a sequence number determined by sorting the original dataset by
 * <tt>COMMUNE</tt>, <tt>EQUIPE</tt>, <tt>GRAPPE</tt>, <tt>MENAGE</tt>, <tt>MERE</tt> and
 * <tt>ENFANT</tt>.
 *
 * The file is expected to have variable names in the <tt>3rd</tt> row and data starting
 * from the <tt>4th</tt>.
 *
 * Arguments:
 *
 * <ul>
 * 	<li><tt>$1</tt>: Household Excel file path.
 * 	<li><tt>$2</tt>: Database name (collection will be <tt>household</tt>).
 * 	<li><tt>$3</tt>: Date variable name.
 * 	<li><tt>$4</tt>: Administrative unit variable name.
 * 	<li><tt>$5</tt>: Team variable name.
 * 	<li><tt>$6</tt>: Cluster variable name.
 * 	<li><tt>$7</tt>: Household identifier variable name.
 * 	<li><tt>$8</tt>: mother identifier variable name.
 * 	<li><tt>$9</tt>: Unit identifier variable name.
 * </ul>
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
if( $argc < 10 )
	exit
	(
		"Usage: php -f household.php " .
		"<file path> " .
		"<database name> " .
		"<date variable name> " .
		"<administrative unit variable name> " .
		"<team variable name> " .
		"<cluster variable name>" .
		"<household identifier variable name>" .
		"<mother identifier variable name>" .
		"<unit identifier variable name>" .
		"\n"
	);

//
// Load and check file.
//
$file = new SplFileObject( $argv[ 1 ], "r" );
if( (! $file->isFile())
	|| (! $file->isWritable()) )
	exit( "Invalid file reference [$file].\n" );

//
// Load other arguments.
//
$db_name = $argv[ 2 ];
$date_name = $argv[ 3 ];
$admin_name = $argv[ 4 ];
$team_name = $argv[ 5 ];
$cluster_name = $argv[ 6 ];
$household_name = $argv[ 7 ];
$mother_name = $argv[ 8 ];
$unit_name = $argv[ 9 ];

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
$collection = $database->selectCollection( 'child' );
$collection->drop();

//
// Open household temporary collection connection.
//
$collection_temp = $database->selectCollection( 'TEMP_child' );
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
echo( "* File name:                     " . $file->getRealPath() . "\n" );
echo( "* Number of rows:                " . (count( $file_array ) - 3) . "\n" );
echo( "* Database:                      " . $collection->getDatabaseName() . "\n" );
echo( "* Collection:                    " . $collection->getCollectionName() . "\n" );
echo( "* Date variable:                 $date_name\n" );
echo( "* Administrative unit variable:  $admin_name\n" );
echo( "* Team unit variable:            $team_name\n" );
echo( "* Cluster variable:              $cluster_name\n" );
echo( "* Household identifier variable: $household_name\n" );
echo( "* Mother identifier variable:    $mother_name\n" );
echo( "* Unit identifier variable:      $unit_name\n" );
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
			if( $variable == $date_name )
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
// Sort.
//
$sort = [ $admin_name => 1, $team_name => 1, $cluster_name => 1,
		  $household_name => 1, $mother_name => 1, $unit_name => 1 ];
$cursor = $collection_temp->find( [], ['sort' => $sort ] );

//
// Iterate records.
//
$id = 1;
$records = [];
foreach( $cursor as $record )
{
	//
	// Set household identifier.
	//
	$record[ '@child_id' ] = $id;

	//
	// Set line identifier.
	//
	$tmp = [];
	$tmp[] = $record[ $admin_name ];
	$tmp[] = $record[ $team_name ];
	$tmp[] = $record[ $cluster_name ];
	$tmp[] = $record[ $household_name ];
	$tmp[] = $record[ $mother_name ];
	$tmp[] = $record[ $unit_name ];
	$record[ '_id' ] = implode( ':', $tmp );

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
//$collection_temp->drop();


?>
