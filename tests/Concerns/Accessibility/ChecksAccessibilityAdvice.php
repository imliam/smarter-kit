<?php

declare(strict_types=1);

namespace Tests\Concerns\Accessibility;

use Dom\HTMLDocument;

trait ChecksAccessibilityAdvice
{
    /**
     * Assert that tel links contain valid phone numbers.
     *
     * A href="tel:" should contain a valid phone number (RFC 2806).
     * Parameters (e.g., ;postd=, ;phone-context=, etc.) are stripped but not validated.
     */
    public function assertTelLinksHaveValidPhoneNumber(HTMLDocument|string $html): void
    {
        $document = $this->ensureDocument($html);
        $telLinks = $document->querySelectorAll('[href^="tel"]');
        $violations = [];

        foreach ($telLinks as $link) {
            $href = $link->getAttribute('href');
            $phoneNumber = mb_substr($href, 4);

            // Strip parameters (everything after first semicolon)
            if (str_contains($phoneNumber, ';')) {
                $phoneNumber = mb_substr($phoneNumber, 0, mb_strpos($phoneNumber, ';'));
            }

            // Remove visual separators and special dial characters for validation
            $cleanedNumber = preg_replace('/[\s\-().pwABCD*#]+/i', '', $phoneNumber);

            if (! preg_match('/^\+?\d{3,}$/', $cleanedNumber) || mb_strlen(mb_ltrim($cleanedNumber, '+')) < 10) {
                $elementIdentifier = $this->getElementIdentifier($link);
                $violations[] = sprintf('<a%s href="tel:%s"> contains invalid phone number', $elementIdentifier, $phoneNumber);
            }
        }

        if (count($violations) > 0) {
            $this->fail(
                "Found tel: links with invalid phone numbers:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that fax links contain valid phone numbers.
     *
     * A href="fax:" should contain a valid phone number (RFC 2806).
     * Parameters (e.g., ;tsub=, ;phone-context=, etc.) are stripped but not validated.
     */
    public function assertFaxLinksHaveValidPhoneNumber(HTMLDocument|string $html): void
    {
        $document = $this->ensureDocument($html);
        $faxLinks = $document->querySelectorAll('[href^="fax"]');
        $violations = [];

        foreach ($faxLinks as $link) {
            $href = $link->getAttribute('href');
            $phoneNumber = mb_substr($href, 4);

            // Strip parameters (everything after first semicolon)
            if (str_contains($phoneNumber, ';')) {
                $phoneNumber = mb_substr($phoneNumber, 0, mb_strpos($phoneNumber, ';'));
            }

            // Remove visual separators and special dial characters for validation
            $cleanedNumber = preg_replace('/[\s\-().pwABCD*#]+/i', '', $phoneNumber);

            if (! preg_match('/^\+?\d{3,}$/', $cleanedNumber) || mb_strlen(mb_ltrim($cleanedNumber, '+')) < 10) {
                $elementIdentifier = $this->getElementIdentifier($link);
                $violations[] = sprintf('<a%s href="fax:%s"> contains invalid phone number', $elementIdentifier, $phoneNumber);
            }
        }

        if (count($violations) > 0) {
            $this->fail(
                "Found fax: links with invalid phone numbers:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that modem links contain valid phone numbers.
     *
     * A href="modem:" should contain a valid phone number (RFC 2806).
     * Parameters (e.g., ;type=, ;rec=, ;phone-context=, etc.) are stripped but not validated.
     */
    public function assertModemLinksHaveValidPhoneNumber(HTMLDocument|string $html): void
    {
        $document = $this->ensureDocument($html);
        $modemLinks = $document->querySelectorAll('[href^="modem"]');
        $violations = [];

        foreach ($modemLinks as $link) {
            $href = $link->getAttribute('href');
            $phoneNumber = mb_substr($href, 6);

            // Strip parameters (everything after first semicolon)
            if (str_contains($phoneNumber, ';')) {
                $phoneNumber = mb_substr($phoneNumber, 0, mb_strpos($phoneNumber, ';'));
            }

            // Remove visual separators and special dial characters for validation
            $cleanedNumber = preg_replace('/[\s\-().pwABCD*#]+/i', '', $phoneNumber);

            if (! preg_match('/^\+?\d{3,}$/', $cleanedNumber) || mb_strlen(mb_ltrim($cleanedNumber, '+')) < 10) {
                $elementIdentifier = $this->getElementIdentifier($link);
                $violations[] = sprintf('<a%s href="modem:%s"> contains invalid phone number', $elementIdentifier, $phoneNumber);
            }
        }

        if (count($violations) > 0) {
            $this->fail(
                "Found modem: links with invalid phone numbers:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that links do not have role="button".
     *
     * A <a role="button"> should be a real <button> element instead.
     */
    public function assertNoButtonRoleOnLinks(HTMLDocument|string $html): void
    {
        $document = $this->ensureDocument($html);
        $links = $document->querySelectorAll('a[role="button"]');
        $violations = [];

        foreach ($links as $link) {
            $elementIdentifier = $this->getElementIdentifier($link);
            $violations[] = sprintf('<a%s role="button"> should be a <button> element', $elementIdentifier);
        }

        if (count($violations) > 0) {
            $this->fail(
                "Found links with role=\"button\" that should be <button> elements:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that mailto links contain valid email addresses.
    }

    /**
     * Assert that unique ARIA roles are not duplicated.
     *
     * Some ARIA roles should be unique: main, search, banner, contentinfo.
     *
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#12.6
     * @see https://www.w3.org/TR/WCAG22/#bypass-blocks
     */
    protected function assertNoDuplicatedUniqueRoles(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $uniqueRoles = ['main', 'search', 'banner', 'contentinfo'];
        $violations = [];

        foreach ($uniqueRoles as $role) {
            $elements = $html->querySelectorAll('[role="'.$role.'"]');

            if ($elements->length > 1) {
                foreach ($elements as $index => $element) {
                    // Skip the first occurrence, report subsequent ones
                    if ($index > 0) {
                        $elementIdentifier = $this->getElementIdentifier($element);
                        $tagName = mb_strtolower($element->nodeName);

                        $violations[] = sprintf(
                            '<%s%s role="%s"> is a duplicate - role="%s" should be unique',
                            $tagName,
                            $elementIdentifier,
                            $role,
                            $role
                        );
                    }
                }
            }
        }

        if (count($violations) > 0) {
            $this->fail(
                "Found duplicate unique ARIA roles:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that elements with placeholders have proper labels.
     *
     * A placeholder is not a replacement for a label. Elements with placeholders
     * must also have a title, aria-label, or aria-labelledby attribute, or an
     * associated <label> element.
     */
    protected function assertPlaceholderNotUsedAsLabel(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $elements = $html->querySelectorAll('[placeholder]:not([title]):not([aria-label]):not([aria-labelledby])');
        $violations = [];

        foreach ($elements as $element) {
            $id = $element->getAttribute('id');
            $hasLabel = false;

            // Check if there's an associated <label> element
            if ($id) {
                $label = $html->querySelector('label[for="'.$id.'"]');
                if ($label) {
                    $hasLabel = true;
                }
            }

            // Check if element is wrapped in a <label>
            $parent = $element->parentNode;
            while ($parent && ! $hasLabel && $parent->nodeName !== '#document') {
                if (mb_strtoupper($parent->nodeName) === 'LABEL') {
                    $hasLabel = true;
                    break;
                }
                $parent = $parent->parentNode;
            }

            if (! $hasLabel) {
                $elementIdentifier = $this->getElementIdentifier($element);
                $tagName = mb_strtolower($element->nodeName);
                $placeholder = $element->getAttribute('placeholder');

                $violations[] = sprintf(
                    '<%s%s placeholder="%s"> uses placeholder as label - add proper label, title, aria-label, or aria-labelledby',
                    $tagName,
                    $elementIdentifier,
                    $placeholder
                );
            }
        }

        if (count($violations) > 0) {
            $this->fail(
                "Found elements using placeholder as label:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that th elements with scope attribute only use valid values.
     *
     * The scope attribute on <th> elements must be either "col" or "row".
     *
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#5.7
     * @see https://www.w3.org/TR/WCAG22/#info-and-relationships
     * @see https://www.w3.org/WAI/WCAG22/Techniques/html/H63
     */
    protected function assertTableHeaderScopeIsValid(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $elements = $html->querySelectorAll('th[scope]');
        $violations = [];

        foreach ($elements as $element) {
            $scope = mb_strtolower(mb_trim($element->getAttribute('scope')));

            if ($scope !== 'col' && $scope !== 'row') {
                $elementIdentifier = $this->getElementIdentifier($element);

                $violations[] = sprintf(
                    '<th%s scope="%s"> has invalid scope - must be "col" or "row"',
                    $elementIdentifier,
                    $element->getAttribute('scope')
                );
            }
        }

        if (count($violations) > 0) {
            $this->fail(
                "Found <th> elements with invalid scope attributes:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that URLs use HTTPS instead of HTTP.
     *
     * When possible, resources should be loaded over HTTPS for security.
     *
     * @see https://transparencyreport.google.com/https/overview?hl=en
     * @see https://letsencrypt.org/
     * @see https://checklists.opquast.com/fr/assurance-qualite-web/les-echanges-de-donnees-sensibles-sont-securises-et-signales-comme-tels
     */
    protected function assertNoInsecureUrls(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $elements = $html->querySelectorAll('[src^="http:"], [href^="http:"]');
        $violations = [];

        foreach ($elements as $element) {
            $elementIdentifier = $this->getElementIdentifier($element);
            $tagName = mb_strtolower($element->nodeName);

            $url = $element->hasAttribute('href')
                ? $element->getAttribute('href')
                : $element->getAttribute('src');

            $violations[] = sprintf(
                '<%s%s %s="%s"> uses insecure HTTP protocol - use HTTPS instead',
                $tagName,
                $elementIdentifier,
                $element->hasAttribute('href') ? 'href' : 'src',
                $url
            );
        }

        if (count($violations) > 0) {
            $this->fail(
                "Found elements with insecure HTTP URLs:\n".
                implode("\n", $violations)
            );
        }
    }

    /** Assert that there is no accessibility advice. */
    protected function assertNoAccessibilityAdvice(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $this->assertRequiredSelectStartsWithEmptyOption($html);
        $this->assertClassAttributeNotEmpty($html);
        $this->assertIdAttributeNotEmpty($html);
        $this->assertOnlyOneVisibleMain($html);
        $this->assertOnlyOneFigcaption($html);
        $this->assertFigcaptionIsFirstOrLastChild($html);
        $this->assertMailtoLinksHaveValidEmail($html);
        $this->assertTelLinksHaveValidPhoneNumber($html);
        $this->assertFaxLinksHaveValidPhoneNumber($html);
        $this->assertModemLinksHaveValidPhoneNumber($html);
        $this->assertNoButtonRoleOnLinks($html);
        $this->assertNoDuplicatedUniqueRoles($html);
        $this->assertPlaceholderNotUsedAsLabel($html);
        $this->assertTableHeaderScopeIsValid($html);
        $this->assertNoInsecureUrls($html);
    }

    /**
     * Assert that mailto links contain valid email addresses.
     *
     * A href="mailto:" should contain a valid email.
     */
    protected function assertMailtoLinksHaveValidEmail(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $elements = $html->querySelectorAll('[href^="mailto"]');

        if ($elements->length > 0) {
            $violations = [];

            foreach ($elements as $element) {
                $href = $element->getAttribute('href');
                $email = mb_substr($href, 7); // Remove "mailto:" prefix

                // Extract just the email address (before any query parameters or additional emails)
                if (str_contains($email, '?')) {
                    $email = mb_substr($email, 0, mb_strpos($email, '?'));
                }

                // Check if there are multiple emails separated by comma
                $emails = array_map('trim', explode(',', $email));

                foreach ($emails as $singleEmail) {
                    if (! filter_var($singleEmail, FILTER_VALIDATE_EMAIL)) {
                        $elementIdentifier = $this->getElementIdentifier($element);

                        $violations[] = sprintf(
                            '<a%s href="mailto:%s"> contains invalid email address: "%s"',
                            $elementIdentifier,
                            $email,
                            $singleEmail
                        );
                    }
                }
            }

            if (count($violations) > 0) {
                $this->fail(
                    "Found mailto links with invalid email addresses:\n".
                    implode("\n", $violations)
                );
            }
        }
    }

    /**
     * Assert that figcaption is positioned as first or last child.
     *
     * <figcaption> should be first or last child.
     *
     * @see https://html.spec.whatwg.org/multipage/grouping-content.html#the-figure-element
     */
    protected function assertFigcaptionIsFirstOrLastChild(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $elements = $html->querySelectorAll('figcaption:not(:first-child):not(:last-child)');

        if ($elements->length > 0) {
            $violations = [];

            foreach ($elements as $element) {
                $elementIdentifier = $this->getElementIdentifier($element);
                $parent = $element->parentNode;
                $parentTag = $parent ? mb_strtolower($parent->nodeName) : 'unknown';

                $violations[] = sprintf(
                    '<figcaption%s> is neither first nor last child within <%s>',
                    $elementIdentifier,
                    $parentTag
                );
            }

            $this->fail(
                "Found <figcaption> elements that are not first or last child:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that figcaption is single inside its parent.
     *
     * <figcaption> should be single inside its parent.
     *
     * @see https://html.spec.whatwg.org/multipage/grouping-content.html#the-figure-element
     */
    protected function assertOnlyOneFigcaption(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $elements = $html->querySelectorAll('figcaption:not(:first-of-type)');

        if ($elements->length > 0) {
            $violations = [];

            foreach ($elements as $element) {
                $elementIdentifier = $this->getElementIdentifier($element);
                $parent = $element->parentNode;
                $parentTag = $parent ? mb_strtolower($parent->nodeName) : 'unknown';

                $violations[] = sprintf(
                    '<figcaption%s> is a second figcaption within <%s>',
                    $elementIdentifier,
                    $parentTag
                );
            }

            $this->fail(
                "Found multiple <figcaption> elements within the same parent:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that only one main element is visible at a time.
     *
     * A single <main> should be visible at a time.
     *
     * @see https://html.spec.whatwg.org/multipage/grouping-content.html#elementdef-main
     */
    protected function assertOnlyOneVisibleMain(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $allMainElements = $html->querySelectorAll('main');
        $visibleMainElements = [];

        foreach ($allMainElements as $element) {
            if (! $element->hasAttribute('hidden')) {
                $visibleMainElements[] = $element;
            }
        }

        if (count($visibleMainElements) > 1) {
            $violations = [];

            foreach (array_slice($visibleMainElements, 1) as $element) {
                $elementIdentifier = $this->getElementIdentifier($element);

                $violations[] = sprintf(
                    '<main%s> is a second visible main element',
                    $elementIdentifier
                );
            }

            $this->fail(
                "Found multiple visible <main> elements (only one should be visible at a time):\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that id attributes are not empty.
     *
     * The [id] attribute shouldn't be present if empty.
     */
    protected function assertIdAttributeNotEmpty(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $selectors = [
            '[id=""]',
            '[id=" "]',
        ];

        $elements = $html->querySelectorAll(implode(', ', $selectors));

        if ($elements->length > 0) {
            $violations = [];

            foreach ($elements as $element) {
                $id = $element->getAttribute('id');
                $tagName = $element->tagName;
                $name = $element->hasAttribute('name') ? sprintf(' name="%s"', $element->getAttribute('name')) : '';

                $violations[] = sprintf(
                    '<%s%s id="%s"> has an empty id attribute',
                    $tagName,
                    $name,
                    $id
                );
            }

            $this->fail(
                "Found elements with empty id attributes:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that class attributes are not empty.
     *
     * The [class] attribute shouldn't be present if empty.
     */
    protected function assertClassAttributeNotEmpty(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $selectors = [
            '[class=""]',
            '[class=" "]',
        ];

        $elements = $html->querySelectorAll(implode(', ', $selectors));

        if ($elements->length > 0) {
            $violations = [];

            foreach ($elements as $element) {
                $class = $element->getAttribute('class');
                $tagName = $element->tagName;
                $elementIdentifier = $this->getElementIdentifier($element);

                $violations[] = sprintf(
                    '<%s%s class="%s"> has an empty class attribute',
                    $tagName,
                    $elementIdentifier,
                    $class
                );
            }

            $this->fail(
                "Found elements with empty class attributes:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that required select elements start with an empty option.
     *
     * A <select required> element, which isn't [multiple] and whose [size] isn't greater than 1,
     * should start with an empty <option>. You may use placeholder content for this option but must
     * ensure to use an empty [value] attribute; or set a [size] attribute to the <select>, which
     * value should equal to the number of <option>s.
     *
     * @see https://html.spec.whatwg.org/multipage/form-elements.html#placeholder-label-option
     * @see https://validator.w3.org/nu/?showsource=yes&doc=https%3A%2F%2Fjsbin.com%2Ftozopid%2Fquiet
     */
    protected function assertRequiredSelectStartsWithEmptyOption(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $selectors = [
            'select[required]:not([multiple])[size="1"]',
            'select[required]:not([multiple]):not([size])',
        ];

        $elements = $html->querySelectorAll(implode(', ', $selectors));

        if ($elements->length > 0) {
            $violations = [];

            foreach ($elements as $element) {
                $options = $element->querySelectorAll('option');

                if ($options->length === 0) {
                    continue;
                }

                $firstOption = $options->item(0);

                if ($firstOption->hasAttribute('value')) {
                    $firstValue = $firstOption->getAttribute('value');
                } else {
                    $firstValue = $firstOption->textContent;
                }

                if ($firstValue !== '') {
                    $selectIdentifier = $this->getElementIdentifier($element);

                    $violations[] = sprintf(
                        '<select%s required> should start with an empty <option> (current first value: "%s")',
                        $selectIdentifier,
                        mb_substr(mb_trim($firstValue), 0, 50)
                    );
                }
            }

            if (count($violations) > 0) {
                $this->fail(
                    "Found required select elements without an empty first option:\n".
                    implode("\n", $violations)
                );
            }
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
