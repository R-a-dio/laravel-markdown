<?php namespace hiro\Markdown;

use Illuminate\Support\ServiceProvider;

class MarkdownServiceProvider extends ServiceProvider {
	
	public function register() {
		$this->app->bind('markdown', function () {
			return new MarkdownResolver($this->app["config"]);
		});
	}
}
