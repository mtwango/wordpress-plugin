<?php

namespace Druidfi\Mona;

use Composer\Script\Event;
use Composer\Util\Filesystem;

class DrupalScaffold
{
    const DRUPAL_SCAFFOLD = 'drupal-scaffold';

    const DEFAULT = [
        'authorize.php',
        'cron.php',
        'index.php',
        'robots.txt',
        'update.php',
        'xmlrpc.php',
        'includes',
        'misc',
        'modules',
        'profiles',
        'themes',
    ];

    /**
     * @var Event
     */
    protected $event;

    /**
     * @var array
     */
    protected $extra;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    public function __construct(Event $event, Filesystem $filesystem, $extra)
    {
        $this->event = $event;
        $this->extra = $extra;
        $this->fileSystem = $filesystem;
    }

    protected function getDrupalPackage()
    {
        $package =  $this->event->getComposer()
                    ->getRepositoryManager()
                    ->getLocalRepository()
                    ->findPackage(Plugin::DRUPAL_PACKAGE, '*');

        return $package;
    }

    protected function getScaffoldConfig()
    {
        if (isset($this->extra[Plugin::EXTRA_NAME][self::DRUPAL_SCAFFOLD])) {
            return $this->extra[Plugin::EXTRA_NAME][self::DRUPAL_SCAFFOLD];
        }

        return self::DEFAULT;
    }

    protected function getWebroot(): string
    {
        if (isset($this->extra[Plugin::EXTRA_NAME][Plugin::WEBROOT])) {
            return $this->extra[Plugin::EXTRA_NAME][Plugin::WEBROOT];
        }

        return Plugin::WEBROOT_DEFAULT;
    }

    public function process(): array
    {
        $config = $this->getScaffoldConfig();
        $drupal = $this->getDrupalPackage();
        $source = $this->event->getComposer()->getInstallationManager()->getInstallPath($drupal);
        $webroot = $this->getWebroot();
        $scaffoldFiles = [];

        foreach ($config as $file) {
            $sourcePath = $source . DIRECTORY_SEPARATOR . $file;
            $targetPath = $webroot . DIRECTORY_SEPARATOR . $file;

            $scaffoldFiles[] = [
                'source' => $file,
                'sourcePath' => $sourcePath,
                'targetPath' => $targetPath,
            ];
        }

        return $scaffoldFiles;
    }
}
