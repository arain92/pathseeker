<?php

namespace Database\Seeders;

use App\Models\Multimedia;
use App\Models\Resource;
use App\Models\SuccessStory;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class ContentLibrarySeeder extends Seeder
{
    public function run(): void
    {
        $tags = collect(['Beginner', 'Skill Building', 'Interview', 'Resume', 'Career Planning', 'Professional Growth', 'Scholarship', 'Portfolio'])->map(fn ($name) => Tag::firstOrCreate(['slug' => str($name)->slug()], ['name' => $name]));
        $titles = ['Career Decision Canvas', 'Skills Evidence Workbook', 'Interview Story Builder', 'Resume Impact Checklist', 'Portfolio Project Planner', 'Scholarship Search Map', 'Professional Transition Guide', 'Networking Conversation Cards', 'Career Research Scorecard', 'Graduate First-90-Days Guide', 'Technical Interview Prep Map', 'Personal Brand Audit', 'Learning Roadmap Template', 'Workplace Strengths Reflection', 'Opportunity Comparison Matrix'];
        foreach ($titles as $i => $title) {
            $r = Resource::updateOrCreate(['slug' => str($title)->slug()], ['title' => $title, 'description' => 'A practical structured tool with prompts that turn career exploration into documented next actions.', 'type' => ['guide', 'checklist', 'pdf', 'infographic'][$i % 4], 'audience' => ['everyone', 'student', 'graduate', 'professional'][$i % 4], 'category' => ['Career Planning', 'Skill Building', 'Interview', 'Professional Growth'][$i % 4], 'file_url' => 'https://www.onetcenter.org/content.html', 'download_count' => $i * 7 + 4, 'is_published' => true, 'thumbnail_url' => ['https://images.unsplash.com/photo-1456324504439-367cee3b3c32','https://images.unsplash.com/photo-1495446815901-a7297e633e8d','https://images.unsplash.com/photo-1516321318423-f06f85e504b3'][$i % 3].'?auto=format&fit=crop&w=900&q=80']);
            $r->tags()->sync([$tags[$i % 8]->id, $tags[($i + 2) % 8]->id]);
        }
        $media = ['How to Read a Career Roadmap', 'Turning Skills into Evidence', 'Inside a Data Career', 'Designing a Useful Portfolio', 'Interview Answers with Structure', 'Exploring Healthcare Pathways', 'Career Change without Guesswork', 'Research Careers Explained', 'Building Professional Confidence', 'Engineering Pathways Overview', 'Finding Your Working Style', 'From Interests to Experiments'];
        foreach ($media as $i => $title) {
            $m = Multimedia::updateOrCreate(['slug' => str($title)->slug()], ['title' => $title, 'description' => 'A concise PathSeeker explainer that breaks the topic into practical decisions and next steps.', 'type' => ['video', 'podcast', 'explainer'][$i % 3], 'source_url' => 'https://www.youtube-nocookie.com/embed/aircAruvnKk', 'transcript' => "This demonstration transcript summarizes the learning objectives, reflection prompts, and practical next actions covered in {$title}.", 'category' => ['Career Planning', 'Skill Building', 'Career Stories'][$i % 3], 'duration_seconds' => 420 + $i * 55, 'view_count' => $i * 11 + 8, 'is_published' => true, 'thumbnail_url' => ['https://images.unsplash.com/photo-1492684223066-81342ee5ff30','https://images.unsplash.com/photo-1516321318423-f06f85e504b3','https://images.unsplash.com/photo-1556761175-b413da4baf72'][$i % 3].'?auto=format&fit=crop&w=900&q=80']);
            $m->tags()->sync([$tags[$i % 8]->id]);
        }
        $people = [['Amina Raza', 'UX Researcher', 'Arts & Design'], ['Bilal Noor', 'Cloud Engineer', 'Technology'], ['Hira Malik', 'Public Health Analyst', 'Healthcare'], ['Usman Tariq', 'Financial Analyst', 'Finance'], ['Noor Fatima', 'Science Educator', 'Education'], ['Zain Ali', 'Civil Engineer', 'Engineering'], ['Maha Khan', 'Content Strategist', 'Media'], ['Rayyan Ahmed', 'Data Analyst', 'Technology']];
        foreach ($people as $i => [$name,$career,$domain]) {
            SuccessStory::updateOrCreate(['slug' => str($name.'-'.$career)->slug()], ['person_name' => $name, 'career_title' => $career, 'domain' => $domain, 'summary' => "{$name} moved from broad uncertainty to a focused {$career} pathway through small experiments, skill evidence, and informed conversations.", 'timeline' => ['Started with competing interests.', 'Mapped transferable skills.', 'Completed a focused project.', 'Tested the direction with mentors.', 'Secured an entry opportunity.'], 'advice' => 'Treat career decisions as testable hypotheses. Build evidence, speak with practitioners, and review what you learn.', 'status' => 'approved', 'published_at' => now()->subDays(30 - $i)]);
        }
    }
}
