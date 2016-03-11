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
	 * In this class we consider by default the <tt>_id</tt> offset as the document key,
	 * derived classes may use another property if necessary.
	 *
	 * @return string				Document key offset.
	 */
	public function getKeyOffset()										{	return '_id';	}


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




} // class Document.


?>
