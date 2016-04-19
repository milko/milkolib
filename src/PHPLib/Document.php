<?php

/**
 * Document.php
 *
 * This file contains the definition of the {@link Document} class.
 */

namespace Milko\PHPLib;

/**
 * Global token definitions.
 */
require_once( 'tokens.inc.php' );

/**
 * Global type definitions.
 */
require_once( 'types.inc.php' );

/**
 * Global kind definitions.
 */
require_once( 'kinds.inc.php' );

/**
 * Global descriptor definitions.
 */
require_once('descriptors.inc.php');

/*=======================================================================================
 *																						*
 *									Document.php										*
 *																						*
 *======================================================================================*/

use Milko\PHPLib\Container;
use Milko\PHPLib\Collection;

/**
 * <h4>Document ancestor object.</h4>
 * 
 * This class is the ancestor of all classes that implement persistent objects. Instances of
 * this class keep track of their class name, so when retrieved from the database they will
 * be instantiated under the correct class.
 *
 * For the purpose of persisting, the document features a set of default offsets that
 * contain information regarding identifiers, class and revisions:
 *
 * <ul>
 * 	<li><tt>{@link Collection::KeyOffset()}</tt>: The offset of the document unique key.
 * 	<li><tt>{@link Collection::ClassOffset()}</tt>: The offset of the document class.
 * 	<li><tt>{@link Collection::RevisionOffset()}</tt>: The offset of the document revision.
 * 	<li><tt>{@link Collection::PropertiesOffset()}</tt>: The offset containing the list of
 * 		properties in the document.
 * </ul>
 *
 * To set the document key you would do<br/>
 * <code>$document[ $collection->KeyOffset() ] = $key;</code> and to retrieve the key,
 * <code>$key = $document[ $collection->KeyOffset() ];</code>
 *
 * These offsets are declared by the enclosing collection, which is also responsible of
 * instantiating ({@link Collection::NewDocument()} and serialising the document: this is
 * because the above property tags depend on the native database engine and may also depend
 * on the business logic of the collection; for this reason, when a document is instantiated
 * by a collection, the latter is stored in an attribute.
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
	 * document collection in an attribute.
	 *
	 * The collection is required, because it is the collection that knows which offsets
	 * corresponds to the class, key, revision and properties list.
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
	 * 	<li>We prevent modifying the document properties list,
	 * 		{@link Collection::PropertiesOffset()}.
	 * 	<li>We prevent modifying locked offsets, {@link lockedOffsets()}.
	 * 	<li>We set the modification state of the document, {@link SetModificationState()}.
	 * </ul>
	 *
	 * @param string				$theOffset			Offset.
	 * @param mixed					$theValue			Value to set at offset.
	 * @throws \RuntimeException
	 *
	 * @uses lockedOffsets()
	 * @uses privateOffsets()
	 */
	public function offsetSet( $theOffset, $theValue )
	{
		//
		// Skip unset.
		//
		if( $theValue !== NULL )
		{
			//
			// Check private offsets.
			//
			if( in_array( $theOffset, $this->privateOffsets() ) )
				throw new \RuntimeException (
					"The property $theOffset cannot be modified: "
					."it is reserved.");										// !@! ==>

			//
			// Check locked offsets.
			//
			if( ($this->mStatus & self::kFLAG_DOC_PERSISTENT)
			 && in_array( $theOffset, $this->lockedOffsets() ) )
				throw new \RuntimeException (
					"The property $theOffset cannot be modified: "
					."the object is persistent.");								// !@! ==>

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
	 * @uses privateOffsets()
	 */
	public function offsetUnset( $theOffset )
	{
		//
		// Check if offset exists.
		//
		if( parent::offsetExists( $theOffset ) )
		{
			//
			// Check private offsets.
			//
			if( in_array( $theOffset, $this->privateOffsets() ) )
				throw new \RuntimeException (
					"The property $theOffset cannot be modified: "
					."it is reserved.");										// !@! ==>

			//
			// Check locked offsets.
			//
			if( ($this->mStatus & self::kFLAG_DOC_PERSISTENT)
			 && in_array( $theOffset, $this->lockedOffsets() ) )
				throw new \RuntimeException (
					"The property $theOffset cannot be modified: "
					."the object is persistent.");								// !@! ==>

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
 *							PUBLIC MEMBER MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Collection																		*
	 *==================================================================================*/

	/**
	 * <h4>Return the document collection.</h4>
	 *
	 * This method can be used to retrieve the document collection object.
	 *
	 * @return Collection			Document collection object.
	 */
	public function Collection()							{	return $this->mCollection;	}



/*=======================================================================================
 *																						*
 *							PUBLIC DOCUMENT PERSISTENCE INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Store																			*
	 *==================================================================================*/

	/**
	 * <h4>Store object.</h4>
	 *
	 * This method will store the current document into the collection provided when
	 * instantiated, if the document is persistent ({@link IsPersistent()}), the document
	 * will be replaced, if not, it will be inserted.
	 *
	 * The method will return the document handle.
	 *
	 * These are the actual steps followed when storing the dicument:
	 *
	 * <ul>
	 * 	<li><tt>{@link Validate()</tt>: Ensure the current document and its eventual
	 * 		embedded sub-documents are valid.
	 * 	<li><tt>{@link StoreSubdocuments()</tt>: Store its eventual embedded subdocuments
	 * 		replacing them with their references.
	 * 	<li>When inserting:
	 * 	 <ul>
	 * 		<li><tt>{@link PrepareInsert()</tt>: Perform any necessary operations before the
	 * 			document is inserted.
	 * 		<li><tt>{@link Collection::normaliseInsertedDocument()</tt>: Prepare the
	 * 			document after it was inserted.
	 * 	 </ul>
	 * 	<li>When replacing:
	 * 	 <ul>
	 * 		<li><tt>{@link Collection::normaliseReplacedDocument()</tt>: Prepare the
	 * 			document after it was replaced.
	 * 	 </ul>
	 * </ul>
	 *
	 * All the above operations are performed by the collection that holds the document.
	 *
	 * @return mixed				The document handle.
	 *
	 * @uses IsModified()
	 * @uses IsPersistent()
	 * @uses Collection::Insert()
	 * @uses Collection::Replace()
	 * @uses Collection::NewDocumentHandle()
	 */
	public function Store()
	{
		//
		// Check if not persistent and modified.
		//
		if( $this->IsModified()
		 || (! $this->IsPersistent()) )
		{
			//
			// Replace.
			//
			if( $this->IsPersistent() )
				$this->mCollection->Replace( $this );

			//
			// Insert.
			//
			else
				$this->mCollection->Insert( $this );

		} // Modified or not persistent.

		return $this->mCollection->NewDocumentHandle( $this );						// ==>

	} // Store.


	/*===================================================================================
	 *	Delete																			*
	 *==================================================================================*/

	/**
	 * <h4>Delete object.</h4>
	 *
	 * This method will delete the current document from the container provided when
	 * instantiated, the method will update the document state by resetting its persistent
	 * state, {@link IsPersistent()}, and setting its modification state,
	 * {@link IsModified()}.
	 *
	 * The method will return the number of deleted documents, <tt>1</tt>, ot <tt>0</tt> if
	 * the document was not deleted or if the document is not persistent.
	 *
	 * These are the actual steps followed when deleting the dicument:
	 *
	 * <ul>
	 * 	<li><tt>{@link Collection::normaliseDeletedDocument()</tt>: Prepare the
	 * 		document after it was deleted.
	 * </ul>
	 *
	 * All the above operations are performed by the collection that holds the document.
	 *
	 * @return int					The number of deleted records.
	 *
	 * @uses mCollection
	 * @uses IsPersistent()
	 * @uses Collection::Delete()
	 */
	public function Delete()
	{
		//
		// Check if persistent.
		//
		if( $this->IsPersistent() )
			return $this->mCollection->DeleteDocument( $this );						// ==>

		return 0;																	// ==>

	} // Delete.



/*=======================================================================================
 *																						*
 *								PUBLIC REFERENCE INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	CreateReference																	*
	 *==================================================================================*/

	/**
	 * <h4>Create a document reference.</h4>
	 *
	 * The duty of this method is to convert the provided document into a reference of the
	 * type determined by the provided offset.
	 *
	 * The default reference type is a document handle, but in certain cases, when the
	 * collection is implicit, there is only the need to specify the key: use this method
	 * whenever you need to store a document reference.
	 *
	 * The method expects two parameters:
	 *
	 * <ul>
	 * 	<li><b>$theOffset</b>: The offset that will receive the reference.
	 * 	<li><b>$theDocument</b>: The document to reference:
	 * 	 <ul>
	 * 		<li><tt>NULL</tt>: If omitted or <tt>NULL</tt>, the method will use the value
	 * 			currently stored at the provided offset:
	 * 		 <ul>
	 * 			<li><tt>NULL</tt>: If the offset is missing, the method will return
	 * 				<tt>NULL</tt>.
	 * 			<li><tt>{@link Document}</tt>: The document will be referenced.
	 * 			<li><em>other</em>: Any other value will be assumed to be a reference and
	 * 				will be returned.
	 * 		 </ul>
	 * 		<li><tt>{@link Document}</tt>: The document will be referenced.
	 * 		<li><em>other</em>: Any other value will be assumed to be a reference and will
	 * 			be returned as is.
	 * 	 </ul>
	 * </ul>
	 *
	 * The method makes use of a protected method, {@link doCreateReference}, which must be
	 * implemented by derived classes: its duty is to determine what kind of reference
	 * should be stored at the provided offset.
	 *
	 * @param string				$theOffset			Sub-document offset.
	 * @param Document				$theDocument		Sub-document object or
	 * 													<tt>NULL</tt>.
	 * @return mixed				The document reference.
	 *
	 * @uses doDocumentToReference()
	 */
	public function CreateReference( $theOffset, $theDocument = NULL )
	{
		//
		// Handle existing value.
		//
		if( $theDocument === NULL )
			$theDocument = $this->offsetGet( $theOffset );

		//
		// Handle document.
		//
		if( $theDocument instanceof Document )
			return $this->doCreateReference( $theOffset, $theDocument );			// ==>

		return $theDocument;														// ==>

	} // CreateReference.


	/*===================================================================================
	 *	ResolveReference																*
	 *==================================================================================*/

	/**
	 * <h4>Resolve a document reference.</h4>
	 *
	 * The duty of this method is to resolve the reference stored at the provided offset
	 * into a document.
	 *
	 * The default reference type is a document handle, but in certain cases, when the
	 * collection is implicit, there is only the need to specify the key: use this method
	 * whenever you need to resolve a document reference.
	 *
	 * The method expects two parameters:
	 *
	 * <ul>
	 * 	<li><b>$theOffset</b>: The offset that will receive the reference.
	 * 	<li><b>$theReference</b>: The document reference:
	 * 	 <ul>
	 * 		<li><tt>NULL</tt>: If omitted or <tt>NULL</tt>, the method will use the value
	 * 			currently stored at the provided offset:
	 * 		 <ul>
	 * 			<li><tt>NULL</tt>: If the offset is missing, the method will return
	 * 				<tt>NULL</tt>.
	 * 			<li><tt>{@link Document}</tt>: The document will be returned.
	 * 			<li><em>other</em>: Any other value will be assumed to be a reference and
	 * 				will be resolved.
	 * 		 </ul>
	 * 		<li><tt>{@link Document}</tt>: The document will be returned.
	 * 		<li><em>other</em>: Any other value will be assumed to be a reference and will
	 * 			be resolved.
	 * 	 </ul>
	 * </ul>
	 *
	 * The method makes use of a protected method, {@link doResolveReference}, which must be
	 * implemented by derived classes: its duty is to determine what kind of reference is
	 * stored at the provided offset.
	 *
	 * @param string				$theOffset			Sub-document offset.
	 * @param mixed					$theReference		Sub-document reference or
	 * 													<tt>NULL</tt>.
	 * @return Document				The referenced document.
	 *
	 * @uses doReferenceToDocument()
	 */
	public function ResolveReference( $theOffset, $theReference = NULL )
	{
		//
		// Handle existing value.
		//
		if( $theReference === NULL )
			$theReference = $this->offsetGet( $theOffset );

		//
		// Handle missing offset.
		//
		if( $theReference === NULL )
			return NULL;															// ==>

		//
		// Handle document.
		//
		if( $theReference instanceof Document )
			return $theReference;													// ==>

		return $this->doResolveReference( $theOffset, $theReference );				// ==>

	} // ResolveReference.



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
	 * @throws \RuntimeException
	 *
	 * @uses manageFlagAttribute()
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
		 && ($theSetter !== $this->Collection()) )
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
	 * @throws \RuntimeException
	 *
	 * @uses manageFlagAttribute()
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
		if( $theSetter !== $this->mCollection )
			throw new \RuntimeException (
				"Only the document's collection " .
				"is allowed to change the state." );							// !@! ==>

		return
			$this->manageFlagAttribute(
				$this->mStatus, self::kFLAG_DOC_PERSISTENT, $theValue );			// ==>

	} // IsPersistent.



/*=======================================================================================
 *																						*
 *							PUBLIC VALIDATION INTERFACE									*
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
	 * In this class we check whether all required properties are there and we traverse the
	 * object calling this method for each of them, in derived classes you should first
	 * check local properties, then call the parent method.
	 *
	 * @throws \RuntimeException
	 *
	 * @uses requiredOffsets()
	 * @uses doValidateSubdocuments()
	 */
	public function Validate()
	{
		//
		// Get required and provided properties.
		//
		$required = $this->requiredOffsets();
		$provided = array_intersect( $required, $this->arrayKeys() );
		$missing = array_diff( $required, $provided );

		//
		// Check if all required offsets are there.
		//
		if( count( $missing ) )
			throw new \RuntimeException(
				"Document is missing the following required properties: "
				.implode( ', ', $missing ) );									// !@! ==>

		//
		// Validate subdocuments.
		//
		$subs = $this->getArrayCopy();
		$this->doValidateSubdocuments( $subs );

	} // validateDocument.



/*=======================================================================================
 *																						*
 *								PUBLIC TRAVERSAL INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	TraverseDocument																*
	 *==================================================================================*/

	/**
	 * <h4>Traverse document.</h4>
	 *
	 * This method should be called prior to inserting the object, it will traverse all
	 * object structures inserting sub-documents expressed as document objects that
	 * are modified ({@link IsModified()}) and are not persistent ({@link IsPersistent()}),
	 * replacing them with their document handles.
	 *
	 * It will also collect and return the list of leaf offsets used in the document.
	 *
	 * This method <em>must</em> be called <em>after</em> calling {@link Validate()}, which
	 * validates also the sub-documents.
	 *
	 * @return array				List of leaf offsets.
	 *
	 * @uses doStoreRelated()
	 */
	public function TraverseDocument()
	{
		//
		// Init local storage.
		//
		$offsets = [];
		$data = $this->getArrayCopy();

		//
		// Convert to array.
		//
		$this->doTraverseDocument( $data, $offsets );

		//
		// Update document data.
		//
		$this->exchangeArray( $data );

		return $offsets;															// ==>

	} // StoreSubdocuments.


	/*===================================================================================
	 *	PrepareInsert																	*
	 *==================================================================================*/

	/**
	 * <h4>Prepare document to be inserted.</h4>
	 *
	 * This method will prepare the document before it should be inserted, the method
	 * expects the document to be valid and all references resolved.
	 *
	 * In this class we do nothing, in derived classes you may overload this method to
	 * perform custom operations.
	 */
	public function PrepareInsert()													   {}


	/*===================================================================================
	 *	PrepareReplace																	*
	 *==================================================================================*/

	/**
	 * <h4>Prepare document to be replaced.</h4>
	 *
	 * This method will prepare the document before it should be replaced, the method
	 * expects the document to be valid and all references resolved.
	 *
	 * In this class we do nothing, in derived classes you may overload this method to
	 * perform custom operations.
	 */
	public function PrepareReplace()												   {}


	/*===================================================================================
	 *	SetPropertiesList																*
	 *==================================================================================*/

	/**
	 * <h4>Set properties list.</h4>
	 *
	 * This method can be used to set the properties list
	 * ({@link Collection::PropertiesOffset()}), only the document collection is allowed to
	 * call this method, or an exception will be raised..
	 *
	 * @param mixed					$theValue			Properties list.
	 * @param mixed					$theSetter			Setting object.
	 * @return array|NULL			The provided value or <tt>NULL</tt> if empty.
	 * @throws \RuntimeException
	 */
	public function SetPropertiesList( array $theValue, $theSetter )
	{
		//
		// Assert setter.
		//
		if( $theSetter !== $this->Collection() )
			throw new \RuntimeException (
				"Only the current document collection " .
				"is allowed to set the properties list." );						// !@! ==>

		//
		// Normalise list.
		//
		if( ! count( $theValue ) )
			$theValue = NULL;

		//
		// Set property.
		//
		Container::offsetSet( $this->mCollection->PropertiesOffset(), $theValue );

		return $theValue;															// ==>

	} // SetPropertiesList.



/*=======================================================================================
 *																						*
 *								PROTECTED OFFSET INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	privateOffsets																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the list of private offsets.</h4>
	 *
	 * This method should return the list of private which can only be modified internally.
	 *
	 * By default the class and properties list are managed internally.
	 *
	 * @return array				List of private offsets.
	 *
	 * @uses Collection::ClassOffset()
	 * @uses Collection::PropertiesOffset()
	 */
	protected function privateOffsets()
	{
		return [ $this->mCollection->ClassOffset(),
				 $this->mCollection->PropertiesOffset() ];							// ==>

		//
		// In derived classes:
		//
		//	return array_merge( parent::privateOffsets(), [ ... ] );

	} // privateOffsets.


	/*===================================================================================
	 *	lockedOffsets																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the list of locked offsets.</h4>
	 *
	 * This method should return the list of offsets which cannot be modified once the
	 * object has been committed to its {@link Collection}.
	 *
	 * By default the key and revision should be locked.
	 *
	 * @return array				List of locked offsets.
	 *
	 * @uses Collection::KeyOffset()
	 * @uses Collection::RevisionOffset()
	 */
	protected function lockedOffsets()
	{
		return [ $this->mCollection->KeyOffset(),
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
	protected function requiredOffsets()
	{
		return [];															// ==>

		//
		// In derived classes:
		//
		//	return array_merge( parent::requiredOffsets(), [ ... ] );

	} // requiredOffsets.



/*=======================================================================================
 *																						*
 *							PROTECTED REFERENCE INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	doCreateReference																*
	 *==================================================================================*/

	/**
	 * <h4>Reference embedded documents.</h4>
	 *
	 * The duty of this method is to convert the provided document into a reference which is
	 * supposed to be stored at the provided offset.
	 *
	 * The method will return the correct type of offset <em>without storing it</em>, if the
	 * provided document lacks the necessary data to generate the reference, the method
	 * should raise an exception.
	 *
	 * In this class we return a handle by default, derived classes may overload this method
	 * to handle other reference types.
	 *
	 * @param string				$theOffset			Sub-document offset.
	 * @param Document				$theDocument		Subdocument.
	 * @return mixed				The document key or handle.
	 */
	protected function doCreateReference( $theOffset, Document $theDocument )
	{
		return $theDocument->mCollection->NewDocumentHandle( $theDocument );		// ==>

	} // doCreateReference.


	/*===================================================================================
	 *	doResolveReference																*
	 *==================================================================================*/

	/**
	 * <h4>Resolve embedded documents.</h4>
	 *
	 * The duty of this method is to resolve the provided document reference associated with
	 * the provided offset into a document object.
	 *
	 * The method will return the {@link Document} instance referenced by the provided
	 * reference, or raise an exception if the referenced document cannot be resolved.
	 *
	 * In this class we assume the provided reference is by default a handle, derived
	 * classes should overload this method to handle other reference types.
	 *
	 * @param string				$theOffset			Sub-document offset.
	 * @param mixed					$theReference		Subdocument reference.
	 * @return mixed				The document key or handle.
	 *
	 * @uses Collection::NewDocumentHandle()
	 */
	protected function doResolveReference( $theOffset, $theReference )
	{
		return $this->mCollection->FindByHandle( $theReference );					// ==>

	} // doResolveReference.



/*=======================================================================================
 *																						*
 *								PROTECTED TRAVERSAL INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	doTraverseDocument																*
	 *==================================================================================*/

	/**
	 * <h4>Traverse document.</h4>
	 *
	 * This method will traverse the current object and {@link Store()} any property that is
	 * a document instance which is modified ({@link IsModified()}) or not persistent
	 * ({@link IsPersistent()}); once the document is stored, the source property will be
	 * replaced with the stored document's handle.
	 *
	 * The method will also collect and return the list of leaf offsets used in the
	 * document.
	 *
	 * This method <em>must</em> be called <em>after</em> calling {@link Validate()} to
	 * ensure the subdocuments are all valid.
	 *
	 * @param array				   &$theData			Document data.
	 * @param array				   &$theOffsets			Document offsets.
	 *
	 * @uses Store()
	 * @uses IsModified()
	 * @uses IsPersistent()
	 */
	protected function doTraverseDocument( array &$theData, array &$theOffsets )
	{
		//
		// Traverse data.
		//
		foreach( $theData as $key => $value )
		{
			//
			// Collect offset.
			//
			if( (substr( $key, 0, 1 ) == kTOKEN_TAG_PREFIX)
				&& (! in_array( $key, $theOffsets )) )
				$theOffsets[] = $key;

			//
			// Validate documents.
			//
			if( $value instanceof Document )
			{
				//
				// Insert new documents.
				//
				if( $value->IsModified()
				 || (! $value->IsPersistent()) )
					$value->Store();

				//
				// Replace with handle.
				//
				$theData[ $key ] = $this->doCreateReference( $key, $value );

			} // Is a document.

			//
			// Handle arrays and array objects.
			//
			elseif( is_array( $value )
				|| ($value instanceof \ArrayObject) )
			{
				//
				// Convert to array.
				//
				if( $value instanceof \ArrayObject )
					$value = $value->getArrayCopy();

				//
				// Recurse.
				//
				$this->doTraverseDocument( $value, $theOffsets );

				//
				// Replace in data.
				//
				$theData[ $key ] = $value;

			} // Array or iterable.

		} // Traversing the document.

	} // doTraverseDocument.


	/*===================================================================================
	 *	doValidateSubdocuments															*
	 *==================================================================================*/

	/**
	 * <h4>Validate embedded documents.</h4>
	 *
	 * This method will traverse the current object and call the {@link Validate()} method
	 * on all document objects encountered.
	 *
	 * @param array				   &$theData			Reference to the object data.
	 *
	 * @uses Store()
	 * @uses IsModified()
	 * @uses IsPersistent()
	 */
	protected function doValidateSubdocuments( &$theData )
	{
		//
		// Traverse data.
		//
		foreach( $theData as $key => $value )
		{
			//
			// Validate documents.
			//
			if( $value instanceof Document )
				$value->Validate();

			//
			// Handle arrays and array objects.
			//
			elseif( is_array( $value )
				|| ($value instanceof \ArrayObject) )
				$this->doValidateSubdocuments( $theData[ $key ] );

		} // Traversing the document.

	} // doValidateSubdocuments.




} // class Document.


?>
