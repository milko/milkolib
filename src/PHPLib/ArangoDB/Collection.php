<?php

/**
 * Collection.php
 *
 * This file contains the definition of the {@link Collection} class.
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
 *									Collection.php										*
 *																						*
 *======================================================================================*/

/**
 * <h4>Collection ancestor object.</h4>
 *
 * This <em>concrete</em> class is the implementation of a ArangoDB collection, it
 * implements the inherited virtual interface to provide an object that can manage ArangoDB
 * collections.
 *
 * This class stores the {@link ArangoCollection} object and instantiates at runtime the
 * {@link ArangoCollectionHandler} to perform operations.
 *
 *	@package	Data
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		21/02/2016
 *
 *	@example	../../test/ArangoCollection.php
 *	@example
 * $server = new Milko\PHPLib\DataServer( 'tcp://localhost:8529/database/collection' );<br/>
 * $database = $server->RetrieveDatabase( "database" );<br/>
 * $collection = $database->RetrieveCollection( "collection" );<br/>
 * // Work with that collection...<br/>
 */
class Collection extends \Milko\PHPLib\Collection
{



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
	 * We overload this method to call the native object's method.
	 *
	 * The options parameter is ignored here.
	 *
	 * @param array					$theOptions			Collection native options.
	 *
	 * @uses Database()
	 * @uses Connection()
	 * @uses ArangoCollectionHandler::truncate()
	 */
	public function Truncate( $theOptions = NULL )
	{
		//
		// Instantiate collection handler.
		//
		$collectionHandler = new ArangoCollectionHandler( $this->Database()->Connection() );

		//
		// Truncate collection.
		//
		$collectionHandler->truncate( $this->Connection() );

	} // Truncate.


	/*===================================================================================
	 *	Drop																			*
	 *==================================================================================*/

