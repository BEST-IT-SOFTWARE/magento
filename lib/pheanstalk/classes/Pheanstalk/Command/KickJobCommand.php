<?php

/**
 * The 'kick' command.
 * Kicks buried or delayed jobs into a 'ready' state.
 * If there are buried jobs, it will kick up to $max of them.
 * Otherwise, it will kick up to $max delayed jobs.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Pheanstalk_Command_KickJobCommand
	extends Pheanstalk_Command_AbstractCommand
	implements Pheanstalk_ResponseParser
{
	private $_max;

	/**
	 * @param int $max The maximum number of jobs to kick
	 */
	public function __construct($job)
	{
		$this->_job = (int)$job;
	}

	/* (non-phpdoc)
	 * @see Pheanstalk_Command::getCommandLine()
	 */
	public function getCommandLine()
	{
		return 'kick-job '.$this->_job->getId();
	}

	/* (non-phpdoc)
	 * @see Pheanstalk_ResponseParser::parseRespose()
	 */
	public function parseResponse($responseLine, $responseData)
	{
		if ($responseLine == Pheanstalk_Response::RESPONSE_NOT_FOUND)
		{
			throw new Pheanstalk_Exception_ServerException(sprintf(
				'Cannot kick job %d: %s',
				$this->_job->getId(),
				$responseLine
			));
		}

		return $this->_createResponse($responseLine);
	}
}
