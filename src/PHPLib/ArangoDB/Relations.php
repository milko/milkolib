<?php

/**
 * Relations.php
 *
 * This file contains the definition of the {@link Relations} class.
 */

namespace Milko\PHPLib\ArangoDB;

use Milko\PHPLib\Collection;
use Milko\PHPLib\iRelations;

use triagens\ArangoDb\Database as ArangoDatabase;
use triagens\ArangoDb\Collection as ArangoCollection;
use triagens\ArangoDb\CollectionHandler as ArangoCollectionHandler;
use triagens\ArangoDb\Endpoint as ArangoEndpoint;
use triagens\ArangoDb\Connection as ArangoConnection;
use triagens\ArangoDb\ConnectionOptions as ArangoConnectionOptions;
use triagens\ArangoDb\DocumentHandler as ArangoDocumentHandler;
use triagens\ArangoDb\Document as ArangoDocument;
use triagens\ArangoDb\EdgeHandler as ArangoEdgeHandler;
use triagens\ArangoDb\Edge as ArangoEdge;
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
 *									Relations.php										*
 *																						*
 *======================================================================================*/

/**
 * <h4>Relationships collection object.</h4>
 *
 * This <em>concrete</em> class is the implementation of a ArangoDB edge collection, it
 * overloads the inherited {@link Collection} interface and implements the
 * {@link iRelations} interface.
 *
 *	@package	Data
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		30/03/2016
 */
class Relations extends \Milko\PHPLib\ArangoDB\Collection
				implements \Milko\PHPLib\iRelations
{



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
	 * We overload this method to get the source and destination vertices from the
	 * {@link ArangoEdge} object.
	 *
	 * @param mixed						$theData			Database native document.
	 * @param string					$theClass			Expected class name.
	 * @return \Milko\PHPLib\Container	Standard document object.
	 *
	 * @uses VertexIn()
	 * @uses VertexOut()
	 * @uses ArangoEdge::getTo()
	 * @uses ArangoEdge::getFrom()
	 */
	public function NewDocument( $theData, $theClass = NULL )
	{
		//
		// Call parent method.
		//
		$document = parent::NewDocument( $theData, $theClass );

		//
		// Get source and destination vertices.
		//
		if( $theData instanceof ArangoEdge )
		{
			//
			// Set incoming vertex.
			//
			if( ($tmp = $theData->getFrom()) !== NULL )
				$document[ $this->VertexIn() ] = $tmp;

			//
			// Set incoming vertex.
			//
			if( ($tmp = $theData->getTo()) !== NULL )
				$document[ $this->VertexOut() ] = $tmp;

		} // ArangoDocument.

		return $document;															// ==>

	} // NewDocument.



/*=======================================================================================
 *																						*
 *							PUBLIC OFFSET DECLARATION INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	VertexIn																		*
	 *==================================================================================*/

	/**
	 * <h4>Return the relationship source offset.</h4>
	 *
	 * We overload this method to use the {@link kTAG_ARANGO_REL_FROM} constant.
	 *
	 * @return mixed				Source vertex document handle.
	 */
	public function VertexIn()							{	return kTAG_ARANGO_REL_FROM;	}


	/*===================================================================================
	 *	VertexOut																		*
	 *==================================================================================*/

	/**
	 * <h4>Return the relationship destination offset.</h4>
	 *
	 * We overload this method to use the {@link kTAG_ARANGO_REL_TO} constant.
	 *
	 * @return mixed				Destination vertex document handle.
	 */
	public function VertexOut()								{	return kTAG_ARANGO_REL_TO;	}



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
	 * We overload the inherited method to return an edges collection. When we create it,
	 * we ensure it is of the correct type; when we retrieve it, we raise an exception if it
	 * is not of the correct type (<tt>3</tt>).
	 *
	 * @param string				$theCollection		Collection name.
	 * @param array					$theOptions			Driver native options.
	 * @return mixed				Native collection object.
	 * @throws \InvalidArgumentException
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
		{
			//
			// Assert the collection type.
			//
			$collection = $handler->get( $theCollection );
			if( $collection->getType() != 3 )
				throw new \InvalidArgumentException (
					"Invalid collection type: "
				   ."expecting an edge collection." );							// !@! ==>

			return $collection;														// ==>
		}

		//
		// Create collection.
		//
		$id = $handler->create( $theCollection, [ "type" => 3 ] );

		return $handler->get( $id );												// ==>

	} // collectionNew.



