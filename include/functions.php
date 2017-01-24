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
 * @param array $edges [from => [to1, to2, to3]]
 * @param int $start
 * @param int $legalDestination marker to check if path is legit
 * @param bool $withEndpoints should we include pooh's and piglet's house?
 * @return array [[v1 => v1, v2 => v2, ...], ...] hash map
 */
function iterativePaths($edges, $start, $legalDestination, $withEndpoints = true)
{
    $stack = [];
    $paths = [];
    if ($withEndpoints) {
        $path = [$start => $start];
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
                    $path[$item['start']] = $item['start'];
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
                $path[$item['start']] = $item['start'];
                $stack[] = ['start' => $vertice, 'path' => $path];

                continue;
            }
        }
    } while (!empty($stack));

    return $paths;
}

/**
 * @param array $path Full path [s, a,b,c, .. t], hash map
 * @return array ['u' => int, 'v' => int, 'key' => 'u' . ':' . 'v']
 */
function pathToEdges($path)
{
    $edges = [];
    $from = false;
    foreach ($path as $to) {
        if ($from === false) {
            // 0 != false, in this case
            $from = $to;
            continue;
        }

        $edgeKey = $from . ':' . $to;
        $edges[$edgeKey] = [
            'u' => $from,
            'v' => $to,
            'key' => $edgeKey,
        ];
        $from = $to;
    }
    return $edges;
}

/**
 * @param $edgedPaths [['u' => int, 'v' => int, 'key' => 'u' . ':' . 'v']]
 * @return array ['u:v' => (int) frequency, 'key' => 'u' . ':' . 'v', 'u' => int, 'v' => int]
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

    // Sorts by frequency
    uasort($result, function ($a, $b) {
        return $a['frequency'] > $b['frequency'];
    });
    return $result;
}

/**
 * @param $edgedPaths [['u' => int, 'v' => int, 'key' => 'u' . ':' . 'v']]
 * @return array
 */
function getHoneyableEdges($edgedPaths)
{
    $result = [];

    while (!empty($edgedPaths)) {
        $edgeFrequencies = getEdgeFrequencies($edgedPaths);
        $edge = array_pop($edgeFrequencies);
        $edgeKey = $edge['key'];

        foreach ($edgedPaths as $k => $edgedPath) {
            if (isset($edgedPath[$edgeKey])) {
                // Found where to put Honeypot
                $result[$edgeKey] = $edge;
                unset($edgedPaths[$k]);
            }
        }
    }

    return $result;
}

/**
 * @param $paths [[v1 => v1, v2 => v2]]
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

function printOutput($honeyableEdges, $verbose = false)
{
    if ($verbose) {
        echo "How much honey puts should we leave? - " . count($honeyableEdges);
        echo "\nEdges: " . implode(" ", array_map(function ($edge) {
                return "\n" . $edge['u'] . " " . $edge['v'];
            }, $honeyableEdges));
    } else {
        echo count($honeyableEdges) . " " . implode(" ", array_map(function ($edge) {
                return $edge['u'] . " " . $edge['v'];
            }, $honeyableEdges));
    }
}

/**
 * Return graph
 * @param string $inputFile
 * @return array graph
 */
function getGraph($inputFile)
{
    $data = regexParser(file($inputFile));
    $graph = parseHorizontal($data);

    return $graph;
}

/**
 * Using regex to pre-format data to horizontal universal parser
 * @param $data
 * @return string
 */
function regexParser($data)
{
    $oneLineData = implode(" ", $data);
    preg_match_all('#(\d+)#', $oneLineData, $matches);
    $data = implode(" ", $matches[1]);

    return $data;
}