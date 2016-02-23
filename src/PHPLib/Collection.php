<?php

/**
 * Collection.php
 *
 * This file contains the definition of the {@link Collection} class.
 */

namespace Milko\PHPLib;

use Milko\PHPLib\Container;

/*=======================================================================================
 *																						*
 *									Collection.php										*
 *																						*
 *======================================================================================*/

/**
 * <h4>Collection ancestor object.</h4>
 *
 * This <em>abstract</em> class is the ancestor of all classes representing collection
 * instances.
 *
 * This class features a data member that holds the {@link Database} object that
 * instantiated the current collection and another data member that holds the native
 * driver's collection object.
 *
 * The class implements a public interface that deploys the common interface of derived
 * concrete classes:
 *
 * <ul>
 * 	<li><em>Collection related:</em>
 *   <ul>
 * 		<li><b>{@link Server()}</b>: Return the database server object.
 * 		<li><b>{@link Database()}</b>: Return the database object.
 * 		<li><b>{@link Connection()}</b>: Return the collection native driver object.
 * 		<li><b>{@link Truncate()}</b>: Clear collection contents; this method is virtual.
 * 		<li><b>{@link Drop()}</b>: Drop current collection; this method is virtual.
 *   </ul>
 * 	<li><em>Record related:</em>
 *   <ul>
 * 		<li><b>{@link Insert()}</b>: Insert one or more records.
 * 		<li><b>{@link Update()}</b>: Update one or more records.
 * 		<li><b>{@link Replace()}</b>: Replace one or more records.
 * 		<li><b>{@link Delete()}</b>: Delete one or more records.
 * 		<li><b>{@link FindByExample()}</b>: Search by example.
 * 		<li><b>{@link Query()}</b>: Perform a native query.
 * 		<li><b>{@link MapReduce()}</b>: Perform a map and reduce query.
 *   </ul>
 * </ul>
 *
 * The public methods do not implement the actual operations, this is delegated to a
 * protected virtual interface which must be implemented by derived concrete classes:
 *
 * <ul>
 * 	<li><b>{@link collectionNew()}</b>: Instantiate a driver native database instance.
 * 	<li><b>{@link collectionName()}</b>: Return the collection name.
 * 	<li><b>{@link doInsert()}</b>: Insert one or more records.
 * 	<li><b>{@link doUpdate()}</b>: Update one or many records.
 * 	<li><b>{@link doReplace()}</b>: Replace one or many records.
 * 	<li><b>{@link doDelete()}</b>: Delete one or many records.
 * 	<li><b>{@link doFind()}</b>: Find one or many records.
 * 	<li><b>{@link doQuery()}</b>: Perform a driver native query.
 * 	<li><b>{@link doMapReduce()}</b>: Perform a map and reduce query.
 * </ul>
 *
 *	@package	Core
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		17/02/2016
 *
 *	@example	../../test/Collection.php
 *	@example
 * $server = new Milko\PHPLib\DataServer( 'protocol://user:pass@host:9090/database/collection' );<br/>
 * $database = $server->RetrieveDatabase( "database" );<br/>
 * $collection = $database->RetrieveCollection( "collection" );<br/>
 * // Work with that collection...<br/>
 * $collection->Drop(); // Drop collection.
 */
abstract class Collection extends Container
{
	/**
	 * <h4>Database object.</h4>
	 *
	 * This data member holds the <i>database object</i>, it is the object that
	 * instantiated the current collection.
	 *
	 * @var Database
	 */
	protected $mDatabase = NULL;
	
