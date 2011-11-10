<?php

/*
* This file is part of Spoon Library.
*
* (c) Davy Hellemans <davy@spoon-library.com>
*
* For the full copyright and license information, please view the license
* file that was distributed with this source code.
*/

namespace Spoon\Template\Parser;
use Spoon\Template\TokenStream;
use Spoon\Template\Environment;
use Spoon\Template\Writer;

/**
 * Class used to convert text tokens into PHP code.
 *
 * @author Davy Hellemans <davy@spoon-library.com>
 */
class TextNode
{
	/**
	 * @var Spoon\Template\Environment
	 */
	protected $environment;

	/**
	 * @var Spoon\Template\TokenStream
	 */
	protected $stream;

	/**
	 * @param Spoon\Template\TokenStream
	 * @param Spoon\Template\Environment
	 */
	public function __construct(TokenStream $stream, Environment $environment)
	{
		$this->stream = $stream;
		$this->environment = $environment;
	}

	/**
	 * Writes the compiled PHP code to the writer object.
	 *
	 * @param Spoon\Template\Writer $writer
	 */
	public function compile(Writer $writer)
	{
		$value = $this->stream->getCurrent()->getValue();
		$line = $this->stream->getCurrent()->getLine();

		$output = "echo '" . str_replace('\\"', '"', addslashes($value)) . "';\n";
		$writer->write($output, $this->stream->getCurrent()->getline());
	}
}