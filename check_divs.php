<?php
$content = file_get_contents('resources/views/admin/reservations/show.blade.php');
$lines = explode("\n", $content);

// Customer Detail tab: lines 651-981 (0-indexed: 650-980)
$stack = [];
$issues = [];

for ($i = 650; $i <= 980; $i++) {
    $line = $lines[$i];
    $lineNum = $i + 1;
    
    // Find all opening divs
    preg_match_all('/<div[^>]*>/i', $line, $opens);
    foreach ($opens[0] as $div) {
        $stack[] = ['line' => $lineNum, 'tag' => substr($div, 0, 50)];
    }
    
    // Find all closing divs
    preg_match_all('/<\/div>/i', $line, $closes);
    foreach ($closes[0] as $close) {
        if (empty($stack)) {
            $issues[] = "Extra </div> at line $lineNum";
        } else {
            array_pop($stack);
        }
    }
}

echo "=== Customer Detail Section Analysis ===\n";
echo "Remaining open divs: " . count($stack) . "\n";
foreach ($stack as $item) {
    echo "  Unclosed div at line {$item['line']}: {$item['tag']}...\n";
}
if (!empty($issues)) {
    echo "\nIssues found:\n";
    foreach ($issues as $issue) {
        echo "  $issue\n";
    }
}

