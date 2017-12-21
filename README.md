# encrypter
## install
> composer install badtomcat/encrypter

## usage
```
$test = new \Badtomcat\Encrypter\Xorox();
$ret = $test->encode("balabala"); //encode
$test->decode($ret); //decode
```
----------------

```
$test = new \Badtomcat\Encrypter\Base32();
$ret = $test->encode("balabala"); //encode
$test->decode($ret); //decode
```

