<?php
function bubble_sort($list) {
    $count = count($list);

    do {
        $sorted = false;

        for ($current = 0;$current < $count; $current++) {
            $next = $current + 1;

            if ($list[$next] < $list[$current] && $next < $count) {   
                $sorted = true;     

                list($list[$current], $list[$next]) = [$list[$next], $list[$current]];
            }
        }

    } while ($sorted);

    return $list;
}

$list = bubble_sort([3,2,1,4,7,6]);

echo "<pre>";
var_dump ($list);
