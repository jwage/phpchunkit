# Tester

## TODO

- Rename the project.
- Create some kind of config object with the following:
    - Root dir
    - Path to tests
    - Path to phpunit.xml
    - Groups to consider functional tests.
    - Nothing should be hardcoded/assumed in the lib. Make it all configurable.
- Create abstraction for the create database/sandbox stuff. Functional tests need to be sandboxed.

## Usage

Run all tests:

    ./bin/tester all

Run just unit tests:

    ./bin/tester unit

Run all functional tests:

    ./bin/tester functional

Run a specific chunk of functional tests:

    ./bin/tester functional --chunk=1
