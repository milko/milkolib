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

use Milko\PHPLib\Document;
use Milko\PHPLib\ArangoDB\Database;

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
	/**
	 * <h4>Document handler.</h4>
	 *
	 * This data member holds the document handler.
	 *
	 * @var ArangoDocumentHandler
	 */
	protected $mDocumentHandler = NULL;

	/**
	 * <h4>Collection handler.</h4>
	 *
	 * This data member holds the collection handler.
	 *
	 * @var ArangoCollectionHandler
	 */
	protected $mCollectionHandler = NULL;




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
	 * We overload this method to instantiate the collection and document handlers used for
	 * database operations, we store them in the object attributes to prevent having to
	 * instantiate them each time an operation requires them.
	 *
	 * @param Database				$theDatabase		Database.
	 * @param string				$theCollection		Collection name.
	 * @param array					$theOptions			Native driver options.
	 */
	public function __construct( Database $theDatabase, $theCollection, $theOptions = NULL )
	{
		//
		// Store document handler.
		//
		$this->mDocumentHandler
			= new ArangoDocumentHandler( $theDatabase->Connection() );

		//
		// Store collection handler.
		//
		$this->mCollectionHandler
			= new ArangoCollectionHandler( $theDatabase->Connection() );

		//
		// Call parent constructor.
		//
		parent::__construct( $theDatabase, $theCollection, $theOptions );

	} // Constructor.



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


	/*===================================================================================
	 *	PropertiesOffset																*
	 *==================================================================================*/

	/**
	 * <h4>Return the document properties offset.</h4>
	 *
	 * We overload this method to use the {@link kTAG_ARANGO_OFFSETS} constant.
	 *
	 * @return string				Document properties offset.
	 */
	public function PropertiesOffset()						{	return kTAG_ARANGO_OFFSETS;	}



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
			// Drop collection.
			//
			$this->mCollectionHandler->drop( $this->mConnection->getName() );

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
			// Truncate collection.
			//
			$this->mCollectionHandler->truncate( $this->mConnection );

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
	 *	NewDocumentNative																*
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
	public function NewDocumentNative( $theData )
	{
		//
		// Handle native document.
		//
		if( $theData instanceof ArangoDocument )
			return $theData;														// ==>

		return parent::NewDocumentNative( $theData );								// ==>

	} // NewDocumentNative.


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


	/*===================================================================================
	 *	BuildDocumentHandle																*
	 *==================================================================================*/

	/**
	 * <h4>Build a document handle.</h4>
	 *
	 * We implement this method by concatenating the current collection name with the
	 * provided key cast to a string separated by a slash.
	 *
	 * @param mixed					$theKey				Document key.
	 * @param mixed					$theCollection		Collection instance or name.
	 * @return mixed				Document handle.
	 */
	public function BuildDocumentHandle( $theKey, $theCollection = NULL )
	{
		//
		// Set current collection.
		//
		if( $theCollection === NULL )
			$theCollection = $this;

		//
		// Handle collection instance.
		//
		if( $theCollection instanceof \Milko\PHPLib\Collection )
			return $theCollection->documentHandleCreate( $theKey );					// ==>

		return (string)$theCollection . '/' . $theKey;								// ==>

	} // BuildDocumentHandle.



/*=======================================================================================
 *																						*
 *						PUBLIC TIMESTAMP MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	NewTimestamp																	*
	 *==================================================================================*/

	/**
	 * <h4>Return a native time stamp.</h4>
	 *
	 * We implement this method by making an AQL statement which calls the
	 * <tt>DATE_NOW()</tt> function.
	 *
	 * @param int					$theTimeStamp		Milliseconds.
	 * @return mixed				Time stamp in native format.
	 */
	public function NewTimestamp( $theTimeStamp = NULL )
	{
		//
		// Handle provided time stamp.
		//
		if( $theTimeStamp !== NULL )
		{
			//
			// Cast timestamp.
			//
			$theTimeStamp = (int)$theTimeStamp;

			//
			// Set filter.
			//
			$filter = [ 'query' => "RETURN DATE_TIMESTAMP($theTimeStamp)" ];
		}

		//
		// Handle current time stamp.
		//
		else
			$filter = [ 'query' => 'RETURN DATE_NOW()' ];

		//
		// Create statement.
		//
		$statement = new ArangoStatement( $this->mDatabase->Connection(), $filter );

		//
		// Execute statement.
		//
		$result = $statement->execute();

		//
		// Return first element.
		//
		foreach( $result as $element )
			return $element;														// ==>

	} // NewTimestamp.


	/*===================================================================================
	 *	GetTimestamp																	*
	 *==================================================================================*/

	/**
	 * <h4>Return an ISO date from a timestamp.</h4>
	 *
	 * We implement this method by converting the time stamp into a {@link \DateTime}
	 * object and returning the following format: <tt>"Y-m-d\TH:i:s.uP"</tt>.
	 *
	 * @param mixed					$theTimeStamp		Native time stamp.
	 * @return string				ISO 8601 date.
	 */
	public function GetTimestamp( $theTimeStamp )
	{
		//
		// Init query.
		//
		$filter = [ 'query' => "RETURN DATE_ISO8601($theTimeStamp)" ];

		//
		// Create statement.
		//
		$statement = new ArangoStatement( $this->mDatabase->Connection(), $filter );

		//
		// Execute statement.
		//
		$result = $statement->execute();

		//
		// Return first element.
		//
		foreach( $result as $element )
			return $element;														// ==>

	} // GetTimestamp.



