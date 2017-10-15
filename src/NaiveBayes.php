<?php


namespace phpML;



/**
 * Class NaiveBayes
 *
 * Bayes classifier based on http://burakkanber.com/blog/machine-learning-naive-bayes-1/
 */
class NaiveBayes
{
    /**
     * Array that holds the labels
     *
     * @var array
     */
    private $labels = [];

    /**
     * Amount of times a word was used per label
     *
     * @var array
     */
    private $wordsPerLabel = [];

    /**
     * Amount of documents per label
     * @var array
     */
    private $documentsPerLabel = [];

    /**
     * @var array
     */
    private $totalWordUsage = [];

    /**
     * @param $label
     * @param $text
     */
    public function train($label, $text)
    {
        $this->addLabel($label);

        // Cleanup the sentance
        $words = $this->tokenize($text);

        foreach ($words as $word)
        {
            $this->updateWordsPerLabel($word, $label);
            $this->updateTotalWordUsage($word);
        }

        $this->updateDocumentsPerLabel($label);
    }

    /**
     * @param $text
     * @return array
     */
    public function guess($text)
    {
        $words = $this->tokenize($text);
        $labelProbability = [];
        $totalDocumentsNotInLabel = [];
        $scores = [];
        $totalDocuments = 0;


        for ($i = 0; $i < count($this->labels); $i++)
        {
            $label = $this->labels[$i];
            $totalDocuments += $this->documentsPerLabel[$label];
        }

        for ($i = 0; $i < count($this->labels); $i++)
        {
            if (!isset($totalDocumentsNotInLabel[$label]))
            {
                $totalDocumentsNotInLabel[$label] = 0;
            }

            $totalDocumentsNotInLabel[$label] = $totalDocuments - $this->documentsPerLabel[$label];
        }


        for ($i = 0; $i < count($this->labels); $i++)
        {
            $label = $this->labels[$i];
            $logSum = 0;

            $labelProbability[$label] = $this->documentsPerLabel[$label] / $totalDocuments;

            for ($j = 0; $j < count($words); $j++)
            {
                $word = $words[$j];
                $totalWordUsage = $this->totalWordUsage[$word];

                if (!$totalWordUsage)
                {
                    continue;
                }

                $wordProbability = $this->getWordUsageInLabel($word, $label) / $this->documentsPerLabel[$label];
                $wordInverseProbability = $this->getInverseWordUsage($word, $label);
                $wordicity = $wordProbability / ($wordProbability + $wordInverseProbability);

                $wordicity = (( 1 * 0.5) + ($totalWordUsage * $wordicity) ) / (1 + $totalWordUsage);

                if ($wordicity === 0)
                {
                    $wordicity = 0.01;
                }

                if ($wordicity == 1)
                {
                    $wordicity = 99.99;
                }

                $logSum += (log(1 - $wordicity) - log($wordicity));
            }

            $scores[$label] = 1 / ( 1 + exp($logSum) );
        }

        return $scores;
    }

    /**
     * @param $label
     * @return array
     */
    protected function addLabel($label)
    {
        if (!isset($this->labels[$label]))
        {
            $this->labels[] = $label;
        }

        return $this->labels;
    }

    /**
     * Returns a list of words
     *
     * @return array
     */
    protected function tokenize($text)
    {
        return array_unique(explode(' ', strtolower($text)));
    }

    /**
     * @param $word
     * @param $label
     * @return $this
     */
    protected function updateWordsPerLabel($word, $label)
    {
        if (!isset($this->wordsPerLabel[$label]))
        {
            $this->wordsPerLabel[$label] = [];
        }

        if (!isset($this->wordsPerLabel[$label][$word]))
        {
            $this->wordsPerLabel[$label][$word] = 0;
        }

        $this->wordsPerLabel[$label][$word]++;

        return $this;
    }

    /**
     * @param $label
     */
    private function updateDocumentsPerLabel($label)
    {
        if (!isset($this->documentsPerLabel[$label]))
        {
            $this->documentsPerLabel[$label] = 0;
        }

        $this->documentsPerLabel[$label]++;
    }

    /**
     * @param $word
     */
    private function updateTotalWordUsage($word)
    {
        if (!isset($this->totalWordUsage[$word]))
        {
            $this->totalWordUsage[$word] = 0;
        }

        $this->totalWordUsage[$word]++;
    }


    private function getWordUsageInLabel($word, $label)
    {
        return (isset($this->wordsPerLabel[$label][$word]) ? $this->wordsPerLabel[$label][$word] : 0);
    }

    private function getInverseWordUsage($word, $label)
    {
        $labels = $this->labels;
        $total = 0;
        for ($i = 0; $i < count($labels); $i++)
        {
            if ($labels[$i] == $label)
            {
                continue;
            }
            $total += $this->getWordUsageInLabel($word, $labels[$i]);
        }


        return $total;
    }
}
