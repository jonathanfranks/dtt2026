# Talk Notes

Speaker prep for the MidCamp DTT talk. Not audience-facing. Not part of the published repo (gitignore if you want).

---

## Deck at a glance (current order — 26 slides)

1. STOP LOSING SLEEP — title
2. 3:47 AM — pain hook (Madalyn Cox cat, dimmed background)
3. TESTING IN DRUPAL IS… A LOT. — orientation
4. Show of hands — three audience check-in questions
5. TESTS ARE PART OF THE WORK — section header
6. How do I convince my client to pay for automated testing? — the question (Govit Pimthong cat)
7. You don't. — the answer (Zoshua Colah cat) ← leave a beat
8. Things you don't itemize on a quote — the analogy (Composer, VCS, code review, config mgmt, local dev, linting, automated tests)
9. WHAT TO TEST. WHAT TO SKIP. — methodology header. Subtitle: *"Test what your client paid you to build."*
10. TEST … OR DON'T BOTHER? — the explicit list
11. But how?!?!?! — audience POV question (Maxim Tolchinskiy cat)
12. Your four options — Unit / Kernel / Functional / Behat, all with caveats
13. Wait, another option? — Beef and Dairy fifth-meat joke
14. DRUPAL TEST TRAITS. — the reveal
15. PHPUnit tests against your already-installed dev site — descriptor
16. Why it changes everything — Drupal APIs for test data, auto-cleanup
17. DEMOS! SHOW ME THE DTT! — combined demo header (DEMO 1 + DEMO 2 run from here)
18. TREASURES. STEAL THESE. — section header
19. Lift these from the repo — the inventory
20. The deal — closing principles (Gaelle Marcel cat)
21. Resources — `drupal.org/project/dtt` + slide repo URL ← **fill in `(add your URL)` before talk day**
22. THANK YOU, QUESTIONS? — Q&A starts (Drew cat)
23. PLEASE PROVIDE YOUR FEEDBACK — admin
24. CONTRIBUTION DAY — admin
25. Drupal Coffee Exchange — admin
26. Cat photo credits — attribution ← **slide numbers on this slide are stale, see "Credits slide" below for canonical text**

---

## Slide 19: TREASURES inventory — what to call out

Slide 18 ("TREASURES. STEAL THESE.") is the section header; slide 19 is the inventory itself. The audience reads the slide while you walk through:

- **`JackotopiaTestBase`** *(the base class — extend it and you get all of this for free)*
  - `drupalGet()` — pre-seeds the `big_pipe_nojs=1` cookie so BigPipe renders placeholders inline. No more silently-missing flash messages.
  - `drupalGet()` and `click()` — both auto-call `assertNoErrorMessage()` after the request. Every page load gets a free error check.
  - `assertNoErrorMessage()` — looks for `.messages--error`, `.alert-danger`, and the usual "unexpected error / Notice / Warning" texts.
  - `clickLinkWithExplicitErrorMessageCheck()` — opt out of the auto-check when a test *should* assert a specific error is present.
