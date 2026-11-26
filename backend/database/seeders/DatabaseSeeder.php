<?php

namespace Database\Seeders;

use App\Models\Interest;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $skills = ['Analytical Thinking', 'Communication', 'Problem Solving', 'Leadership', 'Teamwork', 'Research', 'Project Management', 'Data Analysis', 'Python', 'JavaScript', 'TypeScript', 'PHP', 'SQL', 'Cloud Platforms', 'Cybersecurity', 'UI Design', 'UX Research', 'Graphic Design', 'Content Writing', 'Public Speaking', 'Financial Analysis', 'Teaching', 'Clinical Care', 'Laboratory Skills', 'Mechanical Design', 'Civil Engineering', 'Digital Marketing', 'Entrepreneurship', 'Negotiation', 'Organization', 'Critical Thinking', 'Machine Learning'];
        foreach ($skills as $name) {
            Skill::firstOrCreate(['slug' => str($name)->slug()], ['name' => $name]);
        }

        $interests = ['Building Technology', 'Understanding People', 'Creative Expression', 'Scientific Discovery', 'Business Strategy', 'Helping Communities', 'Health and Wellbeing', 'Teaching and Mentoring', 'Financial Systems', 'Engineering Systems', 'Media and Storytelling', 'Public Service', 'Environmental Impact', 'Research and Investigation', 'Starting Ventures'];
        foreach ($interests as $name) {
            Interest::firstOrCreate(['slug' => str($name)->slug()], ['name' => $name]);
        }

        $this->call(CareerBankSeeder::class);
        $this->call(QuizSeeder::class);
        $this->call(ContentLibrarySeeder::class);

        $accounts = [
            ['PathSeeker Administrator', 'admin@pathseeker.test', 'admin', ['country' => 'Pakistan']],
            ['Areeba Khan', 'student@pathseeker.test', 'student', ['country' => 'Pakistan', 'education_level' => 'Higher Secondary', 'current_field' => 'Computer Science', 'career_goal' => 'Explore technology careers that combine analytical thinking and practical building.']],
            ['Hamza Siddiqui', 'graduate@pathseeker.test', 'graduate', ['country' => 'Pakistan', 'education_level' => 'Bachelor', 'degree' => 'BS Economics', 'field_of_study' => 'Economics', 'graduation_year' => 2025, 'career_goal' => 'Build an evidence-led route into analytics and financial technology.']],
            ['Sara Ahmed', 'professional@pathseeker.test', 'professional', ['country' => 'Pakistan', 'education_level' => 'Bachelor', 'job_title' => 'Operations Coordinator', 'years_experience' => 4, 'industry' => 'Logistics', 'transition_goal' => 'Move toward product operations and data-informed decision making.']],
            ['Maham Raza', 'maham.raza@pathseeker.test', 'student', ['country' => 'Pakistan', 'education_level' => 'Undergraduate', 'current_field' => 'Psychology', 'career_goal' => 'Compare research, counselling, and user research pathways.']],
            ['Daniyal Iqbal', 'daniyal.iqbal@pathseeker.test', 'student', ['country' => 'Pakistan', 'education_level' => 'Higher Secondary', 'current_field' => 'Pre-Engineering', 'career_goal' => 'Understand which engineering discipline fits practical problem solving.']],
            ['Zoya Malik', 'zoya.malik@pathseeker.test', 'student', ['country' => 'Pakistan', 'education_level' => 'Undergraduate', 'current_field' => 'Visual Communication', 'career_goal' => 'Connect design strengths with sustainable digital careers.']],
            ['Usman Farooq', 'usman.farooq@pathseeker.test', 'graduate', ['country' => 'Pakistan', 'education_level' => 'Bachelor', 'degree' => 'BS Biotechnology', 'field_of_study' => 'Biotechnology', 'graduation_year' => 2024, 'career_goal' => 'Evaluate laboratory, research, and health-technology options.']],
            ['Hira Nawaz', 'hira.nawaz@pathseeker.test', 'graduate', ['country' => 'Pakistan', 'education_level' => 'Bachelor', 'degree' => 'BBA', 'field_of_study' => 'Marketing', 'graduation_year' => 2025, 'career_goal' => 'Build a route into ethical digital marketing and brand strategy.']],
            ['Bilal Qureshi', 'bilal.qureshi@pathseeker.test', 'graduate', ['country' => 'Pakistan', 'education_level' => 'Bachelor', 'degree' => 'BS Mathematics', 'field_of_study' => 'Mathematics', 'graduation_year' => 2023, 'career_goal' => 'Translate quantitative strengths into data and risk careers.']],
            ['Nida Sheikh', 'nida.sheikh@pathseeker.test', 'professional', ['country' => 'Pakistan', 'education_level' => 'Master', 'job_title' => 'School Coordinator', 'years_experience' => 6, 'industry' => 'Education', 'transition_goal' => 'Explore learning design and education technology leadership.']],
            ['Omar Hassan', 'omar.hassan@pathseeker.test', 'professional', ['country' => 'Pakistan', 'education_level' => 'Bachelor', 'job_title' => 'Support Engineer', 'years_experience' => 5, 'industry' => 'Technology', 'transition_goal' => 'Progress into cloud engineering and platform reliability.']],
        ];

        foreach ($accounts as $account) {
            [$name, $email, $role, $profile] = $account;
            $user = User::updateOrCreate(
                ['email' => $email],
                ['name' => $name, 'role' => $role, 'password' => Hash::make('PathSeeker2026!'), 'email_verified_at' => now(), 'is_active' => true],
            );
            $user->profile()->updateOrCreate([], $profile);
        }

        $this->call(CommunicationSeeder::class);
    }
}
