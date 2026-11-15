<?php

namespace Tests\Feature;

use App\Enums\BusinessService;
use App\Enums\StudentApplicationStatus;
use App\Enums\StudentDocumentType;
use App\Models\ServiceEnquiry;
use App\Models\StudentDocument;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

class TranslationCoverageTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_public_and_portal_translation_key_has_bengali(): void
    {
        $bengali = json_decode((string) file_get_contents(lang_path('bn.json')), true, flags: JSON_THROW_ON_ERROR);
        $missing = [];

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(resource_path('views')));
        foreach ($files as $file) {
            $path = str_replace(DIRECTORY_SEPARATOR, '/', $file->getPathname());
            if (! str_ends_with($path, '.blade.php') || str_contains($path, '/admin/') || str_contains($path, 'layouts/admin')) {
                continue;
            }

            preg_match_all('/__\(\'((?:[^\'\\\\]|\\\\.)*)\'/', (string) file_get_contents($path), $matches);
            foreach ($matches[1] as $key) {
                if (! isset($bengali[stripslashes($key)])) {
                    $missing[] = basename($path).': '.stripslashes($key);
                }
            }
        }

        $this->assertSame([], $missing, 'Bengali translations are missing.');
    }

    public function test_status_document_service_and_category_labels_have_bengali(): void
    {
        $bengali = json_decode((string) file_get_contents(lang_path('bn.json')), true, flags: JSON_THROW_ON_ERROR);

        $labels = collect(StudentApplicationStatus::cases())->flatMap(fn ($status) => [$status->label(), $status->stage()])
            ->merge(collect(StudentDocumentType::cases())->map->label())
            ->merge(collect(BusinessService::cases())->map->label())
            ->merge(collect(ServiceEnquiry::CATEGORIES)->flatten())
            ->merge(array_values(StudentDocument::STATUS_LABELS))
            ->merge(array_values(ServiceEnquiry::STATUSES))
            ->merge(['Not uploaded'])
            ->unique();

        $this->assertSame([], $labels->reject(fn (string $label): bool => isset($bengali[$label]))->values()->all());
    }

    public function test_student_portal_renders_in_bengali_with_english_fallback_for_data(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('customer');

        $this->actingAs($user)->get(route('student.applications.index', ['lang' => 'bn']))->assertOk()
            ->assertSee('<html lang="bn">', false)
            ->assertSee('আমার পড়াশোনার আবেদনসমূহ');

        $this->actingAs($user)->get(route('service-enquiries.create', ['service' => 'tourist-visa', 'lang' => 'bn']))->assertOk()
            ->assertSee('আপনার তথ্য')->assertSee('ট্যুরিস্ট ভিসা অনুসন্ধান');
    }
}
