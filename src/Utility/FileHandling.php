<?php

declare(strict_types=1);

namespace App\Utility;

class FileHandling
{
    /**
     * Wrapper function for rmdir, allowing recursive deletion of folders and files
     *
     * @param string $path Absolute path to folder, see PHP rmdir() function. Removes trailing slash internally.
     * @param bool $removeNonEmpty Allow deletion of non-empty directories
     * @return bool TRUE if operation was successful
     *
     * @see \TYPO3\CMS\Core\Utility\GeneralUtility::rmdir()
     */
    public static function rmdir(string $path, bool $removeNonEmpty = false): bool
    {
        $OK = false;
        // Remove trailing slash
        $path = rtrim($path, '/');
        $isWindows = DIRECTORY_SEPARATOR === '\\';
        if (file_exists($path)) {
            $OK = true;
            if (!is_link($path) && is_dir($path)) {
                if ($removeNonEmpty === true && ($handle = @opendir($path))) {
                    $entries = [];

                    while (false !== ($file = readdir($handle))) {
                        if ($file === '.' || $file === '..') {
                            continue;
                        }

                        $entries[] = $path . '/' . $file;
                    }

                    closedir($handle);

                    foreach ($entries as $entry) {
                        if (!static::rmdir($entry, $removeNonEmpty)) {
                            $OK = false;
                        }
                    }
                }
                if ($OK) {
                    $OK = @rmdir($path);
                }
            } elseif (is_link($path) && is_dir($path) && $isWindows) {
                $OK = @rmdir($path);
            } else {
                // If $path is a file, simply remove it
                $OK = @unlink($path);
            }
            clearstatcache();
        } elseif (is_link($path)) {
            $OK = @unlink($path);
            if (!$OK && $isWindows) {
                // Try to delete dead folder links on Windows systems
                $OK = @rmdir($path);
            }
            clearstatcache();
        }
        return $OK;
    }

    /**
     * Returns an array with the names of folders in a specific path
     * Will return 'error' (string) if there were an error with reading directory content.
     * Will return null if provided path is false.
     *
     * @param string $path Path to list directories from
     * @return string[]|string|null Returns an array with the directory entries as values. If no path is provided, the return value will be null.
     *
     * @see \TYPO3\CMS\Core\Utility\GeneralUtility::get_dirs()
     */
    public static function get_dirs(string $path): array|string|null
    {
        $dirs = null;
        if ($path) {
            if (is_dir($path)) {
                $dir = scandir($path);
                $dirs = [];
                foreach ($dir as $entry) {
                    if (is_dir($path . '/' . $entry) && $entry !== '..' && $entry !== '.') {
                        $dirs[] = $entry;
                    }
                }
            } else {
                $dirs = 'error';
            }
        }
        return $dirs;
    }

    /**
     * Low level utility function to copy directories and content recursive
     *
     * @param string $source Path to source directory, relative to document root or absolute
     * @param string $destination Path to destination directory, relative to document root or absolute
     *
     * @see \TYPO3\CMS\Core\Utility\GeneralUtility::copyDirectory()
     */
    public static function copyDirectory(string $source, string $destination): void
    {
        static::mkdir_deep($destination);
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        /** @var \SplFileInfo $item */
        foreach ($iterator as $item) {
            $target = $destination . '/' . static::fixWindowsFilePath((string)$iterator->getSubPathName());
            if ($item->isDir()) {
                static::mkdir_deep($target);
            } else {
                static::upload_copy_move(static::fixWindowsFilePath($item->getPathname()), $target);
            }
        }
    }

    /**
     * Explodes a string and removes whitespace-only values.
     *
     * If $removeEmptyValues is set, then all values that contain only whitespace are removed.
     *
     * Each item will have leading and trailing whitespace removed. However, if the tail items are
     * returned as a single array item, their internal whitespace will not be modified.
     *
     * @param string $delim Delimiter string to explode with
     * @param string $string The string to explode
     * @param bool $removeEmptyValues If set, all empty values will be removed in output
     * @param int $limit If limit is set and positive, the returned array will contain a maximum of limit elements with
     *                   the last element containing the rest of string. If the limit parameter is negative, all components
     *                   except the last -limit are returned.
     * @return ($removeEmptyValues is true ? list<non-empty-string> : list<string>) Exploded values
     *
     * @see \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode()
     */
    public static function trimExplode(string $delim, string $string, bool $removeEmptyValues = false, int $limit = 0): array
    {
        if (!$removeEmptyValues) {
            return array_map(trim(...), $limit === 0 ? explode($delim, $string) : explode($delim, $string, $limit));
        }
        $result = explode($delim, $string);
        if ($limit === 0) {
            $nonEmptyValues = [];
            foreach ($result as $value) {
                $value = trim($value);
                if ($value !== '') {
                    $nonEmptyValues[] = $value;
                }
            }
            return $nonEmptyValues;
        }
        // Remove items that are just whitespace, but leave whitespace intact for the rest.
        $result = array_values(array_filter($result, static fn(string $item): bool => trim($item) !== ''));

        if ($limit < 0) {
            // Trim and return just the first $limit elements and ignore the rest.
            return array_map(trim(...), array_slice($result, 0, $limit));
        }

        // Fold the last length - $limit elements into a single trailing item, then trim and return the result.
        $tail = array_slice($result, $limit - 1);
        $result = array_slice($result, 0, $limit - 1);
        if ($tail) {
            $result[] = implode($delim, $tail);
        }
        return array_map(trim(...), $result);
    }

