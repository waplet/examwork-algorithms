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
 * @return array
 */
function parseVertical($data, $delimiter = ' ')
{
    $n = array_shift($data);

    $result = [
        'vertices' => (int)$n,
        'edges' => [],
    ];

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
        'piglet' => $index == 1 ? $graph['vertices'] : ($graph['vertices'] - 1),
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
        $path = [$start];
    } else {
        $path = [];
    }

    foreach ($edges[$start] as $step) {
        $stack[] = ['start' => $step, 'path' => $path];
    }

    do {
        $item = array_pop($stack);

        if (!isset($edges[$item['start']])) {
            if ($item['start'] == $legalDestination) {
                if ($withEndpoints) {
                    $path = $item['path'];
                    $path[] = $item['start'];
                    $paths[] = $path;
                } else {
                    $paths[] = $item['path'];
                }
            }

            continue;

        } else {
            foreach ($edges[$item['start']] as $vertice) {
                if (in_array($item['start'], $item['path'])) {
                    // possible loop
                    continue;
                }

                $path = $item['path'];
                $path[] = $item['start'];
                $stack[] = ['start' => $vertice, 'path' => $path];

                continue;
            }
        }
    } while (!empty($stack));

    return $paths;
}

/**
 * Full path [s, a,b,c, .. t]
 * @param $path
 * @return array
 */
function pathToEdges($path)
{
    $edges = [];
    for ($i = 0; $i < count($path) - 1; $i++) {
        $edges[$path[$i] . ':' . $path[$i + 1]] = [
            'u' => $path[$i],
            'v' => $path[$i + 1],
            'key' => $path[$i] . ':' . $path[$i + 1],
        ];
    }

    return $edges;
}

/**
 * @param $edgedPaths
 * @return array ['u:v' => frequency]
 */
function getEdgeFrequencies($edgedPaths)
{
    $result = [];
    foreach ($edgedPaths as $path) {
        foreach ($path as $edge) {
            $key = $edge['key'];
            if (!isset($result[$key])) {
                $result[$key] = [
                    'key' => $key,
                    'frequency' => 0,
                    'u' => $edge['u'],
                    'v' => $edge['v'],
                ];
            }

            $result[$key]['frequency']++;
        }
    }

    // Sorts
    uasort($result, function ($a, $b) {
        return $a['frequency'] > $b['frequency'];
    });
    return $result;
}

/**
 * @param $edgeFrequencies
 * @param $edgedPaths
 * @return array
 */
function getHoneyableEdges($edgeFrequencies, $edgedPaths)
{
    $result = [];

    while (!empty($edgedPaths) && !empty($edgeFrequencies)) {
        $edge = array_pop($edgeFrequencies);
        $edgeKey = $edge['key'];

        foreach ($edgedPaths as $k => $edgedPath) {
            if (isset($edgedPath[$edgeKey])) {
                // Found where to put Honeypot
                unset($edgedPaths[$k]);
                $result[$edgeKey] = $edge;
            }
        }
    }

    return $result;
}

/**
 * @param $paths
 * @return array [['u:v']] multiple edges
 */
function getPathsToEdges($paths)
{
    $edgedPaths = [];
    foreach ($paths as $path) {
        $edgedPaths[] = pathToEdges($path);
    }

    return $edgedPaths;
}