<?php

/**
 * support macro for IDE
 * see https://ryangjchandler.co.uk/posts/macros-in-laravel#content-intellisense
 */
namespace Illuminate\Support
{
    /** @see \Illuminate\Support\Enumerable */
    interface Enumerable
    {
        public function getTotal(): int;
    }
}