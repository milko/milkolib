<?php

/*=======================================================================================
 *																						*
 *									arango.local.php									*
 *																						*
 *======================================================================================*/

/**
 *	<h4>Local ArangoDb definitions.</h4>
 *
 * This file contains local definitions used by ArangoDB, modify the values to suit your
 * needs.
 *
 *	@package	Core
 *	@subpackage	Definitions
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		14/03/2016
 */

/*=======================================================================================
 *	ARANGODB																			*
 * Modify this section to customise ArangoDB defaults.									*
 *======================================================================================*/

/**
 * <h4>Default connection string.</h4>
 *
 * This definition should contain the default ArangoDB connection URI.
 */
const kARANGO_OPTS_CLIENT_DEFAULT = 'tcp://127.0.0.1:8529';

/**
 * <h4>Default authorisation type.</h4>
 *
 * This definition should contain the default ArangoDB authorisation type.
 */
const kARANGO_OPTS_AUTH_DEFAULT = 'Basic';

/**
 * <h4>Default connection persistence.</h4>
 *
 * This definition should contain the default ArangoDB connection persistence.
 */
const kARANGO_OPTS_PERSIST_DEFAULT = 'Keep-Alive';

/**
 * <h4>Default connection time-out.</h4>
 *
 * This definition should contain the default ArangoDB connection time-out in seconds.
 */
const kARANGO_OPTS_TIMEOUT_DEFAULT = 3;

/**
 * <h4>Default time-out reconnect.</h4>
 *
 * This definition should contain the default ArangoDB connection time-out reconnect.
 */
const kARANGO_OPTS_RECONNECT_DEFAULT = TRUE;

/**
 * <h4>Default collection create.</h4>
 *
 * This definition should contain the default ArangoDB collection create option.
 */
const kARANGO_OPTS_CREATE_DEFAULT = TRUE;

/*=======================================================================================
 *	ARANGODB																			*
 * Default identifier, key, class,revision and vertex offsets.							*
 *======================================================================================*/

/**
 * <h4>Default key offset.</h4>
 *
 * This defines the default offset for document key.
 */
const kTAG_ARANGO_KEY = '_key';

/**
 * <h4>Default class offset.</h4>
 *
 * This defines the default offset for document class.
 */
const kTAG_ARANGO_CLASS = '_class';

/**
 * <h4>Default revision offset.</h4>
 *
 * This defines the default offset for document revision.
 */
const kTAG_ARANGO_REVISION = '_rev';

/**
 * <h4>Default source relationship offset.</h4>
 *
 * This defines the default offset for source nodes in a graph edge.
 */
const kTAG_ARANGO_REL_FROM = '_from';

/**
 * <h4>Default destination relationship offset.</h4>
 *
 * This defines the default offset for destination nodes in a graph edge.
 */
const kTAG_ARANGO_REL_TO = '_to';

/*=======================================================================================
 *	ARANGODB																			*
 * Default collection names.															*
 *======================================================================================*/

/**
 * <h4>Default term collection.</h4>
 *
 * This defines the default terms collection name.
 */
const kTAG_ARANGO_TERMS = 'terms';


?>
