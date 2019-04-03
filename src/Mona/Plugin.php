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
            $factory = new SymlinksFactory($event, $fileSystem);
            $processor = new SymlinksProcessor($fileSystem);

            $symlinks = $factory->process();
            foreach ($symlinks as $symlink) {
                try {
                    if (!$processor->processSymlink($symlink)) {
                        throw new RuntimeException('Unknown error');
                    }

                    // Test colors
                    $event
                        ->getIO()
                        ->write('<error>error</error><info>info</info><warning>warning</warning>');

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
                            '  - Symlinking <comment>%s</comment> to <comment>%s</comment> - %s',
                            $symlink->getOriginalLink(),
                            $symlink->getOriginalTarget(),
                            'Already there'
                        ));
                } catch (Exception $exception) {
                    $event
                        ->getIO()
                        ->writeError(sprintf(
                            '  - Symlinking <comment>%s</comment> to <comment>%s</comment> - %s',
                            $symlink->getLink(),
                            $symlink->getTarget(),
                            $exception->getMessage()
                        ));
                }
            }
        };
    }
}
