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
    /**
     * @param Composer    $composer
     * @param IOInterface $io
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $eventDispatcher = $composer->getEventDispatcher();
        $eventDispatcher->addListener(ScriptEvents::POST_INSTALL_CMD, $this->createLinks());
        $eventDispatcher->addListener(ScriptEvents::POST_UPDATE_CMD, $this->createLinks());
    }

    /**
     * @return callable
     */
    protected function createLinks(): callable
    {
        return function (Event $event) {
            $fileSystem = new Filesystem();
            $drupalScaffold = new DrupalScaffold($event, $fileSystem);
            $factory = new SymlinksFactory($event, $fileSystem);
            $processor = new SymlinksProcessor($fileSystem);

            $event->getIO()->write('<info>Mona: Drupal scaffold files.</info>');
            $scaffoldFiles = $drupalScaffold->process();

            foreach ($scaffoldFiles as $file) {
                try {
                    $fileSystem->copy($file['sourcePath'], $file['targetPath']);
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
}
