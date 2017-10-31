<?php
set_time_limit(60);
include("phpML/NaiveBayes.php");

use phpML\NaiveBayes;

$nb = new NaiveBayes();
echo "<pre>";
$i = 0;
$products = array_map('str_getcsv', file('./dataset/products_small.csv'));
foreach ($products as $product)
{
    $title = $product[0];
    $category = $product[1];

    $category = trim(explode('>>', $category)[0]);

    $nb->train($category, $title);
}

var_dump($nb->getLabels());
?>
<html>
    <head>
        <style>
            body, textarea, button {
                font-family:Helvetica, Arial, sans-serif;
            }
            textarea {
                width:300px;
                height:100px;
                resize:none;
                display:block;
                padding:10px;
                margin:10px auto;
                text-align:center;
            }
            input {
                display:block; 
                margin:auto;
            }
            h2, p { 
                text-align:center;
            }
        </style>
    </head>
    <body>
        <?php
        $phrase = $_POST['phrase'];
        $probability = 0;
        if ($phrase)
        {
            $probability = $nb->guess($phrase);
        }
        ?>
        <form method="post">
            <textarea name="phrase" placeholder="Enter product type."></textarea>
            <input type="submit">Guess</button>
        </form>

        <div style="text-align:left">
        <?php
        if ($probability)
        {
            echo "<p> Phrase: " . $phrase . " has probability of: <br/>";

            $value = max($probability);
            $key = array_search($value, $probability);
            
            echo "<p>" . $key . ": " . $value . "</p>";

            echo "<pre>";
            var_dump($probability);
        }?>
        </div>
    </body>
</html>