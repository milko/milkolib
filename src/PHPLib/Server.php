<?php

/**
 * Server.php
 *
 * This file contains the definition of the {@link Server} class.
 */

namespace Milko\PHPLib;

use Milko\PHPLib\DataSource;

/*=======================================================================================
 *																						*
 *										Server.php										*
 *																						*
 *======================================================================================*/

/**
 * <h4>Base server abstract object.</h4>
 *
 * This <em>abstract</em> class is the ancestor of all classes representing server
 * instances.
 *
 * This class is derived from the {@link DataSource} class, it uses its inherited connection
 * string to provide server's connection parameters.
 *
 * The class features an attribute, {@link $mConnection}, that represents the server native
 * connection object, this is instantiated when the server connects.
 *
 * The class implements the public interface that takes care of connecting, disconnecting,
 * sleeping and waking the object, the implementation of the connection workflow is
 * delegated to a protected interface which is virtual: this is why this class is declared
 * abstract. To create a server one must derive from this class and implement the protected
 * interface.
 *
 * The sleep and wake workflow ensures that the connection is closed before the object
 * goes to sleep and opened when it wakes, this is to handle native connection objects that
 * cannot be serialised in the session.
 *
 * When a connection is open, none of the inherited {@link DataSource} properties can be
 * modified, attempting to do so will trigger an exception.
 *
 *	@package	Core
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		06/02/2016
 *
 *	@example	../../test/Server.php
 *	@example
 * $server = new Milko\PHPLib\Server( 'protocol://user:pass@host:9090' );<br/>
 * $connection = $server->Connect();
 */
abstract class Server extends DataSource
{
	/**
	 * <h4>Server connection object.</h4>
	 *
	 * This data member holds the <i>server connection object</i>, it is the native object
	 * representing the server connection.
	 *
	 * Before the object goes to sleep, this attribute will be set to <tt>TRUE</tt> if a
	 * connection was open and to <tt>NULL</tt> if not: this determines whether a
	 * connection should be restored when the object is waken.
	 *
	 * @var mixed
	 */
	protected $mConnection = NULL;

	/**
	 * Default flags set.
	 *
	 * This represents the default set of flags.
	 *
	 * @var string
	 */
	const kFLAG_DEFAULT = 0x00000000;

	/**
	 * Assert.
	 *
	 * If this flag is set, a missing connection will trigger an exception.
	 *
	 * @var string
	 */
	const kFLAG_ASSERT = 0x00000001;

	/**
	 * Connect if necessary.
	 *
	 * If this flag is set, the server connection will be opened if necessary.
	 *
	 * @var string
	 */
	const kFLAG_CONNECT = 0x00000002;

	/**
	 * Create resource if it doesn't exist.
	 *
	 * If this flag is set, the resource will be created if necessary.
	 *
	 * @var string
	 */
	const kFLAG_CREATE = 0x00000004;




/*=======================================================================================
 *																						*
 *										MAGIC											*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	__destruct																		*
	 *==================================================================================*/

	/**
	 * <h4>Destruct instance.</h4>
	 *
	 * In this class we close any open connection before disposing of the object.
	 *
	 * @uses Disconnect()
	 */
	public function __destruct()
	{
		//
		// Disconnect.
		//
		$this->Disconnect();

	} // __destruct.


	/*===================================================================================
	 *	__sleep																			*
	 *==================================================================================*/

	/**
	 * <h4>Put the object to sleep.</h4>
	 *
	 * This method will close the connection and replace the connection resource with
	 * <tt>TRUE</tt> if the connection was open, this will be used by the {@link __wakeup()}
	 * method to re-open the connection.
	 *
	 * @uses Disconnect()
	 */
	public function __sleep()
	{
		//
		// Disconnect.
		//
		$connected = $this->Disconnect();

		//
		// Signal there was a connection.
		//
		$this->mConnection = ( $connected ) ? TRUE : NULL;

	} // __sleep.


