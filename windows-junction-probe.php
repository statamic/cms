<?php

// Throwaway probe. Confirms how PHP on Windows treats junctions (mklink /J, what
// Laravel's Filesystem::link() creates for directories) versus real symlinks
// (PHP's symlink(), what TemplateFolderTest uses), and whether unlink() can
// remove either of them.

$base = __DIR__.'/probe-tmp';

function probe(string $label, string $path): void
{
    clearstatcache(true, $path);

    printf(
        "  %-10s exists=%-5s is_dir=%-5s is_link=%-5s filetype=%-9s realpath=%s\n",
        $label,
        var_export(file_exists($path), true),
        var_export(is_dir($path), true),
        var_export(is_link($path), true),
        var_export(@filetype($path), true),
        var_export(realpath($path), true),
    );
}

// Candidate fix. hasChildren() takes the "not a link" branch for junctions and
// returns S_ISDIR() of an lstat mode that is neither, so it never reaches the
// FOLLOW_SYMLINKS branch. is_dir() is true for junctions, so fall back to it.
class ViewDirectoryIterator extends RecursiveDirectoryIterator
{
    public function hasChildren(bool $allowLinks = false): bool
    {
        return parent::hasChildren($allowLinks) || is_dir($this->getPathname());
    }
}

echo 'PHP '.PHP_VERSION.' on '.PHP_OS_FAMILY.PHP_EOL.PHP_EOL;

@mkdir($base.'/views', 0777, true);
@mkdir($base.'/target/three', 0777, true);
@mkdir($base.'/empty-target', 0777, true);
file_put_contents($base.'/target/tango.html', '');
file_put_contents($base.'/target/three/uniform.html', '');

echo '== 1. Creating the links =='.PHP_EOL;

$junction = $base.'/views/junction';
exec('mklink /J '.escapeshellarg($junction).' '.escapeshellarg($base.'/target'), $output, $exit);
echo '  mklink /J exit code: '.$exit.PHP_EOL;
echo '  mklink /J output:    '.implode(' | ', $output).PHP_EOL;

$symlink = $base.'/views/symlinked';
echo '  symlink() returned:  '.var_export(@symlink($base.'/target', $symlink), true).PHP_EOL;

$emptyJunction = $base.'/views/empty-junction';
exec('mklink /J '.escapeshellarg($emptyJunction).' '.escapeshellarg($base.'/empty-target'), $output2, $exit2);
echo '  mklink /J (empty) exit code: '.$exit2.PHP_EOL;

echo PHP_EOL.'== 2. How PHP sees them =='.PHP_EOL;
probe('junction', $junction);
probe('symlinked', $symlink);
probe('real dir', $base.'/target');

echo PHP_EOL.'== 3. RecursiveDirectoryIterator, SKIP_DOTS|FOLLOW_SYMLINKS, LEAVES_ONLY =='.PHP_EOL;
echo '   (this is exactly what TemplatesController::index() does)'.PHP_EOL;

$found = [];

foreach (new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(
        $base.'/views',
        FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS
    )
) as $file) {
    $found[] = str_replace($base.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR, '', $file->getPathname());
}

sort($found);

foreach ($found as $path) {
    echo '  '.$path.PHP_EOL;
}

echo PHP_EOL.'== 3b. Candidate fix: override hasChildren() to fall back to is_dir() =='.PHP_EOL;

$fixed = [];

foreach (new RecursiveIteratorIterator(
    new ViewDirectoryIterator(
        $base.'/views',
        FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS
    )
) as $file) {
    $fixed[] = str_replace($base.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR, '', $file->getPathname());
}

sort($fixed);

foreach ($fixed as $path) {
    echo '  '.$path.PHP_EOL;
}

echo PHP_EOL.'== 4. Can unlink() remove them? (this is what deleteDirectory() calls) =='.PHP_EOL;

echo '  unlink(symlinked):  '.var_export(@unlink($symlink), true).PHP_EOL;
clearstatcache(true, $symlink);
echo '    still present:    '.var_export(file_exists($symlink) || is_link($symlink), true).PHP_EOL;

echo '  unlink(junction):   '.var_export(@unlink($junction), true).PHP_EOL;
clearstatcache(true, $junction);
echo '    still present:    '.var_export(file_exists($junction) || is_link($junction), true).PHP_EOL;

echo '  rmdir(junction):    '.var_export(@rmdir($junction), true).PHP_EOL;
clearstatcache(true, $junction);
echo '    still present:    '.var_export(file_exists($junction) || is_link($junction), true).PHP_EOL;

echo PHP_EOL.'== Predictions =='.PHP_EOL;
echo '  mklink /J exits 0; is_dir(junction) is false but is_dir(symlinked) is true;'.PHP_EOL;
echo '  the iterator yields "junction" and "empty-junction" as leaves but descends'.PHP_EOL;
echo '  into "symlinked"; unlink() fails on both links, rmdir() removes the junction.'.PHP_EOL;
echo '  With the candidate fix, 3b descends into the junction too, yielding'.PHP_EOL;
echo '  "junction\tango.html" (virtual path preserved, not resolved to "target\..."),'.PHP_EOL;
echo '  and "empty-junction" disappears entirely rather than becoming a leaf.'.PHP_EOL;
