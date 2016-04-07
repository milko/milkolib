<?php

/*=======================================================================================
 *																						*
 *									kinds.inc.php										*
 *																						*
 *======================================================================================*/

/**
 *	<h4>Global data kind definitions.</h4>
 *
 * This file contains default data kind definitions used in this library.
 *
 *	@package	Core
 *	@subpackage	Definitions
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		07/04/2016
 */

/*=======================================================================================
 *	DOMAIN TYPES																		*
 *======================================================================================*/

/**
 * <h3>Categorical.</h3><p />
 *
 * This kind indicates that the property can take on one or more of a limited, and usually
 * fixed, number of possible values. In general, properties which take their values from an
 * enumerated set of choices are of this kind.
 */
const kKIND_CATEGORICAL = ':kind:categorical';

/**
 * <h3>Quantitative.</h3><p />
 *
 * This kind indicates that the property is one whose type of information is based on
 * quantities or quantifiable data which is continuous. In general numerical values which
 * can be aggregated in ranges fall under this category.
 */
const kKIND_QUANTITATIVE = ':kind:quantitative';

/**
 * <h3>Discrete.</h3><p />
 *
 * This kind indicates that the property is one which may take an indefinite number of
 * values, which differentiates it from a categorical property, and whose values are not
 * continuous, which differentiates it from a quantitative property.
 */
const kKIND_DISCRETE = ':kind:discrete';

/*=======================================================================================
 *	USAGE TYPES																			*
 *======================================================================================*/

/**
 * <h3>Recommended.</h3><p />
 *
 * This kind indicates that the property is recommended, encouraged or important, but not
 * necessarily required or mandatory.
 */
const kKIND_RECOMMENDED = ':kind:recommended';

/**
 * <h3>Required.</h3><p />
 *
 * This kind indicates that the property is required or mandatory.
 */
const kKIND_REQUIRED = ':kind:required';

/**
 * <h3>Private display.</h3><p />
 *
 * This kind indicates that the data property should not be displayed to clients.
 */
const kKIND_PRIVATE_DISPLAY = ':kind:private:display';

/**
 * <h3>Private search.</h3><p />
 *
 * This kind indicates that the data property should not be available to clients for
 * searching.
 */
const kKIND_PRIVATE_SEARCH = ':kind:private:search';

/**
 * <h3>Private modify.</h3><p />
 *
 * This kind indicates that the data property is reserved by the object, which means that it
 * is automatically managed by the class and should not be explicitly set or modified by
 * clients.
 */
const kKIND_PRIVATE_MODIFY = ':kind:private:modify';

/*=======================================================================================
 *	CARDINALITY TYPES																	*
 *======================================================================================*/

/**
 * <h3>List.</h3><p />
 *
 * This kind indicates that the property is a list of values, each of the defined data type.
 */
const kKIND_LIST = ':kind:list';

/**
 * <h3>Summary.</h3><p />
 *
 * This kind indicates that the property can be used to group results in a summary.
 */
const kKIND_SUMMARY = ':kind:summary';

/**
 * <h3>Lookup.</h3><p />
 *
 * This kind indicates that the property can be searched upon using auto-complete.
 */
const kKIND_LOOKUP = ':kind:lookup';


?>
