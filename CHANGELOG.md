# Beanstalkd lib

## 0.5.2 (2020-07-20)

* **[FIXED]** Read `\r\n` on empty payload

## 0.5.1 (2020-07-16)

* **[FIXED]** Don't go to the socket with a 0 length payload

## 0.5.0 (2020-04-11)

* **[CHANGED]** Minimum supported `zlikavac32/php-enum` version is 3.0.0
* **[CHANGED]** Typed properties are now used (PHP 7.4 is a minimum version to be used)

## 0.4.0 (2019-08-23)

* **[CHANGED]** Tube purger and client can now accept job states to flush
* **[CHANGED]** `Zlikavac32\BeanstalkdLib\Runner\JobObserverRunner` does not log `Zlikavac32\BeanstalkdLib\InterruptException`

## 0.3.0 (2019-05-04)

* **[CHANGED]** `Zlikavac32\BeanstalkdLib\Client` and `Zlikavac32\BeanstalkdLib\TubeHandle` support `flush` operation

## 0.2.0 (2019-05-03)

* **[REMOVED]** `Zlikavac32\BeanstalkdLib\Client\TubeConfiguration\TubeConfigurationFactory`
* **[CHANGED]** `Zlikavac32\BeanstalkdLib\JobDispatcher\TubeMapJobDispatcher` does not ignore `default` tube if there is no explicitly watched tube
* **[ADDED]** `Zlikavac32\BeanstalkdLib\Protocol\TraceableProtocol` and `Zlikavac32\BeanstalkdLib\Adapter\PHPUnit\Constraint\OrderedTracesExistInProtocol` to help with testing

## 0.1.1 (2019-04-26)

* **[CHANGED]** Bump `zlikavac32/alarm-scheduler` to `^0.2.1`

## 0.1.0 (2019-04-25)

* **[NEW]** First tagged version
