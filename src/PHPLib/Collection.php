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
 * 	<li><b>{@link Server()}</b>: Return the database server object.
 * 	<li><b>{@link Database()}</b>: Return the database object.
 * 	<li><b>{@link Connection()}</b>: Return the collection native driver object.
 * 	<li><b>{@link InsertOne()}</b>: Insert one record.
 * 	<li><b>{@link InsertMany()}</b>: Insert many records.
 * 	<li><b>{@link UpdateOne()}</b>: Update one record.
 * 	<li><b>{@link UpdateMany()}</b>: Update many records.
 * 	<li><b>{@link ReplaceOne()}</b>: Replace one record.
 * 	<li><b>{@link FindOne()}</b>: Find one record.
 * 	<li><b>{@link FindMany()}</b>: Find many records.
 * 	<li><b>{@link DeleteOne()}</b>: Delete one record.
 * 	<li><b>{@link DeleteMany()}</b>: Delete many records.
 * 	<li><b>{@link Empty()}</b>: Clear collection contents; this method is virtual.
 * 	<li><b>{@link Drop()}</b>: Drop current collection; this method is virtual.
 * </ul>
 *
 * The public methods do not implement the actual operations, this is delegated to a
 * protected virtual interface which must be implemented by derived concrete classes:
 *
 * <ul>
 * 	<li><b>{@link collectionNew()}</b>: Instantiate a driver native database instance.
 * 	<li><b>{@link collectionName()}</b>: Return the collection name.
 * 	<li><b>{@link insert()}</b>: Insert one or many records.
 * 	<li><b>{@link update()}</b>: Update one or many records.
 * 	<li><b>{@link replace()}</b>: Replace one or many records.
 * 	<li><b>{@link find()}</b>: Find one or many records.
 * 	<li><b>{@link delete()}</b>: Delete one or many records.
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
 * $server->Connect();<br/>
 * $database = $server->RetrieveCollection( "database" );<br/>
 * $collection = $database->RetrieveCollection( "collection" );<br/>
 * // Work with that collection...<br/>
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
	 * // In general you will use this form:
	 * $server = new DataServer( 'driver://user:pass@host:8989/database/collection' );<br/>
	 * $database = $server->RetrieveCollection( "database" );<br/>
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
	 *	Empty																			*
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
	abstract public function Empty( $theOptions = NULL );


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
	 *	InsertOne																		*
	 *==================================================================================*/

	/**
	 * <h4>Insert a single record.</h4>
	 *
	 * This method can be used to insert a record in the collection, the method expects the
	 * following parameters:
	 *
	 * <ul>
	 *	<li><b>$theRecord</b>: The record to be inserted.
	 *	<li><b>$theOptions</b>: An array of options representing driver native options.
	 * </ul>
	 *
	 * It is the responsibility of the caller to ensure the server is connected.
	 *
	 * @param array|object			$theRecord			The record to be inserted.
	 * @param mixed					$theOptions			Collection native options.
	 * @return mixed				The record's unique identifier.
	 *
	 * @uses insert()
	 */
	public function InsertOne( $theRecord, $theOptions = NULL )
	{
		return $this->insert( $theRecord, FALSE, $theOptions );						// ==>

	} // InsertOne.

	
	/*===================================================================================
	 *	InsertMany																		*
	 *==================================================================================*/

	/**
	 * <h4>Insert a set of records.</h4>
	 *
	 * This method can be used to insert a set of records in the collection, the method
	 * expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theRecords</b>: The records to be inserted.
	 *	<li><b>$theOptions</b>: An array of options representing driver native options.
	 * </ul>
	 *
	 * It is the responsibility of the caller to ensure the server is connected.
	 *
	 * @param array					$theRecords			The records to be inserted.
	 * @param mixed					$theOptions			Collection native options.
	 * @return array				The list of record's unique identifiers in order.
	 *
	 * @uses insert()
	 */
	public function InsertMany( $theRecords, $theOptions = NULL )
	{
		return $this->insert( $theRecords, TRUE, $theOptions );						// ==>

	} // InsertMany.


	/*===================================================================================
	 *	UpdateOne																		*
	 *==================================================================================*/

	/**
	 * <h4>Update a single record.</h4>
	 *
	 * This method can be used to update the first selected record in a collection, the
	 * method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theCriteria</b>: The modification criteria.
	 *	<li><b>$theFilter</b>: The selection criteria.
	 *	<li><b>$theOptions</b>: An array of options representing driver native options.
	 * </ul>
	 *
	 * It is the responsibility of the caller to ensure the server is connected.
	 *
	 * @param array					$theCriteria		The modification criteria.
	 * @param array					$theFilter			The selection criteria.
	 * @param mixed					$theOptions			Collection native options.
	 * @return int					The number of modified records.
	 *
	 * @uses update()
	 */
	public function UpdateOne( $theCriteria, $theFilter = NULL, $theOptions = NULL )
	{
		return $this->update( $theCriteria, $theFilter, FALSE, $theOptions );		// ==>

	} // UpdateOne.


	/*===================================================================================
	 *	UpdateMany																		*
	 *==================================================================================*/

	/**
	 * <h4>Update a set of records.</h4>
	 *
	 * This method can be used to update a set of records in the collection, the method
	 * expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theCriteria</b>: The modification criteria.
	 *	<li><b>$theFilter</b>: The selection criteria.
	 *	<li><b>$theOptions</b>: An array of options representing driver native options.
	 * </ul>
	 *
	 * It is the responsibility of the caller to ensure the server is connected.
	 *
	 * @param array					$theCriteria		The modification criteria.
	 * @param array					$theFilter			The selection criteria.
	 * @param mixed					$theOptions			Collection native options.
	 * @return int					The number of modified records.
	 *
	 * @uses update()
	 */
	public function UpdateMany( $theCriteria, $theFilter = NULL, $theOptions = NULL )
	{
		return $this->update( $theCriteria, $theFilter, TRUE, $theOptions );		// ==>

	} // UpdateMany.


	/*===================================================================================
	 *	ReplaceOne																		*
	 *==================================================================================*/

	/**
	 * <h4>Replace a record.</h4>
	 *
	 * This method can be used to replace the first selected record in a collection, the
	 * method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theRecord</b>: The replacement record.
	 *	<li><b>$theFilter</b>: The selection criteria.
	 *	<li><b>$theOptions</b>: An array of options representing driver native options.
	 * </ul>
	 *
	 * It is the responsibility of the caller to ensure the server is connected.
	 *
	 * @param array					$theRecord			The replacement record.
	 * @param array					$theFilter			The selection criteria.
	 * @param mixed					$theOptions			Collection native options.
	 * @return int					The number of modified records.
	 *
	 * @uses replace()
	 */
	public function ReplaceOne( $theRecord, $theFilter = NULL, $theOptions = NULL )
	{
		return $this->replace( $theRecord, $theFilter, $theOptions );				// ==>

	} // ReplaceOne.


	/*===================================================================================
	 *	FindOne																			*
	 *==================================================================================*/

	/**
	 * <h4>Find a single record.</h4>
	 *
	 * This method can be used to find the first selected record in a collection, the
	 * method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theFilter</b>: The search criteria.
	 *	<li><b>$theOptions</b>: An array of options representing driver native options.
	 * </ul>
	 *
	 * It is the responsibility of the caller to ensure the server is connected.
	 *
	 * @param array					$theFilter			The search criteria.
	 * @param mixed					$theOptions			Collection native options.
	 * @return mixed				The found record.
	 *
	 * @uses find()
	 */
	public function FindOne( $theFilter, $theOptions = NULL )
	{
		return $this->find( $theFilter, FALSE, $theOptions );						// ==>

	} // FindOne.


	/*===================================================================================
	 *	FindMany																		*
	 *==================================================================================*/

	/**
	 * <h4>Find a set of records.</h4>
	 *
	 * This method can be used to find a set of records in the collection, the method
	 * expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theFilter</b>: The search criteria.
	 *	<li><b>$theOptions</b>: An array of options representing driver native options.
	 * </ul>
	 *
	 * It is the responsibility of the caller to ensure the server is connected.
	 *
	 * @param array					$theFilter			The search criteria.
	 * @param mixed					$theOptions			Collection native options.
	 * @return Iterator				The found records.
	 *
	 * @uses find()
	 */
	public function FindMany( $theFilter, $theOptions = NULL )
	{
		return $this->find( $theFilter, TRUE, $theOptions );						// ==>

	} // FindMany.


	/*===================================================================================
	 *	DeleteOne																		*
	 *==================================================================================*/

	/**
	 * <h4>Delete a single record.</h4>
	 *
	 * This method can be used to delete the first selected record in a collection, the
	 * method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theFilter</b>: The selection criteria.
	 *	<li><b>$theOptions</b>: An array of options representing driver native options.
	 * </ul>
	 *
	 * It is the responsibility of the caller to ensure the server is connected.
	 *
	 * @param array					$theFilter			The deletion criteria.
	 * @param mixed					$theOptions			Collection native options.
	 * @return int					The number of deleted records.
	 *
	 * @uses delete()
	 */
	public function DeleteOne( $theFilter, $theOptions = NULL )
	{
		return $this->delete( $theFilter, FALSE, $theOptions );						// ==>

	} // DeleteOne.


	/*===================================================================================
	 *	DeleteMany																		*
	 *==================================================================================*/

	/**
	 * <h4>Delete a set of records.</h4>
	 *
	 * This method can be used to delete a set of records in the collection, the method
	 * expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theCriteria</b>: The deletion criteria.
	 *	<li><b>$theOptions</b>: An array of options representing driver native options.
	 * </ul>
	 *
	 * It is the responsibility of the caller to ensure the server is connected.
	 *
	 * @param array					$theFilter			The deletion criteria.
	 * @param mixed					$theOptions			Collection native options.
	 * @return int					The number of deleted records.
	 *
	 * @uses delete()
	 */
	public function DeleteMany( $theFilter, $theOptions = NULL )
	{
		return $this->delete( $theFilter, TRUE, $theOptions );						// ==>

	} // DeleteMany.

	
	
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
	 * @param mixed					$theOptions			Native driver options.
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
	 * @param mixed					$theOptions			Native driver options.
	 * @return string				The collection name.
	 */
	abstract protected function collectionName( $theOptions = NULL );

	
	
