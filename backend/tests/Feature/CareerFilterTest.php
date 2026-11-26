<?php

namespace Tests\Feature;

use App\Models\Career;
use App\Models\CareerDomain;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CareerFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_career_search_and_domain_filters_only_return_published_records(): void
    {
        $technology = CareerDomain::create(['name' => 'Technology', 'slug' => 'technology', 'description' => 'Digital systems']);
        $science = CareerDomain::create(['name' => 'Science', 'slug' => 'science', 'description' => 'Evidence and discovery']);
        $this->career($technology, 'Data Engineer', true);
        $this->career($science, 'Research Scientist', true);
        $this->career($technology, 'Secret Prototype Career', false);

        $this->getJson('/api/careers?search=Data&domain=technology')
            ->assertOk()->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.title', 'Data Engineer');
        $this->getJson('/api/careers?search=Secret')->assertOk()->assertJsonPath('meta.total', 0);
    }

    public function test_published_career_detail_includes_skills_and_demo_salary_context(): void
    {
        $domain = CareerDomain::create(['name' => 'Technology', 'slug' => 'technology', 'description' => 'Digital systems']);
        $career = $this->career($domain, 'Cloud Engineer', true);

        $this->getJson('/api/careers/'.$career->slug)->assertOk()
            ->assertJsonPath('data.title', 'Cloud Engineer')
            ->assertJsonPath('data.salary.context', 'Seeded demo range.');
    }

    private function career(CareerDomain $domain, string $title, bool $published): Career
    {
        return Career::create(['career_domain_id' => $domain->id, 'title' => $title, 'slug' => str($title)->slug(), 'summary' => 'Useful summary', 'description' => 'Detailed career profile.', 'responsibilities' => ['Plan work'], 'education_pathway' => ['Foundation'], 'education_level' => 'Bachelor degree', 'experience_level' => 'Entry level', 'salary_min' => 30000, 'salary_max' => 50000, 'currency' => 'USD', 'salary_context' => 'Seeded demo range.', 'demand_level' => 'high', 'is_published' => $published]);
    }
}
