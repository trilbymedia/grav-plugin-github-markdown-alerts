<?php
namespace Grav\Plugin;

use Composer\Autoload\ClassLoader;
use Grav\Common\Markdown\Extension\MarkdownExtensionRegistry;
use Grav\Common\Plugin;
use Grav\Plugin\GithubMarkdownAlerts\AlertsExtension;
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
        $registry = new MarkdownExtensionRegistry($event['markdown'], $event['page'] ?? null);
        $registry->add(new AlertsExtension($this->config->get('plugins.github-markdown-alerts')));
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
