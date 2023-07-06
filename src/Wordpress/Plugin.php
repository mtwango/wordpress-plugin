<?php

namespace Mtwango\Wordpress;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Package\Package;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Composer\Util\Filesystem;
use Exception;
use Mtwango\Wordpress\Exception\LinkDirectoryException;
use Mtwango\Wordpress\Exception\RuntimeException;

/**
 * Class Plugin.
 */
class Plugin implements PluginInterface, EventSubscriberInterface {
  const WORDPRESS_PACKAGE = 'johnpbloch/wordpress-core';
  const WORDPRESS_SCAFFOLD = 'wordpress-scaffold';
  const EXTRA_NAME = 'wordpress-plugin';
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
   * @param Composer $composer
   * @param IOInterface $io
   */
  public function activate(Composer $composer, IOInterface $io): void {
    $io->write('<info>Plugin dependencies installed, now install project dependencies</info>');

    $this->io = $io;

    $eventDispatcher = $composer->getEventDispatcher();
    $this->extra = $composer->getPackage()->getExtra();
    $this->preConfigureExtra();
    $composer->getPackage()->setExtra($this->extra);

    $eventDispatcher->addListener(ScriptEvents::POST_INSTALL_CMD, $this->wordpressfy(), 100);
    $eventDispatcher->addListener(ScriptEvents::POST_UPDATE_CMD, $this->wordpressfy(), 100);
  }

  public static function getSubscribedEvents(): array {
    return [];
  }

  /**
   * Get target package.
   *
   * @param $operation
   *
   * @throws Exception
   *
   * @return Package
   */
  protected function getTargetPackage($operation): Package {
    if ($operation instanceof InstallOperation) {
      return $operation->getPackage();
    }
    if ($operation instanceof UpdateOperation) {
      return $operation->getTargetPackage();
    }

    throw new Exception('Unknown operation: ' . get_class($operation));
  }

  protected function getScaffoldConfig() {
    return $this->extra[self::EXTRA_NAME][self::WORDPRESS_SCAFFOLD] ?? WordpressScaffold::DEFAULT;
  }

  /**
   * Get webroot folder name.
   *
   * @return string
   */
  protected function getWebroot(): string {
    return $this->extra[self::EXTRA_NAME][self::WEBROOT] ?? self::WEBROOT_DEFAULT;
  }

  /**
   * @return callable
   */
  protected function wordpressfy(): callable {
    return function (Event $event) {
      $webroot = $this->getWebroot();
      $fileSystem = new Filesystem();
      $wordpressScaffold = new WordpressScaffold($event, $this->getScaffoldConfig(), $webroot);
      $factory = new SymlinksFactory($event, $fileSystem);
      $processor = new SymlinksProcessor($fileSystem);

      $event->getIO()->write('<info>Plugin: Copying WordPress core files and folders.</info>');
      $scaffoldFiles = $wordpressScaffold->process();

      foreach ($scaffoldFiles as $file) {
        try {
          $fileSystem->copy($file['sourcePath'], $file['targetPath']);

          $event->getIO()->write(sprintf(
            '  - Copying <comment>%s</comment> to <comment>%s</comment>',
            $file['sourcePath'],
            $file['targetPath']
          ));
        }
        catch (Exception $exception) {
          $event->getIO()->writeError(sprintf(
            '  - Copying scaffold file <comment>%s</comment> failed: %s <error>[ERROR]</error>',
            $file['source'],
            $exception->getMessage()
          ));
        }
      }

      $event->getIO()->write('<info>Plugin: symlinking files and folders.</info>');
      $symlinks = $factory->process($webroot);

      foreach ($symlinks as $symlink) {
        try {
          if (!$processor->processSymlink($symlink)) {
            throw new RuntimeException('Unknown error');
          }

          $event->getIO()->write(sprintf(
            '  - Symlinking <comment>%s</comment> to <comment>%s</comment>',
            $symlink->getOriginalLink(),
            $symlink->getOriginalTarget()
          ));
        }
        catch (LinkDirectoryException $exception) {
          $event->getIO()->write(sprintf(
            '  - Symlink from <comment>%s</comment> to <comment>%s</comment> already exists',
            $symlink->getOriginalLink(),
            $symlink->getOriginalTarget()
          ));
        }
        catch (Exception $exception) {
          $event->getIO()->writeError(sprintf(
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
   * Pre-configure other Composer plugins.
   */
  protected function preConfigureExtra(): void {
    $webroot = $this->getWebroot();

    // If root package does not have extra.composer-exit-on-patch-failure.
    if (!isset($this->extra['composer-exit-on-patch-failure'])) {
      $this->extra['composer-exit-on-patch-failure'] = TRUE;
    }

    // If root package does not have extra.installer-paths.
    if (!isset($this->extra['installer-paths'])) {
      $this->extra['installer-paths'] = [
        'vendor/johnpbloch/wordpress-core' => ['type:wordpress-core'],
        $webroot . '/wp-content/plugins/{$name}' => ['type:wordpress-plugin'],
        $webroot . '/wp-content/themes/{$name}' => ['type:wordpress-theme'],
      ];
    }
  }

  /**
   * {@inheritDoc}
   */
  public function deactivate(Composer $composer, IOInterface $io) {
  }

  /**
   * {@inheritDoc}
   */
  public function uninstall(Composer $composer, IOInterface $io) {
  }

}
