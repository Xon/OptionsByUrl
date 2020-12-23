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
     */
    public static function appSetup(\XF\App $app)
    {
        $developmentMode = \XF::$debugMode && \XF::$developmentMode;

        $options = \XF::options();
        $request = $app->request();

        $overrides = [];

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

        if ($overrides)
        {
            // pre-load options
            $app->find('XF:Option', \array_keys($overrides));

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
                    if (!$option->hasErrors() && $option->hasChanges())
                    {
                        $options->offsetSet($optionKey, $option->option_value);
                    }
                }
            }
        }
    }
}
