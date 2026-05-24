<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Storage;

use App\Infrastructure\Storage\LocalAvatarStorage;
use DomainException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class LocalAvatarStorageTest extends TestCase
{
    private string $uploadDirectory;

    protected function setUp(): void
    {
        $this->uploadDirectory = sys_get_temp_dir().'/leklub-avatar-test-'.bin2hex(random_bytes(4));
    }

    protected function tearDown(): void
    {
        if (!is_dir($this->uploadDirectory)) {
            return;
        }

        foreach (glob($this->uploadDirectory.'/*') ?: [] as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        rmdir($this->uploadDirectory);
    }

    public function testItStoresValidAvatarAndDeletesPreviousLocalAvatar(): void
    {
        mkdir($this->uploadDirectory, 0775, true);
        file_put_contents($this->uploadDirectory.'/old.png', self::pngContent());

        $storage = new LocalAvatarStorage($this->uploadDirectory, '/uploads/avatars');
        $avatarUrl = $storage->store(
            $this->uploadedFile('avatar.png', self::pngContent()),
            'http://localhost:8080',
            'http://localhost:8080/uploads/avatars/old.png',
        );

        self::assertStringStartsWith('http://localhost:8080/uploads/avatars/', $avatarUrl);
        self::assertStringEndsWith('.png', $avatarUrl);
        self::assertFalse(is_file($this->uploadDirectory.'/old.png'));
        self::assertCount(1, glob($this->uploadDirectory.'/*.png') ?: []);
    }

    public function testItDoesNotDeleteExternalAvatarUrl(): void
    {
        mkdir($this->uploadDirectory, 0775, true);

        $storage = new LocalAvatarStorage($this->uploadDirectory, '/uploads/avatars');
        $storage->store(
            $this->uploadedFile('avatar.png', self::pngContent()),
            'http://localhost:8080',
            'https://example.com/avatar.png',
        );

        self::assertCount(1, glob($this->uploadDirectory.'/*.png') ?: []);
    }

    public function testItRejectsUnsupportedFileType(): void
    {
        $storage = new LocalAvatarStorage($this->uploadDirectory, '/uploads/avatars');

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('AVATAR_UPLOAD_UNSUPPORTED_TYPE');

        $storage->store($this->uploadedFile('avatar.txt', 'plain text'), 'http://localhost:8080', null);
    }

    public function testItRejectsTooLargeFile(): void
    {
        $storage = new LocalAvatarStorage($this->uploadDirectory, '/uploads/avatars');

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('AVATAR_UPLOAD_TOO_LARGE');

        $storage->store($this->uploadedFile('avatar.png', str_repeat('a', (2 * 1024 * 1024) + 1)), 'http://localhost:8080', null);
    }

    private function uploadedFile(string $name, string $content): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'leklub-avatar-');
        self::assertIsString($path);
        file_put_contents($path, $content);

        return new UploadedFile($path, $name, null, null, true);
    }

    private static function pngContent(): string
    {
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=', true) ?: '';
    }
}