	/**
	 * <h4>Collection native object.</h4>
	 *
	 * This data member holds the <i>collection native object</i>, it is the object provided
	 * by the database driver.
	 *
	 * @var mixed
	 */
	protected $mNativeObject = NULL;
	
	
	
	
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
	 * A database is instantiated by providing the {@link Database} instance to which the
	 * collection belongs, the collection name and a set of native database driver options.
	 *
	 * @param Database				$theDatabase		Database.
	 * @param string				$theCollection		Collection name.
	 * @param mixed					$theOptions			Native driver options.
	 *
	 * @uses collectionNew()
	 *
	 * @example
	 * $server = new DataServer( 'driver://user:pass@host:8989' );<br/>
	 * $database = new Database( $server, "database" );<br/>
	 * $collection = new Collection( $database, "collection" );
	 *
	 * @example
	 * // In general you will use this form:<br/>
	 * $server = new DataServer( 'driver://user:pass@host:8989/database/collection' );<br/>
	 * $database = $server->RetrieveDatabase( "database" );<br/>
	 * $collection = $database->RetrieveCollection( "collection" );
	 */
	public function __construct( Database $theDatabase, $theCollection, $theOptions = NULL )
	{
		//
		// Call parent constructor.
		//
		parent::__construct();
		
		//
		// Store server instance.
		//
		$this->mDatabase = $theDatabase;
		
		//
		// Store the driver instance.
		//
		$this->mNativeObject = $this->collectionNew( $theCollection, $theOptions );
		
	} // Constructor.
	
	
	/*===================================================================================
	 *	__toString																		*
	 *==================================================================================*/
	
	/**
	 * <h4>Return collection name</h4>
	 *
	 * Objects of this class should return the collection name when cast to string.
	 *
	 * The method will use the protected {@link collectionName()} method.
	 *
	 * @return string
	 *
	 * @uses collectionName()
	 *
	 * @example
	 * $name = (string) $collection;
	 */
	public function __toString()						{	return $this->collectionName();	}
	
	
	
/*=======================================================================================
 *																						*
 *							PUBLIC MEMBER MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/
	
	
	
	/*===================================================================================
	 *	Server																			*
	 *==================================================================================*/
	
	/**
	 * <h4>Return the database server.</h4>
	 *
	 * This method can be used to retrieve the database server object.
	 *
	 * @return DataServer			Collection server object.
	 *
	 * @example
	 * $server = $this->Server();
	 */
	public function Server()						{	return $this->mDatabase->Server();	}


	/*===================================================================================
	 *	Database																		*
	 *==================================================================================*/

	/**
	 * <h4>Return the database object.</h4>
	 *
	 * This method can be used to retrieve the database object.
	 *
	 * @return Database				Database object.
	 *
	 * @example
	 * $database = $this->Database();
	 */
	public function Database()								{	return $this->mDatabase;	}

	
	/*===================================================================================
	 *	Connection																		*
	 *==================================================================================*/
	
	/**
	 * <h4>Return the collection native driver object.</h4>
	 *
	 * This method can be used to retrieve the collection native driver object.
	 *
	 * @return mixed				Collection native driver object.
	 *
	 * @example
	 * $col = $this->Connection();
	 */
	public function Connection()						{	return $this->mNativeObject;	}
	
	
	
/*=======================================================================================
 *																						*
 *							PUBLIC COLLECTION MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/
	
	
	
	/*===================================================================================
	 *	Truncate																		*
	 *==================================================================================*/
	
	/**
	 * <h4>Clear the collection contents.</h4>
	 *
	 * This method can be used to erase the contents of the collection, the method expects a
	 * single parameter that represents driver native options.
	 *
	 * It is the responsibility of the caller to ensure the server is connected.
	 *
	 * The provided parameter represents a set of native options provided to the driver for
	 * performing the operation: if needed, in derived concrete classes you should define
	 * globally a set of options and subtitute a <tt>NULL</tt> value with them in this
	 * method, this will guarantee that the options will always be used when performing this
	 * operation.
	 *
	 * Derived concrete classes must implement this method.
	 *
	 * @param mixed					$theOptions			Collection native options.
	 */
	abstract public function Truncate( $theOptions = NULL );


	/*===================================================================================
	 *	Drop																			*
	 *==================================================================================*/

	/**
	 * <h4>Drop the current collection.</h4>
	 *
	 * This method can be used to the current collection, the method expects a single
	 * parameter that represents driver native options.
	 *
	 * It is the responsibility of the caller to ensure the server is connected.
	 *
	 * The provided parameter represents a set of native options provided to the driver for
	 * performing the operation: if needed, in derived concrete classes you should define
	 * globally a set of options and subtitute a <tt>NULL</tt> value with them in this
	 * method, this will guarantee that the options will always be used when performing this
	 * operation.
	 *
	 * Derived concrete classes must implement this method.
	 *
	 * @param mixed					$theOptions			Collection native options.
	 */
	abstract public function Drop( $theOptions = NULL );



/*=======================================================================================
 *																						*
 *							PUBLIC RECORD MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Insert																			*
	 *==================================================================================*/

