<?php

namespace Selective\Artifact\Filesystem;

use RuntimeException;

/**
 * Local filesystem.
 */
interface FilesystemInterface
{
    /**
     * Normalize path.
     *
     * @param string $path The path
     *
     * @return string The normalized path
     */
    public function normalizePath(string $path): string;

    /**
     * Delete files and directories by search patterns.
     *
     * @param string $path The start path
     * @param array $patterns The regex search patterns
     *
     * @return void
     */
    public function deleteFileset(string $path, array $patterns);

    /**
     * Find files and directories.
     *
     * @param string $path The source path
     *
     * @return array The files
     */
    public function listDirectoryRecursive(string $path): array;

    /**
     * Remove directory recursively.
     *
     * This function is compatible with vfsStream.
     *
     * @param string $path The path
     *
     * @return bool true on success or false on failure
     */
    public function removeDirectory(string $path): bool;

    /**
     * Create new directory.
     *
     * @param string $path The path
     *
     * @throws RuntimeException
     *
     * @return void
     */
    public function createDirectory(string $path);

    /**
     * Deletes a file.
     *
     * @param string $filename The file
     *
     * @throws RuntimeException
     *
     * @return void
     */
    public function deleteFile(string $filename);
}
