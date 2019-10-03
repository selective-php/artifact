<?php

namespace Selective\Artifact\Filesystem;

use DirectoryIterator;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;

/**
 * Local filesystem.
 */
class LocalFilesystem implements FilesystemInterface
{
    /**
     * Normalize path.
     *
     * @param string $path The path
     *
     * @return string The normalized path
     */
    public function normalizePath(string $path): string
    {
        return (string)str_replace('\\', '/', $path);
    }

    /**
     * Delete files and directories by search patterns.
     *
     * @param string $path The start path
     * @param array $patterns The regex search patterns
     *
     * @return void
     */
    public function deleteFileset(string $path, array $patterns)
    {
        $files = $this->listDirectoryRecursive($path);

        foreach ($files as $file) {
            $this->deleteByPattern($path, $file, $patterns);
        }
    }

    /**
     * Delete file or directory.
     *
     * @param string $path The path
     * @param string $file The file or path
     * @param array $patterns The patterns
     *
     * @return void
     */
    private function deleteByPattern(string $path, string $file, array $patterns)
    {
        $relativeFile = substr($file, strlen($path) + 1);

        foreach ($patterns as $pattern) {
            if (!preg_match('/' . $pattern . '/', $relativeFile)) {
                continue;
            }
            $this->deleteFileOrDirectory($file);
        }
    }

    /**
     * Delete file or directory.
     *
     * @param string $file The file or path
     *
     * @return void
     */
    private function deleteFileOrDirectory(string $file)
    {
        if (is_file($file)) {
            unlink($file);
        }
        if (is_dir($file)) {
            $this->removeDirectory($file);
        }
    }

    /**
     * Find files and directories.
     *
     * @param string $path The source path
     *
     * @return array The files
     */
    public function listDirectoryRecursive(string $path): array
    {
        $path = $this->normalizePath($path);

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $path,
                FilesystemIterator::KEY_AS_PATHNAME |
                FilesystemIterator::CURRENT_AS_SELF |
                RecursiveIteratorIterator::SELF_FIRST
            )
        );

        $files = [];

        /** @var SplFileInfo|DirectoryIterator $file */
        foreach ($iterator as $file) {
            if ($file->isDir() || ($file instanceof DirectoryIterator && $file->isDot())) {
                $files[] = $this->normalizePath($file->getPath());
                continue;
            }
            if ($file->isFile() && $file->getRealPath() !== false) {
                $files[] = $this->normalizePath($file->getRealPath());
            }
        }

        return $files;
    }

    /**
     * Remove directory recursively.
     *
     * This function is compatible with vfsStream.
     *
     * @param string $path The path
     *
     * @return bool true on success or false on failure
     */
    public function removeDirectory(string $path): bool
    {
        $path = $this->normalizePath($path);

        if (!file_exists($path)) {
            return true;
        }

        $iterator = new DirectoryIterator($path);

        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isDot() || !$fileInfo->isDir()) {
                continue;
            }
            $this->removeDirectory($fileInfo->getPathname());
        }

        $files = new FilesystemIterator($path);

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            unlink((string)$file->getPathname());
        }

        return rmdir($path);
    }

    /**
     * Create new directory.
     *
     * @param string $path The path
     *
     * @throws RuntimeException
     *
     * @return void
     */
    public function createDirectory(string $path)
    {
        if (is_dir($path)) {
            return;
        }

        if (!mkdir($path, 0777, true) && !is_dir($path)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $path));
        }
    }

    /**
     * Deletes a file.
     *
     * @param string $filename The file
     *
     * @throws RuntimeException
     *
     * @return void
     */
    public function deleteFile(string $filename)
    {
        if (!file_exists($filename)) {
            return;
        }

        if (!unlink($filename)) {
            throw new RuntimeException(sprintf('File could not be deleted: %s', $filename));
        }
    }
}
