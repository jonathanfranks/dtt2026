<?php

declare(strict_types=1);

namespace Drupal\Tests\jackotopia\Traits;

/**
 * Trait that contains debugging functions.
 */
trait DebugTrait {

  /**
   * Pauses the test until user presses a key. Useful when debugging a scenario.
   *
   * When you want to pause a test, use this trait and pauseForUserInput() to
   * halt execution of the test without pausing the site.
   */
  protected function pauseForUserInput(): void {
    fwrite(STDOUT, "\033[s \033[93m[Breakpoint] Press \033[1;93m[RETURN]\033[0;93m to continue, or 'q' to quit...\033[0m");
    do {
      $input = fgets(STDIN, 1024);
      if ($input === FALSE) {
        throw new \RuntimeException('Could not read from STDIN while paused.');
      }
      $line = trim($input);
      // Note: this assumes ASCII encoding.  Should probably be revamped to
      // handle other character sets.
      $charCode = ord($line);
      switch ($charCode) {
        // Empty line (Return only — trim() drops the \n, ord('') is 0).
        case 0:
          // Lowercase 'y'.
        case 121:
          // Uppercase 'Y'.
        case 89:
          break 2;

        // Lowercase 'q'.
        case 113:
          // Uppercase 'Q'.
        case 81:
          throw new \Exception("Exiting test intentionally.");

        default:
          fwrite(STDOUT, sprintf("\nInvalid entry '%s'.  Please enter 'y', 'q', or the enter key.\n", $line));
          break;
      }
    } while (TRUE);
    fwrite(STDOUT, "\033[u");
  }

}
