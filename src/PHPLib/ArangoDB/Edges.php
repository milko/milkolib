<?php

/**
 * Edges.php
 *
 * This file contains the definition of the {@link Relations} class.
 */

namespace Milko\PHPLib\ArangoDB;

/*=======================================================================================
 *																						*
 *										Edges.php										*
 *																						*
 *======================================================================================*/

use Milko\PHPLib\ArangoDB\Collection;
use Milko\PHPLib\Document;
use Milko\PHPLib\iEdges;
use Milko\PHPLib\Edge;

use triagens\ArangoDb\Collection as ArangoCollection;
use triagens\ArangoDb\CollectionHandler as ArangoCollectionHandler;
use triagens\ArangoDb\EdgeHandler as ArangoEdgeHandler;
use triagens\ArangoDb\Edge as ArangoEdge;
use triagens\ArangoDb\ServerException as ArangoServerException;
use triagens\ArangoDb\Statement as ArangoStatement;

/**
 * <h4>Relationships collection object.</h4>
 *
 * This <em>concrete</em> class is the implementation of a ArangoDB edge collection, it
 * overloads the inherited {@link Collection} interface and implements the
 * {@link iEdges} interface.
 *
 *	@package	Data
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		30/03/2016
 */
class Edges extends Collection
			implements iEdges
{
	/**
	 * <h4>Edge handler.</h4>
	 *
	 * This data member holds the edge handler.
	 *
	 * @var ArangoEdgeHandler
	 */
	protected $mEdgeHandler = NULL;




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
	 * We overload this method to instantiate the edge handler used for database operations,
	 * we store it in an object attribute to prevent having to instantiate it each time an
	 * operation requires it.
	 *
	 * @param Database				$theDatabase		Database.
	 * @param string				$theCollection		Collection name.
	 * @param array					$theOptions			Native driver options.
	 */
	public function __construct( Database $theDatabase, $theCollection, $theOptions = NULL )
	{
		//
		// Store edge handler.
		//
		$this->mEdgeHandler
			= new ArangoEdgeHandler( $theDatabase->Connection() );

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
	 *	VertexSource																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the relationship source offset.</h4>
	 *
	 * We overload this method to use the {@link kTAG_ARANGO_REL_FROM} constant.
	 *
	 * @return mixed				Source vertex document handle.
	 *
	 * @see kTAG_ARANGO_REL_FROM
	 */
	public function VertexSource()						{	return kTAG_ARANGO_REL_FROM;	}


	/*===================================================================================
	 *	VertexDestination																*
	 *==================================================================================*/

	/**
	 * <h4>Return the relationship destination offset.</h4>
	 *
	 * We overload this method to use the {@link kTAG_ARANGO_REL_TO} constant.
	 *
	 * @return mixed				Destination vertex document handle.
	 *
	 * @see kTAG_ARANGO_REL_TO
	 */
	public function VertexDestination()						{	return kTAG_ARANGO_REL_TO;	}



/*=======================================================================================
 *																						*
 *							PUBLIC INTERNAL OFFSETS INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	GetInternalOffsets																*
	 *==================================================================================*/

	/**
	 * <h4>Return internal offsets.</h4>
	 *
	 * We implement this method to add the vertex property offsets.
	 *
	 * @return array				List of internal offsets.
	 */
	public function GetInternalOffsets()
	{
		return
			array_merge(
				parent::GetInternalOffsets(),
				[ $this->VertexSource(), $this->VertexDestination() ] );			// ==>

	} // GetInternalOffsets.



/*=======================================================================================
 *																						*
 *							PUBLIC DOCUMENT MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	NewDocumentNative																*
	 *==================================================================================*/

	/**
	 * <h4>Convert a standard document to native data.</h4>
	 *
	 * We overload this method to return the eventual {@link triagens\ArangoDb\Edge}
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
		if( $theData instanceof ArangoEdge )
			return $theData;														// ==>

		return parent::NewDocumentNative( $theData );								// ==>

	} // NewDocumentNative.


	/*===================================================================================
	 *	NewDocumentArray																*
	 *==================================================================================*/

	/**
	 * <h4>Return an array from a document.</h4>
	 *
	 * We overload this method to handle {@link triagens\ArangoDb\Edge} instances.
	 *
	 * @param mixed					$theData			Document data.
	 * @return array				Document as array.
	 *
	 * @uses KeyOffset()
	 * @uses RevisionOffset()
	 * @uses VertexSource()
	 * @uses VertexDestination()
	 */
	public function NewDocumentArray( $theData )
	{
		//
		// Handle Edges.
		//
		if( $theData instanceof ArangoEdge )
		{
			//
			// Call parent method.
			//
			$document = parent::NewDocumentArray( $theData );

			//
			// Set incoming vertex.
			//
			if( ($tmp = $theData->getFrom()) !== NULL )
				$document[ $this->VertexSource() ] = $tmp;

			//
			// Set incoming vertex.
			//
			if( ($tmp = $theData->getTo()) !== NULL )
				$document[ $this->VertexDestination() ] = $tmp;
			
			return $document;														// ==>
			
		} // Is an edge.

		return parent::NewDocumentArray( $theData );								// ==>

	} // NewDocumentArray.



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
	 * The method expects the source and destination vertices to be contained in the edges.
	 *
	 * @param mixed					$theDocuments		The native documents set.
	 * @return array				The document unique identifiers.
	 *
	 * @uses collectionName()
	 * @uses triagens\ArangoDb\Edge::getTo()
	 * @uses triagens\ArangoDb\Edge::getFrom()
	 * @uses triagens\ArangoDb\EdgetHandler::saveEdge()
	 */
	public function InsertBulk( $theDocuments )
	{
		//
		// Init list.
		//
		$ids = [];

		//
		// Iterate documents.
		//
		foreach( $theDocuments as $document )
			$ids[] =
				$this->mEdgeHandler->saveEdge(
					$this->collectionName(),
					$document->getFrom(),
					$document->getTo(),
					$document );

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
	 * @uses triagens\ArangoDb\EdgeHandler::updateById()
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
				// Get document key.
				//
				$edgeKey = $document->getKey();

				//
				// Update document.
				//
				foreach( $theCriteria as $key => $value )
					$document->set( $key, $value );

				//
				// Update document.
				//
				$this->mEdgeHandler->updateById(
					$this->collectionName(),
					$edgeKey,
					$document,
					[ 'keepNull' => FALSE ] );

				//
				// Handle only first.
				//
				if( ! $theOptions[ kTOKEN_OPT_MANY ] )
					return 1;														// ==>

			} // Iterating edges.

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
	 * @uses triagens\ArangoDb\EdgeHandler::updateById()
	 * @uses triagens\ArangoDb\Document::set()
	 */
	public function UpdateByExample( array $theCriteria,
									 array $theDocument,
									 array $theOptions = [ kTOKEN_OPT_MANY => TRUE ] )
	{
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
				// Get document key.
				//
				$edgeKey = $document->getKey();

				//
				// Update document.
				//
				foreach( $theCriteria as $key => $value )
					$document->set( $key, $value );

				//
				// Update document.
				//
				$this->mEdgeHandler->updateById(
					$this->collectionName(),
					$edgeKey,
					$document,
					[ 'keepNull' => FALSE ] );

				//
				// Handle only first.
				//
				if( ! $theOptions[ kTOKEN_OPT_MANY ] )
					return 1;														// ==>

			} // Iterating edges.

		} // Non empty selection.

		return $count;																// ==>

	} // UpdateByExample.



