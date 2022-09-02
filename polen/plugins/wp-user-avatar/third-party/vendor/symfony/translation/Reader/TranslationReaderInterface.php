<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ProfilePressVendor\Symfony\Component\Translation\Reader;

use ProfilePressVendor\Symfony\Component\Translation\MessageCatalogue;
/**
 * TranslationReader reads translation messages from translation files.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
interface TranslationReaderInterface
{
    /**
     * Reads translation messages from a directory to the catalogue.
     *
     * @param string $directory
     */
    public function read($directory, MessageCatalogue $catalogue);
}