	/**
	 * <h4>Insert documents.</h4>
	 *
	 * This method can be used to insert one or more documents in the collection, the method
	 * expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theDocument</b>: The document or documents to be inserted, to provide a list
	 * 		of documents, the parameter must be an array.
	 *	<li><b>$theOptions</b>: An array of options representing driver native options.
	 * </ul>
	 *
	 * The options parameter can be used to indicate whether a list of documents was
	 * provided, or only one: set <tt>'$doAll' => TRUE</tt> to indicate that a list of
	 * records was provided; by default the parameter will be <tt>FALSE</tt>.
	 *
	 * If a single document was provided, the method will return its unique identifier, if
	 * a list of documents was provided, the method will return the list of identifiers in
	 * order.
	 *
	 * It is the responsibility of the caller to ensure the server is connected.
	 *
	 * @param mixed					$theDocument		The document(s) to be inserted.
	 * @param array					$theOptions			Driver native options.
	 * @return mixed				The document's unique identifier(s).
	 *
	 * @uses doInsert()
	 *
	 * @example
	 * // Insert the provided document.<br/>
	 * $id = $collection->Insert( $document );<br/>
	 * // Insert the provided list of documents.<br/>
	 * $ids = $collection->Insert( $list, [ '$doAll' => TRUE ] );
	 */
	public function Insert( $theDocument, $theOptions = NULL )
	{
		//
		// Set default options.
		//
		if( $theOptions === NULL )
			$theOptions = [ '$doAll' => FALSE ];
		elseif( ! array_key_exists( '$doAll', $theOptions ) )
			$theOptions[ '$doAll' ] = FALSE;

		return $this->doInsert( $theDocument, $theOptions );						// ==>

	} // Insert.

	
	/*===================================================================================
	 *	Update																			*
	 *==================================================================================*/

	/**
	 * <h4>Update documents.</h4>
	 *
	 * This method can be used to update one or more documents in the collection, the method
	 * expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theCriteria</b>: The modification criteria.
	 *	<li><b>$theFilter</b>: The selection criteria.
	 *	<li><b>$theOptions</b>: An array of options representing driver native options.
	 * </ul>
	 *
	 * The options parameter can be used to indicate whether to update all records selected
	 * by the filter, or just the first one: provide <tt>'$doAll' => FALSE</tt> to only
	 * modify the first document; by default this method will update all matched documents.
	 *
	 * It is the responsibility of the caller to ensure the server is connected.
	 *
	 * @param array					$theCriteria		The modification criteria.
	 * @param mixed					$theFilter			The selection criteria.
	 * @param array					$theOptions			Driver native options.
	 * @return int					The number of modified records.
	 *
	 * @uses doUpdate()
	 *
	 * @example
	 * // Update first document.<br/>
	 * $count = $collection->Update( $criteria, $filter, [ '$doAll' => FALSE ] );<br/>
	 * // Update all documents.<br/>
	 * $ids = $collection->Update( $criteria, $filter );
	 */
	public function Update( $theCriteria, $theFilter = NULL, $theOptions = NULL )
	{
		//
		// Set default options.
		//
		if( $theOptions === NULL )
			$theOptions = [ '$doAll' => TRUE ];
		elseif( ! array_key_exists( '$doAll', $theOptions ) )
			$theOptions[ '$doAll' ] = TRUE;

		return $this->doUpdate( $theCriteria, $theFilter, $theOptions );			// ==>

	} // Update.


	/*===================================================================================
	 *	Replace																			*
	 *==================================================================================*/

	/**
	 * <h4>Replace a record.</h4>
	 *
	 * This method can be used to replace the first selected document in a collection, the
	 * method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theDocument</b>: The replacement document.
	 *	<li><b>$theFilter</b>: The selection criteria.
	 *	<li><b>$theOptions</b>: An array of options representing driver native options.
	 * </ul>
	 *
	 * It is the responsibility of the caller to ensure the server is connected.
	 *
	 * @param array					$theDocument		The replacement document.
	 * @param mixed					$theFilter			The selection criteria.
	 * @param array					$theOptions			Driver native options.
	 * @return int					The number of replaced records.
	 *
	 * @uses doReplace()
	 */
	public function Replace( $theDocument, $theFilter = NULL, $theOptions = NULL )
	{
		return $this->doReplace( $theDocument, $theFilter, $theOptions );			// ==>

	} // Replace.


