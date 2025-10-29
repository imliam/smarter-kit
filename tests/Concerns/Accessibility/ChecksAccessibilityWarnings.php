<?php

declare(strict_types=1);

namespace Tests\Concerns\Accessibility;

use Dom\HTMLDocument;

trait ChecksAccessibilityWarnings
{
    /**
     * Assert that role="presentation" is not used on images.
     *
     * Any decorative image should be marked up with aria-hidden="true" (or empty alt if img).
     * role="presentation" shall do the trick but at the time of writing, its support is too low
     * compared to empty alt or aria-hidden="true".
     *
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#1.2
     * @see https://www.w3.org/WAI/tutorials/images/decorative/
     */
    public function assertRolePresentationNotUsedOnImages(string|HTMLDocument $html): static
    {
        $document = $this->ensureDocument($html);

        $selectors = [
            'img[role="presentation"]',
            'svg[role="presentation"]',
            'area[role="presentation"]',
            'embed[role="presentation"]',
            'canvas[role="presentation"]',
            'object[role="presentation"]',
        ];

        $elements = $document->querySelectorAll(implode(', ', $selectors));

        foreach ($elements as $element) {
            $tagName = mb_strtolower($element->nodeName);
            $identifier = $this->getElementIdentifier($element);

            $this->fail(
                "The <{$tagName}>{$identifier} element uses role=\"presentation\", which has poor browser support. ".
                'Use aria-hidden="true" instead'.
                ($tagName === 'img' ? ' or an empty alt attribute' : '').
                ' to mark decorative images. '.
                'See: https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#1.2'
            );
        }

        return $this;
    }

    /**
     * Assert that SVG elements have a proper role.
     *
     * Any <svg> should either have aria-hidden="true" if decorative,
     * or a role="img" if informative.
     *
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#1.2
     * @see https://www.w3.org/TR/WCAG22/#non-text-content
     * @see https://www.w3.org/WAI/WCAG22/Techniques/failures/F65
     */
    public function assertSvgHasRole(string|HTMLDocument $html): static
    {
        $document = $this->ensureDocument($html);

        $elements = $document->querySelectorAll('svg:not([aria-hidden="true"], [role="img"])');

        foreach ($elements as $element) {
            $identifier = $this->getElementIdentifier($element);

            $this->fail(
                "The <svg>{$identifier} element must have either aria-hidden=\"true\" (if decorative) or role=\"img\" (if informative). ".
                'See: https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#1.2'
            );
        }

        return $this;
    }

    /**
     * Assert that autoplay is not used on media elements.
     *
     * A time-based media like <audio> or <video> should not autoplay,
     * because it can be quite surprising for the user.
     *
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#4.10
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#13.8
     * @see https://www.w3.org/TR/WCAG22/#audio-control
     * @see http://www.punkchip.com/autoplay-is-bad-for-all-users/
     */
    public function assertAutoplayNotUsed(string|HTMLDocument $html): static
    {
        $document = $this->ensureDocument($html);

        $elements = $document->querySelectorAll('video[autoplay], audio[autoplay]');

        foreach ($elements as $element) {
            $tagName = mb_strtolower($element->nodeName);
            $identifier = $this->getElementIdentifier($element);

            $this->fail(
                "The <{$tagName}>{$identifier} element has the autoplay attribute, which can be disruptive for users. ".
                'Remove the autoplay attribute to give users control over media playback. '.
                'See: https://www.w3.org/TR/WCAG22/#audio-control'
            );
        }

        return $this;
    }

    /**
     * Assert that media elements have controls.
     *
     * A time-based media like <audio> or <video> would be easier to use
     * if controls are activated for the user.
     *
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#4.11
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#4.12
     * @see https://www.w3.org/TR/WCAG22/#audio-description-or-media-alternative-prerecorded
     * @see https://www.w3.org/TR/WCAG22/#keyboard
     * @see https://www.w3.org/TR/WCAG22/#no-keyboard-trap
     * @see https://www.w3.org/WAI/WCAG22/Techniques/general/G4
     */
    public function assertMediaHasControls(string|HTMLDocument $html): static
    {
        $document = $this->ensureDocument($html);

        $elements = $document->querySelectorAll('video:not([controls]), audio:not([controls])');

        foreach ($elements as $element) {
            $tagName = mb_strtolower($element->nodeName);
            $identifier = $this->getElementIdentifier($element);

            $this->fail(
                "The <{$tagName}>{$identifier} element is missing the controls attribute. ".
                'Add controls to give users the ability to play, pause, and control the media. '.
                'See: https://www.w3.org/TR/WCAG22/#keyboard'
            );
        }

        return $this;
    }

    /**
     * Assert that there are no empty nodes in the body.
     *
     * Obviously void elements are empty, as well as <iframe> and <textarea> could be :empty.
     * Any other :empty tag that is not hidden is probably useless, and should be deleted.
     *
     * This test only targets tags in <body>, since most of head's tags should be empty.
     * <button> and <a> are also excluded since they are covered by their own error checks.
     * Elements with [src] are excluded to avoid false positives on <video> or <audio>
     * which may be empty if they have at least one source specified through [src].
     *
     * @see https://github.com/Heydon/REVENGE.CSS/blob/master/revenge.css#L243
     * @see https://html.spec.whatwg.org/multipage/syntax.html#void-elements
     * @see https://drafts.csswg.org/selectors-4/#the-blank-pseudo
     * @see https://developer.mozilla.org/en-US/docs/Web/CSS/:-moz-only-whitespace
     * @see https://css-tricks.com/almanac/selectors/b/blank/
     */
    public function assertNoEmptyNodes(string|HTMLDocument $html): static
    {
        $document = $this->ensureDocument($html);

        // Selector excludes:
        // - Hidden elements: [hidden], [aria-hidden]
        // - Elements with source: [src]
        // - Elements with their own checks: button, a
        // - Valid empty elements: iframe, textarea
        // - Void elements: area, base, br, col, command, embed, hr, img, input, keygen, link, meta, param, source, track, wbr
        // - Head element: title
        $selector = 'body *:empty:not([hidden], [aria-hidden], [src], button, a, iframe, textarea, '.
            'area, base, br, col, command, embed, hr, img, input, keygen, link, meta, param, source, track, wbr, title)';

        $elements = $document->querySelectorAll($selector);

        foreach ($elements as $element) {
            $tagName = mb_strtolower($element->nodeName);
            $identifier = $this->getElementIdentifier($element);

            $this->fail(
                "The <{$tagName}>{$identifier} element is empty and serves no purpose. ".
                'Remove this element or add content to it. '.
                'See: https://github.com/Heydon/REVENGE.CSS/blob/master/revenge.css#L243'
            );
        }

        return $this;
    }

