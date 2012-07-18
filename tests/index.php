<?php include_once('../camdb.php');

$camdb = new camdb('test');

$results = array();

// $keys are expected result for value (which is a function to test)
$tests = array(
    // important, NO ; at the end of value and value must be literal (wrapped in single quotes)
    '$camdb->select("*")->from("test")' => 'SELECT * FROM test',
    '$camdb->select("*")->from("test")->where("red", true)' => 'SELECT * FROM test WHERE red = true',
    // 'SELECT * FROM test' => '$camdb->select("*")->from("test")',
    // 'SELECT * FROM test' => '$camdb->select("*")->from("test")',
    // 'SELECT * FROM test' => '$camdb->select("*")->from("test")',
    // 'SELECT * FROM test' => '$camdb->select("*")->from("test")',
);

foreach($tests as $input => $expected)
{
    $results[] = run_test($input, $expected);
}

function run_test($input, $expected)
{
    global $camdb;

    $actual = eval('return ' . $input . '->fetch_query();');
    $camdb->reset();

    return array(
        'input'    => $input . ';',
        'expected' => $expected,
        'actual'   => $actual,
        'class'    => strcasecmp(trim($expected), trim($actual)) === 0 ? 'pass' : 'fail',
    );
}

include_once('view.php');