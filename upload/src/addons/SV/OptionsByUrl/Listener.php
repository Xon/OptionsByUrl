<?php

namespace SV\OptionsByUrl;

/**
 * Class Listener
 *
 * @package SV\OptionsByUrl
 */
class Listener
{
    /**
     * @param \XF\App $app
     * @throws \XF\PrintableException
     */
    public static function appSetup(\XF\App $app)
    {
        $developmentMode = \XF::$debugMode && \XF::$developmentMode;
        $options = \XF::options();
        $request = $app->request();
        $overrides = [];

        // phase 1, collect possible overrides
        foreach ($options as $optionKey => $value)
        {
            $validKey = false;
            $urlKey = 'xf_options_' . $optionKey;
            if ($request->exists($urlKey))
            {
                $validKey = $urlKey;
            }
            else
            {
                $urlKey = 'xf_options_' . \urlencode($optionKey);
                if ($request->exists($urlKey))
                {
                    $validKey = $urlKey;
                }
            }
            if ($validKey)
            {
                if (!$developmentMode)
                {
                    \XF::logError("Ignoring setting xf.options.{$optionKey} as site is not in development mode", true);
                    continue;
                }
                $overrides[$optionKey] = $urlKey;
            }
        }

        // phase 2, validate overrides
        if ($overrides)
        {
            // pre-load options
            $app->find('XF:Option', \array_keys($overrides));

            $changedOptions = [];
            // using the option entity, parse the URL string into valid option data
            foreach ($overrides as $optionKey => $urlKey)
            {
                /** @var \XF\Entity\Option $option */
                $option = $app->find('XF:Option', $optionKey);
                if (!$option)
                {
                    \XF::logError("Unable to find xf.options.{$optionKey}, site configuration damaged or during an upgrade?", true);
                    continue;
                }

                $value = $request->filter($urlKey, '?str');
                if ($value !== null)
                {
                    if ($option->data_type === 'array')
                    {
                        $value = \json_decode($value, true);
                    }
                    $option->option_value = $value;
                    $option->setReadOnly(true);
                    if ($option->hasErrors())
                    {
                        throw new \XF\PrintableException($option->getErrors());
                    }
                    if ($option->hasChanges())
                    {
                        $changedOptions[$optionKey] = $option->option_value;
                    }
                }
            }

            // phase 3, apply overrides to live configuration
            foreach($changedOptions as $key => $value)
            {
                $options->offsetSet($key, $value);
            }
        }
    }
}
