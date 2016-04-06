<?php

/**
 * Edges.php
 *
 * This file contains the definition of the {@link Relations} class.
 */

namespace Milko\PHPLib\ArangoDB;

use Milko\PHPLib\ArangoDB\Collection;
use Milko\PHPLib\iEdges;

use Milko\PHPLib\Edge;
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
 *										Edges.php										*
 *																						*
 *======================================================================================*/

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



/*=======================================================================================
 *																						*
 *							PUBLIC DOCUMENT MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	NewNativeDocument																*
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
	public function NewNativeDocument( $theData )
	{
		//
		// Handle native document.
		//
		if( $theData instanceof ArangoEdge )
			return $theData;														// ==>

		return parent::NewNativeDocument( $theData );								// ==>

	} // NewNativeDocument.


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

			//
			// Get source and destination vertices.
			//
			if( $theData instanceof ArangoEdge )
			{
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

			} // ArangoEdge.

			return $document;														// ==>

		} // ArangoDocument.

		return parent::NewDocumentArray( $theData );								// ==>

	} // NewDocumentArray.



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
 *								PUBLIC GRAPH MANAGEMENT INTERFACE						*
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
	 * @uses Database()
	 * @uses collectionName()
	 * @uses normaliseCursor()
	 * @uses normaliseOptions()
	 * @uses triagens\ArangoDb\EdgeHandler::edges()
	 * @see kTOKEN_OPT_FORMAT
	 * @see kTOKEN_OPT_DIRECTION
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
		if( $theVertex instanceof ArangoEdge )
			$theVertex = $theVertex->getHandle();
		elseif( is_object( $theVertex ) )
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

		return $this->normaliseCursor( $cursor, $theOptions );						// ==>

	} // FindByVertex.



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
		// Normalise options.
		//
		if( $theOptions === NULL )
			$theOptions = [];

		//
		// Init options.
		//
		$theOptions[ "type" ] = ArangoCollection::TYPE_EDGE;

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
			// Get collection.
			//
			$collection = $handler->get( $theCollection );

			//
			// Assert the collection type.
			//
			if( $collection->getType() != ArangoCollection::TYPE_EDGE )
				throw new \InvalidArgumentException (
					"Invalid collection type: "
					."expecting an edge collection." );							// !@! ==>

			return $collection;														// ==>
		}

		return $handler->get( $handler->create( $theCollection, $theOptions ) );	// ==>

	} // collectionNew.



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
	 * We overload this method to extract the source and destination vertices from the
	 * document properties and pass them to the {@link ArangoEdgeHandler::saveEdge()}
	 * method.
	 *
	 * @param mixed					$theDocument		Database native format document.
	 * @return mixed				The inserted document's key.
	 *
	 * @uses Database()
	 * @uses collectionName()
	 * @uses triagens\ArangoDb\DocumentHandler::saveEdge()
	 */
	protected function doInsert( $theDocument )
	{
		//
		// Get vertices.
		//
		if( ($srcVertex = $theDocument->getFrom()) === NULL )
			throw new \InvalidArgumentException (
				"Missing source vertex." );										// !@! ==>
		if( ($dstVertex = $theDocument->getTo()) === NULL )
			throw new \InvalidArgumentException (
				"Missing destination vertex." );								// !@! ==>

		//
		// Instantiate edge handler.
		//
		$handler = new ArangoEdgeHandler( $this->Database()->Connection() );

		return
			$handler->saveEdge(
				$this->collectionName(), $srcVertex, $dstVertex, $theDocument );	// ==>

	} // doInsert.


	/*===================================================================================
	 *	doInsertBulk																	*
	 *==================================================================================*/

	/**
	 * <h4>Insert a list of documents.</h4>
	 *
	 * We overload this method to iterate the provided list and call the
	 * {@link doInsertOne()} method on each document.
	 *
	 * @param array					$theList			Native format documents list.
	 * @return array				The document keys.
	 *
	 * @uses Database()
	 * @uses collectionName()
	 * @uses triagens\ArangoDb\DocumentHandler::saveEdge()
	 */
	protected function doInsertBulk( array $theList )
	{
		//
		// Init local storage.
		//
		$ids = [];
		$handler = new ArangoEdgeHandler( $this->Database()->Connection() );

		//
		// Iterate documents.
		//
		foreach( $theList as $edge )
		{
			//
			// Get vertices.
			//
			if( ($srcVertex = $edge->getFrom()) === NULL )
				throw new \InvalidArgumentException (
					"Missing source vertex." );									// !@! ==>
			if( ($dstVertex = $edge->getTo()) === NULL )
				throw new \InvalidArgumentException (
					"Missing destination vertex." );							// !@! ==>

			//
			// Save edge.
			//
			$ids[] =
				$handler->saveEdge(
					$this->collectionName(), $srcVertex, $dstVertex, $edge );
		}

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
	 * We overload this method to call the
	 * {@link triagens\ArangoDb\EdgeHandler::removeById()} method.
	 *
	 * @param mixed					$theDocument		The document to be deleted.
	 * @return int					The number of deleted documents.
	 * @throws \InvalidArgumentException
	 *
	 * @uses Database()
	 * @uses collectionName()
	 * @uses triagens\ArangoDb\EdgeHandler::removeById()
	 */
	protected function doDelete( $theDocument )
	{
		//
		// Check document key.
		//
		if( ($id = $theDocument->getKey()) !== NULL )
		{
			//
			// Instantiate edge handler.
			//
			$handler = new ArangoEdgeHandler( $this->Database()->Connection() );

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




/*=======================================================================================
 *																						*
 *								PROTECTED CONVERSION UTILITIES							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	toDocument																		*
	 *==================================================================================*/

	/**
	 * <h4>Return a standard database document.</h4>
	 *
	 * We overload this method to return {@link \Milko\PHPLib\Relations} instances by
	 * default.
	 *
	 * @param array					$theData			Document as an array.
	 * @return mixed				Native database document object.
	 */
	protected function toDocument( array $theData )
	{
		return new Edge( $this, $theData );										// ==>

	} // toDocument.


	/*===================================================================================
	 *	toDocumentNative																*
	 *==================================================================================*/

	/**
	 * <h4>Return a native database document.</h4>
	 *
	 * We implement this method by generating a {@link triagens\ArangoDb\Edge}.
	 *
	 * @param array					$theDocument		Document properties.
	 * @return mixed				Native database document object.
	 */
	protected function toDocumentNative( array $theDocument )
	{
		//
		// Create an ArangoDocument.
		//
		$document = ArangoEdge::createFromArray( $theDocument );

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

		//
		// Set source vertex.
		//
		if( ($document->getFrom() === NULL)
		 && array_key_exists( $this->VertexSource(), $theDocument ) )
			$document->setFrom( $theDocument[ $this->VertexSource() ] );

		//
		// Set destination vertex.
		//
		if( ($document->getTo() === NULL)
		 && array_key_exists( $this->VertexDestination(), $theDocument ) )
			$document->setFrom( $theDocument[ $this->VertexDestination() ] );

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
		if( $theDocument instanceof \Milko\PHPLib\Edge )
		{
			$theDocument->offsetSet( $this->VertexSource(), $theData->getFrom() );
			$theDocument->offsetSet( $this->VertexDestination(), $theData->getTo() );
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
	protected function normaliseSelectedDocument( \Milko\PHPLib\Container $theDocument,
												  						  $theData )
	{
		//
		// Set source and destination vertices.
		// Note that here we also check uf thye naticve document is an edge,
		// if that is not the case, we assume the vertex references are in the properties.
		//
		if( ($theData instanceof ArangoEdge)
		 && ($theDocument instanceof \Milko\PHPLib\Edge) )
		{
			$theDocument->offsetSet( $this->VertexSource(), $theData->getFrom() );
			$theDocument->offsetSet( $this->VertexDestination(), $theData->getTo() );
		}

		//
		// Call parent method.
		//
		parent::normaliseSelectedDocument( $theDocument, $theData );

	} // normaliseSelectedDocument.



} // class Edges.


?>