/*=======================================================================================
 *																						*
 *								PUBLIC SELECTION INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	FindByKey																		*
	 *==================================================================================*/

	/**
	 * <h4>Find documents by key.</h4>
	 *
	 * We overload this method to use the {@link triagens\ArangoDb\EdgeHandler::getById()}.
	 *
	 * @param mixed					$theKey				Single or set of document keys.
	 * @param array					$theOptions			Query options.
	 * @return mixed				The found records.
	 *
	 * @uses ConvertDocumentSet()
	 * @uses formatDocument()
	 * @uses collectionName()
	 * @uses triagens\ArangoDb\EdgeHandler::getById()
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
			// Init list.
			//
			$result = [];

			//
			// Iterate keys.
			//
			foreach( $theKey as $key )
			{
				//
				// Try finding edge.
				//
				try
				{
					//
					// Find edge.
					//
					$result[] =
						$this->mEdgeHandler->getById(
							$this->collectionName(), $key );

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

			} // Iterating edges.

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
			// Find edge.
			//
			$result =
				$this->mEdgeHandler->getById(
					$this->collectionName(), $theKey );

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
				// Try finding edge.
				//
				try
				{
					//
					// Find document.
					//
					$document =
						$this->mEdgeHandler->getById(
							$handle[ 0 ], $handle[ 1 ] );

					//
					// Format document.
					//
					$result[] =
						$this->formatDocument(
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
			$document =
				$this->mEdgeHandler->getById(
					$handle[ 0 ], $handle[ 1 ] );

			return
				$this->formatDocument(
					$document, $theOptions[ kTOKEN_OPT_FORMAT ], TRUE );			// ==>

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
	 * We implement this method to perform a selection query and delete the first or all
	 * selected edges by using the
	 * {@link triagens\ArangoDb\EdgeHandler::removeById()} method.
	 *
	 * @param mixed					$theFilter			The deletion criteria.
	 * @param array					$theOptions			Query options.
	 * @return int					The number of deleted documents.
	 *
	 * @uses collectionName()
	 * @uses triagens\ArangoDb\Statement::execute()
	 * @uses triagens\ArangoDb\EdgeHandler::removeById()
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
		$count = $cursor->getCount();

		//
		// Handle non empty result.
		//
		if( $count )
		{
			//
			// Iterate edges.
			//
			foreach( $cursor as $document )
			{
				//
				// Delete edge.
				// Note that an exception will be triggered if the edge was not found,
				// we want this since we are iterating a selection.
				//
				$this->mEdgeHandler->removeById(
					$this->collectionName(),
					$document->getKey(),
					$document->getRevision() );

				//
				// Handle only one.
				//
				if( ! $theOptions[ kTOKEN_OPT_MANY ] )
					break;														// =>

			} // Iterating edges.

		} // Found edges.

		return $count;																// ==>

	} // Delete.


	/*===================================================================================
	 *	DeleteByKey																		*
	 *==================================================================================*/

	/**
	 * <h4>Delete documents by key.</h4>
	 *
	 * We implement this method to use the
	 * {@link triagens\ArangoDb\EdgeHandler::removeById()} method.
	 *
	 * @param mixed					$theKey				Single or set of document keys.
	 * @param array					$theOptions			Query options.
	 * @return int					The number of deleted documents.
	 *
	 * @uses collectionName()
	 * @uses triagens\ArangoDb\EdgeHandler::removeById()
	 */
	public function DeleteByKey(
		$theKey,
		array $theOptions = [ kTOKEN_OPT_MANY => FALSE ] )
	{
		//
		// Init local storage.
		//
		$count = 0;
		if( ! $theOptions[ kTOKEN_OPT_MANY ] )
			$theKey = [ $theKey ];

		//
		// Iterate keys.
		//
		foreach( $theKey as $key )
		{
			//
			// Remove by keys.
			//
			try
			{
				//
				// Delete edge.
				//
				$this->mEdgeHandler->removeById( $this->collectionName(), $key );

				$count++;
			}

			//
			// Handle not found.
			//
			catch( ArangoServerException $error )
			{
				//
				// Handle not found.
				//
				if( $error->getCode() != 404 )
					throw $error;												// !@! ==>
			}

		} // Iterating key set.

		return $count;																// ==>

	} // DeleteByKey.


	/*===================================================================================
	 *	DeleteByHandle																	*
	 *==================================================================================*/

	/**
	 * <h4>Delete documents by handle.</h4>
	 *
	 * We implement the method by aggregating the handles and calling the
	 * {@link triagens\ArangoDb\EdgeHandler::removeById()} method.
	 *
	 * @param mixed					$theHandle			Single or set of document handles.
	 * @param array					$theOptions			Query options.
	 * @return int					The number of deleted documents.
	 *
	 * @uses triagens\ArangoDb\EdgeHandler::removeById()
	 */
	public function DeleteByHandle(
		$theHandle,
		array $theOptions = [ kTOKEN_OPT_MANY => FALSE ] )
	{
		//
		// Init local storage.
		//
		$count = 0;
		if( ! $theOptions[ kTOKEN_OPT_MANY ] )
			$theHandle = [ $theHandle ];

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
		// Iterate collections.
		//
		$count = 0;
		foreach( $handles as $collection => $keys )
		{
			//
			// Iterate collection keys.
			//
			foreach( $keys as $key )
			{
				//
				// Remove by keys.
				//
				try
				{
					//
					// Delete edge.
					//
					$this->mEdgeHandler->removeById( $collection, $key() );

					$count++;
				}

				//
				// Handle not found.
				//
				catch( ArangoServerException $error )
				{
					//
					// Handle not found.
					//
					if( $error->getCode() != 404 )
						throw $error;											// !@! ==>
				}

			} // Iterating collection keys.

		} // Iterating collections.

		return $count;																// ==>

	} // DeleteByHandle.


	/*===================================================================================
	 *	DeleteByExample																	*
	 *==================================================================================*/

	/**
	 * <h4>Delete documents by example.</h4>
	 *
	 * We implement the method by selecting edges using
	 * {@link triagens\ArangoDb\CollectionHandler::byExample()}, we then iterate the result
	 * and call  {@link triagens\ArangoDb\EdgeHandler::removeById()} to remove the edges.
	 *
	 * @param array					$theDocument		Example document as an array.
	 * @param array					$theOptions			Query options.
	 * @return int					The number of deleted documents.
	 *
	 * @uses collectionName()
	 * @uses NewDocumentNative()
	 * @uses triagens\ArangoDb\CollectionHandler::byExample()
	 * @uses triagens\ArangoDb\EdgeHandler::removeById()
	 * @uses triagens\ArangoDb\Cursor::getCount()
	 */
	public function DeleteByExample(
		array $theDocument,
		array $theOptions = [ kTOKEN_OPT_MANY => TRUE ] )
	{
		//
		// Init local storage.
		//
		$options = ( $theOptions[ kTOKEN_OPT_MANY ] )
			? []
			: [ "limit" => 1 ];

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
		// Handle found edges.
		//
		if( $result->getCount() )
		{
			//
			// Iterate result.
			//
			foreach( $result as $edge )
			{
				//
				// Delete edge.
				// Note that an exception will be triggered if the edge was not found,
				// we want this since we are iterating a selection.
				//
				$this->mEdgeHandler->removeById(
					$this->collectionName(), $edge->getKey(), $edge->getRevision() );

			} // Iterating edges.

		} // Found edges.

		return $result->getCount();													// ==>

	} // DeleteByExample.



