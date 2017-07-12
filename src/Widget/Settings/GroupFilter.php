<?php

namespace CultuurNet\ProjectAanvraag\Widget\Settings;

/**
 * Provides a group filter settings type
 */
class GroupFilter
{

    /**
     * Cleanup the configuration.
     */
    public function cleanup($configuration) {

        $cleanedConfiguration = [];
        $cleanedConfiguration['enabled'] = isset($configuration['enabled']) ? (bool) $configuration['enabled'] : false;

        // Add the filters if it was an array.
        if (isset($configuration['filters']) && is_array($configuration['filters'])) {

            // Cleanup every filter.
            $cleanedConfiguration['filters'] = [];
            foreach ($configuration['filters'] as $key => $filter) {

                $cleanedFilter = [];
                $cleanedFilter['label'] = (string) $filter['label'] ?? '';
                $cleanedFilter['placeholder'] = (string) $filter['placeholder'] ?? '';
                $cleanedFilter['type'] = (string) $filter['type'] ?? '';

                // All strings are empty? Skip the filter.
                if (empty($cleanedFilter['label']) && empty($cleanedFilter['placeholder']) && empty($cleanedFilter['type'])) {
                    continue;
                }
                else {

                    // Clean all options.
                    if (is_array($filter['options'])) {
                        $cleanedFilter['options'] = $this->cleanupOptions($filter['options']);
                    }

                    // No valid options? Skip the filter.
                    if (empty($cleanedFilter['options'])) {
                        continue;
                    }

                }

                $cleanedConfiguration['filters'][] = $cleanedFilter;
            }

        }

        return $cleanedConfiguration;
    }

    /**
     * Cleanup the options of a group filter.
     */
    function cleanupOptions($options)
    {

        $cleanedOptions = [];
        foreach ($options as $option) {

            if (isset($option['label']) && isset($option['query'])) {
                $cleanedOptions[] = [
                    'label' => (string) $option['label'],
                    'query' => (string) $option['query'],
                ];
            }

        }

        return $cleanedOptions;
    }

}