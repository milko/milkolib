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
 * The class constructor will set in its {@link Collection::ClassOffset()} property the name
 * of its current class. This must be taken into consideration, because when instantiating
 * an object derived from this class from the contents of another derived object, the class
 * property will be overwritten, which means that the object should be replaced.
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
 * The class also features a method, {@link Validate()}, which should check whether the
 * object has all the required attributes and is fit to be stored in the database, if that
 * is not the case, the method should raise an exception; this method must be called before
 * storing documents in the database. In this class we assume the object is valid.
 *
 * Documents store their status in a data member, a public interface is provided to set and
 * probe a series of states:
 *
 * <ul>
 * 	<li><b>{@link IsPersistent()}</b>: Check whether the the document is stored or was
 * 		retrieved from a collection, <em>only {@link Collection} objects may set this
 * 		state, probing the state is public</em>.
 * 	<li><b>{@link IsModified()}</b>: Check whether the the document was modified since it
 * 		was instantiated, <em>only the current object may set this state, probing the state
 * 		is public</em>.
 * </ul>
 *
 * In order to ensure the correct object is setting the state, the above methods require to
 * provide <tt>$this</tt> when setting the value.
 *
 *	@package	Core
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		10/03/2016
 */
class Document extends Container
{
	/**
	 * Default status.
	 *
	 * This bitfield flags mask represents the default document status.
	 *
	 * @var bitfield
	 */
	const kFLAG_DEFAULT = 0x00000000;

	/**
	 * Persistent status.
	 *
	 * This bitfield flag represents the persistent status of the document: if set, it means
	 * that the document was retrieved, or that it was stored in a collection.
	 *
	 * @var bitfield
	 */
	const kFLAG_DOC_PERSISTENT = 0x00000001;

	/**
	 * Modified status.
	 *
	 * This bitfield flag represents the modification status of the document: if set, it
	 * means that the document has been modified since it was instantiated or retrieved
	 * from its collection.
	 *
	 * @var bitfield
	 */
	const kFLAG_DOC_MODIFIED = 0x00000002;

	/**
	 * Status.
	 *
	 * This attribute stores the current document status.
	 *
	 * @var bitfield
	 */
	protected $mStatus = self::kFLAG_DEFAULT;



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
	 * Derived classes should first call their parent constructor.
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
		// Save class in data.
		//
		$class = $this->offsetGet( $theCollection->ClassOffset() );

		//
		// Add class.
		// Note that we overwrite the eventual existing class name
		// and we use the ancestor class method, since the class property is locked.
		//
		\ArrayObject::offsetSet( $theCollection->ClassOffset(), get_class( $this ) );

		//
		// Reset modification state.
		//
		if( $class == get_class( $this ) )
			$this->IsModified( FALSE, $this );

	} // Constructor.



/*=======================================================================================
 *																						*
 *								PUBLIC ARRAY ACCESS INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	offsetSet																		*
	 *==================================================================================*/

	/**
	 * <h4>Set a value at a given offset.</h4>
	 *
	 * We override this method to set the modified flag ({@link kFLAG_DOC_MODIFIED}, it will
	 * only be set if the value is set.
	 *
	 * @param string				$theOffset			Offset.
	 * @param mixed					$theValue			Value to set at offset.
	 * @return void
	 */
	public function offsetSet( $theOffset, $theValue )
	{
		//
		// Skip deletions.
		//
		if( $theValue !== NULL )
		{
			$this->IsModified( TRUE, $this );
			parent::offsetSet( $theOffset, $theValue );
		}

		//
		// Handle delete.
		//
		else
			$this->offsetUnset( $theOffset );

	} // offsetSet.


	/*===================================================================================
	 *	offsetUnset																		*
	 *==================================================================================*/

	/**
	 * <h4>Reset a value at a given offset.</h4>
	 *
	 * We override this method to set the modified flag ({@link kFLAG_DOC_MODIFIED}, it will
	 * only be set if the offset exists.
	 *
	 * @param string				$theOffset			Offset.
	 * @return void
	 */
	public function offsetUnset( $theOffset )
	{
		//
		// Delete value.
		//
		if( parent::offsetExists( $theOffset ) )
		{
			$this->IsModified( TRUE, $this );
			parent::offsetUnset( $theOffset );
		}

	} // offsetUnset.



