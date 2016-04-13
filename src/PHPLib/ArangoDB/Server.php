<?php

/**
 * Server.php
 *
 * This file contains the definition of the ArangoDB {@link Server} class.
 */

namespace Milko\PHPLib\ArangoDB;

use Milko\PHPLib\Server;

use triagens\ArangoDb\Database as ArangoDatabase;
use triagens\ArangoDb\Connection as ArangoConnection;
use triagens\ArangoDb\ConnectionOptions as ArangoConnectionOptions;
use triagens\ArangoDb\UpdatePolicy as ArangoUpdatePolicy;

/*=======================================================================================
 *																						*
 *										Server.php										*
 *																						*
 *======================================================================================*/

/**
 * <h4>ArangoDB server object.</h4>
 *
 * This <em>concrete</em> class is the implementation of a ArangoDB data server, it
 * implements the inherited virtual interface to provide an object that can manage ArangoDB
 * database.
 *
 * This class makes use of the {@link https://github.com/arangodb/arangodb-php.git} PHP
 * library to communicate with the server. This class will store a connection object that
 * has no defined database, when creating {@link Database} objects, this class will create
 * a connection with the same parameters and a defined database name.
 *
 * The class adds the following public methods:
 *
 * <ul>
 * 	<li><b>{@link GetOptions()}</b>: Return the native connection options.
 * </ul>
 *
 *	@package	Data
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		21/02/2016
 */
class Server extends \Milko\PHPLib\Server
{



/*=======================================================================================
 *																						*
 *										MAGIC											*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	__construct																		*
	 *==================================================================================*/

	/**
	 * <h4>Instantiate class.</h4>
	 *
	 * We override the constructor to provide a default connection URL
	 * ({@link kARANGO_OPTS_CLIENT_DEFAULT}).
	 *
	 * @param string			$theConnection		Data source name.
	 * @param mixed				$theOptions			Server connection options.
	 *
	 * @example
	 * <code>
	 * $dsn = new DataSource();
	 * $dsn = new DataSource( 'tcp://127.0.0.1:8529/_system/test_collection' );
	 * </code>
	 */
	public function __construct( $theConnection = NULL, $theOptions = NULL )
	{
		//
		// Init local storage.
		//
		if( $theConnection === NULL )
			$theConnection = kARANGO_OPTS_CLIENT_DEFAULT;

		//
		// Call parent constructor.
		//
		parent::__construct( $theConnection, $theOptions );

	} // Constructor.



/*=======================================================================================
 *																						*
 *								PUBLIC OPTIONS INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	GetOptions																		*
	 *==================================================================================*/

	/**
	 * <h4>Return connection options.</h4>
	 *
	 * This method can be used to return an array of connection options in ArangoDB format
	 * according to the current object's settings.
	 *
	 * @return array				Connection options.
	 *
	 * @uses Protocol()
	 * @uses Host()
	 * @uses Port()
	 * @uses User()
	 * @uses Password()
	 */
	public function GetOptions()
	{
		//
		// Init local storage.
		//
		$options = [];

		//
		// Set endpoint.
		//
		$endpoint = $this->Protocol() . '://' . $this->Host();
		if( ($tmp = $this->Port()) !== NULL )
			$endpoint .= ":$tmp";
		$options[ ArangoConnectionOptions::OPTION_ENDPOINT ] = $endpoint;

		//
		// Set authorisation type.
		//
		$options[ ArangoConnectionOptions::OPTION_AUTH_TYPE ]
			= $this->mDatasource->offsetGet( ArangoConnectionOptions::OPTION_AUTH_TYPE );

		//
		// Set user.
		//
		if( ($tmp = $this->User()) !== NULL )
			$options[ ArangoConnectionOptions::OPTION_AUTH_USER ] = $tmp;

		//
		// Set password.
		//
		if( ($tmp = $this->Password()) !== NULL )
			$options[ ArangoConnectionOptions::OPTION_AUTH_PASSWD ] = $tmp;

		//
		// Set connection persistence.
		//
		$options[ ArangoConnectionOptions::OPTION_CONNECTION ]
			= $this->mDatasource->offsetGet( ArangoConnectionOptions::OPTION_CONNECTION );

		//
		// Set connection time-out.
		//
		$options[ ArangoConnectionOptions::OPTION_TIMEOUT ]
			= $this->mDatasource->offsetGet( ArangoConnectionOptions::OPTION_TIMEOUT );

		//
		// Set time-out reconnect.
		//
		$options[ ArangoConnectionOptions::OPTION_RECONNECT ]
			= $this->mDatasource->offsetGet( ArangoConnectionOptions::OPTION_RECONNECT );

		//
		// Set creation option.
		//
		$options[ ArangoConnectionOptions::OPTION_CREATE ]
			= $this->mDatasource->offsetGet( ArangoConnectionOptions::OPTION_CREATE );

		//
		// Set update policy.
		//
		$options[ ArangoConnectionOptions::OPTION_UPDATE_POLICY ]
			= $this->mDatasource->offsetGet(
				ArangoConnectionOptions::OPTION_UPDATE_POLICY );

		return $options;															// ==>

	} // GetOptions.



/*=======================================================================================
 *																						*
 *								PROTECTED CONNECTION INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	connectionCreate																*
	 *==================================================================================*/

