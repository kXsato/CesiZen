<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude(['var', 'vendor', 'node_modules', 'public/assets'])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'trailing_comma_in_multiline' => true,
    ])
    ->setFinder($finder)
;
