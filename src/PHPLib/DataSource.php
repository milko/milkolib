<?php

/**
 * DataSource.php
 *
 * This file contains the definition of the {@link milko\php\DataSource} class.
 */

namespace Milko\PHPLib;

use Milko\PHPLib\Container;

/*=======================================================================================
 *																						*
 *									DataSource.php										*
 *																						*
 *======================================================================================*/

/**
 * <h4>Data source or URL object.</h4>
 *
 * This class implements a data source, that is, an URL that contains the connection
 * properties needed by classes representing connection instances, such as servers and
 * databases.
 *
 * When instantiated, this class will parse the provided connection URL and store the
 * connection parameters in the object's properties, the supported offsets are:
 *
 * <ul>
 *	<li><tt>{@link PROT}</tt>: The protocol.
 *	<li><tt>{@link HOST}</tt>: The host.
 *	<li><tt>{@link PORT}</tt>: The port.
 *	<li><tt>{@link USER}</tt>: The user name.
 *	<li><tt>{@link PASS}</tt>: The user password.
 *	<li><tt>{@link PATH}</tt>: The path.
 *	<li><tt>{@link QUERY}</tt>: The query parameters.
 *	<li><tt>{@link FRAG}</tt>: The fragment.
 * </ul>
 *
 * There is also a set of member accessor methods that can be used to manage the data source
 * elements:
 *
 * <ul>
 *	<li><b>{@link Protocol()}</b>: Manages the protocol.
 *	<li><b>{@link Host()}</b>: Manages the host.
 *	<li><b>{@link Port()}</b>: Manages the port.
 *	<li><b>{@link User()}</b>: Manages the user name.
 *	<li><b>{@link Password()}</b>: Manages the user password.
 *	<li><b>{@link Path()}</b>: Manages the path.
 *	<li><b>{@link Query()}</b>: Manages the query.
 *	<li><b>{@link Fragment()}</b>: Manages the fragment.
 * </ul>
 *
 * Casting the object to a string will return its data source name.
 *
 * There is no value checking when setting properties, this is the responsibility of the
 * caller: to ensure the URL is valid instantiate a new object.
 *
 *	@package	Core
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		05/02/2016
 *
 *	@example	../../test/DataSource.php
 *	@example
 * $dsn = new Milko\PHPLib\Datasource( 'protocol://user:pass@host:9090/dir/file?arg=val#frag' );
 *	@example
 * $dsn = new Milko\PHPLib\Datasource( 'protocol://user:pass@host1:9090,host2,host3:8080/dir/file?arg=val#frag' );
 */
class DataSource extends Container
{
	/**
	 * Protocol.
	 *
	 * This offset constant refers to the data source scheme or protocol.
	 *
	 * @var string
	 */
	const PROT = 'scheme';

	/**
	 * Host.
	 *
	 * This offset constant refers to the data source host name.
	 *
	 * @var string
	 */
	const HOST = 'host';

	/**
	 * Port.
	 *
	 * This offset constant refers to the data source port.
	 *
	 * @var string
	 */
	const PORT = 'port';

	/**
	 * User.
	 *
	 * This offset constant refers to the data source user name.
	 *
	 * @var string
	 */
	const USER = 'user';

	/**
	 * Password.
	 *
	 * This offset constant refers to the data source user password.
	 *
	 * @var string
	 */
	const PASS = 'pass';

	/**
	 * Path.
	 *
	 * This offset constant refers to the data source path.
	 *
	 * @var string
	 */
	const PATH = 'path';

	/**
	 * Query.
	 *
	 * This offset constant refers to the data source query parameters.
	 *
	 * @var string
	 */
	const QUERY = 'query';

