<?php
declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Utility;

class FileHandling
{

    /**
     * Returns TRUE if $haystack begins with $needle.
     * The input string is not trimmed before and search is done case sensitive.
     *
     * @param string $haystack Full string to check
     * @param string $needle Reference string which must be found as the "first part" of the full string
     * @return bool TRUE if $needle was found to be equal to the first part of $haystack
     * @throws \InvalidArgumentException
     */
    public static function beginsWith($haystack, $needle)
    {
        // Sanitize $haystack and $needle
        if (is_array($haystack) || is_object($haystack) || $haystack === null || (string)$haystack != $haystack) {
            throw new \InvalidArgumentException(
                '$haystack can not be interpreted as string',
                1347135546
            );
        }
        if (is_array($needle) || is_object($needle) || (string)$needle != $needle || strlen($needle) < 1) {
            throw new \InvalidArgumentException(
                '$needle can not be interpreted as string or has zero length',
                1347135547
            );
        }
        $haystack = (string)$haystack;
        $needle = (string)$needle;
        return $needle !== '' && strpos($haystack, $needle) === 0;
    }


    /**
     * Wrapper function for rmdir, allowing recursive deletion of folders and files
     *
     * @param string $path Absolute path to folder, see PHP rmdir() function. Removes trailing slash internally.
     * @param bool $removeNonEmpty Allow deletion of non-empty directories
     * @return bool TRUE if operation was successful
     */
    public static function rmdir($path, $removeNonEmpty = false)
    {
        $OK = false;
        // Remove trailing slash
        $path = preg_replace('|/$|', '', $path);
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
     *
     * @param string $path Path to list directories from
     * @return array Returns an array with the directory entries as values. If no path, the return value is nothing.
     */
    public static function get_dirs($path)
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
     */
    public static function copyDirectory($source, $destination)
    {
        static::mkdir_deep($destination);
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        /** @var \SplFileInfo $item */
        foreach ($iterator as $item) {
            $target = $destination . '/' . static::fixWindowsFilePath($iterator->getSubPathName());
            if ($item->isDir()) {
                static::mkdir($target);
            } else {
                static::upload_copy_move(static::fixWindowsFilePath($item->getPathname()), $target);
            }
        }
    }


    /**
     * Explodes a string and trims all values for whitespace in the end.
     * If $onlyNonEmptyValues is set, then all blank ('') values are removed.
     *
     * @param string $delim Delimiter string to explode with
     * @param string $string The string to explode
     * @param bool $removeEmptyValues If set, all empty values will be removed in output
     * @param int $limit If limit is set and positive, the returned array will contain a maximum of limit elements with
     *                   the last element containing the rest of string. If the limit parameter is negative, all components
     *                   except the last -limit are returned.
     * @return array Exploded values
     */
    public static function trimExplode($delim, $string, $removeEmptyValues = false, $limit = 0)
    {
        $result = explode($delim, $string);
        if ($removeEmptyValues) {
            $temp = [];
            foreach ($result as $value) {
                if (trim($value) !== '') {
                    $temp[] = $value;
                }
            }
            $result = $temp;
        }
        if ($limit > 0 && count($result) > $limit) {
            $lastElements = array_splice($result, $limit - 1);
            $result[] = implode($delim, $lastElements);
        } elseif ($limit < 0) {
            $result = array_slice($result, 0, $limit);
        }
        $result = array_map('trim', $result);
        return $result;
    }

    public static function mkdir_deep($directory)
    {
        if (!is_string($directory)) {
            throw new \InvalidArgumentException('The specified directory is of type "' . gettype($directory) . '" but a string is expected.', 1303662955);
        }
        // Ensure there is only one slash
        $fullPath = rtrim($directory, '/') . '/';
        if ($fullPath !== '/' && !is_dir($fullPath)) {
            $firstCreatedPath = static::createDirectoryPath($fullPath);
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
     * @static
     * @param string $fullDirectoryPath
     * @return string Path to the the first created directory in the hierarchy
     * @throws \RuntimeException If directory could not be created
     * @see \TYPO3\CMS\Core\Utility\GeneralUtility::mkdir_deep
     */
    protected static function createDirectoryPath($fullDirectoryPath)
    {
        $currentPath = $fullDirectoryPath;
        $firstCreatedPath = '';
        $permissionMask = octdec('2775');
        if (!@is_dir($currentPath)) {
            do {
                $firstCreatedPath = $currentPath;
                $separatorPosition = strrpos($currentPath, DIRECTORY_SEPARATOR);
                $currentPath = substr($currentPath, 0, $separatorPosition);
            } while (!is_dir($currentPath) && $separatorPosition !== false);
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
     * @return mixed TRUE on success, FALSE on error, always TRUE on Windows OS
     */
    public static function fixPermissions($path, $recursive = false)
    {

        $result = false;
        // Make path absolute
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
            $handle = opendir($path);
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
     * Writes $content to the file $file
     *
     * @param string $file Filepath to write to
     * @param string $content Content to write
     * @param bool $changePermissions If TRUE, permissions are forced to be set
     * @return bool TRUE if the file was successfully opened and written to.
     */
    public static function writeFile($file, $content, $changePermissions = false)
    {
        if (!@is_file($file)) {
            $changePermissions = true;
        }
        if ($fd = fopen($file, 'wb')) {
            $res = fwrite($fd, $content);
            fclose($fd);
            if ($res === false) {
                return false;
            }
            // Change the permissions only if the file has just been created
            if ($changePermissions) {
                static::fixPermissions($file);
            }
            return true;
        }
        return false;
    }

    /**
     * Fixes a path for windows-backslashes and reduces double-slashes to single slashes
     *
     * @param string $theFile File path to process
     * @return string
     */
    public static function fixWindowsFilePath($theFile)
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
     * @return array|string Array of the files found, or an error message in case the path could not be opened.
     */
    public static function getFilesInDir($path, $extensionList = '', $prependPath = false, $order = '', $excludePattern = '')
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
                    ($extensionList === ',,' || stripos($extensionList, ',' . substr($entry, strlen($allowedFileExtension) * -1, strlen($allowedFileExtension)) . ',') !== false)
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
     * @see upload_to_tempfile()
     */
    public static function upload_copy_move($source, $destination)
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