	/*===================================================================================
	 *	Delete																			*
	 *==================================================================================*/

	/**
	 * <h4>Delete documents.</h4>
	 *
	 * This method can be used to delete one or more documents selected by the provided
	 * filter, the method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theFilter</b>: The selection criteria.
	 *	<li><b>$theOptions</b>: An array of options representing driver native options.
	 * </ul>
	 *
	 * The options parameter can be used to indicate whether to delete all records selected
	 * by the filter, or just the first one: provide <tt>'$doAll' => FALSE</tt> to only
	 * delete the first document; by default this method will delete all matched documents.
	 *
	 * It is the responsibility of the caller to ensure the server is connected.
	 *
	 * @param mixed					$theFilter			The selection criteria.
	 * @param array					$theOptions			Driver native options.
	 * @return int					The number of deleted records.
	 *
	 * @uses doDelete()
	 */
	public function Delete( $theFilter, $theOptions = NULL )
	{
		//
		// Set default options.
		//
		if( $theOptions === NULL )
			$theOptions = [ '$doAll' => TRUE ];
		elseif( ! array_key_exists( '$doAll', $theOptions ) )
			$theOptions[ '$doAll' ] = TRUE;

		return $this->doDelete( $theFilter, $theOptions );							// ==>

	} // Delete.


	/*===================================================================================
	 *	FindByExample																	*
	 *==================================================================================*/

	/**
	 * <h4>Find by example.</h4>
	 *
	 * This method can be used to select all documents matching the provided example
	 * document. The method will select all documents in the collection whose properties
	 * match all the properties of the provided example document, the method expects the
	 * following parameters:
	 *
	 * <ul>
	 *	<li><b>$theDocument</b>: The search filter as an example document.
	 *	<li><b>$theOptions</b>: An array of options representing driver native options.
	 * </ul>
	 *
	 * The options parameter can be used to indicate the start and limit of the selection:
	 * provide <tt>'$start'</em> to indicate from which found record to start and
	 * <em>'$limit'</em> to indicate the number of documents to be returned; by default the
	 * method will start with the first document and return all.
	 *
	 * It is the responsibility of the caller to ensure the server is connected.
	 *
	 * @param array					$theDocument		The example document.
	 * @param array					$theOptions			Driver native options.
	 * @return Iterator				The found records.
	 *
	 * @uses doFind()
	 *
	 * @example
	 * // Find first five document.
	 * $iterator = $collection->FindByExample( [ 'color' => 'red', 'city' => 'Rome' ], [ '$start' => 0, '$limit' => 5 ] );<br/>
	 * // Find all documents.<br/>
	 * $iterator = $collection->FindByExample( [ 'color' => 'red', 'city' => 'Rome' ] );
	 */
	public function FindByExample( $theDocument, $theOptions = NULL )
	{
		//
		// Set default options.
		//
		if( $theOptions === NULL )
			$theOptions = [ '$start' => 0 ];
		elseif( ! array_key_exists( '$start', $theOptions ) )
			$theOptions[ '$start' ] = 0;

		return $this->doFind( $theDocument, $theOptions );							// ==>

	} // FindByExample.


	/*===================================================================================
	 *	Query																			*
	 *==================================================================================*/

	/**
	 * <h4>Query the collection.</h4>
	 *
	 * This method can be used to perform a query in the current collection, the method
	 * expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theQuery</b>: The query as the driver native selection criteria.
	 *	<li><b>$theOptions</b>: An array of options representing driver native options.
	 * </ul>
	 *
	 * It is the responsibility of the caller to ensure the server is connected.
	 *
	 * @param mixed					$theQuery			The selection criteria.
	 * @param array					$theOptions			Driver native options.
	 * @return Iterator				The found records.
	 *
	 * @uses doQuery()
	 */
	public function Query( $theQuery, $theOptions = NULL )
	{
		return $this->Query( $theQuery, $theOptions );								// ==>

	} // Query.


	/*===================================================================================
	 *	MapReduce																		*
	 *==================================================================================*/

