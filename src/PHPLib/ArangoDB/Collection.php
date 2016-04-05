<?php

/**
 * Collection.php
 *
 * This file contains the definition of the {@link Collection} class.
 */

namespace Milko\PHPLib\ArangoDB;

use Milko\PHPLib\Server;
use triagens\ArangoDb\Database as ArangoDatabase;
use triagens\ArangoDb\Collection as ArangoCollection;
use triagens\ArangoDb\CollectionHandler as ArangoCollectionHandler;
use triagens\ArangoDb\Document;
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
	 * @uses collectionName()
	 * @uses triagens\ArangoDb\CollectionHandler::drop()
	 */
	public function Drop()
	{
		//
		// Check collection.
		//
		if( $this->Connection()->getId() !== NULL )
		{
			//
			// Instantiate collection handler.
			//
			$handler = new ArangoCollectionHandler( $this->Database()->Connection() );

			//
			// Drop collection.
			//
			$handler->drop( $this->collectionName() );
		}

	} // Drop.



/*=======================================================================================
 *																						*
 *							PUBLIC DOCUMENT MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	NewNativeDocument																*
	 *==================================================================================*/

	/**
	 * <h4>Return a native database document.</h4>
	 *
	 * We overload this method to return the eventual {@link triagens\ArangoDb\Document}
	 * provided in the parameter.
	 *
	 * @param mixed					$theData			Document data.
	 * @return mixed				Database native object.
	 */
	public function NewNativeDocument( $theData )
	{
		//
		// Handle native document.
		//
		if( $theData instanceof ArangoDocument )
			return $theData;														// ==>

		return parent::NewNativeDocument( $theData );								// ==>

	} // NewNativeDocument.


	/*===================================================================================
	 *	NewDocumentArray																*
	 *==================================================================================*/

	/**
	 * <h4>Return an array from a document.</h4>
	 *
	 * We overload this method to handle {@link triagens\ArangoDb\Document} instances.
	 *
	 * @param mixed					$theData			Document data.
	 * @return array				Document as array.
	 *
	 * @uses KeyOffset()
	 * @uses RevisionOffset()
	 */
	public function NewDocumentArray( $theData )
	{
		//
		// Convert ArangoDocument to array.
		//
		if( $theData instanceof ArangoDocument )
		{
			//
			// Get document data.
			//
			$document = $theData->getAll();

			//
			// Set key.
			//
			if( ($key = $theData->getKey()) !== NULL )
				$document[ $this->KeyOffset() ] = $key;

			//
			// Set revision.
			//
			if( ($revision = $theData->getRevision()) !== NULL )
				$document[ $this->RevisionOffset() ] = $revision;

			return $document;														// ==>

		} // ArangoDocument.

		return parent::NewDocumentArray( $theData );								// ==>
		
	} // NewDocumentArray.


	/*===================================================================================
	 *	NewDocumentHandle																*
	 *==================================================================================*/

	/**
	 * <h4>Convert a document to a document handle.</h4>
	 *
	 * In this class a handle is the collection name, a slash and the document key.
	 *
	 * We first check whether the provided data is a {@link triagens\ArangoDb\Document}, in
	 * that case we get the handle from it; if the operation fails, the method will raise an
	 * exception.
	 *
	 * For all other provided document types, the method will return a computed handle
	 * <em>assuming the current collection holds the document</em>.
	 *
	 * If the provided data doesn't feature the {@link KeyOffset()} property, the method
	 * will raise an exception.
	 *
	 * @param mixed					$theData			Document to reference.
	 * @return mixed				Document handle.
	 * @throws \InvalidArgumentException
	 *
	 * @uses triagens\ArangoDb\Document::getHandle()
	 */
	public function NewDocumentHandle( $theData )
	{
		//
		// Extract handle.
		//
		if( $theData instanceof ArangoDocument )
		{
			//
			// Check handle.
			//
			if( ($handle = $theData->getHandle()) !== NULL )
				return $handle;														// ==>

			throw new \InvalidArgumentException (
				"Unable to retrieve handle from native document." );			// !@! ==>

		} // ArangoDocument.

		return parent::NewDocumentHandle( $theData );								// ==>

	} // NewDocumentHandle.


	/*===================================================================================
	 *	NewDocumentKey																	*
	 *==================================================================================*/

	/**
	 * <h4>Return document key.</h4>
	 *
	 * We overload this method to handle {@link triagens\ArangoDb\Document} instances.
	 *
	 * @param mixed					$theData			Document data.
	 * @return mixed				Document key.
	 * @throws \InvalidArgumentException
	 *
	 * @uses triagens\ArangoDb\Document::getKey()
	 */
	public function NewDocumentKey( $theData )
	{
		//
		// Handle ArangoDocument.
		//
		if( $theData instanceof ArangoDocument )
		{
			//
			// Check key.
			//
			if( ($key = $theData->getKey()) !== NULL )
				return $key;														// ==>

			throw new \InvalidArgumentException (
				"Data is missing the document key." );							// !@! ==>
			
		} // ArangoDocument.
		
		return parent::NewDocumentKey( $theData );									// ==>

	} // NewDocumentKey.


	/*===================================================================================
	 *	NewHandle																		*
	 *==================================================================================*/

	/**
	 * <h4>Return a document handle.</h4>
	 *
	 * We implement this method to return a string concatenating the collection name and the
	 * document key, separated by a slash.
	 *
	 * The method assumes the provided key is valid.
	 *
	 * @param mixed					$theKey				Document key.
	 * @return mixed				Document handle.
	 *
	 * @uses collectionName()
	 */
	public function NewHandle( $theKey )
	{
		return $this->collectionName() . '/' . $theKey;								// ==>

	} // NewHandle.



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
	 *
	 * @see kTAG_ARANGO_KEY
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
	 *
	 * @see kTAG_ARANGO_CLASS
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
	 *
	 * @see kTAG_ARANGO_REVISION
	 */
	public function RevisionOffset()					{	return kTAG_ARANGO_REVISION;	}



