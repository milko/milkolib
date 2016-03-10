<?php

/**
 * Document.php
 *
 * This file contains the definition of the {@link Document} class.
 */

namespace Milko\PHPLib;

use Milko\PHPLib\Document;

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
 * set of virtual methods that should be implemented in concrete derived classes:
 *
 * <ul>
 * 	<li><b>{@link Key()}</b>: Manage the document key value.
 * 	<li><b>{@link ID()}</b>: Manage the document identifier value.
 * 	<li><b>{@link Record()}</b>: Return a structure compatible with the related database
 * 		engine.
 * </ul>
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
	 * container which is its collection, the database should allow this value to be set.
	 *
	 * Concrete derived classes should implement this method and manage the specific
	 * document property related to the database engine.
	 *
	 * @param mixed					$theValue			Value to set or operation.
	 * @return mixed				Document old or new key.
	 */
	abstract public function Key( $theValue = NULL );


	/*===================================================================================
	 *	ID																				*
	 *==================================================================================*/

	/**
	 * <h4>Handle the document unique identifier.</h4>
	 *
	 * This method should set or retrieve the document unique identifier, this value should
	 * represent the unique key of the document within its enclosing database.
	 *
	 * The method expects a single parameter which represents either the operation or the
	 * value to be set:
	 *
	 * <ul>
	 *	<li><tt>NULL</tt>: Return the current identifier.
	 *	<li><tt>FALSE</tt>: Delete the identifier and return the old value.
	 *	<li><em>other</em>: Set the identifier with the provided value and return it.
	 * </ul>
	 *
	 * The document identifier represents a unique value that identifies the document within
	 * its container which is its database. The database may or may not allow such value to
	 * be set, also, some databases might not have a distinction between the unique key or
	 * identifier, to provide a consistent behaviour you should implement the method as
	 * follows:
	 *
	 * <ul>
	 * 	<li><em>Protected</em>: If the identifier cannot be modified by clients, the method
	 * 		should simply return the current value. This means that whenever setting a value
	 * 		in this case, you should always take the value returned by this method.
	 * 	<li><em>Only one identifier</em>: If there is no distinction between key and
	 * 		identifier in the database, you should either use the same document property, or
	 * 		assign a specific property to the key and use the document identifier in this
	 * 		method.
	 * </ul>
	 *
	 * Concrete derived classes should implement this method and manage the specific
	 * document property related to the database engine.
	 *
	 * @param mixed					$theValue			Value to set or operation.
	 * @return mixed				Document old or new identifier.
	 */
	abstract public function ID( $theValue = NULL );



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




} // class Document.


?>
