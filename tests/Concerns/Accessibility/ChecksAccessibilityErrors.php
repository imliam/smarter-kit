<?php

declare(strict_types=1);

namespace Tests\Concerns\Accessibility;

use Dom\Element;
use Dom\HTMLDocument;

trait ChecksAccessibilityErrors
{
    /** Assert that there are no accessibility errors. */
    protected function assertNoAccessibilityErrors(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $this->assertAttributesDoNotContainWhitespace($html);
        $this->assertTabindexNotGreaterThanZero($html);
        $this->assertHrefNotEmpty($html);
        $this->assertEmptyLinksHaveLabel($html);
        $this->assertImagesHaveAlt($html);
        $this->assertRoleImgHasLabel($html);
        $this->assertImagesHaveValidSource($html);
        $this->assertLabelForNotEmpty($html);
        $this->assertFormFieldsHaveLabel($html);
        $this->assertButtonInputsHaveValue($html);
        $this->assertButtonElementsNotEmpty($html);
        $this->assertButtonAttributesNotEmpty($html);
        $this->assertButtonsHaveType($html);
        $this->assertNonSubmitButtonsDoNotHaveFormAttributes($html);
        $this->assertDisabledButtonsAreActuallyDisabled($html);
        $this->assertInputsHaveType($html);
        $this->assertOptgroupsHaveLabel($html);
        $this->assertIframesHaveTitle($html);
        $this->assertFormsHaveAction($html);
        $this->assertHtmlHasValidLanguage($html);
        $this->assertPresentationTablesDoNotUseSemanticElements($html);
        $this->assertWidthHeightOnlyOnAppropriateElements($html);
        $this->assertNoJavascriptEventAttributes($html);
        $this->assertValidCssNamespaces($html);
        $this->assertTitleNotEmpty($html);
        $this->assertViewportAllowsZoom($html);
        $this->assertCharsetIsUtf8($html);
        $this->assertCharsetComesFirst($html);
        $this->assertDirAttributeIsValid($html);
        $this->assertAccesskeyNotUsed($html);
        $this->assertRadioAndCheckboxInputsHaveName($html);
        $this->assertRadioButtonsInsideFieldset($html);
        $this->assertSliderRoleHasRequiredAttributes($html);
        $this->assertSpinbuttonRoleHasRequiredAttributes($html);
        $this->assertCheckboxRoleHasAriaChecked($html);
        $this->assertComboboxRoleHasAriaExpanded($html);
        $this->assertScrollbarRoleHasRequiredAttributes($html);
        $this->assertNoNestedInteractiveElements($html);
    }

