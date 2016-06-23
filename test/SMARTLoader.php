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
echo( '$test = new SMARTLoader();' . "\n" );
$test = new SMARTLoader();
print_r( $test );

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
exit;

echo( "\n====================================================================================\n\n" );

//
// Set household dataset.
//
echo( '$result = $test->SetHouseholdDataset( __DIR__ . "/SMART/HOUSEHOLD.xlsx", 3, 4, "DATE", "COMMUNE", "EQUIPE", "GRAPPE", "MENAGE" );' . "\n" );
$result = $test->SetHouseholdDataset( __DIR__ . "/SMART/HOUSEHOLD.xlsx", 3, 4, "DATE", "COMMUNE", "EQUIPE", "GRAPPE", "MENAGE" );

//
// Set mother dataset.
//
echo( '$result = $test->SetMotherDataset( __DIR__ . "/SMART/MOTHER.xlsx", 3, 4, "DATE", "COMMUNE", "EQUIPE", "GRAPPE", "MERE", "MENAGE" );' . "\n" );
$result = $test->SetMotherDataset( __DIR__ . "/SMART/MOTHER.xlsx", 3, 4, "DATE", "COMMUNE", "EQUIPE", "GRAPPE", "MERE", "MENAGE" );

//
// Set child dataset.
//
echo( '$result = $test->SetChildDataset( __DIR__ . "/SMART/CHILD.xlsx", 2, 3, "SURVDATE", "COMMUNE", "TEAM", "CLUSTER", "ID", "HH", "MOTHER" );' . "\n" );
$result = $test->SetChildDataset( __DIR__ . "/SMART/CHILD.xlsx", 2, 3, "SURVDATE", "COMMUNE", "TEAM", "CLUSTER", "ID", "HH", "MOTHER" );

echo( "\n====================================================================================\n\n" );

//
// Load household dataset.
//
echo( '$result = $test->LoadHouseholdDataset();' . "\n" );
$result = $test->LoadHouseholdDataset();
var_dump( $result );
switch( $test->HouseholdStatus() )
{
	case SMARTLoader::kDDICT_STATUS_IDLE:
		echo( "==> Dataset is empty\n" );
		break;

	case SMARTLoader::kDDICT_STATUS_LOADED:
		echo( "==> Dataset loaded.\n" );
		break;

	default:
		if( count( $tmp = $test->HouseholdDuplicates() ) )
		{
			echo( "==> Dataset has duplicates:\n" );
			print_r( $tmp );
		}
		break;
}

echo( "\n====================================================================================\n\n" );

//
// Load mother dataset.
//
echo( '$result = $test->LoadMotherDataset();' . "\n" );
$result = $test->LoadMotherDataset();
var_dump( $result );
switch( $test->MotherStatus() )
{
	case SMARTLoader::kDDICT_STATUS_IDLE:
		echo( "==> Dataset is empty\n" );
		break;

	case SMARTLoader::kDDICT_STATUS_LOADED:
		echo( "==> Dataset loaded.\n" );
		break;

	default:
		if( count( $tmp = $test->MotherDuplicates() ) )
		{
			echo( "==> Dataset has duplicates:\n" );
			print_r( $tmp );
		}
		if( count( $tmp = $test->MotherRelated() ) )
		{
			echo( "==> Dataset has invalid references:\n" );
			print_r( $tmp );
		}
		break;
}

echo( "\n====================================================================================\n\n" );

//
// Load child dataset.
//
echo( '$result = $test->LoadChildDataset();' . "\n" );
$result = $test->LoadChildDataset();
var_dump( $result );
switch( $test->ChildStatus() )
{
	case SMARTLoader::kDDICT_STATUS_IDLE:
		echo( "==> Dataset is empty\n" );
		break;

	case SMARTLoader::kDDICT_STATUS_LOADED:
		echo( "==> Dataset loaded.\n" );
		break;

	default:
		if( count( $tmp = $test->ChildDuplicates() ) )
		{
			echo( "==> Dataset has duplicates:\n" );
			print_r( $tmp );
		}
		if( count( $tmp = $test->ChildRelated() ) )
		{
			echo( "==> Dataset has invalid references:\n" );
			print_r( $tmp );
		}
		break;
}

echo( "\n====================================================================================\n\n" );

//
// Merge datasets.
//
echo( '$test->CreateSurveyCollection();' . "\n" );
$test->CreateSurveyCollection();


?>