/*=======================================================================================
 *																						*
 *								PUBLIC INSERTION INTERFACE								*
 *																						*
 *======================================================================================*/



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
		// Iterate documents.
		//
		$ids = [];
		foreach( $theDocuments as $document )
			$ids[] = $this->mDocumentHandler->save( $this->mConnection, $document );

		return $ids;																// ==>

	} // InsertBulk.



/*=======================================================================================
 *																						*
 *								PUBLIC UPDATE INTERFACE									*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Update																			*
	 *==================================================================================*/

	/**
	 * <h4>Update documents.</h4>
	 *
	 * We implement this method by applying the provided filter and modifying the returned
	 * documents.
	 *
	 * @param array					$theCriteria		The modification criteria.
	 * @param mixed					$theFilter			The selection criteria.
	 * @param array					$theOptions			Update options.
	 * @return int					The number of modified records.
	 *
	 * @uses NewDocumentNative()
	 * @uses NewDocumentKey()
	 * @uses triagens\ArangoDb\Cursor::getCount()
	 * @uses triagens\ArangoDb\Statement::execute()
	 * @uses triagens\ArangoDb\Document::set()
	 * @uses triagens\ArangoDb\DocumentHandler::update()
	 */
	public function Update( array $theCriteria,
								  $theFilter = NULL,
							array $theOptions = [ kTOKEN_OPT_MANY => TRUE ] )
	{
		//
		// Normalise query.
		//
		if( $theFilter === NULL )
			$theFilter =
				[ 'query' => 'FOR r IN @@collection RETURN r',
					'bindVars' => [ '@collection' => $this->collectionName() ] ];

		//
		// Normalise criteria.
		//
		$theCriteria[ kTAG_MODIFICATION ] = microtime( TRUE );

		//
		// Select documents.
		//
		$statement = new ArangoStatement( $this->mDatabase->Connection(), $theFilter );
		$cursor = $statement->execute();
		$count = $cursor->getCount();

		//
		// Handle selection.
		//
		if( $count )
		{
			//
			// Process selection.
			//
			foreach( $cursor as $document )
			{
				//
				// Update document.
				//
				foreach( $theCriteria as $key => $value )
					$document->set( $key, $value );

				//
				// Update document.
				//
				$this->mDocumentHandler->update( $document, [ 'keepNull' => FALSE ] );

				//
				// Handle only first.
				//
				if( ! $theOptions[ kTOKEN_OPT_MANY ] )
					return 1;														// ==>

			} // Iterating documents.

		} // Non empty selection.

		return $count;																// ==>

	} // Update.


	/*===================================================================================
	 *	UpdateByExample																	*
	 *==================================================================================*/

	/**
	 * <h4>Update documents by example.</h4>
	 *
	 * We implement this method by using the
	 * {@link triagens\ArangoDb\CollectionHandler::byExample()} method and applying the
	 * modifications to the returned selection.
	 *
	 * @param array					$theCriteria		The modification criteria.
	 * @param array					$theDocument		The example document.
	 * @param array					$theOptions			Update options.
	 * @return int					The number of modified records.
	 *
	 * @uses collectionName()
	 * @uses documentNativeCreate()
	 * @uses triagens\ArangoDb\CollectionHandler::byExample()
	 * @uses triagens\ArangoDb\DocumentHandler::update()
	 * @uses triagens\ArangoDb\Document::set()
	 */
	public function UpdateByExample( array $theCriteria,
									 array $theDocument,
									 array $theOptions = [ kTOKEN_OPT_MANY => TRUE ] )
	{
		//
		// Normalise criteria.
		//
		$theCriteria[ kTAG_MODIFICATION ] = microtime( TRUE );

		//
		// Normalise document.
		//
		$document = $this->documentNativeCreate( $theDocument );

		//
		// Select documents.
		//
		$cursor =
			$this->mCollectionHandler->byExample(
				$this->collectionName(), $document );

		//
		// Iterate documents.
		//
		$count = 0;
		foreach( $cursor as $document )
		{
			//
			// Update document.
			//
			foreach( $theCriteria as $key => $value )
				$document->set( $key, $value );

			//
			// Update document.
			//
			$this->mDocumentHandler->update( $document, [ 'keepNull' => FALSE ] );

			//
			// Handle only first.
			//
			if( ! $theOptions[ kTOKEN_OPT_MANY ] )
				return 1;														// ==>

		} // Iterating selection.

	} // UpdateByExample.