/*=======================================================================================
 *																						*
 *							PUBLIC GRAPH MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	FindByVertex																	*
	 *==================================================================================*/

	/**
	 * <h4>Find by vertex.</h4>
	 *
	 * We overload this method to use the {@link ArangoEdgeHandler::edges()} method.
	 *
	 * @param mixed					$theVertex			The vertex document or handle.
	 * @param array					$theOptions			Find options.
	 * @return array				The found documents.
	 *
	 * @uses collectionName()
	 * @uses NewDocumentHandle()
	 * @uses ConvertDocumentSet()
	 * @uses triagens\ArangoDb\Document::getHandle()
	 * @uses triagens\ArangoDb\EdgeHandler::edges()
	 */
	public function FindByVertex(
		$theVertex,
		array $theOptions = [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_DOCUMENT,
							  kTOKEN_OPT_DIRECTION => kTOKEN_OPT_DIRECTION_ANY ] )
	{
		//
		// Get vertex handle.
		//
		$handle = ( $theVertex instanceof \ArrayObject )
				? $this->NewDocumentHandle( $theVertex )
				: $theVertex->getHandle();

		//
		// Select connected edges.
		//
		$result =
			$this->mEdgeHandler->edges(
				$this->collectionName(), $handle, $theOptions[ kTOKEN_OPT_DIRECTION ] );

		return
			$this->ConvertDocumentSet(
				$result, $theOptions[ kTOKEN_OPT_FORMAT ], TRUE );					// ==>

	} // FindByVertex.



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
	 * We overload the inherited method to return an edges collection. When we create it,
	 * we ensure it is of the correct type; when we retrieve it, we raise an exception if it
	 * is not of the correct type (<tt>3</tt>).
	 *
	 * @param string				$theCollection		Collection name.
	 * @param array					$theOptions			Driver native options.
	 * @return mixed				Native collection object.
	 * @throws \InvalidArgumentException
	 */
	protected function collectionCreate( $theCollection, $theOptions = NULL )
	{
		//
		// Normalise options.
		//
		if( $theOptions === NULL )
			$theOptions = [];

		//
		// Set edge type.
		//
		$theOptions[ "type" ] = ArangoCollection::TYPE_EDGE;

		//
		// Call parent method.
		//
		$collection = parent::collectionCreate( $theCollection, $theOptions );

		//
		// Assert the collection type.
		//
		if( $collection->getType() != ArangoCollection::TYPE_EDGE )
			throw new \InvalidArgumentException (
				"Invalid collection type: "
				."expecting an edge collection." );								// !@! ==>

		return $collection;															// ==>

	} // collectionNew.



