<?php

namespace App\Services;

use App\Models\Career;
use App\Models\QuizAttempt;
use Illuminate\Support\Facades\DB;

class CareerMatchingService
{
    public function complete(QuizAttempt $attempt): QuizAttempt
    {
        return DB::transaction(function () use ($attempt): QuizAttempt {
            $attempt->load(['answers.option', 'user.skills', 'user.interests']);
            $totals = [];
            $counts = [];
            foreach ($attempt->answers as $answer) {
                foreach ($answer->option->dimension_weights as $dimension => $value) {
                    $totals[$dimension] = ($totals[$dimension] ?? 0) + (float) $value;
                    $counts[$dimension] = ($counts[$dimension] ?? 0) + 1;
                }
            }
            $vector = collect($totals)->map(fn ($total, $dimension) => round($total / ($counts[$dimension] * 5), 4))->sortDesc()->all();
            $careers = Career::query()->where('is_published', true)->with(['domain', 'skills', 'interests'])->get();
            $dimensionRows = DB::table('career_dimension_weights')->get()->groupBy('career_id');
            $userSkillIds = $attempt->user->skills->pluck('id');
            $userInterestIds = $attempt->user->interests->pluck('id');
            $ranked = $careers->map(function (Career $career) use ($dimensionRows, $vector, $userSkillIds, $userInterestIds): array {
                $careerVector = $dimensionRows->get($career->id, collect())->pluck('weight', 'dimension')->map(fn ($v) => (float) $v)->all();
                $quiz = $this->cosine($vector, $careerVector) * 100;
                $skillWeight = max(1, $career->skills->sum(fn ($skill) => $skill->pivot->weight));
                $skill = $career->skills->filter(fn ($item) => $userSkillIds->contains($item->id))->sum(fn ($item) => $item->pivot->weight) / $skillWeight * 100;
                $interestWeight = max(1, $career->interests->sum(fn ($item) => (float) $item->pivot->weight));
                $interest = $career->interests->filter(fn ($item) => $userInterestIds->contains($item->id))->sum(fn ($item) => (float) $item->pivot->weight) / $interestWeight * 100;
                $factors = ['quiz_fit' => round($quiz, 2), 'interest_match' => round($interest, 2), 'skill_match' => round($skill, 2), 'interaction_signal' => 0.0];
                $overall = $quiz * .50 + $interest * .20 + $skill * .20;
                $aligned = collect($factors)->except('interaction_signal')->sortDesc()->take(2)->keys()->map(fn ($key) => str($key)->replace('_', ' ')->title())->all();

                return compact('career', 'factors', 'overall', 'aligned');
            })->sortByDesc('overall')->take(10)->values();

            $attempt->result()->create(['dimension_scores' => $vector, 'strongest_traits' => array_slice(array_keys($vector), 0, 4), 'recommended_domains' => $ranked->pluck('career.domain.name')->unique()->take(3)->values()->all(), 'scoring_version' => 'v1']);
            foreach ($ranked as $index => $item) {
                $attempt->recommendations()->create(['career_id' => $item['career']->id, 'rank' => $index + 1, 'overall_score' => round($item['overall'], 2), 'factors' => $item['factors'], 'explanation' => ['strongest_alignment' => $item['aligned'], 'method' => 'Deterministic weighted similarity; no external AI service used.']]);
            }
            $attempt->update(['status' => 'completed', 'completed_at' => now()]);

            return $attempt->fresh(['result', 'recommendations.career.domain', 'recommendations.career.skills']);
        });
    }

    private function cosine(array $left, array $right): float
    {
        $keys = array_unique([...array_keys($left), ...array_keys($right)]);
        $dot = $leftMagnitude = $rightMagnitude = 0.0;
        foreach ($keys as $key) {
            $a = (float) ($left[$key] ?? 0);
            $b = (float) ($right[$key] ?? 0);
            $dot += $a * $b;
            $leftMagnitude += $a ** 2;
            $rightMagnitude += $b ** 2;
        }

        return $leftMagnitude && $rightMagnitude ? $dot / (sqrt($leftMagnitude) * sqrt($rightMagnitude)) : 0.0;
    }
}