/*=======================================================================================
 *																						*
 *								PUBLIC SELECTION INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Find																			*
	 *==================================================================================*/

	/**
	 * <h4>Find documents by query.</h4>
	 *
	 * We overload this method to execute a {@link triagens\ArangoDb\Statement}, the
	 * provided filter should be an array holding the <tt>query</tt> key.
	 *
	 * <em>The options parameters {@link kTOKEN_OPT_SKIP} and {@link kTOKEN_OPT_LIMIT} are
	 * ignored in this method: you must set them directly into the query</em>.
	 *
	 * @param mixed					$theFilter			The selection criteria.
	 * @param array					$theOptions			Query options.
	 * @param bool					$isPersistent		Persistent flag.
	 * @return mixed				The found records.
	 *
	 * @uses ConvertDocumentSet()
	 * @uses triagens\ArangoDb\Statement::execute()
	 */
	public function Find(
		$theFilter = NULL,
		array $theOptions = [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_DOCUMENT ] )
	{
		//
		// Init query.
		//
		if( $theFilter === NULL )
			$theFilter = [
				'query' => 'FOR r IN @@collection RETURN r',
				'bindVars' => [ '@collection' => $this->collectionName() ] ];

		//
		// Create statement.
		//
		$statement = new ArangoStatement( $this->mDatabase->Connection(), $theFilter );

		//
		// Execute statement.
		//
		$result = $statement->execute();

		//
		// Handle native result.
		//
		if( $theOptions[ kTOKEN_OPT_FORMAT ] == kTOKEN_OPT_FORMAT_NATIVE )
			return $result;															// ==>

		return
			$this->ConvertDocumentSet(
				$result, $theOptions[ kTOKEN_OPT_FORMAT ], TRUE );					// ==>

	} // Find.


	/*===================================================================================
	 *	FindByKey																		*
	 *==================================================================================*/

	/**
	 * <h4>Find documents by key.</h4>
	 *
	 * We implement the method by using the
	 * {@link triagens\ArangoDb\CollectionHandler::lookupByKeys()} method if the
	 * {@link kTOKEN_OPT_MANY} option is set; the
	 * {@link triagens\ArangoDb\CollectionHandler::getById()} method if not.
	 *
	 * @param mixed					$theKey				Single or set of document keys.
	 * @param array					$theOptions			Query options.
	 * @return mixed				The found records.
	 *
	 * @uses ConvertDocumentSet()
	 * @uses formatDocument()
	 * @uses collectionName()
	 * @uses triagens\ArangoDb\CollectionHandler::lookupByKeys()
	 * @uses triagens\ArangoDb\DocumentHandler::getById()
	 */
	public function FindByKey(
		$theKey,
		array $theOptions = [ kTOKEN_OPT_MANY => FALSE,
							  kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_DOCUMENT ] )
	{
		//
		// Handle list.
		//
		if( $theOptions[ kTOKEN_OPT_MANY ] )
		{
			//
			// Get documents.
			//
			$result =
				$this->mCollectionHandler->lookupByKeys(
					$this->mConnection->getID(), (array)$theKey );

			//
			// Handle native result.
			//
			if( $theOptions[ kTOKEN_OPT_FORMAT ] == kTOKEN_OPT_FORMAT_NATIVE )
				return $result;														// ==>

			return
				$this->ConvertDocumentSet(
					$result, $theOptions[ kTOKEN_OPT_FORMAT ], TRUE );				// ==>

		} // Set of keys.

		//
		// Try finding document.
		//
		try
		{
			//
			// Find document.
			//
			$result = $this->mDocumentHandler->getById( $this->collectionName(), $theKey );

			//
			// Handle native result.
			//
			if( $theOptions[ kTOKEN_OPT_FORMAT ] == kTOKEN_OPT_FORMAT_NATIVE )
				return $result;														// ==>

			return
				$this->formatDocument(
					$result, $theOptions[ kTOKEN_OPT_FORMAT ], TRUE );				// ==>

		} // Document found.

		//
		// Handle missing document.
		//
		catch( ArangoServerException $error )
		{
			//
			// Handle exceptions.
			//
			if( $error->getCode() != 404 )
				throw $error;													// !@! ==>

		} // Document not found.

		return NULL;																// ==>

	} // FindByKey.


	/*===================================================================================
	 *	FindByHandle																	*
	 *==================================================================================*/

	/**
	 * <h4>Find documents by handle.</h4>
	 *
	 * We implement the method by using the
	 * {@link triagens\ArangoDb\CollectionHandler::lookupByKeys()} method if the
	 * {@link kTOKEN_OPT_MANY} option is set; the
	 * {@link triagens\ArangoDb\CollectionHandler::getById()} method if not.
	 *
	 * @param mixed					$theHandle			Single or set of document handles.
	 * @param array					$theOptions			Query options.
	 * @return mixed				The found records.
	 *
	 * @uses formatDocument()
	 * @uses triagens\ArangoDb\DocumentHandler::getById()
	 */
	public function FindByHandle(
		$theHandle,
		array $theOptions = [ kTOKEN_OPT_MANY => FALSE,
							  kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_DOCUMENT ] )
	{
		//
		// Handle list.
		//
		if( $theOptions[ kTOKEN_OPT_MANY ] )
		{
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
				// Try finding document.
				//
				try
				{
					//
					// Find document.
					//
					$document =
						$this->mDocumentHandler
							->getById( $handle[ 0 ], $handle[ 1 ] );

					//
					// Format document.
					//
					$result[] =
						$this->Database()->NewCollection( $handle[ 0 ] )
							->formatDocument(
								$document, $theOptions[ kTOKEN_OPT_FORMAT ], TRUE );

				} // Document found.

				//
				// Handle missing document.
				//
				catch( ArangoServerException $error )
				{
					//
					// Handle exceptions.
					//
					if( $error->getCode() != 404 )
						throw $error;											// !@! ==>

				} // Document not found.

			} // Iterating handles.

			return $result;															// ==>

		} // List of handles.

		//
		// Decompose handle.
		//
		$handle = explode( '/', $theHandle );

		//
		// Try finding document.
		//
		try
		{
			//
			// Find document.
			//
			$document = $this->mDocumentHandler->getById( $handle[ 0 ], $handle[ 1 ] );

			return
				$this->Database()->NewCollection( $handle[ 0 ] )
					->formatDocument(
						$document, $theOptions[ kTOKEN_OPT_FORMAT ], TRUE );		// ==>

		} // Document found.

		//
		// Handle missing document.
		//
		catch( ArangoServerException $error )
		{
			//
			// Handle exceptions.
			//
			if( $error->getCode() != 404 )
				throw $error;													// !@! ==>

		} // Document not found.

		return NULL;																// ==>

	} // FindByHandle.


	/*===================================================================================
	 *	FindByExample																	*
	 *==================================================================================*/

	/**
	 * <h4>Find documents by example.</h4>
	 *
	 * We overload this method to use the
	 * {@link triagens\ArangoDb\CollectionHandler::byExample()} method.
	 *
	 * We convert the {@link kTOKEN_OPT_SKIP} and {@link kTOKEN_OPT_LIMIT} parameters into
	 * respectively the <tt>skip</tt> and <tt>limit</tt> native options.
	 *
	 * @param array					$theDocument		Example document as an array.
	 * @param array					$theOptions			Query options.
	 * @return mixed				The found records.
	 *
	 * @uses ConvertDocumentSet()
	 * @uses NewDocumentNative()
	 * @uses triagens\ArangoDb\CollectionHandler::byExample()
	 */
	public function FindByExample(
		array $theDocument,
		array $theOptions = [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_DOCUMENT ] )
	{
		//
		// Convert to native options.
		//
		$options = [];
		if( array_key_exists( kTOKEN_OPT_SKIP, $theOptions ) )
			$options[ 'skip' ] = $theOptions[ kTOKEN_OPT_SKIP ];
		if( array_key_exists( kTOKEN_OPT_LIMIT, $theOptions ) )
		{
			$options[ 'limit' ] = $theOptions[ kTOKEN_OPT_LIMIT ];
			if( ! array_key_exists( kTOKEN_OPT_SKIP, $theOptions ) )
				$options[ 'skip' ] = 0;
		}

		//
		// Normalise document.
		//
		$document = $this->NewDocumentNative( $theDocument );

		//
		// Select documents.
		//
		$result =
			$this->mCollectionHandler->byExample(
				$this->collectionName(), $document, $options );

		//
		// Handle native result.
		//
		if( $theOptions[ kTOKEN_OPT_FORMAT ] == kTOKEN_OPT_FORMAT_NATIVE )
			return $result;															// ==>

		return
			$this->ConvertDocumentSet(
				$result, $theOptions[ kTOKEN_OPT_FORMAT ], TRUE );					// ==>

	} // FindByExample.



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
	 * simply call the {@link Find()} method replacing the pipeline parameter with the
	 * query.
	 *
	 * @param mixed					$thePipeline		The aggregation pipeline.
	 * @param array					$theOptions			Query options.
	 * @return array				The result set.
	 *
	 * @uses Find()
	 */
	public function MapReduce( $thePipeline, array $theOptions = [] )
	{
		//
		// Set format to array.
		//
		$theOptions[ kTOKEN_OPT_FORMAT ] = kTOKEN_OPT_FORMAT_ARRAY;

		return $this->Find( $thePipeline, $theOptions );							// ==>

	} // MapReduce.



