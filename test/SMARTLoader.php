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
	case SMARTLoader::kOFFSET_STATUS_IDLE:
		echo( "==> Dataset is empty\n" );
		break;

	case SMARTLoader::kOFFSET_STATUS_DUPLICATES:
		echo( "==> Dataset has duplicates:\n" );
		print_r( $test->HouseholdDuplicates() );
		exit;

	case SMARTLoader::kOFFSET_STATUS_LOADED:
		echo( "==> Dataset loaded.\n" );
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
	case SMARTLoader::kOFFSET_STATUS_IDLE:
		echo( "==> Dataset is empty\n" );
		break;

	case SMARTLoader::kOFFSET_STATUS_DUPLICATES:
		echo( "==> Dataset has duplicates:\n" );
		print_r( $test->MotherDuplicates() );
		exit;

	case SMARTLoader::kOFFSET_STATUS_LOADED:
		echo( "==> Dataset loaded.\n" );
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
	case SMARTLoader::kOFFSET_STATUS_IDLE:
		echo( "==> Dataset is empty\n" );
		break;

	case SMARTLoader::kOFFSET_STATUS_DUPLICATES:
		echo( "==> Dataset has duplicates:\n" );
		print_r( $test->ChildDuplicates() );
		exit;

	case SMARTLoader::kOFFSET_STATUS_LOADED:
		echo( "==> Dataset loaded.\n" );
		break;
}


?>

