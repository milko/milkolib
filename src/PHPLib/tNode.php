<?php

/**
 * tNode.php
 *
 * This file contains the definition of the {@link tNode} trait.
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

/**
 * Global node type definitions.
 */
require_once( 'node_types.inc.php' );

use Milko\PHPLib\Container;

/*=======================================================================================
 *																						*
 *										tNode.php										*
 *																						*
 *======================================================================================*/

/**
 * <h4>Node trait.</h4>
 *
 * This trait declares and implements the interface required by node objects, it handles the
 * following properties:
 *
 * <ul>
 * 	<li><tt>{@link kTAG_NODE_TYPE}</tt>: The node type. Nodes must feature a type that will
 * 		be used when traversing graphs, there types are:
 * 	 <ul>
 * 		<li><tt>{@link kTYPE_NODE_GRAPH}</tt>: Graph nodes represent the entry point to a
 * 			graph, they can be considered as the root node of an ontology.
 * 		<li><tt>{@link kTYPE_NODE_ROOT}</tt>: Root nodes represent entry points to a graph,
 * 			they do not necessarily act as the root of the graph, but rather as a set of
 * 			alternative entry points that represent different thematic views of the graph.
 * 		<li><tt>{@link kTYPE_NODE_TYPE}</tt>: Type nodes are used as containers of a
 * 			structure and represent proxies to that structure. This category indicates that
 * 			the current node is a placeholder for the elements that it references and should
 * 			not be considered as a concrete element in the traversal.
 * 		<li><tt>{@link kTYPE_NODE_ENUM}</tt>: Nodes of this type represent controlled
 * 			vocabulary entry points, they contain the
 * 			structure and represent proxies to that structure. This category indicates that
 * 			the current node is a placeholder for the element that it references.
 * 	 </ul>
 * </ul>
 *
 *	@package	Core
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		06/04/2016
 */
trait tNode
{



/*=======================================================================================
 *																						*
 *							PUBLIC NODE TYPE MANAGEMENT INTERFACE						*
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



} // trait tNode.


?>