	/**
	 * Fragment.
	 *
	 * This offset constant refers to the parameter provided after the hashmark <tt>#</tt>..
	 *
	 * @var string
	 */
	const FRAG = 'fragment';



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
	 * The object must be instantiated from a data source name, if the provided connection
	 * string is invalid, the method will raise an exception.
	 *
	 * The method will raise an exception if the provided URL is not parsable, or if either
	 * the scheme or the host are missing.
	 *
	 * @param string			$theConnection		Data source name.
	 * @throws \InvalidArgumentException
	 *
	 * @uses Protocol()
	 * @uses Host()
	 * @uses Port()
	 * @uses User()
	 * @uses Password()
	 * @uses Query()
	 * @uses Fragment()
	 *
	 * @example
	 * $dsn = new DataSource( 'html://user:pass@host:8080/dir/file?arg=val#frag' );
	 */
	public function __construct( $theConnection )
	{
		//
		// Init local storage.
		//
		$exception_header = "Unable to instantiate data source";
		
		//
		// Handle connection string.
		//
		if( strlen( $theConnection = trim( $theConnection ) ) )
		{
			//
			// Check URL.
			//
			if( parse_url( $theConnection ) === FALSE )
				throw new \InvalidArgumentException(
					"$exception_header: invalid data source string." );			// !@! ==>

			//
			// Call parent constructor.
			//
			parent::__construct();

			//
			// Load components.
			//
			$this->Protocol( parse_url( $theConnection, PHP_URL_SCHEME ) );
			$this->Host( parse_url( $theConnection, PHP_URL_HOST ) );
			$this->Port( parse_url( $theConnection, PHP_URL_PORT ) );
			$this->User( parse_url( $theConnection, PHP_URL_USER ) );
			$this->Password( parse_url( $theConnection, PHP_URL_PASS ) );
			$this->Path( parse_url( $theConnection, PHP_URL_PATH ) );
			$this->Query( parse_url( $theConnection, PHP_URL_QUERY ) );
			$this->Fragment( parse_url( $theConnection, PHP_URL_FRAGMENT ) );

			//
			// Handle multiple hosts.
			//
			if( strpos( ($list = $this->Host()), ',' ) !== FALSE )
			{
				//
				// Split hosts.
				//
				$hosts = $ports = [];
				$parts = explode( ',', $list );
				foreach( $parts as $part )
				{
					$sub = explode( ':', $part );
					$hosts[] = trim( $sub[ 0 ] );
					$ports[] = ( count( $sub ) > 1 )
						? (int)$sub[ 1 ]
						: NULL;
				}

				//
				// Split last port.
				//
				if( ($tmp = $this->Port()) !== NULL )
					$ports[ count( $ports ) - 1 ] = (int)$tmp;

				//
				// Set hosts and ports.
				//
				$this->Host( $hosts );
				$this->Port( $ports );

			} // Has multiple hosts.

		} // Data source name is not empty.

		//
		// Handle empty connection string.
		//
		else
			throw new \InvalidArgumentException(
				"$exception_header: empty connection string." );				// !@! ==>

	} // Constructor.


	/*===================================================================================
	 *	__toString																		*
	 *==================================================================================*/

