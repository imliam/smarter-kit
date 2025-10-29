<?php

declare(strict_types=1);

use PHPUnit\Framework\AssertionFailedError;
use Tests\Concerns\Accessibility\ChecksAccessibilityErrors;

uses(ChecksAccessibilityErrors::class);

describe('assertAttributesDoNotContainWhitespace', function () {
    test('passes when attributes do not contain whitespace', function () {
        $html = <<<'HTML'
                <p id="my-id">This is valid</p>
                <div lang="en-US">Valid language</div>
                <map name="image-map">
                    <area shape="rect" coords="0,0,100,100" href="#" alt="Area">
                </map>
            HTML;

        expect(fn () => $this->assertAttributesDoNotContainWhitespace($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when id attribute contains whitespace', function () {
        $html = <<<'HTML'
                <p id="my id">This is invalid</p>
            HTML;

        $this->assertAttributesDoNotContainWhitespace($html);
    })->throws(AssertionFailedError::class, 'contains whitespace in the id attribute');

    test('fails when lang attribute contains whitespace', function () {
        $html = <<<'HTML'
                <div lang="en US">Invalid language</div>
            HTML;

        $this->assertAttributesDoNotContainWhitespace($html);
    })->throws(AssertionFailedError::class, 'contains whitespace in the lang attribute');

    test('fails when map name attribute contains whitespace', function () {
        $html = <<<'HTML'
                <map name="image map">
                    <area shape="rect" coords="0,0,100,100" href="#" alt="Area">
                </map>
            HTML;

        $this->assertAttributesDoNotContainWhitespace($html);
    })->throws(AssertionFailedError::class, 'contains whitespace in the name attribute');
});

describe('assertTabindexNotGreaterThanZero', function () {
    test('passes when tabindex is 0', function () {
        $html = <<<'HTML'
                <button tabindex="0" type="button">Valid tabindex</button>
            HTML;

        expect(fn () => $this->assertTabindexNotGreaterThanZero($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when tabindex is negative', function () {
        $html = <<<'HTML'
                <button tabindex="-1" type="button">Valid negative tabindex</button>
            HTML;

        expect(fn () => $this->assertTabindexNotGreaterThanZero($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when no tabindex is present', function () {
        $html = <<<'HTML'
                <button type="button">No tabindex</button>
            HTML;

        expect(fn () => $this->assertTabindexNotGreaterThanZero($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when tabindex is greater than 0', function () {
        $html = <<<'HTML'
                <button tabindex="1" type="button">Positive tabindex is bad</button>
            HTML;

        $this->assertTabindexNotGreaterThanZero($html);
    })->throws(AssertionFailedError::class, 'has a positive tabindex value');

    test('fails when tabindex is a large positive number', function () {
        $html = <<<'HTML'
                <div tabindex="99">High tabindex</div>
            HTML;

        $this->assertTabindexNotGreaterThanZero($html);
    })->throws(AssertionFailedError::class, 'has a positive tabindex value');
});

describe('assertHrefNotEmpty', function () {
    test('passes when href has a valid URL', function () {
        $html = <<<'HTML'
                <a href="https://example.com">Valid link</a>
            HTML;

        expect(fn () => $this->assertHrefNotEmpty($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when href has a relative path', function () {
        $html = <<<'HTML'
                <a href="/about">About page</a>
            HTML;

        expect(fn () => $this->assertHrefNotEmpty($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when href has an anchor', function () {
        $html = <<<'HTML'
                <a href="#section">Jump to section</a>
            HTML;

        expect(fn () => $this->assertHrefNotEmpty($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when there is no href attribute', function () {
        $html = <<<'HTML'
                <a>No href</a>
            HTML;

        expect(fn () => $this->assertHrefNotEmpty($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when href is completely empty', function () {
        $html = <<<'HTML'
                <a href="">Empty href</a>
            HTML;

        $this->assertHrefNotEmpty($html);
    })->throws(AssertionFailedError::class, 'has an empty href attribute');

    test('fails when href is only whitespace', function () {
        $html = <<<'HTML'
                <a href=" ">Who am I? Where do I link?</a>
            HTML;

        $this->assertHrefNotEmpty($html);
    })->throws(AssertionFailedError::class, 'has an empty href attribute');
});

describe('assertEmptyLinksHaveLabel', function () {
    test('passes when link has text content', function () {
        $html = <<<'HTML'
                <a href="/">Home</a>
            HTML;

        expect(fn () => $this->assertEmptyLinksHaveLabel($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when empty link has title attribute', function () {
        $html = <<<'HTML'
                <a href="/" title="Go to homepage"></a>
            HTML;

        expect(fn () => $this->assertEmptyLinksHaveLabel($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when empty link has aria-label attribute', function () {
        $html = <<<'HTML'
                <a href="/" aria-label="Go to homepage"></a>
            HTML;

        expect(fn () => $this->assertEmptyLinksHaveLabel($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when empty link has aria-labelledby attribute', function () {
        $html = <<<'HTML'
                <span id="home-label">Home</span>
                <a href="/" aria-labelledby="home-label"></a>
            HTML;

        expect(fn () => $this->assertEmptyLinksHaveLabel($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when empty link has no label attributes', function () {
        $html = <<<'HTML'
                <a href="/" class="inbl w-20" id="empty-link_code"></a>
            HTML;

        $this->assertEmptyLinksHaveLabel($html);
    })->throws(AssertionFailedError::class, 'is empty and has no accessible label');

    test('fails when empty link has empty title attribute', function () {
        $html = <<<'HTML'
                <a href="/" title=""></a>
            HTML;

        $this->assertEmptyLinksHaveLabel($html);
    })->throws(AssertionFailedError::class, 'is empty and has no accessible label');

    test('fails when empty link has empty aria-label attribute', function () {
        $html = <<<'HTML'
                <a href="/" aria-label=""></a>
            HTML;

        $this->assertEmptyLinksHaveLabel($html);
    })->throws(AssertionFailedError::class, 'is empty and has no accessible label');

    test('fails when empty link has empty aria-labelledby attribute', function () {
        $html = <<<'HTML'
                <a href="/" aria-labelledby=""></a>
            HTML;

        $this->assertEmptyLinksHaveLabel($html);
    })->throws(AssertionFailedError::class, 'is empty and has no accessible label');
});

describe('assertImagesHaveAlt', function () {
    test('passes when img has alt attribute', function () {
        $html = <<<'HTML'
                <img src="/image.png" alt="Description of image" />
            HTML;

        expect(fn () => $this->assertImagesHaveAlt($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when img has empty alt for decorative images', function () {
        $html = <<<'HTML'
                <img src="/decorative.png" alt="" />
            HTML;

        expect(fn () => $this->assertImagesHaveAlt($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when area has alt attribute', function () {
        $html = <<<'HTML'
                <map name="map">
                    <area shape="rect" coords="0,0,100,100" href="#" alt="Click here" />
                </map>
            HTML;

        expect(fn () => $this->assertImagesHaveAlt($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when input type image has alt attribute', function () {
        $html = <<<'HTML'
                <input type="image" src="/submit.png" alt="Submit form" />
            HTML;

        expect(fn () => $this->assertImagesHaveAlt($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when img has no alt attribute', function () {
        $html = <<<'HTML'
                <img src="/static/ffoodd.png" width="144" height="144" />
            HTML;

        $this->assertImagesHaveAlt($html);
    })->throws(AssertionFailedError::class, 'is missing the alt attribute');

    test('fails when img has alt with only whitespace', function () {
        $html = <<<'HTML'
                <img src="/image.png" alt=" " />
            HTML;

        $this->assertImagesHaveAlt($html);
    })->throws(AssertionFailedError::class, 'has an alt attribute with only whitespace');

    test('fails when area has no alt attribute', function () {
        $html = <<<'HTML'
                <map name="map">
                    <area shape="rect" coords="0,0,100,100" href="#" />
                </map>
            HTML;

        $this->assertImagesHaveAlt($html);
    })->throws(AssertionFailedError::class, 'is missing the alt attribute');

    test('fails when area has alt with only whitespace', function () {
        $html = <<<'HTML'
                <map name="map">
                    <area shape="rect" coords="0,0,100,100" href="#" alt=" " />
                </map>
            HTML;

        $this->assertImagesHaveAlt($html);
    })->throws(AssertionFailedError::class, 'has an alt attribute with only whitespace');

    test('fails when input type image has no alt attribute', function () {
        $html = <<<'HTML'
                <input type="image" src="/submit.png" />
            HTML;

        $this->assertImagesHaveAlt($html);
    })->throws(AssertionFailedError::class, 'is missing the alt attribute');

    test('fails when input type image has alt with only whitespace', function () {
        $html = <<<'HTML'
                <input type="image" src="/submit.png" alt=" " />
            HTML;

        $this->assertImagesHaveAlt($html);
    })->throws(AssertionFailedError::class, 'has an alt attribute with only whitespace');
});

describe('assertRoleImgHasLabel', function () {
    test('passes when role=img has aria-label', function () {
        $html = <<<'HTML'
                <div role="img" aria-label="Description of image">
                    <span>Icon</span>
                </div>
            HTML;

        expect(fn () => $this->assertRoleImgHasLabel($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when role=img has aria-labelledby', function () {
        $html = <<<'HTML'
                <span id="img-label">Description</span>
                <div role="img" aria-labelledby="img-label">
                    <span>Icon</span>
                </div>
            HTML;

        expect(fn () => $this->assertRoleImgHasLabel($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when role=img is hidden with aria-hidden=true', function () {
        $html = <<<'HTML'
                <div role="img" aria-hidden="true">
                    <span>Decorative</span>
                </div>
            HTML;

        expect(fn () => $this->assertRoleImgHasLabel($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when svg role=img has aria-label', function () {
        $html = <<<'HTML'
                <svg role="img" aria-label="Chart showing data" width="100" height="100">
                    <circle cx="50" cy="50" r="40" />
                </svg>
            HTML;

        expect(fn () => $this->assertRoleImgHasLabel($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when svg role=img has aria-labelledby', function () {
        $html = <<<'HTML'
                <span id="chart-label">Chart</span>
                <svg role="img" aria-labelledby="chart-label" width="100" height="100">
                    <circle cx="50" cy="50" r="40" />
                </svg>
            HTML;

        expect(fn () => $this->assertRoleImgHasLabel($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when role=img has no label', function () {
        $html = <<<'HTML'
                <div role="img">
                    <span>Icon</span>
                </div>
            HTML;

        $this->assertRoleImgHasLabel($html);
    })->throws(AssertionFailedError::class, 'is missing aria-label or aria-labelledby');

    test('fails when svg role=img has no label', function () {
        $html = <<<'HTML'
                <svg width="12cm" height="4cm" viewBox="0 0 1200 400"
                    xmlns="http://www.w3.org/2000/svg" role="img">
                    <rect x="400" y="100" width="400" height="200"
                        fill="forestgreen" stroke="darkgreen" stroke-width="10"/>
                </svg>
            HTML;

        $this->assertRoleImgHasLabel($html);
    })->throws(AssertionFailedError::class, 'is missing aria-label or aria-labelledby');
});

describe('assertImagesHaveValidSource', function () {
    test('passes when img has valid src', function () {
        $html = <<<'HTML'
                <img src="/image.png" alt="Valid image" />
            HTML;

        expect(fn () => $this->assertImagesHaveValidSource($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when img has valid srcset', function () {
        $html = <<<'HTML'
                <img srcset="/image-1x.png 1x, /image-2x.png 2x" alt="Valid image" />
            HTML;

        expect(fn () => $this->assertImagesHaveValidSource($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when img has both src and srcset', function () {
        $html = <<<'HTML'
                <img src="/image.png" srcset="/image-1x.png 1x, /image-2x.png 2x" alt="Valid image" />
            HTML;

        expect(fn () => $this->assertImagesHaveValidSource($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when input type image has valid src', function () {
        $html = <<<'HTML'
                <input type="image" src="/submit.png" alt="Submit" />
            HTML;

        expect(fn () => $this->assertImagesHaveValidSource($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when img has no src or srcset', function () {
        $html = <<<'HTML'
                <img alt="Missing src" width="144" height="144"/>
            HTML;

        $this->assertImagesHaveValidSource($html);
    })->throws(AssertionFailedError::class, 'is missing both src and srcset attributes');

    test('fails when img has empty src', function () {
        $html = <<<'HTML'
                <img src="" alt="Empty src" />
            HTML;

        $this->assertImagesHaveValidSource($html);
    })->throws(AssertionFailedError::class, 'has invalid src=""');

    test('fails when img has whitespace src', function () {
        $html = <<<'HTML'
                <img src=" " alt="Whitespace src" />
            HTML;

        $this->assertImagesHaveValidSource($html);
    })->throws(AssertionFailedError::class, 'has invalid src=" "');

    test('fails when img has hash src', function () {
        $html = <<<'HTML'
                <img src="#" alt="Hash src" />
            HTML;

        $this->assertImagesHaveValidSource($html);
    })->throws(AssertionFailedError::class, 'has invalid src="#"');

    test('fails when img has slash src', function () {
        $html = <<<'HTML'
                <img src="/" alt="Slash src" />
            HTML;

        $this->assertImagesHaveValidSource($html);
    })->throws(AssertionFailedError::class, 'has invalid src="/"');

    test('fails when img has empty srcset', function () {
        $html = <<<'HTML'
                <img srcset="" alt="Empty srcset" />
            HTML;

        $this->assertImagesHaveValidSource($html);
    })->throws(AssertionFailedError::class, 'has invalid srcset=""');

    test('fails when img has whitespace srcset', function () {
        $html = <<<'HTML'
                <img srcset=" " alt="Whitespace srcset" />
            HTML;

        $this->assertImagesHaveValidSource($html);
    })->throws(AssertionFailedError::class, 'has invalid srcset=" "');

    test('fails when input type image has no src', function () {
        $html = <<<'HTML'
                <input type="image" alt="Submit" />
            HTML;

        $this->assertImagesHaveValidSource($html);
    })->throws(AssertionFailedError::class, 'is missing both src and srcset attributes');

    test('fails when input type image has empty src', function () {
        $html = <<<'HTML'
                <input type="image" src="" alt="Submit" />
            HTML;

        $this->assertImagesHaveValidSource($html);
    })->throws(AssertionFailedError::class, 'has invalid src=""');
});

describe('assertLabelForNotEmpty', function () {
    test('passes when label has valid for attribute', function () {
        $html = <<<'HTML'
                <label for="username">Username</label>
                <input type="text" id="username" />
            HTML;

        expect(fn () => $this->assertLabelForNotEmpty($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when label wraps input without for attribute', function () {
        $html = <<<'HTML'
                <label>
                    Username
                    <input type="text" />
                </label>
            HTML;

        expect(fn () => $this->assertLabelForNotEmpty($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when label has no for attribute', function () {
        $html = <<<'HTML'
                <label>Just a label</label>
            HTML;

        expect(fn () => $this->assertLabelForNotEmpty($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when label has empty for attribute', function () {
        $html = <<<'HTML'
                <label for="">Empty for</label>
            HTML;

        $this->assertLabelForNotEmpty($html);
    })->throws(AssertionFailedError::class, 'has an empty or whitespace-only for attribute');

    test('fails when label has whitespace-only for attribute', function () {
        $html = <<<'HTML'
                <label for=" ">Guess what?</label>
            HTML;

        $this->assertLabelForNotEmpty($html);
    })->throws(AssertionFailedError::class, 'has an empty or whitespace-only for attribute');
});

describe('assertFormFieldsHaveLabel', function () {
    test('passes when input has id for label reference', function () {
        $html = <<<'HTML'
                <label for="username">Username</label>
                <input type="text" id="username" />
            HTML;

        expect(fn () => $this->assertFormFieldsHaveLabel($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when input has aria-label', function () {
        $html = <<<'HTML'
                <input type="text" aria-label="Username" />
            HTML;

        expect(fn () => $this->assertFormFieldsHaveLabel($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when input has title', function () {
        $html = <<<'HTML'
                <input type="text" title="Enter your username" />
            HTML;

        expect(fn () => $this->assertFormFieldsHaveLabel($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when input has aria-labelledby', function () {
        $html = <<<'HTML'
                <span id="username-label">Username</span>
                <input type="text" aria-labelledby="username-label" />
            HTML;

        expect(fn () => $this->assertFormFieldsHaveLabel($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when textarea has id', function () {
        $html = <<<'HTML'
                <label for="bio">Bio</label>
                <textarea id="bio"></textarea>
            HTML;

        expect(fn () => $this->assertFormFieldsHaveLabel($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when select has id', function () {
        $html = <<<'HTML'
                <label for="country">Country</label>
                <select id="country">
                    <option>USA</option>
                </select>
            HTML;

        expect(fn () => $this->assertFormFieldsHaveLabel($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when input type is button', function () {
        $html = <<<'HTML'
                <input type="button" value="Click me" />
            HTML;

        expect(fn () => $this->assertFormFieldsHaveLabel($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when input type is submit', function () {
        $html = <<<'HTML'
                <input type="submit" value="Submit" />
            HTML;

        expect(fn () => $this->assertFormFieldsHaveLabel($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when input type is hidden', function () {
        $html = <<<'HTML'
                <input type="hidden" name="csrf" value="token" />
            HTML;

        expect(fn () => $this->assertFormFieldsHaveLabel($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when input text has no label', function () {
        $html = <<<'HTML'
                <input type="text" />
            HTML;

        $this->assertFormFieldsHaveLabel($html);
    })->throws(AssertionFailedError::class, 'is missing a label');

    test('fails when input email has no label', function () {
        $html = <<<'HTML'
                <input type="email" name="email" />
            HTML;

        $this->assertFormFieldsHaveLabel($html);
    })->throws(AssertionFailedError::class, 'is missing a label');

    test('fails when textarea has no label', function () {
        $html = <<<'HTML'
                <textarea name="bio"></textarea>
            HTML;

        $this->assertFormFieldsHaveLabel($html);
    })->throws(AssertionFailedError::class, 'is missing a label');

    test('fails when select has no label', function () {
        $html = <<<'HTML'
                <select name="country">
                    <option>USA</option>
                </select>
            HTML;

        $this->assertFormFieldsHaveLabel($html);
    })->throws(AssertionFailedError::class, 'is missing a label');
});

describe('assertButtonInputsHaveValue', function () {
    test('passes when submit input has value', function () {
        $html = <<<'HTML'
                <input type="submit" value="Submit Form" />
            HTML;

        expect(fn () => $this->assertButtonInputsHaveValue($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when submit input has title', function () {
        $html = <<<'HTML'
                <input type="submit" title="Submit the form" />
            HTML;

        expect(fn () => $this->assertButtonInputsHaveValue($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when submit input has aria-label', function () {
        $html = <<<'HTML'
                <input type="submit" aria-label="Submit the form" />
            HTML;

        expect(fn () => $this->assertButtonInputsHaveValue($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when submit input has aria-labelledby', function () {
        $html = <<<'HTML'
                <span id="submit-label">Submit</span>
                <input type="submit" aria-labelledby="submit-label" />
            HTML;

        expect(fn () => $this->assertButtonInputsHaveValue($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when reset input has value', function () {
        $html = <<<'HTML'
                <input type="reset" value="Reset Form" />
            HTML;

        expect(fn () => $this->assertButtonInputsHaveValue($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when button input has value', function () {
        $html = <<<'HTML'
                <input type="button" value="Click Me" />
            HTML;

        expect(fn () => $this->assertButtonInputsHaveValue($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when regular text input has no value', function () {
        $html = <<<'HTML'
                <input type="text" id="username" />
            HTML;

        expect(fn () => $this->assertButtonInputsHaveValue($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when submit input has no value or label', function () {
        $html = <<<'HTML'
                <input type="submit" />
            HTML;

        $this->assertButtonInputsHaveValue($html);
    })->throws(AssertionFailedError::class, 'is missing a label');

    test('fails when reset input has no value or label', function () {
        $html = <<<'HTML'
                <input type="reset" name="reset-btn" />
            HTML;

        $this->assertButtonInputsHaveValue($html);
    })->throws(AssertionFailedError::class, 'is missing a label');

    test('fails when button input has no value or label', function () {
        $html = <<<'HTML'
                <input type="button" class="btn" />
            HTML;

        $this->assertButtonInputsHaveValue($html);
    })->throws(AssertionFailedError::class, 'is missing a label');
});

describe('assertButtonElementsNotEmpty', function () {
    test('passes when button has text content', function () {
        $html = <<<'HTML'
                <button type="button">Click Me</button>
            HTML;

        expect(fn () => $this->assertButtonElementsNotEmpty($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when button has child elements', function () {
        $html = <<<'HTML'
                <button type="submit">
                    <span>Submit</span>
                </button>
            HTML;

        expect(fn () => $this->assertButtonElementsNotEmpty($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when empty button has aria-label', function () {
        $html = <<<'HTML'
                <button type="button" aria-label="Close dialog"></button>
            HTML;

        expect(fn () => $this->assertButtonElementsNotEmpty($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when empty button has aria-labelledby', function () {
        $html = <<<'HTML'
                <span id="close-label">Close</span>
                <button type="button" aria-labelledby="close-label"></button>
            HTML;

        expect(fn () => $this->assertButtonElementsNotEmpty($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when empty button has title', function () {
        $html = <<<'HTML'
                <button type="button" title="Close dialog"></button>
            HTML;

        expect(fn () => $this->assertButtonElementsNotEmpty($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when button is completely empty', function () {
        $html = <<<'HTML'
                <button type="button"></button>
            HTML;

        $this->assertButtonElementsNotEmpty($html);
    })->throws(AssertionFailedError::class, 'is empty and has no accessible label');

    test('fails when submit button is empty without label', function () {
        $html = <<<'HTML'
                <button type="submit" name="submit-btn"></button>
            HTML;

        $this->assertButtonElementsNotEmpty($html);
    })->throws(AssertionFailedError::class, 'is empty and has no accessible label');

    test('fails when button with class is empty without label', function () {
        $html = <<<'HTML'
                <button class="btn btn-primary"></button>
            HTML;

        $this->assertButtonElementsNotEmpty($html);
    })->throws(AssertionFailedError::class, 'is empty and has no accessible label');
});

describe('assertButtonAttributesNotEmpty', function () {
    test('passes when button has no label attributes', function () {
        $html = <<<'HTML'
                <button type="button">Click Me</button>
            HTML;

        expect(fn () => $this->assertButtonAttributesNotEmpty($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when button has non-empty title', function () {
        $html = <<<'HTML'
                <button type="button" title="Click this button">Click</button>
            HTML;

        expect(fn () => $this->assertButtonAttributesNotEmpty($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when button has non-empty aria-label', function () {
        $html = <<<'HTML'
                <button type="button" aria-label="Close dialog">×</button>
            HTML;

        expect(fn () => $this->assertButtonAttributesNotEmpty($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when button has non-empty aria-labelledby', function () {
        $html = <<<'HTML'
                <span id="submit-label">Submit Form</span>
                <button type="submit" aria-labelledby="submit-label">Submit</button>
            HTML;

        expect(fn () => $this->assertButtonAttributesNotEmpty($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when button has empty title', function () {
        $html = <<<'HTML'
                <button title="" type="button">Test</button>
            HTML;

        $this->assertButtonAttributesNotEmpty($html);
    })->throws(AssertionFailedError::class, 'has an empty title attribute');

    test('fails when button has empty aria-label', function () {
        $html = <<<'HTML'
                <button aria-label="" type="submit">Submit</button>
            HTML;

        $this->assertButtonAttributesNotEmpty($html);
    })->throws(AssertionFailedError::class, 'has an empty aria-label attribute');

    test('fails when button has empty aria-labelledby', function () {
        $html = <<<'HTML'
                <button aria-labelledby="" type="button">Click</button>
            HTML;

        $this->assertButtonAttributesNotEmpty($html);
    })->throws(AssertionFailedError::class, 'has an empty aria-labelledby attribute');
});

describe('assertButtonsHaveType', function () {
    test('passes when button has type attribute', function () {
        $html = <<<'HTML'
                <button type="button">Click Me</button>
            HTML;

        expect(fn () => $this->assertButtonsHaveType($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when button has type submit', function () {
        $html = <<<'HTML'
                <button type="submit">Submit</button>
            HTML;

        expect(fn () => $this->assertButtonsHaveType($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when button has type reset', function () {
        $html = <<<'HTML'
                <button type="reset">Reset</button>
            HTML;

        expect(fn () => $this->assertButtonsHaveType($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when button has form attribute', function () {
        $html = <<<'HTML'
                <form id="my-form"></form>
                <button form="my-form">Submit</button>
            HTML;

        expect(fn () => $this->assertButtonsHaveType($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when button has formaction attribute', function () {
        $html = <<<'HTML'
                <button formaction="/submit">Submit to different action</button>
            HTML;

        expect(fn () => $this->assertButtonsHaveType($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when button has formtarget attribute', function () {
        $html = <<<'HTML'
                <button formtarget="_blank">Submit in new window</button>
            HTML;

        expect(fn () => $this->assertButtonsHaveType($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when button has no type or form attributes', function () {
        $html = <<<'HTML'
                <button>I just don't know what todo with myself</button>
            HTML;

        $this->assertButtonsHaveType($html);
    })->throws(AssertionFailedError::class, 'is missing a type attribute');

    test('fails when button with name has no type', function () {
        $html = <<<'HTML'
                <button name="action">Do Something</button>
            HTML;

        $this->assertButtonsHaveType($html);
    })->throws(AssertionFailedError::class, 'is missing a type attribute');

    test('fails when button with class has no type', function () {
        $html = <<<'HTML'
                <button class="btn btn-primary">Click</button>
            HTML;

        $this->assertButtonsHaveType($html);
    })->throws(AssertionFailedError::class, 'is missing a type attribute');
});

describe('assertNonSubmitButtonsDoNotHaveFormAttributes', function () {
    test('passes when submit button has formmethod', function () {
        $html = <<<'HTML'
                <button type="submit" formmethod="POST">Submit</button>
            HTML;

        expect(fn () => $this->assertNonSubmitButtonsDoNotHaveFormAttributes($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when submit button has formaction', function () {
        $html = <<<'HTML'
                <button type="submit" formaction="/submit">Submit</button>
            HTML;

        expect(fn () => $this->assertNonSubmitButtonsDoNotHaveFormAttributes($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when submit button has formtarget', function () {
        $html = <<<'HTML'
                <button type="submit" formtarget="_blank">Submit</button>
            HTML;

        expect(fn () => $this->assertNonSubmitButtonsDoNotHaveFormAttributes($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when submit button has formenctype', function () {
        $html = <<<'HTML'
                <button type="submit" formenctype="multipart/form-data">Submit</button>
            HTML;

        expect(fn () => $this->assertNonSubmitButtonsDoNotHaveFormAttributes($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when submit button has formnovalidate', function () {
        $html = <<<'HTML'
                <button type="submit" formnovalidate>Submit</button>
            HTML;

        expect(fn () => $this->assertNonSubmitButtonsDoNotHaveFormAttributes($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when button type button has no form attributes', function () {
        $html = <<<'HTML'
                <button type="button">Click Me</button>
            HTML;

        expect(fn () => $this->assertNonSubmitButtonsDoNotHaveFormAttributes($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when button type reset has no form attributes', function () {
        $html = <<<'HTML'
                <button type="reset">Reset</button>
            HTML;

        expect(fn () => $this->assertNonSubmitButtonsDoNotHaveFormAttributes($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when button type button has formmethod', function () {
        $html = <<<'HTML'
                <button type="button" formmethod="GET">I have a method!</button>
            HTML;

        $this->assertNonSubmitButtonsDoNotHaveFormAttributes($html);
    })->throws(AssertionFailedError::class, 'has invalid form attributes');

    test('fails when button type button has formaction', function () {
        $html = <<<'HTML'
                <button type="button" formaction="/action">Action!</button>
            HTML;

        $this->assertNonSubmitButtonsDoNotHaveFormAttributes($html);
    })->throws(AssertionFailedError::class, 'has invalid form attributes');

    test('fails when button type button has formtarget', function () {
        $html = <<<'HTML'
                <button type="button" formtarget="_blank">Target!</button>
            HTML;

        $this->assertNonSubmitButtonsDoNotHaveFormAttributes($html);
    })->throws(AssertionFailedError::class, 'has invalid form attributes');

    test('fails when button type button has formenctype', function () {
        $html = <<<'HTML'
                <button type="button" formenctype="text/plain">Encode!</button>
            HTML;

        $this->assertNonSubmitButtonsDoNotHaveFormAttributes($html);
    })->throws(AssertionFailedError::class, 'has invalid form attributes');

    test('fails when button type button has formnovalidate', function () {
        $html = <<<'HTML'
                <button type="button" formnovalidate>No validate!</button>
            HTML;

        $this->assertNonSubmitButtonsDoNotHaveFormAttributes($html);
    })->throws(AssertionFailedError::class, 'has invalid form attributes');

    test('fails when button type reset has formmethod', function () {
        $html = <<<'HTML'
                <button type="reset" formmethod="POST">Reset with method</button>
            HTML;

        $this->assertNonSubmitButtonsDoNotHaveFormAttributes($html);
    })->throws(AssertionFailedError::class, 'has invalid form attributes');

    test('fails when button type reset has formaction', function () {
        $html = <<<'HTML'
                <button type="reset" formaction="/reset">Reset with action</button>
            HTML;

        $this->assertNonSubmitButtonsDoNotHaveFormAttributes($html);
    })->throws(AssertionFailedError::class, 'has invalid form attributes');

    test('fails when button type reset has formtarget', function () {
        $html = <<<'HTML'
                <button type="reset" formtarget="_self">Reset with target</button>
            HTML;

        $this->assertNonSubmitButtonsDoNotHaveFormAttributes($html);
    })->throws(AssertionFailedError::class, 'has invalid form attributes');

    test('fails when button type reset has formenctype', function () {
        $html = <<<'HTML'
                <button type="reset" formenctype="application/x-www-form-urlencoded">Reset with enctype</button>
            HTML;

        $this->assertNonSubmitButtonsDoNotHaveFormAttributes($html);
    })->throws(AssertionFailedError::class, 'has invalid form attributes');

    test('fails when button type reset has formnovalidate', function () {
        $html = <<<'HTML'
                <button type="reset" formnovalidate>Reset without validation</button>
            HTML;

        $this->assertNonSubmitButtonsDoNotHaveFormAttributes($html);
    })->throws(AssertionFailedError::class, 'has invalid form attributes');

    test('fails when button has multiple invalid attributes', function () {
        $html = <<<'HTML'
                <button type="button" formmethod="POST" formaction="/submit" formtarget="_blank">Multi-invalid</button>
            HTML;

        $this->assertNonSubmitButtonsDoNotHaveFormAttributes($html);
    })->throws(AssertionFailedError::class, 'has invalid form attributes');
});

describe('assertDisabledButtonsAreActuallyDisabled', function () {
    test('passes when button with disabled class has disabled attribute', function () {
        $html = <<<'HTML'
                <button class="btn-disabled" type="button" disabled>Disabled Button</button>
            HTML;

        expect(fn () => $this->assertDisabledButtonsAreActuallyDisabled($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when button with disabled class has readonly attribute', function () {
        $html = <<<'HTML'
                <button class="is-disabled" type="button" readonly>Readonly Button</button>
            HTML;

        expect(fn () => $this->assertDisabledButtonsAreActuallyDisabled($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when button without disabled class is not disabled', function () {
        $html = <<<'HTML'
                <button class="btn-primary" type="button">Active Button</button>
            HTML;

        expect(fn () => $this->assertDisabledButtonsAreActuallyDisabled($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when button has both disabled class and disabled attribute', function () {
        $html = <<<'HTML'
                <button class="button-disabled-state" type="button" disabled>Properly Disabled</button>
            HTML;

        expect(fn () => $this->assertDisabledButtonsAreActuallyDisabled($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when button has disabled in class but no disabled attribute', function () {
        $html = <<<'HTML'
                <button class="is-disabled" type="button">To be or not to be (disabled)?</button>
            HTML;

        $this->assertDisabledButtonsAreActuallyDisabled($html);
    })->throws(AssertionFailedError::class, 'styled as disabled but is not actually disabled');

    test('fails when button has btn-disabled class without attribute', function () {
        $html = <<<'HTML'
                <button class="btn-disabled" type="button">Fake Disabled</button>
            HTML;

        $this->assertDisabledButtonsAreActuallyDisabled($html);
    })->throws(AssertionFailedError::class, 'styled as disabled but is not actually disabled');

    test('fails when button has disabled-button class without attribute', function () {
        $html = <<<'HTML'
                <button class="disabled-button" type="button">Another Fake</button>
            HTML;

        $this->assertDisabledButtonsAreActuallyDisabled($html);
    })->throws(AssertionFailedError::class, 'styled as disabled but is not actually disabled');

    test('fails when button has multiple classes including disabled', function () {
        $html = <<<'HTML'
                <button class="btn btn-primary is-disabled" type="button">Multi-class Fake</button>
            HTML;

        $this->assertDisabledButtonsAreActuallyDisabled($html);
    })->throws(AssertionFailedError::class, 'styled as disabled but is not actually disabled');
});

describe('assertInputsHaveType', function () {
    test('passes when input has type text', function () {
        $html = <<<'HTML'
                <input type="text" id="username" />
            HTML;

        expect(fn () => $this->assertInputsHaveType($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when input has type email', function () {
        $html = <<<'HTML'
                <input type="email" id="email" />
            HTML;

        expect(fn () => $this->assertInputsHaveType($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when input has type password', function () {
        $html = <<<'HTML'
                <input type="password" id="password" />
            HTML;

        expect(fn () => $this->assertInputsHaveType($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when input has type number', function () {
        $html = <<<'HTML'
                <input type="number" id="age" />
            HTML;

        expect(fn () => $this->assertInputsHaveType($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when input has type checkbox', function () {
        $html = <<<'HTML'
                <input type="checkbox" id="agree" />
            HTML;

        expect(fn () => $this->assertInputsHaveType($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when input has type radio', function () {
        $html = <<<'HTML'
                <input type="radio" name="option" />
            HTML;

        expect(fn () => $this->assertInputsHaveType($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when input has no type attribute', function () {
        $html = <<<'HTML'
                <label for="No-type">No type</label>
                <input value="Whatever you want" id="No-type"/>
            HTML;

        $this->assertInputsHaveType($html);
    })->throws(AssertionFailedError::class, 'missing a valid type attribute');

    test('fails when input has empty type attribute', function () {
        $html = <<<'HTML'
                <input type="" id="empty-type" />
            HTML;

        $this->assertInputsHaveType($html);
    })->throws(AssertionFailedError::class, 'missing a valid type attribute');

    test('fails when input has whitespace-only type attribute', function () {
        $html = <<<'HTML'
                <input type=" " id="space-type" />
            HTML;

        $this->assertInputsHaveType($html);
    })->throws(AssertionFailedError::class, 'missing a valid type attribute');

    test('fails when input with name has no type', function () {
        $html = <<<'HTML'
                <input name="username" />
            HTML;

        $this->assertInputsHaveType($html);
    })->throws(AssertionFailedError::class, 'missing a valid type attribute');

    test('fails when multiple inputs have no type', function () {
        $html = <<<'HTML'
                <input id="first" />
                <input id="second" />
            HTML;

        $this->assertInputsHaveType($html);
    })->throws(AssertionFailedError::class, 'without a valid type attribute');
});

describe('assertOptgroupsHaveLabel', function () {
    test('passes when optgroup has label', function () {
        $html = <<<'HTML'
                <select>
                    <optgroup label="Group 1">
                        <option value="1">Option 1</option>
                    </optgroup>
                </select>
            HTML;

        expect(fn () => $this->assertOptgroupsHaveLabel($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when multiple optgroups have labels', function () {
        $html = <<<'HTML'
                <select>
                    <optgroup label="First Group">
                        <option value="1">Option 1</option>
                    </optgroup>
                    <optgroup label="Second Group">
                        <option value="2">Option 2</option>
                    </optgroup>
                </select>
            HTML;

        expect(fn () => $this->assertOptgroupsHaveLabel($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when select has options without optgroup', function () {
        $html = <<<'HTML'
                <select>
                    <option value="1">Option 1</option>
                    <option value="2">Option 2</option>
                </select>
            HTML;

        expect(fn () => $this->assertOptgroupsHaveLabel($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when select mixes optgroups with labels and plain options', function () {
        $html = <<<'HTML'
                <select>
                    <option value="0">Ungrouped Option</option>
                    <optgroup label="Grouped Options">
                        <option value="1">Option 1</option>
                        <option value="2">Option 2</option>
                    </optgroup>
                </select>
            HTML;

        expect(fn () => $this->assertOptgroupsHaveLabel($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when optgroup has no label', function () {
        $html = <<<'HTML'
                <form action="/">
                    <label for="optgroup-test">Oh, hey</label>
                    <select id="optgroup-test">
                        <optgroup label="I'm a group">
                            <option value="1">I'm an option</option>
                            <option value="2">I'm another option</option>
                        </optgroup>
                        <optgroup>
                            <option value="3">I'm an option, but from another group</option>
                            <option value="4">I'm another option, still from another group</option>
                        </optgroup>
                    </select>
                </form>
            HTML;

        $this->assertOptgroupsHaveLabel($html);
    })->throws(AssertionFailedError::class, 'missing a label attribute');

    test('fails when single optgroup has no label', function () {
        $html = <<<'HTML'
                <select>
                    <optgroup>
                        <option value="1">Option 1</option>
                    </optgroup>
                </select>
            HTML;

        $this->assertOptgroupsHaveLabel($html);
    })->throws(AssertionFailedError::class, 'missing a label attribute');

    test('fails when multiple optgroups have no label', function () {
        $html = <<<'HTML'
                <select>
                    <optgroup>
                        <option value="1">Option 1</option>
                    </optgroup>
                    <optgroup>
                        <option value="2">Option 2</option>
                    </optgroup>
                </select>
            HTML;

        $this->assertOptgroupsHaveLabel($html);
    })->throws(AssertionFailedError::class, 'without a label attribute');
});

describe('assertIframesHaveTitle', function () {
    test('passes when iframe has title', function () {
        $html = <<<'HTML'
                <iframe src="https://example.com" title="Example Website"></iframe>
            HTML;

        expect(fn () => $this->assertIframesHaveTitle($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when iframe with srcdoc has title', function () {
        $html = <<<'HTML'
                <iframe srcdoc="<!DOCTYPE html><title>Content</title>" title="Embedded Content"></iframe>
            HTML;

        expect(fn () => $this->assertIframesHaveTitle($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when multiple iframes have titles', function () {
        $html = <<<'HTML'
                <iframe src="https://example.com" title="First Frame"></iframe>
                <iframe src="https://example.org" title="Second Frame"></iframe>
            HTML;

        expect(fn () => $this->assertIframesHaveTitle($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when no iframes are present', function () {
        $html = <<<'HTML'
                <div>No iframes here</div>
            HTML;

        expect(fn () => $this->assertIframesHaveTitle($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when iframe has no title', function () {
        $html = <<<'HTML'
                <iframe srcdoc="<!DOCTYPE html><title>Missing [title]</title>"></iframe>
            HTML;

        $this->assertIframesHaveTitle($html);
    })->throws(AssertionFailedError::class, 'missing a valid title attribute');

    test('fails when iframe with src has no title', function () {
        $html = <<<'HTML'
                <iframe src="https://example.com"></iframe>
            HTML;

        $this->assertIframesHaveTitle($html);
    })->throws(AssertionFailedError::class, 'missing a valid title attribute');

    test('fails when iframe has empty title', function () {
        $html = <<<'HTML'
                <iframe src="https://example.com" title=""></iframe>
            HTML;

        $this->assertIframesHaveTitle($html);
    })->throws(AssertionFailedError::class, 'missing a valid title attribute');

    test('fails when iframe has whitespace-only title', function () {
        $html = <<<'HTML'
                <iframe src="https://example.com" title=" "></iframe>
            HTML;

        $this->assertIframesHaveTitle($html);
    })->throws(AssertionFailedError::class, 'missing a valid title attribute');

    test('fails when multiple iframes have no title', function () {
        $html = <<<'HTML'
                <iframe src="https://example.com"></iframe>
                <iframe src="https://example.org"></iframe>
            HTML;

        $this->assertIframesHaveTitle($html);
    })->throws(AssertionFailedError::class, 'without a valid title attribute');
});

describe('assertFormsHaveAction', function () {
    test('passes when form has action', function () {
        $html = <<<'HTML'
                <form action="/submit">
                    <input type="text" />
                </form>
            HTML;

        expect(fn () => $this->assertFormsHaveAction($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when form has action with query string', function () {
        $html = <<<'HTML'
                <form action="/submit?redirect=true">
                    <input type="text" />
                </form>
            HTML;

        expect(fn () => $this->assertFormsHaveAction($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when form has action with hash', function () {
        $html = <<<'HTML'
                <form action="#anchor">
                    <input type="text" />
                </form>
            HTML;

        expect(fn () => $this->assertFormsHaveAction($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when multiple forms have actions', function () {
        $html = <<<'HTML'
                <form action="/submit1">
                    <input type="text" />
                </form>
                <form action="/submit2">
                    <input type="text" />
                </form>
            HTML;

        expect(fn () => $this->assertFormsHaveAction($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when no forms are present', function () {
        $html = <<<'HTML'
                <div>No forms here</div>
            HTML;

        expect(fn () => $this->assertFormsHaveAction($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when form has no action', function () {
        $html = <<<'HTML'
                <form>
                    <label for="input">Guess what do we do with your datas?</label>
                    <input id="input" type="text" />

                    <input type="submit" value="No idea, huh?"/>
                </form>
            HTML;

        $this->assertFormsHaveAction($html);
    })->throws(AssertionFailedError::class, 'missing a valid action attribute');

    test('fails when form has empty action', function () {
        $html = <<<'HTML'
                <form action="">
                    <input type="text" />
                </form>
            HTML;

        $this->assertFormsHaveAction($html);
    })->throws(AssertionFailedError::class, 'missing a valid action attribute');

    test('fails when form has whitespace-only action', function () {
        $html = <<<'HTML'
                <form action=" ">
                    <input type="text" />
                </form>
            HTML;

        $this->assertFormsHaveAction($html);
    })->throws(AssertionFailedError::class, 'missing a valid action attribute');

    test('fails when form with id has no action', function () {
        $html = <<<'HTML'
                <form id="my-form">
                    <input type="text" />
                </form>
            HTML;

        $this->assertFormsHaveAction($html);
    })->throws(AssertionFailedError::class, 'missing a valid action attribute');

    test('fails when form with name has no action', function () {
        $html = <<<'HTML'
                <form name="signup">
                    <input type="text" />
                </form>
            HTML;

        $this->assertFormsHaveAction($html);
    })->throws(AssertionFailedError::class, 'missing a valid action attribute');

    test('fails when multiple forms have no action', function () {
        $html = <<<'HTML'
                <form id="form1">
                    <input type="text" />
                </form>
                <form id="form2">
                    <input type="text" />
                </form>
            HTML;

        $this->assertFormsHaveAction($html);
    })->throws(AssertionFailedError::class, 'without a valid action attribute');
});

describe('assertHtmlHasValidLanguage', function () {
    test('passes when html has lang attribute', function () {
        $html = <<<'HTML'
                <!DOCTYPE html>
                <html lang="en">
                    <head><title>Test</title></head>
                    <body><p>Hello</p></body>
                </html>
            HTML;

        expect(fn () => $this->assertHtmlHasValidLanguage($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when html has lang attribute with region', function () {
        $html = <<<'HTML'
                <!DOCTYPE html>
                <html lang="en-US">
                    <head><title>Test</title></head>
                    <body><p>Hello</p></body>
                </html>
            HTML;

        expect(fn () => $this->assertHtmlHasValidLanguage($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when html has lang attribute for French', function () {
        $html = <<<'HTML'
                <!DOCTYPE html>
                <html lang="fr">
                    <head><title>Test</title></head>
                    <body><p>Bonjour</p></body>
                </html>
            HTML;

        expect(fn () => $this->assertHtmlHasValidLanguage($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when html has lang attribute for Spanish', function () {
        $html = <<<'HTML'
                <!DOCTYPE html>
                <html lang="es">
                    <head><title>Test</title></head>
                    <body><p>Hola</p></body>
                </html>
            HTML;

        expect(fn () => $this->assertHtmlHasValidLanguage($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when html has no lang attribute', function () {
        $html = <<<'HTML'
                <!DOCTYPE html>
                <html>
                    <head>
                        <meta charset='utf-8'/>
                        <title>Je ne parle pas Français</title>
                    </head>
                    <body>
                        <ul>
                            <li>Speak French?</li>
                            <li>Habla usted francés?</li>
                            <li>Sprechen Sie Französisch?</li>
                        </ul>
                    </body>
                </html>
            HTML;

        $this->assertHtmlHasValidLanguage($html);
    })->throws(AssertionFailedError::class, 'missing a lang attribute');

    test('fails when html has empty lang attribute', function () {
        $html = <<<'HTML'
                <!DOCTYPE html>
                <html lang="">
                    <head><title>Test</title></head>
                    <body><p>Hello</p></body>
                </html>
            HTML;

        $this->assertHtmlHasValidLanguage($html);
    })->throws(AssertionFailedError::class, 'empty lang attribute');

    test('fails when html has lang attribute with whitespace', function () {
        $html = <<<'HTML'
                <!DOCTYPE html>
                <html lang="en us">
                    <head><title>Test</title></head>
                    <body><p>Hello</p></body>
                </html>
            HTML;

        $this->assertHtmlHasValidLanguage($html);
    })->throws(AssertionFailedError::class, 'containing whitespace');
});

describe('assertPresentationTablesDoNotUseSemanticElements', function () {
    test('passes when table without role presentation uses semantic elements', function () {
        $html = <<<'HTML'
                <table>
                    <caption>Data Table</caption>
                    <thead>
                        <tr>
                            <th scope="col">Header 1</th>
                            <th scope="col">Header 2</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Data 1</td>
                            <td>Data 2</td>
                        </tr>
                    </tbody>
                </table>
            HTML;

        expect(fn () => $this->assertPresentationTablesDoNotUseSemanticElements($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when presentation table uses only td elements', function () {
        $html = <<<'HTML'
                <table role="presentation">
                    <tbody>
                        <tr>
                            <td>Layout cell 1</td>
                            <td>Layout cell 2</td>
                        </tr>
                    </tbody>
                </table>
            HTML;

        expect(fn () => $this->assertPresentationTablesDoNotUseSemanticElements($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when presentation table uses colspan for layout', function () {
        $html = <<<'HTML'
                <table role="presentation">
                    <tbody>
                        <tr>
                            <td colspan="2">Wide cell</td>
                        </tr>
                        <tr>
                            <td>Cell 1</td>
                            <td>Cell 2</td>
                        </tr>
                    </tbody>
                </table>
            HTML;

        expect(fn () => $this->assertPresentationTablesDoNotUseSemanticElements($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when no tables are present', function () {
        $html = <<<'HTML'
                <div>No tables here</div>
            HTML;

        expect(fn () => $this->assertPresentationTablesDoNotUseSemanticElements($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when presentation table has caption', function () {
        $html = <<<'HTML'
                <table role="presentation">
                    <caption>I do not mean anything!</caption>
                    <tbody>
                        <tr>
                            <td colspan="2">It works</td>
                        </tr>
                    </tbody>
                </table>
            HTML;

        $this->assertPresentationTablesDoNotUseSemanticElements($html);
    })->throws(AssertionFailedError::class, 'semantic element <caption>');

    test('fails when presentation table has th elements', function () {
        $html = <<<'HTML'
                <table role="presentation">
                    <tr>
                        <th>Not a header</th>
                        <td>Data</td>
                    </tr>
                </table>
            HTML;

        $this->assertPresentationTablesDoNotUseSemanticElements($html);
    })->throws(AssertionFailedError::class, 'semantic element <th>');

    test('fails when presentation table has thead', function () {
        $html = <<<'HTML'
                <table role="presentation">
                    <thead>
                        <tr>
                            <td>Layout header</td>
                        </tr>
                    </thead>
                </table>
            HTML;

        $this->assertPresentationTablesDoNotUseSemanticElements($html);
    })->throws(AssertionFailedError::class, 'semantic element <thead>');

    test('fails when presentation table has tfoot', function () {
        $html = <<<'HTML'
                <table role="presentation">
                    <tfoot>
                        <tr>
                            <td>Layout footer</td>
                        </tr>
                    </tfoot>
                </table>
            HTML;

        $this->assertPresentationTablesDoNotUseSemanticElements($html);
    })->throws(AssertionFailedError::class, 'semantic element <tfoot>');

    test('fails when presentation table has colgroup', function () {
        $html = <<<'HTML'
                <table role="presentation">
                    <colgroup>
                        <col span="2">
                    </colgroup>
                    <tr>
                        <td>Cell 1</td>
                        <td>Cell 2</td>
                    </tr>
                </table>
            HTML;

        $this->assertPresentationTablesDoNotUseSemanticElements($html);
    })->throws(AssertionFailedError::class, 'semantic element <colgroup>');

    test('fails when presentation table has scope attribute', function () {
        $html = <<<'HTML'
                <table role="presentation">
                    <tr>
                        <td scope="col">Not semantic</td>
                    </tr>
                </table>
            HTML;

        $this->assertPresentationTablesDoNotUseSemanticElements($html);
    })->throws(AssertionFailedError::class, 'semantic attribute');

    test('fails when presentation table has headers attribute', function () {
        $html = <<<'HTML'
                <table role="presentation">
                    <tr>
                        <td id="h1">Cell 1</td>
                        <td headers="h1">Cell 2</td>
                    </tr>
                </table>
            HTML;

        $this->assertPresentationTablesDoNotUseSemanticElements($html);
    })->throws(AssertionFailedError::class, 'semantic attribute');

    test('fails when presentation table has axis attribute', function () {
        $html = <<<'HTML'
                <table role="presentation">
                    <tr>
                        <td axis="category">Cell</td>
                    </tr>
                </table>
            HTML;

        $this->assertPresentationTablesDoNotUseSemanticElements($html);
    })->throws(AssertionFailedError::class, 'semantic attribute');
});

describe('assertWidthHeightOnlyOnAppropriateElements', function () {
    test('passes when img has width and height', function () {
        $html = <<<'HTML'
                <img src="photo.jpg" alt="Photo" width="300" height="200" />
            HTML;

        expect(fn () => $this->assertWidthHeightOnlyOnAppropriateElements($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when iframe has width and height', function () {
        $html = <<<'HTML'
                <iframe src="https://example.com" title="Example" width="560" height="315"></iframe>
            HTML;

        expect(fn () => $this->assertWidthHeightOnlyOnAppropriateElements($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when svg has width and height', function () {
        $html = <<<'HTML'
                <svg width="100" height="100">
                    <circle cx="50" cy="50" r="40" />
                </svg>
            HTML;

        expect(fn () => $this->assertWidthHeightOnlyOnAppropriateElements($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when canvas has width and height', function () {
        $html = <<<'HTML'
                <canvas width="300" height="150"></canvas>
            HTML;

        expect(fn () => $this->assertWidthHeightOnlyOnAppropriateElements($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when object has width and height', function () {
        $html = <<<'HTML'
                <object data="movie.swf" width="400" height="300"></object>
            HTML;

        expect(fn () => $this->assertWidthHeightOnlyOnAppropriateElements($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when embed has width and height', function () {
        $html = <<<'HTML'
                <embed src="video.mp4" width="640" height="480" />
            HTML;

        expect(fn () => $this->assertWidthHeightOnlyOnAppropriateElements($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when elements without width or height attributes', function () {
        $html = <<<'HTML'
                <div>Content</div>
                <p>Paragraph</p>
                <span>Text</span>
            HTML;

        expect(fn () => $this->assertWidthHeightOnlyOnAppropriateElements($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when p has width attribute', function () {
        $html = <<<'HTML'
                <p width="20">Damned! I feel sooo strait :(</p>
            HTML;

        $this->assertWidthHeightOnlyOnAppropriateElements($html);
    })->throws(AssertionFailedError::class, 'inappropriate width attribute');

    test('fails when div has height attribute', function () {
        $html = <<<'HTML'
                <div height="100">Too tall</div>
            HTML;

        $this->assertWidthHeightOnlyOnAppropriateElements($html);
    })->throws(AssertionFailedError::class, 'inappropriate height attribute');

    test('fails when span has width attribute', function () {
        $html = <<<'HTML'
                <span width="50">Wide span</span>
            HTML;

        $this->assertWidthHeightOnlyOnAppropriateElements($html);
    })->throws(AssertionFailedError::class, 'inappropriate width attribute');

    test('fails when table has width attribute', function () {
        $html = <<<'HTML'
                <table width="100%">
                    <tr><td>Cell</td></tr>
                </table>
            HTML;

        $this->assertWidthHeightOnlyOnAppropriateElements($html);
    })->throws(AssertionFailedError::class, 'inappropriate width attribute');

    test('fails when td has width attribute', function () {
        $html = <<<'HTML'
                <table>
                    <tr><td width="100">Cell</td></tr>
                </table>
            HTML;

        $this->assertWidthHeightOnlyOnAppropriateElements($html);
    })->throws(AssertionFailedError::class, 'inappropriate width attribute');

    test('fails when multiple elements have inappropriate attributes', function () {
        $html = <<<'HTML'
                <div width="200">Wide div</div>
                <p height="50">Tall paragraph</p>
            HTML;

        $this->assertWidthHeightOnlyOnAppropriateElements($html);
    })->throws(AssertionFailedError::class, 'use CSS instead');
});

describe('assertNoJavascriptEventAttributes', function () {
    test('passes when elements have no event attributes', function () {
        $html = <<<'HTML'
                <button type="button">Click me</button>
                <a href="/page">Link</a>
                <div>Content</div>
            HTML;

        expect(fn () => $this->assertNoJavascriptEventAttributes($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when elements use classes instead of event handlers', function () {
        $html = <<<'HTML'
                <button type="button" class="js-click-handler">Click me</button>
                <div class="hover-effect">Hover over me</div>
            HTML;

        expect(fn () => $this->assertNoJavascriptEventAttributes($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when no elements are present', function () {
        $html = <<<'HTML'
                <p>Just text</p>
            HTML;

        expect(fn () => $this->assertNoJavascriptEventAttributes($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when element has onclick attribute', function () {
        $html = <<<'HTML'
                <span onclick="alert('You clicked!');">Click click click</span>
            HTML;

        $this->assertNoJavascriptEventAttributes($html);
    })->throws(AssertionFailedError::class, 'JavaScript event attribute');

    test('fails when element has onmouseover attribute', function () {
        $html = <<<'HTML'
                <div onmouseover="this.style.color='red'">Hover me</div>
            HTML;

        $this->assertNoJavascriptEventAttributes($html);
    })->throws(AssertionFailedError::class, 'JavaScript event attribute');

    test('fails when element has onload attribute', function () {
        $html = <<<'HTML'
                <body onload="init()">
                    <p>Content</p>
                </body>
            HTML;

        $this->assertNoJavascriptEventAttributes($html);
    })->throws(AssertionFailedError::class, 'JavaScript event attribute');

    test('fails when element has onsubmit attribute', function () {
        $html = <<<'HTML'
                <form action="/submit" onsubmit="return validate()">
                    <input type="text" />
                </form>
            HTML;

        $this->assertNoJavascriptEventAttributes($html);
    })->throws(AssertionFailedError::class, 'JavaScript event attribute');

    test('fails when element has onfocus attribute', function () {
        $html = <<<'HTML'
                <input type="text" onfocus="this.select()" />
            HTML;

        $this->assertNoJavascriptEventAttributes($html);
    })->throws(AssertionFailedError::class, 'JavaScript event attribute');

    test('fails when element has onkeydown attribute', function () {
        $html = <<<'HTML'
                <input type="text" onkeydown="handleKeyDown(event)" />
            HTML;

        $this->assertNoJavascriptEventAttributes($html);
    })->throws(AssertionFailedError::class, 'JavaScript event attribute');

    test('fails when element has ondblclick attribute', function () {
        $html = <<<'HTML'
                <div ondblclick="alert('Double clicked!')">Double click me</div>
            HTML;

        $this->assertNoJavascriptEventAttributes($html);
    })->throws(AssertionFailedError::class, 'JavaScript event attribute');

    test('fails when element has onscroll attribute', function () {
        $html = <<<'HTML'
                <div onscroll="trackScroll()">Scrollable content</div>
            HTML;

        $this->assertNoJavascriptEventAttributes($html);
    })->throws(AssertionFailedError::class, 'JavaScript event attribute');

    test('fails when element has multiple event attributes', function () {
        $html = <<<'HTML'
                <button onclick="doSomething()" onmouseover="highlight()">Click me</button>
            HTML;

        $this->assertNoJavascriptEventAttributes($html);
    })->throws(AssertionFailedError::class, 'onclick, onmouseover');

    test('fails when multiple elements have event attributes', function () {
        $html = <<<'HTML'
                <button onclick="action1()">Button 1</button>
                <div onmouseover="action2()">Div</div>
            HTML;

        $this->assertNoJavascriptEventAttributes($html);
    })->throws(AssertionFailedError::class, 'use CSS pseudo-classes or addEventListener');
});

describe('assertValidCssNamespaces', function () {
    test('passes when id starts with letter', function () {
        $html = <<<'HTML'
                <div id="valid-id">Content</div>
            HTML;

        expect(fn () => $this->assertValidCssNamespaces($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when class starts with letter', function () {
        $html = <<<'HTML'
                <div class="valid-class">Content</div>
            HTML;

        expect(fn () => $this->assertValidCssNamespaces($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when id starts with single hyphen followed by letter', function () {
        $html = <<<'HTML'
                <div id="-valid">Content</div>
            HTML;

        expect(fn () => $this->assertValidCssNamespaces($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when class starts with underscore', function () {
        $html = <<<'HTML'
                <div class="_valid">Content</div>
            HTML;

        expect(fn () => $this->assertValidCssNamespaces($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when id contains digits but does not start with them', function () {
        $html = <<<'HTML'
                <div id="item123">Content</div>
            HTML;

        expect(fn () => $this->assertValidCssNamespaces($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when multiple classes are valid', function () {
        $html = <<<'HTML'
                <div class="header nav primary">Content</div>
            HTML;

        expect(fn () => $this->assertValidCssNamespaces($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when id starts with digit', function () {
        $html = <<<'HTML'
                <div id="2">Hello! My ID is 2.</div>
            HTML;

        $this->assertValidCssNamespaces($html);
    })->throws(AssertionFailedError::class, 'invalid CSS identifier');

    test('fails when id starts with zero', function () {
        $html = <<<'HTML'
                <div id="0-item">Content</div>
            HTML;

        $this->assertValidCssNamespaces($html);
    })->throws(AssertionFailedError::class, 'id="0-item"');

    test('fails when id starts with two hyphens', function () {
        $html = <<<'HTML'
                <div id="--invalid">Content</div>
            HTML;

        $this->assertValidCssNamespaces($html);
    })->throws(AssertionFailedError::class, 'id="--invalid"');

    test('fails when id starts with hyphen followed by digit', function () {
        $html = <<<'HTML'
                <div id="-5-item">Content</div>
            HTML;

        $this->assertValidCssNamespaces($html);
    })->throws(AssertionFailedError::class, 'id="-5-item"');

    test('fails when class starts with digit', function () {
        $html = <<<'HTML'
                <div class="3column">Content</div>
            HTML;

        $this->assertValidCssNamespaces($html);
    })->throws(AssertionFailedError::class, 'class="3column"');

    test('fails when class starts with two hyphens', function () {
        $html = <<<'HTML'
                <div class="--utility">Content</div>
            HTML;

        $this->assertValidCssNamespaces($html);
    })->throws(AssertionFailedError::class, 'class="--utility"');

    test('fails when class starts with hyphen followed by digit', function () {
        $html = <<<'HTML'
                <div class="-1st">Content</div>
            HTML;

        $this->assertValidCssNamespaces($html);
    })->throws(AssertionFailedError::class, 'class="-1st"');

    test('fails when multiple classes include invalid ones', function () {
        $html = <<<'HTML'
                <div class="valid 2invalid another-valid">Content</div>
            HTML;

        $this->assertValidCssNamespaces($html);
    })->throws(AssertionFailedError::class, 'class="2invalid"');

    test('fails when element has both invalid id and class', function () {
        $html = <<<'HTML'
                <div id="1invalid" class="9wrong">Content</div>
            HTML;

        $this->assertValidCssNamespaces($html);
    })->throws(AssertionFailedError::class, 'id="1invalid"');

    test('fails when multiple elements have invalid identifiers', function () {
        $html = <<<'HTML'
                <div id="1first">First</div>
                <div class="2second">Second</div>
            HTML;

        $this->assertValidCssNamespaces($html);
    })->throws(AssertionFailedError::class, 'must not start with digit, --, or -digit');
});

describe('assertTitleNotEmpty', function () {
    test('passes when title has text content', function () {
        $html = <<<'HTML'
                <!DOCTYPE html>
                <html lang="en">
                    <head>
                        <title>Page Title</title>
                    </head>
                    <body><p>Content</p></body>
                </html>
            HTML;

        expect(fn () => $this->assertTitleNotEmpty($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when title has text with special characters', function () {
        $html = <<<'HTML'
                <head>
                    <title>Welcome | My Site - Home</title>
                </head>
            HTML;

        expect(fn () => $this->assertTitleNotEmpty($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when title has unicode content', function () {
        $html = <<<'HTML'
                <head>
                    <title>Accueil — Mon Site 🏠</title>
                </head>
            HTML;

        expect(fn () => $this->assertTitleNotEmpty($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when title has long content', function () {
        $html = <<<'HTML'
                <head>
                    <title>This is a very long page title that describes the content in detail</title>
                </head>
            HTML;

        expect(fn () => $this->assertTitleNotEmpty($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when document has no title tag', function () {
        $html = <<<'HTML'
                <!DOCTYPE html>
                <html lang="en">
                    <head>
                        <meta charset="utf-8">
                    </head>
                    <body><p>Content</p></body>
                </html>
            HTML;

        $this->assertTitleNotEmpty($html);
    })->throws(AssertionFailedError::class, 'missing a <title> tag');

    test('fails when title is empty', function () {
        $html = <<<'HTML'
                <title></title>
            HTML;

        $this->assertTitleNotEmpty($html);
    })->throws(AssertionFailedError::class, 'empty or contains only whitespace');

    test('fails when title contains only whitespace', function () {
        $html = <<<'HTML'
                <head>
                    <title>   </title>
                </head>
            HTML;

        $this->assertTitleNotEmpty($html);
    })->throws(AssertionFailedError::class, 'empty or contains only whitespace');

    test('fails when title contains only newlines and spaces', function () {
        $html = <<<'HTML'
                <head>
                    <title>

                    </title>
                </head>
            HTML;

        $this->assertTitleNotEmpty($html);
    })->throws(AssertionFailedError::class, 'empty or contains only whitespace');

    test('fails when title contains only tabs', function () {
        $html = <<<'HTML'
                <head>
                    <title>		</title>
                </head>
            HTML;

        $this->assertTitleNotEmpty($html);
    })->throws(AssertionFailedError::class, 'empty title');
});

describe('assertViewportAllowsZoom', function () {
    test('passes when viewport has no zoom restrictions', function () {
        $html = <<<'HTML'
                <head>
                    <meta name="viewport" content="width=device-width, initial-scale=1">
                </head>
            HTML;

        expect(fn () => $this->assertViewportAllowsZoom($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when viewport only sets width and initial-scale', function () {
        $html = <<<'HTML'
                <head>
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                </head>
            HTML;

        expect(fn () => $this->assertViewportAllowsZoom($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when no viewport meta tag exists', function () {
        $html = <<<'HTML'
                <head>
                    <meta charset="utf-8">
                    <title>Page</title>
                </head>
            HTML;

        expect(fn () => $this->assertViewportAllowsZoom($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when viewport has user-scalable=yes', function () {
        $html = <<<'HTML'
                <head>
                    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes">
                </head>
            HTML;

        expect(fn () => $this->assertViewportAllowsZoom($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when viewport has high maximum-scale', function () {
        $html = <<<'HTML'
                <head>
                    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=10">
                </head>
            HTML;

        expect(fn () => $this->assertViewportAllowsZoom($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when viewport has user-scalable=no', function () {
        $html = <<<'HTML'
                <head>
                    <meta name="viewport" content="user-scalable=no">
                </head>
            HTML;

        $this->assertViewportAllowsZoom($html);
    })->throws(AssertionFailedError::class, 'user-scalable=no');

    test('fails when viewport has maximum-scale', function () {
        $html = <<<'HTML'
                <head>
                    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
                </head>
            HTML;

        $this->assertViewportAllowsZoom($html);
    })->throws(AssertionFailedError::class, 'maximum-scale=1');

    test('fails when viewport has minimum-scale', function () {
        $html = <<<'HTML'
                <head>
                    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
                </head>
            HTML;

        $this->assertViewportAllowsZoom($html);
    })->throws(AssertionFailedError::class, 'minimum-scale=1');

    test('fails when viewport has maximum-scale with decimal', function () {
        $html = <<<'HTML'
                <head>
                    <meta name="viewport" content="width=device-width, maximum-scale=1.5">
                </head>
            HTML;

        $this->assertViewportAllowsZoom($html);
    })->throws(AssertionFailedError::class, 'maximum-scale=1.5');

    test('fails when viewport has multiple zoom restrictions', function () {
        $html = <<<'HTML'
                <head>
                    <meta name="viewport" content="width=device-width, user-scalable=no, maximum-scale=1, minimum-scale=1">
                </head>
            HTML;

        $this->assertViewportAllowsZoom($html);
    })->throws(AssertionFailedError::class, 'user-scalable=no');

    test('fails when viewport has maximum-scale with spaces', function () {
        $html = <<<'HTML'
                <head>
                    <meta name="viewport" content="width=device-width, maximum-scale = 1.0">
                </head>
            HTML;

        $this->assertViewportAllowsZoom($html);
    })->throws(AssertionFailedError::class, 'maximum-scale=1.0');

    test('fails when viewport restricts zoom for accessibility', function () {
        $html = <<<'HTML'
                <!DOCTYPE html>
                <html lang="en">
                    <head>
                        <meta charset="utf-8">
                        <title>Wrong viewport instruction</title>
                        <meta name="viewport" content="user-scalable=no">
                    </head>
                    <body>
                        <h1>Wrong viewport instruction</h1>
                    </body>
                </html>
            HTML;

        $this->assertViewportAllowsZoom($html);
    })->throws(AssertionFailedError::class, 'restrict zoom');
});

describe('assertCharsetIsUtf8', function () {
    test('passes when charset is utf-8 lowercase', function () {
        $html = <<<'HTML'
                <head>
                    <meta charset="utf-8">
                </head>
            HTML;

        expect(fn () => $this->assertCharsetIsUtf8($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when charset is UTF-8 uppercase', function () {
        $html = <<<'HTML'
                <head>
                    <meta charset="UTF-8">
                </head>
            HTML;

        expect(fn () => $this->assertCharsetIsUtf8($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when no charset meta tag exists', function () {
        $html = <<<'HTML'
                <head>
                    <title>Page</title>
                </head>
            HTML;

        expect(fn () => $this->assertCharsetIsUtf8($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes in complete HTML document with utf-8', function () {
        $html = <<<'HTML'
                <!DOCTYPE html>
                <html lang="en">
                    <head>
                        <meta charset="utf-8">
                        <title>Test Page</title>
                    </head>
                    <body>
                        <p>Content</p>
                    </body>
                </html>
            HTML;

        expect(fn () => $this->assertCharsetIsUtf8($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when charset is not utf-8', function () {
        $html = <<<'HTML'
                <head>
                    <meta charset="ISO-8859-1">
                </head>
            HTML;

        $this->assertCharsetIsUtf8($html);
    })->throws(AssertionFailedError::class, 'incorrect charset');
});

describe('assertCharsetComesFirst', function () {
    test('passes when charset is the first child of head', function () {
        $html = <<<'HTML'
                <!DOCTYPE html>
                <html lang="en">
                    <head>
                        <meta charset="utf-8">
                        <title>Test Page</title>
                    </head>
                    <body><p>Content</p></body>
                </html>
            HTML;

        expect(fn () => $this->assertCharsetComesFirst($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when charset is first with whitespace before', function () {
        $html = <<<'HTML'
                <head>
                    <meta charset="utf-8">
                    <title>Test</title>
                </head>
            HTML;

        expect(fn () => $this->assertCharsetComesFirst($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when charset is UTF-8 and comes first', function () {
        $html = <<<'HTML'
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1">
                    <title>Page</title>
                </head>
            HTML;

        expect(fn () => $this->assertCharsetComesFirst($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when no head element exists', function () {
        $html = <<<'HTML'
                <p>Just content</p>
            HTML;

        expect(fn () => $this->assertCharsetComesFirst($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when head is empty', function () {
        $html = <<<'HTML'
                <head></head>
            HTML;

        expect(fn () => $this->assertCharsetComesFirst($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when title comes before charset', function () {
        $html = <<<'HTML'
                <head>
                    <title>Oups !</title>
                    <meta charset="utf-8">
                </head>
            HTML;

        $this->assertCharsetComesFirst($html);
    })->throws(AssertionFailedError::class, 'instead of charset meta tag');

    test('fails when style comes before charset', function () {
        $html = <<<'HTML'
                <head>
                    <style>html{font:.75rem sans-serif}</style>
                    <meta charset="utf-8">
                </head>
            HTML;

        $this->assertCharsetComesFirst($html);
    })->throws(AssertionFailedError::class, 'First child of <head>');

    test('fails when link comes before charset', function () {
        $html = <<<'HTML'
                <head>
                    <link rel="stylesheet" href="style.css">
                    <meta charset="utf-8">
                </head>
            HTML;

        $this->assertCharsetComesFirst($html);
    })->throws(AssertionFailedError::class, 'charset is not declared first');

    test('fails when meta viewport comes before charset', function () {
        $html = <<<'HTML'
                <head>
                    <meta name="viewport" content="width=device-width, initial-scale=1">
                    <meta charset="utf-8">
                </head>
            HTML;

        $this->assertCharsetComesFirst($html);
    })->throws(AssertionFailedError::class, 'instead of charset meta tag');

    test('fails when script comes before charset', function () {
        $html = <<<'HTML'
                <head>
                    <script>console.log('test');</script>
                    <meta charset="utf-8">
                </head>
            HTML;

        $this->assertCharsetComesFirst($html);
    })->throws(AssertionFailedError::class, 'charset is not declared first');

    test('fails with security message', function () {
        $html = <<<'HTML'
                <head>
                    <title>Late charset</title>
                    <meta charset="utf-8">
                </head>
            HTML;

        $this->assertCharsetComesFirst($html);
    })->throws(AssertionFailedError::class, 'security and compatibility');

    test('fails when no charset exists at all', function () {
        $html = <<<'HTML'
                <head>
                    <title>No charset</title>
                </head>
            HTML;

        $this->assertCharsetComesFirst($html);
    })->throws(AssertionFailedError::class, 'instead of charset meta tag');
});

describe('assertDirAttributeIsValid', function () {
    test('passes when dir is rtl', function () {
        $html = <<<'HTML'
                <p dir="rtl">مرحبا</p>
            HTML;

        expect(fn () => $this->assertDirAttributeIsValid($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when dir is ltr', function () {
        $html = <<<'HTML'
                <p dir="ltr">Hello</p>
            HTML;

        expect(fn () => $this->assertDirAttributeIsValid($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when dir is auto', function () {
        $html = <<<'HTML'
                <p dir="auto">Automatic direction</p>
            HTML;

        expect(fn () => $this->assertDirAttributeIsValid($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when no dir attribute exists', function () {
        $html = <<<'HTML'
                <p>No direction specified</p>
            HTML;

        expect(fn () => $this->assertDirAttributeIsValid($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when multiple elements have valid dir attributes', function () {
        $html = <<<'HTML'
                <div dir="ltr">
                    <p dir="rtl">مرحبا</p>
                    <span dir="auto">Mixed content</span>
                </div>
            HTML;

        expect(fn () => $this->assertDirAttributeIsValid($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when html element has valid dir', function () {
        $html = <<<'HTML'
                <html dir="ltr">
                    <body>
                        <p>Content</p>
                    </body>
                </html>
            HTML;

        expect(fn () => $this->assertDirAttributeIsValid($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when dir is uppercase', function () {
        $html = <<<'HTML'
                <p dir="RTL">Uppercase is valid (HTML is case-insensitive)</p>
            HTML;

        expect(fn () => $this->assertDirAttributeIsValid($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when dir is mixed case', function () {
        $html = <<<'HTML'
                <p dir="Ltr">Mixed case is valid (HTML is case-insensitive)</p>
            HTML;

        expect(fn () => $this->assertDirAttributeIsValid($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when dir has invalid value', function () {
        $html = <<<'HTML'
                <p dir="wtf">Well, I'm kinda disoriented…</p>
            HTML;

        $this->assertDirAttributeIsValid($html);
    })->throws(AssertionFailedError::class, 'invalid dir="wtf"');

    test('fails when dir is left', function () {
        $html = <<<'HTML'
                <p dir="left">Invalid direction</p>
            HTML;

        $this->assertDirAttributeIsValid($html);
    })->throws(AssertionFailedError::class, 'invalid [dir] attribute');

    test('fails when dir is right', function () {
        $html = <<<'HTML'
                <p dir="right">Invalid direction</p>
            HTML;

        $this->assertDirAttributeIsValid($html);
    })->throws(AssertionFailedError::class, 'invalid [dir] attribute');

    test('fails when dir is empty', function () {
        $html = <<<'HTML'
                <p dir="">Empty direction</p>
            HTML;

        $this->assertDirAttributeIsValid($html);
    })->throws(AssertionFailedError::class, 'invalid [dir] attribute');

    test('fails when multiple elements have invalid dir', function () {
        $html = <<<'HTML'
                <div dir="wrong">
                    <p dir="bad">Content</p>
                </div>
            HTML;

        $this->assertDirAttributeIsValid($html);
    })->throws(AssertionFailedError::class, 'invalid [dir] attribute');

    test('fails with error message showing valid values', function () {
        $html = <<<'HTML'
                <p dir="invalid">Text</p>
            HTML;

        $this->assertDirAttributeIsValid($html);
    })->throws(AssertionFailedError::class, 'must be "rtl", "ltr", or "auto"');
});

describe('assertAccesskeyNotUsed', function () {
    test('passes when no accesskey attributes exist', function () {
        $html = <<<'HTML'
                <a href="/home">Home</a>
                <button>Submit</button>
            HTML;

        expect(fn () => $this->assertAccesskeyNotUsed($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when elements have no shortcuts', function () {
        $html = <<<'HTML'
                <div>
                    <a href="/about">About</a>
                    <input type="text" name="search">
                    <button type="submit">Go</button>
                </div>
            HTML;

        expect(fn () => $this->assertAccesskeyNotUsed($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when using aria attributes instead', function () {
        $html = <<<'HTML'
                <button aria-keyshortcuts="Control+S">Save</button>
            HTML;

        expect(fn () => $this->assertAccesskeyNotUsed($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when link has accesskey', function () {
        $html = <<<'HTML'
                <a id="key" name="key" accesskey="1">Skip to this link using <kbd>1</kbd></a>
            HTML;

        $this->assertAccesskeyNotUsed($html);
    })->throws(AssertionFailedError::class, 'accesskey="1"');

    test('fails when button has accesskey', function () {
        $html = <<<'HTML'
                <button accesskey="s">Save</button>
            HTML;

        $this->assertAccesskeyNotUsed($html);
    })->throws(AssertionFailedError::class, 'accesskey="s"');

    test('fails when input has accesskey', function () {
        $html = <<<'HTML'
                <input type="text" accesskey="u" placeholder="Username">
            HTML;

        $this->assertAccesskeyNotUsed($html);
    })->throws(AssertionFailedError::class, 'accesskey="u"');

    test('fails when textarea has accesskey', function () {
        $html = <<<'HTML'
                <textarea accesskey="c">Comment</textarea>
            HTML;

        $this->assertAccesskeyNotUsed($html);
    })->throws(AssertionFailedError::class, 'accesskey="c"');

    test('fails when div has accesskey', function () {
        $html = <<<'HTML'
                <div accesskey="h">Help section</div>
            HTML;

        $this->assertAccesskeyNotUsed($html);
    })->throws(AssertionFailedError::class, 'conflicts with browser and OS shortcuts');

    test('fails when accesskey is a letter', function () {
        $html = <<<'HTML'
                <a href="#content" accesskey="n">Skip to content</a>
            HTML;

        $this->assertAccesskeyNotUsed($html);
    })->throws(AssertionFailedError::class, 'accesskey="n"');

    test('fails when accesskey is a number', function () {
        $html = <<<'HTML'
                <a href="#main" accesskey="0">Main content</a>
            HTML;

        $this->assertAccesskeyNotUsed($html);
    })->throws(AssertionFailedError::class, 'accesskey="0"');

    test('fails when accesskey is a special character', function () {
        $html = <<<'HTML'
                <button accesskey="*">Star this</button>
            HTML;

        $this->assertAccesskeyNotUsed($html);
    })->throws(AssertionFailedError::class, 'accesskey="*"');

    test('fails when multiple elements have accesskey', function () {
        $html = <<<'HTML'
                <a href="#" accesskey="h">Home</a>
                <button accesskey="s">Save</button>
                <input accesskey="u" type="text">
            HTML;

        $this->assertAccesskeyNotUsed($html);
    })->throws(AssertionFailedError::class, 'conflicts with browser and OS shortcuts');

    test('fails with error message about conflicts', function () {
        $html = <<<'HTML'
                <a accesskey="f" href="/find">Find</a>
            HTML;

        $this->assertAccesskeyNotUsed($html);
    })->throws(AssertionFailedError::class, 'may conflict with browser/OS shortcuts');
});

describe('assertRadioAndCheckboxInputsHaveName', function () {
    test('passes when radio buttons have name attribute', function () {
        $html = <<<'HTML'
                <input type="radio" name="options" id="option1">
                <input type="radio" name="options" id="option2">
            HTML;

        expect(fn () => $this->assertRadioAndCheckboxInputsHaveName($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when checkboxes have name attribute', function () {
        $html = <<<'HTML'
                <input type="checkbox" name="features[]" id="feature1">
                <input type="checkbox" name="features[]" id="feature2">
            HTML;

        expect(fn () => $this->assertRadioAndCheckboxInputsHaveName($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when single checkbox without name exists', function () {
        $html = <<<'HTML'
                <input type="checkbox" id="agree">
            HTML;

        expect(fn () => $this->assertRadioAndCheckboxInputsHaveName($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when radio and checkbox groups are properly named', function () {
        $html = <<<'HTML'
                <fieldset>
                    <legend>Options</legend>
                    <input type="radio" name="options" id="option1">
                    <input type="radio" name="options" id="option2">
                </fieldset>
                <fieldset>
                    <legend>Features</legend>
                    <input type="checkbox" name="features[]" id="feature1">
                    <input type="checkbox" name="features[]" id="feature2">
                </fieldset>
            HTML;

        expect(fn () => $this->assertRadioAndCheckboxInputsHaveName($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when text inputs have no name', function () {
        $html = <<<'HTML'
                <input type="text" id="username">
                <input type="email" id="email">
            HTML;

        expect(fn () => $this->assertRadioAndCheckboxInputsHaveName($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when radio button lacks name attribute', function () {
        $html = <<<'HTML'
                <input type="radio" id="option-1">
            HTML;

        $this->assertRadioAndCheckboxInputsHaveName($html);
    })->throws(AssertionFailedError::class, 'lacks [name] attribute');

    test('fails when one radio in group lacks name', function () {
        $html = <<<'HTML'
                <form action="/">
                    <fieldset>
                        <legend>Options</legend>
                        <p>
                            <label for="option-1">Option N<sup>o</sup>1</label>
                            <input type="radio" id="option-1">
                        </p>
                        <p>
                            <label for="option-2">Option N<sup>o</sup>2</label>
                            <input type="radio" id="option-2" name="options">
                        </p>
                    </fieldset>
                </form>
            HTML;

        $this->assertRadioAndCheckboxInputsHaveName($html);
    })->throws(AssertionFailedError::class, 'id="option-1"');

    test('fails when multiple radios lack name', function () {
        $html = <<<'HTML'
                <input type="radio" id="yes">
                <input type="radio" id="no">
            HTML;

        $this->assertRadioAndCheckboxInputsHaveName($html);
    })->throws(AssertionFailedError::class, 'required for grouping');

    test('fails when multiple checkboxes lack name', function () {
        $html = <<<'HTML'
                <input type="checkbox" id="feature1">
                <input type="checkbox" id="feature2">
            HTML;

        $this->assertRadioAndCheckboxInputsHaveName($html);
    })->throws(AssertionFailedError::class, 'required when multiple checkboxes exist');

    test('fails when checkbox without name exists alongside named checkbox', function () {
        $html = <<<'HTML'
                <input type="checkbox" name="newsletter" id="newsletter">
                <input type="checkbox" id="terms">
            HTML;

        $this->assertRadioAndCheckboxInputsHaveName($html);
    })->throws(AssertionFailedError::class, 'id="terms"');

    test('fails when radio without id lacks name', function () {
        $html = <<<'HTML'
                <input type="radio">
            HTML;

        $this->assertRadioAndCheckboxInputsHaveName($html);
    })->throws(AssertionFailedError::class, 'lacks [name] attribute');

    test('fails when checkbox without id lacks name in multi-checkbox context', function () {
        $html = <<<'HTML'
                <input type="checkbox" name="one">
                <input type="checkbox">
            HTML;

        $this->assertRadioAndCheckboxInputsHaveName($html);
    })->throws(AssertionFailedError::class, 'lacks [name] attribute');

    test('fails with mixed radio and checkbox violations', function () {
        $html = <<<'HTML'
                <input type="radio" id="radio1">
                <input type="checkbox" id="check1">
                <input type="checkbox" id="check2">
            HTML;

        $this->assertRadioAndCheckboxInputsHaveName($html);
    })->throws(AssertionFailedError::class, 'required for proper grouping');

    test('fails with error message mentioning grouping', function () {
        $html = <<<'HTML'
                <input type="radio" id="option">
            HTML;

        $this->assertRadioAndCheckboxInputsHaveName($html);
    })->throws(AssertionFailedError::class, 'required for grouping');
});

describe('assertRadioButtonsInsideFieldset', function () {
    test('passes when radio buttons are inside fieldset', function () {
        $html = <<<'HTML'
                <fieldset>
                    <legend>Options</legend>
                    <input type="radio" name="options" id="option1">
                    <input type="radio" name="options" id="option2">
                </fieldset>
            HTML;

        expect(fn () => $this->assertRadioButtonsInsideFieldset($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when radio buttons are nested inside fieldset', function () {
        $html = <<<'HTML'
                <fieldset>
                    <legend>Trying within a fieldset, shouldn't match</legend>
                    <p>
                        <label for="option-2">Option N<sup>o</sup>2</label>
                        <input type="radio" id="option-2" name="options">
                    </p>
                </fieldset>
            HTML;

        expect(fn () => $this->assertRadioButtonsInsideFieldset($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when multiple fieldsets contain radio buttons', function () {
        $html = <<<'HTML'
                <fieldset>
                    <legend>Group 1</legend>
                    <input type="radio" name="group1" id="g1-opt1">
                    <input type="radio" name="group1" id="g1-opt2">
                </fieldset>
                <fieldset>
                    <legend>Group 2</legend>
                    <input type="radio" name="group2" id="g2-opt1">
                    <input type="radio" name="group2" id="g2-opt2">
                </fieldset>
            HTML;

        expect(fn () => $this->assertRadioButtonsInsideFieldset($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when no radio buttons exist', function () {
        $html = <<<'HTML'
                <form>
                    <input type="text" name="username">
                    <input type="checkbox" name="agree">
                </form>
            HTML;

        expect(fn () => $this->assertRadioButtonsInsideFieldset($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when radio is deeply nested in fieldset', function () {
        $html = <<<'HTML'
                <fieldset>
                    <legend>Options</legend>
                    <div>
                        <p>
                            <label>
                                <input type="radio" name="options" id="option1">
                                Option 1
                            </label>
                        </p>
                    </div>
                </fieldset>
            HTML;

        expect(fn () => $this->assertRadioButtonsInsideFieldset($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when radio button is outside fieldset', function () {
        $html = <<<'HTML'
                <p>
                    <label for="option-1">Option N<sup>o</sup>1</label>
                    <input type="radio" id="option-1" name="options">
                </p>
            HTML;

        $this->assertRadioButtonsInsideFieldset($html);
    })->throws(AssertionFailedError::class, 'id="option-1"');

    test('fails when radio button is in form but not in fieldset', function () {
        $html = <<<'HTML'
                <form action="/">
                    <p>
                        <label for="option-1">Option N<sup>o</sup>1</label>
                        <input type="radio" id="option-1" name="options">
                    </p>

                    <br>

                    <fieldset>
                        <legend>Trying within a fieldset, shouldn't match</legend>
                        <p>
                            <label for="option-2">Option N<sup>o</sup>2</label>
                            <input type="radio" id="option-2" name="options">
                        </p>
                    </fieldset>
                </form>
            HTML;

        $this->assertRadioButtonsInsideFieldset($html);
    })->throws(AssertionFailedError::class, 'id="option-1"');

    test('fails when multiple radios are outside fieldset', function () {
        $html = <<<'HTML'
                <input type="radio" name="options" id="yes">
                <input type="radio" name="options" id="no">
            HTML;

        $this->assertRadioButtonsInsideFieldset($html);
    })->throws(AssertionFailedError::class, 'outside <fieldset>');

    test('fails when radio without id is outside fieldset', function () {
        $html = <<<'HTML'
                <input type="radio" name="options">
            HTML;

        $this->assertRadioButtonsInsideFieldset($html);
    })->throws(AssertionFailedError::class, 'name="options"');

    test('fails when radio without id or name is outside fieldset', function () {
        $html = <<<'HTML'
                <input type="radio">
            HTML;

        $this->assertRadioButtonsInsideFieldset($html);
    })->throws(AssertionFailedError::class, 'not inside a <fieldset>');

    test('fails with mixed radios inside and outside fieldset', function () {
        $html = <<<'HTML'
                <input type="radio" id="outside" name="test">
                <fieldset>
                    <legend>Inside</legend>
                    <input type="radio" id="inside" name="test">
                </fieldset>
            HTML;

        $this->assertRadioButtonsInsideFieldset($html);
    })->throws(AssertionFailedError::class, 'id="outside"');

    test('fails with error message about accessibility recommendation', function () {
        $html = <<<'HTML'
                <input type="radio" name="option" id="opt1">
            HTML;

        $this->assertRadioButtonsInsideFieldset($html);
    })->throws(AssertionFailedError::class, 'strongly recommended for accessibility');
});

describe('assertSliderRoleHasRequiredAttributes', function () {
    test('passes when slider has all required attributes', function () {
        $html = <<<'HTML'
                <div role="slider" aria-valuemin="0" aria-valuemax="100" aria-valuenow="50"></div>
            HTML;

        expect(fn () => $this->assertSliderRoleHasRequiredAttributes($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when input range has slider role with all attributes', function () {
        $html = <<<'HTML'
                <input type="range" role="slider" aria-valuemin="0" aria-valuemax="100" aria-valuenow="50">
            HTML;

        expect(fn () => $this->assertSliderRoleHasRequiredAttributes($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when slider has all required attributes plus valuetext', function () {
        $html = <<<'HTML'
                <div role="slider" aria-valuemin="0" aria-valuemax="100" aria-valuenow="50" aria-valuetext="50 percent"></div>
            HTML;

        expect(fn () => $this->assertSliderRoleHasRequiredAttributes($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when no slider elements exist', function () {
        $html = <<<'HTML'
                <input type="range">
                <input type="text">
            HTML;

        expect(fn () => $this->assertSliderRoleHasRequiredAttributes($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when multiple sliders have all required attributes', function () {
        $html = <<<'HTML'
                <div role="slider" aria-valuemin="0" aria-valuemax="100" aria-valuenow="25"></div>
                <div role="slider" aria-valuemin="0" aria-valuemax="10" aria-valuenow="5"></div>
            HTML;

        expect(fn () => $this->assertSliderRoleHasRequiredAttributes($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when slider is missing all required attributes', function () {
        $html = <<<'HTML'
                <label for="slider">Slider</label>
                <input id="slider" role="slider" type="range">
            HTML;

        $this->assertSliderRoleHasRequiredAttributes($html);
    })->throws(AssertionFailedError::class, 'id="slider"');

    test('fails when slider is missing aria-valuemin', function () {
        $html = <<<'HTML'
                <div role="slider" aria-valuemax="100" aria-valuenow="50"></div>
            HTML;

        $this->assertSliderRoleHasRequiredAttributes($html);
    })->throws(AssertionFailedError::class, '[aria-valuemin]');

    test('fails when slider is missing aria-valuemax', function () {
        $html = <<<'HTML'
                <div role="slider" aria-valuemin="0" aria-valuenow="50"></div>
            HTML;

        $this->assertSliderRoleHasRequiredAttributes($html);
    })->throws(AssertionFailedError::class, '[aria-valuemax]');

    test('fails when slider is missing aria-valuenow', function () {
        $html = <<<'HTML'
                <div role="slider" aria-valuemin="0" aria-valuemax="100"></div>
            HTML;

        $this->assertSliderRoleHasRequiredAttributes($html);
    })->throws(AssertionFailedError::class, '[aria-valuenow]');

    test('fails when slider is missing multiple attributes', function () {
        $html = <<<'HTML'
                <div role="slider" aria-valuenow="50"></div>
            HTML;

        $this->assertSliderRoleHasRequiredAttributes($html);
    })->throws(AssertionFailedError::class, 'missing required attributes');

    test('fails when slider without id is missing attributes', function () {
        $html = <<<'HTML'
                <div role="slider"></div>
            HTML;

        $this->assertSliderRoleHasRequiredAttributes($html);
    })->throws(AssertionFailedError::class, 'missing required attributes');

    test('fails when multiple sliders are missing attributes', function () {
        $html = <<<'HTML'
                <div role="slider" id="slider1"></div>
                <div role="slider" id="slider2" aria-valuemin="0"></div>
            HTML;

        $this->assertSliderRoleHasRequiredAttributes($html);
    })->throws(AssertionFailedError::class, 'missing required ARIA attributes');

    test('fails with mixed valid and invalid sliders', function () {
        $html = <<<'HTML'
                <div role="slider" aria-valuemin="0" aria-valuemax="100" aria-valuenow="50"></div>
                <div role="slider" id="invalid"></div>
            HTML;

        $this->assertSliderRoleHasRequiredAttributes($html);
    })->throws(AssertionFailedError::class, 'id="invalid"');

    test('fails with error message listing missing attributes', function () {
        $html = <<<'HTML'
                <div role="slider" aria-valuemin="0"></div>
            HTML;

        $this->assertSliderRoleHasRequiredAttributes($html);
    })->throws(AssertionFailedError::class, '[aria-valuemax], [aria-valuenow]');
});

describe('assertSpinbuttonRoleHasRequiredAttributes', function () {
    test('passes when spinbutton has all required attributes', function () {
        $html = <<<'HTML'
                <div role="spinbutton" aria-valuemin="0" aria-valuemax="100" aria-valuenow="50"></div>
            HTML;

        expect(fn () => $this->assertSpinbuttonRoleHasRequiredAttributes($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when input number has spinbutton role with all attributes', function () {
        $html = <<<'HTML'
                <input type="number" role="spinbutton" aria-valuemin="0" aria-valuemax="100" aria-valuenow="50">
            HTML;

        expect(fn () => $this->assertSpinbuttonRoleHasRequiredAttributes($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when spinbutton has all required attributes plus valuetext', function () {
        $html = <<<'HTML'
                <div role="spinbutton" aria-valuemin="0" aria-valuemax="100" aria-valuenow="50" aria-valuetext="50 items"></div>
            HTML;

        expect(fn () => $this->assertSpinbuttonRoleHasRequiredAttributes($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when no spinbutton elements exist', function () {
        $html = <<<'HTML'
                <input type="number">
                <input type="text">
            HTML;

        expect(fn () => $this->assertSpinbuttonRoleHasRequiredAttributes($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when multiple spinbuttons have all required attributes', function () {
        $html = <<<'HTML'
                <div role="spinbutton" aria-valuemin="0" aria-valuemax="100" aria-valuenow="25"></div>
                <div role="spinbutton" aria-valuemin="1" aria-valuemax="10" aria-valuenow="5"></div>
            HTML;

        expect(fn () => $this->assertSpinbuttonRoleHasRequiredAttributes($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when spinbutton is missing all required attributes', function () {
        $html = <<<'HTML'
                <label for="spinbutton">Spinbutton</label>
                <input id="spinbutton" role="spinbutton" type="range">
            HTML;

        $this->assertSpinbuttonRoleHasRequiredAttributes($html);
    })->throws(AssertionFailedError::class, 'id="spinbutton"');

    test('fails when spinbutton is missing aria-valuemin', function () {
        $html = <<<'HTML'
                <div role="spinbutton" aria-valuemax="100" aria-valuenow="50"></div>
            HTML;

        $this->assertSpinbuttonRoleHasRequiredAttributes($html);
    })->throws(AssertionFailedError::class, '[aria-valuemin]');

    test('fails when spinbutton is missing aria-valuemax', function () {
        $html = <<<'HTML'
                <div role="spinbutton" aria-valuemin="0" aria-valuenow="50"></div>
            HTML;

        $this->assertSpinbuttonRoleHasRequiredAttributes($html);
    })->throws(AssertionFailedError::class, '[aria-valuemax]');

    test('fails when spinbutton is missing aria-valuenow', function () {
        $html = <<<'HTML'
                <div role="spinbutton" aria-valuemin="0" aria-valuemax="100"></div>
            HTML;

        $this->assertSpinbuttonRoleHasRequiredAttributes($html);
    })->throws(AssertionFailedError::class, '[aria-valuenow]');

    test('fails when spinbutton is missing multiple attributes', function () {
        $html = <<<'HTML'
                <div role="spinbutton" aria-valuenow="50"></div>
            HTML;

        $this->assertSpinbuttonRoleHasRequiredAttributes($html);
    })->throws(AssertionFailedError::class, 'missing required attributes');

    test('fails when spinbutton without id is missing attributes', function () {
        $html = <<<'HTML'
                <div role="spinbutton"></div>
            HTML;

        $this->assertSpinbuttonRoleHasRequiredAttributes($html);
    })->throws(AssertionFailedError::class, 'missing required attributes');

    test('fails when multiple spinbuttons are missing attributes', function () {
        $html = <<<'HTML'
                <div role="spinbutton" id="spinbutton1"></div>
                <div role="spinbutton" id="spinbutton2" aria-valuemin="0"></div>
            HTML;

        $this->assertSpinbuttonRoleHasRequiredAttributes($html);
    })->throws(AssertionFailedError::class, 'missing required ARIA attributes');

    test('fails with mixed valid and invalid spinbuttons', function () {
        $html = <<<'HTML'
                <div role="spinbutton" aria-valuemin="0" aria-valuemax="100" aria-valuenow="50"></div>
                <div role="spinbutton" id="invalid"></div>
            HTML;

        $this->assertSpinbuttonRoleHasRequiredAttributes($html);
    })->throws(AssertionFailedError::class, 'id="invalid"');

    test('fails with error message listing missing attributes', function () {
        $html = <<<'HTML'
                <div role="spinbutton" aria-valuemin="0"></div>
            HTML;

        $this->assertSpinbuttonRoleHasRequiredAttributes($html);
    })->throws(AssertionFailedError::class, '[aria-valuemax], [aria-valuenow]');
});

describe('assertCheckboxRoleHasAriaChecked', function () {
    test('passes when checkbox role has aria-checked true', function () {
        $html = <<<'HTML'
                <div role="checkbox" aria-checked="true">Accept terms</div>
            HTML;

        expect(fn () => $this->assertCheckboxRoleHasAriaChecked($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when checkbox role has aria-checked false', function () {
        $html = <<<'HTML'
                <div role="checkbox" aria-checked="false">Subscribe to newsletter</div>
            HTML;

        expect(fn () => $this->assertCheckboxRoleHasAriaChecked($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when checkbox role has aria-checked mixed', function () {
        $html = <<<'HTML'
                <div role="checkbox" aria-checked="mixed">Select all</div>
            HTML;

        expect(fn () => $this->assertCheckboxRoleHasAriaChecked($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when img has checkbox role with aria-checked', function () {
        $html = <<<'HTML'
                <img src="/checkbox.png" alt="Checkbox" role="checkbox" aria-checked="false" width="36" height="36">
            HTML;

        expect(fn () => $this->assertCheckboxRoleHasAriaChecked($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when no checkbox role elements exist', function () {
        $html = <<<'HTML'
                <input type="checkbox">
                <input type="text">
            HTML;

        expect(fn () => $this->assertCheckboxRoleHasAriaChecked($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when multiple checkboxes have aria-checked', function () {
        $html = <<<'HTML'
                <div role="checkbox" aria-checked="true">Option 1</div>
                <div role="checkbox" aria-checked="false">Option 2</div>
                <div role="checkbox" aria-checked="mixed">Select all</div>
            HTML;

        expect(fn () => $this->assertCheckboxRoleHasAriaChecked($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when checkbox role is missing aria-checked', function () {
        $html = <<<'HTML'
                <img src="/static/ffoodd.png" alt="Checkbox" role="checkbox" width="36" height="36">
            HTML;

        $this->assertCheckboxRoleHasAriaChecked($html);
    })->throws(AssertionFailedError::class, 'missing required attribute: [aria-checked]');

    test('fails when div with checkbox role is missing aria-checked', function () {
        $html = <<<'HTML'
                <div role="checkbox">Accept terms</div>
            HTML;

        $this->assertCheckboxRoleHasAriaChecked($html);
    })->throws(AssertionFailedError::class, 'role="checkbox"');

    test('fails when checkbox role with id is missing aria-checked', function () {
        $html = <<<'HTML'
                <div id="terms-checkbox" role="checkbox">Accept terms</div>
            HTML;

        $this->assertCheckboxRoleHasAriaChecked($html);
    })->throws(AssertionFailedError::class, 'id="terms-checkbox"');

    test('fails when checkbox role without id is missing aria-checked', function () {
        $html = <<<'HTML'
                <span role="checkbox">Subscribe</span>
            HTML;

        $this->assertCheckboxRoleHasAriaChecked($html);
    })->throws(AssertionFailedError::class, 'missing required attribute');

    test('fails when multiple checkboxes are missing aria-checked', function () {
        $html = <<<'HTML'
                <div role="checkbox" id="checkbox1">Option 1</div>
                <div role="checkbox" id="checkbox2">Option 2</div>
            HTML;

        $this->assertCheckboxRoleHasAriaChecked($html);
    })->throws(AssertionFailedError::class, 'missing the required [aria-checked] attribute');

    test('fails with mixed valid and invalid checkboxes', function () {
        $html = <<<'HTML'
                <div role="checkbox" aria-checked="true">Valid</div>
                <div role="checkbox" id="invalid">Invalid</div>
            HTML;

        $this->assertCheckboxRoleHasAriaChecked($html);
    })->throws(AssertionFailedError::class, 'id="invalid"');

    test('fails with error message showing element details', function () {
        $html = <<<'HTML'
                <button role="checkbox" id="custom-checkbox">Custom checkbox</button>
            HTML;

        $this->assertCheckboxRoleHasAriaChecked($html);
    })->throws(AssertionFailedError::class, '<BUTTON id="custom-checkbox" role="checkbox">');
});

describe('assertComboboxRoleHasAriaExpanded', function () {
    test('passes when combobox role has aria-expanded true', function () {
        $html = <<<'HTML'
                <input type="text" role="combobox" aria-expanded="true" aria-label="Search">
            HTML;

        expect(fn () => $this->assertComboboxRoleHasAriaExpanded($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when combobox role has aria-expanded false', function () {
        $html = <<<'HTML'
                <input type="text" role="combobox" aria-expanded="false" aria-label="Select option">
            HTML;

        expect(fn () => $this->assertComboboxRoleHasAriaExpanded($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when div has combobox role with aria-expanded', function () {
        $html = <<<'HTML'
                <div role="combobox" aria-expanded="false" aria-label="Country selector">Country</div>
            HTML;

        expect(fn () => $this->assertComboboxRoleHasAriaExpanded($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when no combobox role elements exist', function () {
        $html = <<<'HTML'
                <input type="text">
                <select><option>Item</option></select>
            HTML;

        expect(fn () => $this->assertComboboxRoleHasAriaExpanded($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when multiple comboboxes have aria-expanded', function () {
        $html = <<<'HTML'
                <input type="text" role="combobox" aria-expanded="false" aria-label="Search">
                <div role="combobox" aria-expanded="true" aria-label="Options">Options</div>
            HTML;

        expect(fn () => $this->assertComboboxRoleHasAriaExpanded($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when combobox role is missing aria-expanded', function () {
        $html = <<<'HTML'
                <input type="text" aria-label="Combobox" role="combobox" id="combobox">
            HTML;

        $this->assertComboboxRoleHasAriaExpanded($html);
    })->throws(AssertionFailedError::class, 'missing required attribute: [aria-expanded]');

    test('fails when input with combobox role is missing aria-expanded', function () {
        $html = <<<'HTML'
                <input type="text" role="combobox" aria-label="Search">
            HTML;

        $this->assertComboboxRoleHasAriaExpanded($html);
    })->throws(AssertionFailedError::class, 'role="combobox"');

    test('fails when combobox role with id is missing aria-expanded', function () {
        $html = <<<'HTML'
                <div id="country-combobox" role="combobox" aria-label="Country">Country</div>
            HTML;

        $this->assertComboboxRoleHasAriaExpanded($html);
    })->throws(AssertionFailedError::class, 'id="country-combobox"');

    test('fails when combobox role without id is missing aria-expanded', function () {
        $html = <<<'HTML'
                <div role="combobox" aria-label="Filter">Filter</div>
            HTML;

        $this->assertComboboxRoleHasAriaExpanded($html);
    })->throws(AssertionFailedError::class, 'missing required attribute');

    test('fails when multiple comboboxes are missing aria-expanded', function () {
        $html = <<<'HTML'
                <input type="text" role="combobox" id="combobox1" aria-label="Search">
                <div role="combobox" id="combobox2" aria-label="Options">Options</div>
            HTML;

        $this->assertComboboxRoleHasAriaExpanded($html);
    })->throws(AssertionFailedError::class, 'missing the required [aria-expanded] attribute');

    test('fails with mixed valid and invalid comboboxes', function () {
        $html = <<<'HTML'
                <input type="text" role="combobox" aria-expanded="false" aria-label="Valid">
                <div role="combobox" id="invalid" aria-label="Invalid">Invalid</div>
            HTML;

        $this->assertComboboxRoleHasAriaExpanded($html);
    })->throws(AssertionFailedError::class, 'id="invalid"');

    test('fails with error message showing element details', function () {
        $html = <<<'HTML'
                <button role="combobox" id="custom-combobox" aria-label="Custom">Custom combobox</button>
            HTML;

        $this->assertComboboxRoleHasAriaExpanded($html);
    })->throws(AssertionFailedError::class, '<BUTTON id="custom-combobox" role="combobox">');
});

describe('assertScrollbarRoleHasRequiredAttributes', function () {
    test('passes when scrollbar has all required attributes', function () {
        $html = <<<'HTML'
                <div role="scrollbar" aria-controls="content" aria-valuemin="0" aria-valuemax="100" aria-valuenow="50" aria-orientation="vertical"></div>
            HTML;

        expect(fn () => $this->assertScrollbarRoleHasRequiredAttributes($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when scrollbar has horizontal orientation', function () {
        $html = <<<'HTML'
                <div role="scrollbar" aria-controls="content" aria-valuemin="0" aria-valuemax="100" aria-valuenow="25" aria-orientation="horizontal"></div>
            HTML;

        expect(fn () => $this->assertScrollbarRoleHasRequiredAttributes($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when scrollbar has all required attributes with id', function () {
        $html = <<<'HTML'
                <div id="custom-scrollbar" role="scrollbar" aria-controls="panel" aria-valuemin="0" aria-valuemax="200" aria-valuenow="100" aria-orientation="vertical"></div>
            HTML;

        expect(fn () => $this->assertScrollbarRoleHasRequiredAttributes($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when no scrollbar elements exist', function () {
        $html = <<<'HTML'
                <div>Content</div>
                <input type="range">
            HTML;

        expect(fn () => $this->assertScrollbarRoleHasRequiredAttributes($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when multiple scrollbars have all required attributes', function () {
        $html = <<<'HTML'
                <div role="scrollbar" aria-controls="content1" aria-valuemin="0" aria-valuemax="100" aria-valuenow="50" aria-orientation="vertical"></div>
                <div role="scrollbar" aria-controls="content2" aria-valuemin="0" aria-valuemax="100" aria-valuenow="75" aria-orientation="horizontal"></div>
            HTML;

        expect(fn () => $this->assertScrollbarRoleHasRequiredAttributes($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when scrollbar is missing all required attributes', function () {
        $html = <<<'HTML'
                <div role="scrollbar">↓</div>
            HTML;

        $this->assertScrollbarRoleHasRequiredAttributes($html);
    })->throws(AssertionFailedError::class, 'missing required attributes');

    test('fails when scrollbar is missing aria-controls', function () {
        $html = <<<'HTML'
                <div role="scrollbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="50" aria-orientation="vertical"></div>
            HTML;

        $this->assertScrollbarRoleHasRequiredAttributes($html);
    })->throws(AssertionFailedError::class, '[aria-controls]');

    test('fails when scrollbar is missing aria-valuemin', function () {
        $html = <<<'HTML'
                <div role="scrollbar" aria-controls="content" aria-valuemax="100" aria-valuenow="50" aria-orientation="vertical"></div>
            HTML;

        $this->assertScrollbarRoleHasRequiredAttributes($html);
    })->throws(AssertionFailedError::class, '[aria-valuemin]');

    test('fails when scrollbar is missing aria-valuemax', function () {
        $html = <<<'HTML'
                <div role="scrollbar" aria-controls="content" aria-valuemin="0" aria-valuenow="50" aria-orientation="vertical"></div>
            HTML;

        $this->assertScrollbarRoleHasRequiredAttributes($html);
    })->throws(AssertionFailedError::class, '[aria-valuemax]');

    test('fails when scrollbar is missing aria-valuenow', function () {
        $html = <<<'HTML'
                <div role="scrollbar" aria-controls="content" aria-valuemin="0" aria-valuemax="100" aria-orientation="vertical"></div>
            HTML;

        $this->assertScrollbarRoleHasRequiredAttributes($html);
    })->throws(AssertionFailedError::class, '[aria-valuenow]');

    test('fails when scrollbar is missing aria-orientation', function () {
        $html = <<<'HTML'
                <div role="scrollbar" aria-controls="content" aria-valuemin="0" aria-valuemax="100" aria-valuenow="50"></div>
            HTML;

        $this->assertScrollbarRoleHasRequiredAttributes($html);
    })->throws(AssertionFailedError::class, '[aria-orientation]');

    test('fails when scrollbar is missing multiple attributes', function () {
        $html = <<<'HTML'
                <div role="scrollbar" aria-valuenow="50"></div>
            HTML;

        $this->assertScrollbarRoleHasRequiredAttributes($html);
    })->throws(AssertionFailedError::class, 'missing required attributes');

    test('fails when scrollbar without id is missing attributes', function () {
        $html = <<<'HTML'
                <div role="scrollbar"></div>
            HTML;

        $this->assertScrollbarRoleHasRequiredAttributes($html);
    })->throws(AssertionFailedError::class, 'missing required attributes');

    test('fails when multiple scrollbars are missing attributes', function () {
        $html = <<<'HTML'
                <div role="scrollbar" id="scrollbar1"></div>
                <div role="scrollbar" id="scrollbar2" aria-controls="content"></div>
            HTML;

        $this->assertScrollbarRoleHasRequiredAttributes($html);
    })->throws(AssertionFailedError::class, 'missing required ARIA attributes');

    test('fails with mixed valid and invalid scrollbars', function () {
        $html = <<<'HTML'
                <div role="scrollbar" aria-controls="content" aria-valuemin="0" aria-valuemax="100" aria-valuenow="50" aria-orientation="vertical"></div>
                <div role="scrollbar" id="invalid"></div>
            HTML;

        $this->assertScrollbarRoleHasRequiredAttributes($html);
    })->throws(AssertionFailedError::class, 'id="invalid"');

    test('fails with error message listing missing attributes', function () {
        $html = <<<'HTML'
                <div role="scrollbar" aria-controls="content"></div>
            HTML;

        $this->assertScrollbarRoleHasRequiredAttributes($html);
    })->throws(AssertionFailedError::class, '[aria-valuemin], [aria-valuemax], [aria-valuenow], [aria-orientation]');
});

describe('assertNoNestedInteractiveElements', function () {
    test('passes when link contains only text', function () {
        $html = <<<'HTML'
                <a href="https://example.com">Click here</a>
            HTML;

        expect(fn () => $this->assertNoNestedInteractiveElements($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when button contains only text', function () {
        $html = <<<'HTML'
                <button type="button">Click me</button>
            HTML;

        expect(fn () => $this->assertNoNestedInteractiveElements($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when separate interactive elements exist', function () {
        $html = <<<'HTML'
                <a href="https://example.com">Link</a>
                <button type="button">Button</button>
                <input type="text">
            HTML;

        expect(fn () => $this->assertNoNestedInteractiveElements($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when link contains non-interactive elements', function () {
        $html = <<<'HTML'
                <a href="https://example.com"><span>Click</span> <strong>here</strong></a>
            HTML;

        expect(fn () => $this->assertNoNestedInteractiveElements($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when button contains hidden input', function () {
        $html = <<<'HTML'
                <button type="button"><input type="hidden" name="id" value="123">Submit</button>
            HTML;

        expect(fn () => $this->assertNoNestedInteractiveElements($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when button is nested inside link', function () {
        $html = <<<'HTML'
                <a href="https://www.ffoodd.fr">
                    <button type="button">Oh wait, what should I trigger?</button>
                </a>
            HTML;

        $this->assertNoNestedInteractiveElements($html);
    })->throws(AssertionFailedError::class, 'nested inside');

    test('fails when link is nested inside button', function () {
        $html = <<<'HTML'
                <button type="button">
                    Click <a href="https://example.com">here</a>
                </button>
            HTML;

        $this->assertNoNestedInteractiveElements($html);
    })->throws(AssertionFailedError::class, '<A> nested inside <BUTTON>');

    test('fails when input is nested inside link', function () {
        $html = <<<'HTML'
                <a href="https://example.com">
                    <input type="text" placeholder="Search">
                </a>
            HTML;

        $this->assertNoNestedInteractiveElements($html);
    })->throws(AssertionFailedError::class, '<INPUT> nested inside <A>');

    test('fails when select is nested inside button', function () {
        $html = <<<'HTML'
                <button type="button">
                    Choose: <select><option>Option</option></select>
                </button>
            HTML;

        $this->assertNoNestedInteractiveElements($html);
    })->throws(AssertionFailedError::class, '<SELECT> nested inside <BUTTON>');

    test('fails when textarea is nested inside link', function () {
        $html = <<<'HTML'
                <a href="https://example.com">
                    <textarea>Content</textarea>
                </a>
            HTML;

        $this->assertNoNestedInteractiveElements($html);
    })->throws(AssertionFailedError::class, '<TEXTAREA> nested inside <A>');

    test('fails when label is nested inside label', function () {
        $html = <<<'HTML'
                <label>
                    Outer label
                    <label>Inner label</label>
                </label>
            HTML;

        $this->assertNoNestedInteractiveElements($html);
    })->throws(AssertionFailedError::class, '<LABEL> nested inside <LABEL>');

    test('fails when iframe is nested inside link', function () {
        $html = <<<'HTML'
                <a href="https://example.com">
                    <iframe src="https://example.com" title="Frame"></iframe>
                </a>
            HTML;

        $this->assertNoNestedInteractiveElements($html);
    })->throws(AssertionFailedError::class, '<IFRAME> nested inside <A>');

    test('fails when audio with controls is nested inside button', function () {
        $html = <<<'HTML'
                <button type="button">
                    <audio controls src="audio.mp3"></audio>
                </button>
            HTML;

        $this->assertNoNestedInteractiveElements($html);
    })->throws(AssertionFailedError::class, '<AUDIO> nested inside <BUTTON>');

    test('fails when video with controls is nested inside link', function () {
        $html = <<<'HTML'
                <a href="https://example.com">
                    <video controls src="video.mp4"></video>
                </a>
            HTML;

        $this->assertNoNestedInteractiveElements($html);
    })->throws(AssertionFailedError::class, '<VIDEO> nested inside <A>');

    test('fails when multiple nested elements exist', function () {
        $html = <<<'HTML'
                <a href="https://example.com">
                    <button type="button">Button 1</button>
                </a>
                <button type="button">
                    <a href="https://other.com">Link</a>
                </button>
            HTML;

        $this->assertNoNestedInteractiveElements($html);
    })->throws(AssertionFailedError::class, 'interactive elements nested inside');

    test('fails with element id in error message', function () {
        $html = <<<'HTML'
                <a href="https://example.com">
                    <button id="nested-button" type="button">Nested</button>
                </a>
            HTML;

        $this->assertNoNestedInteractiveElements($html);
    })->throws(AssertionFailedError::class, 'id="nested-button"');
});
