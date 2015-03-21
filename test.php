<?php

$arr = array(
    10=>array(1,2,3),
    11=>array(4,5,6),
    12=>array(7,8,9),
    13=>array(10,11,15,16,18,19,23,47)
);

function allCombinations($arrays)
{
    $result = array();
    $arrays = array_values($arrays);
    $sizeIn = sizeof($arrays);
    $size = $sizeIn > 0 ? 1 : 0;
    foreach ($arrays as $array)
        $size = $size * sizeof($array);
    for ($i = 0; $i < $size; $i ++)
    {
        $result[$i] = array();
        for ($j = 0; $j < $sizeIn; $j ++)
            array_push($result[$i], current($arrays[$j]));
        for ($j = ($sizeIn -1); $j >= 0; $j --)
        {
            if (next($arrays[$j]))
                break;
            elseif (isset ($arrays[$j]))
                reset($arrays[$j]);
        }
    }
    return $result;
}
var_dump(allCombinations($arr));