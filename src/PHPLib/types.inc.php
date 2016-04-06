<?php

/*=======================================================================================
 *																						*
 *									types.inc.php										*
 *																						*
 *======================================================================================*/

/**
 *	<h4>Global type definitions.</h4>
 *
 * This file contains default type definitions used in this library.
 *
 *	@package	Core
 *	@subpackage	Definitions
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		06/04/2016
 */

/*=======================================================================================
 *	PRIMITIVE TYPES																		*
 *======================================================================================*/

/**
 * String.
 *
 * String or text.
 */
const kTYPE_STRING = ':type:string';

/**
 * Integer.
 *
 * Integer number.
 */
const kTYPE_INT = ':type:int';

/**
 * Float.
 *
 * Floating point number.
 */
const kTYPE_FLOAT = ':type:float';

/*=======================================================================================
 *	CATEGORICAL TYPES																	*
 *======================================================================================*/

/**
 * Enumeration.
 *
 * Enumerated controlled vocabulary.
 */
const kTYPE_ENUM = ':type:enum';

/**
 * Enumerated controlled vocabulary set.
 *
 * Enumerated set.
 */
const kTYPE_ENUM_SET = ':type:enum-set';

/*=======================================================================================
 *	NODE TYPES																			*
 *======================================================================================*/

/**
 * Graph root node.
 *
 * The graph root node. This root node represents the graph entry point, there may only be
 * one node of this type in a graph.
 */
const kTYPE_NODE_GRAPH = ':type:node:graph';

/**
 * Root node.
 *
 * A graph root node. This type of node is an entry point to the graph that represents a
 * specific thematic view. There may be more than one node of this type in a graph.
 */
const kTYPE_NODE_ROOT = ':type:node:root';

/**
 * Type node.
 *
 * A node that represents a type, such nodes act as proxies to the structure they contain.
 */
const kTYPE_NODE_TYPE = ':type:node:type';

/**
 * Category node.
 *
 * A node that represents a category or container.
 */
const kTYPE_NODE_CATEGORY = ':type:node:cat';

/**
 * Enumeration.
 *
 * An element of an enumerated set.
 */
const kTYPE_NODE_ENUMERATION = ':type:node:enum';


?>