	/**
	 * <h4>Drop the current collection.</h4>
	 *
	 * We overload this method to call the native object's method.
	 *
	 * Before dropping the collection we first check whether the collection has an ID: if
	 * it is not <tt>NULL</tt> we drop it.
	 *
	 * The options parameter is ignored here.
	 *
	 * @param array					$theOptions			Collection native options.
	 *
	 * @uses Database()
	 * @uses Connection()
	 * @uses ArangoCollectionHandler::drop()
	 */
	public function Drop( $theOptions = NULL )
	{
		//
		// Check collection.
		//
		if( ($id = $this->Connection()->getId()) !== NULL )
		{
			//
			// Instantiate collection handler.
			//
			$handler = new ArangoCollectionHandler( $this->Database()->Connection() );

			//
			// Drop collection.
			//
			$handler->drop( $id );
		}

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
	 * This method will return a {@link ArangoCollection} object.
	 *
	 * We first instantiate a collection handler, then we check whether the collection
	 * exists, in which case we return it; if not, we create a new one and return it.
	 *
	 * The options parameter is ignored here.
	 *
	 * @param string				$theCollection		Collection name.
	 * @param array					$theOptions			Native driver options.
	 * @return mixed				Native collection object.
	 *
	 * @uses Database()
	 * @uses ArangoCollectionHandler::has()
	 * @uses ArangoCollectionHandler::get()
	 * @uses ArangoCollectionHandler::create()
	 */
	protected function collectionNew( $theCollection, $theOptions = NULL )
	{
		//
		// Get collection handler.
		//
		$collectionHandler = new ArangoCollectionHandler( $this->Database()->Connection() );

		//
		// Return existing collection.
		//
		if( $collectionHandler->has( $theCollection ) )
			return $collectionHandler->get( $theCollection );

		return $collectionHandler->create( $theCollection );						// ==>

	} // collectionNew.


	/*===================================================================================
	 *	collectionName																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the collection name.</h4>
	 *
	 * We overload this method to use the native object.
	 *
	 * The options parameter is ignored here.
	 *
	 * @return string				The collection name.
	 *
	 * @uses Connection()
	 * @uses ArangoCollection::getName()
	 */
	protected function collectionName( $theOptions = NULL )
	{
		return $this->Connection()->getName();										// ==>

	} // collectionName.



/*=======================================================================================
 *																						*
 *						PROTECTED RECORD MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	doInsert																		*
	 *==================================================================================*/

	/**
	 * <h4>Insert one or more records.</h4>
	 *
	 * We overload the method to instantiate a document handler, we then check whether the
	 * provided document parameter refers to many documents, in which case we iterate them
	 * and save one by one; if not, we just save it.
	 *
	 * @param mixed					$theDocument		The document(s) to be inserted.
	 * @param array					$theOptions			Driver native options.
	 * @return mixed				The document's unique identifier(s).
	 *
	 * @uses Database()
	 * @uses Connection()
	 * @uses ArangoDocument::createFromArray()
	 * @uses ArangoDocumentHandler::save()
	 */
	protected function doInsert( $theDocument, $theOptions )
	{
		//
		// Init local storage.
		//
		$do_all = $theOptions[ '$doAll' ];
		$handler = new ArangoDocumentHandler( $this->Database()->Connection() );

		//
		// Handle many documents.
		//
		if( $do_all )
		{
			//
			// Init local storage.
			//
			$ids = [];

			//
			// Iterate documents.
			//
			foreach( $theDocument as $record )
			{
				//
				// Convert to document.
				//
				if( is_array( $record ) )
					$record = ArangoDocument::createFromArray( $record );

				//
				// Insert document.
				//
				$ids[] = $handler->save( $this->Connection(), $record );
			}

			return $ids;															// ==>

		} // Many documents.

		//
		// Convert to document.
		//
		if( is_array( $theDocument ) )
			$theDocument = ArangoDocument::createFromArray( $theDocument );

		return $handler->save( $this->Connection(), $theDocument );					// ==>

	} // doInsert.


	/*===================================================================================
	 *	doUpdate																		*
	 *==================================================================================*/

	/**
	 * <h4>Update one or more records.</h4>
	 *
	 * We overload the method to instantiate and execute a statement selecting the documents
	 * matching the provided filter and apply the modifications to each one.
	 *
	 * The options parameter is ignored here.
	 *
	 * @param mixed					$theFilter			The selection criteria.
	 * @param array					$theCriteria		The modification criteria.
	 * @param array					$theOptions			Driver native options.
	 * @return int					The number of modified records.
	 *
	 * @uses Database()
	 * @uses ArangoDocumentHandler::update()
	 */
	protected function doUpdate( $theFilter, $theCriteria, $theOptions )
	{
		//
		// Init local storage.
		//
		$do_all = $theOptions[ '$doAll' ];
		$connection = $this->Database()->Connection();
		if( ! count( $theFilter ) )
			$theFilter = [ 'query' => 'FOR r IN @@collection RETURN r',
				'bindVars' => [ '@collection' => $this->collectionName() ] ];

		//
		// Select documents.
		//
		$statement = new ArangoStatement( $connection, $theFilter );
		$cursor = $statement->execute();
		$count = $cursor->getCount();

		//
		// Handle selection.
		//
		if( $count )
		{
			//
			// Instantiate document handler.
			//
			$handler = new ArangoDocumentHandler( $connection );

			//
			// Process selection.
			//
			foreach( $cursor as $document )
			{
				//
				// Update document.
				//
				foreach( $theCriteria as $key => $value )
				{
					//
					// Replace field.
					//
					if( $value !== NULL )
						$document->set( $key, $value );

					//
					// Remove field.
					//
					else
						unset( $document->$key );

				} // Iterating modification criteria.

				//
				// Update document.
				//
				$handler->update( $document );

				//
				// Handle only first.
				//
				if( ! $do_all )
					return 1;														// ==>

			} // Iterating documents.

		} // Non empty selection.

		return $count;																// ==>

	} // doUpdate.


	/*===================================================================================
	 *	doReplace																		*
	 *==================================================================================*/

	/**
	 * <h4>Replace a record.</h4>
	 *
	 * We overload the method to use native objects. We first make the selection, then we
	 * replace the contents of the found documents with the contents of the provided
	 * document.
	 *
	 * When removing the contents of the found documents we ignode the identifier, key and
	 * revision: be sure not to provide these in the replacement document.
	 *
	 * @param mixed					$theFilter			The selection criteria.
	 * @param array					$theDocument		The replacement document.
	 * @param array					$theOptions			Driver native options.
	 * @return int					The number of replaced records.
	 *
	 * @uses Database()
	 * @uses ArangoDocumentHandler::replace()
	 */
	protected function doReplace( $theFilter, $theDocument, $theOptions )
	{
		//
		// Init local storage.
		//
		$do_all = $theOptions[ '$doAll' ];
		$connection = $this->Database()->Connection();
		if( ! count( $theFilter ) )
			$theFilter = [ 'query' => 'FOR r IN @@collection RETURN r',
				'bindVars' => [ '@collection' => $this->collectionName() ] ];

		//
		// Select documents.
		//
		$statement = new ArangoStatement( $connection, $theFilter );
		$cursor = $statement->execute();
		$count = $cursor->getCount();

		//
		// Handle selection.
		//
		if( $count )
		{
			//
			// Instantiate document handler.
			//
			$handler = new ArangoDocumentHandler( $connection );

			//
			// Process selection.
			//
			foreach( $cursor as $document )
			{
				//
				// Remove document contents.
				//
				$fields = array_diff( array_keys( $document->getAll() ), ['_key'] );
				foreach( $fields as $field )
					unset( $document->$field );

				//
				// Update document contents.
				//
				foreach( $theDocument as $key => $value )
					$document->set( $key, $value );

				//
				// Replace document.
				//
				$handler->replace( $document );

				//
				// Handle only first.
				//
				if( ! $do_all )
					return 1;														// ==>

			} // Iterating documents.

		} // Non empty selection.

		return $count;																// ==>

	} // doReplace.


	/*===================================================================================
	 *	doDelete																		*
	 *==================================================================================*/

	/**
	 * <h4>Delete the first or all records.</h4>
	 *
	 * We overload the method to use native objects. We first make the selection, then we
	 * remove one by one the found records.
	 *
	 * @param mixed					$theFilter			The selection criteria.
	 * @param array					$theOptions			Driver native options.
	 * @return int					The number of deleted records.
	 *
	 * @uses Database()
	 * @uses ArangoDocumentHandler::remove()
	 */
	protected function doDelete( $theFilter, $theOptions )
	{
		//
		// Init local storage.
		//
		$do_all = $theOptions[ '$doAll' ];
		$connection = $this->Database()->Connection();
		if( ! count( $theFilter ) )
			$theFilter = [ 'query' => 'FOR r IN @@collection RETURN r',
						   'bindVars' => [ '@collection' => $this->collectionName() ] ];

		//
		// Select documents.
		//
		$statement = new ArangoStatement( $connection, $theFilter );
		$cursor = $statement->execute();
		$count = $cursor->getCount();

		//
		// Handle selection.
		//
		if( $count )
		{
			//
			// Instantiate document handler.
			//
			$handler = new ArangoDocumentHandler( $connection );

			//
			// Process selection.
			//
			foreach( $cursor as $document )
			{
				//
				// Remove document.
				//
				$handler->remove( $document );

				//
				// Handle only first.
				//
				if( ! $do_all )
					return 1;														// ==>

			} // Iterating documents.

		} // Non empty selection.

		return $count;																// ==>

	} // doDelete.


	/*===================================================================================
	 *	doFindByExample																	*
	 *==================================================================================*/

	/**
	 * <h4>Find by example the first or all records.</h4>
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
	 * @param array					$theDocument		The example document.
	 * @param array					$theOptions			Driver native options.
	 * @return Iterator				The found records.
	 *
	 * @uses Database()
	 * @uses Connection()
	 * @uses ArangoDocumentHandler::byExample()
	 */
	protected function doFindByExample( $theDocument, $theOptions )
	{
		//
		// Normalise document.
		//
		if( $theDocument === NULL )
			$theDocument = [];
		elseif( $theDocument instanceof \Milko\PHPLib\Container )
			$theDocument = $theDocument->toArray();

		//
		// Normalise limits.
		//
		if( array_key_exists( '$start', $theOptions ) )
		{
			$theOptions[ 'skip' ] = $theOptions[ '$start' ];
			unset( $theOptions[ '$start' ] );
		}
		if( array_key_exists( '$limit', $theOptions ) )
		{
			$theOptions[ 'limit' ] = $theOptions[ '$limit' ];
			unset( $theOptions[ '$limit' ] );
		}

		//
		// Get collection handler.
		//
		$handler = new ArangoCollectionHandler( $this->Database()->Connection() );

		//
		// Get cursor.
		//
		return $handler->byExample(
				$this->Connection()->getId(), $theDocument, $theOptions );			// ==>

	} // doFindByExample.


	/*===================================================================================
	 *	doFindByQuery																	*
	 *==================================================================================*/

	/**
	 * <h4>Query the collection.</h4>
	 *
	 * We overload this method to create a statement and execute it. The provided query
	 * should be an array with the <tt>query</tt> key.
	 *
	 * The options parameter is ignored here.
	 *
	 * @param array					$theQuery			The selection criteria.
	 * @param array					$theOptions			Collection native options.
	 * @return Iterator				The found records.
	 *
	 * @uses Database()
	 * @uses ArangoStatement::execute()
	 */
	protected function doFindByQuery( $theQuery, $theOptions )
	{
		//
		// Normalise query.
		//
		if( ! count( $theQuery ) )
			$theFilter = [ 'query' => 'FOR r IN @@collection RETURN r',
						   'bindVars' => [ '@collection' => $this->collectionName() ] ];

		//
		// Create statement.
		//
		$statement = new ArangoStatement( $this->Database()->Connection(), $theQuery );

		return $statement->execute();												// ==>

	} // doFindByQuery.


	/*===================================================================================
	 *	doCountByExample																*
	 *==================================================================================*/

	/**
	 * <h4>Return a find by example record count.</h4>
	 *
	 * We overload this method to use the {@link doFindByExample()} method and return the
	 * cursor's count.
	 *
	 * @param array					$theDocument		The example document.
	 * @param array					$theOptions			Driver native options.
	 * @return int					The records count.
	 *
	 * @uses doFindByExample()
	 */
	protected function doCountByExample( $theDocument, $theOptions )
	{
		return $this->doFindByExample( $theDocument, $theOptions )->getCount();		// ==>

	} // doCountByExample.


	/*===================================================================================
	 *	doCountByQuery																	*
	 *==================================================================================*/

	/**
	 * <h4>Return a find by query record count.</h4>
	 *
	 * We overload this method to use the {@link doFindByQuery()} method and return the
	 * cursor's count.
	 *
	 * @param array					$theDocument		The example document.
	 * @param array					$theOptions			Driver native options.
	 * @return int					The records count.
	 *
	 * @uses doFindByQuery()
	 */
	protected function doCountByQuery( $theDocument, $theOptions )
	{
		return $this->doFindByQuery( $theDocument, $theOptions )->getCount();		// ==>

	} // doCountByQuery.


	/*===================================================================================
	 *	doMapReduce																		*
	 *==================================================================================*/

	/**
	 * <h4>Execute an aggregation query.</h4>
	 *
	 * ArangoDB does not have an aggregation framework such as MongoDB, in this class we
	 * simply call the {@link doFindByQuery()} method replacing the pipeline parameter with
	 * the query.
	 *
	 * @param mixed					$thePipeline		The aggregation pipeline.
	 * @param array					$theOptions			Driver native options.
	 * @return Iterator				The found records.
	 *
	 * @uses doFindByQuery()
	 */
	protected function doMapReduce( $thePipeline, $theOptions )
	{
		return $this->doFindByQuery( $thePipeline, $theOptions );					// ==>

	} // doMapReduce.


} // class Collection.


?>
