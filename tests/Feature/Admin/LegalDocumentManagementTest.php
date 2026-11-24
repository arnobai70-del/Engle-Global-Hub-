<?php

namespace Tests\Feature\Admin;

use App\Models\LegalDocument;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LegalDocumentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        Storage::fake('legal_documents');
    }

    private function actor(string $role): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole($role);

        return $user;
    }

    private function pdf(string $filename, string $text = 'Original document'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $filename,
            "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n% ".$text."\n%%EOF\n"
        );
    }

    private function upload(User $admin): LegalDocument
    {
        $this->actingAs($admin)->post(route('admin.legal-documents.store'), [
            'title' => 'Registered company certificate',
            'document_type' => 'incorporation',
            'issuer' => 'RJSC Bangladesh',
            'reference_number' => 'C-TEST/2026',
            'issued_at' => '2026-10-26',
            'original_pdf' => $this->pdf('original.pdf'),
        ])->assertRedirect(route('admin.legal-documents.index'));

        return LegalDocument::query()->sole();
    }

    public function test_original_is_private_and_only_staff_can_download(): void
    {
        $admin = $this->actor('admin');
        $customer = $this->actor('customer');
        $document = $this->upload($admin);

        Storage::disk('legal_documents')->assertExists($document->private_path);
        $this->assertSame('draft', $document->publication_status);
        $this->get(route('legal-documents.index'))->assertOk()->assertDontSee('Registered company certificate');
        $this->get(route('legal-documents.download', $document))->assertNotFound();
        $this->actingAs($customer)->get(route('admin.legal-documents.original', $document))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.legal-documents.original', $document))->assertForbidden();
        $this->actingAs($this->actor('super-admin'))->get(route('admin.legal-documents.original', $document))->assertOk();
    }

    public function test_owner_approves_then_publishes_separately(): void
    {
        $admin = $this->actor('admin');
        $owner = $this->actor('super-admin');
        $document = $this->upload($admin);

        $this->actingAs($admin)->post(route('admin.legal-documents.approve', $document), [
            'confirm_official_source' => 1,
        ])->assertForbidden();

        $this->actingAs($owner)->post(route('admin.legal-documents.publish', $document))
            ->assertSessionHasErrors('document');

        $this->actingAs($owner)->post(route('admin.legal-documents.approve', $document), [
            'confirm_official_source' => 1,
        ])->assertRedirect();

        $this->get(route('legal-documents.index'))->assertDontSee('Registered company certificate');

        $this->actingAs($owner)->post(route('admin.legal-documents.publish', $document))->assertRedirect();

        $this->get(route('legal-documents.index'))->assertOk()->assertSee('Registered company certificate');
        $this->get(route('legal-documents.index', ['lang' => 'bn']))->assertOk()->assertSee('কোম্পানির নিবন্ধন ও লাইসেন্স');
        $this->get(route('about'))->assertOk()->assertSee(route('legal-documents.index'));
        $this->get(route('legal-documents.download', $document))->assertNotFound();
        $this->assertDatabaseHas('activity_logs', ['action' => 'legal_document.published', 'subject_id' => $document->id]);
    }

    public function test_edit_retracts_published_document_and_requires_new_approval(): void
    {
        $owner = $this->actor('super-admin');
        $doc = $this->upload($owner);
        $this->actingAs($owner)->post(route('admin.legal-documents.approve', $doc), ['confirm_official_source' => 1]);
        $this->actingAs($owner)->post(route('admin.legal-documents.publish', $doc));
        $this->get(route('legal-documents.index'))->assertSee('Registered company certificate');

        $this->actingAs($owner)->patch(route('admin.legal-documents.update', $doc), [
            'title' => 'Updated registered company certificate',
            'document_type' => 'incorporation',
            'issuer' => 'RJSC Bangladesh',
            'reference_number' => 'C-TEST/2026',
        ])->assertRedirect();

        $doc->refresh();
        $this->assertSame('draft', $doc->publication_status);
        $this->assertNull($doc->approved_at);
        $this->get(route('legal-documents.index'))->assertDontSee('Updated registered company certificate');
    }

    public function test_expired_document_cannot_be_approved_or_displayed(): void
    {
        $owner = $this->actor('super-admin');
        $doc = $this->upload($owner);
        $doc->expires_at = today()->subDay();
        $doc->save();

        $this->actingAs($owner)->post(route('admin.legal-documents.approve', $doc), [
            'confirm_official_source' => 1,
        ])->assertSessionHasErrors('document');

        $doc->expires_at = today()->addDay();
        $doc->save();
        $this->actingAs($owner)->post(route('admin.legal-documents.approve', $doc), ['confirm_official_source' => 1]);
        $this->actingAs($owner)->post(route('admin.legal-documents.publish', $doc));
        $this->get(route('legal-documents.index'))->assertSee('Registered company certificate');

        $doc->expires_at = today()->subDay();
        $doc->save();
        $this->get(route('legal-documents.index'))->assertDontSee('Registered company certificate');
    }

    public function test_duplicate_original_cannot_be_uploaded_twice(): void
    {
        $admin = $this->actor('admin');
        $this->upload($admin);
        $this->actingAs($admin)->post(route('admin.legal-documents.store'), [
            'title' => 'Duplicate',
            'document_type' => 'other',
            'issuer' => 'RJSC Bangladesh',
            'original_pdf' => $this->pdf('copy.pdf'),
        ])->assertSessionHasErrors('original_pdf');
        $this->assertDatabaseCount('legal_documents', 1);
    }

    public function test_public_copy_requires_owner_confirmation_and_is_not_the_original(): void
    {
        $owner = $this->actor('super-admin');
        $doc = $this->upload($owner);

        $this->actingAs($owner)->patch(route('admin.legal-documents.update', $doc), [
            'title' => 'Registered company certificate',
            'document_type' => 'incorporation',
            'issuer' => 'RJSC Bangladesh',
            'redacted_pdf' => $this->pdf('same.pdf'),
        ])->assertSessionHasErrors('redacted_pdf');

        $this->actingAs($owner)->patch(route('admin.legal-documents.update', $doc), [
            'title' => 'Registered company certificate',
            'document_type' => 'incorporation',
            'issuer' => 'RJSC Bangladesh',
            'redacted_pdf' => $this->pdf('redacted.pdf', 'Redacted public edition'),
        ])->assertRedirect();

        $this->get(route('legal-documents.download', $doc))->assertNotFound();
        $this->actingAs($owner)->post(route('admin.legal-documents.approve', $doc), [
            'confirm_official_source' => 1,
        ])->assertSessionHasErrors('confirm_redacted_copy');

        $this->actingAs($owner)->post(route('admin.legal-documents.approve', $doc), [
            'confirm_official_source' => 1, 'confirm_redacted_copy' => 1,
        ])->assertRedirect();
        $this->actingAs($owner)->post(route('admin.legal-documents.publish', $doc))->assertRedirect();
        $this->get(route('legal-documents.download', $doc))->assertOk();

        $this->actingAs($owner)->post(route('admin.legal-documents.unpublish', $doc))->assertRedirect();
        $this->get(route('legal-documents.download', $doc))->assertNotFound();
    }

    public function test_invalid_file_is_rejected_and_staff_roles_are_scoped(): void
    {
        $admin = $this->actor('admin');
        $editor = $this->actor('content-editor');
        $this->actingAs($admin)->post(route('admin.legal-documents.store'), [
            'title' => 'Invalid',
            'document_type' => 'other',
            'issuer' => 'Example',
            'original_pdf' => UploadedFile::fake()->create('not-a-pdf.txt', 10, 'text/plain'),
        ])->assertSessionHasErrors('original_pdf');
        $this->actingAs($editor)->get(route('admin.legal-documents.index'))->assertForbidden();
        $this->assertDatabaseCount('legal_documents', 0);
    }
}
