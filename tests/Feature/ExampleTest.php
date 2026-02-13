<?php

test('home page renders the landing layout sections', function () {
    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSee('Know if your text')
        ->assertSee('Everything you need to verify your writing')
        ->assertSee('Three steps to authentic writing')
        ->assertSee('Simple, transparent pricing');
});
