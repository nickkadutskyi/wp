<?php

return (new PhpCsFixer\Config())
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRules([
        '@PHP71Migration' => true,
        '@PHPUnit75Migration:risky' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'method_chaining_indentation' => true,
        'echo_tag_syntax' => ['format' => 'short'], // overrides @Symfony
        'phpdoc_to_comment' => [
            'ignored_tags' => ['psalm-suppress', 'var'],
            'allow_before_return_statement' => true,
        ],
    ])
    ->setFinder(
        (new PhpCsFixer\Finder())
            ->in(
                [
                    __DIR__.'/public/content',
                    __DIR__.'/config',
                ]
            )
            ->name('*.php')
            ->notName('*.blade.php')
            ->ignoreDotFiles(true)
            ->ignoreVCS(true)
    )
    ->setRiskyAllowed(true)
    ->setUsingCache(true);

