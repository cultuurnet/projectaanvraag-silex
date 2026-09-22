# Local SimpleBus JMSSerializerBridge

Vendored copy of [SimpleBus/JMSSerializerBridge](https://github.com/SimpleBus/JMSSerializerBridge)
(MIT, © 2015 Matthias Noback), declared in the root `composer.json` under `provide`.

## Why

`cultuurnet/search-v3` (>= v1.3.7) requires `simple-bus/jms-serializer-bridge: ^6.2`, which in turn
requires `simple-bus/serialization: ^6.0`. Pulling in `serialization` v6 forces `simple-bus/asynchronous`
and `simple-bus/message-bus` to v6 as well, and from there `simple-bus/rabbitmq-bundle-bridge` v6 —
which requires Symfony ^4.4. This application runs on Silex 2, which caps Symfony at 3.4, so the
v6 SimpleBus stack is unreachable until Silex is replaced.

The only thing `search-v3` uses from the bridge is `SerializerMetadata::directory()` /
`::namespacePrefix()`, and the only thing this application uses is `JMSSerializerObjectSerializer`.
Both are a handful of lines and are identical in behaviour between v1.0.3 and v6.2.2, so they are
vendored here and the package is satisfied via `provide`. That keeps the rest of the SimpleBus
stack on its current versions while allowing `jms/serializer` 3.x.

The sources match upstream v6.2.2, minus the PHP 7.4 parameter/return types — those cannot be added
while `SimpleBus\Serialization\ObjectSerializer` (v2.0.1) still declares untyped signatures.

## When to remove

Delete this directory, drop the `provide` entry and require `simple-bus/jms-serializer-bridge: ^6.2`
again once the application has moved off Silex to Symfony >= 4.4 and the whole SimpleBus stack can go to v6.