/*=======================================================================================
 *																						*
 *								PUBLIC DISTINCT INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Distinct																		*
	 *==================================================================================*/

	/**
	 * <h4>Return the distinct values of a property.</h4>
	 *
	 * We implement this method by using the <tt>RETURN DISTINCT</tt> AQL statement.
	 *
	 * @param string				$theOffset			The property offset.
	 * @param boolean				$doCount			Return element counts.
	 * @return array				The result set.
	 *
	 * @uses triagens\ArangoDb\Statement::execute()
	 */
	public function Distinct( $theOffset, $doCount = FALSE )
	{
		//
		// Init query.
		//
	    $statement = ( $doCount )
				   ? 'FOR r IN @@c COLLECT k = r.@f WITH COUNT INTO n RETURN{ k, n }'
				   : 'FOR r IN @@c RETURN DISTINCT r.@f';
		$filter = [
			'query' => $statement,
			'bindVars' => [ '@c' => $this->collectionName(),
							'f' => $theOffset ] ];

		//
		// Execute statement.
		//
		$statement = new ArangoStatement( $this->mDatabase->Connection(), $filter );
		$result = $statement->execute();

		//
		// Handle only values.
		//
		if( ! $doCount )
			return $result->getAll();												// ==>

		//
		// Build results array.
		//
		$list = [];
		foreach( $result as $item )
		{
			$data = $item->getAll();
			$list[ $data[ 'k' ] ] = $data[ 'n' ];
		}

		return $list;																// ==>

	} // Distinct.


	/*===================================================================================
	 *	DistinctByQuery																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the distinct values of a property by query.</h4>
	 *
	 * We implement this method by using the {@link DistinctByExample()} method.
	 *
	 * @param string				$theOffset			The property offset.
	 * @param mixed					$theFilter			The selection criteria.
	 * @param boolean				$doCount			Return element counts.
	 * @return array				The result set.
	 *
	 * @uses DistinctByExample()
	 */
	public function DistinctByQuery( $theOffset, $theFilter, $doCount = FALSE )
	{
		return $this->DistinctByExample( $theOffset, $theFilter, $doCount );		// ==>

	} // DistinctByQuery.


	/*===================================================================================
	 *	DistinctByExample																*
	 *==================================================================================*/

	/**
	 * <h4>Return the distinct values of a property by example.</h4>
	 *
	 * We implement this method by using the <tt>RETURN DISTINCT</tt> AQL statement and
	 * building an AQL filter from the provided example document.
	 *
	 * @param string				$theOffset			The property offset.
	 * @param array					$theDocument		Example document as an array.
	 * @param boolean				$doCount			Return element counts.
	 * @return array				The result set.
	 *
	 * @uses triagens\ArangoDb\Statement::execute()
	 */
	public function DistinctByExample( $theOffset, array $theDocument, $doCount = FALSE )
	{
		//
		// Build statement.
		//
		$i = 0;
		$filter = [];
		$bind = [ '@c' => $this->collectionName(), 'f' => $theOffset ];
		$statement = 'FOR r IN @@c ';
		foreach( $theDocument as $key => $value )
		{
			$i++;
			$filter[] = 'r.' . '`' . $key . '` == ' . "@v$i";
			$bind[ "v$i" ] = $value;
		}
		if( count( $filter ) )
		{
			$filter = implode( ' AND ', $filter );
			$statement .= "FILTER $filter";
		}
		if( $doCount )
			$statement .= ' COLLECT k = r.@f WITH COUNT INTO n RETURN{ k, n }';
		else
			$statement .= ' RETURN DISTINCT r.@f';

		//
		// Init query.
		//
		$filter = [
			'query' => $statement,
			'bindVars' => $bind ];

		//
		// Execute statement.
		//
		$statement = new ArangoStatement( $this->mDatabase->Connection(), $filter );
		$result = $statement->execute();

		//
		// Handle only values.
		//
		if( ! $doCount )
			return $result->getAll();												// ==>

		//
		// Build results array.
		//
		$list = [];
		foreach( $result as $item )
		{
			$data = $item->getAll();
			$list[ $data[ 'k' ] ] = $data[ 'n' ];
		}

		return $list;																// ==>

	} // DistinctByExample.



