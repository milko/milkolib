<?php

/*=======================================================================================
 *																						*
 *									defines.inc.php										*
 *																						*
 *======================================================================================*/

/**
 *	<h4>Local definitions.</h4>
 *
 * This file contains the local definitions for this implementation, modify this file to
 * reflect your environment.
 *
 *	@package	Batch
 *	@subpackage	Definitions
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		07/04/2016
 */

/*=======================================================================================
 *	DATABASE ENVIRONMENT																*
 * Modify this definition to indicate the database engine and environment.				*
 *======================================================================================*/

/**
 * <h3>Database engine.</h3><p />
 *
 * This defines the database type: <tt>ARANGO</tt> for <b>ArangoDB</b> or <tt>MONGO</tt>
 * for <b>MongoDB</b>.
 */
define( 'kENGINE', "ARANGO" );

/*=======================================================================================
 *	DATA SERVER ENVIRONMENT																*
 * Modify this definition to provide the data source name.								*
 *======================================================================================*/

/**
 * <h3>Data source name.</h3><p />
 *
 * This defines the server default URL.
 */
define( 'kDSN_MONGO', "mongodb://localhost:27017" );	// MongoDB.
define( 'kDSN_ARANGO', "tcp://localhost:8529" );		// ArangoDB.

/*=======================================================================================
 *	DATABASE ENVIRONMENT																*
 * Modify this definition to provide the database name.									*
 *======================================================================================*/

/**
 * <h3>Database name.</h3><p />
 *
 * This defines the data dictionary and data base name.
 */
const kDB = 'nipn';


?>