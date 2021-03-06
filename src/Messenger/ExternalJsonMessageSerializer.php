<?php


namespace App\Messenger;


use App\Message\Command\LogEmoji;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\MessageDecodingFailedException;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class ExternalJsonMessageSerializer implements SerializerInterface
{

    public function decode(array $encodedEnvelope): Envelope
    {
        $body = $encodedEnvelope['body'];
        $headers = $encodedEnvelope['headers'];

        $data = json_decode($body, true);
        if (null === $data) {
            throw new MessageDecodingFailedException('Invalid JSON');
        }
        $message = new LogEmoji($data['emoji']);

        // in case of redelivery, unserialize any stamps
        $stamps = [];
        if(isset($headers['stamps'])) {
            $stamps = unserialize($headers['stamps']);
        }

        $envelope = new Envelope($message, $stamps);
        // needed only if you need this to be sent through the non-default bus
        $envelope = $envelope->with(new BusNameStamp('command.bus'));

        return $envelope;
    }

    public function encode(Envelope $envelope): array
    {
        throw new \Exception('Transport & serializer not meant for sending messages');
    }
}