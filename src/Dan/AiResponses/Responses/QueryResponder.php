<?php

namespace Dan\AiResponses;

use Dan\AiCrawler\AiCrawler;

/**
 * Class QueryResponder
 *
 * If you need to query several items with the same $node, you can do so more
 * efficiently with start() and commit(). This will run all your queries with
 * a one complete bread-first pass.
 *
 * @see https://danrichards.gitbooks.io/aicrawler/content/AiCrawler/scorable.html
 * @see https://danrichards.gitbooks.io/aicrawler/content/AiResponses/index.html
 *
 * @package AiResponses
 */
class QueryResponder extends AiResponder
{

    /**
     * @var array DataPointQuery $queued
     */
    protected $queued = null;

    /**
     * @var DOMQuery $current
     */
    protected $current = null;

    /**
     * AiResponder constructor.
     *
     * @param AiCrawler $node
     */
    public function __construct(AiCrawler $node = null)
    {
        parent::__construct($node);
    }

    /**
     * Reset the AiResponder
     *
     * @param AiCrawler $node
     * @return $this
     */
    public function reset(AiCrawler $node = null)
    {
        $this->data = [];
        $this->node = $node;
        $this->queued = null;
        $this->current = null;
        return $this;
    }

    /**
     * Provide the crawler we'll be searching.
     *
     * @param AiCrawler $node
     */
    public function using(AiCrawler $node)
    {
        $this->node = $node;
    }

    /**
     * When get() is called, do not execute, wait for commit()
     */
    public function start()
    {
        $this->queued = [];
    }

    /**
     * Run all queued queries in one BFS iteration.
     *
     * @param bool $clearData
     * @param bool $clearQueued
     *
     * @return array
     */
    public function commit($clearData = true, $clearQueued = true)
    {
        foreach ($this->queued as $query) {
            $this->data[$query->getFrom()] = $query->execute();
        }

        if ($clearQueued) {
            $this->queued = null;
        }

        if ($clearData) {
            $copy = $this->data;
            $this->data = [];
            return $copy;
        } else {
            return $this->data;
        }
    }

    /**
     * Specify data to return.
     *
     * @param array $select
     *
     * @return $this
     */
    public function select(array $select = [])
    {
        $this->current = $this->current ?: new DataPointQuery();
        $this->current->setSelect($select);
        return $this;
    }

    /**
     * The scoreable item / context to look in.
     *
     * @param $item
     *
     * @return $this
     */
    public function from($item) {
        $this->current = $this->current ?: new DataPointQuery();
        $this->current->setFrom($item);
        return $this;
    }

    /**
     * Criteria for our node's data points.
     *
     * @param $mixed
     * @param $sign
     * @param $value
     *
     * @return $this
     */
    public function where($mixed, $sign = ">=", $value = 1)
    {
        $this->current = $this->current ?: new DataPointQuery();
        $this->current->addWhere((object) [
            'mixed' => $mixed,
            'sign' => $sign,
            'value' => $value
        ]);
        return $this;
    }

    /**
     * Order in which dataPoint should be listed.
     *
     * @param $dataPoint
     * @param string $direction
     *
     * @return $this
     */
    public function orderBy($dataPoint, $direction = 'ASC')
    {
        $this->current = $this->current ?: new DataPointQuery();
        $this->current->setOrderBy($dataPoint, $direction);
        return $this;
    }

    /**
     * Set the maximum results to return.
     *
     * @param $limit
     *
     * @return $this
     */
    public function limit($limit)
    {
        $this->current = $this->current ?: new DataPointQuery();
        $this->current->setLimit($limit);
        return $this;
    }

    /**
     * If start() was called, queue query. Otherwise execute.
     *
     * @param int $limit
     *
     * @return mixed
     */
    public function get($limit = null)
    {
        $this->current = $this->current ?: new DataPointQuery();

        /**
         * Optionally set the limit with get()
         */
        if (! is_null($limit)) {
            $this->current->setLimit($limit);
        }

        /**
         * from() was not specified, run the same query on every item.
         */
        if (is_null($this->current->getFrom())) {
            foreach ($this->node->items() as $item) {
                $copy = clone $this->current;
                $copy->setFrom($item);
                $this->queued[] = $copy;
            }
            return $this->commit();
        }

        /**
         * start() has been called, queue up and return $this
         */
        if (is_array($this->queued)) {
            $this->queued[] = $this->current;
            return $this;
        }

        /**
         * Regular get() without start(), don't clear data.
         */
        $this->queued = [$this->current];
        return $this->commit(false);
    }

    /**
     * Alias for get(1), depending on order by.
     *
     * @param $dataPoint
     *
     * @return mixed
     */
    public function first($dataPoint)
    {
        // todo: write first implementation, returns get()
    }

    /**
     * Alias for get(1), depending on order by.
     *
     * @param $dataPoint
     *
     * @return mixed
     */
    public function last($dataPoint)
    {
        // todo: write last implementation, returns get()
    }

    /**
     * Alias for get(1) using total($item)
     *
     * @return mixed
     */
    public function max()
    {
        // todo: write max implementation, returns get()
    }

    /**
     * Alias for get(1) using total($item)
     *
     * @return mixed
     */
    public function min()
    {
        // todo: write min implementation, returns get()
    }

}