/*=======================================================================================
 *																						*
 *								PROTECTED CONVERSION UTILITIES							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	documentCreate																	*
	 *==================================================================================*/

	/**
	 * <h4>Return a standard database document.</h4>
	 *
	 * In this class we return an {@link Edge} instance, in derived classes you can
	 * overload this method to return a different kind of standard document.
	 *
	 * @param array					$theData			Document as an array.
	 * @return mixed				Standard document object.
	 */
	protected function documentCreate( array $theData )
	{
		return new Edge( $this, $theData );											// ==>

	} // documentCreate.


	/*===================================================================================
	 *	documentNativeCreate															*
	 *==================================================================================*/

	/**
	 * <h4>Return a native database document.</h4>
	 *
	 * We implement this method to create a {@link triagens\ArangoDb\Edge} instance, we
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
		// Create an ArangoEdge.
		//
		$document = ArangoEdge::createFromArray( $theData );

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

		//
		// Set source vertex.
		//
		if( ($document->getFrom() === NULL)
		 && array_key_exists( $this->VertexSource(), $theData ) )
			$document->setFrom( $theData[ $this->VertexSource() ] );

		//
		// Set destination vertex.
		//
		if( ($document->getTo() === NULL)
		 && array_key_exists( $this->VertexDestination(), $theData ) )
			$document->setFrom( $theData[ $this->VertexDestination() ] );

		return $document;															// ==>

	} // documentNativeCreate.



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
	 * We implement this method by extracting the vertices from the native document and
	 * calling the {@link triagens\ArangoDb\EdgeHandler::saveEdge()} method to insert the
	 * document.
	 *
	 * @param mixed					$theDocument		The native document to insert.
	 * @return mixed				The inserted document key.
	 *
	 * @uses collectionName()
	 * @uses triagens\ArangoDb\Edge::getTo()
	 * @uses triagens\ArangoDb\Edge::getFrom()
	 * @uses triagens\ArangoDb\EdgeHandler::saveEdge()
	 */
	protected function documentInsert( $theDocument )
	{
		//
		// Get vertices.
		//
		if( ($srcVertex = $theDocument->getFrom()) === NULL )
			throw new \RuntimeException (
				"Missing source vertex." );										// !@! ==>

		if( ($dstVertex = $theDocument->getTo()) === NULL )
			throw new \RuntimeException (
				"Missing destination vertex." );								// !@! ==>

		return
			$this->mEdgeHandler->saveEdge(
				$this->collectionName(), $srcVertex, $dstVertex, $theDocument );	// ==>

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
			// Replace edge.
			//
			$this->mEdgeHandler
				->replaceById(
					$this->collectionName(), $theKey, $theDocument );

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
 *								PROTECTED GENERIC UTILITIES								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	normaliseInsertedDocument														*
	 *==================================================================================*/

	/**
	 * <h4>Normalise inserted document.</h4>
	 *
	 * We overload this method to retrieve and set the source ({@link VertexIn()}) and
	 * destination ({@link VertexOut()}) vertices.
	 *
	 * @param mixed					$theDocument		The inserted document.
	 * @param mixed					$theData			The native inserted document.
	 * @param mixed					$theKey				The document key.
	 *
	 * @uses Document::VertexSource()
	 * @uses Document::VertexDestination()
	 */
	protected function normaliseInsertedDocument( $theDocument, $theData, $theKey )
	{
		//
		// Set source and destination vertices.
		//
		if( $theDocument instanceof \ArrayObject )
		{
			//
			// Get vertices.
			//
			if( $theData instanceof ArangoEdge )
			{
				$dst = $theData->getTo();
				$src = $theData->getFrom();
			}
			else
			{
				$src = $theData->get( $this->VertexSource() );
				$dst = $theData->get( $this->VertexDestination() );
			}

			//
			// Set vertices.
			//
			$theDocument->offsetSet( $this->VertexSource(), $src );
			$theDocument->offsetSet( $this->VertexDestination(), $dst );
		}

		//
		// Call parent method.
		//
		parent::normaliseInsertedDocument( $theDocument, $theData, $theKey );

	} // normaliseInsertedDocument.


	/*===================================================================================
	 *	normaliseSelectedDocument														*
	 *==================================================================================*/

	/**
	 * <h4>Normalise selected document.</h4>
	 *
	 * We overload this method to retrieve and set the source ({@link VertexIn()}) and
	 * destination ({@link VertexOut()}) vertices.
	 *
	 * @param \Milko\PHPLib\Container	$theDocument	The selected document.
	 * @param mixed						$theData		The native database document.
	 *
	 * @uses Document::VertexSource()
	 * @uses Document::VertexDestination()
	 */
	protected function normaliseSelectedDocument( $theDocument, $theData )
	{
		//
		// Set source and destination vertices.
		//
		if( $theDocument instanceof \ArrayObject )
		{
			//
			// Get vertices.
			//
			if( $theData instanceof ArangoEdge )
			{
				$dst = $theData->getTo();
				$src = $theData->getFrom();
			}
			else
			{
				$src = $theData->get( $this->VertexSource() );
				$dst = $theData->get( $this->VertexDestination() );
			}

			//
			// Set vertices.
			//
			$theDocument->offsetSet( $this->VertexSource(), $src );
			$theDocument->offsetSet( $this->VertexDestination(), $dst );
		}

		//
		// Call parent method.
		//
		parent::normaliseSelectedDocument( $theDocument, $theData );

	} // normaliseSelectedDocument.



} // class Edges.


?>