/*=======================================================================================
 *																						*
 *								PUBLIC COUNTING INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Count																			*
	 *==================================================================================*/

	/**
	 * <h4>Return the documents count.</h4>
	 *
	 * We overload this method to use the
	 * {@link triagens\ArangoDb\CollectionHandler::count()} method.
	 *
	 * @return int					The total number of documents in the collection.
	 *
	 * @uses collectionName()
	 * @uses triagens\ArangoDb\CollectionHandler::count()
	 */
	public function Count()
	{
		return $this->mCollectionHandler->count( $this->collectionName() );			// ==>

	} // Count.


	/*===================================================================================
	 *	CountByQuery																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the number of documents by query.</h4>
	 *
	 * We overload this method to execute a {@link triagens\ArangoDb\Statement} and calling
	 * the {@link triagens\ArangoDb\Cursor::getCount()} method on the statement result, the
	 * provided filter should be an array holding the <tt>query</tt> key.
	 *
	 * @param mixed					$theFilter			The selection criteria.
	 * @return int					The number of selected documents.
	 *
	 * @uses collectionName()
	 * @uses triagens\ArangoDb\Statement::execute()
	 * @uses triagens\ArangoDb\Cursor::getCount()
	 */
	public function CountByQuery( $theFilter )
	{
		//
		// Init query.
		//
		if( ! count( $theFilter ) )
			$theFilter = [
				'query' => 'FOR r IN @@collection RETURN r',
				'bindVars' => [ '@collection' => $this->collectionName() ] ];

		//
		// Create statement.
		//
		$statement = new ArangoStatement( $this->mDatabase->Connection(), $theFilter );

		//
		// Execute statement.
		//
		$result = $statement->execute();

		return $result->getCount();													// ==>

	} // CountByQuery.


	/*===================================================================================
	 *	CountByKey																		*
	 *==================================================================================*/

	/**
	 * <h4>Count documents by key.</h4>
	 *
	 * We overload this method to use the {@link \MongoDB\Collection::count()} method.
	 *
	 * @param mixed					$theKey				Document key.
	 * @return int					The number of selected documents.
	 *
	 * @uses collectionName()
	 * @uses triagens\ArangoDb\DocumentHandler::getById()
	 */
	public function CountByKey( $theKey )
	{
		//
		// Try finding document.
		//
		try
		{
			//
			// Find document.
			//
			$this->mDocumentHandler->getById( $this->collectionName(), $theKey );

			return 1;																// ==>

		} // Document found.

		//
		// Handle missing document.
		//
		catch( ArangoServerException $error )
		{
			//
			// Handle exceptions.
			//
			if( $error->getCode() != 404 )
				throw $error;													// !@! ==>

		} // Document not found.

		return 0;																	// ==>

	} // CountByKey.


	/*===================================================================================
	 *	CountByHandle																	*
	 *==================================================================================*/

	/**
	 * <h4>Count documents by handle.</h4>
	 *
	 * We implement this method to use the {@link Collection::CountByKey()} method.
	 *
	 * @param mixed					$theHandle			Document handle.
	 * @return int					The number of selected documents.
	 *
	 * @uses Database()
	 */
	public function CountByHandle( $theHandle )
	{
		//
		// Decompose handle.
		//
		$handle = explode( '/', $theHandle );

		//
		// Check elements.
		//
		if( count( $handle ) != 2 )
			return 0;																// ==>~

		return
			$this->Database()->NewCollection( $handle[ 0 ] )
				->CountByKey( $handle[ 1 ] );										// ==>

	} // CountByHandle.


	/*===================================================================================
	 *	CountByExample																	*
	 *==================================================================================*/

	/**
	 * <h4>Find documents by example.</h4>
	 *
	 * We overload this method to execute
	 * {@link triagens\ArangoDb\CollectionHandler::byExample()} and call the
	 * {@link triagens\ArangoDb\Cursor::getCount()} method on the result.
	 *
	 * @param array					$theDocument		Example document as an array.
	 * @return int					The number of selected documents.
	 *
	 * @uses collectionName()
	 * @uses NewDocumentNative()
	 * @uses triagens\ArangoDb\CollectionHandler::byExample()
	 * @uses triagens\ArangoDb\Cursor::getCount()
	 */
	public function CountByExample( array $theDocument )
	{
		//
		// Normalise document.
		//
		$document = $this->NewDocumentNative( $theDocument );

		//
		// Select documents.
		//
		$result =
			$this->mCollectionHandler->byExample(
				$this->collectionName(), $document );

		return $result->getCount();													// ==>

	} // CountByExample.



