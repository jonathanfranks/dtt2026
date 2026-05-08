# Drupal Test Traits at MidCamp

Companion code for the talk. If you're here, you're either reading along during the session, or you walked away wanting to actually *try* this stuff on your own. Either way: clone it, run it, copy from it.

The site is intentionally small. It's a teaching artifact, not a real product — the contrived bits are the point. Each test in `web/modules/custom/jackotopia/tests/` exists to show off one specific thing [DTT](https://www.drupal.org/project/dtt) does well, and you should feel free to lift any of it for your own project.

## Get it running

You need DDEV. If you don't have it yet: <https://ddev.com/get-started/>.

Then, from the repo root:

```
ddev start
ddev install
```

`ddev start` brings up everything — Drupal 11 on PHP 8.4, MariaDB, nginx, plus a headless Chrome sidecar so the JS-driven tests have something to drive. The first start pulls images, so it takes a minute. After that it's quick.

`ddev install` does a `drush site:install` and then imports the config in `config/sync` on top, so the site you end up with matches what's checked into the repo — same uuid, same blocks placed in the sidebar, same view, same theme. Log in as `admin` / `admin`. **You only need to run this once**, unless you want to wipe the database and start over.

## Run the tests

It all hangs off one command:

```
ddev phpunit
```

That runs the whole suite. To narrow it down:

```
# Just one file
ddev phpunit web/modules/custom/jackotopia/tests/src/ExistingSite/WeatherServiceTest.php

# Just one method
ddev phpunit --filter testGetCurrentConditionsReturnsSomething

# Just one suite (existing-site, kernel, functional, etc.)
ddev phpunit --testsuite existing-site

# Pretty test names
ddev phpunit --testdox
```

`--testdox` is worth knowing. It prints test names as English sentences. Once it does, you start *writing* them like English sentences, and your test file turns into a readable spec without you doing anything extra.

## One test is supposed to fail

When you run the suite locally, `CoreStuffTest::testErrorAssertionsFail` will fail. **That's on purpose.** It's the demo for the auto-error guardrail in `JackotopiaTestBase`: every `drupalGet()` and `click()` quietly checks the resulting page for `.messages--error`, `.alert-danger`, and the usual "unexpected error / Notice / Warning" texts. The test visits a route that deliberately pushes an error message, Drupal renders it inside `.messages--error`, the guardrail spots that selector, and the test fails on the *page load* — before the test author even has to write an assertion about errors. That's the point: you get a free safety net under every test that uses the base class.

Comment out the `messenger()->addError(…)` call in `JackotopiaController` (which is what makes Drupal render the `.messages--error` element) and watch the test go green to confirm the mechanism.

CI excludes this test (it's tagged `@group intentionally_failing`) so the build badge stays green. If you want to see CI fail too, drop the `--exclude-group` flag in `.github/workflows/ci.yml`.
