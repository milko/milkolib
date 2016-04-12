<?php

/**
 * Container.php
 *
 * This file contains the definition of the {@link Milko\PHPLib\Container} class.
 */

namespace Milko\PHPLib;

/*=======================================================================================
 *																						*
 *									Container.php	    								*
 *																						*
 *======================================================================================*/

/**
 * <h4>Container base object.</h4><p />
 *
 * This class is the ancestor of classes that that handle structured data and that can be
 * persistent.
 *
 * The <em>attributes</em> of the object represent transient information which is private
 * to the object itself, this data is stored in the object's data members and is not
 * considered by the persistent framework of this library.
 *
 * The <em>properties</em> of the object represent the persistent information carried by
 * the object, this data is stored in the array data member inherited by the
 * {@link \ArrayObject} ancestor class and are accessed through the array management syntax,
 * this data is used by the persistence framework of the library.
 *
 * Properties cannot hold the <tt>NULL</tt> value, setting a property to that value will
 * result in that property being deleted.
 *
 * The class implements an interface that standardises the way attributes and properties
 * are managed:
 *
 * <ul>
 *  <li><em>Attributes</em>: a protected interface can be used to standardise the behaviour
 *      of member accessor methods, in general there should be a single public method for
 *      a specific attribute that will store, retrieve and delete attributes, depending on
 *      the provided value:
 *   <ul>
 *      <li><tt>NULL</tt>: Retrieve the attribute value.
 *      <li><tt>FALSE</tt>: Reset the attribute value to <tt>NULL</tt>.
 *      <li><em>other</em>: Any other type will result in the attribute being set to that
 *          value.
 *   </ul>
 *  <li><em>Properties</em>: a public interface will take care of implementing the standard
 *      behaviour, this to ensure no warnings are issued:
 *   <ul>
 *      <li>Setting a property to <tt>NULL</tt> will delete the property.
 *      <li>Retrieving a property that does not exist will return the <tt>NULL</tt> value.
 *      <li>Deleting a property that does not exist will do nothing.
 *   </ul>
 * </ul>
 *
 *	@package	Core
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		05/02/2016
 *
 *	@example	../../test/Container.php
 *	@example
 * $test1 = new Milko\PHPLib\Container();<br/>
 * $test2 = new Milko\PHPLib\Container( ['property' => 'value'] );
 *	@example
 * $test1 = new Milko\PHPLib\Container();<br/>
 * $test1[ "property" ] = "value";	// Set a value.<br/>
 * $test1[ "property" ] = NULL;	// Delete value.
 */
class Container extends \ArrayObject
{

	
/*=======================================================================================
 *																						*
 *								PUBLIC ARRAY ACCESS INTERFACE							*
 *																						*
 *======================================================================================*/
	
	
	
	/*===================================================================================
	 *	offsetGet																		*
	 *==================================================================================*/
	
	/**
	 * <h4>Return a value at a given offset.</h4><p />
	 *
	 * We  overload this method to handle the case in which the offset doesn't exist: if
	 * that is the case we return <tt>NULL</tt> instead of issuing a warning.
	 *
	 * @param mixed					$theOffset			Offset.
	 * @return mixed				Offset value or <tt>NULL</tt>.
	 *
	 * @example
	 * $test->offsetGet( "offset" );	// Will return the value at that offset.<br/>
	 * $test->offsetSet( "UNKNOWN" );	// Will not generate a warning and return
	 * 									   <tt>NULL</tt>.<br/>
	 */
	public function offsetGet( $theOffset )
	{
		//
		// Matched offset.
		//
		if( parent::offsetExists( $theOffset ) )
			return parent::offsetGet( $theOffset );									// ==>
		
		return NULL;																// ==>
		
	} // offsetGet.
	
	
	/*===================================================================================
	 *	offsetSet																		*
	 *==================================================================================*/
	
