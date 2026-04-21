<?php

test('example', function () {
    $page = visit('/');
 
    $page->assertSee('Let\'s get started');
});
