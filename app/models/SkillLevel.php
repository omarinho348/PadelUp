<?php

class SkillLevel
{
    /**
     * Calculates a PadelIQ score based on questionnaire answers.
     *
     * @param array $answers An associative array of question names and their selected values.
     * @return float The calculated score, capped at 7.0.
     */
    public static function calculate(array $answers): float
    {
        // Define the value mapping for each answer.
        $values = [
            'net_play'    => [1 => 1.0, 2 => 2.5, 3 => 4.0, 4 => 5.5, 5 => 6.5],
            'glass_play'  => [1 => 1.0, 2 => 2.5, 3 => 4.0, 4 => 5.5, 5 => 6.5],
            'serve'       => [1 => 1.0, 2 => 2.5, 3 => 4.0, 4 => 5.5, 5 => 6.5],
            'overheads'   => [1 => 1.0, 2 => 2.5, 3 => 4.0, 4 => 5.5, 5 => 6.5],
            'positioning' => [1 => 1.0, 2 => 2.5, 3 => 4.0, 4 => 5.5, 5 => 6.5],
            'competition' => [1 => 1.0, 2 => 2.5, 3 => 4.0, 4 => 5.5, 5 => 7.0],
            'time_played' => [1 => 0.2, 2 => 0.4, 3 => 0.6, 4 => 0.8],
            'training'    => [1 => 0.2, 2 => 0.4, 3 => 0.8], // Adjusted to match 3 options in the form
        ];

        $coreQuestions = ['net_play', 'glass_play', 'serve', 'overheads', 'positioning', 'competition'];
        $experienceQuestions = ['time_played', 'training'];

        $coreSum = 0;
        $expSum = 0;

        foreach ($answers as $key => $value) {
            // The form values are decimals like "1.00", so we cast to int to match the array keys.
            if (isset($values[$key][(int)$value])) {
                $score = $values[$key][(int)$value];
                if (in_array($key, $coreQuestions)) {
                    $coreSum += $score;
                } elseif (in_array($key, $experienceQuestions)) {
                    $expSum += $score;
                }
            }
        }

        // Calculate weighted average
        $coreAverage = count($coreQuestions) > 0 ? $coreSum / count($coreQuestions) : 0;
        $expAverage = count($experienceQuestions) > 0 ? $expSum / count($experienceQuestions) : 0;

        // Core skills have more weight (85%) than experience (15%)
        $finalScore = ($coreAverage * 0.85) + ($expAverage * 0.15);

        // Cap the score at 7.0 and ensure it's not negative
        return max(0, min($finalScore, 7.0));
    }
}