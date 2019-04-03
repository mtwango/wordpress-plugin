<?php

namespace Druidfi\Mona;

use Composer\Util\Filesystem;
use Druidfi\Mona\Exception\LinkDirectoryException;
use Druidfi\Mona\Exception\RuntimeException;
use Exception;
use function is_dir;

class SymlinksProcessor
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @param Symlink $symlink
     *
     * @throws RuntimeException
     * @return bool
     */
    public function processSymlink(Symlink $symlink): bool
    {
        if ($symlink->isForceCreate() && $this->isToUnlink($symlink->getLink())) {
            try {
                if (is_dir($symlink->getLink())) {
                    $result = $this->filesystem->removeDirectory($symlink->getLink());
                } else {
                    $result = $this->filesystem->remove($symlink->getLink());
                }
                if (!$result) {
                    throw new RuntimeException('Unknown error');
                }
            } catch (Exception $exception) {
                throw new RuntimeException(sprintf(
                    'Cant unlink %s: %s',
                    $symlink->getLink(),
                    $exception->getMessage()
                ));
            }
        }

        if ($this->isToUnlink($symlink->getLink())) {
            throw new LinkDirectoryException('Link ' . $symlink->getLink() . ' already exists');
        }

        if ($symlink->isAbsolutePath()) {
            return @symlink($symlink->getTarget(), $symlink->getLink());
        }
        return $this->filesystem->relativeSymlink($symlink->getTarget(), $symlink->getLink());
    }

    protected function isToUnlink(string $path): bool
    {
        return
            file_exists($path) ||
            is_dir($path) ||
            is_link($path);
    }
}
