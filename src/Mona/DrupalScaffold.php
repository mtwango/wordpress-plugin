<?php

namespace Druidfi\Mona;

use Composer\Package\PackageInterface;
use Composer\Script\Event;

class DrupalScaffold
{
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
     * @var object
     */
    protected $scaffoldConfig;

    /**
     * @var string
     */
    protected $webroot;

    public function __construct(Event $event, $scaffoldConfig, $webroot)
    {
        $this->event = $event;
        $this->scaffoldConfig = $scaffoldConfig;
        $this->webroot = $webroot;
    }

    protected function getDrupalPackage(): ?PackageInterface
    {
        return $this->event->getComposer()
                    ->getRepositoryManager()
                    ->getLocalRepository()
                    ->findPackage(Plugin::DRUPAL_PACKAGE, '*');
    }

    public function process(): array
    {
        $drupal = $this->getDrupalPackage();
        $source = $this->event->getComposer()->getInstallationManager()->getInstallPath($drupal);
        $scaffoldFiles = [];

        foreach ($this->scaffoldConfig as $file) {
            $sourcePath = $source . DIRECTORY_SEPARATOR . $file;
            $targetPath = $this->webroot . DIRECTORY_SEPARATOR . $file;

            $scaffoldFiles[] = [
                'source' => $file,
                'sourcePath' => $sourcePath,
                'targetPath' => $targetPath,
            ];
        }

        return $scaffoldFiles;
    }
}
