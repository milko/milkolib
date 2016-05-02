<?php

/**
 * iEdges.php
 *
 * This file contains the definition of the {@link iEdges} interface.
 */

namespace Milko\PHPLib;

/*=======================================================================================
 *																						*
 *										iEdges.php										*
 *																						*
 *======================================================================================*/

/**
 * <h4>Edge collection interface.</h4>
 *
 * This interface declares the methods that an edge collection should implement.
 *
 * Edge collections inherit all the functionality of the {@link Collection} class and add
 * an interface which is specific to collections of {@link Edge} instance.
 *
 * We declare this interface to add edge management to concrete derived {@link Collection}
 * classes.
 *
 * This interface declares the following methods:
 *
 * <ul>
 * 	<li><em>Default document properties:</em>
 *   <ul>
 * 		<li><b>{@link VertexIn()}</b>: Return the relationship source offset.
 * 		<li><b>{@link VertexOut()}</b>: Return the relationship destination offset.
 *   </ul>
 * 	<li><em>Relationships management:</em>
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
interface iEdges
{



/*=======================================================================================
 *																						*
 *							PUBLIC OFFSET DECLARATION INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	VertexSource																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the relationship source offset.</h4>
	 *
	 * This represents the default offset for storing document handles
	 * ({@link NewDocumentHandle()}) that represent the source node in a relationship.
	 *
	 * @return string				Relationship source offset.
	 */
	public function VertexSource();


	/*===================================================================================
	 *	VertexDestination																*
	 *==================================================================================*/

	/**
	 * <h4>Return the relationship destination offset.</h4>
	 *
	 * This represents the default offset for storing document handles
	 * ({@link NewDocumentHandle()}) that represent the destination node in a relationship.
	 *
	 * @return string				Relationship destination offset.
	 */
	public function VertexDestination();



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
	 *	<li><b>$theVertex</b>: The vertex document or handle:
	 * 	 <ul>
	 * 		<li><tt>{@link \ArrayObject}</tt>: It will be considered as a document.
	 * 		<li><em>other</em>: It will be considered as a handle.
	 * 	 </ul>
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
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_ARRAY}</tt>: Return an iterable set of
	 * 				arrays.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_CONTAINER}</tt>: Return an iterable set of
	 * 				{@link Container} instances.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_DOCUMENT}</tt>: Return an iterable set of
	 * 				{@link Document} instances.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_HANDLE}</tt>: Return an array of document
	 * 				handles.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_KEY}</tt>: Return an array of document
	 * 				keys.
	 * 		 </ul>
	 * 	 </ul>
	 * </ul>
	 *
	 * By default the method will return documents as {@link Document} derived instances
	 * and select both relationship directions.
	 *
	 * @param mixed					$theVertex			The vertex document or handle.
	 * @param array					$theOptions			Find options.
	 * @return array				The found documents.
	 */
	public function FindByVertex(
		$theVertex,
		array $theOptions = [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_DOCUMENT,
							  kTOKEN_OPT_DIRECTION => kTOKEN_OPT_DIRECTION_ANY ]
	);



} // interface iEdges.


?>
