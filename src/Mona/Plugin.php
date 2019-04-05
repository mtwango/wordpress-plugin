<?php

namespace Druidfi\Mona;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Composer\Util\Filesystem;
use Druidfi\Mona\Exception\LinkDirectoryException;
use Druidfi\Mona\Exception\RuntimeException;
use Exception;

/**
 * Class Plugin
 *
 * @author  Dmitry Panychev <thor_work@yahoo.com>
 *
 */
class Plugin implements PluginInterface
{
    const DRUPAL_PACKAGE = 'drupal/drupal';
    const DRUPAL_REPOSITORY = 'https://packages.drupal.org/7';
    const EXTRA_NAME = 'mona-plugin';
    const WEBROOT = 'webroot';
    const WEBROOT_DEFAULT = 'public';

    /**
     * @var array
     */
    protected $extra;

    /**
     * @param Composer    $composer
     * @param IOInterface $io
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $eventDispatcher = $composer->getEventDispatcher();
        $this->extra = $composer->getPackage()->getExtra();
        $this->preConfigureExtra();
        $composer->getPackage()->setExtra($this->extra);

        $repository = $composer->getRepositoryManager()->createRepository('composer', [
            'url' => self::DRUPAL_REPOSITORY,
        ]);

        $composer->getRepositoryManager()->addRepository($repository);

        $eventDispatcher->addListener(ScriptEvents::POST_INSTALL_CMD, $this->monafy());
        $eventDispatcher->addListener(ScriptEvents::POST_UPDATE_CMD, $this->monafy());
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
            $fileSystem = new Filesystem();
            $drupalScaffold = new DrupalScaffold($event, $fileSystem, $this->extra);
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
                            '  - Copied <comment>%s</comment> to <comment>%s</comment> <info>[OK]</info>',
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
            $symlinks = $factory->process();

            foreach ($symlinks as $symlink) {
                try {
                    if (!$processor->processSymlink($symlink)) {
                        throw new RuntimeException('Unknown error');
                    }

                    $event
                        ->getIO()
                        ->write(sprintf(
                            '  - Symlinking <comment>%s</comment> to <comment>%s</comment> <info>[OK]</info>',
                            $symlink->getOriginalLink(),
                            $symlink->getOriginalTarget()
                        ));
                } catch (LinkDirectoryException $exception) {
                    $event
                        ->getIO()
                        ->write(sprintf(
                            '  - Symlink from <comment>%s</comment> to <comment>%s</comment> already exists <info>[OK]</info>',
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
     *
     * @return array
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
                "vendor/drupal" => ["type:drupal-core"],
                'vendor/drupal_libraries/{$name}' => ["type:drupal-library"],
                $webroot .'/sites/all/modules/contrib/{$name}' => ["type:drupal-module"],
                $webroot .'/sites/all/themes/{$name}' => ["type:drupal-theme"],
                $webroot .'/sites/all/drush/{$name}' => ["type:drupal-drush"],
            ];
        }
    }
}
