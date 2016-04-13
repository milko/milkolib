<?php

/**
 * Collection.php
 *
 * This file contains the definition of the {@link Collection} class.
 */

namespace Milko\PHPLib\ArangoDB;

/*=======================================================================================
 *																						*
 *									Collection.php										*
 *																						*
 *======================================================================================*/

use Milko\PHPLib\Database;
use Milko\PHPLib\Document;

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

/**
 * <h4>Collection ancestor object.</h4>
 *
 * This <em>concrete</em> class is the implementation of a ArangoDB collection, it
 * implements the inherited virtual interface to provide an object that can manage ArangoDB
 * collections.
 *
 *	@package	Data
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		21/02/2016
 */
class Collection extends \Milko\PHPLib\Collection
{



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
 *							PUBLIC COLLECTION MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Drop																			*
	 *==================================================================================*/

	/**
	 * <h4>Drop the current collection.</h4>
	 *
	 * We overload this method to call the {@link triagens\ArangoDb\CollectionHandler::drop}
	 * method.
	 *
	 * The options parameter is ignored here.
	 *
	 * @param array					$theOptions			Native driver options.
	 * @return mixed				<tt>TRUE</tt> dropped, <tt>NULL</tt> not found.
	 *
	 * @uses triagens\ArangoDb\CollectionHandler::drop()
	 */
	public function Drop( $theOptions = NULL )
	{
		//
		// Check collection.
		//
		if( $this->mConnection->getId() !== NULL )
		{
			//
			// Instantiate collection handler.
			//
			$handler = new ArangoCollectionHandler( $this->mDatabase->Connection() );

			//
			// Drop collection.
			//
			$handler->drop( $this->mConnection->getName() );

			return TRUE;															// ==>

		} // Has collection and is active.

		return NULL;																// ==>

	} // Drop.


	/*===================================================================================
	 *	Truncate																		*
	 *==================================================================================*/

	/**
	 * <h4>Clear the collection contents.</h4>
	 *
	 * We overload this method to call the triagens\ArangoDb\CollectionHandler::truncate()
	 * method.
	 *
	 * @param array					$theOptions			Native driver options.
	 * @return mixed				<tt>TRUE</tt> truncated, <tt>NULL</tt> not found.
	 *
	 * @uses triagens\ArangoDb\CollectionHandler::truncate()
	 */
	public function Truncate( $theOptions = NULL )
	{
		//
		// Check collection.
		//
		if( $this->mConnection->getId() !== NULL )
		{
			//
			// Instantiate collection handler.
			//
			$handler = new ArangoCollectionHandler( $this->mDatabase->Connection() );

			//
			// Truncate collection.
			//
			$handler->truncate( $this->mConnection );

			return TRUE;															// ==>

		} // Has collection and is active.

		return NULL;																// ==>

	} // Truncate.



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
	 * We overload this method to handle {@link triagens\ArangoDb\Document} instances: we
	 * extract the key and revision from the native document and add them to the resulting
	 * array.
	 *
	 * @param mixed					$theData			Document data.
	 * @return array				Document as array.
	 *
	 * @uses KeyOffset()
	 * @uses RevisionOffset()
	 * @uses triagens\ArangoDb\Document::getAll()
	 * @uses triagens\ArangoDb\Document::getKey()
	 * @uses triagens\ArangoDb\Document::getRevision()
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
	 * We overload this method to intercept {@link triagens\ArangoDb\Document} instances and
	 * have them return the handle, all other types are passed to the parent method.
	 *
	 * If the provided document cannot return the handle, the method will raise an
	 * exception.
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
			// Get handle.
			//
			$handle = $theData->getHandle();
			if( $handle !== NULL )
				return $handle;														// ==>

			throw new \InvalidArgumentException (
				"Unable to retrieve handle from document." );					// !@! ==>

		} // ArangoDocument.

		return parent::NewDocumentHandle( $theData );								// ==>

	} // NewDocumentHandle.


	/*===================================================================================
	 *	NewDocumentKey																	*
	 *==================================================================================*/

	/**
	 * <h4>Return document key.</h4>
	 *
	 * We overload this method to intercept {@link triagens\ArangoDb\Document} instances and
	 * have them return the key, all other types are passed to the parent method.
	 *
	 * If the provided document cannot return the key, the method will raise an exception.
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
			$key = $theData->getKey();
			if( $key !== NULL )
				return $key;														// ==>

			throw new \InvalidArgumentException (
				"Unable to retrieve key from document." );						// !@! ==>
			
		} // ArangoDocument.
		
		return parent::NewDocumentKey( $theData );									// ==>

	} // NewDocumentKey.



/*=======================================================================================
 *																						*
 *								PUBLIC INSERTION INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Insert																			*
	 *==================================================================================*/

	/**
	 * <h4>Insert document.</h4>
	 *
	 * We implement this method by converting the provided array into a native document and
	 * using the {@link triagens\ArangoDb\DocumentHandler::save()} method to insert it.
	 *
	 * @param array					$theDocument		The document as an array.
	 * @return mixed				The document's unique identifier.
	 *
	 * @uses documentNativeCreate()
	 * @uses triagens\ArangoDb\DocumentHandler::save()
	 */
	public function Insert( array $theDocument )
	{
		//
		// Instantiate handler.
		//
		$handler = new ArangoDocumentHandler( $this->mDatabase->Connection() );

		//
		// Convert to native document.
		//
		$document = $this->documentNativeCreate( $theDocument );

		return $handler->save( $this->mConnection, $document );						// ==>

	} // Insert.


	/*===================================================================================
	 *	InsertMany																		*
	 *==================================================================================*/

	/**
	 * <h4>Insert a set of documents.</h4>
	 *
	 * We implement this method by iterating the provided array, converting the elements to
	 * the database native format ({@link documentNativeCreate()}) and saving them.
	 *
	 * @param array					$theDocuments		The documents set as an array.
	 * @return array				The document unique identifiers.
	 *
	 * @uses documentNativeCreate()
	 * @uses triagens\ArangoDb\DocumentHandler::save()
	 */
	public function InsertMany( array $theDocuments )
	{
		//
		// Instantiate handler.
		//
		$handler = new ArangoDocumentHandler( $this->mDatabase->Connection() );

		//
		// Iterate documents.
		//
		$ids = [];
		foreach( $theDocuments as $document )
			$ids[] =
				$handler->save(
					$this->mConnection,
					$this->documentNativeCreate( $document ) );

		return $ids;																// ==>

	} // InsertMany.


	/*===================================================================================
	 *	InsertBulk																		*
	 *==================================================================================*/

	/**
	 * <h4>Insert a set of documents.</h4>
	 *
	 * We implement this method by iterating the provided array and inserting the elements;
	 * in this method we assume the array elements are already in the database native
	 * format.
	 *
	 * @param mixed					$theDocuments		The native documents set.
	 * @return array				The document unique identifiers.
	 *
	 * @uses triagens\ArangoDb\DocumentHandler::save()
	 */
	public function InsertBulk( $theDocuments )
	{
		//
		// Instantiate handler.
		//
		$handler = new ArangoDocumentHandler( $this->mDatabase->Connection() );

		//
		// Iterate documents.
		//
		$ids = [];
		foreach( $theDocuments as $document )
			$ids[] = $handler->save( $this->mConnection, $document );

		return $ids;																// ==>

	} // InsertBulk.