	/*===================================================================================
	 *	__wakeup																		*
	 *==================================================================================*/

	/**
	 * <h4>Wake the object from sleep.</h4>
	 *
	 * This method will re-open the connection if it was closed by the {@link __sleep()}
	 * method.
	 *
	 * @uses Connect()
	 */
	public function __wakeup()
	{
		//
		// Open closed connection.
		//
		if( $this->mConnection === TRUE )
			$this->Connect();

	} // __wakeup.



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
	 * We overload this method to prevent any modification of data source properties while
	 * there is an open connection.
	 *
	 * @param string				$theOffset			Offset.
	 * @param mixed					$theValue			Value to set at offset.
	 * @throws \BadMethodCallException
	 *
	 * @uses isConnected()
	 * @uses isLockedProperty()
	 */
	public function offsetSet( $theOffset, $theValue )
	{
		//
		// Handle open connection.
		//
		if( $this->isConnected()
		 && $this->isLockedProperty( $theOffset ) )
			throw new \BadMethodCallException (
				"The object properties cannot be modified "
				."while there is an open connection." );						// !@! ==>

		//
		// Call parent method.
		//
		parent::offsetSet( $theOffset, $theValue );

	} // offsetSet.


	/*===================================================================================
	 *	offsetUnset																		*
	 *==================================================================================*/

	/**
	 * <h4>Reset a value at a given offset.</h4>
	 *
	 * We overload this method to prevent any modification of data source properties while
	 * there is an open connection.
	 *
	 * @param string				$theOffset			Offset.
	 * @throws \BadMethodCallException
	 *
	 * @uses isConnected()
	 * @uses isLockedProperty()
	 */
	public function offsetUnset( $theOffset )
	{
		//
		// Handle open connection.
		//
		if( $this->isConnected()
		 && $this->isLockedProperty( $theOffset ) )
			throw new \BadMethodCallException (
				"The object properties cannot be deleted "
				."while there is an open connection." );						// !@! ==>

		//
		// Call parent method.
		//
		parent::offsetUnset( $theOffset );

	} // offsetUnset.



/*=======================================================================================
 *																						*
 *							PUBLIC MEMBER ACCESSOR INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Connection																		*
	 *==================================================================================*/

	/**
	 * <h4>Return native connection object.</h4>
	 *
	 * This method will return the native connection object, if a connection is open, or
	 * <tt>NULL</tt> if not.
	 *
	 * The provided bitfield parameter provides the following options:
	 *
	 * <ul~
	 * 	<li><tt>{@link kFLAG_CONNECT}</tt>: If set, the server will be connected if that is
	 * 		not yet the case.
	 * 	<li><tt>{@link kFLAG_ASSERT}</tt>: If the <tt>kFLAG_CONNECT</tt> flag is not set and
	 * 		the server is not connected, the method will raise a {@link \RuntimeException}.
	 * </ul>
	 *
	 * The second parameter represents eventual native driver options to be used when
	 * opening the connection.
	 *
	 * @param string				$theFlags			Flags bitfield.
	 * @param array					$theOptions			Connection native options.
	 * @return mixed				Native connection object or <tt>NULL</tt>.
	 *
	 * @example
	 * // Will return connection or NULL if not connected.<br/>
	 * $connection = $server->Connection();<br/>
	 * // Will raise an exception if not connected.<br/>
	 * $connection = $server->Connection(self::kFLAG_ASSERT);
	 * // Will connect if not connected.<br/>
	 * $connection = $server->Connection(self::kFLAG_CONNECT);
	 */
	public function Connection( $theFlags = self::kFLAG_DEFAULT, $theOptions = NULL )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected( $theFlags, $theOptions ) )
			return $this->mConnection;												// ==>

		return NULL;																// ==>

	} // Connection.