/*=======================================================================================
 *																						*
 *								PUBLIC DELETION INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Delete																			*
	 *==================================================================================*/

	/**
	 * <h4>Delete documents by query.</h4>
	 *
	 * We overload this method to perform a selection query and delete the first or all
	 * selected documents by using the
	 * {@link triagens\ArangoDb\CollectionHandler::removeByKeys()} method.
	 *
	 * @param mixed					$theFilter			The deletion criteria.
	 * @param array					$theOptions			Query options.
	 * @return int					The number of deleted documents.
	 *
	 * @uses collectionName()
	 * @uses triagens\ArangoDb\Statement::execute()
	 * @uses triagens\ArangoDb\CollectionHandler::removeByKeys()
	 */
	public function Delete(
		$theFilter,
		array $theOptions = [ kTOKEN_OPT_MANY => TRUE ] )
	{
		//
		// Init query.
		//
		if( ! count( $theFilter ) )
			$theFilter = [
				'query' => 'FOR r IN @@collection RETURN r',
				'bindVars' => [ '@collection' => $this->collectionName() ] ];

		//
		// Perform query.
		//
		$statement = new ArangoStatement( $this->mDatabase->Connection(), $theFilter );
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
		// Remove by keys.
		//
		$result =
			$this->mCollectionHandler->removeByKeys(
				$this->collectionName(), $keys );

		return $result[ 'removed' ];												// ==>

	} // Delete.


	/*===================================================================================
	 *	DeleteByKey																		*
	 *==================================================================================*/

	/**
	 * <h4>Delete documents by key.</h4>
	 *
	 * We overload this method to use the
	 * {@link triagens\ArangoDb\CollectionHandler::removeByKeys()} method.
	 *
	 * @param mixed					$theKey				Single or set of document keys.
	 * @param array					$theOptions			Query options.
	 * @return int					The number of deleted documents.
	 *
	 * @uses collectionName()
	 * @uses triagens\ArangoDb\DocumentHandler::removeByKeys()
	 */
	public function DeleteByKey(
		$theKey,
		array $theOptions = [ kTOKEN_OPT_MANY => FALSE ] )
	{
		//
		// Normalise keys.
		//
		if( ! $theOptions[ kTOKEN_OPT_MANY ] )
			$theKey = [ $theKey ];
		else
			$theKey = (array)$theKey;

		//
		// Remove by keys.
		//
		$result =
			$this->mCollectionHandler->removeByKeys(
				$this->collectionName(), $theKey );

		return $result[ 'removed' ];												// ==>

	} // DeleteByKey.


	/*===================================================================================
	 *	DeleteByHandle																	*
	 *==================================================================================*/

	/**
	 * <h4>Delete documents by handle.</h4>
	 *
	 * We implement the method by aggregating the handles and calling the
	 * {@link triagens\ArangoDb\CollectionHandler::removeByKeys()} method.
	 *
	 * @param mixed					$theHandle			Single or set of document handles.
	 * @param array					$theOptions			Query options.
	 * @return int					The number of deleted documents.
	 *
	 * @uses triagens\ArangoDb\DocumentHandler::removeByKeys()
	 */
	public function DeleteByHandle(
		$theHandle,
		array $theOptions = [ kTOKEN_OPT_MANY => FALSE ] )
	{
		//
		// Normalise handles.
		//
		if( ! $theOptions[ kTOKEN_OPT_MANY ] )
			$theHandle = [ $theHandle ];
		else
			$theHandle = (array)$theHandle;

		//
		// Aggregate handles.
		//
		$handles = [];
		foreach( $theHandle as $handle )
		{
			//
			// Decompose handle.
			//
			$handle = explode( '/', $handle );

			//
			// Aggregate.
			//
			if( array_key_exists( $handle[ 0 ], $handles ) )
				$handles[ $handle[ 0 ] ] = $handle[ 1 ];
			else
				$handles[ $handle[ 0 ] ] = [ $handle[ 1 ] ];
		}

		//
		// Iterate handles.
		//
		$count = 0;
		foreach( $handles as $collection => $keys )
		{
			//
			// Remove by keys.
			//
			$result = $this->mCollectionHandler->removeByKeys( $collection, $keys );

			//
			// Increment.
			//
			$count += $result[ 'removed' ];
		}

		return $count;																// ==>

	} // DeleteByHandle.


	/*===================================================================================
	 *	DeleteByExample																	*
	 *==================================================================================*/

	/**
	 * <h4>Delete documents by example.</h4>
	 *
	 * We overload this method to use the
	 * {@link triagens\ArangoDb\CollectionHandler::removeByExample()} method.
	 *
	 * @param array					$theDocument		Example document as an array.
	 * @param array					$theOptions			Query options.
	 * @return int					The number of deleted documents.
	 *
	 * @uses collectionName()
	 * @uses NewDocumentNative()
	 * @uses triagens\ArangoDb\CollectionHandler::removeByExample()
	 */
	public function DeleteByExample(
		array $theDocument,
		array $theOptions = [ kTOKEN_OPT_MANY => TRUE ] )
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
		$document = $this->NewDocumentNative( $theDocument );

		return
			$this->mCollectionHandler->removeByExample(
				$this->collectionName(), $document, $options );						// ==>

	} // DeleteByExample.