    /**
     * Creates a directory - including parent directories if necessary and
     * sets permissions on newly created directories.
     *
     * @param string $directory Target directory to create
     * @throws \RuntimeException If directory could not be created
     *
     * @see \TYPO3\CMS\Core\Utility\GeneralUtility::mkdir_deep()
     */
    public static function mkdir_deep(string $directory): void
    {
        // Ensure there is only one slash
        $fullPath = rtrim($directory, '/');
        if ($fullPath !== '' && !is_dir($fullPath)) {
            $firstCreatedPath = static::createDirectoryPath($fullPath . '/');
            if ($firstCreatedPath !== '') {
                static::fixPermissions($firstCreatedPath, true);
            }
        }
    }

    /**
     * Creates directories for the specified paths if they do not exist. This
     * functions sets proper permission mask but does not set proper user and
     * group.
     *
     * @return string Path to the first created directory in the hierarchy
     * @throws \RuntimeException If directory could not be created
     *
     * @see \TYPO3\CMS\Core\Utility\GeneralUtility::createDirectoryPath()
     */
    protected static function createDirectoryPath(string $fullDirectoryPath): string
    {
        $currentPath = $fullDirectoryPath;
        $firstCreatedPath = '';
        $permissionMask = octdec('2775');
        if (!@is_dir($currentPath)) {
            do {
                $firstCreatedPath = $currentPath;
                $separatorPosition = (int)strrpos($currentPath, DIRECTORY_SEPARATOR);
                $currentPath = substr($currentPath, 0, $separatorPosition);
            } while (!is_dir($currentPath) && $separatorPosition > 0);
            $result = @mkdir($fullDirectoryPath, $permissionMask, true);
            // Check existence of directory again to avoid race condition. Directory could have get created by another process between previous is_dir() and mkdir()
            if (!$result && !@is_dir($fullDirectoryPath)) {
                throw new \RuntimeException('Could not create directory "' . $fullDirectoryPath . '"!', 1170251401);
            }
        }
        return $firstCreatedPath;
    }

    /**
     * Sets the file system mode and group ownership of a file or a folder.
     *
     * @param string $path Path of file or folder, must not be escaped. Path can be absolute or relative
     * @param bool $recursive If set, also fixes permissions of files and folders in the folder (if $path is a folder)
     * @return bool TRUE on success, FALSE on error, always TRUE on Windows OS
     *
     * @see \TYPO3\CMS\Core\Utility\GeneralUtility::fixPermissions()
     */
    public static function fixPermissions(string $path, bool $recursive = false): bool
    {
        $result = false;
        if ($path[0] !== '/') {
            throw new \RuntimeException('Path must be absolute', 1573762656);
        }

        if (@is_file($path)) {
            $targetPermissions = '0644';
        } elseif (@is_dir($path)) {
            $targetPermissions = '0755';
        }
        if (!empty($targetPermissions)) {
            // make sure it's always 4 digits
            $targetPermissions = str_pad($targetPermissions, 4, '0', STR_PAD_LEFT);
            $targetPermissions = octdec($targetPermissions);
            // "@" is there because file is not necessarily OWNED by the user
            $result = @chmod($path, $targetPermissions);
        }

        // Call recursive if recursive flag if set and $path is directory
        if ($recursive && @is_dir($path)) {
            $handle = @opendir($path);
            if (is_resource($handle)) {
                while (($file = readdir($handle)) !== false) {
                    $recursionResult = null;
                    if ($file !== '.' && $file !== '..') {
                        if (@is_file($path . '/' . $file)) {
                            $recursionResult = static::fixPermissions($path . '/' . $file);
                        } elseif (@is_dir($path . '/' . $file)) {
                            $recursionResult = static::fixPermissions($path . '/' . $file, true);
                        }
                        if (isset($recursionResult) && !$recursionResult) {
                            $result = false;
                        }
                    }
                }
                closedir($handle);
            }
        }
        return $result;
    }