/*=======================================================================================
 *																						*
 *							PUBLIC CONNECTION MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Connect																			*
	 *==================================================================================*/

	/**
	 * <h4>Open server connection.</h4>
	 *
	 * This method can be used to create and open the server connection, if the connection
	 * is already open, the method will do nothing.
	 *
	 * The method will return the native connection object, or raise an exception if unable
	 * to open the connection.
	 *
	 * @param array					$theOptions			Connection native options.
	 * @return mixed				Native connection object.
	 *
	 * @uses isConnected( )
	 * @uses connectionCreate()
	 */
	public function Connect( $theOptions = NULL )
	{
		//
		// Check if connected.
		//
		if( ! $this->isConnected() )
			$this->mConnection = $this->connectionCreate( $theOptions );

		return $this->mConnection;													// ==>

	} // Connect.


	/*===================================================================================
	 *	Disconnect																		*
	 *==================================================================================*/

	/**
	 * <h4>Close server connection.</h4>
	 *
	 * This method can be used to close and destruct the server connection, if no connection
	 * was open, the method will do nothing.
	 *
	 * The method will return <tt>TRUE</tt> if it closed a connection
	 *
	 * @param array					$theOptions			Connection native options.
	 * @return boolean				<tt>TRUE</tt> was connected, <tt>FALSE</tt> wasn't.
	 *
	 * @uses isConnected()
	 * @uses connectionDestruct()
	 */
	public function Disconnect( $theOptions = NULL )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
		{
			//
			// Destruct connection.
			//
			$this->connectionDestruct( $theOptions );

			//
			// Reset native connection attribute.
			//
			$this->mConnection = NULL;

			return TRUE;															// ==>
		}

		return FALSE;																// ==>

	} // Disconnect.


	/*===================================================================================
	 *	isConnected																		*
	 *==================================================================================*/

	/**
	 * <h4>Check if connection is open.</h4>
	 *
	 * This method returns a boolean flag indicating whether the connection is open or not.
	 *
	 * The provided bitfield parameter provides the following options:
	 *
	 * <ul~
	 * 	<li><tt>{@link kFLAG_CONNECT}</tt>: If set, the server will be connected if that is
	 * 		not yet the case.
	 * 	<li><tt>{@link kFLAG_ASSERT}</tt>: If the <tt>kFLAG_CONNECT</tt> flag is not set and
	 * 		the server is not connected, the method will raise a {@link \RuntimeException}.
	 * </ul>
	 *
	 * The second parameter represents a set of options to be provided to the native driver.
	 *
	 * This method will be used by derived classes to ensure a connection is open before
	 * performing ceretain operations, the reason for providing the flags parameter is to
	 * allow automatic connection, doing so in this class makes it easier.
	 *
	 * @param string				$theFlags			Flags bitfield.
	 * @param array					$theOptions			Connection native options.
	 * @return boolean				<tt>TRUE</tt> is connected.
	 * @throws \RuntimeException
	 *
	 * @uses Connect()
	 *
	 * @example
	 * // Will return TRUE if connected or FALSE.<br/>
	 * $connection = $server->isConnected();<br/>
	 * // Will return TRUE or raise an exception.<br/>
	 * $connection = $server->isConnected(self::kFLAG_ASSERT);
	 * // Will return TRUE and connect if not connected.<br/>
	 * $connection = $server->isConnected(self::kFLAG_CONNECT);
	 */
	public function isConnected( $theFlags = self::kFLAG_DEFAULT, $theOptions = NULL )
	{
		//
		// Check if connected.
		//
		if( ($this->mConnection !== NULL)
		 && ($this->mConnection !== TRUE) )
			return TRUE;															// ==>

		//
		// Connect.
		//
		if( $theFlags & self::kFLAG_CONNECT )
		{
			$this->Connect( $theOptions );

			return TRUE;															// ==>
		}

		//
		// Assert.
		//
		if( $theFlags & self::kFLAG_ASSERT )
			throw new \RuntimeException (
				"Server connection was not opened." );							// !@! ==>

		return FALSE;																// ==>

	} // isConnected.

	
	