	/**
	 * <h4>Return data source name</h4>
	 *
	 * In this class we consider the data source name as the global identifier; here we
	 * return it as is, in derived classes you should be careful to shadow sensitive data.
	 *
	 * Note that this method cannot return the <tt>NULL</tt> value, which means that it
	 * cannot be used until there is a data source name for the object.
	 *
	 * @return string
	 *
	 * @uses toURL()
	 */
	public function __toString()								{	return $this->toURL();	}



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
	 * We overload this method to check the port value: it can either be an array or an
	 * integer, if that is not the case, an exception will be raised.
	 *
	 * @param string				$theOffset			Offset.
	 * @param mixed					$theValue			Value to set at offset.
	 * @throws \InvalidArgumentException
	 */
	public function offsetSet( $theOffset, $theValue )
	{
		//
		// Handle port.
		//
		if( $theOffset == self::PORT )
		{
			//
			// Assert numeric value.
			//
			if( ! is_array( $theValue ) )
			{
				//
				// Throw.
				//
				if( ! is_numeric( $theValue ) )
					throw new \InvalidArgumentException (
						"The data source port must be a numeric value." );		// !@! ==>

				//
				// Cast value.
				//
				$theValue = (int)$theValue;
			}

		} // Setting port.

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
	 * We overload this method to prevent deleting the host and protocol.
	 * We also delete the password when deleting the user.
	 *
	 * @param string				$theOffset			Offset.
	 * @throws \BadMethodCallException
	 */
	public function offsetUnset( $theOffset )
	{
		//
		// Prevent deleting protocol and host.
		//
		if( ($theOffset == self::HOST)
			|| ($theOffset == self::PROT) )
			throw new \BadMethodCallException(
				"The data source protocol and host are required." );			// !@! ==>

		//
		// Delete password along with user.
		//
		if( $theOffset == self::USER )
			$this->offsetUnset( self::PASS );

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
	 *	Protocol																		*
	 *==================================================================================*/

	/**
	 * <h4>Manage data source protocol.</h4>
	 *
	 * This method will manage the data source protocol, provide <tt>NULL</tt> to retrieve
	 * the current value, or any other type to set it. The protocol is required, so providing
	 * <tt>FALSE</tt> will result in an exception.
	 *
	 * The method will return the current value.
	 *
	 * @param mixed				$theValue			Value or operation.
	 * @return string
	 *
	 * @uses manageProperty()
	 *
	 * @see PROT
	 *
	 * @example
	 * $test = $dsn->Protocol( 'html' );	// Set protocol to <tt>html</tt>.<br/>
	 * $test = $dsn->Protocol(); // Retrieve current protocol.
	 */
	public function Protocol( $theValue = NULL )
	{
		return $this->manageProperty( self::PROT, $theValue );						// ==>

	} // Protocol.


	/*===================================================================================
	 *	Host																			*
	 *==================================================================================*/

	/**
	 * <h4>Manage data source host.</h4>
	 *
	 * This method will manage the data source host, provide <tt>NULL</tt> to retrieve the
	 * current value, or any other type to set it. The host is required, so providing
	 * <tt>FALSE</tt> will result in an exception.
	 *
	 * The host can be provided either as a string, for a single host, or as an array for
	 * multiple hosts.
	 *
	 * The method will return the current value.
	 *
	 * @param mixed				$theValue			Value or operation.
	 * @return string|array
	 *
	 * @uses manageProperty()
	 *
	 * @see HOST
	 *
	 * @example
	 * $test = $dsn->Host( 'example.net' );	// Set host.<br/>
	 * $test = $dsn->Host(); // Retrieve current host.
	 */
	public function Host( $theValue = NULL )
	{
		return $this->manageProperty( self::HOST, $theValue );						// ==>

	} // Host.


	/*===================================================================================
	 *	Port																			*
	 *==================================================================================*/

	/**
	 * <h4>Manage data source port.</h4>
	 *
	 * This method will manage the data source port, provide <tt>NULL</tt> to retrieve
	 * the current value, <tt>FALSE</tt> to delete the host, or any other type to set it.
	 *
	 * The host can be provided either as an integer, for a single port, or as an array for
	 * multiple ports; a single port must be provided as an integer value, or an exception
	 * will be thrown.
	 *
	 * The method will return the old value when deleting and the current value in all other
	 * cases.
	 *
	 * @param mixed				$theValue			Value or operation.
	 * @return int
	 *
	 * @uses manageProperty()
	 *
	 * @see PORT
	 *
	 * @example
	 * $test = $dsn->Port( 8080 );	// Set port.<br/>
	 * $test = $dsn->Port(); // Retrieve current port.
	 * $test = $dsn->Port( FALSE ); // Remove port, returns old value.
	 */
	public function Port( $theValue = NULL )
	{
		return $this->manageProperty( self::PORT, $theValue );						// ==>

	} // Port.


	/*===================================================================================
	 *	User																			*
	 *==================================================================================*/

	/**
	 * <h4>Manage data source user name.</h4>
	 *
	 * This method will manage the data source user name, provide <tt>NULL</tt> to retrieve
	 * the current value, <tt>FALSE</tt> to delete the user, or any other type to set it.
	 *
	 * If you delete the user, also the eventual password will be deleted.
	 *
	 * The method will return the old value when deleting and the current value in all other
	 * cases.
	 *
	 * @param mixed				$theValue			Value or operation.
	 * @return string
	 *
	 * @uses manageProperty()
	 *
	 * @see USER
	 *
	 * @example
	 * $test = $dsn->User( 'admin' );	// Set user name.<br/>
	 * $test = $dsn->User(); // Retrieve current user name.
	 * $test = $dsn->User( FALSE ); // Remove user and password, returns old user name.
	 */
	public function User( $theValue = NULL )
	{
		return $this->manageProperty( self::USER, $theValue );						// ==>

	} // User.


	/*===================================================================================
	 *	Password																		*
	 *==================================================================================*/

	/**
	 * <h4>Manage data source user password.</h4>
	 *
	 * This method will manage the data source user password, provide <tt>NULL</tt> to
	 * retrieve the current value, <tt>FALSE</tt> to delete the password, or any other type
	 * to set it.
	 *
	 * The method will return the old value when deleting and the current value in all other
	 * cases.
	 *
	 * @param mixed				$theValue			Value or operation.
	 * @return string
	 *
	 * @uses manageProperty()
	 *
	 * @see PASS
	 *
	 * @example
	 * $test = $dsn->Password( 'secret' );	// Set user password.<br/>
	 * $test = $dsn->Password(); // Retrieve current user password.
	 * $test = $dsn->Password( FALSE ); // Remove user password, returns old value.
	 */
	public function Password( $theValue = NULL )
	{
		return $this->manageProperty( self::PASS, $theValue );						// ==>

	} // Password.


	/*===================================================================================
	 *	Path																			*
	 *==================================================================================*/

	/**
	 * <h4>Manage data source path.</h4>
	 *
	 * This method will manage the data source path, provide <tt>NULL</tt> to retrieve the
	 * current value, <tt>FALSE</tt> to delete the path, or any other type to
	 * set it.
	 *
	 * This method features a second parameter that can be used to retrieve the path value
	 * as an array featuring the different elements of the path:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: The method will return the path string.
	 * 	<li><tt>TRUE</tt>: The method will split the path using the
	 * 		{@link DIRECTORY_SEPARATOR} token.
	 * 	<li><em>other</em>: The provided value will be converted to a string and the method
	 * 		will split the path using the provided token.
	 * </ul>
	 *
	 * The method will return the old value when deleting and the current value in all other
	 * cases.
	 *
	 * @param mixed				$theValue			Value or operation.
	 * @param boolean			$asArray			<tt>TRUE</tt> return as array.
	 * @return string
	 *
	 * @uses manageProperty()
	 *
	 * @see PATH
	 *
	 * @example
	 * $test = $dsn->Path( 'directory/file' );	// Set path.<br/>
	 * $test = $dsn->Path(); // Retrieve current path.
	 * $test = $dsn->Path( FALSE ); // Remove path, returns old value.
	 */
	public function Path( $theValue = NULL )
	{
		return $this->manageProperty( self::PATH, $theValue );						// ==>

	} // Path.


	/*===================================================================================
	 *	Query																			*
	 *==================================================================================*/

	/**
	 * <h4>Manage data source query.</h4>
	 *
	 * This method will manage the data source query, provide <tt>NULL</tt> to retrieve the
	 * current value, <tt>FALSE</tt> to delete the query, or an associative array to set it;
	 * if you provide any other type, this will be interpreted as a string and it will be
	 * parsed and converted to an array before setting it.
	 *
	 * The method will return the old value when deleting and the current value in all other
	 * cases.
	 *
	 * @param mixed				$theValue			Value or operation.
	 * @return array
	 * @throws \InvalidArgumentException
	 *
	 * @uses manageProperty()
	 *
	 * @see QUERY
	 *
	 * @example
	 * $test = $dsn->Query( [ 'arg' => 'val' ] ); // Set query by array.<br/>
	 * $test = $dsn->Query( 'arg=val' ); // Set query by string.<br/>
	 * $test = $dsn->Path(); // Retrieve current query.
	 * $test = $dsn->Query( FALSE ); // Remove query, returns old value.
	 */
	public function Query( $theValue = NULL )
	{
		//
		// Compile query.
		//
		if( ($theValue !== NULL)
		 && ($theValue !== FALSE)
		 && (! is_array( $theValue )) )
		{
			//
			// Split parameter groups.
			//
			$params = explode( '&', (string)$theValue );
			if( count( $params ) )
			{
				//
				// Init local storage.
				//
				$list = [];

				//
				// Iterate query elements.
				//
				foreach( $params as $param )
				{
					//
					// Parse query element.
					//
					$elements = explode( '=', $param );

					//
					// Check parameter name.
					//
					if( ! strlen( $name = trim( $elements[ 0 ] ) ) )
						throw new \InvalidArgumentException(
							"Invalid data source query." );					// !@! ==>

					//
					// Check parameter value.
					//
					$value = ( count( $elements ) > 1 )
						? $elements[ 1 ]
						: NULL;

					//
					// Set parameter.
					//
					$list[ $name ] = $value;

				} // Iterating query elements.

				//
				// Set converted value.
				//
				$theValue = ( count( $list ) )
					? $list
					: NULL;

			} // Has parameters.

		} // Provided a string.

		return $this->manageProperty( self::QUERY, $theValue );						// ==>

	} // Query.


	/*===================================================================================
	 *	Fragment																		*
	 *==================================================================================*/

	/**
	 * <h4>Manage data source fragment.</h4>
	 *
	 * This method will manage the data source fragment, provide <tt>NULL</tt> to retrieve
	 * the current value, <tt>FALSE</tt> to delete the fragment, or any other type to set it.
	 *
	 * The method will return the old value when deleting and the current value in all other
	 * cases.
	 *
	 * @param mixed				$theValue			Value or operation.
	 * @return string
	 *
	 * @uses manageProperty()
	 *
	 * @see FRAG
	 *
	 * @example
	 * $test = $dsn->Fragment( 'frag' ); // Set fragment.<br/>
	 * $test = $dsn->Fragment(); // Retrieve current fragment.
	 * $test = $dsn->Fragment( FALSE ); // Remove fragment, returns old value.
	 */
	public function Fragment( $theValue = NULL )
	{
		return $this->manageProperty( self::FRAG, $theValue );						// ==>

	} // Fragment.



/*=======================================================================================
 *																						*
 *								PROTECTED URI UTILITIES									*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	toURL																			*
	 *==================================================================================*/

	/**
	 * <h4>Return the data source URL.</h4>
	 *
	 * This method can be used to return an URL from the current data source, it accepts an
	 * array parameter containing the URL offsets <em>to be excluded</em>.
	 *
	 * @param array				$theExcluded		List of excluded offsets.
	 * @return string
	 *
	 * @uses manageProperty()
	 *
	 * @see PROT USER PASS HOST PORT PATH QUERY FRAG
	 *
	 * @example
	 * $test = $dsn->toURL();	// Return full URL.<br/>
	 * $test = $dsn->toURL( [ self::PATH ] );	// Return URL without path.
	 */
	protected function toURL( $theExcluded = [] )
	{
		//
		// Init local storage.
		//
		$dsn = '';

		//
		// Set protocol.
		//
		if( (! in_array( self::PROT, $theExcluded ))
			&& (($tmp = $this->offsetGet( self::PROT )) !== NULL) )
			$dsn .= ($tmp.'://');

		//
		// Handle credentials.
		//
		if( (! in_array( self::USER, $theExcluded ))
			&& (($tmp = $this->offsetGet( self::USER )) !== NULL) )
		{
			//
			// Set user.
			//
			$dsn .= $tmp;

			//
			// Set password.
			//
			if( (! in_array( self::PASS, $theExcluded ))
				&& (($tmp = $this->offsetGet( self::PASS )) !== NULL) )
				$dsn .= ":$tmp";

			//
			// Close credentials.
			//
			$dsn .= '@';

		} // Has user.

		//
		// Add host and port.
		//
		if( (! in_array( self::HOST, $theExcluded ))
			&& (($tmp = $this->offsetGet( self::HOST )) !== NULL) )
		{
			//
			// Init local storage.
			//
			$do_port = ! in_array( self::PORT, $theExcluded );

			//
			// Add hosts.
			//
			if( is_array( $tmp ) )
			{
				//
				// Add hosts and ports.
				//
				$list = [];
				$ports = $this->Port();
				foreach( $tmp as $key => $value )
				{
					if( $do_port )
						$list[] = ( $ports[ $key ] !== NULL )
							? ($tmp[ $key ] . ':' . $ports[ $key ])
							: $tmp[ $key ];
					else
						$list[] = $tmp[ $key ];
				}

				$dsn .= implode( ',', $list );
			}
			else
			{
				//
				// Add host.
				//
				$dsn .= $tmp;

				//
				// Add port.
				//
				if( $do_port
					&& (($tmp = $this->offsetGet( self::PORT )) !== NULL) )
					$dsn .= ":$tmp";
			}
		}

		//
		// Handle path.
		// We add a leading slash
		// if the parameter does not start with one.
		//
		if( (! in_array( self::PATH, $theExcluded ))
			&& (($tmp = $this->offsetGet( self::PATH )) !== NULL) )
		{
			if( ! (substr( $tmp, 0, 1 ) == '/') )
				$dsn .= '/';
			$dsn .= $tmp;
		}

		//
		// Set options.
		//
		if( (! in_array( self::QUERY, $theExcluded ))
			&& (($tmp = $this->offsetGet( self::QUERY )) !== NULL) )
		{
			//
			// Format query.
			//
			$query = [];
			foreach( $tmp as $key => $value )
				$query[] = ( $value !== NULL )
					? "$key=$value"
					: $key;

			//
			// Set query.
			//
			$dsn .= ('?'.implode( '&', $query ));
		}

		//
		// Set fragment.
		//
		if( (! in_array( self::FRAG, $theExcluded ))
			&& (($tmp = $this->offsetGet( self::FRAG )) !== NULL) )
			$dsn .= "#$tmp";

		return $dsn;																// ==>

	} // toURL.



} // class DataSource.


?>
