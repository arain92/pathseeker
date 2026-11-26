<?php

namespace Database\Seeders;

use App\Models\Career;
use App\Models\Interest;
use App\Models\QuizQuestion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuizSeeder extends Seeder
{
    public function run(): void
    {
        $questions = [
            ['analytical', 'I enjoy breaking a complicated problem into smaller, testable parts.'], ['analytical', 'I look for patterns before making an important decision.'],
            ['creative', 'I like producing original ways to communicate an idea.'], ['creative', 'An open-ended brief feels energizing rather than uncomfortable.'],
            ['social', 'I gain energy from helping people work through difficult situations.'], ['social', 'I naturally notice how decisions may affect different groups.'],
            ['leadership', 'I am comfortable setting direction when a group is uncertain.'], ['leadership', 'I enjoy taking responsibility for a shared outcome.'],
            ['technical', 'I like learning how tools and systems work beneath the surface.'], ['technical', 'Troubleshooting a system feels satisfying to me.'],
            ['practical', 'I prefer ideas that can be tested through a tangible project.'], ['practical', 'I enjoy improving a process through hands-on iteration.'],
            ['research', 'I enjoy gathering evidence before reaching a conclusion.'], ['research', 'I am patient with questions that require careful investigation.'],
            ['communication', 'I can adapt an explanation for people with different backgrounds.'], ['communication', 'Writing or presenting a clear argument is rewarding.'],
            ['organization', 'I naturally create structure for complex or long-running work.'], ['organization', 'I keep track of details without losing sight of the larger goal.'],
            ['entrepreneurship', 'I notice unmet needs and imagine services that could address them.'], ['entrepreneurship', 'I am comfortable testing an idea despite uncertainty.'],
        ];
        $labels = [1 => 'Strongly disagree', 2 => 'Disagree', 3 => 'Not sure', 4 => 'Agree', 5 => 'Strongly agree'];
        foreach ($questions as $index => [$dimension, $prompt]) {
            $question = QuizQuestion::updateOrCreate(['prompt' => $prompt], ['section' => str($dimension)->title(), 'type' => 'likert', 'sort_order' => $index + 1, 'is_active' => true]);
            foreach ($labels as $value => $label) {
                $question->options()->updateOrCreate(['value' => (string) $value], ['label' => $label, 'dimension_weights' => [$dimension => $value], 'sort_order' => $value]);
            }
        }

        $vectors = [
            'Technology' => ['technical' => .95, 'analytical' => .85, 'research' => .65, 'organization' => .65, 'creative' => .45],
            'Healthcare' => ['social' => .90, 'communication' => .85, 'research' => .70, 'practical' => .70, 'organization' => .70],
            'Business' => ['leadership' => .80, 'communication' => .85, 'analytical' => .70, 'entrepreneurship' => .75, 'organization' => .70],
            'Engineering' => ['technical' => .90, 'analytical' => .90, 'practical' => .85, 'organization' => .65, 'research' => .60],
            'Arts & Design' => ['creative' => .95, 'communication' => .70, 'social' => .60, 'practical' => .55, 'entrepreneurship' => .45],
            'Education' => ['social' => .90, 'communication' => .95, 'leadership' => .60, 'organization' => .70, 'creative' => .60],
            'Finance' => ['analytical' => .95, 'organization' => .90, 'research' => .70, 'communication' => .55, 'technical' => .55],
            'Science' => ['research' => .98, 'analytical' => .90, 'technical' => .70, 'organization' => .65, 'practical' => .60],
            'Media' => ['communication' => .95, 'creative' => .85, 'social' => .65, 'entrepreneurship' => .55, 'research' => .50],
            'Public Service' => ['social' => .85, 'communication' => .85, 'research' => .75, 'leadership' => .70, 'organization' => .70],
        ];
        $interestNames = ['Technology' => 'Building Technology', 'Healthcare' => 'Health and Wellbeing', 'Business' => 'Business Strategy', 'Engineering' => 'Engineering Systems', 'Arts & Design' => 'Creative Expression', 'Education' => 'Teaching and Mentoring', 'Finance' => 'Financial Systems', 'Science' => 'Scientific Discovery', 'Media' => 'Media and Storytelling', 'Public Service' => 'Public Service'];
        foreach (Career::query()->with('domain')->get() as $career) {
            foreach ($vectors[$career->domain->name] as $dimension => $weight) {
                DB::table('career_dimension_weights')->updateOrInsert(['career_id' => $career->id, 'dimension' => $dimension], ['weight' => $weight]);
            }
            $interest = Interest::where('name', $interestNames[$career->domain->name])->first();
            if ($interest) {
                DB::table('career_interest_weights')->updateOrInsert(['career_id' => $career->id, 'interest_id' => $interest->id], ['weight' => 1]);
            }
        }
    }
}
