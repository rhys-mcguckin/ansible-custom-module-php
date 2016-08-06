<?php
/**
 * @file
 */
namespace Ansible;

/**
 * Class ExampleModule
 * @package Ansible
 */
class ExampleModule extends CustomModule {
  /**
   * Optional argument defaults.
   */
  public function getDefaults() {
    return array(
      'param1' => 'param1',
      'another param1' => array(),
    );
  }

  /**
   * Example execution process of a custom task.
   */
  public function execute() {
    // Example of getting the argument
    if ($this->getArgument('param1') != 'param1') {
      $this->fail('The default argument was not passed through.');
    }

    // Arguments when passed do not check type.
    if (!is_array($this->getArgument('another param1'))) {
      $this->complete('This is the contents for "another param1": ' . print_r($this->getArgument('another param1'), TRUE));
    }

    // Any PHP variable can be supplied as the result.
    $this->complete(array('msg' => 'Finished', 'count' => 1));
  }
}
