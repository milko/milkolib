<?php

/**
 * Descriptor.php
 *
 * This file contains the definition of the {@link Term} class.
 */

namespace Milko\PHPLib;

/*=======================================================================================
 *																						*
 *									Descriptor.php										*
 *																						*
 *======================================================================================*/

use Milko\PHPLib\Term;

/**
 * <h4>Descriptor object.</h4>
 *
 * This class implements an object that represents a variable or descriptor, objects of this
 * kind serve the purpose of defining and describing a data type or data variable.
 *
 * The class derives from {@link Term}, descriptors are identified by a global identifier
 * and feature a name and description; the specific functionality of descriptors is
 * implemented with the following additional properties:
 *
 * <ul>
 * 	<li><tt>{@link kTAG_SYMBOL}</tt>: This represents the descriptor <em>symbol</em>, it is
 * 		the token by which values of that descriptor may be tagged in a dataset. This value
 * 		should be unique at least within the datasets in which it is used; the value is used
 * 		in place of the global identifier, because the latter may contain characters not
 * 		allowed in field names, for that reason you should carefully choose a code which is:
 *   <ul>
 * 		<li>Mnemonic, so that humans may recognise the descriptor.
 * 		<li>Short, so that it will not exceed variable name limits.
 * 		<li>Unique, so that descriptors may be used in the same dataset.
 *   </ul>
 * 		<em>This property is required</em>.
 * 	<li><tt>{@link kTAG_SYNONYMS}</tt>: This represents the <em>list of synonyms</em> of the
 * 		descriptor, it is an array of strings holding the different symbols that represent
 * 		the descriptor in different datasets. The synonyms in this list should not be used
 * 		in active descriptors: in that case a synonym descriptor should point to its master
 * 		descriptor with a graph relationship.
 * 		<em>This property is optional</em>.
 * 	 <li><tt>{@link kTAG_TYPE}</tt>: This represents the descriptor <em>data type</em>, it
 * 		essentially defines the common format for all values of that descriptor, it is
 * 		expressed as an enumerated value of the following:
 *    <ul>
 * 		<li>Primitives:
 * 		 <ul>
 * 			<li><tt>{@link kTYPE_MIXED}</tt>: Mixed data type.
 * 			<li><tt>{@link kTYPE_STRING}</tt>: A string or text.
 * 			<li><tt>{@link kTYPE_INT}</tt>: An integer number.
 * 			<li><tt>{@link kTYPE_FLOAT}</tt>: A floating point number.
 * 			<li><tt>{@link kTYPE_BOOLEAN}</tt>: A boolean switch.
 * 		 </ul>
 * 		<li>Native types:
 * 		 <ul>
 * 			<li><tt>{@link kTYPE_DATE}</tt>: A date in the database native type.
 * 			<li><tt>{@link kTYPE_TIMESTAMP}</tt>: A time stamp in the database native type.
 * 		 </ul>
 * 		<li>Derived types:
 * 		 <ul>
 * 			<li><tt>{@link kTYPE_URL}</tt>: A string containing an URL.
 * 			<li><tt>{@link kTYPE_STRING_DATE}</tt>: A date as <tt>YYYYMMDD</tt>.
 * 			<li><tt>{@link kTYPE_STRING_LAT}</tt>: A latitude as <tt>HDDMMSS.SSSS</tt>.
 * 			<li><tt>{@link kTYPE_STRING_LON}</tt>: A longitude as <tt>HDDDMMSS.SSSS</tt>.
 * 		 </ul>
 * 		<li>Reference types:
 * 		 <ul>
 * 			<li><tt>{@link kTYPE_REF}</tt>: A document handle.
 * 			<li><tt>{@link kTYPE_REF_SELF}</tt>: A document key for the same collection.
 * 			<li><tt>{@link kTYPE_REF_TERM}</tt>: A term document key.
 * 			<li><tt>{@link kTYPE_REF_DESCRIPTOR}</tt>: A descriptor document key.
 * 		 </ul>
 * 		<li>Categorical types:
 * 		 <ul>
 * 			<li><tt>{@link kTYPE_ENUM}</tt>: A controlled vocabulary value.
 * 			<li><tt>{@link kTYPE_ENUM_SET}</tt>: A controlled vocabulary value set.
 * 		 </ul>
 * 		<li>Structured types:
 * 		 <ul>
 * 			<li><tt>{@link kTYPE_ARRAY}</tt>: An array of values of indeterminate type.
 * 			<li><tt>{@link kTYPE_STRUCT}</tt>: A structure or associative array.
 * 			<li><tt>{@link kTYPE_SHAPE}</tt>: A geometric shape.
 * 			<li><tt>{@link kTYPE_LANG_STRING}</tt>: A string in various languages.
 * 			<li><tt>{@link kTYPE_LANG_STRINGS}</tt>: A list of strings in various languages.
 * 		 </ul>
 *   </ul>
 * 		<em>This property is required</em>.
 * 	<li><tt>{@link kTAG_KIND}</tt>: This represents the descriptor <em>kind</em>, it
 * 		essentially defines the function of values of that descriptor, it is expressed as an
 * 		enumerated set of values of the following:
 *   <ul>
 * 		<li>Domain:
 * 		 <ul>
 * 			<li><tt>{@link kKIND_CATEGORICAL}</tt>: Categorical value.
 * 			<li><tt>{@link kKIND_QUANTITATIVE}</tt>: Quantitative value.
 * 			<li><tt>{@link kKIND_DISCRETE}</tt>: Discrete value.
 * 		 </ul>
 * 		<li>Usage:
 * 		 <ul>
 * 			<li><tt>{@link kKIND_RECOMMENDED}</tt>: Recommended.
 * 			<li><tt>{@link kKIND_REQUIRED}</tt>: Required.
 * 		 </ul>
 * 		<li>Access:
 * 		 <ul>
 * 			<li><tt>{@link kKIND_PRIVATE_DISPLAY}</tt>: Do not display.
 * 			<li><tt>{@link kKIND_PRIVATE_SEARCH}</tt>: Do not search.
 * 			<li><tt>{@link kKIND_PRIVATE_MODIFY}</tt>: Do not modify.
 * 		 </ul>
 * 		<li>Cardinality:
 * 		 <ul>
 * 			<li><tt>{@link kKIND_LIST}</tt>: List of elements of the defined type.
 * 			<li><tt>{@link kKIND_SUMMARY}</tt>: Can be summarised.
 * 			<li><tt>{@link kKIND_LOOKUP}</tt>: Can be looked up.
 * 		 </ul>
 *   </ul>
 * 		<em>This property is optional</em>.
 * 	<li><tt>{@link kTAG_REF_COUNT}</tt>: This represents the <em>number of values</em> of
 * 		the current descriptor. It keeps track of the usage of the descriptor in data.
 * 		<em>This property is managed</em>.
 * 	<li><tt>{@link kTAG_MIN_VAL}</tt>: This represents the <em>minimum value</em> found in
 * 		the database for the current descriptor.
 * 		<em>This property is managed</em>.
 * 	<li><tt>{@link kTAG_MAX_VAL}</tt>: This represents the <em>maximum value</em> found in
 * 		the database for the current descriptor.
 * 		<em>This property is managed</em>.
 * 	<li><tt>{@link kTAG_PATTERN}</tt>: This represents a <em>string patter</em> that can be
 * 		used to validate the string value.
 * 		<em>This property is optional</em>.
 * 	<li><tt>{@link kTAG_MIN_VAL_EXPECTED}</tt>: This represents the <em>minimum value</em>
 * 		that an instance of the current descriptor can take.
 * 		<em>This property is optional</em>.
 * 	<li><tt>{@link kTAG_MAX_VAL_EXPECTED}</tt>: This represents the <em>maximum value</em>
 * 		that an instance of the current descriptor can take.
 * 		<em>This property is optional</em>.
 * </ul>
 *
 * The descriptor's {@link Collection::OffsetKey()} property is a string, for user defined
 * descriptors it will be a string prefixed by the {@link kTOKEN_TAG_PREFIX} token followed
 * by a hexadecimal number that represents a global counter: as new descriptors are added,
 * this value is incremented, so the key is relevant only to the current database.
 *
 * Descriptor objects <em>should be stored in a default collection</em>.
 *
 *	@package	Terms
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		14/03/2016
 */
