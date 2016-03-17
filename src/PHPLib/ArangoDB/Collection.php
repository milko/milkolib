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
use triagens\ArangoDb\Cursor as ArangoCursor;
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
	 * @uses Database()
	 * @uses Connection()
	 * @uses triagens\ArangoDb\CollectionHandler::truncate()
	 */
	public function Truncate()
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
	 * @uses Database()
	 * @uses Connection()
	 * @uses triagens\ArangoDb\CollectionHandler::drop()
	 */
	public function Drop()
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
 *							PUBLIC DOCUMENT MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	NewDocument																		*
	 *==================================================================================*/

	/**
	 * <h4>Convert native data to standard document.</h4>
	 *
	 * We overload this method by casting the provided data into an array and instantiating
	 * the expected document.
	 *
	 * @param mixed						$theData			Database native document.
	 * @param string					$theClass			Expected class name.
	 * @return \Milko\PHPLib\Container	Standard document object.
	 *
	 * @uses ClassOffset()
	 * @uses RevisionOffset()
	 */
	public function NewDocument($theData, $theClass = NULL )
	{
		//
		// Convert document to array.
		//
		$document = ( $theData instanceof ArangoDocument )
			? $theData->getAll()
			: (array)$theData;

		//
		// Use provided class name.
		//
		if( $theClass !== NULL )
		{
			$theClass = (string)$theClass;
			$document = new $theClass( $this, $document );
		}

		//
		// Use class in data.
		//
		elseif( array_key_exists( $this->ClassOffset(), $document ) )
		{
			$class = $document[ $this->ClassOffset() ];
			$document = new $class( $this, $document );
		}

		//
		// Instantiate default container.
		//
		else
			$document = new \Milko\PHPLib\Container( $document );

		//
		// Set revision.
		//
		if( $theData instanceof ArangoDocument )
			$document[ $this->RevisionOffset() ] = $theData->getRevision();

		return $document;															// ==>

	} // NewDocument.


	/*===================================================================================
	 *	NewNativeDocument																*
	 *==================================================================================*/

	/**
	 * <h4>Convert a standard document to native data.</h4>
	 *
	 * We overload this method to return an ArangoDocument.
	 *
	 * @param Container				$theDocument		Document to be converted.
	 * @return mixed				Database native object.
	 *
	 * @uses IdOffset()
	 * @uses RevisionOffset()
	 */
	public function NewNativeDocument(\Milko\PHPLib\Container $theDocument )
	{
		//
		// Clone document.
		// We really don't need to do this, but in case an object was provided...
		// Also, we work on a clone, not the original document.
		//
		$document = new \Milko\PHPLib\Container( $theDocument->toArray() );

		//
		// Save and remove revision.
		//
		$rev = $document->manageProperty( $this->RevisionOffset(), FALSE );

		//
		// Compile internal identifier.
		//
		$id = NULL;
		if( (($cid = $this->Connection()->getId()) !== NULL)
		 && (($key = $document[ $this->KeyOffset() ]) !== NULL) )
			$id = "$cid/$key";

		//
		// Create ArangoDB document.
		//
		$document = ArangoDocument::createFromArray( $document->toArray() );

		//
		// Set internal properties.
		//
		if( $id !== NULL )
			$document->setInternalId( $id );
		if( $rev !== NULL )
			$document->setRevision( $rev );

		return $document;															// ==>

	} // NewNativeDocument.


	/*===================================================================================
	 *	NewDocumentHandle																*
	 *==================================================================================*/

	/**
	 * <h4>Convert a document to a document handle.</h4>
	 *
	 * We overload this method to return or compute the handle string.
	 *
	 * Note that if the provided data doesn't feature the {@link KeyOffset()} property, the
	 * method will raise an exception, since it will be impossible to resolve the document.
	 *
	 * @param mixed					$theDocument		Document to reference.
	 * @return mixed				Document handle.
	 * @throws \InvalidArgumentException
	 *
	 * @uses KeyOffset()
	 * @uses Connection()
	 * @uses triagens\ArangoDb\Document::getHandle()
	 */
	public function NewDocumentHandle( $theDocument )
	{
		//
		// Handle handle ;-)
		//
		if( $theDocument instanceof ArangoDocument )
			return $theDocument->getHandle();										// ==>

		//
		// Convert to container.
		//
		$document = new \Milko\PHPLib\Container( (array)$theDocument );

		//
		// Check document key.
		//
		if( ($key = $document[ $this->KeyOffset() ]) !== NULL )
			return $this->Connection()->getName() . '/' . $key;						// ==>

		throw new \InvalidArgumentException (
			"Data is missing the document key." );								// !@! ==>

	} // NewDocumentHandle.



