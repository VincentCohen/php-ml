<pre>
<?php

echo "Bubble sort - https://en.wikipedia.org/wiki/Bubble_sort";

$list = [6,4,7,8,5,2,1,9,3,0];

var_dump ($list);

function bubble_sort(array &$list)
{
    $changed = false;
    for ($currentIndex = 0;$currentIndex < count($list); $currentIndex++)
    {
        $nextIndex = $currentIndex < (count($list) -1) ? $currentIndex + 1 : $currentIndex;

        $current = $list[$currentIndex];
        $next = $list[$nextIndex];
        
        if ($next < $current)
        {   
            $changed = true;     
            $list[$currentIndex] = $next;
            $list[$nextIndex] = $current;
        }
    }

    if ($changed)
    {
        bubble_sort($list, $changed);
    }

    return $list;
}

$list = bubble_sort($list);

var_dump ($list);

echo "--END--";
