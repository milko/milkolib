<?php

/**
 * Document.php
 *
 * This file contains the definition of the {@link Document} class.
 */

namespace Milko\PHPLib;

/**
 * Global tag definitions.
 */
require_once( 'tags.inc.php' );

/**
 * Global token definitions.
 */
require_once( 'tokens.inc.php' );

use Milko\PHPLib\Container;

/*=======================================================================================
 *																						*
 *									Document.php										*
 *																						*
 *======================================================================================*/

/**
 * <h4>Document ancestor object.</h4>
 *
 * This class is essentially a {@link Container} that keeps track of its class name, it is
 * the ancestor of all persistent classes, thus includes the global tokens
 * ({@link 'tokens.inc.php'}}, and global tags ({@link 'tags.inc.php'}).
 *
 * The class features (for the moment) only its constructor which will set in its
 * {@link Collection::ClassOffset()} property the name of its current class. This must be
 * taken into consideration, because when instantiating an object derived from this class
 * from the contents of another derived object, the class property will be overwritten,
 * which means that the object should be replaced.
 *
 * For the purpose of persisting, the document features a set of offsets that contain
 * information regarding identifiers, class and revisions:
 *
 * <ul>
 * 	<li><tt>{@link Collection::KeyOffset()}</tt>: The offset of the document unique key.
 * 	<li><tt>{@link Collection::ClassOffset()}</tt>: The offset of the document class.
 * 	<li><tt>{@link Collection::RevisionOffset()}</tt>: The offset of the document revision.
 * </ul>
 *
 * To set the document key you would do<br/>
 * <code>$document[ $collection->KeyOffset() ] = $key;</code> and to retrieve the key,
 * <code>$key = $document[ $collection->KeyOffset() ];</code>
 *
 * These offsets are declared by the enclosing collection, which is also responsible of
 * instantiating ({@link Collection::NewDocument()} and serialising
 * {@link Collection::NewNativeDocument()} the document: this is because the above property
 * tags depend on the native database engine and may also depend on the business logic of
 * the collection.
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
	 * We override the inherited constructor to set the class offset, for this reason we
	 * need to provide the collection.
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
		// Note that we overwrite the eventual existing class name.
		//
		$this->offsetSet( $theCollection->ClassOffset(), get_class( $this ) );

	} // Constructor.




} // class Document.


?>
