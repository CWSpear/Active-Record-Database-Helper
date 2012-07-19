<?php include_once('../camdb.php');
include_once('finediff.php');

$camdb = new camdb('test');

$results = array();

// $keys are expected result for value (which is a function to test)
$tests = array(
    // important, NO ; at the end of value and value must be literal (wrapped in single quotes)
    array(
        'input'    => '$camdb->select("name")->from("test")',
        'expected' => "SELECT `name` FROM `test`",
        'note'     => 'Simple select',
    ),
    array(
        'input'    => '$camdb->from("test")->where("color", "red")',
        'expected' => "SELECT * FROM `test` WHERE `color` = 'red'",
        'note'     => 'Simple select with "where" and no select',
    ),
    array(
        'input'    => '$camdb->select("*")->from("test")->where("message", "WE\'re going to be \"punks\"!--")',
        'expected' => "SELECT * FROM `test` WHERE `message` = 'WE\'re going to be \\\"punks\\\"!--'",
        'note'     => 'Testing to make sure things are being escaped',
    ),
    array(
        'input'    => '$camdb->select("*")->from("test")->where(array(
     "color" => "red",
     "age" => 24,
     "agree_to_terms" => true,
     "favorite_float_or_double" => 24.4,
))',
        'expected' => "SELECT * FROM `test` WHERE `color` = 'red' AND `age` = 24 AND `agree_to_terms` = 1 AND `favorite_float_or_double` = 24.4",
        'note'     => 'Testing "where" when param is array and testing proper treatmeant of types',
    ),
    array(
        'input'    => '$camdb->insert(array("yes" => "yep", "no" => "nope"), "test", false)',
        'expected' => "INSERT INTO `test` (`yes`, `no`) VALUES ('yep', 'nope')",
        'note'     => 'Testing insert',
    ),
);

foreach($tests as $test)
{
    $results[] = run_test($test['input'], $test['expected'], $test['note']);
}

function run_test($input, $expected, $note = '')
{
    global $camdb;

    $actual = eval('return ' . $input . '->fetch_query();');
    $camdb->reset();

    $diff = new FineDiff($expected, $actual);
    $rendered_diff = $diff->renderDiffToHTML();

    // clean up whitespace
    $expected = trim(preg_replace('/ +/', ' ', $expected));
    $actual = trim(preg_replace('/ +/', ' ', $actual));

    return array(
        'input'    => str_replace(array('&lt;?php&nbsp;', '?&gt;'), '', highlight_string('<?php ' . $input . '; ?>', true)),
        'expected' => highlight_string($expected, true),
        'actual'   => highlight_string($actual, true),
        'class'    => strcasecmp($expected, $actual) === 0 ? 'pass' : 'fail',
        'diff'     => '<code>' . $rendered_diff . '</code>',
        'note'     => $note,
    );
}

include_once('view.php');