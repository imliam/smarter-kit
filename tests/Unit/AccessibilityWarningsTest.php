<?php

declare(strict_types=1);

use PHPUnit\Framework\AssertionFailedError;
use Tests\Concerns\Accessibility\ChecksAccessibilityWarnings;

uses(ChecksAccessibilityWarnings::class);

describe('assertListItemsHaveCorrectParent', function () {
    test('passes when ul contains only li elements', function () {
        $html = <<<'HTML'
                <ul>
                    <li>Item 1</li>
                    <li>Item 2</li>
                    <li>Item 3</li>
                </ul>
            HTML;

        expect(fn () => $this->assertListItemsHaveCorrectParent($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when ol contains only li elements', function () {
        $html = <<<'HTML'
                <ol>
                    <li>First</li>
                    <li>Second</li>
                    <li>Third</li>
                </ol>
            HTML;

        expect(fn () => $this->assertListItemsHaveCorrectParent($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when li is direct child of ul', function () {
        $html = <<<'HTML'
                <ul>
                    <li>Valid list item</li>
                </ul>
            HTML;

        expect(fn () => $this->assertListItemsHaveCorrectParent($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when li is direct child of ol', function () {
        $html = <<<'HTML'
                <ol>
                    <li>Valid list item</li>
                </ol>
            HTML;

        expect(fn () => $this->assertListItemsHaveCorrectParent($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when nested ul contains li elements', function () {
        $html = <<<'HTML'
                <ul>
                    <li>Parent item
                        <ul>
                            <li>Nested item 1</li>
                            <li>Nested item 2</li>
                        </ul>
                    </li>
                </ul>
            HTML;

        expect(fn () => $this->assertListItemsHaveCorrectParent($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when no list elements are present', function () {
        $html = <<<'HTML'
                <div>
                    <p>No lists here</p>
                </div>
            HTML;

        expect(fn () => $this->assertListItemsHaveCorrectParent($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when ul contains non-li child element', function () {
        $html = <<<'HTML'
                <ul>
                    <p>I feel like I'm lost.</p>
                </ul>
            HTML;

        $this->assertListItemsHaveCorrectParent($html);
    })->throws(AssertionFailedError::class, 'not allowed as a child');

    test('fails when ul contains div element', function () {
        $html = <<<'HTML'
                <ul>
                    <div>Invalid child</div>
                </ul>
            HTML;

        $this->assertListItemsHaveCorrectParent($html);
    })->throws(AssertionFailedError::class, 'not allowed as a child');

    test('fails when ol contains span element', function () {
        $html = <<<'HTML'
                <ol>
                    <span>Not a list item</span>
                </ol>
            HTML;

        $this->assertListItemsHaveCorrectParent($html);
    })->throws(AssertionFailedError::class, 'not allowed as a child');

    test('fails when li is child of div', function () {
        $html = <<<'HTML'
                <div>
                    <li>I feel like I'm lost.</li>
                </div>
            HTML;

        $this->assertListItemsHaveCorrectParent($html);
    })->throws(AssertionFailedError::class, 'must be a child of <ul> or <ol>');

    test('fails when li is child of section', function () {
        $html = <<<'HTML'
                <section>
                    <li>Orphaned list item</li>
                </section>
            HTML;

        $this->assertListItemsHaveCorrectParent($html);
    })->throws(AssertionFailedError::class, 'must be a child of <ul> or <ol>');

    test('fails when li is child of article', function () {
        $html = <<<'HTML'
                <article>
                    <li>Misplaced item</li>
                </article>
            HTML;

        $this->assertListItemsHaveCorrectParent($html);
    })->throws(AssertionFailedError::class, 'must be a child of <ul> or <ol>');

    test('fails when li with id is orphaned', function () {
        $html = <<<'HTML'
                <div>
                    <li id="orphaned-item">Lost item</li>
                </div>
            HTML;

        $this->assertListItemsHaveCorrectParent($html);
    })->throws(AssertionFailedError::class, 'id="orphaned-item"');

    test('fails when li with name is orphaned', function () {
        $html = <<<'HTML'
                <div>
                    <li name="item">Lost item</li>
                </div>
            HTML;

        $this->assertListItemsHaveCorrectParent($html);
    })->throws(AssertionFailedError::class, 'name="item"');

    test('fails when ul contains text node', function () {
        $html = <<<'HTML'
                <ul>
                    <li>Valid item</li>
                    <strong>Invalid text</strong>
                </ul>
            HTML;

        $this->assertListItemsHaveCorrectParent($html);
    })->throws(AssertionFailedError::class, 'not allowed as a child');

    test('fails when element with id is invalid child of ul', function () {
        $html = <<<'HTML'
                <ul>
                    <p id="invalid-child">Invalid paragraph</p>
                </ul>
            HTML;

        $this->assertListItemsHaveCorrectParent($html);
    })->throws(AssertionFailedError::class, 'id="invalid-child"');

    test('fails when multiple invalid children are in ul', function () {
        $html = <<<'HTML'
                <ul>
                    <li>Valid item</li>
                    <p>Invalid paragraph</p>
                    <div>Invalid div</div>
                </ul>
            HTML;

        $this->assertListItemsHaveCorrectParent($html);
    })->throws(AssertionFailedError::class, 'incorrect parent elements');

    test('fails when multiple li elements are orphaned', function () {
        $html = <<<'HTML'
                <div>
                    <li>First orphan</li>
                    <li>Second orphan</li>
                </div>
            HTML;

        $this->assertListItemsHaveCorrectParent($html);
    })->throws(AssertionFailedError::class, 'incorrect parent elements');
});

describe('assertDefinitionListStructureIsValid', function () {
    test('passes when dl has valid dt and dd structure', function () {
        $html = <<<'HTML'
                <dl>
                    <dt>Term 1</dt>
                    <dd>Definition 1</dd>
                    <dt>Term 2</dt>
                    <dd>Definition 2</dd>
                </dl>
            HTML;

        expect(fn () => $this->assertDefinitionListStructureIsValid($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when dt has multiple dd elements', function () {
        $html = <<<'HTML'
                <dl>
                    <dt>Term</dt>
                    <dd>First definition</dd>
                    <dd>Second definition</dd>
                    <dd>Third definition</dd>
                </dl>
            HTML;

        expect(fn () => $this->assertDefinitionListStructureIsValid($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when dl has multiple dt-dd pairs', function () {
        $html = <<<'HTML'
                <dl>
                    <dt>HTML</dt>
                    <dd>HyperText Markup Language</dd>
                    <dt>CSS</dt>
                    <dd>Cascading Style Sheets</dd>
                    <dt>JS</dt>
                    <dd>JavaScript</dd>
                </dl>
            HTML;

        expect(fn () => $this->assertDefinitionListStructureIsValid($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when dl contains div wrappers', function () {
        $html = <<<'HTML'
                <dl>
                    <div>
                        <dt>Term</dt>
                        <dd>Definition</dd>
                    </div>
                </dl>
            HTML;

        expect(fn () => $this->assertDefinitionListStructureIsValid($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when there are no definition lists', function () {
        $html = <<<'HTML'
                <div>
                    <p>No definition lists here</p>
                </div>
            HTML;

        expect(fn () => $this->assertDefinitionListStructureIsValid($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when empty dl', function () {
        $html = <<<'HTML'
                <dl></dl>
            HTML;

        expect(fn () => $this->assertDefinitionListStructureIsValid($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when dt is followed by non-dd element', function () {
        $html = <<<'HTML'
                <dl>
                    <dt>I need a definition, don't you think?</dt>
                    <li>I'm a list item.</li>
                </dl>
            HTML;

        $this->assertDefinitionListStructureIsValid($html);
    })->throws(AssertionFailedError::class, 'must be followed by <dd>');

    test('fails when dt is followed by another dt', function () {
        $html = <<<'HTML'
                <dl>
                    <dt>First term</dt>
                    <dt>Second term without definition</dt>
                    <dd>Definition</dd>
                </dl>
            HTML;

        $this->assertDefinitionListStructureIsValid($html);
    })->throws(AssertionFailedError::class, 'must be followed by <dd>');

    test('fails when dt is followed by paragraph', function () {
        $html = <<<'HTML'
                <dl>
                    <dt>Term</dt>
                    <p>Not a definition</p>
                </dl>
            HTML;

        $this->assertDefinitionListStructureIsValid($html);
    })->throws(AssertionFailedError::class, 'must be followed by <dd>');

    test('fails when dt is followed by div', function () {
        $html = <<<'HTML'
                <dl>
                    <dt>Term</dt>
                    <div>Not a definition</div>
                </dl>
            HTML;

        $this->assertDefinitionListStructureIsValid($html);
    })->throws(AssertionFailedError::class, 'must be followed by <dd>');

    test('fails when dd is preceded by non-dt/dd element', function () {
        $html = <<<'HTML'
                <dl>
                    <p>Paragraph</p>
                    <dd>Definition without term</dd>
                </dl>
            HTML;

        $this->assertDefinitionListStructureIsValid($html);
    })->throws(AssertionFailedError::class, 'must be preceded by <dt> or <dd>');

    test('fails when dd is preceded by span', function () {
        $html = <<<'HTML'
                <dl>
                    <span>Not a term</span>
                    <dd>Definition</dd>
                </dl>
            HTML;

        $this->assertDefinitionListStructureIsValid($html);
    })->throws(AssertionFailedError::class, 'must be preceded by <dt> or <dd>');

    test('fails when dl contains ul element', function () {
        $html = <<<'HTML'
                <dl>
                    <dt>Term</dt>
                    <dd>Definition</dd>
                    <ul>
                        <li>List item</li>
                    </ul>
                </dl>
            HTML;

        $this->assertDefinitionListStructureIsValid($html);
    })->throws(AssertionFailedError::class, 'not allowed as a child of <dl>');

    test('fails when dl contains paragraph', function () {
        $html = <<<'HTML'
                <dl>
                    <dt>Term</dt>
                    <dd>Definition</dd>
                    <p>Random paragraph</p>
                </dl>
            HTML;

        $this->assertDefinitionListStructureIsValid($html);
    })->throws(AssertionFailedError::class, 'not allowed as a child of <dl>');

    test('fails when dl contains section', function () {
        $html = <<<'HTML'
                <dl>
                    <section>Invalid section</section>
                </dl>
            HTML;

        $this->assertDefinitionListStructureIsValid($html);
    })->throws(AssertionFailedError::class, 'not allowed as a child of <dl>');

    test('fails when invalid element with id is in dl', function () {
        $html = <<<'HTML'
                <dl>
                    <dt>Term</dt>
                    <dd>Definition</dd>
                    <article id="invalid-article">Article</article>
                </dl>
            HTML;

        $this->assertDefinitionListStructureIsValid($html);
    })->throws(AssertionFailedError::class, 'id="invalid-article"');

    test('fails when dd with id is preceded by invalid element', function () {
        $html = <<<'HTML'
                <dl>
                    <span>Not a term</span>
                    <dd id="orphaned-definition">Definition</dd>
                </dl>
            HTML;

        $this->assertDefinitionListStructureIsValid($html);
    })->throws(AssertionFailedError::class, 'id="orphaned-definition"');

    test('fails when multiple violations exist', function () {
        $html = <<<'HTML'
                <dl>
                    <dt>Term 1</dt>
                    <p>Not a definition</p>
                    <dt>Term 2</dt>
                    <span>Also not a definition</span>
                    <dd>Definition</dd>
                </dl>
            HTML;

        $this->assertDefinitionListStructureIsValid($html);
    })->throws(AssertionFailedError::class, 'invalid structure');

    test('fails when dt followed by li with message', function () {
        $html = <<<'HTML'
                <dl>
                    <dt>I need a definition, don't you think?</dt>
                    <li>I'm a list item.</li>
                </dl>
            HTML;

        $this->assertDefinitionListStructureIsValid($html);
    })->throws(AssertionFailedError::class, 'found <li>');
});

describe('assertDefinitionListChildrenAreValid', function () {
    test('passes when dt and dd are direct children of dl', function () {
        $html = <<<'HTML'
                <dl>
                    <dt>Term</dt>
                    <dd>Definition</dd>
                </dl>
            HTML;

        expect(fn () => $this->assertDefinitionListChildrenAreValid($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when dl contains only dt, dd, and div', function () {
        $html = <<<'HTML'
                <dl>
                    <div>
                        <dt>Term</dt>
                        <dd>Definition</dd>
                    </div>
                </dl>
            HTML;

        expect(fn () => $this->assertDefinitionListChildrenAreValid($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when multiple dt and dd are direct children', function () {
        $html = <<<'HTML'
                <dl>
                    <dt>Term 1</dt>
                    <dd>Definition 1</dd>
                    <dt>Term 2</dt>
                    <dd>Definition 2</dd>
                    <dt>Term 3</dt>
                    <dd>Definition 3</dd>
                </dl>
            HTML;

        expect(fn () => $this->assertDefinitionListChildrenAreValid($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when dl contains div wrappers', function () {
        $html = <<<'HTML'
                <dl>
                    <div>
                        <dt>Wrapped term</dt>
                        <dd>Wrapped definition</dd>
                    </div>
                </dl>
            HTML;

        expect(fn () => $this->assertDefinitionListChildrenAreValid($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when there are no definition lists', function () {
        $html = <<<'HTML'
                <div>
                    <p>No dl elements here</p>
                </div>
            HTML;

        expect(fn () => $this->assertDefinitionListChildrenAreValid($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when dt and dd have no parent', function () {
        $html = <<<'HTML'
                <div>
                    <p>Just regular content</p>
                </div>
            HTML;

        expect(fn () => $this->assertDefinitionListChildrenAreValid($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when dl contains li element', function () {
        $html = <<<'HTML'
                <dl>
                    <li>I'm a list item.</li>
                </dl>
            HTML;

        $this->assertDefinitionListChildrenAreValid($html);
    })->throws(AssertionFailedError::class, 'not allowed as a direct child of <dl>');

    test('fails when dl contains paragraph', function () {
        $html = <<<'HTML'
                <dl>
                    <dt>Term</dt>
                    <dd>Definition</dd>
                    <p>Invalid paragraph</p>
                </dl>
            HTML;

        $this->assertDefinitionListChildrenAreValid($html);
    })->throws(AssertionFailedError::class, 'not allowed as a direct child of <dl>');

    test('fails when dl contains span', function () {
        $html = <<<'HTML'
                <dl>
                    <span>Invalid span</span>
                </dl>
            HTML;

        $this->assertDefinitionListChildrenAreValid($html);
    })->throws(AssertionFailedError::class, 'not allowed as a direct child of <dl>');

    test('fails when dl contains article', function () {
        $html = <<<'HTML'
                <dl>
                    <article>Invalid article</article>
                </dl>
            HTML;

        $this->assertDefinitionListChildrenAreValid($html);
    })->throws(AssertionFailedError::class, 'not allowed as a direct child of <dl>');

    test('fails when dl contains ul', function () {
        $html = <<<'HTML'
                <dl>
                    <ul>
                        <li>List item</li>
                    </ul>
                </dl>
            HTML;

        $this->assertDefinitionListChildrenAreValid($html);
    })->throws(AssertionFailedError::class, 'not allowed as a direct child of <dl>');

    test('fails when dt is child of div outside dl', function () {
        $html = <<<'HTML'
                <div>
                    <dt>Orphaned term</dt>
                </div>
            HTML;

        $this->assertDefinitionListChildrenAreValid($html);
    })->throws(AssertionFailedError::class, 'must be a child of <dl>');

    test('fails when dt is child of section', function () {
        $html = <<<'HTML'
                <section>
                    <dt>Term in section</dt>
                </section>
            HTML;

        $this->assertDefinitionListChildrenAreValid($html);
    })->throws(AssertionFailedError::class, 'must be a child of <dl>');

    test('fails when dd is child of div outside dl', function () {
        $html = <<<'HTML'
                <div>
                    <dd>Orphaned definition</dd>
                </div>
            HTML;

        $this->assertDefinitionListChildrenAreValid($html);
    })->throws(AssertionFailedError::class, 'must be a child of <dl>');

    test('fails when dd is child of article', function () {
        $html = <<<'HTML'
                <article>
                    <dd>Definition in article</dd>
                </article>
            HTML;

        $this->assertDefinitionListChildrenAreValid($html);
    })->throws(AssertionFailedError::class, 'must be a child of <dl>');

    test('fails when dt with id is orphaned', function () {
        $html = <<<'HTML'
                <section>
                    <dt id="orphaned-term">Orphaned term</dt>
                </section>
            HTML;

        $this->assertDefinitionListChildrenAreValid($html);
    })->throws(AssertionFailedError::class, 'id="orphaned-term"');

    test('fails when dd with id is orphaned', function () {
        $html = <<<'HTML'
                <div>
                    <dd id="orphaned-def">Orphaned definition</dd>
                </div>
            HTML;

        $this->assertDefinitionListChildrenAreValid($html);
    })->throws(AssertionFailedError::class, 'id="orphaned-def"');

    test('fails when invalid element with id is in dl', function () {
        $html = <<<'HTML'
                <dl>
                    <span id="invalid-span">Invalid</span>
                </dl>
            HTML;

        $this->assertDefinitionListChildrenAreValid($html);
    })->throws(AssertionFailedError::class, 'id="invalid-span"');

    test('fails when dt with name is orphaned', function () {
        $html = <<<'HTML'
                <div>
                    <dt name="term">Orphaned</dt>
                </div>
            HTML;

        $this->assertDefinitionListChildrenAreValid($html);
    })->throws(AssertionFailedError::class, 'name="term"');

    test('fails when multiple violations exist', function () {
        $html = <<<'HTML'
                <dl>
                    <li>Invalid list item</li>
                    <p>Invalid paragraph</p>
                </dl>
                <div>
                    <dt>Orphaned term</dt>
                    <dd>Orphaned definition</dd>
                </div>
            HTML;

        $this->assertDefinitionListChildrenAreValid($html);
    })->throws(AssertionFailedError::class, 'invalid nesting');

    test('fails when li is direct child of dl with message', function () {
        $html = <<<'HTML'
                <dl>
                    <li>I'm a list item.</li>
                </dl>
            HTML;

        $this->assertDefinitionListChildrenAreValid($html);
    })->throws(AssertionFailedError::class, '<li>');
});

describe('assertFigcaptionIsInsideFigure', function () {
    test('passes when figcaption is inside figure', function () {
        $html = <<<'HTML'
                <figure>
                    <img src="/image.jpg" alt="Image">
                    <figcaption>Image caption</figcaption>
                </figure>
            HTML;

        expect(fn () => $this->assertFigcaptionIsInsideFigure($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when figcaption is first child of figure', function () {
        $html = <<<'HTML'
                <figure>
                    <figcaption>Caption first</figcaption>
                    <img src="/image.jpg" alt="Image">
                </figure>
            HTML;

        expect(fn () => $this->assertFigcaptionIsInsideFigure($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when figcaption is last child of figure', function () {
        $html = <<<'HTML'
                <figure>
                    <img src="/image.jpg" alt="Image">
                    <figcaption>Caption last</figcaption>
                </figure>
            HTML;

        expect(fn () => $this->assertFigcaptionIsInsideFigure($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when multiple figures each have figcaption', function () {
        $html = <<<'HTML'
                <figure>
                    <img src="/image1.jpg" alt="Image 1">
                    <figcaption>Caption 1</figcaption>
                </figure>
                <figure>
                    <img src="/image2.jpg" alt="Image 2">
                    <figcaption>Caption 2</figcaption>
                </figure>
            HTML;

        expect(fn () => $this->assertFigcaptionIsInsideFigure($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when figure contains only figcaption', function () {
        $html = <<<'HTML'
                <figure>
                    <figcaption>Just a caption</figcaption>
                </figure>
            HTML;

        expect(fn () => $this->assertFigcaptionIsInsideFigure($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when there are no figcaption elements', function () {
        $html = <<<'HTML'
                <div>
                    <p>No figcaptions here</p>
                </div>
            HTML;

        expect(fn () => $this->assertFigcaptionIsInsideFigure($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when figure has no figcaption', function () {
        $html = <<<'HTML'
                <figure>
                    <img src="/image.jpg" alt="Image">
                </figure>
            HTML;

        expect(fn () => $this->assertFigcaptionIsInsideFigure($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when figcaption is standalone', function () {
        $html = <<<'HTML'
                <figcaption>I'm captionning something, isn't it?</figcaption>
            HTML;

        $this->assertFigcaptionIsInsideFigure($html);
    })->throws(AssertionFailedError::class, 'must be inside a <figure>');

    test('fails when figcaption is inside div', function () {
        $html = <<<'HTML'
                <div>
                    <figcaption>Caption in wrong place</figcaption>
                </div>
            HTML;

        $this->assertFigcaptionIsInsideFigure($html);
    })->throws(AssertionFailedError::class, 'must be inside a <figure>');

    test('fails when figcaption is inside section', function () {
        $html = <<<'HTML'
                <section>
                    <figcaption>Caption in section</figcaption>
                </section>
            HTML;

        $this->assertFigcaptionIsInsideFigure($html);
    })->throws(AssertionFailedError::class, 'must be inside a <figure>');

    test('fails when figcaption is inside article', function () {
        $html = <<<'HTML'
                <article>
                    <figcaption>Caption in article</figcaption>
                </article>
            HTML;

        $this->assertFigcaptionIsInsideFigure($html);
    })->throws(AssertionFailedError::class, 'must be inside a <figure>');

    test('fails when figcaption is inside aside', function () {
        $html = <<<'HTML'
                <aside>
                    <figcaption>Caption in aside</figcaption>
                </aside>
            HTML;

        $this->assertFigcaptionIsInsideFigure($html);
    })->throws(AssertionFailedError::class, 'must be inside a <figure>');

    test('fails when figcaption is inside main', function () {
        $html = <<<'HTML'
                <main>
                    <figcaption>Caption in main</figcaption>
                </main>
            HTML;

        $this->assertFigcaptionIsInsideFigure($html);
    })->throws(AssertionFailedError::class, 'must be inside a <figure>');

    test('fails when figcaption with id is outside figure', function () {
        $html = <<<'HTML'
                <div>
                    <figcaption id="orphaned-caption">Orphaned caption</figcaption>
                </div>
            HTML;

        $this->assertFigcaptionIsInsideFigure($html);
    })->throws(AssertionFailedError::class, 'id="orphaned-caption"');

    test('fails when figcaption with name is outside figure', function () {
        $html = <<<'HTML'
                <div>
                    <figcaption name="caption">Orphaned caption</figcaption>
                </div>
            HTML;

        $this->assertFigcaptionIsInsideFigure($html);
    })->throws(AssertionFailedError::class, 'name="caption"');

    test('fails when figcaption is inside paragraph', function () {
        $html = <<<'HTML'
                <p>
                    <figcaption>Caption in paragraph</figcaption>
                </p>
            HTML;

        $this->assertFigcaptionIsInsideFigure($html);
    })->throws(AssertionFailedError::class, 'must be inside a <figure>');

    test('fails when multiple figcaptions are outside figure', function () {
        $html = <<<'HTML'
                <div>
                    <figcaption>First orphaned caption</figcaption>
                    <figcaption>Second orphaned caption</figcaption>
                </div>
            HTML;

        $this->assertFigcaptionIsInsideFigure($html);
    })->throws(AssertionFailedError::class, 'outside <figure>');

    test('fails when figcaption is outside figure with valid figure present', function () {
        $html = <<<'HTML'
                <figure>
                    <img src="/image.jpg" alt="Image">
                    <figcaption>Valid caption</figcaption>
                </figure>
                <div>
                    <figcaption>Invalid caption</figcaption>
                </div>
            HTML;

        $this->assertFigcaptionIsInsideFigure($html);
    })->throws(AssertionFailedError::class, 'must be inside a <figure>');

    test('fails when figcaption is at document root', function () {
        $html = <<<'HTML'
                <figcaption>I'm captionning something, isn't it?</figcaption>
            HTML;

        $this->assertFigcaptionIsInsideFigure($html);
    })->throws(AssertionFailedError::class, 'outside <figure>');
});

describe('assertNoInvalidNesting', function () {
    test('passes when main is at document root', function () {
        $html = <<<'HTML'
                <main>
                    <h1>Main content</h1>
                </main>
            HTML;

        expect(fn () => $this->assertNoInvalidNesting($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when main is in body', function () {
        $html = <<<'HTML'
                <body>
                    <main>
                        <h1>Main content</h1>
                    </main>
                </body>
            HTML;

        expect(fn () => $this->assertNoInvalidNesting($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when td and th are in tr', function () {
        $html = <<<'HTML'
                <table>
                    <tr>
                        <th>Header</th>
                        <td>Data</td>
                    </tr>
                </table>
            HTML;

        expect(fn () => $this->assertNoInvalidNesting($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when col is in colgroup', function () {
        $html = <<<'HTML'
                <table>
                    <colgroup>
                        <col>
                        <col>
                    </colgroup>
                </table>
            HTML;

        expect(fn () => $this->assertNoInvalidNesting($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when legend is in fieldset', function () {
        $html = <<<'HTML'
                <fieldset>
                    <legend>Form section</legend>
                </fieldset>
            HTML;

        expect(fn () => $this->assertNoInvalidNesting($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when option is in select', function () {
        $html = <<<'HTML'
                <select>
                    <option>Option 1</option>
                    <option>Option 2</option>
                </select>
            HTML;

        expect(fn () => $this->assertNoInvalidNesting($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when option is in optgroup', function () {
        $html = <<<'HTML'
                <select>
                    <optgroup label="Group">
                        <option>Option 1</option>
                        <option>Option 2</option>
                    </optgroup>
                </select>
            HTML;

        expect(fn () => $this->assertNoInvalidNesting($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when optgroup is in select', function () {
        $html = <<<'HTML'
                <select>
                    <optgroup label="Group 1">
                        <option>Option 1</option>
                    </optgroup>
                    <optgroup label="Group 2">
                        <option>Option 2</option>
                    </optgroup>
                </select>
            HTML;

        expect(fn () => $this->assertNoInvalidNesting($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when table has valid children', function () {
        $html = <<<'HTML'
                <table>
                    <caption>Table caption</caption>
                    <colgroup>
                        <col>
                    </colgroup>
                    <thead>
                        <tr><th>Header</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>Data</td></tr>
                    </tbody>
                    <tfoot>
                        <tr><td>Footer</td></tr>
                    </tfoot>
                </table>
            HTML;

        expect(fn () => $this->assertNoInvalidNesting($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when address contains valid content', function () {
        $html = <<<'HTML'
                <address>
                    <p>Contact information</p>
                    <a href="mailto:test@example.com">Email</a>
                </address>
            HTML;

        expect(fn () => $this->assertNoInvalidNesting($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when no invalid nesting exists', function () {
        $html = <<<'HTML'
                <div>
                    <p>Regular content</p>
                </div>
            HTML;

        expect(fn () => $this->assertNoInvalidNesting($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when main is inside nav', function () {
        $html = <<<'HTML'
                <nav>
                    <main>Content</main>
                </nav>
            HTML;

        $this->assertNoInvalidNesting($html);
    })->throws(AssertionFailedError::class, '<main> must not be contained within <nav>');

    test('fails when main is inside aside', function () {
        $html = <<<'HTML'
                <aside>
                    <main>Content</main>
                </aside>
            HTML;

        $this->assertNoInvalidNesting($html);
    })->throws(AssertionFailedError::class, '<main> must not be contained within <aside>');

    test('fails when main is inside footer', function () {
        $html = <<<'HTML'
                <footer>
                    <main>Content</main>
                </footer>
            HTML;

        $this->assertNoInvalidNesting($html);
    })->throws(AssertionFailedError::class, '<main> must not be contained within <footer>');

    test('fails when main is inside header', function () {
        $html = <<<'HTML'
                <header>
                    <main>Content</main>
                </header>
            HTML;

        $this->assertNoInvalidNesting($html);
    })->throws(AssertionFailedError::class, '<main> must not be contained within <header>');

    test('fails when main is inside article', function () {
        $html = <<<'HTML'
                <article>
                    <main>Content</main>
                </article>
            HTML;

        $this->assertNoInvalidNesting($html);
    })->throws(AssertionFailedError::class, '<main> must not be contained within <article>');

    test('fails when optgroup is outside select', function () {
        $html = <<<'HTML'
                <div>
                    <optgroup>
                        <option>Option 1</option>
                    </optgroup>
                </div>
            HTML;

        $this->assertNoInvalidNesting($html);
    })->throws(AssertionFailedError::class, '<optgroup> must be inside a <select>');

    test('fails when legend is outside fieldset', function () {
        $html = <<<'HTML'
                <legend>I'm an legend. Am I?</legend>
            HTML;

        $this->assertNoInvalidNesting($html);
    })->throws(AssertionFailedError::class, '<legend> must be inside a <fieldset>');

    test('fails when legend is in div', function () {
        $html = <<<'HTML'
                <div>
                    <legend>Lost legend</legend>
                </div>
            HTML;

        $this->assertNoInvalidNesting($html);
    })->throws(AssertionFailedError::class, '<legend> must be inside a <fieldset>');

    test('fails when option is outside select or optgroup', function () {
        $html = <<<'HTML'
                <div>
                    <option>Option 1</option>
                </div>
            HTML;

        $this->assertNoInvalidNesting($html);
    })->throws(AssertionFailedError::class, '<option> must be inside a <select> or <optgroup>');

    test('fails when address contains h1', function () {
        $html = <<<'HTML'
                <address>
                    <h1>Invalid heading</h1>
                </address>
            HTML;

        $this->assertNoInvalidNesting($html);
    })->throws(AssertionFailedError::class, '<h1> is not allowed inside <address>');

    test('fails when address contains h2', function () {
        $html = <<<'HTML'
                <address>
                    <h2>Invalid heading</h2>
                </address>
            HTML;

        $this->assertNoInvalidNesting($html);
    })->throws(AssertionFailedError::class, '<h2> is not allowed inside <address>');

    test('fails when address contains h3', function () {
        $html = <<<'HTML'
                <address>
                    <h3>Invalid heading</h3>
                </address>
            HTML;

        $this->assertNoInvalidNesting($html);
    })->throws(AssertionFailedError::class, '<h3> is not allowed inside <address>');

    test('fails when address contains h4', function () {
        $html = <<<'HTML'
                <address>
                    <h4>Invalid heading</h4>
                </address>
            HTML;

        $this->assertNoInvalidNesting($html);
    })->throws(AssertionFailedError::class, '<h4> is not allowed inside <address>');

    test('fails when address contains h5', function () {
        $html = <<<'HTML'
                <address>
                    <h5>Invalid heading</h5>
                </address>
            HTML;

        $this->assertNoInvalidNesting($html);
    })->throws(AssertionFailedError::class, '<h5> is not allowed inside <address>');

    test('fails when address contains h6', function () {
        $html = <<<'HTML'
                <address>
                    <h6>Invalid heading</h6>
                </address>
            HTML;

        $this->assertNoInvalidNesting($html);
    })->throws(AssertionFailedError::class, '<h6> is not allowed inside <address>');

    test('fails when address contains nav', function () {
        $html = <<<'HTML'
                <address>
                    <nav>Invalid navigation</nav>
                </address>
            HTML;

        $this->assertNoInvalidNesting($html);
    })->throws(AssertionFailedError::class, '<nav> is not allowed inside <address>');

    test('fails when address contains aside', function () {
        $html = <<<'HTML'
                <address>
                    <aside>Invalid aside</aside>
                </address>
            HTML;

        $this->assertNoInvalidNesting($html);
    })->throws(AssertionFailedError::class, '<aside> is not allowed inside <address>');

    test('fails when address contains header', function () {
        $html = <<<'HTML'
                <address>
                    <header>Invalid header</header>
                </address>
            HTML;

        $this->assertNoInvalidNesting($html);
    })->throws(AssertionFailedError::class, '<header> is not allowed inside <address>');

    test('fails when address contains footer', function () {
        $html = <<<'HTML'
                <address>
                    <footer>Invalid footer</footer>
                </address>
            HTML;

        $this->assertNoInvalidNesting($html);
    })->throws(AssertionFailedError::class, '<footer> is not allowed inside <address>');

    test('fails when address contains address', function () {
        $html = <<<'HTML'
                <address>
                    <address>Nested address</address>
                </address>
            HTML;

        $this->assertNoInvalidNesting($html);
    })->throws(AssertionFailedError::class, '<address> is not allowed inside <address>');

    test('fails when address contains article', function () {
        $html = <<<'HTML'
                <address>
                    <article>Invalid article</article>
                </address>
            HTML;

        $this->assertNoInvalidNesting($html);
    })->throws(AssertionFailedError::class, '<article> is not allowed inside <address>');

    test('fails when address contains section', function () {
        $html = <<<'HTML'
                <address>
                    <section>Invalid section</section>
                </address>
            HTML;

        $this->assertNoInvalidNesting($html);
    })->throws(AssertionFailedError::class, '<section> is not allowed inside <address>');

    test('fails when element with id has invalid nesting', function () {
        $html = <<<'HTML'
                <legend id="orphaned-legend">Lost legend</legend>
            HTML;

        $this->assertNoInvalidNesting($html);
    })->throws(AssertionFailedError::class, 'id="orphaned-legend"');

    test('fails when element with name has invalid nesting', function () {
        $html = <<<'HTML'
                <legend name="legend">Lost legend</legend>
            HTML;

        $this->assertNoInvalidNesting($html);
    })->throws(AssertionFailedError::class, 'name="legend"');

    test('fails when multiple violations exist', function () {
        $html = <<<'HTML'
                <legend>Lost legend</legend>
                <nav>
                    <main>Main in nav</main>
                </nav>
                <address>
                    <h1>Invalid heading</h1>
                </address>
            HTML;

        $this->assertNoInvalidNesting($html);
    })->throws(AssertionFailedError::class, 'invalid HTML element nesting');
});

describe('assertNoMisplacedDiv', function () {
    test('passes when div is not inside inline elements', function () {
        $html = <<<'HTML'
                <div>
                    <p>Regular content</p>
                </div>
            HTML;

        expect(fn () => $this->assertNoMisplacedDiv($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when span is used inside inline elements', function () {
        $html = <<<'HTML'
                <b><span>Correct usage</span></b>
            HTML;

        expect(fn () => $this->assertNoMisplacedDiv($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when div contains inline elements', function () {
        $html = <<<'HTML'
                <div>
                    <b>Bold text</b>
                    <i>Italic text</i>
                    <span>Span text</span>
                </div>
            HTML;

        expect(fn () => $this->assertNoMisplacedDiv($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when div is inside block elements', function () {
        $html = <<<'HTML'
                <section>
                    <div>Content</div>
                </section>
            HTML;

        expect(fn () => $this->assertNoMisplacedDiv($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when div is at document root', function () {
        $html = <<<'HTML'
                <div>Root content</div>
            HTML;

        expect(fn () => $this->assertNoMisplacedDiv($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when no div elements exist', function () {
        $html = <<<'HTML'
                <p>Just a paragraph</p>
            HTML;

        expect(fn () => $this->assertNoMisplacedDiv($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when inline elements are used correctly', function () {
        $html = <<<'HTML'
                <p>
                    <b>Bold</b> and <i>italic</i> and <span>span</span>
                </p>
            HTML;

        expect(fn () => $this->assertNoMisplacedDiv($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when div is inside b element', function () {
        $html = <<<'HTML'
                <b><div>Hey ya!</div></b>
            HTML;

        $this->assertNoMisplacedDiv($html);
    })->throws(AssertionFailedError::class, '<div> should not be inside <b>');

    test('fails when div is inside i element', function () {
        $html = <<<'HTML'
                <i><div>Italic block</div></i>
            HTML;

        $this->assertNoMisplacedDiv($html);
    })->throws(AssertionFailedError::class, '<div> should not be inside <i>');

    test('fails when div is inside q element', function () {
        $html = <<<'HTML'
                <q><div>Quoted block</div></q>
            HTML;

        $this->assertNoMisplacedDiv($html);
    })->throws(AssertionFailedError::class, '<div> should not be inside <q>');

    test('fails when div is inside em element', function () {
        $html = <<<'HTML'
                <em><div>Emphasized block</div></em>
            HTML;

        $this->assertNoMisplacedDiv($html);
    })->throws(AssertionFailedError::class, '<div> should not be inside <em>');

    test('fails when div is inside abbr element', function () {
        $html = <<<'HTML'
                <abbr><div>Abbreviated block</div></abbr>
            HTML;

        $this->assertNoMisplacedDiv($html);
    })->throws(AssertionFailedError::class, '<div> should not be inside <abbr>');

    test('fails when div is inside cite element', function () {
        $html = <<<'HTML'
                <cite><div>Citation block</div></cite>
            HTML;

        $this->assertNoMisplacedDiv($html);
    })->throws(AssertionFailedError::class, '<div> should not be inside <cite>');

    test('fails when div is inside code element', function () {
        $html = <<<'HTML'
                <code><div>Code block</div></code>
            HTML;

        $this->assertNoMisplacedDiv($html);
    })->throws(AssertionFailedError::class, '<div> should not be inside <code>');

    test('fails when div is inside span element', function () {
        $html = <<<'HTML'
                <span><div>Span block</div></span>
            HTML;

        $this->assertNoMisplacedDiv($html);
    })->throws(AssertionFailedError::class, '<div> should not be inside <span>');

    test('fails when div is inside small element', function () {
        $html = <<<'HTML'
                <small><div>Small block</div></small>
            HTML;

        $this->assertNoMisplacedDiv($html);
    })->throws(AssertionFailedError::class, '<div> should not be inside <small>');

    test('fails when div is inside label element', function () {
        $html = <<<'HTML'
                <label><div>Label block</div></label>
            HTML;

        $this->assertNoMisplacedDiv($html);
    })->throws(AssertionFailedError::class, '<div> should not be inside <label>');

    test('fails when div is inside strong element', function () {
        $html = <<<'HTML'
                <strong><div>Strong block</div></strong>
            HTML;

        $this->assertNoMisplacedDiv($html);
    })->throws(AssertionFailedError::class, '<div> should not be inside <strong>');

    test('fails when div with id is inside inline element', function () {
        $html = <<<'HTML'
                <b><div id="misplaced">Content</div></b>
            HTML;

        $this->assertNoMisplacedDiv($html);
    })->throws(AssertionFailedError::class, 'id="misplaced"');

    test('fails when div with name is inside inline element', function () {
        $html = <<<'HTML'
                <strong><div name="block">Content</div></strong>
            HTML;

        $this->assertNoMisplacedDiv($html);
    })->throws(AssertionFailedError::class, 'name="block"');

    test('fails when nested div is inside inline element', function () {
        $html = <<<'HTML'
                <span>
                    <div>
                        <div>Deeply nested</div>
                    </div>
                </span>
            HTML;

        $this->assertNoMisplacedDiv($html);
    })->throws(AssertionFailedError::class, '<div> should not be inside <span>');

    test('fails when multiple divs are inside inline elements', function () {
        $html = <<<'HTML'
                <b><div>First</div></b>
                <i><div>Second</div></i>
            HTML;

        $this->assertNoMisplacedDiv($html);
    })->throws(AssertionFailedError::class, 'inside inline elements');

    test('fails when div is inside nested inline elements', function () {
        $html = <<<'HTML'
                <strong>
                    <em>
                        <div>Nested inline with div</div>
                    </em>
                </strong>
            HTML;

        $this->assertNoMisplacedDiv($html);
    })->throws(AssertionFailedError::class, '<div> should not be inside <em>');

    test('fails with error message suggesting span', function () {
        $html = <<<'HTML'
                <b><div>Content</div></b>
            HTML;

        $this->assertNoMisplacedDiv($html);
    })->throws(AssertionFailedError::class, 'use <span> instead');

    test('fails when multiple violations with different inline elements', function () {
        $html = <<<'HTML'
                <b><div>Bold block</div></b>
                <span><div>Span block</div></span>
                <code><div>Code block</div></code>
            HTML;

        $this->assertNoMisplacedDiv($html);
    })->throws(AssertionFailedError::class, '<div> elements inside inline elements');
});

describe('assertNoMisusedSectioningTags', function () {
    test('passes when section has other content as first child', function () {
        $html = <<<'HTML'
                <section>
                    <h2>Heading</h2>
                    <p>Content</p>
                </section>
            HTML;

        expect(fn () => $this->assertNoMisusedSectioningTags($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when aside has non-sectioning first child', function () {
        $html = <<<'HTML'
                <aside>
                    <h3>Sidebar</h3>
                    <p>Content</p>
                </aside>
            HTML;

        expect(fn () => $this->assertNoMisusedSectioningTags($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when article has non-sectioning first child', function () {
        $html = <<<'HTML'
                <article>
                    <h1>Article Title</h1>
                    <p>Article content</p>
                </article>
            HTML;

        expect(fn () => $this->assertNoMisusedSectioningTags($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when sectioning tag is not first child', function () {
        $html = <<<'HTML'
                <section>
                    <h2>Heading</h2>
                    <section>Nested section after other content</section>
                </section>
            HTML;

        expect(fn () => $this->assertNoMisusedSectioningTags($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when aside contains section but not as first child', function () {
        $html = <<<'HTML'
                <aside>
                    <h3>Sidebar</h3>
                    <section>Related content</section>
                </aside>
            HTML;

        expect(fn () => $this->assertNoMisusedSectioningTags($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when article contains multiple sections with header first', function () {
        $html = <<<'HTML'
                <article>
                    <header>
                        <h1>Article Title</h1>
                    </header>
                    <section>First section</section>
                    <section>Second section</section>
                </article>
            HTML;

        expect(fn () => $this->assertNoMisusedSectioningTags($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when no sectioning tags are present', function () {
        $html = <<<'HTML'
                <div>
                    <p>Regular content</p>
                </div>
            HTML;

        expect(fn () => $this->assertNoMisusedSectioningTags($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when sectioning tags are siblings', function () {
        $html = <<<'HTML'
                <main>
                    <section>First section</section>
                    <section>Second section</section>
                    <aside>Sidebar</aside>
                </main>
            HTML;

        expect(fn () => $this->assertNoMisusedSectioningTags($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when aside contains section as first child', function () {
        $html = <<<'HTML'
                <aside>
                    <section>I'm wrapping, you know.</section>
                </aside>
            HTML;

        $this->assertNoMisusedSectioningTags($html);
    })->throws(AssertionFailedError::class, '<section> should not be used as a wrapper');

    test('fails when aside contains aside as first child', function () {
        $html = <<<'HTML'
                <aside>
                    <aside>Nested aside</aside>
                </aside>
            HTML;

        $this->assertNoMisusedSectioningTags($html);
    })->throws(AssertionFailedError::class, '<aside> should not be used as a wrapper');

    test('fails when article contains aside as first child', function () {
        $html = <<<'HTML'
                <article>
                    <aside>Wrapped content</aside>
                </article>
            HTML;

        $this->assertNoMisusedSectioningTags($html);
    })->throws(AssertionFailedError::class, '<aside> should not be used as a wrapper');

    test('fails when aside contains article as first child', function () {
        $html = <<<'HTML'
                <aside>
                    <article>Wrapped article</article>
                </aside>
            HTML;

        $this->assertNoMisusedSectioningTags($html);
    })->throws(AssertionFailedError::class, '<article> should not be used as a wrapper');

    test('fails when section contains section as first child', function () {
        $html = <<<'HTML'
                <section>
                    <section>Wrapped section</section>
                </section>
            HTML;

        $this->assertNoMisusedSectioningTags($html);
    })->throws(AssertionFailedError::class, '<section> should not be used as a wrapper');

    test('fails when article contains section as first child', function () {
        $html = <<<'HTML'
                <article>
                    <section>Wrapped section</section>
                </article>
            HTML;

        $this->assertNoMisusedSectioningTags($html);
    })->throws(AssertionFailedError::class, '<section> should not be used as a wrapper');

    test('fails when article contains article as first child', function () {
        $html = <<<'HTML'
                <article>
                    <article>Nested article</article>
                </article>
            HTML;

        $this->assertNoMisusedSectioningTags($html);
    })->throws(AssertionFailedError::class, '<article> should not be used as a wrapper');

    test('fails when element with id is misused as wrapper', function () {
        $html = <<<'HTML'
                <section>
                    <section id="wrapper-section">Content</section>
                </section>
            HTML;

        $this->assertNoMisusedSectioningTags($html);
    })->throws(AssertionFailedError::class, 'id="wrapper-section"');

    test('fails when element with name is misused as wrapper', function () {
        $html = <<<'HTML'
                <aside>
                    <section name="wrapper">Content</section>
                </aside>
            HTML;

        $this->assertNoMisusedSectioningTags($html);
    })->throws(AssertionFailedError::class, 'name="wrapper"');

    test('fails with error showing parent and child tags', function () {
        $html = <<<'HTML'
                <aside>
                    <section>Content</section>
                </aside>
            HTML;

        $this->assertNoMisusedSectioningTags($html);
    })->throws(AssertionFailedError::class, 'first child of <aside>');

    test('fails when multiple sectioning tags are misused', function () {
        $html = <<<'HTML'
                <aside>
                    <section>Wrapped section</section>
                </aside>
                <article>
                    <section>Another wrapped section</section>
                </article>
            HTML;

        $this->assertNoMisusedSectioningTags($html);
    })->throws(AssertionFailedError::class, 'sectioning tags misused as wrappers');

    test('fails when deeply nested sectioning tags are misused', function () {
        $html = <<<'HTML'
                <section>
                    <div>
                        <section>
                            <section>Deeply nested wrapper</section>
                        </section>
                    </div>
                </section>
            HTML;

        $this->assertNoMisusedSectioningTags($html);
    })->throws(AssertionFailedError::class, '<section> should not be used as a wrapper');

    test('fails with all different combinations', function () {
        $html = <<<'HTML'
                <aside>
                    <aside>aside in aside</aside>
                </aside>
                <article>
                    <aside>aside in article</aside>
                </article>
                <aside>
                    <article>article in aside</article>
                </aside>
                <aside>
                    <section>section in aside</section>
                </aside>
                <section>
                    <section>section in section</section>
                </section>
                <article>
                    <section>section in article</section>
                </article>
                <article>
                    <article>article in article</article>
                </article>
            HTML;

        $this->assertNoMisusedSectioningTags($html);
    })->throws(AssertionFailedError::class, 'sectioning tags misused as wrappers');
});

describe('assertLegendIsFirstChildOfFieldset', function () {
    test('passes when legend is first child of fieldset', function () {
        $html = <<<'HTML'
                <fieldset>
                    <legend>Personal Information</legend>
                    <label>Name: <input type="text"></label>
                </fieldset>
            HTML;

        expect(fn () => $this->assertLegendIsFirstChildOfFieldset($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when fieldset has only a legend', function () {
        $html = <<<'HTML'
                <fieldset>
                    <legend>Section Title</legend>
                </fieldset>
            HTML;

        expect(fn () => $this->assertLegendIsFirstChildOfFieldset($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when multiple fieldsets each have legend first', function () {
        $html = <<<'HTML'
                <form>
                    <fieldset>
                        <legend>Section 1</legend>
                        <input type="text">
                    </fieldset>
                    <fieldset>
                        <legend>Section 2</legend>
                        <input type="email">
                    </fieldset>
                </form>
            HTML;

        expect(fn () => $this->assertLegendIsFirstChildOfFieldset($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when nested fieldsets each have legend first', function () {
        $html = <<<'HTML'
                <fieldset>
                    <legend>Outer Legend</legend>
                    <fieldset>
                        <legend>Inner Legend</legend>
                        <input type="text">
                    </fieldset>
                </fieldset>
            HTML;

        expect(fn () => $this->assertLegendIsFirstChildOfFieldset($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when fieldset has no legend', function () {
        $html = <<<'HTML'
                <fieldset>
                    <label>I'm not a legend.</label>
                </fieldset>
            HTML;

        $this->assertLegendIsFirstChildOfFieldset($html);
    })->throws(AssertionFailedError::class, '<fieldset> has <label> as first child');

    test('fails when legend is not first child', function () {
        $html = <<<'HTML'
                <fieldset>
                    <div>Something else first</div>
                    <legend>Legend in wrong position</legend>
                </fieldset>
            HTML;

        $this->assertLegendIsFirstChildOfFieldset($html);
    })->throws(AssertionFailedError::class, '<legend> is not the first child');
});

describe('assertSummaryIsFirstChildOfDetails', function () {
    test('passes when summary is first child of details', function () {
        $html = <<<'HTML'
                <details>
                    <summary>Click to expand</summary>
                    <p>Hidden content here</p>
                </details>
            HTML;

        expect(fn () => $this->assertSummaryIsFirstChildOfDetails($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when nested details each have summary first', function () {
        $html = <<<'HTML'
                <details>
                    <summary>Outer Summary</summary>
                    <details>
                        <summary>Inner Summary</summary>
                        <p>Nested content</p>
                    </details>
                </details>
            HTML;

        expect(fn () => $this->assertSummaryIsFirstChildOfDetails($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when summary is not first child', function () {
        $html = <<<'HTML'
                <details>
                    <div>Something else first</div>
                    <summary>Summary in wrong position</summary>
                </details>
            HTML;

        $this->assertSummaryIsFirstChildOfDetails($html);
    })->throws(AssertionFailedError::class, '<summary> is not the first child');

    test('fails when nested details has wrong structure', function () {
        $html = <<<'HTML'
                <details>
                    <summary>Outer Summary</summary>
                    <details>
                        <p>Content first</p>
                        <summary>Inner Summary (wrong position)</summary>
                    </details>
                </details>
            HTML;

        $this->assertSummaryIsFirstChildOfDetails($html);
    })->throws(AssertionFailedError::class, '<details> has <p> as first child');
});

describe('assertAbbrHasTitle', function () {
    test('passes when abbr has title attribute', function () {
        $html = <<<'HTML'
                <p>Do you know about <abbr title="World Wide Web Consortium">W3C</abbr>?</p>
            HTML;

        expect(fn () => $this->assertAbbrHasTitle($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when multiple abbr elements have title', function () {
        $html = <<<'HTML'
                <p>
                    <abbr title="HyperText Markup Language">HTML</abbr> and
                    <abbr title="Cascading Style Sheets">CSS</abbr>
                </p>
            HTML;

        expect(fn () => $this->assertAbbrHasTitle($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when abbr has no title attribute', function () {
        $html = <<<'HTML'
                <p>Do you know about <abbr>W3C</abbr>?</p>
            HTML;

        $this->assertAbbrHasTitle($html);
    })->throws(AssertionFailedError::class, 'missing a title attribute');

    test('fails when abbr has empty title attribute', function () {
        $html = <<<'HTML'
                <p>Do you know about <abbr title="">W3C</abbr>?</p>
            HTML;

        $this->assertAbbrHasTitle($html);
    })->throws(AssertionFailedError::class, 'empty title attribute');

    test('fails when abbr has whitespace-only title attribute', function () {
        $html = <<<'HTML'
                <p>Do you know about <abbr title="   ">W3C</abbr>?</p>
            HTML;

        $this->assertAbbrHasTitle($html);
    })->throws(AssertionFailedError::class, 'whitespace-only title attribute');
});

describe('assertAltDoesNotContainFileName', function () {
    test('passes when alt does not contain file extension', function () {
        $html = <<<'HTML'
                <img src="/image.png" alt="A beautiful landscape" />
            HTML;

        expect(fn () => $this->assertAltDoesNotContainFileName($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when alt contains extension-like text in middle', function () {
        $html = <<<'HTML'
                <img src="/image.png" alt="Download the PDF guide" />
            HTML;

        expect(fn () => $this->assertAltDoesNotContainFileName($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when img alt ends with .png', function () {
        $html = <<<'HTML'
                <img alt="/static/ffoodd.png" src="/static/ffoodd.png" width="144" height="144" />
            HTML;

        $this->assertAltDoesNotContainFileName($html);
    })->throws(AssertionFailedError::class, 'contains a file name in the alt attribute');

    test('fails when img alt ends with .jpg', function () {
        $html = <<<'HTML'
                <img alt="image.jpg" src="/image.jpg" />
            HTML;

        $this->assertAltDoesNotContainFileName($html);
    })->throws(AssertionFailedError::class, 'contains a file name in the alt attribute');

    test('fails when img alt ends with .pdf', function () {
        $html = <<<'HTML'
                <img alt="document.pdf" src="/icon.png" />
            HTML;

        $this->assertAltDoesNotContainFileName($html);
    })->throws(AssertionFailedError::class, 'contains a file name in the alt attribute');

    test('fails when input type image alt ends with file extension', function () {
        $html = <<<'HTML'
                <input type="image" src="/submit.png" alt="submit-button.png" />
            HTML;

        $this->assertAltDoesNotContainFileName($html);
    })->throws(AssertionFailedError::class, 'contains a file name in the alt attribute');

    test('fails when area alt ends with file extension', function () {
        $html = <<<'HTML'
                <map name="image-map">
                    <area shape="rect" coords="0,0,100,100" href="/link" alt="region.gif" />
                </map>
            HTML;

        $this->assertAltDoesNotContainFileName($html);
    })->throws(AssertionFailedError::class, 'contains a file name in the alt attribute');
});

describe('assertDecorativeImagesDoNotHaveAccessibleName', function () {
    test('passes when img with alt has no naming attributes', function () {
        $html = <<<'HTML'
                <img src="/image.png" alt="Descriptive text" />
            HTML;

        expect(fn () => $this->assertDecorativeImagesDoNotHaveAccessibleName($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when img with empty alt has no naming attributes', function () {
        $html = <<<'HTML'
                <img src="/image.png" alt="" />
            HTML;

        expect(fn () => $this->assertDecorativeImagesDoNotHaveAccessibleName($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when svg without aria-hidden has title', function () {
        $html = <<<'HTML'
                <svg title="Chart" role="img">
                    <circle cx="50" cy="50" r="40" />
                </svg>
            HTML;

        expect(fn () => $this->assertDecorativeImagesDoNotHaveAccessibleName($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when img with empty alt has title', function () {
        $html = <<<'HTML'
                <img src="/image.png" alt="" title="This is decorative" />
            HTML;

        $this->assertDecorativeImagesDoNotHaveAccessibleName($html);
    })->throws(AssertionFailedError::class, 'is decorative (empty alt) but has [title] attribute');

    test('fails when img with empty alt has aria-label', function () {
        $html = <<<'HTML'
                <img src="/image.png" alt="" aria-label="Decorative image" />
            HTML;

        $this->assertDecorativeImagesDoNotHaveAccessibleName($html);
    })->throws(AssertionFailedError::class, 'is decorative (empty alt) but has [aria-label] attribute');

    test('fails when svg with aria-hidden has title', function () {
        $html = <<<'HTML'
                <svg width="12cm" height="4cm" viewBox="0 0 1200 400"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true" title="Decorative SVG, you punk!">
                    <rect x="400" y="100" width="400" height="200"
                        fill="forestgreen" stroke="darkgreen" stroke-width="10"/>
                </svg>
            HTML;

        $this->assertDecorativeImagesDoNotHaveAccessibleName($html);
    })->throws(AssertionFailedError::class, 'is decorative (aria-hidden="true") but has [title] attribute');

    test('fails when svg with aria-hidden has aria-label', function () {
        $html = <<<'HTML'
                <svg aria-hidden="true" aria-label="Decorative graphic">
                    <circle cx="50" cy="50" r="40" />
                </svg>
            HTML;

        $this->assertDecorativeImagesDoNotHaveAccessibleName($html);
    })->throws(AssertionFailedError::class, 'is decorative (aria-hidden="true") but has [aria-label] attribute');

    test('fails when canvas with aria-hidden has aria-labelledby', function () {
        $html = <<<'HTML'
                <canvas aria-hidden="true" aria-labelledby="canvas-label"></canvas>
                <span id="canvas-label">Canvas label</span>
            HTML;

        $this->assertDecorativeImagesDoNotHaveAccessibleName($html);
    })->throws(AssertionFailedError::class, 'is decorative (aria-hidden="true") but has [aria-labelledby] attribute');

    test('fails when area without href has non-empty alt', function () {
        $html = <<<'HTML'
                <map name="image-map">
                    <area shape="rect" coords="0,0,100,100" alt="Decorative region" />
                </map>
            HTML;

        $this->assertDecorativeImagesDoNotHaveAccessibleName($html);
    })->throws(AssertionFailedError::class, 'is decorative (no href)');
});

describe('assertRolePresentationNotUsedOnImages', function () {
    test('passes when img has aria-hidden instead of role presentation', function () {
        $html = '<img src="decorative.png" aria-hidden="true" />';

        expect(fn () => $this->assertRolePresentationNotUsedOnImages($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when img has empty alt instead of role presentation', function () {
        $html = '<img src="decorative.png" alt="" />';

        expect(fn () => $this->assertRolePresentationNotUsedOnImages($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when svg has aria-hidden instead of role presentation', function () {
        $html = '<svg aria-hidden="true"><rect /></svg>';

        expect(fn () => $this->assertRolePresentationNotUsedOnImages($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when img has role presentation', function () {
        $html = '<img src="decorative.png" role="presentation" />';

        $this->assertRolePresentationNotUsedOnImages($html);
    })->throws(
        AssertionFailedError::class,
        'The <img> element uses role="presentation", which has poor browser support. Use aria-hidden="true" instead or an empty alt attribute to mark decorative images.'
    );

    test('fails when svg has role presentation', function () {
        $html = <<<'HTML'
                <svg width="12cm" height="4cm" viewBox="0 0 1200 400"
                    xmlns="http://www.w3.org/2000/svg"
                    role="presentation">
                    <rect x="400" y="100" width="400" height="200"
                        fill="forestgreen" stroke="darkgreen" stroke-width="10"/>
                </svg>
            HTML;

        $this->assertRolePresentationNotUsedOnImages($html);
    })->throws(
        AssertionFailedError::class,
        'The <svg> element uses role="presentation", which has poor browser support. Use aria-hidden="true" instead to mark decorative images.'
    );

    test('fails when area has role presentation', function () {
        $html = '<area shape="rect" coords="0,0,100,100" role="presentation" />';

        $this->assertRolePresentationNotUsedOnImages($html);
    })->throws(
        AssertionFailedError::class,
        'The <area> element uses role="presentation", which has poor browser support. Use aria-hidden="true" instead to mark decorative images.'
    );

    test('fails when embed has role presentation', function () {
        $html = '<embed src="decorative.svg" role="presentation" />';

        $this->assertRolePresentationNotUsedOnImages($html);
    })->throws(
        AssertionFailedError::class,
        'The <embed> element uses role="presentation", which has poor browser support. Use aria-hidden="true" instead to mark decorative images.'
    );

    test('fails when canvas has role presentation', function () {
        $html = '<canvas role="presentation"></canvas>';

        $this->assertRolePresentationNotUsedOnImages($html);
    })->throws(
        AssertionFailedError::class,
        'The <canvas> element uses role="presentation", which has poor browser support. Use aria-hidden="true" instead to mark decorative images.'
    );

    test('fails when object has role presentation', function () {
        $html = '<object data="decorative.svg" role="presentation"></object>';

        $this->assertRolePresentationNotUsedOnImages($html);
    })->throws(
        AssertionFailedError::class,
        'The <object> element uses role="presentation", which has poor browser support. Use aria-hidden="true" instead to mark decorative images.'
    );
});

describe('assertSvgHasRole', function () {
    test('passes when svg has aria-hidden="true"', function () {
        $html = '<svg aria-hidden="true"><rect /></svg>';

        expect(fn () => $this->assertSvgHasRole($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when svg has role="img"', function () {
        $html = '<svg role="img"><title>Chart</title><rect /></svg>';

        expect(fn () => $this->assertSvgHasRole($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when svg has both aria-hidden and role', function () {
        $html = '<svg aria-hidden="true" role="img"><rect /></svg>';

        expect(fn () => $this->assertSvgHasRole($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when svg has neither aria-hidden nor role', function () {
        $html = '<svg><rect /></svg>';

        $this->assertSvgHasRole($html);
    })->throws(
        AssertionFailedError::class,
        'The <svg> element must have either aria-hidden="true" (if decorative) or role="img" (if informative).'
    );

    test('fails when svg has aria-label but no role or aria-hidden', function () {
        $html = <<<'HTML'
                <svg width="12cm" height="4cm" viewBox="0 0 1200 400"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-label="Decorative SVG, you punk!">
                    <rect x="400" y="100" width="400" height="200"
                        fill="forestgreen" stroke="darkgreen" stroke-width="10"/>
                </svg>
            HTML;

        $this->assertSvgHasRole($html);
    })->throws(
        AssertionFailedError::class,
        'The <svg> element must have either aria-hidden="true" (if decorative) or role="img" (if informative).'
    );

    test('fails when svg has title but no role or aria-hidden', function () {
        $html = '<svg><title>Chart title</title><rect /></svg>';

        $this->assertSvgHasRole($html);
    })->throws(
        AssertionFailedError::class,
        'The <svg> element must have either aria-hidden="true" (if decorative) or role="img" (if informative).'
    );

    test('fails when svg with id has no role or aria-hidden', function () {
        $html = '<svg id="my-svg"><rect /></svg>';

        $this->assertSvgHasRole($html);
    })->throws(
        AssertionFailedError::class,
        'The <svg> id="my-svg" element must have either aria-hidden="true" (if decorative) or role="img" (if informative).'
    );

    test('fails when svg has role="presentation" instead of role="img"', function () {
        $html = '<svg role="presentation"><rect /></svg>';

        $this->assertSvgHasRole($html);
    })->throws(
        AssertionFailedError::class,
        'The <svg> element must have either aria-hidden="true" (if decorative) or role="img" (if informative).'
    );

    test('fails when svg has aria-hidden="false"', function () {
        $html = '<svg aria-hidden="false"><rect /></svg>';

        $this->assertSvgHasRole($html);
    })->throws(
        AssertionFailedError::class,
        'The <svg> element must have either aria-hidden="true" (if decorative) or role="img" (if informative).'
    );
});

describe('assertAutoplayNotUsed', function () {
    test('passes when video has no autoplay attribute', function () {
        $html = '<video controls src="video.mp4"></video>';

        expect(fn () => $this->assertAutoplayNotUsed($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when audio has no autoplay attribute', function () {
        $html = '<audio controls src="audio.mp3"></audio>';

        expect(fn () => $this->assertAutoplayNotUsed($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when video has controls but no autoplay', function () {
        $html = '<video controls muted src="video.mp4"></video>';

        expect(fn () => $this->assertAutoplayNotUsed($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when video has autoplay attribute', function () {
        $html = '<video autoplay controls src=""></video>';

        $this->assertAutoplayNotUsed($html);
    })->throws(
        AssertionFailedError::class,
        'The <video> element has the autoplay attribute, which can be disruptive for users. Remove the autoplay attribute to give users control over media playback.'
    );

    test('fails when audio has autoplay attribute', function () {
        $html = '<audio autoplay controls src="audio.mp3"></audio>';

        $this->assertAutoplayNotUsed($html);
    })->throws(
        AssertionFailedError::class,
        'The <audio> element has the autoplay attribute, which can be disruptive for users. Remove the autoplay attribute to give users control over media playback.'
    );

    test('fails when video with id has autoplay', function () {
        $html = '<video id="main-video" autoplay src="video.mp4"></video>';

        $this->assertAutoplayNotUsed($html);
    })->throws(
        AssertionFailedError::class,
        'The <video> id="main-video" element has the autoplay attribute, which can be disruptive for users.'
    );

    test('fails when audio with name has autoplay', function () {
        $html = '<audio name="background-music" autoplay src="audio.mp3"></audio>';

        $this->assertAutoplayNotUsed($html);
    })->throws(
        AssertionFailedError::class,
        'The <audio> name="background-music" element has the autoplay attribute, which can be disruptive for users.'
    );

    test('fails when video has autoplay even with muted', function () {
        $html = '<video autoplay muted controls src="video.mp4"></video>';

        $this->assertAutoplayNotUsed($html);
    })->throws(
        AssertionFailedError::class,
        'The <video> element has the autoplay attribute, which can be disruptive for users.'
    );

    test('fails when video has autoplay as boolean attribute', function () {
        $html = '<video autoplay src="video.mp4"></video>';

        $this->assertAutoplayNotUsed($html);
    })->throws(
        AssertionFailedError::class,
        'The <video> element has the autoplay attribute, which can be disruptive for users.'
    );
});

describe('assertMediaHasControls', function () {
    test('passes when video has controls attribute', function () {
        $html = '<video controls src="video.mp4"></video>';

        expect(fn () => $this->assertMediaHasControls($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when audio has controls attribute', function () {
        $html = '<audio controls src="audio.mp3"></audio>';

        expect(fn () => $this->assertMediaHasControls($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when video has controls with other attributes', function () {
        $html = '<video controls autoplay muted src="video.mp4"></video>';

        expect(fn () => $this->assertMediaHasControls($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when video has no controls attribute', function () {
        $html = '<video src=""></video>';

        $this->assertMediaHasControls($html);
    })->throws(
        AssertionFailedError::class,
        'The <video> element is missing the controls attribute. Add controls to give users the ability to play, pause, and control the media.'
    );

    test('fails when audio has no controls attribute', function () {
        $html = '<audio src="audio.mp3"></audio>';

        $this->assertMediaHasControls($html);
    })->throws(
        AssertionFailedError::class,
        'The <audio> element is missing the controls attribute. Add controls to give users the ability to play, pause, and control the media.'
    );

    test('fails when video with id has no controls', function () {
        $html = '<video id="main-video" src="video.mp4"></video>';

        $this->assertMediaHasControls($html);
    })->throws(
        AssertionFailedError::class,
        'The <video> id="main-video" element is missing the controls attribute.'
    );

    test('fails when audio with name has no controls', function () {
        $html = '<audio name="background-music" src="audio.mp3"></audio>';

        $this->assertMediaHasControls($html);
    })->throws(
        AssertionFailedError::class,
        'The <audio> name="background-music" element is missing the controls attribute.'
    );

    test('fails when video has autoplay but no controls', function () {
        $html = '<video autoplay src="video.mp4"></video>';

        $this->assertMediaHasControls($html);
    })->throws(
        AssertionFailedError::class,
        'The <video> element is missing the controls attribute.'
    );

    test('fails when video has muted but no controls', function () {
        $html = '<video muted src="video.mp4"></video>';

        $this->assertMediaHasControls($html);
    })->throws(
        AssertionFailedError::class,
        'The <video> element is missing the controls attribute.'
    );
});

describe('assertNoEmptyNodes', function () {
    test('passes when elements have content', function () {
        $html = '<body><p>Content</p><div>More content</div></body>';

        expect(fn () => $this->assertNoEmptyNodes($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when void elements are empty', function () {
        $html = '<body><img src="image.png" alt="Image"><br><hr><input type="text"></body>';

        expect(fn () => $this->assertNoEmptyNodes($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when iframe is empty', function () {
        $html = '<body><iframe src="page.html"></iframe></body>';

        expect(fn () => $this->assertNoEmptyNodes($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when textarea is empty', function () {
        $html = '<body><textarea placeholder="Enter text"></textarea></body>';

        expect(fn () => $this->assertNoEmptyNodes($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when element has hidden attribute', function () {
        $html = '<body><div hidden></div></body>';

        expect(fn () => $this->assertNoEmptyNodes($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when element has aria-hidden', function () {
        $html = '<body><span aria-hidden="true"></span></body>';

        expect(fn () => $this->assertNoEmptyNodes($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when video has src attribute', function () {
        $html = '<body><video src="video.mp4"></video></body>';

        expect(fn () => $this->assertNoEmptyNodes($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when audio has src attribute', function () {
        $html = '<body><audio src="audio.mp3"></audio></body>';

        expect(fn () => $this->assertNoEmptyNodes($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when paragraph is empty', function () {
        $html = '<body><p id="empty-node_code"></p></body>';

        $this->assertNoEmptyNodes($html);
    })->throws(
        AssertionFailedError::class,
        'The <p> id="empty-node_code" element is empty and serves no purpose. Remove this element or add content to it.'
    );

    test('fails when div is empty', function () {
        $html = '<body><div></div></body>';

        $this->assertNoEmptyNodes($html);
    })->throws(
        AssertionFailedError::class,
        'The <div> element is empty and serves no purpose.'
    );

    test('fails when span is empty', function () {
        $html = '<body><span></span></body>';

        $this->assertNoEmptyNodes($html);
    })->throws(
        AssertionFailedError::class,
        'The <span> element is empty and serves no purpose.'
    );

    test('fails when section is empty', function () {
        $html = '<body><section></section></body>';

        $this->assertNoEmptyNodes($html);
    })->throws(
        AssertionFailedError::class,
        'The <section> element is empty and serves no purpose.'
    );

    test('fails when article is empty', function () {
        $html = '<body><article></article></body>';

        $this->assertNoEmptyNodes($html);
    })->throws(
        AssertionFailedError::class,
        'The <article> element is empty and serves no purpose.'
    );

    test('fails when li is empty', function () {
        $html = '<body><ul><li></li></ul></body>';

        $this->assertNoEmptyNodes($html);
    })->throws(
        AssertionFailedError::class,
        'The <li> element is empty and serves no purpose.'
    );

    test('fails when heading is empty', function () {
        $html = '<body><h1></h1></body>';

        $this->assertNoEmptyNodes($html);
    })->throws(
        AssertionFailedError::class,
        'The <h1> element is empty and serves no purpose.'
    );
});

describe('assertNoNestedTables', function () {
    test('passes when table has no nested tables', function () {
        $html = <<<'HTML'
                <table>
                    <caption>Simple table</caption>
                    <thead>
                        <tr>
                            <th scope="col">Header</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Cell content</td>
                        </tr>
                    </tbody>
                </table>
            HTML;

        expect(fn () => $this->assertNoNestedTables($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when multiple tables are siblings', function () {
        $html = <<<'HTML'
                <table>
                    <tbody>
                        <tr>
                            <td>First table</td>
                        </tr>
                    </tbody>
                </table>
                <table>
                    <tbody>
                        <tr>
                            <td>Second table</td>
                        </tr>
                    </tbody>
                </table>
            HTML;

        expect(fn () => $this->assertNoNestedTables($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when no tables exist', function () {
        $html = '<div>No tables here</div>';

        expect(fn () => $this->assertNoNestedTables($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when table is nested inside another table', function () {
        $html = <<<'HTML'
                <table>
                    <caption>I'm a caption :3</caption>
                    <thead>
                        <tr>
                            <th scope="col">Oh boy…</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>I'm a table-cell!</td>
                        </tr>
                        <tr>
                            <td>
                                <table>
                                    <caption>I'm a caption too!</caption>
                                    <thead>
                                        <tr>
                                            <th scope="col">Oh boy…</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>I'm a table-cell!</td>
                                        </tr>
                                        <tr>
                                            <td>I'm a table-cell!</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
            HTML;

        $this->assertNoNestedTables($html);
    })->throws(
        AssertionFailedError::class,
        'Found a nested <table> element inside another table. Tables should not be nested as they are likely being used for layout purposes.'
    );

    test('fails when table is nested in tbody', function () {
        $html = <<<'HTML'
                <table>
                    <tbody>
                        <tr>
                            <td>
                                <table>
                                    <tr>
                                        <td>Nested table</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
            HTML;

        $this->assertNoNestedTables($html);
    })->throws(
        AssertionFailedError::class,
        'Found a nested <table> element inside another table.'
    );

    test('fails when table is nested in thead', function () {
        $html = <<<'HTML'
                <table>
                    <thead>
                        <tr>
                            <th>
                                <table>
                                    <tr>
                                        <td>Nested in header</td>
                                    </tr>
                                </table>
                            </th>
                        </tr>
                    </thead>
                </table>
            HTML;

        $this->assertNoNestedTables($html);
    })->throws(
        AssertionFailedError::class,
        'Found a nested <table> element inside another table.'
    );

    test('fails when table is nested in tfoot', function () {
        $html = <<<'HTML'
                <table>
                    <tfoot>
                        <tr>
                            <td>
                                <table>
                                    <tr>
                                        <td>Nested in footer</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            HTML;

        $this->assertNoNestedTables($html);
    })->throws(
        AssertionFailedError::class,
        'Found a nested <table> element inside another table.'
    );

    test('fails when nested table has id', function () {
        $html = <<<'HTML'
                <table>
                    <tr>
                        <td>
                            <table id="nested-table">
                                <tr>
                                    <td>Nested</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            HTML;

        $this->assertNoNestedTables($html);
    })->throws(
        AssertionFailedError::class,
        'Found a nested <table> id="nested-table" element inside another table.'
    );

    test('fails when deeply nested tables', function () {
        $html = <<<'HTML'
                <table>
                    <tr>
                        <td>
                            <div>
                                <table>
                                    <tr>
                                        <td>Nested with wrapper</td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                </table>
            HTML;

        $this->assertNoNestedTables($html);
    })->throws(
        AssertionFailedError::class,
        'Found a nested <table> element inside another table.'
    );
});

describe('assertTableHasCaption', function () {
    test('passes when table has caption as first child', function () {
        $html = <<<'HTML'
                <table>
                    <caption>Table caption</caption>
                    <thead>
                        <tr>
                            <th>Header</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Data</td>
                        </tr>
                    </tbody>
                </table>
            HTML;

        expect(fn () => $this->assertTableHasCaption($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when simple table has caption', function () {
        $html = <<<'HTML'
                <table>
                    <caption>Simple caption</caption>
                    <tr>
                        <td>Data</td>
                    </tr>
                </table>
            HTML;

        expect(fn () => $this->assertTableHasCaption($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when table has role presentation without caption', function () {
        $html = <<<'HTML'
                <table role="presentation">
                    <tr>
                        <td>Layout table</td>
                    </tr>
                </table>
            HTML;

        expect(fn () => $this->assertTableHasCaption($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when multiple tables all have captions', function () {
        $html = <<<'HTML'
                <table>
                    <caption>First table</caption>
                    <tr>
                        <td>Data</td>
                    </tr>
                </table>
                <table>
                    <caption>Second table</caption>
                    <tr>
                        <td>Data</td>
                    </tr>
                </table>
            HTML;

        expect(fn () => $this->assertTableHasCaption($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when table has no caption', function () {
        $html = <<<'HTML'
                <table>
                    <thead>
                        <tr>
                            <th id="th-one">I'm a table without a caption!</th>
                            <th id="th-two">I'm a table without a caption!</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="2" headers="th-one th-two">I'm a table without a caption!</td>
                        </tr>
                        <tr>
                            <td colspan="2" headers="th-one th-two">I'm a table without a caption!</td>
                        </tr>
                    </tbody>
                </table>
            HTML;

        $this->assertTableHasCaption($html);
    })->throws(
        AssertionFailedError::class,
        'The <table> element is missing a <caption> as its first child. Data tables must have a <caption> element to provide a title or explanation.'
    );

    test('fails when table starts with tbody instead of caption', function () {
        $html = <<<'HTML'
                <table>
                    <tbody>
                        <tr>
                            <td>No caption here</td>
                        </tr>
                    </tbody>
                </table>
            HTML;

        $this->assertTableHasCaption($html);
    })->throws(
        AssertionFailedError::class,
        'The <table> element is missing a <caption> as its first child.'
    );

    test('fails when table starts with thead instead of caption', function () {
        $html = <<<'HTML'
                <table>
                    <thead>
                        <tr>
                            <th>Header</th>
                        </tr>
                    </thead>
                </table>
            HTML;

        $this->assertTableHasCaption($html);
    })->throws(
        AssertionFailedError::class,
        'The <table> element is missing a <caption> as its first child.'
    );

    test('fails when caption is not the first child', function () {
        $html = <<<'HTML'
                <table>
                    <thead>
                        <tr>
                            <th>Header</th>
                        </tr>
                    </thead>
                    <caption>Caption in wrong position</caption>
                    <tbody>
                        <tr>
                            <td>Data</td>
                        </tr>
                    </tbody>
                </table>
            HTML;

        $this->assertTableHasCaption($html);
    })->throws(
        AssertionFailedError::class,
        'The <table> element has a <caption> but it is not the first child. The <caption> must be the first child of the <table> element.'
    );

    test('fails when caption comes after tbody', function () {
        $html = <<<'HTML'
                <table>
                    <tbody>
                        <tr>
                            <td>Data</td>
                        </tr>
                    </tbody>
                    <caption>Caption too late</caption>
                </table>
            HTML;

        $this->assertTableHasCaption($html);
    })->throws(
        AssertionFailedError::class,
        'The <table> element has a <caption> but it is not the first child.'
    );

    test('fails when table with id has no caption', function () {
        $html = <<<'HTML'
                <table id="data-table">
                    <tr>
                        <td>No caption</td>
                    </tr>
                </table>
            HTML;

        $this->assertTableHasCaption($html);
    })->throws(
        AssertionFailedError::class,
        'The <table> id="data-table" element is missing a <caption> as its first child.'
    );

    test('fails when table starts with tr instead of caption', function () {
        $html = <<<'HTML'
                <table>
                    <tr>
                        <td>First element is tr</td>
                    </tr>
                </table>
            HTML;

        $this->assertTableHasCaption($html);
    })->throws(
        AssertionFailedError::class,
        'The <table> element is missing a <caption> as its first child.'
    );
});

describe('assertTableStructureIsValid', function () {
    test('passes when table has correct order: caption, thead, tfoot, tbody', function () {
        $html = <<<'HTML'
                <table>
                    <caption>Correct order</caption>
                    <thead>
                        <tr>
                            <th>Header</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <td>Footer</td>
                        </tr>
                    </tfoot>
                    <tbody>
                        <tr>
                            <td>Body</td>
                        </tr>
                    </tbody>
                </table>
            HTML;

        expect(fn () => $this->assertTableStructureIsValid($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when table has correct order: thead, tbody', function () {
        $html = <<<'HTML'
                <table>
                    <thead>
                        <tr>
                            <th>Header</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Body</td>
                        </tr>
                    </tbody>
                </table>
            HTML;

        expect(fn () => $this->assertTableStructureIsValid($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when table has colgroup before thead', function () {
        $html = <<<'HTML'
                <table>
                    <colgroup>
                        <col>
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Header</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Body</td>
                        </tr>
                    </tbody>
                </table>
            HTML;

        expect(fn () => $this->assertTableStructureIsValid($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when table has only tbody', function () {
        $html = <<<'HTML'
                <table>
                    <tbody>
                        <tr>
                            <td>Body only</td>
                        </tr>
                    </tbody>
                </table>
            HTML;

        expect(fn () => $this->assertTableStructureIsValid($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when tfoot comes after tbody', function () {
        $html = <<<'HTML'
                <table>
                    <caption>I'm a caption</caption>
                    <thead>
                        <tr>
                            <th scope="col">Where's my foot?</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>I'm a table with tfoot done wrong.</td>
                            <td>I'm a table with tfoot done wrong.</td>
                        </tr>
                        <tr>
                            <td>I'm a table with tfoot done wrong.</td>
                            <td>I'm a table with tfoot done wrong.</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <th id="th-1">I'm a table with tfoot done wrong.</th>
                        <th id="th-2">I'm a table with tfoot done wrong.</th>
                    </tfoot>
                </table>
            HTML;

        $this->assertTableStructureIsValid($html);
    })->throws(
        AssertionFailedError::class,
        'The <table> element has invalid structure: tbody cannot come before tfoot. Table elements must be in this order: caption, colgroup, thead, tfoot, tbody.'
    );

    test('fails when tbody comes before thead', function () {
        $html = <<<'HTML'
                <table>
                    <tbody>
                        <tr>
                            <td>Body first</td>
                        </tr>
                    </tbody>
                    <thead>
                        <tr>
                            <th>Header after body</th>
                        </tr>
                    </thead>
                </table>
            HTML;

        $this->assertTableStructureIsValid($html);
    })->throws(
        AssertionFailedError::class,
        'The <table> element has invalid structure: tbody cannot come before thead.'
    );

    test('fails when tfoot comes before thead', function () {
        $html = <<<'HTML'
                <table>
                    <tfoot>
                        <tr>
                            <td>Footer first</td>
                        </tr>
                    </tfoot>
                    <thead>
                        <tr>
                            <th>Header after footer</th>
                        </tr>
                    </thead>
                </table>
            HTML;

        $this->assertTableStructureIsValid($html);
    })->throws(
        AssertionFailedError::class,
        'The <table> element has invalid structure: tfoot cannot come before thead.'
    );

    test('fails when tbody comes before colgroup', function () {
        $html = <<<'HTML'
                <table>
                    <tbody>
                        <tr>
                            <td>Body</td>
                        </tr>
                    </tbody>
                    <colgroup>
                        <col>
                    </colgroup>
                </table>
            HTML;

        $this->assertTableStructureIsValid($html);
    })->throws(
        AssertionFailedError::class,
        'The <table> element has invalid structure: tbody cannot come before colgroup.'
    );

    test('fails when thead comes before colgroup', function () {
        $html = <<<'HTML'
                <table>
                    <thead>
                        <tr>
                            <th>Header</th>
                        </tr>
                    </thead>
                    <colgroup>
                        <col>
                    </colgroup>
                </table>
            HTML;

        $this->assertTableStructureIsValid($html);
    })->throws(
        AssertionFailedError::class,
        'The <table> element has invalid structure: thead cannot come before colgroup.'
    );

    test('fails when tfoot comes before colgroup', function () {
        $html = <<<'HTML'
                <table>
                    <tfoot>
                        <tr>
                            <td>Footer</td>
                        </tr>
                    </tfoot>
                    <colgroup>
                        <col>
                    </colgroup>
                </table>
            HTML;

        $this->assertTableStructureIsValid($html);
    })->throws(
        AssertionFailedError::class,
        'The <table> element has invalid structure: tfoot cannot come before colgroup.'
    );

    test('fails when table with id has invalid structure', function () {
        $html = <<<'HTML'
                <table id="invalid-table">
                    <tbody>
                        <tr>
                            <td>Body</td>
                        </tr>
                    </tbody>
                    <thead>
                        <tr>
                            <th>Header</th>
                        </tr>
                    </thead>
                </table>
            HTML;

        $this->assertTableStructureIsValid($html);
    })->throws(
        AssertionFailedError::class,
        'The <table> id="invalid-table" element has invalid structure: tbody cannot come before thead.'
    );
});

describe('assertTableHasThead', function () {
    test('passes when table has thead and tbody', function () {
        $html = <<<'HTML'
            <table>
                <thead>
                    <tr>
                        <th>Header</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Data</td>
                    </tr>
                </tbody>
            </table>
        HTML;

        expect(fn () => $this->assertTableHasThead($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when table has caption, thead and tbody', function () {
        $html = <<<'HTML'
            <table>
                <caption>Table caption</caption>
                <thead>
                    <tr>
                        <th>Header</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Data</td>
                    </tr>
                </tbody>
            </table>
        HTML;

        expect(fn () => $this->assertTableHasThead($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when table has only thead without tbody', function () {
        $html = <<<'HTML'
            <table>
                <thead>
                    <tr>
                        <th>Header</th>
                    </tr>
                </thead>
                <tr>
                    <td>Data</td>
                </tr>
            </table>
        HTML;

        expect(fn () => $this->assertTableHasThead($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when layout table with presentation role has tbody without thead', function () {
        $html = <<<'HTML'
            <table role="presentation">
                <tbody>
                    <tr>
                        <td>Layout content</td>
                    </tr>
                </tbody>
            </table>
        HTML;

        expect(fn () => $this->assertTableHasThead($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when table has tbody as first child without thead', function () {
        $html = <<<'HTML'
            <table>
                <tbody>
                    <tr>
                        <td>Data</td>
                    </tr>
                </tbody>
            </table>
        HTML;

        $this->assertTableHasThead($html);
    })->throws(
        AssertionFailedError::class,
        'The <table> element has a <tbody> but is missing a <thead>. Data tables with a <tbody> must have a <thead> to provide column headers.'
    );

    test('fails when table has caption and tbody but no thead', function () {
        $html = <<<'HTML'
            <table>
                <caption>Table caption</caption>
                <tbody>
                    <tr>
                        <td>Data</td>
                    </tr>
                </tbody>
            </table>
        HTML;

        $this->assertTableHasThead($html);
    })->throws(
        AssertionFailedError::class,
        'The <table> element has a <tbody> but is missing a <thead>. Data tables with a <tbody> must have a <thead> to provide column headers.'
    );

    test('fails when table with id has tbody without thead', function () {
        $html = <<<'HTML'
            <table id="data-table">
                <tbody>
                    <tr>
                        <td>Data</td>
                    </tr>
                </tbody>
            </table>
        HTML;

        $this->assertTableHasThead($html);
    })->throws(
        AssertionFailedError::class,
        'The <table> id="data-table" element has a <tbody> but is missing a <thead>. Data tables with a <tbody> must have a <thead> to provide column headers.'
    );

    test('fails when table with name has tbody without thead', function () {
        $html = <<<'HTML'
            <table name="data-table">
                <tbody>
                    <tr>
                        <td>Data</td>
                    </tr>
                </tbody>
            </table>
        HTML;

        $this->assertTableHasThead($html);
    })->throws(
        AssertionFailedError::class,
        'The <table> name="data-table" element has a <tbody> but is missing a <thead>. Data tables with a <tbody> must have a <thead> to provide column headers.'
    );

    test('fails when table has colgroup, caption and tbody but no thead', function () {
        $html = <<<'HTML'
            <table>
                <colgroup>
                    <col>
                </colgroup>
                <caption>Table caption</caption>
                <tbody>
                    <tr>
                        <td>Data</td>
                    </tr>
                </tbody>
            </table>
        HTML;

        $this->assertTableHasThead($html);
    })->throws(
        AssertionFailedError::class,
        'The <table> element has a <tbody> but is missing a <thead>. Data tables with a <tbody> must have a <thead> to provide column headers.'
    );

    test('fails when table has tfoot and tbody but no thead', function () {
        $html = <<<'HTML'
            <table>
                <tbody>
                    <tr>
                        <td>Data</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td>Footer</td>
                    </tr>
                </tfoot>
            </table>
        HTML;

        $this->assertTableHasThead($html);
    })->throws(
        AssertionFailedError::class,
        'The <table> element has a <tbody> but is missing a <thead>. Data tables with a <tbody> must have a <thead> to provide column headers.'
    );

    test('fails when table has multiple tbody elements but no thead', function () {
        $html = <<<'HTML'
            <table>
                <tbody>
                    <tr>
                        <td>Data 1</td>
                    </tr>
                </tbody>
                <tbody>
                    <tr>
                        <td>Data 2</td>
                    </tr>
                </tbody>
            </table>
        HTML;

        $this->assertTableHasThead($html);
    })->throws(
        AssertionFailedError::class,
        'The <table> element has a <tbody> but is missing a <thead>. Data tables with a <tbody> must have a <thead> to provide column headers.'
    );
});

describe('assertNoJavascriptHrefWithoutRole', function () {
    test('passes when link uses regular href', function () {
        $html = <<<'HTML'
            <a href="https://example.com">Regular link</a>
        HTML;

        expect(fn () => $this->assertNoJavascriptHrefWithoutRole($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when link uses relative href', function () {
        $html = <<<'HTML'
            <a href="/page">Internal link</a>
        HTML;

        expect(fn () => $this->assertNoJavascriptHrefWithoutRole($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when javascript href has role button', function () {
        $html = <<<'HTML'
            <a href="javascript:void(0);" role="button">Button-like link</a>
        HTML;

        expect(fn () => $this->assertNoJavascriptHrefWithoutRole($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when bookmarklet has role button', function () {
        $html = <<<'HTML'
            <a href="javascript:(function(){alert('bookmarklet')})();" role="button">Bookmarklet</a>
        HTML;

        expect(fn () => $this->assertNoJavascriptHrefWithoutRole($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when link uses javascript href without role', function () {
        $html = <<<'HTML'
            <a href="javascript:void(0);">Click me</a>
        HTML;

        $this->assertNoJavascriptHrefWithoutRole($html);
    })->throws(
        AssertionFailedError::class,
        'The <a> element uses href="javascript:..." without role="button". Links with javascript: protocol should be replaced with <button> elements or have role="button".'
    );

    test('fails when link uses javascript function without role', function () {
        $html = <<<'HTML'
            <a href="javascript:doSomething();">Action</a>
        HTML;

        $this->assertNoJavascriptHrefWithoutRole($html);
    })->throws(
        AssertionFailedError::class,
        'The <a> element uses href="javascript:..." without role="button". Links with javascript: protocol should be replaced with <button> elements or have role="button".'
    );

    test('fails when bookmarklet link has no role', function () {
        $html = <<<'HTML'
            <a href="javascript:(function(){a11ycss=document.createElement('LINK');a11ycss.href='https://rawgit.com/ffoodd/a11y.css/master/css/a11y-en.css';a11ycss.rel='stylesheet';a11ycss.media='all';document.body.appendChild(a11ycss);})();">Please use my bookmarklet ;)</a>
        HTML;

        $this->assertNoJavascriptHrefWithoutRole($html);
    })->throws(
        AssertionFailedError::class,
        'The <a> element uses href="javascript:..." without role="button". Links with javascript: protocol should be replaced with <button> elements or have role="button".'
    );

    test('fails when link with id uses javascript href without role', function () {
        $html = <<<'HTML'
            <a id="action-link" href="javascript:performAction();">Action</a>
        HTML;

        $this->assertNoJavascriptHrefWithoutRole($html);
    })->throws(
        AssertionFailedError::class,
        'The <a> id="action-link" element uses href="javascript:..." without role="button". Links with javascript: protocol should be replaced with <button> elements or have role="button".'
    );

    test('fails when link with name uses javascript href without role', function () {
        $html = <<<'HTML'
            <a name="legacy-link" href="javascript:legacyAction();">Legacy action</a>
        HTML;

        $this->assertNoJavascriptHrefWithoutRole($html);
    })->throws(
        AssertionFailedError::class,
        'The <a> name="legacy-link" element uses href="javascript:..." without role="button". Links with javascript: protocol should be replaced with <button> elements or have role="button".'
    );

    test('fails when javascript href has different role', function () {
        $html = <<<'HTML'
            <a href="javascript:void(0);" role="tab">Tab</a>
        HTML;

        $this->assertNoJavascriptHrefWithoutRole($html);
    })->throws(
        AssertionFailedError::class,
        'The <a> element uses href="javascript:..." without role="button". Links with javascript: protocol should be replaced with <button> elements or have role="button".'
    );

    test('fails when multiple links use javascript href without role', function () {
        $html = <<<'HTML'
            <a href="javascript:action1();">Action 1</a>
            <a href="javascript:action2();">Action 2</a>
        HTML;

        $this->assertNoJavascriptHrefWithoutRole($html);
    })->throws(
        AssertionFailedError::class,
        'The <a> element uses href="javascript:..." without role="button". Links with javascript: protocol should be replaced with <button> elements or have role="button".'
    );
});

describe('assertNoHashOnlyHrefWithoutRole', function () {
    test('passes when link uses regular href', function () {
        $html = <<<'HTML'
            <a href="https://example.com">Regular link</a>
        HTML;

        expect(fn () => $this->assertNoHashOnlyHrefWithoutRole($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when link uses hash with fragment identifier', function () {
        $html = <<<'HTML'
            <a href="#section">Jump to section</a>
        HTML;

        expect(fn () => $this->assertNoHashOnlyHrefWithoutRole($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when hash only link has role button', function () {
        $html = <<<'HTML'
            <a href="#" role="button">Button-like link</a>
        HTML;

        expect(fn () => $this->assertNoHashOnlyHrefWithoutRole($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when link uses relative path', function () {
        $html = <<<'HTML'
            <a href="/page">Internal link</a>
        HTML;

        expect(fn () => $this->assertNoHashOnlyHrefWithoutRole($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when link uses hash only href without role', function () {
        $html = <<<'HTML'
            <a href="#">Oh boy, I don't mean anything</a>
        HTML;

        $this->assertNoHashOnlyHrefWithoutRole($html);
    })->throws(
        AssertionFailedError::class,
        'The <a> element uses href="#" without role="button". Links with href="#" should be replaced with <button> elements or have role="button".'
    );

    test('fails when link with id uses hash only href without role', function () {
        $html = <<<'HTML'
            <a id="action-link" href="#">Click me</a>
        HTML;

        $this->assertNoHashOnlyHrefWithoutRole($html);
    })->throws(
        AssertionFailedError::class,
        'The <a> id="action-link" element uses href="#" without role="button". Links with href="#" should be replaced with <button> elements or have role="button".'
    );

    test('fails when link with name uses hash only href without role', function () {
        $html = <<<'HTML'
            <a name="legacy-link" href="#">Legacy action</a>
        HTML;

        $this->assertNoHashOnlyHrefWithoutRole($html);
    })->throws(
        AssertionFailedError::class,
        'The <a> name="legacy-link" element uses href="#" without role="button". Links with href="#" should be replaced with <button> elements or have role="button".'
    );

    test('fails when hash only link has different role', function () {
        $html = <<<'HTML'
            <a href="#" role="tab">Tab</a>
        HTML;

        $this->assertNoHashOnlyHrefWithoutRole($html);
    })->throws(
        AssertionFailedError::class,
        'The <a> element uses href="#" without role="button". Links with href="#" should be replaced with <button> elements or have role="button".'
    );

    test('fails when hash only link has aria-label but no role', function () {
        $html = <<<'HTML'
            <a href="#" aria-label="Open menu">Menu</a>
        HTML;

        $this->assertNoHashOnlyHrefWithoutRole($html);
    })->throws(
        AssertionFailedError::class,
        'The <a> element uses href="#" without role="button". Links with href="#" should be replaced with <button> elements or have role="button".'
    );

    test('fails when hash only link is in navigation', function () {
        $html = <<<'HTML'
            <nav>
                <a href="#">Home</a>
            </nav>
        HTML;

        $this->assertNoHashOnlyHrefWithoutRole($html);
    })->throws(
        AssertionFailedError::class,
        'The <a> element uses href="#" without role="button". Links with href="#" should be replaced with <button> elements or have role="button".'
    );

    test('fails when multiple links use hash only href without role', function () {
        $html = <<<'HTML'
            <a href="#">Link 1</a>
            <a href="#">Link 2</a>
        HTML;

        $this->assertNoHashOnlyHrefWithoutRole($html);
    })->throws(
        AssertionFailedError::class,
        'The <a> element uses href="#" without role="button". Links with href="#" should be replaced with <button> elements or have role="button".'
    );
});

describe('assertHeadingRoleHasAriaLevel', function () {
    test('passes when heading role has aria-level', function () {
        $html = <<<'HTML'
            <div role="heading" aria-level="2">Section heading</div>
        HTML;

        expect(fn () => $this->assertHeadingRoleHasAriaLevel($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when strong with heading role has aria-level', function () {
        $html = <<<'HTML'
            <strong role="heading" aria-level="3">Important heading</strong>
        HTML;

        expect(fn () => $this->assertHeadingRoleHasAriaLevel($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when using native heading elements', function () {
        $html = <<<'HTML'
            <h1>Main heading</h1>
            <h2>Subheading</h2>
        HTML;

        expect(fn () => $this->assertHeadingRoleHasAriaLevel($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when no elements with heading role exist', function () {
        $html = <<<'HTML'
            <div>Regular content</div>
            <p>Paragraph</p>
        HTML;

        expect(fn () => $this->assertHeadingRoleHasAriaLevel($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when heading role missing aria-level', function () {
        $html = <<<'HTML'
            <strong role="heading">Heading with undefined level</strong>
        HTML;

        $this->assertHeadingRoleHasAriaLevel($html);
    })->throws(
        AssertionFailedError::class,
        'The <strong> element has role="heading" but is missing the aria-level attribute. Elements with role="heading" should specify aria-level to indicate their hierarchical level.'
    );

    test('fails when div with heading role missing aria-level', function () {
        $html = <<<'HTML'
            <div role="heading">Section heading</div>
        HTML;

        $this->assertHeadingRoleHasAriaLevel($html);
    })->throws(
        AssertionFailedError::class,
        'The <div> element has role="heading" but is missing the aria-level attribute. Elements with role="heading" should specify aria-level to indicate their hierarchical level.'
    );

    test('fails when span with heading role missing aria-level', function () {
        $html = <<<'HTML'
            <span role="heading">Heading text</span>
        HTML;

        $this->assertHeadingRoleHasAriaLevel($html);
    })->throws(
        AssertionFailedError::class,
        'The <span> element has role="heading" but is missing the aria-level attribute. Elements with role="heading" should specify aria-level to indicate their hierarchical level.'
    );

    test('fails when heading role with id missing aria-level', function () {
        $html = <<<'HTML'
            <div id="page-title" role="heading">Page Title</div>
        HTML;

        $this->assertHeadingRoleHasAriaLevel($html);
    })->throws(
        AssertionFailedError::class,
        'The <div> id="page-title" element has role="heading" but is missing the aria-level attribute. Elements with role="heading" should specify aria-level to indicate their hierarchical level.'
    );

    test('fails when heading role with name missing aria-level', function () {
        $html = <<<'HTML'
            <div name="section-heading" role="heading">Section</div>
        HTML;

        $this->assertHeadingRoleHasAriaLevel($html);
    })->throws(
        AssertionFailedError::class,
        'The <div> name="section-heading" element has role="heading" but is missing the aria-level attribute. Elements with role="heading" should specify aria-level to indicate their hierarchical level.'
    );

    test('fails when heading role with aria-label but no aria-level', function () {
        $html = <<<'HTML'
            <div role="heading" aria-label="Important section">Content</div>
        HTML;

        $this->assertHeadingRoleHasAriaLevel($html);
    })->throws(
        AssertionFailedError::class,
        'The <div> element has role="heading" but is missing the aria-level attribute. Elements with role="heading" should specify aria-level to indicate their hierarchical level.'
    );

    test('fails when multiple heading roles missing aria-level', function () {
        $html = <<<'HTML'
            <div role="heading">First heading</div>
            <div role="heading">Second heading</div>
        HTML;

        $this->assertHeadingRoleHasAriaLevel($html);
    })->throws(
        AssertionFailedError::class,
        'The <div> element has role="heading" but is missing the aria-level attribute. Elements with role="heading" should specify aria-level to indicate their hierarchical level.'
    );
});

describe('assertLabelHasForOrControl', function () {
    test('passes when label has for attribute', function () {
        $html = <<<'HTML'
            <label for="username">Username</label>
            <input id="username" type="text">
        HTML;

        expect(fn () => $this->assertLabelHasForOrControl($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when label contains input', function () {
        $html = <<<'HTML'
            <label>
                Username
                <input type="text">
            </label>
        HTML;

        expect(fn () => $this->assertLabelHasForOrControl($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when label contains select', function () {
        $html = <<<'HTML'
            <label>
                Country
                <select>
                    <option>USA</option>
                </select>
            </label>
        HTML;

        expect(fn () => $this->assertLabelHasForOrControl($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when label contains textarea', function () {
        $html = <<<'HTML'
            <label>
                Message
                <textarea></textarea>
            </label>
        HTML;

        expect(fn () => $this->assertLabelHasForOrControl($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when label contains button', function () {
        $html = <<<'HTML'
            <label>
                <button>Submit</button>
            </label>
        HTML;

        expect(fn () => $this->assertLabelHasForOrControl($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when label contains progress', function () {
        $html = <<<'HTML'
            <label>
                Loading
                <progress value="50" max="100"></progress>
            </label>
        HTML;

        expect(fn () => $this->assertLabelHasForOrControl($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when label contains meter', function () {
        $html = <<<'HTML'
            <label>
                Score
                <meter min="0" max="100" value="75"></meter>
            </label>
        HTML;

        expect(fn () => $this->assertLabelHasForOrControl($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when label contains output', function () {
        $html = <<<'HTML'
            <label>
                Result
                <output>42</output>
            </label>
        HTML;

        expect(fn () => $this->assertLabelHasForOrControl($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when no labels exist', function () {
        $html = <<<'HTML'
            <div>No labels here</div>
        HTML;

        expect(fn () => $this->assertLabelHasForOrControl($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes when label has for and contains the same control', function () {
        $html = <<<'HTML'
            <label for="username">
                Username
                <input id="username" type="text">
            </label>
        HTML;

        expect(fn () => $this->assertLabelHasForOrControl($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when label has no for attribute and no control', function () {
        $html = <<<'HTML'
            <label>Guess what?</label>
        HTML;

        $this->assertLabelHasForOrControl($html);
    })->throws(
        AssertionFailedError::class,
        'The <label> element is missing the for attribute and does not contain a form control. Labels should either have a for attribute referencing a form control, or contain a labelable element'
    );

    test('fails when label contains only text', function () {
        $html = <<<'HTML'
            <label>Just some text</label>
        HTML;

        $this->assertLabelHasForOrControl($html);
    })->throws(
        AssertionFailedError::class,
        'The <label> element is missing the for attribute and does not contain a form control. Labels should either have a for attribute referencing a form control, or contain a labelable element'
    );

    test('fails when label contains only div', function () {
        $html = <<<'HTML'
            <label>
                <div>Not a form control</div>
            </label>
        HTML;

        $this->assertLabelHasForOrControl($html);
    })->throws(
        AssertionFailedError::class,
        'The <label> element is missing the for attribute and does not contain a form control. Labels should either have a for attribute referencing a form control, or contain a labelable element'
    );

    test('fails when label contains only span', function () {
        $html = <<<'HTML'
            <label><span>Label text</span></label>
        HTML;

        $this->assertLabelHasForOrControl($html);
    })->throws(
        AssertionFailedError::class,
        'The <label> element is missing the for attribute and does not contain a form control. Labels should either have a for attribute referencing a form control, or contain a labelable element'
    );

    test('fails when label with id has no for or control', function () {
        $html = <<<'HTML'
            <label id="my-label">Label</label>
        HTML;

        $this->assertLabelHasForOrControl($html);
    })->throws(
        AssertionFailedError::class,
        'The <label> id="my-label" element is missing the for attribute and does not contain a form control. Labels should either have a for attribute referencing a form control, or contain a labelable element'
    );

    test('fails when label with name has no for or control', function () {
        $html = <<<'HTML'
            <label name="legacy-label">Label</label>
        HTML;

        $this->assertLabelHasForOrControl($html);
    })->throws(
        AssertionFailedError::class,
        'The <label> name="legacy-label" element is missing the for attribute and does not contain a form control. Labels should either have a for attribute referencing a form control, or contain a labelable element'
    );

    test('fails when label contains hidden input only', function () {
        $html = <<<'HTML'
            <label>
                Label text
                <input type="hidden" value="secret">
            </label>
        HTML;

        $this->assertLabelHasForOrControl($html);
    })->throws(
        AssertionFailedError::class,
        'The <label> element is missing the for attribute and does not contain a form control. Labels should either have a for attribute referencing a form control, or contain a labelable element'
    );

    test('fails when multiple labels have no for or control', function () {
        $html = <<<'HTML'
            <label>First label</label>
            <label>Second label</label>
        HTML;

        $this->assertLabelHasForOrControl($html);
    })->throws(
        AssertionFailedError::class,
        'The <label> element is missing the for attribute and does not contain a form control. Labels should either have a for attribute referencing a form control, or contain a labelable element'
    );

    test('fails when label contains multiple inputs', function () {
        $html = <<<'HTML'
            <label>
                First name and Last name
                <input type="text" name="first">
                <input type="text" name="last">
            </label>
        HTML;

        $this->assertLabelHasForOrControl($html);
    })->throws(
        AssertionFailedError::class,
        'The <label> element contains multiple form controls. A label should only contain one form control element.'
    );

    test('fails when label contains input and select', function () {
        $html = <<<'HTML'
            <label>
                Name and Country
                <input type="text">
                <select>
                    <option>USA</option>
                </select>
            </label>
        HTML;

        $this->assertLabelHasForOrControl($html);
    })->throws(
        AssertionFailedError::class,
        'The <label> element contains multiple form controls. A label should only contain one form control element.'
    );

    test('fails when label with id contains multiple controls', function () {
        $html = <<<'HTML'
            <label id="multi-control">
                <input type="text">
                <textarea></textarea>
            </label>
        HTML;

        $this->assertLabelHasForOrControl($html);
    })->throws(
        AssertionFailedError::class,
        'The <label> id="multi-control" element contains multiple form controls. A label should only contain one form control element.'
    );

    test('fails when label contains three inputs', function () {
        $html = <<<'HTML'
            <label>
                <input type="text">
                <input type="email">
                <input type="tel">
            </label>
        HTML;

        $this->assertLabelHasForOrControl($html);
    })->throws(
        AssertionFailedError::class,
        'The <label> element contains multiple form controls. A label should only contain one form control element.'
    );
});

describe('assertDirMatchesLang', function () {
    test('passes when Arabic has dir=rtl', function () {
        $html = '<p lang="ar" dir="rtl">مرحبا بك</p>';

        $this->assertDirMatchesLang($html);
    })->throwsNoExceptions();

    test('passes when Hebrew has dir=rtl', function () {
        $html = '<p lang="he" dir="rtl">שלום</p>';

        $this->assertDirMatchesLang($html);
    })->throwsNoExceptions();

    test('passes when nested lang in RTL content has dir=ltr', function () {
        $html = <<<'HTML'
            <div lang="ar" dir="rtl">
                مرحبا
                <span lang="en" dir="ltr">Hello</span>
            </div>
        HTML;

        $this->assertDirMatchesLang($html);
    })->throwsNoExceptions();

    test('passes when nested lang in RTL content has dir=rtl', function () {
        $html = <<<'HTML'
            <div lang="ar" dir="rtl">
                مرحبا
                <span lang="he" dir="rtl">שלום</span>
            </div>
        HTML;

        $this->assertDirMatchesLang($html);
    })->throwsNoExceptions();

    test('passes when English content has no dir attribute', function () {
        $html = '<p lang="en">Hello world</p>';

        $this->assertDirMatchesLang($html);
    })->throwsNoExceptions();

    test('passes when multiple Arabic elements have dir=rtl', function () {
        $html = <<<'HTML'
            <p lang="ar" dir="rtl">مرحبا</p>
            <p lang="ar" dir="rtl">شكرا</p>
        HTML;

        $this->assertDirMatchesLang($html);
    })->throwsNoExceptions();

    test('fails when Arabic is missing dir=rtl', function () {
        $html = '<p lang="ar">مرحبا بك</p>';

        $this->assertDirMatchesLang($html);
    })->throws(
        AssertionFailedError::class,
        'The <p> element has lang="ar" but is missing dir="rtl". Right-to-left languages like Arabic and Hebrew require the dir="rtl" attribute'
    );

    test('fails when Hebrew is missing dir=rtl', function () {
        $html = '<p lang="he">שלום</p>';

        $this->assertDirMatchesLang($html);
    })->throws(
        AssertionFailedError::class,
        'The <p> element has lang="he" but is missing dir="rtl". Right-to-left languages like Arabic and Hebrew require the dir="rtl" attribute'
    );

    test('fails when Arabic with id is missing dir=rtl', function () {
        $html = '<div id="greeting" lang="ar">مرحبا</div>';

        $this->assertDirMatchesLang($html);
    })->throws(
        AssertionFailedError::class,
        'The <div> id="greeting" element has lang="ar" but is missing dir="rtl". Right-to-left languages like Arabic and Hebrew require the dir="rtl" attribute'
    );

    test('fails when dir=rtl is used with English', function () {
        $html = '<p dir="rtl" lang="en">Well, I\'m kinda disoriented…</p>';

        $this->assertDirMatchesLang($html);
    })->throws(
        AssertionFailedError::class,
        'The <p> element has dir="rtl" but lang="en". The dir="rtl" attribute should be used with right-to-left languages like Arabic (ar) or Hebrew (he)'
    );

    test('fails when dir=rtl is used without lang attribute', function () {
        $html = '<p dir="rtl">Confused text</p>';

        $this->assertDirMatchesLang($html);
    })->throws(
        AssertionFailedError::class,
        'The <p> element has dir="rtl" but lang="not set". The dir="rtl" attribute should be used with right-to-left languages like Arabic (ar) or Hebrew (he)'
    );

    test('fails when dir=rtl is used with French', function () {
        $html = '<p dir="rtl" lang="fr">Bonjour</p>';

        $this->assertDirMatchesLang($html);
    })->throws(
        AssertionFailedError::class,
        'The <p> element has dir="rtl" but lang="fr". The dir="rtl" attribute should be used with right-to-left languages like Arabic (ar) or Hebrew (he)'
    );

    test('fails when lang change in RTL content is missing dir', function () {
        $html = <<<'HTML'
            <div lang="ar" dir="rtl">
                مرحبا
                <span lang="en">Hello</span>
            </div>
        HTML;

        $this->assertDirMatchesLang($html);
    })->throws(
        AssertionFailedError::class,
        'The <span> element has lang="en" within RTL content but is missing a dir attribute. Language changes within right-to-left content should define dir="ltr" or dir="rtl" as appropriate'
    );

    test('fails when nested lang in Hebrew content is missing dir', function () {
        $html = <<<'HTML'
            <div lang="he" dir="rtl">
                שלום
                <span lang="en">Hello</span>
            </div>
        HTML;

        $this->assertDirMatchesLang($html);
    })->throws(
        AssertionFailedError::class,
        'The <span> element has lang="en" within RTL content but is missing a dir attribute. Language changes within right-to-left content should define dir="ltr" or dir="rtl" as appropriate'
    );

    test('fails when multiple Arabic elements are missing dir=rtl', function () {
        $html = <<<'HTML'
            <p lang="ar">مرحبا</p>
            <p lang="ar">شكرا</p>
        HTML;

        $this->assertDirMatchesLang($html);
    })->throws(
        AssertionFailedError::class,
        'The <p> element has lang="ar" but is missing dir="rtl". Right-to-left languages like Arabic and Hebrew require the dir="rtl" attribute'
    );

    test('fails when dir=rtl with id is used with wrong language', function () {
        $html = '<section id="content" dir="rtl" lang="es">Hola</section>';

        $this->assertDirMatchesLang($html);
    })->throws(
        AssertionFailedError::class,
        'The <section> id="content" element has dir="rtl" but lang="es". The dir="rtl" attribute should be used with right-to-left languages like Arabic (ar) or Hebrew (he)'
    );
});
