<?php

namespace Database\Seeders;

use App\Models\Career;
use App\Models\CareerDomain;
use App\Models\Skill;
use Illuminate\Database\Seeder;

class CareerBankSeeder extends Seeder
{
    public function run(): void
    {
        $catalogue = [
            'Technology' => ['Software Engineer', 'Data Engineer', 'Cybersecurity Analyst', 'Cloud Engineer'],
            'Healthcare' => ['Registered Nurse', 'Physiotherapist', 'Health Informatics Specialist', 'Clinical Research Coordinator'],
            'Business' => ['Product Manager', 'Business Analyst', 'Operations Manager', 'Management Consultant'],
            'Engineering' => ['Civil Engineer', 'Mechanical Engineer', 'Electrical Engineer', 'Renewable Energy Engineer'],
            'Arts & Design' => ['UX Designer', 'Graphic Designer', 'Product Designer', 'Motion Designer'],
            'Education' => ['Secondary School Teacher', 'Instructional Designer', 'Academic Advisor', 'Education Program Manager'],
            'Finance' => ['Financial Analyst', 'Risk Analyst', 'Accountant', 'Investment Research Associate'],
            'Science' => ['Environmental Scientist', 'Laboratory Scientist', 'Research Scientist', 'Bioinformatics Analyst'],
            'Media' => ['Content Strategist', 'Multimedia Producer', 'Journalist', 'Digital Marketing Specialist'],
            'Public Service' => ['Policy Analyst', 'Urban Planner', 'Community Development Officer', 'Public Health Officer'],
        ];
        $descriptions = [
            'Technology' => 'Builds dependable digital systems that solve practical problems for people and organizations.',
            'Healthcare' => 'Improves health outcomes through evidence-based care, coordination, research, or responsible technology.',
            'Business' => 'Turns customer, operational, and market evidence into decisions that improve organizations.',
            'Engineering' => 'Applies mathematics, science, and design discipline to create safe and useful physical systems.',
            'Arts & Design' => 'Combines visual craft, user understanding, and iteration to communicate or improve experiences.',
            'Education' => 'Creates guidance and learning experiences that help people develop lasting capability.',
            'Finance' => 'Uses structured financial information to support accountable decisions, planning, and risk management.',
            'Science' => 'Investigates questions systematically and communicates findings with evidence and appropriate uncertainty.',
            'Media' => 'Researches, shapes, and distributes accurate stories or campaigns for specific audiences.',
            'Public Service' => 'Designs or delivers programs and policies intended to improve communities and public systems.',
        ];
        $skillMap = [
            'Technology' => ['Problem Solving', 'Critical Thinking', 'SQL', 'Communication'],
            'Healthcare' => ['Communication', 'Research', 'Teamwork', 'Organization'],
            'Business' => ['Analytical Thinking', 'Communication', 'Project Management', 'Negotiation'],
            'Engineering' => ['Problem Solving', 'Critical Thinking', 'Project Management', 'Teamwork'],
            'Arts & Design' => ['UI Design', 'UX Research', 'Graphic Design', 'Communication'],
            'Education' => ['Teaching', 'Communication', 'Public Speaking', 'Organization'],
            'Finance' => ['Financial Analysis', 'Analytical Thinking', 'SQL', 'Critical Thinking'],
            'Science' => ['Research', 'Laboratory Skills', 'Data Analysis', 'Critical Thinking'],
            'Media' => ['Content Writing', 'Digital Marketing', 'Communication', 'Graphic Design'],
            'Public Service' => ['Research', 'Communication', 'Project Management', 'Leadership'],
        ];

        foreach ($catalogue as $domainName => $titles) {
            $order = array_search($domainName, array_keys($catalogue), true);
            $domain = CareerDomain::updateOrCreate(['slug' => str($domainName)->slug()], ['name' => $domainName, 'description' => $descriptions[$domainName], 'icon' => str($domainName)->slug(), 'sort_order' => $order]);
            foreach ($titles as $index => $title) {
                $base = 32000 + ($order * 2200) + ($index * 3500);
                $career = Career::updateOrCreate(['slug' => str($title)->slug()], [
                    'career_domain_id' => $domain->id, 'title' => $title, 'summary' => $descriptions[$domainName],
                    'description' => "A {$title} works across people, information, and domain tools to deliver measurable outcomes. This profile explains responsibilities, preparation, and transferable skills without claiming live labour-market verification.",
                    'responsibilities' => ['Understand goals and constraints', 'Plan and deliver high-quality work', 'Collaborate with stakeholders', 'Document decisions and improve practice'],
                    'education_pathway' => ['Foundation knowledge', 'Core professional skills', 'Portfolio or supervised practice', 'Entry-level responsibility', 'Continued specialization'],
                    'education_level' => in_array($domainName, ['Healthcare', 'Engineering', 'Science'], true) ? 'Bachelor degree or equivalent' : 'Bachelor degree or skills-based pathway',
                    'experience_level' => $index === 0 ? 'Entry to mid-level' : 'Entry level',
                    'salary_min' => $base, 'salary_max' => $base + 28000, 'currency' => 'USD',
                    'salary_context' => 'Seeded annual demo range; not live or location-adjusted labour data.',
                    'demand_level' => ['high', 'very_high', 'high', 'moderate'][$index],
                    'future_outlook' => 'Catalogue outlook for demonstration, not an external live market feed.',
                    'icon' => str($title)->slug(), 'is_published' => true,
                ]);
                $skills = Skill::query()->whereIn('name', $skillMap[$domainName])->get();
                $career->skills()->sync($skills->mapWithKeys(fn (Skill $skill, int $position) => [$skill->id => ['importance' => $position < 3 ? 'required' : 'recommended', 'weight' => 5 - $position]])->all());
            }
        }
    }
}