    /**
     * Assert that there are no nested tables.
     *
     * There's no good reason to nest data tables: thus it probably means
     * we're facing a layout table.
     *
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#5.3
     * @see https://www.w3.org/TR/WCAG22/#meaningful-sequence
     * @see https://www.w3.org/WAI/tutorials/tables/tips/
     * @see https://github.com/karlgroves/diagnostic.css/blob/39ede15ff46bd59af9f8f30efb04cbb45b6c1ba5/diagnostic.css#L175
     */
    public function assertNoNestedTables(string|HTMLDocument $html): static
    {
        $document = $this->ensureDocument($html);

        $elements = $document->querySelectorAll('table table');

        foreach ($elements as $element) {
            $identifier = $this->getElementIdentifier($element);

            // Find the parent table for better error messaging
            $parent = $element->parentNode;
            while ($parent && $parent->nodeName !== 'TABLE') {
                $parent = $parent->parentNode;
            }

            $this->fail(
                "Found a nested <table>{$identifier} element inside another table. ".
                'Tables should not be nested as they are likely being used for layout purposes. '.
                'Use CSS for layout instead. '.
                'See: https://www.w3.org/WAI/tutorials/tables/tips/'
            );
        }

        return $this;
    }

    /**
     * Assert that data tables have a caption as the first child.
     *
     * <caption> is needed for data <table>. And it must be the :first-child.
     * Tables with role="presentation" are excluded as they are layout tables.
     *
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#5.4
     * @see https://www.w3.org/TR/WCAG22/#info-and-relationships
     * @see https://html.spec.whatwg.org/multipage/tables.html#the-caption-element
     * @see https://www.w3.org/WAI/WCAG22/Techniques/html/H39
     * @see https://github.com/imbrianj/debugCSS/blob/e04b489388870dd214aa1c1c1a07f6811210c8ef/debugCSS.css#L309
     */
    public function assertTableHasCaption(string|HTMLDocument $html): static
    {
        $document = $this->ensureDocument($html);

        // Check for tables where caption is not the first child
        $captionNotFirst = $document->querySelectorAll('table:not([role="presentation"]) > caption:not(:first-child)');

        foreach ($captionNotFirst as $element) {
            $table = $element->parentNode;
            $tableIdentifier = $this->getElementIdentifier($table);

            $this->fail(
                "The <table>{$tableIdentifier} element has a <caption> but it is not the first child. ".
                'The <caption> must be the first child of the <table> element. '.
                'See: https://html.spec.whatwg.org/multipage/tables.html#the-caption-element'
            );
        }

        // Check for tables where the first child is not a caption
        $missingCaption = $document->querySelectorAll('table:not([role="presentation"]) > *:first-child:not(caption)');

        foreach ($missingCaption as $element) {
            $table = $element->parentNode;
            $tableIdentifier = $this->getElementIdentifier($table);

            $this->fail(
                "The <table>{$tableIdentifier} element is missing a <caption> as its first child. ".
                'Data tables must have a <caption> element to provide a title or explanation. '.
                'See: https://www.w3.org/WAI/WCAG22/Techniques/html/H39'
            );
        }

        return $this;
    }

    /**
     * Assert that table structure is valid.
     *
     * <thead>, <tfoot> and <tbody> must be in this order.
     * <colgroup> must come before thead, tbody, and tfoot.
     *
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#8.2
     * @see https://www.w3.org/TR/WCAG22/#parsing
     * @see https://www.w3.org/TR/WCAG22/#name-role-value
     * @see https://github.com/imbrianj/debugCSS/blob/e04b489388870dd214aa1c1c1a07f6811210c8ef/debugCSS.css#L314
     */
    public function assertTableStructureIsValid(string|HTMLDocument $html): static
    {
        $document = $this->ensureDocument($html);

        // Check for invalid table structure ordering
        $selectors = [
            'table > tfoot ~ thead' => 'tfoot cannot come before thead',
            'table > tbody ~ tfoot' => 'tbody cannot come before tfoot',
            'table > tbody ~ thead' => 'tbody cannot come before thead',
            'table > tfoot ~ colgroup' => 'tfoot cannot come before colgroup',
            'table > tbody ~ colgroup' => 'tbody cannot come before colgroup',
            'table > thead ~ colgroup' => 'thead cannot come before colgroup',
        ];

        foreach ($selectors as $selector => $message) {
            $elements = $document->querySelectorAll($selector);

            foreach ($elements as $element) {
                $table = $element->parentNode;
                $tableIdentifier = $this->getElementIdentifier($table);
                $elementName = mb_strtolower($element->nodeName);

                $this->fail(
                    "The <table>{$tableIdentifier} element has invalid structure: {$message}. ".
                    'Table elements must be in this order: caption, colgroup, thead, tfoot, tbody. '.
                    'See: https://www.w3.org/TR/WCAG22/#parsing'
                );
            }
        }

        return $this;
    }

    /**
     * Assert that data tables with tbody have a thead.
     *
     * <thead> is strongly needed if <tbody> is present.
     * Tables with role="presentation" are excluded as they are layout tables.
     */
    public function assertTableHasThead(string|HTMLDocument $html): static
    {
        $document = $this->ensureDocument($html);

        // Check for tbody that comes right after caption (no thead)
        // or tbody as first child (no caption or thead)
        $selectors = [
            'table:not([role="presentation"]) > caption + tbody',
            'table:not([role="presentation"]) > tbody:first-child',
        ];

        $elements = $document->querySelectorAll(implode(', ', $selectors));

        foreach ($elements as $element) {
            $table = $element->parentNode;
            $tableIdentifier = $this->getElementIdentifier($table);

            $this->fail(
                "The <table>{$tableIdentifier} element has a <tbody> but is missing a <thead>. ".
                'Data tables with a <tbody> must have a <thead> to provide column headers.'
            );
        }

        return $this;
    }

