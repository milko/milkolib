<?php

/**
 * Wrapper.php
 *
 * This file contains the definition of the {@link Wrapper} class.
 */

namespace Milko\PHPLib\ArangoDB;

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

use Milko\PHPLib\ArangoDB\Database;
use Milko\PHPLib\tWrapper;

use triagens\ArangoDb\DocumentHandler as ArangoDocumentHandler;

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
		$resources = $this->offsetGet( kTAG_ARANGO_RESOURCES );
		if( $resources instanceof Collection )
		{
			//
			// Instantiate document handler.
			//
			$handler = new ArangoDocumentHandler( $this->mConnection );

			//
			// Find document.
			//
			$serial =
				$handler->getById(
					(string)$resources, kTOKEN_SERIAL_DESCRIPTOR );

			//
			// Save serial number.
			//
			$number = $serial->get( kTOKEN_SERIAL_OFFSET );
			if( $number !== NULL )
			{
				//
				// Update serial number.
				//
				$serial->set( kTOKEN_SERIAL_OFFSET, $number + 1 );
				$handler->replace( $serial );

				return kTOKEN_TAG_PREFIX . dechex( $number );						// ==>

			} // Descriptors serial has number.

			else
				throw new \RuntimeException(
					"Missing descriptors incremental number." );				// !@! ==>

		} // Resources collection is connected.

		else
			throw new \RuntimeException(
				"Missing resources collection." );								// !@! ==>

	} // NewDescriptorKey.



} // class Wrapper.


?>