class Descriptor extends Term
{



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
	 * We overload this method to store the descriptor into the wrapper cache.
	 *
	 * @return mixed				The document handle.
	 *
	 * @uses IsModified()
	 * @uses IsPersistent()
	 * @uses Collection::NewDocumentHandle()
	 */
	public function Store()
	{
		//
		// Set in cache.
		//
		if( $this->IsModified()
		 || (! $this->IsPersistent()) )
		{
			//
			// Store.
			//
			$handle = parent::Store();

			//
			// Set in cache.
			//
			$this->mCollection->Database()->SetDescriptor( $this );

			return $handle;															// ==>

		} // Modified or not persistent.

		return $this->mCollection->NewDocumentHandle( $this );						// ==>

	} // Store.


	/*===================================================================================
	 *	Delete																			*
	 *==================================================================================*/

	/**
	 * <h4>Delete object.</h4>
	 *
	 * We overload this method to check whether the current descriptor is built-in and if
	 * the descriptor is referenced in data, in that case we raise an exception.
	 *
	 * @return int					The number of deleted records.
	 * @throws \RuntimeException
	 */
	public function Delete()
	{
		//
		// Check whether it is a built-in descriptor.
		//
		if( substr( $this->offsetGet( $this->mCollection->KeyOffset() ), 0, 1 )
			== kTOKEN_TAG_PREFIX )
		{
			//
			// Check if in use.
			//
			if( ! ($count = $this->offsetGet( kTAG_REF_COUNT )) )
			{
				//
				// Delete.
				//
				$count = parent::Delete();
				if( $count )
					$this->Collection()->Database()->DelDescriptor(
						$this->offsetGet( $this->mCollection->KeyOffset() ) );

				return $count;														// ==>
			}

			throw new \RuntimeException (
				"Cannot delete the descriptor: " .
				"it is used $count times." );									// !@! ==>

		} // Not a built-in descriptor.

		throw new \RuntimeException (
			"Cannot delete the descriptor: " .
			"it is a built-in descriptor." );									// !@! ==>

	} // Delete.



/*=======================================================================================
 *																						*
 *								PUBLIC TRAVERSAL INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	PrepareInsert																	*
	 *==================================================================================*/