	/**
	 * <h4>Set a value at a given offset.</h4><p />
	 *
	 * We overload this method to handle the <tt>NULL</tt> value in the <tt>$theValue</tt>
	 * parameter: if the offset exists it will be deleted, if not, the method will do
	 * nothing.
	 *
	 * @param string				$theOffset			Offset.
	 * @param mixed					$theValue			Value to set at offset.
	 * @return void
	 *
	 * @example
	 * $test->offsetSet( "offset", "value" );	// Will set a value in that offset.<br/>
	 * $test->offsetSet( "offset", NULL );	// Will unset that offset.<br/>
	 */
	public function offsetSet( $theOffset, $theValue )
	{
		//
		// Skip deletions.
		//
		if( $theValue !== NULL )
			parent::offsetSet( $theOffset, $theValue );

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
	 * <h4>Reset a value at a given offset.</h4><p />
	 *
	 * We overload this method to prevent warnings when a non-existing offset is provided,
	 * in that case we do nothing.
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
			parent::offsetUnset( $theOffset );
		
	} // offsetUnset.
	
	
	
/*=======================================================================================
 *																						*
 *								PUBLIC ARRAY UTILITY INTERFACE							*
 *																						*
 *======================================================================================*/
	
	
	
	/*===================================================================================
	 *	arrayKeys																		*
	 *==================================================================================*/
	
	/**
	 * <h4>Return object's offsets.</h4><p />
	 *
	 * This method has the same function as the PHP function {@link \array_keys()}, it will
	 * return all the object's offset keys as an array.
	 *
	 * @return array				List of object offsets.
	 */
	public function arrayKeys()				{	return array_keys( $this->getArrayCopy() );	}
	
	
	/*===================================================================================
	 *	arrayValues																		*
	 *==================================================================================*/
	
	/**
	 * <h4>Return object's offset values.</h4><p />
	 *
	 * This method has the same function as the PHP function {@link array_values()}, it
	 * will return all the object's offset values as an array.
	 *
	 * @return array				List of object offset values.
	 */
	public function arrayValues()		{	return array_values( $this->getArrayCopy() );	}


	/*===================================================================================
	 *	toArray 																		*
	 *==================================================================================*/

	/**
	 * <h4>Return the object properties as an aray.</h4><p />
	 *
	 * This method can be used to convert the object properties to an array, this will
	 * take care of converting embedded objects deriving from this class.
	 *
	 * @return array				Object and embedded properties as an array.
	 *
	 * @uses convertToArray()
	 */
	public function toArray()
	{
		//
		// Init local storage.
		//
		$array = [];

		//
		// Convert to array.
		//
		$this->convertToArray( $this->getArrayCopy(), $array );

		return $array;        														// ==>

	} // toArray.

	
	
/*=======================================================================================
 *																						*
 *						PROTECTED ATTRIBUTE MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/
	
	
	
	/*===================================================================================
	 *	manageAttribute																	*
	 *==================================================================================*/
	
	/**
	 * <h4>Manage an attribute</h4><p />
	 *
	 * This library implements a standard interface for managing attributes using
	 * accessor methods, attributes are stored in the object's data members, this method
	 * implements this interface:
	 *
	 * <ul>
	 *	<li><tt>&$theMember</tt>: Reference to the object property being managed.
	 *	<li><tt>$theValue</tt>: The attribute value or operation:
	 *	 <ul>
	 *		<li><tt>NULL</tt>: Return the current attribute value.
	 *		<li><tt>FALSE</tt>: Reset the attribute to <tt>NULL</tt> and return the old
	 * 			value.
	 *		<li><em>other</em>: Set the attribute with the provided value and return it.
	 *	 </ul>
	 * </ul>
	 *
	 * @param mixed				   &$theMember			Reference to the data member.
	 * @param mixed					$theValue			Value or operation.
	 * @return mixed				Old or current attribute value.
	 *
	 * @example
	 * $this->manageAttribute( $member, "value" );	// Will set a value in that member.<br/>
	 * $this->manageAttribute( $member, NULL );	// Will return the member's current value.<br/>
	 * $this->manageAttribute( $member, FALSE );	// Will set the member's value to NULL.
	 */
	protected function manageAttribute( &$theMember, $theValue = NULL )
	{
		//
		// Return current value.
		//
		if( $theValue === NULL )
			return $theMember;														// ==>

		//
		// Set the new value.
		//
		if( $theValue !== FALSE )
		{
			$theMember = $theValue;

			return $theValue;														// ==>
		}

		//
		// Save current value.
		//
		$save = $theMember;

		//
		// Reset the member.
		//
		$theMember = NULL;

		return $save;																// ==>

	} // manageAttribute.


