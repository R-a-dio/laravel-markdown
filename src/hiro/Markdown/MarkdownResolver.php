<?php namespace hiro\Markdown;

use Michelf\Markdown;
use Michelf\MarkdownExtra;
use \HTMLPurifier; // piece of shit that doesnt know what autoloading is. Sadly, nothing better is available.
use Illuminate\Config\Repository;

class MarkdownResolver {

	protected $instance;
	protected $purifier;
	protected $config;
	protected $purifier_config;
	protected $purifier_enabled;


	// setup using injected config
	public function __construct(Repository $config)
	{
		// choosing the instance to use~
		if ($config->get("markdown::extra", true) {
			$this->instance = new MarkdownExtra;
		} else {
			$this->instance = new Markdown;
		}

		$this->instance->camo_host = $config->get("radio.camo.host", "http://localhost:8081/");
		$this->instance->camo_key = $config->get("radio.camo.key", "0x24FEEDFACEDEADBEEFCAFE");

		$this->instance->empty_element_suffix = $config->get("markdown::empty", ">");
		$this->instance->no_markup = $config->get("markdown::safe", false);

		$this->purifier_config = new HTMLPurifier_Config::createDefault();
		$this->purifier_config->set("Cache.DefinitionImpl", null);
		$this->purifier_config->set("HTML.Doctype", $config->get("markdown::purifier.doctype", "HTML 4.01 Transitional"));

		foreach ($config->get("markdown::purifier.custom", [])
			as $namespace => $value)
		{

			$this->setPurifierConfig($namespace, $value);
		}

		$this->purifier_enabled = $config->get("markdown::purifier.enabled", false);
		$this->purifier = new HTMLPurifier($this->purifier_config);

	}


	// primary entry point
	public function render($text, $purify = false)
	{
		$text = $this->instance->transform($text);

		if ($purify or $this->purifier_enabled) {
			$text = $this->purifier->purify($text);
		}

		return $text;
	}

	// change to no custom HTML allowed. 
	public function setSafe($safe = true)
	{
		$this->instance->no_markup = $safe;
	}


	// render markdown with purify forced
	public function purify($text)
	{
		return $this->render($text, true);
	}


	// just purify text.
	public function purifyOnly($text)
	{
		return $this->purifier->purify($text);
	}


	// get the markdown instance by reference using Markdown::getInstance()
	public function &getInstance()
	{
		return $this->instance;
	}


	// set purifier namespace configs
	public function setPurifierConfig($namespace, $value)
	{
		$this->purifier_config->set($namespace, $value);

		// regenerate HTMLPurifier since it's a piece of garbage
		// and has no concept of standards (e.g. autoloading)
		$this->purifier = new HTMLPurifier($this->purifier_config);
	}
}
