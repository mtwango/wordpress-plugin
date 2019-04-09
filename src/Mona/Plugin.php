<?php

namespace Druidfi\Mona;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Package\Package;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Composer\Util\Filesystem;
use Druidfi\Mona\Exception\LinkDirectoryException;
use Druidfi\Mona\Exception\RuntimeException;
use Exception;

/**
 * Class Plugin
 */
class Plugin implements PluginInterface, EventSubscriberInterface
{
    const DRUPAL_PACKAGE = 'drupal/drupal';
    const DRUPAL_SCAFFOLD = 'drupal-scaffold';
    const EXTRA_NAME = 'mona-plugin';
    const WEBROOT = 'webroot';
    const WEBROOT_DEFAULT = 'public';

    /**
     * @var array
     */
    protected $extra;

    /**
     * @var IOInterface
     */
    protected $io;

    /**
     * @param Composer    $composer
     * @param IOInterface $io
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $io->write('<info>Mona dependencies installed, now install project dependencies</info>');

        $this->io = $io;

        $eventDispatcher = $composer->getEventDispatcher();
        $this->extra = $composer->getPackage()->getExtra();
        $this->preConfigureExtra();
        $composer->getPackage()->setExtra($this->extra);

        $eventDispatcher->addListener(ScriptEvents::POST_INSTALL_CMD, $this->monafy(), 100);
        $eventDispatcher->addListener(ScriptEvents::POST_UPDATE_CMD, $this->monafy(), 100);
    }

    public static function getSubscribedEvents()
    {
        return [
            PackageEvents::PRE_PACKAGE_INSTALL => 'checkForDrupalLibrary',
            PackageEvents::PRE_PACKAGE_UPDATE => 'checkForDrupalLibrary',
        ];
    }

    public function checkForDrupalLibrary(PackageEvent $event)
    {
        try {
            $package = $this->getTargetPackage($event->getOperation());
            $package_name = $package->getName();
            $libraries = $this->extra[self::EXTRA_NAME]['libraries'] ?? [];

            if (in_array($package_name, $libraries)) {
                $event->getIO()->write('  - Changing <info>' . $package_name . '</info> type to <comment>drupal-library</comment>');
                $package->setType('drupal-library');
            }
        } catch (Exception $e) {
            // Do nothing, we don't care about other operation types
        }
    }

    /**
     * Get target package
     *
     * @param $operation
     * @return Package
     * @throws Exception
     */
    protected function getTargetPackage($operation): Package
    {
        if ($operation instanceof InstallOperation) {
            return $operation->getPackage();
        } elseif ($operation instanceof UpdateOperation) {
            return $operation->getTargetPackage();
        }

        throw new Exception('Unknown operation: ' . get_class($operation));
    }

    protected function getScaffoldConfig()
    {
        if (isset($this->extra[self::EXTRA_NAME][self::DRUPAL_SCAFFOLD])) {
            return $this->extra[self::EXTRA_NAME][self::DRUPAL_SCAFFOLD];
        }

        return DrupalScaffold::DEFAULT;
    }

    /**
     * Get webroot folder name
     *
     * @return string
     */
    protected function getWebroot(): string
    {
        if (isset($this->extra[self::EXTRA_NAME][self::WEBROOT])) {
            return $this->extra[self::EXTRA_NAME][self::WEBROOT];
        }

        return self::WEBROOT_DEFAULT;
    }

    /**
     * @return callable
     */
    protected function monafy(): callable
    {
        return function (Event $event) {
            $webroot = $this->getWebroot();
            $fileSystem = new Filesystem();
            $drupalScaffold = new DrupalScaffold($event, $this->getScaffoldConfig(), $webroot);
            $factory = new SymlinksFactory($event, $fileSystem);
            $processor = new SymlinksProcessor($fileSystem);

            $event->getIO()->write('<info>Mona: Copying Drupal 7 core files and folders.</info>');
            $scaffoldFiles = $drupalScaffold->process();

            foreach ($scaffoldFiles as $file) {
                try {
                    $fileSystem->copy($file['sourcePath'], $file['targetPath']);

                    $event
                        ->getIO()
                        ->write(sprintf(
                            '  - Copying <comment>%s</comment> to <comment>%s</comment>',
                            $file['sourcePath'],
                            $file['targetPath']
                        ));
                } catch (Exception $exception) {
                    $event
                        ->getIO()
                        ->writeError(sprintf(
                            '  - Copying scaffold file <comment>%s</comment> failed: %s <error>[ERROR]</error>',
                            $file['source'],
                            $exception->getMessage()
                        ));
                }
            }

            $event->getIO()->write('<info>Mona: symlinking files and folders.</info>');
            $symlinks = $factory->process($webroot);

            foreach ($symlinks as $symlink) {
                try {
                    if (!$processor->processSymlink($symlink)) {
                        throw new RuntimeException('Unknown error');
                    }

                    $event
                        ->getIO()
                        ->write(sprintf(
                            '  - Symlinking <comment>%s</comment> to <comment>%s</comment>',
                            $symlink->getOriginalLink(),
                            $symlink->getOriginalTarget()
                        ));
                } catch (LinkDirectoryException $exception) {
                    $event
                        ->getIO()
                        ->write(sprintf(
                            '  - Symlink from <comment>%s</comment> to <comment>%s</comment> already exists',
                            $symlink->getOriginalLink(),
                            $symlink->getOriginalTarget()
                        ));
                } catch (Exception $exception) {
                    $event
                        ->getIO()
                        ->writeError(sprintf(
                            '  - Symlinking <comment>%s</comment> to <comment>%s</comment> failed: %s <error>[ERROR]</error>',
                            $symlink->getLink(),
                            $symlink->getTarget(),
                            $exception->getMessage()
                        ));
                }
            }
        };
    }

    /**
     * Pre-configure other Composer plugins
     */
    protected function preConfigureExtra()
    {
        $webroot = $this->getWebroot();

        // If root package does not have extra.composer-exit-on-patch-failure
        if (!isset($this->extra['composer-exit-on-patch-failure'])) {
            $this->extra['composer-exit-on-patch-failure'] = true;
        }

        // If root package does not have extra.installer-paths
        if (!isset($this->extra['installer-paths'])) {
            $this->extra['installer-paths'] = [
                'vendor/drupal' => ['type:drupal-core'],
                $webroot .'/sites/all/libraries/{$name}' => ["type:drupal-library"],
                $webroot .'/sites/all/modules/contrib/{$name}' => ["type:drupal-module"],
                $webroot .'/sites/all/themes/contrib/{$name}' => ["type:drupal-theme"],
                $webroot .'/sites/all/drush/{$name}' => ["type:drupal-drush"],
            ];
        }
    }
}
