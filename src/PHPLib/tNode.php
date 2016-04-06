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
require_once('types.inc.php');

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
 * 		<li><tt>{@link kTYPE_NODE_GRAPH}</tt>: Graph nodes represent the entry point of a
 * 			graph, they can be considered as the root node of the ontology.
 * 		<li><tt>{@link kTYPE_NODE_ROOT}</tt>: Root nodes represent entry points to a graph,
 * 			they represent a set of alternative entry points that constitute different
 * 			thematic views of the graph.
 * 		<li><tt>{@link kTYPE_NODE_TYPE}</tt>: Type nodes define a structure or data type.
 * 		<li><tt>{@link kTYPE_NODE_CATEGORY}</tt>: Category nodes act as categorised
 * 			containers.
 * 		<li><tt>{@link kTYPE_NODE_ENUMERATION}</tt>: Controlled vocabulary element.
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
 *							PUBLIC MEMBER MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	NodeType																		*
	 *==================================================================================*/

	/**
	 * <h4>Manage node type.</h4>
	 *
	 * This method can be used to manage the node type ({@link kTAG_NODE_TYPE}), the method
	 * features one parameter which can take the following values:
	 *
	 * <ul>
	 *	<li><tt>NULL</tt>: Return the current type.
	 *	<li><em>other</em>: Set the type with the provided value, only the following values
	 * 		are accepted, or an exception will be raised:
	 * 	 <ul>
	 * 		<li><tt>{@link kTYPE_NODE_GRAPH}</tt>: Graph nodes represent the entry point of a
	 * 			graph, they can be considered as the root node of the ontology.
	 * 		<li><tt>{@link kTYPE_NODE_ROOT}</tt>: Root nodes represent entry points to a graph,
	 * 			they represent a set of alternative entry points that constitute different
	 * 			thematic views of the graph.
	 * 		<li><tt>{@link kTYPE_NODE_TYPE}</tt>: Type nodes define a structure or data type.
	 * 		<li><tt>{@link kTYPE_NODE_CATEGORY}</tt>: Category nodes act as categorised
	 * 			containers.
	 * 		<li><tt>{@link kTYPE_NODE_ENUMERATION}</tt>: Controlled vocabulary element.
	 * 	 </ul>
	 * </ul>
	 *
	 * The method will return the current value.
	 *
	 * @param string				$theValue			The node type.
	 * @return string				The current value.
	 */
	public function NodeType( $theValue = NULL )
	{
		//
		// Assert type.
		//
		if( $theValue !== NULL )
		{
			switch( $theValue )
			{
				
			}
		}

	} // NodeType.



} // trait tNode.


?>
