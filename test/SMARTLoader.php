<?php

/**
 * SMART loader object test suite.
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		17/06/2016
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
// Include test class.
//
require_once( kPATH_LIBRARY_ROOT . "/src/PHPLib/SMARTLoader.php" );

//
// Instantiate object.
//
echo( '$test = new SMARTLoader( "SMART" );' . "\n" );
$test = new SMARTLoader( "SMART" );

echo( "\n====================================================================================\n\n" );

//
// Set household dataset.
//
echo( '$result = $test->HouseholdDataset( __DIR__ . "/SMART/HOUSEHOLD.xlsx" );' . "\n" );
$result = $test->HouseholdDataset( __DIR__ . "/SMART/HOUSEHOLD.xlsx" );
var_dump( $result );

echo( "\n" );

//
// Set household header row.
//
echo( '$result = $test->HouseholdDatasetHeaderRow( 3 );' . "\n" );
$result = $test->HouseholdDatasetHeaderRow( 3 );
var_dump( $result );

echo( "\n" );

//
// Set household data row.
//
echo( '$result = $test->HouseholdDatasetDataRow( 4 );' . "\n" );
$result = $test->HouseholdDatasetDataRow( 4 );
var_dump( $result );

echo( "\n" );

//
// Set household survey date.
//
echo( '$result = $test->HouseholdDatasetDateOffset( "DATE" );' . "\n" );
$result = $test->HouseholdDatasetDateOffset( "DATE" );
var_dump( $result );

echo( "\n" );

//
// Set household survey location.
//
echo( '$result = $test->HouseholdDatasetLocationOffset( "COMMUNE" );' . "\n" );
$result = $test->HouseholdDatasetLocationOffset( "COMMUNE" );
var_dump( $result );

echo( "\n" );

//
// Set household survey team.
//
echo( '$result = $test->HouseholdDatasetTeamOffset( "EQUIPE" );' . "\n" );
$result = $test->HouseholdDatasetTeamOffset( "EQUIPE" );
var_dump( $result );

echo( "\n" );

//
// Set household survey cluster.
//
echo( '$result = $test->HouseholdDatasetClusterOffset( "GRAPPE" );' . "\n" );
$result = $test->HouseholdDatasetClusterOffset( "GRAPPE" );
var_dump( $result );

echo( "\n" );

//
// Set household survey identifier.
//
echo( '$result = $test->HouseholdDatasetIdentifierOffset( "MENAGE" );' . "\n" );
$result = $test->HouseholdDatasetIdentifierOffset( "MENAGE" );
var_dump( $result );

echo( "\n====================================================================================\n\n" );

//
// Set mother dataset.
//
echo( '$result = $test->MotherDataset( __DIR__ . "/SMART/MOTHER.xlsx" );' . "\n" );
$result = $test->MotherDataset( __DIR__ . "/SMART/MOTHER.xlsx" );
var_dump( $result );

echo( "\n" );

//
// Set mother header row.
//
echo( '$result = $test->MotherDatasetHeaderRow( 3 );' . "\n" );
$result = $test->MotherDatasetHeaderRow( 3 );
var_dump( $result );

echo( "\n" );

//
// Set mother data row.
//
echo( '$result = $test->MotherDatasetDataRow( 4 );' . "\n" );
$result = $test->MotherDatasetDataRow( 4 );
var_dump( $result );

echo( "\n" );

//
// Set mother survey date.
//
echo( '$result = $test->MotherDatasetDateOffset( "DATE" );' . "\n" );
$result = $test->MotherDatasetDateOffset( "DATE" );
var_dump( $result );

echo( "\n" );

//
// Set mother survey location.
//
echo( '$result = $test->MotherDatasetLocationOffset( "COMMUNE" );' . "\n" );
$result = $test->MotherDatasetLocationOffset( "COMMUNE" );
var_dump( $result );

echo( "\n" );

//
// Set mother survey team.
//
echo( '$result = $test->MotherDatasetTeamOffset( "EQUIPE" );' . "\n" );
$result = $test->MotherDatasetTeamOffset( "EQUIPE" );
var_dump( $result );

echo( "\n" );

//
// Set mother survey cluster.
//
echo( '$result = $test->MotherDatasetClusterOffset( "GRAPPE" );' . "\n" );
$result = $test->MotherDatasetClusterOffset( "GRAPPE" );
var_dump( $result );

echo( "\n" );

//
// Set mother survey identifier.
//
echo( '$result = $test->MotherDatasetIdentifierOffset( "MERE" );' . "\n" );
$result = $test->MotherDatasetIdentifierOffset( "MERE" );
var_dump( $result );

echo( "\n" );

//
// Set mother survey household.
//
echo( '$result = $test->MotherDatasetHouseholdOffset( "MENAGE" );' . "\n" );
$result = $test->MotherDatasetHouseholdOffset( "MENAGE" );
var_dump( $result );

echo( "\n====================================================================================\n\n" );

//
// Set child dataset.
//
echo( '$result = $test->ChildDataset( __DIR__ . "/SMART/CHILD.xlsx" );' . "\n" );
$result = $test->ChildDataset( __DIR__ . "/SMART/CHILD.xlsx" );
var_dump( $result );

echo( "\n" );

//
// Set child header row.
//
echo( '$result = $test->ChildDatasetHeaderRow( 2 );' . "\n" );
$result = $test->ChildDatasetHeaderRow( 2 );
var_dump( $result );

echo( "\n" );

//
// Set child data row.
//
echo( '$result = $test->ChildDatasetDataRow( 3 );' . "\n" );
$result = $test->ChildDatasetDataRow( 3 );
var_dump( $result );

echo( "\n" );

//
// Set child survey date.
//
echo( '$result = $test->ChildDatasetDateOffset( "SURVDATE" );' . "\n" );
$result = $test->ChildDatasetDateOffset( "SURVDATE" );
var_dump( $result );

echo( "\n" );

//
// Set child survey location.
//
echo( '$result = $test->ChildDatasetLocationOffset( "COMMUNE" );' . "\n" );
$result = $test->ChildDatasetLocationOffset( "COMMUNE" );
var_dump( $result );

echo( "\n" );

//
// Set child survey team.
//
echo( '$result = $test->ChildDatasetTeamOffset( "TEAM" );' . "\n" );
$result = $test->ChildDatasetTeamOffset( "TEAM" );
var_dump( $result );

echo( "\n" );

//
// Set child survey cluster.
//
echo( '$result = $test->ChildDatasetClusterOffset( "CLUSTER" );' . "\n" );
$result = $test->ChildDatasetClusterOffset( "CLUSTER" );
var_dump( $result );

echo( "\n" );

//
// Set child survey identifier.
//
echo( '$result = $test->ChildDatasetIdentifierOffset( "ID" );' . "\n" );
$result = $test->ChildDatasetIdentifierOffset( "ID" );
var_dump( $result );

echo( "\n" );

//
// Set child survey household.
//
echo( '$result = $test->ChildDatasetHouseholdOffset( "HH" );' . "\n" );
$result = $test->ChildDatasetHouseholdOffset( "HH" );
var_dump( $result );

echo( "\n" );

//
// Set child survey mother.
//
echo( '$result = $test->ChildDatasetMotherOffset( "MOTHER" );' . "\n" );
$result = $test->ChildDatasetMotherOffset( "MOTHER" );
var_dump( $result );

echo( "\n====================================================================================\n\n" );

//
// Load child dataset.
//
echo( '$result = $test->LoadChildDataset();' . "\n" );
$result = $test->LoadChildDataset();
var_dump( $result );

echo( "\n" );

//
// Get child header.
//
echo( '$result = $test->LoadChildDatasetHeader();' . "\n" );
$result = $test->LoadChildDatasetHeader();
var_dump( $result );

echo( "\n" );

//
// Get child fields.
//
echo( '$test->LoadChildDatasetFields();' . "\n" );
$test->LoadChildDatasetFields();

echo( "\n" );

//
// Get child data.
//
echo( '$test->LoadChildDatasetData();' . "\n" );
$test->LoadChildDatasetData();

echo( "\n" );

//
// Get child duplicates.
//
echo( '$result = $test->CheckChildDatasetDuplicates();' . "\n" );
$result = $test->CheckChildDatasetDuplicates();
var_dump( $result );

//
// Show status.
//
echo( "\nSTATUS:\n" );
if( $test->ChildDatasetStatus() & SMARTLoader::kSTATUS_DUPLICATE_COLUMNS )
{
	echo( "==> Has duplicate header columns:\n" );
	print_r( $test->ChildDatasetDuplicateHeaderCoumns() );
}
if( $test->ChildDatasetStatus() & SMARTLoader::kSTATUS_DUPLICATE_ENTRIES )
{
	echo( "==> Has duplicate entry records:\n" );
	print_r( $test->ChildDatasetDuplicateEntries() );
}

echo( "\n====================================================================================\n\n" );

//
// Load mother dataset.
//
echo( '$result = $test->LoadMotherDataset();' . "\n" );
$result = $test->LoadMotherDataset();
var_dump( $result );

echo( "\n" );

//
// Get mother header.
//
echo( '$result = $test->LoadMotherDatasetHeader();' . "\n" );
$result = $test->LoadMotherDatasetHeader();
var_dump( $result );

echo( "\n" );

//
// Get mother fields.
//
echo( '$test->LoadMotherDatasetFields();' . "\n" );
$test->LoadMotherDatasetFields();

echo( "\n" );

//
// Get mother data.
//
echo( '$test->LoadMotherDatasetData();' . "\n" );
$test->LoadMotherDatasetData();

echo( "\n" );

//
// Get mother duplicates.
//
echo( '$result = $test->CheckMotherDatasetDuplicates();' . "\n" );
$result = $test->CheckMotherDatasetDuplicates();
var_dump( $result );

//
// Show status.
//
echo( "\nSTATUS:\n" );
if( $test->MotherDatasetStatus() & SMARTLoader::kSTATUS_DUPLICATE_COLUMNS )
{
	echo( "==> Has duplicate header columns:\n" );
	print_r( $test->MotherDatasetDuplicateHeaderCoumns() );
}
if( $test->MotherDatasetStatus() & SMARTLoader::kSTATUS_DUPLICATE_ENTRIES )
{
	echo( "==> Has duplicate entry records:\n" );
	print_r( $test->MotherDatasetDuplicateEntries() );
}

echo( "\n====================================================================================\n\n" );

//
// Load household dataset.
//
echo( '$result = $test->LoadHouseholdDataset();' . "\n" );
$result = $test->LoadHouseholdDataset();
var_dump( $result );

echo( "\n" );

//
// Get household header.
//
echo( '$result = $test->LoadHouseholdDatasetHeader();' . "\n" );
$result = $test->LoadHouseholdDatasetHeader();
var_dump( $result );

echo( "\n" );

//
// Get household fields.
//
echo( '$test->LoadHouseholdDatasetFields();' . "\n" );
$test->LoadHouseholdDatasetFields();
var_dump( $result );

echo( "\n" );

//
// Get household data.
//
echo( '$test->LoadHouseholdDatasetData();' . "\n" );
$test->LoadHouseholdDatasetData();

echo( "\n" );

//
// Get household duplicates.
//
echo( '$result = $test->CheckHouseholdDatasetDuplicates();' . "\n" );
$result = $test->CheckHouseholdDatasetDuplicates();
var_dump( $result );

//
// Show status.
//
echo( "\nSTATUS:\n" );
if( $test->HouseholdDatasetStatus() & SMARTLoader::kSTATUS_DUPLICATE_COLUMNS )
{
	echo( "==> Has duplicate header columns:\n" );
	print_r( $test->HouseholdDatasetDuplicateHeaderCoumns() );
}
if( $test->HouseholdDatasetStatus() & SMARTLoader::kSTATUS_DUPLICATE_ENTRIES )
{
	echo( "==> Has duplicate entry records:\n" );
	print_r( $test->HouseholdDatasetDuplicateEntries() );
}


?>