	/**
	 * <h4>Prepare document to be inserted.</h4>
	 *
	 * We overload this method to set the document key by getting the descriptor serial from
	 * the wrapper.
	 *
	 * If the key is already set, we do nothing: this is to allow the entry of built-in
	 * descriptors.
	 */
	public function PrepareInsert()
	{
		//
		// Set key.
		//
		if( ! $this->offsetExists( $this->mCollection->KeyOffset() ) )
			$this->offsetSet(
				$this->mCollection->KeyOffset(),
				$this->mCollection->Database()->NewDescriptorKey() );

	} // PrepareInsert.



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
	 * We overload this method to add the following offsets:
	 *
	 * <ul>
	 * 	<li><tt>kTAG_TYPE</tt>: Data type.
	 * 	<li><tt>kTAG_KIND</tt>: Data kind.
	 * </ul>
	 *
	 * @return array				List of locked offsets.
	 */
	protected function lockedOffsets()
	{
		return
			array_merge(
				parent::lockedOffsets(),
				[ kTAG_TYPE, kTAG_KIND ] );											// ==>

	} // lockedOffsets.


	/*===================================================================================
	 *	requiredOffsets																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the list of required offsets.</h4>
	 *
	 * We overload this method to add the following offsets:
	 *
	 * <ul>
	 * 	<li><tt>kTAG_SYMBOL</tt>: Symbol.
	 * 	<li><tt>kTAG_TYPE</tt>: Data type.
	 * </ul>
	 *
	 * @return array				List of required offsets.
	 */
	protected function requiredOffsets()
	{
		return
			array_merge(
				parent::requiredOffsets(),
				[ kTAG_SYMBOL, kTAG_TYPE ] );										// ==>

	} // requiredOffsets.



/*=======================================================================================
 *																						*
 *						PROTECTED NAMESPACE COLLECTION INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	getNamespaceCollection															*
	 *==================================================================================*/

	/**
	 * <h4>Get namespace collection.</h4>
	 *
	 * We overload this method to return the wrapper default terms collection..
	 *
	 * @return Collection					Namespace collection.
	 */
	protected function getNamespaceCollection()
	{
		return $this->mCollection->Database()->NewTermsCollection();				// ==>

	} // getNamespaceCollection.



} // class Descriptor.


?>
