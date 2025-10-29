<?php

declare(strict_types=1);

use PHPUnit\Framework\AssertionFailedError;
use Tests\Concerns\Accessibility\ChecksAccessibilityAdvice;

uses(ChecksAccessibilityAdvice::class);

describe('assertNoInsecureUrls', function () {
    test('passes when link uses HTTPS', function () {
        $html = <<<'HTML'
                <a href="https://www.example.com/">Secure link</a>
            HTML;

        expect(fn () => $this->assertNoInsecureUrls($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when image uses HTTPS', function () {
        $html = <<<'HTML'
                <img src="https://www.example.com/image.jpg" alt="Image">
            HTML;

        expect(fn () => $this->assertNoInsecureUrls($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when script uses HTTPS', function () {
        $html = <<<'HTML'
                <script src="https://cdn.example.com/script.js"></script>
            HTML;

        expect(fn () => $this->assertNoInsecureUrls($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when multiple elements use HTTPS', function () {
        $html = <<<'HTML'
                <a href="https://www.example.com/">Link</a>
                <img src="https://www.example.com/image.jpg" alt="Image">
                <script src="https://cdn.example.com/script.js"></script>
            HTML;

        expect(fn () => $this->assertNoInsecureUrls($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when using relative URLs', function () {
        $html = <<<'HTML'
                <a href="/about">About</a>
                <img src="/images/logo.png" alt="Logo">
            HTML;

        expect(fn () => $this->assertNoInsecureUrls($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when using protocol-relative URLs', function () {
        $html = <<<'HTML'
                <a href="//www.example.com/">Link</a>
                <img src="//www.example.com/image.jpg" alt="Image">
            HTML;

        expect(fn () => $this->assertNoInsecureUrls($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when using mailto links', function () {
        $html = <<<'HTML'
                <a href="mailto:test@example.com">Email</a>
            HTML;

        expect(fn () => $this->assertNoInsecureUrls($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when using tel links', function () {
        $html = <<<'HTML'
                <a href="tel:+1234567890">Call</a>
            HTML;

        expect(fn () => $this->assertNoInsecureUrls($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when no elements have URLs', function () {
        $html = <<<'HTML'
                <div>Just a div</div>
                <p>Paragraph</p>
            HTML;

        expect(fn () => $this->assertNoInsecureUrls($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when link has no href', function () {
        $html = <<<'HTML'
                <a>Link without href</a>
            HTML;

        expect(fn () => $this->assertNoInsecureUrls($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when link uses HTTP', function () {
        $html = <<<'HTML'
                <a href="http://www.ffoodd.fr/">My target's protocol isn't secured</a>
            HTML;

        $this->assertNoInsecureUrls($html);
    })->throws(AssertionFailedError::class, 'uses insecure HTTP protocol');

    test('fails when image uses HTTP', function () {
        $html = <<<'HTML'
                <img src="http://www.example.com/image.jpg" alt="Image">
            HTML;

        $this->assertNoInsecureUrls($html);
    })->throws(AssertionFailedError::class, 'uses insecure HTTP protocol');

    test('fails when script uses HTTP', function () {
        $html = <<<'HTML'
                <script src="http://cdn.example.com/script.js"></script>
            HTML;

        $this->assertNoInsecureUrls($html);
    })->throws(AssertionFailedError::class, 'uses insecure HTTP protocol');

    test('fails when stylesheet uses HTTP', function () {
        $html = <<<'HTML'
                <link rel="stylesheet" href="http://cdn.example.com/style.css">
            HTML;

        $this->assertNoInsecureUrls($html);
    })->throws(AssertionFailedError::class, 'uses insecure HTTP protocol');

    test('fails when iframe uses HTTP', function () {
        $html = <<<'HTML'
                <iframe src="http://www.example.com/embed"></iframe>
            HTML;

        $this->assertNoInsecureUrls($html);
    })->throws(AssertionFailedError::class, 'uses insecure HTTP protocol');

    test('fails when link with id uses HTTP', function () {
        $html = <<<'HTML'
                <a id="external-link" href="http://www.example.com/">Link</a>
            HTML;

        $this->assertNoInsecureUrls($html);
    })->throws(AssertionFailedError::class, 'id="external-link"');

    test('fails when image with id uses HTTP', function () {
        $html = <<<'HTML'
                <img id="logo" src="http://www.example.com/logo.png" alt="Logo">
            HTML;

        $this->assertNoInsecureUrls($html);
    })->throws(AssertionFailedError::class, 'id="logo"');

    test('fails when element with name uses HTTP', function () {
        $html = <<<'HTML'
                <a name="anchor" href="http://www.example.com/">Link</a>
            HTML;

        $this->assertNoInsecureUrls($html);
    })->throws(AssertionFailedError::class, 'name="anchor"');

    test('fails when multiple elements use HTTP', function () {
        $html = <<<'HTML'
                <a href="http://www.example.com/">Link</a>
                <img src="http://www.example.com/image.jpg" alt="Image">
            HTML;

        $this->assertNoInsecureUrls($html);
    })->throws(AssertionFailedError::class, 'insecure HTTP URLs');

    test('fails when mixing HTTPS and HTTP', function () {
        $html = <<<'HTML'
                <a href="https://www.secure.com/">Secure</a>
                <a href="http://www.insecure.com/">Insecure</a>
            HTML;

        $this->assertNoInsecureUrls($html);
    })->throws(AssertionFailedError::class, 'uses insecure HTTP protocol');

    test('fails with URL in error message', function () {
        $html = <<<'HTML'
                <a href="http://www.example.com/page">Link</a>
            HTML;

        $this->assertNoInsecureUrls($html);
    })->throws(AssertionFailedError::class, 'http://www.example.com/page');

    test('fails showing href attribute for links', function () {
        $html = <<<'HTML'
                <a href="http://www.example.com/">Link</a>
            HTML;

        $this->assertNoInsecureUrls($html);
    })->throws(AssertionFailedError::class, 'href="http://www.example.com/"');

    test('fails showing src attribute for images', function () {
        $html = <<<'HTML'
                <img src="http://www.example.com/image.jpg" alt="Image">
            HTML;

        $this->assertNoInsecureUrls($html);
    })->throws(AssertionFailedError::class, 'src="http://www.example.com/image.jpg"');
});

describe('assertTableHeaderScopeIsValid', function () {
    test('passes when th has scope col', function () {
        $html = <<<'HTML'
                <table>
                    <thead>
                        <tr>
                            <th scope="col">Header</th>
                        </tr>
                    </thead>
                </table>
            HTML;

        expect(fn () => $this->assertTableHeaderScopeIsValid($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when th has scope row', function () {
        $html = <<<'HTML'
                <table>
                    <tbody>
                        <tr>
                            <th scope="row">Header</th>
                            <td>Data</td>
                        </tr>
                    </tbody>
                </table>
            HTML;

        expect(fn () => $this->assertTableHeaderScopeIsValid($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when multiple th elements have valid scope', function () {
        $html = <<<'HTML'
                <table>
                    <thead>
                        <tr>
                            <th scope="col">First column</th>
                            <th scope="col">Second column</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th scope="row">First row</th>
                            <td>Data</td>
                        </tr>
                    </tbody>
                </table>
            HTML;

        expect(fn () => $this->assertTableHeaderScopeIsValid($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when th has no scope attribute', function () {
        $html = <<<'HTML'
                <table>
                    <thead>
                        <tr>
                            <th>Header</th>
                        </tr>
                    </thead>
                </table>
            HTML;

        expect(fn () => $this->assertTableHeaderScopeIsValid($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when there are no th elements', function () {
        $html = <<<'HTML'
                <table>
                    <tr>
                        <td>Cell 1</td>
                        <td>Cell 2</td>
                    </tr>
                </table>
            HTML;

        expect(fn () => $this->assertTableHeaderScopeIsValid($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when scope is uppercase COL', function () {
        $html = <<<'HTML'
                <table>
                    <thead>
                        <tr>
                            <th scope="COL">Header</th>
                        </tr>
                    </thead>
                </table>
            HTML;

        expect(fn () => $this->assertTableHeaderScopeIsValid($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when scope is uppercase ROW', function () {
        $html = <<<'HTML'
                <table>
                    <tbody>
                        <tr>
                            <th scope="ROW">Header</th>
                            <td>Data</td>
                        </tr>
                    </tbody>
                </table>
            HTML;

        expect(fn () => $this->assertTableHeaderScopeIsValid($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when scope has extra whitespace', function () {
        $html = <<<'HTML'
                <table>
                    <thead>
                        <tr>
                            <th scope=" col ">Header</th>
                        </tr>
                    </thead>
                </table>
            HTML;

        expect(fn () => $this->assertTableHeaderScopeIsValid($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when th has invalid scope value', function () {
        $html = <<<'HTML'
                <table>
                    <thead>
                        <tr>
                            <th scope="column">Header</th>
                        </tr>
                    </thead>
                </table>
            HTML;

        $this->assertTableHeaderScopeIsValid($html);
    })->throws(AssertionFailedError::class, 'must be "col" or "row"');

    test('fails when th has scope colgroup', function () {
        $html = <<<'HTML'
                <table>
                    <thead>
                        <tr>
                            <th scope="colgroup">Header</th>
                        </tr>
                    </thead>
                </table>
            HTML;

        $this->assertTableHeaderScopeIsValid($html);
    })->throws(AssertionFailedError::class, 'scope="colgroup"');

    test('fails when th has scope rowgroup', function () {
        $html = <<<'HTML'
                <table>
                    <tbody>
                        <tr>
                            <th scope="rowgroup">Header</th>
                        </tr>
                    </tbody>
                </table>
            HTML;

        $this->assertTableHeaderScopeIsValid($html);
    })->throws(AssertionFailedError::class, 'scope="rowgroup"');

    test('fails when th has empty scope', function () {
        $html = <<<'HTML'
                <table>
                    <thead>
                        <tr>
                            <th scope="">Header</th>
                        </tr>
                    </thead>
                </table>
            HTML;

        $this->assertTableHeaderScopeIsValid($html);
    })->throws(AssertionFailedError::class, 'must be "col" or "row"');

    test('fails when th has arbitrary scope value', function () {
        $html = <<<'HTML'
                <table>
                    <thead>
                        <tr>
                            <th scope="invalid">Header</th>
                        </tr>
                    </thead>
                </table>
            HTML;

        $this->assertTableHeaderScopeIsValid($html);
    })->throws(AssertionFailedError::class, 'scope="invalid"');

    test('fails when th with id has invalid scope', function () {
        $html = <<<'HTML'
                <table>
                    <thead>
                        <tr>
                            <th id="header-1" scope="column">Header</th>
                        </tr>
                    </thead>
                </table>
            HTML;

        $this->assertTableHeaderScopeIsValid($html);
    })->throws(AssertionFailedError::class, 'id="header-1"');

    test('fails when th with name has invalid scope', function () {
        $html = <<<'HTML'
                <table>
                    <thead>
                        <tr>
                            <th name="header" scope="cell">Header</th>
                        </tr>
                    </thead>
                </table>
            HTML;

        $this->assertTableHeaderScopeIsValid($html);
    })->throws(AssertionFailedError::class, 'name="header"');

    test('fails when multiple th elements have invalid scope', function () {
        $html = <<<'HTML'
                <table>
                    <thead>
                        <tr>
                            <th scope="column">First</th>
                            <th scope="header">Second</th>
                        </tr>
                    </thead>
                </table>
            HTML;

        $this->assertTableHeaderScopeIsValid($html);
    })->throws(AssertionFailedError::class, 'invalid scope attributes');

    test('fails when mixing valid and invalid scope values', function () {
        $html = <<<'HTML'
                <table>
                    <thead>
                        <tr>
                            <th scope="col">Valid</th>
                            <th scope="column">Invalid</th>
                        </tr>
                    </thead>
                </table>
            HTML;

        $this->assertTableHeaderScopeIsValid($html);
    })->throws(AssertionFailedError::class, 'must be "col" or "row"');
});

describe('assertPlaceholderNotUsedAsLabel', function () {
    test('passes when input has placeholder and label element', function () {
        $html = <<<'HTML'
                <label for="test">Name</label>
                <input type="text" id="test" placeholder="Enter your name">
            HTML;

        expect(fn () => $this->assertPlaceholderNotUsedAsLabel($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when input has placeholder and title attribute', function () {
        $html = <<<'HTML'
                <input type="text" placeholder="Enter your name" title="Name">
            HTML;

        expect(fn () => $this->assertPlaceholderNotUsedAsLabel($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when input has placeholder and aria-label', function () {
        $html = <<<'HTML'
                <input type="text" placeholder="Enter your name" aria-label="Name">
            HTML;

        expect(fn () => $this->assertPlaceholderNotUsedAsLabel($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when input has placeholder and aria-labelledby', function () {
        $html = <<<'HTML'
                <span id="label">Name</span>
                <input type="text" placeholder="Enter your name" aria-labelledby="label">
            HTML;

        expect(fn () => $this->assertPlaceholderNotUsedAsLabel($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when input is wrapped in label element', function () {
        $html = <<<'HTML'
                <label>
                    Name
                    <input type="text" placeholder="Enter your name">
                </label>
            HTML;

        expect(fn () => $this->assertPlaceholderNotUsedAsLabel($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when input has no placeholder', function () {
        $html = <<<'HTML'
                <input type="text" id="test">
            HTML;

        expect(fn () => $this->assertPlaceholderNotUsedAsLabel($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when textarea has placeholder and label', function () {
        $html = <<<'HTML'
                <label for="comment">Comment</label>
                <textarea id="comment" placeholder="Enter your comment"></textarea>
            HTML;

        expect(fn () => $this->assertPlaceholderNotUsedAsLabel($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when input has placeholder and multiple labeling methods', function () {
        $html = <<<'HTML'
                <label for="test">Name</label>
                <input type="text" id="test" placeholder="Enter your name" title="Name field" aria-label="Name">
            HTML;

        expect(fn () => $this->assertPlaceholderNotUsedAsLabel($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when input is nested deeply in label', function () {
        $html = <<<'HTML'
                <label>
                    <div>
                        <span>Name</span>
                        <input type="text" placeholder="Enter your name">
                    </div>
                </label>
            HTML;

        expect(fn () => $this->assertPlaceholderNotUsedAsLabel($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when input has only placeholder', function () {
        $html = <<<'HTML'
                <input type="text" placeholder="Look Ma, no label!" id="test">
            HTML;

        $this->assertPlaceholderNotUsedAsLabel($html);
    })->throws(AssertionFailedError::class, 'uses placeholder as label');

    test('fails when input has placeholder but no associated label', function () {
        $html = <<<'HTML'
                <input type="text" id="test" placeholder="Enter your name">
            HTML;

        $this->assertPlaceholderNotUsedAsLabel($html);
    })->throws(AssertionFailedError::class, 'uses placeholder as label');

    test('fails when input has id but label for attribute does not match', function () {
        $html = <<<'HTML'
                <label for="other">Name</label>
                <input type="text" id="test" placeholder="Enter your name">
            HTML;

        $this->assertPlaceholderNotUsedAsLabel($html);
    })->throws(AssertionFailedError::class, 'uses placeholder as label');

    test('fails when textarea has only placeholder', function () {
        $html = <<<'HTML'
                <textarea placeholder="Enter comment"></textarea>
            HTML;

        $this->assertPlaceholderNotUsedAsLabel($html);
    })->throws(AssertionFailedError::class, 'uses placeholder as label');

    test('fails when input with id has only placeholder', function () {
        $html = <<<'HTML'
                <input type="email" id="email-field" placeholder="your@email.com">
            HTML;

        $this->assertPlaceholderNotUsedAsLabel($html);
    })->throws(AssertionFailedError::class, 'id="email-field"');

    test('fails when input with name has only placeholder', function () {
        $html = <<<'HTML'
                <input type="text" name="username" placeholder="Username">
            HTML;

        $this->assertPlaceholderNotUsedAsLabel($html);
    })->throws(AssertionFailedError::class, 'name="username"');

    test('fails when multiple inputs have only placeholders', function () {
        $html = <<<'HTML'
                <input type="text" placeholder="First name">
                <input type="text" placeholder="Last name">
            HTML;

        $this->assertPlaceholderNotUsedAsLabel($html);
    })->throws(AssertionFailedError::class, 'uses placeholder as label');

    test('fails when select has only placeholder', function () {
        $html = <<<'HTML'
                <select placeholder="Choose option">
                    <option value="">Select...</option>
                </select>
            HTML;

        $this->assertPlaceholderNotUsedAsLabel($html);
    })->throws(AssertionFailedError::class, 'uses placeholder as label');

    test('fails with placeholder text in error message', function () {
        $html = <<<'HTML'
                <input type="text" placeholder="Look Ma, no label!">
            HTML;

        $this->assertPlaceholderNotUsedAsLabel($html);
    })->throws(AssertionFailedError::class, 'placeholder="Look Ma, no label!"');
});

describe('assertNoDuplicatedUniqueRoles', function () {
    test('passes when there is only one element with role main', function () {
        $html = <<<'HTML'
                <main role="main">Content</main>
            HTML;

        expect(fn () => $this->assertNoDuplicatedUniqueRoles($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when there is only one element with role search', function () {
        $html = <<<'HTML'
                <div role="search">Search form</div>
            HTML;

        expect(fn () => $this->assertNoDuplicatedUniqueRoles($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when there is only one element with role banner', function () {
        $html = <<<'HTML'
                <header role="banner">Header</header>
            HTML;

        expect(fn () => $this->assertNoDuplicatedUniqueRoles($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when there is only one element with role contentinfo', function () {
        $html = <<<'HTML'
                <footer role="contentinfo">Footer</footer>
            HTML;

        expect(fn () => $this->assertNoDuplicatedUniqueRoles($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when there are no elements with unique roles', function () {
        $html = <<<'HTML'
                <div>Just a div</div>
                <p>Paragraph</p>
            HTML;

        expect(fn () => $this->assertNoDuplicatedUniqueRoles($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when there are multiple different unique roles', function () {
        $html = <<<'HTML'
                <header role="banner">Header</header>
                <div role="search">Search</div>
                <main role="main">Content</main>
                <footer role="contentinfo">Footer</footer>
            HTML;

        expect(fn () => $this->assertNoDuplicatedUniqueRoles($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when there are elements with non-unique roles', function () {
        $html = <<<'HTML'
                <div role="navigation">Nav 1</div>
                <div role="navigation">Nav 2</div>
                <div role="complementary">Sidebar 1</div>
                <div role="complementary">Sidebar 2</div>
            HTML;

        expect(fn () => $this->assertNoDuplicatedUniqueRoles($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when semantic element is used without role', function () {
        $html = <<<'HTML'
                <main>Content</main>
                <header>Header</header>
            HTML;

        expect(fn () => $this->assertNoDuplicatedUniqueRoles($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when there are two elements with role main', function () {
        $html = <<<'HTML'
                <main role="main"><br /></main>
                <div role="main"><br /></div>
            HTML;

        $this->assertNoDuplicatedUniqueRoles($html);
    })->throws(AssertionFailedError::class, 'role="main" should be unique');

    test('fails when there are two elements with role search', function () {
        $html = <<<'HTML'
                <div role="search">Search 1</div>
                <div role="search">Search 2</div>
            HTML;

        $this->assertNoDuplicatedUniqueRoles($html);
    })->throws(AssertionFailedError::class, 'role="search" should be unique');

    test('fails when there are two elements with role banner', function () {
        $html = <<<'HTML'
                <header role="banner">Header 1</header>
                <div role="banner">Header 2</div>
            HTML;

        $this->assertNoDuplicatedUniqueRoles($html);
    })->throws(AssertionFailedError::class, 'role="banner" should be unique');

    test('fails when there are two elements with role contentinfo', function () {
        $html = <<<'HTML'
                <footer role="contentinfo">Footer 1</footer>
                <div role="contentinfo">Footer 2</div>
            HTML;

        $this->assertNoDuplicatedUniqueRoles($html);
    })->throws(AssertionFailedError::class, 'role="contentinfo" should be unique');

    test('fails when duplicate role main element has id', function () {
        $html = <<<'HTML'
                <main role="main">First</main>
                <div id="second-main" role="main">Second</div>
            HTML;

        $this->assertNoDuplicatedUniqueRoles($html);
    })->throws(AssertionFailedError::class, 'id="second-main"');

    test('fails when duplicate role main element has name', function () {
        $html = <<<'HTML'
                <main role="main">First</main>
                <div name="second" role="main">Second</div>
            HTML;

        $this->assertNoDuplicatedUniqueRoles($html);
    })->throws(AssertionFailedError::class, 'name="second"');

    test('fails when there are three elements with role main', function () {
        $html = <<<'HTML'
                <main role="main">First</main>
                <div role="main">Second</div>
                <section role="main">Third</section>
            HTML;

        $this->assertNoDuplicatedUniqueRoles($html);
    })->throws(AssertionFailedError::class, 'role="main" should be unique');

    test('fails when multiple different unique roles are duplicated', function () {
        $html = <<<'HTML'
                <header role="banner">Header 1</header>
                <div role="banner">Header 2</div>
                <main role="main">Main 1</main>
                <div role="main">Main 2</div>
            HTML;

        $this->assertNoDuplicatedUniqueRoles($html);
    })->throws(AssertionFailedError::class, 'duplicate unique ARIA roles');
});

describe('assertNoButtonRoleOnLinks', function () {
    test('passes when there are no links with button role', function () {
        $html = <<<'HTML'
                <a href="/">Regular link</a>
                <button type="button">Proper button</button>
            HTML;

        expect(fn () => $this->assertNoButtonRoleOnLinks($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when link has different role', function () {
        $html = <<<'HTML'
                <a href="/" role="tab">Tab link</a>
            HTML;

        expect(fn () => $this->assertNoButtonRoleOnLinks($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when button has button role', function () {
        $html = <<<'HTML'
                <button role="button">Button with role</button>
            HTML;

        expect(fn () => $this->assertNoButtonRoleOnLinks($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when there are no elements at all', function () {
        $html = <<<'HTML'
                <div>Just a div</div>
            HTML;

        expect(fn () => $this->assertNoButtonRoleOnLinks($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when link has no role attribute', function () {
        $html = <<<'HTML'
                <a href="/submit">Submit</a>
            HTML;

        expect(fn () => $this->assertNoButtonRoleOnLinks($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when link has button role', function () {
        $html = <<<'HTML'
                <a href="/" role="button">Submit</a>
            HTML;

        $this->assertNoButtonRoleOnLinks($html);
    })->throws(AssertionFailedError::class, 'should be a <button> element');

    test('fails when link with id has button role', function () {
        $html = <<<'HTML'
                <a id="submit-link" href="/" role="button">Submit</a>
            HTML;

        $this->assertNoButtonRoleOnLinks($html);
    })->throws(AssertionFailedError::class, 'id="submit-link"');

    test('fails when link with name has button role', function () {
        $html = <<<'HTML'
                <a name="submit" href="/" role="button">Submit</a>
            HTML;

        $this->assertNoButtonRoleOnLinks($html);
    })->throws(AssertionFailedError::class, 'name="submit"');

    test('fails when multiple links have button role', function () {
        $html = <<<'HTML'
                <a href="/" role="button">First</a>
                <a href="/" role="button">Second</a>
            HTML;

        $this->assertNoButtonRoleOnLinks($html);
    })->throws(AssertionFailedError::class, 'should be a <button> element');

    test('fails when link has button role with other attributes', function () {
        $html = <<<'HTML'
                <a href="/submit" class="btn btn-primary" role="button">Submit</a>
            HTML;

        $this->assertNoButtonRoleOnLinks($html);
    })->throws(AssertionFailedError::class, 'should be a <button> element');

    test('fails when link has button role case-sensitive', function () {
        $html = <<<'HTML'
                <a href="/" role="button">Click me</a>
            HTML;

        $this->assertNoButtonRoleOnLinks($html);
    })->throws(AssertionFailedError::class, 'should be a <button> element');
});

describe('assertFaxLinksHaveValidPhoneNumber', function () {
    test('passes when fax link has valid US phone number', function () {
        $html = <<<'HTML'
                <a href="fax:+15551234567">Fax us</a>
            HTML;

        expect(fn () => $this->assertFaxLinksHaveValidPhoneNumber($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when fax link has valid UK phone number', function () {
        $html = <<<'HTML'
                <a href="fax:+442071234567">Fax UK</a>
            HTML;

        expect(fn () => $this->assertFaxLinksHaveValidPhoneNumber($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when fax link has phone with spaces', function () {
        $html = <<<'HTML'
                <a href="fax:+1 555 123 4567">Fax with spaces</a>
            HTML;

        expect(fn () => $this->assertFaxLinksHaveValidPhoneNumber($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when fax link has phone with dashes', function () {
        $html = <<<'HTML'
                <a href="fax:+1-555-123-4567">Fax with dashes</a>
            HTML;

        expect(fn () => $this->assertFaxLinksHaveValidPhoneNumber($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when fax link has 10-digit local number', function () {
        $html = <<<'HTML'
                <a href="fax:5551234567">Local fax</a>
            HTML;

        expect(fn () => $this->assertFaxLinksHaveValidPhoneNumber($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when there are no fax links', function () {
        $html = <<<'HTML'
                <a href="https://example.com">Regular link</a>
                <a href="tel:+15551234567">Phone link</a>
            HTML;

        expect(fn () => $this->assertFaxLinksHaveValidPhoneNumber($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when fax link has T.33 subaddress parameter', function () {
        $html = <<<'HTML'
                <a href="fax:+15551234567;tsub=9876">Fax with subaddress</a>
            HTML;

        expect(fn () => $this->assertFaxLinksHaveValidPhoneNumber($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when fax link has phone-context parameter', function () {
        $html = <<<'HTML'
                <a href="fax:5551234567;phone-context=+1555">Fax with context</a>
            HTML;

        expect(fn () => $this->assertFaxLinksHaveValidPhoneNumber($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when fax link has too few digits', function () {
        $html = <<<'HTML'
                <a href="fax:123456789">Too short fax</a>
            HTML;

        $this->assertFaxLinksHaveValidPhoneNumber($html);
    })->throws(AssertionFailedError::class, 'invalid phone number');

    test('fails when fax link has only 3 digits', function () {
        $html = <<<'HTML'
                <a href="fax:123">Too short</a>
            HTML;

        $this->assertFaxLinksHaveValidPhoneNumber($html);
    })->throws(AssertionFailedError::class, 'invalid phone number');

    test('fails when fax link has letters', function () {
        $html = <<<'HTML'
                <a href="fax:555-FAXX">Letters not allowed</a>
            HTML;

        $this->assertFaxLinksHaveValidPhoneNumber($html);
    })->throws(AssertionFailedError::class, 'invalid phone number');

    test('fails when fax link is empty', function () {
        $html = <<<'HTML'
                <a href="fax:">Empty number</a>
            HTML;

        $this->assertFaxLinksHaveValidPhoneNumber($html);
    })->throws(AssertionFailedError::class, 'invalid phone number');

    test('fails when link with id has invalid fax number', function () {
        $html = <<<'HTML'
                <a id="fax-link" href="fax:12345">Fax</a>
            HTML;

        $this->assertFaxLinksHaveValidPhoneNumber($html);
    })->throws(AssertionFailedError::class, 'id="fax-link"');
});

describe('assertModemLinksHaveValidPhoneNumber', function () {
    test('passes when modem link has valid US phone number', function () {
        $html = <<<'HTML'
                <a href="modem:+15551234567">Dial modem</a>
            HTML;

        expect(fn () => $this->assertModemLinksHaveValidPhoneNumber($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when modem link has valid UK phone number', function () {
        $html = <<<'HTML'
                <a href="modem:+442071234567">Dial UK modem</a>
            HTML;

        expect(fn () => $this->assertModemLinksHaveValidPhoneNumber($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when modem link has phone with spaces', function () {
        $html = <<<'HTML'
                <a href="modem:+1 555 123 4567">Modem with spaces</a>
            HTML;

        expect(fn () => $this->assertModemLinksHaveValidPhoneNumber($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when modem link has phone with dashes', function () {
        $html = <<<'HTML'
                <a href="modem:+1-555-123-4567">Modem with dashes</a>
            HTML;

        expect(fn () => $this->assertModemLinksHaveValidPhoneNumber($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when modem link has 10-digit local number', function () {
        $html = <<<'HTML'
                <a href="modem:5551234567">Local modem</a>
            HTML;

        expect(fn () => $this->assertModemLinksHaveValidPhoneNumber($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when there are no modem links', function () {
        $html = <<<'HTML'
                <a href="https://example.com">Regular link</a>
                <a href="tel:+15551234567">Phone link</a>
            HTML;

        expect(fn () => $this->assertModemLinksHaveValidPhoneNumber($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when modem link has type parameter', function () {
        $html = <<<'HTML'
                <a href="modem:+15551234567;type=v32b">Modem with type</a>
            HTML;

        expect(fn () => $this->assertModemLinksHaveValidPhoneNumber($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when modem link has multiple type parameters', function () {
        $html = <<<'HTML'
                <a href="modem:+3585551234567;type=v32b?7e1;type=v110">Modem with options</a>
            HTML;

        expect(fn () => $this->assertModemLinksHaveValidPhoneNumber($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when modem link has phone-context parameter', function () {
        $html = <<<'HTML'
                <a href="modem:5551234567;phone-context=+1555">Modem with context</a>
            HTML;

        expect(fn () => $this->assertModemLinksHaveValidPhoneNumber($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when modem link has too few digits', function () {
        $html = <<<'HTML'
                <a href="modem:123456789">Too short modem</a>
            HTML;

        $this->assertModemLinksHaveValidPhoneNumber($html);
    })->throws(AssertionFailedError::class, 'invalid phone number');

    test('fails when modem link has only 3 digits', function () {
        $html = <<<'HTML'
                <a href="modem:123">Too short</a>
            HTML;

        $this->assertModemLinksHaveValidPhoneNumber($html);
    })->throws(AssertionFailedError::class, 'invalid phone number');

    test('fails when modem link has letters', function () {
        $html = <<<'HTML'
                <a href="modem:555-DATA">Letters not allowed</a>
            HTML;

        $this->assertModemLinksHaveValidPhoneNumber($html);
    })->throws(AssertionFailedError::class, 'invalid phone number');

    test('fails when modem link is empty', function () {
        $html = <<<'HTML'
                <a href="modem:">Empty number</a>
            HTML;

        $this->assertModemLinksHaveValidPhoneNumber($html);
    })->throws(AssertionFailedError::class, 'invalid phone number');

    test('fails when link with id has invalid modem number', function () {
        $html = <<<'HTML'
                <a id="modem-link" href="modem:12345">Dial</a>
            HTML;

        $this->assertModemLinksHaveValidPhoneNumber($html);
    })->throws(AssertionFailedError::class, 'id="modem-link"');
});

describe('assertTelLinksHaveValidPhoneNumber', function () {
    test('passes when tel link has valid US phone number', function () {
        $html = <<<'HTML'
                <a href="tel:+15551234567">Call us</a>
            HTML;

        expect(fn () => $this->assertTelLinksHaveValidPhoneNumber($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when tel link has valid UK phone number', function () {
        $html = <<<'HTML'
                <a href="tel:+442071234567">Call UK</a>
            HTML;

        expect(fn () => $this->assertTelLinksHaveValidPhoneNumber($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when tel link has valid international number', function () {
        $html = <<<'HTML'
                <a href="tel:+33123456789">Call France</a>
            HTML;

        expect(fn () => $this->assertTelLinksHaveValidPhoneNumber($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when tel link has phone with spaces', function () {
        $html = <<<'HTML'
                <a href="tel:+1 555 123 4567">Call with spaces</a>
            HTML;

        expect(fn () => $this->assertTelLinksHaveValidPhoneNumber($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when tel link has phone with dashes', function () {
        $html = <<<'HTML'
                <a href="tel:+1-555-123-4567">Call with dashes</a>
            HTML;

        expect(fn () => $this->assertTelLinksHaveValidPhoneNumber($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when tel link has phone with parentheses', function () {
        $html = <<<'HTML'
                <a href="tel:+1(555)123-4567">Call with parentheses</a>
            HTML;

        expect(fn () => $this->assertTelLinksHaveValidPhoneNumber($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when tel link has phone with dots', function () {
        $html = <<<'HTML'
                <a href="tel:+1.555.123.4567">Call with dots</a>
            HTML;

        expect(fn () => $this->assertTelLinksHaveValidPhoneNumber($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when tel link has 10-digit local number without country code', function () {
        $html = <<<'HTML'
                <a href="tel:5551234567">Local call</a>
            HTML;

        expect(fn () => $this->assertTelLinksHaveValidPhoneNumber($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when there are no tel links', function () {
        $html = <<<'HTML'
                <a href="https://example.com">Regular link</a>
                <a href="mailto:contact@example.com">Email link</a>
            HTML;

        expect(fn () => $this->assertTelLinksHaveValidPhoneNumber($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when tel link has post-dial parameter', function () {
        $html = <<<'HTML'
                <a href="tel:+358-555-1234567;postd=pp22">Call with extension</a>
            HTML;

        expect(fn () => $this->assertTelLinksHaveValidPhoneNumber($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when tel link has phone-context parameter', function () {
        $html = <<<'HTML'
                <a href="tel:1234567890;phone-context=+358555">Call with context</a>
            HTML;

        expect(fn () => $this->assertTelLinksHaveValidPhoneNumber($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when tel link has multiple parameters', function () {
        $html = <<<'HTML'
                <a href="tel:+1234567890;phone-context=+1234;vnd.company.option=foo">Call with options</a>
            HTML;

        expect(fn () => $this->assertTelLinksHaveValidPhoneNumber($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when tel link has wait character and context', function () {
        $html = <<<'HTML'
                <a href="tel:0w0012341234567;phone-context=+12341234">Call and wait</a>
            HTML;

        expect(fn () => $this->assertTelLinksHaveValidPhoneNumber($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when tel link has ISDN subaddress', function () {
        $html = <<<'HTML'
                <a href="tel:+15551234567;isub=1234">Call with subaddress</a>
            HTML;

        expect(fn () => $this->assertTelLinksHaveValidPhoneNumber($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when tel link has too few digits', function () {
        $html = <<<'HTML'
                <a href="tel:012345678">Who will I call?</a>
            HTML;

        $this->assertTelLinksHaveValidPhoneNumber($html);
    })->throws(AssertionFailedError::class, 'invalid phone number');

    test('fails when tel link has only 3 digits', function () {
        $html = <<<'HTML'
                <a href="tel:123">Too short</a>
            HTML;

        $this->assertTelLinksHaveValidPhoneNumber($html);
    })->throws(AssertionFailedError::class, 'invalid phone number');

    test('fails when tel link has letters', function () {
        $html = <<<'HTML'
                <a href="tel:555-CALL">Letters not allowed</a>
            HTML;

        $this->assertTelLinksHaveValidPhoneNumber($html);
    })->throws(AssertionFailedError::class, 'invalid phone number');

    test('fails when tel link has special characters', function () {
        $html = <<<'HTML'
                <a href="tel:+1#555*123">Special chars</a>
            HTML;

        $this->assertTelLinksHaveValidPhoneNumber($html);
    })->throws(AssertionFailedError::class, 'invalid phone number');

    test('fails when tel link is empty', function () {
        $html = <<<'HTML'
                <a href="tel:">Empty number</a>
            HTML;

        $this->assertTelLinksHaveValidPhoneNumber($html);
    })->throws(AssertionFailedError::class, 'invalid phone number');

    test('fails when tel link has only spaces', function () {
        $html = <<<'HTML'
                <a href="tel:   ">Only spaces</a>
            HTML;

        $this->assertTelLinksHaveValidPhoneNumber($html);
    })->throws(AssertionFailedError::class, 'invalid phone number');

    test('fails when link with id has invalid number', function () {
        $html = <<<'HTML'
                <a id="call-link" href="tel:12345">Call</a>
            HTML;

        $this->assertTelLinksHaveValidPhoneNumber($html);
    })->throws(AssertionFailedError::class, 'id="call-link"');

    test('fails when link with name has invalid number', function () {
        $html = <<<'HTML'
                <a name="phone" href="tel:123">Call</a>
            HTML;

        $this->assertTelLinksHaveValidPhoneNumber($html);
    })->throws(AssertionFailedError::class, 'name="phone"');

    test('fails when multiple links have invalid numbers', function () {
        $html = <<<'HTML'
                <a href="tel:123">First</a>
                <a href="tel:456">Second</a>
            HTML;

        $this->assertTelLinksHaveValidPhoneNumber($html);
    })->throws(AssertionFailedError::class, 'invalid phone number');
});

describe('assertMailtoLinksHaveValidEmail', function () {
    test('passes when mailto link has valid email', function () {
        $html = <<<'HTML'
                <a href="mailto:contact@example.com">Email us</a>
            HTML;

        expect(fn () => $this->assertMailtoLinksHaveValidEmail($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when mailto link has valid email with subdomain', function () {
        $html = <<<'HTML'
                <a href="mailto:info@mail.example.com">Contact</a>
            HTML;

        expect(fn () => $this->assertMailtoLinksHaveValidEmail($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when mailto link has valid email with plus sign', function () {
        $html = <<<'HTML'
                <a href="mailto:user+tag@example.com">Email</a>
            HTML;

        expect(fn () => $this->assertMailtoLinksHaveValidEmail($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when mailto link has valid email with numbers', function () {
        $html = <<<'HTML'
                <a href="mailto:user123@example456.com">Contact</a>
            HTML;

        expect(fn () => $this->assertMailtoLinksHaveValidEmail($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when mailto link has valid email with dots', function () {
        $html = <<<'HTML'
                <a href="mailto:first.last@example.com">Email</a>
            HTML;

        expect(fn () => $this->assertMailtoLinksHaveValidEmail($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when mailto link has query parameters', function () {
        $html = <<<'HTML'
                <a href="mailto:contact@example.com?subject=Hello">Email with subject</a>
            HTML;

        expect(fn () => $this->assertMailtoLinksHaveValidEmail($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when mailto link has multiple valid emails', function () {
        $html = <<<'HTML'
                <a href="mailto:first@example.com,second@example.com">Email multiple</a>
            HTML;

        expect(fn () => $this->assertMailtoLinksHaveValidEmail($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when there are no mailto links', function () {
        $html = <<<'HTML'
                <a href="https://example.com">Regular link</a>
                <a href="/contact">Internal link</a>
            HTML;

        expect(fn () => $this->assertMailtoLinksHaveValidEmail($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when mailto link has no at symbol', function () {
        $html = <<<'HTML'
                <a href="mailto:myself">Surpri-ise!</a>
            HTML;

        $this->assertMailtoLinksHaveValidEmail($html);
    })->throws(AssertionFailedError::class, 'invalid email address');

    test('fails when mailto link has no domain', function () {
        $html = <<<'HTML'
                <a href="mailto:user@">Incomplete email</a>
            HTML;

        $this->assertMailtoLinksHaveValidEmail($html);
    })->throws(AssertionFailedError::class, 'invalid email address');

    test('fails when mailto link has no username', function () {
        $html = <<<'HTML'
                <a href="mailto:@example.com">No username</a>
            HTML;

        $this->assertMailtoLinksHaveValidEmail($html);
    })->throws(AssertionFailedError::class, 'invalid email address');

    test('fails when mailto link has spaces', function () {
        $html = <<<'HTML'
                <a href="mailto:user name@example.com">Email with spaces</a>
            HTML;

        $this->assertMailtoLinksHaveValidEmail($html);
    })->throws(AssertionFailedError::class, 'invalid email address');

    test('fails when mailto link has double at symbols', function () {
        $html = <<<'HTML'
                <a href="mailto:user@@example.com">Double at</a>
            HTML;

        $this->assertMailtoLinksHaveValidEmail($html);
    })->throws(AssertionFailedError::class, 'invalid email address');

    test('fails when mailto link has no top-level domain', function () {
        $html = <<<'HTML'
                <a href="mailto:user@domain">No TLD</a>
            HTML;

        $this->assertMailtoLinksHaveValidEmail($html);
    })->throws(AssertionFailedError::class, 'invalid email address');

    test('fails when link with id has invalid email', function () {
        $html = <<<'HTML'
                <a id="contact-link" href="mailto:invalid">Contact</a>
            HTML;

        $this->assertMailtoLinksHaveValidEmail($html);
    })->throws(AssertionFailedError::class, 'id="contact-link"');

    test('fails when link with name has invalid email', function () {
        $html = <<<'HTML'
                <a name="email" href="mailto:notanemail">Email</a>
            HTML;

        $this->assertMailtoLinksHaveValidEmail($html);
    })->throws(AssertionFailedError::class, 'name="email"');

    test('fails when one of multiple emails is invalid', function () {
        $html = <<<'HTML'
                <a href="mailto:valid@example.com,invalid">Email multiple</a>
            HTML;

        $this->assertMailtoLinksHaveValidEmail($html);
    })->throws(AssertionFailedError::class, 'invalid email address');

    test('fails when multiple links have invalid emails', function () {
        $html = <<<'HTML'
                <a href="mailto:invalid1">First</a>
                <a href="mailto:invalid2">Second</a>
            HTML;

        $this->assertMailtoLinksHaveValidEmail($html);
    })->throws(AssertionFailedError::class, 'invalid email');
});

describe('assertFigcaptionIsFirstOrLastChild', function () {
    test('passes when figcaption is first child', function () {
        $html = <<<'HTML'
                <figure>
                    <figcaption>I'm the caption.</figcaption>
                    <img src="/photo.jpg" alt="A photo" />
                </figure>
            HTML;

        expect(fn () => $this->assertFigcaptionIsFirstOrLastChild($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when figcaption is last child', function () {
        $html = <<<'HTML'
                <figure>
                    <img src="/photo.jpg" alt="A photo" />
                    <figcaption>Caption below</figcaption>
                </figure>
            HTML;

        expect(fn () => $this->assertFigcaptionIsFirstOrLastChild($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when figcaption is only child', function () {
        $html = <<<'HTML'
                <figure>
                    <figcaption>Only caption</figcaption>
                </figure>
            HTML;

        expect(fn () => $this->assertFigcaptionIsFirstOrLastChild($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when figure has no figcaption', function () {
        $html = <<<'HTML'
                <figure>
                    <img src="/photo.jpg" alt="A photo" />
                    <p>Some text</p>
                </figure>
            HTML;

        expect(fn () => $this->assertFigcaptionIsFirstOrLastChild($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when figcaption is first with multiple siblings', function () {
        $html = <<<'HTML'
                <figure>
                    <figcaption>Caption first</figcaption>
                    <img src="/photo1.jpg" alt="Photo 1" />
                    <img src="/photo2.jpg" alt="Photo 2" />
                    <p>Description</p>
                </figure>
            HTML;

        expect(fn () => $this->assertFigcaptionIsFirstOrLastChild($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when figcaption is last with multiple siblings', function () {
        $html = <<<'HTML'
                <figure>
                    <p>Description</p>
                    <img src="/photo1.jpg" alt="Photo 1" />
                    <img src="/photo2.jpg" alt="Photo 2" />
                    <figcaption>Caption last</figcaption>
                </figure>
            HTML;

        expect(fn () => $this->assertFigcaptionIsFirstOrLastChild($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when figcaption is in the middle', function () {
        $html = <<<'HTML'
                <figure role="group">
                    <img src="/static/ffoodd.png" alt="Needed" width="144" height="144" />
                    <figcaption>I'm the caption.</figcaption>
                    <p>I'm something else. Oh, wait.</p>
                </figure>
            HTML;

        $this->assertFigcaptionIsFirstOrLastChild($html);
    })->throws(AssertionFailedError::class, 'neither first nor last child');

    test('fails when figcaption is second of three children', function () {
        $html = <<<'HTML'
                <figure>
                    <p>First child</p>
                    <figcaption>Second child - the caption</figcaption>
                    <img src="/photo.jpg" alt="Third child" />
                </figure>
            HTML;

        $this->assertFigcaptionIsFirstOrLastChild($html);
    })->throws(AssertionFailedError::class, 'neither first nor last child');

    test('fails when figcaption is in the middle with many siblings', function () {
        $html = <<<'HTML'
                <figure>
                    <p>First</p>
                    <div>Second</div>
                    <figcaption>Third - middle caption</figcaption>
                    <img src="/photo.jpg" alt="Fourth" />
                    <p>Fifth</p>
                </figure>
            HTML;

        $this->assertFigcaptionIsFirstOrLastChild($html);
    })->throws(AssertionFailedError::class, 'neither first nor last child');

    test('fails when figcaption with id is in the middle', function () {
        $html = <<<'HTML'
                <figure>
                    <img src="/photo.jpg" alt="Photo" />
                    <figcaption id="middle-caption">Caption in middle</figcaption>
                    <p>Description</p>
                </figure>
            HTML;

        $this->assertFigcaptionIsFirstOrLastChild($html);
    })->throws(AssertionFailedError::class, 'id="middle-caption"');

    test('fails when figcaption with name is in the middle', function () {
        $html = <<<'HTML'
                <figure>
                    <img src="/photo.jpg" alt="Photo" />
                    <figcaption name="caption">Caption in middle</figcaption>
                    <p>Description</p>
                </figure>
            HTML;

        $this->assertFigcaptionIsFirstOrLastChild($html);
    })->throws(AssertionFailedError::class, 'name="caption"');

    test('fails when figcaption is in middle of div', function () {
        $html = <<<'HTML'
                <div>
                    <p>First</p>
                    <figcaption>Middle caption in div</figcaption>
                    <p>Last</p>
                </div>
            HTML;

        $this->assertFigcaptionIsFirstOrLastChild($html);
    })->throws(AssertionFailedError::class, 'within <div>');

    test('fails when multiple figcaptions are in the middle', function () {
        $html = <<<'HTML'
                <figure>
                    <p>First</p>
                    <figcaption>Middle caption 1</figcaption>
                    <figcaption>Middle caption 2</figcaption>
                    <p>Last</p>
                </figure>
            HTML;

        $this->assertFigcaptionIsFirstOrLastChild($html);
    })->throws(AssertionFailedError::class, 'not first or last child');
});

describe('assertOnlyOneFigcaption', function () {
    test('passes when figure has one figcaption', function () {
        $html = <<<'HTML'
                <figure>
                    <figcaption>I'm the caption.</figcaption>
                    <img src="/photo.jpg" alt="A photo" />
                </figure>
            HTML;

        expect(fn () => $this->assertOnlyOneFigcaption($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when figure has figcaption after image', function () {
        $html = <<<'HTML'
                <figure>
                    <img src="/photo.jpg" alt="A photo" />
                    <figcaption>Caption below</figcaption>
                </figure>
            HTML;

        expect(fn () => $this->assertOnlyOneFigcaption($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when figure has no figcaption', function () {
        $html = <<<'HTML'
                <figure>
                    <img src="/photo.jpg" alt="A photo" />
                </figure>
            HTML;

        expect(fn () => $this->assertOnlyOneFigcaption($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when multiple figures each have one figcaption', function () {
        $html = <<<'HTML'
                <figure>
                    <figcaption>First caption</figcaption>
                    <img src="/photo1.jpg" alt="Photo 1" />
                </figure>
                <figure>
                    <figcaption>Second caption</figcaption>
                    <img src="/photo2.jpg" alt="Photo 2" />
                </figure>
            HTML;

        expect(fn () => $this->assertOnlyOneFigcaption($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when figcaption is outside figure', function () {
        $html = <<<'HTML'
                <div>
                    <figcaption>Orphan caption</figcaption>
                </div>
            HTML;

        expect(fn () => $this->assertOnlyOneFigcaption($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when figure has two figcaptions', function () {
        $html = <<<'HTML'
                <figure role="group">
                    <figcaption>I'm the caption.</figcaption>
                    <img src="/static/ffoodd.png" alt="Needed" width="144" height="144" />
                    <figcaption>I'm the caption too.</figcaption>
                </figure>
            HTML;

        $this->assertOnlyOneFigcaption($html);
    })->throws(AssertionFailedError::class, 'second figcaption');

    test('fails when figure has three figcaptions', function () {
        $html = <<<'HTML'
                <figure>
                    <figcaption>First caption</figcaption>
                    <img src="/photo.jpg" alt="Photo" />
                    <figcaption>Second caption</figcaption>
                    <figcaption>Third caption</figcaption>
                </figure>
            HTML;

        $this->assertOnlyOneFigcaption($html);
    })->throws(AssertionFailedError::class, 'multiple <figcaption>');

    test('fails when second figcaption has id', function () {
        $html = <<<'HTML'
                <figure>
                    <figcaption>First caption</figcaption>
                    <img src="/photo.jpg" alt="Photo" />
                    <figcaption id="second-caption">Second caption</figcaption>
                </figure>
            HTML;

        $this->assertOnlyOneFigcaption($html);
    })->throws(AssertionFailedError::class, 'id="second-caption"');

    test('fails when second figcaption has name', function () {
        $html = <<<'HTML'
                <figure>
                    <figcaption>First caption</figcaption>
                    <img src="/photo.jpg" alt="Photo" />
                    <figcaption name="caption">Second caption</figcaption>
                </figure>
            HTML;

        $this->assertOnlyOneFigcaption($html);
    })->throws(AssertionFailedError::class, 'name="caption"');

    test('fails when div has multiple figcaptions', function () {
        $html = <<<'HTML'
                <div>
                    <figcaption>First</figcaption>
                    <figcaption>Second</figcaption>
                </div>
            HTML;

        $this->assertOnlyOneFigcaption($html);
    })->throws(AssertionFailedError::class, 'within <div>');

    test('fails when multiple figures each have multiple figcaptions', function () {
        $html = <<<'HTML'
                <figure>
                    <figcaption>Figure 1 Caption 1</figcaption>
                    <figcaption>Figure 1 Caption 2</figcaption>
                    <img src="/photo1.jpg" alt="Photo 1" />
                </figure>
                <figure>
                    <figcaption>Figure 2 Caption 1</figcaption>
                    <figcaption>Figure 2 Caption 2</figcaption>
                    <img src="/photo2.jpg" alt="Photo 2" />
                </figure>
            HTML;

        $this->assertOnlyOneFigcaption($html);
    })->throws(AssertionFailedError::class, 'multiple <figcaption>');
});

describe('assertOnlyOneVisibleMain', function () {
    test('passes when there is only one main element', function () {
        $html = <<<'HTML'
                <header>Header</header>
                <main>I'm the main content!</main>
                <footer>Footer</footer>
            HTML;

        expect(fn () => $this->assertOnlyOneVisibleMain($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when there is no main element', function () {
        $html = <<<'HTML'
                <div>
                    <header>Header</header>
                    <div>Content without main</div>
                    <footer>Footer</footer>
                </div>
            HTML;

        expect(fn () => $this->assertOnlyOneVisibleMain($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when there are multiple main elements but only one is visible', function () {
        $html = <<<'HTML'
                <main>I'm the visible main content!</main>
                <main hidden>I'm hidden</main>
            HTML;

        expect(fn () => $this->assertOnlyOneVisibleMain($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when there are multiple main elements but all except one are hidden', function () {
        $html = <<<'HTML'
                <main hidden>Hidden first</main>
                <main>Visible main</main>
                <main hidden>Hidden third</main>
            HTML;

        expect(fn () => $this->assertOnlyOneVisibleMain($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when single main has id attribute', function () {
        $html = <<<'HTML'
                <main id="main-content">Content</main>
            HTML;

        expect(fn () => $this->assertOnlyOneVisibleMain($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when there are two visible main elements', function () {
        $html = <<<'HTML'
                <main>I'm the main content!</main>
                <main>No, It's me!</main>
            HTML;

        $this->assertOnlyOneVisibleMain($html);
    })->throws(AssertionFailedError::class, 'second visible main element');

    test('fails when there are three visible main elements', function () {
        $html = <<<'HTML'
                <main>First main</main>
                <main>Second main</main>
                <main>Third main</main>
            HTML;

        $this->assertOnlyOneVisibleMain($html);
    })->throws(AssertionFailedError::class, 'visible <main> elements');

    test('fails when second main has id attribute', function () {
        $html = <<<'HTML'
                <main>First main</main>
                <main id="secondary-main">Second main with id</main>
            HTML;

        $this->assertOnlyOneVisibleMain($html);
    })->throws(AssertionFailedError::class, 'id="secondary-main"');

    test('fails when second main has name attribute', function () {
        $html = <<<'HTML'
                <main>First main</main>
                <main name="content">Second main with name</main>
            HTML;

        $this->assertOnlyOneVisibleMain($html);
    })->throws(AssertionFailedError::class, 'name="content"');

    test('fails when multiple mains are visible after first', function () {
        $html = <<<'HTML'
                <main>First visible</main>
                <main>Second visible</main>
                <main>Third visible</main>
                <main>Fourth visible</main>
            HTML;

        $this->assertOnlyOneVisibleMain($html);
    })->throws(AssertionFailedError::class, 'multiple visible');
});

describe('assertIdAttributeNotEmpty', function () {
    test('passes when elements have valid id attributes', function () {
        $html = <<<'HTML'
                <div id="container">Content</div>
                <p id="main-paragraph">Paragraph</p>
                <span id="icon-1">Icon</span>
            HTML;

        expect(fn () => $this->assertIdAttributeNotEmpty($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when elements have no id attribute', function () {
        $html = <<<'HTML'
                <div>No id here</div>
                <p>Another element without id</p>
                <button type="button">Click me</button>
            HTML;

        expect(fn () => $this->assertIdAttributeNotEmpty($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when id has single character', function () {
        $html = <<<'HTML'
                <div id="a">Single character</div>
            HTML;

        expect(fn () => $this->assertIdAttributeNotEmpty($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when id has multiple characters', function () {
        $html = <<<'HTML'
                <div id="my-complex-id-123">Complex id</div>
            HTML;

        expect(fn () => $this->assertIdAttributeNotEmpty($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when element has empty id attribute', function () {
        $html = <<<'HTML'
                <i id="">Wait. Why is my ID empty?</i>
            HTML;

        $this->assertIdAttributeNotEmpty($html);
    })->throws(AssertionFailedError::class, 'has an empty id attribute');

    test('fails when element has whitespace-only id attribute', function () {
        $html = <<<'HTML'
                <div id=" ">Empty id</div>
            HTML;

        $this->assertIdAttributeNotEmpty($html);
    })->throws(AssertionFailedError::class, 'has an empty id attribute');

    test('fails when div has empty id attribute', function () {
        $html = <<<'HTML'
                <div id="">Content</div>
            HTML;

        $this->assertIdAttributeNotEmpty($html);
    })->throws(AssertionFailedError::class, 'has an empty id attribute');

    test('fails when span has empty id attribute', function () {
        $html = <<<'HTML'
                <span id="">Text</span>
            HTML;

        $this->assertIdAttributeNotEmpty($html);
    })->throws(AssertionFailedError::class, 'has an empty id attribute');

    test('fails when element with name has empty id attribute', function () {
        $html = <<<'HTML'
                <input type="text" name="username" id="" />
            HTML;

        $this->assertIdAttributeNotEmpty($html);
    })->throws(AssertionFailedError::class, 'name="username"');

    test('fails when button has empty id attribute', function () {
        $html = <<<'HTML'
                <button type="button" id="">Click me</button>
            HTML;

        $this->assertIdAttributeNotEmpty($html);
    })->throws(AssertionFailedError::class, 'has an empty id attribute');

    test('fails when form field has empty id attribute', function () {
        $html = <<<'HTML'
                <label for="">Email</label>
                <input type="email" id="" name="email" />
            HTML;

        $this->assertIdAttributeNotEmpty($html);
    })->throws(AssertionFailedError::class, 'empty id attribute');

    test('fails when multiple elements have empty id attributes', function () {
        $html = <<<'HTML'
                <div id="">First</div>
                <span id="">Second</span>
            HTML;

        $this->assertIdAttributeNotEmpty($html);
    })->throws(AssertionFailedError::class, 'empty id attribute');
});

describe('assertClassAttributeNotEmpty', function () {
    test('passes when elements have valid class attributes', function () {
        $html = <<<'HTML'
                <div class="container">Content</div>
                <p class="text-bold text-center">Paragraph</p>
                <span class="icon">Icon</span>
            HTML;

        expect(fn () => $this->assertClassAttributeNotEmpty($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when elements have no class attribute', function () {
        $html = <<<'HTML'
                <div>No class here</div>
                <p>Another element without class</p>
                <button type="button">Click me</button>
            HTML;

        expect(fn () => $this->assertClassAttributeNotEmpty($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when class has only one class name', function () {
        $html = <<<'HTML'
                <div class="single">Single class</div>
            HTML;

        expect(fn () => $this->assertClassAttributeNotEmpty($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when class has multiple class names', function () {
        $html = <<<'HTML'
                <div class="multiple classes here">Multiple classes</div>
            HTML;

        expect(fn () => $this->assertClassAttributeNotEmpty($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when element has empty class attribute', function () {
        $html = <<<'HTML'
                <b class="">Why is there a class here?</b>
            HTML;

        $this->assertClassAttributeNotEmpty($html);
    })->throws(AssertionFailedError::class, 'has an empty class attribute');

    test('fails when element has whitespace-only class attribute', function () {
        $html = <<<'HTML'
                <div class=" ">Empty class</div>
            HTML;

        $this->assertClassAttributeNotEmpty($html);
    })->throws(AssertionFailedError::class, 'has an empty class attribute');

    test('fails when div has empty class attribute', function () {
        $html = <<<'HTML'
                <div class="">Content</div>
            HTML;

        $this->assertClassAttributeNotEmpty($html);
    })->throws(AssertionFailedError::class, 'has an empty class attribute');

    test('fails when span has empty class attribute', function () {
        $html = <<<'HTML'
                <span class="">Text</span>
            HTML;

        $this->assertClassAttributeNotEmpty($html);
    })->throws(AssertionFailedError::class, 'has an empty class attribute');

    test('fails when element with id has empty class attribute', function () {
        $html = <<<'HTML'
                <p id="my-paragraph" class="">Paragraph</p>
            HTML;

        $this->assertClassAttributeNotEmpty($html);
    })->throws(AssertionFailedError::class, 'id="my-paragraph"');

    test('fails when button has empty class attribute', function () {
        $html = <<<'HTML'
                <button type="button" class="">Click me</button>
            HTML;

        $this->assertClassAttributeNotEmpty($html);
    })->throws(AssertionFailedError::class, 'has an empty class attribute');

    test('fails when multiple elements have empty class attributes', function () {
        $html = <<<'HTML'
                <div class="">First</div>
                <span class="">Second</span>
            HTML;

        $this->assertClassAttributeNotEmpty($html);
    })->throws(AssertionFailedError::class, 'empty class attribute');
});

describe('assertRequiredSelectStartsWithEmptyOption', function () {
    test('passes when required select starts with empty option value', function () {
        $html = <<<'HTML'
                <select name="slices" required>
                    <option value="">Choose an option</option>
                    <option value="1">Cheese</option>
                    <option value="2">Salami</option>
                </select>
            HTML;

        expect(fn () => $this->assertRequiredSelectStartsWithEmptyOption($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when required select starts with empty option without value attribute', function () {
        $html = <<<'HTML'
                <select name="slices" required>
                    <option></option>
                    <option value="1">Cheese</option>
                    <option value="2">Salami</option>
                </select>
            HTML;

        expect(fn () => $this->assertRequiredSelectStartsWithEmptyOption($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when required select with placeholder text has empty value', function () {
        $html = <<<'HTML'
                <select name="country" required>
                    <option value="">Select a country</option>
                    <option value="us">United States</option>
                    <option value="uk">United Kingdom</option>
                </select>
            HTML;

        expect(fn () => $this->assertRequiredSelectStartsWithEmptyOption($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when select is not required', function () {
        $html = <<<'HTML'
                <select name="slices">
                    <option value="1">Cheese</option>
                    <option value="2">Salami</option>
                </select>
            HTML;

        expect(fn () => $this->assertRequiredSelectStartsWithEmptyOption($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when select has multiple attribute', function () {
        $html = <<<'HTML'
                <select name="slices" required multiple>
                    <option value="1">Cheese</option>
                    <option value="2">Salami</option>
                </select>
            HTML;

        expect(fn () => $this->assertRequiredSelectStartsWithEmptyOption($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when select has size greater than 1', function () {
        $html = <<<'HTML'
                <select name="slices" required size="3">
                    <option value="1">Cheese</option>
                    <option value="2">Salami</option>
                    <option value="3">Pepperoni</option>
                </select>
            HTML;

        expect(fn () => $this->assertRequiredSelectStartsWithEmptyOption($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when required select has no options', function () {
        $html = <<<'HTML'
                <select name="empty" required></select>
            HTML;

        expect(fn () => $this->assertRequiredSelectStartsWithEmptyOption($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when required select starts with non-empty option', function () {
        $html = <<<'HTML'
                <select name="slices" required>
                    <option value="1">Cheese</option>
                    <option value="2">Salami</option>
                </select>
            HTML;

        $this->assertRequiredSelectStartsWithEmptyOption($html);
    })->throws(AssertionFailedError::class, 'should start with an empty <option>');

    test('fails when required select has size 1 and starts with non-empty option', function () {
        $html = <<<'HTML'
                <select name="slices" required size="1">
                    <option value="cheese">Cheese</option>
                    <option value="salami">Salami</option>
                </select>
            HTML;

        $this->assertRequiredSelectStartsWithEmptyOption($html);
    })->throws(AssertionFailedError::class, 'should start with an empty <option>');

    test('fails when required select first option has whitespace-only value', function () {
        $html = <<<'HTML'
                <select name="slices" required>
                    <option value=" ">Choose</option>
                    <option value="1">Cheese</option>
                </select>
            HTML;

        $this->assertRequiredSelectStartsWithEmptyOption($html);
    })->throws(AssertionFailedError::class, 'should start with an empty <option>');

    test('fails when required select with id starts with non-empty option', function () {
        $html = <<<'HTML'
                <select id="pizza-slices" name="slices" required>
                    <option value="1">Cheese</option>
                    <option value="2">Salami</option>
                </select>
            HTML;

        $this->assertRequiredSelectStartsWithEmptyOption($html);
    })->throws(AssertionFailedError::class, 'id="pizza-slices"');

    test('fails when required select first option has text content but no value attribute', function () {
        $html = <<<'HTML'
                <select name="slices" required>
                    <option>Cheese</option>
                    <option>Salami</option>
                </select>
            HTML;

        $this->assertRequiredSelectStartsWithEmptyOption($html);
    })->throws(AssertionFailedError::class, 'should start with an empty <option>');
});
