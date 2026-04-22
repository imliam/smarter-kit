<?php

declare(strict_types=1);

test('example', function (): void {
    $page = visit('/');

    $page->assertSee("Let's get started");
});
