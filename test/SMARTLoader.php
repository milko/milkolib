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
// Load household dataset.
//
echo( '$result = $test->LoadHouseholdDataset();' . "\n" );
$result = $test->LoadHouseholdDataset();
var_dump( $result );
if( $test->HouseholdStatus() == SMARTLoader::kOFFSET_STATUS_ERROR )
	print_r( $test->HouseholdDuplicates() );

?>

