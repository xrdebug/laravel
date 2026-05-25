<?php

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevere.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Chevere\Tests\Watchers;

use Chevere\Tests\TestCase;
use Chevere\xrDebug\Laravel\Watchers\MailWatcher;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Mail\Events\MessageSending;
use Symfony\Component\Mime\Email;

final class MailWatcherTest extends TestCase
{
    public function testDefaultsToEnabled(): void
    {
        /** @var MailWatcher $watcher */
        $watcher = $this->app->make(MailWatcher::class);
        $this->assertTrue($watcher->isEnabled());
    }

    public function testConcernsLoggedMailReturnsTrueForMailMessage(): void
    {
        $watcher = new MailWatcher();
        $message = new MessageLogged('debug', "Message-ID: <abc@example.com>\nTo: user@example.com", []);
        $this->assertTrue($watcher->concernsLoggedMail($message));
    }

    public function testConcernsLoggedMailReturnsFalseForPlainLog(): void
    {
        $watcher = new MailWatcher();
        $message = new MessageLogged('info', 'just a plain log', []);
        $this->assertFalse($watcher->concernsLoggedMail($message));
    }

    public function testMessageSendingEventSendsMessage(): void
    {
        $email = (new Email())
            ->from('sender@example.com')
            ->to('recipient@example.com')
            ->subject('Test')
            ->html('<p>Hello</p>');
        event(new MessageSending($email));
        $this->assertSentCount(1);
    }

    public function testMessageSendingTopic(): void
    {
        $email = (new Email())
            ->from('sender@example.com')
            ->to('recipient@example.com')
            ->subject('Test')
            ->text('Hello');
        event(new MessageSending($email));
        $this->assertSame('Mail', $this->lastMessage()->topic());
    }

    public function testDisabledWatcherDoesNotSend(): void
    {
        $this->disableWatcher(MailWatcher::class);
        $email = (new Email())
            ->from('sender@example.com')
            ->to('recipient@example.com')
            ->subject('Test')
            ->text('Hello');
        event(new MessageSending($email));
        $this->assertNoMessagesSent();
    }

    public function testLegacyMessageLoggedMatchSendsMessage(): void
    {
        $watcher = new class() extends MailWatcher {
            protected function supportsMessageSendingEvent(): bool
            {
                return false;
            }
        };
        $watcher->enable();
        $watcher->register();
        $message = new MessageLogged('debug', "Message-ID: <abc@example.com>\nTo: user@example.com\nsome body", []);
        event($message);
        $this->assertSentCount(1);
    }

    public function testLegacyMessageLoggedMatchTopic(): void
    {
        $watcher = new class() extends MailWatcher {
            protected function supportsMessageSendingEvent(): bool
            {
                return false;
            }
        };
        $watcher->enable();
        $watcher->register();
        $message = new MessageLogged('debug', "Message-ID: <abc@example.com>\nTo: user@example.com\nsome body", []);
        event($message);
        $this->assertSame('Mail', $this->lastMessage()->topic());
    }

    public function testLegacyMessageLoggedNoMatchDoesNotSend(): void
    {
        $watcher = new class() extends MailWatcher {
            protected function supportsMessageSendingEvent(): bool
            {
                return false;
            }
        };
        $watcher->enable();
        $watcher->register();
        event(new MessageLogged('info', 'just a plain log without mail headers', []));
        $this->assertNoMessagesSent();
    }

    public function testLegacyDisabledWatcherDoesNotSend(): void
    {
        $watcher = new class() extends MailWatcher {
            protected function supportsMessageSendingEvent(): bool
            {
                return false;
            }
        };
        $watcher->register();
        $watcher->disable();
        $message = new MessageLogged('debug', "Message-ID: <abc@example.com>\nTo: user@example.com\nsome body", []);
        event($message);
        $this->assertNoMessagesSent();
    }

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('xr.show_mails', true);
        $app['config']->set('xr.show_logs', false);
    }
}
