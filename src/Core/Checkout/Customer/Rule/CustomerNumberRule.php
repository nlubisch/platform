<?php declare(strict_types=1);

namespace Shopware\Core\Checkout\Customer\Rule;

use Shopware\Core\Checkout\CheckoutRuleScope;
use Shopware\Core\Framework\Rule\Match;
use Shopware\Core\Framework\Rule\Rule;
use Shopware\Core\Framework\Rule\RuleScope;
use Shopware\Core\Framework\Validation\Constraint\ArrayOfType;
use Symfony\Component\Validator\Constraints\NotBlank;

class CustomerNumberRule extends Rule
{
    /**
     * @var string[]
     */
    protected $numbers;

    public function match(RuleScope $scope): Match
    {
        if (!$scope instanceof CheckoutRuleScope) {
            return new Match(false, ['Wrong scope']);
        }

        /** @var CheckoutRuleScope $scope */
        if (!$customer = $scope->getCheckoutContext()->getCustomer()) {
            return new Match(false, ['Not logged in customer']);
        }

        $this->numbers = array_map('strtolower', $this->numbers);

        return new Match(
            \in_array(strtolower($customer->getCustomerNumber()), $this->numbers, true),
            ['Customer number not match']
        );
    }

    public static function getConstraints(): array
    {
        return [
            'numbers' => [new NotBlank(), new ArrayOfType('string')],
        ];
    }
}
