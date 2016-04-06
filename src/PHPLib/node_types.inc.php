<?php

/*=======================================================================================
 *																						*
 *									node_types.inc.php									*
 *																						*
 *======================================================================================*/

/**
 *	<h4>Global node type definitions.</h4>
 *
 * This file contains default node type definitions used in this library, these represent
 * the different types a node object belongs to.
 *
 *	@package	Core
 *	@subpackage	Definitions
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		06/04/2016
 */

/*=======================================================================================
 *	ROOT TYPES																			*
 *======================================================================================*/

/**
 * Graph root.
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

/*=======================================================================================
 *	TYPE NODES																			*
 *======================================================================================*/

/**
 * Type.
 *
 * A node that represents a type, such nodes act as proxies to the structure they contain.
 */
const kTYPE_NODE_TYPE = ':type:node:type';

/**
 * Enumerated vocabulary.
 *
 * A type node that represents a controlled vocabulary where there can be exactly one choice
 * among a set of valid elements.
 */
const kTYPE_NODE_ENUM = ':type:node:enum';

/**
 * Enumerated set.
 *
 * A type node that represents a controlled vocabulary where there can be one or more
 * choices among a set of valid elements.
 */
const kTYPE_NODE_ENUM_SET = ':type:node:enum:set';


?>
