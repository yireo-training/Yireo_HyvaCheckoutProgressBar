<?php

declare(strict_types=1);

namespace Yireo\HyvaCheckoutProgressBar\Magewire;

use Hyva\Checkout\Model\ConfigData\FormFieldMappingManagement;
use Hyva\Checkout\Model\Form\EntityFormProvider;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magewirephp\Magewire\Component;

class Progressbar extends Component
{
    public int $totalCount = 0;
    public int $currentCount = 0;

    protected $listeners = [
        'billing_address_submitted' => 'refresh',
        'billing_address_saved' => 'refresh',
        'shipping_address_submitted' => 'refresh',
        'shipping_address_saved' => 'refresh',
        'shipping_address_activated' => 'refresh'
    ];

    public function __construct(
        private EntityFormProvider $entityFormProvider,
        private CheckoutSession $checkoutSession,
        private FormFieldMappingManagement $formFieldMappingManagement
    ) {
    }

    public function boot()
    {
        $this->collect();
    }

    public function refresh()
    {
        $this->collect();
    }

    private function collect()
    {
        $totalCount = 0;
        $currentCount = 0;

        $quote = $this->checkoutSession->getQuote();

        $shippingAddress = $quote->getShippingAddress();
        foreach ($this->getShippingFormFields() as $field) {
            $totalCount++;
            if ($shippingAddress->getData($field)) {
                $currentCount++;
            }
        }

        $billingAddress = $quote->getBillingAddress();
        foreach ($this->getBillingFormFields() as $field) {
            $totalCount++;
            if ($billingAddress->getData($field)) {
                $currentCount++;
            }
        }

        $this->totalCount = $totalCount;
        $this->currentCount = $currentCount;
    }

    private function getShippingFormFields(): array
    {
        return $this->getFormFields($this->formFieldMappingManagement->getShippingFormFieldMapping());
    }

    private function getBillingFormFields(): array
    {
        return $this->getFormFields($this->formFieldMappingManagement->getBillingAddressMapping());
    }

    private function getFormFields(array $formFieldMappings): array
    {
        $fieldNames = [];
        foreach ($formFieldMappings as $formFieldMapping) {
            if (empty($formFieldMapping['mappingConfig']['enabled'])) {
                continue;
            }

            if (empty($formFieldMapping['mappingConfig']['required'])) {
                continue;
            }

            $fieldNames[] = $formFieldMapping['mappingConfig']['attribute_code'];
        }

        return $fieldNames;
    }
}