/*=======================================================================================
 *																						*
 *								PUBLIC VALIDATION INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Validate																		*
	 *==================================================================================*/

	/**
	 * <h4>Validate object.</h4>
	 *
	 * This method should check whether the current object's required attributes are present
	 * and if it is structurally and referentially valid; if that is not the case, the
	 * method should raise an exception.
	 *
	 * In this class we assume the object to be valid.
	 */
	protected function Validate()														   {}



/*=======================================================================================
 *																						*
 *								PUBLIC STATUS INTERFACE									*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	IsPersistent																	*
	 *==================================================================================*/

	/**
	 * <h4>Manage document persistent state.</h4>
	 *
	 * This method can be used to set or check the document's persistent state, the method
	 * expects the following parameters:
	 *
	 * <ul>
	 * 	<li><b>$theValue</ul>: The new persistent state or the command:
	 * 	 <ul>
	 * 		<li><tt>NULL</tt>: Get the current persistent state.
	 * 		<li><tt>FALSE</tt>: Reset the persistent state, the method will return the
	 * 			previous state.
	 * 		<tt>TRUE</tt> Set the persistent state, the method will return the previous
	 * 			state.
	 * 	 </ul>
	 * 	<li><b>$theSetter</ul>: This parameter should be set by the caller: provide the
	 * 		class reference of the object setting the value. Provide <tt>$this</tt> when
	 * 		setting the value.
	 * </ul>
	 *
	 * The second parameter is ignored when retrieving the state, but is required when
	 * setting or resetting it: only objects derived from the {@link Collection} class are
	 * allowed to indicate whether the object is persistent or not.
	 *
	 * @param mixed					$theValue			<tt>NULL</tt>, <tt>TRUE</tt> or
	 * 													<tt>FALSE</tt>.
	 * @param array					$theSetter			Setting object.
	 * @return boolean				<tt>TRUE</tt> is persistent.
	 * @throws RuntimeException
	 *
	 * @uses manageFlagAttribute()
	 */
	public function IsPersistent( $theValue, $theSetter = NULL )
	{
		//
		// Check who is setting the state.
		//
		if( ($theValue !== NULL)
			&& ($theValue !== FALSE)
			&& (! ($theSetter instanceof Collection)) )
			throw new \RuntimeException (
				"Only collections may set the document persistent state." );	// !@! ==>

		return
			$this->manageFlagAttribute(
				$this->mStatus, kFLAG_DOC_PERSISTENT, $theValue );					// ==>

	} // IsPersistent.


	/*===================================================================================
	 *	IsModified																		*
	 *==================================================================================*/

	/**
	 * <h4>Manage document modified state.</h4>
	 *
	 * This method can be used to set or check the document's modified state, the method
	 * expects the following parameters:
	 *
	 * <ul>
	 * 	<li><b>$theValue</ul>: The new modified state or the command:
	 * 	 <ul>
	 * 		<li><tt>NULL</tt>: Get the current modified state.
	 * 		<li><tt>FALSE</tt>: Reset the modified state, the method will return the
	 * 			previous state.
	 * 		<tt>TRUE</tt> Set the modified state, the method will return the previous
	 * 			state.
	 * 	 </ul>
	 * 	<li><b>$theSetter</ul>: This parameter should be set by the caller: provide the
	 * 		class reference of the object setting the value. Provide <tt>$this</tt> when
	 * 		setting the value.
	 * </ul>
	 *
	 * The second parameter is ignored when retrieving the state, but is required when
	 * setting or resetting it: only the current object is allowed to indicate whether it
	 * was modified or not.
	 *
	 * @param mixed					$theValue			<tt>NULL</tt>, <tt>TRUE</tt> or
	 * 													<tt>FALSE</tt>.
	 * @param array					$theSetter			Setting object.
	 * @return boolean				<tt>TRUE</tt> was modified.
	 * @throws RuntimeException
	 *
	 * @uses manageFlagAttribute()
	 */
	public function IsModified( $theValue, $theSetter = NULL )
	{
		//
		// Check who is setting the state.
		//
		if( ($theValue !== NULL)
		 && ($theValue !== FALSE)
		 && ($theSetter !== $this) )
			throw new \RuntimeException (
				"Only collections may set the document modified state." );		// !@! ==>

		return
			$this->manageFlagAttribute(
				$this->mStatus, kFLAG_DOC_MODIFIED, $theValue );					// ==>

	} // IsModified.




} // class Document.


?>
