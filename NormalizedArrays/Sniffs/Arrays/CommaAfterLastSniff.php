<?php
/**
 * PHPCSExtra, a collection of sniffs and standards for use with PHP_CodeSniffer.
 *
 * @package   PHPCSExtra
 * @copyright 2020 PHPCSExtra Contributors
 * @license   https://opensource.org/licenses/LGPL-3.0 LGPL3
 * @link      https://github.com/PHPCSStandards/PHPCSExtra
 */

namespace PHPCSExtra\NormalizedArrays\Sniffs\Arrays;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;
use PHPCSUtils\Utils\Arrays;

/**
 * Enforce/forbid a comma after the last item in an array declaration.
 *
 * Allows for different settings for single-line and multi-line arrays.
 *
 * @since 1.0.0
 */
final class CommaAfterLastSniff implements Sniff
{

    /**
     * Whether or not to enforce a comma after the last array item in a single-line array.
     *
     * Valid values:
     * - 'enforce' to always demand a comma after the last item in single-line arrays;
     * - 'forbid'  to disallow a comma after the last item in single-line arrays;
     * - 'skip'    to ignore single-line arrays.
     *
     * Defaults to: 'forbid'.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $singleLine = 'forbid';

    /**
     * Whether or not to enforce a comma after the last array item in a multi-line array.
     *
     * Valid values:
     * - 'enforce' to always demand a comma after the last item in single-line arrays;
     * - 'forbid'  to disallow a comma after the last item in single-line arrays;
     * - 'skip'    to ignore multi-line arrays.
     *
     * Defaults to: 'enforce'.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $multiLine = 'enforce';

    /**
     * The input values to consider as valid for the public properties.
     *
     * Used for input validation.
     *
     * @since 1.0.0
     *
     * @var array
     */
    private $validValues = [
        'enforce' => true,
        'forbid'  => true,
        'skip'    => true,
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
        return [
            \T_ARRAY,
            \T_OPEN_SHORT_ARRAY,
            \T_OPEN_SQUARE_BRACKET,
        ];
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
        // Validate the property input. Invalid values will result in the check being skipped.
        if (isset($this->validValues[$this->singleLine]) === false) {
            $this->singleLine = 'skip';
        }
        if (isset($this->validValues[$this->multiLine]) === false) {
            $this->multiLine = 'skip';
        }

        $openClose = Arrays::getOpenClose($phpcsFile, $stackPtr);
        if ($openClose === false) {
            // Short list, real square bracket, live coding or parse error.
            return;
        }

        $tokens = $phpcsFile->getTokens();
        $opener = $openClose['opener'];
        $closer = $openClose['closer'];

        $action    = $this->singleLine;
        $phrase    = 'single-line';
        $errorCode = 'SingleLine';
        if ($tokens[$opener]['line'] !== $tokens[$closer]['line']) {
            $action    = $this->multiLine;
            $phrase    = 'multi-line';
            $errorCode = 'MultiLine';
        }

        if ($action === 'skip') {
            // Nothing to do.
            return;
        }

        $lastNonEmpty = $phpcsFile->findPrevious(Tokens::$emptyTokens, ($closer - 1), $opener, true);
        if ($lastNonEmpty === false || $lastNonEmpty === $opener) {
            // Bow out: empty array.
            return;
        }

        $isComma = ($tokens[$lastNonEmpty]['code'] === \T_COMMA);

        $phpcsFile->recordMetric(
            $stackPtr,
            \ucfirst($phrase) . ' array - comma after last item',
            ($isComma === true ? 'yes' : 'no')
        );

        switch ($action) {
            case 'enforce':
                if ($isComma === true) {
                    return;
                }

                $error     = 'There should be a comma after the last array item in a %s array.';
                $errorCode = 'Missing' . $errorCode;
                $data      = [$phrase];
                $fix       = $phpcsFile->addFixableError($error, $lastNonEmpty, $errorCode, $data);
                if ($fix === true) {
                    $extraContent = ',';

                    if ($tokens[$lastNonEmpty]['code'] === \T_END_HEREDOC
                        || $tokens[$lastNonEmpty]['code'] === \T_END_NOWDOC
                    ) {
                        // Prevent parse errors in PHP < 7.3 which doesn't support flexible heredoc/nowdoc.
                        $extraContent = $phpcsFile->eolChar . $extraContent;
                    }

                    $phpcsFile->fixer->addContent($lastNonEmpty, $extraContent);
                }

                return;

            case 'forbid':
                if ($isComma === false) {
                    return;
                }

                $error     = 'A comma after the last array item in a %s array is not allowed.';
                $errorCode = 'Found' . $errorCode;
                $data      = [$phrase];
                $fix       = $phpcsFile->addFixableError($error, $lastNonEmpty, $errorCode, $data);
                if ($fix === true) {
                    $start = $lastNonEmpty;
                    $end   = $lastNonEmpty;

                    // Make sure we're not leaving a superfluous blank line behind.
                    $prevNonWhitespace = $phpcsFile->findPrevious(\T_WHITESPACE, ($lastNonEmpty - 1), $opener, true);
                    $nextNonWhitespace = $phpcsFile->findNext(\T_WHITESPACE, ($lastNonEmpty + 1), ($closer + 1), true);
                    if ($prevNonWhitespace !== false
                        && $tokens[$prevNonWhitespace]['line'] < $tokens[$lastNonEmpty]['line']
                        && $nextNonWhitespace !== false
                        && $tokens[$nextNonWhitespace]['line'] > $tokens[$lastNonEmpty]['line']
                    ) {
                        $start = ($prevNonWhitespace + 1);
                    }

                    $phpcsFile->fixer->beginChangeset();
                    for ($i = $start; $i <= $end; $i++) {
                        $phpcsFile->fixer->replaceToken($i, '');
                    }

                    $phpcsFile->fixer->endChangeset();
                }

                return;
        }
    }
}
