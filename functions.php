<?php


/**
 * @param string $data n a1 b1 a2 b2 ... an bn
 * @param string $delimiter
 * @return array
 */
function parseHorizontal($data, $delimiter = ' ')
{
    $numbers = explode($delimiter, $data);

    $n = array_shift($numbers);

    $result = [
        'vertices' => (int)$n,
        'edges' => [],
    ];

    for ($i = 0; $i < count($numbers) / 2; $i++) {
        $edge = [$numbers[$i * 2], $numbers[$i * 2 + 1]];

        if (!array_key_exists($edge[0], $result['edges'])) {
            $result['edges'][(int)$edge[0]] = [];
        }

        $result['edges'][(int)$edge[0]][] = (int)$edge[1];
    }

    return $result;
}

/**
 * @param array $data array of lines
 * @param string $delimiter
 * @return array ['vertices' => int, 'edges' => []]
 */
function parseVertical($data, $delimiter = ' ')
{
    $n = array_shift($data);

    $result = [
        'vertices' => (int)$n,
        'edges' => [],
    ];

    /**
     * $edge[0] from
     * $edge[1] to
     */
    foreach ($data as $value) {
        $edge = explode($delimiter, $value);

        if (!array_key_exists($edge[0], $result['edges'])) {
            $result['edges'][(int)$edge[0]] = [];
        }

        $result['edges'][(int)$edge[0]][] = (int)$edge[1];
    }

    return $result;
}

/**
 * Returns pooh and piglet house ids
 * @param $graph
 * @param int $index
 * @return array
 */
function getHouses($graph, $index = 1)
{
    return [
        'pooh' => $index == 1 ? 1 : 0,
        'piglet' => $index == 1 ? $graph['vertices'] : ($graph['vertices'] - 1)
    ];
}

/**
 * @param $edges
 * @param $start
 * @param $legalDestination
 * @return array
 * @deprecated lets use iterative version
 */
function calculatePaths($edges, $start, $legalDestination)
{
    $result = [];
    calcRecursivePaths($result, $edges, [], $start, $legalDestination);
    return $result;
}

function calcRecursivePaths(&$result, $edges, $currentPath, $lastEdge, $legalDestination)
{
    if (!isset($edges[$lastEdge])) {
        if ($lastEdge == $legalDestination) {
            $result[] = array_merge($currentPath, [$lastEdge]);
        }
        return;
    }

    if (in_array($lastEdge, $currentPath)) {
        // loop
        $result[] = $currentPath;
        return;
    }

    foreach ($edges[$lastEdge] as $edge) {
        calcRecursivePaths($result, $edges, array_merge($currentPath, [$lastEdge]), $edge, $legalDestination);
    }

    return;
}

function iterativePaths($edges, $start, $legalDestination, $withEndpoints = true)
{
    $stack = [];
    $paths = [];
    if ($withEndpoints) {
        $path = [$start => true];
    } else {
        $path = [];
    }

    foreach ($edges[$start] as $step) {
        $stack[] = ['start' => $step, 'path' => $path];
    }

    do
    {
        $item = array_pop($stack);

        if (!isset($edges[$item['start']])) {
            if ($item['start'] == $legalDestination) {
                if ($withEndpoints) {
                    $path = $item['path'];
                    $path[$item['start']] = true;
                    $paths[] = $path;
                } else {
                    $paths[] = $item['path'];
                }
            }

            continue;

        } else {
            foreach ($edges[$item['start']] as $vertice) {
                if (isset($item['path'][$item['start']])) {
                    // possible loop
                    continue;
                }

                $path = $item['path'];
                $path[$item['start']] = true;
                $stack[] = ['start' => $vertice, 'path' => $path];

                continue;
            }
        }
    }
    while (!empty($stack));

    return $paths;
}

/**
 * Ford-Fulkerson algorithm for finding max flow in graph
 * @param $paths
 * @return array
 */
function maxFlow($paths)
{
    // All edges has capacity of 1
    $capacity = 1;
    $flow = [];

    foreach ($paths as $path) {
        $edges = pathToEdges($path);
        $residuals = array_map(function ($e) use ($flow, $capacity) {
            $edge = $e['u'] . ':' . $e['v'];
            return $capacity - (isset($flow[$edge]) ? $flow[$edge] : 0);
        }, $edges);

        $localFlow = min($residuals);
        foreach ($edges as $e) {
            $edge = $e['u'] . ':' . $e['v'];
            if (!isset($flow[$edge])) {
                $flow[$edge] = 0;
            }
            $flow[$edge] += $localFlow;

            // $rEdge = $e['v'] . ':' . $e['u'];
            // if (!isset($flow[$rEdge])) {
            //     $flow[$rEdge] = 0;
            // }
            // $flow[$rEdge] += $localFlow;
        }
    }

    return $flow;
}

/**
 * Full path [s, a,b,c, .. t], hash map
 * @param $path
 * @return array ['u' => int, 'v' => int]
 */
function pathToEdges($path)
{
    $edges = [];
    $i = 0;
    $edge = [];
    foreach ($path as $e => $_) {
        if ($i > 0) {
            $edge['v'] = $e;
            $edges[] = $edge;
            $edge = ['u' => $e];
        } else {
            $edge['u'] = $e;
        }
        $i++;
    }

    return $edges;
}