- **`ConfigTrait`** — `getConfig($name)` and `assertConfigValue($name, $key, $expected)`. One-liner config assertions at dotted key paths.
- **`DebugTrait::pauseForUserInput()`** — drop it in a running test, halts on STDIN. You click around the live site mid-test, press Return to continue. (Live demo'd during DEMOS!.)
- **`NodeContentTrait`** — `unpublishNodesOfBundle($bundle)` mass-unpublishes everything of a type and tracks IDs; `republishUnpublishedNodes()` restores them in tearDown. Lets a test assert "the empty state" without permanently destroying real content. Canonical demo: `NodeContentTraitTest::testPagerDisappearsBelowPageSize` — wipe articles, create one, assert `/two-per-page` no longer renders `nav.pager`. Structural assertion (the view's pagination markup), not just "is this string on the page."

**Speaker beat:** "Everything on this slide is in `web/modules/custom/jackotopia/tests/src/`. Open the file, copy the trait into your own module, change the namespace, you're done. The repo URL is on the next slide." Then move on.

**Bonus mentions** *(not on this slide, but worth a sentence elsewhere if time allows — these are infrastructure, not test traits)*: the `ddev install` command (composer + `drush si` + uuid swap + `cim`) and the `.github/workflows/ci.yml` two-trigger pattern. Both could anchor a follow-up post.

---

## Slide 17: DEMOS! — both demos run from here (~19 min total)

The deck has one combined "DEMOS!" header. Switch to the IDE/terminal and run DEMO 1, then jump to a GitHub tab for DEMO 2 without coming back to a slide in between.

### DEMO 1 — Writing Your First Test (~12 min)

Goal: get attendees to *see* DTT working and *believe* they could do it themselves.

Path:

1. **`CoreStuffTest::testUserLogin`** (~1 min) — Three lines. `createUser`, `drupalLogin`. Run it. Green. "That's a test."
2. **`CoreStuffTest::testErrorAssertionsFail`** (~3 min) — Run it. Red. Walk through *why*: visit a route that pushes an error message, the base class catches `.messages--error` automatically, the test fails on the page load before you've written any error assertion. **This is the BigPipe story.** Tell it: "the messages aren't even in the response by default — BigPipe ships them as a placeholder that needs JS to materialize. We pre-seed the no-JS cookie in `drupalGet` so they render server-side." That's the diagnostic moment that gets them.
3. **`TestableViewTest`** (~3 min) — Both methods. Behavioral test creates Charlie/Bravo/Alpha (reverse alphabetical creation order!) and asserts the view renders them in alphabetical order. Config test reads `views.view.testable_view` and asserts the sort. **Same property, two angles.** This is the talk's thesis in miniature.
4. **`EntityCrawlerStuffTest::testArticleTeaserTrimsTheBody`** (~3 min) — Same node, two view modes, observably different output, no HTTP. The "I didn't even know you could do that" beat.
5. **`WeatherServiceTest::testGetCurrentConditionsReturnsSomething`** (~2 min) — Live API hit. We don't assert today's temperature, we assert *shape and range*. Discipline matters.
6. **`NodeContentTraitTest::testPagerDisappearsBelowPageSize`** (~2 min, optional if pacing allows) — Pager appears with 3 articles, gone with 1. The trait wipes the slate, the test creates one fresh node, the assertion is on the *absence* of `nav.pager`. Sells "test isolation without nuking real content" in two visible state changes. Cut this one first if running long.

Things to skim past (mention, don't run):
- Other `ConfigStuffTest` cases
- `WeatherBlockTest` (covered as concept in the dual-angle point)
- `EntityCrawlerStuffTest::testPoweredByBlockPlugin`

### DEMO 2 — CI / CD (~7 min)

Goal: connect "I wrote a test" to "the team is protected by it."

Path:

1. **Open `.github/workflows/ci.yml`** (~2 min) — Walk through the two jobs. `tests` runs `ddev install` then `ddev phpunit`. `quality` runs `ddev cs` and `ddev phpstan`. Note the `--exclude-group intentionally_failing` — connect it back to the test we saw fail in DEMO 1.
2. **The two triggers** (~2 min) — `pull_request` for PR validation (the bullet from the abstract). `push` for "I want a full run going while I work locally." This is your real-world workflow: when local runs take hours, GH is your CI cluster. Parallelize across jobs and a 1100-test suite finishes in 30 minutes while you're working on the next thing.
3. **A real run** (~2 min) — Switch to a GitHub tab, show a recent green build. If you have a deliberately-broken example PR with red checks, even better.
4. **The "tests are part of the work" callback** (~1 min) — "This is what 'you don't ask the client to pay for it' looks like in practice. The CI is just there. Like Composer."

---

## Misc speaker notes

- **Slide 4 (Show of hands)** — three groups: needs convincing / needs to convince a boss / doesn't know how. Each group has a different home in the deck:
  - "needs convincing" → slides 5–8 (the philosophy arc)
  - "convince a boss/client" → slides 6–8 (especially "Things you don't itemize")
  - "doesn't know how" → slide 17 onward (DEMOS! + treasures inventory)
  Knowing which third is biggest tells you which beat to push hardest, even if you don't comment on the count. Keep it brief — count, react, move on.
- **Slide 7 ("You don't.")** — leave a beat. Don't fill the silence. Slide 8 ("Things you don't itemize") is the answer.
- **Slide 11 ("But how?!?!?!")** — comedic transition that mirrors slide 13's "Wait, another option?" beat structurally. The cat is asking the audience's question — let the joke land before clicking forward.
- **BigPipe** — surfaces naturally during DEMO 1's `testErrorAssertionsFail`. Don't dwell on it anywhere else; the demo is where it lands.
- **Slide 21 (Resources)** — has `(add your URL)` for the slide repo. Fill in before talk day.

---

## Cat images (free-with-attribution)

All Unsplash unless noted. Click through to download. Photographer names are what go on the credits slide.

| Slide | Vibe | Photo | Photographer |
|---|---|---|---|
| **2** — "It's 3:47 AM…" | dark room, cat awake on bed | <https://unsplash.com/photos/wqID2dZz8NU> | Madalyn Cox |
| **6** — "How do I convince my client…?" | head-tilt, confused | <https://www.pexels.com/photo/cat-tilting-its-head-curiously-18726814/> *(Pexels)* | Govit Pimthong |
| **7** — "You don't." | smug, patient, staring you down | <https://unsplash.com/photos/a-cat-sits-patiently-looking-at-the-camera-yfBlPkv8BcI> | Zoshua Colah |
| **11** — "But how?!?!?!" | surprised wide-eyed face | <https://unsplash.com/photos/a-close-up-of-a-cat-with-a-surprised-look-on-its-face-i9CTUYsuuM4> | Maxim Tolchinskiy |
| **20** — "The deal" (closing) | peacefully sleeping (callback to slide 2) | <https://unsplash.com/photos/an-orange-cat-sleeps-peacefully-on-a-bed-f6ZQIQ6wAmE> | Gaelle Marcel |
| **22** — "Thank you, questions?" | paw raised, attentive | <https://unsplash.com/photos/a-tabby-cat-sits-with-one-paw-raised-ppC8AnHbKU0> | Drew |

Cats removed from the deck (didn't work over the purple section-header slides): Unma Desai (was slide 3), Rodrigo dos Reis (was slide 18).

### Credits slide (canonical text — matches the deck)

> **Cat photos**
>
> Slide 2 — Madalyn Cox (Unsplash)
> Slide 6 — Govit Pimthong (Pexels)
> Slide 7 — Zoshua Colah (Unsplash)
> Slide 11 — Maxim Tolchinskiy (Unsplash)
> Slide 20 — Gaelle Marcel (Unsplash)
> Slide 22 — Drew (Unsplash)
>
> Thanks to the photographers and to the Unsplash and Pexels communities. Free-with-attribution licensing makes talks like this possible.
