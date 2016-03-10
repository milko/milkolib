<?php

/**
 * Document.php
 *
 * This file contains the definition of the {@link Document} class.
 */

namespace Milko\PHPLib\MongoDB;

use Milko\PHPLib\Document;

/*=======================================================================================
 *																						*
 *									Document.php										*
 *																						*
 *======================================================================================*/

/**
 * <h4>MongoDB document object.</h4>
 *
 * This <em>concrete</em> class is the implementation of a MongoDB document, it implements
 * the inherited virtual interface to provide an object that can be stored in MongoDB
 * collections and that knows which property represents its key and identifier.
 *
 * MongoDB documents have a single unique identifier, in this class we treat both the
 * {@link Key()} and the {@link ID()} as the <tt>_id</tt> property, derived classes may
 * overload the {@Key()} method to treat a specific property as the document key.
 *
 *	@package	Core
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		10/03/2016
 *
 *	@example	../../test/MongoDocument.php
 */
class Document extends \Milko\PHPLib\Document
{



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
	 * We overload this method to consider the <tt>_id</tt> property as the document key.
	 *
	 * @param mixed					$theValue			Value to set or operation.
	 * @return mixed				Document old or new key.
	 *
	 * @uses manageProperty()
	 */
	public function Key( $theValue = NULL )
	{
		return $this->manageProperty( '_id', $theValue );							// ==>

	} // Key.


	/*===================================================================================
	 *	ID																				*
	 *==================================================================================*/

	/**
	 * <h4>Handle the document unique identifier.</h4>
	 *
	 * We overload this method to consider the <tt>_id</tt> property as the document key.
	 *
	 * @param mixed					$theValue			Value to set or operation.
	 * @return mixed				Document old or new identifier.
	 *
	 * @uses manageProperty()
	 */
	public function ID( $theValue = NULL )
	{
		return $this->manageProperty( '_id', $theValue );							// ==>

	} // ID.



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
	 * We overload this method to return an associative array.
	 *
	 * @return mixed				Database native document.
	 *
	 * @uses toArray()
	 */
	public function Record()								{	return $this->toArray();	}




} // class Document.


?>