    /**
     * Fixes a path for windows-backslashes and reduces double-slashes to single slashes
     *
     * @see \TYPO3\CMS\Core\Utility\GeneralUtility::fixWindowsFilePath()
     */
    public static function fixWindowsFilePath(string $theFile): string
    {
        return str_replace(['\\', '//'], '/', $theFile);
    }

    /**
     * Finds all files in a given path and returns them as an array. Each
     * array key is a md5 hash of the full path to the file. This is done because
     * 'some' extensions like the import/export extension depend on this.
     *
     * @param string $path The path to retrieve the files from.
     * @param string $extensionList A comma-separated list of file extensions. Only files of the specified types will be retrieved. When left blank, files of any type will be retrieved.
     * @param bool $prependPath If TRUE, the full path to the file is returned. If FALSE only the file name is returned.
     * @param string $order The sorting order. The default sorting order is alphabetical. Setting $order to 'mtime' will sort the files by modification time.
     * @param string $excludePattern A regular expression pattern of file names to exclude. For example: 'clear.gif' or '(clear.gif|.htaccess)'. The pattern will be wrapped with: '/^' and '$/'.
     * @return array<string, string>|string Array of the files found, or an error message in case the path could not be opened.
     *
     * @see \TYPO3\CMS\Core\Utility\GeneralUtility::getFilesInDir()
     */
    public static function getFilesInDir(string $path, string $extensionList = '', bool $prependPath = false, string $order = '', string $excludePattern = ''): array|string
    {
        $excludePattern = (string)$excludePattern;
        $path = rtrim($path, '/');
        if (!@is_dir($path)) {
            return [];
        }

        $rawFileList = scandir($path);
        if ($rawFileList === false) {
            return 'error opening path: "' . $path . '"';
        }

        $pathPrefix = $path . '/';
        $allowedFileExtensionArray = self::trimExplode(',', $extensionList);
        $extensionList = ',' . str_replace(' ', '', $extensionList) . ',';
        $files = [];
        foreach ($rawFileList as $entry) {
            $completePathToEntry = $pathPrefix . $entry;
            if (!@is_file($completePathToEntry)) {
                continue;
            }

            foreach ($allowedFileExtensionArray as $allowedFileExtension) {
                if (
                    ($extensionList === ',,' || str_ends_with(mb_strtolower($entry), mb_strtolower('.' . $allowedFileExtension)))
                    && ($excludePattern === '' || !preg_match('/^' . $excludePattern . '$/', $entry))
                ) {
                    if ($order !== 'mtime') {
                        $files[] = $entry;
                    } else {
                        // Store the value in the key so we can do a fast asort later.
                        $files[$entry] = filemtime($completePathToEntry);
                    }
                }
            }
        }

        $valueName = 'value';
        if ($order === 'mtime') {
            asort($files);
            $valueName = 'key';
        }

        $valuePathPrefix = $prependPath ? $pathPrefix : '';
        $foundFiles = [];
        /** @noinspection PhpUnusedLocalVariableInspection key is possibly used with "valueName */
        foreach ($files as $key => $value) {
            // Don't change this ever - extensions may depend on the fact that the hash is an md5 of the path! (import/export extension)
            $foundFiles[md5($pathPrefix . ${$valueName})] = $valuePathPrefix . ${$valueName};
        }

        return $foundFiles;
    }

    /**
     * Moves $source file to $destination if uploaded, otherwise try to make a copy
     *
     * @param string $source Source file, absolute path
     * @param string $destination Destination file, absolute path
     * @return bool Returns TRUE if the file was moved.
     *
     * @see \TYPO3\CMS\Core\Utility\GeneralUtility::getFilesInDir()
     */
    public static function upload_copy_move(string $source, string $destination): bool
    {
        $result = false;
        if (is_uploaded_file($source)) {
            // Return the value of move_uploaded_file, and if FALSE the temporary $source is still
            // around so the user can use unlink to delete it:
            $result = move_uploaded_file($source, $destination);
        } else {
            @copy($source, $destination);
        }
        // Change the permissions of the file
        self::fixPermissions($destination);
        // If here the file is copied and the temporary $source is still around,
        // so when returning FALSE the user can try unlink to delete the $source
        return $result;
    }
}