	/**
	 * <h4>Execute an aggregation query.</h4>
	 *
	 * This method can be used to perform a map and reduce query, the method expects the
	 * following parameters:
	 *
	 * <ul>
	 *	<li><b>$thePipeline</b>: The aggregation pipeline.
	 *	<li><b>$theOptions</b>: An array of options representing driver native options.
	 * </ul>
	 *
	 * It is the responsibility of the caller to ensure the server is connected.
	 *
	 * @param mixed					$thePipeline		The aggregation pipeline.
	 * @param array					$theOptions			Driver native options.
	 * @return Iterator				The found records.
	 *
	 * @uses doMapReduce()
	 */
	public function MapReduce( $thePipeline, $theOptions = NULL )
	{
		return $this->doMapReduce( $thePipeline, $theOptions );						// ==>

	} // MapReduce.

	
	
/*=======================================================================================
 *																						*
 *						PROTECTED COLLECTION MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/
	
	
	
	/*===================================================================================
	 *	collectionNew																	*
	 *==================================================================================*/
	
	/**
	 * <h4>Return a native collection object.</h4>
	 *
	 * This method should instantiate and return a native driver collection object.
	 *
	 * This method assumes that the server is connected and that the {@link Server()} was
	 * set.
	 *
	 * The options parameter represents a set of native options provided to the driver for
	 * performing the operation: if needed, in derived concrete classes you should define
	 * globally a set of options and subtitute a <tt>NULL</tt> value with them in this
	 * method, this will guarantee that the options will always be used when performing this
	 * operation.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param string				$theCollection		Collection name.
	 * @param array					$theOptions			Driver native options.
	 * @return mixed				Native collection object.
	 */
	abstract protected function collectionNew( $theCollection, $theOptions = NULL );
	
	
	/*===================================================================================
	 *	collectionName																	*
	 *==================================================================================*/
	
	/**
	 * <h4>Return the collection name.</h4>
	 *
	 * This method should return the current collection name.
	 *
	 * Note that this method <em>must</em> return a non empty string.
	 *
	 * The provided parameter represents a set of native options provided to the driver for
	 * performing the operation: if needed, in derived concrete classes you should define
	 * globally a set of options and subtitute a <tt>NULL</tt> value with them in this
	 * method, this will guarantee that the options will always be used when performing this
	 * operation.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param array					$theOptions			Driver native options.
	 * @return string				The collection name.
	 */
	abstract protected function collectionName( $theOptions = NULL );

	
	
/*=======================================================================================
 *																						*
 *						PROTECTED DOCUMENT MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/
	
	
	
	/*===================================================================================
	 *	doInsert																		*
	 *==================================================================================*/
	
	/**
	 * <h4>Insert one or more records.</h4>
	 *
	 * This method should insert the provided record or records, the method expects the
	 * following parameters:
	 *
	 * <ul>
	 *	<li><b>$theDocument</b>: The document or documents to be inserted.
	 *	<li><b>$theOptions</b>: An array of options representing driver native options.
	 * </ul>
	 *
	 * If the <tt>'$doAll'</tt> option is set, the method will assume the provided parameter
	 * to be a list of documents and will return the list of inserted document identifiers;
	 * if the option is not set, the method will assume the parameter to be a single
	 * document and will return its identifier; the option <em>must</em> be provided. Other
	 * provided values represent native driver options.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param mixed					$theDocument		The document(s) to be inserted.
	 * @param array					$theOptions			Driver native options.
	 * @return mixed				The document's unique identifier(s).
	 */
	abstract protected function doInsert( $theDocument, $theOptions );


	/*===================================================================================
	 *	doUpdate																		*
	 *==================================================================================*/

	/**
	 * <h4>Update one or more records.</h4>
	 *
	 * This method should update the first or all records matching the provided search
	 * criteria, the method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theCriteria</b>: The modification criteria.
	 *	<li><b>$theFilter</b>: The selection criteria.
	 *	<li><b>$theOptions</b>: An array of options representing driver native options.
	 * </ul>
	 *
	 * If the <tt>'$doAll'</tt> option is set, the method will update all found records, if
	 * not, it will only update the first found record; the option <em>must</em> be
	 * provided. Other provided values represent native driver options.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param array					$theCriteria		The modification criteria.
	 * @param mixed					$theFilter			The selection criteria.
	 * @param array					$theOptions			Driver native options.
	 * @return int					The number of modified records.
	 */
	abstract protected function doUpdate( $theCriteria, $theFilter, $theOptions );


