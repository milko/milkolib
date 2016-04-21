<?php

/**
 * Wrapper.php
 *
 * This file contains the definition of the {@link Wrapper} class.
 */

namespace Milko\PHPLib\MongoDB;

/**
 * Global token definitions.
 */
require_once( dirname(__DIR__) . '/tokens.inc.php' );

/**
 * Global type definitions.
 */
require_once( dirname(__DIR__) . '/types.inc.php' );

/**
 * Global kind definitions.
 */
require_once( dirname(__DIR__) . '/kinds.inc.php' );

/**
 * Global predicate definitions.
 */
require_once( dirname(__DIR__) . '/predicates.inc.php' );

/**
 * Global descriptor definitions.
 */
require_once( dirname(__DIR__) . '/descriptors.inc.php');

/*=======================================================================================
 *																						*
 *									Wrapper.php											*
 *																						*
 *======================================================================================*/

use Milko\PHPLib\MongoDB\Database;
use Milko\PHPLib\tWrapper;

/**
 * <h4>Wrapper object.</h4>
 *
 * This class implements a data repository and ontology derived from the
 * {@link Milko\PHPLib\ArangoDB\Database} class. It aggregates the functionality for
 * implementing an ontology based data repository using in its inherited database.
 *
 *	@package	Core
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		20/04/2016
 */
class Wrapper extends Database
{
	/**
	 * <h4>Wrapper trait.</h4>
	 *
	 * This trait contains the interface implementing the wrapper.
	 */
	use tWrapper;




/*=======================================================================================
 *																						*
 *								PUBLIC DESCRIPTOR INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	NewDescriptorKey																*
	 *==================================================================================*/

	/**
	 * <h4>Return a new descriptor key.</h4>
	 *
	 * We implement this method to use as much as possible native database commands.
	 *
	 * @return string				New descriptor key.
	 * @throws \RuntimeException
	 */
	public function NewDescriptorKey()
	{
		//
		// Get resources collection.
		//
		$resources = $this->offsetGet( kTAG_MONGO_RESOURCES );
		if( $resources instanceof Collection )
		{
			//
			// Get current serial.
			//
			$serial =
				$resources->Connection()->findOne(
					[ $resources->KeyOffset() => kTOKEN_SERIAL_DESCRIPTOR ] );
			if( $serial !== NULL )
			{
				//
				// Check serial number.
				//
				if( $serial->offsetExists( kTOKEN_SERIAL_OFFSET ) )
				{
					//
					// Save serial number.
					//
					$number = $serial->offsetGet( kTOKEN_SERIAL_OFFSET );

					//
					// Update serial number.
					//
					$resources->Connection()->updateOne(
						[ $resources->KeyOffset() => kTOKEN_SERIAL_DESCRIPTOR ],
						[ '$inc' => [ kTOKEN_SERIAL_OFFSET => 1 ] ] );

					return kTOKEN_TAG_PREFIX . dechex( $number );					// ==>

				} // Descriptors serial has number.

				else
					throw new \RuntimeException(
						"Missing descriptors incremental number." );			// !@! ==>

			} // Resources have descriptors serial.

			else
				throw new \RuntimeException(
					"Missing descriptors serial." );							// !@! ==>

		} // Resources collection is connected.

		else
			throw new \RuntimeException(
				"Missing resources collection." );								// !@! ==>

	} // NewDescriptorKey.



} // class Wrapper.


?>