	/*===================================================================================
	 *	manageProperty																	*
	 *==================================================================================*/

	/**
	 * <h4>Manage a property</h4><p />
	 *
	 * Properties in this class are stored in an array and accessed by offset, besides this
	 * method, dedicated accessor methods can be used: this method should be used to provide
	 * a consistent interface when deploying member accessor methods that manage properties.
	 *
	 * The method accepts the following parameters:
	 *
	 * <ul>
	 *	<li><tt>$theProperty</tt>: Property offset.
	 *	<li><tt>$theValue</tt>: The property value or operation:
	 *	 <ul>
	 *		<li><tt>NULL</tt>: Return the current property value.
	 *		<li><tt>FALSE</tt>: Delete the property and return the old value.
	 *		<li><em>other</em>: Set the property with the provided value and return it.
	 *	 </ul>
	 * </ul>
	 *
	 * @param string				$theProperty		Property offset.
	 * @param mixed					$theValue			Value or operation.
	 * @return mixed				Old or current property value.
	 *
	 * @example
	 * $this->manageProperty( $offset, "value" );	// Will set a value in that offset.<br/>
	 * $this->manageProperty( $offset, NULL );	// Will return the value at that offset.<br/>
	 * $this->manageProperty( $offset, FALSE );	// Will delete that offset.
	 */
	protected function manageProperty( $theProperty, $theValue = NULL )
	{
		//
		// Return current value.
		//
		if( $theValue === NULL )
			return $this->offsetGet( $theProperty );								// ==>

		//
		// Set the new value.
		//
		if( $theValue !== FALSE )
		{
			$this->offsetSet( $theProperty, $theValue );

			return $theValue;														// ==>
		}

		//
		// Save current value.
		//
		$save = $this->offsetGet( $theProperty );

		//
		// Reset the property.
		//
		$this->offsetUnset( $theProperty );

		return $save;																// ==>

	} // manageProperty.


	/*===================================================================================
	 *	manageIndexedProperty															*
	 *==================================================================================*/

	/**
	 * <h4>Manage an indexed property</h4><p />
	 *
	 * This method can be used to manage a property structured as an associative array, the
	 * method accepts the following parameters:
	 *
	 * <ul>
	 *	<li><tt>$theProperty</tt>: Property offset.
	 *	<li><tt>$theKey</tt>: The property value key or operation:
	 *	 <ul>
	 *		<li><tt>NULL</tt>: Return the full property, the next parameter is ignored.
	 *		<li><tt>FALSE</tt>: Delete the full property and return the old value, the next
	 * 			parameter is ignored.
	 *		<li><em>other</em>: Use the value as the associative array key, the next
	 * 			parameter will be considered the value or operation.
	 *	 </ul>
	 *	<li><tt>$theValue</tt>: The property value or operation:
	 *	 <ul>
	 *		<li><tt>NULL</tt>: Return the property value at the provided key.
	 *		<li><tt>FALSE</tt>: Delete the property at the provided key and return the old
	 * 			value.
	 *		<li><em>other</em>: Set the property at the provided key with the provided value
	 * 			and return it.
	 *	 </ul>
	 * </ul>
	 *
	 * @param string				$theProperty		Property offset.
	 * @param string				$theKey				Key or operation.
	 * @param mixed					$theValue			Value or operation.
	 * @return mixed				Old or current property value.
	 *
	 * @example
	 * $this->manageIndexedProperty( $offset );	// Will retrieve the full offset.<br/>
	 * $this->manageIndexedProperty( $offset, FALSE ); // Will remove the full offset.<br/>
	 * $this->manageIndexedProperty( $offset, 'key', 'value' ); // Will set the value at key "key" to "value".<br/>
	 * $this->manageIndexedProperty( $offset, 'key' ); // Will retrieve the value at key "key".<br/>
	 * $this->manageIndexedProperty( $offset, 'key', FALSE ); // Will reset the value at key "key".<br/>
	 */
	protected function manageIndexedProperty( $theProperty, $theKey = NULL,
											  				$theValue = NULL )
	{
		//
		// Save property.
		//
		$save = $this->offsetGet( $theProperty );

		//
		// Return full property.
		//
		if( $theKey === NULL )
			return $save;															// ==>

		//
		// Delete full property.
		//
		if( $theKey === FALSE )
		{
			$this->offsetUnset( $theProperty );

			return $save;															// ==>
		}

		//
		// Return key property.
		//
		if( $theValue === NULL )
		{
			if( array_key_exists( $theKey, $save ) )
				return $save[ $theKey ];											// ==>

			return NULL;															// ==>
		}

		//
		// Set key property.
		//
		if( $theValue !== FALSE )
		{
			if( $save === NULL )
				$save = [ $theKey => $theValue ];
			else
				$save[ $theKey ] = $theValue;
			$this->offsetSet( $theProperty, $save );

			return $theValue;														// ==>
		}

		//
		// Save old value.
		//
		$old = ( array_key_exists( $theKey, $save ) )
			 ? $save[ $theKey ]
			 : NULL;

		//
		// Remove value at key.
		//
		if( $old !== NULL )
		{
			unset( $save[ $theKey ] );
			if( count( $save ) )
				$this->offsetSet( $theProperty, $save );
			else
				$this->offsetUnset( $theProperty );
		}

		return $old;																// ==>

	} // manageIndexedProperty.