	/*===================================================================================
	 *	doReplace																		*
	 *==================================================================================*/

	/**
	 * <h4>Replace a record.</h4>
	 *
	 * This method should replace the matching provided record, the method expects the
	 * following parameters:
	 *
	 * <ul>
	 *	<li><b>$theRecord</b>: The replacement record.
	 *	<li><b>$theFilter</b>: The selection criteria.
	 *	<li><b>$theOptions</b>: An array of options representing driver native options.
	 * </ul>
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param array					$theDocument		The replacement document.
	 * @param mixed					$theFilter			The selection criteria.
	 * @param array					$theOptions			Driver native options.
	 * @return int					The number of replaced records.
	 */
	abstract protected function doReplace( $theRecord, $theFilter, $theOptions );


	/*===================================================================================
	 *	doDelete																		*
	 *==================================================================================*/

	/**
	 * <h4>Delete the first or all records.</h4>
	 *
	 * This method should delete the first or all records matching the provided search
	 * criteria, the method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theFilter</b>: The selection criteria.
	 *	<li><b>$theOptions</b>: An array of options representing driver native options.
	 * </ul>
	 *
	 * If the <tt>'$doAll'</tt> option is set, the method will delete all found records, if
	 * not, it will only delete the first found record; the option <em>must</em> be
	 * provided. Other provided values represent native driver options.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param mixed					$theFilter			The selection criteria.
	 * @param array					$theOptions			Driver native options.
	 * @return int					The number of deleted records.
	 */
	abstract protected function doDelete( $theFilter, $theOptions );


	/*===================================================================================
	 *	doFind																			*
	 *==================================================================================*/

	/**
	 * <h4>Find the first or all records.</h4>
	 *
	 * This method should the records matching the provided search criteria, the method
	 * expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theDocument</b>: The search filter as an example document.
	 *	<li><b>$theOptions</b>: An array of options representing driver native options.
	 * </ul>
	 *
	 * The options parameter can be used to indicate the start and limit of the selection:
	 * provide <tt>'$start'</tt> to indicate from which found record to start and
	 * <tt>'$limit'</tt> to indicate the number of documents to be returned; by default the
	 * method will start with the first document and return all. Other provided values
	 * represent native driver options.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param array					$theDocument		The example document.
	 * @param array					$theOptions			Driver native options.
	 * @return Iterator				The found records.
	 */
	abstract protected function doFind( $theDocument, $theOptions );


	/*===================================================================================
	 *	doQuery																			*
	 *==================================================================================*/

	/**
	 * <h4>Query the collection.</h4>
	 *
	 * This method should perform the provided query expressed in the driver's native
	 * format, the method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theQuery</b>: The selection criteria.
	 *	<li><b>$theOptions</b>: An array of options representing driver native options.
	 * </ul>
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param mixed					$theQuery			The selection criteria.
	 * @param mixed					$theOptions			Collection native options.
	 * @return Iterator				The found records.
	 */
	abstract protected function doQuery( $theQuery, $theOptions );


	/*===================================================================================
	 *	doMapReduce																		*
	 *==================================================================================*/

	/**
	 * <h4>Execute an aggregation query.</h4>
	 *
	 * This method should perform a map and reduce query expressed in the driver's native
	 * format, the method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$thePipeline</b>: The aggregation pipeline.
	 *	<li><b>$theOptions</b>: An array of options representing driver native options.
	 * </ul>
	 *
	 * This method assumes that the server is connected, it is the responsibility of the
	 * caller to ensure this.
	 *
	 * The options parameter represents a set of native options provided to the driver for
	 * performing the operation: if needed, in derived concrete classes you should define
	 * globally a set of options and subtitute a <tt>NULL</tt> value with them in this
	 * method, this will guarantee that the options will always be used when performing this
	 * operation.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param mixed					$thePipeline		The aggregation pipeline.
	 * @param array					$theOptions			Driver native options.
	 * @return Iterator				The found records.
	 */
	abstract protected function doMapReduce( $thePipeline, $theOptions );

	
	
} // class Collection.


?>
