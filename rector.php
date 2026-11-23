<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\ClassLike\NewlineBetweenClassLikeStmtsRector;
use Rector\CodingStyle\Rector\FuncCall\FunctionFirstClassCallableRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveVoidDocblockFromMagicMethodRector;
use Rector\Exception\Configuration\InvalidConfigurationException;
use Rector\Php81\Rector\Array_\ArrayToFirstClassCallableRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\AddSeeTestAnnotationRector;
use Rector\TypeDeclaration\Rector\Class_\ReturnTypeFromStrictTernaryRector;
use Rector\TypeDeclaration\Rector\ClassLike\AddVoidReturnTypeWhereNoReturnRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\ClassMethod\NumericReturnTypeFromStrictScalarReturnsRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNativeCallRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnUnionTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\StringReturnTypeFromStrictScalarReturnsRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\SafeDeclareStrictTypesRector;

try {
    return RectorConfig::configure()
        ->withPaths([
            __DIR__ . '/src',
        ])
        // uncomment to reach your current PHP version.
        ->withPhpSets(php84: true)
        ->withPreparedSets(
            codingStyle: true,
            typeDeclarationDocblocks: true,
            privatization: true,
            namedArgs: true,
            earlyReturn: true,
            rectorPreset: true,
            phpunitCodeQuality: true,
        )
        ->withRules([
            AddReturnTypeDeclarationRector::class,
            AddVoidReturnTypeWhereNoReturnRector::class,
            NumericReturnTypeFromStrictScalarReturnsRector::class,
            RemoveVoidDocblockFromMagicMethodRector::class,
            ReturnTypeFromStrictNativeCallRector::class,
            ReturnTypeFromStrictTernaryRector::class,
            ReturnTypeFromStrictTypedCallRector::class,
            ReturnUnionTypeRector::class,
            SafeDeclareStrictTypesRector::class,
            StringReturnTypeFromStrictScalarReturnsRector::class,
            TypedPropertyFromStrictConstructorRector::class,
        ])
        ->withSkip([
            __DIR__ . '/src/Api/Shortcode/Handler/templates/*',
            AddSeeTestAnnotationRector::class,
            ArrayToFirstClassCallableRector::class,
            FunctionFirstClassCallableRector::class,
            NewlineBetweenClassLikeStmtsRector::class,
        ])
        ->withCodeQualityLevel(10)
        ->withDeadCodeLevel(8)
        ->withImportNames(importDocBlockNames: false)
        ->withTypeCoverageLevel(10);
} catch (InvalidConfigurationException $e) {
    // Don't do anything.
}
