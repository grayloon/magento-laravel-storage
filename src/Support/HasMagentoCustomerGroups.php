<?php

namespace Grayloon\MagentoStorage\Support;

use Grayloon\MagentoStorage\Models\MagentoCustomerGroup;

trait HasMagentoCustomerGroups
{
    /**
     * Update or create the supplied groups.
     *
     * @param array $groups
     *
     * @return $this
     */
    protected function updateOrCreateCustomerGroups($groups)
    {
        foreach ($groups['items'] as $group) {
            $this->updateOrCreateCustomerGroup($group);
        }

        return $this;
    }

    /**
     * Update or create the customer group.
     *
     * @param  array  $group
     * @return \Grayloon\MagentoStorage\Models\MagentoCustomerGroup
     */
    protected function updateOrCreateCustomerGroup($group)
    {
        return MagentoCustomerGroup::updateOrCreate(
            ['id' => $group['id']],
            [
                'code' => $group['code'],
                'tax_class_id' => $group['tax_class_id'],
                'tax_class_name' => $group['tax_class_name'],
                'synced_at' => now(),
            ],
        );
    }
}
