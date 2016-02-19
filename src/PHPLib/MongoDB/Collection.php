<?php

/**
 * Collection.php
 *
 * This file contains the definition of the {@link Collection} class.
 */

namespace Milko\PHPLib\MongoDB;

/*=======================================================================================
 *																						*
 *									Collection.php										*
 *																						*
 *======================================================================================*/

/**
 * <h4>Collection ancestor object.</h4>
 *
 * This <em>concrete</em> class is the implementation of a MongoDB collection, it implements
 * the inherited virtual interface to provide an object that can manage MongoDB collections.
 *
 *	@package	Data
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		19/02/2016
 *
 *	@example	../../test/Collection.php
 *	@example
 * $server = new Milko\PHPLib\DataServer( 'protocol://user:pass@host:9090/database/collection' );<br/>
 * $server->Connect();<br/>
 * $database = $server->RetrieveCollection( "database" );<br/>
 * $collection = $database->RetrieveCollection( "collection" );<br/>
 * // Work with that collection...
 */
class Collection extends \Milko\PHPLib\Collection
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
 *							PUBLIC COLLECTION MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Clear																			*
	 *==================================================================================*/

	/**
	 * <h4>Clear the collection contents.</h4>
	 *
	 * We overload this method to call the native object's method.
	 *
	 * @param mixed					$theOptions			Collection native options.
	 *
	 * @uses Connection()
	 * @uses \MongoDB\Collection::deleteMany()
	 *
	 * @see kMONGO_OPTS_CL_EMPTY
	 */
	public function Clear( $theOptions = NULL )
	{
		//
		// Init local storage.
		//
		if( $theOptions === NULL )
			$theOptions = kMONGO_OPTS_CL_EMPTY;
		
		//
		// Empty collection.
		//
		$this->Connection()->deleteMany( [], $theOptions );

	} // Clear.


	/*===================================================================================
	 *	Drop																			*
	 *==================================================================================*/

	/**
	 * <h4>Drop the current collection.</h4>
	 *
	 * We overload this method to call the native object's method.
	 *
	 * @param mixed					$theOptions			Collection native options.
	 *
	 * @uses Connection()
	 * @uses \MongoDB\Collection::drop()
	 *
	 * @see kMONGO_OPTS_CL_DROP
	 */
	public function Drop( $theOptions = NULL )
	{
		//
		// Init local storage.
		//
		if( $theOptions === NULL )
			$theOptions = kMONGO_OPTS_CL_DROP;

		//
		// Call native method.
		//
		$this->Connection()->drop( $theOptions );

	} // Drop.



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
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param string				$theCollection		Collection name.
	 * @param mixed					$theOptions			Native driver options.
	 * @return mixed				Native collection object.
	 *
	 * @uses Database()
	 * @uses \MongoDB\Database::selectCollection()
	 *
	 * @see kMONGO_OPTS_DB_CLCREATE
	 */
	protected function collectionNew( $theCollection, $theOptions = NULL )
	{
		//
		// Init local storage.
		//
		if( $theOptions === NULL )
			$theOptions = kMONGO_OPTS_DB_CLCREATE;

		return $this->Database()->Connection()->selectCollection(
				(string)$theCollection, $theOptions );								// ==>

	} // collectionNew.


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
	 * This method must be implemented by derived concrete classes.
	 *
	 * @return string				The collection name.
	 *
	 * @uses Connection()
	 * @uses \MongoDB\Collection::getCollectionName()
	 */
	protected function collectionName( $theOptions = NULL )
	{
		return $this->Connection()->getCollectionName();							// ==>
	
	} // collectionName.



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
	 *	<li><b>$theOptions</b>: An array of options representing driver native options.
	 *	<li><b>$doMany</b>: <tt>TRUE</tt> provided many records, <tt>FALSE</tt> provided
	 * 		one record.
	 * </ul>
	 *
	 * This method assumes that the server is connected, it is the responsibility of the
	 * caller to ensure this.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param array|object			$theRecord			The record to be inserted.
	 * @param boolean				$doMany				Single or multiple records flag.
	 * @param mixed					$theOptions			Collection native options.
	 * @return mixed|array			The record's unique identifier(s).
	 *
	 * @uses Connection()
	 * @uses \MongoDB\Collection::insertOne()
	 * @uses \MongoDB\Collection::insertMany()
	 *
	 * @see kMONGO_OPTS_CL_INSERT
	 *
	 * @example
	 * // Insert one record.
	 * $collection->insert( $record, FALSE );<br/>
	 * // Insert many records.
	 * $collection->insert( $records, TRUE );
	 */
	protected function insert( $theRecord, $doMany, $theOptions = NULL )
	{
		//
		// Init local storage.
		//
		if( $theOptions === NULL )
			$theOptions = kMONGO_OPTS_CL_INSERT;
		
		//
		// Normalise container.
		//
		if( $theRecord instanceof \Milko\PHPLib\Container )
			$theRecord = $theRecord->toArray();

		//
		// Insert one or more records.
		//
		$result = ( $doMany ) ? $this->Connection()->insertMany( $theRecord, $theOptions )
							  : $this->Connection()->insertOne( $theRecord, $theOptions );

		return ( $doMany ) ? $result->getInsertedIds()								// ==>
						   : $result->getInsertedId();								// ==>

	} // insert.


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
	 *    <li><b>$theCriteria</b>: The modification criteria.
	 *    <li><b>$theFilter</b>: The selection criteria.
	 *    <li><b>$doMany</b>: <tt>TRUE</tt> update all records, <tt>FALSE</tt> update one
	 *        record.
	 *    <li><b>$theOptions</b>: An array of options representing driver native options.
	 * </ul>
	 *
	 * This method assumes that the server is connected, it is the responsibility of the
	 * caller to ensure this.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param array					$theCriteria		The modification criteria.
	 * @param array					$theFilter			The selection criteria.
	 * @param boolean				$doMany				Single or multiple records flag.
	 * @param mixed					$theOptions			Collection native options.
	 * @return int					The number of modified records.
	 *
	 * @uses Connection()
	 * @uses \MongoDB\Collection::updateOne()
	 * @uses \MongoDB\Collection::updateMany()
	 *
	 * @see kMONGO_OPTS_CL_UPDATE
	 *
	 * @example
	 * // Update one record.
	 * $collection->update( $criteria, $query, FALSE );<br/>
	 * // Insert many records.
	 * $collection->update( $criteria, $query, TRUE );
	 */
	protected function update( $theCriteria,
							   $theFilter,
							   $doMany,
							   $theOptions = NULL )
	{
		//
		// Init local storage.
		//
		if( $theOptions === NULL )
			$theOptions = kMONGO_OPTS_CL_UPDATE;

		//
		// Insert one or more records.
		//
		$result = ( $doMany )
				? $this->Connection()->updateMany( $theFilter, $theCriteria, $theOptions )
				: $this->Connection()->updateOne( $theFilter, $theCriteria, $theOptions );

		return $result->getModifiedCount();											// ==>
	
	} // update.


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
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param array					$theRecord			The replacement record.
	 * @param array					$theFilter			The selection criteria.
	 * @param mixed					$theOptions			Collection native options.
	 * @return int					The number of modified records.
	 *
	 * @uses Connection()
	 * @uses \MongoDB\Collection::replaceOne()
	 *
	 * @see kMONGO_OPTS_CL_REPLACE
	 *
	 * @example
	 * // Update one record.
	 * $collection->replace( $record, $query, FALSE );<br/>
	 * // Insert many records.
	 * $collection->replace( $record, $query, TRUE );
	 */
	protected function replace( $theRecord, $theFilter, $theOptions = NULL )
	{
		//
		// Init local storage.
		//
		if( $theOptions === NULL )
			$theOptions = kMONGO_OPTS_CL_REPLACE;

		//
		// Replace a record.
		//
		$result = $this->Connection()->replaceOne( $theFilter, $theRecord, $theOptions );

		return $result->getModifiedCount();											// ==>
	
	} // replace.


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
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param array					$theFilter			The selection criteria.
	 * @param boolean				$doMany				Single or multiple records flag.
	 * @param mixed					$theOptions			Collection native options.
	 * @return Iterator				The found records.
	 *
	 * @uses Connection()
	 * @uses \MongoDB\Collection::find()
	 * @uses \MongoDB\Collection::findOne()
	 *
	 * @see kMONGO_OPTS_CL_FIND
	 *
	 * @example
	 * // Find first record.
	 * $collection->find( $query, FALSE );<br/>
	 * // Find all records.
	 * $collection->find( $query, TRUE );
	 */
	protected function find( $theFilter, $doMany, $theOptions = NULL )
	{
		//
		// Init local storage.
		//
		if( $theOptions === NULL )
			$theOptions = kMONGO_OPTS_CL_FIND;

		//
		// Insert one or more records.
		//
		return ( $doMany )
			 ? $this->Connection()->find( $theFilter, $theOptions )					// ==>
			 : $this->Connection()->findOne( $theFilter, $theOptions );				// ==>
	
	} // find.


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
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param array					$theFilter			The selection criteria.
	 * @param boolean				$doMany				Single or multiple records flag.
	 * @param mixed					$theOptions			Collection native options.
	 * @return int					The number of deleted records.
	 *
	 * @uses Connection()
	 * @uses \MongoDB\Collection::deleteOne()
	 * @uses \MongoDB\Collection::deleteMany()
	 *
	 * @see kMONGO_OPTS_CL_DELETE
	 *
	 * @example
	 * // Delete first record.
	 * $collection->delete( $query, FALSE );<br/>
	 * // Delete all records.
	 * $collection->delete( $query, TRUE );
	 */
	protected function delete( $theFilter, $doMany, $theOptions = NULL )
	{
		//
		// Init local storage.
		//
		if( $theOptions === NULL )
			$theOptions = kMONGO_OPTS_CL_DELETE;

		//
		// Delete one or more records.
		//
		$result = ( $doMany )
				? $this->Connection()->deleteMany( $theFilter, $theOptions )
				: $this->Connection()->deleteOne( $theFilter, $theOptions );

		return $result->getDeletedCount();											// ==>
	
	} // delete.


} // class Collection.


?>