/*=======================================================================================
 *																						*
 *							PUBLIC HANDLE PARSING INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	ParseDocumentHandle																*
	 *==================================================================================*/

	/**
	 * <h4>Return handle components.</h4>
	 *
	 * We implement this method by assuming the handle is string with the collectionname
	 * concatenated to the document key separated by a slash.
	 *
	 * @param mixed					$theHandle			The object handle.
	 * @param string			   &$theCollection		Receives collection name.
	 * @param mixed				   &$theIdentifier		Receives object key.
	 */
	public function ParseDocumentHandle( $theHandle, &$theCollection, &$theIdentifier )
	{
		//
		// Parse handle.
		//
		$handle = explode( '/', $theHandle );

		//
		// Extract components.
		//
		$theCollection = $handle[ 0 ];
		$theIdentifier = $handle[ 1 ];

	} // ParseDocumentHandle.



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
		// Return existing collection.
		//
		if( $this->mCollectionHandler->has( $theCollection ) )
			return $this->mCollectionHandler->get( $theCollection );				// ==>

		return
			$this->mCollectionHandler->get(
				$this->mCollectionHandler->create( $theCollection, $theOptions ) );	// ==>

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
	protected function documentHandleCreate( $theKey )
	{
		return $this->collectionName() . '/' . (string)$theKey;						// ==>

	} // documentHandleCreate.