    /**
     * Assert that attributes that should not contain whitespace are valid.
     *
     * Some HTML attributes should not contain any whitespace - namely [id], [lang] and map[name].
     *
     * @see https://html.spec.whatwg.org/#the-id-attribute
     * @see https://html.spec.whatwg.org/#the-map-element
     */
    protected function assertAttributesDoNotContainWhitespace(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $selectors = [
            '[id*=" "]',
            '[lang*=" "]',
            'map[name*=" "]',
        ];

        $elements = $html->querySelectorAll(implode(', ', $selectors));

        if ($elements->length > 0) {
            $violations = [];

            foreach ($elements as $element) {
                $tagName = $element->tagName;
                $attribute = match (true) {
                    $element->hasAttribute('id') && str_contains($element->getAttribute('id'), ' ') => 'id',
                    $element->hasAttribute('lang') && str_contains($element->getAttribute('lang'), ' ') => 'lang',
                    mb_strtolower($tagName) === 'map' && $element->hasAttribute('name') && str_contains($element->getAttribute('name'), ' ') => 'name',
                    default => null,
                };

                if ($attribute !== null) {
                    $violations[] = sprintf(
                        '<%s %s="%s"> contains whitespace in the %s attribute',
                        $tagName,
                        $attribute,
                        $element->getAttribute($attribute),
                        $attribute
                    );
                }
            }

            $this->fail(
                "Found attributes containing whitespace:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that tabindex attribute is never greater than 0.
     *
     * The [tabindex] attribute should never be greater than 0.
     *
     * @see https://github.com/Heydon/REVENGE.CSS/blob/master/revenge.css#L337
     * @see https://www.w3.org/WAI/WCAG22/Techniques/failures/F44
     * @see https://www.scottohara.me/blog/2019/05/25/tabindex.html
     */
    protected function assertTabindexNotGreaterThanZero(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $elements = $html->querySelectorAll('[tabindex]:not([tabindex="0"], [tabindex^="-"])');

        if ($elements->length > 0) {
            $violations = [];

            foreach ($elements as $element) {
                $tabindex = $element->getAttribute('tabindex');

                if (is_numeric($tabindex) && (int) $tabindex > 0) {
                    $violations[] = sprintf(
                        '<%s tabindex="%s"> has a positive tabindex value',
                        $element->tagName,
                        $tabindex
                    );
                }
            }

            if (count($violations) > 0) {
                $this->fail(
                    "Found elements with tabindex > 0:\n".
                    implode("\n", $violations)
                );
            }
        }
    }

    /**
     * Assert that href attributes are not empty.
     *
     * The [href] attribute, if present, should not be empty.
     *
     * @see https://html.spec.whatwg.org/multipage/links.html#links-created-by-a-and-area-elements
     * @see https://github.com/Heydon/REVENGE.CSS/blob/master/revenge.css#L161
     * @see https://github.com/Heydon/REVENGE.CSS/blob/master/revenge.css#L165
     */
    protected function assertHrefNotEmpty(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $selectors = [
            'a[href=""]',
            'a[href=" "]',
        ];

        $elements = $html->querySelectorAll(implode(', ', $selectors));

        if ($elements->length > 0) {
            $violations = [];

            foreach ($elements as $element) {
                $href = $element->getAttribute('href');
                $text = mb_trim($element->textContent);

                $violations[] = sprintf(
                    '<a href="%s">%s</a> has an empty href attribute',
                    $href,
                    $text ?: '...'
                );
            }

            $this->fail(
                "Found links with empty href attributes:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that empty links have proper labels.
     *
     * An empty link should have a label, within [title], [aria-label] or targeted by [aria-labelledby].
     *
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#6.2
     * @see https://www.w3.org/TR/WCAG22/#non-text-content
     * @see https://www.w3.org/TR/WCAG22/#link-purpose-in-context
     * @see https://www.w3.org/TR/WCAG22/#link-purpose-link-only
     * @see https://www.w3.org/WAI/WCAG22/Techniques/html/H30
     * @see https://www.w3.org/WAI/WCAG22/Techniques/general/G91
     * @see https://github.com/Heydon/REVENGE.CSS/blob/master/revenge.css#L193
     */
    protected function assertEmptyLinksHaveLabel(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $selectors = [
            'a:empty[title=""]',
            'a:empty[aria-label=""]',
            'a:empty[aria-labelledby=""]',
            'a:empty:not([title], [aria-label], [aria-labelledby])',
        ];

        $elements = $html->querySelectorAll(implode(', ', $selectors));

        if ($elements->length > 0) {
            $violations = [];

            foreach ($elements as $element) {
                $href = $element->hasAttribute('href') ? $element->getAttribute('href') : '';
                $id = $element->hasAttribute('id') ? sprintf(' id="%s"', $element->getAttribute('id')) : '';
                $class = $element->hasAttribute('class') ? sprintf(' class="%s"', $element->getAttribute('class')) : '';

                $violations[] = sprintf(
                    '<a%s%s href="%s"> is empty and has no accessible label',
                    $id,
                    $class,
                    $href
                );
            }

            $this->fail(
                "Found empty links without proper labels:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that images have alt attributes.
     *
     * An <img> must have an [alt]. Always. This also applies to <area> and <input type="image">.
     *
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#1.1
     * @see https://www.w3.org/WAI/tutorials/images/decision-tree/
     * @see https://html.spec.whatwg.org/multipage/images.html#alt
     * @see https://www.w3.org/TR/WCAG22/#non-text-content
     * @see https://www.w3.org/WAI/WCAG22/Techniques/html/H36
     * @see https://www.w3.org/WAI/WCAG22/Techniques/html/H37
     * @see https://www.w3.org/WAI/WCAG22/Techniques/html/H24
     * @see https://www.w3.org/WAI/WCAG22/Techniques/failures/F65
     */
    protected function assertImagesHaveAlt(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $selectors = [
            'img[alt=" "]',
            'area[alt=" "]',
            'input[type="image"][alt=" "]',
            'img:not([alt])',
            'area:not([alt])',
            'input[type="image"]:not([alt])',
        ];

        $elements = $html->querySelectorAll(implode(', ', $selectors));

        if ($elements->length > 0) {
            $violations = [];

            foreach ($elements as $element) {
                $tagName = mb_strtolower($element->tagName);
                $src = $element->hasAttribute('src') ? $element->getAttribute('src') : '';
                $type = $element->hasAttribute('type') ? sprintf(' type="%s"', $element->getAttribute('type')) : '';

                $issue = match (true) {
                    ! $element->hasAttribute('alt') => 'is missing the alt attribute',
                    $element->getAttribute('alt') === ' ' => 'has an alt attribute with only whitespace',
                    default => 'has an invalid alt attribute',
                };

                $violations[] = sprintf(
                    '<%s%s src="%s"> %s',
                    $tagName,
                    $type,
                    $src,
                    $issue
                );
            }

            $this->fail(
                "Found images without proper alt attributes:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that elements with role="img" have proper labels.
     *
     * [role=img] without [aria-hidden=true] should either have [aria-label] or [aria-labelledby].
     * If image is decorative, please use [role=presentation] instead.
     *
     * @see https://www.w3.org/TR/wai-aria-1.3/#img
     * @see https://www.w3.org/TR/WCAG22/#non-text-content
     * @see https://www.w3.org/WAI/tutorials/images/decision-tree/
     * @see https://www.w3.org/TR/wai-aria-1.3/#presentation
     */
    protected function assertRoleImgHasLabel(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $selectors = [
            '[role="img"]:not([aria-hidden="true"], [aria-label], [aria-labelledby])',
            'svg[role="img"]:not([aria-hidden="true"], [aria-label], [aria-labelledby])',
        ];

        $elements = $html->querySelectorAll(implode(', ', $selectors));

        if ($elements->length > 0) {
            $violations = [];

            foreach ($elements as $element) {
                $tagName = mb_strtolower($element->tagName);
                $id = $element->hasAttribute('id') ? sprintf(' id="%s"', $element->getAttribute('id')) : '';
                $class = $element->hasAttribute('class') ? sprintf(' class="%s"', $element->getAttribute('class')) : '';

                $violations[] = sprintf(
                    '<%s%s%s role="img"> is missing aria-label or aria-labelledby',
                    $tagName,
                    $id,
                    $class
                );
            }

            $this->fail(
                "Found elements with role=\"img\" without proper labels:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that images have valid source attributes.
     *
     * An <img> must have an [src] or an [srcset], and it should be a valid one.
     * This also applies to <input type="image">.
     *
     * @see https://html.spec.whatwg.org/multipage/embedded-content.html#attr-img-src
     * @see https://scottjehl.github.io/picturefill/
     */
    protected function assertImagesHaveValidSource(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $selectors = [
            'img:not([src], [srcset])',
            'img[src=""]',
            'img[src=" "]',
            'img[src="#"]',
            'img[src="/"]',
            'img[srcset=""]',
            'img[srcset=" "]',
            'img[srcset="#"]',
            'img[srcset="/"]',
            'input[type="image"]:not([src], [srcset])',
            'input[type="image"][src=""]',
            'input[type="image"][src=" "]',
            'input[type="image"][src="#"]',
            'input[type="image"][src="/"]',
            'input[type="image"][srcset=""]',
            'input[type="image"][srcset=" "]',
            'input[type="image"][srcset="#"]',
            'input[type="image"][srcset="/"]',
        ];

        $elements = $html->querySelectorAll(implode(', ', $selectors));

        if ($elements->length > 0) {
            $violations = [];

            foreach ($elements as $element) {
                $tagName = mb_strtolower($element->tagName);
                $type = $element->hasAttribute('type') ? sprintf(' type="%s"', $element->getAttribute('type')) : '';
                $alt = $element->hasAttribute('alt') ? $element->getAttribute('alt') : '';

                $issue = match (true) {
                    ! $element->hasAttribute('src') && ! $element->hasAttribute('srcset') => 'is missing both src and srcset attributes',
                    $element->hasAttribute('src') && in_array($element->getAttribute('src'), ['', ' ', '#', '/']) => sprintf('has invalid src="%s"', $element->getAttribute('src')),
                    $element->hasAttribute('srcset') && in_array($element->getAttribute('srcset'), ['', ' ', '#', '/']) => sprintf('has invalid srcset="%s"', $element->getAttribute('srcset')),
                    default => 'has an invalid source',
                };

                $violations[] = sprintf(
                    '<%s%s alt="%s"> %s',
                    $tagName,
                    $type,
                    $alt,
                    $issue
                );
            }

            $this->fail(
                "Found images without valid source attributes:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that labels with for attributes have valid values.
     *
     * A <label> with a [for] attribute should label something with an [id] attribute.
     *
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#11.1.2
     * @see https://github.com/DISIC/rgaa_referentiel_en/blob/44e2bee0c710e37ca49901b1e6b8fae9b553fd5d/criteria.html#L3981
     * @see https://www.w3.org/WAI/WCAG22/Techniques/html/H44
     * @see https://www.w3.org/WAI/tutorials/forms/labels/
     * @see https://make.wordpress.org/accessibility/2017/01/16/testing-form-functionality-with-different-assistive-technology/
     * @see https://www.w3.org/TR/WCAG22/#labels-or-instructions
     * @see https://www.w3.org/TR/WCAG22/#headings-and-labels
     * @see https://www.w3.org/TR/WCAG22/#info-and-relationships
     */
    protected function assertLabelForNotEmpty(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $selectors = [
            'label[for=""]',
            'label[for=" "]',
        ];

        $elements = $html->querySelectorAll(implode(', ', $selectors));

        if ($elements->length > 0) {
            $violations = [];

            foreach ($elements as $element) {
                $forValue = $element->getAttribute('for');
                $text = mb_trim($element->textContent);

                $violations[] = sprintf(
                    '<label for="%s">%s</label> has an empty or whitespace-only for attribute',
                    $forValue,
                    $text ?: '...'
                );
            }

            $this->fail(
                "Found labels with empty or invalid for attributes:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that form fields have proper labels.
     *
     * Form fields should have labels through [id], [title], [aria-label], or [aria-labelledby].
     *
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#11.1
     * @see https://www.w3.org/TR/WCAG22/#labels-or-instructions
     * @see https://www.w3.org/TR/WCAG22/#headings-and-labels
     * @see https://www.w3.org/TR/WCAG22/#info-and-relationships
     * @see https://www.w3.org/WAI/WCAG22/Techniques/html/H44
     */
    protected function assertFormFieldsHaveLabel(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $selectors = [
            'input:not([type="button"], [type="submit"], [type="hidden"], [type="reset"], [type="image"], [id], [aria-label], [title], [aria-labelledby])',
            'textarea:not([id], [aria-label], [aria-labelledby])',
            'select:not([id], [aria-label], [aria-labelledby])',
        ];

        $elements = $html->querySelectorAll(implode(', ', $selectors));

        if ($elements->length > 0) {
            $violations = [];

            foreach ($elements as $element) {
                $tagName = mb_strtolower($element->tagName);
                $type = $element->hasAttribute('type') ? sprintf(' type="%s"', $element->getAttribute('type')) : '';
                $name = $element->hasAttribute('name') ? sprintf(' name="%s"', $element->getAttribute('name')) : '';
                $class = $element->hasAttribute('class') ? sprintf(' class="%s"', $element->getAttribute('class')) : '';

                $violations[] = sprintf(
                    '<%s%s%s%s> is missing a label (id, aria-label, title, or aria-labelledby)',
                    $tagName,
                    $type,
                    $name,
                    $class
                );
            }

            $this->fail(
                "Found form fields without proper labels:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that button-type inputs have proper labels.
     *
     * Button-type inputs should have labels through [value], [title], [aria-label], or [aria-labelledby].
     *
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#11.9
     * @see https://www.w3.org/TR/WCAG22/#name-role-value
     * @see https://www.w3.org/WAI/WCAG22/Techniques/html/H91
     */
    protected function assertButtonInputsHaveValue(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $selectors = [
            'input[type="reset"]:not([value], [title], [aria-label], [aria-labelledby])',
            'input[type="submit"]:not([value], [title], [aria-label], [aria-labelledby])',
            'input[type="button"]:not([value], [title], [aria-label], [aria-labelledby])',
        ];

        $elements = $html->querySelectorAll(implode(', ', $selectors));

        if ($elements->length > 0) {
            $violations = [];

            foreach ($elements as $element) {
                $type = $element->getAttribute('type');
                $name = $element->hasAttribute('name') ? sprintf(' name="%s"', $element->getAttribute('name')) : '';
                $class = $element->hasAttribute('class') ? sprintf(' class="%s"', $element->getAttribute('class')) : '';

                $violations[] = sprintf(
                    '<input type="%s"%s%s> is missing a label (value, title, aria-label, or aria-labelledby)',
                    $type,
                    $name,
                    $class
                );
            }

            $this->fail(
                "Found button-type inputs without proper labels:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that button elements are not empty.
     *
     * A <button> should either have content or an [aria-label], [aria-labelledby] or [title].
     *
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#11.9
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#11
     * @see https://www.w3.org/TR/WCAG22/#name-role-value
     * @see https://www.w3.org/WAI/WCAG22/Techniques/html/H91
     * @see https://github.com/Heydon/REVENGE.CSS/blob/master/revenge.css#L193
     */
    protected function assertButtonElementsNotEmpty(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $selectors = [
            'button:empty:not([aria-label], [aria-labelledby], [title])',
        ];

        $elements = $html->querySelectorAll(implode(', ', $selectors));

        if ($elements->length > 0) {
            $violations = [];

            foreach ($elements as $element) {
                $type = $element->hasAttribute('type') ? sprintf(' type="%s"', $element->getAttribute('type')) : '';
                $name = $element->hasAttribute('name') ? sprintf(' name="%s"', $element->getAttribute('name')) : '';
                $class = $element->hasAttribute('class') ? sprintf(' class="%s"', $element->getAttribute('class')) : '';

                $violations[] = sprintf(
                    '<button%s%s%s> is empty and has no accessible label',
                    $type,
                    $name,
                    $class
                );
            }

            $this->fail(
                "Found empty buttons without proper labels:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that button label attributes are not empty.
     *
     * [aria-label], [aria-labelledby] or [title] on a <button> must not be empty.
     *
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#11.9
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#11
     * @see https://www.w3.org/TR/WCAG22/#name-role-value
     * @see https://www.w3.org/WAI/WCAG22/Techniques/html/H91
     * @see https://github.com/Heydon/REVENGE.CSS/blob/master/revenge.css#L193
     */
    protected function assertButtonAttributesNotEmpty(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $selectors = [
            'button[title=""]',
            'button[aria-label=""]',
            'button[aria-labelledby=""]',
        ];

        $elements = $html->querySelectorAll(implode(', ', $selectors));

        if ($elements->length > 0) {
            $violations = [];

            foreach ($elements as $element) {
                $type = $element->hasAttribute('type') ? sprintf(' type="%s"', $element->getAttribute('type')) : '';
                $text = mb_trim($element->textContent);

                $emptyAttribute = match (true) {
                    $element->hasAttribute('title') && $element->getAttribute('title') === '' => 'title',
                    $element->hasAttribute('aria-label') && $element->getAttribute('aria-label') === '' => 'aria-label',
                    $element->hasAttribute('aria-labelledby') && $element->getAttribute('aria-labelledby') === '' => 'aria-labelledby',
                    default => 'unknown',
                };

                $violations[] = sprintf(
                    '<button%s>%s</button> has an empty %s attribute',
                    $type,
                    $text ?: '...',
                    $emptyAttribute
                );
            }

            $this->fail(
                "Found buttons with empty label attributes:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that buttons have proper type attributes.
     *
     * A <button> has a default [type] value of "submit". Outside a form, if it doesn't submit
     * anything, it should have [type="button"], or mention form submission with [form],
     * [formaction], or [formtarget] attributes.
     *
     * @see https://html.spec.whatwg.org/multipage/forms.html#the-button-element
     * @see https://github.com/Heydon/REVENGE.CSS/blob/master/revenge.css#L189
     */
    protected function assertButtonsHaveType(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $selectors = [
            'button:not([type], [form], [formaction], [formtarget])',
        ];

        $elements = $html->querySelectorAll(implode(', ', $selectors));

        if ($elements->length > 0) {
            $violations = [];

            foreach ($elements as $element) {
                $text = mb_trim($element->textContent);
                $name = $element->hasAttribute('name') ? sprintf(' name="%s"', $element->getAttribute('name')) : '';
                $class = $element->hasAttribute('class') ? sprintf(' class="%s"', $element->getAttribute('class')) : '';

                $violations[] = sprintf(
                    '<button%s%s>%s</button> is missing a type attribute',
                    $name,
                    $class,
                    $text ?: '...'
                );
            }

            $this->fail(
                "Found buttons without type attributes:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that non-submit buttons do not have form attributes.
     *
     * Buttons with type="reset" or type="button" should not use form-related attributes
     * (formmethod, formaction, formtarget, formenctype, formnovalidate) as these are
     * only valid for submit buttons.
     *
     * @see https://html.spec.whatwg.org/multipage/forms.html#the-button-element
     */
    protected function assertNonSubmitButtonsDoNotHaveFormAttributes(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $selector = 'button[type="reset"][formmethod], '.
            'button[type="reset"][formaction], '.
            'button[type="reset"][formtarget], '.
            'button[type="reset"][formenctype], '.
            'button[type="reset"][formnovalidate], '.
            'button[type="button"][formmethod], '.
            'button[type="button"][formaction], '.
            'button[type="button"][formtarget], '.
            'button[type="button"][formenctype], '.
            'button[type="button"][formnovalidate]';

        $buttons = $html->querySelectorAll($selector);
        $violations = [];

        foreach ($buttons as $button) {
            $type = $button->getAttribute('type');
            $invalidAttrs = [];

            foreach (['formmethod', 'formaction', 'formtarget', 'formenctype', 'formnovalidate'] as $attr) {
                if ($button->hasAttribute($attr)) {
                    $invalidAttrs[] = $attr;
                }
            }

            $buttonHtml = $html->saveHTML($button);
            $violations[] = "- Button with type=\"{$type}\" has invalid form attributes [".implode(', ', $invalidAttrs).']: '.mb_trim($buttonHtml);
        }

        if (count($violations) > 0) {
            $count = count($violations);
            $buttonText = $count === 1 ? 'button' : 'buttons';

            $this->fail(
                "Found {$count} {$buttonText} with type=\"reset\" or type=\"button\" using invalid form attributes:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that buttons styled as disabled are actually disabled.
     *
     * Buttons with class names containing "disabled" should use the proper [disabled]
     * or [readonly] attributes to ensure they are truly disabled for assistive technologies
     * and keyboard users, not just visually styled to appear disabled.
     *
     * @see https://github.com/Heydon/REVENGE.CSS/blob/master/revenge.css#L122
     */
    protected function assertDisabledButtonsAreActuallyDisabled(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $selector = 'button[class*="disabled"]:not([disabled], [readonly])';
        $buttons = $html->querySelectorAll($selector);
        $violations = [];

        foreach ($buttons as $button) {
            $class = $button->getAttribute('class');
            $buttonHtml = $html->saveHTML($button);
            $violations[] = "- Button with class=\"{$class}\" is styled as disabled but is not actually disabled: ".mb_trim($buttonHtml);
        }

        if (count($violations) > 0) {
            $count = count($violations);
            $buttonText = $count === 1 ? 'button' : 'buttons';

            $this->fail(
                "Found {$count} {$buttonText} styled as disabled without proper disabled or readonly attributes:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that input elements have a type attribute.
     *
     * Input elements need a [type] attribute to tell the user what kind of data is expected.
     * The type attribute should not be missing, empty, or contain only whitespace.
     *
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#11.11
     * @see https://www.w3.org/TR/WCAG22/#error-suggestion
     */
    protected function assertInputsHaveType(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $selector = 'input:not([type]), input[type=" "], input[type=""]';
        $inputs = $html->querySelectorAll($selector);
        $violations = [];

        foreach ($inputs as $input) {
            $inputHtml = $html->saveHTML($input);
            $id = $input->getAttribute('id');
            $name = $input->getAttribute('name');

            $identifier = '';
            if ($id) {
                $identifier = " (id=\"{$id}\")";
            } elseif ($name) {
                $identifier = " (name=\"{$name}\")";
            }

            $violations[] = "- Input{$identifier} is missing a valid type attribute: ".mb_trim($inputHtml);
        }

        if (count($violations) > 0) {
            $count = count($violations);
            $inputText = $count === 1 ? 'input' : 'inputs';

            $this->fail(
                "Found {$count} {$inputText} without a valid type attribute:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that optgroup elements have a label attribute.
     *
     * Optgroup elements need a [label] attribute to explain what options are grouped together.
     * This helps users understand the organization of options within a select element.
     *
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#11.8
     * @see https://www.w3.org/WAI/WCAG22/Techniques/html/H85
     * @see https://www.w3.org/TR/2014/NOTE-WCAG20-TECHS-20140916/H85
     */
    protected function assertOptgroupsHaveLabel(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $selector = 'optgroup:not([label])';
        $optgroups = $html->querySelectorAll($selector);
        $violations = [];

        foreach ($optgroups as $optgroup) {
            $optgroupHtml = $html->saveHTML($optgroup);
            $violations[] = '- Optgroup is missing a label attribute: '.mb_trim($optgroupHtml);
        }

        if (count($violations) > 0) {
            $count = count($violations);
            $optgroupText = $count === 1 ? 'optgroup' : 'optgroups';

            $this->fail(
                "Found {$count} {$optgroupText} without a label attribute:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that iframe elements have a title attribute.
     *
     * Iframes need a [title] attribute to tell the user what to expect inside the iframe.
     * The title attribute should not be missing, empty, or contain only whitespace.
     *
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#2.1
     * @see https://www.w3.org/TR/WCAG22/#name-role-value
     * @see https://www.w3.org/WAI/WCAG22/Techniques/html/H64
     */
    protected function assertIframesHaveTitle(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $selector = 'iframe:not([title]), iframe[title=" "], iframe[title=""]';
        $iframes = $html->querySelectorAll($selector);
        $violations = [];

        foreach ($iframes as $iframe) {
            $iframeHtml = $html->saveHTML($iframe);
            $src = $iframe->getAttribute('src');
            $srcdoc = $iframe->getAttribute('srcdoc');

            $identifier = '';
            if ($src) {
                $identifier = " (src=\"{$src}\")";
            } elseif ($srcdoc) {
                $truncatedSrcdoc = mb_strlen($srcdoc) > 50 ? mb_substr($srcdoc, 0, 50).'...' : $srcdoc;
                $identifier = " (srcdoc=\"{$truncatedSrcdoc}\")";
            }

            $violations[] = "- Iframe{$identifier} is missing a valid title attribute: ".mb_trim($iframeHtml);
        }

        if (count($violations) > 0) {
            $count = count($violations);
            $iframeText = $count === 1 ? 'iframe' : 'iframes';

            $this->fail(
                "Found {$count} {$iframeText} without a valid title attribute:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that form elements have an action attribute.
     *
     * Forms should have an [action] attribute to define where the form data is sent.
     * The action attribute should not be missing, empty, or contain only whitespace.
     *
     * @see https://github.com/Heydon/REVENGE.CSS/blob/master/revenge.css#L214
     */
    protected function assertFormsHaveAction(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $selector = 'form:not([action]), form[action=" "], form[action=""]';
        $forms = $html->querySelectorAll($selector);
        $violations = [];

        foreach ($forms as $form) {
            $formHtml = $html->saveHTML($form);
            $id = $form->getAttribute('id');
            $name = $form->getAttribute('name');

            $identifier = '';
            if ($id) {
                $identifier = " (id=\"{$id}\")";
            } elseif ($name) {
                $identifier = " (name=\"{$name}\")";
            }

            $violations[] = "- Form{$identifier} is missing a valid action attribute: ".mb_trim($formHtml);
        }

        if (count($violations) > 0) {
            $count = count($violations);
            $formText = $count === 1 ? 'form' : 'forms';

            $this->fail(
                "Found {$count} {$formText} without a valid action attribute:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that the html element has a valid language attribute.
     *
     * The <html> element must indicate to User Agents the human language used in the document.
     * The lang attribute should not be missing, empty, or contain whitespace.
     *
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#8.3
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#8.4
     * @see https://www.w3.org/TR/WCAG22/#language-of-page
     * @see https://www.w3.org/WAI/WCAG22/Techniques/html/H57
     * @see https://html.spec.whatwg.org/#attr-lang
     * @see https://www.w3.org/WAI/WCAG22/Understanding/language-of-page
     */
    protected function assertHtmlHasValidLanguage(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $selector = 'html:not([lang]), html[lang*=" "], html[lang=""]';
        $htmlElements = $html->querySelectorAll($selector);
        $violations = [];

        foreach ($htmlElements as $htmlElement) {
            $lang = $htmlElement->getAttribute('lang');

            if (! $htmlElement->hasAttribute('lang')) {
                $violations[] = '- HTML element is missing a lang attribute';
            } elseif ($lang === '') {
                $violations[] = '- HTML element has an empty lang attribute';
            } elseif (str_contains($lang, ' ')) {
                $violations[] = "- HTML element has a lang attribute containing whitespace: lang=\"{$lang}\"";
            }
        }

        if (count($violations) > 0) {
            $this->fail(
                "HTML element must have a valid language defined:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that presentation tables do not use semantic table elements.
     *
     * Tables with role="presentation" are used for layout and should not use semantic
     * elements or attributes meant to organize data, such as <th>, <thead>, <tfoot>,
     * <caption>, <colgroup>, or attributes like axis, scope, and headers.
     *
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#5.8
     * @see https://www.w3.org/WAI/WCAG22/Techniques/failures/F46
     * @see https://www.w3.org/WAI/WCAG22/Techniques/failures/F49
     * @see https://www.w3.org/TR/WCAG22/#info-and-relationships
     */
    protected function assertPresentationTablesDoNotUseSemanticElements(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $selector = 'table[role="presentation"] th, '.
            'table[role="presentation"] thead, '.
            'table[role="presentation"] tfoot, '.
            'table[role="presentation"] caption, '.
            'table[role="presentation"] colgroup, '.
            'table[role="presentation"] [axis], '.
            'table[role="presentation"] [scope], '.
            'table[role="presentation"] [headers]';

        $elements = $html->querySelectorAll($selector);
        $violations = [];

        foreach ($elements as $element) {
            $tagName = mb_strtolower($element->nodeName);
            $elementHtml = $html->saveHTML($element);

            if (in_array($tagName, ['th', 'thead', 'tfoot', 'caption', 'colgroup'])) {
                $violations[] = "- Presentation table contains semantic element <{$tagName}>: ".mb_trim($elementHtml);
            } else {
                $semanticAttrs = [];
                foreach (['axis', 'scope', 'headers'] as $attr) {
                    if ($element->hasAttribute($attr)) {
                        $semanticAttrs[] = $attr;
                    }
                }
                $attrList = implode(', ', $semanticAttrs);
                $violations[] = "- Presentation table contains element with semantic attribute(s) [{$attrList}]: ".mb_trim($elementHtml);
            }
        }

        if (count($violations) > 0) {
            $count = count($violations);
            $elementText = $count === 1 ? 'element' : 'elements';

            $this->fail(
                "Found {$count} semantic {$elementText} in presentation tables:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that width and height attributes are only used on appropriate elements.
     *
     * Width and height are presentation information and should be in CSS, not markup.
     * They are only allowed on specific elements: img, object, embed, svg, canvas,
     * picture > source, and iframe.
     *
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#10
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#10.1
     * @see https://www.w3.org/TR/WCAG22/#info-and-relationships
     * @see https://www.w3.org/TR/WCAG22/#meaningful-sequence
     */
    protected function assertWidthHeightOnlyOnAppropriateElements(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        // Find elements with width or height that are NOT in the allowed list
        $widthSelector = ':not(img):not(object):not(embed):not(svg):not(canvas):not(iframe):not(picture > source)[width]';
        $heightSelector = ':not(img):not(object):not(embed):not(svg):not(canvas):not(iframe):not(picture > source)[height]';

        $widthElements = $html->querySelectorAll($widthSelector);
        $heightElements = $html->querySelectorAll($heightSelector);

        $violations = [];
        $processedElements = [];

        foreach ($widthElements as $element) {
            $tagName = mb_strtolower($element->nodeName);
            $elementHtml = $html->saveHTML($element);
            $elementKey = spl_object_id($element);

            if (! isset($processedElements[$elementKey])) {
                $processedElements[$elementKey] = true;
                $violations[] = "- Element <{$tagName}> has inappropriate width attribute: ".mb_trim($elementHtml);
            }
        }

        foreach ($heightElements as $element) {
            $tagName = mb_strtolower($element->nodeName);
            $elementHtml = $html->saveHTML($element);
            $elementKey = spl_object_id($element);

            if (! isset($processedElements[$elementKey])) {
                $processedElements[$elementKey] = true;
                $violations[] = "- Element <{$tagName}> has inappropriate height attribute: ".mb_trim($elementHtml);
            }
        }

        if (count($violations) > 0) {
            $count = count($violations);
            $elementText = $count === 1 ? 'element' : 'elements';

            $this->fail(
                "Found {$count} {$elementText} with inappropriate width or height attributes (use CSS instead):\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that JavaScript event attributes are not used.
     *
     * JavaScript event attributes (such as onclick, onmouseover, etc.) should not be used.
     * Prefer either CSS pseudo-classes (:hover, :focus, :active, etc.) or JS event listeners.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/CSS/Pseudo-classes
     * @see https://developer.mozilla.org/en-US/docs/Web/API/EventTarget/addEventListener
     */
    protected function assertNoJavascriptEventAttributes(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $selector = '[onafterprint], [onbeforeprint], [onbeforeunload], '.
            '[onerror], [onhaschange], [onload], [onmessage], '.
            '[onoffline], [ononline], [onpagehide], [onpageshow], '.
            '[onpopstate], [onredo], [onresize], [onstorage], '.
            '[onundo], [onunload], '.
            '[onblur], [onchage], [oncontextmenu], [onfocus], '.
            '[onformchange], [onforminput], [oninput], [oninvalid], '.
            '[onreset], [onselect], [onsubmit], '.
            '[onkeydown], [onkeypress], [onkeyup], '.
            '[onclick], [ondblclick], [ondrag], [ondragend], '.
            '[ondragenter], [ondragleave], [ondragover], '.
            '[ondragstart], [ondrop], [onmousedown], [onmousemove], '.
            '[onmouseout], [onmouseover], [onmouseup], [onmousewheel], '.
            '[onscroll], '.
            '[onabort], [oncanplay], [oncanplaythrough], '.
            '[ondurationchange], [onemptied], [onended], '.
            '[onloadeddata], [onloadedmetadata], [onloadstart], '.
            '[onpause], [onplay], [onplaying], [onprogress], '.
            '[onratechange], [onreadystatechange], [onseeked], '.
            '[onseeking], [onstalled], [onsuspend], [ontimeupdate], '.
            '[onvolumechange], [onwaiting]';

        $elements = $html->querySelectorAll($selector);
        $violations = [];

        foreach ($elements as $element) {
            $tagName = mb_strtolower($element->nodeName);
            $elementHtml = $html->saveHTML($element);

            // Find which event attribute is present
            $eventAttributes = [];
            foreach ($element->attributes as $attr) {
                if (str_starts_with(mb_strtolower($attr->name), 'on')) {
                    $eventAttributes[] = $attr->name;
                }
            }

            $attributesList = implode(', ', $eventAttributes);
            $violations[] = "- Element <{$tagName}> has JavaScript event attribute(s) [{$attributesList}]: ".mb_trim($elementHtml);
        }

        if (count($violations) > 0) {
            $count = count($violations);
            $elementText = $count === 1 ? 'element' : 'elements';

            $this->fail(
                "Found {$count} {$elementText} with JavaScript event attributes (use CSS pseudo-classes or addEventListener instead):\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that CSS namespaces (id and class attributes) use valid identifiers.
     *
     * CSS identifiers should not start with a digit, two hyphens, or a hyphen followed by a digit.
     * These patterns are invalid according to CSS3 selectors specification.
     *
     * @see https://www.w3.org/TR/2011/REC-css3-selectors-20110929/#w3cselgrammar
     * @see https://www.w3.org/Style/CSS/Test/CSS3/Selectors/current/
     */
    protected function assertValidCssNamespaces(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        // Query all elements with id or class attributes
        $elements = $html->querySelectorAll('[id], [class]');
        $violations = [];

        foreach ($elements as $element) {
            $tagName = mb_strtolower($element->nodeName);
            $elementHtml = $html->saveHTML($element);

            $invalidIdentifiers = [];

            // Check id attribute
            if ($element->hasAttribute('id')) {
                $id = $element->getAttribute('id');
                if ($this->isInvalidCssIdentifier($id)) {
                    $invalidIdentifiers[] = "id=\"{$id}\"";
                }
            }

            // Check class attribute
            if ($element->hasAttribute('class')) {
                $classes = mb_trim($element->getAttribute('class'));
                if ($classes !== '') {
                    $classList = preg_split('/\s+/', $classes);

                    foreach ($classList as $class) {
                        if ($this->isInvalidCssIdentifier($class)) {
                            $invalidIdentifiers[] = "class=\"{$class}\"";
                        }
                    }
                }
            }

            if (count($invalidIdentifiers) > 0) {
                $identifiersList = implode(', ', $invalidIdentifiers);
                $violations[] = "- Element <{$tagName}> has invalid CSS identifier(s) [{$identifiersList}]: ".mb_trim($elementHtml);
            }
        }

        if (count($violations) > 0) {
            $count = count($violations);
            $elementText = $count === 1 ? 'element' : 'elements';

            $this->fail(
                "Found {$count} {$elementText} with invalid CSS identifiers (must not start with digit, --, or -digit):\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that the title tag is not empty.
     *
     * The <title> tag is the first thing read aloud by screen readers to announce the page title,
     * and also the first thing displayed on search engines' results pages.
     *
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#8.5
     * @see https://html.spec.whatwg.org/multipage/semantics.html#the-title-element
     * @see https://www.w3.org/TR/WCAG22/#page-titled
     * @see https://www.w3.org/WAI/WCAG22/Techniques/html/H25
     */
    protected function assertTitleNotEmpty(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $titles = $html->querySelectorAll('title');
        $violations = [];

        if (count($titles) === 0) {
            $this->fail('Document is missing a <title> tag');
        }

        foreach ($titles as $title) {
            $content = $title->textContent ?? '';
            $trimmedContent = mb_trim($content);

            if ($trimmedContent === '') {
                $titleHtml = $html->saveHTML($title);
                $violations[] = '- Title tag is empty or contains only whitespace: '.mb_trim($titleHtml);
            }
        }

        if (count($violations) > 0) {
            $count = count($violations);
            $tagText = $count === 1 ? 'tag' : 'tags';

            $this->fail(
                "Found {$count} empty title {$tagText}:\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that viewport meta tag allows zoom.
     *
     * Users should be able to zoom in or out the page to improve readability and comfort.
     * The viewport meta tag should not disable zooming with user-scalable=no, maximum-scale, or minimum-scale.
     *
     * @see https://dequeuniversity.com/rules/axe/2.1/meta-viewport
     * @see https://www.w3.org/TR/WCAG22/#resize-text
     * @see https://www.w3.org/WAI/WCAG22/Understanding/resize-text.html
     * @see https://adrianroselli.com/2015/10/dont-disable-zoom.html
     */
    protected function assertViewportAllowsZoom(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $selector = 'meta[name="viewport"][content*="maximum-scale"], '.
            'meta[name="viewport"][content*="minimum-scale"], '.
            'meta[name="viewport"][content*="user-scalable=no"]';

        $elements = $html->querySelectorAll($selector);
        $violations = [];

        foreach ($elements as $element) {
            $content = $element->getAttribute('content') ?? '';
            $elementHtml = $html->saveHTML($element);

            $issues = [];

            // Check for user-scalable=no
            if (str_contains($content, 'user-scalable=no')) {
                $issues[] = 'user-scalable=no';
            }

            // Check for maximum-scale (restrictive if <= 2)
            if (preg_match('/maximum-scale\s*=\s*([0-9.]+)/', $content, $matches)) {
                $maxScale = (float) $matches[1];
                if ($maxScale <= 2.0) {
                    $issues[] = "maximum-scale={$matches[1]}";
                }
            }

            // Check for minimum-scale (restrictive if >= 1)
            if (preg_match('/minimum-scale\s*=\s*([0-9.]+)/', $content, $matches)) {
                $minScale = (float) $matches[1];
                if ($minScale >= 1.0) {
                    $issues[] = "minimum-scale={$matches[1]}";
                }
            }

            if (count($issues) > 0) {
                $issuesList = implode(', ', $issues);
                $violations[] = "- Viewport meta tag restricts zoom [{$issuesList}]: ".mb_trim($elementHtml);
            }
        }

        if (count($violations) > 0) {
            $count = count($violations);
            $tagText = $count === 1 ? 'tag' : 'tags';

            $this->fail(
                "Found {$count} viewport meta {$tagText} that restrict zoom (users should be able to zoom for readability):\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that charset is UTF-8.
     *
     * Using UTF-8 character encoding is recommended by the W3C. It supports many languages
     * and can accommodate pages and forms in any mixture of those languages. It also avoids
     * some security issues exploiting other charsets.
     *
     * @see https://html.spec.whatwg.org/multipage/semantics.html#attr-meta-charset
     * @see https://www.w3.org/International/tutorials/tutorial-char-enc/
     * @see https://www.w3.org/TR/WCAG22/#readable
     * @see https://validator.w3.org/i18n-checker/
     */
    protected function assertCharsetIsUtf8(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        // Find meta tags with charset attribute that are not UTF-8
        $selector = 'meta[charset]:not([charset="utf-8"], [charset="UTF-8"])';
        $elements = $html->querySelectorAll($selector);
        $violations = [];

        foreach ($elements as $element) {
            $charset = $element->getAttribute('charset') ?? '';
            $elementHtml = $html->saveHTML($element);
            $violations[] = "- Meta charset is not UTF-8 [charset=\"{$charset}\"]: ".mb_trim($elementHtml);
        }

        if (count($violations) > 0) {
            $count = count($violations);
            $tagText = $count === 1 ? 'tag' : 'tags';

            $this->fail(
                "Found {$count} meta {$tagText} with incorrect charset (use UTF-8 for maximum compatibility and security):\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that charset declaration comes first in head.
     *
     * The <meta> element declaring the encoding must be inside the <head> element and within
     * the first 1024 bytes of the HTML. Declaring the charset before the <title> tag prevents
     * an old security exploit using UTF-7.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTML/Element/meta
     * @see https://html.spec.whatwg.org/multipage/semantics.html#charset
     */
    protected function assertCharsetComesFirst(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        // Find first children of head that are NOT charset meta tags
        // This selector finds the first child of head that doesn't have a charset attribute
        $selector = 'head :first-child:not([charset])';
        $elements = $html->querySelectorAll($selector);
        $violations = [];

        foreach ($elements as $element) {
            $tagName = mb_strtolower($element->nodeName);
            $elementHtml = $html->saveHTML($element);
            $violations[] = "- First child of <head> is <{$tagName}> instead of charset meta tag: ".mb_trim($elementHtml);
        }

        if (count($violations) > 0) {
            $count = count($violations);
            $elementText = $count === 1 ? 'element' : 'elements';

            $this->fail(
                "Found {$count} <head> {$elementText} where charset is not declared first (charset must be the first element for security and compatibility):\n".
                implode("\n", $violations)
            );
        }
    }

    /**
     * Assert that radio and checkbox inputs have a [name] attribute for grouping.
     *
     * Inputs with type="radio" or type="checkbox" are usually grouped together. The [name] attribute
     * programmatically associates them, which is essential for accessibility and form functionality.
     * Radio buttons must always have a name, while checkboxes need a name when there are multiple checkboxes.
     *
     * @see https://www.w3.org/WAI/tutorials/forms/grouping/
     * @see https://html.spec.whatwg.org/multipage/forms.html#checkbox-state-typecheckbox
     * @see https://html.spec.whatwg.org/multipage/forms.html#radio-button-state-typeradio
     * @see https://html.spec.whatwg.org/multipage/forms.html#element-attrdef-formelements-name
     * @see https://www.w3.org/WAI/WCAG22/Techniques/html/H71
     */
    protected function assertRadioAndCheckboxInputsHaveName(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        // Radio buttons without name
        $radiosWithoutName = $html->querySelectorAll('[type="radio"]:not([name])');

        // Checkboxes without name (but not if it's the only checkbox of its type)
        $checkboxesWithoutName = $html->querySelectorAll('[type="checkbox"]:not([name])');

        // Filter checkboxes to only include those that have siblings of the same type
        $problematicCheckboxes = [];
        foreach ($checkboxesWithoutName as $checkbox) {
            // Count all checkboxes in the document
            $allCheckboxes = $html->querySelectorAll('[type="checkbox"]');
            if (count($allCheckboxes) > 1) {
                $problematicCheckboxes[] = $checkbox;
            }
        }

        if (count($radiosWithoutName) === 0 && count($problematicCheckboxes) === 0) {
            $this->assertTrue(true);

            return;
        }

        $violations = [];

        foreach ($radiosWithoutName as $radio) {
            $id = $radio->getAttribute('id');
            $identifier = $id ? " id=\"{$id}\"" : '';
            $violations[] = "- <input type=\"radio\"{$identifier}> lacks [name] attribute (required for grouping)";
        }

        foreach ($problematicCheckboxes as $checkbox) {
            $id = $checkbox->getAttribute('id');
            $identifier = $id ? " id=\"{$id}\"" : '';
            $violations[] = "- <input type=\"checkbox\"{$identifier}> lacks [name] attribute (required when multiple checkboxes exist)";
        }

        $count = count($violations);
        $elementText = $count === 1 ? 'input' : 'inputs';

        $this->fail(
            "Found {$count} radio/checkbox {$elementText} without [name] attribute (required for proper grouping):\n".
            implode("\n", $violations)
        );
    }

    /**
     * Assert that radio buttons are inside a <fieldset> element.
     *
     * Radio buttons should be grouped by a parent <fieldset> and described by its <legend> for better accessibility.
     * While WCAG doesn't strictly require this, it strongly recommends it for proper grouping context.
     * Note: This is an opinionated check - WCAG states "where the individual label associated with each
     * particular control provides a sufficient description, the use of the fieldset and legend elements is not required."
     *
     * @see https://www.w3.org/WAI/tutorials/forms/grouping/#radio-buttons
     * @see https://html.spec.whatwg.org/multipage/forms.html#radio-button-state-typeradio
     * @see https://html.spec.whatwg.org/multipage/forms.html#element-attrdef-formelements-name
     * @see https://www.w3.org/WAI/WCAG22/Techniques/html/H71
     * @see https://www.w3.org/WAI/WCAG22/Techniques/html/H44
     */
    protected function assertRadioButtonsInsideFieldset(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $allRadios = $html->querySelectorAll('[type="radio"]');

        if (count($allRadios) === 0) {
            $this->assertTrue(true);

            return;
        }

        $violations = [];

        foreach ($allRadios as $radio) {
            // Check if the radio is inside a fieldset
            $parent = $radio->parentNode;
            $insideFieldset = false;

            while ($parent !== null) {
                if ($parent instanceof Element && mb_strtolower($parent->nodeName) === 'fieldset') {
                    $insideFieldset = true;
                    break;
                }
                $parent = $parent->parentNode;
            }

            if (! $insideFieldset) {
                $id = $radio->getAttribute('id');
                $name = $radio->getAttribute('name');
                $identifier = $id ? " id=\"{$id}\"" : ($name ? " name=\"{$name}\"" : '');
                $violations[] = "- <input type=\"radio\"{$identifier}> is not inside a <fieldset> (recommended for grouping)";
            }
        }

        if (count($violations) === 0) {
            $this->assertTrue(true);

            return;
        }

        $count = count($violations);
        $elementText = $count === 1 ? 'radio button' : 'radio buttons';

        $this->fail(
            "Found {$count} {$elementText} outside <fieldset> (strongly recommended for accessibility):\n".
            implode("\n", $violations)
        );
    }

    /**
     * Assert that elements with role="slider" have required ARIA attributes.
     *
     * Elements with role="slider" require aria-valuemin, aria-valuemax, and aria-valuenow attributes
     * to properly communicate the slider's state to assistive technologies.
     * aria-valuetext is also recommended but not required.
     *
     * @see https://github.com/imbrianj/debugCSS/blob/master/debugCSS.css#L378
     * @see https://www.w3.org/TR/wai-aria-1.3/#slider
     * @see https://www.w3.org/TR/wai-aria-1.3/#aria-valuenow
     * @see https://www.w3.org/TR/wai-aria-1.3/#aria-valuemin
     * @see https://www.w3.org/TR/wai-aria-1.3/#aria-valuemax
     * @see https://www.tpgi.com/aria-slider-part-1/
     * @see https://www.tpgi.com/aria-slider-part-2/
     * @see https://www.tpgi.com/aria-slider-part-3/
     * @see https://developer.mozilla.org/en-US/docs/Web/Accessibility/ARIA/Roles/slider_role
     */
    protected function assertSliderRoleHasRequiredAttributes(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $sliders = $html->querySelectorAll('[role="slider"]');

        if (count($sliders) === 0) {
            $this->assertTrue(true);

            return;
        }

        $violations = [];

        foreach ($sliders as $slider) {
            $missingAttributes = [];

            if (! $slider->hasAttribute('aria-valuemin')) {
                $missingAttributes[] = 'aria-valuemin';
            }

            if (! $slider->hasAttribute('aria-valuemax')) {
                $missingAttributes[] = 'aria-valuemax';
            }

            if (! $slider->hasAttribute('aria-valuenow')) {
                $missingAttributes[] = 'aria-valuenow';
            }

            if (count($missingAttributes) > 0) {
                $tagName = $slider->nodeName;
                $id = $slider->getAttribute('id');
                $identifier = $id ? " id=\"{$id}\"" : '';
                $missing = implode(', ', array_map(fn ($attr) => "[$attr]", $missingAttributes));
                $violations[] = "- <{$tagName}{$identifier} role=\"slider\"> missing required attributes: {$missing}";
            }
        }

        if (count($violations) === 0) {
            $this->assertTrue(true);

            return;
        }

        $count = count($violations);
        $elementText = $count === 1 ? 'slider element' : 'slider elements';

        $this->fail(
            "Found {$count} {$elementText} with role=\"slider\" missing required ARIA attributes:\n".
            implode("\n", $violations)
        );
    }

    /**
     * Assert that elements with [role="spinbutton"] have required ARIA attributes.
     *
     * The WAI-ARIA 1.3 specification requires that spinbutton roles have:
     * - [aria-valuemin]: The minimum allowed value
     * - [aria-valuemax]: The maximum allowed value
     * - [aria-valuenow]: The current value (required for assistive technologies)
     *
     * @see https://www.w3.org/TR/wai-aria-1.3/#spinbutton
     * @see https://www.w3.org/TR/wai-aria-1.3/#aria-valuenow
     * @see https://www.w3.org/TR/wai-aria-1.3/#aria-valuemin
     * @see https://www.w3.org/TR/wai-aria-1.3/#aria-valuemax
     */
    protected function assertSpinbuttonRoleHasRequiredAttributes(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $spinbuttons = $html->querySelectorAll('[role="spinbutton"]');

        if (count($spinbuttons) === 0) {
            $this->assertTrue(true);

            return;
        }

        $violations = [];

        foreach ($spinbuttons as $spinbutton) {
            $missingAttributes = [];

            if (! $spinbutton->hasAttribute('aria-valuemin')) {
                $missingAttributes[] = 'aria-valuemin';
            }

            if (! $spinbutton->hasAttribute('aria-valuemax')) {
                $missingAttributes[] = 'aria-valuemax';
            }

            if (! $spinbutton->hasAttribute('aria-valuenow')) {
                $missingAttributes[] = 'aria-valuenow';
            }

            if (count($missingAttributes) > 0) {
                $tagName = $spinbutton->nodeName;
                $id = $spinbutton->getAttribute('id');
                $identifier = $id ? " id=\"{$id}\"" : '';
                $missing = implode(', ', array_map(fn ($attr) => "[$attr]", $missingAttributes));
                $violations[] = "- <{$tagName}{$identifier} role=\"spinbutton\"> missing required attributes: {$missing}";
            }
        }

        if (count($violations) === 0) {
            $this->assertTrue(true);

            return;
        }

        $count = count($violations);
        $elementText = $count === 1 ? 'spinbutton element' : 'spinbutton elements';

        $this->fail(
            "Found {$count} {$elementText} with role=\"spinbutton\" missing required ARIA attributes:\n".
            implode("\n", $violations)
        );
    }

    /**
     * Assert that elements with [role="checkbox"] have the [aria-checked] attribute.
     *
     * The WAI-ARIA 1.3 specification requires that checkbox roles have the [aria-checked] attribute
     * to indicate the current state of the checkbox. This is essential for assistive technologies
     * to communicate the checkbox state to users.
     *
     * Valid values for [aria-checked] are "true", "false", or "mixed".
     *
     * @see https://www.w3.org/TR/wai-aria-1.3/#checkbox
     * @see https://www.w3.org/TR/wai-aria-1.3/#aria-checked
     */
    protected function assertCheckboxRoleHasAriaChecked(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $checkboxes = $html->querySelectorAll('[role="checkbox"]:not([aria-checked])');

        if (count($checkboxes) === 0) {
            $this->assertTrue(true);

            return;
        }

        $violations = [];

        foreach ($checkboxes as $checkbox) {
            $tagName = $checkbox->nodeName;
            $id = $checkbox->getAttribute('id');
            $identifier = $id ? " id=\"{$id}\"" : '';
            $violations[] = "- <{$tagName}{$identifier} role=\"checkbox\"> missing required attribute: [aria-checked]";
        }

        $count = count($violations);
        $elementText = $count === 1 ? 'checkbox element' : 'checkbox elements';

        $this->fail(
            "Found {$count} {$elementText} with role=\"checkbox\" missing the required [aria-checked] attribute:\n".
            implode("\n", $violations)
        );
    }

    /**
     * Assert that elements with [role="combobox"] have the [aria-expanded] attribute.
     *
     * The WAI-ARIA 1.3 specification requires that combobox roles have the [aria-expanded] attribute
     * to indicate whether the combobox popup is currently displayed. This is essential for assistive
     * technologies to communicate the state of the combobox to users.
     *
     * Valid values for [aria-expanded] are "true" or "false".
     *
     * @see https://www.w3.org/TR/wai-aria-1.3/#combobox
     * @see https://www.w3.org/TR/wai-aria-1.3/#aria-expanded
     */
    protected function assertComboboxRoleHasAriaExpanded(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $comboboxes = $html->querySelectorAll('[role="combobox"]:not([aria-expanded])');

        if (count($comboboxes) === 0) {
            $this->assertTrue(true);

            return;
        }

        $violations = [];

        foreach ($comboboxes as $combobox) {
            $tagName = $combobox->nodeName;
            $id = $combobox->getAttribute('id');
            $identifier = $id ? " id=\"{$id}\"" : '';
            $violations[] = "- <{$tagName}{$identifier} role=\"combobox\"> missing required attribute: [aria-expanded]";
        }

        $count = count($violations);
        $elementText = $count === 1 ? 'combobox element' : 'combobox elements';

        $this->fail(
            "Found {$count} {$elementText} with role=\"combobox\" missing the required [aria-expanded] attribute:\n".
            implode("\n", $violations)
        );
    }

    /**
     * Assert that elements with [role="scrollbar"] have required ARIA attributes.
     *
     * The WAI-ARIA 1.3 specification requires that scrollbar roles have:
     * - [aria-controls]: The ID of the element being scrolled
     * - [aria-valuemin]: The minimum scroll position value (typically 0)
     * - [aria-valuemax]: The maximum scroll position value
     * - [aria-valuenow]: The current scroll position
     * - [aria-orientation]: The orientation of the scrollbar (horizontal or vertical)
     *
     * @see https://www.w3.org/TR/wai-aria-1.3/#scrollbar
     * @see https://www.w3.org/TR/wai-aria-1.3/#aria-controls
     * @see https://www.w3.org/TR/wai-aria-1.3/#aria-valuemin
     * @see https://www.w3.org/TR/wai-aria-1.3/#aria-valuemax
     * @see https://www.w3.org/TR/wai-aria-1.3/#aria-valuenow
     * @see https://www.w3.org/TR/wai-aria-1.3/#aria-orientation
     */
    protected function assertScrollbarRoleHasRequiredAttributes(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $scrollbars = $html->querySelectorAll('[role="scrollbar"]');

        if (count($scrollbars) === 0) {
            $this->assertTrue(true);

            return;
        }

        $violations = [];

        foreach ($scrollbars as $scrollbar) {
            $missingAttributes = [];

            if (! $scrollbar->hasAttribute('aria-controls')) {
                $missingAttributes[] = 'aria-controls';
            }

            if (! $scrollbar->hasAttribute('aria-valuemin')) {
                $missingAttributes[] = 'aria-valuemin';
            }

            if (! $scrollbar->hasAttribute('aria-valuemax')) {
                $missingAttributes[] = 'aria-valuemax';
            }

            if (! $scrollbar->hasAttribute('aria-valuenow')) {
                $missingAttributes[] = 'aria-valuenow';
            }

            if (! $scrollbar->hasAttribute('aria-orientation')) {
                $missingAttributes[] = 'aria-orientation';
            }

            if (count($missingAttributes) > 0) {
                $tagName = $scrollbar->nodeName;
                $id = $scrollbar->getAttribute('id');
                $identifier = $id ? " id=\"{$id}\"" : '';
                $missing = implode(', ', array_map(fn ($attr) => "[$attr]", $missingAttributes));
                $violations[] = "- <{$tagName}{$identifier} role=\"scrollbar\"> missing required attributes: {$missing}";
            }
        }

        if (count($violations) === 0) {
            $this->assertTrue(true);

            return;
        }

        $count = count($violations);
        $elementText = $count === 1 ? 'scrollbar element' : 'scrollbar elements';

        $this->fail(
            "Found {$count} {$elementText} with role=\"scrollbar\" missing required ARIA attributes:\n".
            implode("\n", $violations)
        );
    }

    /**
     * Assert that interactive elements are not nested inside other interactive elements.
     *
     * Interactive elements (like links, buttons, form controls) should not be contained within other
     * interactive elements as this creates confusing interaction behavior and violates HTML specifications.
     * For example, a button inside a link or a link inside a button is not allowed.
     *
     * @see https://accessibilite.numerique.gouv.fr/methode/criteres-et-tests/#8.2
     * @see https://www.w3.org/TR/WCAG22/#parsing
     * @see https://www.w3.org/TR/WCAG22/#name-role-value
     * @see https://www.w3.org/TR/xhtml1/#prohibitions
     * @see https://html.spec.whatwg.org/multipage/dom.html#interactive-content-2
     * @see https://html.spec.whatwg.org/multipage/forms.html#the-button-element
     * @see https://html.spec.whatwg.org/multipage/text-level-semantics.html#the-a-element
     * @see https://html.spec.whatwg.org/multipage/forms.html#the-label-element
     */
    protected function assertNoNestedInteractiveElements(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $selectors = [
            'a a[href]',
            'button a[href]',
            'a audio[controls]',
            'button audio[controls]',
            'a video[controls]',
            'button video[controls]',
            'a button',
            'button button',
            'a details',
            'button details',
            'a embed',
            'button embed',
            'a iframe',
            'button iframe',
            'a img[usemap]',
            'button img[usemap]',
            'a label',
            'button label',
            'a select',
            'button select',
            'a textarea',
            'button textarea',
            'a input[type]:not([type="hidden"])',
            'button input[type]:not([type="hidden"])',
            'form form',
            'label label',
            'meter meter',
            'progress progress',
        ];

        $nestedElements = $html->querySelectorAll(implode(', ', $selectors));

        if (count($nestedElements) === 0) {
            $this->assertTrue(true);

            return;
        }

        $violations = [];

        foreach ($nestedElements as $element) {
            $tagName = $element->nodeName;
            $id = $element->getAttribute('id');
            $identifier = $id ? " id=\"{$id}\"" : '';

            // Find the parent interactive element
            $parent = $element->parentNode;
            while ($parent) {
                if ($parent instanceof Element) {
                    $parentTag = mb_strtolower($parent->nodeName);
                    if (in_array($parentTag, ['a', 'button', 'form', 'label', 'meter', 'progress'])) {
                        $parentId = $parent->getAttribute('id');
                        $parentIdentifier = $parentId ? " id=\"{$parentId}\"" : '';
                        $violations[] = "- <{$tagName}{$identifier}> nested inside <{$parent->nodeName}{$parentIdentifier}>";
                        break;
                    }
                }
                $parent = $parent->parentNode;
            }
        }

        if (count($violations) === 0) {
            $this->assertTrue(true);

            return;
        }

        $count = count($violations);
        $elementText = $count === 1 ? 'interactive element' : 'interactive elements';

        $this->fail(
            "Found {$count} {$elementText} nested inside other interactive elements:\n".
            implode("\n", $violations)
        );
    }

    /**
     * Assert that the [accesskey] attribute is not used.
     *
     * The [accesskey] attribute is meant to implement site-specific keyboard shortcuts, but this is usually a bad idea
     * since keys might already be used by the operating system, browser, browser extensions, or user settings.
     * This creates accessibility issues and conflicts with assistive technologies.
     *
     * @see https://github.com/karlgroves/diagnostic.css/blob/39ede15ff46bd59af9f8f30efb04cbb45b6c1ba5/diagnostic.css#L159
     * @see https://jkorpela.fi/forms/accesskey.html
     * @see https://developer.mozilla.org/en-US/docs/Web/HTML/Global_attributes/accesskey
     * @see https://html.spec.whatwg.org/multipage/interaction.html#the-accesskey-attribute
     * @see https://john.foliot.ca/more-reasons-why-we-dont-use-accesskeys/
     * @see https://www.alsacreations.com/article/lire/568-Accesskey-le-grand-echec-de-l-accessibilite-du-Web.html
     */
    protected function assertAccesskeyNotUsed(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $elementsWithAccesskey = $html->querySelectorAll('[accesskey]');

        if (count($elementsWithAccesskey) === 0) {
            $this->assertTrue(true);

            return;
        }

        $violations = [];

        foreach ($elementsWithAccesskey as $element) {
            $tagName = $element->nodeName;
            $accesskeyValue = $element->getAttribute('accesskey');
            $violations[] = "- <{$tagName}> has accesskey=\"{$accesskeyValue}\" (may conflict with browser/OS shortcuts)";
        }

        $count = count($violations);
        $elementText = $count === 1 ? 'element' : 'elements';

        $this->fail(
            "Found {$count} {$elementText} using [accesskey] attribute (creates conflicts with browser and OS shortcuts):\n".
            implode("\n", $violations)
        );
    }

    /**
     * Assert that the [dir] attribute only uses valid values.
     *
     * The [dir] attribute specifies the element's text directionality and only accepts three values: rtl, ltr, and auto.
     *
     * @see https://github.com/karlgroves/diagnostic.css/blob/39ede15ff46bd59af9f8f30efb04cbb45b6c1ba5/diagnostic.css#L113
     * @see https://www.w3.org/International/questions/qa-html-dir
     * @see https://www.w3.org/International/articles/inline-bidi-markup/#dirattribute
     * @see https://html.spec.whatwg.org/multipage/dom.html#the-dir-attribute
     */
    protected function assertDirAttributeIsValid(HTMLDocument|string $html): void
    {
        $html = $this->ensureDocument($html);

        $elementsWithInvalidDir = $html->querySelectorAll('[dir]:not([dir="rtl"], [dir="ltr"], [dir="auto"])');

        if (count($elementsWithInvalidDir) === 0) {
            $this->assertTrue(true);

            return;
        }

        $violations = [];

        foreach ($elementsWithInvalidDir as $element) {
            $tagName = $element->nodeName;
            $dirValue = $element->getAttribute('dir');
            $violations[] = "- <{$tagName}> has invalid dir=\"{$dirValue}\" (must be \"rtl\", \"ltr\", or \"auto\")";
        }

        $count = count($violations);
        $elementText = $count === 1 ? 'element' : 'elements';

        $this->fail(
            "Found {$count} {$elementText} with invalid [dir] attribute (must be rtl, ltr, or auto):\n".
            implode("\n", $violations)
        );
    }

    /**
     * Check if a CSS identifier is invalid (starts with digit, --, or -digit).
     *
     * @see https://www.w3.org/TR/2011/REC-css3-selectors-20110929/#w3cselgrammar
     * https://www.w3.org/Style/CSS/Test/CSS3/Selectors/current/
     * https://www.w3.org/Style/CSS/Test/CSS3/Selectors/current/html/tests/css3-modsel-175a.html
     */
    private function isInvalidCssIdentifier(string $identifier): bool
    {
        if (mb_strlen($identifier) === 0) {
            return false;
        }

        // Starts with digit (0-9)
        if (preg_match('/^[0-9]/', $identifier)) {
            return true;
        }

        // Starts with two hyphens (--)
        if (str_starts_with($identifier, '--')) {
            return true;
        }

        // Starts with hyphen followed by digit (-0 through -9)
        if (preg_match('/^-[0-9]/', $identifier)) {
            return true;
        }

        return false;
    }

    private function ensureDocument(HTMLDocument|string $html): HTMLDocument
    {
        if (is_string($html)) {
            $html = HTMLDocument::createFromString($html, LIBXML_HTML_NOIMPLIED);
        }

        return $html;
    }
}
