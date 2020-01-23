# flood-control

Install 
```shell script
composer install makm/flood-control
```


Configuration simple:
```php
$redis  = new \Redis();
$redis->connect('localhost');
$provider = new \Makm\FloodControl\AttemptProvider($redis);
$floodControl = new \Makm\FloodControl\FloodControl(
$provider,
[
    ['period' => \Makm\FloodControl\Limitations::PERIOD_WEEK, 'amount' => 5, 'times' => 15, 'sms-code'],
    ['period' => \Makm\FloodControl\Limitations::PERIOD_WEEK, 'amount' => 3, 'times' => 3, 'mail'],
    ['period' => \Makm\FloodControl\Limitations::PERIOD_DAY, 'amount' => 3, 'times' => 10, 'sms-code'],
    ['period' => \Makm\FloodControl\Limitations::PERIOD_DAY, 'amount' => 1, 'times' => 2, 'mail'],
    ['period' => \Makm\FloodControl\Limitations::PERIOD_MONTH, 'amount' => 1, 'times' => 200, 'sms-code'],
]);

$result = $floodControl->doAttempt(new Action('sms-code', '+155555555'));

$result = $floodControl->allow(new Action('mail', '+155555555'));

```

Symfony:
```yaml
  Makm\FloodControl\FloodControl:
    arguments:
      - '@Makm\FloodControl\AttemptProvider\RedisProvider'
      - [
          {period: minute, amount: 1, times: 1, group: confirm-phone }
          {period: hour, amount: 1, times: 5, group: confirm-phone }
          {period: day, amount: 1, times: 10, group: confirm-phone }
        ]
```
