<?php

namespace Solution\PhpStanBoundedContext;

use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
class BoundedContextRule implements Rule
{
    private BoundedContextChecker $checker;

    public function __construct(BoundedContextChecker $checker)
    {
        $this->checker = $checker;
    }

    public function getNodeType(): string
    {
        return Use_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $errors = [];
        foreach ($node->uses as $use) {
            $importedNamespace = (string) $use->name;
            $filePath = $scope->getFile();

            $errorMessage = $this->checker->check($importedNamespace, $filePath);
            if ($errorMessage !== null) {
                $errors[] = RuleErrorBuilder::message($errorMessage)->build();
            }
        }

        return $errors;
    }
}