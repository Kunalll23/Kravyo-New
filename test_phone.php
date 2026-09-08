<?php
$pattern = '/^[6-9][0-9]{9}$/';
$cases = [
    '9876543210'  => true,
    '8123456789'  => true,
    '7123456789'  => true,
    '6123456789'  => true,
    '5123456789'  => false,
    '1234567890'  => false,
    '0123456789'  => false,
    '987654321'   => false,
    '98765432101' => false,
];

echo "Backend regex test: /^[6-9][0-9]{9}$/\n";
echo str_repeat('-', 52) . "\n";

$pass = 0; $fail = 0;
foreach ($cases as $number => $shouldPass) {
    $result = (bool) preg_match($pattern, $number);
    $ok = ($result === $shouldPass);
    if ($ok) $pass++; else $fail++;
    $status   = $ok ? 'PASS' : 'FAIL';
    $outcome  = $result ? 'ACCEPTED' : 'REJECTED';
    $expected = $shouldPass ? 'ACCEPTED' : 'REJECTED';
    echo "{$status}  {$number}  ->  {$outcome} (expected: {$expected})\n";
}

echo str_repeat('-', 52) . "\n";
echo "Result: {$pass}/" . count($cases) . " passed, {$fail} failed\n";
