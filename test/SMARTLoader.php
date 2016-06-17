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
echo( '$test = new SMARTLoader();' . "\n\n" );
$test = new SMARTLoader();

//
// Set household dataset.
//
echo( '$result = $test->SetHouseholdDataset( __DIR__ . "/SMART/HOUSEHOLD.xlsx", 3, 4, "DATE", "COMMUNE", "EQUIPE", "GRAPPE", "MENAGE" );' . "\n\n" );
$result = $test->SetHouseholdDataset( __DIR__ . "/SMART/HOUSEHOLD.xlsx", 3, 4, "DATE", "COMMUNE", "EQUIPE", "GRAPPE", "MENAGE" );
print_r( $result );

//
// Load household dataset.
//
echo( '$result = $test->LoadHouseholdDataset();' . "\n" );
$result = $test->LoadHouseholdDataset();
print_r( $result );
echo( "\n" );

?>

