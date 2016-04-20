<?php

/*=======================================================================================
 *																						*
 *									includes.local.php									*
 *																						*
 *======================================================================================*/

/**
 *	<h4>Local include file.</h4><p />
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
 * <h4>Library root path.</h4><p />
 *
 * This defines the library root directory.
 */
define( 'kPATH_LIBRARY_ROOT', __DIR__ . DIRECTORY_SEPARATOR );

/*=======================================================================================
 *	MEMCACHED DEFINITIONS																*
 * Modify these definition if necessary.												*
 *======================================================================================*/

/**
 * <h4>Memcached persistent ID.</h4><p />
 *
 * This defines the the memcached persistent ID.
 */
define( 'kSESSION_CACHE_ID', 'wrapper' );

/**
 * <h4>Memcached default host.</h4><p />
 *
 * This defines the the memcached default host.
 */
define( 'kSESSION_CACHE_HOST', 'localhost' );

/**
 * <h4>Memcached default port.</h4><p />
 *
 * This defines the the memcached default port.
 */
define( 'kSESSION_CACHE_PORT', 11211 );

/*=======================================================================================
 *	AUTOLOAD																			*
 * Modify this entry to point to the autoload script.									*
 *======================================================================================*/

/**
 * <h4>Autoload script include.</h4><p />
 *
 * This will include the composer autoload script.
 */
require_once( __DIR__ . DIRECTORY_SEPARATOR . 'vendor/autoload.php' );


?>
