<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Alias\NoAliasFunctionsFixer;
use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\Basic\NonPrintableCharacterFixer;
use PhpCsFixer\Fixer\CastNotation\CastSpacesFixer;
use PhpCsFixer\Fixer\ClassNotation\ProtectedToPrivateFixer;
use PhpCsFixer\Fixer\ClassNotation\SelfAccessorFixer;
use PhpCsFixer\Fixer\ControlStructure\NoSuperfluousElseifFixer;
use PhpCsFixer\Fixer\LanguageConstruct\ErrorSuppressionFixer;
use PhpCsFixer\Fixer\LanguageConstruct\FunctionToConstantFixer;
use PhpCsFixer\Fixer\Operator\ConcatSpaceFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocAlignFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocSummaryFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PATHS, [
        __DIR__ . '/classes',
        __DIR__ . '/controllers',
        __DIR__ . '/helpers',
        __DIR__ . '/library',
        __DIR__ . '/pluglin.php',
    ]);

    $services = $containerConfigurator->services();
    $services->set(ArraySyntaxFixer::class)->call('configure', [[
        'syntax' => 'short',
    ]]);
    $services->set(ConcatSpaceFixer::class)->call('configure', [[
        'spacing' => 'one',
    ]]);
    $services->set(CastSpacesFixer::class)->call('configure', [[
        'space' => 'single',
    ]]);

    $services->set(ErrorSuppressionFixer::class);
    $services->set(FunctionToConstantFixer::class);
    $services->set(NoAliasFunctionsFixer::class);
    $services->set(PhpdocSummaryFixer::class);
    $services->set(PhpdocAlignFixer::class)->call('configure', [[
        'align' => 'left',
    ]]);
    $services->set(ProtectedToPrivateFixer::class);
    $services->set(SelfAccessorFixer::class);
    $services->set(NonPrintableCharacterFixer::class);
    $services->set(NoSuperfluousElseifFixer::class);


    $containerConfigurator->import(SetList::PSR_12);
    $containerConfigurator->import(SetList::SYMFONY);
};
