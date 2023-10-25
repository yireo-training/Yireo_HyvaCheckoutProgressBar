<?php

declare(strict_types=1);

namespace Yireo\HyvaCheckoutProgressBar\Magewire;

use Hyva\Checkout\Model\Form\EntityFormProvider;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magewirephp\Magewire\Component;

class Progressbar extends Component
{
    public int $totalCount = 0;
    public int $currentCount = 0;

    protected $listeners = [
        'billing_address_submitted' => 'refresh',
        'shipping_address_submitted' => 'refresh',
        'shipping_address_activated' => 'refresh'
    ];

    public function __construct(
        private EntityFormProvider $entityFormProvider,
        private CheckoutSession $checkoutSession,
    ) {
    }

    public function getPercentage(): int
    {
        if ($this->totalCount === 0 || $this->currentCount === 0) {
            return 0;
        }

        return (int) ($this->totalCount / $this->currentCount);
    }

    public function boot()
    {
        $this->collect();
    }

    public function refresh()
    {
        $this->collect();;
    }

    private function collect()
    {
        $totalCount = 0;
        $currentCount = 0;

        $quote = $this->checkoutSession->getQuote();
        $shippingAddress = $quote->getShippingAddress();
        foreach ($this->entityFormProvider->getShippingAddressForm()->getFields() as $field) {
            if (false === $field->isRequired()) {
                continue;
            }

            $totalCount++;
            if ($shippingAddress->getData($field->getName())) {
                $currentCount++;
            }
        }

        $billingAddress = $quote->getBillingAddress();
        foreach ($this->entityFormProvider->getBillingAddressForm()->getFields() as $field) {
            if (false === $field->isRequired()) {
                continue;
            }

            $totalCount++;
            if ($billingAddress->getData($field->getName())) {
                $currentCount++;
            }
        }

        $this->totalCount = $totalCount;
        $this->currentCount = $currentCount;
    }
}
