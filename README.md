Key Value storage ![](https://travis-ci.org/go1com/kv.svg?branch=master) [![Latest Stable Version](https://poser.pugx.org/go1/kv/v/stable.svg)](https://packagist.org/packages/go1/kv) [![License](https://poser.pugx.org/go1/kv/license)](https://packagist.org/packages/go1/kv)
====

```php
$db = DriverManager::getConnection(['url' => 'sqlite://sqlite::memory:']);
$kv = new go1\kv\KV($db);

$kv->write('key', ['some' => 'value);
if ($kv->has('key')) {
    $kv->fetch('key');
    $kv->delete('key');
}
```
