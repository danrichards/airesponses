<?php

namespace Dan\AiResponses;

use Closure;
use Dan\AiCrawler\AiCrawler;
use InvalidArgumentException;

/**
 * Class DataPointQuery
 *
 * DataPointQuery is a SQL-like query implementation for a AiCrawler object.
 * Each instance of DataPointQuery is with respect to a single Scoreable item
 * on a single node and that item's data point(s) with the potential to return
 * a single record.
 *
 * DataPointQuery works with QueryResponder to return sets of records.
 *
 * @package Dan\AiResponses
 */
class DataPointQuery
{

    /**
     * Symfony DOMCrawler methods are like columns using select()
     *
     * @var array $select
     */
    public $select;

    /**
     * Scoreable item keys are like tables using from()
     *
     * @var string $from
     */
    public $from;

    /**
     * Scoreable data points are like columns using where()
     *
     * @var array $where
     */
    public $where;

    /**
     * Data points may be sorted using orderBy()
     *
     * @var array $orderBy
     */
    public $orderBy;

    /**
     * @var null $sorting
     */
    public $sorting;

    /**
     * @var array $limit
     */
    public $limit;

    /**
     * DataPointQuery constructor.
     */
    public function __construct()
    {
        $this->select = [];
        $this->from = null;
        $this->where = [];
        $this->orderBy = null;
        $this->sorting = 'ASC';
        $this->limit = null;
    }

    /**
     * Example into execute will handle:
     *
     * $this->select = [
     *     'nodeName', 'text', 'html', 'attributes' => ['id', 'name']',
     *     'children', 'siblings', 'parent', 'previousAll', 'nextAll'
     * ];
     * $this->from = 'item';
     * $this->addWhere = [         // Multiple items uses AND logic!
     *     (object) [
     *         'mixed' => 'data_point',
     *         'operator' => '>=',
     *         'value' => '1'
     *     ],
     *     Closure                 // Use a single Closure for something special.
     * ];
     * ORDER BY ~ handled by QueryResponder
     * LIMIT ~ handled by QueryResponder
     *
     * The purpose of this method is to addWhere() the data, from the item()
     * and then select() the criteria requested.
     *
     * @param AiCrawler $node
     */
    public function execute(AiCrawler $node)
    {
        $accept = true;
        /**
         * Run AND logic, each where criteria must assert true!
         */
        foreach ($this->where as $criteria) {
            if ($criteria instanceof Closure) {
                if (! $accept = $criteria($node)) {
                    break;
                }
                continue;
            }
            if ($this->hasValidCriteria($criteria)) {
                if (! $accept = $this->assertCriteria($node, $criteria)) {
                    break;
                }
                continue;
            }
            throw new InvalidArgumentException("addWhere() criteria may be Closure or array with `mixed`, `operator`, and `value` keys.");
        }

        if ($accept) {
            /**
             * Run through everything our select wants.
             */
            $this->select = (array) $this->select;
            foreach ($this->select as $key => $args) {
                $key = is_numeric($key) ? $args : $key;
                $record[$key] = $this->selectFromNode($key, $args);
            }
        }
    }

    /**
     * Verify if the node passes the required where criteria.
     *
     * @param AiCrawler $node
     * @param array|Closure $criteria
     *
     * @return bool
     */
    private function assertCriteria($node, $criteria) {
        $criteria = (array) $criteria;
        if ($this->hasValidCriteria($criteria)) {
            switch ($criteria['operator']) {
                case ">=":
                case "=>":
                case "gte":
                    return AiResponder::gte($node, $criteria->mixed, $criteria->value);
                    break;
                case "<=":
                case "=<":
                case "lte":
                    return AiResponder::lte($node, $criteria->mixed, $criteria->value);
                    break;
                case "<":
                case "lt":
                    return AiResponder::lt($node, $criteria->mixed, $criteria->value);
                    break;
                case ">":
                case "gt":
                    return AiResponder::gt($node, $criteria->mixed, $criteria->value);
                    break;
            }
        }
    }

    /**
     * Conveniences for retrieving info from the object dynamically.
     *
     * @param AiCrawler $node
     * @param $select
     * @param array|Closure $args
     *
     * @return mixed
     */
    public function selectFromNode(AiCrawler $node, $select, $args)
    {
        /**
         * nodeName, text, html...methods on $node would be most common.
         */
        if (method_exists($node, $select)) {
            if (in_array($select, static::$countable)) {
                return $node->$select()->count();
            }
            return empty($args)
                ? call_user_func([$node, $select])
                : call_user_func_array([$node, $select], $args);
        }

        /**
         * Something really special.
         */
        if ($args instanceof Closure) {
            return $args($node);
        }

        /**
         * Check the Scoreable Trait for something.
         */
        if ($select == $args && $this->hasDataPoint($this->getFrom(), $select)) {
            return $node->dataPoint($this->getFrom(), $args);
        } else {
            return $node->item($select);
        }

        /**
         * Check the Extra Trait for something.
         */
        if ($node->hasExtra($select)) {
            return $node->getExtra($select);
        }

        return null;
    }

    /**
     * Verify if the where criteria is valid.
     *
     * @param $criteria
     * @return bool
     */
    private function hasValidCriteria($criteria)
    {
        $criteria = (array) $criteria;
        return count(
            array_intersect_key(
                $criteria, ['mixed' => 1, 'operator' => 1, 'value' => 1]
            )
        ) == 3;
    }

    /**
     * @return mixed
     */
    public function getSelect()
    {
        return $this->select;
    }

    /**
     * @param mixed $select
     *
     * @return $this
     */
    public function setSelect($select)
    {
        $this->select = $select;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param mixed $from
     *
     * @return $this
     */
    public function setFrom($from)
    {
        $this->from = $from;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getWhere()
    {
        return $this->where;
    }

    /**
     * @param mixed $where
     *
     * @return $this
     */
    public function addWhere($where)
    {
        $this->where[] = $where;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * @param mixed $orderBy
     * @param string $sorting
     *
     * @return $this
     */
    public function setOrderBy($orderBy, $sorting = 'ASC')
    {
        $this->orderBy = $orderBy;
        $this->sorting = $sorting;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param mixed $limit
     *
     * @return $this
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

}