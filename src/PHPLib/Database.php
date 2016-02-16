<?php

/**
 * Database.php
 *
 * This file contains the definition of the {@link Database} class.
 */

namespace Milko\PHPLib;

/*=======================================================================================
 *																						*
 *									Database.php										*
 *																						*
 *======================================================================================*/

/**
 * <h4>Database ancestor object.</h4>
 *
 * This <em>abstract</em> class is the ancestor of all classes representing database
 * instances.
 *
 * This class features a single data member that holds the native driver database object.
 *
 * The class implements a public interface that deploys the common functionality, while a
 * virtual protected interface must be implemented by derived concrete classes to provide
 * the actual database functionality.
 *
 *	@package	Core
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		16/02/2016
 *
 *	@example	../../test/Database.php
 *	@example
 * $server = new Milko\PHPLib\Server( 'protocol://user:pass@host:9090' );<br/>
 * $connection = $server->Connect();<br/>
 * $databases = $connection->DatabaseList();<br/>
 * $database = $connection->DatabaseGet( $databases[ 0 ] );<br/>
 * // Work with that database...<br/>
 * $connection->DatabaseDrop( $databases[ 0 ] );<br/>
 * // Dropped the database.
 */
abstract class Database
{
	/**
	 * <h4>Database native object.</h4>
	 *
	 * This data member holds the <i>database native object</i>, it is the object provided
	 * by the database driver.
	 *
	 * @var mixed
	 */
	protected $mNativeObject = NULL;




/*=======================================================================================
 *																						*
 *							PUBLIC CONNECTION MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	ListDatabases																	*
	 *==================================================================================*/

	/**
	 * <h4>Return server databases list.</h4>
	 *
	 * This method can be used to retrieve the list of server database names, the method
	 * features the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theFlags</b>: A bitfield providing the following options:
	 *	 <ul>
	 * 		<li><tt>{@link kFLAG_CONNECT}: If set, the server will connect if necessary, if
	 * 			not set and the server is not connected, the method will return an empty
	 * 			array.
	 * 		<li><tt>{@link kFLAG_ASSERT}: If set and the server is not connected, an
	 * 			exception will be raised.
	 * 	 </ul>
	 * </ul>
	 *
	 * The {@link kFLAG_CONNECT} flag is set by default to ensure the server is connected.
	 *
	 * @param string				$theFlags			Flags bitfield.
	 * @return mixed				List of database names.
	 *
	 * @uses isConnected()
	 * @uses databasesList()
	 */
//	public function ListDatabases( $theFlags = self::kFLAG_CONNECT )
//	{
//		//
//		// Assert connection.
//		//
//		if( $this->isConnected( $theFlags ) )
//			return $this->databaseList();											// ==>
//
//		return [];																	// ==>
//
//	} // ListDatabases.



} // class Database.


?>
