<?php
namespace Psalm\Tests;

use const DIRECTORY_SEPARATOR;

class ListTest extends TestCase
{
    use Traits\InvalidCodeAnalysisTestTrait;
    use Traits\ValidCodeAnalysisTestTrait;

    /**
     * @return iterable<string,array{string,assertions?:array<string,string>,error_levels?:string[]}>
     */
    public function providerValidCodeParse(): iterable
    {
        return [
            'simpleVars' => [
                '<?php
                    list($a, $b) = ["a", "b"];',
                'assertions' => [
                    '$a' => 'string',
                    '$b' => 'string',
                ],
            ],
            'simpleVarsWithSeparateTypes' => [
                '<?php
                    list($a, $b) = ["a", 2];',
                'assertions' => [
                    '$a' => 'string',
                    '$b' => 'int',
                ],
            ],
            'simpleVarsWithSeparateTypesInVar' => [
                '<?php
                    $bar = ["a", 2];
                    list($a, $b) = $bar;',
                'assertions' => [
                    '$a' => 'string',
                    '$b' => 'int',
                ],
            ],
            'thisVar' => [
                '<?php
                    class A {
                        /** @var string */
                        public $a = "";

                        /** @var string */
                        public $b = "";

                        public function fooFoo(): string
                        {
                            list($this->a, $this->b) = ["a", "b"];

                            return $this->a;
                        }
                    }',
            ],
            'mixedNestedAssignment' => [
                '<?php
                    /** @psalm-suppress MissingReturnType */
                    function getMixed() {}

                    /**
                     * @psalm-suppress MixedArrayAccess
                     * @psalm-suppress MixedAssignment
                     */
                    list($a, list($b, $c)) = getMixed();',
                'assertions' => [
                    '$a' => 'mixed',
                    '$b' => 'mixed',
                    '$c' => 'mixed',
                ],
            ],
            'explicitLiteralKey' => [
                '<?php
                    /** @param list<int> $a */
                    function takesList($a): void {}

                    $a = [1, 1 => 2, 3];
                    takesList($a);',
            ],
        ];
    }

    /**
     * @return iterable<string,array{string,error_message:string,1?:string[],2?:bool,3?:string}>
     */
    public function providerInvalidCodeParse(): iterable
    {
        return [
            'thisVarWithBadType' => [
                '<?php
                    class A {
                        /** @var int */
                        public $a = 0;

                        /** @var string */
                        public $b = "";

                        public function fooFoo(): string
                        {
                            list($this->a, $this->b) = ["a", "b"];

                            return $this->a;
                        }
                    }',
                'error_message' => 'InvalidPropertyAssignmentValue - src' . DIRECTORY_SEPARATOR . 'somefile.php:11',
            ],
            'explicitVariableKey' => [
                '<?php
                    /** @param list<int> $a */
                    function takesList($a): void {}

                    /** @return array-key */
                    function getKey() {
                        return 0;
                    }

                    $a = [getKey() => 1];
                    takesList($a);',
                'error_message' => 'MixedArgumentTypeCoercion',
            ],
        ];
    }
}
