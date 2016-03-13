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
 * This class implements a special {@link Container} instance which is supposed to persist
 * in a collection. For this purpose the document features a set of offsets that contain
 * information regarding persistence:
 *
 * <ul>
 * 	<li><tt>{@link Collection::IdOffset()}</tt>: The offset of the document reference.
 * 	<li><tt>{@link Collection::KeyOffset()}</tt>: The offset of the document unique key.
 * 	<li><tt>{@link Collection::ClassOffset()}</tt>: The offset of the document class.
 * 	<li><tt>{@link Collection::RevisionOffset()}</tt>: The offset of the document revision.
 * </ul>
 *
 * These offsets are managed by the enclosing collection, which is also responsible of
 * instantiating and serialising the document: this is because the above information is
 * dependent both on the database engine and the structure of the collection objects. So,
 * to set the document key you would do:<br/>
 * <code>$document[ $collection->KeyOffset() ] = $key;</code> and to retrieve the key,
 * <code>$key = $document[ $collection->KeyOffset() ];</code>
 *
 * For this reason, a document must be instantiated by providing the {@link Collection} in
 * which it will reside, when instantiated, the only default property that is set is the
 * document's class, which will be used to instantiate it when read from the database.
 *
 *	@package	Core
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		10/03/2016
 */
class Document extends Container
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
	 * We overload the inherited constructor to set the class offset, for this reason we
	 * need to provide the collection in which the document should be stored.
	 *
	 * @param Collection			$theCollection		Collection name.
	 * @param array					$theData			Document data.
	 */
	public function __construct( Collection $theCollection, $theData = [] )
	{
		//
		// Call parent constructor.
		//
		parent::__construct( $theData );

		//
		// Add class.
		//
		$this->offsetSet( $theCollection->ClassOffset(), get_class( $this ) );

	} // Constructor.




} // class Document.


?>
