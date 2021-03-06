<?php declare(strict_types=1);

namespace Shopware\Core\Framework\FeatureFlag;

class FeatureFlagGenerator
{
    private const TEMPLATE_PHP = <<<'EOD'
<?php declare(strict_types=1);

namespace %s {
    use Shopware\Core\Framework\FeatureFlag\FeatureConfig;

    FeatureConfig::registerFlag('%s', '%s');

    function %s(): bool
    {
        return FeatureConfig::isActive('%s');
    }

    function if%s(\Closure $closure): void
    {
        %s() && $closure();
    }

    function if%sCall($object, string $methodName, ...$arguments): void
    {
        $closure = function () use ($methodName, $arguments) {
            $this->{$methodName}(...$arguments);
        };

        if%s(\Closure::bind($closure, $object, $object));
    }

    function skipTest%s(\PHPUnit\Framework\TestCase $test): void
    {
        if (%s()) {
            return;
        }

        $test->markTestSkipped('Skipping feature test "%s"');
    }
}

EOD;

    private const TEMPLATE_JS = <<<'EOD'
export default {
    %s,
    if%s,
    if%sCall,
    %s
};

export const %s = '%s';

export function %s() {
    return Shopware.FeatureConfig.isActive('%s');
}

export function if%s(closure) {
    if (%s()) {
        closure();
    }
}

export function if%sCall(object, methodName) {
    const closure = () => {
        object[methodName]();
    };

    if%s(closure);
}
  
EOD;

    public function exportPhp(string $namespace, string $featureName, string $destinationPath): string
    {
        $constantName = $this->getEnvironmentName($featureName);
        $lowerCamelCaseName = $this->toLowerCammelCase($featureName);
        $upperCamelCase = ucfirst($lowerCamelCaseName);
        $featureFilePath = $destinationPath . "/feature_$lowerCamelCaseName.php";

        $contents = sprintf(self::TEMPLATE_PHP,
            $namespace,
            $lowerCamelCaseName,
            $constantName,
            $lowerCamelCaseName,
            $lowerCamelCaseName,
            $upperCamelCase,
            $lowerCamelCaseName,
            $upperCamelCase,
            $lowerCamelCaseName,
            $upperCamelCase,
            $lowerCamelCaseName,
            $featureName
        );

        file_put_contents($featureFilePath, $contents);

        return $featureFilePath;
    }

    public function exportJs(string $featureName, string $destinationPath): string
    {
        $lowerCamelCaseName = $this->toLowerCammelCase($featureName);
        $upperCamelCase = ucfirst($lowerCamelCaseName);
        $capitablame = strtoupper($lowerCamelCaseName);
        $featureFilePath = $destinationPath . "/feature_$lowerCamelCaseName.js";

        $contents = sprintf(self::TEMPLATE_JS,
            $lowerCamelCaseName,
            $upperCamelCase,
            $upperCamelCase,
            $capitablame,
            $capitablame,
            $lowerCamelCaseName,
            $lowerCamelCaseName,
            $lowerCamelCaseName,
            $upperCamelCase,
            $lowerCamelCaseName,
            $upperCamelCase,
            $upperCamelCase
        );

        file_put_contents($featureFilePath, $contents);

        return $featureFilePath;
    }

    public function getEnvironmentName(string $string): string
    {
        return 'FEATURE_' . str_replace(' ', '_', trim(strtoupper(preg_replace('/[^\da-z]/i', ' ', $string))));
    }

    private function toLowerCammelCase(string $string): string
    {
        $cleanedFeatureName = strtolower(preg_replace('/[^\da-z]/i', ' ', $string));

        $parts = explode(' ', $cleanedFeatureName);

        $cammelCasedName = implode('', array_map('ucfirst', $parts));

        return lcfirst($cammelCasedName);
    }
}
