<?php

test('free analysis page renders', function () {
    $response = $this->get(route('free-analysis'));

    $response
        ->assertOk()
        ->assertSee('AI Text Analyzer')
        ->assertSee('Input Text')
        ->assertSee('Analyze Text');
});

test('home page analyze button links to free analysis route', function () {
    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSee('href="'.route('free-analysis').'"', false)
        ->assertSee('wire:navigate', false);
});

test('free analysis page pricing link uses livewire navigation', function () {
    $response = $this->get(route('free-analysis'));

    $response
        ->assertOk()
        ->assertSee('href="'.route('home').'#pricing"', false)
        ->assertSee('wire:navigate', false);
});
