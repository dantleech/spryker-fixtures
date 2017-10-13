Spryker Fixture Loader
======================

Fixtures for Spryker

## Fixtures

```yaml
Orm\Zed\Customer\Persistence\SpyCustomer:
    customer_1:
        email: customer_1@example.com
        customerReference: "customer_1"

Orm\Zed\Product\Persistence\SpyProductAbstract:
    product_1:
        sku: 1234-1234-1234
        attributes: "{}"
```

References can be specified with `@`:

```yaml
Acme\Address:
    address_1:
        name: One
Acme\Person:
    person_1:
        address: @address_1
```

If a scalar value is required from a reference, a property can be specified
after `:`:

```yaml
Acme\Address:
    address_1:
        name: One
Acme\Person:
    person_1:
        address_id: @address_1:idAddress
```

Fixtures can be included relative to the current fixture file:

```
_include:
    - "common/lookuptables.yml"

FixtureOne:
    fixture_one:
        # ...
```

Included fixtures will be included before loading the rest of the file.

### Special Types

Special cases can be handled by using an array synxtax, currently only
constants are supported:

```
_include:
    - "common/lookuptables.yml"

FixtureOne:
    fixture_one:
        state_machine_name:
            type: constant
            constant: "Acme\\StateMachine::NAME"
```

### Installation

```diff
+++ b/src/Pyz/Zed/Console/ConsoleDependencyProvider.php
@@ -63,6 +63,7 @@ use Spryker\Zed\Transfer\Communication\Console\ValidatorConsole;
 use Spryker\Zed\Twig\Communication\Console\CacheWarmerConsole;
 use Spryker\Zed\ZedNavigation\Communication\Console\BuildNavigationConsole;
 use Stecman\Component\Symfony\Console\BashCompletion\CompletionCommand;
+use DTL\Spryker\Fixtures\Console\FixtureConsole;

 /**
  * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
@@ -146,6 +147,7 @@ class ConsoleDependencyProvider extends SprykerConsoleDependencyProvider
         $commands = array_merge($commands, $propelCommands);

         if (Environment::isDevelopment() || Environment::isTesting()) {
+            $commands[] = new FixtureConsole();
```

### Loading

```bash
$ ./vendor/bin/console inviqa:fixture:load path/to/fixtures.yml
```

### ID resolution

The console returns a JSON list of fixture names => ids, use `--no-progress-
to supress (most) other output:

```bash
$ ./vendor/bin/console inviqa:fixture:load tests/test.yml --no-progress
Store: DE | Environment: devtest
{"customer_1":56,"product_1":101}
```

This is useful when you have to load the fixtures from a remote process (e.g.
an Yves context) and have no access to the database.
