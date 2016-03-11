<?php

/**
 * Document.php
 *
 * This file contains the definition of the {@link Document} class.
 */

namespace Milko\PHPLib\ArangoDB;

use Milko\PHPLib\Document;

use triagens\ArangoDb\Document as ArangoDocument;

/*=======================================================================================
 *																						*
 *									Document.php										*
 *																						*
 *======================================================================================*/

/**
 * <h4>ArangoDB document object.</h4>
 *
 * This <em>concrete</em> class implements a concrete instance of
 * {@link Milko\PHPLib\Document} that knows how to manage its key and identifier, and can
 * serialise to a {@link ArangoDocument} for storing in the database.
 *
 *	@package	Core
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		10/03/2016
 *
 *	@example	../../test/ArangoDocument.php
 */
class Document extends \Milko\PHPLib\Document
{



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
	 * We overload the inherited constructor to handle {@link ArangoDocument} instances.
	 *
	 * @param mixed					$theValue			Array or object.
	 */
	public function __construct( $theValue = [] )
	{
		//
		// Handle objects.
		//
		if( $theValue instanceof ArangoDocument )
		{
			//
			// Load properties.
			//
			$document = $theValue->getAll();

			//
			// Add internal properties.
			//
			$document[ $this->getIdOffset() ] = $theValue->getInternalId();
			$document[ $this->getRevOffset() ] = $theValue->getRevision();

			//
			// Call parent constructor.
			//
			parent::__construct( $document );

		} // ArangoDocument.

		//
		// Call parent constructor.
		//
		else
			parent::__construct( $theValue );

	} // Constructor.



/*=======================================================================================
 *																						*
 *						PUBLIC DOCUMENT SERIALISATION INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Record																			*
	 *==================================================================================*/

	/**
	 * <h4>Return the document in its native format.</h4>
	 *
	 * We overload this method to return an {@link ArangoDocument}.
	 *
	 * @return mixed				Database native document.
	 *
	 * @uses toArray()
	 */
	public function Record()
	{
		//
		// Clone document.
		//
		$class = get_class( $this );
		$document = new $class( $this->toArray() );

		//
		// Save and remove internal properties.
		//
		$id = $document->manageProperty( $this->getIdOffset(), FALSE );
		$rev = $document->manageProperty( $this->getRevOffset(), FALSE );

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

	} // Record();



/*=======================================================================================
 *																						*
 *						PROTECTED IDENTIFIER MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	getKeyOffset																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the document key offset.</h4>
	 *
	 * In this class we consider by default the <tt>_key</tt> offset as the document key,
	 * derived classes may use another property if necessary.
	 *
	 * @return string				Document key offset.
	 */
	public function getKeyOffset()										{	return '_key';	}


	/*===================================================================================
	 *	getIdOffset																		*
	 *==================================================================================*/

	/**
	 * <h4>Return the document identifier offset.</h4>
	 *
	 * In this class we consider by default the <tt>_id</tt> offset as the document
	 * identifier or reference, derived classes may use another property if necessary.
	 *
	 * @return string				Document identifier offset.
	 */
	public function getIdOffset()										{	return '_id';	}


	/*===================================================================================
	 *	getRevOffset																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the document revision offset.</h4>
	 *
	 * In this class we consider by default the <tt>_rev</tt> offset as the document
	 * revision.
	 *
	 * @return string				Document revision offset.
	 */
	public function getRevOffset()										{	return '_rev';	}




} // class Document.


?>