    /**
     * Assert that links do not use javascript: protocol without role="button".
     *
     * The href attribute should not start with "javascript:" unless the link has
     * role="button". Links using javascript: protocol should typically be replaced
     * with proper <button> elements or have role="button" to indicate their purpose.
     * The only valid exception is bookmarklets.
     *
     * @see https://github.com/Heydon/REVENGE.CSS/blob/master/revenge.css#L165
     */
    public function assertNoJavascriptHrefWithoutRole(string|HTMLDocument $html): static
    {
        $document = $this->ensureDocument($html);

        $elements = $document->querySelectorAll('a[href^="javascript"]:not([role="button"])');

        foreach ($elements as $element) {
            $identifier = $this->getElementIdentifier($element);

            $this->fail(
                "The <a>{$identifier} element uses href=\"javascript:...\" without role=\"button\". ".
                'Links with javascript: protocol should be replaced with <button> elements or have role="button". '.
                'See: https://github.com/Heydon/REVENGE.CSS/blob/master/revenge.css#L165'
            );
        }

        return $this;
    }

    /**
     * Assert that links do not use href="#" without role="button".
     *
     * The href attribute should not contain only "#" unless the link has role="button".
     * Links with href="#" should typically be replaced with proper <button> elements
     * or have role="button" to indicate their purpose as interactive elements rather
     * than navigation links.
     *
     * @see https://github.com/Heydon/REVENGE.CSS/blob/master/revenge.css#L165
     */
    public function assertNoHashOnlyHrefWithoutRole(string|HTMLDocument $html): static
    {
        $document = $this->ensureDocument($html);

        $elements = $document->querySelectorAll('a[href="#"]:not([role="button"])');

        foreach ($elements as $element) {
            $identifier = $this->getElementIdentifier($element);

            $this->fail(
                "The <a>{$identifier} element uses href=\"#\" without role=\"button\". ".
                'Links with href="#" should be replaced with <button> elements or have role="button". '.
                'See: https://github.com/Heydon/REVENGE.CSS/blob/master/revenge.css#L165'
            );
        }

        return $this;
    }

    /**
     * Assert that elements with role="heading" have aria-level attribute.
     *
     * Though aria-level is not required by ARIA specification, it's actually better
     * to specify it to provide proper heading hierarchy information to assistive
     * technologies. The aria-level attribute indicates the hierarchical level of the
     * heading within the document structure.
     *
     * @see https://www.w3.org/TR/wai-aria-1.3/#aria-level
     * @see https://www.w3.org/TR/wai-aria-1.3/#heading
     * @see https://www.w3.org/WAI/WCAG22/Techniques/aria/ARIA12
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#9.1
     */
    public function assertHeadingRoleHasAriaLevel(string|HTMLDocument $html): static
    {
        $document = $this->ensureDocument($html);

        $elements = $document->querySelectorAll('[role="heading"]:not([aria-level])');

        foreach ($elements as $element) {
            $identifier = $this->getElementIdentifier($element);
            $tagName = mb_strtolower($element->nodeName);

            $this->fail(
                "The <{$tagName}>{$identifier} element has role=\"heading\" but is missing the aria-level attribute. ".
                'Elements with role="heading" should specify aria-level to indicate their hierarchical level.'
            );
        }

        return $this;
    }

    /**
     * Assert that label elements have a for attribute or contain a form control.
     *
     * A <label> element should either have a for attribute that references a form
     * control by ID, or it should contain a labelable form control (button, input,
     * meter, output, progress, select, or textarea). A label can have both a for
     * attribute and a contained control, as long as the for attribute points to
     * the contained control. Labels should only contain one form control.
     *
     * @see https://www.w3.org/WAI/WCAG22/Techniques/html/H44
     * @see https://www.w3.org/WAI/tutorials/forms/labels/
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#11.1.2
     * @see https://www.w3.org/TR/WCAG22/#labels-or-instructions
     */
    public function assertLabelHasForOrControl(string|HTMLDocument $html): static
    {
        $document = $this->ensureDocument($html);

        $labels = $document->querySelectorAll('label');

        foreach ($labels as $label) {
            // Check if label contains labelable form controls
            // Elements that can be labeled: button, input (except hidden), meter, output, progress, select, textarea
            $controls = $label->querySelectorAll('button, input:not([type="hidden"]), meter, output, progress, select, textarea');

            $identifier = $this->getElementIdentifier($label);

            // Check if label has neither for attribute nor nested control
            if (! $label->hasAttribute('for') && $controls->length === 0) {
                $this->fail(
                    "The <label>{$identifier} element is missing the for attribute and does not contain a form control. ".
                    'Labels should either have a for attribute referencing a form control, or contain a labelable element (button, input, meter, output, progress, select, or textarea). '.
                    'See: https://www.w3.org/WAI/WCAG22/Techniques/html/H44'
                );
            }

            // Check if label contains multiple form controls
            if ($controls->length > 1) {
                $this->fail(
                    "The <label>{$identifier} element contains multiple form controls. ".
                    'A label should only contain one form control element. '.
                    'See: https://www.w3.org/WAI/WCAG22/Techniques/html/H44'
                );
            }
        }

        return $this;
    }

