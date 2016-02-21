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

/**
 * <h4>Connection creation options.</h4>
 *
 * This definition should contain the default options for creating a MongoDB client, it
 * refers to the driver options, the URI options should be provided in the connection
 * string.
 */
define( "kMONGO_OPTS_CLIENT_CREATE", [] );

/**
 * <h4>Connection destruction options.</h4>
 *
 * This definition should contain the default options for destructing a MongoDB client, by
 * default there is no destructor.
 */
define( "kMONGO_OPTS_CLIENT_DESTRUCT", NULL );

/**
 * <h4>Databases list options.</h4>
 *
 * This definition should contain the default options for returning a list of databases from
 * a MongoDB client.
 */
define( "kMONGO_OPTS_CLIENT_DBLIST", [] );

/**
 * <h4>Databases creation options.</h4>
 *
 * This definition should contain the default options for creating a database for a MongoDB
 * client.
 */
define( "kMONGO_OPTS_CLIENT_DBCREATE", [] );

/**
 * <h4>Databases retrieval options.</h4>
 *
 * This definition should contain the default options for retrieving a database for a
 * MongoDB client.
 */
define( "kMONGO_OPTS_CLIENT_DBRETRIEVE", [] );

/**
 * <h4>Databases creation options.</h4>
 *
 * This definition should contain the default options for creating a database.
 */
define( "kMONGO_OPTS_DB_CREATE", [] );

/**
 * <h4>Databases drop options.</h4>
 *
 * This definition should contain the default options for dropping a database.
 */
define( "kMONGO_OPTS_DB_DROP", [] );

/**
 * <h4>Collections list options.</h4>
 *
 * This definition should contain the default options for returning a list of collections
 * from a MongoDB database.
 */
define( "kMONGO_OPTS_DB_CLLIST", [] );

/**
 * <h4>Collections creation options.</h4>
 *
 * This definition should contain the default options for creating a collection from a
 * MongoDB database.
 */
define( "kMONGO_OPTS_DB_CLCREATE", [] );

/**
 * <h4>Collections retrieval options.</h4>
 *
 * This definition should contain the default options for retrieving a collection from a
 * MongoDB database.
 */
define( "kMONGO_OPTS_DB_CLRETRIEVE", [] );

/**
 * <h4>Collections empty options.</h4>
 *
 * This definition should contain the default options for emptying a collection.
 */
define( "kMONGO_OPTS_CL_EMPTY", [] );

/**
 * <h4>Collections drop options.</h4>
 *
 * This definition should contain the default options for dropping a collection.
 */
define( "kMONGO_OPTS_CL_DROP", [] );

/**
 * <h4>Collections insert options.</h4>
 *
 * This definition should contain the default options for inserting a record.
 */
define( "kMONGO_OPTS_CL_INSERT", [] );

/**
 * <h4>Collections update options.</h4>
 *
 * This definition should contain the default options for updating a record.
 */
define( "kMONGO_OPTS_CL_UPDATE", [] );

/**
 * <h4>Collections replace options.</h4>
 *
 * This definition should contain the default options for replacing a record.
 */
define( "kMONGO_OPTS_CL_REPLACE", [] );

/**
 * <h4>Collections find options.</h4>
 *
 * This definition should contain the default options for finding a record.
 */
define( "kMONGO_OPTS_CL_FIND", [] );

/**
 * <h4>Collections delete options.</h4>
 *
 * This definition should contain the default options for deleting a record.
 */
define( "kMONGO_OPTS_CL_DELETE", [] );

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

/**
 * <h4>Default update policy.</h4>
 *
 * This definition should contain the default ArangoDB update policy.
 */
define( "kARANGO_OPTS_UPDATE_DEFAULT", TRUE );


?>
