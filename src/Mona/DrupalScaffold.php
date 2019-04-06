<?php

namespace Druidfi\Mona;

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
     * @var array
     */
    protected $scaffoldConfig;

    /**
     * @var array
     */
    protected $webroot;

    public function __construct(Event $event, array $scaffoldConfig, $webroot)
    {
        $this->event = $event;
        $this->scaffoldConfig = $scaffoldConfig;
        $this->webroot = $webroot;
    }

    protected function getDrupalPackage()
    {
        $package =  $this->event->getComposer()
                    ->getRepositoryManager()
                    ->getLocalRepository()
                    ->findPackage(Plugin::DRUPAL_PACKAGE, '*');

        return $package;
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