	/**
	 * Open connection.
	 *
	 * We overload this method to return a ArangoDB connection object excluding eventual
	 * database and collection.
	 *
	 * The options parameter is ignored here.
	 *
	 * @param array					$theOptions			Connection native options.
	 * @return ArangoConnection		The native connection.
	 *
	 * @uses defaultConnectionOptions()
	 * @uses GetOptions()
	 */
	protected function connectionCreate( $theOptions = NULL )
	{
		//
		// Add default connection options.
		//
		$this->defaultConnectionOptions();

		return new ArangoConnection( $this->GetOptions() );							// ==>

	} // connectionCreate.


	/*===================================================================================
	 *	connectionDestruct																*
	 *==================================================================================*/

	/**
	 * Close connection.
	 *
	 * The ArangoDB client does not have a destructor, this method does nothing.
	 */
	protected function connectionDestruct( $theOptions = NULL ) {}



/*=======================================================================================
 *																						*
 *						PROTECTED DATABASE MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	databaseCreate																	*
	 *==================================================================================*/

	/**
	 * <h4>Create database.</h4>
	 *
	 * We overload this method to return a database of the correct type.
	 *
	 * @param string				$theDatabase		Database name.
	 * @param array					$theOptions			Database native options.
	 * @return Database				Database object.
	 */
	protected function databaseCreate( $theDatabase, $theOptions = NULL )
	{
		return
			new \Milko\PHPLib\ArangoDB\Database(
				$this, $theDatabase, $theOptions );									// ==>

	} // databaseCreate.


	/*===================================================================================
	 *	databaseList																	*
	 *==================================================================================*/

	/**
	 * <h4>List server databases.</h4>
	 *
	 * In this class we ask the Arango client for the list of user databases by default and
	 * extract their names.
	 *
	 * The options parameter is ignored here.
	 *
	 * @param array					$theOptions			Database native options.
	 * @return array				List of database names.
	 *
	 * @uses Connection()
	 * @uses triagens\ArangoDb\Database::listUserDatabases()
	 */
	protected function databaseList( $theOptions = NULL )
	{
		return
			ArangoDatabase::listUserDatabases(
				$this->mConnection )
					[ 'result' ];													// ==>

	} // databaseList.



/*=======================================================================================
 *																						*
 *							PROTECTED CONFIGURATION INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	defaultConnectionOptions														*
	 *==================================================================================*/

	/**
	 * <h4>Normalise connection options.</h4>
	 *
	 * This method is called by the constructor to complete the provided connection options
	 * with the default values, these values are defined in the {@link arango.local.php}
	 * file, except for the update policy which is hard coded here to <tt>last</tt>.
	 */
	protected function defaultConnectionOptions()
	{
		//
		// Set authorisation type.
		//
		if( ! $this->mDatasource->offsetExists(
			ArangoConnectionOptions::OPTION_AUTH_TYPE ) )
			$this->mDatasource->offsetSet( ArangoConnectionOptions::OPTION_AUTH_TYPE,
				kARANGO_OPTS_AUTH_DEFAULT );

		//
		// Set connection persistence.
		//
		if( ! $this->mDatasource->offsetExists(
			ArangoConnectionOptions::OPTION_CONNECTION ) )
			$this->mDatasource->offsetSet( ArangoConnectionOptions::OPTION_CONNECTION,
				kARANGO_OPTS_PERSIST_DEFAULT );

		//
		// Set connection time-out.
		//
		if( ! $this->mDatasource->offsetExists(
			ArangoConnectionOptions::OPTION_TIMEOUT ) )
			$this->mDatasource->offsetSet( ArangoConnectionOptions::OPTION_TIMEOUT,
				kARANGO_OPTS_TIMEOUT_DEFAULT );

		//
		// Set time-out reconnect.
		//
		if( ! $this->mDatasource->offsetExists(
			ArangoConnectionOptions::OPTION_RECONNECT ) )
			$this->mDatasource->offsetSet( ArangoConnectionOptions::OPTION_RECONNECT,
				kARANGO_OPTS_RECONNECT_DEFAULT );

		//
		// Set creation option.
		//
		if( ! $this->mDatasource->offsetExists(
			ArangoConnectionOptions::OPTION_CREATE ) )
			$this->mDatasource->offsetSet( ArangoConnectionOptions::OPTION_CREATE,
				kARANGO_OPTS_CREATE_DEFAULT );

		//
		// Set update policy.
		//
		if( ! $this->mDatasource->offsetExists(
			ArangoConnectionOptions::OPTION_UPDATE_POLICY ) )
			$this->mDatasource->offsetSet( ArangoConnectionOptions::OPTION_UPDATE_POLICY,
				ArangoUpdatePolicy::LAST );

	} // defaultConnectionOptions.



} // class Server.


?>
