<?php

/*=======================================================================================
 *																						*
 *									includes.local.php									*
 *																						*
 *======================================================================================*/

/**
 *	<h4>Local include file.</h4>
 *
 * This file contains the local definitions for this library, here users should set the
 * locations of the library files and other data dependant on the local environment.
 *
 *	@package	Core
 *	@subpackage	Definitions
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		06/02/2016
 */

/*=======================================================================================
 *	LIBRARY ROOT																		*
 * Modify this definition to point to the "src" directory.								*
 *======================================================================================*/

/**
 * <h4>Library root path.</h4>
 *
 * This defines the library root directory.
 */
define( 'kPATH_LIBRARY_ROOT', __DIR__ . DIRECTORY_SEPARATOR . 'src' );

/*=======================================================================================
 *	AUTOLOAD																			*
 * Modify this entry to point to the autoload script.									*
 *======================================================================================*/

/**
 * <h4>Autoload script include.</h4>
 *
 * This will include the composer autoload script.
 */
require_once( __DIR__ . DIRECTORY_SEPARATOR . 'vendor/autoload.php' );


?>
