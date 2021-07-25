<?php
/**
 * PHPCSExtra, a collection of sniffs and standards for use with PHP_CodeSniffer.
 *
 * @package   PHPCSExtra
 * @copyright 2020 PHPCSExtra Contributors
 * @license   https://opensource.org/licenses/LGPL-3.0 LGPL3
 * @link      https://github.com/PHPCSStandards/PHPCSExtra
 */

namespace PHPCSExtra\Universal\Sniffs\Files;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;
use PHPCSUtils\Tokens\Collections;
use PHPCSUtils\Utils\FunctionDeclarations;

/**
 * A file should either declare (global/namespaced) functions or declare OO structures, but not both.
 *
 * Nested function declarations, i.e. functions declared within a function/method will be disregarded
 * for the purposes of this sniff.
 * The same goes for anonymous classes, closures and arrow functions.
 *
 * Notes:
 * - This sniff has no opinion on side effects. If you want to sniff for those, use the PHPCS
 *   native `PSR1.Files.SideEffects` sniff.
 * - This sniff has no opinion on multiple OO structures being declared in one file.
 *   If you want to sniff for that, use the PHPCS native `Generic.Files.OneObjectStructurePerFile` sniff.
 *
 * @since 1.0.0
 */
class SeparateFunctionsFromOOSniff implements Sniff
{

    /**
     * Tokens this sniff searches for.
     *
     * Enhanced from within the register() methods.
     *
     * @since 1.0.0
     *
     * @var array
     */
    private $search = [
        // Some tokens to help skip over structures we're not interested in.
        \T_START_HEREDOC => \T_START_HEREDOC,
        \T_START_NOWDOC  => \T_START_NOWDOC,
    ];

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function register()
    {
        $this->search += Tokens::$ooScopeTokens;
        $this->search += Collections::functionDeclarationTokens();

        return Collections::phpOpenTags();
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @since 1.0.0
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token
     *                                               in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $firstOO       = null;
        $firstFunction = null;
        $functionCount = 0;
        $OOCount       = 0;

        for ($i = 0; $i < $phpcsFile->numTokens; $i++) {
            // Ignore anything within square brackets.
            if ($tokens[$i]['code'] !== \T_OPEN_CURLY_BRACKET
                && isset($tokens[$i]['bracket_opener'], $tokens[$i]['bracket_closer'])
                && $i === $tokens[$i]['bracket_opener']
            ) {
                $i = $tokens[$i]['bracket_closer'];
                continue;
            }

            // Skip past nested arrays, function calls and arbitrary groupings.
            if ($tokens[$i]['code'] === \T_OPEN_PARENTHESIS
                && isset($tokens[$i]['parenthesis_closer'])
            ) {
                $i = $tokens[$i]['parenthesis_closer'];
                continue;
            }

            // Skip over potentially large docblocks.
            if ($tokens[$i]['code'] === \T_DOC_COMMENT_OPEN_TAG
                && isset($tokens[$i]['comment_closer'])
            ) {
                $i = $tokens[$i]['comment_closer'];
                continue;
            }

            // Ignore everything else we're not interested in.
            if (isset($this->search[$tokens[$i]['code']]) === false) {
                continue;
            }

            // Skip over structures which won't contain anything we're interested in.
            switch ($tokens[$i]['type']) {
                case 'T_START_HEREDOC':
                case 'T_START_NOWDOC':
                case 'T_ANON_CLASS':
                case 'T_CLOSURE':
                case 'T_FN':
                    if (isset($tokens[$i]['scope_condition'], $tokens[$i]['scope_closer'])
                        && $tokens[$i]['scope_condition'] === $i
                    ) {
                        $i = $tokens[$i]['scope_closer'];
                    }
                    continue 2;

                case 'T_STRING':
                    $arrowOpenClose = FunctionDeclarations::getArrowFunctionOpenClose($phpcsFile, $i);
                    if ($arrowOpenClose !== false && isset($arrowOpenClose['scope_closer'])) {
                        $i = $arrowOpenClose['scope_closer'];
                    }
                    continue 2;
            }

            // This will be either a function declaration or an OO declaration token.
            if ($tokens[$i]['code'] === \T_FUNCTION) {
                if (isset($firstFunction) === false) {
                    $firstFunction = $i;
                }

                ++$functionCount;
            } else {
                if (isset($firstOO) === false) {
                    $firstOO = $i;
                }

                ++$OOCount;
            }

            if (isset($tokens[$i]['scope_closer']) === true) {
                $i = $tokens[$i]['scope_closer'];
            }
        }

        if ($functionCount > 0 && $OOCount > 0) {
            $phpcsFile->recordMetric($stackPtr, 'Functions or OO declarations ?', 'Both function and OO declarations');

            $reportToken = \max($firstFunction, $firstOO);

            $phpcsFile->addError(
                'A file should either contain function declarations or OO structure declarations, but not both.'
                    . ' Found %d function declaration(s) and %d OO structure declaration(s).'
                    . ' The first function declaration was found on line %d;'
                    . ' the first OO declaration was found on line %d',
                $reportToken,
                'Mixed',
                [
                    $functionCount,
                    $OOCount,
                    $tokens[$firstFunction]['line'],
                    $tokens[$firstOO]['line'],
                ]
            );
        } elseif ($functionCount > 0) {
            $phpcsFile->recordMetric($stackPtr, 'Functions or OO declarations ?', 'Only function(s)');
        } elseif ($OOCount > 0) {
            $phpcsFile->recordMetric($stackPtr, 'Functions or OO declarations ?', 'Only OO structure(s)');
        } else {
            $phpcsFile->recordMetric($stackPtr, 'Functions or OO declarations ?', 'Neither');
        }

        // Ignore the rest of the file.
        return ($phpcsFile->numTokens + 1);
    }
}
