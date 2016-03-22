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
use Milko\PHPLib\Collection;

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
 * the collection; for this reason, when a document is instantiated by a collection, the
 * latter is stored in an attribute.
 *
 * The class also features a method, {@link Validate()}, which should check whether the
 * object has all the required attributes and is fit to be stored in the database, if that
 * is not the case, the method should raise an exception; this method must be called before
 * storing documents in the database. In this class we assume the object is valid.
 *
 * Documents store their state in a data member, a public interface is provided to set and
 * probe a series of states:
 *
 * <ul>
 * 	<li><tt>{@link IsModified()}</tt>: Handle the modification state of the document, when
 * 		changing the state, you must provide the <tt>$this</tt> of the calling object: only
 * 		the current document collection is allowed to change the modification state (handle
 * 		the object attribute directly within this class to manage this state).
 * 	<li><tt>{@link IsPersistent()}</tt>: Handle the persistent state of the document, when
 * 		changing the state, you must provide the <tt>$this</tt> of the calling object: only
 * 		the document collection is allowed to change the persistent state (this state should
 * 		effectively only be changed by the document collection).
 * </ul>
 *
 * A special public method, {@link SetKey()}, is used by {@link Collection} instances to set
 * the document key (@link Collection::KeyOffset()}) after it was inserted, this is useful
 * when the key was not provided and the database generated one automatically; this method
 * will reset the modification state ({@link IsModified()}) after setting the key, so that
 * the object will result clean after being inserted, this method can be called only by
 * the document {@link Collection}.
 *
 * Two protected methods, {@link lockedOffsets()} and {@link requiredOffsets()}, are used
 * to return respectively the list of offsets that cannot be changed once the document is
 * persistent and those which are required prior to saving the object.
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

	/**
	 * Collection.
	 *
	 * This attribute stores the document collection.
	 *
	 * @var Collection
	 */
	protected $mCollection = NULL;



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
	 * We override the inherited constructor in order to set the document class and set the
	 * enclosing collection in a document's attribute.
	 *
	 * The collection is required, because it is the collection that knows which offsets
	 * corresponds to the class, key and revision.
	 *
	 * Derived classes should first call their parent constructor.
	 *
	 * @param Collection			$theCollection		Collection name.
	 * @param array					$theData			Document data.
	 *
	 * @see kFLAG_DOC_MODIFIED
	 */
	public function __construct( Collection $theCollection, $theData = [] )
	{
		//
		// Call parent constructor.
		//
		parent::__construct( $theData );

		//
		// Set collection.
		//
		$this->mCollection = $theCollection;

		//
		// Save old class.
		//
		$class = $this->offsetGet( $this->mCollection->ClassOffset() );

		//
		// Add class.
		// Note that we overwrite the eventual existing class name
		// and we use the ancestor class method, since the class property is locked.
		//
		\ArrayObject::offsetSet(
			$this->mCollection->ClassOffset(), get_class( $this ) );

		//
		// Reset modification state.
		//
		if( $class == $this->offsetGet( $this->mCollection->ClassOffset() ) )
			$this->mStatus &= (~ self::kFLAG_DOC_MODIFIED);

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
	 * We overload this method to perform the following operations:
	 *
	 * <ul>
	 * 	<li>We prevent modifying the document class, {@link Collection::ClassOffset()}.
	 * 	<li>We prevent modifying locked offsets, {@link lockedOffsets()}.
	 * 	<li>We set the modification state of the document, {@link SetModificationState()}.
	 * </ul>
	 *
	 * @param string				$theOffset			Offset.
	 * @param mixed					$theValue			Value to set at offset.
	 * @throws \RuntimeException
	 *
	 * @uses lockedOffsets()
	 * @see kFLAG_DOC_MODIFIED
	 */
	public function offsetSet( $theOffset, $theValue )
	{
		//
		// Skip unset.
		//
		if( $theValue !== NULL )
		{
			//
			// Prevent modifying class.
			//
			if( $theOffset == $this->mCollection->ClassOffset() )
				throw new \RuntimeException(
					"You cannot modify the document's class." );				// !@! ==>

			//
			// Check locked offsets.
			//
			if( ($this->mStatus & self::kFLAG_DOC_PERSISTENT)
			 && in_array( $theOffset, $this->lockedOffsets() ) )
				throw new \RuntimeException (
					"The property $theOffset cannot be modified: "
					."the object is persistet.");								// !@! ==>

			//
			// Set modification state.
			//
			$this->mStatus |= self::kFLAG_DOC_MODIFIED;

		} // Not unsetting.

		//
		// Set offset.
		//
		parent::offsetSet( $theOffset, $theValue );

	} // offsetSet.


	/*===================================================================================
	 *	offsetUnset																		*
	 *==================================================================================*/

	/**
	 * <h4>Reset a value at a given offset.</h4>
	 *
	 * We overload this method to perform the following operations:
	 *
	 * <ul>
	 * 	<li>We prevent deleting the document class, {@link Collection::ClassOffset()}.
	 * 	<li>We prevent deleting locked offsets, {@link lockedOffsets()}.
	 * 	<li>We set the modification state of the document, {@link SetModificationState()},
	 * 		only if the offset matches an existing property.
	 * </ul>
	 *
	 * @param string				$theOffset			Offset.
	 * @return void
	 *
	 * @uses lockedOffsets()
	 * @see kFLAG_DOC_MODIFIED
	 * @see kFLAG_DOC_PERSISTENT
	 */
	public function offsetUnset( $theOffset )
	{
		//
		// Check if offset exists.
		//
		if( parent::offsetExists( $theOffset ) )
		{
			//
			// Prevent modifying class.
			//
			if( $theOffset == $this->mCollection->ClassOffset() )
				throw new \RuntimeException (
					"You cannot modify the document's class.");						// !@! ==>

			//
			// Check locked offsets.
			//
			if( ($this->mStatus & self::kFLAG_DOC_PERSISTENT)
			 && in_array( $theOffset, $this->lockedOffsets() ) )
				throw new \RuntimeException (
					"The property $theOffset cannot be modified: "
					."the object is persistet.");								// !@! ==>

			//
			// Set modification state.
			//
			$this->mStatus |= self::kFLAG_DOC_MODIFIED;
		}

		//
		// Unset offset.
		//
		parent::offsetUnset( $theOffset );

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
	 * This method should check whether the document is valid and ready to be stored in its
	 * collection, if that is not the case, the method should raise an exception.
	 *
	 * In this class we check whether all the required properties are there, in derived
	 * classes you should first call this method, then do any other type of validation.
	 *
	 * @throws \RuntimeException
	 *
	 * @uses requiredOffsets()
	 */
	public function Validate()
	{
		//
		// Get required properties.
		//
		$required = $this->requiredOffsets();

		//
		// Check if all required offsets are there.
		//
		if( count( $required )
			!= count( $missing = array_intersect( $required, $this->arrayKeys() ) ) )
			throw new \RuntimeException(
				"Document is missing the following required properties: "
				.implode( ', ', $missing ) );									// !@! ==>

	} // Validate.



/*=======================================================================================
 *																						*
 *							PUBLIC KEY MANAGEMENT INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	SetKey																			*
	 *==================================================================================*/

	/**
	 * <h4>Set document key.</h4>
	 *
	 * This method will be used by {@link Collection} instances to set the document key,
	 * after setting it, the method will reset the modification state; only the document
	 * {@link Collection} instance is allowed to call this method, for this reason it
	 * expects the caller's <tt>$this</tt> value as parameter. This is because the key is
	 * locked once the document becomes persistent.
	 *
	 * @param mixed					$theKey				The document key.
	 * @param Collection			$theSetter			The instance calling the method.
	 * @throws \RuntimeException
	 *
	 * @see kFLAG_DOC_MODIFIED
	 */
	public function SetKey( $theKey, Collection $theSetter )
	{
		//
		// Handle persistent object and assert caller
		//
		if( ($this->mStatus & self::kFLAG_DOC_PERSISTENT)
		 && ($theSetter !== $this->mCollection) )
			throw new \RuntimeException (
				"Only the document's collection may set its key "
			   ."once the document is persistent.");							// !@! ==>

		//
		// Set document key.
		//
		\ArrayObject::offsetSet( $theSetter->KeyOffset(), $theKey );

		//
		// Reset modification state.
		//
		$this->mStatus &= (~ self::kFLAG_DOC_MODIFIED);

	} // SetKey.



/*=======================================================================================
 *																						*
 *								PUBLIC STATUS INTERFACE									*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	IsModified																		*
	 *==================================================================================*/

	/**
	 * <h4>Handle the document modification state.</h4>
	 *
	 * This method can be used to set or reset the document modification state, the state
	 * can only be changed by the current object or by its {@link Collection}, or an
	 * exception will be raised.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 * 	<li><b>$theValue</b>: The new state or operation:
	 * 	 <ul>
	 * 		<li><tt>NULL</tt>: Return the current state; the other parameter is ignored.
	 * 		<li><tt>TRUE</tt>: Set the current state; in this case the second parameter is
	 * 			required.
	 * 		<li><tt>TRUE</tt>: Reset the current state; in this case the second parameter is
	 * 			required.
	 * 	 </ul>
	 * 	<li><b>$theSetter</b>: This parameter is required when modifying the current state,
	 * 		it should be the <tt>$this</tt> value of the object calling the method, which
	 * 		can either be the current object itself, or the document's collection.
	 * </ul>
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
	public function IsModified( $theValue = NULL, $theSetter = NULL )
	{
		//
		// Return current state.
		//
		if( $theValue === NULL )
			return (bool) ($this->mStatus & self::kFLAG_DOC_MODIFIED);				// ==>

		//
		// Assert setter.
		//
		if( ($theSetter !== $this)
		 && ($theSetter !== $this->mCollection) )
			throw new \RuntimeException (
				"Only the current document and its collection " .
				"are allowed to change the state." );							// !@! ==>

		return
			$this->manageFlagAttribute(
				$this->mStatus, self::kFLAG_DOC_MODIFIED, $theValue );				// ==>

	} // IsModified.


	/*===================================================================================
	 *	IsPersistent																	*
	 *==================================================================================*/

	/**
	 * <h4>Handle the document persistent state.</h4>
	 *
	 * This method can be used to set or reset the document persistent state, the state can
	 * only be changed by the document's {@link Collection}, or an exception will be raised.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 * 	<li><b>$theValue</b>: The new state or operation:
	 * 	 <ul>
	 * 		<li><tt>NULL</tt>: Return the current state; the other parameter is ignored.
	 * 		<li><tt>TRUE</tt>: Set the current state; in this case the second parameter is
	 * 			required.
	 * 		<li><tt>TRUE</tt>: Reset the current state; in this case the second parameter is
	 * 			required.
	 * 	 </ul>
	 * 	<li><b>$theSetter</b>: This parameter is required when modifying the current state,
	 * 		it should be the <tt>$this</tt> value of the collection setting the state, it
	 * 		must match the collection set in the constructor.
	 * </ul>
	 *
	 * The method will return the state <em>before</em> it was set or reset.
	 *
	 * @param mixed					$theValue			<tt>TRUE</tt> or <tt>FALSE</tt>.
	 * @param mixed					$theSetter			Setting object.
	 * @return bool					New or old state.
	 * @throws RuntimeException
	 *
	 * @uses manageFlagAttribute()
	 * @see kFLAG_DOC_PERSISTENT
	 */
	public function IsPersistent( $theValue = NULL, $theSetter = NULL )
	{
		//
		// Return current state.
		//
		if( $theValue === NULL )
			return (bool) ($this->mStatus & self::kFLAG_DOC_PERSISTENT);			// ==>

		//
		// Assert setter.
		//
		if( $theSetter !== $this )
			throw new \RuntimeException (
				"Only the document's collection " .
				"is allowed to change the state." );							// !@! ==>

		return
			$this->manageFlagAttribute(
				$this->mStatus, self::kFLAG_DOC_PERSISTENT, $theValue );			// ==>

	} // IsPersistent.



/*=======================================================================================
 *																						*
 *							PROTECTED VALIDATION INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	lockedOffsets																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the list of locked offsets.</h4>
	 *
	 * This method should return the list of offsets which cannot be modified once the
	 * object has been committed to its {@link Container}.
	 *
	 * By default the key, revision and class should be locked.
	 *
	 * @return array				List of locked offsets.
	 *
	 * @uses Collection::KeyOffset()
	 * @uses Collection::ClassOffset()
	 * @uses Collection::RevisionOffset()
	 */
	public function lockedOffsets()
	{
		return [ $this->mCollection->KeyOffset(),
				 $this->mCollection->ClassOffset(),
				 $this->mCollection->RevisionOffset() ];							// ==>

		//
		// In derived classes:
		//
	//	return array_merge( parent::lockedOffsets(), [ ... ] );

	} // lockedOffsets.


	/*===================================================================================
	 *	requiredOffsets																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the list of required offsets.</h4>
	 *
	 * This method should return the list of offsets which are required prior to saving the
	 * document in its collection.
	 *
	 * This class doesn't feature any required offsets.
	 *
	 * @return array				List of required offsets.
	 */
	public function requiredOffsets()
	{
		return [];															// ==>

		//
		// In derived classes:
		//
	//	return array_merge( parent::lockedOffsets(), [ ... ] );

	} // requiredOffsets.




} // class Document.


?>
