<?php

namespace Selective\Artifact\Compression;

use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;
use ZipArchive;

/**
 * Provides methods for creating, extracting zip archives.
 */
final class ZipFile implements ZipFileInterface
{
    /**
     * Creates a zip archive that contains the files and directories from the specified directory.
     *
     * @param string $sourceDirectoryName The path to the directory to be archived,
     * specified as a relative or absolute path. A relative path is interpreted as
     * relative to the current working directory.
     * @param string $destinationArchiveFileName The path of the archive to be created,
     * specified as a relative or absolute path. A relative path is interpreted as
     * relative to the current working directory.
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     *
     * @return void
     */
    public function createFromDirectory(string $sourceDirectoryName, string $destinationArchiveFileName)
    {
        if (!is_dir($sourceDirectoryName)) {
            throw new RuntimeException(sprintf('Directory not found: %s', $sourceDirectoryName));
        }

        $zip = new ZipArchive();
        $zip->open($destinationArchiveFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sourceDirectoryName));

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $fileName = $file->getFilename();

            if ($fileName === '..') {
                continue;
            }

            $realPath = str_replace('\\', '/', (string)$file->getRealPath());

            if ($file->isDir()) {
                $dirName = str_replace($sourceDirectoryName . '/', '', $realPath . '/');

                if ($dirName === '' || $dirName === '/') {
                    continue;
                }

                $zip->addEmptyDir($dirName);

                continue;
            }

            $dirName = str_replace($sourceDirectoryName . '/', '', $realPath);
            $zip->addFile($realPath, $dirName);
        }

        $zip->close();
    }

    /**
     * Extracts all the files in the specified zip archive to a directory on the file system.
     *
     * @param string $sourceArchiveFileName The path to the archive that is to be extracted
     * @param string $destinationDirectoryName The path to the directory in which to place the extracted files,
     * specified as a relative or absolute path.
     * A relative path is interpreted as relative to the current working directory.
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     *
     * @return void
     */
    public function extractToDirectory(string $sourceArchiveFileName, string $destinationDirectoryName)
    {
        if (empty($sourceArchiveFileName)) {
            throw new InvalidArgumentException('The source ZIP file is required');
        }
        if (!file_exists($sourceArchiveFileName)) {
            throw new RuntimeException(sprintf('The file could not be found: %s', $sourceArchiveFileName));
        }

        $zip = new ZipArchive();
        if ($zip->open($sourceArchiveFileName) === true) {
            $zip->extractTo($destinationDirectoryName);
            $zip->close();
        } else {
            throw new RuntimeException(sprintf('Unzip failed: %s', $sourceArchiveFileName));
        }
    }
}
