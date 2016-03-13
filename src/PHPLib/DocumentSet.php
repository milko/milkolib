<?php

/**
 * DocumentSet.php
 *
 * This file contains the definition of the {@link DocumentSet} class.
 */

namespace Milko\PHPLib;

use Milko\PHPLib\Collection;

/*=======================================================================================
 *																						*
 *									DocumentSet.php										*
 *																						*
 *======================================================================================*/

/**
 * <h4>Document set object.</h4>
 *
 * This class implements a set of documents that can be inserted into a collection.
 *
 * The class is instantiated by providing the collection and the buffer size: as we add
 * documents to the set, when the buffer count is reached the documents will be inserted.
 *
 * The destructor will take care of inserting the pending buffer contents.
 *
 *	@package	Core
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		11/03/2016
 *
 *	@example	../../test/Document.php
 */
class DocumentSet extends Container
{
	/**
	 * <h4>Collection object.</h4>
	 *
	 * This data member holds the <i>collection object</i>.
	 *
	 * @var Collection
	 */
	protected $mCollection = NULL;

	/**
	 * Buffer size.
	 *
	 * Maximum number of documents in buffer.
	 *
	 * @var int
	 */
	protected $mBufferCount = NULL;




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
	 * We instantiate the class with the collection and buffer count.
	 *
	 * @param Collection			$theCollection		Collection object.
	 * @param int					$theBufferCount		Buffer count.
	 *
	 * @uses Collection()
	 * @uses BufferCount()
	 */
	public function __construct( Collection $theCollection, int $theBufferCount = 100 )
	{
		//
		// Set properties.
		//
		$this->Collection( $theCollection );
		$this->BufferCount( $theBufferCount );

		//
		// Call parent constructor.
		//
		parent::__construct( [] );

	} // Constructor.


	/*===================================================================================
	 *	__destruct																		*
	 *==================================================================================*/

	/**
	 * <h4>Release object.</h4>
	 *
	 * We flush the eventual remaining documents in the buffer.
	 *
	 * @uses Flush()
	 */
	public function __destruct()
	{
		//
		// Flush buffer.
		//
		$this->Flush();

	} // Destructor.



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
	 * We overload this method to flush the buffer when it reaches the limit.
	 *
	 * @param string				$theOffset			Offset.
	 * @param mixed					$theValue			Value to set at offset.
	 *
	 * @uses Flush()
	 */
	public function offsetSet( $theOffset, $theValue )
	{
		//
		// Call parent method.
		//
		parent::offsetSet( $theOffset, $theValue );

		//
		// Flush buffer.
		//
		if( $this->count() >= $this->mBufferCount )
			$this->Flush();

	} // offsetSet.



/*=======================================================================================
 *																						*
 *							PUBLIC MEMBER ACCESSOR INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Collection																		*
	 *==================================================================================*/

	/**
	 * <h4>Manage the collection.</h4>
	 *
	 * This method can be used to set or retrieve the collection attribute, provide a
	 * {@link Collection} object to set, or <tt>NULL</tt> to retrieve the current
	 * collection.
	 *
	 * The current collection will be flushed prior of setting the new value.
	 *
	 * @param mixed					$theValue			Collection or operation.
	 * @return Collection			Current collection.
	 * @throws InvalidArgumentException
	 *
	 * @uses Flush()
	 */
	public function Collection( $theValue = NULL )
	{
		//
		// Return current collection.
		//
		if( $theValue === NULL )
			return $this->mCollection;												// ==>

		//
		// Check collection.
		//
		if( ! ($theValue instanceof Collection) )
			throw new \InvalidArgumentException (
				"Unable to set collection: "
				."must provide an instance of Collection." );					// !@! ==>

		//
		// Flush buffer.
		//
		$this->Flush();

		//
		// Set value.
		//
		$this->mCollection = $theValue;

		return $this->mCollection;													// ==>

	} // Collection.


	/*===================================================================================
	 *	BufferCount																		*
	 *==================================================================================*/

	/**
	 * <h4>Manage the buffer count.</h4>
	 *
	 * This method can be used to set or retrieve the buffer count attribute, provide an
	 * integer to set, or <tt>NULL</tt> to retrieve the current value.
	 *
	 * The current collection will not be flushed by default, when reducing the buffer count
	 * it will be the next operation that will determine whether the buffer will be flushed.
	 *
	 * @param mixed					$theValue			Collection or operation.
	 * @return Collection			Current collection.
	 *
	 * @throws InvalidArgumentException
	 */
	public function BufferCount( $theValue = NULL )
	{
		//
		// Return current count.
		//
		if( $theValue === NULL )
			return $this->mBufferCount;												// ==>

		//
		// Check count.
		//
		if( ! is_int( $theValue ) )
			throw new \InvalidArgumentException (
				"Unable to set buffer count: "
				."expecting an integer." );										// !@! ==>

		//
		// Set value.
		//
		$this->mBufferCount = (int) $theValue;

		return $this->mBufferCount;													// ==>

	} // BufferCount.



/*=======================================================================================
 *																						*
 *								PUBLIC BUFFER INTERFACE									*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Flush																			*
	 *==================================================================================*/

	/**
	 * <h4>Flush the buffer.</h4>
	 *
	 * Insert the contents of the buffer and reset.
	 */
	public function Flush()
	{
		//
		// Check buffer.
		//
		if( $this->count() )
		{
			//
			// Flush buffer.
			//
			$this->mCollection->Insert( $this->toArray(), [ '$doAll' => TRUE ] );

			//
			// Reset buffer.
			//
			$this->exchangeArray( [] );

		} // Buffer not empty.

	} // Flush.




} // class DocumentSet.


?>
