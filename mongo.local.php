<?php

/*=======================================================================================
 *																						*
 *									mongo.local.php										*
 *																						*
 *======================================================================================*/

/**
 *	<h4>Local MongoDB definitions.</h4><p />
 *
 * This file contains local definitions used by MongoDB, modify the values to suit your
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
 *	MONGODB																				*
 * Modify this section to customise MongoDB defaults.									*
 *======================================================================================*/

/**
 * <h4>Default connection string.</h4><p />
 *
 * This definition should contain the default MongoDB connection URI.
 */
const kMONGO_OPTS_CLIENT_DEFAULT = 'mongodb://localhost:27017';

/*=======================================================================================
 *	MONGODB																				*
 * Default identifier, key, class,revision and vertex offsets.							*
 *======================================================================================*/

/**
 * <h4>Default key offset.</h4><p />
 *
 * This defines the default offset for document key; note that we use the same property as
 * the document identifier, since by default the identifier should be a read-only property.
 */
const kTAG_MONGO_KEY = '_id';

/**
 * <h4>Default class offset.</h4><p />
 *
 * This defines the default offset for document class.
 */
const kTAG_MONGO_CLASS = '_class';

/**
 * <h4>Default revision offset.</h4><p />
 *
 * This defines the default offset for document revision.
 */
const kTAG_MONGO_REVISION = '_rev';

/**
 * <h4>Default offsets list.</h4><p />
 *
 * This defines the offset that lists all property names in the object.
 */
const kTAG_MONGO_OFFSETS = '_tags';

/**
 * <h4>Default source relationship offset.</h4><p />
 *
 * This defines the default offset for source nodes in a graph edge.
 */
const kTAG_MONGO_REL_FROM = '_from';

/**
 * <h4>Default destination relationship offset.</h4><p />
 *
 * This defines the default offset for destination nodes in a graph edge.
 */
const kTAG_MONGO_REL_TO = '_to';

/*=======================================================================================
 *	MONGODB																				*
 * Default collection names.															*
 *======================================================================================*/

/**
 * <h4>Default term collection.</h4><p />
 *
 * This defines the default terms collection name.
 */
const kTAG_MONGO_TERMS = '_terms';

/**
 * <h4>Default types collection.</h4><p />
 *
 * This defines the default types collection name.
 */
const kTAG_MONGO_TYPES = '_types';

/**
 * <h4>Default descriptors collection.</h4><p />
 *
 * This defines the default descriptors collection name.
 */
const kTAG_MONGO_DESCRIPTORS = '_descriptors';

/**
 * <h4>Default resources collection.</h4><p />
 *
 * This defines the default resources collection name.
 */
const kTAG_MONGO_RESOURCES = '_resources';

/**
 * <h4>Default surveys collection.</h4><p />
 *
 * This defines the default surveys collection name.
 */
const kTAG_MONGO_SURVEYS = '_surveys';

/**
 * <h4>Default data collection.</h4><p />
 *
 * This defines the default data collection name.
 */
const kTAG_MONGO_DATA = '_data';


?>