    /**
     * Assert that dir and lang attributes are properly matched.
     *
     * Right-to-left (RTL) languages like Arabic and Hebrew require dir="rtl" to be
     * set. When these languages are used, the dir attribute must match. Similarly,
     * when dir="rtl" is used, it should only be used with RTL languages. Any language
     * change within RTL content should also define the appropriate dir attribute.
     *
     * @see https://alphagov.github.io/accessibility-tool-audit/test-cases.html#languageofcontent
     * @see https://www.w3.org/International/questions/qa-html-dir
     * @see https://www.w3.org/International/articles/inline-bidi-markup/#dirattribute
     * @see https://html.spec.whatwg.org/multipage/dom.html#the-dir-attribute
     */
    public function assertDirMatchesLang(string|HTMLDocument $html): static
    {
        $document = $this->ensureDocument($html);

        // Check for Arabic or Hebrew without dir="rtl"
        $rtlLangsWithoutDir = $document->querySelectorAll('[lang="ar"]:not([dir="rtl"]), [lang="he"]:not([dir="rtl"])');

        foreach ($rtlLangsWithoutDir as $element) {
            $lang = $element->getAttribute('lang');
            $identifier = $this->getElementIdentifier($element);
            $tagName = mb_strtolower($element->nodeName);

            $this->fail(
                "The <{$tagName}>{$identifier} element has lang=\"{$lang}\" but is missing dir=\"rtl\". ".
                'Right-to-left languages like Arabic and Hebrew require the dir="rtl" attribute. '.
                'See: https://www.w3.org/International/questions/qa-html-dir'
            );
        }

        // Check for language changes within RTL content without dir attribute
        $langChangesInRtl = $document->querySelectorAll('[lang="ar"] [lang]:not([dir]), [lang="he"] [lang]:not([dir])');

        foreach ($langChangesInRtl as $element) {
            $lang = $element->getAttribute('lang');
            $identifier = $this->getElementIdentifier($element);
            $tagName = mb_strtolower($element->nodeName);

            $this->fail(
                "The <{$tagName}>{$identifier} element has lang=\"{$lang}\" within RTL content but is missing a dir attribute. ".
                'Language changes within right-to-left content should define dir="ltr" or dir="rtl" as appropriate. '.
                'See: https://www.w3.org/International/articles/inline-bidi-markup/#dirattribute'
            );
        }

        // Check for dir="rtl" without Arabic or Hebrew
        $rtlDirWithoutRtlLang = $document->querySelectorAll('[dir="rtl"]:not([lang="ar"], [lang="he"])');

        foreach ($rtlDirWithoutRtlLang as $element) {
            $lang = $element->getAttribute('lang') ?: 'not set';
            $identifier = $this->getElementIdentifier($element);
            $tagName = mb_strtolower($element->nodeName);

            $this->fail(
                "The <{$tagName}>{$identifier} element has dir=\"rtl\" but lang=\"{$lang}\". ".
                'The dir="rtl" attribute should be used with right-to-left languages like Arabic (ar) or Hebrew (he). '.
                'See: https://www.w3.org/International/questions/qa-html-dir'
            );
        }

        return $this;
    }

    /** Assert that there are no accessibility warnings. */
    protected function assertNoAccessibilityWarnings(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $this->assertListItemsHaveCorrectParent($html);
        $this->assertDefinitionListStructureIsValid($html);
        $this->assertDefinitionListChildrenAreValid($html);
        $this->assertFigcaptionIsInsideFigure($html);
        $this->assertNoInvalidNesting($html);
        $this->assertNoMisplacedDiv($html);
        $this->assertNoMisusedSectioningTags($html);
        $this->assertLegendIsFirstChildOfFieldset($html);
        $this->assertSummaryIsFirstChildOfDetails($html);
        $this->assertAbbrHasTitle($html);
        $this->assertAltDoesNotContainFileName($html);
        $this->assertDecorativeImagesDoNotHaveAccessibleName($html);
        $this->assertRolePresentationNotUsedOnImages($html);
        $this->assertSvgHasRole($html);
        $this->assertAutoplayNotUsed($html);
        $this->assertMediaHasControls($html);
        $this->assertNoEmptyNodes($html);
        $this->assertNoNestedTables($html);
        $this->assertTableHasCaption($html);
        $this->assertTableStructureIsValid($html);
        $this->assertTableHasThead($html);
        $this->assertNoJavascriptHrefWithoutRole($html);
        $this->assertNoHashOnlyHrefWithoutRole($html);
        $this->assertHeadingRoleHasAriaLevel($html);
        $this->assertLabelHasForOrControl($html);
        $this->assertDirMatchesLang($html);
    }

