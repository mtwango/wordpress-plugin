<?php

namespace Mtwango\Wordpress;

use Composer\Package\PackageInterface;
use Composer\Script\Event;

class WordpressScaffold
{
    const DEFAULT = [
        'index.php',
        'wp-activate.php',
        'wp-blog-header.php',
        'wp-comments-post.php',
        'wp-config-sample.php',
        'wp-cron.php',
        'wp-links-opml.php',
        'wp-load.php',
        'wp-login.php',
        'wp-mail.php',
        'wp-settings.php',
        'wp-signup.php',
        'wp-trackback.php',
        'xmlrpc.php',
        'wp-admin',
        'wp-content',
        'wp-includes',
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

    protected function getWordpressPackage(): ?PackageInterface
    {
        return $this->event->getComposer()
                    ->getRepositoryManager()
                    ->getLocalRepository()
                    ->findPackage(Plugin::WORDPRESS_PACKAGE, '*');
    }

    public function process(): array
    {
        $wordpress = $this->getWordpressPackage();
        $source = $this->event->getComposer()->getInstallationManager()->getInstallPath($wordpress);
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
