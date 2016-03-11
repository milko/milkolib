<?php

/**
 * Document.php
 *
 * This file contains the definition of the {@link Document} class.
 */

namespace Milko\PHPLib;

use Milko\PHPLib\Container;

/*=======================================================================================
 *																						*
 *									Document.php										*
 *																						*
 *======================================================================================*/

/**
 * <h4>Document ancestor object.</h4>
 *
 * This <em>abstract</em> class is the ancestor of all classes representing documents, or
 * database record instances.
 *
 * This class stores the document data in its inherited array data member and features a
 * set of methods to handle the document identifiers:
 *
 * <ul>
 * 	<li><b>{@link ID()}</b>: Retrieve the document identifier or reference.
 * 	<li><b>{@link Key()}</b>: Manage the document unique key value.
 * 	<li><b>{@link Record()}</b>: Return a structure compatible with the related database
 * 		engine.
 * </ul>
 *
 * The document {@link Key()} represents the <em>unique identifier</em> of the document
 * within its container, which should be at least its collection, this property should be
 * set by clients.
 *
 * The document {@link ID()} represents the <em>document reference</em>, which is the
 * value used by other documents to reference the current one. It is the document's unique
 * identifier within the top level container. In other words, if a database features a
 * document identifier at the database level, this would be the one, while the unique
 * identifier at the collection level would be handled by the {@link Key()}. This property
 * should be set by the database, clients may only access it in read-only mode.
 *
 * Derived classes should use the above methods to handle unique keys and references.
 *
 *	@package	Core
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		10/03/2016
 *
 *	@example	../../test/Document.php
 */
abstract class Document extends Container
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
	 * This method should set or retrieve the document unique key, this value should
	 * represent the unique key of the document within its enclosing collection.
	 *
	 * The method expects a single parameter which represents either the operation or the
	 * value to be set:
	 *
	 * <ul>
	 *	<li><tt>NULL</tt>: Return the current key.
	 *	<li><tt>FALSE</tt>: Delete the key and return the old value.
	 *	<li><em>other</em>: Set the key with the provided value and return it.
	 * </ul>
	 *
	 * The document key represents a unique value that identifies the document within its
	 * collection, the database should allow this value to be set.
	 *
	 * This method makes use of the virtual {@link getKeyOffset()} method to determine
	 * which property represents the document key.
	 *
	 * @param mixed					$theValue			Value to set or operation.
	 * @return mixed				Document old or new key.
	 *
	 * @uses getKeyOffset()
	 */
	public function Key( $theValue = NULL )
	{
		return $this->manageProperty( $this->getKeyOffset(), $theValue );			// ==>

	} // Key.


	/*===================================================================================
	 *	ID																				*
	 *==================================================================================*/

	/**
	 * <h4>Handle the document unique identifier.</h4>
	 *
	 * This method should return the document unique identifier, this value should represent
	 * the document's reference, or its unique identifier at the top container level.
	 *
	 * This value should be set by the database and should be used to reference documents
	 * from other documents.
	 *
	 * @return mixed				Document old or new identifier.
	 *
	 * @uses getIdOffset()
	 */
	public function ID()
	{
		return $this->offsetGet( $this->getIdOffset() );							// ==>

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
	 * This method can be used to return a version of the current document compatible with
	 * its related database engine.
	 *
	 * @return mixed				Database native document.
	 */
	abstract public function Record();



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
	 * This method should return the document key offset, concrete derived classes should
	 * implement this method.
	 *
	 * @return string				Document key offset.
	 */
	abstract public function getKeyOffset();


	/*===================================================================================
	 *	getIdOffset																		*
	 *==================================================================================*/

	/**
	 * <h4>Return the document identifier offset.</h4>
	 *
	 * This method should return the document identifier or reference offset, concrete
	 * derived classes should implement this method.
	 *
	 * @return string				Document identifier offset.
	 */
	abstract public function getIdOffset();




} // class Document.


?>
