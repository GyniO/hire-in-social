<?php

declare(strict_types=1);

/*
 * This file is part of the Hire in Social project.
 *
 * (c) Norbert Orzechowicz <norbert@orzechowicz.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HireInSocial\Tests\Application\Integration\System\FileStorage;

use HireInSocial\Application\Exception\InvalidAssertionException;
use HireInSocial\Application\System\FileStorage\File;
use PHPUnit\Framework\TestCase;

final class FileTest extends TestCase
{
    public function test_creating_pdf_file()
    {
        $file = File::pdf('/destination/file.pdf', __DIR__ . '/fixtures/blank.pdf');

        $this->assertEquals(
            '/destination/file.pdf',
            $file->destinationPath()
        );
    }

    public function test_creating_pdf_file_from_txt_file()
    {
        $this->expectException(InvalidAssertionException::class);
        $this->expectExceptionMessage('Expected application/pdf file got text/plain');

        File::pdf('/destination/file.pdf', __DIR__ . '/fixtures/text.txt');
    }
}
