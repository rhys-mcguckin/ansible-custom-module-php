<?php
/**
 * @file
 */
namespace Ansible;

/**
 * Class CustomModuleTest
 * @package Ansible
 */
class CustomModuleTest extends \PHPUnit_Framework_TestCase {
  /**
   * Test the construction and arguments of the plugin.
   */
  public function testConstruction() {
    $arguments = file_get_contents(__DIR__ . '/args.txt');

    $stub = $this->getMockForAbstractClass('\Ansible\CustomModule', array($arguments));

    $result = array(
      '_ansible_version' => '2.1.0.0',
      'some [funny] value' => TRUE,
      '_ansible_no_log' => FALSE,
      'hierarchy' => array (
        'of' => array (
          'content' => TRUE,
        ),
      ),
      '_ansible_verbosity' => '3',
      '_ansible_syslog_facility' => 'LOG_USER',
      '_ansible_selinux_special_fs' => array (
        'fuse',
        'nfs',
        'vboxsf',
        'ramfs',
      ),
      '_ansible_diff' => FALSE,
      '_ansible_debug' => FALSE,
      '_ansible_check_mode' => FALSE,
      'array' => array(
        'item 1',
        'item 2',
        'Values "',
        'Values "',
        'てすと',
        'Ł Ą Ż Ę Ć Ń Ś Ź',
        'Я Б Г Д Ж Й',
        'Ä ä Ü ü ß',
      ),
    );

    $this->assertEquals($stub->getArguments(), $result);
  }
}
