<?php

namespace Tests\Unit;

use App\Services\PhoneNumber;
use PHPUnit\Framework\TestCase;

class PhoneNumberTest extends TestCase
{
    public function test_bangladeshi_numbers_in_any_stored_form_give_the_same_links_and_display(): void
    {
        foreach (['01715127464', '8801715127464', '+8801715127464', '+880 1715-127464', '00880 1715 127464'] as $stored) {
            $this->assertSame('+8801715127464', PhoneNumber::e164($stored), $stored);
            $this->assertSame('8801715127464', PhoneNumber::whatsappDigits($stored), $stored);
            $this->assertSame('+880 1715-127464', PhoneNumber::display($stored), $stored);
        }
    }

    public function test_foreign_numbers_use_e164_and_unusable_values_give_no_link(): void
    {
        $this->assertSame('+6591234567', PhoneNumber::e164('+65 9123 4567'));
        $this->assertSame('+6591234567', PhoneNumber::display('+65 9123 4567'));

        foreach (['', 'call us', '12345', '91234567'] as $stored) {
            $this->assertNull(PhoneNumber::e164($stored), $stored);
            $this->assertNull(PhoneNumber::whatsappDigits($stored), $stored);
        }
        $this->assertSame('call us', PhoneNumber::display('call us'));
    }
}