/*=======================================================================================
 *																						*
 *						PROTECTED COLLECTION MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	collectionCreate																*
	 *==================================================================================*/

	/**
	 * <h4>Return a native collection object.</h4>
	 *
	 * We implement this method to return a {@link triagens\ArangoDb\Collection} instance.
	 *
	 * We first check if the collection exists, in that case we return it; if not, we create
	 * it and return it.
	 *
	 * @param string				$theCollection		Collection name.
	 * @param array					$theOptions			Driver native options.
	 * @return mixed				Native collection object.
	 *
	 * @uses triagens\ArangoDb\CollectionHandler::has()
	 * @uses triagens\ArangoDb\CollectionHandler::get()
	 * @uses triagens\ArangoDb\CollectionHandler::create()
	 */
	protected function collectionCreate( $theCollection, $theOptions = NULL )
	{
		//
		// Init options.
		//
		if( $theOptions === NULL )
			$theOptions = [];

		//
		// Get collection handler.
		//
		$handler = new ArangoCollectionHandler( $this->mDatabase->Connection() );

		//
		// Return existing collection.
		//
		if( $handler->has( $theCollection ) )
			return $handler->get( $theCollection );									// ==>

		//
		// Create collection.
		//
		$collection = $handler->create( $theCollection, $theOptions );

		return $handler->get( $collection );										// ==>

	} // collectionCreate.


	/*===================================================================================
	 *	collectionName																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the collection name.</h4>
	 *
	 * We implement this method by calling the
	 * {@link triagens\ArangoDb\Collection::getName()} method.
	 *
	 * @return string				The collection name.
	 *
	 * @uses triagens\ArangoDb\Collection::getName()
	 */
	protected function collectionName()
	{
		return $this->mConnection->getName();										// ==>

	} // collectionName.



/*=======================================================================================
 *																						*
 *								PROTECTED CONVERSION UTILITIES							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	documentNativeCreate															*
	 *==================================================================================*/

	/**
	 * <h4>Return a native database document.</h4>
	 *
	 * We implement this method to create a {@link triagens\ArangoDb\Document} instance, we
	 * also ensure the internal key and revision properties of the native document are set.
	 *
	 * @param array					$theData			Document as an array.
	 * @return mixed				Native database document object.
	 *
	 * @uses KeyOffset()
	 * @uses RevisionOffset()
	 * @uses triagens\ArangoDb\Document::createFromArray()
	 * @uses triagens\ArangoDb\Document::getKey()
	 * @uses triagens\ArangoDb\Document::setInternalKey()
	 * @uses triagens\ArangoDb\Document::getRevision()
	 * @uses triagens\ArangoDb\Document::setRevision()
	 */
	protected function documentNativeCreate( array $theData )
	{
		//
		// Create an ArangoDocument.
		//
		$document = ArangoDocument::createFromArray( $theData );

		//
		// Set key.
		//
		if( ($document->getKey() === NULL)
		 && array_key_exists( $this->KeyOffset(), $theData ) )
			$document->setInternalKey( $theData[ $this->KeyOffset() ] );

		//
		// Set revision.
		//
		if( ($document->getRevision() === NULL)
		 && array_key_exists( $this->RevisionOffset(), $theData ) )
			$document->setRevision( $theData[ $this->RevisionOffset() ] );

		return $document;															// ==>

	} // documentNativeCreate.


	/*===================================================================================
	 *	documentHandleCreate															*
	 *==================================================================================*/

	/**
	 * <h4>Return a document handle.</h4>
	 *
	 * We implement this method by concatenating the current collection name with the
	 * provided key cast to a string separated by a slash.
	 *
	 * @param mixed					$theKey				Document key.
	 * @return mixed				Document handle.
	 *
	 * @uses collectionName()
	 */
	public function documentHandleCreate( $theKey )
	{
		return $this->collectionName() . '/' . (string)$theKey;						// ==>

	} // documentHandleCreate.



} // class Collection.


?>
