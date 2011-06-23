<?php

	// Errors should become exceptions:
	set_error_handler(function($number, $message, $file, $line) {
		throw new \ErrorException($message, 0, $number, $file, $line);
	});

	// Turn plain text into a usable handle:
	function serialize_title($title) {
		return trim(preg_replace('%[^.\w]+%', '-', strtolower($title)), '-');
	}

	// Load the template:
	$template = new DOMDocument('1.0', 'utf8');
	$template->formatOutput = false;
	$template->load('assets/template.html');
	$template_xpath = new DOMXPath($template);

	$nav = $template_xpath->query('//header/nav/ol')->item(0);
	$footer = $template_xpath->query('//body/footer')->item(0);
	$body = $template_xpath->query('//body')->item(0);

	// Load all of the scripts and add them to the template:
	foreach (glob('scripts/*.html') as $file) {
		try {
			$script = new DOMDocument('1.0', 'utf8');
			$script->load($file);
			$script_xpath = new DOMXPath($script);
		}

		catch (Exception $e) {
			continue;
		}

		// Script is missing its header
		if ($script_xpath->evaluate('boolean(/article/h1 | /article/header/h1)') === false) {
			continue;
		}

		$article = $script_xpath->query('/article')->item(0);
		$title = $script_xpath->query('h1 | header/h1', $article)->item(0);
		$href = serialize_title($title->nodeValue);

		$article->setAttribute('id', $href);

		$item = $template->createElement('li');
		$link = $template->createElement('a', $title->nodeValue);
		$link->setAttribute('href', '#' . $href);
		$item->appendChild($link);
		$nav->appendChild($item);

		$body->appendChild(
			$template->importNode($article, true)
		);
	}

	// Move the footer to the end of the body:
	$body->appendChild($footer);

	// Output it!
	echo $template->saveHTML();

?>