/*=======================================================================================
 *																						*
 *							PUBLIC OFFSET DECLARATION INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	KeyOffset																		*
	 *==================================================================================*/

	/**
	 * <h4>Return the document key offset.</h4>
	 *
	 * We overload this method to use the {@link kTAG_ARANGO_KEY} constant.
	 *
	 * @return string				Document key offset.
	 */
	public function KeyOffset()									{	return kTAG_ARANGO_KEY;	}


	/*===================================================================================
	 *	ClassOffset																		*
	 *==================================================================================*/

	/**
	 * <h4>Return the document class offset.</h4>
	 *
	 * We overload this method to use the {@link kTAG_ARANGO_CLASS} constant.
	 *
	 * @return string				Document class offset.
	 */
	public function ClassOffset()							{	return kTAG_ARANGO_CLASS;	}


	/*===================================================================================
	 *	RevisionOffset																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the document revision offset.</h4>
	 *
	 * We overload this method to use the {@link kTAG_ARANGO_REVISION} constant.
	 *
	 * @return string				Document revision offset.
	 */
	public function RevisionOffset()					{	return kTAG_ARANGO_REVISION;	}



/*=======================================================================================
 *																						*
 *							PUBLIC RECORD MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	RecordCount																		*
	 *==================================================================================*/

	/**
	 * <h4>Count documents.</h4>
	 *
	 * We overload this method to use the
	 * {@link triagens\ArangoDb\CollectionHandler::count()} method.
	 *
	 * @return int					The number of records in the collection.
	 *
	 * @uses Database()
	 * @uses Connection()
	 * @uses triagens\ArangoDb\CollectionHandler::count()
	 */
	public function RecordCount()
	{
		//
		// Get collection handler.
		//
		$handler = new ArangoCollectionHandler( $this->Database()->Connection() );

		return $handler->count( $this->Connection()->getId() );						// ==>

	} // RecordCount.


	/*===================================================================================
	 *	CountByExample																	*
	 *==================================================================================*/

	/**
	 * <h4>Count by example.</h4>
	 *
	 * In this class we overload this method to use the <tt>count()</tt> method of the
	 * Mongo Collection.
	 *
	 * The document is expected as an array, container or native document.
	 *
	 * @param mixed					$theDocument		The example document.
	 * @return int					The found records count.
	 *
	 * @uses Connection()
	 * @uses triagens\ArangoDb\Cursor::count()
	 */
	public function CountByExample( $theDocument = NULL )
	{
		//
		// Convert document to array.
		//
		if( $theDocument === NULL )
			$theDocument = [];
		elseif( $theDocument instanceof \Milko\PHPLib\Container )
			$theDocument = $theDocument->toArray();

		//
		// Get collection handler.
		//
		$handler = new ArangoCollectionHandler( $this->Database()->Connection() );

		return $handler->byExample(
			$this->Connection()->getId(), $theDocument )
			->getCount();													// ==>

	} // CountByExample.


	/*===================================================================================
	 *	CountByQuery																	*
	 *==================================================================================*/

	/**
	 * <h4>Count by example.</h4>
	 *
	 * TWe overload this method by calling {@link CountByExample()}.
	 *
	 * @param mixed					$theQuery			The selection criteria.
	 * @return int					The found records count.
	 *
	 * @uses Database()
	 * @uses collectionName()
	 */
	public function CountByQuery( $theQuery = NULL )
	{
		//
		// Normalise query.
		// Note that we check both for null and empty array.
		//
		if( ! $theQuery )
			$theQuery =
				[ 'query' => 'FOR r IN @@collection RETURN r',
				'bindVars' => [ '@collection' => $this->collectionName() ] ];

		//
		// Create statement.
		//
		$statement = new ArangoStatement( $this->Database()->Connection(), $theQuery );

		return $statement->execute()->getCount();									// ==>

	} // CountByQuery.


	/*===================================================================================
	 *	MapReduce																		*
	 *==================================================================================*/

	/**
	 * <h4>Execute an aggregation query.</h4>
	 *
	 * ArangoDB does not have an aggregation framework such as MongoDB, in this class we
	 * simply call the {@link doFindByQuery()} method replacing the pipeline parameter with
	 * the query.
	 *
	 * @param mixed					$thePipeline		The aggregation pipeline.
	 * @param array					$theOptions			Query options.
	 * @return array				The result set.
	 *
	 * @uses FindByQuery()
	 */
	public function MapReduce( $thePipeline, $theOptions = [] )
	{
		return $this->FindByQuery( $thePipeline, $theOptions );						// ==>

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
	 * This method will return a {@link ArangoCollection} object.
	 *
	 * We first instantiate a collection handler, then we check whether the collection
	 * exists, in which case we return it; if not, we create a new one and return it.
	 *
	 * The options parameter is ignored here.
	 *
	 * @param string				$theCollection		Collection name.
	 * @param array					$theOptions			Driver native options.
	 * @return mixed				Native collection object.
	 *
	 * @uses Database()
	 * @uses triagens\ArangoDb\CollectionHandler::has()
	 * @uses triagens\ArangoDb\CollectionHandler::get()
	 * @uses triagens\ArangoDb\CollectionHandler::create()
	 */
	protected function collectionNew( $theCollection, $theOptions = [] )
	{
		//
		// Get collection handler.
		//
		$handler = new ArangoCollectionHandler( $this->Database()->Connection() );

		//
		// Return existing collection.
		//
		if( $handler->has( $theCollection ) )
			return $handler->get( $theCollection );									// ==>

		return $handler->create( $theCollection );									// ==>

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
	 * @uses triagens\ArangoDb\Collection::getName()
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
	 * @param array					$theOptions			Insert options.
	 * @return mixed				The document's unique identifier(s).
	 *
	 * @uses Database()
	 * @uses Connection()
	 * @uses NewNativeDocument()
	 * @uses triagens\ArangoDb\Document::createFromArray()
	 * @uses triagens\ArangoDb\DocumentHandler::save()
	 */
	protected function doInsert( $theDocument, array $theOptions )
	{
		//
		// Init local storage.
		//
		$handler = new ArangoDocumentHandler( $this->Database()->Connection() );

		//
		// Handle many documents.
		//
		if( $theOptions[ kTOKEN_OPT_MANY ] )
		{
			//
			// Init local storage.
			//
			$ids = [];

			//
			// Iterate documents.
			//
			foreach( $theDocument as $document )
			{
				//
				// Convert to document.
				//
				$record = ( $document instanceof \Milko\PHPLib\Container )
						? $this->NewNativeDocument( $document )
						: ArangoDocument::createFromArray( (array)$document );

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
		$record = ( $theDocument instanceof \Milko\PHPLib\Container )
				? $this->NewNativeDocument( $theDocument )
				: ArangoDocument::createFromArray( (array)$theDocument );

		return $handler->save( $this->Connection(), $record );						// ==>

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
	 * @param mixed					$theCriteria		The modification criteria.
	 * @param array					$theOptions			Update options.
	 * @return int					The number of modified records.
	 *
	 * @uses Database()
	 * @uses collectionName()
	 * @uses triagens\ArangoDb\DocumentHandler::update()
	 */
	protected function doUpdate( $theFilter, $theCriteria, array $theOptions )
	{
		//
		// Normalise query.
		// Note that we check both for null and empty array.
		//
		if( ! $theFilter )
			$theFilter =
				[ 'query' => 'FOR r IN @@collection RETURN r',
					'bindVars' => [ '@collection' => $this->collectionName() ] ];

		//
		// Select documents.
		//
		$statement = new ArangoStatement( $this->Database()->Connection(), $theFilter );
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
			$handler = new ArangoDocumentHandler( $this->Database()->Connection() );

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
				if( ! $theOptions[ kTOKEN_OPT_MANY ] )
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
	 * @param mixed					$theDocument		The replacement document.
	 * @param array					$theOptions			Replace options.
	 * @return int					The number of replaced records.
	 *
	 * @uses Database()
	 * @uses KeyOffset()
	 * @uses collectionName()
	 * @uses triagens\ArangoDb\DocumentHandler::replace()
	 */
	protected function doReplace( $theFilter, $theDocument, array $theOptions )
	{
		//
		// Normalise query.
		// Note that we check both for null and empty array.
		//
		if( ! $theFilter )
			$theFilter =
				[ 'query' => 'FOR r IN @@collection RETURN r',
					'bindVars' => [ '@collection' => $this->collectionName() ] ];

		//
		// Select documents.
		//
		$statement = new ArangoStatement( $this->Database()->Connection(), $theFilter );
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
			$handler = new ArangoDocumentHandler( $this->Database()->Connection() );

			//
			// Process selection.
			//
			foreach( $cursor as $document )
			{
				//
				// Get field names except key and revision.
				//
				$fields =
					array_diff(
						array_keys( $document->getAll() ),
						[$this->KeyOffset(), $this->RevisionOffset()] );

				//
				// Remove document contents.
				//
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
				if( ! $theOptions[ kTOKEN_OPT_MANY ] )
					return 1;														// ==>

			} // Iterating documents.

		} // Non empty selection.

		return $count;																// ==>

	} // doReplace.


	/*===================================================================================
	 *	doDeleteByKey																	*
	 *==================================================================================*/

	/**
	 * <h4>Delete the first or all records by key.</h4>
	 *
	 * We overload this method to use the {@link FindByKey()} method and pass the resulting
	 * cursor to the protected {@link doDeleteByCursor()}.
	 *
	 * @param mixed					$theKey				The document key(s).
	 * @param array					$theOptions			Find options.
	 * @return int					The number of deleted records.
	 *
	 * @uses Database()
	 * @uses FindByKey()
	 * @uses doDeleteByCursor()
	 * @uses triagens\ArangoDb\DocumentHandler::remove()
	 * @see kTOKEN_OPT_FORMAT
	 * @see kTOKEN_OPT_FORMAT_NATIVE
	 */
	protected function doDeleteByKey( $theKey, array $theOptions )
	{
		//
		// Normalise options.
		//
		$theOptions[ kTOKEN_OPT_FORMAT ] = kTOKEN_OPT_FORMAT_NATIVE;

		//
		// Make selection.
		//
		$cursor = $this->FindByKey( $theKey, $theOptions );
		if( $cursor instanceof ArangoCursor )
			return $this->doDeleteByCursor( $cursor, $theOptions );					// ==>
		if( $cursor !== NULL )
		{
			//
			// Instantiate document handler.
			//
			$handler = new ArangoDocumentHandler( $this->Database()->Connection() );

			//
			// Remove document.
			//
			$handler->remove( $cursor );

			return 1;																// ==>
		}

		return 0;																	// ==>

	} // doDeleteByKey.


	/*===================================================================================
	 *	doDeleteByExample																*
	 *==================================================================================*/

	/**
	 * <h4>Delete the first or all records by example.</h4>
	 *
	 * We overload this method to use the {@link FindByExample()} method and pass the
	 * resulting cursor to the protected {@link doDeleteByCursor()}.
	 *
	 * @param mixed					$theDocument		The example document.
	 * @param array					$theOptions			Delete options.
	 * @return int					The number of deleted records.
	 *
	 * @uses FindByExample()
	 * @uses doDeleteByCursor()
	 * @see kTOKEN_OPT_FORMAT
	 * @see kTOKEN_OPT_FORMAT_NATIVE
	 */
	protected function doDeleteByExample( $theDocument, array $theOptions )
	{
		//
		// Normalise options.
		//
		$theOptions[ kTOKEN_OPT_FORMAT ] = kTOKEN_OPT_FORMAT_NATIVE;

		return
			$this->doDeleteByCursor(
				$this->FindByExample( $theDocument, $theOptions ),
				$theOptions );														// ==>

	} // doDeleteByExample.


	/*===================================================================================
	 *	doDeleteByQuery																	*
	 *==================================================================================*/

	/**
	 * <h4>Delete the first or all records by query.</h4>
	 *
	 * We overload this method to use the {@link FindByExample()} method and pass the
	 * resulting cursor to the protected {@link doDeleteByCursor()}.
	 *
	 * Note that you can delete using AQL, try to use the dedicated methods, so the workflow
	 * will be more consistent.
	 *
	 * @param mixed					$theQuery			The selection criteria.
	 * @param array					$theOptions			Delete options.
	 * @return int					The number of deleted records.
	 *
	 * @uses FindByQuery()
	 * @uses doDeleteByCursor()
	 * @see kTOKEN_OPT_FORMAT
	 * @see kTOKEN_OPT_FORMAT_NATIVE
	 */
	protected function doDeleteByQuery( $theQuery, array $theOptions )
	{
		//
		// Normalise options.
		//
		$theOptions[ kTOKEN_OPT_FORMAT ] = kTOKEN_OPT_FORMAT_NATIVE;

		return
			$this->doDeleteByCursor(
				$this->FindByQuery( $theQuery, $theOptions ),
				$theOptions );														// ==>

	} // doDeleteByQuery.


	/*===================================================================================
	 *	doDeleteByCursor																*
	 *==================================================================================*/

	/**
	 * <h4>Delete the the documents from a cursor.</h4>
	 *
	 * This method will delete the first or all documents contained in the provided cursor,
	 * the method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theCursor</b>: The cursor.
	 *	<li><b>$theOptions</b>: An array of options:
	 * 	 <ul>
	 * 		<li><b>{@link kTOKEN_OPT_MANY}</b>: This option determines whether to delete
	 *			only the first selected record or all:
	 * 		 <ul>
	 * 			<li><tt>TRUE</tt>: Delete the whole selection.
	 * 			<li><tt>FALSE</tt>: Delete only the first selected record.
	 * 		 </ul>
	 * 	 </ul>
	 * </ul>
	 *
	 * The method will return the number of deleted records.
	 *
	 * @param mixed					$theCursor			The cursor.
	 * @param array					$theOptions			Delete options.
	 * @return int					The number of deleted records.
	 *
	 * @uses Database()
	 * @uses collectionName()
	 * @uses triagens\ArangoDb\DocumentHandler::remove()
	 */
	protected function doDeleteByCursor( ArangoCursor $theCursor, array $theOptions )
	{
		//
		// Get count.
		//
		$count = $theCursor->getCount();

		//
		// Handle selection.
		//
		if( $count )
		{
			//
			// Instantiate document handler.
			//
			$handler = new ArangoDocumentHandler( $this->Database()->Connection() );

			//
			// Process selection.
			//
			foreach( $theCursor as $document )
			{
				//
				// Remove document.
				//
				$handler->remove( $document );

				//
				// Handle only first.
				//
				if( ! $theOptions[ kTOKEN_OPT_MANY ] )
					return 1;														// ==>

			} // Iterating documents.

		} // Non empty selection.

		return $count;																// ==>

	} // doDeleteByCursor.


	/*===================================================================================
	 *	doFindById																		*
	 *==================================================================================*/

	/**
	 * <h4>Find by ID.</h4>
	 *
	 * We implement the method by using the <tt>lookupByKeys()</tt> method.
	 *
	 * @param mixed					$theKey				The document identifier.
	 * @param array					$theOptions			Delete options.
	 * @return mixed				The found document(s).
	 * @throws \InvalidArgumentException
	 *
	 * @uses Connection()
	 * @uses ClassOffset()
	 * @uses NewDocument()
	 * @uses triagens\ArangoDb\DocumentHandler::getById()
	 * @uses triagens\ArangoDb\CollectionHandler::lookupByKeys()
	 */
	protected function doFindByKey($theKey, array $theOptions )
	{
		//
		// Handle list.
		//
		if( $theOptions[ kTOKEN_OPT_MANY ] )
		{
			//
			// Instantiate collection handler.
			//
			$handler = new ArangoCollectionHandler( $this->Database()->Connection() );

			//
			// Get documents.
			//
			$result =
				$handler->lookupByKeys( $this->Connection()->getID(), (array)$theKey );

			//
			// Format result.
			//
			switch( $theOptions[ kTOKEN_OPT_FORMAT ] )
			{
				case kTOKEN_OPT_FORMAT_NATIVE:
					return $result;													// ==>

				case kTOKEN_OPT_FORMAT_STANDARD:
				case kTOKEN_OPT_FORMAT_HANDLE:
					return $this->formatCursor(
						$result, $theOptions[ kTOKEN_OPT_FORMAT ] );				// ==>
			}

			//
			// Invalid format code.
			//
			throw new \InvalidArgumentException (
				"Invalid conversion format code." );							// !@! ==>

		} // List.

		//
		// Instantiate document handler.
		//
		$handler = new ArangoDocumentHandler( $this->Database()->Connection() );

		//
		// Get document.
		//
		try
		{
			//
			// Find document.
			//
			$result = $handler->getById( $this->Connection()->getId(), $theKey );

			//
			// Format result.
			//
			switch( $theOptions[ kTOKEN_OPT_FORMAT ] )
			{
				case kTOKEN_OPT_FORMAT_NATIVE:
					return $result;													// ==>

				case kTOKEN_OPT_FORMAT_STANDARD:
					return $this->NewDocument( $result );							// ==>

				case kTOKEN_OPT_FORMAT_HANDLE:
					return $this->NewDocumentHandle( $result );						// ==>
			}
		}
		catch( ArangoServerException $error )
		{
			//
			// Handle not found.
			//
			if( $error->getCode() == 404 )
				return NULL;														// ==>

			throw $error;														// !@! ==>
		}

		//
		// Invalid format code.
		//
		throw new \InvalidArgumentException (
			"Invalid conversion format code." );								// !@! ==>

	} // doFindById.


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
	 * @return mixed				The found records.
	 *
	 * @uses Database()
	 * @uses Connection()
	 * @uses triagens\ArangoDb\CollectionHandler::byExample()
	 */
	protected function doFindByExample( $theDocument, array $theOptions )
	{
		//
		// Init local storage.
		//
		$options = [];

		//
		// Normalise document.
		//
		if( $theDocument === NULL )
			$theDocument = [];
		elseif( $theDocument instanceof \Milko\PHPLib\Container )
			$theDocument = $theDocument->toArray();

		//
		// Convert to native options.
		//
		if( array_key_exists( kTOKEN_OPT_SKIP, $theOptions ) )
			$options[ 'skip' ] = $theOptions[ kTOKEN_OPT_SKIP ];
		if( array_key_exists( kTOKEN_OPT_LIMIT, $theOptions ) )
			$options[ 'limit' ] = $theOptions[ kTOKEN_OPT_LIMIT ];

		//
		// Get collection handler.
		//
		$handler = new ArangoCollectionHandler( $this->Database()->Connection() );

		//
		// Select documents.
		//
		$cursor =
			$handler->byExample( $this->Connection()->getId(), $theDocument, $options );

		//
		// Handle native result.
		//
		if( $theOptions[ kTOKEN_OPT_FORMAT ] == kTOKEN_OPT_FORMAT_NATIVE )
			return $cursor;															// ==>

		return
			$this->formatCursor(
				$handler->byExample(
					$this->Connection()->getId(), $theDocument, $options ),
				$theOptions[ kTOKEN_OPT_FORMAT ] );									// ==>

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
	 * <em>The options parameters {@link kTOKEN_OPT_SKIP} and {@link kTOKEN_OPT_LIMIT} are
	 * ignored in this method: you must set them directly into the query</em>.
	 *
	 * @param array					$theQuery			The selection criteria.
	 * @param array					$theOptions			Collection native options.
	 * @return mixed				The found records.
	 *
	 * @uses Database()
	 * @uses formatCursor()
	 * @uses triagens\ArangoDb\Statement::execute()
	 */
	protected function doFindByQuery( $theQuery, array $theOptions )
	{
		//
		// Normalise query.
		//
		if( ! count( $theQuery ) )
			$theQuery = [ 'query' => 'FOR r IN @@collection RETURN r',
						  'bindVars' => [ '@collection' => $this->collectionName() ] ];

		//
		// Create statement.
		//
		$statement = new ArangoStatement( $this->Database()->Connection(), $theQuery );

		return $this->formatCursor(
			$statement->execute(), $theOptions[ kTOKEN_OPT_FORMAT ] );				// ==>

	} // doFindByQuery.



} // class Collection.


?>
