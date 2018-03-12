# Pwned Check Validator for Laravel

Laravel validation rule to determine whether a password has appeared in a set of known compromised passwords. This is a PHP wrapper for the [Pwned Check](https://github.com/square1-io/pwned-check) utility class, making use of the [Pwned Passwords](https://haveibeenpwned.com/Passwords) service provided by [Troy Hunt](https://www.troyhunt.com/).


## Install

Via Composer

``` bash
$ composer require square1/pwned-check-laravel
```

### Laravel 5.5+
If you're using Laravel 5.5+, then the package will be auto-discovered.

### Laravel <= 5.4

To use the Pwned Check validation rule, you must register the provider when bootstrapping your Laravel application.

Find the `providers` key in your `config/app.php` and add the below.

```php
    'providers' => array(
        // ...
        Square1\Laravel\PwnedCheck\Providers\PwnedCheckServiceProvider::class,
    )
```

### Publishing config file

```
php artisan vendor:publish --provider="Square1\Laravel\PwnedCheck\Providers\PwnedCheckServiceProvider" --tag=config
```

This will publish the configuration file to `pwned-check.php`.


## Configuration Options

A number of configuration options are available to modify the behaviour of the class.

| Option | Default | Comment |
|--------|---------|---------|
| `endpoint` | `https://api.pwnedpasswords.com/range/` | Service endpoint url |
| `user_agent` | `Square1 Pwned PHP package` | User agent to use - api calls without a user agent are rejected |
| `connection_timeout` | `0` | Initial curl connection limit (0 for off). If connection takes longer than X seconds to establish, it's terminated |
| `remote_processing_timeout` | `0` | Number of seconds after which to kill a slow-responding connection (0 for off) |
| `minimum_occurrences` | `1` | Minimum number of times a password needs to appear in breaches before being considered compromised |


## Usage

``` php
    // RegisterController.php
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            // Reject any password that has appeared in the list of compromised ones
            'password' => 'required|string|min:6|confirmed|pwned',
        ]);
    }

    // ...
    // Reject any password that has appeared in the list of compromised ones more than ten times
    'password' => 'required|string|min:6|confirmed|pwned:10',

```


## FAQ

### How do I set the validation error message shown?

In the `resources/lang/{LANG}/validation` for each language your app runs in, the message can be set within the `custom` array:

```
    'custom' => [
        'password' => [
            'pwned' => 'The :attribute has appeared in a known set of compromised passwords. Please choose a different password.',
        ],
    ],
```

### How do you decide if a password is "known compromised"?

The [Pwned Passwords](https://haveibeenpwned.com/Passwords) service provided by [Troy Hunt](https://www.troyhunt.com/) is a great resource that aggregates passwords found in known data breaches. The api allows us to check whether a password has appeared in previous data breaches, and also how frequently it shows up. The frequency allows us to decide how strict we want to be when deciding if a password is to be considered compromised. For example, `abcd1234` may show up 334,000 times in data breaches, while `totallyuniqueandrandompass1234` may only show up once. Depending on your use case, it may be appropriate to only blacklist widely compromised passwords. The frequency count is what allows us to do this.


### Does sending a password to the service not constitute a security risk?

The Pwned password api allows for range queries to be made. This involves hashing the password via this library within your application, and sending a partial section of it to the api. The api returns a set of password hashes (and frequency counts for each). These can then be matched against the full password hash, which never needs to leave the application. Cloudflare worked closely with Troy on the design of this api, and go into a lot more detail on this approach to using k-anonymity in [this blog post](https://blog.cloudflare.com/validating-leaked-passwords-with-k-anonymity/).


### What if the api server is slow to respond? Will my app have problems?

Typical api responses are blazingly-fast - the article [here](https://www.troyhunt.com/i-wanna-go-fast-why-searching-through-500m-pwned-passwords-is-so-quick/) is worth a read. However, it's possible that at some point there'll be a connection issue or some other performance issue with the service. To protect your app in these cases, you can set the `connection_timeout` and `remote_processing_timeout` config values. These are the seconds to wait before killing a curl connection and wait time after connection respectively. If the service call is terminated due to one of these timeouts being reached, a `Square1\Pwned\Exception\ConnectionFailedException` will be thrown.


### If the api does time out, does that behave the same as if the password is compromised?

This can be controlled through the `fail_on_timeout` config value. When it is set to `true`, any connection failure will be treated as a validation failure. However, you may wish to have this compromised check as a less critical one, so in the event of a remote service failure you'd prefer that your user registration continues unimpacted. Setting this value to `false` will mean that a connection failure won't trigger a validation failure.


### Are the api results cached?

The api results are cached for a day by default. This value can be altered in the `cache_default_ttl` config variable.


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.