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
 * 	<li><em>Persistent state</em>: This state indicates whether the the document is stored
 * 		or was retrieved from a collection, <em>only {@link Collection} objects may set this
 * 		state, probing the state is public</em>.
 * 	 <ul>
 * 		<li><b>{@link SetPersistentState()}</b>: Set the persistent state; requires
 * 			providing the <tt>$this</tt> of the instance that is setting the state.
 * 		<li><b>{@link GetPersistentState()}</b>: Get the persistent state.
 * 	 </ul>
 * 	<li><em>Modification state</em>: This state indicates whether the document was modified
 * 		since it was instantiated, <em>only the current object or a {@link Collection}
 * 		instance may set this state, probing the state is public</em>.
 * 	 <ul>
 * 		<li><b>{@link SetModificationState()}</b>: Set the modification state.
 * 		<li><b>{@link GetModificationState()}</b>: Get the modification state.
 * 	 </ul>
 * </ul>
 *
 * A special public method, {@link SetKey()}, is used by {@link Collection} instances to set
 * the document key (@link Collection::JeyOffset()}) after it was inserted, this is useful
 * when the key was not provided and the database generated one automatically; this method
 * will reset the modification state ({@link IsModified()}) after setting the key, so that
 * the object will result clean after being inserted, this method can be called only by
 * {@link Collection} instances.
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
	 * Modified status.
	 *
	 * This bitfield flag represents the modification status of the document: if set, it
	 * means that the document has been modified since it was instantiated or retrieved
	 * from its collection.
	 *
	 * @var bitfield
	 */
	const kFLAG_DOC_MODIFIED = 0x00000001;

	/**
	 * Persistent status.
	 *
	 * This bitfield flag represents the persistent status of the document: if set, it means
	 * that the document was retrieved, or that it was stored in a collection.
	 *
	 * @var bitfield
	 */
	const kFLAG_DOC_PERSISTENT = 0x00000002;

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
			$this->SetModificationState( FALSE, $this );

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
	 * only be set for new values.
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
			$this->SetModificationState( TRUE, $this );
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
			$this->SetModificationState( TRUE, $this );
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
	public function Validate()														   {}



/*=======================================================================================
 *																						*
 *							PUBLIC KEY MANAGEMENT INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	SetKey																		*
	 *==================================================================================*/

	/**
	 * <h4>Set document key.</h4>
	 *
	 * This method will be used by {@link Collection} instances to set the document key,
	 * after setting it, the method will reset the modification state; only
	 * {@link Collection} instances are allowed to call this method, for this reason it
	 * expects the <tt>$this</tt> value as parameter.
	 *
	 * @param mixed					$theKey				The document key.
	 * @param Collection			$theSetter			The instance calling the method.
	 *
	 * @uses SetModificationState()
	 */
	public function SetKey( $theKey, Collection $theSetter )
	{
		//
		// Set document key.
		//
		$this->offsetSet( $theSetter->KeyOffset(), $theKey );

		//
		// Reset modification state.
		//
		$this->SetModificationState( FALSE, $this );

	} // SetKey.



/*=======================================================================================
 *																						*
 *								PUBLIC STATUS INTERFACE									*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	SetModificationState															*
	 *==================================================================================*/

	/**
	 * <h4>Set the document modified state.</h4>
	 *
	 * This method can be used to set or reset the document modification state, it can only
	 * be called by the current object or by a {@link Collection} instance, or an exception
	 * will be raised.
	 *
	 * Provide <tt>TRUE</tt> to set the <em>dirty</em> state and <tt>FALSE</tt> to set the
	 * <em>clean</em> state.
	 *
	 * The second parameter is the <tt>$this</tt> value of the object calling the method,
	 * Only a {@link Collection} instance or the current object are allowed to call the
	 * method.
	 *
	 * The method will return the state <em>before</em> it was set or reset.
	 *
	 * @param mixed					$theValue			<tt>TRUE</tt> or <tt>FALSE</tt>.
	 * @param mixed					$theSetter			Setting object.
	 * @return bool					New or old state.
	 * @throws RuntimeException
	 *
	 * @uses manageFlagAttribute()
	 * @see kFLAG_DOC_MODIFIED
	 */
	public function SetModificationState( $theValue, $theSetter )
	{
		//
		// Let only this or a collection call it.
		//
		if( ($theSetter === $this)
		 || ($theSetter instanceof Collection) )
			return
				$this->manageFlagAttribute(
					$this->mStatus, self::kFLAG_DOC_MODIFIED, $theValue );			// ==>

		throw new \RuntimeException (
			"Only collections or the current document " .
			"are allowed to call this method." );								// !@! ==>

	} // SetModificationState.


	/*===================================================================================
	 *	GetModificationState															*
	 *==================================================================================*/

	/**
	 * <h4>Get the document current modified state.</h4>
	 *
	 * This method can be used to get the document current modification state, it returns
	 * a boolean, <tt>TRUE</tt> for the <em>dirty</em> state and <tt>FALSE</tt> for the
	 * <em>clean</em> state.
	 *
	 * @return bool					Current state.
	 *
	 * @see kFLAG_DOC_MODIFIED
	 */
	public function GetModificationState()
	{
		return $this->mStatus & self::kFLAG_DOC_MODIFIED;							// ==>

	} // GetModificationState.


	/*===================================================================================
	 *	SetPersistentState																*
	 *==================================================================================*/

	/**
	 * <h4>Set the document persistent state.</h4>
	 *
	 * This method can be used to set or reset the document persistent state, it can only
	 * be called by {@link Collection} instances.
	 *
	 * Provide <tt>TRUE</tt> after storing and after retrieving the document from its
	 * collection, and <tt>FALSE</tt> after deleting the document.
	 *
	 * The method will return the state <em>before</em> it was set or reset.
	 *
	 * @param mixed					$theValue			<tt>TRUE</tt> or <tt>FALSE</tt>.
	 * @param Collection			$theSetter			Setting object.
	 * @return bool					New or old state.
	 *
	 * @uses manageFlagAttribute()
	 * @see kFLAG_DOC_PERSISTENT
	 */
	public function SetPersistentState( $theValue, Collection $theSetter )
	{
		return
			$this->manageFlagAttribute(
				$this->mStatus, self::kFLAG_DOC_PERSISTENT, $theValue );			// ==>

	} // SetPersistentState.


	/*===================================================================================
	 *	GetPersistentState																*
	 *==================================================================================*/

	/**
	 * <h4>Get the document persistent state.</h4>
	 *
	 * This method can be used to get the document persistent state, it returns a boolean
	 * a boolean, <tt>TRUE</tt> if the object resides in a collection or <tt>FALSE</tt> if
	 * it doesn't (yet).
	 *
	 * @return bool					Current state.
	 *
	 * @see kFLAG_DOC_PERSISTENT
	 */
	public function GetPersistentState()
	{
		return $this->mStatus & self::kFLAG_DOC_PERSISTENT;							// ==>

	} // GetPersistentState.




} // class Document.


?>
