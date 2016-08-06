<?php
/**
 * @file
 */
namespace Ansible;

/**
 * Class CustomModule
 * @package Ansible
 */
abstract class CustomModule {
  /**
   * @var string[]
   */
  protected $arguments;

  /**
   * Constructor.
   */
  public function __construct($arguments = NULL) {
    // Default behaviour of the ansible called plugin.
    if ($arguments == NULL) {
      $arguments = file_get_contents($GLOBALS['argv'][1]);
    }

    // Parse the arguments passed through to the plugin.
    if (is_string($arguments)) {
      $this->arguments = array();

      // Process the string text for the plugin.
      $contents = trim($arguments);
      $contents = str_replace("'\"'\"'", "'", $contents);
      while ($contents) {
        if (!preg_match('/\s*(.*?)="((?:u"(?:\\"|.)*"|u\'.*?\'|\'"\'"\'|[^"])*)"/', $contents, $match)) {
          $this->fail("Unable to parse the argument list.");
        }

        // Remove surrounding quotation marks.
        if (substr($match[2], 0, 1) == "'") {
          $match[2] = substr($match[2], 1, -1);
        }
        // Convert all '"'"' to ' to ensure easier parsing.
        $match[2] = str_replace("'\"'\"'", "'", $match[2]);
        $this->arguments[$match[1]] = $this->depickle($match[2], $length);
        $contents = substr($contents, strlen($match[0]));
      }
    }
    else {
      $this->arguments = (array)$arguments;
    }

    $this->arguments += $this->getDefaults();
  }

  /**
   * Get the arguments for the plugin.
   *
   * @return array
   */
  public function getArguments() {
    return $this->arguments;
  }

  /**
   * Get the argument for the plugin.
   *
   * @return mixed
   */
  public function getArgument($key) {
    return $this->arguments[$key];
  }

  /**
   * Get the defaults for arguments of the plugin.
   *
   * @return array
   */
  public function getDefaults() {
    return array();
  }

  /**
   * Indicates the action failed in some way.
   *
   * @param string $msg
   */
  protected function fail($msg) {
    $output = array(
      'failed' => TRUE,
      'msg' => $msg,
    );

    $this->finish($output);
  }

  /**
   * Indicates the action has been completed.
   *
   * @param array|string $results
   * @param bool $changed
   */
  protected function complete($results, $changed = FALSE) {
    $output = array(
      'changed' => $changed,
    );
    if (is_array($results)) {
      $output += $results;
    }
    else {
      $output['results'] = $results;
    }
    $this->finish($output);
  }

  /**
   * Finishes the action after printing the output.
   *
   * @param $output
   *
   * @throws \Exception
   */
  protected function finish($output) {
    print json_encode($output);
    throw new \Exception();
  }

  /**
   * Stream parse a python pickled string.
   *
   * @param $value
   * @param $length
   *
   * @return mixed
   */
  protected function depickle($value, &$length) {
    // Match a boolean.
    if (preg_match('/^(True|False)/i', $value, $match)) {
      $length = strlen($match[0]);
      return strtolower($match[0]) == 'true';
    }

    // Match against string.
    if (preg_match('/^u?\'((?:\\\\\'|.)*?)\'/', $value, $match)) {
      $length = strlen($match[0]);
      return $this->unescape($match[1]);
    }

    // Match against string.
    if (preg_match('/^u?"((?:\\"|.)*?)"/', $value, $match)) {
      $length = strlen($match[0]);
      return $match[1];
    }

    // Parse unindexed array.
    if (substr($value, 0, 1) == '[') {
      $length = 1;
      $list = array();
      $value = trim(substr($value, 1));

      // Cycle through items.
      while (substr($value, 0, 1) != ']') {
        $list[] = $this->depickle($value, $sublength);

        // Strip off processed items.
        $length += $sublength;
        $value = substr($value, $sublength);

        // Strip off any following whitespace and comma.
        if (preg_match('/^\s*,?\s*/', $value, $match)) {
          $length += strlen($match[0]);
          $value = substr($value, strlen($match[0]));
        }
      }
      $length += 1;
      return $list;
    }

    // Parse associative array.
    if (substr($value, 0, 1) == '{') {
      $length = 1;
      $list = array();
      $value = trim(substr($value, 1));

      // Cycle through items.
      while (substr($value, 0, 1) != '}') {
        $index = $this->depickle($value, $sublength);

        // Strip off processed items.
        $length += $sublength;
        $value = substr($value, $sublength);

        // Strip off any following whitespace and colon.
        if (preg_match('/^\s*:?\s*/', $value, $match)) {
          $length += strlen($match[0]);
          $value = substr($value, strlen($match[0]));
        }

        $list[$index] = $this->depickle($value, $sublength);

        // Strip off processed items.
        $length += $sublength;
        $value = substr($value, $sublength);

        // Strip off any following whitespace and comma.
        if (preg_match('/^\s*,?\s*/', $value, $match)) {
          $length += strlen($match[0]);
          $value = substr($value, strlen($match[0]));
        }
      }
      $length += 1;
      return $list;
    }

    return $value;
  }

  /**
   * Unescapes the string into a valid PHP string.
   *
   * @param $string
   *
   * @return string
   */
  protected function unescape($string) {
    $str = preg_replace_callback("/\\\\(u[0-9a-fA-F]{4}|x[0-9a-fA-F]{2}|.)/", function ($match) {
      if (preg_match('/^\\\\x(.*)$/', $match[0], $code)) {
        return utf8_encode(chr(hexdec($code[1])));
      }
      return json_decode('"' . $match[0] . '"');
    }, $string);
    return $str;
  }

  /**
   * Perform the action for the plugin.
   *
   * @return void
   *   This should never return.
   */
  abstract public function execute();
}
