<?php

namespace Grayloon\MagentoStorage\Jobs;

use Grayloon\Magento\Magento;
use Grayloon\MagentoStorage\Models\MagentoCustomAttributeType;
use Grayloon\MagentoStorage\Support\HasCustomAttributes;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateProductAttributeGroup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HasCustomAttributes;

    /**
     * The Magento Custom Attribute Type.
     *
     * @var \Grayloon\Magento\Models\MagentoCustomAttributeType
     */
    public $type;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(MagentoCustomAttributeType $type)
    {
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $api = (new Magento())->api('productAttributes')
            ->show($this->type->name);

        if ($api->ok()) {
            $response = $api->json();
            $this->type->update([
                'display_name' => $this->resolveAttributeLabel($response['frontend_labels'], $response['default_frontend_label'] ?? $this->type->display_name),
                'options'      => $response['options'] ?? [],
                'synced_at'    => now(),
                'is_queued'    => false,
                'attribute_id' => $response['attribute_id'],
                'type'         => $response['frontend_input'] ?? '',
            ]);

            $this->updateCustomAttributeTypeValues($this->type);
        }
    }

    /**
     * Resolve the Attribute label by the associated assigned website.
     *
     * @param  array   $availableLabels
     * @param  string  $defaultLabel
     * @return string
     */
    public function resolveAttributeLabel($availableLabels, $defaultLabel)
    {
        if (! $availableLabels) {
            return $defaultLabel;
        }

        $labels = collect($availableLabels)
            ->when(config('magento.default_store_id'), function ($collection) {
                return $collection->filter(fn ($label) => $label['store_id'] == config('magento.default_store_id'));
            })
            ->when(! config('magento.default_store_id') && $defaultLabel, function ($collection) use ($defaultLabel) {
                return $collection->filter(fn ($label) => $label['label'] === $defaultLabel);
            });

        return $labels->first()['label'];
    }
}