	/*===================================================================================
	 *	manageFlagAttribute																*
	 *==================================================================================*/

	/**
	 * <h4>Manage a flag attribute.</h4><p />
	 *
	 * This method can be used to manage a bitfield attribute, the method expects the
	 * following parameters:
	 *
	 * <ul>
	 * 	<li><b>&$theAttribute</b>: Reference of the attribute.
	 * 	<li><b>$theValue</b>: The switch new value or operation:
	 * 	 <ul>
	 * 		<li><tt>NULL</tt>: Retrieve the current state.
	 * 		<li><tt>TRUE</tt> Set the current state and return the previous state.
	 * 		<li><tt>FALSE</tt>: Reset the current state and return the previous state.
	 * 	 </ul>
	 * 	<li><b>$theMask</b>: The flag mask.
	 * </ul>
	 *
	 * @param bitfield			   &$theAttribute		Bitfield attribute reference.
	 * @param bitfield				$theMask			Flag mask.
	 * @param mixed					$theValue			New value or operation.
	 * @return boolean				Current or previous attribute switch value.
	 */
	protected function manageFlagAttribute( &$theAttribute, $theMask, $theValue = NULL )
	{
		//
		// Return state.
		//
		if( $theValue === NULL )
			return ($theAttribute & $theMask);										// ==>

		//
		// Save previous value.
		//
		$save = (boolean)($theAttribute & $theMask);

		//
		// Set flag.
		//
		if( $theValue )
			$theAttribute |= $theMask;

		//
		// Reset flag.
		//
		else
			$theAttribute &= (~$theMask);

		return $save;																// ==>

	} // manageFlag.

	

/*=======================================================================================
 *																						*
 *							PROTECTED SERIALISATION INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	convertToArray																	*
	 *==================================================================================*/

	/**
	 * <h4>Convert embedded objects to array.</h4><p />
	 *
	 * This method is used by the {@link toArray()} method to convert embedded properties
	 * derived from this class, it willtraverse the object's properties structured
	 * converting any encountered objects to arrays.
	 *
	 * There is no error checking on parameters, it is the caller's responsibility.
	 *
	 * @param array					$theSource			Source structure.
	 * @param array				   &$theDestination		Reference to the destination array.
	 * @return void
	 */
	protected function convertToArray( $theSource, &$theDestination )
	{
		//
		// Traverse source.
		//
		$keys = array_keys( $theSource );
		foreach( $keys as $key )
		{
			//
			// Init local storage.
			//
			$value = & $theSource[ $key ];

			//
			// Handle collections.
			//
			if( is_array( $value )
			 || ($value instanceof \ArrayObject) )
			{
				//
				// Initialise destination element.
				//
				$theDestination[ $key ] = NULL;

				//
				// Convert.
				//
				if( $value instanceof \ArrayObject )
					$this->convertToArray( $value->getArrayCopy(),
							$theDestination[ $key ] );
				else
					$this->convertToArray( $value,
							$theDestination[ $key ] );

			} // Is collection.

			//
			// Handle scalars.
			//
			else
				$theDestination[ $key ] = $value;

		} // Traversing source.

	} // convertToArray.

	
	
} // class Container.


?>
