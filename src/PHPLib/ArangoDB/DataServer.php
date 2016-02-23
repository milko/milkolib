<?php

/**
 * DataServer.php
 *
 * This file contains the definition of the ArangoDB {@link DataServer} class.
 */

namespace Milko\PHPLib\ArangoDB;

use triagens\ArangoDb\Database as ArangoDatabase;
use triagens\ArangoDb\Collection as ArangoCollection;
use triagens\ArangoDb\CollectionHandler as ArangoCollectionHandler;
use triagens\ArangoDb\Endpoint as ArangoEndpoint;
use triagens\ArangoDb\Connection as ArangoConnection;
use triagens\ArangoDb\ConnectionOptions as ArangoConnectionOptions;
use triagens\ArangoDb\DocumentHandler as ArangoDocumentHandler;
use triagens\ArangoDb\Document as ArangoDocument;
use triagens\ArangoDb\Exception as ArangoException;
use triagens\ArangoDb\Export as ArangoExport;
use triagens\ArangoDb\ConnectException as ArangoConnectException;
use triagens\ArangoDb\ClientException as ArangoClientException;
use triagens\ArangoDb\ServerException as ArangoServerException;
use triagens\ArangoDb\Statement as ArangoStatement;
use triagens\ArangoDb\UpdatePolicy as ArangoUpdatePolicy;

/*=======================================================================================
 *																						*
 *									DataServer.php										*
 *																						*
 *======================================================================================*/

/**
 * <h4>ArangoDB data server object.</h4>
 *
 * This <em>concrete</em> class is the implementation of a ArangoDB data server, it
 * implements the inherited virtual interface to provide an object that can manage ArangoDB
 * databases, collections and documents.
 *
 * This class makes use of the {@link https://github.com/arangodb/arangodb-php.git} PHP
 * library to communicate with the server. This class will store a connection object that
 * has no defined database, when creating {@link Database} objects, this class will create
 * a connection with the same parameters and a defined database name.
 *
 *	@package	Data
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		21/02/2016
 *
 *	@example	../../test/ArangoDataServer.php
 *	@example
 * $server = new Milko\PHPLib\DataServer();<br/>
 * $databases = $server->ListDatabases( kFLAG_CONNECT );<br/>
 * $database = $server->RetrieveDatabase( $databases[ 0 ] );<br/>
 * // Work with that database...<br/>
 * $server->DatabaseDrop( $databases[ 0 ] );<br/>
 * // Dropped the database.
 */
