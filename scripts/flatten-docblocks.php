<?php

/**
 * Enforces the project docblock style: prose lines are stripped from
 * tag-bearing docblocks, and docblocks left with a single tag collapse
 * to one line. Runs as part of `composer lint` and `composer ide:models`.
 */
$directories = ['app', 'database', 'routes', 'tests', 'bootstrap'];

$pattern = '#^([ \t]*)/\*\*\n((?:\1 \*[^\n]*\n)+?)\1 \*/\n#m';

$changed = 0;

foreach ($directories as $directory) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(__DIR__.'/../'.$directory, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $original = file_get_contents($file->getPathname());

        $flattened = preg_replace_callback($pattern, function (array $matches): string {
            [, $indent, $body] = $matches;

            $tags = array_values(array_filter(
                explode("\n", $body),
                fn (string $line): bool => (bool) preg_match('/^\s*\*\s*@/', $line),
            ));

            $content = array_map(
                fn (string $line): string => preg_replace('/^\s*\*\s?/', '', $line),
                $tags,
            );

            return match (true) {
                count($content) === 1 => "{$indent}/** {$content[0]} */\n",
                count($content) > 1 => "{$indent}/**\n".implode('', array_map(
                    fn (string $line): string => "{$indent} * {$line}\n",
                    $content,
                ))."{$indent} */\n",
                default => '',
            };
        }, $original);

        if ($flattened !== $original) {
            file_put_contents($file->getPathname(), $flattened);
            $changed++;
        }
    }
}

echo json_encode(['tool' => 'flatten-docblocks', 'result' => 'passed', 'files_changed' => $changed]).PHP_EOL;
