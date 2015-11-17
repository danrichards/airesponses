<?php

namespace Dan\AiResponses;

use Dan\AiCrawler\AiCrawler;
use InvalidArgumentException;

/**
 * Class AiResponder
 *
 * AiResponder handles what type of data you want back and provides a core set
 * of static methods for building more exotic responders (extensions).
 *
 * @see QueryResponder
 *
 * @package AiResponses
 */
class AiResponder {

    /**
     * @var AiCrawler $node
     */
    protected $node = null;

    /**
     * @var array $data
     */
    protected $data = [];

    /**
     * AiResponder constructor.
     *
     * @param AiCrawler $node
     */
    public function __construct(AiCrawler $node = null)
    {
        $this->node = $node;
    }

    /**
     * Return json_encoded response.
     *
     * Optional utf8_encode flag.
     */
    public function json()
    {

    }

    /**
     * Arrays (associative where possible).
     */
    public function arrays()
    {

    }

    /**
     * Objects (or array of objects)
     */
    public function objects()
    {

    }

    /**
     * Changed $data to from an associative array with item as key to an
     * associative array with data points as keys containing arrays of the
     * outcome across all the items.
     *
     * @param null $fill
     */
    public function rotate($fill = null)
    {

    }

    /**
     * Less than or equal to
     *
     * @param AiCrawler $node
     * @param $mixed
     * @param $value
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public static function lte(AiCrawler $node, $mixed, $value = 0)
    {
        if (is_string($mixed) && strpos($mixed, '.')) {
            list($item, $dataPoint) = explode('.', $mixed);
            return (int) $node->dataPoint($item, $dataPoint) <= $value;
        }

        if (method_exists($node, $mixed)) {
            switch(true) {
                case $mixed == 'children':
                case $mixed == 'siblings':
                case $mixed == 'parents':
                case $mixed == 'nextAll':
                case $mixed == 'previousAll':
                    return $node->$mixed()->count() <= $value;
                case $mixed == "first":
                    return $node->previousAll()->count() == 0;
                case $mixed == "last":
                    return $node->nextAll()->count() == 0;
                case $mixed == 'attr':
                    return (int) $node->attr("value") <= $value;
                default:
                    return (string) $node->$mixed() <= $value;
            }
        }

        if (is_callable($mixed)) {
            return $mixed($node) <= $value;
        }

        throw new InvalidArgumentException(
            "{$mixed} is not a valid `item.data_point` nor callable."
        );
    }

    /**
     * Less than
     *
     * @param AiCrawler $node
     * @param $mixed
     * @param $value
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public static function lt(AiCrawler $node, $mixed, $value)
    {
        // todo write unit tests for lte() then use that as boiler-plate for lt()
    }

    /**
     * Greater than or equal to
     *
     * @param AiCrawler $node
     * @param $mixed
     * @param $value
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public static function gte(AiCrawler $node, $mixed, $value)
    {
        // todo write unit tests for lte() then use that as boiler-plate for gte()
    }

    /**
     * Greater than
     *
     * @param AiCrawler $node
     * @param $mixed
     * @param $value
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public static function gt(AiCrawler $node, $mixed, $value)
    {
        // todo write unit tests for lte() then use that as boiler-plate for gt()
    }

}