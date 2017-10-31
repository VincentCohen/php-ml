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

        // Cleanup the sentence
        $words = $this->tokenize($text);

        foreach ($words as $word) {
            $this->updateWordsPerLabel($word, $label);
            $this->updateTotalWordUsage($word);
        }

        $this->updateDocumentsPerLabel($label);
    }

    /**
     * @param $text
     *
     * @return array
     */
    public function guess($text)
    {
        $words = $this->tokenize($text);
        $labelProbability = [];
        $totalDocumentsNotInLabel = [];
        $scores = [];
        $totalDocuments = 0;

        for ($i = 0; $i < count($this->labels); $i++) {
            $label = $this->labels[$i];
            $totalDocuments += $this->documentsPerLabel[$label];
        }

        for ($i = 0; $i < count($this->labels); $i++) {
            $label = $this->labels[$i];
            if (!isset($totalDocumentsNotInLabel[$label])) {
                $totalDocumentsNotInLabel[$label] = 0;
            }

            $totalDocumentsNotInLabel[$label] = $totalDocuments - $this->documentsPerLabel[$label];
        }


        for ($i = 0; $i < count($this->labels); $i++) {
            $label = $this->labels[$i];
            $combinedProbability = 0;

            $labelProbability[$label] = $this->documentsPerLabel[$label] / $totalDocuments;

            for ($j = 0; $j < count($words); $j++) {
                $word = $words[$j];
                $totalWordUsage = $this->totalWordUsage[$word];

                if (!$totalWordUsage) {
                    continue;
                }

                $wordProbability = $this->getWordUsageInLabel($word, $label) / $this->documentsPerLabel[$label]; 
                $wordInverseProbability = $this->getInverseWordUsage($word, $label);
                $probabilityToLabel = $wordProbability / ($wordProbability + $wordInverseProbability);

                $uniqueWordWeight = 1; // Depends on data set, set higher for higher amount of data
                $probabilityToLabel = (($uniqueWordWeight * 0.5) + ($totalWordUsage * $probabilityToLabel)) 
                    / ($uniqueWordWeight + $totalWordUsage);

                if ($probabilityToLabel === 0) {
                    $probabilityToLabel = 0.01;
                }

                if ($probabilityToLabel == 1) {
                    $probabilityToLabel = 99.99;
                }

                $combinedProbability += (log(1 - $probabilityToLabel) - log($probabilityToLabel));
            }

            $scores[$label] = 1 / ( 1 + exp($combinedProbability) );
        }

        return $scores;
    }

    /**
     * @param $label
     *
     * @return array
     */
    protected function addLabel($label)
    {
        if (!in_array($label, $this->labels)) {
            $this->labels[] = $label;
        }

        return $this->labels;
    }

    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * Returns a list of words
     *
     * @param $str
     *
     * @return array
     */
    protected function tokenize($str)
    {
        return array_unique(explode(' ', strtolower($str)));
    }

    /**
     * @param $word
     * @param $label
     *
     * @return $this
     */
    protected function updateWordsPerLabel($word, $label)
    {
        if (!isset($this->wordsPerLabel[$label])) {
            $this->wordsPerLabel[$label] = [];
        }

        if (!isset($this->wordsPerLabel[$label][$word])) {
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
        if (!isset($this->documentsPerLabel[$label])) {
            $this->documentsPerLabel[$label] = 0;
        }

        $this->documentsPerLabel[$label]++;
    }

    /**
     * @param $word
     */
    private function updateTotalWordUsage($word)
    {
        if (!isset($this->totalWordUsage[$word])) {
            $this->totalWordUsage[$word] = 0;
        }

        $this->totalWordUsage[$word]++;
    }

    /**
     * @param $word
     * @param $label
     *
     * @return int
     */
    private function getWordUsageInLabel($word, $label)
    {
        return (isset($this->wordsPerLabel[$label][$word]) ? $this->wordsPerLabel[$label][$word] : 0);
    }

    /**
     * @param $word
     * @param $label
     *
     * @return int
     */
    private function getInverseWordUsage($word, $label)
    {
        $labels = $this->labels;
        $total  = 0;
        for ($i = 0; $i < count($labels); $i++) {
            if ($labels[$i] == $label) {
                continue;
            }

            $total += $this->getWordUsageInLabel($word, $labels[$i]);
        }

        return $total;
    }
}