/*=======================================================================================
 *																						*
 *						PROTECTED RECORD MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/
	
	
	
	/*===================================================================================
	 *	insert																			*
	 *==================================================================================*/
	
	/**
	 * <h4>Insert one or more records.</h4>
	 *
	 * This method should insert the provided record or records, the method expects the
	 * following parameters:
	 *
	 * <ul>
	 *	<li><b>$theRecord</b>: The record to be inserted.
	 *	<li><b>$doMany</b>: <tt>TRUE</tt> provided many records, <tt>FALSE</tt> provided
	 * 		one record.
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
	 * @param array|object			$theRecord			The record to be inserted.
	 * @param boolean				$doMany				Single or multiple records flag.
	 * @param mixed					$theOptions			Collection native options.
	 * @return mixed|array			The record's unique identifier(s).
	 */
	abstract protected function insert( $theRecord, $doMany, $theOptions = NULL );


	/*===================================================================================
	 *	update																			*
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
	 *	<li><b>$doMany</b>: <tt>TRUE</tt> update all records, <tt>FALSE</tt> update one
	 * 		record.
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
	 * @param array					$theCriteria		The modification criteria.
	 * @param array					$theFilter			The selection criteria.
	 * @param boolean				$doMany				Single or multiple records flag.
	 * @param mixed					$theOptions			Collection native options.
	 * @return int					The number of modified records.
	 */
	abstract protected function update( $theCriteria,
										$theFilter,
										$doMany,
										$theOptions = NULL );


	/*===================================================================================
	 *	replace																			*
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
	 * @param array					$theRecord			The replacement record.
	 * @param array					$theFilter			The selection criteria.
	 * @param mixed					$theOptions			Collection native options.
	 * @return int					The number of modified records.
	 */
	abstract protected function replace( $theRecord, $theFilter, $theOptions = NULL );


	/*===================================================================================
	 *	find																			*
	 *==================================================================================*/

	/**
	 * <h4>Find the first or all records.</h4>
	 *
	 * This method should find the first or all records matching the provided search
	 * criteria, the method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theFilter</b>: The selection criteria.
	 *	<li><b>$theOptions</b>: An array of options representing driver native options.
	 *	<li><b>$doMany</b>: <tt>TRUE</tt> return all records, <tt>FALSE</tt> return first
	 * 		record.
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
	 * @param array					$theFilter			The selection criteria.
	 * @param boolean				$doMany				Single or multiple records flag.
	 * @param mixed					$theOptions			Collection native options.
	 * @return Iterator				The found records.
	 */
	abstract protected function find( $theFilter, $doMany, $theOptions = NULL );


	/*===================================================================================
	 *	delete																			*
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
	 *	<li><b>$doMany</b>: <tt>TRUE</tt> delete all records, <tt>FALSE</tt> delete first
	 * 		record.
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
	 * @param array					$theFilter			The selection criteria.
	 * @param boolean				$doMany				Single or multiple records flag.
	 * @param mixed					$theOptions			Collection native options.
	 * @return int					The number of deleted records.
	 */
	abstract protected function delete( $theFilter, $doMany, $theOptions = NULL );

	
	
} // class Collection.


?>