/*=======================================================================================
 *																						*
 *							PUBLIC DOCUMENT MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	FindKey																			*
	 *==================================================================================*/

	/**
	 * <h4>Find a document.</h4>
	 *
	 * We implement the method by using the
	 * {@link triagens\ArangoDb\CollectionHandler::getById()} method.
	 *
	 * @param mixed					$theKey				The document key.
	 * @return Document				The found document or <tt>NULL</tt>.
	 *
	 * @uses Database()
	 * @uses collectionName()
	 * @uses triagens\ArangoDb\DocumentHandler::getById()
	 */
	public function FindKey($theKey )
	{
		//
		// Instantiate document handler.
		//
		$handler = new ArangoDocumentHandler( $this->Database()->Connection() );

		//
		// Try finding the document.
		//
		try
		{
			return
				$this->NewDocument(
					$handler->getById( $this->collectionName(), $theKey ) );		// ==>
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

	} // FindKey.


	/*===================================================================================
	 *	FindHandle																		*
	 *==================================================================================*/

	/**
	 * <h4>Find a document by handle.</h4>
	 *
	 * We implement this method by calling the {@link FindKey()} method of the handle's
	 * collection.
	 *
	 * @param mixed					$theHandle			The document handle.
	 * @return Document				The found document or <tt>NULL</tt>.
	 *
	 * @uses FindKey()
	 */
	public function FindHandle( $theHandle )
	{
		//
		// Decompose handle.
		//
		$handle = explode( '/', $theHandle );

		return
			$this->Database()->RetrieveCollection(
				$handle[ 0 ], Server::kFLAG_ASSERT )
					->FindKey( $handle[ 1 ] );										// ==>

	} // FindHandle.



/*=======================================================================================
 *																						*
 *								PUBLIC RECORD COUNT INTERFACE							*
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
	 * @uses collectionName()
	 * @uses triagens\ArangoDb\CollectionHandler::count()
	 */
	public function RecordCount()
	{
		//
		// Get collection handler.
		//
		$handler = new ArangoCollectionHandler( $this->Database()->Connection() );

		return $handler->count( $this->collectionName() );							// ==>

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
	 * @uses Database()
	 * @uses collectionName()
	 * @uses NewNativeDocument()
	 * @uses triagens\ArangoDb\CollectionHandler::byExample()
	 * @uses triagens\ArangoDb\Cursor::getCount()
	 */
	public function CountByExample( $theDocument = NULL )
	{
		//
		// Convert document to ArangoDocument.
		//
		$document = $this->NewNativeDocument( $theDocument );

		//
		// Get collection handler.
		//
		$handler = new ArangoCollectionHandler( $this->Database()->Connection() );

		return
			$handler->byExample(
				$this->collectionName(), $document )
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
	 * @uses triagens\ArangoDb\Statement::execute()
	 * @uses triagens\ArangoDb\Cursor::getCount()
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



/*=======================================================================================
 *																						*
 *							PUBLIC AGGREGATION FRAMEWORK INTERFACE						*
 *																						*
 *======================================================================================*/



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
	protected function collectionNew( $theCollection, $theOptions = NULL )
	{
		//
		// Init options.
		//
		if( $theOptions === NULL )
			$theOptions = [];

		//
		// Get collection handler.
		//
		$handler = new ArangoCollectionHandler( $this->Database()->Connection() );

		//
		// Return existing collection.
		//
		if( $handler->has( $theCollection ) )
			return $handler->get( $theCollection );									// ==>

		return $handler->get( $handler->create( $theCollection, $theOptions ) );	// ==>

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
	protected function collectionName()
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
	 * <h4>Insert a document.</h4>
	 *
	 * We implement this method to use the {@link triagens\ArangoDb\DocumentHandler::save()}
	 * method.
	 *
	 * @param mixed					$theDocument		The document to be inserted.
	 * @return mixed				The inserted document's key.
	 *
	 * @uses Database()
	 * @uses Connection()
	 * @uses triagens\ArangoDb\DocumentHandler::save()
	 */
	protected function doInsert( $theDocument )
	{
		//
		// Instantiate handler.
		//
		$handler = new ArangoDocumentHandler( $this->Database()->Connection() );

		return $handler->save( $this->Connection(), $theDocument );					// ==>

	} // doInsert.


	/*===================================================================================
	 *	doInsertBulk																	*
	 *==================================================================================*/

	/**
	 * <h4>Insert a list of documents.</h4>
	 *
	 * We overload this method to iterate the provided list and call the
	 * {@link triagens\ArangoDb\DocumentHandler::save()} method on each document.
	 *
	 * @param array					$theList			The documents list.
	 * @return array				The document keys.
	 *
	 * @uses Database()
	 * @uses Connection()
	 * @uses NewNativeDocument()
	 * @uses triagens\ArangoDb\DocumentHandler::save()
	 */
	protected function doInsertBulk( array $theList )
	{
		//
		// Init local storage.
		//
		$ids = [];
		$handler = new ArangoDocumentHandler( $this->Database()->Connection() );

		//
		// Iterate documents.
		//
		foreach( $theList as $document )
			$ids[] =
				$handler->save( $this->Connection(),
								$this->NewNativeDocument( $document ) );

		return $ids;																// ==>

	} // doInsertBulk.



/*=======================================================================================
 *																						*
 *							PROTECTED DOCUMENT DELETE INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	doDelete																		*
	 *==================================================================================*/

	/**
	 * <h4>Delete a document.</h4>
	 *
	 * We implement this method to use the
	 * {@link triagens\ArangoDb\DocumentHandler::removeById()} method.
	 *
	 * If the document lacks its key, the method will raise an exception.
	 *
	 * @param mixed					$theDocument		The document to be deleted.
	 * @return int					The number of deleted documents.
	 * @throws \InvalidArgumentException
	 *
	 * @uses Database()
	 * @uses collectionName()
	 * @uses triagens\ArangoDb\DocumentHandler::removeById()
	 */
	protected function doDelete( $theDocument )
	{
		//
		// Check document key.
		//
		if( ($id = $theDocument->getKey()) !== NULL )
		{
			//
			// Instantiate document handler.
			//
			$handler = new ArangoDocumentHandler( $this->Database()->Connection() );

			//
			// Remove document.
			//
			try
			{
				//
				// Try to delete document.
				//
				$handler->removeById(
					$this->collectionName(), $id, $theDocument->getRevision() );

				return 1;															// ==>
			}
			catch( ArangoServerException $error )
			{
				//
				// Handle not found.
				//
				if( $error->getCode() != 404 )
					throw $error;												// !@! ==>
			}

			return 0;																// ==>

		} // Has key.

		throw new \InvalidArgumentException (
			"Document is missing its key." );									// !@! ==>

	} // doDelete.


	/*===================================================================================
	 *	doDeleteByKey																	*
	 *==================================================================================*/

	/**
	 * <h4>Delete the first or all records by key.</h4>
	 *
	 * We overload this method to use the
	 * {@link triagens\ArangoDb\CollectionHandler::removeByKeys()} method.
	 *
	 * @param mixed					$theKey				The document key(s).
	 * @param array					$theOptions			Find options.
	 * @return int					The number of deleted documents.
	 *
	 * @uses Database()
	 * @uses collectionName()
	 * @uses triagens\ArangoDb\DocumentHandler::removeByKeys()
	 */
	protected function doDeleteByKey( $theKey, array $theOptions )
	{
		//
		// Normalise keys.
		//
		if( ! $theOptions[ kTOKEN_OPT_MANY ] )
			$theKey = [ $theKey ];
		else
			$theKey = (array)$theKey;

		//
		// Get collection handler.
		//
		$collectionHandler = new ArangoCollectionHandler( $this->Database()->Connection() );

		//
		// Remove by keys.
		//
		$result =
			$collectionHandler->removeByKeys(
				$this->collectionName(), $theKey );

		return $result[ 'removed' ];												// ==>

	} // doDeleteByKey.


	/*===================================================================================
	 *	doDeleteByExample																*
	 *==================================================================================*/

	/**
	 * <h4>Delete the first or all records by example.</h4>
	 *
	 * We overload this method to use the
	 * {@link triagens\ArangoDb\CollectionHandler::removeByExample()} method.
	 *
	 * @param mixed					$theDocument		The example document.
	 * @param array					$theOptions			Delete options.
	 * @return int					The number of deleted documents.
	 *
	 * @uses Database()
	 * @uses collectionName()
	 * @uses NewNativeDocument()
	 * @uses triagens\ArangoDb\CollectionHandler::removeByExample()
	 * @see kTOKEN_OPT_MANY
	 */
	protected function doDeleteByExample( $theDocument, array $theOptions )
	{
		//
		// Set native options.
		//
		$options = ( $theOptions[ kTOKEN_OPT_MANY ] )
				 ? []
				 : [ "limit" => 1 ];

		//
		// Convert to native document.
		//
		$document = $this->NewNativeDocument( $theDocument );

		//
		// Get collection handler.
		//
		$collectionHandler = new ArangoCollectionHandler( $this->Database()->Connection() );

		return
			$collectionHandler->removeByExample(
				$this->collectionName(), $document, $options );						// ==>

	} // doDeleteByExample.


	/*===================================================================================
	 *	doDeleteByQuery																	*
	 *==================================================================================*/

	/**
	 * <h4>Delete the first or all records by query.</h4>
	 *
	 * We overload this method to perform a selection query and delete the first or all
	 * selected documents by using the
	 * {@link triagens\ArangoDb\CollectionHandler::removeByKeys()} method.
	 *
	 * @param mixed					$theQuery			The selection criteria.
	 * @param array					$theOptions			Delete options.
	 * @return int					The number of deleted documents.
	 *
	 * @uses Database()
	 * @uses collectionName()
	 * @uses triagens\ArangoDb\CollectionHandler::removeByKeys()
	 * @see kTOKEN_OPT_MANY
	 */
	protected function doDeleteByQuery( $theQuery, array $theOptions )
	{
		//
		// Perform query.
		//
		$statement = new ArangoStatement( $this->Database()->Connection(), $theQuery );
		$cursor = $statement->execute();

		//
		// Handle empty result.
		//
		if( ! $cursor->getCount() )
			return 0;																// ==>

		//
		// Collect keys.
		//
		$keys = [];
		foreach( $cursor as $document )
		{
			$keys[] = $document->getKey();
			if( ! $theOptions[ kTOKEN_OPT_MANY ] )
				break;															// =>
		}

		//
		// Get collection handler.
		//
		$collectionHandler =
			new ArangoCollectionHandler( $this->Database()->Connection() );

		//
		// Remove by keys.
		//
		$result = $collectionHandler->removeByKeys( $this->collectionName(), $keys );

		return $result[ 'removed' ];												// ==>

	} // doDeleteByQuery.



/*=======================================================================================
 *																						*
 *							PROTECTED UPDATE MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	doUpdate																		*
	 *==================================================================================*/

	/**
	 * <h4>Update one or more records.</h4>
	 *
	 * We overload the method to instantiate and execute a statement selecting the documents
	 * matching the provided filter and apply the modifications to each one.
	 *
	 * @param mixed					$theFilter			The selection criteria.
	 * @param mixed					$theCriteria		The modification criteria.
	 * @param array					$theOptions			Update options.
	 * @return int					The number of modified records.
	 *
	 * @uses Database()
	 * @uses collectionName()
	 * @uses triagens\ArangoDb\Cursor::getCount()
	 * @uses triagens\ArangoDb\Statement::execute()
	 * @uses triagens\ArangoDb\DocumentHandler::set()
	 * @uses triagens\ArangoDb\DocumentHandler::update()
	 * @see kTOKEN_OPT_MANY
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
	 * We overload this method to read the document from the collection, matching its key,
	 * then replacing its contents with the provided document and replacing the document in
	 * the collection.
	 *
	 * If the provided document doesn't have its key ({@link KeyOffset()}), the method will
	 * not perform the replacement and return <tt>0</tt>.
	 *
	 * @param mixed					$theDocument		The replacement document.
	 * @return int					The number of replaced records.
	 *
	 * @uses Database()
	 * @uses collectionName()
	 * @uses KeyOffset()
	 * @uses RevisionOffset()
	 * @uses triagens\ArangoDb\DocumentHandler::replace()
	 * @uses triagens\ArangoDb\DocumentHandler::getById()
	 */
	protected function doReplace( $theDocument )
	{
		//
		// Assert document key.
		//
		if( ($key = $theDocument->getKey()) !== NULL )
		{
			//
			// Get document.
			//
			try
			{
				//
				// Instantiate document handler.
				//
				$handler = new ArangoDocumentHandler( $this->Database()->Connection() );

				//
				// Find document.
				//
				$found = $handler->getById( $this->collectionName(), $key );

				//
				// Reset document properties.
				//
				$properties = array_keys( $found->getAll() );
				$properties = array_diff(
					$properties, [ $this->KeyOffset(), $this->RevisionOffset() ] );
				foreach( $properties as $property )
					unset( $found->$property );

				//
				// Set document properties.
				//
				$properties = array_keys( $theDocument->getAll() );
				$properties = array_diff(
					$properties, [ $this->KeyOffset(), $this->RevisionOffset() ] );
				foreach( $properties as $property )
					$found->set( $property, $theDocument->$property );

				//
				// Replace document.
				//
				$handler->replace( $found );

				return 1;															// ==>
			}
			catch( ArangoServerException $error )
			{
				//
				// Handle not found.
				//
				if( $error->getCode() != 404 )
					throw $error;												// !@! ==>
			}

		} // Document has key.

		return 0;																	// ==>

	} // doReplace.



/*=======================================================================================
 *																						*
 *							PROTECTED QUERY MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	doFindKey																		*
	 *==================================================================================*/

	/**
	 * <h4>Find by key.</h4>
	 *
	 * We implement the method by using the
	 * {@link triagens\ArangoDb\CollectionHandler::lookupByKeys()} method if the
	 * {@link kTOKEN_OPT_MANY} option is set; the
	 * {@link triagens\ArangoDb\CollectionHandler::getById()} method if not.
	 *
	 * @param mixed					$theKey				The document key(s).
	 * @param array					$theOptions			Delete options.
	 * @return mixed				The found document(s).
	 *
	 * @uses Database()
	 * @uses collectionName()
	 * @uses triagens\ArangoDb\DocumentHandler::getById()
	 * @uses triagens\ArangoDb\CollectionHandler::lookupByKeys()
	 * @see kTOKEN_OPT_MANY
	 */
	protected function doFindKey( $theKey, array $theOptions )
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
				$handler->lookupByKeys(
					$this->Connection()->getID(), (array)$theKey );

		} // Set of keys.

		//
		// Handle scalar.
		//
		else
		{
			try
			{
				//
				// Instantiate document handler.
				//
				$handler = new ArangoDocumentHandler( $this->Database()->Connection() );

				//
				// Find document.
				//
				$result = $handler->getById( $this->collectionName(), $theKey );

				//
				// Set to array.
				//
				$result = [ $result ];
			}
			catch( ArangoServerException $error )
			{
				//
				// Skip not found.
				//
				if( $error->getCode() != 404 )
					throw $error;												// !@! ==>

				return NULL;														// ==>
			}

		} // Scalar key.

		return $result;																// ==>

	} // doFindKey.


	/*===================================================================================
	 *	doFindHandle																	*
	 *==================================================================================*/

	/**
	 * <h4>Find by handle.</h4>
	 *
	 * We implement the method by using the
	 * {@link triagens\ArangoDb\CollectionHandler::lookupByKeys()} method if the
	 * {@link kTOKEN_OPT_MANY} option is set; the
	 * {@link triagens\ArangoDb\CollectionHandler::getById()} method if not.
	 *
	 * @param mixed					$theHandle			The document handle(s).
	 * @param array					$theOptions			Find options.
	 * @return mixed				The found document(s).
	 *
	 * @uses Database()
	 * @uses triagens\ArangoDb\DocumentHandler::getById()
	 * @see kTOKEN_OPT_MANY
	 */
	protected function doFindHandle( $theHandle, array $theOptions )
	{
		//
		// Convert scalar to array.
		//
		if( ! $theOptions[ kTOKEN_OPT_MANY ] )
			$theHandle = [ $theHandle ];

		//
		// Instantiate document handler.
		//
		$handler = new ArangoDocumentHandler( $this->Database()->Connection() );

		//
		// Iterate handles.
		//
		$result = [];
		foreach( $theHandle as $handle )
		{
			//
			// Decompose handle.
			//
			$handle = explode( '/', $handle );

			//
			// Get by key.
			//
			try
			{
				//
				// Find document.
				//
				$document = $handler->getById( $handle[ 0 ], $handle[ 1 ] );
				if( $document !== NULL )
					$result[] = $document;
			}
			catch( ArangoServerException $error )
			{
				//
				// Skip not found.
				//
				if( $error->getCode() != 404 )
					throw $error;												// !@! ==>
			}

		} // Iterating handles.

		return $result;																// ==>

	} // doFindHandle.


	/*===================================================================================
	 *	doFindExample																	*
	 *==================================================================================*/

	/**
	 * <h4>Find by example the first or all records.</h4>
	 *
	 * We overload this method to use the
	 * {@link triagens\ArangoDb\CollectionHandler::byExample()} method.
	 *
	 * We convert the {@link kTOKEN_OPT_SKIP} and {@link kTOKEN_OPT_LIMIT} parameters into
	 * respectively the <tt>skip</tt> and <tt>limit</tt> native options.
	 *
	 * @param array					$theDocument		The example document.
	 * @param array					$theOptions			Driver native options.
	 * @return mixed				The found records.
	 *
	 * @uses Database()
	 * @uses collectionName()
	 * @uses NewNativeDocument()
	 * @uses triagens\ArangoDb\CollectionHandler::byExample()
	 * @see kTOKEN_OPT_SKIP
	 * @see kTOKEN_OPT_LIMIT
	 */
	protected function doFindExample( $theDocument, array $theOptions )
	{
		//
		// Convert to native options.
		//
		$options = [];
		if( array_key_exists( kTOKEN_OPT_SKIP, $theOptions ) )
			$options[ 'skip' ] = $theOptions[ kTOKEN_OPT_SKIP ];
		if( array_key_exists( kTOKEN_OPT_LIMIT, $theOptions ) )
			$options[ 'limit' ] = $theOptions[ kTOKEN_OPT_LIMIT ];

		//
		// Normalise document.
		//
		$document = $this->NewNativeDocument( $theDocument );

		//
		// Get collection handler.
		//
		$handler = new ArangoCollectionHandler( $this->Database()->Connection() );

		//
		// Select documents.
		//
		return
			$handler->byExample(
				$this->collectionName(), $document, $options );						// ==>

	} // doFindExample.


	/*===================================================================================
	 *	doFindQuery																		*
	 *==================================================================================*/

	/**
	 * <h4>Find by query.</h4>
	 *
	 * We overload this method to execute a {@link triagens\ArangoDb\Statement}, the
	 * provided query should be an array with the <tt>query</tt> key.
	 *
	 * <em>The options parameters {@link kTOKEN_OPT_SKIP} and {@link kTOKEN_OPT_LIMIT} are
	 * ignored in this method: you must set them directly into the query</em>.
	 *
	 * @param mixed					$theQuery			The selection criteria.
	 * @param array					$theOptions			Find options.
	 * @return mixed				The found records.
	 *
	 * @uses Database()
	 * @uses collectionName()
	 * @uses triagens\ArangoDb\Statement::execute()
	 */
	protected function doFindQuery( $theQuery, array $theOptions )
	{
		//
		// Init query.
		//
		if( ! count( $theQuery ) )
			$theQuery = [
				'query' => 'FOR r IN @@collection RETURN r',
				'bindVars' => [ '@collection' => $this->collectionName() ] ];

		//
		// Create statement.
		//
		$statement = new ArangoStatement( $this->Database()->Connection(), $theQuery );

		return $statement->execute();												// ==>

	} // doFindQuery.




/*=======================================================================================
 *																						*
 *								PROTECTED CONVERSION UTILITIES							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	toDocumentNative																*
	 *==================================================================================*/

	/**
	 * <h4>Return a native database document.</h4>
	 *
	 * We implement this method by generating a {@link triagens\ArangoDb\Document}.
	 *
	 * @param array					$theDocument		Document properties.
	 * @return mixed				Native database document object.
	 *
	 * @uses KeyOffset()
	 * @uses RevisionOffset()
	 * @uses triagens\ArangoDb\Document::createFromArray()
	 */
	protected function toDocumentNative( array $theDocument )
	{
		//
		// Create an ArangoDocument.
		//
		$document = ArangoDocument::createFromArray( $theDocument );

		//
		// Set key.
		//
		if( ($document->getKey() === NULL)
		 && array_key_exists( $this->KeyOffset(), $theDocument ) )
			$document->setInternalKey( $theDocument[ $this->KeyOffset() ] );

		//
		// Set revision.
		//
		if( ($document->getRevision() === NULL)
		 && array_key_exists( $this->RevisionOffset(), $theDocument ) )
			$document->setRevision( $theDocument[ $this->RevisionOffset() ] );

		return $document;															// ==>

	} // toDocumentNative.




/*=======================================================================================
 *																						*
 *								PROTECTED GENERIC UTILITIES								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	normaliseInsertedDocument														*
	 *==================================================================================*/

	/**
	 * <h4>Normalise inserted document.</h4>
	 *
	 * We overload this method to retrieve and set the revision ({@link RevisionOffset()})
	 * back into the document.
	 *
	 * @param mixed					$theDocument		The inserted document.
	 * @param mixed					$theData			The native inserted document.
	 * @param mixed					$theKey				The document key.
	 *
	 * @uses RevisionOffset()
	 */
	protected function normaliseInsertedDocument( $theDocument, $theData, $theKey )
	{
		//
		// Set document revision.
		//
		if( $theDocument instanceof \ArrayObject )
			$theDocument->offsetSet( $this->RevisionOffset(), $theData->getRevision() );

		//
		// Call parent method.
		//
		parent::normaliseInsertedDocument( $theDocument, $theData, $theKey );

	} // normaliseInsertedDocument.


	/*===================================================================================
	 *	normaliseDeletedDocument														*
	 *==================================================================================*/

	/**
	 * <h4>Normalise inserted document.</h4>
	 *
	 * We overload this method to first call the parent method, which will reset the
	 * document persistent state, allowing this method to remove the revision
	 * ({@link RevisionOffset()}) from the document.
	 *
	 * @param \Milko\PHPLib\Container	$theDocument	The deleted document.
	 *
	 * @uses RevisionOffset()
	 */
	protected function normaliseDeletedDocument( \Milko\PHPLib\Container $theDocument )
	{
		//
		// Call parent method.
		//
		parent::normaliseDeletedDocument( $theDocument );

		//
		// Remove revision.
		//
		$theDocument->offsetUnset( $this->RevisionOffset() );

	} // normaliseDeletedDocument.


	/*===================================================================================
	 *	normaliseSelectedDocument														*
	 *==================================================================================*/

	/**
	 * <h4>Normalise selected document.</h4>
	 *
	 * This method will be called when a {@link Container} instance has been selected from
	 * the current collection via a query, its duty is to pass information back to the
	 * document, including eventual internal native database properties.
	 *
	 * The method expects a single parameter which should be a {@link Container} instance.
	 *
	 * The method is implemented in this class to handle {@link Document} instances:
	 *
	 * <ul>
	 *	<li><tt>{@link Document::IsPersistent()}</tt>: If the provided document is a
	 * 		{@link Document} instance, the document's persistent state will be set.
	 *	<li><tt>{@link Document::IsModified()}</tt>: If the provided document is a
	 * 		{@link Document} instance, the document's modification state will be reset.
	 * </ul>
	 *
	 * In derived classes you should first manage internal database properties, if relevant,
	 * then call the current method.
	 *
	 * @param \Milko\PHPLib\Container	$theDocument	The inserted document.
	 * @param mixed						$theData		The native database document.
	 *
	 * @uses RevisionOffset()
	 */
	protected function normaliseSelectedDocument( \Milko\PHPLib\Container $theDocument,
																		  $theData )
	{
		//
		// Set document revision.
		//
		if( $theDocument instanceof \Milko\PHPLib\Document )
			$theDocument->offsetSet( $this->RevisionOffset(), $theData->getRevision() );

		//
		// Call parent method.
		//
		parent::normaliseSelectedDocument( $theDocument, $theData );

	} // normaliseSelectedDocument.



} // class Collection.


?>
