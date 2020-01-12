<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\SQL;


abstract class SqlOperator
{
    #region Arithmetic Operator
    /**
     * Addition - Adds values on either side of the operator
     */
    const plus = '+';

    /**
     * Subtraction - Subtracts right hand operand from left hand operand
     */
    const minus = '-';

    /**
     * Multiplication - Multiplies values on either side of the operator
     */
    const multiply = '*';


    /**
     * Division - Divides left hand operand by right hand operand
     */
    const divide = '/';

    /**
     * Modulus - Divides left hand operand by right hand operand and returns remainder
     */
    const modulus = '%';
    #endregion

    #region Comparison Operator
    /**
     * Checks two values are not identical
     */
    const isNot = SqlOperator::is . ' ' . SqlOperator::not;

    /**
     * Checks if the values of two operands are equal or not, if yes then condition becomes true.
     */
    const equal = '=';

    /**
     * Checks if the values of two operands are equal or not, if values are not equal then condition becomes true.
     */
    const notEqual = '!=';

    /**
     * Checks if the values of two operands are equal or not, if values are not equal then condition becomes true.
     */
    const notEqualFancy = '<>';

    /**
     * Checks if the value of left operand is less than the value of right operand, if yes then condition becomes true.
     */
    const lessThan = '<';

    /**
     * Checks if the value of left operand is not less than the value of right operand, if yes then condition becomes true.
     */
    const notLessThan = '!<';

    /**
     * Checks if the value of left operand is greater than the value of right operand, if yes then condition becomes true.
     */
    const greaterThan = '>';

    /**
     * Checks if the value of left operand is not greater than the value of right operand, if yes then condition becomes true.
     */
    const notGreaterThan = '!>';

    /**
     * Checks if the value of left operand is less than or equal to the value of right operand, if yes then condition becomes true.
     */
    const lessThanOrEqual = '<=';

    /**
     * Checks if the value of left operand is greater than or equal to the value of right operand, if yes then condition becomes true.
     */
    const greaterThanOrEqual = '>=';
    #endregion

    #region Logical Operator
    /**
     * ALL The ALL operator is used to compare a value to all values in another value set.
     */
    public const all = 'all';
    /**
     *  NOT The NOT operator reverses the meaning of the logical operator with which it is used. Eg: NOT EXISTS, NOT BETWEEN, NOT IN, etc. This is a negate operator.
     */
    public const not = 'not';
    /**
     *  OR The OR operator is used to combine multiple conditions in an SQL statement's WHERE clause.
     */
    public const or = 'or';
    /**
     * Null
     */
    public const null = 'null';
    /**
     *  IN The IN operator is used to compare a value to a list of literal values that have been specified.
     */
    public const in = 'in';
    /**
     *  AND The AND operator allows the existence of multiple conditions in an SQL statement's WHERE clause.
     */
    public const and = 'and';
    /**
     *  UNIQUE The UNIQUE operator searches every row of a specified table for uniqueness (no duplicates).
     */
    public const unique = 'unique';
    /**
     *  EXISTS The EXISTS operator is used to search for the presence of a row in a specified table that meets certain criteria.
     */
    public const exists = 'exists';
    /**
     *  IS NULL The NULL operator is used to compare a value with a NULL value.
     */
    public const is = 'is';
    /**
     *  ANY The ANY operator is used to compare a value to any applicable value in the list according to the condition.
     */
    public const any = 'any';
    /**
     *  BETWEEN The BETWEEN operator is used to search for values that are within a set of values, given the minimum value and the maximum value.
     */
    public const between = 'between';
    #endregion
}