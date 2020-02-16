<?php

class vk {
	public $bot = null;
	public $client = null;

	public $audio = null;

	public $client_type = 'lp';

	public function __construct(string $client_type, bool $needLowerCase = true) {
		if(!in_array($client_type, ['lp', 'cb'])) throw new TypeException();

		$this->init();

		$this->client_type = $client_type;
		$this->newBot($needLowerCase);
	}

	public function newBot(bool $needLowerCase = true) {
		$this->bot = new BotEngine($needLowerCase);
		return $this->bot;
	}

	public function listen() {
		$this->client = new DataHandler($this->client_type, $this->bot);
		return $this->client;
	}

	protected function init() {
		global $config;

		if(!empty($config) && isset($config['type']) && $config['type'] == 'user') $this->audio = new VkAudio();
	}
}

?>