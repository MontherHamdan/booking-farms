<?php

namespace App\Traits;

use App\Models\City;
use App\Models\Feature;
use Illuminate\Support\Facades\Cache;

trait FarmHelperTrait
{
    /**
     * Get filter fields configuration
     */
    private function getFilterFieldsConfig($locale): array
    {
        $today = now()->format('Y-m-d');
        
        return [
            'city_id' => [
                'type' => 'multi_select',
                'label' => __('farm.attributes.city_id'),
                'placeholder' => __('farm.filter_placeholders.city_id'),
                'rules' => [
                    'nullable' => true,
                    'array' => true,
                    'items' => [
                        'type' => 'integer',
                        'exists' => 'cities,id'
                    ]
                ],
                'options' => $this->getCityOptions($locale)
            ],

            'area_id' => [
                'type' => 'multi_select',
                'label' => __('farm.attributes.area_id'),
                'placeholder' => __('farm.filter_placeholders.area_id'),
                'rules' => [
                    'nullable' => true,
                    'array' => true,
                    'items' => [
                        'type' => 'integer',
                        'exists' => 'areas,id'
                    ]
                ],
                'options' => [],
                'conditions' => [
                    'show_when' => [
                        'city_id' => ['not_empty']
                    ]
                ]
            ],
            
            'min_price' => [
                'type' => 'number',
                'label' => __('farm.attributes.min_price'),
                'placeholder' => __('farm.filter_placeholders.min_price'),
                'rules' => [
                    'nullable' => true,
                    'type' => 'numeric',
                    'min' => 0
                ]
            ],
            
            'max_price' => [
                'type' => 'number',
                'label' => __('farm.attributes.max_price'),
                'placeholder' => __('farm.filter_placeholders.max_price'),
                'rules' => [
                    'nullable' => true,
                    'type' => 'numeric',
                    'min' => 0
                ]
            ],
            
            'has_offer' => [
                'type' => 'select',
                'label' => __('farm.attributes.has_offer'),
                'placeholder' => __('farm.filter_placeholders.has_offer'),
                'rules' => [
                    'nullable' => true,
                    'type' => 'boolean'
                ],
                'options' => [
                    ['value' => true, 'label' => __('farm.filter_options.yes')],
                    ['value' => false, 'label' => __('farm.filter_options.no')]
                ]
            ],
            
            'available_time' => [
                'type' => 'multi_select',
                'label' => __('farm.attributes.available_time'),
                'placeholder' => __('farm.filter_placeholders.available_time'),
                'rules' => [
                    'nullable' => true,
                    'array' => true,
                    'items' => [
                        'type' => 'string',
                        'in' => ['day_use', 'night', 'full_day']
                    ]
                ],
                'options' => [
                    ['value' => 'day_use', 'label' => __('farm.price_types.day_use')],
                    ['value' => 'night', 'label' => __('farm.price_types.night')],
                    ['value' => 'full_day', 'label' => __('farm.price_types.full_day')]
                ]
            ],
            
            'date' => [
                'type' => 'date',
                'label' => __('farm.attributes.date'),
                'placeholder' => __('farm.filter_placeholders.date'),
                'rules' => [
                    'nullable' => true,
                    'date_format' => 'Y-m-d',
                    'after_or_equal' => "{$today}"
                ]
            ],
            
            'start_date' => [
                'type' => 'date',
                'label' => __('farm.attributes.start_date'),
                'placeholder' => __('farm.filter_placeholders.start_date'),
                'rules' => [
                    'nullable' => true,
                    'date_format' => 'Y-m-d',
                    'after_or_equal' => "{$today}"
                ]
            ],
            
            'end_date' => [
                'type' => 'date',
                'label' => __('farm.attributes.end_date'),
                'placeholder' => __('farm.filter_placeholders.end_date'),
                'rules' => [
                    'nullable' => true,
                    'date_format' => 'Y-m-d',
                    'after_or_equal' => 'start_date'
                ],
                'conditions' => [
                    'show_when' => [
                        'start_date' => ['not_empty']
                    ]
                ]
            ],
            
            'features' => [
                'type' => 'multi_select',
                'label' => __('farm.attributes.features'),
                'placeholder' => __('farm.filter_placeholders.features'),
                'rules' => [
                    'nullable' => true,
                    'array' => true,
                    'items' => [
                        'type' => 'integer',
                        'exists' => 'features,id'
                    ]
                ],
                'options' => $this->getFeatureOptions($locale)
            ],
            
            'ratings' => [
                'type' => 'multi_select',
                'label' => __('farm.attributes.ratings'),
                'placeholder' => __('farm.filter_placeholders.ratings'),
                'rules' => [
                    'nullable' => true,
                    'array' => true,
                    'items' => [
                        'type' => 'integer',
                        'in' => [1, 2, 3, 4, 5]
                    ]
                ],
                'options' => [
                    ['value' => 1, 'label' => __('farm.filter_options.rating_1')],
                    ['value' => 2, 'label' => __('farm.filter_options.rating_2')],
                    ['value' => 3, 'label' => __('farm.filter_options.rating_3')],
                    ['value' => 4, 'label' => __('farm.filter_options.rating_4')],
                    ['value' => 5, 'label' => __('farm.filter_options.rating_5')]
                ]
            ],
            
            'passenger_count' => [
                'type' => 'number',
                'label' => __('farm.attributes.passenger_count'),
                'placeholder' => __('farm.filter_placeholders.passenger_count'),
                'rules' => [
                    'nullable' => true,
                    'type' => 'integer',
                    'min' => 1
                ]
            ],
            
            'sort_by' => [
                'type' => 'select',
                'label' => __('farm.attributes.sort_by'),
                'placeholder' => __('farm.filter_placeholders.sort_by'),
                'rules' => [
                    'nullable' => true,
                    'type' => 'string',
                    'in' => ['lowest_price', 'highest_price', 'highest_rating', 'lowest_rating']
                ],
                'options' => [
                    ['value' => 'lowest_price', 'label' => __('farm.sort_options.lowest_price')],
                    ['value' => 'highest_price', 'label' => __('farm.sort_options.highest_price')],
                    ['value' => 'highest_rating', 'label' => __('farm.sort_options.highest_rating')],
                    ['value' => 'lowest_rating', 'label' => __('farm.sort_options.lowest_rating')]
                ]
            ],
            
            'per_page' => [
                'type' => 'select',
                'label' => __('farm.attributes.per_page'),
                'placeholder' => __('farm.filter_placeholders.per_page'),
                'rules' => [
                    'nullable' => true,
                    'type' => 'integer',
                    'min' => 1,
                    'max' => 100
                ],
                'options' => [
                    ['value' => 10, 'label' => '10'],
                    ['value' => 20, 'label' => '20'],
                    ['value' => 50, 'label' => '50'],
                    ['value' => 100, 'label' => '100']
                ],
                'default' => 10
            ]
        ];
    }

    /**
     * Get city options (cached for performance)
    */
    private function getCityOptions($locale = 'en'): array
    {
        $cacheKey = "cities_options_{$locale}";
        
        return Cache::remember($cacheKey, 3600, function () use ($locale) {
            $nameField = $locale === 'ar' ? 'name_ar' : 'name_en';
            
            return City::select('id as value', $nameField . ' as label')
                ->where('status', City::STATUS_PUBLISHED)
                ->orderBy('order')
                ->get()
                ->toArray();
        });
    }
    
    /**
     * Get feature options (cached)
     */
    private function getFeatureOptions($locale = 'en'): array
    {
        $cacheKey = "features_options_{$locale}";
        
        return Cache::remember($cacheKey, 3600, function () use ($locale) {
            $nameField = $locale === 'ar' ? 'name_ar' : 'name_en';
            
            return Feature::select('id as value', $nameField . ' as label')
                ->orderBy('order')
                ->get()
                ->toArray();
        });
    }
}