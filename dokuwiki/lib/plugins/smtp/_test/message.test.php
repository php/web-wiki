<?php
/**
 * General tests for the smtp plugin
 *
 * @group plugin_smtp
 * @group plugins
 */
class message_plugin_smtp_test extends DokuWikiTest {
    public function setUp() {
        parent::setUp();
        require_once __DIR__ . '/../loader.php';
    }

    public function test_body() {
        $input = trim('
X-Mailer: DokuWiki
X-Dokuwiki-User: admin
X-Dokuwiki-Title: Test Wiki
X-Dokuwiki-Server: localhost.localhost
From: a@example.com
To: b@example.com
Bcc: c@example.com, d@example.com,
    d@example.com
Subject: A test

This is the body of the mail
Bcc: this is not a header line
end of message
');

        $expect = trim('
X-Mailer: DokuWiki
X-Dokuwiki-User: admin
X-Dokuwiki-Title: Test Wiki
X-Dokuwiki-Server: localhost.localhost
From: a@example.com
To: b@example.com
Subject: A test

This is the body of the mail
Bcc: this is not a header line
end of message
');
        $expect .= "\r\n\r\n.\r\n";

        $message = new \splitbrain\dokuwiki\plugin\smtp\Message('','',$input);

        $this->assertEquals($expect, $message->toString());

    }
}
