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
			$theValue = $theValue->getAll();

		//
		// Call parent constructor.
		//
		parent::__construct( $theValue );

	} // Constructor.



/*=======================================================================================
 *																						*
 *						PUBLIC IDENTIFIER MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Key																				*
	 *==================================================================================*/

	/**
	 * <h4>Handle the document unique key.</h4>
	 *
	 * We overload this method to consider the <tt>_key</tt> property as the document key.
	 *
	 * @param mixed					$theValue			Value to set or operation.
	 * @return mixed				Document old or new key.
	 *
	 * @uses manageProperty()
	 */
	public function Key( $theValue = NULL )
	{
		return $this->manageProperty( '_key', $theValue );							// ==>

	} // Key.


	/*===================================================================================
	 *	ID																				*
	 *==================================================================================*/

	/**
	 * <h4>Handle the document unique identifier.</h4>
	 *
	 * We overload this method to consider the <tt>_id</tt> property as the document
	 * identifier; this method will not allow modifying the value, since the database does
	 * not allow this, so it will simply return the current value.
	 *
	 * @param mixed					$theValue			Value to set or operation.
	 * @return mixed				Document old or new identifier.
	 */
	public function ID( $theValue = NULL )			{	return $this->offsetGet( '_id' );	}



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
		return ArangoDocument::createFromArray( $this->toArray() );

	} // Record();




} // class Document.


?>