class DataServer extends \Milko\PHPLib\DataServer
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
	 * We override the constructor to provide a default connection URL.
	 *
	 * @param string				$theConnection		Data source name.
	 *
	 * @uses defaultConnectionOptions()
	 *
	 * @see kARANGO_OPTS_CLIENT_DEFAULT
	 *
	 * @example
	 * $dsn = new DataSource( 'tcp://127.0.0.1:8529/_system/test_collection' );
	 */
	public function __construct( $theConnection = NULL )
	{
		//
		// Init local storage.
		//
		if( $theConnection === NULL )
			$theConnection = kARANGO_OPTS_CLIENT_DEFAULT;

		//
		// Call parent constructor.
		//
		parent::__construct( $theConnection );

		//
		// Complete connection options.
		//
		$this->defaultConnectionOptions();

	} // Constructor.



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
	 * @param array					$theOptions			Connection native options.
	 * @return ArangoConnection		The native connection.
	 *
	 * @uses getConnectionOptions()
	 */
	protected function connectionCreate( $theOptions = NULL )
	{
		return new ArangoConnection( $this->getConnectionOptions() );				// ==>

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
	 *	databaseList																	*
	 *==================================================================================*/

	/**
	 * <h4>List server databases.</h4>
	 *
	 * In this class we ask the Mongo client for the list of databases and extract their
	 * names.
	 *
	 * @param array					$theOptions			Database native options.
	 * @return array				List of database names.
	 *
	 * @uses Connection()
	 * @uses ArangoDatabase::listUserDatabases()
	 */
	protected function databaseList( $theOptions = NULL )
	{
		return
			ArangoDatabase::listUserDatabases
					( $this->Connection() )[ 'result' ];							// ==>

	} // databaseList.


	/*===================================================================================
	 *	databaseCreate																	*
	 *==================================================================================*/

	/**
	 * <h4>Create database.</h4>
	 *
	 * In this class we instantiate a {@link Database} object.
	 *
	 * @param string				$theDatabase		Database name.
	 * @param array					$theOptions			Database native options.
	 * @return Database				Database object.
	 */
	protected function databaseCreate( $theDatabase, $theOptions = NULL )
	{
		return new Database( $this, $theDatabase, $theOptions );					// ==>

	} // databaseCreate.


	/*===================================================================================
	 *	databaseRetrieve																*
	 *==================================================================================*/

	/**
	 * <h4>Return a database object.</h4>
	 *
	 * In this class we first check whether the database exists in the server, if that is
	 * the case, we instantiate a {@link Database} object, if not, we return <tt>NULL</tt>.
	 *
	 * @param string				$theDatabase		Database name.
	 * @param array					$theOptions			Database native options.
	 * @return Database				Database object or <tt>NULL</tt> if not found.
	 *
	 * @uses databaseList()
	 */
	protected function databaseRetrieve( $theDatabase, $theOptions = NULL )
	{
		//
		// Check if database exists.
		//
		if( in_array( $theDatabase, $this->databaseList() ) )
			return new Database( $this, $theDatabase, $theOptions );				// ==>

		return NULL;																// ==>

	} // databaseRetrieve.



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
	 * with the default values, these values are defined in the {@link includes.inc.php}
	 * file.
	 *
	 * @see kARANGO_OPTS_AUTH_DEFAULT kARANGO_OPTS_PERSIST_DEFAULT
	 * @see kARANGO_OPTS_TIMEOUT_DEFAULT kARANGO_OPTS_RECONNECT_DEFAULT
	 * @see kARANGO_OPTS_CREATE_DEFAULT kARANGO_OPTS_UPDATE_DEFAULT
	 */
	protected function defaultConnectionOptions()
	{
		//
		// Set authorisation type.
		//
		if( ! offsetExists( ArangoConnectionOptions::OPTION_AUTH_TYPE ) )
			$this->offsetSet( ArangoConnectionOptions::OPTION_AUTH_TYPE,
				kARANGO_OPTS_AUTH_DEFAULT );

		//
		// Set connection persistence.
		//
		if( ! offsetExists( ArangoConnectionOptions::OPTION_CONNECTION ) )
			$this->offsetSet( ArangoConnectionOptions::OPTION_CONNECTION,
				kARANGO_OPTS_PERSIST_DEFAULT );

		//
		// Set connection time-out.
		//
		if( ! offsetExists( ArangoConnectionOptions::OPTION_TIMEOUT ) )
			$this->offsetSet( ArangoConnectionOptions::OPTION_TIMEOUT,
				kARANGO_OPTS_TIMEOUT_DEFAULT );

		//
		// Set time-out reconnect.
		//
		if( ! offsetExists( ArangoConnectionOptions::OPTION_RECONNECT ) )
			$this->offsetSet( ArangoConnectionOptions::OPTION_RECONNECT,
				kARANGO_OPTS_RECONNECT_DEFAULT );

		//
		// Set creation option.
		//
		if( ! offsetExists( ArangoConnectionOptions::OPTION_CREATE ) )
			$this->offsetSet( ArangoConnectionOptions::OPTION_CREATE,
				kARANGO_OPTS_CREATE_DEFAULT );

		//
		// Set update policy.
		//
		if( ! offsetExists( ArangoConnectionOptions::OPTION_UPDATE_POLICY ) )
			$this->offsetSet( ArangoConnectionOptions::OPTION_UPDATE_POLICY,
				kARANGO_OPTS_UPDATE_DEFAULT );

	} // defaultConnectionOptions.


	/*===================================================================================
	 *	getConnectionOptions															*
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
	 *
	 * @see ArangoConnectionOptions::OPTION_ENDPOINT
	 * @see ArangoConnectionOptions::OPTION_AUTH_TYPE
	 * @see ArangoConnectionOptions::OPTION_AUTH_USER
	 * @see ArangoConnectionOptions::OPTION_AUTH_PASSWD
	 * @see ArangoConnectionOptions::OPTION_CONNECTION
	 * @see ArangoConnectionOptions::OPTION_TIMEOUT
	 * @see ArangoConnectionOptions::OPTION_RECONNECT
	 * @see ArangoConnectionOptions::OPTION_CREATE
	 * @see ArangoConnectionOptions::OPTION_UPDATE_POLICY
	 */
	protected function getConnectionOptions()
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
			= $this->offsetGet( ArangoConnectionOptions::OPTION_AUTH_TYPE );

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
			= $this->offsetGet( ArangoConnectionOptions::OPTION_CONNECTION );

		//
		// Set connection time-out.
		//
		$options[ ArangoConnectionOptions::OPTION_TIMEOUT ]
			= $this->offsetGet( ArangoConnectionOptions::OPTION_TIMEOUT );

		//
		// Set time-out reconnect.
		//
		$options[ ArangoConnectionOptions::OPTION_RECONNECT ]
			= $this->offsetGet( ArangoConnectionOptions::OPTION_RECONNECT );

		//
		// Set creation option.
		//
		$options[ ArangoConnectionOptions::OPTION_CREATE ]
			= $this->offsetGet( ArangoConnectionOptions::OPTION_CREATE );

		//
		// Set update policy.
		//
		$options[ ArangoConnectionOptions::OPTION_UPDATE_POLICY ]
			= $this->offsetGet( ArangoConnectionOptions::OPTION_UPDATE_POLICY );

		return $options;															// ==>

	} // defaultConnectionOptions.



} // class DataServer.


?>
