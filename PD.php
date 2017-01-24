<?php

require_once __DIR__ . "/functions.php";
require_once __DIR__ . "/FlowNetwork.php";


/**
 * Example No. 1
 */
$inputFile = 'example.txt';
$data = file($inputFile); // Read
$graph = parseVertical($data); // Parse
$houses = getHouses($graph); // Get house IDs

/**
 * Example No. 2
 */
$inputFile = 'input1.txt';
$data = file($inputFile);
$graph = parseHorizontal($data[0]);
$houses = getHouses($graph, 0);


/**
 * Simple algorithm
 */
$paths = iterativePaths($graph['edges'], $houses['pooh'], $houses['piglet']);
$edgedPaths = getPathsToEdges($paths);
$edgeFrequencies = getEdgeFrequencies($edgedPaths);
$honeyableEdges = getHoneyableEdges($edgeFrequencies, $edgedPaths);

echo "On how many edges to put the:\t " . count($honeyableEdges);
echo "\nResult: " . implode(" ", array_map(function ($edge) {
    return $edge['u'] . " " . $edge['v'];
},$honeyableEdges));

/**
 * With Ford-Fulkerson algorithm
 */
$flowNetwork = new FlowNetwork();
for ($i = $houses['pooh']; $i <= $houses['piglet']; $i++) {
    $flowNetwork->addVertex($i);
}

foreach ($graph['edges'] as $from => $edges) {
    foreach ($edges as $to) {
        $flowNetwork->addEdge($from, $to);
    }
}
$flow = $flowNetwork->maxFlow($houses['pooh'], $houses['piglet']);
echo "\n";
print_r($flowNetwork->flow);
var_dump($flow);
