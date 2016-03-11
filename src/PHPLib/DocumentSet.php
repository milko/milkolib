<?php

/**
 * DocumentSet.php
 *
 * This file contains the definition of the {@link DocumentSet} class.
 */

namespace Milko\PHPLib;

use Milko\PHPLib\Container;
use Milko\PHPLib\Document;
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
abstract class DocumentSet extends Container
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
	 */
	public function __construct( Collection $theCollection, int $theBufferCount = 100 )
	{
		//
		// Set properties.
		//
		$this->mCollection = $theCollection;
		$this->mBufferCount = $theBufferCount;

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
	 */
	public function __destruct( Collection $theCollection, int $theBufferCount = 100 )
	{
		//
		// Flush buffer.
		//
		if( $this->count() )
			$this->mCollection->Insert( $this->toArray(), [ '$doAll' => TRUE ] );

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
	 * @return void
	 */
	public function offsetSet( $theOffset, $theValue )
	{
		//
		// Check buffer.
		//
		if( ($theValue !== NULL)
		 && ($this->count() > $this->mBufferCount) )
		{
			//
			// Flush buffer.
			//
			$this->mCollection->Insert( $this->toArray(), [ '$doAll' => TRUE ] );

			//
			// Reset buffer.
			//
			$this->exchangeArray( [] );

		} // Flushed buffer

		//
		// Call parent method.
		//
		parent::offsetSet( $theOffset, $theValue );

	} // offsetSet.




} // class DocumentSet.


?>
