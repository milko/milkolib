<?php

/**
 * iDatasource.php
 *
 * This file contains the definition of the {@link milko\php\iDatasource} interface.
 */

namespace Milko\PHPLib;

use Milko\PHPLib\Container;

/*=======================================================================================
 *																						*
 *									iDatasource.php										*
 *																						*
 *======================================================================================*/

/**
 * <h4>Data source interface.</h4><p />
 *
 * This interface declares the data source interface to which classes that implement servers
 * or other types of connections must adhere.
 *
 * This interface declares the following member accessor methods:
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
 *	@package	Core
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		05/02/2016
 */
interface iDatasource
{


/*=======================================================================================
 *																						*
 *							PUBLIC MEMBER ACCESSOR INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Protocol																		*
	 *==================================================================================*/

	/**
	 * <h4>Manage data source protocol.</h4><p />
	 *
	 * This method should manage the data source protocol, provide <tt>NULL</tt> to retrieve
	 * the current value, or any other type to set it. The protocol is required, so
	 * providing <tt>FALSE</tt> should result in an exception.
	 *
	 * The method should return the current value.
	 *
	 * @param mixed				$theValue			Value or operation.
	 * @return string
	 */
	public function Protocol( $theValue = NULL );


	/*===================================================================================
	 *	Host																			*
	 *==================================================================================*/

	/**
	 * <h4>Manage data source host.</h4><p />
	 *
	 * This method should manage the data source host, provide <tt>NULL</tt> to retrieve the
	 * current value, or any other type to set it. The host is required, so providing
	 * <tt>FALSE</tt> should result in an exception.
	 *
	 * The host can be provided either as a string, for a single host, or as an array for
	 * multiple hosts.
	 *
	 * The method should return the current value.
	 *
	 * @param mixed				$theValue			Value or operation.
	 * @return string|array
	 */
	public function Host( $theValue = NULL );


	/*===================================================================================
	 *	Port																			*
	 *==================================================================================*/

	/**
	 * <h4>Manage data source port.</h4><p />
	 *
	 * This method should manage the data source port, provide <tt>NULL</tt> to retrieve
	 * the current value, <tt>FALSE</tt> to delete the port, or any other value to set it.
	 *
	 * The host should be provided either as an integer, for a single port, or as an array
	 * of integers for multiple ports; a single port must be provided as an integer value,
	 * or an exception should be thrown.
	 *
	 * The method should return the old value when deleting and the current value in all
	 * other cases.
	 *
	 * @param mixed				$theValue			Value or operation.
	 * @return int
	 */
	public function Port( $theValue = NULL );


	/*===================================================================================
	 *	User																			*
	 *==================================================================================*/

	/**
	 * <h4>Manage data source user name.</h4><p />
	 *
	 * This method should manage the data source user name, provide <tt>NULL</tt> to
	 * retrieve the current value, <tt>FALSE</tt> to delete the user, or any other type to
	 * set it.
	 *
	 * If you delete the user, also the eventual password should be deleted.
	 *
	 * The method should return the old value when deleting and the current value in all
	 * other cases.
	 *
	 * @param mixed				$theValue			Value or operation.
	 * @return string
	 */
	public function User( $theValue = NULL );


	/*===================================================================================
	 *	Password																		*
	 *==================================================================================*/

	/**
	 * <h4>Manage data source user password.</h4><p />
	 *
	 * This method should manage the data source user password, provide <tt>NULL</tt> to
	 * retrieve the current value, <tt>FALSE</tt> to delete the password, or any other type
	 * to set it.
	 *
	 * The method should return the old value when deleting and the current value in all
	 * other cases.
	 *
	 * @param mixed				$theValue			Value or operation.
	 * @return string
	 */
	public function Password( $theValue = NULL );


	/*===================================================================================
	 *	Path																			*
	 *==================================================================================*/

	/**
	 * <h4>Manage data source path.</h4><p />
	 *
	 * This method should manage the data source path, provide <tt>NULL</tt> to retrieve the
	 * current value, <tt>FALSE</tt> to delete the path, or any other type to set it.
	 *
	 * The method should return the old value when deleting and the current value in all
	 * other cases.
	 *
	 * @param mixed				$theValue			Value or operation.
	 * @return string
	 */
	public function Path( $theValue = NULL );


	/*===================================================================================
	 *	Query																			*
	 *==================================================================================*/

	/**
	 * <h4>Manage data source query.</h4><p />
	 *
	 * This method should manage the data source query, provide <tt>NULL</tt> to retrieve
	 * the current value, <tt>FALSE</tt> to delete the query, or an associative array to set
	 * it; if you provide any other type, this will be interpreted as a string and it will
	 * be parsed and converted to an array before setting it.
	 *
	 * The method should return the old value when deleting and the current value in all
	 * other cases.
	 *
	 * @param mixed				$theValue			Value or operation.
	 * @return array
	 */
	public function Query( $theValue = NULL );


	/*===================================================================================
	 *	Fragment																		*
	 *==================================================================================*/

	/**
	 * <h4>Manage data source fragment.</h4><p />
	 *
	 * This method should manage the data source fragment, provide <tt>NULL</tt> to retrieve
	 * the current value, <tt>FALSE</tt> to delete the fragment, or any other type to set
	 * it.
	 *
	 * The method should return the old value when deleting and the current value in all
	 * other cases.
	 *
	 * @param mixed				$theValue			Value or operation.
	 * @return string
	 */
	public function Fragment( $theValue = NULL );



} // interface iDatasource.


?>
