<?php

namespace App\Livewire;

use App\Models\TaxSetting;
use Livewire\Component;

class TaxSettings extends Component
{
    public $settings = [];

    protected $rules = [
        'settings.*.value' => 'required|numeric|min:0',
    ];

    public function mount()
    {
        $allSettings = TaxSetting::all();
        $this->settings = [];
        
        foreach ($allSettings as $setting) {
            $value = $setting->value;
            if ($setting->type === 'percentage') {
                $value = (float) $value * 100;
            }
            $this->settings[] = [
                'id' => $setting->id,
                'key' => $setting->key,
                'value' => $value,
                'display_name' => $setting->display_name,
                'category' => $setting->category,
                'type' => $setting->type,
            ];
        }
    }

    public function save()
    {
        $this->validate();

        foreach ($this->settings as $index => $settingData) {
            $setting = TaxSetting::find($settingData['id']);
            if ($setting) {
                $value = $settingData['value'];
                if ($setting->type === 'percentage') {
                    $value = (float) $value / 100;
                }
                $setting->update([
                    'value' => $value,
                ]);
            }
        }

        // Refresh model data
        $this->mount();

        session()->flash('message', 'Tax settings successfully updated.');
    }

    public function render()
    {
        return view('livewire.tax-settings')->layout('layouts.app');
    }
}
