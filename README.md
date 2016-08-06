# Ansible PHP Module


## Purpose

Ansible allows for custom modules to be defined within the ANSIBLE_LIBRARY path, and according to the [documentation](http://docs.ansible.com/ansible/developing_modules.html)
can use any programming language. 

While this is technically true, the arguments passed are using the python-specific pickle serialization format, which
makes passing complex variables to these custom modules non-trivial.

As such, the ansible-custom-module-php is a PHP library containing a base class for loading the arguments passed
through to the script, and generating PHP equivalent data structures.

## Requirements

This was originally built for ansible 2.1, but may work for later versions.

## Usage

Custom ansible modules are located generally within the library/ directory relative to the playbook. 

An example module would be defined in library/example file (**N.B. Note the lack of extension), as a shell script
```sh
#!/usr/bin/env php
<?php
/**
 * @file
 */
// This is relative to the location the ansible command is run from.
require('../vendor/autoload.php');
$plugin = new \Ansible\ExampleModule();
try {
  $plugin->execute();
}
catch (\Exception $e) {}
```

**Note that the composer autoload require is relative from the location ansible is run from**

## Running the example

Change into the examples directory, and run the following command:

```sh
ansible-playbook playbook.yml
```

## Running the tests

This uses phpunit for testing purposes. To run the tests, use the following command within the repository directory:

```
phpunit
```
