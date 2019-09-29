<?php

namespace Selective\Artifact\Builder;

use DirectoryIterator;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;
use ZipArchive;

/**
 * Artifact Filesystem.
 */
class ArtifactFilesystem
{
    public function normalizePath(string $path): string
    {
        return (string)str_replace('\\', '/', $path);
    }

    /**
     * @param string $dir
     * @param array $patterns
     */
    public function deleteFileset(string $dir, array $patterns)
    {
        $files = $this->getDirContents($dir);

        foreach ($files as $i => $file) {
            $relativeFile = substr($file, strlen($dir) + 1);
            //echo "File: " . $relativeFile . "\n";

            foreach ($patterns as $pattern) {
                if (preg_match('/' . $pattern . '/', $relativeFile)) {
                    if (is_file($file)) {
                        //echo "Delete file: " . $file . "\n";
                        unlink($file);
                    }
                    if (is_dir($file)) {
                        //echo "Delete directory: " . $file . "\n";
                        $this->rrmdir($file);
                    }
                }
            }
        }
    }

    public function getDirContents(string $path): array
    {
        $path = str_replace('\\', '/', $path);

        $rii = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $path,
                FilesystemIterator::KEY_AS_PATHNAME |
                FilesystemIterator::CURRENT_AS_SELF |
                RecursiveIteratorIterator::SELF_FIRST
            )
        );

        $files = [];

        /** @var SplFileInfo|DirectoryIterator $file */
        foreach ($rii as $file) {
            if ($file->isDir() || ($file instanceof DirectoryIterator && $file->isDot())) {
                $files[] = (string)str_replace('\\', '/', (string)$file->getPath());
            }
            if ($file->isFile()) {
                $files[] = (string)str_replace('\\', '/', (string)$file->getRealPath());
            }
        }

        return $files;
    }

    public function zipDirectory(string $zipFile, string $path)
    {
        $zip = new ZipArchive();
        $zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

        /** @var SplFileInfo $file */
        foreach ($files as $name => $file) {
            if ($file->isDir()) {
                continue;
            }

            $filePath = (string)$file->getRealPath();
            $relativePath = (string)substr($filePath, strlen($path) + 1);

            $zip->addFile($filePath, $relativePath);
        }
        $zip->close();
    }

    public function unzip(string $zipFile, string $destination)
    {
        $zip = new ZipArchive();
        if ($zip->open($zipFile) === true) {
            $zip->extractTo($destination);
            $zip->close();
        } else {
            throw new RuntimeException('Unzip failed');
        }
    }

    public function removeFile(string $file)
    {
        if (!file_exists($file)) {
            return true;
        }
        unlink($file);
    }

    /**
     * Remove directory recursively.
     * This function is compatible with vfsStream.
     *
     * @param string $path The path
     *
     * @return bool true on success or false on failure
     */
    public function rrmdir(string $path): bool
    {
        if (!file_exists($path)) {
            return true;
        }

        $iterator = new DirectoryIterator($path);

        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isDot() || !$fileInfo->isDir()) {
                continue;
            }
            $this->rrmdir($fileInfo->getPathname());
        }
        $files = new FilesystemIterator($path);

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            unlink((string)$file->getPathname());
        }

        return rmdir($path);
    }

    public function createDirectory(string $path)
    {
        if (is_dir($path)) {
            return;
        }
        if (!mkdir($path, 0777, true) && !is_dir($path)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
        }
    }

    public function unlink(string $filename)
    {
        if (!file_exists($filename)) {
            return true;
        }

        if (!unlink($filename)) {
            throw new RuntimeException(sprintf('Unlink failed: %s', $filename));
        }
    }
}
