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
use Spoon\Template\Writer;

/**
 * Writes the if node to the writer.
 *
 * @author Davy Hellemans <davy@spoon-library.com>
 */
class IfNode extends Node
{
	/**
	 * Counter for all opened brackets.
	 *
	 * @var int
	 */
	protected $brackets;

	/**
	 * @var string
	 */
	protected $output;

	/**
	 * Makes sure the output is just a bit more clean.
	 */
	protected function cleanup()
	{
		$this->output = trim($this->output);
		$this->output = str_replace(array('( ', '! '), array('(', '!'), $this->output);
	}

	/**
	 * Writes the compiled PHP code to the writer object.
	 *
	 * @param Spoon\Template\Writer $writer
	 */
	public function compile(Writer $writer)
	{
		$this->stream->next();
		$this->process();
		$this->validateBrackets();
		$this->cleanup();

		$writer->write('if(' . $this->output . "):\n", $this->line);
		$writer->indent();
	}

	/**
	 * Processes the if syntax.
	 *
	 * @todo document me inline
	 */
	protected function process()
	{
		$token = $this->stream->getCurrent();

		if($token->test(Token::NAME))
		{
			if(substr($token->getValue(), 0, 1) != '$')
			{
				throw new SyntaxError(
					'Variables should start with "$"',
					$token->getLine(),
					$this->stream->getFilename()
				);
			}

			else
			{
				$subVariable = new SubVariable($this->stream, $this->environment);
				$this->output .= ' ' . $subVariable->compile();
				$this->stream->previous();
			}
		}

		elseif($token->test(Token::NUMBER))
		{
			$this->output .= ' ' . $token->getValue();
		}

		elseif($token->test(Token::OPERATOR))
		{
			/*
			 * Expression regex pattern for:
			 * or, and, ==, !=, <, >, >=, <=, +, -, *, /, %
			 */
			switch($token->getValue())
			{
				case 'or':
					$this->output .= ' ||';
					break;

				case 'and':
					$this->output .= ' &&';
					break;

				case 'not':
					$this->output .= ' !';
					break;

				case '==':
				case '!=':
				case '>':
				case '>=':
				case '<':
				case '<=':
				case '*':
				case '+':
				case '-':
				case '/':
				case '%':
				case '~':
					$this->output .= ' ' . $token->getValue();
					break;

				default:
					throw new SyntaxError(
						sprintf('The operator "%s" is not supported.', $token->getValue()),
						$token->getLine(),
						$this->stream->getFilename()
					);
			}
		}

		elseif($token->test(Token::PUNCTUATION, '('))
		{
			$this->output .= ' (';
			$this->brackets += 1;
		}

		elseif($token->test(Token::PUNCTUATION, ')'))
		{
			$this->output .= ')';
			$this->brackets -= 1;
		}

		elseif($token->test(Token::STRING))
		{
			$this->output .= " '" . str_replace("'", "\'", ($token->getValue())) . "'";
		}

		$token = $this->stream->next();
		if(!$token->test(Token::BLOCK_END))
		{
			$this->process();
		}
	}

	/**
	 * Basic check to see if all opened brackets were properly closed.
	 */
	protected function validateBrackets()
	{
		if($this->brackets != 0)
		{
			throw new SyntaxError(
				'Not all opened brackets were properly closed.',
				$this->line,
				$this->stream->getFilename()
			);
		}
	}
}
