<?php

namespace Dan\AiResponses;

use Dan\AiCrawler\AiCrawler;

/**
 * Class DataPointQuery
 *
 * DataPointQuery is a SQL-like query implementation for a AiCrawler object.
 * Each instance of DataPointQuery is with respect to a single Scoreable item
 * on the node and that item's data point(s).
 *
 * If you have multiple queries to run on the same node, for the sake of
 * efficiency, you may queue up DataPointQuery(s) with the QueryResponder
 * class.
 *
 * @package Dan\AiResponses
 */
class DataPointQuery
{

    /**
     * Symfony DOMCrawler methods are like columns using select()
     *
     * @var array
     */
    public $select;

    /**
     * Scoreable item keys are like tables using from()
     *
     * @var array
     */
    public $from;

    /**
     * Scoreable data points are like columns using where()
     *
     * @var array
     */
    public $where;

    /**
     * Data points may be sorted using orderBy()
     *
     * @var array
     */
    public $orderBy;

    /**
     * @var null
     */
    public $sorting;

    /**
     * @var array
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
     * @param AiCrawler $node
     */
    public function execute(AiCrawler $node)
    {
        // todo: write the execute() method.
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