/*=======================================================================================
 *																						*
 *						PROTECTED RECORD MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	doInsertOne																		*
	 *==================================================================================*/

	/**
	 * <h4>Insert a document.</h4>
	 *
	 * We overload this method to extract the source and destination vertices from the
	 * document properties and pass them to the {@link ArangoEdgeHandler::saveEdge()}
	 * method.
	 *
	 * @param mixed					$theDocument		The document to be inserted.
	 * @return mixed				The inserted document's key.
	 * @throws \InvalidArgumentException
	 *
	 * @uses Database()
	 * @uses collectionName()
	 * @uses NewNativeDocument()
	 * @uses \Milko\PHPLib\Document::Validate()
	 * @uses \Milko\PHPLib\Document::ResolveRelated()
	 * @uses triagens\ArangoDb\DocumentHandler::saveEdge()
	 */
	protected function doInsertOne( $theDocument )
	{
		//
		// Prepare document.
		//
		if( $theDocument instanceof \Milko\PHPLib\Document )
		{
			//
			// Validate document.
			//
			$theDocument->Validate();

			//
			// Store sub-documents.
			//
			$theDocument->ResolveRelated();
		}
		
		//
		// Init local storage.
		//
		$document = $this->NewNativeDocument( $theDocument );

		//
		// Get vertices.
		//
		if( ($srcVertex = $document->getFrom()) === NULL )
			throw new \InvalidArgumentException (
				"Missing source vertex." );										// !@! ==>
		if( ($dstVertex = $document->getTo()) === NULL )
			throw new \InvalidArgumentException (
				"Missing destination vertex." );								// !@! ==>

		//
		// Instantiate edge handler.
		//
		$handler = new ArangoEdgeHandler( $this->Database()->Connection() );

		//
		// Save document.
		//
		$key =
			$handler->saveEdge(
				$this->collectionName(), $srcVertex, $dstVertex, $document );

		//
		// Normalise inserted document.
		//
		if( $theDocument instanceof \Milko\PHPLib\Container )
			$this->normaliseInsertedDocument( $theDocument, $document );

		return $key;																// ==>

	} // doInsertOne.


	/*===================================================================================
	 *	doInsertBulk																	*
	 *==================================================================================*/

	/**
	 * <h4>Insert a list of documents.</h4>
	 *
	 * We overload this method to iterate the provided list and call the
	 * {@link doInsertOne()} method on each document.
	 *
	 * @param array					$theList			The documents list.
	 * @return array				The document keys.
	 *
	 * @uses doInsertOne()
	 */
	protected function doInsertBulk( array $theList )
	{
		//
		// Init local storage.
		//
		$ids = [];

		//
		// Iterate documents.
		//
		foreach( $theList as $document )
			$ids[] = $this->doInsertOne( $document );

		return $ids;																// ==>

	} // doInsertBulk.