    /**
     * Assert that list items have correct parent elements.
     *
     * The only child allowed in <ul> and <ol> is <li> - and the converse is also true.
     * <li> elements should only be children of <ul> or <ol> elements.
     *
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#8.2
     * @see https://www.w3.org/TR/WCAG22/#parsing
     * @see https://www.w3.org/TR/WCAG22/#name-role-value
     * @see https://html.spec.whatwg.org/multipage/grouping-content.html#the-li-element
     */
    protected function assertListItemsHaveCorrectParent(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $violations = [];

        // Check for non-li children in ul/ol
        $invalidChildren = $html->querySelectorAll('ul > :not(li), ol > :not(li)');
        foreach ($invalidChildren as $element) {
            $parent = $element->parentNode;
            $parentTag = $parent ? mb_strtolower($parent->nodeName) : 'unknown';
            $childTag = mb_strtolower($element->nodeName);
            $elementIdentifier = $this->getElementIdentifier($element);

            $violations[] = sprintf(
                '<%s%s> is not allowed as a child of <%s> - only <li> elements are allowed',
                $childTag,
                $elementIdentifier,
                $parentTag
            );
        }

        // Check for li elements outside ul/ol
        $orphanedLi = $html->querySelectorAll('li');
        foreach ($orphanedLi as $element) {
            $parent = $element->parentNode;
            if ($parent) {
                $parentTag = mb_strtolower($parent->nodeName);
                if ($parentTag !== 'ul' && $parentTag !== 'ol') {
                    $elementIdentifier = $this->getElementIdentifier($element);

                    $violations[] = sprintf(
                        '<li%s> must be a child of <ul> or <ol>, found within <%s>',
                        $elementIdentifier,
                        $parentTag
                    );
                }
            }
        }

        if (count($violations) > 0) {
            $this->fail(
                "Found list items with incorrect parent elements:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that definition lists have valid structure.
     *
     * <dt> and <dd> should be direct adjacent siblings, and nothing else.
     * Multiple <dd> may follow a single <dt>, but <dt> must be followed by at least one <dd>,
     * and <dd> must be preceded by <dt>.
     *
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#8.2
     * @see https://www.w3.org/TR/WCAG22/#parsing
     * @see https://www.w3.org/TR/WCAG22/#name-role-value
     * @see https://html.spec.whatwg.org/multipage/grouping-content.html#the-dl-element
     */
    protected function assertDefinitionListStructureIsValid(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $violations = [];

        // Check for dt followed by non-dd elements (dt + :not(dd))
        $dtElements = $html->querySelectorAll('dt');
        foreach ($dtElements as $dt) {
            $nextSibling = $dt->nextElementSibling;
            if ($nextSibling) {
                $nextTag = mb_strtolower($nextSibling->nodeName);
                if ($nextTag !== 'dd') {
                    $elementIdentifier = $this->getElementIdentifier($nextSibling);
                    $violations[] = sprintf(
                        '<dt> must be followed by <dd>, found <%s%s> instead',
                        $nextTag,
                        $elementIdentifier
                    );
                }
            }
        }

        // Check for dd preceded by non-dt elements (:not(dt, dd) + dd)
        $ddElements = $html->querySelectorAll('dd');
        foreach ($ddElements as $dd) {
            $prevSibling = $dd->previousElementSibling;
            if ($prevSibling) {
                $prevTag = mb_strtolower($prevSibling->nodeName);
                if ($prevTag !== 'dt' && $prevTag !== 'dd') {
                    $elementIdentifier = $this->getElementIdentifier($dd);
                    $violations[] = sprintf(
                        '<dd%s> must be preceded by <dt> or <dd>, found <%s> instead',
                        $elementIdentifier,
                        $prevTag
                    );
                }
            }
        }

        // Check for invalid children in dl elements
        $dlElements = $html->querySelectorAll('dl');
        foreach ($dlElements as $dl) {
            foreach ($dl->childNodes as $child) {
                // Skip text nodes and comments
                if ($child->nodeType !== XML_ELEMENT_NODE) {
                    continue;
                }

                $childTag = mb_strtolower($child->nodeName);
                if ($childTag !== 'dt' && $childTag !== 'dd' && $childTag !== 'div') {
                    $elementIdentifier = $this->getElementIdentifier($child);
                    $violations[] = sprintf(
                        '<%s%s> is not allowed as a child of <dl> - only <dt>, <dd>, or <div> elements are allowed',
                        $childTag,
                        $elementIdentifier
                    );
                }
            }
        }

        if (count($violations) > 0) {
            $this->fail(
                "Found definition lists with invalid structure:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that definition list children have valid nesting.
     *
     * <div>, <dt> and <dd> should be direct children of <dl>.
     * <dt> and <dd> should not appear outside of <dl> elements.
     * <dl> should only contain <dt>, <dd>, or <div> as direct children.
     *
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#8.2
     * @see https://www.w3.org/TR/WCAG22/#parsing
     * @see https://www.w3.org/TR/WCAG22/#name-role-value
     * @see https://html.spec.whatwg.org/multipage/grouping-content.html#the-dl-element
     * @see https://html.spec.whatwg.org/multipage/grouping-content.html#elementdef-dl
     */
    protected function assertDefinitionListChildrenAreValid(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $violations = [];

        // Check for dt elements not inside dl or div>dl (:not(dl) > dt)
        $dtElements = $html->querySelectorAll('dt');
        foreach ($dtElements as $dt) {
            $parent = $dt->parentNode;
            if ($parent) {
                $parentTag = mb_strtolower($parent->nodeName);
                $isValid = false;

                // dt can be direct child of dl
                if ($parentTag === 'dl') {
                    $isValid = true;
                }

                // dt can be child of div that is child of dl
                if ($parentTag === 'div' && $parent->parentNode) {
                    $grandparentTag = mb_strtolower($parent->parentNode->nodeName);
                    if ($grandparentTag === 'dl') {
                        $isValid = true;
                    }
                }

                if (! $isValid) {
                    $elementIdentifier = $this->getElementIdentifier($dt);
                    $violations[] = sprintf(
                        '<dt%s> must be a child of <dl> or <div> within <dl>, found within <%s>',
                        $elementIdentifier,
                        $parentTag
                    );
                }
            }
        }

        // Check for dd elements not inside dl or div>dl (:not(dl) > dd)
        $ddElements = $html->querySelectorAll('dd');
        foreach ($ddElements as $dd) {
            $parent = $dd->parentNode;
            if ($parent) {
                $parentTag = mb_strtolower($parent->nodeName);
                $isValid = false;

                // dd can be direct child of dl
                if ($parentTag === 'dl') {
                    $isValid = true;
                }

                // dd can be child of div that is child of dl
                if ($parentTag === 'div' && $parent->parentNode) {
                    $grandparentTag = mb_strtolower($parent->parentNode->nodeName);
                    if ($grandparentTag === 'dl') {
                        $isValid = true;
                    }
                }

                if (! $isValid) {
                    $elementIdentifier = $this->getElementIdentifier($dd);
                    $violations[] = sprintf(
                        '<dd%s> must be a child of <dl> or <div> within <dl>, found within <%s>',
                        $elementIdentifier,
                        $parentTag
                    );
                }
            }
        }

        // Check for invalid direct children in dl (dl > :not(dt, dd, div))
        $dlElements = $html->querySelectorAll('dl');
        foreach ($dlElements as $dl) {
            foreach ($dl->childNodes as $child) {
                // Skip text nodes and comments
                if ($child->nodeType !== XML_ELEMENT_NODE) {
                    continue;
                }

                $childTag = mb_strtolower($child->nodeName);
                if ($childTag !== 'dt' && $childTag !== 'dd' && $childTag !== 'div') {
                    $elementIdentifier = $this->getElementIdentifier($child);
                    $violations[] = sprintf(
                        '<%s%s> is not allowed as a direct child of <dl> - only <dt>, <dd>, or <div> elements are allowed',
                        $childTag,
                        $elementIdentifier
                    );
                }
            }
        }

        if (count($violations) > 0) {
            $this->fail(
                "Found definition lists with invalid nesting:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that figcaption elements are inside figure elements.
     *
     * <figcaption> doesn't make sense outside a <figure>.
     *
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#8.2
     * @see https://www.w3.org/TR/WCAG22/#parsing
     * @see https://www.w3.org/TR/WCAG22/#name-role-value
     * @see https://html.spec.whatwg.org/multipage/grouping-content.html#the-figcaption-element
     */
    protected function assertFigcaptionIsInsideFigure(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $violations = [];

        // Check for figcaption elements not inside figure (:not(figure) > figcaption)
        $figcaptionElements = $html->querySelectorAll('figcaption');
        foreach ($figcaptionElements as $figcaption) {
            $parent = $figcaption->parentNode;
            if ($parent) {
                $parentTag = mb_strtolower($parent->nodeName);
                if ($parentTag !== 'figure') {
                    $elementIdentifier = $this->getElementIdentifier($figcaption);
                    $violations[] = sprintf(
                        '<figcaption%s> must be inside a <figure> element, found within <%s>',
                        $elementIdentifier,
                        $parentTag
                    );
                }
            }
        }

        if (count($violations) > 0) {
            $this->fail(
                "Found <figcaption> elements outside <figure>:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that there are no invalid HTML element nestings.
     *
     * Some nestings are forbidden by HTML specifications:
     * - <main> contained in <nav>, <aside>, <footer>, <header> or <article>
     * - <address> containing <address>, <nav>, <aside>, <section>, <footer>, <header>, <article> or headings
     * - <option> and <optgroup> outside a <select>
     * - <legend> outside a <fieldset>
     *
     * Note: Some invalid nesting patterns (like <td> outside <tr>, invalid table children, etc.)
     * are automatically corrected by the HTML parser and cannot be reliably detected.
     *
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#8.2
     * @see https://html.spec.whatwg.org/multipage/grouping-content.html#elementdef-main
     * @see https://html.spec.whatwg.org/multipage/grouping-content.html#the-address-element
     * @see https://www.w3.org/TR/WCAG22/#parsing
     * @see https://www.w3.org/TR/WCAG22/#name-role-value
     */
    protected function assertNoInvalidNesting(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $violations = [];

        // Check for main contained in nav, aside, footer, header, or article
        $invalidMainElements = $html->querySelectorAll('nav main, aside main, footer main, header main, article main');
        foreach ($invalidMainElements as $element) {
            $parent = $element->parentNode;
            if ($parent) {
                $parentTag = mb_strtolower($parent->nodeName);
                $elementIdentifier = $this->getElementIdentifier($element);
                $violations[] = sprintf(
                    '<main%s> must not be contained within <%s>',
                    $elementIdentifier,
                    $parentTag
                );
            }
        }

        // Check for optgroup outside select
        $optgroupElements = $html->querySelectorAll('optgroup');
        foreach ($optgroupElements as $element) {
            $parent = $element->parentNode;
            if ($parent) {
                $parentTag = mb_strtolower($parent->nodeName);
                if ($parentTag !== 'select') {
                    $elementIdentifier = $this->getElementIdentifier($element);
                    $violations[] = sprintf(
                        '<optgroup%s> must be inside a <select> element, found within <%s>',
                        $elementIdentifier,
                        $parentTag
                    );
                }
            }
        }

        // Check for legend outside fieldset
        $legendElements = $html->querySelectorAll('legend');
        foreach ($legendElements as $element) {
            $parent = $element->parentNode;
            if ($parent) {
                $parentTag = mb_strtolower($parent->nodeName);
                if ($parentTag !== 'fieldset') {
                    $elementIdentifier = $this->getElementIdentifier($element);
                    $violations[] = sprintf(
                        '<legend%s> must be inside a <fieldset> element, found within <%s>',
                        $elementIdentifier,
                        $parentTag
                    );
                }
            }
        }

        // Check for option outside select or optgroup
        $optionElements = $html->querySelectorAll('option');
        foreach ($optionElements as $element) {
            $parent = $element->parentNode;
            if ($parent) {
                $parentTag = mb_strtolower($parent->nodeName);
                if ($parentTag !== 'select' && $parentTag !== 'optgroup') {
                    $elementIdentifier = $this->getElementIdentifier($element);
                    $violations[] = sprintf(
                        '<option%s> must be inside a <select> or <optgroup> element, found within <%s>',
                        $elementIdentifier,
                        $parentTag
                    );
                }
            }
        }

        // Check for address containing forbidden elements
        $addressElements = $html->querySelectorAll('address');
        foreach ($addressElements as $address) {
            $forbiddenInAddress = $html->querySelectorAll(
                'address h1, address h2, address h3, address h4, address h5, address h6, '.
                'address nav, address aside, address header, address footer, address address, '.
                'address article, address section'
            );

            foreach ($forbiddenInAddress as $element) {
                // Verify this element is actually within this specific address element
                $current = $element->parentNode;
                $isInThisAddress = false;
                while ($current) {
                    if ($current === $address) {
                        $isInThisAddress = true;
                        break;
                    }
                    $current = $current->parentNode;
                }

                if ($isInThisAddress) {
                    $childTag = mb_strtolower($element->nodeName);
                    $elementIdentifier = $this->getElementIdentifier($element);
                    $violations[] = sprintf(
                        '<%s%s> is not allowed inside <address> element',
                        $childTag,
                        $elementIdentifier
                    );
                }
            }
        }

        if (count($violations) > 0) {
            $this->fail(
                "Found invalid HTML element nesting:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that div elements are not inside inline elements.
     *
     * <div> elements should not be placed inside inline elements like <b>, <i>, <span>, etc.
     * Use <span> instead when you need a container inside inline elements.
     *
     * @see https://github.com/Heydon/REVENGE.CSS/blob/master/revenge.css#L326
     */
    protected function assertNoMisplacedDiv(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $violations = [];

        // Check for div inside inline elements
        $inlineElements = ['b', 'i', 'q', 'em', 'abbr', 'cite', 'code', 'span', 'small', 'label', 'strong'];
        $selectors = array_map(fn ($tag) => "{$tag} div", $inlineElements);
        $misplacedDivs = $html->querySelectorAll(implode(', ', $selectors));

        foreach ($misplacedDivs as $div) {
            $parent = $div->parentNode;
            if ($parent) {
                $parentTag = mb_strtolower($parent->nodeName);
                $elementIdentifier = $this->getElementIdentifier($div);
                $violations[] = sprintf(
                    '<div%s> should not be inside <%s> - use <span> instead for inline containers',
                    $elementIdentifier,
                    $parentTag
                );
            }
        }

        if (count($violations) > 0) {
            $this->fail(
                "Found <div> elements inside inline elements:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that sectioning tags are not misused as wrappers.
     *
     * <section>, <aside>, and <article> are sectioning tags that provide semantic meaning.
     * They should not be used as the first child of another sectioning element,
     * which indicates they are being misused as generic wrappers.
     *
     * @see https://github.com/Heydon/REVENGE.CSS/blob/master/revenge.css#L252
     * @see https://html.spec.whatwg.org/multipage/dom.html#sectioning-content
     */
    protected function assertNoMisusedSectioningTags(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $violations = [];

        // Check for sectioning tags as first child of other sectioning tags (misused as wrappers)
        $selectors = [
            'aside > aside:first-child',
            'article > aside:first-child',
            'aside > article:first-child',
            'aside > section:first-child',
            'section > section:first-child',
            'article > section:first-child',
            'article > article:first-child',
        ];

        $misusedElements = $html->querySelectorAll(implode(', ', $selectors));

        foreach ($misusedElements as $element) {
            $parent = $element->parentNode;
            if ($parent) {
                $elementTag = mb_strtolower($element->nodeName);
                $parentTag = mb_strtolower($parent->nodeName);
                $elementIdentifier = $this->getElementIdentifier($element);
                $violations[] = sprintf(
                    '<%s%s> should not be used as a wrapper - <%s> as first child of <%s> indicates misuse',
                    $elementTag,
                    $elementIdentifier,
                    $elementTag,
                    $parentTag
                );
            }
        }

        if (count($violations) > 0) {
            $this->fail(
                "Found sectioning tags misused as wrappers:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that <legend> elements are the first child of their <fieldset>.
     *
     * <legend> must be a <fieldset>'s first child. Always.
     *
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#11.6
     * @see https://www.w3.org/TR/WCAG22/#info-and-relationships
     * @see https://www.w3.org/TR/WCAG22/#labels-or-instructions
     * @see https://github.com/Heydon/REVENGE.CSS/blob/master/revenge.css#L218
     */
    protected function assertLegendIsFirstChildOfFieldset(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $violations = [];

        // Check for fieldsets with non-legend first child
        $fieldsetWithNonLegendFirst = $html->querySelectorAll('fieldset > *:not(legend):first-child');
        foreach ($fieldsetWithNonLegendFirst as $element) {
            $elementTag = mb_strtolower($element->nodeName);
            $elementIdentifier = $this->getElementIdentifier($element);
            $violations[] = sprintf(
                '<fieldset> has <%s%s> as first child - <legend> must be the first child',
                $elementTag,
                $elementIdentifier
            );
        }

        // Check for legends that are not first child
        $legendNotFirst = $html->querySelectorAll('fieldset > legend:not(:first-child)');
        foreach ($legendNotFirst as $element) {
            $elementIdentifier = $this->getElementIdentifier($element);
            $violations[] = sprintf(
                '<legend%s> is not the first child of <fieldset>',
                $elementIdentifier
            );
        }

        if (count($violations) > 0) {
            $this->fail(
                "Found <legend> elements that are not the first child of <fieldset>:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that <summary> elements are the first child of their <details>.
     *
     * <summary> must be a <details>'s first child. Always.
     *
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#11.6
     * @see https://www.w3.org/TR/WCAG22/#info-and-relationships
     * @see https://www.w3.org/TR/WCAG22/#labels-or-instructions
     * @see https://html.spec.whatwg.org/multipage/interactive-elements.html#elementdef-summary
     * @see https://html.spec.whatwg.org/multipage/interactive-elements.html#elementdef-details
     */
    protected function assertSummaryIsFirstChildOfDetails(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $violations = [];

        // Check for details with non-summary first child
        $detailsWithNonSummaryFirst = $html->querySelectorAll('details > *:not(summary):first-child');
        foreach ($detailsWithNonSummaryFirst as $element) {
            $elementTag = mb_strtolower($element->nodeName);
            $elementIdentifier = $this->getElementIdentifier($element);
            $violations[] = sprintf(
                '<details> has <%s%s> as first child - <summary> must be the first child',
                $elementTag,
                $elementIdentifier
            );
        }

        // Check for summaries that are not first child
        $summaryNotFirst = $html->querySelectorAll('details > summary:not(:first-child)');
        foreach ($summaryNotFirst as $element) {
            $elementIdentifier = $this->getElementIdentifier($element);
            $violations[] = sprintf(
                '<summary%s> is not the first child of <details>',
                $elementIdentifier
            );
        }

        if (count($violations) > 0) {
            $this->fail(
                "Found <summary> elements that are not the first child of <details>:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that <abbr> elements have a title attribute.
     *
     * Any abbreviation should give an explanation about its meaning,
     * at least on its first occurrence.
     *
     * @see https://checklists.opquast.com/en/web-quality-assurance/each-abbreviation-has-a-definition-available
     * @see https://www.w3.org/TR/WCAG22/#abbreviations
     * @see https://www.w3.org/WAI/WCAG22/Techniques/general/G70
     * @see https://www.w3.org/WAI/WCAG22/Techniques/general/G97
     * @see https://www.w3.org/WAI/WCAG22/Techniques/general/G102
     * @see https://www.w3.org/WAI/WCAG22/Techniques/html/H28
     */
    protected function assertAbbrHasTitle(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $violations = [];

        // Check for abbr without title, or with empty/whitespace-only title
        $invalidAbbr = $html->querySelectorAll('abbr:not([title])');
        foreach ($invalidAbbr as $element) {
            $elementIdentifier = $this->getElementIdentifier($element);
            $text = $element->textContent;
            $violations[] = sprintf(
                '<abbr%s> is missing a title attribute (content: "%s")',
                $elementIdentifier,
                $text
            );
        }

        // Check for abbr with empty title
        $emptyTitleAbbr = $html->querySelectorAll('abbr[title=""]');
        foreach ($emptyTitleAbbr as $element) {
            $elementIdentifier = $this->getElementIdentifier($element);
            $text = $element->textContent;
            $violations[] = sprintf(
                '<abbr%s> has an empty title attribute (content: "%s")',
                $elementIdentifier,
                $text
            );
        }

        // Check for abbr with whitespace-only title
        foreach ($html->querySelectorAll('abbr[title]') as $element) {
            $title = $element->getAttribute('title');
            if (mb_trim($title) === '') {
                $elementIdentifier = $this->getElementIdentifier($element);
                $text = $element->textContent;
                $violations[] = sprintf(
                    '<abbr%s> has a whitespace-only title attribute (content: "%s")',
                    $elementIdentifier,
                    $text
                );
            }
        }

        if (count($violations) > 0) {
            $this->fail(
                "Found <abbr> elements without a proper title attribute:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that alt attributes do not contain file names.
     *
     * A file name in [alt] is probably wrongly automated and would never help any user.
     *
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#1.2
     * @see https://www.accede-web.com/notices/html-et-css/images-et-icones/gerer-lattribut-alt-des-balises-img-et-input-typeimage/
     * @see https://www.w3.org/WAI/tutorials/images/decision-tree/
     * @see https://html.spec.whatwg.org/multipage/images.html#alt
     * @see https://www.w3.org/TR/WCAG22/#non-text-content
     * @see https://www.w3.org/WAI/WCAG22/Techniques/html/H67
     * @see https://www.w3.org/WAI/WCAG22/Techniques/failures/F39
     * @see https://www.w3.org/WAI/WCAG22/Techniques/failures/F38
     */
    protected function assertAltDoesNotContainFileName(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        // Common file extensions that should not appear in alt text
        $extensions = ['pdf', 'doc', 'docx', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'svgz', 'apng', 'mp3', 'mp4', 'mov', 'ogg', 'xls', 'xlsx', 'txt', 'zip', 'rar'];

        $selectors = [];
        $elements = ['img', 'area', 'input[type="image"]', 'embed[type="image"]', 'object[type="image"]'];

        foreach ($elements as $element) {
            foreach ($extensions as $ext) {
                $selectors[] = sprintf('%s[alt$=".%s"]', $element, $ext);
            }
        }

        $invalidElements = $html->querySelectorAll(implode(', ', $selectors));

        if ($invalidElements->length > 0) {
            $violations = [];

            foreach ($invalidElements as $element) {
                $tagName = mb_strtolower($element->tagName);
                $alt = $element->getAttribute('alt');
                $type = $element->hasAttribute('type') ? sprintf(' type="%s"', $element->getAttribute('type')) : '';
                $elementIdentifier = $this->getElementIdentifier($element);

                $violations[] = sprintf(
                    '<%s%s%s alt="%s"> contains a file name in the alt attribute',
                    $tagName,
                    $type,
                    $elementIdentifier,
                    $alt
                );
            }

            $this->fail(
                "Found elements with file names in alt attributes:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that decorative images do not have accessible names.
     *
     * Any decorative image — with [aria-hidden="true"] or empty [alt] — shouldn't have
     * [title], [aria-label], [aria-labelledby], or [aria-describedby].
     *
     * @see https://checklists.opquast.com/en/web-quality-assurance/each-decorative-image-has-a-relevant-text-alternative
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#1.2
     * @see https://www.accede-web.com/notices/html-et-css/images-et-icones/gerer-lattribut-alt-des-balises-img-et-input-typeimage/#ancre-01
     * @see https://www.w3.org/WAI/tutorials/images/decision-tree/
     * @see https://www.w3.org/TR/wai-aria-1.3/#presentation
     * @see https://html.spec.whatwg.org/multipage/images.html#alt
     * @see https://www.w3.org/TR/WCAG22/#non-text-content
     * @see https://www.w3.org/WAI/WCAG22/Techniques/html/H67
     * @see https://www.w3.org/WAI/WCAG22/Techniques/failures/F39
     * @see https://www.w3.org/WAI/WCAG22/Techniques/failures/F38
     * @see https://www.w3.org/WAI/WCAG22/Techniques/failures/F65
     */
    protected function assertDecorativeImagesDoNotHaveAccessibleName(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $namingAttributes = ['title', 'aria-label', 'aria-labelledby', 'aria-describedby'];
        $selectors = [];

        // img with empty alt and naming attributes
        foreach ($namingAttributes as $attr) {
            $selectors[] = sprintf('img[alt=""][%s]', $attr);
        }

        // area without href with alt (decorative) - should have empty alt or no naming attributes
        $selectors[] = 'area:not([href])[alt]:not([alt=""])';
        foreach ($namingAttributes as $attr) {
            $selectors[] = sprintf('area:not([href])[alt=""][%s]', $attr);
        }

        // Elements with aria-hidden="true" and naming attributes
        $elements = ['svg', 'canvas', 'embed[type="image"]', 'object[type="image"]'];
        foreach ($elements as $element) {
            foreach ($namingAttributes as $attr) {
                $selectors[] = sprintf('%s[aria-hidden="true"][%s]', $element, $attr);
            }
        }

        $invalidElements = $html->querySelectorAll(implode(', ', $selectors));

        if ($invalidElements->length > 0) {
            $violations = [];

            foreach ($invalidElements as $element) {
                $tagName = mb_strtolower($element->tagName);
                $type = $element->hasAttribute('type') ? sprintf(' type="%s"', $element->getAttribute('type')) : '';
                $elementIdentifier = $this->getElementIdentifier($element);

                // Determine which naming attribute is present
                $namingAttr = null;
                foreach ($namingAttributes as $attr) {
                    if ($element->hasAttribute($attr)) {
                        $namingAttr = $attr;
                        break;
                    }
                }

                // Determine decorative indicator
                $decorativeIndicator = match (true) {
                    $element->hasAttribute('alt') && $element->getAttribute('alt') === '' => 'empty alt',
                    $element->hasAttribute('aria-hidden') && $element->getAttribute('aria-hidden') === 'true' => 'aria-hidden="true"',
                    ! $element->hasAttribute('href') => 'no href',
                    default => 'unknown',
                };

                $violations[] = sprintf(
                    '<%s%s%s> is decorative (%s) but has [%s] attribute',
                    $tagName,
                    $type,
                    $elementIdentifier,
                    $decorativeIndicator,
                    $namingAttr ?? 'accessible name'
                );
            }

            $this->fail(
                "Found decorative images with accessible names:\n".
                implode("\n", $violations)
            );
        }
    }

    private function getElementIdentifier(mixed $element): string
    {
        if ($element->hasAttribute('id')) {
            return sprintf(' id="%s"', $element->getAttribute('id'));
        }

        if ($element->hasAttribute('name')) {
            return sprintf(' name="%s"', $element->getAttribute('name'));
        }

        return '';
    }

    private function ensureDocument(HTMLDocument|string $html): HTMLDocument
    {
        if (is_string($html)) {
            $html = HTMLDocument::createFromString($html, LIBXML_HTML_NOIMPLIED);
        }

        return $html;
    }
}
