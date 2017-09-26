Spryker Fixture Loader
======================

Fixtures for Spryker

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

```bash
$ ./vendor/bin/console inviqa:fixture:load path/to/fixtures.yml
```

The console returns a JSON list of fixture names => ids, use `--no-progress-
to supress (most) other output:

```bash
$ ./vendor/bin/console inviqa:fixture:load tests/test.yml --no-progress
Store: DE | Environment: devtest
{"customer_1":56,"product_1":101}
```
