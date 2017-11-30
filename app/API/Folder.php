<?php

namespace Statamic\API;

use Statamic\Filesystem\FolderAccessor;

/**
 * Manipulating folders on the local filesystem
 *
 * @method static \Illuminate\Contracts\Filesystem\Filesystem filesystem()
 * @method static bool exists(string $folder)
 * @method static bool make(string $folder)
 * @method static array getFiles(string $folder, bool $recursive = false)
 * @method static array getFilesRecursively(string $folder)
 * @method static array getFilesByType(string $folder, array|string $extension, bool $recursive = false)
 * @method static array getFilesByTypeRecursively(string $folder, array|string $extension)
 * @method static array getFolders(string $folder, bool $recursive = false)
 * @method static array getFoldersRecursively(string $folder)
 * @method static int lastModified(string $folder)
 * @method static bool isEmpty(string $folder)
 * @method static void copy(string $src, string $dest, bool $overwrite = false)
 * @method static void rename(string $old_folder, string $new_folder)
 * @method static bool delete(string $folder)
 * @method static void deleteEmptySubfolders(string $folder)
 */
class Folder extends File
{
    //
}
