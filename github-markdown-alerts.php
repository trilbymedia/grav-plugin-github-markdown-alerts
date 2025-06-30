<?php
namespace Grav\Plugin;

use Composer\Autoload\ClassLoader;
use Grav\Common\Grav;
use Grav\Common\Plugin;
use Grav\Common\Twig\Extension\GravExtension;
use RocketTheme\Toolbox\Event\Event;

/**
 * Class GithubMarkdownAlertsPlugin
 * @package Grav\Plugin
 */
class GithubMarkdownAlertsPlugin extends Plugin
{
    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onMarkdownInitialized' => ['onMarkdownInitialized', 0],
            'onTwigSiteVariables'   => ['onTwigSiteVariables', 0],
            'registerEditorProPlugin' => ['registerEditorProPlugin', 0]
        ];
    }

    /**
     * Composer autoload
     *
     * @return ClassLoader
     */
    public function autoload(): ClassLoader
    {
        return require __DIR__ . '/vendor/autoload.php';
    }

    public function onMarkdownInitialized(Event $event)
    {
        $markdown = $event['markdown'];
        $markdown->addBlockType('>', 'Alerts', true, false, 0);

        $markdown->blockAlerts = function($line) {
            if (preg_match('/^>\s\[!(NOTE|TIP|IMPORTANT|WARNING|CAUTION)\]\s*$/i', $line['text'], $matches))
            {
                $alert_type = strtolower($matches[1]);
                $title_text = Grav::instance()['language']->translate('PLUGIN_GITHUB_MARKDOWN_ALERTS.' . strtoupper($alert_type));

                $wrapper_class = $this->config->get('plugins.github-markdown-alerts.wrapper_class');
                $title_class = $this->config->get('plugins.github-markdown-alerts.title_class');
                $body_class = $this->config->get('plugins.github-markdown-alerts.body_class');

                if ($this->config->get('plugins.github-markdown-alerts.enable_octicons')) {
                    $alert_type = strtolower($matches[1]);
                    $title_text = GravExtension::svgImageFunction('plugin://github-markdown-alerts/assets/icons/octicon-' . $alert_type . '.svg') . " $title_text";
                }

                $title = [
                    'name' => 'p',
                    'handler' => 'line',
                    'attributes' => [
                        'class' => $title_class,
                    ],
                    'text' =>  $title_text,
                ];

                $body = [
                    'name' => 'div',
                    'handler' => 'lines',
                    'attributes' => [
                        'class' => $body_class
                    ],
                    'text' => [],
                ];

                $block = [
                    'alert' => true,
                    'type' => $alert_type,
                    'element' => [
                        'name' => 'div',
                        'handler' => 'elements',
                        'attributes' => [
                            'class' => $wrapper_class . strtolower($alert_type),
                            'dir' => 'auto'
                        ],
                        'text' => [ $title, $body ],
                    ]
                ];
                return $block;
            }
        };

        $markdown->blockAlertsContinue = function($line, array $block) {
            if (isset($block['interrupted']))
                return;

            if (!empty($block['alert'])) {
                $text = preg_replace('/^>\s?/', '', $line['text'] ?? '');
                $block['element']['text'][1]['text'][] = $text;
                return $block;
            }
        };
    }

    public function onTwigSiteVariables()
    {
        if ($this->config->get('plugins.github-markdown-alerts.include_css')) {
            $this->grav['assets']
                ->add('plugin://github-markdown-alerts/assets/github-markdown-alerts.css');
            $colors = $this->config->get('plugins.github-markdown-alerts.colors');
            $theme = ":root {
    --gh-alert-note-border-color: {$colors['note-border']};
    --gh-alert-note-title-color: {$colors['note-title']};
    --gh-alert-tip-border-color: {$colors['tip-border']};
    --gh-alert-tip-title-color: {$colors['tip-title']};
    --gh-alert-important-border-color: {$colors['important-border']};
    --gh-alert-important-title-color: {$colors['important-title']};
    --gh-alert-warning-border-color: {$colors['warning-border']};
    --gh-alert-warning-title-color: {$colors['warning-title']};
    --gh-alert-caution-border-color: {$colors['caution-border']};
    --gh-alert-caution-title-color: {$colors['caution-title']};
}";
            $this->grav['assets']->addInlineCss($theme);
        }
    }

    public function registerEditorProPlugin($event)
    {
        $plugins = $event['plugins'];
        
        // Add Editor Pro GitHub alerts integration JavaScript
        $plugins['js'][] = 'plugin://github-markdown-alerts/editor-pro/github-alerts-integration.js';
        
        $event['plugins'] = $plugins;
        return $event;
    }
}
