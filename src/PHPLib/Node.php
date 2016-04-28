<?php

/**
 * Node.php
 *
 * This file contains the definition of the {@link Node} class.
 */

namespace Milko\PHPLib;

/*=======================================================================================
 *																						*
 *										Node.php										*
 *																						*
 *======================================================================================*/

use Milko\PHPLib\Document;

/**
 * <h4>Node object.</h4>
 *
 * This class implements an object that acts as a vertex in a directed graph and represents
 * a proxy to another object.
 *
 * Instances of this class are used to place objects in a graph using aliases or proxies:
 * this allows the same target object to be used in a graph without needing to duplicate it.
 * The main usage of nodes is to use the same object in different contexts.
 *
 * The class features the following properties:
 *
 * <ul>
 * 	<li><tt>{@link kTAG_NODE_REF}</tt>: This represents the node <em>alias target</em>, it
 * 		is the document handle of the object that the node represents.
 * 	<li><tt>{@link kTAG_NODE_KIND}</tt>: This represents the node <em>kind</em> or
 * 		<em>function</em>: it is an enumerated value that indicates what is the function of
 * 		the current node:
 * 	 <ul>
 * 		<li><tt>{@link kKIND_ROOT}</tt>: This indicates that the node is the root or an
 * 		entry point to a graph.
 * 		<li><tt>{@link kKIND_TYPE}</tt>: This indicates that the node represents a type
 * 			definition: it means that the node can be used as a type declaration.
 * 		<li><tt>{@link kKIND_CATEGORY}</tt>: This indicates that the node represents a
 * 			category or container: it means that the node is essentially a container for
 * 			an underlining structure, but that the node itself doesn't act as an element of
 * 			the structure.
 * 	 </ul>
 * </ul>
 *
 * Derived classes may derive this class to implement custom functionality.
 *
 *	@package	Core
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		28/04/2016
 */
class Node extends Document
{



/*=======================================================================================
 *																						*
 *							PROTECTED VALIDATION INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	lockedOffsets																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the list of locked offsets.</h4>
	 *
	 * We overload this method to add the following offsets:
	 *
	 * <ul>
	 * 	<li><tt>kTAG_NODE_REF</tt>: Node alias handle.
	 * </ul>
	 *
	 * @return array				List of locked offsets.
	 */
	protected function lockedOffsets()
	{
		return
			array_merge(
				parent::lockedOffsets(),
				[ kTAG_NODE_REF ] );												// ==>

	} // lockedOffsets.


	/*===================================================================================
	 *	requiredOffsets																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the list of required offsets.</h4>
	 *
	 * We overload this method to add the following offsets:
	 *
	 * <ul>
	 * 	<li><tt>kTAG_NODE_REF</tt>: Node alias handle.
	 * </ul>
	 *
	 * @return array				List of required offsets.
	 */
	protected function requiredOffsets()
	{
		return
			array_merge(
				parent::requiredOffsets(),
				[ kTAG_NODE_REF ] );												// ==>

	} // requiredOffsets.



} // class Node.


?>
