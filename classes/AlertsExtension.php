<?php

namespace Grav\Plugin\GithubMarkdownAlerts;

use Grav\Common\Grav;
use Grav\Common\Markdown\BlockResult;
use Grav\Common\Markdown\Element;
use Grav\Common\Markdown\Extension\AbstractMarkdownExtension;
use Grav\Common\Markdown\Extension\BlockContinuableInterface;
use Grav\Common\Markdown\Extension\BlockHandlerInterface;
use Grav\Common\Markdown\Extension\MarkdownExtensionRegistry;
use Grav\Common\Twig\Extension\GravExtension;

/**
 * GitHub-style alerts as a Grav Markdown extension.
 *
 * Recognises a blockquote whose first line is `> [!NOTE]` (or TIP, IMPORTANT,
 * WARNING, CAUTION) and renders it as a titled, coloured callout instead of a
 * plain `<blockquote>`. Built on Grav 2.0's Markdown Extension API: it
 * implements the block + continue handler interfaces and describes its output
 * with the Element builder, so there are no raw element arrays or magic method
 * names.
 *
 * @package Grav\Plugin\GithubMarkdownAlerts
 */
class AlertsExtension extends AbstractMarkdownExtension implements BlockHandlerInterface, BlockContinuableInterface
{
    /** The five alert keywords GitHub recognises, matched case-insensitively. */
    private const TYPES = 'NOTE|TIP|IMPORTANT|WARNING|CAUTION';

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'github-alerts';
    }

    /**
     * {@inheritdoc}
     */
    public function register(MarkdownExtensionRegistry $registry): void
    {
        // Shares the blockquote marker '>', but index 0 runs it first so an
        // alert is detected before the built-in blockquote handler.
        $registry->registerBlock('Alerts', '>', $this, ['index' => 0]);
    }

    /**
     * Open an alert when the blockquote starts with `> [!TYPE]`.
     */
    public function block(array $line, ?array $block = null): ?array
    {
        if (!preg_match('/^>\s\[!(' . self::TYPES . ')\]\s*$/i', $line['text'], $matches)) {
            return null; // not an alert — let the normal blockquote handler run
        }

        $type = strtolower($matches[1]);

        $title = Element::create('p')
            ->attr('class', (string) $this->setting('title_class', 'md-alert-title'))
            ->setInlineText($this->titleText($type));

        // Body starts empty; blockContinue() appends each following line to it.
        $body = Element::div()
            ->attr('class', (string) $this->setting('body_class', 'md-alert-body'))
            ->setRawLines([]);

        $wrapper = Element::div()
            ->attr('class', $this->setting('wrapper_class', 'md-alert md-alert--') . $type)
            ->attr('dir', 'auto')
            ->setChildren([$title, $body]);

        // BlockResult pairs the element with the block state the continue step reads.
        return BlockResult::fromElement($wrapper)
            ->with(['alert' => true, 'type' => $type])
            ->toArray();
    }

    /**
     * Append each following `> ...` line to the alert body (the 2nd child).
     */
    public function blockContinue(array $line, array $block): ?array
    {
        if (isset($block['interrupted']) || empty($block['alert'])) {
            return null; // a blank line (or a foreign block) closes the alert
        }

        $text = preg_replace('/^>\s?/', '', $line['text'] ?? '');
        $block['element']['text'][1]['text'][] = $text;

        return $block;
    }

    /**
     * The translated alert title, optionally prefixed with its octicon SVG.
     */
    private function titleText(string $type): string
    {
        $title = Grav::instance()['language']->translate('PLUGIN_GITHUB_MARKDOWN_ALERTS.' . strtoupper($type));

        if ($this->setting('enable_octicons', true)) {
            $icon = GravExtension::svgImageFunction('plugin://github-markdown-alerts/assets/icons/octicon-' . $type . '.svg');
            $title = $icon . " $title";
        }

        return $title;
    }

    /**
     * Read a value from the plugin config handed to the constructor.
     *
     * @param mixed $default
     * @return mixed
     */
    private function setting(string $key, $default = null)
    {
        $config = $this->getConfig();

        if (is_array($config)) {
            return $config[$key] ?? $default;
        }
        if (is_object($config) && method_exists($config, 'get')) {
            return $config->get($key, $default);
        }

        return $default;
    }
}
