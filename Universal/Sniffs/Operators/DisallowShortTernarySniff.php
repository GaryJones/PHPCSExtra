<?php
/**
 * PHPCSExtra, a collection of sniffs and standards for use with PHP_CodeSniffer.
 *
 * @package   PHPCSExtra
 * @copyright 2020 PHPCSExtra Contributors
 * @license   https://opensource.org/licenses/LGPL-3.0 LGPL3
 * @link      https://github.com/PHPCSStandards/PHPCSExtra
 */

namespace PHPCSExtra\Universal\Sniffs\Operators;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHPCSUtils\Utils\Operators;

/**
 * Disallow the use of short ternaries.
 *
 * While short ternaries are useful when used correctly, the principle of them is
 * often misunderstood and they are more often than not used incorrectly, leading to
 * hard to debug issues and/or PHP warnings/notices.
 *
 * @since 1.0.0
 */
final class DisallowShortTernarySniff implements Sniff
{

    /**
     * Registers the tokens that this sniff wants to listen for.
     *
     * @since 1.0.0
     *
     * @return int[]
     */
    public function register()
    {
        return [\T_INLINE_THEN];
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
        if (Operators::isShortTernary($phpcsFile, $stackPtr) === false) {
            $phpcsFile->recordMetric($stackPtr, 'Ternary usage', 'long');
            return;
        }

        $phpcsFile->recordMetric($stackPtr, 'Ternary usage', 'short');

        $phpcsFile->addError(
            'Using short ternaries is not allowed as they are rarely used correctly',
            $stackPtr,
            'Found'
        );
    }
}
