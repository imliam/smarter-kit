<?php

declare(strict_types=1);

use PHPUnit\Framework\AssertionFailedError;
use Tests\Concerns\Accessibility\ChecksAccessibilityObsoletes;

uses(ChecksAccessibilityObsoletes::class);

describe('assertNoObsoleteTags', function () {
    test('passes when no obsolete tags are present', function () {
        $html = <<<'HTML'
                <div>
                    <p>Modern HTML content</p>
                    <span>More content</span>
                </div>
            HTML;

        expect(fn () => $this->assertNoObsoleteTags($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when acronym tag is used', function () {
        $html = <<<'HTML'
                <acronym title="World Wide Web Consortium">W3C</acronym>
            HTML;

        $this->assertNoObsoleteTags($html);
    })->throws(AssertionFailedError::class, '<acronym> is an obsolete HTML tag');

    test('fails when applet tag is used', function () {
        $html = <<<'HTML'
                <applet code="MyApplet.class">Applet content</applet>
            HTML;

        $this->assertNoObsoleteTags($html);
    })->throws(AssertionFailedError::class, '<applet> is an obsolete HTML tag');

    test('fails when bgsound tag is used', function () {
        $html = <<<'HTML'
                <bgsound src="sound.mp3">
            HTML;

        $this->assertNoObsoleteTags($html);
    })->throws(AssertionFailedError::class, '<bgsound> is an obsolete HTML tag');

    test('fails when dir tag is used', function () {
        $html = <<<'HTML'
                <dir>
                    <li>Item 1</li>
                    <li>Item 2</li>
                </dir>
            HTML;

        $this->assertNoObsoleteTags($html);
    })->throws(AssertionFailedError::class, '<dir> is an obsolete HTML tag');

    test('fails when frame tag is used', function () {
        $html = <<<'HTML'
                <frameset>
                    <frame src="frame.html">
                </frameset>
            HTML;

        $this->assertNoObsoleteTags($html);
    })->throws(AssertionFailedError::class, '<frame> is an obsolete HTML tag');

    test('fails when frameset tag is used', function () {
        $html = <<<'HTML'
                <frameset cols="50%,50%">
                    <frame src="frame1.html">
                    <frame src="frame2.html">
                </frameset>
            HTML;

        $this->assertNoObsoleteTags($html);
    })->throws(AssertionFailedError::class, '<frameset> is an obsolete HTML tag');

    test('fails when noframes tag is used', function () {
        $html = <<<'HTML'
                <noframes>Your browser does not support frames</noframes>
            HTML;

        $this->assertNoObsoleteTags($html);
    })->throws(AssertionFailedError::class, '<noframes> is an obsolete HTML tag');

    test('fails when isindex tag is used', function () {
        $html = <<<'HTML'
                <isindex prompt="Search:">
            HTML;

        $this->assertNoObsoleteTags($html);
    })->throws(AssertionFailedError::class, '<isindex> is an obsolete HTML tag');

    test('fails when keygen tag is used', function () {
        $html = <<<'HTML'
                <keygen name="security">
            HTML;

        $this->assertNoObsoleteTags($html);
    })->throws(AssertionFailedError::class, '<keygen> is an obsolete HTML tag');

    test('fails when menuitem tag is used', function () {
        $html = <<<'HTML'
                <menuitem label="Save">
            HTML;

        $this->assertNoObsoleteTags($html);
    })->throws(AssertionFailedError::class, '<menuitem> is an obsolete HTML tag');

    test('fails when listing tag is used', function () {
        $html = <<<'HTML'
                <listing>Code listing</listing>
            HTML;

        $this->assertNoObsoleteTags($html);
    })->throws(AssertionFailedError::class, '<listing> is an obsolete HTML tag');

    test('fails when nextid tag is used', function () {
        $html = <<<'HTML'
                <nextid n="z123">
            HTML;

        $this->assertNoObsoleteTags($html);
    })->throws(AssertionFailedError::class, '<nextid> is an obsolete HTML tag');

    test('fails when noembed tag is used', function () {
        $html = <<<'HTML'
                <noembed>Your browser does not support embedded content</noembed>
            HTML;

        $this->assertNoObsoleteTags($html);
    })->throws(AssertionFailedError::class, '<noembed> is an obsolete HTML tag');

    test('fails when param tag is used', function () {
        $html = <<<'HTML'
                <param name="autoplay" value="true">
            HTML;

        $this->assertNoObsoleteTags($html);
    })->throws(AssertionFailedError::class, '<param> is an obsolete HTML tag');

    test('fails when plaintext tag is used', function () {
        $html = <<<'HTML'
                <plaintext>Plain text content
            HTML;

        $this->assertNoObsoleteTags($html);
    })->throws(AssertionFailedError::class, '<plaintext> is an obsolete HTML tag');

    test('fails when rb tag is used', function () {
        $html = <<<'HTML'
                <div><rb>Content</rb></div>
            HTML;

        $this->assertNoObsoleteTags($html);
    })->throws(AssertionFailedError::class, '<rb> is an obsolete HTML tag');

    test('fails when rtc tag is used', function () {
        $html = <<<'HTML'
                <div><rtc>Content</rtc></div>
            HTML;

        $this->assertNoObsoleteTags($html);
    })->throws(AssertionFailedError::class, '<rtc> is an obsolete HTML tag');

    test('fails when strike tag is used', function () {
        $html = <<<'HTML'
                <strike>Strikethrough text</strike>
            HTML;

        $this->assertNoObsoleteTags($html);
    })->throws(AssertionFailedError::class, '<strike> is an obsolete HTML tag');

    test('fails when xmp tag is used', function () {
        $html = <<<'HTML'
                <xmp>Example code</xmp>
            HTML;

        $this->assertNoObsoleteTags($html);
    })->throws(AssertionFailedError::class, '<xmp> is an obsolete HTML tag');

    test('fails when basefont tag is used', function () {
        $html = <<<'HTML'
                <basefont size="3" color="red">
            HTML;

        $this->assertNoObsoleteTags($html);
    })->throws(AssertionFailedError::class, '<basefont> is an obsolete HTML tag');

    test('fails when big tag is used', function () {
        $html = <<<'HTML'
                <big>Big text</big>
            HTML;

        $this->assertNoObsoleteTags($html);
    })->throws(AssertionFailedError::class, '<big> is an obsolete HTML tag');

    test('fails when blink tag is used', function () {
        $html = <<<'HTML'
                <blink>Blinking text</blink>
            HTML;

        $this->assertNoObsoleteTags($html);
    })->throws(AssertionFailedError::class, '<blink> is an obsolete HTML tag');

    test('fails when center tag is used', function () {
        $html = <<<'HTML'
                <center>Centered content</center>
            HTML;

        $this->assertNoObsoleteTags($html);
    })->throws(AssertionFailedError::class, '<center> is an obsolete HTML tag');

    test('fails when font tag is used', function () {
        $html = <<<'HTML'
                <font face="Arial" size="2">Text</font>
            HTML;

        $this->assertNoObsoleteTags($html);
    })->throws(AssertionFailedError::class, '<font> is an obsolete HTML tag');

    test('fails when marquee tag is used', function () {
        $html = <<<'HTML'
                <marquee>Scrolling text</marquee>
            HTML;

        $this->assertNoObsoleteTags($html);
    })->throws(AssertionFailedError::class, '<marquee> is an obsolete HTML tag');

    test('fails when multicol tag is used', function () {
        $html = <<<'HTML'
                <multicol cols="3">Multi-column content</multicol>
            HTML;

        $this->assertNoObsoleteTags($html);
    })->throws(AssertionFailedError::class, '<multicol> is an obsolete HTML tag');

    test('fails when nobr tag is used', function () {
        $html = <<<'HTML'
                <nobr>No line breaks here</nobr>
            HTML;

        $this->assertNoObsoleteTags($html);
    })->throws(AssertionFailedError::class, '<nobr> is an obsolete HTML tag');

    test('fails when spacer tag is used', function () {
        $html = <<<'HTML'
                <spacer type="horizontal" size="10">
            HTML;

        $this->assertNoObsoleteTags($html);
    })->throws(AssertionFailedError::class, '<spacer> is an obsolete HTML tag');

    test('fails when tt tag is used', function () {
        $html = <<<'HTML'
                <tt>Teletype text</tt>
            HTML;

        $this->assertNoObsoleteTags($html);
    })->throws(AssertionFailedError::class, '<tt> is an obsolete HTML tag');

    test('fails with multiple obsolete tags', function () {
        $html = <<<'HTML'
                <center>
                    <font size="3">Text</font>
                    <acronym title="Test">TEST</acronym>
                </center>
            HTML;

        $this->assertNoObsoleteTags($html);
    })->throws(AssertionFailedError::class, 'obsolete HTML tag');

    test('passes with abbr tag instead of acronym', function () {
        $html = <<<'HTML'
                <abbr title="World Wide Web Consortium">W3C</abbr>
            HTML;

        expect(fn () => $this->assertNoObsoleteTags($html))->not->toThrow(AssertionFailedError::class);
    });

    test('passes with del or s tag instead of strike', function () {
        $html = <<<'HTML'
                <del>Deleted text</del>
                <s>Strikethrough text</s>
            HTML;

        expect(fn () => $this->assertNoObsoleteTags($html))->not->toThrow(AssertionFailedError::class);
    });
});

describe('assertNoObsoleteAttributes', function () {
    test('passes when no obsolete attributes are present', function () {
        $html = <<<'HTML'
                <div>
                    <h3 class="title">Modern HTML</h3>
                    <p id="content">Content with modern attributes</p>
                    <a href="/page">Link</a>
                </div>
            HTML;

        expect(fn () => $this->assertNoObsoleteAttributes($html))->not->toThrow(AssertionFailedError::class);
    });

    test('fails when align attribute is used on h3', function () {
        $html = <<<'HTML'
                <h3 align="right">I'm using [align]!</h3>
            HTML;

        $this->assertNoObsoleteAttributes($html);
    })->throws(AssertionFailedError::class, 'obsolete attribute');

    test('fails when align attribute is used on div', function () {
        $html = <<<'HTML'
                <div align="center">Centered content</div>
            HTML;

        $this->assertNoObsoleteAttributes($html);
    })->throws(AssertionFailedError::class, 'align');

    test('fails when bgcolor attribute is used on table', function () {
        $html = <<<'HTML'
                <table bgcolor="#ffffff">
                    <tr><td>Cell</td></tr>
                </table>
            HTML;

        $this->assertNoObsoleteAttributes($html);
    })->throws(AssertionFailedError::class, 'bgcolor');

    test('fails when bgcolor attribute is used on body', function () {
        $html = <<<'HTML'
                <body bgcolor="#ffffff">Content</body>
            HTML;

        $this->assertNoObsoleteAttributes($html);
    })->throws(AssertionFailedError::class, 'bgcolor');

    test('fails when cellpadding attribute is used on table', function () {
        $html = <<<'HTML'
                <table cellpadding="5">
                    <tr><td>Cell</td></tr>
                </table>
            HTML;

        $this->assertNoObsoleteAttributes($html);
    })->throws(AssertionFailedError::class, 'cellpadding');

    test('fails when cellspacing attribute is used on table', function () {
        $html = <<<'HTML'
                <table cellspacing="0">
                    <tr><td>Cell</td></tr>
                </table>
            HTML;

        $this->assertNoObsoleteAttributes($html);
    })->throws(AssertionFailedError::class, 'cellspacing');

    test('fails when width attribute is used on table', function () {
        $html = <<<'HTML'
                <table width="100%">
                    <tr><td>Cell</td></tr>
                </table>
            HTML;

        $this->assertNoObsoleteAttributes($html);
    })->throws(AssertionFailedError::class, 'width');

    test('fails when border attribute is used on img', function () {
        $html = <<<'HTML'
                <img src="image.jpg" border="0" alt="Image">
            HTML;

        $this->assertNoObsoleteAttributes($html);
    })->throws(AssertionFailedError::class, 'border');

    test('fails when name attribute is used on img', function () {
        $html = <<<'HTML'
                <img src="image.jpg" name="myimage" alt="Image">
            HTML;

        $this->assertNoObsoleteAttributes($html);
    })->throws(AssertionFailedError::class, 'name');

    test('fails when name attribute is used on anchor', function () {
        $html = <<<'HTML'
                <a name="section1">Section 1</a>
            HTML;

        $this->assertNoObsoleteAttributes($html);
    })->throws(AssertionFailedError::class, 'name');

    test('fails when charset attribute is used on anchor', function () {
        $html = <<<'HTML'
                <a href="page.html" charset="UTF-8">Link</a>
            HTML;

        $this->assertNoObsoleteAttributes($html);
    })->throws(AssertionFailedError::class, 'charset');

    test('fails when valign attribute is used on td', function () {
        $html = <<<'HTML'
                <table>
                    <tr><td valign="top">Cell</td></tr>
                </table>
            HTML;

        $this->assertNoObsoleteAttributes($html);
    })->throws(AssertionFailedError::class, 'valign');

    test('fails when nowrap attribute is used on td', function () {
        $html = <<<'HTML'
                <table>
                    <tr><td nowrap>Cell</td></tr>
                </table>
            HTML;

        $this->assertNoObsoleteAttributes($html);
    })->throws(AssertionFailedError::class, 'nowrap');

    test('fails when height attribute is used on td', function () {
        $html = <<<'HTML'
                <table>
                    <tr><td height="100">Cell</td></tr>
                </table>
            HTML;

        $this->assertNoObsoleteAttributes($html);
    })->throws(AssertionFailedError::class, 'height');

    test('fails when summary attribute is used on table', function () {
        $html = <<<'HTML'
                <table summary="This is a summary">
                    <tr><td>Cell</td></tr>
                </table>
            HTML;

        $this->assertNoObsoleteAttributes($html);
    })->throws(AssertionFailedError::class, 'summary');

    test('fails when language attribute is used on script', function () {
        $html = <<<'HTML'
                <script language="JavaScript">alert('test');</script>
            HTML;

        $this->assertNoObsoleteAttributes($html);
    })->throws(AssertionFailedError::class, 'language');

    test('fails when frameborder attribute is used on iframe', function () {
        $html = <<<'HTML'
                <iframe src="page.html" frameborder="0"></iframe>
            HTML;

        $this->assertNoObsoleteAttributes($html);
    })->throws(AssertionFailedError::class, 'frameborder');

    test('fails when scrolling attribute is used on iframe', function () {
        $html = <<<'HTML'
                <iframe src="page.html" scrolling="no"></iframe>
            HTML;

        $this->assertNoObsoleteAttributes($html);
    })->throws(AssertionFailedError::class, 'scrolling');

    test('fails when marginwidth attribute is used on iframe', function () {
        $html = <<<'HTML'
                <iframe src="page.html" marginwidth="0"></iframe>
            HTML;

        $this->assertNoObsoleteAttributes($html);
    })->throws(AssertionFailedError::class, 'marginwidth');

    test('fails when hspace attribute is used on img', function () {
        $html = <<<'HTML'
                <img src="image.jpg" hspace="10" alt="Image">
            HTML;

        $this->assertNoObsoleteAttributes($html);
    })->throws(AssertionFailedError::class, 'hspace');

    test('fails when vspace attribute is used on img', function () {
        $html = <<<'HTML'
                <img src="image.jpg" vspace="10" alt="Image">
            HTML;

        $this->assertNoObsoleteAttributes($html);
    })->throws(AssertionFailedError::class, 'vspace');

    test('fails when clear attribute is used on br', function () {
        $html = <<<'HTML'
                <br clear="all">
            HTML;

        $this->assertNoObsoleteAttributes($html);
    })->throws(AssertionFailedError::class, 'clear');

    test('fails when color attribute is used on hr', function () {
        $html = <<<'HTML'
                <hr color="red">
            HTML;

        $this->assertNoObsoleteAttributes($html);
    })->throws(AssertionFailedError::class, 'color');

    test('fails when size attribute is used on hr', function () {
        $html = <<<'HTML'
                <hr size="2">
            HTML;

        $this->assertNoObsoleteAttributes($html);
    })->throws(AssertionFailedError::class, 'size');

    test('fails when noshade attribute is used on hr', function () {
        $html = <<<'HTML'
                <hr noshade>
            HTML;

        $this->assertNoObsoleteAttributes($html);
    })->throws(AssertionFailedError::class, 'noshade');

    test('fails when compact attribute is used on ul', function () {
        $html = <<<'HTML'
                <ul compact>
                    <li>Item</li>
                </ul>
            HTML;

        $this->assertNoObsoleteAttributes($html);
    })->throws(AssertionFailedError::class, 'compact');

    test('fails when type attribute is used on li', function () {
        $html = <<<'HTML'
                <ul>
                    <li type="circle">Item</li>
                </ul>
            HTML;

        $this->assertNoObsoleteAttributes($html);
    })->throws(AssertionFailedError::class, 'type');

    test('fails when background attribute is used on body', function () {
        $html = <<<'HTML'
                <body background="bg.jpg">Content</body>
            HTML;

        $this->assertNoObsoleteAttributes($html);
    })->throws(AssertionFailedError::class, 'background');

    test('fails when alink attribute is used on body', function () {
        $html = <<<'HTML'
                <body alink="#ff0000">Content</body>
            HTML;

        $this->assertNoObsoleteAttributes($html);
    })->throws(AssertionFailedError::class, 'alink');

    test('fails when link attribute is used on body', function () {
        $html = <<<'HTML'
                <body link="#0000ff">Content</body>
            HTML;

        $this->assertNoObsoleteAttributes($html);
    })->throws(AssertionFailedError::class, 'link');

    test('fails when vlink attribute is used on body', function () {
        $html = <<<'HTML'
                <body vlink="#800080">Content</body>
            HTML;

        $this->assertNoObsoleteAttributes($html);
    })->throws(AssertionFailedError::class, 'vlink');

    test('fails when text attribute is used on body', function () {
        $html = <<<'HTML'
                <body text="#000000">Content</body>
            HTML;

        $this->assertNoObsoleteAttributes($html);
    })->throws(AssertionFailedError::class, 'text');

    test('fails when scheme attribute is used on meta', function () {
        $html = <<<'HTML'
                <meta name="DC.date" scheme="YYYY-MM-DD" content="2024-01-01">
            HTML;

        $this->assertNoObsoleteAttributes($html);
    })->throws(AssertionFailedError::class, 'scheme');

    test('fails with multiple obsolete attributes on same element', function () {
        $html = <<<'HTML'
                <table width="100%" cellpadding="5" cellspacing="0" border="1" bgcolor="#ffffff">
                    <tr><td>Cell</td></tr>
                </table>
            HTML;

        $this->assertNoObsoleteAttributes($html);
    })->throws(AssertionFailedError::class, 'obsolete attribute');

    test('fails with multiple elements having obsolete attributes', function () {
        $html = <<<'HTML'
                <div align="center">
                    <h3 align="right">Heading</h3>
                </div>
            HTML;

        $this->assertNoObsoleteAttributes($html);
    })->throws(AssertionFailedError::class, 'obsolete attribute');

    test('passes with modern alternatives', function () {
        $html = <<<'HTML'
                <div style="text-align: center;">
                    <h3 style="text-align: right;">Heading</h3>
                    <table style="width: 100%; background-color: #ffffff;">
                        <tr><td style="vertical-align: top;">Cell</td></tr>
                    </table>
                </div>
            HTML;

        expect(fn () => $this->assertNoObsoleteAttributes($html))->not->toThrow(AssertionFailedError::class);
    });
});
