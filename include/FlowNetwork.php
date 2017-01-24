<?php

require_once __DIR__ . "/Edge.php";

/**
 * https://en.wikipedia.org/wiki/Ford%E2%80%93Fulkerson_algorithm
 * Class FlowNetwork
 */
class FlowNetwork
{
    public $adjacentVertex = [];
    public $flow = [];

    /**
     * @param int $vertex
     */
    public function addVertex($vertex)
    {
        $this->adjacentVertex[$vertex] = [];
    }

    /**
     * @param int $vertex
     * @return mixed
     */
    public function getEdges($vertex)
    {
        return $this->adjacentVertex[$vertex];
    }

    /**
     * @param int $u from
     * @param int $v to
     * @param int $w capacity
     * @throws ErrorException
     */
    public function addEdge($u, $v, $w = 1)
    {
        if ($u == $v) {
            throw new \ErrorException("u == v");
        }

        $edge = new Edge($u, $v, $w);
        $redge = new Edge($v, $u, 0);

        $edge->redge = $redge;
        $redge->redge = $edge;

        $this->adjacentVertex[$u][$edge->getKey()] = $edge;
        $this->adjacentVertex[$v][$redge->getKey()] = $redge;
        $this->flow[$edge->getKey()] = 0;
        $this->flow[$redge->getKey()] = 0;
    }

    /**
     * @param int $source
     * @param int $sink
     * @param [] $path
     * @return array
     */
    public function findPath($source, $sink, $path)
    {
        if ($source == $sink) {
            return $path;
        }

        foreach ($this->getEdges($source) as $edge) {
            /** @var Edge $edge */
            $residual = $edge->capacity - $this->flow[$edge->getKey()];

            if ($residual > 0 && !isset($path[$edge->getKey()]) && !isset($path[$edge->redge->getKey()])) {
                $tPath = $path;
                $tPath[$edge->getKey()] = $edge;
                $result = $this->findPath($edge->sink, $sink, $tPath);

                if (!empty($result)) {
                    return $result;
                }
            }
        }

        return [];
    }

    /**
     * @param int $source
     * @param int $sink
     * @return number
     */
    public function maxFlow($source, $sink)
    {
        $path = $this->findPath($source, $sink, []);

        while (!empty($path)) {
            $residuals = array_map(function (Edge $edge) {
                return $edge->capacity - $this->flow[$edge->getKey()];
            }, $path);

            $flow = min($residuals);

            foreach ($path as $edge) {
                /** @var Edge $edge */
                $this->flow[$edge->getKey()] += $flow;
                $this->flow[$edge->redge->getKey()] -= $flow;
            }

            $path = $this->findPath($source, $sink, []);
        }

        return array_sum(array_map(function (Edge $edge) {
            return $this->flow[$edge->getKey()];
        }, $this->getEdges($source)));
    }
}