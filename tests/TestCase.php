<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Pengiriman Telegram dimatikan per default supaya tidak ada test
        // yang membuat request HTTP asli. Test Telegram menyalakannya lagi
        // lewat config()->set() pada metode masing-masing.
        config()->set('telegram.bot_token', '');
        config()->set('telegram.chat_id', '');

        // API key agent monitoring juga kosong per default; test API agent
        // mengisinya sendiri lewat config()->set().
        config()->set('services.monitoring.api_key', '');
    }
}
