<?php

/*=======================================================================================
 *																						*
 *									predicates.inc.php									*
 *																						*
 *======================================================================================*/

/**
 *	<h4>Global predicate definitions.</h4>
 *
 * This file contains default predicate definitions used in this library.
 *
 *	@package	Core
 *	@subpackage	Definitions
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		07/04/2016
 */

/*=======================================================================================
 *	AGGREGATION PREDICATE TYPES															*
 *======================================================================================*/

/**
 * <h4>Subclass of.</h4><p />
 *
 * This predicate indicates that the subject of the relationship is a subclass of the object
 * of the relationship, in other words, the subject is derived from the object.
 */
const kPREDICATE_SUBCLASS_OF = ':predicate:SUBCLASS-OF';

/**
 * <h4>Subrank of.</h4><p />
 *
 * This predicate indicates that the subject of the relationship belongs to the next lowest
 * rank than the object of the relationship.
 */
const kPREDICATE_SUBRANK_OF = ':predicate:SUBRANK-OF';

/**
 * <h4>Subset of.</h4><p />
 *
 * This predicate indicates that the subject of the relationship represents a subset of the
 * object of the relationship, in other words, the subject is contained by the object.
 */
const kPREDICATE_SUBSET_OF = ':predicate:SUBSET-OF';

/**
 * <h4>Part of.</h4><p />
 *
 * This predicate indicates that the subject of the relationship is a part or a component of
 * the object of the relationship.
 */
const kPREDICATE_PART_OF = ':predicate:PART-OF';

/**
 * <h4>Type of.</h4><p />
 *
 * This predicate indicates that the subject of the relationship represents the type of the
 * object of the relationship. This predicate can also be as a group and a proxy: it may
 * define a formal group by collecting all elements that relate to it, and it acts as a
 * proxy, because this relationship type implies that all the elements related to the group
 * will relate directly to the object of the current relationship.
 */
const kPREDICATE_TYPE_OF = ':predicate:TYPE-OF';

/**
 * <h4>Function of.</h4><p />
 *
 * This predicate indicates that the subject of the relationship represents a function or
 * trait group of the object of the relationship, in other words, the subject is a group of
 * functions that can be applied to the object.
 */
const kPREDICATE_FUNCTION_OF = ':predicate:FUNCTION-OF';

/**
 * <h4>Collection of.</h4><p />
 *
 * This predicate indicates that the subject of the relationship is a collection belonging
 * to the object of the relationship. This predicate is similar to the attribute of
 * predicate, except that in the latter case the subject is a scalar item of the object,
 * while, in this case, the subject is a template for the collection of elements that belong
 * to the object.
 */
const kPREDICATE_COLLECTION_OF = ':predicate:COLLECTION-OF';

/**
 * <h4>Attribute of.</h4><p />
 *
 * This predicate indicates that the subject of the relationship is an attribute of the
 * object of the relationship, this means that the subject of the relationship belongs to
 * the set of attributes of the object of the relationship.
 */
const kPREDICATE_ATTRIBUTE_OF = ':predicate:ATTRIBUTE-OF';

/**
 * <h4>Property of.</h4><p />
 *
 * This predicate indicates that the subject of the relationship is a property of the object
 * of the relationship, this means that the subject of the relationship is a feature.
 */
const kPREDICATE_PROPERTY_OF = ':predicate:PROPERTY-OF';

/**
 * <h4>Enumeration of.</h4><p />
 *
 * This predicate relates vertex elements of an enumerated set, it indicates that the
 * subject of the relationship is an enumerated set item instance. If the object of the
 * relationship is also an enumerated set item instance, this means that the subject is a
 * subset of the object.
 */
const kPREDICATE_ENUM_OF = ':predicate:ENUM-OF';

/**
 * <h4>Instance of.</h4><p />
 *
 * This predicate relates a type to its instance, it indicates that the object of the
 * relationship is an instance of the subject of the relationship.
 */
const kPREDICATE_INSTANCE_OF = ':predicate:INSTANCE-OF';

/*=======================================================================================
 *	PREFERENCE PREDICATE TYPES															*
 *======================================================================================*/

/**
 * <h4>Preferred.</h4><p />
 *
 * This predicate indicates that the object of the relationship is the preferred choice, in
 * other words, if possible, one should use the object of the relationship in place of the
 * subject. This predicate will be used in general by obsolete or deprecated items. The
 * scope of this predicate is similar to the {@link kPREDICATE_VALID} predicate, except that
 * in this case the use of the subject of the relationship is only deprecated, while in the
 * {@link kPREDICATE_VALID} predicate it is not valid.
 */
const kPREDICATE_PREFERRED = ':predicate:PREFERRED';

/**
 * <h4>Valid.</h4><p />
 *
 * This predicate indicates that the object of the relationship is the valid choice, in
 * other words, the subject of the relationship is obsolete or not valid, and one should use
 * the object of the relationship in its place. This predicate will be used in general to
 * store the obsolete or deprecated versions. The scope of this predicate is similar to the
 * {@link kPREDICATE_PREFERRED} predicate, except that in this case the use of the subject
 * of the relationship is invalid, while in the {@link kPREDICATE_PREFERRED} predicate it is
 * only deprecated.
 */
const kPREDICATE_VALID = ':predicate:VALID';

/**
 * <h4>Legacy.</h4><p />
 *
 * This predicate indicates that the object of the relationship is the former or legacy
 * version, in other words, the object of the relationship is obsolete or not in use. This
 * predicate will be used in general to record historical information. The scope of this
 * predicate is similar to the {@link kPREDICATE_PREFERRED} and {@link kPREDICATE_VALID}
 * predicates, except that in this case the legacy choice might not be invalid nor
 * deprecated: it only means that the object of the relationship was used in the past and
 * the subject of the relationship is currently used in its place.
 */
const kPREDICATE_LEGACY = ':predicate:LEGACY';

/*=======================================================================================
 *	REFERENCE PREDICATE TYPES															*
 *======================================================================================*/

/**
 * <h4>Cross-reference.</h4><p />
 *
 * This predicate indicates that the subject of the relationship is related to the object of
 * the relationship. This predicate does not represent any specific type of relationship,
 * other than what the edge object attributes may indicate. The scope of this predicate is
 * similar to the {@link kPREDICATE_XREF-EXACT} predicate, except that the latter indicates
 * that the object of the relationship can be used in place of the subject, while in this
 * predicate this is not necessarily true.
 */
const kPREDICATE_XREF = ':predicate:XREF';

/**
 * <h4>Exact cross-reference.</h4><p />
 *
 * This predicate indicates that the object of the relationship can be used in place of the
 * subject of the relationship. If the predicate is found in both directions, one could say
 * that the two vertices are identical, except for their formal representation. The scope of
 * this predicate is similar to the {@link kPREDICATE_XREF} predicate, except that the
 * latter only indicates that both vertices are related, this predicate indicates that they
 * are interchangeable.
 */
const kPREDICATE_XREF_EXACT = ':predicate:XREF-EXACT';


?>