/*=======================================================================================
 *																						*
 *								PROTECTED CONNECTION INTERFACE							*
 *																						*
 *======================================================================================*/
	
	
	
	/*===================================================================================
	 *	connectionCreate																*
	 *==================================================================================*/
	
	/**
	 * Open connection.
	 *
	 * This method should create the actual connection and return the native connection
	 * object; in this class the method is virtual, it is the responsibility of concrete
	 * classes to implement this method.
	 *
	 * This method assumes the caller has checked whether the connection was already open,
	 * it should not take care of closing previously opened connections.
	 *
	 * All the options required for the connection should have been provided via the data
	 * source connection query parameters. The provided parameter represents the default or
	 * additional set of options provided to the driver: if needed, in derived concrete
	 * classes you should define globally a set of options and subtitute a <tt>NULL</tt>
	 * value with them in this method, this will guarantee that special options will
	 * always be set.
	 *
	 * The provided parameter represents a set of default options for creating the
	 * connection
	 *
	 * If the operation fails, the method should raise an exception.
	 *
	 * @param array					$theOptions			Connection native options.
	 * @return mixed				The native connection.
	 */
	abstract protected function connectionCreate( $theOptions = NULL );
	
	
	/*===================================================================================
	 *	connectionDestruct																*
	 *==================================================================================*/
	
	/**
	 * Close connection.
	 *
	 * This method should close the open connection, in this class the method is virtual, it
	 * is the responsibility of concrete classes to implement this method.
	 *
	 * This method assumes the caller has checked whether a connection is open, it should
	 * assume the {@link $mConnection} attribute holds a valid native connection object.
	 *
	 * The provided parameter represents the default or additional set of options provided
	 * to the driver when closing the connection: if needed, in derived concrete classes you
	 * should define globally a set of options and subtitute a <tt>NULL</tt> value with them
	 * in this method, this will guarantee that special options will always be set.
	 *
	 * If the operation fails, the method should raise an exception.
	 *
	 * @param array					$theOptions			Connection native options.
	 */
	abstract protected function connectionDestruct( $theOptions = NULL );



/*=======================================================================================
 *																						*
 *								PROTECTED PROPERTY INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	isLockedProperty																*
	 *==================================================================================*/

	/**
	 * <h4>Check if provided property is locked.</h4>
	 *
	 * This method is called whenever an object property is being modified or deleted: it
	 * will check whether the property is among those which should not be modified while a
	 * connection is open, if that is the case the method will return <tt>TRUE</tt>, if not
	 * <tt>FALSE</tt>.
	 *
	 * @param string				$theOffset			Property offset.
	 * @return boolean				<tt>TRUE</tt> is open.
	 *
	 * @uses lockedProperties()
	 */
	protected function isLockedProperty( $theOffset )
	{
		return in_array( $theOffset, $this->lockedProperties() );					// ==>

	} // isLockedProperty.


	/*===================================================================================
	 *	lockedProperties																*
	 *==================================================================================*/

	/**
	 * <h4>Return locked properties.</h4>
	 *
	 * This method should return the list of locked properties, this list will be used to
	 * filter the properties that cannot be modified if the server is connected.
	 *
	 * By default we consider all properties of the parent object, this method should be
	 * overloaded by derived concrete instances to filter local properties.
	 *
	 * @return array				The list of property offsets to be locked.
	 *
	 * @see PROT, HOST, PORT, USER, PASS, PATH, FRAG, QUERY
	 *
	 * @example
	 * // In derived classes you could implement the method as follows:<br/>
	 * return array_merge( parent::lockedProperties(), [ your values ] );
	 */
	protected function lockedProperties()
	{
		return [ self::PROT, self::HOST, self::PORT, self::USER,
				 self::PASS, self::PATH, self::FRAG, self::QUERY];					// ==>

	} // lockedProperties.



} // class Server.


?>
