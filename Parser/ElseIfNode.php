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
use Spoon\Template\SyntaxError;
use Spoon\Template\Token;
use Spoon\Template\TokenStream;
use Spoon\Template\Environment;
use Spoon\Template\Writer;

/**
 * Almost completely the same as the if node.
 *
 * @author Davy Hellemans <davy@spoon-library.com>
 */
class ElseIfNode extends IfNode
{
	/**
	 * Writes the compiled PHP code to the writer object.
	 *
	 * @param Spoon\Template\Writer $writer
	 */
	public function compile(Writer $writer)
	{
		$this->line = $this->stream->next()->getLine();

		$this->process();
		$this->output = trim($this->output);
		$this->output = str_replace(array('( ', '! '), array('(', '!'), $this->output);

		if($this->brackets != 0)
		{
			exit('Not all opened brackets were properly closed');
		}

		$writer->outdent();
		$writer->write('elseif(' . $this->output . "):\n");
		$writer->indent();
	}
}
