<?php

namespace Druidfi\Mona;

use Composer\Script\Event;
use Composer\Util\Filesystem;

class DrupalScaffold
{
    const EXTRA_NAME = 'mona-plugin';
    const DRUPAL_PACKAGE = 'drupal/drupal';
    const DRUPAL_SCAFFOLD = 'drupal-scaffold';
    const WEBROOT = 'webroot';
    const WEBROOT_DEFAULT = 'public';

    const DEFAULT = [
        'foo' => 'bar',
    ];

    /**
     * @var Event
     */
    protected $event;

    /**
     * @var array
     */
    protected $extras;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    public function __construct(Event $event, Filesystem $filesystem)
    {
        $this->event = $event;
        $this->extras = $event->getComposer()->getPackage()->getExtra();
        $this->fileSystem = $filesystem;
    }

    protected function getDrupalPackage()
    {
        $package =  $this->event->getComposer()
                    ->getRepositoryManager()
                    ->getLocalRepository()
                    ->findPackage(self::DRUPAL_PACKAGE, '*');

        return $package;
    }

    protected function getScaffoldConfig()
    {
        if (isset($this->extras[self::EXTRA_NAME][self::DRUPAL_SCAFFOLD])) {
            return $this->extras[self::EXTRA_NAME][self::DRUPAL_SCAFFOLD];
        }

        return self::DEFAULT;
    }

    protected function getWebroot(): string
    {
        if (isset($this->extras[self::EXTRA_NAME][self::WEBROOT])) {
            return $this->extras[self::EXTRA_NAME][self::WEBROOT];
        }

        return self::WEBROOT_DEFAULT;
    }

    public function process(): array
    {
        $config = $this->getScaffoldConfig();
        $drupal = $this->getDrupalPackage();
        $installPath = $this->event->getComposer()->getInstallationManager()->getInstallPath($drupal);

        $this->event->getIO()->write('Install path = '. $installPath);
        $this->event->getIO()->write('Web root = '. $this->getWebroot());

        foreach ($config as $source => $target) {
            $this->event->getIO()->write($source .' >> '. $target);
        }
    }
}