/*=======================================================================================
 *																						*
 *							PROTECTED DOCUMENT DELETE INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	doDeleteOne																		*
	 *==================================================================================*/

	/**
	 * <h4>Delete a document.</h4>
	 *
	 * We overload this method to call the
	 * {@link triagens\ArangoDb\EdgeHandler::removeById()} method.
	 *
	 * @param mixed					$theDocument		The document to be deleted.
	 * @return mixed				The number of deleted documents.
	 *
	 * @uses Database()
	 * @uses collectionName()
	 * @uses NewNativeDocument()
	 * @uses normaliseDeletedDocument()
	 * @uses triagens\ArangoDb\EdgeHandler::removeById()
	 * @see kTOKEN_OPT_FORMAT
	 * @see kTOKEN_OPT_FORMAT_NATIVE
	 */
	protected function doDeleteOne( $theDocument )
	{
		//
		// Convert document.
		//
		$document = $this->NewNativeDocument( $theDocument );

		//
		// Check document key.
		//
		if( ($id = $document->getKey()) !== NULL )
		{
			//
			// Instantiate edge handler.
			//
			$handler = new ArangoEdgeHandler( $this->Database()->Connection() );

			//
			// Remove document.
			//
			$count = 0;
			try
			{
				//
				// Try to delete document.
				//
				$handler->removeById(
					$this->collectionName(), $id, $document->getRevision() );

				//
				// Normalise deleted document.
				//
				if( $theDocument instanceof \Milko\PHPLib\Container )
					$this->normaliseDeletedDocument( $theDocument );

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

	} // doDeleteOne.



/*=======================================================================================
 *																						*
 *							PROTECTED SELECTION MANAGEMENT INTERFACE					*
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
	 * @throws \InvalidArgumentException
	 *
	 * @uses Database()
	 * @uses NewDocument()
	 * @uses NewDocumentKey()
	 * @uses NewDocumentHandle()
	 * @uses collectionName()
	 * @uses normaliseOptions()
	 */
	public function FindByVertex( $theVertex, $theOptions = NULL )
	{
		//
		// Normalise options.
		//
		$this->normaliseOptions(
			kTOKEN_OPT_FORMAT, kTOKEN_OPT_FORMAT_STANDARD, $theOptions );
		$this->normaliseOptions(
			kTOKEN_OPT_DIRECTION, kTOKEN_OPT_DIRECTION_ANY, $theOptions );

		//
		// Get vertex handle.
		//
		if( $theVertex instanceof \Milko\PHPLib\Container )
			$theVertex = $this->NewDocumentHandle( $theVertex );

		//
		// Get edge handler.
		//
		$handler = new ArangoEdgeHandler( $this->Database()->Connection() );

		//
		// Select connected edges.
		//
		$cursor =
			$handler->edges(
				$this->collectionName(), $theVertex, $theOptions[ kTOKEN_OPT_DIRECTION ] );

		//
		// Handle native result.
		//
		if( $theOptions[ kTOKEN_OPT_FORMAT ] == kTOKEN_OPT_FORMAT_NATIVE )
			return $cursor;															// ==>

		//
		// Iterate cursor.
		//
		$list = [];
		foreach( $cursor as $document )
		{
			//
			// Format document.
			//
			switch( $theOptions[ kTOKEN_OPT_FORMAT ] )
			{
				case kTOKEN_OPT_FORMAT_STANDARD:
					$tmp = $this->NewDocument( $document );
					$this->normaliseSelectedDocument( $tmp, $document );
					$list[] = $tmp;
					break;

				case kTOKEN_OPT_FORMAT_HANDLE:
					$list[] = $this->NewDocumentHandle( $document );
					break;

				case kTOKEN_OPT_FORMAT_KEY:
					$list[] = $this->NewDocumentKey( $document );
					break;

				default:
					throw new \InvalidArgumentException (
						"Invalid conversion format code." );					// !@! ==>
			}
		}

		return $list;																// ==>

	} // FindByVertex.




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
	 * @param \Milko\PHPLib\Container	$theDocument	The inserted document.
	 * @param mixed						$theData		The insert operation data.
	 *
	 * @uses Document::VertexIn()
	 * @uses Document::VertexOut()
	 */
	protected function normaliseInsertedDocument( \Milko\PHPLib\Container $theDocument,
												  $theData )
	{
		//
		// Set source and destination vertices.
		//
		if( $theDocument instanceof \Milko\PHPLib\Relation )
		{
			$theDocument->offsetSet( $this->VertexIn(), $theData->getFrom() );
			$theDocument->offsetSet( $this->VertexOut(), $theData->getTo() );
		}

		//
		// Call parent method.
		//
		parent::normaliseInsertedDocument( $theDocument, $theData->getId() );

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
	 * @param Container				$theDocument		The selected document.
	 * @param mixed					$theData			The native database document.
	 *
	 * @uses Document::VertexIn()
	 * @uses Document::VertexOut()
	 */
	protected function normaliseSelectedDocument( \Milko\PHPLib\Container $theDocument,
												  $theData )
	{
		//
		// Set source and destination vertices.
		//
		if( $theDocument instanceof \Milko\PHPLib\Relation )
		{
			$theDocument->offsetSet( $this->VertexIn(), $theData->getFrom() );
			$theDocument->offsetSet( $this->VertexOut(), $theData->getTo() );
		}

		//
		// Call parent method.
		//
		parent::normaliseSelectedDocument( $theDocument, $theData );

	} // normaliseSelectedDocument.



} // class Relations.


?>
