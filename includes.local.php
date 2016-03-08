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

/*=======================================================================================
 *	MONGODB																				*
 * Modify this section to customise MongoDB defaults.									*
 *======================================================================================*/

/**
 * <h4>Default connection string.</h4>
 *
 * This definition should contain the default MongoDB connection URI.
 */
define( "kMONGO_OPTS_CLIENT_DEFAULT", 'mongodb://localhost:27017' );

/*=======================================================================================
 *	ARANGODB																			*
 * Modify this section to customise ArangoDB defaults.									*
 *======================================================================================*/

/**
 * <h4>Default connection string.</h4>
 *
 * This definition should contain the default ArangoDB connection URI.
 */
define( "kARANGO_OPTS_CLIENT_DEFAULT", 'tcp://127.0.0.1:8529' );

/**
 * <h4>Default authorisation type.</h4>
 *
 * This definition should contain the default ArangoDB authorisation type.
 */
define( "kARANGO_OPTS_AUTH_DEFAULT", 'Basic' );

/**
 * <h4>Default connection persistence.</h4>
 *
 * This definition should contain the default ArangoDB connection persistence.
 */
define( "kARANGO_OPTS_PERSIST_DEFAULT", 'Keep-Alive' );

/**
 * <h4>Default connection time-out.</h4>
 *
 * This definition should contain the default ArangoDB connection time-out in seconds.
 */
define( "kARANGO_OPTS_TIMEOUT_DEFAULT", 3 );

/**
 * <h4>Default time-out reconnect.</h4>
 *
 * This definition should contain the default ArangoDB connection time-out reconnect.
 */
define( "kARANGO_OPTS_RECONNECT_DEFAULT", TRUE );

/**
 * <h4>Default collection create.</h4>
 *
 * This definition should contain the default ArangoDB collection create option.
 */
define( "kARANGO_OPTS_CREATE_DEFAULT", TRUE );


?>
