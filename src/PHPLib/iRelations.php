<?php

/**
 * iRelations.php
 *
 * This file contains the definition of the {@link iRelations} interface.
 */

namespace Milko\PHPLib;

/**
 * Global tag definitions.
 */
require_once( 'tags.inc.php' );

/**
 * Global token definitions.
 */
require_once( 'tokens.inc.php' );

use Milko\PHPLib\Container;

/*=======================================================================================
 *																						*
 *									iRelations.php										*
 *																						*
 *======================================================================================*/

/**
 * <h4>Edge collection interface.</h4>
 *
 * This interface declares the methods that an edge collection should implement.
 * 
 * Edge collections inherit all the functionality of the {@link Collection} class and add
 * an interface which is specific to collections of {@link Edge} instances; rather than
 * deriving from the {@link Collection} class, we chhoose here to declare an interface that
 * edge collections derived from concrete collection classes should implement.
 * 
 * This interface declares the following methods:
 *
 * <ul>
 * 	<li><em>Default document properties:</em>
 *   <ul>
 * 		<li><b>{@link VertexIn()}</b>: Return the relationship source offset.
 * 		<li><b>{@link VertexOut()}</b>: Return the relationship destination offset.
 *   </ul>
 * 	<li><em>Record related:</em>
 *   <ul>
 * 		<li><b>{@link FindByVertex()}</b>: Find edges connected to a vertex.
 *   </ul>
 * </ul>
 *
 *	@package	Core
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		30/03/2016
 */
interface iRelations
{



/*=======================================================================================
 *																						*
 *							PUBLIC OFFSET DECLARATION INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	VertexIn																		*
	 *==================================================================================*/

	/**
	 * <h4>Return the relationship source offset.</h4>
	 *
	 * This represents the default offset for storing document handles
	 * ({@link NewDocumentHandle()}) that represent the source node in a relationship.
	 *
	 * @return string				Relationship source offset.
	 */
	public function VertexIn();


	/*===================================================================================
	 *	VertexOut																		*
	 *==================================================================================*/

	/**
	 * <h4>Return the relationship destination offset.</h4>
	 *
	 * This represents the default offset for storing document handles
	 * ({@link NewDocumentHandle()}) that represent the destination node in a relationship.
	 *
	 * @return string				Relationship destination offset.
	 */
	public function VertexOut();



/*=======================================================================================
 *																						*
 *							PUBLIC SELECTION MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	FindByVertex																	*
	 *==================================================================================*/

	/**
	 * <h4>Find by vertex.</h4>
	 *
	 * This method will return all edges that are connected to the provided vertex, the
	 * method features two parameters:
	 *
	 * <ul>
	 *	<li><b>$theVertex</b>: The vertex {@link Document} or handle.
	 *	<li><b>$theOptions</b>: An array of options:
	 * 	 <ul>
	 * 		<li><b>{@link kTOKEN_OPT_DIRECTION}</b>: This option determines the relationship
	 * 			direction:
	 * 		 <ul>
	 *			<li><tt>{@link kTOKEN_OPT_DIRECTION_IN}</tt>: Incoming relationships.
	 *			<li><tt>{@link kTOKEN_OPT_DIRECTION_OUT}</tt>: Outgoing relationships.
	 *			<li><tt>{@link kTOKEN_OPT_DIRECTION_ANY}</tt>: Incoming and outgoing
	 * 				relationships.
	 * 		 </ul>
	 * 		<li><b>{@link kTOKEN_OPT_FORMAT}</b>: This option determines the result format:
	 * 		 <ul>
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_NATIVE}</tt>: Return the unchanged driver
	 * 				database result.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_STANDARD}</tt>: Return {@link Document}
	 * 				instances.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_HANDLE}</tt>: Return
	 * 				(@link NewDocumentHandle()}) instances.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_KEY}</tt>: Return document key(s).
	 * 		 </ul>
	 * 	 </ul>
	 * </ul>
	 *
	 * If the provided {@link kTOKEN_OPT_MANY} option is <tt>FALSE</tt>, the method will
	 * return a scalar result (except if the {@link kTOKEN_OPT_FORMAT} is
	 * {@link kTOKEN_OPT_FORMAT_NATIVE}), if not, it will return an array of results.
	 *
	 * By default the method will return documents as {@link Document} derived instances
	 * and select both relationship directions.
	 *
	 * It is the responsibility of the caller to ensure the server is connected.
	 *
	 * @param mixed					$theVertex			The vertex document or handle.
	 * @param array					$theOptions			Find options.
	 * @return array				The found documents.
	 */
	public function FindByVertex( $theVertex, $theOptions = NULL );



} // interface iRelations.


?>
