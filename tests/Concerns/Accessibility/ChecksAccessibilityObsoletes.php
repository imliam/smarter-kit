<?php

declare(strict_types=1);

namespace Tests\Concerns\Accessibility;

use Dom\HTMLDocument;

trait ChecksAccessibilityObsoletes
{
    /** Assert that there are no accessibility obsoletes. */
    protected function assertNoAccessibilityObsoletes(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $this->assertNoObsoleteTags($html);
        $this->assertNoObsoleteAttributes($html);
    }

    /**
     * Assert that no obsolete HTML tags are used.
     *
     * Many tags are obsolete in HTML5 and should not be used.
     *
     * @see https://html.spec.whatwg.org/multipage/obsolete.html#obsolete
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#8.2
     */
    protected function assertNoObsoleteTags(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $obsoleteTags = [
            'applet',
            'acronym',
            'bgsound',
            'dir',
            'frame',
            'frameset',
            'noframes',
            'isindex',
            'keygen',
            'menuitem',
            'listing',
            'nextid',
            'noembed',
            'param',
            'plaintext',
            'rb',
            'rtc',
            'strike',
            'xmp',
            'basefont',
            'big',
            'blink',
            'center',
            'font',
            'marquee',
            'multicol',
            'nobr',
            'spacer',
            'tt',
        ];

        $selector = implode(', ', $obsoleteTags);
        $elements = $html->querySelectorAll($selector);

        if ($elements->length > 0) {
            $violations = [];

            foreach ($elements as $element) {
                $tagName = mb_strtolower($element->tagName);
                $violations[] = sprintf(
                    '<%s> is an obsolete HTML tag and should not be used',
                    $tagName
                );
            }

            $this->fail(
                "Found obsolete HTML tags:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that no obsolete HTML attributes are used.
     *
     * Many attributes are obsolete in HTML5 and should not be used.
     *
     * @see https://html.spec.whatwg.org/multipage/obsolete.html#obsolete
     * @see https://html.spec.whatwg.org/multipage/obsolete.html#non-conforming-features
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#8.2
     * @see https://developer.mozilla.org/en-US/docs/Web/HTML/Element/meta
     */
    protected function assertNoObsoleteAttributes(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $obsoleteMap = $this->getObsoleteAttributesMap();
        $selectors = $this->buildObsoleteAttributeSelectors($obsoleteMap);

        $selector = implode(', ', $selectors);
        $elements = $html->querySelectorAll($selector);

        if ($elements->length > 0) {
            $violations = [];

            foreach ($elements as $element) {
                $tagName = mb_strtolower($element->tagName);
                $obsoleteAttrs = [];

                foreach ($element->attributes as $attr) {
                    $attrName = mb_strtolower($attr->name);

                    if ($this->isObsoleteAttribute($tagName, $attrName, $obsoleteMap)) {
                        $obsoleteAttrs[] = sprintf('%s="%s"', $attrName, $attr->value);
                    }
                }

                if (! empty($obsoleteAttrs)) {
                    $violations[] = sprintf(
                        '<%s %s> contains obsolete attribute(s): %s',
                        $tagName,
                        implode(' ', $obsoleteAttrs),
                        implode(', ', array_map(fn ($attr) => explode('=', $attr)[0], $obsoleteAttrs))
                    );
                }
            }

            $this->fail(
                "Found obsolete HTML attributes:\n".
                implode("\n", $violations)
            );
        }
    }

    private function getObsoleteAttributesMap(): array
    {
        return [
            '*' => ['dropzone', 'contextmenu', 'onshow'],
            'a' => ['charset', 'coords', 'shape', 'methods', 'name', 'rev', 'urn', 'datasrc', 'datafld'],
            'link' => ['charset', 'methods', 'rev', 'urn', 'target'],
            'option' => ['name', 'datasrc', 'dataformatas'],
            'embed' => ['name', 'hspace', 'vspace', 'align'],
            'img' => ['name', 'lowsrc', 'longdesc', 'datasrc', 'datafld', 'hspace', 'vspace', 'align', 'border'],
            'form' => ['accept'],
            'head' => ['profile'],
            'html' => ['version'],
            'menu' => ['type', 'label'],
            'param' => ['type', 'valuetype', 'datafld'],
            'script' => ['language', 'event', 'for'],
            'table' => ['datapagesize', 'summary', 'bgcolor', 'datasrc', 'dataformatas', 'width', 'align', 'cellpadding', 'cellspacing', 'frame', 'rules', 'background'],
            'td' => ['axis', 'scope', 'abbr', 'bgcolor', 'char', 'charoff', 'valign', 'width', 'align', 'height', 'nowrap', 'background'],
            'th' => ['axis', 'bgcolor', 'char', 'charoff', 'valign', 'width', 'align', 'height', 'nowrap', 'background'],
            'applet' => ['datasrc', 'datafld'],
            'button' => ['datasrc', 'datafld', 'dataformatas'],
            'div' => ['datasrc', 'datafld', 'dataformatas', 'align'],
            'frame' => ['datasrc', 'datafld'],
            'label' => ['datasrc', 'datafld', 'dataformatas'],
            'legend' => ['datasrc', 'datafld', 'dataformatas', 'align'],
            'marquee' => ['datasrc', 'datafld', 'dataformatas'],
            'span' => ['datasrc', 'datafld', 'dataformatas'],
            'fieldset' => ['datafld'],
            'body' => ['alink', 'bgcolor', 'link', 'bottommargin', 'leftmargin', 'rightmargin', 'topmargin', 'marginheight', 'marginwidth', 'text', 'vlink', 'background'],
            'col' => ['char', 'charoff', 'valign', 'width', 'align'],
            'tbody' => ['char', 'charoff', 'valign', 'align', 'background'],
            'thead' => ['char', 'charoff', 'valign', 'align', 'background'],
            'tfoot' => ['char', 'charoff', 'valign', 'align', 'background'],
            'tr' => ['char', 'charoff', 'valign', 'bgcolor', 'align', 'background'],
            'pre' => ['width'],
            'dl' => ['compact'],
            'ol' => ['compact'],
            'ul' => ['compact', 'type'],
            'h1' => ['align'],
            'h2' => ['align'],
            'h3' => ['align'],
            'h4' => ['align'],
            'h5' => ['align'],
            'h6' => ['align'],
            'caption' => ['align'],
            'p' => ['align'],
            'li' => ['type'],
            'area' => ['nohref', 'type', 'hreflang'],
            'input' => ['ismap', 'usemap', 'datasrc', 'datafld', 'dataformatas', 'hspace', 'vspace', 'align'],
            'iframe' => ['longdesc', 'datasrc', 'datafld', 'marginheight', 'marginwidth', 'hspace', 'vspace', 'align', 'allowtransparency', 'frameborder', 'framespacing', 'scrolling'],
            'object' => ['archive', 'classid', 'code', 'codebase', 'codetype', 'declare', 'standby', 'typemustmatch', 'datasrc', 'datafld', 'dataformatas', 'hspace', 'vspace', 'align', 'border'],
            'select' => ['datasrc', 'datafld', 'dataformatas'],
            'textarea' => ['datasrc', 'datafld'],
            'br' => ['clear'],
            'hr' => ['width', 'color', 'noshade', 'size', 'align'],
            'meta' => ['scheme'],
        ];
    }

    private function buildObsoleteAttributeSelectors(array $obsoleteMap): array
    {
        $selectors = [];

        foreach ($obsoleteMap as $element => $attributes) {
            foreach ($attributes as $attribute) {
                if ($element === '*') {
                    $selectors[] = "[{$attribute}]";
                } else {
                    $selectors[] = "{$element}[{$attribute}]";
                }
            }
        }

        return $selectors;
    }

    private function isObsoleteAttribute(string $tagName, string $attrName, array $obsoleteMap): bool
    {
        if (in_array($attrName, $obsoleteMap['*'] ?? [])) {
            return true;
        }

        return in_array($attrName, $obsoleteMap[$tagName] ?? []);
    }

    private function ensureDocument(HTMLDocument|string $html): HTMLDocument
    {
        if (is_string($html)) {
            $html = HTMLDocument::createFromString($html, LIBXML_HTML_NOIMPLIED);
        }

        return $html;
    }
}
