<?php

namespace CultuurNet\ProjectAanvraag\Widget;

use MatthiasMullie\Minify\CSS;
use PHPUnit\Framework\TestCase;

final class LicenseNoticeTest extends TestCase
{
    private const CSS_PATH = __DIR__ . '/../../web/assets/css/cn_widget_styling.css';

    private const NOTICES = [
        'Lucide icons v',
        'Lucide Icons and Contributors',
        'Copyright (c) 2013-present Cole Bemis',
        'Permission to use, copy, modify, and/or distribute this software for any',
        'The above copyright notice and this permission notice shall be included in all',
    ];

    /**
     * @test
     */
    public function it_keeps_the_lucide_license_notice_in_the_compiled_css(): void
    {
        $this->assertContainsNotices(file_get_contents(self::CSS_PATH), 'compiled');
    }

    /**
     * @test
     */
    public function it_keeps_the_lucide_license_notice_in_the_minified_css(): void
    {
        $this->assertContainsNotices((new CSS(self::CSS_PATH))->minify(), 'minified');
    }

    private function assertContainsNotices(string $css, string $variant): void
    {
        foreach (self::NOTICES as $notice) {
            $this->assertTrue(
                strpos($css, $notice) !== false,
                sprintf('License notice "%s" is missing from the %s cn_widget_styling.css.', $notice, $variant)
            );
        }
    }
}