/*=======================================================================================
 *																						*
 *							PROTECTED PERSISTENCE INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	documentInsert																	*
	 *==================================================================================*/

	/**
	 * <h4>Insert a document.</h4>
	 *
	 * We implement this method by using the
	 * {@link triagens\ArangoDb\DocumentHandler::save()} method to insert the document.
	 *
	 * @param mixed					$theDocument		The native document to insert.
	 * @return mixed				The inserted document key.
	 *
	 * @uses triagens\ArangoDb\DocumentHandler::save()
	 */
	protected function documentInsert( $theDocument )
	{
		return $this->mDocumentHandler->save( $this->mConnection, $theDocument );	// ==>

	} // documentInsert.


	/*===================================================================================
	 *	documentReplace																	*
	 *==================================================================================*/

	/**
	 * <h4>Replace a document.</h4>
	 *
	 * This method should replace the provided document in the current collection and
	 * return <tt>1</tt> if the document was repaled, or <tt>0</tt>.
	 *
	 * The method expects the document key and the replacement document in the database
	 * native format.
	 *
	 * @param mixed					$theKey				The document key.
	 * @param mixed					$theDocument		The replacement native document.
	 * @return mixed				The number of replaced documents.
	 */
	protected function documentReplace( $theKey, $theDocument )
	{
		//
		// Assert document exists.
		//
		try
		{
			//
			// Replace document.
			//
			$this->mDocumentHandler
				->replaceById( $this->collectionName(), $theKey, $theDocument );

			return 1;																// ==>

		} // Found document.

		//
		// Handle missing document.
		//
		catch( ArangoServerException $error )
		{
			//
			// Skip not found.
			//
			if( $error->getCode() != 404 )
				throw $error;													// !@! ==>

		} // Document not found.

		return 0;																	// ==>

	} // documentReplace.



/*=======================================================================================
 *																						*
 *								PROTECTED PERSISTENCE UTILITIES							*
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
	 * @uses triagens\ArangoDb\Document::getRevision()
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
	 *	normaliseReplacedDocument														*
	 *==================================================================================*/

	/**
	 * <h4>Normalise replaced document.</h4>
	 *
	 * We overload this method to update the revision ({@link RevisionOffset()}) in the
	 * replaced document.
	 *
	 * @param mixed					$theDocument		The replaced document.
	 * @param mixed						$theData		The native database document.
	 *
	 * @uses RevisionOffset()
	 * @uses triagens\ArangoDb\Document::getRevision()
	 */
	protected function normaliseReplacedDocument( $theDocument, $theData )
	{
		//
		// Set document revision.
		//
		if( $theDocument instanceof \ArrayObject )
			$theDocument->offsetSet( $this->RevisionOffset(), $theData->getRevision() );

		//
		// Call parent method.
		//
		parent::normaliseReplacedDocument( $theDocument, $theData );

	} // normaliseReplacedDocument.


	/*===================================================================================
	 *	normaliseSelectedDocument														*
	 *==================================================================================*/

	/**
	 * <h4>Normalise selected document.</h4>
	 *
	 * We overload this method to retrieve and set the revision ({@link RevisionOffset()})
	 * back into the document.
	 *
	 * @param mixed					$theDocument		The selected document.
	 * @param mixed					$theData			The native database document.
	 *
	 * @uses RevisionOffset()
	 * @uses triagens\ArangoDb\Document::getRevision()
	 */
	protected function normaliseSelectedDocument( $theDocument, $theData )
	{
		//
		// Set document revision.
		//
		if( $theDocument instanceof \ArrayObject )
			$theDocument->offsetSet( $this->RevisionOffset(), $theData->getRevision() );

		//
		// Call parent method.
		//
		parent::normaliseSelectedDocument( $theDocument, $theData );

	} // normaliseSelectedDocument.


	/*===================================================================================
	 *	normaliseDeletedDocument														*
	 *==================================================================================*/

	/**
	 * <h4>Normalise deleted document.</h4>
	 *
	 * We overload this method to first call the parent method, which will reset the
	 * document persistent state, allowing this method to remove the revision
	 * ({@link RevisionOffset()}) from the document.
	 *
	 * @param mixed					$theDocument		The deleted document.
	 *
	 * @uses RevisionOffset()
	 */
	protected function normaliseDeletedDocument( $theDocument )
	{
		//
		// Call parent method.
		//
		parent::normaliseDeletedDocument( $theDocument );

		//
		// Remove document revision.
		//
		if( $theDocument instanceof \ArrayObject )
			$theDocument->offsetUnset( $this->RevisionOffset() );

	} // normaliseDeletedDocument.



} // class Collection.


?>
