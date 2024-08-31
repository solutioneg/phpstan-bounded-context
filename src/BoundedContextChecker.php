<?php

namespace Solution\PhpStanBoundedContext;

use ReflectionClass;
use ReflectionException;

class BoundedContextChecker
{
    private string $baseFolder;
    private array $excludedFolders;

    public function __construct(string $baseFolder, array $excludedFolders)
    {
        $this->baseFolder = $baseFolder;
        $this->excludedFolders = $excludedFolders;
    }

    public function check(string $importedNamespace, string $currentFilePath): ?string
    {
        try {
            $reflector = new ReflectionClass($importedNamespace);
            $importedFilePath = $reflector->getFileName();

            if (!$this->isInsideModules($importedFilePath) || $this->isInsideVendor($importedFilePath)) {
                return null; // Skip checks for vendor files or files outside the modules directory
            }

            $currentSubmodule = $this->getSubmodule($currentFilePath);
            $importedSubmodule = $this->getSubmodule($importedFilePath);

            if ($currentSubmodule !== $importedSubmodule && !in_array($importedSubmodule, $this->excludedFolders, true)) {
                return "Bounded context violation: {$importedNamespace} is not allowed in {$currentFilePath}";
            }

            return null;

        } catch (ReflectionException $e) {
            return null; // Skip classes that cannot be reflected
        }
    }

    private function getSubmodule(string $filePath): ?string
    {
        $pathParts = explode(DIRECTORY_SEPARATOR, $filePath);
        $baseIndex = array_search($this->baseFolder, $pathParts, true);
        return $pathParts[$baseIndex + 1] ?? null;
    }

    private function isInsideModules(string $filePath): bool
    {
        return str_contains($filePath, DIRECTORY_SEPARATOR . $this->baseFolder . DIRECTORY_SEPARATOR);
    }

    private function isInsideVendor(string $filePath): bool
    {
        return str_contains($filePath, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR);
    }
}