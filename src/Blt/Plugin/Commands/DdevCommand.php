<?php

namespace Lcatlett\BltDdev\Blt\Plugin\Commands;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;
use Robo\Contract\VerbosityThresholdInterface;

/**
 * Defines commands related to ddev.
 */
class DdevCommand extends BltTasks {

  /**
   * Initializes default ddev configs for this project.
   *
   * @command recipes:ddev
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  public function ddevProjectInit() {
    // Copy .ddev folder from template into the current project root.
    $result = $this->taskCopyDir([$this->getConfigValue('repo.root') . '/vendor/lcatlett/blt-ddev/config/.ddev' => $this->getConfigValue('repo.root') . '/.ddev'])
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    if (!$result->wasSuccessful()) {
      throw new BltException("Could not initialize ddev configuration.");
    }

    // Copy BLT local config template (aka example.local.blt.yml).
    $result = $this->taskFilesystemStack()
      ->copy($this->getConfigValue('repo.root') . '/vendor/lcatlett/blt-ddev/config/blt/example.local.blt.yml', $this->getConfigValue('repo.root') . '/blt/example.local.blt.yml', true)
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    if (!$result->wasSuccessful()) {
      throw new BltException("Could not copy example.local.blt.yml template to blt folder.");
    }
    $result = $this->taskFilesystemStack()
    ->copy($this->getConfigValue('blt.config-files.example-local'), $this->getConfigValue('blt.config-files.local'), true)
    ->stopOnFail()
    ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
    ->run();

  if (!$result->wasSuccessful()) {
    $filepath = $this->getInspector()->getFs()->makePathRelative($this->getConfigValue('blt.config-files.local'), $this->getConfigValue('repo.root'));
    throw new BltException("Unable to create $filepath.");
  }

    $this->ddevInit();
    $this->ddevConfig();

    $this->say("<info>ddev project and BLT config were successfully initialized.</info>");

    $execute_init = $this->confirm('Would you like to run ddev start to provision your ddev stack?', true);
    if ($execute_init) {
      $this->taskExec('ddev start')->run();
    }
  }


  /**
   * Initializes ddev configuration for blt project.
   *
   * @command recipes:ddev:init
   *
   */
  public function ddevInit() {
    $this->say('Generating ddev project config');
    $result = $this->taskExecStack()
      ->exec("ddev config --docroot docroot --project-type drupal8  --project-name \"{$this->getConfigValue('project.machine_name')}\"")
      ->run();

    if (!$result->wasSuccessful()) {
      throw new BltException("Unable to generate ddev config.");
    }

    return $result;
  
  }

  /**
   * Initializes BLT configs to work with ddev.
   *
   * @command recipes:ddev:config
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  public function ddevConfig() {
    // Initialize local settings.
    try {
      $result = $this->taskFilesystemStack()
        // TODO: Add multisite local settings support as in blt:init:settings.
        ->remove($this->getConfigValue('drupal.local_settings_file'))
        ->remove($this->getConfigValue('docroot')  . '/sites/default/local.drush.yml')
        ->stopOnFail()
        ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
        ->run();

      if (!$result->wasSuccessful()) {
        throw new BltException("Could not remove old local settings. Please check your permissions.");
      }

      // Re-init settings after old settings are removed..
      $this->invokeCommand('blt:init:settings');
    }
    catch (BltException $e) {
      throw new BltException("Could not init local BLT or settings files.");
    }

    // Use containerized Chrome for behat tests if available.
    if (file_exists($this->getConfigValue('repo.root') . '/tests/behat/example.local.yml')) {
      $result = $this->taskReplaceInFile($this->getConfigValue('repo.root') . '/tests/behat/example.local.yml')
        ->from('api_url: "http://localhost:9222"')
        ->to('api_url: "http://chrome:9222"')
        ->run();

      if ($result->getData()['replaced'] > 0) {
        $this->say('Successfully replaced Behat Chrome debug URL, trying to regenerate behat local settings.');
        $result = $this->taskFilesystemStack()
          ->remove( $this->getConfigValue('repo.root') . '/tests/behat/local.yml')
          ->stopOnFail()
          ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
          ->run();

        if (!$result->wasSuccessful()) {
          throw new BltException("Could not remove Behat local settings. Please check your permissions.");
        }

        // Re-init settings after old settings are removed..
        $this->invokeCommand('tests:behat:init:config');
      }
    }
  }

}
