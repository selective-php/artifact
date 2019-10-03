<?php

namespace Selective\Artifact\Compression;

/**
 * Provides methods for creating, extracting zip archives.
 */
interface ZipFileInterface
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
     * @return void
     */
    public function createFromDirectory(string $sourceDirectoryName, string $destinationArchiveFileName);

    /**
     * Extracts all the files in the specified zip archive to a directory on the file system.
     *
     * @param string $sourceArchiveFileName The path to the archive that is to be extracted
     * @param string $destinationDirectoryName The path to the directory in which to place the extracted files,
     * specified as a relative or absolute path.
     * A relative path is interpreted as relative to the current working directory.
     *
     * @return void
     */
    public function extractToDirectory(string $sourceArchiveFileName, string $destinationDirectoryName);
}
