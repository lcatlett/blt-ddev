<?php

namespace Lcatlett\BltDdev\Blt\Plugin\Commands;

use Acquia\BltBehat\Blt\Plugin\Commands\BehatCommand;

class BehatDdevCommand extends BehatCommand {

  /**
   * Executes all behat tests.
   *
   * @command tests:behat:run
   * @description Executes all behat tests. Relies on external ddev services for Selenium/Chromium support
   * available on the network.
   * @usage
   *   Executes all configured tests.
   * @usage -D behat.paths=${PWD}/tests/behat/features/Examples.feature
   *   Executes scenarios in the Examples.feature file.
   * @usage -D behat.paths=${PWD}/tests/behat/features/Examples.feature:4
   *   Executes only the scenario on line 4 of Examples.feature.
   *
   * @aliases tbr behat tests:behat
   *
   * @interactGenerateSettingsFiles
   * @interactInstallDrupal
   * @interactConfigureBehat
   * @validateMySqlAvailable
   * @validateDrupalIsInstalled
   * @validateBehatIsConfigured
   * @validateVmConfig
   * @launchWebServer
   * @executeInVm
   */
  public function behat() {
    // Fallback to original Behat command unless ddev is enabled.
    if (!$this->getConfigValue('ddev.enable')) {
      parent::behat();
    }

    // Log config for debugging.
    $this->logConfig($this->getConfigValue('behat'), 'behat');
    $this->logConfig($this->getInspector()->getLocalBehatConfig()->export());
    $this->createReportsDir();

    $this->executeBehatTests();
  }

}
